// Rewrite of Node.js code for handling web scraper events
// - State machine design to control request throttling
//   and improve result posting time to users
// Updated 2/26/20 KT

const fs = require('fs');
const https = require('https');
const WebSocket = require('ws');
//const fetch = require('node-fetch');
var mysql = require('mysql');
const jsdom = require("jsdom");
const { JSDOM } = jsdom;
var request = require('request');
const { spawn, exec } = require('child_process');

var maxDepth = 1;       // Maximum depth that a crawl can go

var crawlList = [];     // Array of URLs to be crawled
var scoreList = [];     // Array of URLs to be scored
var crawledList = [];   // Array of URLs we have already crawled (not to be crawled again)
var scoredList = [];    // Array of URLs we have already scored (not to be scored again)

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
            // Add the requested URL to our scoring list
            scoreList.push(url);
            scoredList.push(url);

            // Add the requested URL to our crawling list
            var target = new Object();
            target.url = url;
            target.depth = 0;
            crawlList.push(target);
            crawledList.push(url);

            // Start servicing the scoring and crawling queues
            serviceQueues();
        }
    });
});

// Listen for connections on TCP 8080
server.listen(8080,"0.0.0.0");

// Check for URLs that need to be scored or crawled
// If both queues are empty, or an active scoring AND
// crawling operation is present, do nothing.
// This ensures that only one scoring and one crawling
// operation can happen simultaneously.

function serviceQueues() {
    console.log("  Entered serviceQueues(), " + crawlList.length + " URLs in the crawling queue, " + scoreList.length + " URLs in the scoring queue..");
    // Start a scoring job if URLs in queue and a scoring job is not already running
    if (activeScoring == false && scoreList.length > 0) {
        scorePage(); 
    // Else start a crawling job if URLs in queue and a crawling job is not already running
    } else if (activeCrawling == false && crawlList.length > 0) {
        crawlPage();
        serviceQueues();
    }        
    // Else return without doing anything (wait to be called again)
}

function scorePage() {
    // Set flag
    activeScoring = true;
    console.log("    Scoring flag set");
    var URL = scoreList.pop();
    console.log("    Want to score the URL:  " + URL);

    console.log("      Executing:  plugin-submit-url.php url=" + URL);
    // Clean URL for exec()
    var cleanURL = URL.replace("(", "\\(");
    cleanURL = cleanURL.replace(")", "\\)");

    // Score page and wait for result
    exec("php /var/www/html/api/plugin-submit-url.php url=" + cleanURL, function (error, stdout, stderr) {
        //console.log("      PHP completed: " + stdout);
        console.log("      Result received from API:" + stdout);
        // Write result to websocket
        var apiResult = JSON.parse(stdout);
        wss.clients.forEach(function each(client) {
            if (client.readyState === WebSocket.OPEN && !(apiResult.page_title === "Website Unavailable")) {     // TODO: ADD DOMAIN BACK IN HERE
                var result = new Object();
                result.isScrapeResult = true;
                result.url = apiResult.url;
                result.page_title = apiResult.page_title;
                // Exception handling here in case client disconnected
                try {
                    client.send(JSON.stringify(result));
                    console.log("      Result pushed to client");
                }
                catch (error) {
                    console.log("      Error--Websocket client disconnected");
                }
            }
        });
        // Release flag
        activeScoring = false;
        console.log("      Scoring flag released");
        // Return to core state
        serviceQueues();
    });
}

