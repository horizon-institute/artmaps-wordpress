<?php //get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<div class="artwork-page">
<script type="text/javascript">
google.maps.visualRefresh = true;
jQuery(function($) {
    
    ArtMapsConfig = ArtMapsConfig || {};
    ArtMapsConfig.ObjectID = "<?php echo get_post_meta(get_the_ID(),"object_id",true); ?>";
    
    var config = {
        "map": {
            "center": new google.maps.LatLng(51.507854, -0.099462) /* Tate Britain */
        }
    };
    var map = new ArtMaps.Object.MapObject($("#artmaps-object-map"), config);
    
    (function() {
        var autocomplete = $("#artmaps-object-map-autocomplete");
        map.bindAutocomplete(new google.maps.places.Autocomplete(autocomplete.get(0)));
        map.addControl(autocomplete.get(0), google.maps.ControlPosition.RIGHT_BOTTOM);
        
        var suggest = $("#artmaps-object-map-suggest");
        if(ArtMapsConfig.IsUserLoggedIn) {
            suggest.click(function() {
               map.suggest(); 
            });
            map.addControl(suggest.get(0), google.maps.ControlPosition.LEFT_TOP);
        } else {
            suggest.hide();
        }
        
        var showall = $("#artmaps-object-map-showall");
        showall.click(function() {
           map.reset(); 
        });
        map.addControl(showall.get(0), google.maps.ControlPosition.LEFT_TOP);
        
        $("a").filter(function() {
            var e = $(this);
            return e.text() == "The Map";
        }).text("Back To The Map");
    })();
});
</script>
<div id="artmaps-object-metadata">
    
    <?php if(get_post_meta(get_the_ID(),"imageurl",true)) { ?>
    <img src="<?php echo get_post_meta(get_the_ID(),"imageurl",true); ?>" alt="<?php the_title(); ?>" />
    <?php } else { ?>
    <img src="{'/content/unavailable.jpg'|artmapsUri}" alt="{$metadata->title}" />
    <?php } ?>
    <h1><?php the_title(); ?></h1>
    <dl>
      <dt>Artist</dt>
        <dd><?php echo get_post_meta(get_the_ID(),"artist",true); ?> <?php echo get_post_meta(get_the_ID(),"artistdate",true); ?></dd>
      <dt>Date</dt>
        <dd><?php echo get_post_meta(get_the_ID(),"artworkdate",true); ?></dd>
    </dl>
    
    <a href="http://www.tate.org.uk/art/artworks/<?php echo get_post_meta(get_the_ID(),"reference",true); ?>" class="artwork-external">View on Tate Online</a>

</div>
<div id="artmaps-object-detail">
<div id="artmaps-object-map" style="width:400px; height:300px;"></div>

<button id="artmaps-object-map-showall" type="button">Show All Suggestions</button>

<button id="artmaps-object-map-suggest" type="button">Suggest A Location</button>

<input id="artmaps-object-map-autocomplete" type="text" />

<div id="artmaps-object-suggestion-message" style="display: none;">
    <p>
        Your suggested location has been added and awaits confirmation.<br />
        Once others have confirmed the location, it will become part of Tate collection data.
    </p>
    <br />
    <h2>Further actions</h2>
    <ul id="artmaps-object-suggestion-message-other-actions">
        <li>
            <span class="artmaps-button" 
                    id="artmaps-object-suggestion-message-comment-button">
                Comment</span> on your suggestion
        </li>
    </ul>
    
</div>
</div>
</div>
<?php endwhile; endif; ?>

<?php // get_footer(); ?>