function handleMessage(request, sender, sendResponse) {
  if(request.context=="explore"){
    var url = request.URL;
    var sReq = new XMLHttpRequest();
    sReq.open("POST","https://datadogsanalytics.com/api/plugin-submit-url.php",true);
    sReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    sReq.send("url="+url);
    console.log(url);
    sReq.onload = function(){
      var newURL = "{\""+url+"\":"+this.responseText+"}";
      newURL = JSON.parse(newURL);
      document.getElementById("pageTitle").innerHTML = newURL[url].page_title;
      document.getElementById("pageScore").innerHTML ="Page Score: " + newURL[url].page_score;
      document.getElementById("siteScore").innerHTML = "Site Score: " + newURL[url].site_score;
      //console.log(newURL);
      //console.log(this.responseText);
    }
  }
}
browser.runtime.onMessage.addListener(handleMessage);