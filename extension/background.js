console.log("Background");

function handleMessage(request, sender, sendResponse) {
	var responseObject = JSON.parse('{"result":true, "count":42}');
	//console.log("Message from the content script: " + request.data);
	var httpRequest = new XMLHttpRequest();
	httpRequest.open("POST","http://sandbox1.datadogsanalytics.com/serverResponse.php",true);
	httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	httpRequest.send("links="+request.data);
	httpRequest.onload = function(){
		responseObject = JSON.parse(this.responseText);
		console.log(responseObject);
		sendResponse(request.data);
		browser.tabs.query({active: true, currentWindow: true}, function(tabs) {
			browser.tabs.sendMessage(tabs[0].id, responseObject, function() {
			  console.log("Message sent to content script.");
			});
		  });
	}
	//Not currently in use, but keep it.
	//sendResponse(responseObject);
}


browser.runtime.onMessage.addListener(handleMessage);
