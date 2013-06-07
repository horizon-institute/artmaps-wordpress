<?php
foreach(array(
                'google-jsapi', 'google-maps', 'jquery', 'jquery-ui-complete',
                'jquery-bbq', 'jquery-xcolor', 'json2', 'markerclusterer',
                'styledmarker', 'artmaps-map')
        as $script)
    wp_enqueue_script($script);
foreach(array('jquery-theme', 'artmaps-template-map') as $style)
    wp_enqueue_style($style);

$network = new ArtMapsNetwork();
$blog = $network->getCurrentBlog();
$core = new ArtMapsCoreServer($blog);

wp_localize_script('artmaps-map', 'ArtMapsConfig',
        array(
                'CoreServerPrefix' => $core->getPrefix(),
                'SiteUrl' => get_site_url(),
                'ThemeDirUrl' => get_stylesheet_directory_uri(),
                'IpInfoDbApiKey' => $network->getIpInfoDbApiKey(),
                'SearchSource' => $blog->getSearchSource()
        ));

add_filter('wp_title', function() {
    return 'The Art Map | ' . get_bloginfo('name');
});

get_header();
?>
<script type="text/javascript">
jQuery(function($) {

    var config = {
            "map": {
                "center": new google.maps.LatLng(51.507854, -0.099462), /* Tate Britain */
                "mapTypeId": google.maps.MapTypeId.SATELLITE
            }
        };
    var map = new ArtMaps.Map.MapObject($("#artmaps-map"), config);

    (function() {
        var link = jQuery("#artmaps-nav-bar-login").find("a");
        var url = link.attr("href");
        if(!url) return;
        var sep = url.indexOf("?") > -1 ? "&" : "?";
        link.attr("href", url + sep + "redirect_to=" + encodeURIComponent(location.href));
        $(window).bind("hashchange", function(e) {
            link.attr("href", url + sep + "redirect_to=" + encodeURIComponent(location.href));
    	});
    })();

    (function() {
        var button = $("#artmaps-mapview-bar span");
        var dropdown = $("#artmaps-mapview-bar ul");
        button.toggle(
                function() { dropdown.stop().show(); },
                function() { dropdown.stop().hide(); });
        dropdown.find("input").change(function(){
            map.setMapType($(this).val());
            button.click();
        });
    })();

    (function() {
        var dropdown = $("#artmaps-comments-bar ul");
        $("#artmaps-comments-bar span").toggle(
    	        function() { dropdown.stop().show(); },
                function() { dropdown.stop().hide(); });
    })();

    (function() {
        var con = $("#artmaps-object-container").children().first();
        $("#artmaps-object-container").detach();
        ArtMaps.UI.formatMetadata = function(object, metadata) {
            var c = con.clone();
            c.find("a").attr("href", ArtMapsConfig.SiteUrl + "/object/" + object.ID + window.location.hash);
            if(typeof metadata.imageurl != "undefined") {
                c.find("img").attr("src", metadata.imageurl);
            }
            c.find(".artmaps-object-container-title").text(metadata.title);
            c.find(".artmaps-object-container-artist").text(metadata.artist);
            c.find(".artmaps-object-container-suggestions").text(object.SuggestionCount + " suggestions");
    		$(window).bind("hashchange", function(e) {
    		    c.find("a").each(function(i, a) {
    		        var ax = $(a);
    		        ax.attr("href", $.param.fragment(ax.attr("href"), e.fragment));
    		    });
    		});
    		return c;
        };
    })();

    (function() {
        var con = $("#artmaps-search-container");
        var location = $("#artmaps-search-container-location-input");
        map.bindAutocomplete(new google.maps.places.Autocomplete(location.get(0)));
        var keyword = $("#artmaps-search-container-keyword-input");
        ArtMaps.Search.formatSearchResult = function(metadata) {
            return metadata.title + " by " + metadata.artist;
        };
        keyword.autocomplete({
            "source": ArtMaps.Search.objectSearch,
            "minLength": 3,
            "select": function(event, ui) {
                event.preventDefault();
                if(ui.item.value == -1) return;
                if(ui.item.value == -10) {
                    keyword.autocomplete("search", ui);
                    return;
                }
                window.location = "<?= get_site_url() ?>/object/"
                        + ui.item.value + window.location.hash;
                return;
            }
        });
        $("#artmaps-search-container-keyword .artmaps-search-container-button").click(function() {
            keyword.focus().autocomplete("search");
        });
        $("#artmaps-search-container-location .artmaps-search-container-button").click(function() {
            google.maps.event.trigger(location.get(0), "focus", {});
        });
        con.tabs({
            "activate": function(e, ui) {
                switch(con.tabs("option", "active")) {
                case 0:
                    keyword.autocomplete("enable");
                    keyword.val(location.val());
                    keyword.focus().autocomplete("search");
                    break;
                case 1:
                    keyword.autocomplete("close").autocomplete("disable");
                    location.val(keyword.val()).focus();
                    google.maps.event.trigger(location.get(0), "focus", {});
                    break;
                }
            }
        });
        $("#artmaps-search-bar span").click(function() {
            var closeFunc = function() {
                con.dialog("close");
            };
            con.dialog({
                "open": function() {
                    con.tabs("option", "active", 0);
                    keyword.val("");
                    location.val("");
                    keyword.focus();
                    jQuery(ArtMaps).trigger("artmaps-dialog-opened");
                    jQuery(ArtMaps).on("artmaps-dialog-opened", closeFunc);
                },
                "close" : function () {
                    keyword.autocomplete("close");
                    jQuery(ArtMaps).off("artmaps-dialog-opened", closeFunc);
                }
            });
        });
    })();

});
</script>

