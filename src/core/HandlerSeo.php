<?php

declare(strict_types=1);

final class SEOBuilder
{
    private ?string $title = null;
    private ?string $canonical = null;
    private string $favicon = "/assets/img/favicon.ico";

    private array $metadata = [
        "description" => null,
        "keywords" => null,
        "robots" => "index, follow",
        "author" => null,
        "viewport" => "width=device-width, initial-scale=1.0",
    ];

    private array $og = [
        "type" => "website",
        "url" => null,
        "title" => null,
        "description" => null,
        "image" => null,
        "site_name" => null,
        "locale" => null,
    ];

    private array $twitter = [
        "card" => "summary_large_image",
        "title" => null,
        "description" => null,
        "image" => null,
        "site" => null,
        "creator" => null,
    ];

    private array $jsonld = [];
    private array $templates = [];

    private const OG_KEYS = [
        "type",
        "url",
        "title",
        "description",
        "image",
        "site_name",
        "locale"
    ];

    private const TWITTER_KEYS = [
        "card",
        "title",
        "description",
        "image",
        "site",
        "creator"
    ];

    /*
    |--------------------------------------------------------------------------
    | CORE
    |--------------------------------------------------------------------------
    */

    public function set_title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function set_description(string $description): self
    {
        $this->metadata["description"] = $description;
        return $this;
    }

    public function set_keywords(array|string $keywords): self
    {
        $this->metadata["keywords"] = is_array($keywords)
            ? implode(", ", $keywords)
            : $keywords;

        return $this;
    }

    public function set_robots(string $robots): self
    {
        $this->metadata["robots"] = $robots;
        return $this;
    }

    public function set_author(string $author): self
    {
        $this->metadata["author"] = $author;
        return $this;
    }

    public function set_favicon(string $url): self
    {
        $this->validate_asset_url($url);
        $this->favicon = $url;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | CANONICAL
    |--------------------------------------------------------------------------
    */

    public function set_canonical(string $url): self
    {
        $this->validate_absolute_url($url);

        $this->canonical = $url;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | OPEN GRAPH
    |--------------------------------------------------------------------------
    */

    public function set_og(array $data): self
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, self::OG_KEYS, true)) {
                throw new InvalidArgumentException(
                    "Unsupported Open Graph property: {$key}"
                );
            }

