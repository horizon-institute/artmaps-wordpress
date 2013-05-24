self.addEventListener(
        "message",
        function (e) {
            var req = new XMLHttpRequest();
            req.open("GET", e.data, false);
            req.send(null);
            self.postMessage(JSON.parse(req.responseText));
        },
        false);