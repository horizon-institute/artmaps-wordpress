/* Namespace: ArtMaps.Map */
ArtMaps.Map = ArtMaps.Map || {};

ArtMaps.Map.MapObject = function(container, config) {

    var self = this;
    
    var mapType = jQuery.bbq.getState("maptype");
    if(mapType) config.mapConf.mapTypeId = mapType;
    var map = new google.maps.Map(container.get(0), config.mapConf);
    var clusterer = new MarkerClusterer(map, [], config.clustererConf);
    
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
