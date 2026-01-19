/*
  JS Grundfunktionen | © 2009 by www.kraiburgtpe.de
____________________________________________ */

/*
 * jqModal - Minimalist Modaling with jQuery
 *   (http://dev.iceburg.net/jquery/jqModal/)
 *
 * Copyright (c) 2007, 2008 Brice Burgess <bhb@iceburg.net>
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 * 
 * $Version: 03/01/2009 +r14
 */
(function($) {
$.fn.jqm=function(o){
var p={
overlay: 50,
overlayClass: 'jqmOverlay',
closeClass: 'jqmClose',
trigger: '.jqModal',
ajax: F,
ajaxText: '',
target: F,
modal: F,
toTop: F,
onShow: F,
onHide: F,
onLoad: F
};
return this.each(function(){if(this._jqm)return H[this._jqm].c=$.extend({},H[this._jqm].c,o);s++;this._jqm=s;
H[s]={c:$.extend(p,$.jqm.params,o),a:F,w:$(this).addClass('jqmID'+s),s:s};
if(p.trigger)$(this).jqmAddTrigger(p.trigger);
});};

$.fn.jqmAddClose=function(e){return hs(this,e,'jqmHide');};
$.fn.jqmAddTrigger=function(e){return hs(this,e,'jqmShow');};
$.fn.jqmShow=function(t){return this.each(function(){t=t||window.event;$.jqm.open(this._jqm,t);});};
$.fn.jqmHide=function(t){return this.each(function(){t=t||window.event;$.jqm.close(this._jqm,t)});};

$.jqm = {
hash:{},
open:function(s,t){var h=H[s],c=h.c,cc='.'+c.closeClass,z=(parseInt(h.w.css('z-index'))),z=(z>0)?z:3000,o=$('<div></div>').css({height:'100%',width:'100%',position:'fixed',left:0,top:0,'z-index':z-1,opacity:c.overlay/100});if(h.a)return F;h.t=t;h.a=true;h.w.css('z-index',z);
 if(c.modal) {if(!A[0])L('bind');A.push(s);}
 else if(c.overlay > 0)h.w.jqmAddClose(o);
 else o=F;

 h.o=(o)?o.addClass(c.overlayClass).prependTo('body'):F;
 if(ie6){$('html,body').css({height:'100%',width:'100%'});if(o){o=o.css({position:'absolute'})[0];for(var y in {Top:1,Left:1})o.style.setExpression(y.toLowerCase(),"(_=(document.documentElement.scroll"+y+" || document.body.scroll"+y+"))+'px'");}}

 if(c.ajax) {var r=c.target||h.w,u=c.ajax,r=(typeof r == 'string')?$(r,h.w):$(r),u=(u.substr(0,1) == '@')?$(t).attr(u.substring(1)):u;
  r.html(c.ajaxText).load(u,function(){if(c.onLoad)c.onLoad.call(this,h);if(cc)h.w.jqmAddClose($(cc,h.w));e(h);});}
 else if(cc)h.w.jqmAddClose($(cc,h.w));

 if(c.toTop&&h.o)h.w.before('<span id="jqmP'+h.w[0]._jqm+'"></span>').insertAfter(h.o); 
 (c.onShow)?c.onShow(h):h.w.show();e(h);return F;
},
close:function(s){var h=H[s];if(!h.a)return F;h.a=F;
 if(A[0]){A.pop();if(!A[0])L('unbind');}
 if(h.c.toTop&&h.o)$('#jqmP'+h.w[0]._jqm).after(h.w).remove();
 if(h.c.onHide)h.c.onHide(h);else{h.w.hide();if(h.o)h.o.remove();} return F;
},
params:{}};
var s=0,H=$.jqm.hash,A=[],ie6=$.browser.msie&&($.browser.version == "6.0"),F=false,
i=$('<iframe src="javascript:false;document.write(\'\');" class="jqm"></iframe>').css({opacity:0}),
e=function(h){if(ie6)if(h.o)h.o.html('<p style="width:100%;height:100%"/>').prepend(i);else if(!$('iframe.jqm',h.w)[0])h.w.prepend(i); f(h);},
f=function(h){try{$(':input:visible',h.w)[0].focus();}catch(_){}},
L=function(t){$()[t]("keypress",m)[t]("keydown",m)[t]("mousedown",m);},
m=function(e){var h=H[A[A.length-1]],r=(!$(e.target).parents('.jqmID'+h.s)[0]);if(r)f(h);return !r;},
hs=function(w,t,c){return w.each(function(){var s=this._jqm;$(t).each(function() {
 if(!this[c]){this[c]=[];$(this).click(function(){for(var i in {jqmShow:1,jqmHide:1})for(var s in this[i])if(H[this[i][s]])H[this[i][s]].w[i](this);return F;});}this[c].push(s);});});};
})(jQuery);

/**
 * 
 */
