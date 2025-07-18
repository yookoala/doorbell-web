<!DOCTYPE html>
<html>
<head>
    <title>Doorbell</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #eee;
        }
        .door-container {
            position: relative;
            width: calc(100% - 2em);
            max-width: 800px;
            aspect-ratio: 800 / 1000;
            background-image: url('/assets/door.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            display: grid;
            grid-template-columns: repeat(100, 1fr);
            grid-template-rows: repeat(100, 1fr);
        }
        .doorbell-button {
            grid-column: 85 / span 5;
            grid-row: 48 / span 5;
            background: none;
            border: none;
            padding: 0;
            font-size: 2.5rem;
            cursor: pointer;
            color: #f1c40f;
            text-shadow: 0 0 5px #f1c40f, 0 0 10px #f1c40f;
            transition: transform 0.1s ease;
            outline: none;
        }
        .doorbell-button:hover {
            color: #f39c12;
        }
        .doorbell-button:active {
            transform: scale(0.9);
        }
        @media (max-width: 600px) {
            .doorbell-button {
                font-size: 1.8rem;
            }
        }
        @media (max-width: 400px) {
            .doorbell-button {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="door-container">
        <button id="ringButton" class="doorbell-button">🔔</button>
    </div>
    <audio id="doorbellSound" src="/assets/doorbell.mp3"></audio>

    <script>
        const ringButton = document.getElementById('ringButton');
        const doorbellSound = document.getElementById('doorbellSound');
        let lastCheckTime = 0;

        ringButton.addEventListener('click', () => {
            fetch('/api/trigger')
                .then(response => response.text())
                .then(data => console.log(data));
        });

        function listenForEvents() {
            const eventSource = new EventSource(`/api/sse?last_check_time=${lastCheckTime}`);
            eventSource.onmessage = function(event) {
                if (event.data.startsWith('{')) {
                    const data = JSON.parse(event.data);
                    if (data.ring_time) {
                        lastCheckTime = data.ring_time;
                        doorbellSound.play();
                    }
                }
                // Reconnect after a short delay
                setTimeout(listenForEvents, 1000);
                eventSource.close();
            };
            eventSource.onerror = function() {
                // Reconnect on error
                setTimeout(listenForEvents, 1000);
                eventSource.close();
            };
        }

        listenForEvents();
    </script>
</body>
</html>
