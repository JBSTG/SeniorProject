<?php
    // Arguments:       list (JSON list of URLs) 
    // Returns:         NULL 

    include("function_parseDomain.php");

    if (isset($_REQUEST["list"])) {
        // URL list provided

        // Parse JSON
        $list = json_decode($_REQUEST["list"]);

        // Print the list
        print "You passed in:<p>";
        for ($i = 0; $i < sizeof($list); $i++)
            print $list[$i] . "<br>";

    } else {
        print "Error:  URL list required and not provided";
    }

?>

