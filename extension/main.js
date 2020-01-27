console.log("START");
document.body.style.border = "5px solid red";
var x = document.links.length;
var data = buildRequestString(x);
var linksObject = new Object();
var infoBox = createInfoBox();
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
	//notifyBackgroundPage(e);
}

function buildRequestString(links){
    var requestString = new Object();
    requestString.links = [];
    for(var i=0;i<links;i++){
        var string = document.links.item(i).href;
        string = string.split(/[&?#$]/);
        requestString.links.push(string[0]);
    }
    console.log(requestString);
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
  var e = window.e || e;

  if (e.target.tagName !== 'A'){
    return;
  }
  var url = e.target.href;
  url = url.split(/[&?#$]/);
  url = url[0];
  currentURL = url;
  if(linksObject){
    console.log(url);
    if(linksObject[url]!=null){
      var linkScores = linksObject[url];
      moveInfoBox(url,linkScores,e.pageX,e.pageY);
    }else{
      //TODO: I'm going to make a direct call to the API here, I should use messages later
      console.log("There is no match for this URL, contacting API.");
      var sReq = new XMLHttpRequest();
      sReq.open("POST","https://datadogsanalytics.com/api/plugin-submit-url.php",true);
      sReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      sReq.send("url="+url);

      sReq.onload = function(){
        var newURL = "{\""+url+"\":"+this.responseText+"}";
        console.log(this.responseText);
        newURL = JSON.parse(newURL);
        Object.assign(linksObject,newURL);
        //console.log(this.responseText);
      }
    }
    }
}

if (document.addEventListener)
  document.addEventListener('mouseover', processLinks, false);
else
  document.attachEvent('onmouseover', processLinks);

function createInfoBox(){
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
  document.body.appendChild(infoBox);
  return infoBox;
}

function moveInfoBox(url,values,x,y){
  infoBox.innerHTML = "";
  infoBox.style.display = "block";
  
  var title = document.createElement("p");
  title.innerHTML = values.page_title;
  title.style.margin = "0px";
  title.style.padding = "0px";
  infoBox.appendChild(title);
  
  var pageScore = document.createElement("p");
  pageScore.innerHTML = values.page_score;
  pageScore.style.margin = "0px";
  pageScore.style.padding = "0px";
  infoBox.appendChild(pageScore);

  var siteScore = document.createElement("p");
  siteScore.innerHTML = values.site_score+"%";
  siteScore.style.margin = "0px";
  siteScore.style.padding = "0px";
  infoBox.appendChild(siteScore);
  //infoBox.style.left = x-75+"px";
  //infoBox.style.top = y-120+"px";
  infoBox.style.zIndex ="999999999";
}

console.log("END");
