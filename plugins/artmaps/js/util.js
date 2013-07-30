/* Namespace: ArtMaps.Util */
ArtMaps.Util = ArtMaps.Util || {};

ArtMaps.Util.browserLocation = function(success, failure) {
    var ipInfoDb = function() {
        jQuery.ajax({
            "type": "GET",
            "url": "http://api.ipinfodb.com/v3/ip-city/?format=json&key=" 
                    + ArtMapsConfig.IpInfoDbApiKey,
            "async": false,
            "dataType": "jsonp",
            "success": function(data) {
                success(new google.maps.LatLng(data.latitude, data.longitude));
            },
            "error": failure
        });
    };    
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
                function(pos) {
                    success(new google.maps.LatLng(
                            pos.coords.latitude, pos.coords.longitude));
                },
                ipInfoDb
        );
    } else { ipInfoDb(); }
};

ArtMaps.Util.toIntCoord = function(f) {
    return parseInt(f * Math.pow(10, 8));
};

ArtMaps.Util.toFloatCoord = function(i) {
    return parseFloat(i) / Math.pow(10, 8);
};

ArtMaps.Util.boundingBox = function(origin, distance) {
    
  
};
