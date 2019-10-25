<?php

    $link = mysqli_connect("localhost", "root", "DataDogs2020CSUB") or die("Could not connect to database\n");
    mysqli_select_db($link, "analytics") or die("Could not select database\n");


    // Pull a list of all URLs in database
    $query = "SELECT URL, Page_Score, Last_Analyzed FROM pages";
    $result = mysqli_query($link, $query);
    $numrows = mysqli_num_rows($result);

    print "$numrows pages in database\n\n";

    print "Score  Last Analyzed          URL\n";
    print "-------------------------------------------------------\n";

    // Print the list
    for ($i = 0; $i < $numrows; $i++) {
        $row = mysqli_fetch_row($result);
        if (strlen($row[2]))
            print "$row[1]    $row[2]    $row[0]\n";
        else
            print "$row[1]                             $row[0]\n";
    }
?>

