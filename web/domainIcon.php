<?php
    include("api/api_lib.php");
 
    //DB details
    $dbHost     = 'localhost';
    $dbUsername = 'datadogs';
    $dbPassword = 'DataDogs2020CSUB';
    $dbName     = 'analytics';

    // Prevent browsers from caching (for testing purposes)
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
        
    //Create connection and select DB
    $db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
    
    //Check connection
    if ($db->connect_error) {
       die("Connection failed: " . $db->connect_error);
    }

    // Parse domain name from URL
    $domain = parseDomain($_REQUEST["url"]);    

    //Get image data from database
    $result = $db->query("SELECT Icon FROM domain_pagescore WHERE Domain = '$domain'");
    if ($result->num_rows > 0) {
        $imgData = $result->fetch_assoc();
        
        //Render image
        header("Content-type: image/x-icon");
        
        $icon = $imgData['Icon'];
        if (strlen($icon) > 0)
          echo $icon; 
        else
          readfile("/var/www/html/Images/defaultDomainIcon.ico");

    } else {
        // Put an alternate icon here
        header("Content-type: image/x-icon");
        readfile("/var/www/html/Images/defaultDomainIcon.ico");
    }    
?>
