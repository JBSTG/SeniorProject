<?php
    // Provides a function to return the site score from the database
    // (Average of all pages from a given domain)

    //include("function_parseDomain.php");

    function getSiteScore($url)
    {
        $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
        mysqli_select_db($link, "analytics") or die("Could not select database\n");

        $domain = parseDomain($_REQUEST["url"]);
        $query = "SELECT AVG(Page_Score) FROM pages WHERE Domain = '$domain'";
        $result = mysqli_query($link, $query);
        $numrows = mysqli_num_rows($result);
        if ($numrows) {
            $row = mysqli_fetch_row($result);
            // Return the average score from the database
            return $row[0];
        } else {
            // Return -1 if no records for that domain exist
            return -1;
        }
    }

?>
