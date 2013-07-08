/* Namespace: ArtMaps.UI */
ArtMaps.Map = ArtMaps.Map || {};
ArtMaps.Map.UI = ArtMaps.Map.UI || {};

ArtMaps.Map.UI.Marker = function(object, location) {
    var marker = new google.maps.Marker({
        "position": new google.maps.LatLng(location.Latitude, location.Longitude)
    });
    marker.Location = location;
    marker.ObjectOfInterest = object;
    return marker;
};

ArtMaps.Map.UI.formatMetadata = function(object, metadata) {
    return jQuery(document.createElement("div"));
};
