<?php
    function parseDomain($url)
    {
        // Pull out only the domain portion of the URL
        $domain = explode("/", $url)[2];

        // Handle a couple of special cases here (i.e. treat news.google.com as itself rather than google.com
        if ($domain == "news.google.com") return $domain;

        // For all other sites, return only the second- and first-level portions of the domain name (i.e. cnn.com)
        $domainparts = explode(".", $domain);
        $size = sizeof($domainparts);
        return $domainparts[$size-2] . "." . $domainparts[$size-1];
    }
?>
