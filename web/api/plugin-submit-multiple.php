<?php
    // Arguments:       list (JSON list of URLs) 
    // Returns:         NULL 

    include("function_parseDomain.php");
    include("function_getPageScore.php");
    include("function_getSiteScore.php");

    $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
    mysqli_select_db($link, "analytics") or die("Could not select database\n");

    if (isset($_REQUEST["list"])) {
        // URL list provided

        // Parse JSON
        $list = json_decode($_REQUEST["list"]);

        // JSON open tag
        print "{";


        // For each URL in the list 
        for ($i = 0; $i < sizeof($list); $i++) {
            $url = $list[$i];

            // Print URL
            print chr(34) . $url . chr(34) . ": {";

            // Fetch the page score
            $pagescore = getPageScore($url);
            print chr(34) . "page_score" . chr(34) . ":$pagescore, ";

            // Fetch the site score
            $sitescore = getSiteScore($url);
            print chr(34) . "site_score" . chr(34) . ":$sitescore, ";

            // Author score not implemented for now
            print chr(34) . "author_score" . chr(34) . ":-1 ";

            if ($i + 1 < sizeof($list))
                print "},";
            else
                print "}";

        }

        // JSON close tag
        print "}";

    } else {
        print "Error:  URL list required and not provided";
    }

?>

