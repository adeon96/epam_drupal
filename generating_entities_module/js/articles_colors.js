(function ($) {
  Drupal.behaviors.generating_entities_module = {
    attach: function (context, settings) {
		
      $('.articles p', context).once('').each(function () {
        //default color if no color value
        if($(this).attr("class") == "") 
          $(this).attr("class", "gray");
        
        $(this).css("color", $(this).attr("class"));
      });
	  
    }
  };
}(jQuery));