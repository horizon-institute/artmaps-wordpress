function set_page_title(title) {
  var site_title = "ArtMaps";
  if(title) {
    document.title = title + " \u00B7 " + site_title;
  } else {
    document.title = site_title;
  }
}

jQuery(document).ready(function(){ 

	
  jQuery( "#overlay" ).click(function(event) {
    jQuery.fancybox.close();
  });
  
  jQuery("#tabs li a").click(function(event){
    var t = jQuery(this).attr('id');

    if(jQuery(this).hasClass('inactive')){ //this is the start of our condition 
      jQuery('#tabs li a').addClass('inactive');           
      jQuery(this).removeClass('inactive');
  
      jQuery('.tab-container').hide();
      jQuery('#'+ t + 'C').show();
   }
    event.preventDefault();
  });
  
  // Logo
  jQuery( "#home" ).click(function(event) {
    set_page_title();
    jQuery.fancybox.close();
    jQuery(".ui-dialog-content:visible").dialog("close");
    window.main_map.centerOnMyLocation();
    event.preventDefault();
  });
  
  // Go to geolocated marker button
  jQuery( "#my-location" ).click(function(event) {
    jQuery("body").removeClass("menu-expanded"); // Close responsive menu
    jQuery(".ui-dialog-content:visible").dialog("close"); // Close dialogs
    window.main_map.centerOnMyLocation();
    event.preventDefault();
  });
  
  if(window.location) {
    jQuery("#my-location").css("display", "block");
  }

  google.maps.visualRefresh = true;
  var config = {
        "map": {
            "center": new google.maps.LatLng(51.507854, -0.099462), /* Tate Britain */
            "mapTypeId": google.maps.MapTypeId.ROADMAP,
            "styles" : [{"featureType":"administrative","stylers":[{"visibility":"off"}]},{"featureType":"poi","stylers":[{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"simplified"}]},{"featureType":"water","stylers":[{"visibility":"simplified"}]},{"featureType":"transit","stylers":[{"visibility":"simplified"}]},{"featureType":"landscape","stylers":[{"visibility":"simplified"}]},{"featureType":"road.highway","stylers":[{"visibility":"off"}]},{"featureType":"road.local","stylers":[{"visibility":"on"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"water","stylers":[{"color":"#84afa3"},{"lightness":52}]},{"stylers":[{"saturation":-17},{"gamma":0.36}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"color":"#3f518c"}]}]
        }
    };
  window.main_map = new ArtMaps.Map.MapObject(jQuery("#artmaps-map-themap"), config);
  var autocomplete = jQuery("#artmaps-map-autocomplete");
  main_map.bindAutocomplete(new google.maps.places.Autocomplete(autocomplete.get(0)));
  
  (function() {
      var con = jQuery("#artmaps-map-object-container").children().first();
      jQuery("#artmaps-map-object-container").detach();
      ArtMaps.Map.UI.formatMetadata = function(object, metadata) {
          var c = con.clone();
          c.find("a").attr("href", ArtMapsConfig.SiteUrl + "/object/" + object.ID);
          c.find("a.artwork-link").attr("data-object-id", object.ID);
          if(typeof metadata.ImageFile != "undefined" && metadata.ImageFile != "") {
              c.find("img").attr("src", "//www.artmaps.org.uk/maps/glatam/dynimage/x/65/" + metadata.ImageFile);
          }
          c.find(".artmaps-map-object-container-name").text(metadata.Name);
          c.find(".artmaps-map-object-container-allegiance").text(metadata.Allegiance);
          c.find(".artmaps-map-object-container-eventtype").text(metadata.EventType);
          c.find(".artmaps-map-object-container-year").text(metadata.Year);
          return c;
      };
  })();
  
  (function() {
    jQuery(window).bind("hashchange", function(e) {
      jQuery.ajax(ArtMapsConfig.AjaxUrl, {
        "type": "post",
          "data": {
            "action": "artmaps.storeMapState",
            "data": { "state": e.fragment }
          }
      });
    });
  })();
  
  function show_page(name, title) {
    
    jQuery("body").removeClass("menu-expanded"); // Close responsive menu
    jQuery('#welcome').fadeOut(300);
    
    this.dialog = jQuery('#'+name+'-sidebar');
    this.dialog.dialog({
        "show": { "duration": 0 },
        "hide": { "duration": 0 },
        "dialogClass": "artwork-results "+name+"-sidebar",
        "resizable": false,
        "closeText": "",
        "draggable": false,
        "open": function() {
          jQuery(".ui-dialog:visible").removeAttr('style');
          jQuery("time").timeago();
          set_page_title(title);
          jQuery.bbq.pushState({ "page": name });
        },
        "close": function() {
          set_page_title();
          jQuery.bbq.removeState("page");
        },
        "title": title
    });
    
  }
    
  // Activity button
  jQuery("#whats-new").click(function(event) {
    jQuery(".ui-dialog-content:visible").dialog("close"); // Close other dialogs
    jQuery.fancybox.close(); // Close artwork page
    show_page("activity", "What's new?");
    event.preventDefault();
  });
  
  // About button
  jQuery("#how-it-works, #more-info").click(function(event) {
    jQuery(".ui-dialog-content:visible").dialog("close"); // Close other dialogs
    jQuery.fancybox.close(); // Close artwork page
    jQuery("#about-sidebar").html("").load("about"); // Load content
    show_page("about", "About");
    event.preventDefault();
  });
  
  // Account button
  jQuery(document).on("click","#account-button, .log-in-trigger",function(event){
    show_page("account", "Account");
    jQuery( "#user_login" ).focus(); // Place cursor in email field
    event.preventDefault();
  });
  
  var hashstate = jQuery.bbq.getState();
  switch(hashstate.page) {
  case "about" :  jQuery("#how-it-works, #more-info").click();
  case "activity" : jQuery("#whats-new").click();
  };
    
  // Artwork page
  function show_object_modal(object_id) {
  
    jQuery.fancybox({
  		href      : ArtMapsConfig.SiteUrl + "/object/" + object_id,
  		type      : 'ajax',
  		openEffect: 'none',
  		closeEffect: 'none',
  		closeClick	: false,
  		showEarly   : false,
  		helpers: { overlay : null },
      beforeShow : function() {
        jQuery.bbq.pushState({ "object": object_id });
        jQuery("#overlay").fadeIn();
        jQuery("body").addClass("fancybox-lock");
        jQuery("time").timeago();
        set_page_title(jQuery("#artmaps-object-metadata h1").text());
      },
      beforeClose : function() {
        jQuery("body").removeClass("fancybox-lock");
        jQuery("#overlay").fadeOut();
        jQuery("#full-image").trigger('close');
        jQuery.bbq.removeState("object");
        set_page_title();
      },
      tpl : {
      	error    : '<p class="fancybox-error">The requested content cannot be loaded.<br/>Please try again later.</p>',
      	closeBtn : '<a title="Close" class="fancybox-item fancybox-backtoresults" href="javascript:;"><i class="fa-chevron-left"></i> Back to results</a>',
      }
    });
    
  }
  
  // View artwork full size lightbox
  jQuery(document).on("click",".artwork-img",function(event){
    var full_image = jQuery('<div id="full-image">');
    var img = jQuery('<img class="full-image-img">');
    img.attr('src', jQuery(this).data("full-image"));
    full_image.appendTo('body');
    img.appendTo('#full-image');
    jQuery('#full-image').lightbox_me({
        centered: true,
        destroyOnClose: true,
        zIndex: '99999999'
        });
    event.preventDefault();
  });
  
  jQuery(document).on("click","#full-image",function(event){
    jQuery(this).trigger('close');
    event.preventDefault();
  });
  
  // Toggles for mobile site
  jQuery(document).on("click","#menu-toggle",function(event){
    jQuery("body").toggleClass("menu-expanded");
  });
  
  jQuery(document).on("click","#search-toggle, #close-search",function(event){
    jQuery(".search-form").toggle();
  });
  
  jQuery(document).on("click",".artwork-link",function(event){
    var object_id = jQuery(this).attr('data-object-id');
    show_object_modal(object_id);
    event.preventDefault();
  });
  
  jQuery(document).on("click","#comment-loc-indicator",function(event){
    event.preventDefault();
  });
  
  var search_mode_select = jQuery("#search-mode");
  search_mode_select.change(function(){
    var id = jQuery(this).find("option:selected").attr("id");
    
    switch (id) {
      case "search-mode-places":
        jQuery('#location-search-form, #search-label-places').show();
        jQuery('#keyword-search-form, #search-label-art').hide();
        jQuery('#location-search-form .query-field').focus();
        break;
      case "search-mode-artworks":
        jQuery('#location-search-form, #search-label-places').hide();
        jQuery('#keyword-search-form, #search-label-art').show();
        jQuery('#keyword-search-form .query-field').focus();
        break;
    }
  });
  
  jQuery( "#explore-map, #welcome" ).click(function(event) {
    jQuery('#welcome').fadeOut(300);
    event.preventDefault();
  });
  
  /* Keyword search */
  (function () {        
    var searchInput = jQuery("#keyword-search-form input.query-field");
    var searchForm = jQuery("#keyword-search-form form");
    var artworkResults = jQuery("#artmaps-search-results-artworks");
    
    var displayArtworks = function(data, currentPage) {

        var art_list = jQuery(document.createElement("ul"));
        art_list.addClass("artmaps-map-object-list-container-page-body");
        artworkResults.empty().append(art_list);        
        
        var nav = jQuery(document.createElement("div"));
        nav.addClass("artmaps-map-object-list-container-page-nav");
        nav.append("<span class=\"artmaps-map-object-list-container-page-current\">Page " + (currentPage + 1) + "</span>");
        artworkResults.append(nav);
        
        if(currentPage > 0) 
          nav.append(jQuery(document.createElement("a"))
              .addClass("artmaps-map-object-list-container-page-previous artmaps-button")
              .text("Prev")
              .one("click", function() {
                  jQuery.ajax({
                    "url": ArtMapsConfig.CoreServerPrefix + "external/search?p=" + (currentPage - 1) + "&s=artmaps://" + searchInput.val(),
                    "dataType": "json",
                    "async": true,
                    "success": function(data) { displayArtworks(data, currentPage - 1); }
                  });
          }));
        
        nav.append(jQuery(document.createElement("a"))
            .addClass("artmaps-map-object-list-container-page-next artmaps-button")
            .text("Next")
            .one("click", function() {
                jQuery.ajax({
                  "url": ArtMapsConfig.CoreServerPrefix + "external/search?p=" + (currentPage + 1) + "&s=artmaps://" + searchInput.val(),
                  "dataType": "json",
                  "async": true,
                  "success": function(data) { displayArtworks(data, currentPage + 1); }
                });
        }));
        
        jQuery.each(data, function(i, o) {
          var na = jQuery(document.createElement("a"));
          na.addClass("artwork-link").attr("href", "#");
          var nc = jQuery(document.createElement("li"));
          nc.addClass("artmaps-map-object-container");
          na.attr("href", ArtMapsConfig.SiteUrl + "/object/" + o.ID);
          na.attr("data-object-id", o.ID);
          if(o.metadata.ImageFile != "") {
        	var ni = jQuery(document.createElement("img"));
        	ni.attr("src", "//www.artmaps.org.uk/maps/glatam/dynimage/x/65/" + o.metadata.ImageFile);
            na.append(ni);
          }            
          nc.append(na);
          na.append(jQuery("<h2>" + o.metadata.Name + " (" + o.metadata.EventType + ")</h2>"));
          art_list.append(nc);
        });
        
        jQuery(".ui-dialog-content").not("#artmaps-search-results-artworks").dialog("close");
        jQuery("#artmaps-search-results-artworks").dialog({
          "width": 260,
          "dialogClass": "artwork-results artwork-results-keyword",
          "height": jQuery(window).height() - 160,
          "position": "right bottom",
          "resizable": false,
          "closeText": "",
          "draggable": false,
          "open": function() {},
          "close": function() {
            jQuery("#keyword-search-form input.query-field").val("");
            jQuery.fancybox.close();
          },
          "title": ""
        });
        jQuery(".ui-dialog:visible").removeAttr("style");        
    };

    searchForm.submit(function() {
      jQuery('#welcome').fadeOut(300);
      jQuery.ajax({
        "url": ArtMapsConfig.CoreServerPrefix + "external/search?p=0&s=artmaps://" + searchInput.val(),
        "dataType": "json",
        "async": true,
        "success": function(data) { displayArtworks(data, 0); }
      });
	  return false;
    });

  })();
  /* End keyword search */
    
  /* Fix iOS 7 browser bug */
  (function () {
	function fixHeightOnIOS7() {
	  var fixedHeight = Math.min(
	      jQuery(window).height(), // This is smaller on Desktop
	      window.innerHeight || Infinity // This is smaller on iOS7
      );
      jQuery("body").height(fixedHeight);
    }
    jQuery(window).on("resize orientationchange", fixHeightOnIOS7);
    fixHeightOnIOS7();
  })();
  /* End fix iOS 7 browser bug */
  
  /* Add new object */
  (function () {
	  jQuery("#add-new-object").click(function () {
		  window.main_map.addNewObject(function() {});
	  });
  })();
  /* End add new object */

});
