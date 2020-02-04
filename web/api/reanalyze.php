<?php
    // Re-run both analyzer scripts for all pages and domains in the database
    // This script is called by cron daily at 2:00 am
    // Updated 1/3/20 KT

    //include("function_parseDomain.php");
    include("api_lib.php");

    // Log to file
    $log = fopen("/root/logs/reanalyze.log", "a");
    fwrite($log, "-----------------------------------------\n");
    fwrite($log, "Running reanalyze.php at " . date("Y-m-d H:i:s", strtotime("-8 hours")) . "\n");

    $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
    mysqli_select_db($link, "analytics") or die("Could not select database\n");

    // ------------------------------------------------
    // Re-run analyzer.py for each page URL in database
    // ------------------------------------------------

    // Target pages that are more than 30 days old
    $targetDate = date("Y-m-d", strtotime("-30 days"));
    $query = "SELECT URL,Page_Score FROM pages WHERE Last_Analyzed < '$targetDate'";
    print "Want to run:  $query";
    $result = mysqli_query($link, $query);
    $numrows = mysqli_num_rows($result);
    print "Re-running analyzer.py for $numrows URLs..\n";
    fwrite($log, "    Running analyzer.py for $numrows URLs..\n");

    for ($i = 0; $i < $numrows; $i++) {
        // Fetch row
        $row = mysqli_fetch_row($result);
        $url = $row[0];
        $oldscore = $row[1];
        print "\nURL:  $url\n";
        // Call analyzer
        $cmd = "python3 /var/www/html/api/analyzer.py " . $url;
        //print "    Running $cmd..\n";
        unset($output);
        exec($cmd, $output);
        //$pagescore = trim(explode(":", $output[0])[1]);       // Old analyzer.py output format
        $pagescore = $output[0];
        $pagetitle = $output[1];
        // Remove single quotes from page title
        $pagetitle = str_replace("'", "", $pagetitle);

        print "    Page title:  $pagetitle\n";
        fwrite($log, "        Page title:  $pagetitle\n");
        print "        Old score:  $oldscore, new score: $pagescore\n";
        fwrite($log, "            Old score:  $oldscore, new score: $pagescore\n");

        // Update database with new score
        $domain = parseDomain($url);
        print "        Domain:  $domain\n";
        fwrite($log, "            Domain:  $domain\n");
        $currentdate = date("Y-m-d H:i:s");
        $query = "REPLACE INTO pages (URL, Domain, Page_Score, Last_Analyzed, Title) VALUES ('$url', '$domain', $pagescore, '$currentdate','$pagetitle')";
        mysqli_query($link, $query);

        // Sleep 5 seconds to avoid hitting a single site too hard
        sleep(5);
    }

    // ------------------------------------------------
    // Re-run pagescore.py for each domain in database
    // ------------------------------------------------

    $query = "SELECT Domain FROM pages GROUP BY Domain ORDER BY Domain ASC";
    $result = mysqli_query($link, $query);
    $numrows = mysqli_num_rows($result);
    print "\nRunning pagescore.py for $numrows domains..\n";
    fwrite($log, "    Running pagescore.py for $numrows domains..\n");

    for ($i = 0; $i < $numrows; $i++) {
        // Fetch row
        $row = mysqli_fetch_row($result);
        $domain = $row[0];
        print "    Checking $domain..  ";
        fwrite($log, "        Checking $domain..  ");
        $cmd = "python3 /var/www/html/api/pagescore.py -d https://" . $domain;
        unset($output);
        //print "    want to run:  $cmd\n";
        // Temporary fix - skip bonvoyaged.com
        if ($domain != "bonvoyaged.com")
            exec($cmd, $output);
        $domainscore = trim(explode(":", $output[0])[1]);
        print "score = $domainscore\n";
        fwrite($log, "score = $domainscore\n");
        // Add updated score to database
        $currentdate = date("Y-m-d H:i:s");
        $query = "REPLACE INTO domain_pagescore (Domain, Score, Last_Analyzed) VALUES ('$domain', $domainscore, '$currentdate')";
        mysqli_query($link, $query);
    }

    // Close log file
    fclose($log);
?>
