/* Namespace: ArtMaps.Map */
ArtMaps.Map = ArtMaps.Map || {};

ArtMaps.Map.MapObject = function(container, config) {

    var runOnce = new ArtMaps.Util.RunOnce();
    var workerPool = new ArtMaps.WorkerPool(1, ArtMapsConfig.ThemeDirUrl + "/js/do-get.js");
    var map = new google.maps.Map(container.get(0), config.mapConf);
    
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
        
    var clusterer = new MarkerClusterer(map, [], config.clustererConf);
    
    var loadingControl = jQuery(document.createElement("img"))
            .attr("src", ArtMapsConfig.ThemeDirUrl + "/content/loading/50x50.gif")
            .attr("alt", "")
            .css("display", "none");
    map.controls[google.maps.ControlPosition.LEFT_CENTER].push(loadingControl.get(0));
    var updateCounter = 0;
    var loadedObjects = {};
    map.on("idle", function() {
        runOnce.runAfter(function() {
        	var centre = map.getCenter();
        	jQuery.bbq.pushState({
        	    "zoom": map.getZoom(),
        	    "lat": centre.lat(),
        	    "lng": centre.lng(),
        	    "maptype": map.getMapTypeId() 
            });
            updateCounter++;
            if(updateCounter > 0)
                loadingControl.css("display", "inline");
            var bounds = map.getBounds();
            workerPool.queueTask(ArtMapsConfig.CoreServerPrefix + "objectsofinterest/search/?"
                    + "boundingBox.northEast.latitude=" + ArtMaps.Util.toIntCoord(bounds.getNorthEast().lat())
                    + "&boundingBox.southWest.latitude=" + ArtMaps.Util.toIntCoord(bounds.getSouthWest().lat())
                    + "&boundingBox.northEast.longitude=" + ArtMaps.Util.toIntCoord(bounds.getNorthEast().lng())
                    + "&boundingBox.southWest.longitude=" + ArtMaps.Util.toIntCoord(bounds.getSouthWest().lng()),
                function(objects) {
                    var markers = [];
                    jQuery.each(objects, function(i, o) {
                        if(loadedObjects[o.ID] == true)
                            return;
                        loadedObjects[o.ID] = true;
                        var obj = new ArtMaps.ObjectOfInterest(o);
                        jQuery.each(obj.Locations, function(j, loc) {
                            if(loc.Source != "SystemImport") return;
                            var marker = new ArtMaps.UI.Marker(loc, map);
                            markers.push(marker);
                        });
                    });
                    clusterer.addMarkers(markers);
                    updateCounter--;
                    if(updateCounter < 1)
                        loadingControl.css("display", "none");
                });
            }, 500);
        });
   
    clusterer.on("click", function(cluster) {
    	
        var markers = cluster.getMarkers();
        if(!markers || !markers.length) return;
        jQuery(".artmaps-popup").remove();
        var firstLoad = false;
        if(!cluster.overlay) {
            firstLoad = true;
            cluster.overlay = jQuery("<div class=\"artmaps-object-list-popup\"></div>");
        }
        
        var pageSize = 10;
        var pages = {};
        var loadObjects = function (pageNo) {
            cluster.overlay.children(".artmaps-object-list-popup-object")
                    .css("display", "none");
            if(pages[pageNo]) {
                pages[pageNo].css("display", "block");
                return;
            }
            
            var div = jQuery("<div class=\"artmaps-object-list-popup-object\"></div>");
            cluster.overlay.append(div);
            pages[pageNo] = div;
            
        	var numPages = Math.floor(markers.length / pageSize);
        	if(markers.length % pageSize != 0) { numPages++; }
        	var startMarker = pageSize * pageNo;
    		var endMarker = pageSize * (pageNo + 1);
    		var pageMarkers =  markers.slice(startMarker,endMarker); 
        	jQuery.each(pageMarkers, function(i, marker) {
	                var content = jQuery(document.createElement("div"))
	                    .addClass("artmaps-object-popup");
	                content.html("<img src=\"" + ArtMapsConfig.ThemeDirUrl + "/content/loading/25x25.gif\" alt=\"\" />");
	                div.append(content);
	                marker.location.ObjectOfInterest.runWhenMetadataLoaded(function(metadata){
	                    content.replaceWith(ArtMaps.UI.formatMetadata(
	                            marker.location.ObjectOfInterest,
	                            metadata,
	                            marker.location));
	                });
	            });
        	
    		var navbar = jQuery("<div></div>");
    		if(pageNo > 0) {
    		    navbar.append(
    		            jQuery("<a href=\"#\">[Previous]&nbsp;</a>").click(function() {
    		                loadObjects(pageNo - 1);
    		            })
    		    );
    		}
    		
    		navbar.append(jQuery("<span></span>").text("Page " + (pageNo + 1) + " of " + numPages));
    		
    		if(pageNo + 1 < numPages) {
    		    navbar.append(
                        jQuery("<a href=\"#\">&nbsp;[Next]</a>").click(function() {
                            loadObjects(pageNo + 1);
                        })
                );
    		}
    		div.prepend(navbar);
    		div.append(navbar.clone(true));
        };
                
        cluster.overlay.dialog({
            "autoOpen": true,
            "show": { "effect": "fade", "speed": 1, "complete": firstLoad ? loadObjects(0) : function() {} },
            "hide": { "effect": "fade", "speed": 1 },
            "resizable": false,
            "dialogClass": "artmaps-popup"
        });
    });

    this.switchMapType = function(type) {
        map.setMapTypeId(type);
        var hashstate = jQuery.bbq.getState();
        hashstate.maptype = type;
        jQuery.bbq.pushState(hashstate);
    };
    
    this.getMapType = function() {
        return map.getMapTypeId();
    };

    this.registerAutocomplete = function(autoComplete) {
        autoComplete.bindTo("bounds", map);
        google.maps.event.addListener(autoComplete, "place_changed", function() {
            var place = autoComplete.getPlace();
            if(place.id)
                if(place.geometry.viewport)
                    map.fitBounds(place.geometry.viewport);
                else{
                    map.setCenter(place.geometry.location);
                    map.setZoom(12);
                }
            jQuery(".artmaps-popup").remove();
        });
    };
};
