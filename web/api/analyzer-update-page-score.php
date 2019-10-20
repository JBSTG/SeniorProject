<?php
    // Update page score (called by analyzer)
    // --------------------------------------
    // Arguments:       url
    //                  score
    // Returns:         Null

    include("function_parseDomain.php");

    $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
    mysqli_select_db($link, "analytics") or die("Could not select database\n");

    if (isset($_REQUEST["url"]) && isset($_REQUEST["score"])) {
        // URL and score provided

        // Check whether page already exists in the database
        $query = "SELECT URL FROM pages WHERE URL = '" . $_REQUEST["url"] . "'";
        $result = mysqli_query($link, $query);
        $numrows = mysqli_num_rows($result);
        if ($numrows) {
            // Page exists in database - Update record
            $query = "UPDATE pages SET Page_Score = " . $_REQUEST["score"] . " WHERE URL = '" . $_REQUEST["url"] . "'";
            mysqli_query($link, $query);
            print "Existing record updated!";

        } else {
            // Page does not exist in database - Create new record
            $domain = parseDomain($_REQUEST["url"]);
            $query = "INSERT INTO pages (URL, Domain, Date_Added, Page_Score) VALUES ('" . $_REQUEST["url"] . "','$domain','" . date("Y-m-d H:i:s") . "'," . $_REQUEST["score"] . ")";
            mysqli_query($link, $query);
            print "New record created and updated!";
        }
    } else {
        print "Error:  Both URL and score parameters are required and not provided";
    }
?>
