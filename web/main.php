<?php
session_start();
if(!isset($_SESSION["username"])){
    header("location: index.php");
}

    $username = $_SESSION["username"];
    $_SESSION["prof_flag"] = false;
    $dbHost     = 'localhost';
    $dbUsername = 'datadogs';
    $dbPassword = 'DataDogs2020CSUB';
    $dbName     = 'analytics';

          //Create connection and select DB
     $db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

           // Check connection
      if($db->connect_error){
      die("Connection failed: " . $db->connect_error);
          }

     $follower_count = $db->query("SELECT * FROM following_list WHERE
                                   User2_ID = '" .$_SESSION['accountid'] . "'");
     $following_count = $db->query("SELECT * FROM following_list WHERE
                                   User1_ID = '" .$_SESSION['accountid'] . "'");

     $num_followers = mysqli_num_rows($follower_count);
     $num_following = mysqli_num_rows($following_count);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no, maximum-scale=1, user-scalable=0">
    <title>Data Dogs Analytics</title>
    <!-- W3Schools core CSS -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://www.w3schools.com/lib/w3-theme-blue.css">
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Open+Sans'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="css/new_main.css">
</head>

<body class="w3-theme-l5">
  <!-- Page Container -->
  <div class="w3-container w3-content" style="max-width:1400px; margin-top:10px">    
    <!-- The Grid -->
      <div class="w3-row">
        <div class="w3-container w3-content" style="max-width:1400px;margin-top:10px">    
          <!-- The Grid -->
            <div class="w3-row">
              <!-- Left Column -->
                <div class="w3-col m3">
                  <!-- Profile -->
                  <div class="w3-card w3-round w3-white">
                    <div class="w3-container">
                      <p class="w3-center">
                      <a href="profile_page.php">
                        <img class="w3-circle" style="height:106px;width:106px" src="view.php?id=<?php echo $_SESSION['username']?>" alt="Avatar" style="height:100px; width:100px">
                      </a>
                      </p>
                      <h6 class="w3-center"><?php echo $_SESSION['username']?></h6>
                      <hr>
                      <a href="" onclick="open_home(); return false;">
                        <p><i class="fa fa-home fa-fw w3-margin-right w3-text-theme"></i>Home</p></a>
                      <a href="" onclick="open_profile(); return false;"><p><i class="fa fa-user-circle-o fa-fw w3-margin-right w3-text-theme"></i>My Profile</p></a>
                      <a href="logoff.php"><p><i class="fa fa-sign-out fa-fw w3-margin-right w3-text-theme"></i>Log Off</p></a>
                      <div class="follow-col" id="follow-col">
                            <button class="start-home-link follow-list" id="following" onclick="load_following_bg()">Following (<?php echo $num_following;?>)</button>
                            <div class="start-home-link v2" id="follow-separator"></div>
                            <button class="start-home-link follow-list" id="follower">Followers (<?php echo $num_followers;?>)</button>
                      </div>
                    </div>
                  </div><br>

                  <!-- Accordion -->
                  <div class="w3-card w3-round">
                    <div class="w3-white">
                      <button class="follow-list w3-button w3-block w3-theme-l1 w3-left-align" id="following" onclick="load_following_bg()">Following <?php echo $num_following;?></button>
                    </div>
                    </div><br>
                  <div class="w3-card w3-round">
                    <div class="w3-white">
                      <button class="follow-list w3-button w3-block w3-theme-l1 w3-left-align" id="follower">Followers <?php echo $num_followers;?></button>
                    </div>
                  </div>
              </div>
              <!-- End of Left Coulmn -->

              <script>
                function open_profile() {
                    var statsView = document.getElementById("statsView");
                    var search = document.getElementById("search");
                    var scrapeButton = document.getElementById("scrapeButton");

                    var following = document.getElementById("following");
                    var follower = document.getElementById("follower");
                    var follow_separator = document.getElementById("follow-separator");
                    var bio = document.getElementById("bio");
                    var occ = document.getElementById("occ");
                    var bdate = document.getElementById("bdate");
                    
                    statsView.className="invis";
                    search.className="invis-search";
                    scrapeButton.className="invis-scrapeButton";
                    setTimeout(load_profile, 0500);

                    function load_profile() {
                        following.className="home-link follow-list";
                        follower.className="home-link follow-list";
                        follow_separator.className="home-link v2";
                        bio.className="prof-info bio";
                        occ.className="prof-info occ";
                        bdate.className="prof-info bio";
                    }
                }
                
                function open_home() {
                    var statsView = document.getElementById("statsView");
                    var search = document.getElementById("search");
                    var scrapeButton = document.getElementById("scrapeButton");

                    var following = document.getElementById("following");
                    var follower = document.getElementById("follower");
                    var follow_separator = document.getElementById("follow-separator");
                    var bio = document.getElementById("bio");
                    var occ = document.getElementById("occ");
                    var bdate = document.getElementById("bdate");

                    following.className="invis-home-link follow-list";
                    follower.className="invis-home-link follow-list";
                    follow_separator.className="invis-home-link v2";
                    bio.className="invis-prof-info bio";
                    occ.className="invis-prof-info occ";
                    bdate.className="invis-prof-info bio";
                    setTimeout(load_home, 0500);

                    function load_home() {
                        statsView.className="load";
                        search.className="load-search";
                        scrapeButton.className="load-scrapeButton";
                    }
                }
              </script>

              <!-- Middle Coumn -->
              <div class="w3-col m7">
                <div class="w3-row-padding">
                  <div class="w3-col m12">
                    <div class="w3-card w3-round w3-white">
                      <div class="w3-container w3-padding">
                        <div class = "col-12 col-md-8 offset-md-3" >
                          <div class="w3-container w3-card w3-white w3-round w3-margin" id="articleView">
                            <h1 id="infoBoxTitle">Title</h1>
                            <p id="infoBoxPageScore"></p>
                            <p id="infoBoxSiteScore">Site:</p>
                            <p>Comments:</p>
                            <div id="infoBoxComments"></div>
                            <button id="fromButton" class="">Return</button>
                          </div>                                                    
                          <div class="bio-info-col">
                            <p class="start-prof-info bio" id="bio"><b>Bio:</b><br>
                            <?php 
                              $load_user_bio = $db->query("SELECT bio FROM profile_info WHERE Username = '$username'");
                              $bio = $load_user_bio->fetch_assoc();
                              echo $bio["bio"];
                            ?></p>
                          </div>

                          <div class="bio-info-col">
                            <p class="start-prof-info bio" id="occ"><b>Occupation:</b><br>
                            <?php
                              $load_user_occup = $db->query("SELECT occup FROM profile_info WHERE Username = '$username'");
                              $occup = $load_user_occup->fetch_assoc();
                              echo $occup["occup"];
                            ?></p>
                          </div>

                          <div class="bio-info-col">
                            <p class="start-prof-info bio" id="bdate"><b>Birth Date:</b><br>
                            <?php
                              $load_user_date = $db->query("SELECT bday FROM profile_info WHERE Username = '$username'");
                              $bday = $load_user_date->fetch_assoc();
                              echo $bday["bday"];
                            ?></p>
                          </div>

                          <div class="" id="statsView"> Total pages indexed:
                            <?php
                              $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
                              mysqli_select_db($link, "analytics") or die("Could not select database\n");
                              $result = mysqli_query($link, "SELECT COUNT(*) FROM pages");
                              $row = mysqli_fetch_row($result);
                              print number_format($row[0]);
                            ?>
                          </div><br>
                            
                          <input class="w3 border w3-padding" type ="text" id="search" placeholder="Search for article"><br><br>
                          <button type="button" id="scrapeButton" class="w3-button w3-theme-l1">Search</button>
                          <img src="Images/blue-loading-gif-transparent.gif" id="loadingGif"><hr>
                          <p id="results"></p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>  
            </div>
          </div>
        </div> 
      </div>
    </div>  
</body>

<script>
    var searchbar = document.getElementById("search");
    var results = document.getElementById("results");
    var socket = new WebSocket("wss://www.datadogsanalytics.com:8080");
    socket.onopen = function(e) {
        console.log("Connected to websocket server.");
    }
    socket.onmessage = function(e) {
        console.log(e.data);
    }
    searchbar.addEventListener("keyup",function() {
        var xhr = new XMLHttpRequest();
        xhr.open("POST","getSearchResults.php",true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            //results.innerHTML = this.responseText;
            console.log(this.responseText);
            var resultArray = JSON.parse(this.responseText);
            if(resultArray.length==0) {
                results.innerHTML="No results found.";
            } else {
                results.innerHTML="";
                range = (resultArray.length>10)? 10:resultArray.length;
                for(var i = 0;i<range;i++){
                    if(resultArray.title!=""){
                        //console.log(resultArray[i]);
                        appendScrapedPage(resultArray[i]);
                    }
                }
            }
        }
        };
        xhr.send("input="+searchbar.value);
    });
    
    /*document.getElementById("scrapeButton").addEventListener("click",function(){
        var xhr = new XMLHttpRequest();
        xhr.open("POST","scraper.php",true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        document.getElementById("loadingGif").style.visibility="visible";
        xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                //TODO:Change to prepend
                document.getElementById("loadingGif").style.visibility="hidden";
                console.log(this.responseText);
                var output = JSON.parse(this.responseText);
                for(var i = 0;i<output.length;i++){
                    appendScrapedPage(output[i]);
                }
            }
        };
        xhr.send("domain="+searchbar.value);
    });*/
    
    document.getElementById("scrapeButton").addEventListener("click",function() {
        var scrapeMessage = new Object();
        scrapeMessage.target = searchbar.value;
        scrapeMessage.isScrapeSubscription = true;
        socket.send(JSON.stringify(scrapeMessage));
    });

    function appendScrapedPage(object) {
        if(object.page_title=="Website Unavailable (Exception Encountered)") { return; }
        var container = document.createElement("div");
        container.setAttribute("id",object.url);
        var title= document.createElement("p");
        var linkToArticle = document.createElement("a");
        var moreInfo = document.createElement("img");
        moreInfo.addEventListener("click",function(e){
            loadArticleInfo(object.url);
            document.getElementById("articleView").style.display="block";
            document.getElementById("searchView").style.display="none";
        });
    
        var domain = document.createElement("p");
        title.innerHTML = object.page_title;
        moreInfo.src = "domainIcon.php?url='" + object.url + "'";
        moreInfo.width = 32;
        moreInfo.height = 32;
        linkToArticle.setAttribute("href",object.url);
        linkToArticle.innerHTML = "&nbsp;&nbsp;Visit Page";
        domain.innerHTML = object.url;
        container.appendChild(title);
        container.appendChild(moreInfo);
        container.appendChild(linkToArticle);
        container.appendChild(domain);
        results.appendChild(container);
    }
    
    function loadArticleInfo(url) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST","getArticleInfoAndComments.php",true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                console.log(this.responseText);
                var data = JSON.parse(this.responseText);
                document.getElementById("infoBoxTitle").innerHTML = data.title;
                document.getElementById("infoBoxPageScore").innerHTML = (data.page_score>0)? "Clickbait":"Not Clickbait";
                document.getElementById("infoBoxSiteScore").innerHTML = "Site Rep: "+data.site_score+"%";
                console.log(data);
                document.getElementById("infoBoxComments").innerHTML="";
                for(var i = 0;i<data.comments.length;i++){
                    document.getElementById("infoBoxComments").innerHTML += data.comments[i].username+": "+data.comments[i].body;
                }
            }
        };
        xhr.send("url="+url);
    }
    
    document.getElementById("fromButton").addEventListener("click",function() {
        document.getElementById("articleView").style.display="none";
        document.getElementById("searchView").style.display="block";
    });
</script>
</html>
