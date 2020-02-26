// Rewrite of Node.js code for handling web scraper events
// - Simplified code flow (less promises)
// - Added duplicate URL checking to avoid loops
// - Added trailing slash stripping to avoid duplicates
// Updated 2/24/20 KT

const fs = require('fs');
const https = require('https');
const WebSocket = require('ws');
//const fetch = require('node-fetch');
var mysql = require('mysql');
const jsdom = require("jsdom");
const { JSDOM } = jsdom;
var request = require('request');
const { spawn, exec } = require('child_process');

var urlList = [];       // Array of URLs to be scored
var visitedList = [];   // Array of URLs we have already scraped (not to be scraped again)

var activeCrawling = false;     // Global flag to track whether a crawling operation is active
var activeScoring = false;      // Global flag to track whether a scoring operation is active

// Create HTTPS server with existing datadogsanalytics.com SSL keys
const server = https.createServer({
  cert: fs.readFileSync('/etc/letsencrypt/live/datadogsanalytics.com/fullchain.pem'),
  key: fs.readFileSync('/etc/letsencrypt/live/datadogsanalytics.com/privkey.pem')
});
const wss = new WebSocket.Server({ server });

// Set up MySQL connection
var msConnection = mysql.createConnection({
  host:'localhost',
  user:'datadogs',
  database:'analytics',
  password:'DataDogs2020CSUB'
});
msConnection.connect(function(err) {
  if (err){
    throw err;
  }
});

// Handle incoming websocket connections
wss.on('connection', function connection(ws) {
  ws.on('message', function incoming(message) {
    var messageObject = JSON.parse(message);
    if(messageObject.isScrapeSubscription){
      var url = messageObject.target;
      // Strip trailing slash for consistency
      if (url.endsWith("/")) { url = url.substr(0, url.length - 1); }
      console.log("Received incoming scrape request for domain: " + url);
      // Add the requested URL to our scraping list
      urlList.push(url);
      // Get links from page
      getLinksFromPage(url);
      // Start servicing the URL queue
      serviceQueue();
    }
  });
});

// Listen for connections on TCP 8080
server.listen(8080,"0.0.0.0");

// Loop to service the urlList[] array
function serviceQueue() {
    while (urlList.length) {
        console.log("  Entered serviceQueue(), " + urlList.length + " URLs in the queue..");
        var URL = urlList.pop();
        console.log("    " + URL + " popped from queue");
        // TODO:  Add some delay here

        console.log("      Executing:  plugin-submit-url.php url=" + URL);
        // Score page and wait for result
        exec("php /var/www/html/api/plugin-submit-url.php url=" + URL, function (error, stdout, stderr) {
            console.log("    PHP completed: " + stdout);

            // Write result to websocket
            var apiResult = JSON.parse(stdout);
            wss.clients.forEach(function each(client) {
                if (client.readyState === WebSocket.OPEN) {     // TODO: ADD DOMAIN BACK IN HERE
                    var result = new Object();
                    result.isScrapeResult = true;
                    result.url = apiResult.url;
                    result.page_title = apiResult.page_title;
                    client.send(JSON.stringify(result));
                }
            });
        });
    }
};

function getLinksFromPage(URL) {
    // Determine domain name
    domain = URL.split("/")[0] + "//" + URL.split("/")[2];

    // Mark URL as visited
    visitedList.push(URL);

    console.log("  Entered getLinksFromPage(" + URL + ")  Domain is: " + domain);
    request(URL, function(error, response, body) {
        console.log("    " + body.length + " bytes fetched");
        const dom = new JSDOM(body);
        console.log("    " + dom.window.document.querySelectorAll("a").length + " anchor elements found");
        for (var i = 0; i < dom.window.document.querySelectorAll("a").length; i++) {
            var currentLink = dom.window.document.querySelectorAll("a")[i].toString();
 
            // Handle absolute links
            if (currentLink.startsWith("http://") || currentLink.startsWith("https://")) {
                var newLink = currentLink;
                // Strip trailing slash for consistency
                if (newLink.endsWith("/")) { newLink = newLink.substr(0, newLink.length - 1); }
                // Add link to queue unless we've already visited it
                if (!visitedList.contains(newLink)) {
                    console.log("    Adding URL " + newLink + " to crawl queue");
                    urlList.push(newLink);
                }
            // Ignore non-HTML links
            } else if (currentLink.startsWith("mailto:") || currentLink.endsWith(".gif") || currentLink.endsWith(".jpg") || currentLink.endsWith(".pdf") || currentLink.endsWith(".png")) {
            // Handle relative links pointing to a different folder
            } else if (currentLink.startsWith("/")) {
                var newLink = domain + currentLink;
                // Strip trailing slash for consistency
                if (newLink.endsWith("/")) { newLink = newLink.substr(0, newLink.length - 1); }
                // Add link to queue unless we've already visited it
                if (!visitedList.contains(newLink)) {
                    console.log("    Adding URL " + newLink + " to crawl queue");
                    urlList.push(newLink);
                }
            // Handle relative links pointing to the same folder
            } else {
                var newLink = domain + "/" + currentLink;
                // Strip trailing slash for consistency
                if (newLink.endsWith("/")) { newLink = newLink.substr(0, newLink.length - 1); }
                // Add link to queue unless we've already visited it
                if (!visitedList.contains(newLink)) {
                    console.log("    Adding URL " + newLink + " to crawl queue");
                    urlList.push(newLink);
                }
            }

        }
        // Mark this URL as visited
        visitedList.push(URL);
        // Service URL queue
        serviceQueue();
    });
}

// Used to determine if a URL is a member of the visitedList[] array
Array.prototype.contains = function(obj) {
    var i = this.length;
    while (i--) {
        if (this[i] == obj) {
            return true;
        }
    }
    return false;
}
