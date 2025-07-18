<!DOCTYPE html>
<html>
<head>
    <title>Doorbell</title>
</head>
<body>
    <h1>Doorbell</h1>
    <button id="ringButton">Ring Doorbell</button>
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
