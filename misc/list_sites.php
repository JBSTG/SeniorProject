<?php

    $link = mysqli_connect("localhost", "root", "DataDogs2020CSUB") or die("Could not connect to database\n");
    mysqli_select_db($link, "analytics") or die("Could not select database\n");


    // Pull a list of all URLs in database
    $query = "SELECT URL, Date_Added, Last_Crawled FROM pages";
    $result = mysqli_query($link, $query);
    $numrows = mysqli_num_rows($result);

    print "\n$numrows pages in database\n";

    // Pull a list of all sites in the database
    $query = "SELECT Domain FROM pages GROUP BY Domain ORDER BY Domain";
    $result = mysqli_query($link, $query);
    $numrows = mysqli_num_rows($result);

    print "$numrows sites in database\n\n";

    print "Site Domain\n";
    print "-------------------------------------\n";

    // Print the list
    for ($i = 0; $i < $numrows; $i++) {
        $row = mysqli_fetch_row($result);
        print "$row[0]\n";
    }
?>

