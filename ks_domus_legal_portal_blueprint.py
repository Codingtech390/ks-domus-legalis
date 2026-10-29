#!/usr/bin/env python3
"""
K & S DOMUS LEGALIS — PHP PORTAL BLUEPRINT GENERATOR
===================================================

Read-only project scanner designed for PHP 8.x / XAMPP / Apache / MySQL
portals, including legal-news, article, publishing, and admin-panel sites.

SAFETY
------
* Reads files only; never executes PHP or project code.
* Never runs Apache, MySQL, Composer, Git, or shell commands.
* Never installs packages or changes project configuration.
* Excludes common dependency, cache, backup, upload, and generated folders.
* Redacts likely secrets from embedded text, including passwords, tokens,
  connection strings, private-key blocks, and sensitive config assignments.
* Does not embed binary files; it inventories them by path, size, and hash.
* Writes only the generated Markdown blueprint. Default output is outside
  the selected project directory.

The resulting Markdown file is intended to be a source-of-truth handoff for
future development conversations. File presence and static pattern matches
are hints, not proof that a feature works correctly.
"""
from __future__ import annotations

import hashlib
import json
import os
import re
from collections import Counter
from datetime import datetime
from pathlib import Path

VERSION = "1.0.0"
MAX_EMBED_BYTES = 750_000
MAX_EMBED_FILES = 1500

SKIP_DIRS = {
    ".git", ".svn", ".hg", ".idea", ".vscode", "node_modules",
    "vendor", "bower_components", "__pycache__", ".pytest_cache",
    ".mypy_cache", ".ruff_cache", "venv", ".venv", "env",
    "cache", "caches", "tmp", "temp", "logs", "log", "sessions",
    "coverage", "dist", "build", "out", "target", ".next", ".turbo",
    ".phpunit.cache", "storage", "uploads", "upload", "media",
    "backups", "backup", "old", "archive", ".well-known",
}
SKIP_FILES = {
    ".DS_Store", "Thumbs.db", "desktop.ini", "error_log", "debug.log",
    "php_errors.log", "composer.lock", "package-lock.json", "yarn.lock",
    "pnpm-lock.yaml", "bun.lockb", "*.min.js", "*.min.css",
}
SECRET_NAMES = {
    ".env", ".env.local", ".env.production", ".env.development",
    ".env.test", ".env.production.local", ".env.development.local",
    ".env.test.local", "id_rsa", "id_ed25519", "known_hosts",
}
SAFE_ENV_TEMPLATES = {
    ".env.example", ".env.sample", ".env.template", ".env.dist",
}
TEXT_EXTENSIONS = {
    ".php", ".phtml", ".php5", ".phps", ".inc", ".html", ".htm",
    ".css", ".scss", ".js", ".mjs", ".cjs", ".ts", ".json",
    ".xml", ".yml", ".yaml", ".toml", ".ini", ".conf", ".config",
    ".htaccess", ".sql", ".md", ".txt", ".rst", ".sh", ".bat",
    ".ps1", ".py", ".csv", ".svg", ".webmanifest",
}
BINARY_EXTENSIONS = {
    ".png", ".jpg", ".jpeg", ".gif", ".webp", ".avif", ".ico",
    ".bmp", ".tif", ".tiff", ".pdf", ".mp4", ".webm", ".mov",
    ".avi", ".mp3", ".wav", ".ogg", ".woff", ".woff2", ".ttf",
    ".otf", ".eot", ".zip", ".rar", ".7z", ".gz", ".tar", ".phar",
    ".exe", ".dll", ".so", ".bin", ".db", ".sqlite", ".sqlite3",
    ".bak", ".dump", ".psd", ".ai", ".sketch", ".fig", ".glb", ".gltf",
}
IMPORTANT_NAMES = {
    "readme.md", "composer.json", "package.json", "phpunit.xml",
    "phpunit.xml.dist", "phpstan.neon", "phpstan.neon.dist", "php.ini",
    ".htaccess", "web.config", "dockerfile", "makefile", "schema.sql",
    "database.sql", "robots.txt", "sitemap.xml",
}

