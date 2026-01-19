
tpepdb2 = function () {
  // tpepdb2.search();
  tpepdb2.item();
  tpepdb2.table();
	// terms ist ersetzt die Begriffe, die in Terms gespeichert sind bzw. baut ein Klasse drum rum. 
	tpepdb2.terms();
};


	var currentindex = 0;
	var maxindex = 0;
	var hidecolsindex = 0;




tpepdb2.table = function () {

};

/**
 * Klick des PDF Download Buttons
 */
tpepdb2.item = function () {

};











jQuery(document).ready(
    function () {
      tpepdb2();
			userstatenavigation();
			einblendenDrupalBug();
			blocksprachumschalter();
    }
);

/*
* Gibt einzelne Parameter der URL zurück
*/
var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};

/*
* Hängt an den Link der Sprachwahl im Drupal einen Link per js an
*/
function blocksprachumschalter() {
	var myhref = '';
	var cid = getUrlParameter('cid');
	//alert(cid);
	var sid = getUrlParameter('sid');
	if (cid > 0) {
		$('.language-dropdown ul a').each(function()
    { 
		myhref = $(this).attr("href");
     $(this).attr("href", myhref + '&cid=' + cid);
   });
	}
	if (sid > 0) {
		$('.language-dropdown ul a').each(function()
    { 
		myhref = $(this).attr("href");
      $(this).attr("href", myhref + '&sid=' + sid);
   });
	}
}


function einblendenDrupalBug(){
	$('.layout-container').css('opacity', '1 !important');
	// .layout-container, header, footer, .pdb-linksource.button, #sliding-popup
}
/*
* Vergleicht die Begriffe der Webseite mit den
 Terms aus dem Wcms_NewsController. Diese werden in der TpePdbWeb.php 
 in ein hidden Field geschrieben.
*/
tpepdb2.terms = function () {

	if (($('#termslist').length > 0)){
		var str_termslist = $('#termslist').val();
		var arr_termslist = str_termslist.split(",");
		//console.log(str_termslist);
		var text = "";
		// alle Ausdrücke aus dem dem hiddden Field als Array duchgehen
		for (i = 0; i < arr_termslist.length; i++) {
			//console.log(arr_termslist[i]);

			// Alle .properties durchgehen und mit dem term[i] vergleichen
			$( ".properties" ).each(function( index ) {	
				text = $( this ).text();
				//console.log( index + ": " + strstr($( this ).text(), "Tear Resistance", true) );
				//console.log( index + ": " + strstr($( this ).text(), "Elongation at Break", true) );
				if(strstr($( this ).text(), arr_termslist[i]) != false ) {
					$( this ).addClass('hasterm');
					$( this ).attr('term', arr_termslist[i]);
					
				}
			});	
			// Alle .pdb2-processing-note-name durchgehen und mit dem term[i] vergleichen
			$( ".pdb2-processing-note-name" ).each(function( index ) {
				var text = $( this ).text();
				if(strstr($( this ).text(), arr_termslist[i]) != false ) {
					$( this ).addClass('hasterm');
					$( this ).attr('term', arr_termslist[i]);
				}
			});

			// Alle materialvorteile  durchgehen und mit dem term[i] vergleichen
			$( ".materialvorteile li " ).each(function( index ) {
				var text = $( this ).text();
				if(strstr($( this ).text(), arr_termslist[i]) != false ) {
					$( this ).addClass('hasterm');
					$( this ).attr('term', arr_termslist[i]);
				}
			});
			
			// Alle anwendungsbereiche  durchgehen und mit dem term[i] vergleichen
			$( ".anwendungsbereiche li " ).each(function( index ) {
				var text = $( this ).text();
				if(strstr($( this ).text(), arr_termslist[i]) != false ) {
					$( this ).addClass('hasterm');
					$( this ).attr('term', arr_termslist[i]);
				}
			});
			
			// Alle Verordnungen  durchgehen und mit dem term[i] vergleichen
			$( ".verordnungenzulassungen li " ).each(function( index ) {
				var text = $( this ).text();
				if(strstr($( this ).text(), arr_termslist[i]) != false ) {
					$( this ).addClass('hasterm');
					$( this ).attr('term', arr_termslist[i]);
				}
			});
			
		}
	}

};




