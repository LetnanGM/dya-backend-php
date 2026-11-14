<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class SafeRedirect extends TrustRedirect
{
    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function redirect(string $shortlink): void
    {
        $longlink = $this->get_longlink($shortlink);
        if ($longlink) {
            header("Location: " . $longlink);
            exit();
        } else {
            http_response_code(404);
            echo "Shortlink not found.";
        }
    }

    public function create_shortlink(string $shortlink, string $longlink): void
    {
        $this->insert_shortlink($shortlink, $longlink);
    }

    public function update_shortlink_entry(string $shortlink, string $longlink): void
    {
        $this->update_shortlink($shortlink, $longlink);
    }

    public function delete_shortlink_entry(string $shortlink): void
    {
        $this->delete_shortlink($shortlink);
    }

    public function get_longlink_entry(string $shortlink): ?string
    {
        return $this->get_longlink($shortlink);
    }
}
