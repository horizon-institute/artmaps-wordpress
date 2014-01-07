/* Namespace: ArtMaps.Map */
ArtMaps.Map = ArtMaps.Map || {};

ArtMaps.Map.MapObject = function(container, config) {

var styles =  [
      {
        "featureType": "poi.business",
        "stylers": [
          { "visibility": "off" }
        ]
      }
    ];
        
    var mapconf = {
            "center": new google.maps.LatLng(0, 0),
            "streetViewControl": false,
            "zoom": 15,
            "minZoom": 3,
            "styles": styles,
            "maxZoom": 20,
            "mapTypeId": google.maps.MapTypeId.ROADMAP,
            "zoomControlOptions": {
                "position": google.maps.ControlPosition.LEFT_CENTER             },
            "panControl": false,
            "mapTypeControl": false
        };
    
    var clusterconf = {
            "gridSize": 100,
            "minimumClusterSize": 1,
            "zoomOnClick": false,
            "imageSizes": [53],
            "styles": [{
                "url": ArtMapsConfig.ClusterIconUrl,
                "width": 42,
                "height": 53,
                "anchorText": ['-15px',0],
                "anchorIcon": [21,53],
                "textColor": '#ffffff',
                "textSize": 11
            }]
        };   

    var map = new google.maps.Map(container.get(0), jQuery.extend(true, mapconf, config.map));
    var clusterer = new MarkerClusterer(map, [], jQuery.extend(true, clusterconf, config.cluster));
    var firstLoad = true;
        
    // Maintain location when window resized
    google.maps.event.addDomListener(window, "resize", function() {
      var center = map.getCenter();
      google.maps.event.trigger(map, "resize");
      map.setCenter(center); 
    });
        
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
    
    
    var myloc = new google.maps.Marker({
        "icon": ArtMapsConfig.MyLocationIconUrl,
        "optimized": false,
        "title": "Your location"
    });
    ArtMaps.Util.watchLocation(function (pos) { 
         myloc.setMap(map);
         myloc.setPosition(pos);
    });
    
    
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
        var loading = jQuery(".loading-indicator");
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
                "lat": (centre.lat()).toFixed(2),
                "lng": (centre.lng()).toFixed(2),
                "maptype": map.getMapTypeId() 
            });
            var bounds = map.getBounds();
            runOnce.queueTask(ArtMapsConfig.CoreServerPrefix + "objectsofinterest/search/?"
                    + "boundingBox.northEast.latitude=" + ArtMaps.Util.toIntCoord(bounds.getNorthEast().lat())
                    + "&boundingBox.southWest.latitude=" + ArtMaps.Util.toIntCoord(bounds.getSouthWest().lat())
                    + "&boundingBox.northEast.longitude=" + ArtMaps.Util.toIntCoord(bounds.getNorthEast().lng())
                    + "&boundingBox.southWest.longitude=" + ArtMaps.Util.toIntCoord(bounds.getSouthWest().lng()),
                function() {
                    loading.fadeIn();
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
                            else if(systemloc == null) { systemloc = loc; }
                        });
                        var marker = new ArtMaps.Map.UI.Marker(obj, finalloc != null ? finalloc : systemloc);
                        filter(marker, markers);
                    });
                    clusterer.addMarkers(markers);
                    loading.fadeOut();
                    
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
            jQuery('.popover').fadeOut(150);

            cluster.custom_center = ( jQuery(window).width() * 0.12 );
            offsetCenter(new google.maps.LatLng( cluster.getCenter().lat(), cluster.getCenter().lng() ), -Math.abs(cluster.custom_center));

            if(!markers || !markers.length) return;
            
            var pageSize = 15;
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
                        "lat": (cluster.getCenter().lat()).toFixed(2),
                        "lng": (cluster.getCenter().lng()).toFixed(2),
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
                                .html("<span class=\"mini-loading-indicator\"></span>");
                        body.append(content);
                        marker.ObjectOfInterest.Metadata(function(metadata){
                            content.replaceWith(ArtMaps.Map.UI.formatMetadata(
                                    marker.ObjectOfInterest,
                                    metadata));
                        });
                    });
                    
                    var current = page.find(".artmaps-map-object-list-container-page-current");
                    
                    current.empty();
                    if(totalPages>1) {
                      current.append("Page " + (pageNo+1) + " of " + totalPages);
                    }
                    /*for(var i = 0; i < totalPages; i++) {
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
                    }*/
                    
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
            
            jQuery(".ui-dialog-content").not(cluster).dialog("close");
            cluster.dialog.dialog({
                "show": { 
                    "duration": 0,
                    "complete": function() { showPage(0); }
                 },
                "hide": { "duration": 0 },
                "width": 260,
                "dialogClass": "artwork-results",
                "height": jQuery(window).height() - 160,
                "position": "right bottom",
                "resizable": false,
                "closeText": "",
                "draggable": false,
                "open": function() {
                    jQuery("#artmaps-search-results-artworks").dialog("close");
                    jQuery(ArtMaps).trigger("artmaps-dialog-opened");
                    jQuery(ArtMaps).on("artmaps-dialog-opened", cluster.dialog.closeFunc);
                },
                "close": function() {
                    jQuery(ArtMaps).off("artmaps-dialog-opened", cluster.dialog.closeFunc);
                    jQuery.fancybox.close();
                    if(!cluster.dialog.otherOpening) {
                      jQuery.bbq.removeState("cluster");
                      var centre = map.getCenter();
                      var custom_center = jQuery(window).width() * 0.12;
                      offsetCenter(new google.maps.LatLng( centre.lat(), centre.lng() ), Math.abs(custom_center));
                    }
                    cluster.dialog.otherOpening = false;
                },
                "title": '<i class="fa-map-marker"></i>Artwork at this location'
            });
        });
    })();
    
    (function() {
    
    
    
    
    
    /* Custom map type toggle */
        var mode_map = jQuery(document.createElement("option"))
                            .attr({
                                "name": "artmaps-map-mode",
                                "id": "mode_map",
                                "selected": "selected"
                            })
                            .text('Map view');

        var mode_satellite = jQuery(document.createElement("option"))
                            .attr({
                                "name": "artmaps-map-mode",
                                "id": "mode_satellite",
                            })
                            .text('Satellite view');
                            
        var mode_hybrid = jQuery(document.createElement("option"))
                            .attr({
                                "name": "artmaps-map-mode",
                                "id": "mode_hybrid",
                            })
                            .text('Hybrid view');

        var mode_terrain = jQuery(document.createElement("option"))
                            .attr({
                                "name": "artmaps-map-mode",
                                "id": "mode_terrain",
                            })
                            .text('Terrain view');
    
        var map_mode_menu = jQuery(document.createElement("select"))
                .attr("id", "artmaps-map-mode")
                .attr("class", "gmnoprint")
                .append(mode_map)
                .append(mode_satellite)
                .append(mode_hybrid)
                .append(mode_terrain);
                
        map_mode_menu.change(function(){
          var id = jQuery(this).find("option:selected").attr("id");
        
          switch (id) {
            case "mode_map":
              map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
              break;
            case "mode_satellite":
              map.setMapTypeId(google.maps.MapTypeId.SATELLITE);
              break;
            case "mode_hybrid":
              map.setMapTypeId(google.maps.MapTypeId.HYBRID);
              break;
            case "mode_terrain":
              map.setMapTypeId(google.maps.MapTypeId.TERRAIN);
              break;
          }
        });
        
        jQuery('#map-settings .settings-inner').append(map_mode_menu);
        //map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(map_mode_menu.get(0));
    
    
    var unlocated = jQuery(document.createElement("option"))
                            .attr({
                                "name": "artmaps-map-filter",
                                "id": "unlocated"
                            })
                            .text('Artworks without suggestions');

                            
        var located = jQuery(document.createElement("option"))
                            .attr({
                                "name": "artmaps-map-filter",
                                "id": "located"
                            })
                            .text('Artworks with suggestions');
                            
        var comments = jQuery(document.createElement("option"))
                            .attr({
                                "name": "artmaps-map-filter",
                                "id": "comments"
                            })
                            .text('Artworks with comments');

        var nocomments = jQuery(document.createElement("option"))
                            .attr({
                                "name": "artmaps-map-filter",
                                "id": "nocomments"
                            })
                            .text('Artworks without comments');

        var reset = jQuery(document.createElement("option"))
                            .attr({
                                "name": "artmaps-map-filter",
                                "id": "reset",
                                "checked": "checked"
                            })
                            .text('All artworks');
        
        var panel = jQuery(document.createElement("select"))
                .attr("id", "artmaps-filter-menu")
                .attr("class", "gmnoprint")
                .append(reset)
                .append(located)
                .append(unlocated)
                .append(comments)
                .append(nocomments);
                
        panel.change(function(){
          var id = jQuery(this).find("option:selected").attr("id");
        
          switch (id) {
            case "reset":
              map.setFilter(function(m, l) {
                l.push(m);
              });
              break;
            case "nocomments":
              map.setFilter(function(m, l) {
                if(!m.ObjectOfInterest.HasComments) l.push(m);
              });
              break;
            case "comments":
              map.setFilter(function(m, l) {
                if(m.ObjectOfInterest.HasComments) l.push(m);
              });
              break;
            case "located":
              map.setFilter(function(m, l) {
                if(m.ObjectOfInterest.SuggestionCount != 0)
                  l.push(m);
              });
              break;
            case "unlocated":
              map.setFilter(function(m, l) {
                if(m.ObjectOfInterest.SuggestionCount == 0)
                  l.push(m);
              });
              break;
          }
        });
        
        jQuery('#map-settings .settings-inner').append( panel );
        //map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(panel.get(0));
        
        
    
    /*
        var unlocated = jQuery(document.createElement("label")).text("Artworks without suggestions")
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
        
        var nocomments = jQuery(document.createElement("label")).text("Artworks without comments")
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
        
        var reset = jQuery(document.createElement("label")).text("All artworks")
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
                .append(jQuery('<h2 id="filter-toggle">Filter</h2>'))
                .append(reset)
                .append(located)
                .append(unlocated)
                .append(comments)
                .append(nocomments);
        
        map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(panel.get(0));
    */
    })();
    
    function offsetCenter(latlng,offsetx,offsety) {
 
      // latlng is the apparent centre-point
      // offsetx is the distance you want that point to move to the right, in pixels
      // offsety is the distance you want that point to move upwards, in pixels
      // offset can be negative
      // offsetx and offsety are both optional
       
      var scale = Math.pow(2, map.getZoom());
      var nw = new google.maps.LatLng(
          map.getBounds().getNorthEast().lat(),
          map.getBounds().getSouthWest().lng()
      );
       
      var worldCoordinateCenter = map.getProjection().fromLatLngToPoint(latlng);
      var pixelOffset = new google.maps.Point((offsetx/scale) || 0,(offsety/scale) ||0)
       
      var worldCoordinateNewCenter = new google.maps.Point(
          worldCoordinateCenter.x - pixelOffset.x,
          worldCoordinateCenter.y + pixelOffset.y
      );
       
      var newCenter = map.getProjection().fromPointToLatLng(worldCoordinateNewCenter);
       
      map.panTo(newCenter);
       
    }

    this.bindAutocomplete = function(autoComplete) {
        autoComplete.bindTo("bounds", map);
        google.maps.event.addListener(autoComplete, "place_changed", function() {
            jQuery.fancybox.close();
            jQuery('#welcome').fadeOut(300);
            if (jQuery("#artmaps-search-results-artworks").hasClass('ui-dialog-content')) {
              jQuery("#artmaps-search-results-artworks").dialog("close");
            }
            var place = autoComplete.getPlace();
            if(place.id) {
                if(place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                    offsetCenter(place.geometry.location);
                } else {
                    offsetCenter(place.geometry.location);
                    map.setZoom(17);
                }
            }
        });
    };
    
    this.addControl = function(control, position) {
        map.controls[position].push(control);
    }; 
    
    this.centerOnMyLocation = function() {
        offsetCenter(myloc.getPosition());
    };
    
    this.addMapTypeListener = function(listener) {
        map.on("maptypeid_changed", function() {
            listener(map.getMapTypeId()); 
        });
    };
       
};