/*
Ruft die Details des Terms auf, der angeklickt wurde
*/
tpepdb2.termsdetails = function (str, off_y) {
	//alert(str)
	var str_url = "admin/wcms/news/termdetails?str=" + str;

	//alert(str_url);
    $.get(
       baseproject + str_url,
       function (html) {
					//alert(y);
				 $('.termsdetails').remove();
				 var ypos = off_y - 50;
					$('body').append('<div style=" top: ' + ypos + 'px; position: absolute; background: #fff; border: 1px solid #333; z-index: 602; " class="termsdetails"><div class="close-term">X</div>' + html + '</div>');
					//$('.pdb2-treffer span').html(html);
        }
    );
};



function userstatenavigation() {
	//alert($('.pdb2-userstate').html());
	//$('.navbar-nav').html($('.pdb2-userstate').html());
	//$( "ul[block='block-hauptnavigation'" ).html( $('.pdb2-userstate').html() );
	
}


/*
* Speichert die Einträge in der Statistik db
*/
function tpepedb2stats(str, lang, type, mode, sendtype) {
//	 var str_search = '&' + $( "#tpepdb2_search" ).serialize();
	
	var language = 'de';
	var str_url = "admin/tpepdb2/ajax/saveStats?s=" + str + "&lang=" + lang + "+&type=" + type + "&mode=" + mode + "&sendtype=" + sendtype;

	//alert(str_url);
	
    $.get(
       baseproject + str_url,
       function (html) {
				//	alert(html);

					return html;
        }
    );
}

// Has Term Laden des Inhalts und anzeigen in Popup
jQuery(document).on (
	'click',
	'.close-term',
	function () {
		$('.termsdetails').remove();
	}
);
// Blendet den Anmeldebalken aus um die Sprachwahl zu ermöglichen
jQuery(document).on (
	'click',
	'.language-dropdown',
	function () {
		$('.pdb2-userstate').hide();
	}
);

jQuery(document).on (
	'click',
	'.hasterm',
	function () {
		//alert($(this).html());
		var p = $( this);
		//var position = p.position();
		var offset = p.offset();
		//$( "p:last" ).text( "left: " + position.left + ", top: " + position.top );
		tpepdb2.termsdetails($(this).attr('term'), offset.top - 15);
	}
);

// Hardness radio supersoft
jQuery(document).on (
	'click',
	'#cbox_supersoft',
	function () {
		$('.pdb2-hardness-range-container').css('opacity', '0.3');
	}
);
// Hardness radio ShoreD
jQuery(document).on (
	'click',
	'#cbox_shored',
	function () {
		$('.pdb2-hardness-range-container').css('opacity', '0.3');
	}
);
// Hardness radio Normal
jQuery(document).on (
	'click',
	'#cbox_hardness',
	function () {
		$('.pdb2-hardness-range-container').css('opacity', '1');
	}
);




$('#tpepdb2_s_text__').on('keyup keypress', function(e) {
  var keyCode = e.keyCode || e.which;
  if (keyCode === 13) { 
    e.preventDefault();
		//alert('test');
		jQuery("#tpepdb2_search").submit();
    return false;
  }
	
});

jQuery(document).on (
	'click',
	'.ncw-light-background',
	function () {
						jQuery('.ncw-light-img').remove();
						jQuery(this).remove();
	}
);
jQuery(document).on (
	'click',
	'.tpe-img-container',
	function () {
		var html = '<div class="ncw-light-background"></div>';
		html += '<img src="' + jQuery(this).children('img').attr('src') + '" class="ncw-light-img" />';
		jQuery('body').append(html);
		var bodyw = jQuery('body').width();
		var imgw = jQuery('.ncw-light-img').width();
		var left = (bodyw - imgw) / 2;
		jQuery( ".ncw-light-img" ).css('left', left + 'px');
		
		jQuery( ".ncw-light-img" ).animate({
		 opacity: 1
		}, 350, function() {
		// Animation complete.
		});
		
	}
);

// Hardness Min
jQuery(document).on (
	'change',
	'#rangeHardnessMin',
	function () {
		pdb2_count_suche();
	}
);

// Hardness Max
jQuery(document).on (
	'change',
	'#rangeHardnessMax',
	function () {
		pdb2_count_suche();
	}
);

// HArdness Mode Radio
jQuery(document).on (
	'click',
	'.hardnessmode',
	function () {
		pdb2_count_suche();
	}
);


