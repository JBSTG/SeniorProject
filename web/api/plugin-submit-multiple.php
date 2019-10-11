<?php
    // Arguments:       list (JSON list of URLs) 
    // Returns:         NULL 

    include("/var/www/html/api/function_parseDomain.php");

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
            print chr(34) . $url . chr(34) . ": [";

            // Check database for the page score
            $query = "SELECT Page_Score FROM pages WHERE URL = '$url'";
            $result = mysqli_query($link, $query);
            $numrows = mysqli_num_rows($result);
            if ($numrows) {
                // Page found in database
                $row = mysqli_fetch_row($result);
                $pagescore = $row[0];
            } else {
                // Page not found in database
                $pagescore = -1;
            }
            print chr(34) . "page_score" . chr(34) . ":$pagescore, ";

            // Check database for the site score
            $domain = parseDomain($url);
            $query = "SELECT AVG(Page_Score) FROM pages WHERE Domain = '$domain'";
            $result = mysqli_query($link, $query);
            $numrows = mysqli_num_rows($result);
            if ($numrows) {
                // Site score found in database
                $row = mysqli_fetch_row($result);
                $sitescore = $row[0];
            } else {
                // Site score not found in database
                $sitescore = -1;
            }
            print chr(34) . "site_score" . chr(34) . ":$sitescore, ";

            // Author score not implemented for now
            print chr(34) . "author_score" . chr(34) . ":-1 ";

            if ($i + 1 < sizeof($list))
                print "],";
            else
                print "]";

            // =========================================================
            // Insert page into database and/or update score in database
            // =========================================================
            // (to be added)
        }

        // JSON close tag
        print "}";

    } else {
        print "Error:  URL list required and not provided";
    }

?>

