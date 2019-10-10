console.log("START");
document.body.style.border = "5px solid red";
var x = document.links.length;
var data = buildRequestString(x);
var linksObject = null;
var infoBox = createInfoBox();
function handleResponse(message) {
  //console.log(message);
}
function handleError(error) {
  //console.log(`Error: ${error}`);
}
browser.runtime.onMessage.addListener(function(message){
  linksObject = message;
  console.log(message);
});

function notifyBackgroundPage(e) {
  var sending = browser.runtime.sendMessage({
    "data": data
  });
  //We're not using responses at the moment, this may be useful later.
  //sending.then(handleResponse, handleError);  
}

window.onload = function(e){
	notifyBackgroundPage(e);
}

function buildRequestString(links){
    var requestString = "";
    for(var i=0;i<links;i++){
        var li = "l"+i;
        var string = document.links.item(i).href;
        string = string.split(/[&?#$-+]/);
        requestString+=string[0]+"^";
    }
    requestString = requestString.substring(0, requestString.length - 1);
    return requestString;
}
//TODO: Needs to work with dynamically added elements as well.
var domLinks = document.getElementsByTagName("a");
console.log("EEE");
for(var i = 0;i<domLinks.length;i++){
    domLinks[i].addEventListener("mouseover", function(e){
        if(linksObject){
            for(var i =0;i<linksObject.links.length;i++){
                if(linksObject.links[i].url==e.target.href.split(/[&?#$-+]/)){
                    console.log(linksObject.links[i].value);
                    moveInfoBox(linksObject.links[i].value,e.clientX,e.clientY);
                    break;
                }
            }
        }
    });
}


function createInfoBox(){
  var infoBox = document.createElement("div");
  infoBox.style.width = "150px";
  infoBox.style.color = "red";
  infoBox.style.height= "100px";
  infoBox.style.backgroundColor = "white";
  infoBox.style.position = "absolute";
  infoBox.style.border="1px black solid";
  infoBox.style.left = 300+"px";
  infoBox.style.top = 300+"px";
  infoBox.style.display = "none";
  document.body.appendChild(infoBox);
  return infoBox;
}

function moveInfoBox(value,x,y){
  infoBox.style.display = "block";
  infoBox.innerHTML= "Site Score: "+value;
  infoBox.style.left = x-75+"px";
  infoBox.style.top = y-120+"px";
  console.log("HERE");
}


console.log("END");