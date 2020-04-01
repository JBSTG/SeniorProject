var currentArticleId = -1;
var commentServerConnection2 = new WebSocket("wss://www.datadogsanalytics.com:9000");

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

	  currentArticleId = newURL[url].id;
      document.getElementById("pageTitle").innerHTML = newURL[url].title;
      document.getElementById("siteScore").innerHTML = "Site Score: " + newURL[url].site_score+"%";
      document.getElementById("commentsHeader").style.visibility="visible";
      if(newURL[url].page_score==1){
		document.getElementById("pageScore").innerHTML = "This page is clickbait";
		document.getElementById("pageTitle").parentElement.classList.add("w3-deep-orange");
	  }else{
		document.getElementById("pageScore").innerHTML ="This page is not clickbait";
		document.getElementById("pageTitle").parentElement.classList.add("w3-blue");
	  }
	  
	  var commentsBox = document.getElementById("infoBoxComments");
	  commentsBox.innerHTML = "";
	  if(newURL[url].comments.length==0){
		commentsBox.innerHTML = "<div class=\"w3-container w3-text-blue-gray\"><p>No comments yet.</p></div>";
      }else{
		for(var i = 0;i<newURL[url].comments.length;i++){
			addComment(newURL[url].comments[i].username,newURL[url].comments[i].body,newURL[url].comments[i].date);
		}
	  }
    }
  }
}

commentServerConnection2.onopen = function(e) {
	console.log("Connected to comment server.");
	document.getElementById("submitCommentButton2").addEventListener("click",function(){
	  var commentMessage = new Object();
	  commentMessage.isCommentMessage = true;
	  commentMessage.body = document.getElementById("commentEntry").value;
	  document.getElementById("commentEntry").value = "";
	  commentMessage.user = 9;
	  commentMessage.username = "Anonymous";
	  commentMessage.article = currentArticleId;
	  console.log(commentMessage);
	  commentServerConnection2.send(JSON.stringify(commentMessage));
  });
}
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

commentServerConnection2.onmessage = function(message) {
	var msg = JSON.parse(message.data);
	if(msg.article==currentArticleId){
	  addComment(msg.username,msg.body,msg.date);
	}
};
browser.runtime.onMessage.addListener(handleMessage);
