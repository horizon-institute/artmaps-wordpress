/* Namespace: ArtMaps.Util */
ArtMaps.Util = ArtMaps.Util || {};

ArtMaps.Util.RunOnce = function() {
    var timeoutID = false;
    this.run = function(handler) {
        this.runAfter(handler, 0);
    };
    this.runAfter = function(handler, timeout) {
        if(timeoutID != false)
            window.clearTimeout(timeoutID);
        timeoutID = window.setTimeout(function() {
            handler();
            timeoutID = false;
        }, timeout);
    };
};

ArtMaps.Util.browserLocation = function(success, failure) {
    function fallback() {
        if(google.loader.ClientLocation) {
            success(new google.maps.LatLng(
                    google.loader.ClientLocation.latitude,
                    google.loader.ClientLocation.longitude));
            return;
        } 
        jQuery.ajax({
            "type": "GET",
            "url": "http://api.ipinfodb.com/v3/ip-city/?format=json&key=" 
                    + ArtMapsConfig.IpInfoDbApiKey,
            "async": false,
            "dataType": "jsonp",
            "success": function(data) {
                success(new google.maps.LatLng(data.latitude, data.longitude));
            },
            "error": function() { failure(); }
        });
    };
    
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
                function(pos) {
                    success(new google.maps.LatLng(
                            pos.coords.latitude, pos.coords.longitude));
                },
                function(err) { fallback(); }
        );
    } else { fallback(); }
};

ArtMaps.Util.toIntCoord = function(f) {
    return parseInt(f * Math.pow(10, 8));
};

ArtMaps.Util.toFloatCoord = function(i) {
    return parseFloat(i) / Math.pow(10, 8);
};

ArtMaps.Util.actionArraySort = function(a, b) {
    return a.timestamp - b.timestamp;
};