// Klick auf Checkbox
jQuery(document).on (
	'click',
	'.pdb2-suche-checkbox',
	function () {
		pdb2_showhideSearch('textsearch', 'checkboxes');
		pdb2_count_suche();
		
	}
);
// Klick auf Checkbox
jQuery(document).on (
	'click',
	'.greyslider',
	function () {
		greyslider();
	}
);

jQuery(document).on (
	'click',
	'.whiteslider',
	function () {
		whiteslider();
	}
);

// Suchen Button drücken
// pdb2_search ruft das hier auf
jQuery(document).on (
	'click',
	'.pdb2_search',
	function () {
		// Checkboxen nur aktivieren wenn Mobile ansicht Suchfilter aus ist
		if (mobile_search_view == false) {
			pdb2_suche();
		}
		

	}
);
// Hier wird nur der Content nach oben gesrollt
jQuery(document).on (
	'click',
	'.refresh-icon',
	function () {
		pdb2_suche();
			jQuery('body,html').animate({
				scrollTop: 0
			}, 500);
			return false;
	}
);
jQuery(document).on (
	'click',
	'.show-results',
	function () {
		pdb2_suche();
			jQuery('body,html').animate({
				scrollTop: 0
			}, 500);
		
			return false;
	}
);




// Suchen Button drücken
// pdb2_search ruft das hier auf
jQuery(document).on (
	'click',
	'.pdb2-search-console-textsearch .kbtpe-icon-search',
	function () {
		pdb2_suche();
	}
);

// 

// Klick auf Suchfeld
jQuery(document).on (
	'click',
	'#tpepdb2_s_text',
	function () {
		pdb2_showhideSearch('checkboxes', 'textsearch');
		pdb2_count_suche();
		reset_search_inputs();
		var textinserach = jQuery('#tpepdb2_s_text').val();
		if (textinserach.length < 2) {
			if (mobile_search_view == false) {
			reset_right_content();
			}
		}
		
	}
);



//oninput



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

//$('#tpepdb2_s_text').donetyping(function(callback, timeout=150){
	//alert('Meise');
//  $('.pdb2-treffer').html('Event last fired @ ' + (new Date().toUTCString()));
//});


/*
* Verhindert das Abschicken der Suchform
* Ruft stattdessen den Trefferzähler auf
*/
function dontSubmit (e, my) {
	if ( e.keyCode==13 ) { // Enter abfragen
		my.onblur = null; // Blur-Ereign. f. Input-Feld vor alert löschen
		pdb2_suche();

		return false; // in diesem Fall nichts machen
	} else {
		//pdb2_count_suche();
		
		//showFormValues();
		//return true;
	}
}

function pdb2_showhideSearch(hide, show)
{
	
	if (hide == 'textsearch' ) {
		//$().css('opacity', '30');
		jQuery( "#tpepdb2_s_text" ).val('');
		jQuery( "#tpepdb2_s_text" ).animate({
		 opacity: 0.3
		}, 350, function() {
		// Animation complete.
		});
	}
	
	if (show == 'textsearch' ) {
		//$().css('opacity', '30');
		jQuery( "#tpepdb2_s_text" ).animate({
		 opacity: 1
		}, 350, function() {
		// Animation complete.
		});
	}
	
	if (hide == 'checkboxes' ) {
		//$().css('opacity', '30');
		jQuery( ".checkboxes" ).animate({
		 opacity: 0.3
		}, 350, function() {
		// Animation complete.
		});
	}
	
	if (show == 'checkboxes' ) {
		//$().css('opacity', '30');
		jQuery( ".checkboxes" ).animate({
		 opacity: 1
		}, 350, function() {
		// Animation complete.
		});
	}
}

/*
* Blendet den Slider der Härtewahl fast aus
*/
function greyslider(){
	jQuery('.range-container').animate({
		 opacity: 0.2
		}, 350, function() {
		// Animation complete.
		});
	jQuery('.range-max').animate({
		 opacity: 0.2
		}, 350, function() {
		// Animation complete.
		});
	jQuery('.range-min').animate({
		 opacity: 0.2
		}, 350, function() {
		// Animation complete.
		});
}
/*
* Blendet den Slider der Härtewahl ein
*/
function whiteslider(){
	jQuery('.range-container').animate({
		 opacity: 1
		}, 350, function() {
		// Animation complete.
		});
	jQuery('.range-max').animate({
		 opacity: 1
		}, 350, function() {
		// Animation complete.
		});
	jQuery('.range-min').animate({
		 opacity: 1
		}, 350, function() {
		// Animation complete.
		});
}



