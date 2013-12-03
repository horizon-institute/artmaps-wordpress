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
      jQuery('#log-in-popover p.status').show().text(ajax_login_object.loadingmessage);
      jQuery.ajax({
          type: 'POST',
          dataType: 'json',
          url: ajax_login_object.ajaxurl,
          data: { 
              'action': 'ajaxlogin', //calls wp_ajax_nopriv_ajaxlogin
              'username': jQuery('form#loginform #username').val(), 
              'password': jQuery('form#loginform #password').val(), 
              'security': jQuery('form#loginform #security').val() },
          success: function(data){
                      console.log(data);
            console.log('success');
              jQuery('#log-in-popover p.status').text(data.message);
              if (data.loggedin == true){
                  document.location.href = ajax_login_object.redirecturl;
              }
          },
          error: function(data){
                      console.log('error');
              jQuery('#log-in-popover p.status').text("Nope");
          }
      });
      e.preventDefault();
  });

});