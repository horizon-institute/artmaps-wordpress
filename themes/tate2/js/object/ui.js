/* Namespace: ArtMaps.UI */
ArtMaps.UI = ArtMaps.UI || {};

ArtMaps.UI.SystemMarkerColor = "#ff0000";
ArtMaps.UI.UserMarkerColor = "#00EEEE";
ArtMaps.UI.SuggestionMarkerColor = "#0CF52F";

ArtMaps.UI.InfoWindow = function(location, suggestFunc) {
    
    var isOpen = false;

    var content = jQuery(document.createElement("div"));
    var confirmed = jQuery("<span>" + location.Confirmations + " confirmations</span>");
    content.append(confirmed).append(jQuery(document.createElement("br")));
    
    if(ArtMapsConfig.IsUserLoggedIn) {
        var confirm = jQuery("<div class=\"artmaps-button\">Confirm</div>");
        var suggest = jQuery("<div class=\"artmaps-button\">Suggest</div>");
        content.append(confirm);
        content.append(suggest);
        confirm.click(function() {
            confirm.remove();
            jQuery.ajax(ArtMapsConfig.AjaxUrl, {
                "type": "post",
                "data": {
                    "action": "artmaps.signData",
                    "data": {
                        "URI": "confirmation://{\"LocationID\":" + location.ID + "}"
                    }
                },
                "success": function(saction) {
                    jQuery.ajax(ArtMapsConfig.CoreServerPrefix 
                            + "objectsofinterest/" + location.ObjectOfInterest.ID + "/actions", {
                        "type": "post",
                        "data": JSON.stringify(saction),
                        "dataType": "json",
                        "contentType": "application/json",
                        "processData": false,
                        "success": function(action) {
                            location.Actions[location.Actions.length] = action;
                            location.Confirmations++;
                            confirmed.text(location.Confirmations + " confirmations");
                        },
                    });
                },
            });        
        });  
        suggest.click(function() {
            suggestFunc();
        });
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
ArtMaps.UI.InfoWindow.prototype = new google.maps.InfoWindow();

ArtMaps.UI.Marker = function(location, map, suggestFunc) {
    var color = location.Source == "SystemImport"
            ? ArtMaps.UI.SystemMarkerColor
            : ArtMaps.UI.UserMarkerColor;
    color = jQuery.xcolor.darken(color, location.Confirmations, 10).getHex();
    var marker = new StyledMarker({
        "position": new google.maps.LatLng(location.Latitude, location.Longitude),
        "styleIcon": new StyledIcon(
                StyledIconTypes.MARKER,
                {"color": color, "starcolor": "000000"})
    });
    marker.setTitle(location.Confirmations + " confirmations");
    var iw = new ArtMaps.UI.InfoWindow(location, suggestFunc);
    marker.on("click", function() {
        iw.toggle(map, marker);
    });    
    marker.close = function() { iw.close(); };
    return marker;
};

ArtMaps.UI.SuggestionInfoWindow = function(marker, object, clusterer) {
	
    var self = this;
    
    var initialContent = jQuery("<div><div>Drag this pin and hit confirm</div></div>");
    var processingContent = jQuery("<div><img src=\"" + ArtMapsConfig.ThemeDirUrl + "/content/loading/50x50.gif\" alt=\"\" /></div>");
    var errorContent = jQuery("<div>Unfortunately, an error occurred. Please close this popup and try again.</div>");
    
    function suggestionError(jqXHR, textStatus, errorThrown) {
        self.setContent(errorContent.get(0));
    }
        
    var confirm = jQuery("<div class=\"artmaps-button\">Confirm</div>");
    confirm.click(function() {
        
        marker.setDraggable(false);
        var pos = marker.getPosition();
        self.setContent(processingContent.get(0));
        
        jQuery.ajax(ArtMapsConfig.AjaxUrl, {
            "type": "post",
            "data": {
                "action": "artmaps.signData",
                "data": {
                    "error": 0,
                    "latitude": ArtMaps.Util.toIntCoord(pos.lat()),
                    "longitude": ArtMaps.Util.toIntCoord(pos.lng())
                }
            },
            "success": function(slocation) {
                
                jQuery.ajax(ArtMapsConfig.CoreServerPrefix 
                        + "objectsofinterest/" + object.ID + "/locations", {
                    "type": "post",
                    "data": JSON.stringify(slocation),
                    "dataType": "json",
                    "contentType": "application/json",
                    "processData": false,
                    "success" : function(location) {
                        
                        jQuery.ajax(ArtMapsConfig.AjaxUrl, {
                            "type": "post",
                            "data": {
                                "action": "artmaps.signData",
                                "data": {
                                    "URI": "suggestion://{\"LocationID\":" + location.ID + "}"
                                }
                            },
                            "success": function(saction) {
                                jQuery.ajax(ArtMapsConfig.CoreServerPrefix 
                                        + "objectsofinterest/" + object.ID + "/actions", {
                                    "type": "post",
                                    "data": JSON.stringify(saction),
                                    "dataType": "json",
                                    "contentType": "application/json",
                                    "processData": false,
                                    "success": function(action) {
                                        
                                        marker.hide();
                                        var map = marker.getMap();
                                        var loc = new ArtMaps.Location(location, object, [action]);
                                        var mkr = new ArtMaps.UI.Marker(loc, map);
                                        clusterer.addMarkers([mkr]);
                                        clusterer.fitMapToMarkers();
                                    },
                                    "error": suggestionError
                                });
                            },
                            "error": suggestionError
                        });
                    },
                    "error": suggestionError
                });                    
            },
            "error": suggestionError
        });
        
    });
    initialContent.append(confirm);
    
    var cancel = jQuery("<div class=\"artmaps-button\">Cancel</div>");
    cancel.click(function() { marker.hide(); });
    initialContent.append(cancel);
        
    this.setContent(initialContent.get(0));
    
    this.close = function() {
        this.setContent(initialContent.get(0));
        google.maps.InfoWindow.prototype.close.call(this);
    };
        
    this.on("closeclick", function() {
        marker.hide();
    });
};
ArtMaps.UI.SuggestionInfoWindow.prototype = new google.maps.InfoWindow();

ArtMaps.UI.SuggestionMarker = function(map, object, clusterer) {
    var marker = new StyledMarker({
        "styleIcon": new StyledIcon(
                StyledIconTypes.MARKER,
                {"color": ArtMaps.UI.SuggestionMarkerColor, "starcolor": "000000"})
    });
    google.maps.event.addListener(marker, 'dragend', function() {
        map.panTo(marker.getPosition());
    });
    marker.setTitle("Drag me");
    var iw = new ArtMaps.UI.SuggestionInfoWindow(marker, object, clusterer);
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
