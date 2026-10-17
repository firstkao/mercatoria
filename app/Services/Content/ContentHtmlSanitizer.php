<?php

namespace App\Services\Content;

use DOMDocument;
use DOMElement;
use DOMNode;
use RuntimeException;

class ContentHtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'a', 'blockquote', 'br', 'caption', 'div', 'em', 'figcaption', 'figure',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'li', 'ol',
        'p', 'span', 'strong', 'table', 'tbody', 'td', 'th', 'thead', 'tr', 'u', 'ul',
    ];

    private const DROP_CONTENT_TAGS = ['iframe', 'object', 'script', 'style', 'svg', 'template'];

    public function sanitize(string $html): string
    {
        if (! class_exists(DOMDocument::class)) {
            throw new RuntimeException('The DOM PHP extension is required to sanitize page content.');
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="content-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            throw new RuntimeException('Unable to parse supplied page content.');
        }

        $root = $document->getElementById('content-root');
        if (! $root) {
            return '';
        }

        $this->sanitizeChildren($root);

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }

    private function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, self::DROP_CONTENT_TAGS, true)) {
                $parent->removeChild($child);

                continue;
            }

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                $this->unwrap($child);

                continue;
            }

            $this->sanitizeAttributes($child, $tag);
            $this->sanitizeChildren($child);
        }
    }

    private function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $allowed = match ($tag) {
                'a' => in_array($name, ['href', 'title', 'target'], true),
                'img' => in_array($name, ['src', 'alt', 'width', 'height'], true),
                default => false,
            };

            if (! $allowed || ($name === 'href' && ! $this->isSafeUrl($attribute->value))
                || ($name === 'src' && ! $this->isSafeUrl($attribute->value, allowMailto: false))) {
                $element->removeAttributeNode($attribute);
            }
        }

        if ($tag === 'a' && $element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (! $parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private function isSafeUrl(string $url, bool $allowMailto = true): bool
    {
        $url = trim($url);

        return preg_match('/^(https?:\/\/|\/(?!\/)|#)/i', $url) === 1
            || ($allowMailto && preg_match('/^(mailto:|tel:)/i', $url) === 1);
    }
}
