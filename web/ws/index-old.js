const fs = require('fs');
const https = require('https');
const WebSocket = require('ws');
const fetch = require('node-fetch');
var mysql = require('mysql'); 
const jsdom = require("jsdom");
const { JSDOM } = jsdom;
var req = require('request');
const { spawn, exec } = require('child_process');

const server = https.createServer({
  cert: fs.readFileSync('/etc/letsencrypt/live/datadogsanalytics.com/fullchain.pem'),
  key: fs.readFileSync('/etc/letsencrypt/live/datadogsanalytics.com/privkey.pem')
});
const wss = new WebSocket.Server({ server });

var msConnection = mysql.createConnection({
  host:'localhost',
  user:'datadogs',
  database:'analytics',
  password:'DataDogs2020CSUB'
});

var queuedDomains = [];
var queuedURLs = [];

msConnection.connect(function(err) {
  if (err){
    throw err;
  }
});

setInterval(scrapeDomain,10000);
setInterval(scrapeURL,10000);

wss.on('connection', function connection(ws) {
  ws.on('message', function incoming(message) {
    var messageObject = JSON.parse(message);
    if(messageObject.isScrapeSubscription){
      ws.subscription = messageObject.target;
      var responseObject = new Object();
      responseObject.isScrapeContent = true;
      responseObject.target = ws.subscription;
      this.send(JSON.stringify(responseObject));
      queuedDomains[ws.subscription] = 1;
    }
  });
});

server.listen(8080,"0.0.0.0");
//The primary purpose of this function is to provide us with articles for domains.
function scrapeDomain(){
  if(Object.keys(queuedDomains).length>0){
    //Iterate through domains
    //check to see if it hasn't been scraped in the last 3 seconds
    var keys = Object.keys(queuedDomains);
    for(var i = 0;i<keys.length;i++){
      var currentDomain = keys[i];
      queryDomainReady(keys[i]).then(function(readyStatus){
        if(readyStatus){
          getStartPointForDomain(currentDomain).then(function(result){
            if(result.length==0){
              //We just need to do the homepage.
              getLinksFromPage(currentDomain,currentDomain).then(()=>{
                //Add the page to the database.
                analyzeURL(currentDomain).then(function(){
                  //Add the domain to the database.
                  createDomain(currentDomain);
                });
              });
            }else{
              //console.log(result[0].Domain);
              //Add the page's links, put them in the queue to be processed.
              var URL = result[0].URL;
              var domain = result[0].Domain;
              getLinksFromPage(result[0].URL,result[0].Domain).then(()=>{
                setRootVisited(URL).then(function(){
                  updateDomainScrapeTime(domain);
                });
              });
            }
          });
        }
      });
    }
  }
}

function scrapeURL(){
  if(Object.keys(queuedURLs).length>0){
    var keys = Object.keys(queuedURLs);
    for(var i=0;i<keys.length;i++){
      console.log("O:"+keys[i]);
      //console.log(currentLink.URL);
      let currentLink = queuedURLs[keys[i]];
      queryDomainReady(currentLink.domain).then(function(readyStatus){
        let ready = readyStatus;
        console.log(ready);
        if(ready==true){
        updateDomainScrapeTime(currentLink.domain).then(()=>{
          getLinksFromPage(currentLink.URL).then(function(){
            analyzeURL(currentLink.URL).then(function(output){
              console.log("I:"+currentLink.URL);
              broadcastAnalysis(output,currentLink.domain).then(()=>{
                  delete queuedURLs[currentLink];
                });
              });
            });
          });
        }else{
          console.log("Not Ready");
        }
      });
    }
  }
}

function broadcastAnalysis(result,domain){
  return new Promise(function(resolve,reject){
    wss.clients.forEach(function each(client) {
      if (client.readyState === WebSocket.OPEN && client.subscription == domain) {
        client.send(result);
      }
    });
    resolve();
  });
}

function queryDomainReady(domain){
  return new Promise(function(resolve,reject){
    msConnection.query("SELECT Last_Analyzed FROM domain_pagescore WHERE Domain='"+domain+"'", function (error, result, fields) {
      if (error) {
        //throw err;
      }
      //Compare last scrape date to current time.
      if(result.length==0||result==undefined){
        resolve(true);
      }else{
        var raw_date = String(result[0].Last_Analyzed);
        var d1 = Date.parse(raw_date);
        var d2 = Date.parse(new Date());
        var ready = false;
        console.log("Current Time: "+d2+" Last Time: "+d1);
        resolve((d2-d1)>3000);
      }
  })
});
}

function getStartPointForDomain(domain){
  return new Promise(function(resolve,reject){
    msConnection.query("SELECT * FROM pages WHERE Domain = '"+domain+"' AND Has_Been_Root = 0 ORDER BY RAND() LIMIT 1",function(error, result, fields){
      if(error){
      }
      resolve(result);
    });
  });
}

function getLinksFromPage(URL,domain){
  return new Promise(function(resolve,reject){
    if(!URL.includes("http")&&!URL.includes("www")){
      URL="http://"+URL;
    }
    fetch(URL).then(resp=> resp.text()).then(function(body){
      //console.log(body);
      const dom = new JSDOM(body);
      for(var i = 0;i<dom.window.document.querySelectorAll("a").length;i++){
        var currentLink = dom.window.document.querySelectorAll("a")[i].toString();
        if((currentLink.includes(domain)&&currentLink.includes("http"))||(currentLink.includes(domain)&&currentLink.includes("www"))){
          //console.log(currentLink);
          if(!(currentLink in queuedURLs)){
            var preparedURL = new Object();
            preparedURL.URL = currentLink;
            preparedURL.domain = domain;
            queuedURLs[currentLink] = preparedURL;
          }
        }
      }
      resolve();
    });
  });
}
function updateDomainScrapeTime(domain){
  return new Promise(function(resolve,reject){
    msConnection.query("UPDATE domain_pagescore SET Last_Analyzed = NOW() where Domain='"+domain+"'",function(error,result,fields){
      resolve();
    });
  });
}
function setRootVisited(URL){
  return new Promise(function(resolve,reject){
    msConnection.query("UPDATE pages SET Has_Been_Root = 1 WHERE URL='"+URL+"'",function(error,result,fields){
      resolve();
    });
  });
}
function analyzeURL(URL){
  return new Promise(function(resolve,reject){
    var protocol="";
    if(!URL.includes("http")&&!URL.includes("www")){
      protocol="http://";
    }
    exec("php /var/www/html/api/plugin-submit-url.php url="+protocol+URL, function (error, stdout, stderr) {
      resolve(stdout);
    });
  });
}

function createDomain(domain){
  return new Promise(function(resolve,reject){
    msConnection.query("REPLACE INTO domain_pagescore (Domain,Last_Analyzed) VALUES('"+domain+"',NOW())",function(error,result,fields){
      resolve();
    });
  });
}

/*
updateDomainScrapeTime("aspca.org").then(()=>{
  setTimeout(()=>{
    queryDomainReady("aspca.org").then((res)=>{console.log(res)});
  },3500);
});
*/