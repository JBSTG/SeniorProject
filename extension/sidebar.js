function handleMessage(request, sender, sendResponse) {
  if(request.context=="explore"){
    var url = request.URL;
    var sReq = new XMLHttpRequest();
    sReq.open("POST","https://datadogsanalytics.com/getArticleInfoAndComments.php",true);
    sReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    sReq.send("url="+url);
    console.log(url);
    sReq.onload = function(){
      var newURL = "{\""+url+"\":"+this.responseText+"}";
      console.log(this.responseText);
      newURL = JSON.parse(newURL);
      document.getElementById("pageTitle").innerHTML = newURL[url].title;
      document.getElementById("siteScore").innerHTML = "Site Score: " + newURL[url].site_score+"%";
      document.getElementById("commentsHeader").style.visibility="visible";
      if(newURL[url].page_score==1){
		document.getElementById("pageScore").innerHTML ="This page is clickbait";
		document.getElementById("pageTitle").parentElement.classList.add("w3-deep-orange");
	  }else{
		document.getElementById("pageScore").innerHTML ="This page is not clickbait";
		document.getElementById("pageTitle").parentElement.classList.add("w3-blue");
	  }
	  
	  var commentsBox = document.getElementById("comments");
	  commentsBox.innerHTML = "";
	  if(newURL[url].comments.length==0){
		commentsBox.innerHTML = "<div class=\"w3-container w3-text-blue-gray\"><p>No comments yet.</p></div>";
      }else{
		for(var i = 0;i<newURL[url].comments.length;i++){
			var h = document.createElement("div");
			h.className="w3-container w3-light-blue w3-text-white";
			var u = document.createElement("p");
			u.innerHTML = newURL[url].comments[i].username;
			h.appendChild(u);
			commentsBox.appendChild(h);
			
			var c = document.createElement("div");
			c.className="w3-container w3-text-pale-blue";
			var p = document.createElement("p");
			p.innerHTML = newURL[url].comments[i].body;
			
			
			c.appendChild(p);
			commentsBox.appendChild(c);
		}
	  }
    }
  }
}
browser.runtime.onMessage.addListener(handleMessage);
