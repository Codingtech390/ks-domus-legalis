<?php

declare(strict_types=1);

/**
 * Ensure that the portal has starter categories.
 *
 * This does not create database tables or alter existing categories.
 * The categories table must already exist.
 */
function ensure_default_categories(PDO $pdo): void
{
    $count = (int) $pdo
        ->query('SELECT COUNT(*) FROM categories')
        ->fetchColumn();

    if ($count > 0) {
        return;
    }

    $defaults = [
        [
            'Supreme Court',
            'supreme-court',
            'Supreme Court judgments and legal developments.',
        ],
        [
            'Constitutional Law',
            'constitutional-law',
            'Constitutional questions, rights and public law.',
        ],
        [
            'Corporate Law',
            'corporate-law',
            'Company law, transactions and regulatory developments.',
        ],
        [
            'Criminal Law',
            'criminal-law',
            'Criminal justice, procedure and notable judgments.',
        ],
        [
            'Civil Law',
            'civil-law',
            'Civil litigation, disputes and procedural updates.',
        ],
        [
            'Legal Analysis',
            'legal-analysis',
            'Commentary and analysis of important legal issues.',
        ],
    ];

    $statement = $pdo->prepare(
        'INSERT IGNORE INTO categories (name, slug, description)
         VALUES (:name, :slug, :description)'
    );

    foreach ($defaults as [$name, $slug, $description]) {
        $statement->execute([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
        ]);
    }
}