<div id="artmaps-search-bar">
    <span>Search</span>
</div>

<div id="artmaps-mapview-bar">
    <span>View</span>
    <ul style="display: none;">
        <li><label><input type="radio" name="artmaps-maptype" value="hybrid" />Hybrid</label></li>
        <li><label><input type="radio" name="artmaps-maptype" value="roadmap" />Roadmap</label></li>
 		<li><label><input type="radio" name="artmaps-maptype" value="terrain" />Terrain</label></li>
        <li><label><input type="radio" name="artmaps-maptype" value="satellite" />Satellite</label></li>
    </ul>
</div>

<div id="artmaps-comments-bar">
    <span>Latest Comments</span>
    <ul style="display: none;">
    <?php
    foreach(get_comments(array('number' => 5, 'status' => 'approve')) as $c) {
        $p = get_post($c->comment_post_ID);
    ?>
        <li><a href="<?= get_permalink($c->comment_post_ID)?>"> <?= $p->post_title ?></a></li>
    <?php } ?>
    </ul>
</div>

<div id="artmaps-map"></div>

<div id="artmaps-object-list-container-page" style="display: none;">
    <div class="artmaps-object-list-container-page">
        <div>
            <span class="artmaps-object-list-container-page-previous">[Previous]</span>
            <span class="artmaps-object-list-container-page-current">Page 1 of 21</span>
            <span class="artmaps-object-list-container-page-next">&nbsp;[Next]</span>
        </div>
        <div class="artmaps-object-list-container-page-body"></div>
        <div>
            <span class="artmaps-object-list-container-page-previous">[Previous]</span>
            <span class="artmaps-object-list-container-page-current">Page 1 of 21</span>
            <span class="artmaps-object-list-container-page-next">&nbsp;[Next]</span>
        </div>
    </div>
</div>

<div id="artmaps-object-container" style="display: none;">
    <div class="artmaps-object-container">
        <a><img src="<?= get_stylesheet_directory_uri() ?>/content/unavailable.jpg" /></a>
        <span class="artmaps-object-container-title"></span><br />
        by <span class="artmaps-object-container-artist"></span><br />
        <a>View Artwork</a><br />
        <span class="artmaps-object-container-suggestions"></span><br />
    </div>
</div>

<div id="artmaps-search-container" style="display: none;">
    <ul>
        <li><a href="#artmaps-search-container-keyword">Keyword</a></li>
        <li><a href="#artmaps-search-container-location">Location</a></li>
    </ul>
    <div id="artmaps-search-container-keyword">
        <input id="artmaps-search-container-keyword-input" name="artmaps-search-container-keyword-input"
                type="text" placeholder="Enter a keyword" autocomplete="off" />
        <span class="artmaps-search-container-button">Search</span>
        <br />
        <span>You are searching by keyword<br />(artwork title/artist's name/subject)</span>
    </div>
    <div id="artmaps-search-container-location">
        <input id="artmaps-search-container-location-input" name="artmaps-search-container-location-input"
                type="text" placeholder="Enter a location" autocomplete="off" />
        <span class="artmaps-search-container-button">Search</span>
        <br />
        <span>You are searching the map for locations</span>
    </div>
</div>

<?php get_footer(); ?>
