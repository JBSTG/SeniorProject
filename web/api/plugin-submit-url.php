<?php
    // Arguments:       url (page URL)
    // Returns JSON:    page_score
    //                  site_score
    //                  author_score

    include("function_parseDomain.php");
    include("function_getPageScore.php");
    include("function_getSiteScore.php");

    if (isset($_REQUEST["url"])) {
        // URL provided, pull scores from database

        // Fetch the page score
        $pagescore = getPageScore($_REQUEST["url"]);

        // Fetch the domain (site) score
        $sitescore = getSiteScore($_REQUEST["url"]);

        // Fetch the author score
        $authorscore = -1;

        // Print the results (JSON)

        $arr = array("page_score" => (double)$pagescore, "site_score" => (double)$sitescore, "author_score" => (double)$authorscore);
        print json_encode($arr);

    } else {
        print "Error:  URL required and not provided";
    }
    echo "Good.";
?>

