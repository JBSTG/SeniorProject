document.body.style.border = "5px solid red";
var x = document.links.length; 
var data = buildRequestString(x);
var response = null;

//TODO: Needs to work with dynamically added elements as well.
var domLinks = document.getElementsByTagName("a");
for(var i = 0;i<domLinks.length;i++){
    domLinks[i].addEventListener("mouseover", function(e){
        if(response){
            for(var i =0;i<response.links.length;i++){
                if(response.links[i].url==e.target.href.split(/[&?#$-+]/)){
                    console.log(response.links[i].value);
                    break;
                }
            }
        }
    });
}

//TODO: make this smaller. Needs a version for dynamic links.
var httpRequest = new XMLHttpRequest();
httpRequest.open("POST","http://sandbox1.datadogsanalytics.com/serverResponse.php",true);
httpRequest.onload = function(){
    console.log(this.responseText);
    response = JSON.parse(this.responseText);
    console.log(response);
    //console.log(JSON.parse(this.responseText));
}
httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
httpRequest.send("links="+data);



//Libs//////////////////////////////////////////////////////////////////////////////////////////////
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