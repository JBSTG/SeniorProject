<?php
    // Provides a function to check the database, call analyzer.py if needed,
    // then return the page score

    //include("function_parseDomain.php");

    function getPageScore($url)
    {

        $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
        mysqli_select_db($link, "analytics") or die("Could not select database\n");

        // Fetch the page score
        $query = "SELECT Page_Score FROM pages WHERE URL = '$url' AND NOT Page_Score IS NULL";
        $result = mysqli_query($link, $query);
        $numrows = mysqli_num_rows($result);
        if ($numrows) {
            // Page score exists in database
            $row = mysqli_fetch_row($result);

            // Return the page score from the database
            return $row[0];
        } else {
            // Page score does not exist in database--call analyzer
            $cmd = "python3 /var/www/html/api/analyzer.py $url";
            //print "Running:  $cmd<p>";
            exec($cmd, $output);
            $pagescore = $output[0];
            $pagetitle = $output[1];

            // Update database with new score
            $domain = parseDomain($url);
            $currentdate = date("Y-m-d H:i:s");
            $query = "REPLACE INTO pages (URL, Domain, Page_Score, Last_Analyzed, Title) VALUES ('$url', '$domain', $pagescore, '$currentdate', '$pagetitle')";
            mysqli_query($link, $query);

            // Return the page score from analyzer.py
            return $pagescore;
        }
    }

?>
