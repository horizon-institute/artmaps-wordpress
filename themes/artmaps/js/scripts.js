function set_page_title(title) {
  var site_title = "ArtMaps";
  if(title) {
    document.title = title+" \u00B7 "+site_title;
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
            "mapTypeId": google.maps.MapTypeId.ROADMAP
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
          if(typeof metadata.imageurl != "undefined") {
              c.find("img").attr("src", "//artmaps.tate.org.uk/artmaps/tate/dynimage/x/65/"+metadata.imageurl);
          }
          c.find(".artmaps-map-object-container-title").text(metadata.title);
          c.find(".artmaps-map-object-container-artist").text(metadata.artist);
          //c.find(".artmaps-map-object-container-suggestions").text(object.SuggestionCount);
          c.find(".artmaps-map-object-container-suggestions").html('<i class="fa-question"></i>');
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
    var marker = jQuery("#artmaps-location-id").val();
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

  // Perform AJAX login on form submit
  /* jQuery('form#loginform').on('submit', function(e){
      jQuery('form#loginform .loader').fadeIn();

      jQuery.ajax({
          type: 'POST',
          dataType: 'json',
          url: ajax_login_object.ajaxurl,
          data: { 
              'action': 'ajaxlogin', //calls wp_ajax_nopriv_ajaxlogin
              'username': jQuery('form#loginform #user_login').val(), 
              'password': jQuery('form#loginform #user_pass').val(), 
              'security': jQuery('form#loginform #security').val() },
          success: function(data){
              if (data.loggedin == true){
                  jQuery.fancybox.close();
                  jQuery('.account-panel').hide();
                  jQuery('form#loginform .loader').fadeOut();
              } else {
                jQuery('form#loginform .loader').delay(750).fadeOut(function() {
                  jQuery('#account-panel p.status').text(data.message).slideDown();
                });
              }
          },
          error: function(data){
              jQuery('form#loginform .loader').fadeOut();
              jQuery('#account-panel p.status').text("Could not connect!").slideDown();
          }
      });
      e.preventDefault();
  });*/
          
    var searchInput = jQuery("#keyword-search-form input.query-field");
    var searchForm = jQuery("#keyword-search-form form");
    var artistResults = jQuery("#artmaps-search-results-artists");
    var artworkResults = jQuery("#artmaps-search-results-artworks");
    
    var sanitizeTerm = function(term) {
        return term.replace(/[-,";:\(\).!\[\]\t\n]/g, " ")
                .replace(/(^')/gm, "")
                .replace(/('$)/gm, " ")
                .replace(/('\s)/g, " ")
                .replace(/(\s')/g, " ")
                .replace(/\s+/g, " ")
                .replace(/\*+/g, "*");
    }
    
    var displayArtists = function(data) {
        var list = artistResults.find("ul");
        list.empty();
        if(typeof data.artistsData == "undefined") return;
        jQuery.each(data.artistsData, function(i, a) {
            var link = jQuery(document.createElement("a"))
                .text(a.label + (a.info ? " (" + a.info + ")" : ""))
                .click(function() {
                    jQuery.ajax({
                        "url": "http://www.tate.org.uk/art/artworks?q=" + searchInput.val() + "&aid=" + a.id,
                        "dataType": "xml",
                        "async": true,
                        "success": displayArtworks
                    });
                });
            var li = jQuery(document.createElement("li"));
            li.append(link);
            list.append(li); 
        });
    }
    
    var displayArtworks = function(data) {

        var art_list = jQuery(document.createElement("ul"));
        art_list.addClass("artmaps-map-object-list-container-page-body");
        artworkResults.empty().append(art_list);

        var doc = jQuery(data);
        var noresults = doc.find(".noresults").length > 0;
        if(noresults) return;
        
        var showing = doc.find(".listData").first().text();
        var currentPage = 1;
        if(doc.find(".pager-current").length > 0)
            currentPage = parseInt(doc.find(".pager-current").first().text().replace(",", ""));
        var totalPages = 1;
        doc.find(".pager-item").each(function(i, e) {
            var v = parseInt(jQuery(e).text().replace(",", ""));
            if(v > totalPages)
                totalPages = v;
        });
        
        var nav = jQuery(document.createElement("div"));
        nav.addClass("artmaps-map-object-list-container-page-nav");
        
        //nav.append(showing);
        nav.append('<span class="artmaps-map-object-list-container-page-current">Page ' + currentPage + " of " + totalPages + '</span>');
        
        var prevPage = jQuery(document.createElement("a"))
                .addClass("artmaps-map-object-list-container-page-previous artmaps-button")
                .text("Prev")
                .one("click", function() {
                    jQuery.ajax({
                        "url": "http://www.tate.org.uk/art/artworks?q=" + searchInput.val() + "&wp=" + (currentPage - 1),
                        "dataType": "xml",
                        "async": true,
                        "success": displayArtworks
                    });
                });
        if(currentPage > 1) nav.append(prevPage);
        
        var firstPage = jQuery(document.createElement("a"))
                .addClass("search-page")
                .text(" 1 ")
                .one("click", function() {
                    jQuery.ajax({
                        "url": "http://www.tate.org.uk/art/artworks?q=" + searchInput.val() + "&wp=1",
                        "dataType": "xml",
                        "async": true,
                        "success": displayArtworks
                    });
                });
        //pagenav.append(firstPage);
        
        var min = Math.max(currentPage - 3, 2);
        var max = Math.min(min + 6, totalPages);
        var vals = new Array();
        for(var i = min; i <= max; i++) {
            vals.push(i);
        }
        jQuery.each(vals, function(e, i) {
            var page = jQuery(document.createElement("a"))
                    .addClass("search-page")
                    .text(" " + i + " ")
                    .one("click", function() {
                        jQuery.ajax({
                            "url": "http://www.tate.org.uk/art/artworks?q=" + searchInput.val() + "&wp=" + i,
                            "dataType": "xml",
                            "async": true,
                            "success": displayArtworks
                        });
                    });
            //pagenav.append(page);
        });
        
        var lastPage = jQuery(document.createElement("a"))
                .addClass("search-page")
                .text(" " + totalPages + " ")
                .one("click", function() {
                    jQuery.ajax({
                        "url": "http://www.tate.org.uk/art/artworks?q=" + searchInput.val() + "&wp=" + totalPages,
                        "dataType": "xml",
                        "async": true,
                        "success": displayArtworks
                    });
                });
        //if(totalPages > 1) pagenav.append(lastPage);
        
        var nextPage = jQuery(document.createElement("a"))
                .addClass("artmaps-map-object-list-container-page-next artmaps-button")
                .text("Next")
                .one("click", function() {
                    jQuery.ajax({
                        "url": "http://www.tate.org.uk/art/artworks?q=" + searchInput.val() + "&wp=" + (currentPage + 1),
                        "dataType": "xml",
                        "async": true,
                        "success": displayArtworks
                    });
                });
        if(currentPage < totalPages) nav.append(nextPage);
        
        artworkResults.append(nav);
        
        
        var artworks = doc.find(".grid-work");
        artworks.each(function(i, e) {
        var oc = jQuery(e);
    		var na = jQuery(document.createElement("a"));
    		na.addClass("artwork-link").attr("href", "#");
            var nc = jQuery(document.createElement("li"));
            nc.addClass("artmaps-map-object-container");
               var acno = oc.find(".acno").first().text();
               jQuery.ajax({
                   "url": "http://devservice.artmaps.org.uk/service/tate/rest/v1/objectsofinterest/searchbyuri?URI=tatecollection://" + acno,
                   "dataType": "json",
                   "async": true,
                   "success": function(data) {
                      na.attr("href", ArtMapsConfig.SiteUrl + "/object/" + data.ID);
                      na.attr("data-object-id", data.ID);
                   },
                   "error": function() {
                       na.remove();
                   }
               });
            
            var oi = oc.find(".grid-work-image img");
            if(oi.length > 0) {
                oi = oi.first();
                var ni = jQuery(document.createElement("img"));
                ni.attr("src", "//artmaps.tate.org.uk/artmaps/tate/dynimage/x/65/http://www.tate.org.uk" + oi.attr("src"));
                na.append(ni);
            }
            
            nc.append(na);
            na.append(jQuery("<h2>" + oc.find(".grid-work-text .title-and-date .title").first().text() + "</h2>"));
            na.append(jQuery('<em>by <span class="artmaps-map-object-container-artist">' + oc.find(".grid-work-text .artist").first().text() + '</span></em>'));
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
          "open": function() {
            
          },
          "close": function() {
            jQuery("#keyword-search-form input.query-field").val("");
            jQuery.fancybox.close();
          },
          "title": ''
        });
        jQuery(".ui-dialog:visible").removeAttr('style');
        
    }
    
    searchForm.submit(function() {
        jQuery('#welcome').fadeOut(300);
        jQuery.ajax({
            "url": "http://www.tate.org.uk/art/artworks?q=" + searchInput.val(),
            "dataType": "text",
            "async": true,
            "success": displayArtworks
        });
        return false;
    });
    
    // Fix iOS 7 browser bug
    function fixHeightOnIOS7() {
      var fixedHeight = Math.min(
        jQuery(window).height(), // This is smaller on Desktop
        window.innerHeight || Infinity // This is smaller on iOS7
      );
      jQuery('body').height(fixedHeight);
    }

    jQuery(window).on('resize orientationchange', fixHeightOnIOS7);
    fixHeightOnIOS7();

});
