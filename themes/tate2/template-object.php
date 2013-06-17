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
                'ObjectID' => $objectID,
                'CoreServerPrefix' => $core->getPrefix(),
                'SiteUrl' => site_url(),
                'ThemeDirUrl' => get_stylesheet_directory_uri(),
                'AjaxUrl' => admin_url('admin-ajax.php', is_ssl() ? 'https' : 'http'),
                'IsUserLoggedIn' => is_user_logged_in()
        ));

get_header();
?>
<script type="text/javascript">
jQuery(document).ready(function($) {

    var map = new ArtMaps.Map.MapObject($("#artmaps-object-container-map-canvas"), {});

    $("#artmaps-nav-bar-map a").attr("href", $.param.fragment("<?= site_url('/map') ?>", location.hash));

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

    (function() {
        var type = map.getMapType();
        var menu = $("#artmaps-object-container-map-canvas-actions-maptype-menu");
        menu.find("input:radio[name=artmaps-maptype]").filter("[value=" + type + "]").prop("checked", true);
        $("#artmaps-object-container-map-canvas-actions-maptype").click(function() {
            menu.toggle();
        });
        menu.find("input").change(function(){
            map.setMapType($(this).val());
            menu.toggle(false);
        });
    })();

    $("#artmaps-object-container-map-canvas-actions-suggest").click(function() {
        <?php if(is_user_logged_in()) { ?>
        map.suggest();
        <?php } else { ?>
        var url = "<?= wp_login_url() ?>";
        var sep = url.indexOf("?") > -1 ? "&" : "?";
        window.open(url + sep + "redirect_to=" + encodeURIComponent(location.href), "_self");
        <?php } ?>
    });

    $("#artmaps-object-container-map-canvas-actions-reset").click(map.reset);

    (function() {
        var con = $("#artmaps-blogthis-container");
        con.detach();
        var canvas = con.find("textarea");
        $("#artmaps-comment-container-action-blog").click(function() {
            con.dialog({
                "dialogClass": "artmaps-blogthis-dialog",
                "modal": true,
                "draggable": false,
                "open": function() {
                    canvas.select();
                },
                "width": 600,
                "height": 400
            });
        });
        con.find("#artmaps-blogthis-container-action-close").click(function() {
            con.dialog("close");
        });
    })();

    (function() {
        var con = $("#artmaps-commenton-container");
        con.detach();
        var canvas = con.find("textarea");
        $("#artmaps-comment-container-action-comment").click(function() {
            <?php if(!is_user_logged_in()) { ?>
            var url = "<?= wp_login_url() ?>";
            var sep = url.indexOf("?") > -1 ? "&" : "?";
            window.open(url + sep + "redirect_to=" + encodeURIComponent(location.href), "_self");
            <?php } else { ?>
            con.dialog({
                "dialogClass": "artmaps-commenton-dialog",
                "modal": true,
                "draggable": false,
                "open": function() {
                    canvas.focus();
                },
                "width": 600,
                "height": 400
            });
            <?php } ?>
        });
        con.find("#artmaps-commenton-container-action-close").click(function() {
            con.dialog("close");
        });
        con.find("#artmaps-commenton-container-action-comment").click(function() {
            var val = canvas.val();
            canvas.val("");
            con.dialog("close");
            jQuery.post(ArtMapsConfig.AjaxUrl,
                    {
                        "action": "artmaps.publishComment",
                        "objectID": <?= $objectID ?>,
                        "text": val
                    },
                    function(data) {
                        window.location.reload();
                    });
        });
    })();
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
        <div id="artmaps-object-container-map-canvas-actions">
            <div id="artmaps-object-container-map-canvas-actions-maptype" class="artmaps-button">Change Map View</div>
            <ul id="artmaps-object-container-map-canvas-actions-maptype-menu" style="display: none;">
                <li><label><input type="radio" name="artmaps-maptype" value="hybrid" />Hybrid</label></li>
                <li><label><input type="radio" name="artmaps-maptype" value="roadmap" />Roadmap</label></li>
                <li><label><input type="radio" name="artmaps-maptype" value="terrain" />Terrain</label></li>
                <li><label><input type="radio" name="artmaps-maptype" value="satellite" />Satellite</label></li>
            </ul>
            <div id="artmaps-object-container-map-canvas-actions-suggest" class="artmaps-button">Suggest a location</div>
            <div id="artmaps-object-container-map-canvas-actions-reset" class="artmaps-button">Show all locations</div>
        </div>
        <div id="artmaps-object-container-map-canvas-key">
            <span><img src="<?= content('pins/red.jpg') ?>" alt="" />Original Location</span>
            <span><img src="<?= content('pins/blue.jpg') ?>" alt="" />Suggested Location</span>
            <span><img src="<?= content('pins/green.jpg') ?>" alt="" />Your Active Suggestion</span>
        </div>
    </div>

</div>

<div id="artmaps-comment-container">
    <div>
        <h3>We think that this artwork is associated with this location. What do you think?</h3>
        <div id="artmaps-comment-container-action-comment" class="artmaps-button">Add Comment</div>
        <div id="artmaps-comment-container-action-blog" class="artmaps-button">Blog This</div>
        <div id="artmaps-comment-container-comments">
            Comments:
            <?php foreach(get_approved_comments($post->ID) as $comment) { ?>
            <div>
                <span><?= $comment->comment_content ?></span>
                <?php if(!empty($comment->comment_author_url)) { ?>
                <a href="<?= $comment->comment_author_url ?>" target="_blank">(Full text)</a>
                <?php } ?><br />
                <span>
                    Posted by <?= $comment->comment_author ?> on <?= $comment->comment_date?>
                    (<?= $safe_report_comments->get_flagging_link($comment->comment_ID) ?> )
                </span>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<div id="artmaps-commenton-container" style="display: hidden;">
    <div>
        <textarea></textarea>
    </div>
    <div>
        <div id="artmaps-commenton-container-action-close" class="artmaps-button">Close</div>
        <div id="artmaps-commenton-container-action-comment" class="artmaps-button">Comment</div>
    </div>
</div>

<div id="artmaps-blogthis-container" style="display: hidden;">
    <div>
        <textarea readonly="readonly"><?php
        $tmpl = new ArtMapsTemplating();
        echo htmlentities($tmpl->renderCommentTemplate(
            $blog, $objectID, site_url('/object/' . $objectID), $metadata));
        ?></textarea>
    </div>
    <div>
        <div id="artmaps-blogthis-container-action-close" class="artmaps-button">Close</div>
    </div>
</div>

<?php get_footer(); ?>
