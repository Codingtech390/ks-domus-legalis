<?php

// declare(strict_types=1);

/**
 * Sanitize rich-text HTML before public rendering.
 *
 * Requires PHP's DOM extension, normally enabled in XAMPP.
 * Only approved formatting tags and attributes are retained.
 */
function sanitize_article_html(string $html): string
{
    if (!class_exists(DOMDocument::class)) {
        error_log('The PHP DOM extension is required to sanitize article HTML.');
        return '<p>Article content is temporarily unavailable.</p>';
    }

    $allowedTags = [
        'p',
        'br',
        'strong',
        'b',
        'em',
        'i',
        'u',
        's',
        'strike',
        'h1',
        'h2',
        'h3',
        'h4',
        'blockquote',
        'ul',
        'ol',
        'li',
        'a',
        'img',
        'figure',
        'figcaption',
        'hr',
        'pre',
        'code',
        'sub',
        'sup',
        'span',
        'div',
    ];

    $allowedClasses = [
        'ql-align-center',
        'ql-align-right',
        'ql-align-justify',
        'ql-indent-1',
        'ql-indent-2',
        'ql-indent-3',
        'ql-size-small',
        'ql-size-large',
        'ql-size-huge',
    ];

    $previous = libxml_use_internal_errors(true);
    $document = new DOMDocument('1.0', 'UTF-8');

    $wrapped = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'
        . '<div id="article-root">' . $html . '</div></body></html>';

    $document->loadHTML(
        '<?xml encoding="UTF-8">' . $wrapped,
        LIBXML_HTML_NODEFDTD | LIBXML_NONET
    );

    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $root = $document->getElementById('article-root');

    if (!$root instanceof DOMElement) {
        return '';
    }

    $cleanNode = static function (DOMNode $node) use (
        &$cleanNode,
        $allowedTags,
        $allowedClasses
    ): void {
        if ($node instanceof DOMComment) {
            $node->parentNode?->removeChild($node);
            return;
        }

        if ($node instanceof DOMText) {
            return;
        }

        if (!$node instanceof DOMElement) {
            foreach (iterator_to_array($node->childNodes) as $child) {
                $cleanNode($child);
            }
            return;
        }

        $tag = strtolower($node->tagName);

        if (!in_array($tag, $allowedTags, true)) {
            /*
             * Drop dangerous elements and their contents entirely.
             * For unsupported formatting elements, retain text/children.
             */
            $dropContents = [
                'script',
                'style',
                'iframe',
                'object',
                'embed',
                'form',
                'input',
                'button',
                'textarea',
                'select',
                'svg',
                'math',
                'template',
                'link',
                'meta',
            ];

            $parent = $node->parentNode;

            if ($parent !== null && in_array($tag, $dropContents, true)) {
                $parent->removeChild($node);
                return;
            }

            if ($parent !== null) {
                while ($node->firstChild !== null) {
                    $parent->insertBefore($node->firstChild, $node);
                }

                $parent->removeChild($node);
            }

            return;
        }

        $attributesToKeep = [];

        if ($tag === 'a') {
            $href = trim($node->getAttribute('href'));

            if (
                $href !== ''
                && preg_match('/^(https?:\/\/|mailto:|\/|#)/i', $href)
                && !str_starts_with($href, '//')
            ) {
                $attributesToKeep['href'] = $href;
            }
        }

        if ($tag === 'img') {
            $src = trim($node->getAttribute('src'));

            /*
             * Article images may be hosted locally or over HTTPS.
             * Data URIs and javascript: URLs are never allowed.
             */
            if (
                $src !== ''
                && (
                    str_starts_with($src, '/')
                    || str_starts_with($src, 'https://')
                )
                && !str_starts_with($src, '//')
            ) {
                $attributesToKeep['src'] = $src;
            }

            $alt = $node->getAttribute('alt');

            if ($alt !== '') {
                $attributesToKeep['alt'] = $alt;
            }

            $attributesToKeep['loading'] = 'lazy';
        }

        if ($node->hasAttribute('class')) {
            $classes = preg_split(
                '/\s+/',
                trim($node->getAttribute('class'))
            ) ?: [];

            $classes = array_values(array_filter(
                $classes,
                static fn(string $class): bool =>
                in_array($class, $allowedClasses, true)
            ));

            if ($classes !== []) {
                $attributesToKeep['class'] = implode(' ', $classes);
            }
        }

        /*
         * Remove all attributes, then restore the explicit allowlist.
         * This also strips onerror, onclick, style and arbitrary data-*.
         */
        while ($node->attributes->length > 0) {
            $attribute = $node->attributes->item(0);

            if ($attribute === null) {
                break;
            }

            $node->removeAttributeNode($attribute);
        }

        foreach ($attributesToKeep as $name => $value) {
            $node->setAttribute($name, $value);
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            $cleanNode($child);
        }

        if ($tag === 'a' && $node->hasAttribute('href')) {
            $node->setAttribute('rel', 'noopener noreferrer');
        }
    };

    foreach (iterator_to_array($root->childNodes) as $child) {
        $cleanNode($child);
    }

    $output = '';

    foreach ($root->childNodes as $child) {
        $output .= $document->saveHTML($child);
    }

    return $output;
}
