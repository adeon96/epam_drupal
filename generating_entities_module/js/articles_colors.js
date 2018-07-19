(function ($) {
  Drupal.behaviors.generating_entities_module = {
    attach: function (context, settings) {
		
      $('.articles p', context).once('').each(function () {
        $(this).css("color", $(this).attr("class"));
      });
	  
    }
  };
}(jQuery));