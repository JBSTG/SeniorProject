<!-- Private message page, to be used as a pop-up window -->

<?php
    ini_set("session.cookie_httponly",1);
    session_start();

    // Throw an error if the user isn't logged in
    if (!isset($_SESSION["username"]))
        die("Your session has expired.  Please log in again");

    // Throw an error if the target user isn't specified
    if (!isset($_REQUEST["target"]))
        die("Target user must be passed in as an argument");
?>

<html>
<head>
    <title>User Chat</title>
</head>

<body>

<div class="chat">
    <!--- CHAT WINDOW IN THIS DIV -->
    <textarea id="chatlog" rows=26 cols=80></textarea>
    <div class="chatControls">
        <input type="text" id="message" placeholder="Message">
        <button id="sendMessage">Send</button>
    </div>
</div>

<script>
    var url = 'wss://datadogsanalytics.com:9000';
    var messageButton = document.getElementById("sendMessage");
    var message = document.getElementById("message");
    // Pull sender username from session cookie
    var sender = '<?php echo $_SESSION["username"]; ?>';
    var target = '<?php echo $_REQUEST["target"]; ?>';
    var connection = createSocket();
    var connectionOpen = false;

    // Handle send message button click
    messageButton.addEventListener ("click", function() {
        var messageObject = new Object();
        messageObject.isUserMessage = true;
        // NEED TO CHANGE THESE PARAMETERS TO MATCH THE NEW CHAT SYSTEM
        messageObject.sender = document.getElementById("username").value;
        messageObject.body = message.value;
        messageObject.session = session;
        connection.send(JSON.stringify(messageObject));
    });

    function createSocket(){
        connection = new WebSocket(url);

        // Send a connect message, triggering server to send us the chat history
        connection.onopen = () => {
            var connectionObject = new Object();
            connectionObject.isConnectionMessage = true;
            connectionObject.sender = sender;
            connectionObject.target = target;
            connection.send(JSON.stringify(connectionObject));
            connectionOpen = true;
        }

        // Handle incoming messages
        connection.onmessage = (message) =>{
            var msg = JSON.parse(message.data);
            console.log(msg);
            // Handle incoming user message
            if(msg.isUserMessage){
                chatlog.value += msg.sender + " [" + msg.timestamp + "]: " + msg.body + "\n";
            }
        }

        // Log connection errors to browser console
        connection.onerror = error => {
            console.log("error");
        }

        return connection;
    }
</script>


</body>
</html>
