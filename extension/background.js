console.log("Background");
/*
function handleMessage(request, sender, sendResponse) {
	var responseObject = null;
	var httpRequest = new XMLHttpRequest();
	var url = "https://datadogsanalytics.com/api/plugin-submit-multiple.php";
	url = url+="?list="+JSON.stringify(request.data.links);
	httpRequest.open("POST",url,true);
	httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	httpRequest.send();
	
	httpRequest.onload = function(){
		
		console.log(this.responseText);
		responseObject = JSON.parse(this.responseText);
		console.log(responseObject);
		sendResponse(request.data);
		browser.tabs.query({active: true, currentWindow: true}, function(tabs) {
			browser.tabs.sendMessage(tabs[0].id, responseObject, function() {
			  console.log("Message sent to content script.");
			});
		  });
		 
		 console.log(this.responseText);
		 console.log(JSON.parse(this.responseText));
		}
	
	//Not currently in use, but keep it.
	//sendResponse(responseObject);
}
*/
browser.menus.create({
  id: "explore-link",
  title: "Explore Link",
  contexts: ["link"]
}, onCreated);
function onCreated(){
  console.log("created");
}
browser.menus.onClicked.addListener(function(info,tab){
  browser.sidebarAction.open();
  browser.tabs.query({active: true, currentWindow: true}, function(tabs) {
	var responseObject = new Object();
	responseObject.context = "explore";
	console.log(info);
	responseObject.URL = info.linkUrl;
	/*
    browser.tabs.sendMessage(tabs[0].id, responseObject, function() {
	  console.log("Message sent to content script.");
	});
	*/
	browser.runtime.sendMessage(responseObject);
  });
});
//browser.runtime.onMessage.addListener(handleMessage);
