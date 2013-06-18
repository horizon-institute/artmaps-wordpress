/* Namespace: ArtMaps.Map */
ArtMaps.Map = ArtMaps.Map || {};

ArtMaps.Map.MapObject = function(container, config) {

    var self = this;
    
    var mapconf = {
            "scrollwheel": true,
            "center": new google.maps.LatLng(0, 0),
            "streetViewControl": true,
            "zoom": 15,
            "mapTypeId": google.maps.MapTypeId.SATELLITE,
            "zoomControlOptions": {
                "position": google.maps.ControlPosition.LEFT_CENTER
            },
            "panControl": false,
            "mapTypeControl": false
    };
    
    var clusterconf = {
            "gridSize": 150,
            "minimumClusterSize": 2,
            "zoomOnClick": true,
            "imageSizes": [56],
            "styles": [{
                "url": ArtMapsConfig.ThemeDirUrl + "/content/cluster.png",
                "height": 56,
                "width": 56
            }] 
    };
    
    jQuery.extend(true, mapconf, config.map);
    var mapType = jQuery.bbq.getState("maptype");
    if(mapType) mapconf.mapTypeId = mapType;
    var map = new google.maps.Map(container.get(0), mapconf);
    var clusterer = new MarkerClusterer(map, [], jQuery.extend(true, clusterconf, config.cluster));
    
    var suggestionRequested = false;
        
    jQuery.getJSON(ArtMapsConfig.CoreServerPrefix + "objectsofinterest/" + ArtMapsConfig.ObjectID,
		function(object) {
        
            var obj = new ArtMaps.ObjectOfInterest(object);
        
            var markers = new Array();
			jQuery.each(obj.Locations, function(i, loc) {
                markers.push(new ArtMaps.UI.Marker(loc, map, function() { self.suggest(); }));
			});			
			clusterer.addMarkers(markers);
			self.reset();
			
			var suggestionMarker = new ArtMaps.UI.SuggestionMarker(map, obj, clusterer); 
            self.suggest = function() {
                jQuery.each(markers, function(i, m) { m.close(); });
                suggestionMarker.show();
            };
			if(suggestionRequested) self.suggest();
		}
	);

    this.setMapType = function(type) {
        map.setMapTypeId(type);
    };
    
    this.getMapType = function() { 
        return map.getMapTypeId(); 
    };
    
    this.getCenter = map.getCenter;
    
    this.suggest = function() { 
        suggestionRequested = true;
    };
        
    this.reset = function() {
        var markers = clusterer.getMarkers(); 
        if(markers.length == 0) {
            map.setCenter(mapconf.center); 
            map.setZoom(mapconf.zoom);
        } else if(markers.length == 1) {
            map.setCenter(markers[0].getPosition()); 
            map.setZoom(mapconf.zoom);
        } else
            clusterer.fitMapToMarkers();
        clusterer.repaint();
    };
};