# Apply to source before embedding. These rules are deliberately conservative.
REDACTION_PATTERNS = [
    (re.compile(r"(?im)(\b(?:DB_PASS|DB_PASSWORD|DATABASE_PASSWORD|MYSQL_PASSWORD|MAIL_PASSWORD|SMTP_PASSWORD|APP_KEY|SECRET_KEY|JWT_SECRET|API_KEY|ACCESS_TOKEN|CLIENT_SECRET)\b\s*(?:=|=>|,)\s*)(['\"])[^'\"]*\2"), r"\1'[REDACTED]'"),
    (re.compile(r"(?im)(['\"]?(?:password|passwd|pwd|secret|token|api[_-]?key|access[_-]?key|client[_-]?secret|private[_-]?key)['\"]?\s*(?:=>|=|:)\s*)(['\"])[^'\"]*\2"), r"\1'[REDACTED]'"),
    (re.compile(r"(?im)^(\s*(?:define\s*\(\s*)?['\"]?(?:DB_PASS|DB_PASSWORD|DATABASE_PASSWORD|MYSQL_PASSWORD|MAIL_PASSWORD|SMTP_PASSWORD|APP_KEY|SECRET_KEY|JWT_SECRET|API_KEY|ACCESS_TOKEN|CLIENT_SECRET)['\"]?\s*(?:,|=|=>)\s*)(['\"]?)[^\r\n,;)]*", re.MULTILINE), r"\1[REDACTED]"),
    (re.compile(r"(?im)(\b(?:password|passwd|pwd|secret|token|api[_-]?key|access[_-]?key|client[_-]?secret|private[_-]?key)\b\s*(?:=|:|=>)\s*)(['\"])[^'\"]*\2"), r"\1'[REDACTED]'"),
    (re.compile(r"(?im)(\b(?:password|passwd|pwd|secret|token|api[_-]?key|access[_-]?key|client[_-]?secret|private[_-]?key)\b\s*=\s*)([^\s;,#]+)"), r"\1[REDACTED]"),
    (re.compile(r"(?im)(mysql:\s*host=[^;\s]+;dbname=[^;\s]+;[^\r\n]*password=)[^;\s]+"), r"\1[REDACTED]"),
    (re.compile(r"-----BEGIN (?:RSA |EC |OPENSSH |DSA )?PRIVATE KEY-----[\s\S]*?-----END (?:RSA |EC |OPENSSH |DSA )?PRIVATE KEY-----"), "[REDACTED PRIVATE KEY BLOCK]"),
    (re.compile(r"(?i)\b(?:sk_live|rk_live|ghp_|github_pat_|xox[baprs]-)[A-Za-z0-9_\-]{12,}\b"), "[REDACTED_TOKEN]"),
]


