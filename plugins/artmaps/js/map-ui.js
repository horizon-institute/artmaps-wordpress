/* Namespace: ArtMaps.UI */
ArtMaps.Map = ArtMaps.Map || {};
ArtMaps.Map.UI = ArtMaps.Map.UI || {};

ArtMaps.Map.UI.Marker = function(object, location) {
    this.setPosition(new google.maps.LatLng(location.Latitude, location.Longitude));
    this.Location = location;
    this.ObjectOfInterest = object;
};
ArtMaps.Map.UI.Marker.prototype = new google.maps.Marker();
ArtMaps.Map.UI.Marker.prototype.constructor = ArtMaps.Map.UI.Marker;

ArtMaps.Map.UI.formatMetadata = function(object, metadata) {
    return jQuery(document.createElement("div"));
};
