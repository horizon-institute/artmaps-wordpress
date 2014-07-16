<!DOCTYPE div PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
</head>
<body>
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
                jQuery('.fancybox-type-ajax').animate({
                  scrollTop: jQuery("#artmaps-object-detail").offset().top
                }, 250);
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
      <?php if(get_post_meta(get_the_ID(),"ImageFile", true)) { ?>
      <img src="//artmaps.tate.org.uk/artmaps/tate/dynimage/y/250/<?php echo get_post_meta(get_the_ID(), "ImageFile", true); ?>"
      		data-full-image="<?php echo get_post_meta(get_the_ID(), "ImageFile", true); ?>" alt="<?php the_title(); ?>" class="artwork-img" />
      <?php } else { ?>
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/unavailable.png" alt="<?php the_title(); ?>" class="artwork-img" />
      <?php } ?>
    </div>

    <h1><?php if(get_post_meta(get_the_ID(), "Name", true)) { echo get_post_meta(get_the_ID(), "Name", true);} else { the_title(); } ?></h1>
    <dl>
      <dt>Allegiance</dt>
      <dd><?php echo get_post_meta(get_the_ID(), "Allegiance", true); ?></dd>
      <dt>Biography</dt>
      <dd><a href="<?php echo get_post_meta(get_the_ID(), "Biography", true); ?>" target="_blank">Link</a></dd>
      <dt>English Notes</dt>
      <dd><?php echo get_post_meta(get_the_ID(), "English Notes", true); ?></dd>
      <dt>EventType</dt>
      <dd><?php echo get_post_meta(get_the_ID(), "EventType", true); ?></dd>
      <dt>Place Name</dt>
      <dd><?php echo get_post_meta(get_the_ID(), "Place Name", true); ?></dd>
      <dt>Spanish Notes</dt>
      <dd><?php echo get_post_meta(get_the_ID(), "Spanish Notes", true); ?></dd>
      <dt>SpanishEventType</dt>
      <dd><?php echo get_post_meta(get_the_ID(), "SpanishEventType", true); ?></dd>
      <dt>Year</dt>
      <dd><?php echo get_post_meta(get_the_ID(), "Year", true); ?></dd>
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
      <p>We've saved your suggested location.</p>
      <a href="#" class="artmaps-button" id="artmaps-object-suggestion-message-comment-button">Explain your suggestion</a>
    </div>

    <div class="artmaps-object-comments">
      <?php comments_template(); ?>
    </div>

  </div>
</div>

  <div id="artmaps-object-detail">

    <div id="artmaps-object-map" style="width:100%; height:100%;"></div>

    <button id="artmaps-object-map-showall" type="button">Reset map</button>
    <input id="artmaps-object-map-autocomplete" type="text" />

</div>
</div>
<?php endwhile; endif; ?>

<?php // get_footer(); ?>
</body>
</html>