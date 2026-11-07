<?php
require_once __DIR__ . "/../core/HandlerSeo.php";
function register_all_template(SEOBuilder $seo)
{
    $seo->register_jsonld_template("organization", [
        "@context" => "https://schema.org",
        "@type" => "Organization",
        "name" => "{{name}}",
        "url" => "{{url}}",
        "logo" => "{{logo}}",
        "sameAs" => "{{sameAs}}",
        "contactPoint" => [
            "@type" => "ContactPoint",
            "telephone" => "{{telephone}}",
            "contactType" => "customer service"
        ]
    ]);
}

function getSeo(): SEOBuilder
{
    $seo_in = new SEOBuilder();
    register_all_template($seo_in);
    return $seo_in;
}
?>