<?php
session_start();
if(!isset($_SESSION["username"])){
    header("location: index.php");
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Data Dogs Analytics</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/4.3/examples/sign-in/">
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" 
     integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" 
     crossorigin="anonymous">
    <!-- Custom styles for this template -->
    <link href="css/main.css" rel="stylesheet">
</style>
  </head>
   <body>
    <div class="row">
    <div class="col-12 col-md-3 offset-md-2">
    <img class="logo" src="/Images/DataDogs_ClearLogo.png"> </img>
    </div>
    <div class="col-12 col-md-1 offset-md-4">
    <a href="profile_page.php">
    <img class="def-prof-pic d-none d-md-block" src="view.php?id= <?php echo $_SESSION["username"]?> " alt="DefaultProf">
    </a>
    <form action="logoff.php" method="post">    
    <button class="log-out" type="submit">Log Out</button>
    </form>
    </div>
    </div>
    <?php include_once("nav.php");?>
    <div class = "col-12 col-md-8 offset-md-2" >
    
    <div class="Body-box" id="articleView">
    <h1 id="infoBoxTitle">Title</h1>
        <p id="infoBoxPageScore"></p>
        <p id="infoBoxSiteScore">Site:</p>
        <p>Comments:</p>
        <div id="infoBoxComments"></div>
    <button id="fromButton">Return</button>
    </div>

    <div class="Stats-box" id="statsView">
        Total pages indexed:
<?php
    $link = mysqli_connect("localhost", "datadogs", "DataDogs2020CSUB") or die("Could not connect to database\n");
    mysqli_select_db($link, "analytics") or die("Could not select database\n");
    $result = mysqli_query($link, "SELECT COUNT(*) FROM pages");
    $row = mysqli_fetch_row($result);
    print number_format($row[0]);
?>
    </div>

    <div class="Body-box" id="searchView">
    <input type = "text" id="search" placeholder="Search for article">
    <button id="scrapeButton">Scrape Domain</button>
    <img src="Images/blue-loading-gif-transparent.gif" id="loadingGif">
    <p id="results"></p>
    </div>
</div>
    </body>
    <script>
    var searchbar = document.getElementById("search");
    var results = document.getElementById("results");
    var socket = new WebSocket("wss://www.datadogsanalytics.com:8080");
    socket.onopen = function(e){
        console.log("Connected to websocket server.");
    }
    socket.onmessage = function(e){
        console.log(e.data);
    }
    searchbar.addEventListener("keyup",function(){
        var xhr = new XMLHttpRequest();
        xhr.open("POST","getSearchResults.php",true);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
        	//results.innerHTML = this.responseText;
            console.log(this.responseText);
            var resultArray = JSON.parse(this.responseText);
            if(resultArray.length==0){
                results.innerHTML="No results found.";
            }else{
                results.innerHTML="";
                for(var i = 0;i<resultArray.length;i++){
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
    /*
    document.getElementById("scrapeButton").addEventListener("click",function(){
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
    });
    */
    document.getElementById("scrapeButton").addEventListener("click",function(){
        var scrapeMessage = new Object();
        scrapeMessage.target = searchbar.value;
        scrapeMessage.isScrapeSubscription = true;
        socket.send(JSON.stringify(scrapeMessage));
    });
    function appendScrapedPage(object){
        if(object.page_title=="Website Unavailable (Exception Encountered)"){
            return;
        }
        var container = document.createElement("div");
        container.setAttribute("id",object.url);
                var title= document.createElement("p");
        var linkToArticle = document.createElement("a");
        var moreInfo = document.createElement("button");
        moreInfo.addEventListener("click",function(e){
        loadArticleInfo(object.url);
        document.getElementById("articleView").style.display="block";
        document.getElementById("searchView").style.display="none";
        });
        var domain = document.createElement("p");
        title.innerHTML = object.page_title;
        moreInfo.innerHTML = "&#128203;";
        moreInfo.classList.add("moreInfoButton");
        linkToArticle.setAttribute("href",object.url);
        linkToArticle.innerHTML = "Visit Page";
        domain.innerHTML = object.url;
        container.appendChild(title);
        container.appendChild(moreInfo);
        container.appendChild(linkToArticle);
        container.appendChild(domain);
        results.appendChild(container);
    }
    function loadArticleInfo(url){
        var xhr = new XMLHttpRequest();
        xhr.open("POST","getArticleInfoAndComments.php",true);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200){
                console.log(this.responseText);
                var data = JSON.parse(this.responseText);
                document.getElementById("searchView").style.display="none";
               
                document.getElementById("infoBoxTitle").innerHTML = data.title;
                document.getElementById("infoBoxPageScore").innerHTML = data.page_score;
                document.getElementById("infoBoxSiteScore").innerHTML = data.site_score;
                console.log(data);
                document.getElementById("infoBoxComments").innerHTML="";
                for(var i = 0;i<data.comments.length;i++){
                    document.getElementById("infoBoxComments").innerHTML += data.comments[i].username+": "+data.comments[i].body;
                }
            }
        };
        xhr.send("url="+url);       
    }
    document.getElementById("fromButton").addEventListener("click",function(){
        document.getElementById("articleView").style.display="none";
        document.getElementById("searchView").style.display="block";
    });


    </script>
</html>
