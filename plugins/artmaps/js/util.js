/* Namespace: ArtMaps.Util */
ArtMaps.Util = ArtMaps.Util || {};

ArtMaps.Util.browserLocation = function(success, failure) {
    var ipInfoDb = function() {
        jQuery.ajax({
            "type": "GET",
            "url": "http://api.ipinfodb.com/v3/ip-city/?format=json&key=" 
                    + ArtMapsConfig.IpInfoDbApiKey,
            "async": false,
            "dataType": "jsonp",
            "success": function(data) {
                success(new google.maps.LatLng(data.latitude, data.longitude));
            },
            "error": failure
        });
    };    
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
                function(pos) {
                    success(new google.maps.LatLng(
                            pos.coords.latitude, pos.coords.longitude));
                },
                ipInfoDb
        );
    } else { ipInfoDb(); }
};

ArtMaps.Util.toIntCoord = function(f) {
    return parseInt(f * Math.pow(10, 8));
};

ArtMaps.Util.toFloatCoord = function(i) {
    return parseFloat(i) / Math.pow(10, 8);
};

ArtMaps.Util.confirmLocation = function(location, success) {
    jQuery.ajax(ArtMapsConfig.AjaxUrl, {
        "type": "post",
        "data": {
            "action": "artmaps.signData",
            "data": {
                "URI": "confirmation://{\"LocationID\":" + location.ID + "}"
            }
        },
        "success": function(signed) {
            jQuery.ajax(ArtMapsConfig.CoreServerPrefix 
                    + "objectsofinterest/" + location.ObjectOfInterest.ID + "/actions", {
                "type": "post",
                "data": JSON.stringify(signed),
                "dataType": "json",
                "contentType": "application/json",
                "processData": false,
                "success": success
            });
        }
    });        
};

ArtMaps.Util.removeLocation = function(location, success) {
    jQuery.ajax(ArtMapsConfig.AjaxUrl, {
        "type": "post",
        "data": {
            "action": "artmaps.signData",
            "data": {
                "URI": "deletion://{\"LocationID\":" + location.ID + "}"
            }
        },
        "success": function(signed) {
            jQuery.ajax(ArtMapsConfig.CoreServerPrefix 
                    + "objectsofinterest/" + location.ObjectOfInterest.ID + "/actions", {
                "type": "post",
                "data": JSON.stringify(signed),
                "dataType": "json",
                "contentType": "application/json",
                "processData": false,
                "success": success
            });
        }
    });        
};

ArtMaps.Util.suggestLocation = function(object, position, success, failure) {
    jQuery.ajax(ArtMapsConfig.AjaxUrl, {
        "type": "post",
        "data": {
            "action": "artmaps.signData",
            "data": {
                "error": 0,
                "latitude": ArtMaps.Util.toIntCoord(position.lat()),
                "longitude": ArtMaps.Util.toIntCoord(position.lng())
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
                                    success(location, action);
                                },
                                "error": failure
                            });
                        },
                        "error": failure
                    });
                },
                "error": failure
            });
        },
        "error": failure
    });  
};
