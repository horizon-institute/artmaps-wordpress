function doGet(e) {
    var req = new XMLHttpRequest();
    req.open("GET", e.data, false);
    req.send(null);
    self.postMessage(JSON.parse(req.responseText));
}
self.addEventListener("message", doGet, false);
