<?php
    // Arguments:       url (page URL)
    // Returns JSON:    page_score
    //                  site_score
    //                  author_score

    include("function_parseDomain.php");

    if (isset($_REQUEST["url"])) {
        // URL provided, pull scores from database
        //print "Fetching scores for URL:  " . $_REQUEST["url"];

        $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
        mysqli_select_db($link, "analytics") or die("Could not select database\n");

        // Fetch the page score
        $query = "SELECT Page_Score FROM pages WHERE URL = '" . $_REQUEST["url"] . "' AND NOT Page_Score IS NULL";
        $result = mysqli_query($link, $query);
        $numrows = mysqli_num_rows($result);
        if ($numrows) {
            // Page score exists in database
            $row = mysqli_fetch_row($result);
            $pagescore = $row[0];
        } else {
            // Page score does not exist in database--call analyzer
            // Not working yet
            $cmd = "python /var/www/html/api/analyzer.py " . $_REQUEST["url"];
            print $cmd;
            $output = shell_exec($cmd);
            print sizeof($output);
            $pagescore = -1;
        }

        // Fetch the domain (site) score
        $domain = parseDomain($_REQUEST["url"]);
        $query = "SELECT AVG(Page_Score) FROM pages WHERE Domain = '$domain'";
        $result = mysqli_query($link, $query);
        $numrows = mysqli_num_rows($result);
        if ($numrows) {
            $row = mysqli_fetch_row($result);
            $sitescore = $row[0];
        } else {
            $sitescore = -1;
        }

        // Fetch the author score
        $authorscore = -1;

        // Print the results (JSON)

        $arr = array("page_score" => (double)$pagescore, "site_score" => (double)$sitescore, "author_score" => (double)$authorscore);
        print json_encode($arr);

        // =========================================================
        // Insert page into database and/or update score in database
        // =========================================================
        // (to be added)

    } else {
        print "Error:  URL required and not provided";
    }
?>

