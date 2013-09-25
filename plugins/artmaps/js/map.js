/* Namespace: ArtMaps.Map */
ArtMaps.Map = ArtMaps.Map || {};

ArtMaps.Map.MapObject = function(container, config) {
        
    var mapconf = {
            "center": new google.maps.LatLng(0, 0),
            "streetViewControl": false,
            "zoom": 15,
            "minZoom": 3,
            "mapTypeId": google.maps.MapTypeId.HYBRID,
            "zoomControlOptions": {
                "position": google.maps.ControlPosition.LEFT_CENTER
            },
            "panControl": false,
            "mapTypeControl": true
        };
    
    var clusterconf = {
            "gridSize": 150,
            "minimumClusterSize": 1,
            "zoomOnClick": false,
            "imageSizes": [56],
            "styles": [{
                "url": ArtMapsConfig.ClusterIconUrl,
                "height": 56,
                "width": 56
            }]
        };   

    var map = new google.maps.Map(container.get(0), jQuery.extend(true, mapconf, config.map));
    var clusterer = new MarkerClusterer(map, [], jQuery.extend(true, clusterconf, config.cluster));
    var firstLoad = true;
        
    (function() {
        var sessionstate = {};
        if(ArtMapsConfig.MapState) sessionstate = jQuery.deparam(ArtMapsConfig.MapState);
        if(sessionstate.cluster) {
            jQuery.bbq.pushState({ "cluster": sessionstate.cluster });
        }
        var hashstate = jQuery.bbq.getState();
        if(hashstate.maptype) {
            map.setMapTypeId(hashstate.maptype);
        } else if(sessionstate.maptype) {
            map.setMapTypeId(sessionstate.maptype);
        }
        if(hashstate.zoom) {
            map.setCenter(new google.maps.LatLng(hashstate.lat, hashstate.lng));
            map.setZoom(parseInt(hashstate.zoom));
        } else if(sessionstate.zoom) {
            map.setCenter(new google.maps.LatLng(sessionstate.lat, sessionstate.lng));
            map.setZoom(parseInt(sessionstate.zoom));
        } else {
            ArtMaps.Util.browserLocation(
                function(pos) { map.setCenter(pos); },
                function() { });
        }
    })();
    
    (function() {
        map.on("maptypeid_changed", function() {
            var centre = map.getCenter();
            jQuery.bbq.pushState({
                "zoom": map.getZoom(),
                "lat": centre.lat(),
                "lng": centre.lng(),
                "maptype": map.getMapTypeId() 
            }); 
        });
    })();
        
    (function() {
        var runOnce = new ArtMaps.RunOnce(ArtMapsConfig.PluginDirUrl + "/js/do-get.js",
                function(data, callback) {
                    jQuery.getJSON(data, function(j) {
                        callback(j);
                    });
                });
        var loading = jQuery(document.createElement("img"))
                .attr("src", ArtMapsConfig.LoadingIcon50x50Url)
                .attr("alt", "")
                .css("display", "none");
        map.controls[google.maps.ControlPosition.LEFT_CENTER].push(loading.get(0));
        var cache = {};
        var filter = function(m, l) { l.push(m); };
        map.setFilter = function(f) {
            clusterer.clearMarkers();
            filter = f;
            cache = {};
            map.trigger("idle");
        };
        map.on("idle", function() {
            var centre = map.getCenter();
            jQuery.bbq.pushState({
                "zoom": map.getZoom(),
                "lat": centre.lat(),
                "lng": centre.lng(),
                "maptype": map.getMapTypeId() 
            });
            var bounds = map.getBounds();
            runOnce.queueTask(ArtMapsConfig.CoreServerPrefix + "objectsofinterest/search/?"
                    + "boundingBox.northEast.latitude=" + ArtMaps.Util.toIntCoord(bounds.getNorthEast().lat())
                    + "&boundingBox.southWest.latitude=" + ArtMaps.Util.toIntCoord(bounds.getSouthWest().lat())
                    + "&boundingBox.northEast.longitude=" + ArtMaps.Util.toIntCoord(bounds.getNorthEast().lng())
                    + "&boundingBox.southWest.longitude=" + ArtMaps.Util.toIntCoord(bounds.getSouthWest().lng()),
                function() {
                    loading.css("display", "inline");
                },
                function(objects) {
                    var markers = [];
                    jQuery.each(objects, function(i, o) {
                        if(cache[o.ID] == true) return;
                        cache[o.ID] = true;
                        var obj = new ArtMaps.ObjectOfInterest(o);
                        var finalloc = null;
                        var systemloc = null;
                        jQuery.each(obj.Locations, function(j, loc) {
                            if(loc.IsFinal) { finalloc = loc; }
                            else if(loc.Source == "SystemImport") { systemloc = loc; }
                        });
                        var marker = new ArtMaps.Map.UI.Marker(obj, finalloc != null ? finalloc : systemloc);
                        filter(marker, markers);
                    });
                    clusterer.addMarkers(markers);
                    loading.css("display", "none");
                    
                    if(firstLoad) {
                        firstLoad = false;
                        var cs = jQuery.bbq.getState("cluster");
                        if(cs) {
                            jQuery.each(clusterer.getClusters(), function(i, c) {
                                if(c.getCenter().lat() == cs.lat
                                        && c.getCenter().lng() == cs.lng) {
                                    google.maps.event.trigger(clusterer, "click", c);
                                }
                            });
                        }
                    }
                });
        });
    })();
    
    
    (function(){
        var pagetemplate = jQuery("#artmaps-map-object-list-container-page").children().first();
        jQuery("#artmaps-object-list-container-page").detach();
        clusterer.on("click", function(cluster) {            
            var markers = cluster.getMarkers();
            if(!markers || !markers.length) return;
            
            var pageSize = 6;
            var totalPages = Math.floor(markers.length / pageSize);
            if(markers.length % pageSize != 0) totalPages++;
            
            if(!cluster.dialog) {
                cluster.dialog = jQuery(document.createElement("div"))
                        .addClass("artmaps-map-object-list-container");
                cluster.pages = new Array();
                cluster.dialog.otherOpening = false;
                cluster.dialog.closeFunc = function() {
                    cluster.dialog.otherOpening = true;
                    cluster.dialog.dialog("close"); 
                };
            }
            
            var showPage = function(pageNo) {
                
                jQuery.bbq.pushState({
                    "cluster": { 
                        "lat": cluster.getCenter().lat(),
                        "lng": cluster.getCenter().lng(),
                        "page" : pageNo
                    }
                });
                
                cluster.dialog.children().hide();
                if(cluster.pages[pageNo]) {
                    cluster.pages[pageNo].show();
                } else {
                    var page = pagetemplate.clone();
                    cluster.pages[pageNo] = page;
                    cluster.dialog.append(page);
                    var mkrs = markers.slice(pageSize * pageNo, pageSize * (pageNo + 1));
                    var body = page.find(".artmaps-map-object-list-container-page-body");
                    jQuery.each(mkrs, function(i, marker) {
                        var content = jQuery(document.createElement("div"))
                                .html("<img src=\"" + ArtMapsConfig.LoadingIcon25x25Url + "\" />");
                        body.append(content);
                        marker.ObjectOfInterest.Metadata(function(metadata){
                            content.replaceWith(ArtMaps.Map.UI.formatMetadata(
                                    marker.ObjectOfInterest,
                                    metadata));
                        });
                    });
                    
                    var current = page.find(".artmaps-map-object-list-container-page-current");
                    
                    current.empty();
                    for(var i = 0; i < totalPages; i++) {
                        if(i == pageNo)
                            current.append(jQuery("<b>" + (i + 1) + "</b>"));
                        else {
                            var b = jQuery(document.createElement("span"));
                            b.text(i + 1);
                            (function(j) {
                                b.click(function() {
                                    showPage(j); 
                                });
                            })(i);
                            current.append(b);
                        }
                        current.append(" ");                            
                    }
                    
                    var previous = page.find(".artmaps-map-object-list-container-page-previous");
                    if(pageNo == 0) {
                        previous.off("click");
                        previous.addClass("disabled");
                        previous.removeClass("artmaps-button");
                    } else {
                        previous.show();
                        previous.off("click");
                        previous.removeClass("disabled");
                        previous.addClass("artmaps-button");
                        previous.click(function() {
                           showPage(pageNo - 1); 
                        });
                    }
                    
                    var next = page.find(".artmaps-map-object-list-container-page-next");
                    if(pageNo + 1 < totalPages) {
                        next.show();
                        next.off("click");
                        next.removeClass("disabled");
                        next.addClass("artmaps-button");
                        next.click(function() {
                           showPage(pageNo + 1); 
                        });
                    } else {
                        next.off("click");
                        next.addClass("disabled");
                        next.removeClass("artmaps-button");
                    }                    
                }
            };
            
            cluster.dialog.dialog({
                "show": { 
                        "effect": "fade",
                        "speed": 1,
                        "complete": function() { showPage(0); }
                 },
                "hide": { "effect": "fade", "speed": 1 },
                "width": 380,
                "height": jQuery(window).height() - 40,
                "position": "right",
                "resizable": true,
                "open": function() {
                    jQuery(ArtMaps).trigger("artmaps-dialog-opened");
                    jQuery(ArtMaps).on("artmaps-dialog-opened", cluster.dialog.closeFunc);
                },
                "close": function() {
                    jQuery(ArtMaps).off("artmaps-dialog-opened", cluster.dialog.closeFunc);
                    if(!cluster.dialog.otherOpening)
                        jQuery.bbq.removeState("cluster");
                    cluster.dialog.otherOpening = false;
                },
                "title": "Artworks at this location"
            });
        });
    })();
    
    (function() {
        var unlocated = jQuery(document.createElement("label")).text("Artworks with no suggestions")
            .append(
                    jQuery(document.createElement("input"))
                            .attr({
                                "type": "radio",
                                "name": "artmaps-map-filter"
                            })
                            .click(function() {
                                map.setFilter(function(m, l) {
                                    if(m.ObjectOfInterest.SuggestionCount == 0)
                                        l.push(m);
                            });
                    }));
        
        var located = jQuery(document.createElement("label")).text("Artworks with suggestions")
            .append(
                    jQuery(document.createElement("input"))
                            .attr({
                                "type": "radio",
                                "name": "artmaps-map-filter"
                            })
                            .click(function() {
                                map.setFilter(function(m, l) {
                                    if(m.ObjectOfInterest.SuggestionCount != 0)
                                        l.push(m);
                            });
                    }));
        
        var comments = jQuery(document.createElement("label")).text("Artworks with comments")
            .append(
                    jQuery(document.createElement("input"))
                            .attr({
                                "type": "radio",
                                "name": "artmaps-map-filter"
                            })
                            .click(function() {
                                map.setFilter(function(m, l) {
                                    if(m.ObjectOfInterest.HasComments) l.push(m);
                            });
                    }));
        
        var nocomments = jQuery(document.createElement("label")).text("Artworks with no comments")
            .append(
                    jQuery(document.createElement("input"))
                            .attr({
                                "type": "radio",
                                "name": "artmaps-map-filter"
                            })
                            .click(function() {
                                map.setFilter(function(m, l) {
                                    if(!m.ObjectOfInterest.HasComments) l.push(m);
                            });
                    }));
        
        var reset = jQuery(document.createElement("label")).text("No filter")
            .append(
                    jQuery(document.createElement("input"))
                            .attr({
                                "type": "radio",
                                "name": "artmaps-map-filter",
                                "checked": "checked"
                            })
                            .click(function() {
                                map.setFilter(function(m, l) {
                                    l.push(m);
                            });
                    }));
        
        var panel = jQuery(document.createElement("div"))
                .attr("id", "artmaps-filter-menu")
                .css("background-color", "white")
                .append(jQuery("<b>FILTER ARTWORKS</b><br />"))
                .append(unlocated)
                .append(jQuery(document.createElement("br")))
                .append(located)
                .append(jQuery(document.createElement("br")))
                .append(comments)
                .append(jQuery(document.createElement("br")))
                .append(nocomments)
                .append(jQuery(document.createElement("br")))
                .append(reset);
        
        map.controls[google.maps.ControlPosition.LEFT_TOP].push(panel.get(0));
    })();

    this.bindAutocomplete = function(autoComplete) {
        autoComplete.bindTo("bounds", map);
        google.maps.event.addListener(autoComplete, "place_changed", function() {
            var place = autoComplete.getPlace();
            if(place.id) {
                if(place.geometry.viewport)
                    map.fitBounds(place.geometry.viewport);
                else{
                    map.setCenter(place.geometry.location);
                    map.setZoom(12);
                }
            }
        });
    };
    
    this.addControl = function(control, position) {
        map.controls[position].push(control);
    };    
};
