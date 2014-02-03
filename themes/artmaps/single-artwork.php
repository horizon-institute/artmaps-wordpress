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
            "center": new google.maps.LatLng(51.507854, -0.099462), /* Tate Britain */
            "mapTypeControl": false
        }
    };
    var map = new ArtMaps.Object.MapObject($("#artmaps-object-map"), config);

    // Keep maptype matching main map
    window.main_map.addMapTypeListener(function(maptype) {
        map.setMapType(maptype);
    });

    (function() {
        var autocomplete = $("#artmaps-object-map-autocomplete");
        map.bindAutocomplete(new google.maps.places.Autocomplete(autocomplete.get(0)));
        map.addControl(autocomplete.get(0), google.maps.ControlPosition.RIGHT_TOP);

        var suggest = $("#artmaps-object-map-suggest");
        <?php if(is_user_logged_in()) { ?>
            suggest.click(function() {
               map.suggest();
            });
        <?php } else { ?>
            suggest.hide();
        <?php } ?>

        var showall = $("#artmaps-object-map-showall");
        showall.click(function() {
           map.reset();
        });
        map.addControl(showall.get(0), google.maps.ControlPosition.RIGHT_TOP);

        $("a").filter(function() {
            var e = $(this);
            return e.text() == "The Map";
        }).text("Back To The Map");
    })();
});
</script>
<div id="artmaps-object-metadata">
  <div class="content">

    <div class="artmaps-object-image">
      <?php if(get_post_meta(get_the_ID(),"imageurl",true)) { ?>
        <!--<a href="<?php echo get_post_meta(get_the_ID(),"imageurl",true); ?>">-->
          <img src="http://dev.artmaps.org.uk/artmaps/tate/dynimage/y/250/<?php echo get_post_meta(get_the_ID(),"imageurl",true); ?>" data-full-image="<?php echo get_post_meta(get_the_ID(),"imageurl",true); ?>" alt="<?php the_title(); ?>" class="artwork-img" />
        <!--</a>-->
      <?php } else { ?>
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/unavailable.png" alt="<?php the_title(); ?>" class="artwork-img" />
      <?php } ?>
    </div>

    <h1><?php if(get_post_meta(get_the_ID(),"title",true)) { echo get_post_meta(get_the_ID(),"title",true);} else { the_title(); } ?></h1>
    <dl>
      <dt>Artist</dt>
        <dd><?php echo get_post_meta(get_the_ID(),"artist",true); ?> <span class="artist-lifetime"><?php echo str_replace("‑","&ndash;",get_post_meta(get_the_ID(),"artistdate",true)); ?></span></dd>
      <dt>Date</dt>
        <dd><?php echo get_post_meta(get_the_ID(),"artworkdate",true); ?></dd>
      <dt></dt>
        <dd><a href="http://www.tate.org.uk/art/artworks/<?php echo get_post_meta(get_the_ID(),"reference",true); ?>" target="_blank" class="artwork-external">View on Tate Online</a></dd>
    </dl>

    <button id="artmaps-object-map-suggest" type="button">Suggest a location</button>
    
    <?php if(!is_user_logged_in()) { ?>
    <div id="artmaps-object-suggestion-message">
        <h2><i class="fa-question-circle"></i> Where does this artwork belong?</h2>
      <p>You can add a pin to the map, or agree with an existing pin. <a href="#" class="log-in-trigger">Log in</a> to get started.</p>
    </div>
    <?php } ?>

    <div id="artmaps-object-suggestion-message" style="display:none">
      <h2><i class="fa-check"></i> Got it.</h2>
      <p>We've saved your suggested location. If other users agree with your choice, the coordinates will become part of Tate collection data.</p>
      <a href="#" class="artmaps-button" id="artmaps-object-suggestion-message-comment-button">Explain your suggestion</a>
    </div>

    <div class="artmaps-object-comments">
      <?php comments_template(); ?>
    </div>

  </div>
</div>

  <div id="artmaps-object-detail">

    <div id="artmaps-object-map" style="width:400px; height:300px;"></div>

    <button id="artmaps-object-map-showall" type="button">Reset map</button>
    <input id="artmaps-object-map-autocomplete" type="text" />

</div>
</div>
<?php endwhile; endif; ?>

<?php // get_footer(); ?>