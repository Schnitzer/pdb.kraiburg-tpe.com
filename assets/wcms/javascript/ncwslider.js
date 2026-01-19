/*

  $( function() {
    $( "#slider-range-hardness" ).slider({
      range: true,
      min: 20,
      max: 80,
      values: [ 20, 80 ],
			step: 5,
      slide: function( event, ui ) {
        //$( "#rangeHardnessMin" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
        $( "#rangeHardnessMin" ).val( ui.values[ 0 ]);
        $( "#rangeHardnessMax" ).val( ui.values[ 1 ]);
        $('#hardness-range-label').html('Hardness ' + $( "#rangeHardnessMin" ).val() + ' Shore A - ' + $( "#rangeHardnessMax" ).val() + ' Shore A');
				pdb2_count_suche();
      }
    });
    $( "#rangeHardnessMin" ).val( $( "#slider-range-hardness" ).slider( "values", 0 ));
    $( "#rangeHardnessMax" ).val( $( "#slider-range-hardness" ).slider( "values", 1 ));
    $('#hardness-range-label').html('Hardness ' + $( "#rangeHardnessMin" ).val() + ' Shore A - ' + $( "#rangeHardnessMax" ).val() + ' Shore A');
    pdb2_count_suche();
  } );*/


  $( function() {
    $( "#slider-range-hardness2" ).slider({
      range: true,
      min: 10,
      max: 95,
      values: [ 10, 95 ],
			step: 5,
      slide: function( event, ui ) {
        //$( "#rangeHardnessMin" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
        $( "#rangeHardnessMin" ).val( ui.values[ 0 ]);
        $( "#rangeHardnessMax" ).val( ui.values[ 1 ]);
        $('.range-min span').html($( "#rangeHardnessMin" ).val());
        $('.range-max span').html($( "#rangeHardnessMax" ).val());
				pdb2_count_suche();
				pdb2_verzoegerte_suche();
				//pdb2_suche();
      }
    });
    $( "#rangeHardnessMin" ).val( 10);
    $( "#rangeHardnessMax" ).val( 95);
    //$('#hardness-range-label').html('Hardness ' + $( "#rangeHardnessMin" ).val() + ' Shore A - ' + $( "#rangeHardnessMax" ).val() + ' Shore A');
    pdb2_count_suche();
		
  } );



/*
* Verzögerte Key abfrage des Suchfeldes in search_haeder.php
*/

//
// $('#element').donetyping(callback[, timeout=1000])
// Fires callback when a user has finished typing. This is determined by the time elapsed
// since the last keystroke and timeout parameter or the blur event--whichever comes first.
//   @callback: function to be called when even triggers
//   @timeout:  (default=1000) timeout, in ms, to to wait before triggering event if not
//              caused by blur.
// Requires jQuery 1.7+
//
;(function($){
    $.fn.extend({
        donetyping: function(callback,timeout){
            timeout = timeout || 1e3; // 1 second default timeout
            var timeoutReference,
                doneTyping = function(el){
                    if (!timeoutReference) return;
                    timeoutReference = null;
                    callback.call(el);
                };
            return this.each(function(i,el){
                var $el = $(el);
                // Chrome Fix (Use keyup over keypress to detect backspace)
                // thank you @palerdot
                $el.is(':input') && $el.on('keyup keypress paste',function(e){
                    // This catches the backspace button in chrome, but also prevents
                    // the event from triggering too preemptively. Without this line,
                    // using tab/shift+tab will make the focused element fire the callback.
                    if (e.type=='keyup' && e.keyCode!=8) return;
                    
                    // Check if timeout has been set. If it has, "reset" the clock and
                    // start over again.
                    if (timeoutReference) clearTimeout(timeoutReference);
                    timeoutReference = setTimeout(function(){
                        // if we made it here, our timeout has elapsed. Fire the
                        // callback
                        doneTyping(el);
                    }, timeout);
                }).on('blur',function(){
                    // If we can, fire the event since we're leaving the field
                    doneTyping(el);
                });
            });
        }
    });
})(jQuery);

$('#tpepdb2_s_text').donetyping(function(){
	pdb2_count_suche();
	//pdb2_suche();
	// Abfragen od der Inhalt des Suchfeldes leer ist. Wenn ja dann wird der Urprüngliche Content wieder geladen
	var search_input = jQuery('#tpepdb2_s_text').val();
	if (search_input.length < 1) {
			reset_right_content();
	}
});