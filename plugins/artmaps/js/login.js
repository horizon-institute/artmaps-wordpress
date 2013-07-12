(function() {
    var links = jQuery("a[href*='wp-login.php']");
    links.each(function(i, e) {
       var link = jQuery(e);
       var update = function(e) {
           link.querystring("href", { "redirect_to": location.href });
       };
       update();
       jQuery(window).bind("hashchange", update);
    });
})();