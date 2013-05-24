<?php
add_filter('show_admin_bar', '__return_false');
remove_action('wp_head', '_admin_bar_bump_cb');

foreach(array(
                'google-maps', 'jquery', 'jquery-ui-core', 'jquery-ui-button',
                'jquery-ui-dialog', 'jquery-xcolor','jquery-outside-event', 'json2', 'markerclusterer',
                'jquery-bbq', 'styledmarker', 'artmaps-object')
        as $script)
    wp_enqueue_script($script);
foreach(array('jquery-theme', 'artmaps-template-object') as $style)
    wp_enqueue_style($style);

$network = new ArtMapsNetwork();
$blog = $network->getCurrentBlog();
$core = new ArtMapsCoreServer($blog);

wp_localize_script('artmaps-object', 'ArtMapsConfig',
        array(
                'CoreServerPrefix' => $core->getPrefix(),
                'SiteUrl' => get_site_url(),
                'ThemeDirUrl' => themeUri(),
                'AjaxUrl' => admin_url('admin-ajax.php', isHttps('https', 'http')),
                'IsUserLoggedIn' => is_user_logged_in()
        ));

add_filter("body_class", function($classes) {
    $classes = array("artmaps-object");
    return $classes;
}, 99);

$objectID = get_query_var('objectid');
if(!isset($objectID) || !$objectID)
    $objectID = $blog->getObjectForPage($post->ID);
if(!isset($objectID) || !$objectID) {
    wp_redirect(site_url('/404.php'), 302);
    exit;
}

$metadata = $core->fetchObjectMetadata($objectID);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<title><?php wp_title('|', true, 'right'); bloginfo('name');?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div id="artmaps-navigation-top" class="artmaps-navigation-container">
	<div id="artmaps-navigation-map" class="artmaps-navigation-link">
	    <a class="artmaps-map-link" href="<?= get_site_url() ?>/map">Back to Map</a>
	</div>
</div>
<script type="text/javascript">
var config = {
        "objectID": <?= $objectID ?>,
        "mapConf": {
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
        },
        "clustererConf" : {
            "gridSize": 150,
            "minimumClusterSize": 2,
            "zoomOnClick": true,
            "imageSizes": [56],
            "styles": [{
                "url": "<?= themeUri('/content/cluster.png') ?>",
                "height": 56,
                "width": 56
            }]
        }
    };
