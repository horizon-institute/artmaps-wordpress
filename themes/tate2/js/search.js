/* Namespace: ArtMaps.Search */
ArtMaps.Search = ArtMaps.Search || {};

ArtMaps.Search.formatSearchResult = function(metadata) {
    return String(metadata);
};

ArtMaps.Search.objectSearch = function(query, callback) {
    callback([{"label": "Searching...", "value": "-1"}]);
    var page = 0;
    var term = "";
    var res = new Array();
    if(typeof(query.term) == "string") {
        term = query.term;
    } else {
        term = query.term.item.term;
        page = query.term.item.page;
        res = query.term.item.results;
    }
    if(res.length > 0)
        callback(res);
    jQuery.ajax(
        ArtMapsConfig.CoreServerPrefix + "external/search?s=" 
                + ArtMapsConfig.SearchSource + "://" + term + "&p=" + page,
        {
            "dataType": "json",
            "success": function(data) {
                if(data == null || data.length == 0) {
                    callback([{"label": "No results found", "value": "-1"}]);
                } else {
                    jQuery.each(data, function(i, o) {
                        jQuery.ajax(
                                ArtMapsConfig.CoreServerPrefix + "objectsofinterest/" + o.ID + "/metadata",
                                {
                                    "dataType": "json",
                                    "success": function(metadata) {
                                        res.unshift({
                                            "label": ArtMaps.Search.formatSearchResult(metadata),
                                            "value": o.ID
                                        });
                                        callback(res);
                                    }
                                });
                    });  
                    if(page > 0) 
                        res.pop();
                    res.push({
                        "label": "Keep searching...",
                        "value": -10,
                        "term": term,
                        "page": (page + 1),
                        "results" : res
                    });
                    callback(res);
                }
            }
        }
    );
};