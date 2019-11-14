<?php
    // Re-run the analyzer for all domains in the database
    // Updated 11/14/19 KT

    include("function_parseDomain.php");

    $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
    mysqli_select_db($link, "analytics") or die("Could not select database\n");

    // Pull list of pages from database
    $query = "SELECT URL,Page_Score FROM pages";
    $result = mysqli_query($link, $query);
    $numrows = mysqli_num_rows($result);

    // Call analyzer for each page URL
    //for ($i = 0; $i < $numrows; $i++) {
    for ($i = 0; $i < 10; $i++) {
        // Fetch row
        $row = mysqli_fetch_row($result);
        $url = $row[0];
        $oldscore = $row[1];
        print "URL:  $url\n";
        // Call analyzer
        $cmd = "python3 /var/www/html/api/analyzer.py " . $url;
        //print "    Running $cmd..\n";
        exec($cmd, $output);
        //$pagescore = trim(explode(":", $output[0])[1]);       // Old analyzer.py output format
        $pagescore = $output[0];

        //print "    Score $pagescore\n";
        print "    Old score:  $oldscore, new score: $pagescore\n";

        // Update database with new score
        $domain = parseDomain($url);
        //print "    Domain:  $domain\n";
        $currentdate = date("Y-m-d H:i:s");
        $query = "REPLACE INTO pages (URL, Domain, Page_Score, Last_Analyzed) VALUES ('$url', '$domain', $pagescore, '$currentdate')";
        //mysqli_query($link, $query);

    }
?>