jQuery(document).ready(function($) {

    var map = new ArtMaps.Map.MapObject($("#artmaps-mapcontainer"), config);

    /* Update the back to map link to return to the map in the last viewed state */
    $(".artmaps-map-link").attr("href", $.param.fragment("<?= get_site_url() ?>/map", location.hash));

    /* Image zoom handler */
    $(".artmaps-object-image").click(function(e) {
        e.stopPropagation();
        var t = jQuery(e.target).clone();
        t.dialog({
            "dialogClass": "artmaps-large-image-popup",
            "modal": true,
            "draggable": false,
            "height": $(window).height(),
            "width": $(window).width(),
            "open": function() {
                t.bind("clickoutside", function() {
                    t.dialog("close");
                });
                t.bind("click", function() {
                    t.dialog("close");
                });
            }
        });
    });

    /* Map view handler */
    $(".artmaps-mapview-link-button").click(function() {
        $(".artmaps-mapview-menu").toggle();
    });
    $(".artmaps-mapview-menu").find("input").change(function(){
        $(".artmaps-mapview-menu").toggle(false);
        switch($(this).val()) {
        case "hybrid":
            map.setMapType(google.maps.MapTypeId.HYBRID);
            break;
        case "roadmap":
            map.setMapType(google.maps.MapTypeId.ROADMAP);
            break;
        case "satellite":
            map.setMapType(google.maps.MapTypeId.SATELLITE);
            break;
        case "terrain":
            map.setMapType(google.maps.MapTypeId.TERRAIN);
            break;
        }
    });

    /* Suggestion handler */
    $(".artmaps-action-suggest-button").click(function() {
        <?php if(is_user_logged_in()) { ?>
        map.suggest();
        <?php } ?>
    });

    function blogthis(event) {
        var canvas = jQuery(document.createElement("textarea"))
                .addClass("artmaps-editor-canvas");
        jQuery.post(ArtMapsConfig.AjaxUrl,
	            {
	                "action": "artmaps.generateCommentTemplate",
	                "objectID": <?= $objectID ?>
	            },
                function(data) {
	                canvas.val(data);
	                canvas.select();
	            }
	    );
        var btns = jQuery(document.createElement("div"))
                .addClass("artmaps-action-comment-popup-buttons");
        var con = jQuery(document.createElement("div"));
        var close = jQuery(document.createElement("div"))
                .text("Close")
                .click(function() {
                    con.dialog("close");
                });
        btns.append(close);
        con.append(canvas).append(btns).dialog({
            "dialogClass": "artmaps-action-comment-popup",
            "modal": true
            });
    }

    function comment(event) {
        <?php
        if(is_user_logged_in()) {
            $user = ArtMapsUser::currentUser();

        ?>
        var btns = jQuery(document.createElement("div"))
                .addClass("artmaps-action-comment-popup-buttons");
        var con = jQuery(document.createElement("div"));
        var text = jQuery(document.createElement("div"))
                .addClass("artmaps-action-comment-popup-body")
                .text("Please enter your comment below:");
        var canvas = jQuery(document.createElement("textarea"))
                .addClass("artmaps-editor-canvas");
        var loading = jQuery(document.createElement("img"))
                .attr("src", "<?= get_stylesheet_directory_uri() . '/content/loading/25x25.gif' ?>")
                .attr("alt", "");
        var submit = jQuery(document.createElement("div"))
                .text("Submit")
                .click(function() {
                    btns.empty().append(loading);
                    canvas.attr("readonly", "readonly");
                    jQuery.post(ArtMapsConfig.AjaxUrl,
                            {
                                "action": "artmaps.publishComment",
                                "objectID": <?= $objectID ?>,
                                "text": canvas.val()
                            },
                            function(data) {
                                con.dialog("close");
                                window.location.reload();
                            });

                });

        var close = jQuery(document.createElement("div"))
                .text("Close")
                .click(function() {
                    con.dialog("close");
                });
        btns
                .append(submit)
                .append(close);
        con.append(text).append(canvas).append(btns).dialog({
                "dialogClass": "artmaps-action-comment-popup",
                "modal": true
            });
        <?php

        } else {
        ?>
        var con = jQuery(document.createElement("div"));
        var text = jQuery(document.createElement("div"))
                .addClass("artmaps-action-comment-popup-body")
                .html("Before you can comment, we ask that you sign in with an "
                        + "<a href=\"http://openid.net/get-an-openid/\" target=\"_blank\">OpenID</a>. "
                        + "If you don't know what an OpenID is, don't worry, you most likely already "
                        + "have one without realising it. For more information please use this link: "
                        + "<a href=\"http://openid.net/get-an-openid/\" target=\"_blank\">Get an OpenID</a>.");
        var signin = jQuery(document.createElement("a"))
                .attr("href", "<?= wp_login_url(get_permalink()) ?>")
                .text("Sign in");
        var close = jQuery(document.createElement("div"))
                .text("Close")
                .click(function() {
                    con.dialog("close");
                });
        var btns = jQuery(document.createElement("div"))
                .addClass("artmaps-action-comment-popup-buttons")
                .append(signin)
                .append(close);
        con.append(text).append(btns).dialog({
                "dialogClass": "artmaps-action-comment-popup",
                "modal": true
            });
        <?php } ?>
        $(".artmaps-action-comment-button").one("click", comment);
        $(".artmaps-action-blog-button").one("click", blog);
    }
    $(".artmaps-action-comment-button").on("click", comment);
    $(".artmaps-action-blog-button").on("click", blogthis);
    $(".artmaps-action-show-all-button").on("click", map.reset);
});
</script>
<div id="artmaps-objectcontainer">
    <div>
    <?php
    if(property_exists($metadata, 'imageurl')) {
        ?><img class="artmaps-object-image" src="<?= $metadata->imageurl ?>" alt="<?= $metadata->title ?>" /><?php
    } else {
        ?><img src="<?= themeUri('/content/unavailable.jpg') ?>" alt="<?= $metadata->title ?>" /><?php
    }
    ?>
        <p>
            Artist: <?= $metadata->artist ?> <?= $metadata->artistdate ?><br/>
            Title: <?= $metadata->title ?><br />
            Date: <?= $metadata->artworkdate ?><br />
            <a href="http://www.tate.org.uk/art/artworks/<?= $metadata->reference ?>">View on Tate Online</a>
        </p>
    </div>
</div>
<div id="artmaps-map-dialogcontainer">
    <div id="artmaps-mapcontainer"></div>
    <div class="artmaps-map-key">
        <span><img src="<?= themeUri('/content/pins/red.jpg') ?>" alt="" />Original Location</span>
        <span><img src="<?= themeUri('/content/pins/blue.jpg') ?>" alt="" />Suggested Location</span>
        <span><img src="<?= themeUri('/content/pins/green.jpg') ?>" alt="" />Your Active Suggestion</span>
    </div>
    <div id="artmaps-actionscontainer">
        <div class="artmaps-mapview-link-button">Change Map View</div>
        <ul class="artmaps-mapview-menu" style="display: none;">
            <li><label><input type="radio" name="maptype" value="hybrid" />Hybrid</label></li>
            <li><label><input type="radio" name="maptype" value="roadmap" />Roadmap</label></li>
            <li><label><input type="radio" name="maptype" value="terrain" />Terrain</label></li>
            <li><label><input type="radio" name="maptype" value="satellite" checked="checked" />Satellite</label></li>
        </ul>
        <div class="artmaps-action-suggest-button">Suggest a location</div>
        <div class="artmaps-action-show-all-button">Show all locations</div>
    </div>
</div>
<div id="artmaps-commentcontainer">
    <h3 id="artmaps-ask-location">We think this artwork is associated with this location. What do you think?</h3>
    <div class="artmaps-action-comment-button">Add Comment</div>
    <div class="artmaps-action-blog-button">Use My Blog</div>
    <div class="artmaps-comments-text">
    Comments:
    <?php
    foreach(get_approved_comments($post->ID) as $comment) {
        ?><div class="artmaps-commentcontainer-comment">
        <a href="<?= $comment->comment_author_url ?>" target="_blank"><?= $comment->comment_author ?></a><br />
        <span><?= $comment->comment_content ?></span>
        <span class = "artmaps-comment-date"><?= $comment->comment_date?></span>
        <span class = "artmaps-repport-comments"><?= $safe_report_comments->get_flagging_link($comment->comment_ID) ?></span>
        </div><?php
    }
    ?>
    </div>
</div>
<?php get_footer(); ?>
