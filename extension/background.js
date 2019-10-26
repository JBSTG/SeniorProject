console.log("Background");

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


browser.runtime.onMessage.addListener(handleMessage);
