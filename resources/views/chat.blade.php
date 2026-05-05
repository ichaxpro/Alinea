<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat</title>
    <style>
        body { margin: 0; font-family: Arial; }
        .container { display: flex; height: 100vh; }

        .sidebar {
            width: 25%;
            border-right: 1px solid #ccc;
            padding: 10px;
        }

        .chat-item {
            padding: 10px;
            background: #eee;
            margin-top: 10px;
            border-radius: 8px;
        }

        .chat-area {
            width: 75%;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        .chat-body {
            flex: 1;
            padding: 10px;
        }

        .chat-message {
            background: #d1e7ff;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 8px;
            width: fit-content;
        }

        .chat-input {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ccc;
        }

        .chat-input input {
            flex: 1;
            padding: 10px;
        }

        .chat-input button {
            padding: 10px;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="sidebar">
        <h3>Pesan</h3>
        <div class="chat-item">User 1</div>
        <div class="chat-item">User 2</div>
    </div>

    <div class="chat-area">
        <div class="chat-header">User 1</div>

        <div class="chat-body" id="chatBody"></div>

        <div class="chat-input">
            <input type="text" id="messageInput">
            <button onclick="sendMessage()">Kirim</button>
        </div>
    </div>

</div>

<script>
function sendMessage() {
    let input = document.getElementById("messageInput");
    let chatBody = document.getElementById("chatBody");

    if (input.value.trim() === "") return;

    let msg = document.createElement("div");
    msg.className = "chat-message";
    msg.innerText = input.value;

    chatBody.appendChild(msg);

    input.value = "";
}
</script>

</body>
</html>