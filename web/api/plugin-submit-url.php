<?php
    // Arguments:       url (page URL)
    // Returns JSON:    page_score
    //                  site_score
    //                  author_score

    if (!isset($_SERVER["HTTP_HOST"])) {
        parse_str($argv[1], $_REQUEST);
    }



    include_once("api_lib.php");
    processURL($_REQUEST["url"]);
?>

