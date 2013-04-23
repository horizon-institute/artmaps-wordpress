(function($){

$(document).ready(function($){
   // your code goes here
	var magglass = $('<span class="mag-glass"></span>').click(function(){
		$('#artmaps-objectcontainer img').click();
	}) 
	
	$('#artmaps-objectcontainer').append(magglass);
	
	
//	$('body').append('<div class="on-page-pop-up-wrapper"><div class="on-page-pop-up"><span class="close-pop-up"></span><p>This is the text for the pop up, believe it or not</p></div></div>');
//	
//	$('.on-page-pop-up').fadeIn();
//	$('.close-pop-up').click(function() {
//		  $('.on-page-pop-up-wrapper').fadeOut();
//	});
//	
//	$('.on-page-pop-up-wrapper').click(function() {
//		  $('.on-page-pop-up-wrapper').fadeOut();
//	});

	
	setTimeout (function(){
		if (!$('#artmaps-objectcontainer img[src$="unavailable.jpg"]').length) 
			{	
				
				$("#artmaps-objectcontainer img").hover(
					  function () {
						    $('.mag-glass').fadeIn('fast');
						  },
						  function () {
							  $('.mag-glass').fadeOut('fast');
						  }
						);
			}
	}, 500) 	

	
	

});


   
})(jQuery);