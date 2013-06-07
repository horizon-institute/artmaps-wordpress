/* Namespace: ArtMaps.Map */
ArtMaps.Map = ArtMaps.Map || {};

ArtMaps.Map.MapObject = function(container, config) {

    var self = this;
    
    var mapconf = {
            "scrollwheel": true,
            "center": new google.maps.LatLng(0, 0),
            "streetViewControl": true,
            "zoom": 1,
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
    var lat = jQuery.bbq.getState("lat");
    var lng = jQuery.bbq.getState("lng");
    if(lat && lng) mapconf.center = new google.maps.LatLng(lat, lng);
    var zoom = jQuery.bbq.getState("zoom");
    if(zoom) mapconf.zoom =  parseInt(zoom);
    var map = new google.maps.Map(container.get(0), mapconf);
    var clusterer = new MarkerClusterer(map, [], jQuery.extend(true, clusterconf, config.cluster));
    
    var suggestionRequested = false;
        
    jQuery.getJSON(ArtMapsConfig.CoreServerPrefix + "objectsofinterest/" + config.objectID,
		function(object) {
        
            var obj = new ArtMaps.ObjectOfInterest(object);
        
            var markers = new Array();
			jQuery.each(obj.Locations, function(i, loc) {
                markers.push(new ArtMaps.UI.Marker(loc, map, self));
			});
			
			if(markers.length > 0) {
			    clusterer.addMarkers(markers);
			    clusterer.fitMapToMarkers();
			}
			
			var suggestionMarker = new ArtMaps.UI.SuggestionMarker(map, obj, clusterer); 
            self.suggest = function() {
                jQuery(ArtMaps.UI).trigger("suggest");
                suggestionMarker.show();
            };
			
			if(suggestionRequested)
                self.suggest();
		}
	);

    this.setMapType = function(type) {
        map.setMapTypeId(type);
        jQuery.bbq.pushState({"maptype" : type});
    };
    
    this.getCenter = map.getCenter;
    
    this.suggest = function() { 
        suggestionRequested = true;
    };
        
    this.reset = function() {
        clusterer.fitMapToMarkers();
        clusterer.repaint();
    };
};
