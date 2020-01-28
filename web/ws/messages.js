// Web socket server for private messaging functions
// Listens on TCP port 9000

const fs = require('fs');
const https = require('https');
const WebSocket = require('ws');
const { spawn, exec } = require('child_process');
const mysql = require('mysql');

const server = https.createServer({
    cert: fs.readFileSync('/etc/letsencrypt/live/datadogsanalytics.com/fullchain.pem'),
    key: fs.readFileSync('/etc/letsencrypt/live/datadogsanalytics.com/privkey.pem')
});
const wss = new WebSocket.Server({ server });
Clients = [];

// Establish MySQL connection
const con = mysql.createConnection({
    host: "localhost",
    user: "datadogs",
    password: "DataDogs2020CSUB",
    database: "analytics"
});
con.connect(function(err) {
    if (err) throw err;
}); 

wss.on('connection', function connection(ws, request, client) {
    //console.log(ws);

    // Handle incoming messages
    ws.on('message', function incoming(message) {
        messageObject = JSON.parse(message);

        // Handle connection message (send user chat history)
        if (messageObject.isConnectionMessage === true) {
            var senderid, targetid;
            console.log("User '" + messageObject.sender + "' connected from " + ws._socket.remoteAddress);

            // Notify client of successful connection in chat
            var chatMessage = new Object();
            chatMessage.isUserMessage = true;
            chatMessage.sender = "System";
            chatMessage.body = "Connected to chat subsystem.";
            ws.send(JSON.stringify(chatMessage));
            console.log("    Sent welcome message to client");

            // Fetch and send chat log history to client (uses stored procedure in database)
            var sqlquery = "CALL FetchMessages('" + messageObject.sender + "','" + messageObject.target + "');";
            con.query(sqlquery, function (err, result) {
                if (err) throw err;
                result[0].forEach(row => {
                    chatMessage.isUserMessage = true;
                    chatMessage.sender = row.Sender;
                    chatMessage.body = row.message;
                    chatMessage.timestamp = row.sent_at;
                    ws.send(JSON.stringify(chatMessage));
                });
            });
        }

        // Handle user chat message
        if (messageObject.isUserMessage === true) {
            console.log("User chat message received from '" + messageObject.sender + "'");

            // Insert user chat message to database (uses stored procedure in database)
            var sqlquery = "CALL SendMessage('" + messageObject.sender + "', '" + messageObject.target + "','" + messageObject.body + "')";
            con.query(sqlquery, function (err, result) {
                if (err) throw err;
                console.log("Result: " + result);
            });

            // Write user chat message out to the target user (if connected)
            // NEED TO UPDATE THIS TO SELECT ONLY THE CORRECT TARGET USER
            wss.clients.forEach(function each(client) {
                if ((client.session_number === messageObject.session) && (client.readyState === WebSocket.OPEN)) {
                    client.send(JSON.stringify(messageObject));
                }
            });
        }
    });
});

// Listen for connections on TCP port 9000
server.listen(9000,"0.0.0.0");
