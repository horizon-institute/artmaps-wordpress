/* Namespace: ArtMaps.Object.UI */
ArtMaps.Object = ArtMaps.Object || {};
ArtMaps.Object.UI = ArtMaps.Object.UI || {};

ArtMaps.Object.UI.SystemMarkerColor = "marker_106";
ArtMaps.Object.UI.UserMarkerColor = "marker_106";
ArtMaps.Object.UI.SuggestionMarkerColor = "marker_move";
ArtMaps.Object.UI.OwnerMarkerColor = "marker_106";
ArtMaps.Object.UI.FinalMarkerColor = "marker_final";

ArtMaps.Object.UI.InfoWindow = function(map, marker, location, clusterer) {    

    var self = this;
    var isOpen = false;

    var content = jQuery(document.createElement("div"));
    var confirmed = jQuery(document.createElement("h2"));
    var updateConfirmedText = function() {
        var text = location.Confirmations == 1 
                ? "1 person agrees with this location"
                : location.Confirmations + " people agree with this location";
        if(location.hasUserConfirmed(ArtMapsConfig.CoreUserID))
            text += ", including you";
        confirmed.text(text);
    };
    updateConfirmedText();
    content.append(confirmed);
   
    if(ArtMapsConfig.IsUserLoggedIn) {
        if(ArtMapsConfig.CoreUserID != location.OwnerID 
                && (!location.hasUserConfirmed(ArtMapsConfig.CoreUserID))) {
            var confirm = jQuery("<div class=\"artmaps-button primary-button\"><i class=\"fa-check\"></i>&nbsp;Agree</div>");
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
            
            if(location.CommentID < 0) {
                var comment = jQuery("<div class=\"artmaps-button\" id=\"comment-on-new-suggestion\"><i class=\"fa-comment\"></i>&nbsp;Explain with a comment</div>");
                content.append(comment);
                comment.click(function() {
                    jQuery("#artmaps-object-metadata").scrollTo("#respond",250);
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
            
            var remove = jQuery("<div class=\"artmaps-button button-delete\"><i class=\"fa-times\"></i>&nbsp;Remove suggestion</div>");
            content.append(remove);
            remove.click(function() {
                remove.remove();
                jQuery("#artmaps-object-suggestion-message").hide();
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
            
            content.append(jQuery("<div class=\"artmaps-button cancel-button\">Close</div>")
            .click(function() { marker.hide(); }));
            
        }
        
        if(ArtMapsConfig.UserLevel.indexOf("administrator") > -1
                || ArtMapsConfig.UserLevel.indexOf("editor") > -1
                || ArtMapsConfig.UserLevel.indexOf("author") > -1
                || ArtMapsConfig.UserLevel.indexOf("contributor") > -1) {
            var accept = jQuery("<div class=\"artmaps-button\">Approve as final</div>");
            content.append(accept);
            accept.click(function() {
                accept.remove();
                ArtMaps.Util.finaliseLocation(location, function(action) {
                    location.addAction(action);
                    jQuery.each(clusterer.getMarkers(), function(i, m) {
                       m.resetIcon(); 
                    });
                    marker.setIcon(ArtMapsConfig.ClusterIconUrl+'marker_final.png');
                });
            });
        }

    } else {
      content.append(jQuery('<p>Log in to agree with this or add your own pin to the map.</p>'));
    }
    
    if(location.CommentID > -1 
            && jQuery("#comment-" + location.CommentID).length > 0) {
        var e = jQuery("#comment-" + location.CommentID);
        var comment = jQuery("<div class=\"artmaps-button\">Show explanation</div>")
                .click(function() {
                    jQuery(".highlighted").removeClass("highlighted");
                    e.addClass("highlighted");
                    self.setOptions({"boxClass": "artmaps-object-infobox highlighted"});
                    jQuery("#artmaps-object-metadata").scrollTo(e,250);
                });
        content.append(comment);
        var pin = jQuery("<a href=\"#\" class=\"location-link\">View associated location</a>")
                .click(function(event) {
                    jQuery(".highlighted")
                            .removeClass("highlighted");
                    e.addClass("highlighted");
                    self.setOptions({"boxClass": "artmaps-object-infobox highlighted"});
                    self.open(map, marker);
                    map.panTo(marker.getPosition());
                    map.setZoom(15);
                    event.preventDefault();
                });
        jQuery("#comment-" + location.CommentID + " .comment-content").append(pin);
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
            jQuery("#comment-" + location.CommentID).removeClass("artmaps-highlighted-comment");
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
        return color;
    };
    var marker_img = {
      url: ArtMapsConfig.ClusterIconUrl+getColor()+'.png',
      scaledSize: new google.maps.Size(53,53)
    };
    var marker = new google.maps.Marker({
        position: new google.maps.LatLng(location.Latitude, location.Longitude),
        icon: marker_img,
        animation: google.maps.Animation.DROP,
        optimized: false
    });
    var iw = new ArtMaps.Object.UI.InfoWindow(map, marker, location, clusterer);
    marker.on("click", function() {
        map.panTo(marker.getPosition());
        jQuery.each(clusterer.getMarkers(), function(i, m){
          m.close();
        });
        iw.toggle(map, marker);
    });
    marker.close = function() { iw.close(); };
    marker.setMap = function(m) {
        StyledMarker.prototype.setMap.call(marker, m);
        if(m == null)
            iw.close();
    };
    marker.resetIcon = function() {
        marker.setIcon("color");
    };
    return marker;
};

ArtMaps.Object.UI.SuggestionInfoWindow = function(marker, object, clusterer) {
    var self = this;
    
    var initialContent = jQuery("<div><h2>Your suggestion</h2><p>Drag the pin into position and choose a reason.</p></div>");
    var processingContent = jQuery("<div><img src=\"" + ArtMapsConfig.LoadingIcon50x50Url + "\" alt=\"\" /></div>");
    var errorContent = jQuery("<div>Sorry; an error occurred. Please close this popup and try again.</div>");
    var tooCloseContent = jQuery("<div><div>This is too close to an existing suggestion. Please consider agreeing with that suggestion instead or moving this pin further away.</div></div>");
    
    var reason = jQuery(document.createElement("select"));
    for(var i = 0; i < ArtMapsConfig.LocationReasons.length; i++) {
        var op = jQuery(document.createElement("option"));
        op.val(ArtMapsConfig.LocationReasons[i]).text(ArtMapsConfig.LocationReasons[i]);
        reason.append(op);
    }
    initialContent.append(reason);
    
    initialContent.append(jQuery("<div class=\"artmaps-button primary-button\"><i class=\"fa-check\"></i>&nbsp;Suggest this location</div>").click(function() {
        self.setContent(processingContent.get(0));
        object.Metadata(function(md) {
            jQuery("#artmaps-object-suggestion-message-other-actions")
                .append("<li><a href=\"" + ArtMapsConfig.SearchUrl + encodeURIComponent(md.artist) + "\" class=\"artmaps-button\">Search</a> for other works by this artist</li>");
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
                    if(i >= 5) return;
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
                e.append(jQuery("<span>View nearby artworks:</span>")).append(list);
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
                    jQuery("#artmaps-object-suggestion-message-comment-button").click(function() {
                                jQuery("#artmaps-object-suggestion-message").dialog("close");
                                jQuery("#artmaps-object-metadata").scrollTo("#artmaps-object-metadata",250);
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
                    jQuery("#artmaps-object-suggestion-message").show();
                },
                function(jqXHR, textStatus, errorThrown) {
                    self.setContent(errorContent.get(0));
                },
                { "reason" : reason.val() });
        })
    );
    
    initialContent.append(jQuery("<div class=\"artmaps-button cancel-button\">Close</div>")
            .click(function() { marker.hide(); }));
    
    tooCloseContent.append(jQuery("<div class=\"artmaps-button cancel-button\">Close</div>")
            .click(function() { marker.hide(); }));
            
    errorContent.append(jQuery("<div class=\"artmaps-button cancel-button\">Close</div>")
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
    var marker_img = {
      url: ArtMapsConfig.ClusterIconUrl+'marker_move.png',
      scaledSize: new google.maps.Size(53,53)
    };
    var marker = new google.maps.Marker({
      icon: marker_img,
      animation: google.maps.Animation.BOUNCE
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

