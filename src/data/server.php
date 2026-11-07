<?php

function getSelfInfo(): array
{
    static $data = null;

    if ($data === null) {
        $scheme_site = getenv('SCHEME_SERVER') ?: 'https';
        $domain_site = getenv('URI_SERVER') ?: 'www.dya.web.id';
        $full_uri_site = $scheme_site . '://' . $domain_site;

        $data = [
            'scheme' => $scheme_site,
            'domain' => $domain_site,
            'uri' => $full_uri_site,
        ];
    }

    return (array) $data;

}

?>