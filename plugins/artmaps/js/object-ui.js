/* Namespace: ArtMaps.Object.UI */
ArtMaps.Object = ArtMaps.Object || {};
ArtMaps.Object.UI = ArtMaps.Object.UI || {};

ArtMaps.Object.UI.SystemMarkerColor = "#ff0000";
ArtMaps.Object.UI.UserMarkerColor = "#00EEEE";
ArtMaps.Object.UI.SuggestionMarkerColor = "#0CF52F";
ArtMaps.Object.UI.OwnerMarkerColor = "#BF1BE0";

ArtMaps.Object.UI.InfoWindow = function(marker, location, clusterer) {

    var isOpen = false;

    var content = jQuery(document.createElement("div"));
    
    var confirmed = jQuery(document.createElement("div"));
    var updateConfirmedText = function() {
        var text = location.Confirmations == 1 
                ? "1 person agrees with this location"
                : location.Confirmations + " people agree with this location";
        if(location.hasUserConfirmed(ArtMapsConfig.CoreUserID))
            text += " (including you)";
        confirmed.text(text);
    };
    updateConfirmedText();
    content.append(confirmed);
    
    if(ArtMapsConfig.IsUserLoggedIn) {
        
        var confirm = jQuery("<div class=\"artmaps-button\">Click here to agree with this location</div>");
        if(ArtMapsConfig.CoreUserID != location.OwnerID 
                && (!location.hasUserConfirmed(ArtMapsConfig.CoreUserID))) {
            content.append(confirm);
            confirm.click(function() {
                confirm.remove();
                ArtMaps.Util.confirmLocation(location,
                        function(action) {
                            location.addAction(action);
                            updateConfirmedText();
                        });   
            }); 
        }
        
        var remove = jQuery("<div class=\"artmaps-button\">Click here to delete this suggestion</div>");
        if(ArtMapsConfig.CoreUserID == location.OwnerID) {
            content.append(remove);
            remove.click(function() {
                remove.remove();
                ArtMaps.Util.removeLocation(location, 
                        function(action) {
                            location.addAction(action);
                            clusterer.removeMarker(marker);
                            clusterer.repaint();
                        });
            });
        }
        
        if(location.CommentID > -1 
                && jQuery("#comment-" + location.CommentID).length > 0) {
            var comment = jQuery("<div class=\"artmaps-button\">View associated comment</div>")
                    .click(function() {
                        jQuery.scrollTo("#comment-" + location.CommentID);
                    });
            content.append(comment);
        }
    }
        
    this.setContent(content.get(0));
    
    this.on("closeclick", function() {
        isOpen = false;
    });

    this.open = function(map, marker) {
    	if(isOpen) return;
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
ArtMaps.Object.UI.InfoWindow.prototype = new google.maps.InfoWindow();

ArtMaps.Object.UI.Marker = function(location, map, clusterer) {
    var color = location.Source == "SystemImport"
            ? ArtMaps.Object.UI.SystemMarkerColor
            : ArtMapsConfig.IsUserLoggedIn && (ArtMapsConfig.CoreUserID == location.OwnerID)
                    ? ArtMaps.Object.UI.OwnerMarkerColor
                    : ArtMaps.Object.UI.UserMarkerColor;
    color = jQuery.xcolor.darken(color, location.Confirmations, 10).getHex();
    var marker = new StyledMarker({
        "position": new google.maps.LatLng(location.Latitude, location.Longitude),
        "styleIcon": new StyledIcon(
                StyledIconTypes.MARKER,
                {"color": color, "starcolor": "000000"})
    });
    var iw = new ArtMaps.Object.UI.InfoWindow(marker, location, clusterer);
    marker.on("click", function() {
        iw.toggle(map, marker);
    });
    marker.close = function() { iw.close(); };
    return marker;
};

ArtMaps.Object.UI.SuggestionInfoWindow = function(marker, object, clusterer) {
	
    var self = this;
    
    var initialContent = jQuery("<div><div>Click and hold to drag the pin into position,<br /> click finish when you are done</div></div>");
    var processingContent = jQuery("<div><img src=\"" + ArtMapsConfig.LoadingIcon50x50Url + "\" alt=\"\" /></div>");
    var errorContent = jQuery("<div>Unfortunately, an error occurred. Please close this popup and try again.</div>");
    
    initialContent.append(jQuery("<div class=\"artmaps-button\">Finish</div>").click(function() {
        self.setContent(processingContent.get(0));
        marker.setDraggable(false);
        ArtMaps.Util.suggestLocation(object, marker.getPosition(),
                function(location, action) {
                    var map = marker.getMap();
                    marker.hide();
                    var loc = new ArtMaps.Location(location, object, [action]);
                    object.Locations[object.Locations.length] = loc;
                    var mkr = new ArtMaps.Object.UI.Marker(loc, map, clusterer);
                    clusterer.addMarkers([mkr]);
                    clusterer.repaint();
                    jQuery("#artmaps-object-suggestion-message-comment-button")
                            .unbind("click")
                            .click(function() {
                                jQuery("#artmaps-object-suggestion-message").dialog("close");
                                jQuery.scrollTo("#comment");
                                var input = jQuery("#artmaps-location-id");
                                if(input.length == 0) {
                                    input = jQuery(document.createElement("input")).attr({
                                        "type": "hidden",
                                        "name": "artmaps-location-id",
                                        "id": "artmaps-location-id"
                                    }); 
                                    jQuery("#commentform").append(input);
                                }
                                input.attr("value", loc.ID);
                            }); 
                    jQuery("#artmaps-object-suggestion-message").dialog();
                },
                function(jqXHR, textStatus, errorThrown) {
                    self.setContent(errorContent.get(0));
                });
        })
    );
    
    initialContent.append(jQuery("<div class=\"artmaps-button\">Cancel</div>")
            .click(function() { marker.hide(); }));
        
    this.setContent(initialContent.get(0));
    
    this.close = function() {
        this.setContent(initialContent.get(0));
        google.maps.InfoWindow.prototype.close.call(this);
    };
        
    this.on("closeclick", function() {
        marker.hide();
    });
};
ArtMaps.Object.UI.SuggestionInfoWindow.prototype = new google.maps.InfoWindow();

ArtMaps.Object.UI.SuggestionMarker = function(map, object, clusterer) {
    var marker = new StyledMarker({
        "styleIcon": new StyledIcon(
                StyledIconTypes.MARKER,
                {"color": ArtMaps.Object.UI.SuggestionMarkerColor, "starcolor": "000000"})
    });
    google.maps.event.addListener(marker, 'dragend', function() {
        map.panTo(marker.getPosition());
    });
    marker.setTitle("Click and hold to drag me");
    var iw = new ArtMaps.Object.UI.SuggestionInfoWindow(marker, object, clusterer);
    var isVisible = false;
    
    marker.show = function() {
        marker.setPosition(map.getCenter());
        if(isVisible) return;
        isVisible = true;
        marker.setMap(map);
        marker.setPosition(map.getCenter());
        marker.setDraggable(true);
        iw.open(map, marker);
    };
    
    marker.hide = function() {
        if(!isVisible) return;
        isVisible = false;
        iw.close();
        marker.setMap(null);
    };
    
    return marker;
};