$(document).ready(function () {

    $.fn.kraiburgTpeScripts = function(settings) {
		settings = jQuery.extend({
			setupAnimationsZeit: '500' // Durschnittliche Zeit aller Animationen
		},settings);
	    
		// Navigation hover-effect
	    $(".nav1").hover(
	      function () {
	    	$(this).css({
	          height: '27px',
	          borderBottom: '2px solid #ffc300'
	        });
	      },
	      function() {
	    	$(this).css({
	          height: '29px',
	          borderBottom: 'none'
	        });
	    	}
	    );

	  // --------------------- TABLES --------------------------
	
	  $(".table tr:nth-child(odd)").css({background: '#cbcbcb'});
	  $(".table tbody tr:nth-child(odd) td:nth-child(even)").css({background: '#bfbfbf'});
	  $(".table tbody tr:nth-child(even) td:nth-child(even)").css({background: '#9f9f9f'});
	
	  // --------------------- SEARCHFRAME --------------------------
	
	  $('#search_wrapper').jqm({trigger: '#text_search'}); 
	  $('#text_search').click(function () {
	      $("select").css('display', 'none');
	  });
	  $('#s_close').click(function () {
	      $('#search_wrapper').jqmHide();
	      $("select").css('display', 'block');
	  });
    
	  // --------------------- LOGOUT MESSAGE --------------------------
	  
      if ($("#logout_message").length > 0) {
          window.setTimeout(function() {
              $('#logout_message').hide('slow', function () {
                  $('#logout_message').remove();
              });
          }, 3000);       
      }	  
	};	
	
	
	// language select
	var language_select_visible = false;
	$("#change_language li, #change_language_dropdown_icon").click(
	    function () {
	        $("#change_language_overlay").show();
	        $("#change_language li").show();
	        language_select_visible = true;
	    }
	);
    $("#change_language_overlay").click(
        function () {
            if (true == language_select_visible) {
                $("#change_language_overlay").hide();
                $("#change_language li.selectable").hide();
                language_select_visible = false;
            }
        }
    );	
	
	// Activate kraiburgTpeScripts if HTML is ready
	$("html").kraiburgTpeScripts();

});

/**
 * Befores the WYSIWYG Editor is started remove the styles from the product table
 * 
 * @return
 */
function beforeWYSIWYGStart ()
{
    $(".table tr:nth-child(odd)").attr('style', '');
    $(".table tr:nth-child(odd) td:nth-child(even)").attr('style', '');
    $(".table tr:nth-child(even) td:nth-child(even)").attr('style', '');
}

/**
 * Text search
 * 
 * @return
 */
function search () 
{
    $.post('http://www.kraiburg-tpe.com/admin/wcms/website/search', $('#textsearchform').serialize(),
    function (data) {
        $('#ts_results').html('');
        $.each(data, function(site_id, attributes) {
            $('#ts_results').append(
                '<div class="ts_box">'
                + '<h4><a href="' + attributes['url'] + '">' + attributes['name'] + '</a></h4>'
                + '<p>'
                + '<a href="' + attributes['url'] + '">' + attributes['content'] + '</a>'
                + '<br /><a href="' + attributes['url'] + '" class="url">'
                + attributes['url'] + '</a>'
                + '</p>'
                + '</div>'
            );
        });
        if ($('#ts_results').html() == '') {
            $('#ts_results').html('<div style="padding-left: 12px; color: #fff;">No results</div>');
        }
    }, "json");
}

/*
* search countries 
* 
* @param region_id
*/

function region_search_country(region_id, label_country, label_forward, language)
{
	str_label = '<div class="tpe-input-radio-container_dbp"><label for="cf_selectcountry_dbp" >' + label_country + '</label>'; 
	str_button = '<div><input id="cf_btn_dbp" type="button" value="' + label_forward + '" class="tpe-button-small" tabindex="4" title="' + label_forward + '" onclick="region_goto_site($(\'#cf_selectcountry_dbp\').val());" /></div>';
    $.getJSON('http://www.kraiburg-tpe.com/admin/tpe/region/ajaxCountries/' + region_id + '/' + language,
        function(data){
            if (data.country) {
                str_options = '';
                $.each(data.country, function(i,item){
                    str_options+='<option value="' + item.url + '" >' + item.name + '</option>';
                });
                $('#distribution_country').html(str_label + '<select id="cf_selectcountry_dbp" >' + str_options + '</select></div>' + str_button);
            }
        }
    );
}

/*
 * goto site which is selected 
 * build URL and forward
 
 * @param url
 */
function region_goto_site(url)
{
	location.href = url;
}

/**
 * Paginate products
 * on PDB
 * */
function pageinate_products( page )
{
	// other pages
	$('.ncw-product-page').hide();
	$('.ncw-product-page-navigation-items').css('background', 'transparent');
	$('.ncw-product-page-navigation-items').css('color', '#4B4B4D');
	
	// active page
	$('#ncw-product-page-' + page ).show();
	$('#ncw-product-page-navigation-item-' + page ).css('background', '#696969');
	$('#ncw-product-page-navigation-item-' + page ).css('color', '#fff');
}


