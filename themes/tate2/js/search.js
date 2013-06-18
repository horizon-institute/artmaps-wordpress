/* Namespace: ArtMaps.Search */
ArtMaps.Search = ArtMaps.Search || {};

ArtMaps.Search.objectSearch = function(term, result, complete, page) {
    console.log(page);
    jQuery.ajax(
            ArtMapsConfig.CoreServerPrefix + "external/search?s=" 
                    + ArtMapsConfig.SearchSource + "://" + term + "&p=" + page,
            {
                "dataType": "json",
                "success": function(data) {
                    if(data == null || data.length == 0) {
                        complete(null);
                    } else {
                        jQuery.each(data, function(i, o) {
                            jQuery.ajax(
                                    ArtMapsConfig.CoreServerPrefix + "objectsofinterest/" + o.ID + "/metadata",
                                    {
                                        "dataType": "json",
                                        "success": function(metadata) {
                                            result(o, metadata);
                                        }
                                    });
                        });
                        complete(page + 1);
                    }
                }
            });
};
                        