def sha256_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as stream:
        for chunk in iter(lambda: stream.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def rel(project: Path, path: Path) -> str:
    return path.relative_to(project).as_posix()


def format_size(size: int) -> str:
    if size < 1024:
        return f"{size} B"
    if size < 1024 * 1024:
        return f"{size / 1024:.1f} KB"
    return f"{size / (1024 * 1024):.2f} MB"


def should_skip(path: Path) -> bool:
    name = path.name
    lower = name.lower()
    if path.is_dir() and (name in SKIP_DIRS or lower in SKIP_DIRS):
        return True
    if lower in {x.lower() for x in SECRET_NAMES}:
        return True
    if name.startswith(".") and name not in SAFE_ENV_TEMPLATES and name not in {".htaccess", ".htpasswd"}:
        # Keep Apache rules, but skip hidden environment and tool files.
        if name not in {".htaccess", ".htpasswd"}:
            return True
    if path.is_file() and (name in SKIP_FILES or lower.endswith(".log")):
        return True
    if path.is_file() and (lower.endswith("~") or lower.endswith(".swp") or lower.endswith(".tmp")):
        return True
    return False


def is_text(path: Path) -> bool:
    return path.suffix.lower() in TEXT_EXTENSIONS or path.name.lower() in IMPORTANT_NAMES or path.name in SAFE_ENV_TEMPLATES or path.name in {".htaccess", ".htpasswd"}


def read_text(path: Path) -> str | None:
    try:
        return path.read_text(encoding="utf-8-sig", errors="replace")
    except (OSError, UnicodeError):
        return None


def redact(content: str) -> str:
    result = content
    for pattern, replacement in REDACTION_PATTERNS:
        result = pattern.sub(replacement, result)
    # Redact common .htpasswd-style bcrypt/crypt hashes if accidentally embedded.
    result = re.sub(r"(?im)^(\s*[^:\s]+:)(\$2[aby]\$[^\s]+|\$6\$[^\s]+)$", r"\1[REDACTED_PASSWORD_HASH]", result)
    return result


def classify(path: Path) -> str:
    name = path.name.lower()
    suffix = path.suffix.lower()
    p = path.as_posix().lower()
    if name in {"composer.json", "package.json", "phpunit.xml", "phpunit.xml.dist"}:
        return "DEPENDENCY_OR_TEST_CONFIG"
    if name == ".htaccess" or name == "web.config":
        return "WEB_SERVER_CONFIGURATION"
    if suffix == ".php" or suffix in {".phtml", ".php5", ".inc", ".phps"}:
        if any(x in p for x in ("/admin/", "/administrator/")):
            return "PHP_ADMIN_PAGE_OR_MODULE"
        if any(x in p for x in ("/includes/", "/partials/", "/templates/")):
            return "PHP_INCLUDE_OR_TEMPLATE"
        if any(x in p for x in ("/api/", "/ajax/")):
            return "PHP_ENDPOINT"
        if name in {"index.php", "post.php", "login.php", "logout.php", "create.php", "edit.php", "delete.php"}:
            return "PHP_ENTRY_OR_PAGE"
        return "PHP_SOURCE"
    if suffix == ".sql":
        return "SQL_SCHEMA_OR_DUMP"
    if suffix in {".css", ".scss"}:
        return "STYLESHEET"
    if suffix in {".js", ".mjs", ".cjs"}:
        return "JAVASCRIPT"
    if suffix in {".html", ".htm"}:
        return "HTML_TEMPLATE"
    if suffix in {".md", ".txt", ".rst"} or name == "readme.md":
        return "DOCUMENTATION"
    if suffix in BINARY_EXTENSIONS:
        return "ASSET_OR_BINARY"
    if suffix in {".json", ".ini", ".conf", ".config", ".xml", ".yaml", ".yml", ".toml"}:
        return "CONFIGURATION"
    return "OTHER_TEXT_OR_FILE"


def scan(project: Path):
    text_files: list[Path] = []
    binary_files: list[Path] = []
    empty_dirs: list[Path] = []
    symlinks: list[Path] = []
    for root, dirs, files in os.walk(project, followlinks=False):
        root_path = Path(root)
        dirs[:] = sorted([d for d in dirs if not should_skip(root_path / d) and not (root_path / d).is_symlink()], key=str.lower)
        if root_path != project and not dirs and not files:
            empty_dirs.append(root_path)
        for filename in sorted(files, key=str.lower):
            path = root_path / filename
            if should_skip(path):
                continue
            if path.is_symlink():
                symlinks.append(path)
                continue
            if is_text(path):
                text_files.append(path)
            else:
                binary_files.append(path)
    key = lambda p: rel(project, p).lower()
    return sorted(text_files, key=key), sorted(binary_files, key=key), sorted(empty_dirs, key=key), sorted(symlinks, key=key)


def find_matches(content: str, pattern: str) -> list[str]:
    return sorted(set(m.group(0).strip() for m in re.finditer(pattern, content, re.IGNORECASE | re.MULTILINE)))


def php_intelligence(content: str) -> dict:
    php_functions = find_matches(content, r"\bfunction\s+&?\s*[A-Za-z_][A-Za-z0-9_]*\s*\([^\n]{0,240}\)")
    includes = find_matches(content, r"\b(?:require_once|require|include_once|include)\s*(?:\(\s*)?[^;\n]{1,240}")
    classes = find_matches(content, r"\b(?:abstract\s+|final\s+)?class\s+[A-Za-z_][A-Za-z0-9_]*")
    routes = find_matches(content, r"\$_(?:GET|POST|REQUEST|SERVER|FILES)\s*(?:\[[^\]]+\])?")
    form_actions = find_matches(content, r"<form\b[^>]{0,500}>")
    sql_tables = find_matches(content, r"\b(?:FROM|JOIN|INTO|UPDATE|TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?|REFERENCES)\s+[`\"]?[A-Za-z_][A-Za-z0-9_]*[`\"]?")
    session_refs = find_matches(content, r"\$_SESSION\s*(?:\[[^\]]+\])?")
    csrf_refs = find_matches(content, r"csrf[_-]?(?:token|field|verify)?|hash_equals\s*\(")
    auth_refs = find_matches(content, r"password_verify\s*\(|password_hash\s*\(|session_regenerate_id\s*\(|\b(?:auth_check|require_admin|is_admin|admin_required)\b")
    methods = find_matches(content, r"\b(?:GET|POST|PUT|PATCH|DELETE)\b")
    return {
        "functions": php_functions,
        "classes": classes,
        "includes": includes,
        "superglobals": routes,
        "forms": form_actions,
        "sql_tables": sql_tables,
        "session_usage": session_refs,
        "csrf_hints": csrf_refs,
        "authentication_hints": auth_refs,
        "http_method_mentions": methods,
    }


def render_tree(project: Path, max_nodes: int = 10000) -> list[str]:
    lines: list[str] = [project.name + "/"]
    count = 1
    def visit(directory: Path, prefix: str = ""):
        nonlocal count
        try:
            children = sorted([p for p in directory.iterdir() if not should_skip(p) and not p.is_symlink()], key=lambda p: (p.is_file(), p.name.lower()))
        except (PermissionError, OSError):
            lines.append(prefix + "└── [ACCESS DENIED]")
            return
        for i, child in enumerate(children):
            if count >= max_nodes:
                lines.append(prefix + "└── [TREE LIMIT REACHED]")
                return
            count += 1
            last = i == len(children) - 1
            lines.append(prefix + ("└── " if last else "├── ") + child.name + ("/" if child.is_dir() else ""))
            if child.is_dir():
                visit(child, prefix + ("    " if last else "│   "))
    visit(project)
    return lines


def build_blueprint(project: Path, text_files: list[Path], binary_files: list[Path], empty_dirs: list[Path], symlinks: list[Path], started: datetime) -> str:
    now = datetime.now().astimezone()
    all_files = text_files + binary_files
    classes = Counter(classify(p) for p in all_files)
    lines: list[str] = []
    add = lines.append
    add("# K & S DOMUS LEGALIS — PROJECT BLUEPRINT")
    add("")
    add(f"**Blueprint version:** {VERSION}  ")
    add(f"**Generated:** {now.strftime('%Y-%m-%d %H:%M:%S %Z')}  ")
    add(f"**Project root:** `{project}`  ")
    add("**Project type:** PHP legal portal / legal-news publishing website (intended classification; actual implementation is determined by scanned files).")
    add("")
    add("> [!IMPORTANT]")
    add("> This is a static, read-only snapshot. It does not execute the project, connect to MySQL, test routes, or prove that features work. Source code and live testing remain authoritative.")
    add("")
    add("## 1. Safety and privacy")
    add("")
    add("- The scanner does not execute PHP, JavaScript, SQL, Composer, Git, Apache, or MySQL.")
    add("- It does not modify, move, rename, or delete project files.")
    add("- The generated Markdown file is written outside the project by default.")
    add("- Common dependency, generated, cache, upload/media, backup, and log directories are skipped.")
    add("- Environment secret files are excluded. Likely credentials and tokens are redacted from source text before embedding.")
    add("- Review the generated blueprint before sharing it. Static secret redaction is best-effort and cannot guarantee detection of every secret.")
    add("")
    add("## 2. Automatic project summary")
    add("")
    add(f"- Readable source/config/document files: **{len(text_files)}**")
    add(f"- Other/binary/asset files inventoried: **{len(binary_files)}**")
    add(f"- Total files inventoried: **{len(all_files)}**")
    add(f"- Empty visible directories: **{len(empty_dirs)}**")
    add(f"- Symbolic links skipped: **{len(symlinks)}**")
    add(f"- Scan duration: **{(datetime.now() - started).total_seconds():.2f} seconds**")
    add("")
    add("### File classification")
    add("")
    add("| Classification | Count |")
    add("|---|---:|")
    for name, count in sorted(classes.items()):
        add(f"| `{name}` | {count} |")
    add("")
    add("## 3. PHP portal structure hints")
    add("")
    rel_names = [rel(project, p) for p in all_files]
    expected = [
        ("Public homepage", ["index.php"]),
        ("Article detail page", ["post.php", "article.php"]),
        ("Admin login", ["admin/login.php"]),
        ("Admin dashboard", ["admin/index.php", "admin/dashboard.php"]),
        ("Admin article create/edit/delete", ["admin/create.php", "admin/edit.php", "admin/delete.php"]),
        ("Shared header/footer", ["includes/header.php", "includes/footer.php"]),
        ("Authentication guard", ["includes/auth_check.php", "includes/auth.php", "includes/admin_auth.php"]),
        ("Database configuration", ["config/database.php", "includes/database.php", "config.php"]),
        ("Content sanitizer", ["includes/content_sanitizer.php"]),
        ("Article helpers/card", ["includes/article_helpers.php", "includes/article_card.php"]),
        ("Database schema", ["schema.sql", "database.sql", "db/schema.sql"]),
        ("Stylesheet", ["assets/css/style.css", "assets/css/main.css"]),
        ("JavaScript", ["assets/js/app.js", "assets/js/main.js"]),
        ("Apache rules", [".htaccess"]),
    ]
    normalized = {x.lower() for x in rel_names}
    add("The table checks for common portal files by path. A missing match does not necessarily mean the feature is absent; filenames may differ.")
    add("")
    add("| Area | Matching file found |")
    add("|---|---|")
    for label, candidates in expected:
        found = next((c for c in candidates if c.lower() in normalized), None)
        add(f"| {label} | {('`' + found + '`') if found else 'Not detected by expected path'} |")
    add("")
    add("## 4. Complete project tree")
    add("")
    add("```text")
    lines.extend(render_tree(project))
    add("```")
    add("")
    add("## 5. Complete file inventory")
    add("")
    add("| # | Path | Classification | Size | SHA-256 | Status hint |")
    add("|---:|---|---|---:|---|---|")
    for i, path in enumerate(sorted(all_files, key=lambda p: rel(project, p).lower()), 1):
        try:
            size = path.stat().st_size
            digest = sha256_file(path)
        except OSError:
            size, digest = 0, "UNAVAILABLE"
        if path in binary_files:
            status = "ASSET / NOT EMBEDDED"
        else:
            content = read_text(path) or ""
            markers = []
            if re.search(r"\bTODO\b", content, re.I): markers.append("TODO")
            if re.search(r"\bFIXME\b", content, re.I): markers.append("FIXME")
            if re.search(r"coming\s+soon|not\s+implemented", content, re.I): markers.append("PLACEHOLDER")
            status = ", ".join(markers) if markers else ("EMPTY" if not content.strip() else "HAS CONTENT")
        add(f"| {i} | `{rel(project, path)}` | `{classify(path)}` | {format_size(size)} | `{digest}` | {status} |")
    add("")
    add("## 6. Binary and asset inventory")
    add("")
    if binary_files:
        ext_counts = Counter(p.suffix.lower() or "[no extension]" for p in binary_files)
        add("### Asset types")
        add("")
        add("| Extension | Count |")
        add("|---|---:|")
        for ext, count in sorted(ext_counts.items()): add(f"| `{ext}` | {count} |")
        add("")
        add("### Asset paths")
        add("")
        for p in binary_files:
            try: size = p.stat().st_size
            except OSError: size = 0
            add(f"- `{rel(project, p)}` — {format_size(size)}")
    else:
        add("No binary files were detected.")
    add("")
    add("## 7. Empty directories")
    add("")
    if empty_dirs:
        for p in empty_dirs: add(f"- `{rel(project, p)}/`")
    else:
        add("No empty visible directories detected.")
    add("")
    add("## 8. Database and SQL intelligence")
    add("")
    sql_files = [p for p in text_files if p.suffix.lower() == ".sql"]
    if not sql_files:
        add("No `.sql` files were detected. Database tables may be created manually or documented elsewhere.")
    for p in sql_files:
        content = read_text(p) or ""
        tables = sorted(set(m.group(1).strip('`\"') for m in re.finditer(r"\bCREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`\"]?([A-Za-z_][A-Za-z0-9_]*)", content, re.I)))
        add(f"### `{rel(project, p)}`")
        add("")
        add(f"- CREATE TABLE names detected: {', '.join(f'`{x}`' for x in tables) if tables else 'None detected by simple pattern matching'}")
        add("- SQL is inventoried as source text and may have sensitive seed data redacted.")
        add("")
    add("## 9. PHP symbols, includes, forms, and security hints")
    add("")
    php_files = [p for p in text_files if p.suffix.lower() in {".php", ".phtml", ".php5", ".inc", ".phps"}]
    if not php_files:
        add("No PHP files were detected.")
    else:
        for p in php_files:
            raw = read_text(p)
            if raw is None: continue
            info = php_intelligence(raw)
            add(f"### `{rel(project, p)}`")
            add("")
            add(f"- Functions detected: {len(info['functions'])}")
            add(f"- Classes detected: {len(info['classes'])}")
            add(f"- Include/require statements detected: {len(info['includes'])}")
            add(f"- Request/session superglobal references: {len(info['superglobals'])}")
            add(f"- Form tags detected: {len(info['forms'])}")
            add(f"- SQL table references detected: {len(info['sql_tables'])}")
            add(f"- CSRF-related hints: {len(info['csrf_hints'])}")
            add(f"- Authentication-related hints: {len(info['authentication_hints'])}")
            for label, key in (("Functions", "functions"), ("Classes", "classes"), ("Includes / requires", "includes"), ("Form actions", "forms"), ("SQL table references", "sql_tables"), ("Authentication hints", "authentication_hints"), ("CSRF hints", "csrf_hints")):
                values = info[key]
                if values:
                    add(f"  - {label}: " + "; ".join(f"`{v[:180]}`" for v in values[:20]) + (f"; … (+{len(values)-20})" if len(values) > 20 else ""))
            add("")
    add("## 10. External libraries and frontend tooling")
    add("")
    for filename in ("composer.json", "package.json"):
        p = project / filename
        if p.exists() and p.is_file():
            try:
                data = json.loads(read_text(p) or "{}")
            except json.JSONDecodeError:
                data = {}
            add(f"### `{filename}`")
            add("")
            if not data:
                add("Could not parse this JSON file.")
            else:
                if data.get("name"): add(f"- Name: `{data['name']}`")
                if data.get("require"):
                    add("- Composer requirements:")
                    for k, v in sorted(data["require"].items()): add(f"  - `{k}`: `{v}`")
                if data.get("scripts"):
                    add("- Scripts:")
                    for k, v in sorted(data["scripts"].items()): add(f"  - `{k}`: `{str(v)[:240]}`")
                for group in ("dependencies", "devDependencies"):
                    if data.get(group):
                        add(f"- {group}:")
                        for k, v in sorted(data[group].items()): add(f"  - `{k}`: `{v}`")
            add("")
    add("## 11. Environment and configuration names")
    add("")
    add("Secret environment files are excluded. The scanner lists likely variable/constant names only when detectable; it does not intentionally report their values.")
    add("")
    config_names: set[str] = set()
    for p in text_files:
        if p.name in SECRET_NAMES or p.name.startswith(".env") and p.name not in SAFE_ENV_TEMPLATES:
            continue
        content = read_text(p) or ""
        for m in re.finditer(r"(?im)^\s*(?:const\s+|define\s*\(\s*['\"]|\$)?([A-Z][A-Z0-9_]{2,})\s*(?:=|,)", content):
            name = m.group(1)
            if any(t in name for t in ("PASS", "SECRET", "TOKEN", "KEY", "PASSWORD")):
                config_names.add(name + " (value intentionally not shown)")
            elif name.startswith(("DB_", "APP_", "BASE_", "UPLOAD_", "MAX_", "MAIL_", "SMTP_")):
                config_names.add(name)
        for m in re.finditer(r"\b(?:getenv\s*\(\s*['\"]|\$_ENV\s*\[\s*['\"])([A-Z][A-Z0-9_]{2,})", content):
            config_names.add(m.group(1))
    if config_names:
        for name in sorted(config_names): add(f"- `{name}`")
    else:
        add("No configuration constant/environment names detected by the static patterns.")
    add("")
    add("## 12. TODOs, placeholders, and development markers")
    add("")
    marker_found = False
    for p in text_files:
        content = read_text(p) or ""
        matches = []
        for label, pattern in (("TODO", r"\bTODO\b"), ("FIXME", r"\bFIXME\b"), ("HACK", r"\bHACK\b"), ("COMING SOON", r"coming\s+soon"), ("NOT IMPLEMENTED", r"not\s+(?:yet\s+)?implemented"), ("PLACEHOLDER", r"placeholder")):
            count = len(re.findall(pattern, content, re.I))
            if count: matches.append(f"{label}={count}")
        if matches:
            marker_found = True
            add(f"- `{rel(project, p)}`: {', '.join(matches)}")
    if not marker_found: add("No common development markers detected.")
    add("")
    add("## 13. Embedded source and configuration contents")
    add("")
    add(f"Full content is limited to files at or below **{format_size(MAX_EMBED_BYTES)}** and the first **{MAX_EMBED_FILES}** text files. Content is redacted before inclusion.")
    add("")
    add("> Note: upload directories, dependencies, caches, logs, backups, environment secrets, and binary files are not embedded. Review redaction before sharing this blueprint.")
    add("")
    embedded_count = 0
    for p in text_files:
        if embedded_count >= MAX_EMBED_FILES:
            add(f"> Remaining files omitted after the {MAX_EMBED_FILES}-file embedding limit; they remain listed in the inventory.")
            add("")
            break
        try: size = p.stat().st_size
        except OSError: size = 0
        if size > MAX_EMBED_BYTES:
            add(f"### `{rel(project, p)}`")
            add("")
            add(f"Content not embedded because file size ({format_size(size)}) exceeds the limit. File remains inventoried.")
            add("")
            continue
        raw = read_text(p)
        if raw is None:
            add(f"### `{rel(project, p)}`")
            add("")
            add("Content could not be read as text.")
            add("")
            continue
        # Safe env templates: include only variable names, never assignment values.
        if p.name in SAFE_ENV_TEMPLATES:
            names = sorted(set(re.findall(r"(?m)^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=", raw)))
            content = "\n".join(f"{name}=[VALUE OMITTED]" for name in names)
        else:
            content = redact(raw)
        language = {".php": "php", ".phtml": "php", ".inc": "php", ".sql": "sql", ".css": "css", ".scss": "scss", ".js": "javascript", ".mjs": "javascript", ".cjs": "javascript", ".json": "json", ".html": "html", ".htm": "html", ".md": "markdown", ".xml": "xml", ".yml": "yaml", ".yaml": "yaml", ".ini": "ini", ".sh": "bash", ".ps1": "powershell", ".py": "python", ".txt": "text", ".htaccess": "apache"}.get(p.suffix.lower(), "text")
        add(f"### {embedded_count + 1}. `{rel(project, p)}`")
        add("")
        add(f"- Classification: `{classify(p)}`")
        add(f"- Size: {format_size(size)}")
        add(f"- Lines: {len(raw.splitlines())}")
        add("")
        add(f"```{language}")
        add(content.rstrip())
        add("```")
        add("")
        embedded_count += 1
    add("## 14. Handoff and interpretation rules")
    add("")
    add("1. This blueprint is a timestamped snapshot of files that existed at scan time.")
    add("2. A file existing does not prove that its feature works or is secure.")
    add("3. Static pattern matching can miss dynamic PHP behavior and can produce false positives.")
    add("4. The live source code is more authoritative than an older blueprint.")
    add("5. Preserve existing functionality and architecture unless a clear reason supports changing them.")
    add("6. Before changing a file, inspect the full current file and its callers/includes.")
    add("7. Do not claim routes, admin authorization, CSRF, sanitization, or database behavior are correct without reviewing and testing them.")
    add("8. Treat this document as confidential if it contains client, case, article-draft, or internal legal-firm information.")
    add("")
    add("## 15. Suggested development workflow for K & S Domus Legalis")
    add("")
    add("Use this blueprint as the current technical snapshot, then maintain a separate human-written status section for decisions that static scanning cannot infer. Recommended review areas:")
    add("")
    for item in [
        "Public portal: homepage, article detail, category pages, search, pagination, related articles, SEO metadata, responsive layout.",
        "Editorial admin: authentication, role/permission checks, create/edit/publish/unpublish/delete articles, categories, media uploads, previews.",
        "Security: password hashing, session cookie settings, session ID regeneration, CSRF on mutations, output escaping, rich-text sanitization, prepared SQL statements, upload validation, safe redirects, access control.",
        "Data integrity: schema and migrations, foreign keys, unique slugs, indexes, published/draft states, timestamps, seed data.",
        "Operations: XAMPP local setup, PHP version/extensions, database import, error logging, backup/restore, production deployment differences, HTTPS and secrets management.",
        "Legal publishing governance: editorial review, author attribution, publication/update dates, disclaimer policy, source citations, correction history, and access control for unpublished material.",
    ]: add(f"- {item}")
    add("")
    add("---")
    add("")
    add("*Generated by K&S Domus Legalis PHP Portal Blueprint Generator. Read-only scanner; no project code was executed.*")
    return "\n".join(lines) + "\n"


def main() -> None:
    print("=" * 72)
    print(" K & S DOMUS LEGALIS — PHP PORTAL BLUEPRINT GENERATOR")
    print("=" * 72)
    print("READ-ONLY SCAN: project code will not be executed or changed.\n")
    while True:
        entered = input("Enter the FULL path to the project folder (e.g. C:\\xampp\\htdocs\\legal-news-portal):\n> ").strip().strip('"')
        if not entered:
            print("Please enter a project folder path.")
            continue
        project = Path(entered).expanduser().resolve()
        if not project.exists() or not project.is_dir():
            print(f"Folder not found or not a directory: {project}")
            continue
        break
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    default_output = project.parent / f"KS_Domus_Legalis_Project_Blueprint_{timestamp}.md"
    print(f"\nDefault output (outside the project):\n{default_output}")
    output_entered = input("\nPress Enter to use this path, or enter another output file path:\n> ").strip().strip('"')
    output = Path(output_entered).expanduser().resolve() if output_entered else default_output.resolve()
    try:
        output.relative_to(project)
        inside = True
    except ValueError:
        inside = False
    if inside:
        print("\nWARNING: The output path is inside the project folder.")
        if input("Type YES to continue (anything else cancels):\n> ").strip() != "YES":
            print("Cancelled. No project files were changed.")
            return
    if output.exists() and input(f"\nOutput already exists: {output}\nType OVERWRITE to replace it, or anything else to cancel:\n> ").strip() != "OVERWRITE":
        print("Cancelled. Existing output was not changed.")
        return
    started = datetime.now()
    print("\nScanning files (read-only)...")
    text_files, binary_files, empty_dirs, symlinks = scan(project)
    print(f"Readable source/config files: {len(text_files):,}")
    print(f"Other/binary/asset files: {len(binary_files):,}")
    print(f"Empty directories: {len(empty_dirs):,}")
    print("Generating Markdown blueprint...")
    content = build_blueprint(project, text_files, binary_files, empty_dirs, symlinks, started)
    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(content, encoding="utf-8")
    print("\n" + "=" * 72)
    print("BLUEPRINT GENERATED")
    print("=" * 72)
    print(f"Output: {output}")
    print(f"Blueprint lines: {len(content.splitlines()):,}")
    print(f"Files inventoried: {len(text_files) + len(binary_files):,}")
    print("The scanned project was not modified.")
    print("Review the generated Markdown for accidental sensitive information before uploading it anywhere.")


if __name__ == "__main__":
    main()
