/* Namespace: ArtMaps.UI */
ArtMaps.UI = ArtMaps.UI || {};

ArtMaps.UI.SystemMarkerColor = "#ff0000";
ArtMaps.UI.UserMarkerColor = "#00EEEE";
ArtMaps.UI.SuggestionMarkerColor = "#0CF52F";

ArtMaps.UI.InfoWindow = function(location) {
    
    var isOpen = false;
    var marker = null;
    var map = null;

    var content = jQuery(document.createElement("div"));
        var confirmed = jQuery(document.createElement("span"))
                .text(location.Confirmations + " confirmations");
    content.append(confirmed).append(jQuery(document.createElement("br")));
    
    var confirm = jQuery(document.createElement("div"))
            .addClass("artmaps-action-confirm-button")
            .text("Confirm");
    
    var suggest = jQuery(document.createElement("div"))
    		.addClass("artmaps-action-suggest-button")
    		.text("Suggest");
    
    var cfunc = function(){};
    
    var efunc = function() {
        confirmed.text("An error occurred, please try again.");
        confirm.unbind("click").bind("click", cfunc);
        content.append(confirm);
    };
    
    cfunc = function() {
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
                    "error": efunc
                });
            },
            "error": efunc
        });        
    };
    
    
    confirm.unbind("click").bind("click", cfunc);
    
    if(ArtMapsConfig.IsUserLoggedIn){
        content.append(confirm);
        content.append(suggest);
    }
    
    this.setContent(content.get(0));
    
    this.on("closeclick", function() {
        isOpen = false;
    });

    this.open = function(_map, _marker) {
        if(isOpen) return;
        map = _map;
        marker = _marker;
        isOpen = true;
        google.maps.event.addListener(this, "domready", function() {
            var iw = this;
            suggest.one("click", function() {
                iw.close();
            });
        });
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
    
    this.reset = function() {
        confirm.unbind("click").bind("click", cfunc);
    };
};
ArtMaps.UI.InfoWindow.prototype = new google.maps.InfoWindow();

ArtMaps.UI.Marker = function(location, map) {
    var color = location.Source == "SystemImport"
            ? ArtMaps.UI.SystemMarkerColor
            : ArtMaps.UI.UserMarkerColor;
    color = jQuery.xcolor.darken(color, location.Confirmations, 10).getHex();
    var image = ArtMapsConfig.ThemeDirUrl + '/content/pins/icon-2.png';
    //var image 'icon-1.png'
    /*var image = new google.maps.MarkerImage(ArtMapsConfig.ThemeDirUrl + '/content/pins/icon-1.png',
    		new google.maps.Size(32,42),
    		new google.maps.Point(0,0),
    		new google.maps.Point(50,50)
    	);*/
    var marker = new google.maps.Marker({
        position: new google.maps.LatLng(location.Latitude, location.Longitude),
        icon: image
        });
    /*var marker = new StyledMarker({
        "position": new google.maps.LatLng(location.Latitude, location.Longitude),
        "styleIcon": new StyledIcon(
                StyledIconTypes.MARKER,
                {"color": color, "starcolor": "000000"})
    });*/
    marker.location = location;
    marker.setTitle(location.Confirmations + " confirmations");
    var iw = new ArtMaps.UI.InfoWindow(location);
    marker.on("click", function() {
        iw.toggle(map, marker);
    });
    
    marker.reset = function() {
        iw.reset();
    };
    
    return marker;
};

ArtMaps.UI.SuggestionInfoWindow = function(marker, object) {
    var self = this;
    var content = jQuery(document.createElement("div"));
        content.html("<div>Drag this pin and hit confirm</div>");
        
        var confirm = jQuery(document.createElement("div"))
                .addClass("artmaps-action-suggest-confirm-button")
                .text("Confirm");
        
        function suggestionError(jqXHR, textStatus, errorThrown) {
            content.html("<div>Unfortunately, an error occurred. "
                    + "Please close this popup and try again.</div>");
        }
        
        confirm.click(function() {
            
            marker.setDraggable(false);
            var pos = marker.getPosition();
            content.html("<img src=\"" + ArtMapsConfig.ThemeDirUrl 
                    + "/content/loading/50x50.gif\" alt=\"\" />");
            
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
                                            
                                            self.close();
                                            var map = marker.getMap();
                                            var loc = new ArtMaps.Location(location, object, [action]);
                                            var mkr = new ArtMaps.UI.Marker(loc, map);
                                            mkr.setMap(map);
                                            
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
        content.append(confirm);
        
        var cancel = jQuery(document.createElement("div"))
                .addClass("artmaps-action-suggest-cancel-button")
                .text("Cancel");
        cancel.click(function() { marker.setMap(null); });
        content.append(cancel);
        
    this.setContent(content.get(0));
        
    this.on("closeclick", function() {
        marker.setMap(null);
    });
};
ArtMaps.UI.SuggestionInfoWindow.prototype = new google.maps.InfoWindow();

ArtMaps.UI.SuggestionMarker = function(map, object) {
	var image = ArtMapsConfig.ThemeDirUrl + '/content/pins/icon-3.png';
	var marker = new google.maps.Marker({
		"draggable": true,
		"map": map,
        "position": map.getCenter(),
        "icon": image
        });
    /*var marker = new StyledMarker({
        "draggable": true,
        "map": map,
        "icon":image,
        "position": map.getCenter(),
        "styleIcon": new StyledIcon(
                StyledIconTypes.MARKER,
                {"color": ArtMaps.UI.SuggestionMarkerColor, "starcolor": "000000"})
    });*/
    marker.setTitle("Drag me");
    var iw = new ArtMaps.UI.SuggestionInfoWindow(marker, object);
    iw.open(map, marker);
    return marker;
};