/*
* Suche Treffer Zähler
*/
function pdb2_count_suche ()
{
	// Aufruf zum Speichern des TML Bodys auf der rechten Seite
	save_right_content();
	 var str_search = '&' + $( "#tpepdb2_search" ).serialize();
	
	var language = 'de';
	var str_url = "admin/tpepdb2/ajax/countSuche?l=" + language + str_search;

	$.get(
		 baseproject + str_url,
		 function (html) {
				$('.pdb2-treffer span').html(html);
			}
	);
}


function pdb2_count_suche_int ()
{
	//$('.pdb2-treffer span').html('22');
	 var str_search = '&' + $( "#tpepdb2_search" ).serialize();
	
	var language = 'de';
	var str_url = "admin/tpepdb2/ajax/countSuche?l=" + language + str_search;

	//alert(str_url);
    $.get(
       baseproject + str_url,
       function (html) {
					//alert(html);

					return html;
        }
    );
	
}

/*
* Suche Ausgeben
*/
function pdb2_suche ()
{
	// Speichert zuerst den Inhalt der Rechten Seite in eine Variable
	save_right_content();
	
	
	var str_search = '&' + $( "#tpepdb2_search" ).serialize();
	
	var language = 'de';
	var str_url = "admin/tpepdb2/ajax/suche?l=" + language + str_search;
	//console.log(str_url);
	$.get(
		 baseproject + str_url,
		 function (html) {
				//alert(html);
			 	var treffer = $('.pdb2-treffer span').text();
			$('.pdb2_search_headline').html('<h2>' + $('#searchresult').val() + '</h2>');
			//$('.pdb2_search_headline').html('<h2>Search result</h2>');
			$('.pdb2_search_headline').css('height', '0px');
			if (html.length > 10) {
					$('.pdb2_search_list').html(html);
			} else {
				$('.pdb2_search_list').html($('#noresult').val());
			}
			
				
			 var islogintrue = $('#islogintrue').val();
			 var minheightlist = 2159;
			 if (mobile_search_view == true) {
				 minheightlist = 1000;
			 }
			 if (islogintrue == 'true') {
					 minheightlist = 2030;
			 }
			 
			 var zeileh_desctop = 42;
			 var zeileh_mobil = 44;
			 if (mobile_search_view == false) {
				 
			 //	if (treffer > 54) {
				// 	minheightlist = treffer * zeileh_desctop;
				//}
				if (treffer * zeileh_desctop > minheightlist) {
					minheightlist = treffer * zeileh_desctop;
				}
			 } else {
			 	//if (treffer > 30) {
				// 	minheightlist = treffer * zeileh_mobil;
				//}
				if (treffer * zeileh_mobil > minheightlist) {
					minheightlist = treffer * zeileh_mobil;
				}
			 }
			 
			 	$('.pdb2_search_list').css('min-height', minheightlist+ 'px');
			 	
				if (mobile_search_view == true) {
					$('.treffercompound').hide();
				}
			 
			}
	);
}

function pdb2_verzoegerte_suche ()
{

			setTimeout(function(){ 
					pdb2_suche();
			}, 1600);
	
}

function send_share ()
{
	var language = 'de';
	var customer_email = $('#customer-email').val();
	var customer_subject = $('#customer-subject').val();
	var customer_text = $('#customer-text').val();
	var your_email = $('#your-email').val();
	var file_link = $('#file_link').val();
	var file_ls = $('#file_ls').val();
	var file_dm = $('#file_dm').val();
	if (file_dm == 'related') {
			file_link = encodeURI(file_link);
	}
	var str_url = "admin/tpepdb2/ajax/shareFile?l=" + language + '&customer_subject=' + customer_subject + '&customer_text=' + customer_text + '&your_email=' + your_email + '&customer_email=' + customer_email + '&file_link=' + file_link + '&file_ls=' + file_ls + '&file_dm=' + file_dm;
    $.get(
       baseproject + str_url,
       function (html) {
					//alert(html);
				// $('.pdb2_search_headline').html('<h2>' + $('.pdb2-label-input-search-results').val() + '</h2>');
				//	$('.pdb2_search_list').html(html);
        }
    );
	//alert('E-Mail wird versendet');
	$('.pdb-share-dialog').hide();
}

