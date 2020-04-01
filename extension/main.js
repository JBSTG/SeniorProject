console.log("START");
document.body.style.border = "5px solid red";
var x = document.links.length;
var data = buildRequestString(x);
var linksObject = new Object();
var infoBox = createInfoBox();
var isBoxDynamic = 1;
var isBoxEnabled = 1;
function handleResponse(message) {
  //console.log(message);
}
function handleError(error) {
  //console.log(`Error: ${error}`);
}

function notifyBackgroundPage(e) {
  var sending = browser.runtime.sendMessage({
    "data": data
  });
  //We're not using responses at the moment, this may be useful later.
  //sending.then(handleResponse, handleError);  
}

window.onload = function(e){
	// This gets called when the page loads
	//notifyBackgroundPage(e);
	
	// Emumerate all links on the page
	var links = document.getElementsByTagName("A");
	console.log("links[] contains " + links.length + " items");
	// Query API for each link in array
	var i;
	for (i = 0; i < links.length; i++)
		queryAPI(links[i]);
}

function queryAPI (url) {
    var sReq = new XMLHttpRequest();
    sReq.open("POST","https://datadogsanalytics.com/api/plugin-submit-url.php",true);
    sReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    console.log("Requesting from API:  " + url);
    sReq.send("url="+url);
  
    sReq.onload = function(){
        var newURL = "{\""+url+"\":"+this.responseText+"}";
        //console.log(this.responseText);
        newURL = JSON.parse(newURL);
        Object.assign(linksObject,newURL);
        console.log("Received from API:  " + this.responseText);
  }
}

function buildRequestString(links){
    var requestString = new Object();
    requestString.links = [];
    for(var i=0;i<links;i++){
        var string = document.links.item(i).href;
        string = string.split(/[&?#$]/);
        requestString.links.push(string[0]);
    }
    //console.log(requestString);
    return requestString;
}
/*
//TODO: Needs to work with dynamically added elements as well.
var domLinks = document.getElementsByTagName("a");
console.log("EEE");
for(var i = 0;i<domLinks.length;i++){
    domLinks[i].addEventListener("mouseover", function(e){
        if(linksObject){
            var url = e.target.href;
            console.log(url+": "+linksObject[url].site_score);
            var linkScores = linksObject[url];
            moveInfoBox(url,linkScores,e.pageX,e.pageY);
        }
    });
}
*/

function processLinks(e) {
  // This gets called by the mouseover event
  var e = window.e || e;

  if (e.target.tagName !== 'A'){
    return;
  }
  var url = e.target.href;
  url = url.split(/[&?#$]/);
  url = url[0];
  currentURL = url;
  
  if (linksObject) {
    console.log(url);
    if (linksObject[url] == null) {
      //TODO: I'm going to make a direct call to the API here, I should use messages later
      console.log("There is no match for this URL, contacting API.");
	  queryAPI(url);
    }
  }
}

function mouseMoved(e) {
  // This gets called by the mousemove event
  var e = window.e || e;

  if (e.target.tagName !== 'A'){
    return;
  }
  var url = e.target.href;
  url = url.split(/[&?#$]/);
  url = url[0];

  if (linksObject) {
    console.log(url);
    if (linksObject[url] != null) {
      var linkScores = linksObject[url];
      moveInfoBox(url,linkScores,e.pageX,e.pageY);
	}
  }
}

if (document.addEventListener) {
  document.addEventListener('mouseover', processLinks, false);
  document.addEventListener('mousemove', mouseMoved, false);
} else {
  document.attachEvent('onmouseover', processLinks);
  document.attachEvent('onmousemove', mouseMoved);
}

function createInfoBox(){
  console.log("Creating infobox");
  var infoBox = document.createElement("div");
  //infoBox.style.width = "150px";
  infoBox.style.padding = "0px";
  infoBox.style.fontSize = "10";
  infoBox.style.color = "white";
  //infoBox.style.height= "100px";
  infoBox.style.backgroundImage = "linear-gradient(135deg,rgba(0,0,0,1),rgba(0,0,0,0.5) 70%)";
  infoBox.style.position = "absolute";
  //infoBox.style.border="1px darkslategray solid";
  infoBox.style.left = 0+"px";
  infoBox.style.top = 0+"px";
  infoBox.style.display = "none";
  infoBox.style.pointerEvents = "none";
  document.body.appendChild(infoBox);
  return infoBox;
}

function moveInfoBox(url,values,x,y){

  if(!isBoxEnabled){
    return;
  }

  infoBox.innerHTML = "";
  infoBox.style.display = "block";
  infoBox.style.padding = "10px";

  // Page title  
  var title = document.createElement("p");
  if (values.page_title.length > 0)
    title.innerHTML = "<a href='" + url + "'><font color=white>" + values.page_title + "</font></a>";
  else
	  title.innerHTML = "<a href='" + url + "'><font color=white>" + url + "</font></a>";

  title.style.margin = "0px";
  title.style.padding = "0px";
  infoBox.appendChild(title);

  // Page score from API  
  var pageScore = document.createElement("p");
  if (values.page_score == "0") 
    pageScore.innerHTML = "Page Reputation:  Good";
  else if(values.page_score == "1")
	pageScore.innerHTML = "Page Reputation:  Bad";
  pageScore.style.margin = "0px";
  pageScore.style.padding = "0px";
  infoBox.appendChild(pageScore);

  // Site score from API
  var siteScore = document.createElement("p");
  siteScore.innerHTML = "Site Score:  " + values.site_score+"%";
  siteScore.style.margin = "0px";
  siteScore.style.padding = "0px";
  infoBox.appendChild(siteScore);

  infoBox.style.zIndex ="999999999";
  if(isBoxDynamic){
    infoBox.style.left = x-75 + "px";
    infoBox.style.top = y-60 + "px";
    infoBox.style.position = "absolute";

  }else{
    infoBox.style.left="0px";
    infoBox.style.top="0px";
    infoBox.style.position = "fixed";
  }
}
function handleMessage(request, sender, sendResponse) {
  console.log(request.context);
  if(request.context=="toggle"){
    isBoxDynamic^=1;
    if(!isBoxDynamic){
      infoBox.style.left="0px";
      infoBox.style.top="0px";
      infoBox.style.position = "fixed";
    }
    return;
  }
  if(request.context="dismiss"){
    isBoxEnabled^=1;
    if(!isBoxEnabled){
      infoBox.style.display = "none";
    }
  }
}
browser.runtime.onMessage.addListener(handleMessage);

console.log("END");
