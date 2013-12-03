jQuery(document).ready(function(){ 
  
  jQuery(document).click(function(e) {
    var target = e.target;

    if (!jQuery(target).is('.popover') && !jQuery(target).parents().is('.popover') && !jQuery(target).is('.toggle')) {
      jQuery('.popover').fadeOut(150);
    }
  });
  
  jQuery( "#log-in" ).click(function(event) {
    jQuery('.popover').fadeOut(150);
    jQuery( "#log-in-popover" ).show();
    jQuery ( "#user_login" ).focus();
    event.preventDefault();
  });
  
  jQuery( "#map-settings .toggle" ).click(function(event) {
    jQuery('.popover').fadeOut(150);
    jQuery( "#map-settings .settings" ).show();
    event.preventDefault();
  });
  
  // Perform AJAX login on form submit
  jQuery('form#loginform').on('submit', function(e){
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
                  document.location.href = ajax_login_object.redirecturl;
              } else {
                jQuery('form#loginform .loader').delay(750).fadeOut(function() {
                  jQuery('#log-in-popover p.status').text(data.message).slideDown();
                });
              }
          },
          error: function(data){
              jQuery('form#loginform .loader').fadeOut();
              jQuery('#log-in-popover p.status').text("Could not connect!").slideDown();
          }
      });
      e.preventDefault();
  });

});