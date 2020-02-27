<?php
function parseDomain($url)
{
    // Pull out only the domain portion of the URL
    if(!isset(explode("/",$url)[2])){
        $domain = $url;
    }else{
        $domain = explode("/", $url)[2];
    }

    // Handle a couple of special cases here (i.e. treat news.google.com as itself rather than google.com
    if ($domain == "cs.csub.edu") return $domain;
    if ($domain == "cs.csubak.edu") return $domain;
    if ($domain == "news.google.com") return $domain;
    if ($domain == "www.cs.csubak.edu") return "cs.csubak.edu";
    if ($domain == "www.cs.csub.edu") return "cs.csub.edu";

    // For all other sites, return only the second- and first-level portions of the domain name (i.e. cnn.com)
    $domainparts = explode(".", $domain);
    $size = sizeof($domainparts);

    return @$domainparts[$size-2] . "." . @$domainparts[$size-1];
}

    // Provides a function to return the site score from the database
    // (Average of all pages from a given domain)
    //include("function_parseDomain.php");
function getSiteScore($url)
{   
    $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
    mysqli_select_db($link, "analytics") or die("Could not select database\n");

    $domain = parseDomain($url);
    $query = "SELECT AVG(Page_Score) FROM pages WHERE Domain = '$domain'";
    $result = mysqli_query($link, $query);
    $numrows = mysqli_num_rows($result);
    if ($numrows) {
        $row = mysqli_fetch_row($result);
        // Return the average score from the database
        return round((1-$row[0])*100,2);
    } else {
        // Return -1 if no records for that domain exist
        return -1; 
    }
}   

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
        if($result!=false){
            $numrows = mysqli_num_rows($result);
        }else{
            $numrows = false;
        }
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
            //print "Exec() finished";
            @$pagescore = $output[0];
            @$pagetitle = $output[1];

            // Update database with new score
            $domain = parseDomain($url);
            $currentdate = date("Y-m-d H:i:s");
            $query = "REPLACE INTO pages (URL, Domain, Page_Score, Last_Analyzed, Title) VALUES ('$url', '$domain', $pagescore, '$currentdate', '$pagetitle')";
            mysqli_query($link, $query);

            // Return the page score from analyzer.py
            return $pagescore;
        }
    }

//TODO: The page scoring function needs to return an array, this will save us a sql query
function getPageTitle($url){
    $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
    mysqli_select_db($link, "analytics") or die("Could not select database\n");
  
    $query = "SELECT Title FROM pages WHERE URL = '$url'";
    $res = $link->query($query); 

    if($res!=false){
       $row = $res->fetch_assoc(); 
        return $row["Title"];
    }else{
        return "--ERROR--: TITLE NOT FOUND";
    }
}

function getPageComments($url){
    $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
    mysqli_select_db($link, "analytics") or die("Could not select database\n");
    
    $firstQuery = "SELECT * FROM pages WHERE URL = '$url'";
    $res = $link->query($firstQuery);
    $output = "";
    if($res){
        $row = $res->fetch_assoc();
        $ID = $row["ID"];
        $secondQuery = "SELECT * FROM comments WHERE page_ID = $ID";
        
        $cRes = $link->query($secondQuery);
        if($cRes){
            $output.="[";
            while($innerRow = $cRes->fetch_assoc()){
                $unQuery = "SELECT * FROM accounts where ID = ".$innerRow["user_ID"];
                $unRes = $link->query($unQuery);
                $unrow = $unRes->fetch_assoc();


                //Escape for JSON output.
                $uname = addslashes($unrow["Username"]);
                $comment = addslashes($innerRow["comment"]);
                $date = addslashes($innerRow["time_posted"]);
                

                $output.="{\"username\":\"".$unrow["Username"]."\",\"body\":\"".$innerRow["comment"]."\"},";

            }
            if(strlen($output)>1){
                $output = substr($output, 0, -1);
            }
            $output.="]";
        }else{
            $output.="[]";
        }
    }
    echo $output;
}



function processURL($url){
    if (isset($url)) {
        // URL provided, pull scores from database

        // Fetch the page score
        $pagescore = getPageScore($url);

        // Fetch the domain (site) score
        $sitescore = getSiteScore($url);

        // Fetch the author score
        $authorscore = -1;

        // Fetch the title
        $pagetitle = getPageTitle($url);
        //$pagetitle = preg_replace('/\s+/', '', $pagetitle);

        // Print the results (JSON)

        $arr = array("page_score" => (double)$pagescore, "site_score" => (double)$sitescore, "author_score" => (double)$authorscore, "page_title" => (string)$pagetitle, "url" => (string)$url);
        print json_encode($arr);

    } else {
        print "Error:  URL required and not provided";
    }   
}

function processMultiple($list){

    $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
    mysqli_select_db($link, "analytics") or die("Could not select database\n");

    if (isset($list)) {
        // URL list provided


        // JSON open tag
        print "[";
        // For each URL in the list 
        for ($i = 0; $i < sizeof($list); $i++) {
            $url = $list[$i];
            print "{";
            // Print URL
            print "\"url\":".chr(34) . $url . chr(34).",";

            // Fetch the page score
            $pagescore = getPageScore($url);
            if(!strlen($pagescore)){
                $pagescore = -1;
            }
            print chr(34) . "page_score" . chr(34) . ":$pagescore, ";

            // Fetch the site score
            $sitescore = getSiteScore($url);
            if(!strlen($sitescore)){
                $sitescore = -1;
            }
            print chr(34) . "site_score" . chr(34) . ":$sitescore, ";

            // Author score not implemented for now
            print chr(34) . "author_score" . chr(34) . ":-1, ";
            
            // Fetch the title score
            $pagetitle = getPageTitle($url);
            $pagetitle = str_replace('\"','\\"',$pagetitle);
            $pagetitle = str_replace('\\',' ',$pagetitle);
            //$pagetitle = str_replace(':','\:',$pagetitle);
            //$pagetitle = preg_replace('/\s+/', '', $pagetitle);
            print chr(34) . "page_title" . chr(34) . ":\"$pagetitle\" ";

            if ($i + 1 < sizeof($list))
                print "},";
            else
                print "}";

        }

        // JSON close tag
        print "]";
    } else {
        print "Error:  URL list required and not provided";
    }
}




 
?>
