/* Namespace: ArtMaps.UI */
ArtMaps.UI = ArtMaps.UI || {};

ArtMaps.UI.Marker = function(location, map) {
    var marker = new google.maps.Marker({
        "position": new google.maps.LatLng(location.Latitude, location.Longitude)
    });
    marker.location = location;
    return marker;
};
