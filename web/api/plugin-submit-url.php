<?php
    // Arguments:       url (page URL)
    // Returns:         NULL 

    include("function_parseDomain.php");

    if (isset($_REQUEST["url"])) {
        // URL provided, add to database if not exist

        $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
        mysqli_select_db($link, "analytics") or die("Could not select database\n");

        // Check if URL exists
        $query = "SELECT URL FROM pages WHERE URL = '" . $_REQUEST["url"] . "'";
        $result = mysqli_query($link, $query);
        $numrows = mysqli_num_rows($result);
        if ($numrows) {
            // Page is already in database
            print "Page already exists in database!";
        } else {
            // Page does not exist
            // Determine domain name
            $domain = parseDomain($_REQUEST["url"]);
            $query = "INSERT INTO pages (URL, Domain, Date_Added) VALUES ('" . $_REQUEST["url"] . "','$domain','" . date("Y-m-d H:i:s") . "')";
            mysqli_query($link, $query);
            print "Inserted the following:<p>";
            print "Page URL:  " . $_REQUEST["url"] . "<br>";
            print "Domain:  $domain<br>";
            print "Date_Added:  " . date("Y-m-d H:i:s");
        }
    } else {
        print "Error:  URL required and not provided";
    }

?>

