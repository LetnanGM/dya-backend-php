<?php

declare(strict_types=1);

/**
 * SEO handler: satu tempat untuk title, description, canonical, Open Graph,
 * Twitter Card, robots, dan JSON-LD.
 *
 * Alur:
 *   1. bootstrap.php membuat instance dari data/seo.php lalu memasang preset halaman.
 *   2. Halaman bisa menimpa nilainya (set / canonical / noindex / schema) sebelum layout dirender.
 *   3. views/partials/seo.php mencetak hasilnya ke <head>. Semua output di-escape di sana.
 *
 * Class ini hanya menyimpan & menghitung nilai, tidak mencetak HTML.
 */
final class SeoHandler
{
    private const META_KEYS = ['title', 'description', 'keywords', 'image', 'type', 'robots'];

    /** @var array<string, string> */
    private array $meta;

    /** @var array<int, array<string, mixed>> */
    private array $schemas = [];

    private string $baseUrl;
    private string $requestPath;
    private ?string $canonicalPath = null;

    /**
     * @param array  $config      isi src/data/seo.php
     * @param string $baseUrl     URL publik situs tanpa slash akhir (config 'site_url')
     * @param string $publicDir   path folder public/ (untuk memeriksa file gambar)
     * @param string $requestUri  $_SERVER['REQUEST_URI']
     */
    public function __construct(
        private array $config,
        string $baseUrl,
        private string $publicDir,
        string $requestUri = '/'
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->meta = array_map('strval', $config['defaults'] ?? []);
        $this->requestPath = self::cleanPath($requestUri);

        if (!empty($config['organization'])) {
            $this->schemas[] = $this->organizationSchema($config['organization']);
        } elseif (!empty($config["person"])) {
            $this->schemas[] = $this->personSchema($config["person"]);
        }
    }

    /* ---------- Menyusun nilai ---------- */

    /** Terapkan preset dari data/seo.php ('pages'). Kunci yang tidak dikenal diabaikan. */
    public function page(string $key): self
    {
        return $this->set($this->config['pages'][$key] ?? []);
    }

    /** Timpa meta. Nilai null diabaikan, jadi default tetap dipakai. */
    public function set(array $meta): self
    {
        foreach (self::META_KEYS as $key) {
            if (isset($meta[$key])) {
                $this->meta[$key] = trim((string) $meta[$key]);
            }
        }

        return $this;
    }

    /** Path canonical relatif terhadap situs, mis. 'member/arya'. Tanpa query string. */
    public function canonical(string $path): self
    {
        $this->canonicalPath = '/' . ltrim($path, '/');

        return $this;
    }

    /** Halaman tidak boleh masuk indeks (mis. hasil pencarian, 404). Link tetap boleh diikuti. */
    public function noindex(): self
    {
        $this->meta['robots'] = 'noindex, follow';

        return $this;
    }

    /** Tambah satu blok JSON-LD. */
    public function schema(array $data): self
    {
        $this->schemas[] = $data;

        return $this;
    }

    /* ---------- Membaca nilai ---------- */

    public function title(): string
    {
        return $this->meta['title'] ?? '';
    }

    public function description(): string
    {
        return $this->meta['description'] ?? '';
    }

    public function keywords(): string
    {
        return $this->meta['keywords'] ?? '';
    }

    public function robots(): string
    {
        return $this->meta['robots'] ?? 'index, follow';
    }

    public function type(): string
    {
        return $this->meta['type'] ?? 'website';
    }

    public function siteName(): string
    {
        return (string) ($this->config['site_name'] ?? '');
    }

    /** URL absolut situs untuk $path ('' = beranda). */
    public function absoluteUrl(string $path = ''): string
    {
        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    /** Canonical selalu memakai URL publik tetap dan tanpa query string. */
    public function canonicalUrl(): string
    {
        return $this->absoluteUrl($this->canonicalPath ?? $this->requestPath);
    }

    /** URL absolut gambar share, atau null jika tidak ada gambar yang valid. */
    public function imageUrl(): ?string
    {
        return $this->resolveImage($this->meta['image'] ?? '');
    }

    /** @return array<int, array<string, mixed>> */
    public function schemas(): array
    {
        return $this->schemas;
    }

    /** JSON untuk <script type="application/ld+json"> yang aman ditanam di HTML. */
    public function schemaJson(array $schema): string
    {
        return (string) json_encode(
            $schema,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
    }

    /* ---------- Internal ---------- */

    private function organizationSchema(array $org): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $org['name'] ?? $this->siteName(),
            'url' => $this->absoluteUrl(),
            'logo' => $this->resolveImage($org['logo'] ?? ''),
            'sameAs' => $org['same_as'] ?? [],
        ];

        if (!empty($org['phone'])) {
            $schema['contactPoint'] = [
                '@type' => 'ContactPoint',
                'telephone' => $org['phone'],
                'contactType' => 'customer service',
            ];
        }

        return array_filter($schema, static fn($v): bool => $v !== null && $v !== []);
    }

    private function personSchema(array $person): array
    {
        $schmea = [
            "@context" => "https://schema.org",
            "@type" => "Person",
            "name" => $person["name"] ?? "",
            "jobTitle" => $person["jobTitle"] ?? "",
            "url" => $person["uri"] ?? $this->absoluteUrl(),
            "image" => $this->resolveImage($person["image_url"] ?? ""),
            "worksFor" => [
                "@type" => "Organization",
                "name" => "DyA Teams",
                "url" => $this->absoluteUrl(),
            ],
            "sameAs" => (array) $person["socmed_links"],
            "knowsAbout" => (array) $person["knows"]
        ];

        return array_filter($schmea, static fn($v): bool => $v !== null && $v !== []);
    }

    /** URL penuh dipakai apa adanya; path relatif dianggap file di public/assets/ dan harus ada. */
    private function resolveImage(string $image): ?string
    {
        if ($image === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $image)) {
            return $image;
        }

        $relative = ltrim($image, '/');

        return is_file($this->publicDir . '/assets/' . $relative)
            ? $this->baseUrl . '/assets/' . $relative
            : null;
    }

    /** Ambil path dari REQUEST_URI: buang query string, normalkan /index.php, tolak karakter aneh. */
    private static function cleanPath(string $requestUri): string
    {
        $path = parse_url($requestUri, PHP_URL_PATH);

        if (!is_string($path) || !preg_match('#^/[\w./%~+-]*$#', $path)) {
            return '/';
        }

        return preg_replace('~/index\.php$~', '/', $path) ?? '/';
    }
}