            if ($key === "url" || $key === "image") {
                if ($value !== null) {
                    $this->validate_absolute_url((string) $value);
                }
            }
        }

        $this->og = array_replace($this->og, $data);

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | TWITTER CARDS
    |--------------------------------------------------------------------------
    */

    public function set_twitter(array $data): self
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, self::TWITTER_KEYS, true)) {
                throw new InvalidArgumentException(
                    "Unsupported Twitter property: {$key}"
                );
            }

            if ($key === "image" && $value !== null) {
                $this->validate_absolute_url((string) $value);
            }
        }

        $this->twitter = array_replace($this->twitter, $data);

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | JSON-LD DIRECT REGISTRATION
    |--------------------------------------------------------------------------
    */

    public function add_jsonld(array $schema): self
    {
        $this->validate_schema($schema);

        $this->jsonld[] = $schema;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | JSON-LD TEMPLATE REGISTRY
    |--------------------------------------------------------------------------
    */

    public function register_jsonld_template(
        string $name,
        array $template
    ): self {
        if (trim($name) === "") {
            throw new InvalidArgumentException(
                "Template name cannot be empty."
            );
        }

        $this->validate_schema($template);

        $this->templates[$name] = $template;

        return $this;
    }

    public function add_jsonld_template(
        string $name,
        array $data
    ): self {
        if (!isset($this->templates[$name])) {
            throw new InvalidArgumentException(
                "Template '{$name}' does not exist."
            );
        }

        $schema = $this->resolve_template(
            $this->templates[$name],
            $data
        );

        $schema = $this->remove_null_values($schema);

        $this->validate_schema($schema);

        $this->jsonld[] = $schema;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | TEMPLATE RESOLVER
    |--------------------------------------------------------------------------
    |
    | Supports:
    | {{name}}
    | {{author.name}}
    | {{contact.telephone}}
    | {{sameAs}}
    |
    | Exact placeholders preserve original data types.
    */

    private function resolve_template(
        array $template,
        array $data
    ): array {
        $resolver = function (mixed $value) use (&$resolver, $data): mixed {
            if (is_array($value)) {
                $result = [];

                foreach ($value as $key => $item) {
                    $result[$key] = $resolver($item);
                }

                return $result;
            }

            if (!is_string($value)) {
                return $value;
            }

            if (
                !preg_match(
                    '/^\{\{([a-zA-Z0-9_.]+)\}\}$/',
                    $value,
                    $matches
                )
            ) {
                return $value;
            }

            $keys = explode(".", $matches[1]);
            $resolved = $data;

            foreach ($keys as $key) {
                if (!is_array($resolved) || !array_key_exists($key, $resolved)) {
                    throw new InvalidArgumentException(
                        "Missing template variable: {$matches[1]}"
                    );
                }

                $resolved = $resolved[$key];
            }

            return $resolved;
        };

        return $resolver($template);
    }

    private function remove_null_values(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value === null) {
                unset($data[$key]);
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->remove_null_values($value);
            }
        }

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    private function validate_schema(array $schema): void
    {
        if (
            !isset($schema["@context"]) ||
            !isset($schema["@type"])
        ) {
            throw new InvalidArgumentException(
                "JSON-LD requires @context and @type."
            );
        }

        if (
            !is_string($schema["@context"]) ||
            !is_string($schema["@type"])
        ) {
            throw new InvalidArgumentException(
                "JSON-LD @context and @type must be strings."
            );
        }
    }

    private function validate_absolute_url(string $url): void
    {
        $parts = parse_url($url);

        if (
            filter_var($url, FILTER_VALIDATE_URL) === false ||
            !is_array($parts) ||
            !isset($parts["scheme"], $parts["host"]) ||
            !in_array(strtolower($parts["scheme"]), ["http", "https"], true) ||
            isset($parts["user"]) ||
            isset($parts["pass"])
        ) {
            throw new InvalidArgumentException(
                "Invalid HTTP/HTTPS URL: {$url}"
            );
        }
    }

    private function validate_asset_url(string $url): void
    {
        if (
            str_starts_with($url, "/") &&
            !str_starts_with($url, "//")
        ) {
            return;
        }

        $this->validate_absolute_url($url);
    }

    /*
    |--------------------------------------------------------------------------
    | HTML HELPERS
    |--------------------------------------------------------------------------
    */

    private function escape(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            "UTF-8"
        );
    }

    private function render_meta(
        string $key,
        mixed $value,
        string $attribute = "name"
    ): string {
        if ($value === null || $value === "") {
            return "";
        }

        if (!is_scalar($value)) {
            throw new InvalidArgumentException(
                "Metadata values must be scalar."
            );
        }

        return sprintf(
            '<meta %s="%s" content="%s">' . PHP_EOL,
            $attribute,
            $this->escape($key),
            $this->escape((string) $value)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RENDERER
    |--------------------------------------------------------------------------
    */

    public function render(): string
    {
        $html = "";

        // Standard metadata
        $html .= '<meta charset="UTF-8">' . PHP_EOL;

        if ($this->title !== null) {
            $html .= '<title>'
                . $this->escape($this->title)
                . "</title>\n";
        }

        foreach ($this->metadata as $key => $value) {
            $html .= $this->render_meta($key, $value);
        }

        // Canonical
        if ($this->canonical !== null) {
            $html .= '<link rel="canonical" href="'
                . $this->escape($this->canonical)
                . '">' . PHP_EOL;
        }

        // Open Graph
        foreach ($this->og as $key => $value) {
            if ($value === null) {
                $value = match ($key) {
                    "url" => $this->canonical,
                    "title" => $this->title,
                    "description" => $this->metadata["description"],
                    default => null,
                };
            }

            $html .= $this->render_meta(
                "og:" . $key,
                $value,
                "property"
            );
        }

        // Twitter
        foreach ($this->twitter as $key => $value) {
            if ($value === null) {
                $value = match ($key) {
                    "title" => $this->title,
                    "description" => $this->metadata["description"],
                    "image" => $this->og["image"],
                    default => null,
                };
            }

            $html .= $this->render_meta(
                "twitter:" . $key,
                $value
            );
        }

        // Favicon
        if ($this->favicon !== "") {
            $html .= '<link rel="icon" href="'
                . $this->escape($this->favicon)
                . '">' . PHP_EOL;
        }

        // JSON-LD
        foreach ($this->jsonld as $schema) {
            $encoded = json_encode(
                $schema,
                JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
                | JSON_THROW_ON_ERROR
            );

            $html .= '<script type="application/ld+json">'
                . $encoded
                . '</script>' . PHP_EOL;
        }

        return $html;
    }
}
