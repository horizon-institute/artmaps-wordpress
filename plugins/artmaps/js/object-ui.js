/* Namespace: ArtMaps.Object.UI */
ArtMaps.Object = ArtMaps.Object || {};
ArtMaps.Object.UI = ArtMaps.Object.UI || {};

ArtMaps.Object.UI.SystemMarkerColor = "#ff0000";
ArtMaps.Object.UI.UserMarkerColor = "#00EEEE";
ArtMaps.Object.UI.SuggestionMarkerColor = "#0CF52F";
ArtMaps.Object.UI.OwnerMarkerColor = "#BF1BE0";
ArtMaps.Object.UI.FinalMarkerColor = "#391BE0";

ArtMaps.Object.UI.InfoWindow = function(map, marker, location, clusterer) {

    var self = this;
    
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
        
        if(ArtMapsConfig.CoreUserID != location.OwnerID 
                && (!location.hasUserConfirmed(ArtMapsConfig.CoreUserID))) {
            var confirm = jQuery("<div class=\"artmaps-button\">Click here to agree with this location</div>");
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
        
        if(ArtMapsConfig.CoreUserID == location.OwnerID) {
            var remove = jQuery("<div class=\"artmaps-button\">Click here to delete this suggestion</div>");
            content.append(remove);
            remove.click(function() {
                remove.remove();
                ArtMaps.Util.removeLocation(location, 
                        function(action) {
                            location.addAction(action);
                            jQuery.each(clusterer.getMarkers(), function(i, m) {
                                m.resetIcon(); 
                            });
                            clusterer.removeMarker(marker);
                            clusterer.repaint();
                            if(location.CommentID > -1) {
                                jQuery.ajax(ArtMapsConfig.AjaxUrl, {
                                    "type": "post",
                                    "data": {
                                        "action": "artmaps.deleteComment",
                                        "commentID": location.CommentID
                                    },
                                    "success": function(r) {}
                                });
                                jQuery("#comment-" + location.CommentID).remove();
                            }
                        });
            });
            
            if(location.CommentID < 0) {
                var comment = jQuery("<div class=\"artmaps-button\">Click here to comment on this suggestion</div>");
                content.append(comment);
                comment.click(function() {
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
                    input.attr("value", location.ID); 
                });
            }
        }
        
        if(ArtMapsConfig.UserLevel.indexOf("administrator") > -1
                || ArtMapsConfig.UserLevel.indexOf("editor") > -1
                || ArtMapsConfig.UserLevel.indexOf("author") > -1
                || ArtMapsConfig.UserLevel.indexOf("contributor") > -1) {
            var accept = jQuery("<div class=\"artmaps-button\">Click here to accept this suggestion as final</div>");
            content.append(accept);
            accept.click(function() {
                accept.remove();
                ArtMaps.Util.finaliseLocation(location, function(action) {
                    location.addAction(action);
                    jQuery.each(clusterer.getMarkers(), function(i, m) {
                       m.resetIcon(); 
                    });
                    marker.styleIcon.set("color", ArtMaps.Object.UI.FinalMarkerColor); 
                });
            });
        }
        
        if(location.CommentID > -1 
                && jQuery("#comment-" + location.CommentID).length > 0) {
            var e = jQuery("#comment-" + location.CommentID);
            var comment = jQuery("<div class=\"artmaps-button\">View associated comment</div>")
                    .click(function() {
                        jQuery(".artmaps-highlighted-comment")
                                .removeClass("artmaps-highlighted-comment");
                        jQuery(".artmaps-object-infobox-highlighted")
                                .removeClass("artmaps-object-infobox-highlighted")
                                .addClass("artmaps-object-infobox");
                        e.addClass("artmaps-highlighted-comment");
                        self.setOptions({"boxClass": "artmaps-object-infobox-highlighted"});
                        jQuery.scrollTo(e);
                    });
            content.append(comment);
            var pin = jQuery("<div class=\"artmaps-button\">View associated location</div>")
                    .click(function() {
                        jQuery(".artmaps-highlighted-comment")
                                .removeClass("artmaps-highlighted-comment");
                        jQuery(".artmaps-object-infobox-highlighted")
                                .removeClass("artmaps-object-infobox-highlighted")
                                .addClass("artmaps-object-infobox");
                        e.addClass("artmaps-highlighted-comment");
                        self.setOptions({"boxClass": "artmaps-object-infobox-highlighted"});
                        self.open(map, marker);
                        map.panTo(marker.getPosition());
                        map.setZoom(clusterer.getMaxZoom());
                        jQuery.scrollTo("#artmaps-object-map");
                    });
            e.append(pin);
        }
    }
        
    this.setContent(content.get(0));
    
    this.on("closeclick", function() {
        isOpen = false;
    });

    this.open = function(map, marker) {
    	if(isOpen) return;
        isOpen = true;
        InfoBox.prototype.open.call(this, map, marker);
    };

    this.close = function() {
    	if(!isOpen) return;
        isOpen = false;
        if(location.CommentID > -1 
                && jQuery("#comment-" + location.CommentID).length > 0) {
            self.setOptions({"boxClass": "artmaps-object-infobox"});
            jQuery("#comment-" + location.CommentID).removeClass("artmaps-highlighted-comment");;
        }
        InfoBox.prototype.close.call(this);
    };

    this.toggle = function(map, marker) {
    	if(isOpen) this.close();
        else this.open(map, marker);
    };
};
ArtMaps.Object.UI.InfoWindow.prototype = new InfoBox({
    "boxClass": "artmaps-object-infobox"
});

