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
$coreUserID = -1;
try {
    $user = ArtMapsUser::currentUser();
    $coreUserID = $user->getCoreID($blog);
}
catch(Exception $e) { }
wp_localize_script('artmaps-object', 'ArtMapsConfig',
        array(
                'CoreServerPrefix' => $core->getPrefix(),
                'SiteUrl' => get_site_url(),
                'ThemeDirUrl' => get_stylesheet_directory_uri(),
                'AjaxUrl' => admin_url('admin-ajax.php'),
                'IsUserLoggedIn' => is_user_logged_in(),
                'CoreUserID' => $coreUserID
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


?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<title><?php
global $ArtmapsPageTitle;
if(isset($ArtmapsPageTitle))
    echo "$ArtmapsPageTitle | ";
else
    wp_title('|', true, 'right');
?><?php bloginfo('name');?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div id="artmaps-navigation-top" class="artmaps-navigation-container">


	<div id="artmaps-navigation-map" class="artmaps-navigation-link">
	    <a href="javascript:window.history.back();">Back to Map</a></div>

</div>
<script type="text/javascript">
var config = {
        "objectID": <?= $objectID ?>,
        "mapConf": {
            "scrollwheel": true,
            "center": new google.maps.LatLng(51.5171, 0.1062),
            "streetViewControl": false,
            "zoom": 12,
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
            "maxZoom": 15,
            "zoomOnClick": true,
            "imageSizes": [56],
            "styles": [{
                "url": "http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclustererplus/images/m2.png",
                "height": 56,
                "width": 56
            }]
        }
    };
jQuery(document).ready(function($) {

    
    ArtMaps.UI.formatMetadata = function(metadata) {
        var con = jQuery(document.createElement("div"));

		if(metadata.imageurl) {
		    var img = jQuery(document.createElement("img"))
		            .attr("src", metadata.imageurl)
		            .attr("alt", metadata.title);
		    con.append(img);
		}
		
		var p = jQuery(document.createElement("p"));
		p.html("Artist: " + metadata.artist + " " + metadata.artistdate
		        + "<br />Title: " + metadata.title
		        + "<br />Date: " + metadata.artworkdate
		        + "<br /><a href=\"http://www.tate.org.uk/art/artworks/"
		        + metadata.reference + "\" target=\"_blank\">View on Tate Online</a>");
		con.append(p);
		
		return con;
    };

    

    jQuery.getJSON(
            ArtMapsConfig.CoreServerPrefix + "objectsofinterest/"
                    + config.objectID + "/metadata",
            function(data) {
                jQuery("#artmaps-objectcontainer").append(
                        ArtMaps.UI.formatMetadata(data));
    });

    if(window.opener)
        jQuery("#artmaps-navigation-map").children("a")
                .attr("href", "#").click(function() {
                    window.opener.focus();
                    window.close();
            });

    var map = new ArtMaps.Map.MapObject($("#artmaps-mapcontainer"), config);

    function resetMapViewToggle() {
        $(".artmaps-mapview-menu").toggle(false);
        $(".artmaps-mapview-link-button").unbind("click");
        $(".artmaps-mapview-link-button").click(function() {
            $(".artmaps-mapview-menu").toggle();
        });
        $(".artmaps-mapview-menu").find("input").change(function(){
        	//$(".artmaps-mapview-menu").unbind("click");
            $(".artmaps-mapview-menu").toggle(false);
            switch($(this).val()) {
            case "hybrid":
                map.switchMapType(google.maps.MapTypeId.HYBRID);
                break;
            case "roadmap":
                map.switchMapType(google.maps.MapTypeId.ROADMAP);
                break;
            case "satellite":
                map.switchMapType(google.maps.MapTypeId.SATELLITE);
                break;
            case "terrain":
                map.switchMapType(google.maps.MapTypeId.TERRAIN);
                break;
            }
        });
    }

    function dialogMap (e) {
        return;
        //var i = jQuery("#artmaps-objectcontainer");
        var t = jQuery("#artmaps-map-dialogcontainer");

        var resizeHandler = function() {
            var centre = map.getCenter();
            t.dialog({
                "height": jQuery(window).height() - 50,
                "width": jQuery(window).width() - 50
            });
            map.resize();
            map.setCenter(centre);
        };

        var replacement = jQuery(document.createElement("div"));
        var width = t.width();
        var height = t.height();
        t.replaceWith(replacement);
        t.dialog({
            "dialogClass": "artmaps-map-popup",
            "draggable": false,
            "modal": true,
            "height": jQuery(window).height() - 50,
            "width": jQuery(window).width() - 50,
            "close": function() {
                jQuery(window).unbind("resize", resizeHandler);
                jQuery(".artmaps-action-suggest-cancel-button").click();
                replacement.replaceWith(t);
                t.width(width);
                t.height(height);
                var centre = map.getCenter();
                map.resize();
                map.setCenter(centre);
                t.removeClass();
                $("#artmaps-mapcontainer").one("click", dialogMap);
                resetMapViewToggle();
            },
            "open": function() {
                var centre = map.getCenter();
                map.resize();
                map.setCenter(centre);
                resetMapViewToggle();
                jQuery(window).resize(resizeHandler);
            }
        });
        /*i.bind('clickoutside', function(e) {
            i.dialog("close");
        });*/
        /*t.bind('clickoutside', function(e) {
            t.dialog("close");
        });*/
    }
    //$("#artmaps-mapcontainer").one("click", dialogMap); //- removed by LI to disable the map pop up

    resetMapViewToggle();

    $(".artmaps-action-suggest-button").live("click", function() {
        <?php
        if(is_user_logged_in()) {
        ?>
        if(jQuery("#artmaps-map-dialogcontainer").dialog("isOpen") !== true)
            dialogMap();
        map.suggest();
        <?php } else { ?>

        var con = jQuery(document.createElement("div"));
        var text = jQuery(document.createElement("div"))
                .addClass("artmaps-action-comment-popup-body")
                .html("Before you can suggest a location, we ask that you sign in with an "
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
    });

    function comment(event) {
        <?php
        if(is_user_logged_in()) {
            $user = ArtMapsUser::currentUser();
            if($user->getExternalBlog()->isConfigured) {
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
        var blog = jQuery(document.createElement("div"))
                .text("Use My Blog")
                .click(function() {
                    btns.empty().append(loading);
                    jQuery.post(ArtMapsConfig.AjaxUrl,
                    {
                        "action": "artmaps.createDraftComment",
                        "objectID": <?= $objectID ?>
                    },
                    function(data) {
                        var edit = jQuery(document.createElement("a"))
                                .attr("target", "_blank")
                                .attr("href", data.BlogUrl)
                                .text("Edit now")
                                .click(function() {
                                    con.dialog("close");
                                });
                        loading.remove();
                        btns.append(edit).append(close);
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
                .text("There's still a bit more setup to be done on your account "
                        + "before you can comment. We need the details of a "
                        + "Wordpress blog that you can publish to to be configured "
                        + "in your account settings.");
        var configure = jQuery(document.createElement("a"))
                .attr("href", "<?= admin_url(
                        '/profile.php?artmaps_redirect='
                        . get_permalink()) . '#artmaps' ?>")
                .text("Configure account");
        var close = jQuery(document.createElement("div"))
                .text("Close")
                .click(function() {
                    con.dialog("close");
                });
        var btns = jQuery(document.createElement("div"))
                .addClass("artmaps-action-comment-popup-buttons")
                .append(configure)
                .append(close);
        con.append(text).append(btns).dialog({
                "dialogClass": "artmaps-action-comment-popup",
                "modal": true
            });
        <?php
            }
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
    }
    $(".artmaps-action-comment-button").one("click", comment);
});
</script>
<div id="artmaps-objectcontainer"></div>
<div id="artmaps-map-dialogcontainer">
<div id="artmaps-mapcontainer"></div>
<div class="artmaps-map-key">
    <span><img src="<?= get_stylesheet_directory_uri() ?>/content/pins/red.jpg" alt="" />Original Location</span>
    <span><img src="<?= get_stylesheet_directory_uri() ?>/content/pins/blue.jpg" alt="" />Suggested Location</span>
    <span><img src="<?= get_stylesheet_directory_uri() ?>/content/pins/green.jpg" alt="" />Your Active Suggestion</span>
</div>
<div id="artmaps-actionscontainer">
    <div class="artmaps-mapview-link-button">Change Map View</div>
    <ul class="artmaps-mapview-menu" style="display: none;">
        <li><label><input type="radio" name="maptype" value="hybrid" />Hybrid</label></li>
        <li><label><input type="radio" name="maptype" value="satellite" checked="checked" />Satellite</label></li>
    </ul>
    <div class="artmaps-action-suggest-button">Suggest a location</div>
</div>
</div>
<div id="artmaps-commentcontainer">
<H3 id="artmaps-ask-location">We think this artwork is associated with this location. What do you think?</H3>
<div class="artmaps-action-comment-button">Add Comment</div>
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
