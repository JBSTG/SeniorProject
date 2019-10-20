<?php

    // Example crawler code:
    //   1) Pull a list of all URLs
    //   2) For each URL on list:
    //      - Retrieve HTML code using file_get_contents()
    //      - Save the HTML in the 'Content' column in the database
    //      - Update the 'Last_Crawled column' in the database

    $link = mysqli_connect("localhost", "root", "DataDogs2020CSUB") or die("Could not connect to database\n");
    mysqli_select_db($link, "analytics") or die("Could not select database\n");

    // Pull a list of all URLs in the database
    $query = "SELECT URL FROM pages";
    $result = mysqli_query($link, $query);
    $numrows = mysqli_num_rows($result);

    print "$numrows pages in database\n\n";

    // Iterate through each page in the database
    for ($i = 0; $i < $numrows; $i++) {
        $row = mysqli_fetch_row($result);
        print "Retrieving $row[0]..\n";
        // Retrieve page HTML code
        $content = file_get_contents($row[0]);
        // Check whether we received content
        if (strlen($content) > 0) {
            // Content was received
            print "    " . strlen($content) . " bytes retrieved..\n";
            // Replace single quotes with double quotes to avoid messing up SQL UPDATE query
            $content = str_replace("'", '"', $content);
            // Insert the page content into the database
            $query = "UPDATE pages SET Content = '$content' WHERE URL = '$row[0]'";
            mysqli_query($link, $query);
            print "    Content saved to database\n";
            // Updated 'Last_Crawled' column in database
            $query = "UPDATE pages SET Last_Crawled = '" . date("Y-m-d H:i:s") . "' WHERE URL = '$row[0]'";
            mysqli_query($link, $query);
            print "    Last_Crawled date/time updated\n\n";
        } else {
            // Content was not received
            print "    Content was not received\n\n";
        }
    }
?>

