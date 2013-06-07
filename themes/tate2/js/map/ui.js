/* Namespace: ArtMaps.UI */
ArtMaps.UI = ArtMaps.UI || {};

ArtMaps.UI.Marker = function(object, location) {
    var marker = new google.maps.Marker({
        "position": new google.maps.LatLng(location.Latitude, location.Longitude)
    });
    marker.Location = location;
    marker.ObjectOfInterest = object;
    return marker;
};

ArtMaps.UI.formatMetadata = function(object, metadata) {
    return $(document.createElement("div"));
};