function crawlPage() {
    activeCrawling = true;      // Set flag
    var target = crawlList.pop();
    console.log("    Want to crawl the URL:  " + target.url + ", depth = " + target.depth);

    // Determine domain name
    domain = target.url.split("/")[0] + "//" + target.url.split("/")[2];

    // Fetch HTML
    request(target.url, function(error, response, body) {
        if (body) {
            console.log("      " + body.length + " bytes fetched");
            const dom = new JSDOM(body);
            console.log("      " + dom.window.document.querySelectorAll("a").length + " anchor elements found");
            for (var i = 0; i < dom.window.document.querySelectorAll("a").length; i++) {
                var currentLink = dom.window.document.querySelectorAll("a")[i].toString();

                // Strip any arguments in the URL
                currentLink = currentLink.split("?")[0];

                // Strip any anchors in the URL
                currentLink = currentLink.split("#")[0];

                // Fix link format if it begins with //
                if (currentLink.startsWith("//"))
                    currentLink = "https:" + currentLink;
     
                // Save domain portion of the current link
                var currentLinkDomain = currentLink.split("/")[0] + "//" + currentLink.split("/")[2];

                // Ignore non-HTML links
                var checkLink = currentLink.toLowerCase();
                if (checkLink.startsWith("mailto:") || checkLink.endsWith(".doc") || checkLink.endsWith(".docx") || checkLink.endsWith(".gif") || checkLink.endsWith(".iso") || checkLink.endsWith(".jpeg") || checkLink.endsWith(".jpg") || checkLink.endsWith(".mkv") || checkLink.endsWith(".mp3") || checkLink.endsWith(".mp4") || checkLink.endsWith(".mpg") || checkLink.endsWith(".msi") || checkLink.endsWith(".pdf") || checkLink.endsWith(".png") || checkLink.endsWith(".tar.gz") || checkLink.endsWith(".tar.xz") || checkLink.endsWith(".tgz") || checkLink.endsWith(".torrent") || checkLink.endsWith(".wmv") || checkLink.endsWith(".xls") || checkLink.endsWith(".xlsx") || checkLink.endsWith(".zip")) {

                // Ignore links containing javascript
                } else if (currentLink.includes("javascript:")) {

                // Ignore links to other sites
                } else if (currentLink.startsWith("http") && !(domain === currentLinkDomain)) {
                    console.log("      Ignoring offiste link:  " + currentLink);
                    console.log("        targetDomain = " + domain + ", currentLinkDomain = " + currentLinkDomain);

                // Handle absolute links
                } else if (currentLink.startsWith("http://") || currentLink.startsWith("https://")) {
                    var newLink = currentLink;
                    // Strip trailing slash for consistency
                    if (newLink.endsWith("/")) { newLink = newLink.substr(0, newLink.length - 1); }
                    // Add link to scoring queue unless we've already scored it
                    if (!scoredList.contains(newLink)) {
                        console.log("      Adding URL " + newLink + " to scoring queue");
                        scoreList.push(newLink);
                        scoredList.push(newLink);       // Mark as scored (or to be scored)
                    }
                    // Add link to crawling queue unless we've already crawled it
                    if (!crawledList.contains(newLink)) {
                        // Add the URL to our crawling list
                        var newTarget = new Object();
                        newTarget.url = newLink;
                        newTarget.depth = target.depth + 1;
                        if (newTarget.depth <= maxDepth) {
                            console.log("      Adding URL " + newLink + " to crawling queue");
                            crawlList.push(newTarget);
                            crawledList.push(newLink);      // Mark as crawled (or to be crawled)
                        }
                    }
    
                // Handle relative links pointing to a different folder
                } else if (currentLink.startsWith("/")) {
                    var newLink = domain + currentLink;
                    // Strip trailing slash for consistency
                    if (newLink.endsWith("/")) { newLink = newLink.substr(0, newLink.length - 1); }
                    // Add link to scoring queue unless we've already scored it
                    if (!scoredList.contains(newLink)) {
                        console.log("      Adding URL " + newLink + " to scoring queue");
                        scoreList.push(newLink);
                        scoredList.push(newLink);       // Mark as scored (or to be scored)
                    }
                    // Add link to crawling queue unless we've already crawled it
                    if (!crawledList.contains(newLink)) {
                        // Add the URL to our crawling list
                        var newTarget = new Object();
                        newTarget.url = newLink;
                        newTarget.depth = target.depth + 1;
                        if (newTarget.depth <= maxDepth) {
                            console.log("      Adding URL " + newLink + " to crawling queue");
                            crawlList.push(newTarget);
                            crawledList.push(newLink);      // Mark as crawled (or to be crawled)
                        }
                    }

                // Handle relative links pointing to the same folder
                } else {
                    var newLink = domain + "/" + currentLink;
                    // Strip trailing slash for consistency
                    if (newLink.endsWith("/")) { newLink = newLink.substr(0, newLink.length - 1); }
                    // Add link to scoring queue unless we've already scored it
                    if (!scoredList.contains(newLink)) {
                        console.log("      Adding URL " + newLink + " to scoring queue");
                        scoreList.push(newLink);
                        scoredList.push(newLink);       // Mark as scored (or to be scored)
                    }
                    // Add link to crawling queue unless we've already crawled it
                    if (!crawledList.contains(newLink)) {
                        // Add the URL to our crawling list
                        var newTarget = new Object();
                        newTarget.url = newLink;
                        newTarget.depth = target.depth + 1;
                        if (newTarget.depth <= maxDepth) {
                            console.log("      Adding URL " + newLink + " to crawling queue");
                            crawlList.push(newTarget);
                            crawledList.push(newLink);      // Mark as crawled (or to be crawled)
                        }
                    }
                }
            }
        }

        // Release flag
        activeCrawling = false;
        console.log("      Crawling flag released");

        // Return to core state
        serviceQueues();
    });
}

// Used to determine if a URL is a member of the crawledList[] and scoredList[] arrays
Array.prototype.contains = function(obj) {
    var i = this.length;
    while (i--) {
        if (this[i] == obj) {
            return true;
        }
    }
    return false;
}

