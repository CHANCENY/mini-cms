<div class="container mt-lg-5">
    <div class="bg-light p-5">
        <div class="d-block">
            <h1>WebSocket Chat</h1>
            <input id="message" type="text" placeholder="Type a message">
            <button onclick="sendMessage()">Send</button>
            <ul id="messages"></ul>
        </div>
    </div>
</div>
<script>
    const ws = new WebSocket('ws://<?= ($content["socket"]["host"] ?? null). ":" . ($content["socket"]["port"] ?? null) ?>');

    ws.onopen = () => {
        console.log('Connected to WebSocket server');
    };

    ws.onmessage = (event) => {
        const messages = document.getElementById('messages');
        const message = document.createElement('li');
        message.textContent = event.data;
        messages.appendChild(message);
    };

    ws.onclose = () => {
        console.log('Disconnected from WebSocket server');
    };

    function sendMessage() {
        const input = document.getElementById('message');
        ws.send(input.value);
        input.value = '';
    }
</script>