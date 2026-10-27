<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Sanitasi HTML berbasis daftar putih (whitelist) untuk konten editor Trix.
 *
 * Berbeda dengan pendekatan regex sebelumnya, HTML diurai menjadi DOM sehingga entity
 * (mis. "&#106;avascript:") sudah ter-decode sebelum atribut diperiksa.
 */
class HtmlSanitizer
{
    /** Tag yang diizinkan beserta atributnya. */
    private const TAG_DIIZINKAN = [
        'p' => [], 'br' => [], 'div' => [], 'span' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [], 'del' => [],
        'ul' => [], 'ol' => [], 'li' => [], 'blockquote' => [], 'pre' => [],
        'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [],
        'figure' => [], 'figcaption' => [],
        'a' => ['href', 'title'],
    ];

    /** Tag yang dibuang beserta seluruh isinya. */
    private const TAG_DIBUANG_TOTAL = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template', 'noscript', 'form'];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $internal = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );
        libxml_clear_errors();
        libxml_use_internal_errors($internal);

        $root = $dom->getElementById('__root');
        if (!$root) {
            return '';
        }

        self::bersihkanAnak($root);

        $hasil = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $hasil .= $dom->saveHTML($child);
        }

        return trim($hasil);
    }

    private static function bersihkanAnak(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);
                continue;
            }

            if (!$child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::TAG_DIBUANG_TOTAL, true)) {
                $node->removeChild($child);
                continue;
            }

            self::bersihkanAnak($child);

            if (!array_key_exists($tag, self::TAG_DIIZINKAN)) {
                // Tag tidak dikenal: pertahankan teks/anaknya, buang pembungkusnya
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attr) {
                $nama = strtolower($attr->name);
                if (!in_array($nama, self::TAG_DIIZINKAN[$tag], true)) {
                    $child->removeAttribute($attr->name);
                    continue;
                }
                if ($nama === 'href' && !self::urlAman($attr->value)) {
                    $child->setAttribute('href', '#');
                }
            }

            if ($tag === 'a' && $child->hasAttribute('href')) {
                $child->setAttribute('rel', 'noopener noreferrer nofollow');
            }
        }
    }

    private static function urlAman(string $url): bool
    {
        // Hapus karakter kontrol & spasi yang dapat dipakai menyamarkan skema
        $bersih = strtolower(preg_replace('/[\x00-\x20\x7F]+/u', '', html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        if ($bersih === '' || str_starts_with($bersih, '#') || str_starts_with($bersih, '/') || str_starts_with($bersih, '?')) {
            return true;
        }

        if (preg_match('/^([a-z][a-z0-9+.\-]*):/', $bersih, $m)) {
            return in_array($m[1], ['http', 'https', 'mailto', 'tel'], true);
        }

        return true; // URL relatif tanpa skema
    }
}
