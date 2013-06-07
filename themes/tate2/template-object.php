<?php
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

$objectID = get_query_var('objectid');
if(!isset($objectID) || !$objectID)
    $objectID = $blog->getObjectForPage($post->ID);
if(!isset($objectID) || !$objectID) {
    wp_redirect(site_url('/404.php'), 302);
    exit;
}

$metadata = $core->fetchObjectMetadata($objectID);

function content($path) {
    return get_stylesheet_directory_uri() . "/content/" . $path;
}

wp_localize_script('artmaps-object', 'ArtMapsConfig',
        array(
                'CoreServerPrefix' => $core->getPrefix(),
                'SiteUrl' => get_site_url(),
                'ThemeDirUrl' => get_stylesheet_directory_uri(),
                'AjaxUrl' => admin_url('admin-ajax.php', is_ssl() ? 'https' : 'http'),
                'IsUserLoggedIn' => is_user_logged_in()
        ));

get_header();
?>
<script type="text/javascript">
jQuery(document).ready(function($) {

    var map = new ArtMaps.Map.MapObject($("#artmaps-object-container-map-canvas"), { "objectID": <?= $objectID ?> });

    $("#artmaps-nav-bar-map a").attr("href", $.param.fragment("<?= get_site_url() ?>/map", location.hash));

    (function() {
        var small = $("#artmaps-object-container-object img");
        var large = small.clone();
        large.bind("clickoutside", function() {
            large.dialog("close");
        }).bind("click", function() {
            large.dialog("close");
        });
        small.click(function(e) {
            e.stopPropagation();
            large.dialog({
                "dialogClass": "artmaps-object-image-large",
                "modal": true,
                "draggable": false,
                "height": $(window).height(),
                "width": $(window).width()
            });
        });
    })();

    /*****************/





    /* Map view handler */
    /*$(".artmaps-mapview-menu").find("input:radio[name=maptype]")
            .filter("[value=" + config.mapConf.mapTypeId + "]")
            .prop("checked", true);*/
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

    /* Map reset handler */
    $(".artmaps-action-show-all-button").click(map.reset);

    /* Suggestion handler */
    $(".artmaps-action-suggest-button").click(function() {
        <?php if(is_user_logged_in()) { ?>
        map.suggest();
        <?php } else { ?>
        window.open("<?= wp_login_url($_SERVER['REQUEST_URI']) ?>"
                + encodeURIComponent(location.hash), "_self");
        <?php } ?>
    });

    /* Comment handler */
    $(".artmaps-action-comment-button").click(function (event) {
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
        window.open("<?= wp_login_url($_SERVER['REQUEST_URI']) ?>"
                + encodeURIComponent(location.hash), "_self");
        <?php } ?>
    });

    /* Blog handler */
    $(".artmaps-action-blog-button").click(function (event) {
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
    });
});
</script>

<div id="artmaps-object-container">

    <div id="artmaps-object-container-object">
        <?php
        if(property_exists($metadata, 'imageurl')) {
            ?><img src="<?= $metadata->imageurl ?>" alt="<?= $metadata->title ?>" /><?php
        } else {
            ?><img src="<?= content('unavailable.jpg') ?>" alt="<?= $metadata->title ?>" /><?php
        }
        ?>
            <p>
                Artist: <?= $metadata->artist ?> <?= $metadata->artistdate ?><br/>
                Title: <?= $metadata->title ?><br />
                Date: <?= $metadata->artworkdate ?><br />
                <a href="http://www.tate.org.uk/art/artworks/<?= $metadata->reference ?>">View on Tate Online</a>
            </p>
    </div>

    <div id="artmaps-object-container-map">
        <div id="artmaps-object-container-map-canvas"></div>
        <div class="artmaps-map-key">
            <span><img src="<?= content('pins/red.jpg') ?>" alt="" />Original Location</span>
            <span><img src="<?= content('pins/blue.jpg') ?>" alt="" />Suggested Location</span>
            <span><img src="<?= content('pins/green.jpg') ?>" alt="" />Your Active Suggestion</span>
        </div>
        <div id="artmaps-actionscontainer">
            <div class="artmaps-mapview-link-button">Change Map View</div>
            <ul class="artmaps-mapview-menu" style="display: none;">
                <li><label><input type="radio" name="maptype" value="hybrid" />Hybrid</label></li>
                <li><label><input type="radio" name="maptype" value="roadmap" />Roadmap</label></li>
                <li><label><input type="radio" name="maptype" value="terrain" />Terrain</label></li>
                <li><label><input type="radio" name="maptype" value="satellite" />Satellite</label></li>
            </ul>
            <div class="artmaps-action-suggest-button">Suggest a location</div>
            <div class="artmaps-action-show-all-button">Show all locations</div>
        </div>
    </div>

</div>

<div id="artmaps-comment-container">
    <div>
        <h3 id="artmaps-ask-location">We think that this artwork is associated with this location. What do you think?</h3>
        <div class="artmaps-action-comment-button">Add Comment</div>
        <div class="artmaps-action-blog-button">Blog about this artwork</div>
        <div class="artmaps-comments-text">
            Comments:
            <?php foreach(get_approved_comments($post->ID) as $comment) { ?>
            <div class="artmaps-commentcontainer-comment">
            <a href="<?= $comment->comment_author_url ?>" target="_blank"><?= $comment->comment_author ?></a><br />
            <span><?= $comment->comment_content ?></span>
            <span class = "artmaps-comment-date"><?= $comment->comment_date?></span>
            <span class = "artmaps-repport-comments"><?= $safe_report_comments->get_flagging_link($comment->comment_ID) ?></span>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
