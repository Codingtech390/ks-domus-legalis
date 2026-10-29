#!/usr/bin/env python3
"""
K & S DOMUS LEGALIS — PROJECT CONTEXT PACK GENERATOR
Version 2.0.0

Read-only scanner for PHP/XAMPP/Apache/MySQL projects, designed to create a
multi-file handoff pack for ongoing development through deployment.

Safety:
- Never executes project code, PHP, SQL, shell commands, Composer, Git, Apache, or MySQL.
- Never edits project files. It only reads project files and writes a separate output pack.
- Secret files are excluded; likely secrets are redacted from embedded text (best effort).
- Binary assets are inventoried, never embedded.
- Excluded directories and files are reported so omissions are visible.
- No static scan can prove runtime correctness or guarantee secret detection.

Usage:
  python ks_domus_legal_portal_blueprint.py
  python ks_domus_legal_portal_blueprint.py "C:\\xampp\\htdocs\\legal-news-portal"
  python ks_domus_legal_portal_blueprint.py "C:\\path\\project" --output "D:\\ProjectPacks"
  python ks_domus_legal_portal_blueprint.py --help
"""
from __future__ import annotations

import argparse
import hashlib
import json
import os
import re
import sys
from collections import Counter, defaultdict
from datetime import datetime
from pathlib import Path
from typing import Iterable

VERSION = "2.0.0"
DEFAULT_PART_CHARS = 180_000
DEFAULT_MAX_EMBED_FILE_BYTES = 2_000_000

# Dependency/build/cache folders are always skipped. Upload/media and backups
# are inventory-only by default so their existence is documented without
# copying private or bulky contents into the pack.
ALWAYS_SKIP_DIRS = {
    ".git", ".svn", ".hg", ".idea", ".vscode", ".fleet",
    "node_modules", "vendor", "bower_components", "__pycache__",
    ".pytest_cache", ".mypy_cache", ".ruff_cache", "venv", ".venv",
    "coverage", "dist", "build", "out", "target", ".next", ".turbo",
    ".phpunit.cache", ".sass-cache", ".cache", "cache", "caches",
    "tmp", "temp", "logs", "log", "sessions", "playwright-report",
    "test-results",
}
INVENTORY_ONLY_DIRS = {
    "uploads", "upload", "media", "storage", "backups", "backup",
    "old", "archive", "archives",
}
SECRET_EXACT_NAMES = {
    ".env", ".env.local", ".env.production", ".env.development",
    ".env.test", ".env.staging", ".env.production.local",
    ".env.development.local", ".env.test.local", ".env.staging.local",
    ".htpasswd", "id_rsa", "id_ed25519", "id_ecdsa", "id_dsa",
    "known_hosts",
}
SAFE_ENV_TEMPLATES = {".env.example", ".env.sample", ".env.template", ".env.dist"}
ALWAYS_SKIP_FILES = {
    ".DS_Store", "Thumbs.db", "desktop.ini", "error_log", "debug.log",
    "php_errors.log", "npm-debug.log", "yarn-error.log",
}
TEXT_EXTENSIONS = {
    ".php", ".phtml", ".php5", ".phps", ".inc", ".html", ".htm",
    ".css", ".scss", ".sass", ".less", ".js", ".mjs", ".cjs", ".ts",
    ".tsx", ".jsx", ".json", ".jsonc", ".xml", ".yml", ".yaml", ".toml",
    ".ini", ".conf", ".config", ".htaccess", ".sql", ".md", ".txt",
    ".rst", ".sh", ".bat", ".cmd", ".ps1", ".py", ".csv", ".svg",
    ".webmanifest", ".lock", ".properties", ".env", ".example",
}
IMPORTANT_TEXT_NAMES = {
    "readme", "readme.md", "readme.txt", "composer.json", "composer.lock",
    "package.json", "package-lock.json", "pnpm-lock.yaml", "yarn.lock",
    "bun.lock", "bun.lockb", "phpunit.xml", "phpunit.xml.dist",
    "phpstan.neon", "phpstan.neon.dist", "php.ini", "dockerfile",
    "makefile", "schema.sql", "database.sql", "robots.txt", "sitemap.xml",
    ".htaccess", "web.config", "docker-compose.yml", "docker-compose.yaml",
    "procfile", ".gitignore", ".gitattributes", ".editorconfig",
}
BINARY_EXTENSIONS = {
    ".png", ".jpg", ".jpeg", ".gif", ".webp", ".avif", ".ico", ".bmp",
    ".tif", ".tiff", ".pdf", ".mp4", ".webm", ".mov", ".avi", ".mp3",
    ".wav", ".ogg", ".woff", ".woff2", ".ttf", ".otf", ".eot", ".zip",
    ".rar", ".7z", ".gz", ".tar", ".phar", ".exe", ".dll", ".so", ".bin",
    ".db", ".sqlite", ".sqlite3", ".bak", ".dump", ".psd", ".ai", ".sketch",
    ".fig", ".glb", ".gltf", ".doc", ".docx", ".xls", ".xlsx", ".ppt",
    ".pptx",
}
LANGUAGE_BY_SUFFIX = {
    ".php": "php", ".phtml": "php", ".php5": "php", ".phps": "php", ".inc": "php",
    ".sql": "sql", ".css": "css", ".scss": "scss", ".sass": "sass",
    ".less": "less", ".js": "javascript", ".mjs": "javascript", ".cjs": "javascript",
    ".ts": "typescript", ".tsx": "tsx", ".jsx": "jsx", ".json": "json",
    ".jsonc": "jsonc", ".html": "html", ".htm": "html", ".xml": "xml",
    ".yml": "yaml", ".yaml": "yaml", ".toml": "toml", ".ini": "ini",
    ".sh": "bash", ".bat": "bat", ".cmd": "bat", ".ps1": "powershell",
    ".py": "python", ".md": "markdown", ".txt": "text", ".csv": "csv",
    ".svg": "xml", ".htaccess": "apache", ".conf": "text", ".config": "text",
}
SECRET_KEYWORDS = r"(?:password|passwd|pwd|secret|token|api[_-]?key|access[_-]?key|client[_-]?secret|private[_-]?key|authorization)"
REDACTION_PATTERNS = [
    (re.compile(r"(?im)(\b(?:DB_PASS|DB_PASSWORD|DATABASE_PASSWORD|MYSQL_PASSWORD|MAIL_PASSWORD|SMTP_PASSWORD|APP_KEY|SECRET_KEY|JWT_SECRET|API_KEY|ACCESS_TOKEN|CLIENT_SECRET|STRIPE_SECRET_KEY|RAZORPAY_KEY_SECRET)\b\s*(?:=|=>|:|,)\s*)(['\"])[^'\"]*\2"), r"\1'[REDACTED]'"),
    (re.compile(r"(?im)(['\"]?" + SECRET_KEYWORDS + r"['\"]?\s*(?:=>|=|:)\s*)(['\"])[^'\"]*\2"), r"\1'[REDACTED]'"),
    (re.compile(r"(?im)(\b" + SECRET_KEYWORDS + r"\b\s*=\s*)([^\s;,#,]+)"), r"\1[REDACTED]"),
    (re.compile(r"(?im)(mysql:\s*host=[^;\s]+;dbname=[^;\s]+;[^\r\n]*password=)[^;\s]+"), r"\1[REDACTED]"),
    (re.compile(r"(?im)(Bearer\s+)[A-Za-z0-9._~+/\-=]{12,}"), r"\1[REDACTED_TOKEN]"),
    (re.compile(r"-----BEGIN (?:RSA |EC |OPENSSH |DSA )?PRIVATE KEY-----[\s\S]*?-----END (?:RSA |EC |OPENSSH |DSA )?PRIVATE KEY-----"), "[REDACTED PRIVATE KEY BLOCK]"),
    (re.compile(r"(?i)\b(?:sk_live|rk_live|ghp_|github_pat_|xox[baprs]-)[A-Za-z0-9_\-]{12,}\b"), "[REDACTED_TOKEN]"),
    (re.compile(r"(?im)^(\s*[^:\s]+:)(\$2[aby]\$[^\s]+|\$6\$[^\s]+)$"), r"\1[REDACTED_PASSWORD_HASH]"),
    (re.compile(r"(?im)(-----BEGIN CERTIFICATE-----[\s\S]*?-----END CERTIFICATE-----)"), "[CERTIFICATE OMITTED]"),
]

