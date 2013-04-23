(function($){

$(document).ready(function($){
   // your code goes here
	$('#artmaps-objectcontainer').append('<span class="mag-glass"></span>');
	
	
	$('body').append('<div class="on-page-pop-up-wrapper"><div class="on-page-pop-up"><span class="close-pop-up"></span><p>This is the text for the pop up, believe it or not</p></div></div>');
	
	$('.on-page-pop-up').fadeIn();
	$('.close-pop-up').click(function() {
		  $('.on-page-pop-up-wrapper').fadeOut();
	});
	
	$('.on-page-pop-up-wrapper').click(function() {
		  $('.on-page-pop-up-wrapper').fadeOut();
	});
	

	
});


   
})(jQuery);