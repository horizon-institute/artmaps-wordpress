/* Namespace: ArtMaps.UI */
ArtMaps.UI = ArtMaps.UI || {};

ArtMaps.UI.SystemMarkerColor = "#ff0000";
ArtMaps.UI.UserMarkerColor = "#00EEEE";

ArtMaps.UI.InfoWindow = function(location) {

    var isOpen = false;
    var map = null;  
    var marker = null;

    this.setContent(ArtMaps.UI.formatMetadata(
            location.ObjectOfInterest,
            location.ObjectOfInterest.Metadata,
            location).get(0));

    this.on("closeclick", function() {
        isOpen = false;
    });

    this.open = function(_map, _marker) {
        if(isOpen) return;
        map = _map;
        marker = _marker;
        isOpen = true;
        google.maps.InfoWindow.prototype.open.call(this, map, marker);
    };

    this.close = function() {
        if(!isOpen) return;
        isOpen = false;
        google.maps.InfoWindow.prototype.close.call(this);
    };

    this.toggle = function(map, marker) {
        if(isOpen) this.close();
        else this.open(map, marker);
    };
};
ArtMaps.UI.InfoWindow.prototype = new google.maps.InfoWindow();

ArtMaps.UI.Marker = function(location, map) {
    var color = location.Source == "SystemImport"
            ? ArtMaps.UI.SystemMarkerColor
            : ArtMaps.UI.UserMarkerColor;
    color = jQuery.xcolor.darken(color, location.Confirmations, 10).getHex();
    var image = ArtMapsConfig.ThemeDirUrl + '/content/pins/icon-1.png';
   var marker = new google.maps.Marker({
        position: new google.maps.LatLng(location.Latitude, location.Longitude),
        icon: image
        styleIcon: new StyledIcon(
                StyledIconTypes.MARKER,
                {"color": ArtMaps.UI.SuggestionMarkerColor, "starcolor": "000000"})
        });
    marker.setClickable(false);
    marker.location = location;
    location.ObjectOfInterest.runWhenMetadataLoaded(function(metadata) {
        marker.setTitle(ArtMaps.UI.getTitleFromMetadata(metadata));
        var iw = new ArtMaps.UI.InfoWindow(location);
        marker.setClickable(true);
        marker.on("click", function() {
            iw.toggle(map, marker);
        });
    });
    return marker;
};