def now_local() -> datetime:
    return datetime.now().astimezone()

def rel(project: Path, path: Path) -> str:
    return path.relative_to(project).as_posix()

def sha256_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as f:
        for chunk in iter(lambda: f.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()

def fmt_size(n: int) -> str:
    if n < 1024:
        return f"{n} B"
    if n < 1024**2:
        return f"{n / 1024:.1f} KB"
    return f"{n / 1024**2:.2f} MB"

def is_secret_file(path: Path) -> bool:
    name = path.name.lower()
    if name in {x.lower() for x in SECRET_EXACT_NAMES}:
        return True
    if name.startswith(".env") and name not in SAFE_ENV_TEMPLATES:
        return True
    if name.endswith((".pem", ".key", ".p12", ".pfx", ".keystore")):
        return True
    return False

def is_inventory_only(path: Path, include_uploads: bool, include_backups: bool) -> bool:
    parts = {part.lower() for part in path.parts}
    upload_area = bool(parts & {"uploads", "upload", "media", "storage"})
    backup_area = bool(parts & {"backups", "backup", "old", "archive", "archives"})
    return (upload_area and not include_uploads) or (backup_area and not include_backups)

def skip_reason(path: Path, include_uploads: bool, include_backups: bool) -> str | None:
    name = path.name
    lower = name.lower()
    if path.is_symlink():
        return "SYMLINK_NOT_FOLLOWED"
    if path.is_dir():
        if lower in ALWAYS_SKIP_DIRS:
            return "DEPENDENCY_CACHE_OR_BUILD_DIRECTORY"
        if lower in INVENTORY_ONLY_DIRS:
            if lower in {"uploads", "upload", "media", "storage"} and not include_uploads:
                return "UPLOAD_MEDIA_STORAGE_INVENTORY_ONLY"
            if lower in {"backups", "backup", "old", "archive", "archives"} and not include_backups:
                return "BACKUP_ARCHIVE_INVENTORY_ONLY"
    if is_secret_file(path):
        return "SECRET_OR_PRIVATE_KEY_FILE"
    if path.is_file():
        if lower in ALWAYS_SKIP_FILES:
            return "NOISY_LOG_OR_OS_FILE"
        if lower.endswith((".log", ".swp", ".tmp", "~")):
            return "TEMPORARY_OR_LOG_FILE"
        if re.search(r"\.min\.(?:js|css)$", lower):
            return "MINIFIED_VENDOR_ASSET"
        if lower.endswith((".pyc", ".pyo")):
            return "COMPILED_PYTHON"
    return None

def is_text(path: Path) -> bool:
    return path.suffix.lower() in TEXT_EXTENSIONS or path.name.lower() in IMPORTANT_TEXT_NAMES or path.name in SAFE_ENV_TEMPLATES or path.name.lower() in {".htaccess", "web.config"}

def read_text(path: Path) -> str | None:
    try:
        data = path.read_bytes()
        # Avoid embedding arbitrary binary files that happen to use a text extension.
        if b"\x00" in data[:8192]:
            return None
        return data.decode("utf-8-sig", errors="replace")
    except OSError:
        return None

def redact(content: str) -> str:
    result = content
    for pattern, replacement in REDACTION_PATTERNS:
        result = pattern.sub(replacement, result)
    return result

def classify(path: Path) -> str:
    name, suffix, p = path.name.lower(), path.suffix.lower(), path.as_posix().lower()
    if name in {"composer.json", "composer.lock", "package.json", "package-lock.json", "pnpm-lock.yaml", "yarn.lock"}:
        return "DEPENDENCY_MANIFEST_OR_LOCKFILE"
    if name in {".htaccess", "web.config", "dockerfile", "docker-compose.yml", "docker-compose.yaml"}:
        return "DEPLOYMENT_CONFIGURATION"
    if suffix in {".php", ".phtml", ".php5", ".inc", ".phps"}:
        if "/admin/" in p or "/administrator/" in p:
            return "PHP_ADMIN_PAGE_OR_MODULE"
        if any(x in p for x in ("/includes/", "/partials/", "/templates/")):
            return "PHP_INCLUDE_OR_TEMPLATE"
        if any(x in p for x in ("/api/", "/ajax/")):
            return "PHP_ENDPOINT"
        if name in {"index.php", "post.php", "login.php", "logout.php", "create.php", "edit.php", "delete.php"}:
            return "PHP_ENTRY_OR_PAGE"
        return "PHP_SOURCE"
    if suffix == ".sql":
        return "SQL_SCHEMA_OR_MIGRATION"
    if suffix in {".css", ".scss", ".sass", ".less"}:
        return "STYLESHEET"
    if suffix in {".js", ".mjs", ".cjs", ".ts", ".tsx", ".jsx"}:
        return "JAVASCRIPT_OR_TYPESCRIPT"
    if suffix in {".html", ".htm"}:
        return "HTML_TEMPLATE"
    if suffix in {".md", ".rst", ".txt"} or name.startswith("readme"):
        return "DOCUMENTATION"
    if suffix in BINARY_EXTENSIONS:
        return "BINARY_OR_ASSET"
    if suffix in {".json", ".ini", ".conf", ".config", ".xml", ".yaml", ".yml", ".toml", ".lock"}:
        return "CONFIGURATION_OR_MANIFEST"
    return "OTHER_TEXT_OR_FILE"

def scan(project: Path, include_uploads: bool, include_backups: bool) -> dict:
    text_files, binary_files, empty_dirs, skipped, symlinks, unreadable = [], [], [], [], [], []
    for root, dirs, files in os.walk(project, followlinks=False):
        root_path = Path(root)
        kept_dirs = []
        for d in sorted(dirs, key=str.lower):
            p = root_path / d
            reason = skip_reason(p, include_uploads, include_backups)
            if reason and reason in {"UPLOAD_MEDIA_STORAGE_INVENTORY_ONLY", "BACKUP_ARCHIVE_INVENTORY_ONLY"}:
                skipped.append({"path": rel(project, p), "reason": reason, "kind": "directory"})
                kept_dirs.append(d)  # Traverse to inventory children, but do not embed them by default.
            elif reason:
                skipped.append({"path": rel(project, p), "reason": reason, "kind": "directory"})
            else:
                kept_dirs.append(d)
        dirs[:] = kept_dirs
        if root_path != project and not dirs and not files:
            empty_dirs.append(root_path)
        for filename in sorted(files, key=str.lower):
            path = root_path / filename
            reason = skip_reason(path, include_uploads, include_backups)
            if reason:
                skipped.append({"path": rel(project, path), "reason": reason, "kind": "file"})
                if path.is_symlink():
                    symlinks.append(path)
                continue
            if is_inventory_only(path, include_uploads, include_backups):
                # Inventory-only directories are traversed for paths/hashes, but their
                # text content is not embedded unless that specific area is opted in.
                binary_files.append(path)
                continue
            if is_text(path):
                if read_text(path) is not None:
                    text_files.append(path)
                else:
                    binary_files.append(path)
                    unreadable.append(rel(project, path))
            else:
                binary_files.append(path)
    key = lambda p: rel(project, p).lower()
    return {
        "text": sorted(text_files, key=key),
        "binary": sorted(binary_files, key=key),
        "empty_dirs": sorted(empty_dirs, key=key),
        "skipped": sorted(skipped, key=lambda x: x["path"].lower()),
        "symlinks": sorted(symlinks, key=key),
        "unreadable": sorted(set(unreadable)),
    }

def php_intelligence(content: str) -> dict:
    patterns = {
        "functions": r"\bfunction\s+&?\s*[A-Za-z_][A-Za-z0-9_]*\s*\([^\n]{0,240}\)",
        "classes": r"\b(?:abstract\s+|final\s+)?class\s+[A-Za-z_][A-Za-z0-9_]*",
        "includes": r"\b(?:require_once|require|include_once|include)\s*(?:\(\s*)?[^;\n]{1,240}",
        "request_inputs": r"\$_(?:GET|POST|REQUEST|SERVER|FILES|COOKIE)\s*(?:\[[^\]]+\])?",
        "session_usage": r"\$_SESSION\s*(?:\[[^\]]+\])?",
        "sql_tables": r"\b(?:FROM|JOIN|INTO|UPDATE|TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?|REFERENCES)\s+[`\"]?[A-Za-z_][A-Za-z0-9_]*[`\"]?",
        "csrf_hints": r"csrf[_-]?(?:token|field|verify)?|hash_equals\s*\(",
        "auth_hints": r"password_verify\s*\(|password_hash\s*\(|session_regenerate_id\s*\(|\b(?:auth_check|require_admin|is_admin|admin_required)\b",
    }
    return {k: sorted(set(m.group(0).strip() for m in re.finditer(v, content, re.I | re.M))) for k, v in patterns.items()}

def markdown_table(rows: list[list[str]]) -> str:
    if not rows:
        return "_None detected._\n"
    header = rows[0]
    out = ["| " + " | ".join(header) + " |", "| " + " | ".join("---" for _ in header) + " |"]
    out.extend("| " + " | ".join(str(c).replace("|", "\\|").replace("\n", " ") for c in row) + " |" for row in rows[1:])
    return "\n".join(out) + "\n"

def file_inventory(project: Path, scan_data: dict) -> str:
    files = scan_data["text"] + scan_data["binary"]
    rows = [["#", "Path", "Type", "Size", "SHA-256", "Content status"]]
    for i, p in enumerate(sorted(files, key=lambda x: rel(project, x).lower()), 1):
        try:
            size, digest = p.stat().st_size, sha256_file(p)
        except OSError:
            size, digest = 0, "UNAVAILABLE"
        if p in scan_data["binary"]:
            status = "Inventoried only (binary/asset or inventory-only folder)"
        else:
            raw = read_text(p) or ""
            status = "Empty" if not raw.strip() else "Text eligible for source parts"
        rows.append([str(i), rel(project, p), classify(p), fmt_size(size), digest, status])
    out = ["# File inventory", "", f"Scanned source root: `{project}`", "", markdown_table(rows)]
    out += ["## Excluded paths and reasons", ""]
    if scan_data["skipped"]:
        out.append(markdown_table([["Path", "Kind", "Reason"]] + [[x["path"], x["kind"], x["reason"]] for x in scan_data["skipped"]]))
    else:
        out.append("_No paths were excluded by directory/file rules._")
    out += ["", "## Empty directories", ""]
    out += [f"- `{rel(project, p)}/`" for p in scan_data["empty_dirs"]] or ["_None detected._"]
    out += ["", "## Unreadable or binary-looking files", ""]
    out += [f"- `{x}`" for x in scan_data["unreadable"]] or ["_None detected._"]
    out += ["", "## Notes", "",
            "- File hashes help compare snapshots; a hash does not validate file correctness.",
            "- Upload/media/storage and backup/archive directories are inventory-only unless explicitly enabled.",
            "- Secret files, dependency/build/cache folders, and noisy logs are excluded by default.",
            "- Inventory-only means the file path/metadata is documented, not its content."]
    return "\n".join(out) + "\n"

def database_report(project: Path, text_files: list[Path]) -> str:
    out = ["# Database and data model", "",
           "This is static analysis of SQL and source references. It does not connect to MySQL or inspect live rows.", ""]
    sql_files = [p for p in text_files if p.suffix.lower() == ".sql"]
    if not sql_files:
        out += ["No SQL files detected. Database structure may be created elsewhere or manually.", ""]
    for p in sql_files:
        content = read_text(p) or ""
        tables = sorted(set(m.group(1).strip('`"') for m in re.finditer(r"\bCREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`\"]?([A-Za-z_][A-Za-z0-9_]*)", content, re.I)))
        table_list = ", ".join("`" + t + "`" for t in tables) if tables else "No table names detected by simple matching."
        out += [f"## `{rel(project, p)}`", "", f"- Tables detected: {table_list}", ""]
    out += ["## SQL table references found in PHP/source", ""]
    refs = []
    for p in text_files:
        if p.suffix.lower() not in {".php", ".sql"}:
            continue
        raw = read_text(p) or ""
        names = sorted(set(m.group(1).strip('`"') for m in re.finditer(r"\b(?:FROM|JOIN|INTO|UPDATE|REFERENCES)\s+[`\"]?([A-Za-z_][A-Za-z0-9_]*)", raw, re.I)))
        if names:
            refs.append([rel(project, p), ", ".join(names)])
    out.append(markdown_table([["File", "Referenced tables"]] + refs) if refs else "_No references detected._")
    out += ["## Live database context checklist", "",
            "- [ ] Database name and server version recorded (do not include passwords).",
            "- [ ] Schema imported and migrations applied.",
            "- [ ] Current tables/columns compared with the schema files.",
            "- [ ] Relevant failing query and exact error recorded.",
            "- [ ] Current data state described using anonymized examples only.",
            "- [ ] Backup/restore process tested before production changes.", "",
            "Never export real client/case details or personal data into the context pack unless necessary and appropriately redacted."]
    return "\n".join(out) + "\n"

def architecture_security_report(project: Path, text_files: list[Path], scan_data: dict) -> str:
    out = ["# Architecture, configuration, security hints, and deployment", "",
           "> Static hints are not a security audit. Verify all findings manually and test the running application.", "",
           "## PHP source map", ""]
    php = [p for p in text_files if p.suffix.lower() in {".php", ".phtml", ".php5", ".inc", ".phps"}]
    rows = [["File", "Functions", "Classes", "Includes", "Request/session inputs", "CSRF hints", "Auth hints"]]
    for p in php:
        info = php_intelligence(read_text(p) or "")
        rows.append([rel(project, p), str(len(info["functions"])), str(len(info["classes"])), str(len(info["includes"])), str(len(info["request_inputs"]) + len(info["session_usage"])), str(len(info["csrf_hints"])), str(len(info["auth_hints"]))])
    out.append(markdown_table(rows))
    out += ["## Environment/configuration files detected", ""]
    config_names = []
    for p in text_files:
        raw = read_text(p) or ""
        if p.name.lower().startswith(".env") and p.name.lower() not in SAFE_ENV_TEMPLATES:
            continue
        for m in re.finditer(r"(?im)^\s*(?:const\s+|define\s*\(\s*['\"]|\$)?([A-Z][A-Z0-9_]{2,})\s*(?:=|,)", raw):
            name = m.group(1)
            if any(t in name for t in ("PASS", "SECRET", "TOKEN", "KEY", "PASSWORD")):
                config_names.append(name + " (value omitted)")
            elif name.startswith(("DB_", "APP_", "BASE_", "UPLOAD_", "MAX_", "MAIL_", "SMTP_", "SITE_")):
                config_names.append(name)
        for m in re.finditer(r"\b(?:getenv\s*\(\s*['\"]|\$_ENV\s*\[\s*['\"])([A-Z][A-Z0-9_]{2,})", raw):
            config_names.append(m.group(1))
    out.extend([f"- `{x}`" for x in sorted(set(config_names))] or ["_No configuration names detected by static patterns._"])
    out += ["", "## Static security indicators to review", "",
            "- [ ] Every admin mutation checks authentication and authorization server-side.",
            "- [ ] State-changing actions use POST (not GET) and validate CSRF tokens.",
            "- [ ] SQL uses prepared statements and input is validated by type and range.",
            "- [ ] All output is contextually escaped; rich HTML is sanitized with an allowlist.",
            "- [ ] Passwords use PHP `password_hash()` / `password_verify()`.",
            "- [ ] Sessions regenerate IDs on login; cookie flags are appropriate for deployment.",
            "- [ ] Uploads validate size, MIME/content, extension, generated filename, and storage location.",
            "- [ ] Upload folders cannot execute scripts; files are served safely.",
            "- [ ] Errors are logged privately; production responses do not expose stack traces or secrets.",
            "- [ ] Redirect targets are validated; no open redirect or path traversal.",
            "- [ ] Secrets are configured outside source control and rotated if exposed.",
            "- [ ] Dependencies are reviewed and production error display is disabled.", "",
            "## Deployment context checklist", "",
            "- [ ] Target host/plan and PHP version/extensions confirmed.",
            "- [ ] Database creation, least-privilege user, import/migration, and backup plan confirmed.",
            "- [ ] Document root points to the intended public directory.",
            "- [ ] Writable upload/storage directories and permissions confirmed.",
            "- [ ] HTTPS, domain/DNS, environment settings, and error logging configured.",
            "- [ ] Production credentials differ from local development credentials.",
            "- [ ] Smoke tests run on public pages, admin login, CRUD, uploads, and error paths.",
            "- [ ] Restore from a backup tested before launch."]
    return "\n".join(out) + "\n"

def overview_report(project: Path, scan_data: dict, generated: datetime, part_chars: int, max_file_bytes: int) -> str:
    text_files, binary_files = scan_data["text"], scan_data["binary"]
    classes = Counter(classify(p) for p in text_files + binary_files)
    out = ["# K & S Domus Legalis — Project Context Pack", "",
           f"- **Generator version:** {VERSION}",
           f"- **Generated:** {generated.strftime('%Y-%m-%d %H:%M:%S %Z')}",
           f"- **Project root:** `{project}`",
           f"- **Readable text files:** {len(text_files)}",
           f"- **Binary/asset or inventory-only files:** {len(binary_files)}",
           f"- **Excluded paths logged:** {len(scan_data['skipped'])}",
           f"- **Source part target size:** about {part_chars:,} characters",
           f"- **Maximum individual source file embedded:** {fmt_size(max_file_bytes)}", "",
           "> This pack is a read-only static snapshot. It does not execute the app, connect to MySQL, run tests, or prove correctness.", "",
           "## How to use this pack in a new ChatGPT chat", "",
           "1. Upload this overview and `04_PROJECT_STATUS.md` first.",
           "2. Upload `01_FILE_INVENTORY.md`, `02_DATABASE_AND_DATA_MODEL.md`, and `03_ARCHITECTURE_SECURITY_DEPLOYMENT.md`.",
           "3. Upload all `source_parts/source_part_*.md` files if upload limits allow. If not, upload the source part containing the relevant path, using `05_SOURCE_INDEX.md` to locate it.",
           "4. For a specific bug, include the exact current error and relevant screenshot/log. Do not assume the snapshot reflects unscanned edits.",
           "5. Ask ChatGPT to inspect the full current file and its callers before proposing changes.",
           "6. After meaningful code changes, regenerate the pack and update the status file.", "",
           "## Classification counts", "", markdown_table([["Classification", "Count"]] + [[k, str(v)] for k, v in sorted(classes.items())]),
           "## Important limitations", "",
           "- Database schema files are not the live database. No live records or credentials are collected.",
           "- Binary assets are inventoried, not embedded. Upload/media/storage and backups are inventory-only by default.",
           "- Screenshots, approved designs, conversation-only decisions, and manual test results must be added to the status file or uploaded separately.",
           "- Secret redaction is best-effort. Review the pack before sharing it, especially because this is a legal-firm project.",
           "- Source files larger than the configured limit are listed but not embedded; the manifest reports the reason.",
           "- Static scanning may misidentify code constructs and cannot certify security.", "",
           "## File guide", "",
           "- `01_FILE_INVENTORY.md` — scanned files, hashes, and excluded paths.",
           "- `02_DATABASE_AND_DATA_MODEL.md` — SQL files and source-level table references.",
           "- `03_ARCHITECTURE_SECURITY_DEPLOYMENT.md` — PHP source map and security/deployment checklist.",
           "- `04_PROJECT_STATUS.md` — editable project handoff notes; maintain this between chats.",
           "- `05_SOURCE_INDEX.md` — map from source file paths to source part numbers.",
           "- `source_parts/` — embedded source text, split into upload-friendly Markdown parts.",
           "- `manifest.json` — machine-readable scan details and per-file inclusion decisions.", "",
           "## Classification details", ""]
    return "\n".join(out) + "\n"

def status_template(project: Path, generated: datetime, user_notes: dict) -> str:
    def val(key: str, prompt: str) -> str:
        return user_notes.get(key, "").strip() or f"[Fill in: {prompt}]"
    return f"""# Project status and handoff — K & S Domus Legalis

> Update this file before each handoff. Do not include passwords, private keys, or unredacted client/case details.

- Snapshot generated: {generated.strftime('%Y-%m-%d %H:%M:%S %Z')}
- Project root: `{project}`

## Project goal
{val('goal', 'What the website is meant to do and who uses it')}

## Current state / what works
{val('works', 'Features verified working in the browser')}

## Known bugs / current blockers
{val('bugs', 'Exact issue, error text, affected URL/file, and reproduction steps')}

## Most recent changes
{val('changes', 'Files changed and what changed since the previous snapshot')}

## Decisions and preferences that must be preserved
{val('decisions', 'Approved UI/design, architecture choices, constraints, and things not to change')}

## Next task
{val('next', 'The single next step to do, in priority order')}

## Environment and test results
- Local environment: [e.g. Windows + XAMPP; record actual versions]
- PHP version/extensions: [record verified values]
- Database/schema status: [record without credentials]
- Browser/manual tests performed: [list and results]
- Deployment target/status: [local / staging / production; plan and blockers]

## Context not represented by source code
- Approved screenshots/design references: [file names or separate uploads]
- Prior-chat decisions: [important decisions not visible in code]
- Data/privacy constraints: [describe without including sensitive records]

## Handoff instructions for the next ChatGPT chat
- Read this status file and the project overview first.
- Treat the current source files as authoritative over stale descriptions.
- Before changing a file, inspect its complete current contents and related includes/callers.
- Preserve unrelated functionality; explain the exact files and changes.
- Do not claim tests passed unless they were actually run.
- For database or deployment changes, recommend a backup and rollback plan first.
"""

def split_source(project: Path, text_files: list[Path], part_dir: Path, part_chars: int,
                 max_file_bytes: int) -> tuple[list[dict], list[dict], str]:
    part_dir.mkdir(parents=True, exist_ok=True)
    parts: list[dict] = []
    index_entries: list[dict] = []
    current_sections: list[str] = []
    current_len = 0
    part_number = 0

    def flush() -> None:
        nonlocal current_sections, current_len, part_number
        if not current_sections:
            return
        part_number += 1
        name = f"source_part_{part_number:03d}.md"
        body = f"# Source files — part {part_number:03d}\n\n" + "\n\n".join(current_sections).rstrip() + "\n"
        (part_dir / name).write_text(body, encoding="utf-8")
        parts.append({"file": f"source_parts/{name}", "characters": len(body), "source_sections": len(current_sections)})
        current_sections, current_len = [], 0

    for path in text_files:
        path_rel = rel(project, path)
        try:
            size = path.stat().st_size
        except OSError:
            size = 0
        if size > max_file_bytes:
            index_entries.append({"path": path_rel, "status": "OMITTED_TOO_LARGE", "reason": f"File size {size} exceeds configured {max_file_bytes} byte limit.", "parts": []})
            continue
        raw = read_text(path)
        if raw is None:
            index_entries.append({"path": path_rel, "status": "OMITTED_UNREADABLE", "reason": "Could not decode as text.", "parts": []})
            continue
        if path.name in SAFE_ENV_TEMPLATES:
            names = sorted(set(re.findall(r"(?m)^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=", raw)))
            content = "\n".join(f"{n}=[VALUE OMITTED]" for n in names)
        else:
            content = redact(raw)
        lang = LANGUAGE_BY_SUFFIX.get(path.suffix.lower(), "text")
        if path.name.lower() == ".htaccess":
            lang = "apache"
        # A huge source file is split by character ranges, never silently truncated.
        chunks = []
        max_payload = max(5_000, part_chars - 1200)
        if len(content) > max_payload:
            start = 0
            while start < len(content):
                end = min(start + max_payload, len(content))
                if end < len(content):
                    newline = content.rfind("\n", start, end)
                    if newline > start + max_payload // 2:
                        end = newline + 1
                chunks.append((start, end, content[start:end]))
                start = end
        else:
            chunks = [(0, len(content), content)]
        part_names = []
        for chunk_idx, (start, end, chunk) in enumerate(chunks, 1):
            section_title = f"## `{path_rel}`"
            if len(chunks) > 1:
                line_start = content.count("\n", 0, start) + 1
                line_end = content.count("\n", 0, end) + 1
                section_title += f" — chunk {chunk_idx}/{len(chunks)} (approx. lines {line_start}-{line_end})"
            longest_fence = max((len(m.group(0)) for m in re.finditer(r"`+", chunk)), default=0)
            fence = "`" * max(3, longest_fence + 1)
            section = f"{section_title}\n\n- Type: `{classify(path)}`\n- Original size: {fmt_size(size)}\n- SHA-256: `{sha256_file(path)}`\n- Source chunk: {chunk_idx}/{len(chunks)}\n\n{fence}{lang}\n{chunk.rstrip()}\n{fence}"
            if current_sections and current_len + len(section) + 2 > part_chars:
                flush()
            if len(section) > part_chars and not current_sections:
                # max_payload normally prevents this; still avoid violating target.
                section = section[:part_chars - 100] + "\n\n[Section display limit reached; consult source file locally.]"
            current_sections.append(section)
            current_len += len(section) + 2
            if part_number == 0:
                target_part = 1
            else:
                target_part = part_number + (1 if current_len == len(section) + 2 else 0)
            # Final part assignment is resolved after flush by matching section headers below.
            part_names.append({"chunk": chunk_idx, "source_range": [start, end]})
        index_entries.append({"path": path_rel, "status": "EMBEDDED", "reason": "", "chunks": part_names, "sha256": sha256_file(path)})
    flush()
    # Build a reliable index from actual written part files and headings.
    for entry in index_entries:
        entry["parts"] = []
        if entry["status"] != "EMBEDDED":
            continue
        for part in parts:
            body = (part_dir / Path(part["file"]).name).read_text(encoding="utf-8")
            if f"## `{entry['path']}`" in body:
                entry["parts"].append(part["file"])
    index = ["# Source index", "", "Use this index to find the part containing a source file. Long files may be split into multiple chunks.", "",
             markdown_table([["Project path", "Status", "Source part(s)", "Notes"]] + [
                 [e["path"], e["status"], ", ".join(Path(p).name for p in e.get("parts", [])) or "—", e.get("reason", "")] for e in index_entries
             ])]
    return parts, index_entries, "\n".join(index) + "\n"

def ask_notes(interactive: bool) -> dict:
    if not interactive:
        return {}
    print("\nOptional project handoff notes (press Enter to leave any item as a fill-in prompt).")
    notes = {}
    prompts = {
        "goal": "Project goal",
        "works": "Features currently verified working",
        "bugs": "Known bugs / blockers",
        "changes": "Most recent changes",
        "decisions": "Decisions / preferences to preserve",
        "next": "Next task",
    }
    for key, label in prompts.items():
        notes[key] = input(f"{label}: ").strip()
    return notes

def build_pack(project: Path, output_root: Path, include_uploads: bool, include_backups: bool,
               part_chars: int, max_file_bytes: int, interactive: bool) -> Path:
    project = project.resolve()
    stamp = now_local()
    folder_name = f"KS_Domus_Legalis_Context_Pack_{stamp.strftime('%Y%m%d_%H%M%S')}"
    output_dir = output_root / folder_name
    output_dir.mkdir(parents=True, exist_ok=False)
    print("Scanning project read-only...")
    data = scan(project, include_uploads, include_backups)
    print(f"Text files: {len(data['text']):,} | Binary/inventory-only files: {len(data['binary']):,} | Excluded paths logged: {len(data['skipped']):,}")
    notes = ask_notes(interactive)
    files = {
        "00_PROJECT_OVERVIEW.md": overview_report(project, data, stamp, part_chars, max_file_bytes),
        "01_FILE_INVENTORY.md": file_inventory(project, data),
        "02_DATABASE_AND_DATA_MODEL.md": database_report(project, data["text"]),
        "03_ARCHITECTURE_SECURITY_DEPLOYMENT.md": architecture_security_report(project, data["text"], data),
        "04_PROJECT_STATUS.md": status_template(project, stamp, notes),
    }
    for name, content in files.items():
        (output_dir / name).write_text(content, encoding="utf-8")
    part_dir = output_dir / "source_parts"
    parts, source_manifest, source_index = split_source(project, data["text"], part_dir, part_chars, max_file_bytes)
    (output_dir / "05_SOURCE_INDEX.md").write_text(source_index, encoding="utf-8")
    all_files = data["text"] + data["binary"]
    manifest = {
        "generator": "K&S Domus Legalis PHP Portal Context Pack Generator",
        "version": VERSION,
        "generated_at": stamp.isoformat(),
        "project_root": str(project),
        "output_directory": str(output_dir),
        "safety": {
            "project_code_executed": False,
            "database_connected": False,
            "project_files_modified": False,
            "secret_redaction_is_best_effort": True,
            "binary_assets_embedded": False,
        },
        "options": {
            "include_uploads_and_storage": include_uploads,
            "include_backups_and_archives": include_backups,
            "source_part_target_characters": part_chars,
            "max_source_file_bytes": max_file_bytes,
        },
        "counts": {
            "text_files_scanned": len(data["text"]),
            "binary_or_inventory_only_files": len(data["binary"]),
            "files_inventoried": len(all_files),
            "excluded_paths": len(data["skipped"]),
            "empty_directories": len(data["empty_dirs"]),
            "source_parts": len(parts),
            "source_files_embedded": sum(1 for x in source_manifest if x["status"] == "EMBEDDED"),
            "source_files_omitted": sum(1 for x in source_manifest if x["status"] != "EMBEDDED"),
        },
        "files": [
            {
                "path": rel(project, p),
                "kind": "text" if p in data["text"] else "binary_or_inventory_only",
                "size_bytes": p.stat().st_size if p.exists() else None,
                "sha256": sha256_file(p) if p.exists() and p.is_file() else None,
                "classification": classify(p),
            } for p in sorted(all_files, key=lambda x: rel(project, x).lower())
        ],
        "source_embedding": source_manifest,
        "source_parts": parts,
        "excluded_paths": data["skipped"],
        "symlinks_not_followed": [rel(project, p) for p in data["symlinks"]],
        "unreadable_paths": data["unreadable"],
    }
    (output_dir / "manifest.json").write_text(json.dumps(manifest, indent=2, ensure_ascii=False), encoding="utf-8")
    readme = """# Read me first

This is a generated, read-only project context pack. It does not execute the project or connect to its database.

Before uploading:
1. Review `04_PROJECT_STATUS.md` and fill in any missing context.
2. Review the pack for private client/case information and secrets.
3. Upload `00_PROJECT_OVERVIEW.md`, `04_PROJECT_STATUS.md`, and the relevant technical/source files.
4. `manifest.json` lists all scanned files, excluded paths, and source inclusion decisions.

Do not upload `.env` files, credentials, private keys, or real client/case data unless absolutely necessary and properly redacted.
"""
    (output_dir / "README_FIRST.md").write_text(readme, encoding="utf-8")
    return output_dir

def parse_args(argv: list[str]) -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Create a multi-file project context pack for ongoing development.")
    parser.add_argument("project", nargs="?", help="Project folder path. If omitted, you will be prompted.")
    parser.add_argument("--output", help="Parent directory for the context-pack folder (default: project parent).")
    parser.add_argument("--part-chars", type=int, default=DEFAULT_PART_CHARS, help=f"Approximate maximum characters per source Markdown part (default: {DEFAULT_PART_CHARS}).")
    parser.add_argument("--max-file-mb", type=float, default=DEFAULT_MAX_EMBED_FILE_BYTES / 1_000_000, help="Maximum individual source file size to embed in bytes/MB (default: 2 MB).")
    parser.add_argument("--include-uploads", action="store_true", help="Allow text files inside uploads/media/storage folders to be embedded; binary files are still inventory-only.")
    parser.add_argument("--include-backups", action="store_true", help="Allow eligible text files inside backup/archive folders to be embedded.")
    parser.add_argument("--no-notes", action="store_true", help="Do not prompt for project status notes.")
    args = parser.parse_args(argv)
    if args.part_chars < 10_000:
        parser.error("--part-chars must be at least 10000.")
    if args.max_file_mb <= 0:
        parser.error("--max-file-mb must be greater than zero.")
    return args

def main(argv: list[str] | None = None) -> int:
    args = parse_args(argv or sys.argv[1:])
    entered = args.project
    if not entered:
        entered = input("Enter the FULL path to your project folder:\n> ").strip().strip('"')
    if not entered:
        print("No project folder provided. Nothing was changed.")
        return 2
    project = Path(entered).expanduser().resolve()
    if not project.exists() or not project.is_dir():
        print(f"Project folder not found or not a directory: {project}")
        return 2
    output_root = Path(args.output).expanduser().resolve() if args.output else project.parent
    try:
        output_root.mkdir(parents=True, exist_ok=True)
        if output_root == project or project in output_root.parents:
            print("WARNING: Output is inside the project folder. Use an output directory outside the project for a clean read-only workflow.")
            if input("Type YES to continue anyway: ").strip() != "YES":
                print("Cancelled. No project files were changed.")
                return 1
        output_dir = build_pack(project, output_root, args.include_uploads, args.include_backups,
                                args.part_chars, int(args.max_file_mb * 1_000_000), not args.no_notes)
    except FileExistsError:
        print("Output pack folder already exists; rerun to create a new timestamped pack.")
        return 1
    except (OSError, PermissionError) as exc:
        print(f"Could not create the context pack: {exc}")
        return 1
    print("\n" + "=" * 72)
    print("PROJECT CONTEXT PACK GENERATED")
    print("=" * 72)
    print(f"Output folder: {output_dir}")
    print(f"Main guide: {output_dir / 'README_FIRST.md'}")
    print(f"Overview: {output_dir / '00_PROJECT_OVERVIEW.md'}")
    print("Review the files before uploading. Secrets redaction is best-effort.")
    print("Project source files were not modified and no project code was executed.")
    return 0

if __name__ == "__main__":
    raise SystemExit(main())
