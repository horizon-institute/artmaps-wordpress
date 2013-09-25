self.addEventListener(
        "message",
        function (e) {
            var req = new XMLHttpRequest();
            req.open("GET", e.data, false);
            req.setRequestHeader("Accept", "application/json");
            req.setRequestHeader("Content-Type", "application/json; charset=utf-8");
            req.send(null);
            self.postMessage(JSON.parse(req.responseText));
        },
        false);