ArtMaps.Object.UI.Marker = function(location, map, clusterer) {
    var getColor = function() {
        var color = location.IsFinal 
                ? ArtMaps.Object.UI.FinalMarkerColor
                : location.Source == "SystemImport"
                    ? ArtMaps.Object.UI.SystemMarkerColor
                    : ArtMapsConfig.IsUserLoggedIn && (ArtMapsConfig.CoreUserID == location.OwnerID)
                            ? ArtMaps.Object.UI.OwnerMarkerColor
                            : ArtMaps.Object.UI.UserMarkerColor;
        color = jQuery.xcolor.darken(color, location.Confirmations, 10).getHex();
        return color;
    };
    var marker = new StyledMarker({
        "position": new google.maps.LatLng(location.Latitude, location.Longitude),
        "styleIcon": new StyledIcon(
                StyledIconTypes.MARKER,
                {"color": getColor(), "starcolor": "000000"})
    });
    var iw = new ArtMaps.Object.UI.InfoWindow(map, marker, location, clusterer);
    marker.on("click", function() {
        iw.toggle(map, marker);
    });
    marker.close = function() { iw.close(); };
    marker.setMap = function(m) {
        StyledMarker.prototype.setMap.call(marker, m);
        if(m == null)
            iw.close();
    };
    marker.resetIcon = function() {
        marker.styleIcon.set("color", getColor());
    };
    return marker;
};

ArtMaps.Object.UI.SuggestionInfoWindow = function(marker, object, clusterer) {
    var self = this;
    
    var initialContent = jQuery("<div><div>Click and hold to drag the pin into position,<br />click finish when you are done</div></div>");
    var processingContent = jQuery("<div><img src=\"" + ArtMapsConfig.LoadingIcon50x50Url + "\" alt=\"\" /></div>");
    var errorContent = jQuery("<div>Unfortunately, an error occurred. Please close this popup and try again.</div>");
    var tooCloseContent = jQuery("<div><div>This is too close to another suggestion,<br />please consider agreeing with that<br /> suggestion instead or moving me away</div></div>");
    
    initialContent.append(jQuery("<div class=\"artmaps-button\">Finish</div>").click(function() {
        self.setContent(processingContent.get(0));
        object.Metadata(function(md) {
            jQuery("#artmaps-object-suggestion-message-other-actions")
                .append("<li><a href=\"" + ArtMapsConfig.SearchUrl + encodeURIComponent(md.artist) + "\">Search for other works by this artist</a></li>");
        });
        var worker = new ArtMaps.RunOnce(ArtMapsConfig.PluginDirUrl + "/js/do-get.js");
        var bounds = ArtMaps.Util.boundingBox(marker.getPosition(), 20);
        worker.queueTask(ArtMapsConfig.CoreServerPrefix + "objectsofinterest/search/?"
                + "boundingBox.northEast.latitude=" + ArtMaps.Util.toIntCoord(bounds.getNorthEast().lat())
                + "&boundingBox.southWest.latitude=" + ArtMaps.Util.toIntCoord(bounds.getSouthWest().lat())
                + "&boundingBox.northEast.longitude=" + ArtMaps.Util.toIntCoord(bounds.getNorthEast().lng())
                + "&boundingBox.southWest.longitude=" + ArtMaps.Util.toIntCoord(bounds.getSouthWest().lng()),
            function() { },
            function(objects) {
                var list = jQuery(document.createElement("ul"));
                var found = false;
                jQuery.each(objects, function(i, o) {
                    if(o.locations.length != 1) return;
                    if(o.ID == object.ID) return;
                    found = true;
                    var obj = new ArtMaps.ObjectOfInterest(o);
                    var e = jQuery(document.createElement("li"));
                    obj.Metadata(function(md) {
                        e.html("<a href=\"" + ArtMapsConfig.SiteUrl + "/object/" + obj.ID 
                                + "\">" + md.title + " by " + md.artist + "</a>");
                    });
                    list.append(e);
                });
                if(!found) return;
                var e = jQuery(document.createElement("li"));
                e.append(jQuery("<h2>View nearby artworks</h2>")).append(list);
                jQuery("#artmaps-object-suggestion-message-other-actions").append(e);
            });
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
                    jQuery("#artmaps-object-suggestion-message").dialog({
                        "modal": true,
                        "width": 640
                    });
                },
                function(jqXHR, textStatus, errorThrown) {
                    self.setContent(errorContent.get(0));
                });
        })
    );
    
    initialContent.append(jQuery("<div class=\"artmaps-button\">Cancel</div>")
            .click(function() { marker.hide(); }));
    
    tooCloseContent.append(jQuery("<div class=\"artmaps-button\">Cancel</div>")
            .click(function() { marker.hide(); }));
        
    this.setContent(initialContent.get(0));
    
    this.close = function() {
        this.setContent(initialContent.get(0));
        this.setTooClose(false);
        google.maps.InfoWindow.prototype.close.call(this);
    };
        
    this.on("closeclick", function() {
        marker.hide();
    });
    
    var tooClose = false;
    this.setTooClose = function(tc) {
        if(tooClose == tc) return;
        tooClose = tc;
        if(tc)
            this.setContent(tooCloseContent.get(0));
        else
            this.setContent(initialContent.get(0));
    };
};
ArtMaps.Object.UI.SuggestionInfoWindow.prototype = new InfoBox({
    "boxClass": "artmaps-object-infobox"
});

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
    marker.setZIndex(google.maps.Marker.MAX_ZINDEX + 1);
    var iw = new ArtMaps.Object.UI.SuggestionInfoWindow(marker, object, clusterer);
    var isVisible = false;
    
    marker.on("position_changed", function() {
        var pos = marker.getPosition();
        var tooClose = false;
        jQuery.each(clusterer.getMarkers(), function(i, m){
            if(google.maps.geometry.spherical.computeDistanceBetween(pos, m.getPosition()) < 10)
                tooClose = true;
        });
        iw.setTooClose(tooClose);
    });
    
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
