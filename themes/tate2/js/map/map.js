/* Namespace: ArtMaps.Map */
ArtMaps.Map = ArtMaps.Map || {};

ArtMaps.Map.MapObject = function(container, config) {

    var runOnce = new ArtMaps.Util.RunOnce();
    var runMapOnce = new ArtMaps.Util.RunOnce();
    var map = new google.maps.Map(container.get(0), config.mapConf);
    
    var hashstate = jQuery.bbq.getState();
    if(hashstate.zoom) {
        map.setCenter(new google.maps.LatLng(hashstate.lat, hashstate.lng));
        map.setZoom(parseInt(hashstate.zoom));
        
    } else {
        ArtMaps.Util.browserLocation(
            function(pos) { map.setCenter(pos); },
            function() { });
    }
    if(hashstate.maptype)
        map.setMapTypeId(hashstate.maptype);
    
    var clusterer = new MarkerClusterer(map, [], config.clustererConf);
    clusterer.on("clusteringend", function() {
            jQuery.each(clusterer.getClusters(),
                    function(i, cluster) {
                        if(cluster.getSize() > config.clustererConf.minimumClusterSize)
                            return;
                        /*jQuery.each(cluster.getMarkers(), function(j, marker) {
                            marker.location.ObjectOfInterest.runWhenMetadataLoaded();
                        });*/
            });
    });

    var loadingControl = jQuery(document.createElement("img"))
            .attr("src", ArtMapsConfig.ThemeDirUrl + "/content/loading/50x50.gif")
            .attr("alt", "")
            .css("display", "none");
    map.controls[google.maps.ControlPosition.LEFT_CENTER].push(loadingControl.get(0));
    var updateCounter = 0;
    var loadedObjects = {};
    map.on("idle", function() {
        runMapOnce.runAfter(function() {
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
            //jQuery.getJSON
            metadataLoaderPool.queueTask(ArtMapsConfig.CoreServerPrefix + "objectsofinterest/search/?"
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
                            //if(map.hasObjectMarker(loc.ID)) return;
                            // Future: At present, only display the original pin on the map
                            // view.  In future, if there is an 'accepted' pin, this will
                            // instead be displayed.
                            if(loc.Source != "SystemImport") return;
                            var marker = new ArtMaps.UI.Marker(loc, map);
                            markers.push(marker);
                            map.putObjectMarker(loc.ID, marker);
                        });
                    });
                    clusterer.addMarkers(markers);
                    updateCounter--;
                    if(updateCounter < 1)
                        loadingControl.css("display", "none");
                });
            }, 1000);
        });

    /*clusterer.on("click", function(cluster) {
        var markers = cluster.getMarkers();
        if(!markers || !markers.length) return;
        jQuery(".artmaps-popup").remove();
        var firstLoad = false;
        if(!cluster.overlay) {
            firstLoad = true;
            cluster.overlay = jQuery("<div class=\"artmaps-object-list-popup\"></div>");
        }
        
        var loadObjects = function () {
            jQuery.each(markers, function(i, marker) {
                var content = jQuery(document.createElement("div"))
                    .addClass("artmaps-object-popup");
                content.html("<img src=\"" + ArtMapsConfig.ThemeDirUrl + "/content/loading/25x25.gif\" alt=\"\" />");
                cluster.overlay.append(content);
                marker.location.ObjectOfInterest.runWhenMetadataLoaded(function(metadata){
                    content.replaceWith(ArtMaps.UI.formatMetadata(
                            marker.location.ObjectOfInterest,
                            metadata,
                            marker.location));
                });
            });
        };*/
    clusterer.on("click", function(cluster) {
    	
        var markers = cluster.getMarkers();
        if(!markers || !markers.length) return;
        jQuery(".artmaps-popup").remove();
        var firstLoad = false;
        if(!cluster.overlay) {
            firstLoad = true;
            cluster.overlay = jQuery("<div class=\"artmaps-object-list-popup\"></div>");
            
        }
        
        var loadObjects = function (pageNo) {
        	//console.log(pageNo);
        	cluster.overlay.empty();
        	var numPages = Math.floor(markers.length/10) + 1;
        	if(markers.length%10 == 0){
        		numPages = Math.floor(markers.length/10); 
        		//console.log(numPages);
        	}
        	//console.log(numPages);
        	var startMarker = 10*pageNo;
    		var endMarker = 10*(pageNo+1);       	
        	//var pageMarkers =  markers.slice(0,10);
    		//for (var i = 1; i <= numPages; i++) {
    		var pageMarkers =  markers.slice(startMarker,endMarker); 
        	jQuery.each(pageMarkers, function(i, marker) {
	                var content = jQuery(document.createElement("div"))
	                    .addClass("artmaps-object-popup");
	                content.html("<img src=\"" + ArtMapsConfig.ThemeDirUrl + "/content/loading/25x25.gif\" alt=\"\" />");
	                cluster.overlay.append(content);
	                marker.location.ObjectOfInterest.runWhenMetadataLoaded(function(metadata){
	                    content.replaceWith(ArtMaps.UI.formatMetadata(
	                            marker.location.ObjectOfInterest,
	                            metadata,
	                            marker.location));
	                });
	            });
        		pageNo++;
        		var buttonPrev = jQuery("<div class=\"artmaps-previous-button\"> Previous </div>")
           		var buttonNext = jQuery("<div class=\"artmaps-next-button\"> Next </div>")
           		var page = jQuery("<div class=\"artmaps-page-number\">" + pageNo + "</div>")
        		/*var buttonPrev = jQuery("<div> Previous </div>")
           		var buttonNext = jQuery("<div> Next </div>")
           		var page = jQuery("<div>" + pageNo + "</div>")*/
        		buttonPrev.click(function() {
        			loadObjects(pageNo-2);
        		});
        		buttonNext.click(function() {
        			loadObjects(pageNo);
        		});
        		if(numPages>1){
	        		if(pageNo<numPages){        			   		
		        		if(pageNo>1){
	        				cluster.overlay.append(buttonPrev);
		        		}
		        		cluster.overlay.append(page);
		        		cluster.overlay.append(buttonNext);
	        		}else{
	        			if(pageNo>1){
	        				cluster.overlay.append(buttonPrev);
		        		}
		        		cluster.overlay.append(page);
	        		}
        		}        	
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
