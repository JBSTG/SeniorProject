<?php
session_start();
if(!isset($_SESSION["username"])) {
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
    if($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
    $follower_count = $db->query("SELECT * FROM following_list WHERE User2_ID = '" .$_SESSION['accountid'] . "'");
    $following_count = $db->query("SELECT * FROM following_list WHERE User1_ID = '" .$_SESSION['accountid'] . "'");
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
                <div class="w3-col m4 l3">
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
                      <a href="feed.php" onclick="open_feed(); return false;">
                        <p><i class="fa fa-home fa-fw w3-margin-right w3-text-theme"></i>Home</p></a>
                      <a href="" onclick="open_search(); return false;">
                        <p><i class="fa fa-search fa-fw w3-margin-right w3-text-theme"></i>Search</p></a>
                      <a href="" onclick="open_profile('+<?php echo $_SESSION['username']?>+'); return false;">
                        <p><i class="fa fa-user-circle-o fa-fw w3-margin-right w3-text-theme"></i>My Profile</p></a>
                      <a href="logoff.php">
                        <p><i class="fa fa-sign-out fa-fw w3-margin-right w3-text-theme"></i>Log Off</p></a>
                      <div class="follow-col" id="follow-col">
                            <button class="start-home-link follow-list" id="following" onclick="load_following_bg()">Following (<?php echo $num_following;?>)</button>
                            <div class="start-home-link v2" id="follow-separator"></div>
                            <button class="start-home-link follow-list" id="follower">Followers (<?php echo $num_followers;?>)</button>
                      </div>
                    </div>
                  </div><br>
                  <!-- End of Profile Card -->

                  <!-- Accordion -->
                  <div class="w3-card w3-round" class="follow-col" id="follow-col">
                    <div class="w3-white">
                      <button class="follow-list w3-button w3-block w3-theme-l1 w3-left-align" id="following" onclick="load_following_bg()">Following <?php echo $num_following;?></button>
                      
                        <!-- Following List -->
                        <div class="content" onclick="load_following_list()">
                        <div class="invis" id="following-bg">
                        <?php
                            $following_count = $db->query("SELECT * FROM following_list WHERE User1_ID = '" .$_SESSION['accountid'] . "'");
                            while($row = $following_count->fetch_assoc()) {
                                $following_username = $db->query("SELECT Username FROM accounts WHERE ID = '" .$row["User2_ID"] . "'");
                                $row2 = $following_username->fetch_assoc();
                                $user = $row2["Username"];
                        ?>
                                <a href="" onclick="open_profile('+<?php echo $user?>+'); return false;">
                        <?php
                                echo "<div class='profilePreview'>";
                                echo "<div class='mini-prof-container' id='mini-prof-container'>";
                                echo "<img class='w3-center w3-circle' id='follow-prof-pic' style='height:40px; width:40px'";
                                echo "     src='other_user_profile_picture.php?Username=".$user."'>";

                                echo "<p class='profilePreviewUsername' style='display:inline-block; margin-left: 10px;'>".$user."</p>";
                                echo "</div>";
                                echo "</div></a>";
                            }
                        ?>
                        <script>
						function load_following_bg() {
                 			var elem = document.getElementById("following-bg");
                 			if (elem.className == "invis" || elem.className == "following-bg-disappear") {
                     			elem.className = "following-bg";
                      			setTimeout(load_following_list, 0500);
                 			} else {
                     			elem.className = "following-bg-disappear";
                     			load_following_list();
                 			}
             			}

                        function load_following_list() {
                          var follow_list_info = document.getElementById("load-follow-list");
                          var follow_prof_pic = document.getElementById("follow-prof-pic");
                          var follow_prof_pic_cont = document.getElementById("mini-prof-container");

                          if (follow_prof_pic.className == "invis" || follow_prof_pic.className == "rm-follow-list def-prof-pic") {
                              follow_prof_pic_cont.className = "load-follow-list mini-prof-container";
                              follow_prof_pic.className = "load-follow-list def-prof-pic";
                              follow_list_info.className = "load-follow-list";
                          } else {
                              follow_list_info.className = "rm-follow-list";
                              follow_prof_pic.className = "rm-follow-list def-prof-pic";
                              follow_prof_pic_cont.className = "rm-follow-list mini-prof-container";
                          }
                        }
                        </script>

                        </div>
                        <!-- End of Following List -->
                      </div>
                    </div>
                  </div><br>
                  <!-- End of Accordion -->

                  <!-- Accordion -->
                  <div class="w3-card w3-round">
                    <div class="w3-white">
                      <button class="follow-list w3-button w3-block w3-theme-l1 w3-left-align" id="following">Followers <?php echo $num_followers;?></button>
                    
                    <!-- Following List -->
                    <div class="content">
                        <?php
                            $following_count = $db->query("SELECT * FROM following_list WHERE User2_ID = '" .$_SESSION['accountid'] . "'");
                            while($row = $following_count->fetch_assoc()) {
                                $following_username = $db->query("SELECT Username FROM accounts WHERE ID = '" .$row["User1_ID"] . "'");
                                $row2 = $following_username->fetch_assoc();
                                $user = $row2["Username"];
                        ?>
                                <a href="" onclick="open_profile('+<?php echo $user?>+'); return false;">
                        <?php
                                echo "<div class='profilePreview'>";
                                echo "<div class='mini-prof-container' id='mini-prof-container'>";
                                echo "<img class='w3-center w3-circle' id='follow-prof-pic' style='height:40px; width:40px'";
                                echo "     src='other_user_profile_picture.php?Username=".$user."'>";

                                echo "<p class='profilePreviewUsername' style='display:inline-block; margin-left: 10px;'>".$user."</p>";
                                echo "</div>";
                                echo "</div></a>";
                            }
                        ?>
                    </div>
                    <!-- End of Follower List -->
                    </div>
                  </div><br>
                  <!-- End of Accordion -->
              </div>
              <!-- End of Left Column -->

              <script>
                var coll = document.getElementsByClassName("follow-list");
                var i;

                for (i = 0; i < coll.length; i++) {
                  coll[i].addEventListener("click", function() {
                    this.classList.toggle("active");
                    var content = this.nextElementSibling;
                    if (content.style.maxHeight){
                      content.style.maxHeight = null;
                    } else {
                      content.style.maxHeight = content.scrollHeight + "px";
                    }
                  });
                }

                function open_profile(username) {
                    // Search elements
                    var statsView = document.getElementById("statsView");
                    var search = document.getElementById("search");
                    var scrapeButton = document.getElementById("scrapeButton");
                    var nextButton = document.getElementById("nextPage");
                    var prevButton = document.getElementById("prevPage");
                    // Feed elements
                    var feedView = document.getElementById("feedView");

                    // Hide the search results
                    statsView.className="invis";
                    search.className="invis-search";
                    scrapeButton.className="invis-scrapeButton";
                    nextButton.className="invis-scrapeButton";
                    prevButton.className="invis-scrapeButton"; 
                    document.getElementById("results").classList.add("invis-scrapeButton");

                    // Hide the feed
                    feedView.style.display = "none";

                    setTimeout(load_profile(username), 0500);
                }

                function open_feed() {
                    console.log("open_feed() entered..");
                    // Search elements
                    var statsView = document.getElementById("statsView");
                    var search = document.getElementById("search");
                    var scrapeButton = document.getElementById("scrapeButton");
                    var nextButton = document.getElementById("nextPage");
                    var prevButton = document.getElementById("prevPage");
                    // Feed elements
                    var feedView = document.getElementById("feedView");
                    // Profile elements
                    var following = document.getElementById("following");
                    var follower = document.getElementById("follower");
                    var follow_separator = document.getElementById("follow-separator");
                    var bio = document.getElementById("bio");
                    var occ = document.getElementById("occ");
                    var bdate = document.getElementById("bdate");
                    var picture = document.getElementById("profile-pic");
                    var follow_button = document.getElementById("follow-button");

                    // Hide the search results
                    statsView.className="invis";
                    search.className="invis-search";
                    scrapeButton.className="invis-scrapeButton";
                    nextButton.className="invis-scrapeButton";
                    prevButton.className="invis-scrapeButton";
                    document.getElementById("results").classList.add("invis-scrapeButton");

                    // Hide the profile
                    following.className="invis-home-link follow-list";
                    follower.className="invis-home-link follow-list";
                    follow_separator.className="invis-home-link v2";
                    bio.className="invis-prof-info bio";
                    occ.className="invis-prof-info occ";
                    bdate.className="invis-prof-info bio";
                    picture.style.display = "none";
                    follow_button.style.display = "none";

                    // Show the feed
                    feedView.style.display = null;
 
                    console.log("Leaving open_feed()");
                }

                function open_search() {
                    // Search elements
                    var statsView = document.getElementById("statsView");
                    var search = document.getElementById("search");
                    var scrapeButton = document.getElementById("scrapeButton");
                    var nextButton = document.getElementById("nextPage");
                    var prevButton = document.getElementById("prevPage");
                    // Feed elements
                    var feedView = document.getElementById("feedView");
                    // Profile elements
                    var following = document.getElementById("following");
                    var follower = document.getElementById("follower");
                    var follow_separator = document.getElementById("follow-separator");
                    var bio = document.getElementById("bio");
                    var occ = document.getElementById("occ");
                    var bdate = document.getElementById("bdate");
                    var picture = document.getElementById("profile-pic");
                    var follow_button = document.getElementById("follow-button");

                    // Hide the feed
                    feedView.style.display = "none";

                    // Hide the profile                    
                    picture.style.display = "none";
                    follow_button.style.display = "none";
                    following.className="invis-home-link follow-list";
                    follower.className="invis-home-link follow-list";
                    follow_separator.className="invis-home-link v2";
                    bio.className="invis-prof-info bio";
                    occ.className="invis-prof-info occ";
                    bdate.className="invis-prof-info bio";
                    picture.style.display = "none";
                    follow_button.style.display = "none";

                    // Redraw the search results
                    setTimeout(load_search, 0500);

                    function load_search() {
                        statsView.className="load";
                        search.className="load-search";
                        search.classList.add("w3-border");
                        search.classList.add("w3-padding");
                        scrapeButton.className="load-scrapeButton w3-button w3-theme-l1";
                        prevButton.className="load-scrapeButton w3-button w3-theme-l1";
                        nextButton.className="load-scrapeButton w3-button w3-theme-l1";
                        document.getElementById("results").classList.remove("invis-scrapeButton");
                    }
                }
              </script>

              <!-- Middle Coumn -->
              <div class="w3-col m8 l9 w3-padding">
                <div class="w3-row-padding">
                  <div class="w3-col m12">
                    <div class="w3-card w3-round w3-white">
                      <div class="w3-container w3-padding">
                        <div class = "col-12 col-md-8 offset-md-3" >
                          <div class="w3-container w3-card w3-white w3-round w3-margin" id="articleView">
                            <h1 id="infoBoxTitle">Title</h1>
                            <p id="infoBoxPageScore"></p>
                            <p id="infoBoxSiteScore">Site:</p>
                            <div class="w3-container w3-text-blue-gray"><p id="commentsHeader">Comments:</p></div>
                            <div id="infoBoxComments"></div>
                            <textarea class="w3-input w3-border" style="resize:none" id="commentEntry" placeholder="Add a Comment"></textarea>
                            <button style="margin-bottom:10px;" id="submitCommentButton" class="w3-button w3-theme-l1">Post Comment</button>
                            <button style="margin-bottom:10px;" id="fromButton" class="w3-button w3-theme-l1">Return</button>
                          </div>
                          <img class="w3-circle" id="profile-pic" style="height:106px;width:106px; display:none;" src='' alt="Avatar">
                          <button type="button" id="follow-button" class="w3-button w3-theme-l1" style="margin-left:20px; display:none;"
                                  onclick="change_follow_status();"></button>
                          <div class="bio-info-col" id="bio-info-col">
                          <script>
                            function load_profile(username) {
                            var following = document.getElementById("following");
                            var follower = document.getElementById("follower");
                            var follow_separator = document.getElementById("follow-separator");
                            var bio = document.getElementById("bio");
                            var occ = document.getElementById("occ");
                            var bdate = document.getElementById("bdate");
                            var follow_button = document.getElementById("follow-button");

                            following.className="home-link follow-list";
                            follower.className="home-link follow-list";
                            follow_separator.className="home-link v2";
                            bio.className="prof-info bio";
                            occ.className="prof-info occ";
                            bdate.className="prof-info bio";
                            
                            var xhr = new XMLHttpRequest();
                            xhr.open("POST","display_profile_info.php",true);
                            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            xhr.onreadystatechange = function() {
                                if (this.readyState == 4 && this.status == 200) {
                                    var obj = JSON.parse(this.responseText);
                                    var picture = document.getElementById("profile-pic");

                                    picture.style.display = null;
                                  
                                    // if My Profile is pressed, the following button won't display 
                                    if(obj.main_user_flag == true) 
                                    follow_button.style.display = "none";
                                    else
                                    follow_button.style.display = null;
                                    
                                    // if following user, display "Unfollow" and vice versa
                                    if(obj.follow_status == "following"){
                                    follow_button.innerHTML = "Unfollow";
                                    } else if (obj.follow_status == "not following") {
                                    follow_button.innerHTML = "Follow";
                                    }
                                    picture.src = "other_user_profile_picture.php?Username="+username;
                                    bio.innerHTML = "<b>Bio:</b><br>"+obj.bio;
                                    occ.innerHTML = "<b>Occupation:</b><br>"+obj.occup;
                                    bdate.innerHTML = "<b>Birth Date:</b><br>"+obj.bday;
                                }
                            };

                            // Send the username to retrieve profile info
                            xhr.send("user="+username);
                            } 
                            function change_follow_status() {
                            var follow_button = document.getElementById("follow-button"); 
                            var xhr = new XMLHttpRequest();

                            xhr.open("POST","change_follow_status.php",true);
                            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            xhr.onreadystatechange = function() {
                                if (this.readyState == 4 && this.status == 200) {
                                    if(follow_button.innerHTML == "Unfollow"){
                                    follow_button.innerHTML = "Follow";
                                    } else{
                                     follow_button.innerHTML = "Unfollow";
                                    }
                                }
                            };

                            // Send the username to retrieve profile info
                            xhr.send(); 
                          } 
                          </script>
                            <p class="start-prof-info bio" id="bio"></p>
                          </div>

                          <div class="bio-info-col">
                            <p class="start-prof-info bio" id="occ"></p>
                          </div>

                          <div class="bio-info-col">
                            <p class="start-prof-info bio" id="bdate"></p>
                          </div>

                          <div class="invis" id="statsView"> Total pages indexed:
                            <?php
                              $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
                              mysqli_select_db($link, "analytics") or die("Could not select database\n");
                              $result = mysqli_query($link, "SELECT COUNT(*) FROM pages");
                              $row = mysqli_fetch_row($result);
                              print number_format($row[0]);
                            ?>
                          </div><br>

                          <input class="invis-search" type ="text" id="search" placeholder="Search for article"><br><br>
                          <button type="button" id="scrapeButton" class="invis-scrapeButton">Scrape Site</button>
                          <button id="prevPage" class="invis-scrapeButton">Previous</button>
                          <button id="nextPage" class="invis-scrapeButton">Next</button>
                          <img src="Images/blue-loading-gif-transparent.gif" id="loadingGif"><hr>
                          <p id="results"></p><br>
                        </div>

                        <!-------------------------- Begin Feed Code ------------------------------------->
                          <div class="" id="feedView">Most recent comments from users you are following:
                            <?php
                              $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
                              mysqli_select_db($link, "analytics") or die("Could not select database\n");

                              // Look up the current user's ID
                              $result = mysqli_query($link, "SELECT ID FROM accounts WHERE Username = '$username'");
                              $row = mysqli_fetch_row($result);
                              $userid = $row[0];

                              // Pull the most recent 20 comments from users they are following
                              $result = mysqli_query($link, "SELECT c.user_ID, a.username, c.page_ID, p.Title, c.comment, c.time_posted, p.URL FROM comments AS c LEFT JOIN accounts AS a ON c.user_ID = a.ID LEFT JOIN pages AS p ON c.page_ID = p.ID WHERE c.user_ID IN (SELECT User2_ID FROM following_list WHERE User1_ID = $userid) ORDER BY time_posted DESC LIMIT 20");
                              $numrows = mysqli_num_rows($result);

                              print "<hr>";
                              print "<p id='results'>";

                              // Post the results, one div each
                              for ($i = 0; $i < $numrows; $i++) {
                                $row = mysqli_fetch_row($result);
                                print "<div id='comment-$i' class='w3-card'>";
                                // Heading:  Username + Page Title
                                print "<header class='w3-container w3-blue'>";
                                print "<p style='font-weight: bold;'>$row[1]:  $row[3]</p>";
                                print "</header>";
                                // Comment text
                                print "<div class='w3-panel'>";
                                print "<p>$row[4]</p>";
                                print "</div>";
                                // Timestamp and URL
                                print "<div class='w3-panel w3-text-blue-gray'>";
                                print "<p>$row[5]</p>";
                                print "<p>$row[6]</p>";
                                print "</div>";
                                print "</div>";
                              }

                            ?>
                          </div><br>
                          <!--------------- End feed code -------------------->

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
    document.getElementById("prevPage").disabled = true;
    document.getElementById("nextPage").disabled = true;
    var currentPage = 0;
    var socket = new WebSocket("wss://www.datadogsanalytics.com:8080");
    socket.onopen = function(e) {
        console.log("Connected to websocket server.");
    }

    // Handle incoming scrape results
    socket.onmessage = function(message) {
        console.log("Received from Node.js backend:  " + message.data);
        var msg = JSON.parse(message.data);
        if (msg.isScrapeResult) {
            // Pass returned JSON to appendScrapedPage
            appendScrapedPage(msg);
        }
    }

    searchbar.addEventListener("keyup",function() {
        currentPage = 0;
        document.getElementById("prevPage").disabled = true;
        document.getElementById("articleView").style.display = "none";
        document.getElementById("statsView").appendChild(document.getElementById("articleView"));
        requestSearchResults(currentPage);
});


document.getElementById("prevPage").addEventListener("click",function(){
    if(currentPage==0){
        document.getElementById("prevPage").disabled = true;
    }else if(currentPage == 1){
        document.getElementById("prevPage").disabled = true;
        currentPage--;
    }else{
        currentPage--;
    }
    document.getElementById("nextPage").disabled = false;
    requestSearchResults(currentPage);
    console.log(currentPage);
});

document.getElementById("nextPage").addEventListener("click",function(){
    currentPage++;
    document.getElementById("prevPage").disabled = false;
    requestSearchResults(currentPage);
    console.log(currentPage);
});

function requestSearchResults(page){
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
                document.getElementById("nextPage").disabled = true;
            } else {
                results.innerHTML="";
                range = (resultArray.length>10)? 10:resultArray.length;
                if(resultArray.length<10){
                    document.getElementById("nextPage").disabled = true;
                    console.log(resultArray.length);
                }else{
                    document.getElementById("nextPage").disabled = false;
                }


                for(var i = 0;i<range;i++){
                    if(resultArray.title!=""){
                        //console.log(resultArray[i]);
                        appendScrapedPage(resultArray[i]);
                    }
                }
            }
        }
        };
        xhr.send("input="+searchbar.value+"&page="+page);
}

    document.getElementById("scrapeButton").addEventListener("click",function() {
        var scrapeMessage = new Object();
        scrapeMessage.target = searchbar.value;
        scrapeMessage.isScrapeSubscription = true;
        socket.send(JSON.stringify(scrapeMessage));
    });

    function appendScrapedPage(object) {
        if (object.page_title == "Website Unavailable (Exception Encountered)") { return; }
        var container = document.createElement("div");
        container.setAttribute("id",object.url);
        // Alternate background colors
        container.className= "w3-card";


        var titleWrapper = document.createElement("header");
        titleWrapper.className="w3-container";

        if(object.page_score>0){
          titleWrapper.classList.add("w3-deep-orange");
        }else{
          titleWrapper.classList.add("w3-blue");
        }
        var title = document.createElement("p");
        titleWrapper.appendChild(title);

        var linkToArticle = document.createElement("a");
        var linkWrapper = document.createElement("div");
        linkWrapper.className  = "w3-panel w3-text-blue-gray";
        var moreInfo = document.createElement("img");
        moreInfo.addEventListener("click",function(e){
            loadArticleInfo(object.url,e.target.parentElement);
            //console.log(e.target.parentElement);
            //document.getElementById("searchView").style.display="none";
        });

        var domain = document.createElement("p");
        title.setAttribute("style", "font-weight: bold;");
        title.innerHTML = object.page_title;
        moreInfo.src = "domainIcon.php?url=" + object.url + "";
        moreInfo.width = 32;
        moreInfo.height = 32;
        linkToArticle.setAttribute("href",object.url);
        linkToArticle.innerHTML = "&nbsp;&nbsp;Visit Page";
        linkToArticle.target = "_blank";
        domain.innerHTML = object.url;
        linkWrapper.appendChild(domain);
        container.appendChild(titleWrapper);
        container.appendChild(moreInfo);
        container.appendChild(linkToArticle);
        container.appendChild(linkWrapper);
        results.appendChild(container);
    }

    function loadArticleInfo(url,newParent) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST","getArticleInfoAndComments.php",true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.parent = newParent;
        xhr.onreadystatechange = function(newParent) {
            if (this.readyState == 4 && this.status == 200) {
                console.log(this.responseText);
                var data = JSON.parse(this.responseText);
                console.log(data.id);
                currentArticleId = data.id;
                document.getElementById("infoBoxTitle").innerHTML = data.title;
                document.getElementById("infoBoxPageScore").innerHTML = (data.page_score>0)? "Clickbait":"Not Clickbait";
                document.getElementById("infoBoxSiteScore").innerHTML = "Site Rep: "+data.site_score+"%";
                document.getElementById("infoBoxComments").innerHTML="";
                for(var i = 0;i<data.comments.length;i++){
                    addComment(data.comments[i].username,data.comments[i].body,data.comments[i].date);
                }
                //console.log(document.getElementById("articleView"));
                this.parent.appendChild(document.getElementById("articleView"));
                document.getElementById("articleView").style.display="block";

            }
        };
        xhr.send("url="+url);
    }

    document.getElementById("fromButton").addEventListener("click",function() {
        document.getElementById("articleView").style.display="none";
        //document.getElementById("searchView").style.display="block";
    });

    /////////////Comments Stuff below here///////////////////////
    var currentArticleId = -1;
    var commentServerConnection = new WebSocket("wss://www.datadogsanalytics.com:9000");
    commentServerConnection.onopen = function(e) {
        console.log("Connected to comment server.");
        document.getElementById("submitCommentButton").addEventListener("click",function(){
          var commentMessage = new Object();
          commentMessage.isCommentMessage = true;
          commentMessage.body = document.getElementById("commentEntry").value;
          document.getElementById("commentEntry").value = "";

          commentMessage.user = <?php echo $_SESSION["accountid"];?>;
          commentMessage.username = <?php echo "'".$_SESSION["username"]."'"?>;
          commentMessage.article = currentArticleId;
          console.log(commentMessage);
          commentServerConnection.send(JSON.stringify(commentMessage));
      });
    }
  commentServerConnection.onmessage = function(message) {
        var msg = JSON.parse(message.data);
        if(msg.article==currentArticleId){
          addComment(msg.username,msg.body,msg.date);
        }
  };
  function addComment(username,body,date){
    //This is because I am too lazy to implement synchonicity in the server.
    //Really no big deal, the right date shows up on page refresh.
    if(date==undefined){
      date="Just Now";
    }
    var commentName = document.createElement("div");
    commentName.className="w3-panel w3-text-Black";
    commentName.innerHTML = "<b>"+username+"</b>";
    document.getElementById("infoBoxComments").appendChild(commentName);

    var commentDate = document.createElement("div");
    commentDate.className="w3-panel w3-text-Gray commentDate";
    commentDate.innerHTML="<i>"+date+"</i>";
    document.getElementById("infoBoxComments").appendChild(commentDate);

    var commentBody = document.createElement("div");

    commentBody.className="w3-panel w3-text-black";
    commentBody.innerHTML = body+"<br><hr>";
    document.getElementById("infoBoxComments").appendChild(commentBody);
    //document.getElementById("infoBoxComments").innerHTML += username+": "+body+" "+date+"<br>";
  }
</script>
</html>