/*
* Öffent den Teilen Dialog
*/
function open_share_dialog(mode, language, compound_id, file_link)
{
	$('#file_link').val(file_link);
	$('#file_ls').val(language);
	$('#file_dm').val(mode);
	var filetitle = '';
	if (mode == 'datasheet') {
			filetitle = 'Datasheet ' + language + ' ' + $('.headline-1').html();
	} else if (mode == 'pg') {
			filetitle = 'Processing Guideline ' + language + ' ' + $('.headline-1').html();
	} else {
			filetitle = 'Datasheet ' + language + ' ' + $('.headline-1').html();
	}
	filetitle = filetitle.toUpperCase();
	$('.share-file-name').html(filetitle);
	$('.pdb-share-dialog').show();
	//$('.pdb-share-dialog').html(mode+language+compound_id);

}
/*
* Schließt den Teilen Dialog
*/
function close_share_dialog(mode, language, compound_id, file_link)
{

	$('.pdb-share-dialog').hide();

}

 function showFormValues() {
    var str = $( "#tpepdb2_search" ).serialize();
    $('.pdb2-treffer span').html(str);
	 	
  }


/*
* Öffnet den Suchen Dialog für kleine Ansichten
*/
var mobile_search_view = false;
	function open_small_search () {
		mobile_search_view = true;
		$('.search-small-overlay').show();
		$('.search-small-label').show();
		$('.pdb2-search-console-textsearch .input-group').show();
		$('.tpe-pdb-search-textsearch').show();
		$('.kbtpe-icon-search:before').show();
		$('.pdb2-search-console-show').show();
		$('.pdb2-search-console-sliderblock').show();
		$('.pdb2-search-console-haftungblock').show();
		$('.pdb2-search-console-eigenschaftenblock').show();
		$('.pdb2-search-console-farbblock').show();
		$('.pdb2-search-console-show').css('z-index','700');
		$('.pdb2-search-console-show').css('top','80px');
		$('.pdb2-search-console-regionblock').hide();
		$('.regionblocktoploggedin').hide();
		$('.regionblocktop').show();
		
		$('.pdb2_search_list').css('min-height','1910px');
		$('.search-small-overlay').css('height','210px');
	  $('.pdb2_search_headline').hide();
		$('.pdb-list-search').hide();
		$('#block-newsletter').hide();
		$('#footer').hide();
		$('.treffercompound').css('opacity', '0');
		$('.pdb2-userstate').css('position', 'fixed');
			$('.pdb2-userstate').hide();
		
		$('.open-small-search').hide();
	}
/*
* Schließt den Suchen Dialog für kleine Ansichten
*/
	function close_small_search() {
		if (mobile_search_view != false) {
			$('.search-small-overlay').hide();
			$('.search-small-label').hide();
			$('.pdb2-search-console-textsearch .input-group').hide();
			$('.tpe-pdb-search-textsearch').hide();
			$('.kbtpe-icon-search:before').hide();
			$('.pdb2-search-console-show').hide();
			$('.pdb2-search-console-sliderblock').hide();
			$('.pdb2-search-console-haftungblock').hide();
			$('.pdb2-search-console-eigenschaftenblock').hide();
			$('.pdb2-search-console-farbblock').hide();
			$('.pdb2-search-console-regionblock').hide();
			$('.regionblocktop').hide();

			$('.pdb2-search-console-show').css('z-index','555');
			$('.pdb2_search_list').css('min-height','1600px');
			$('.treffercompound').show();
			$('.pdb2_search_headline').show();
			$('.pdb-list-search').show();
			$('#block-newsletter').show();
			$('#footer').show();
			$('.treffercompound').css('opacity', '1');
			$('.pdb2-userstate').css('position', 'static');
			$('.pdb2-userstate').show();
			$('.open-small-search').show();
			jQuery('body,html').animate({
				scrollTop: 0
			}, 500);
		}
		
		mobile_search_view = false;
	}




