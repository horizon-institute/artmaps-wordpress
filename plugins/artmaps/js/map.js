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

    var workerPool = new ArtMaps.RunOnce(ArtMapsConfig.PluginDirUrl + "/js/do-get.js");
    var map = new google.maps.Map(container.get(0), jQuery.extend(true, mapconf, config.map));
    var clusterer = new MarkerClusterer(map, [], jQuery.extend(true, clusterconf, config.cluster));
    
    (function() {
        var hashstate = jQuery.bbq.getState();
        if(hashstate.maptype) {
            map.setMapTypeId(hashstate.maptype);
        }
        if(hashstate.zoom) {
            map.setCenter(new google.maps.LatLng(hashstate.lat, hashstate.lng));
            map.setZoom(parseInt(hashstate.zoom));
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
        var loading = jQuery(document.createElement("img"))
                .attr("src", ArtMapsConfig.LoadingIcon50x50Url)
                .attr("alt", "")
                .css("display", "none");
        map.controls[google.maps.ControlPosition.LEFT_CENTER].push(loading.get(0));
        var cache = {};
        map.on("idle", function() {
            var centre = map.getCenter();
            jQuery.bbq.pushState({
                "zoom": map.getZoom(),
                "lat": centre.lat(),
                "lng": centre.lng(),
                "maptype": map.getMapTypeId() 
            });
            var bounds = map.getBounds();
            workerPool.queueTask(ArtMapsConfig.CoreServerPrefix + "objectsofinterest/search/?"
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
                        jQuery.each(obj.Locations, function(j, loc) {
                            if(loc.Source != "SystemImport") return;
                            var marker = new ArtMaps.Map.UI.Marker(obj, loc);
                            markers.push(marker);
                        });
                    });
                    clusterer.addMarkers(markers);
                    loading.css("display", "none");
                });
        });
    })();
    
    
    (function(){
        var pagetemplate = jQuery("#artmaps-map-object-list-container-page").children().first();
        jQuery("#artmaps-object-list-container-page").detach();
        clusterer.on("click", function(cluster) {
            var markers = cluster.getMarkers();
            if(!markers || !markers.length) return;
            
            var pageSize = 10;
            var totalPages = Math.floor(markers.length / pageSize);
            if(markers.length % pageSize != 0) totalPages++;
                        
            if(!cluster.dialog) {
                cluster.dialog = jQuery(document.createElement("div"))
                        .addClass("artmaps-map-object-list-container");
                cluster.pages = new Array();
                cluster.dialog.closeFunc = function() {
                    cluster.dialog.dialog("close"); 
                };
            }
            
            var showPage = function(pageNo) {
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
                                .html("<img src=\"" + ArtMapsConfig.LoadingIcon25x25Url);
                        body.append(content);
                        marker.ObjectOfInterest.Metadata(function(metadata){
                            content.replaceWith(ArtMaps.Map.UI.formatMetadata(
                                    marker.ObjectOfInterest,
                                    metadata));
                        });
                    });
                    
                    var current = page.find(".artmaps-map-object-list-container-page-current");
                    current.text("Page " + (pageNo + 1) + " of " + totalPages);
                    
                    var previous = page.find(".artmaps-map-object-list-container-page-previous");
                    if(pageNo == 0) {
                        previous.hide();
                    } else {
                        previous.show();
                        previous.off("click");
                        previous.click(function() {
                           showPage(pageNo - 1); 
                        });
                    }
                    
                    var next = page.find(".artmaps-map-object-list-container-page-next");
                    if(pageNo + 1 < totalPages) {
                        next.show();
                        next.off("click");
                        next.click(function() {
                           showPage(pageNo + 1); 
                        });
                    } else {
                        next.hide();
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
                "resizable": false,
                "open": function() {
                    jQuery(ArtMaps).trigger("artmaps-dialog-opened");
                    jQuery(ArtMaps).on("artmaps-dialog-opened", cluster.dialog.closeFunc);
                },
                "close": function() {
                    jQuery(ArtMaps).off("artmaps-dialog-opened", cluster.dialog.closeFunc);
                }
            });
        });
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