/*
* Strstr wei bei PHP Möglich
*/
function strstr(haystack, needle, bool) {
    var pos = 0;
    haystack += "";
    pos = haystack.indexOf(needle); 
		if (pos == -1) {
    	return false;
    } else {
        if (bool) {
            return haystack.substr(0, pos);
        } else {
            return haystack.slice(pos);
        }
    }
}

/*
* Setzt die Suche zurück
*/
function reset_search_inputs()
{
		$( ".pdb2_search" ).each(function( index ) {	
			//console.log( index + $( this ).val() );
			$( this ).prop( "checked", false );
		});
		$('#cbox_hardness').prop( "checked", true );
}


var right_content_flag = false;
var right_content_headline = '';
var right_content_body = '';
/*
* speichert den Body auf der rechten Seite, das Ganze kann dann nach dem leeren der Texteingabe des Suchfeldes wider zurückgesetzt werden
*/
function save_right_content()
{
	if (right_content_flag == false) {
		right_content_headline = $('.pdb2_search_headline').html();
		right_content_body = $('.pdb2_search_list').html();
		right_content_flag = true;
	}
}

/*
* gibt den Body auf der rechten Seite zurück der am Anfang gespeichert wurde
*/
function reset_right_content(){
	$('.pdb2_search_headline').css('height', '109px');
	$('.pdb2_search_headline').html(right_content_headline);
	$('.pdb2_search_list').html(right_content_body);
	
	//$('.pdb2_search_headline').css('height', '0px');
}
// Positioniert die Suchergebnisse neu beim scrollen
$(document).ready(function () {  
 // var top = $('.pdb2_search_list').offset().top;
	
// Ist die Serienübersicht eingeblendet oder die Produkte

	
	var top = $('.pdb2_search_list').offset().top;
	var mode = '';
	var count_compounds = 0;
  $(window).scroll(function (event) {
		if ( $( "#is_compound_search" ).length ) {
			mode = 'compound';
			count_compounds = $( "#is_compound_search" ).val();

		//	console.log('compound');
		} else {
		 	mode = 'serie';
			//console.log('serie');
		}
    var y = $(this).scrollTop();
		var newy = 0;
		var newmay = 0;
		var yoffset = 0;
		if (mode == 'serie') {
			newy = y - 220;
			newmay = y - 220;
		} else {
			newy = y - 120;
			newmay = y - 220;
			yoffset = 98;
			
		}
		if ( $('.open-small-search').is(":hidden") ) {
		//if (mobile_search_view == false) {
			// Ergebnisse rechts
			if (y + yoffset >= top && count_compounds < 31) {
				if (newy > 900) {
				//	$('.pdb2_search_list').css('top',  '900px');
				} else {
				//	$('.pdb2_search_list').css('top', newy +'px');
				}
			} else {
				if (mode == 'serie') {
				//	 $('.pdb2_search_list').css('top', '50px');
				} else {
				//	 $('.pdb2_search_list').css('top', '54px');
				}

			//$('.pdb2_search_list').width($('.pdb2_search_listx').parent().width());
			}
		}


		// Das Grüne Feld mit den Ergebnissen poistionieren Wenn mobile_search_view == false
		if (mobile_search_view == false) {
			if (y >= 180) {
				//$('.pdb2-search-console-show').css('border-top', '10px solid #fff');
				//$('.pdb2-search-console-show').css('border-bottom', '10px solid #fff');	
				$('.pdb2-search-console-show').css('position', 'fixed');
				$('.pdb2-search-console-show').css('top', '176px');
				if (y >= 2000) {
						var top_console_showmax_y =  2000 - y + 176;
						$('.pdb2-search-console-show').css('top', top_console_showmax_y + 'px');
				}
				//console.log(y);
			} else {
				$('.pdb2-search-console-show').css('position', 'absolute');
				$('.pdb2-search-console-show').css('top', '180px');
			}
		} else {
			//console.log(newy);
			if (y > 80) {

				var maitry = newmay + 212;
				
				$('.pdb2-search-console-show').css('top', maitry + 'px');
			} else {
				$('.pdb2-search-console-show').css('top', '80px');
			}
			
			if (y > 1) {
				var mylabely = newmay + 155;
				$('.search-small-label').css('top', mylabely + 'px');
			} else {
				$('.search-small-label').css('top', '-59px');
			}
		}

		
		
  });
});