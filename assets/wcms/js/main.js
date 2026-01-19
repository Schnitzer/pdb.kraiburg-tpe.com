'use strict';
(function($) {
  $(document).ready(function() {
	  
	  $( ".automotive .portfolio_contentOne" ).click(function() {
		  $( ".automotive .portfolio_contentOne" ).css("opacity", "0.5");
		  $( ".automotive .portfolio_contentTwo" ).css("opacity", "1");
		  $( ".automotive .portfolio_contentThree" ).css("opacity", "1");
		  
		  
	  $( ".automotive .stepOne" ).addClass("active");
		  $( ".automotive .stepTwo" ).removeClass("active");
		  $( ".automotive .stepThree" ).removeClass("active");
		$( ".automotive .stepOne .inner-container" ).addClass("right-animation");
		  $( ".automotive .stepTwo .inner-container" ).removeClass("right-animation");
		  $( ".automotive .stepThree .inner-container" ).removeClass("right-animation");
		  
		  
		  
		  
		  $( ".automotive .right-animation" ).animate({
    marginLeft: "50%",
			  borderRightWidth: "30px",
   
   
  }, 300, function() {
    // Animation complete.
  });
		  
		   $( ".automotive .stepTwo .inner-container" ).css("margin-left", "50%");
		   $( ".automotive .stepThree .inner-container" ).css("margin-left", "50%");
		  
		  
		  $( ".automotive .stepZero" ).addClass("inactive");
		 
		  
	 });
	  
	  
	  
	  
	  
	  
	  	  $( ".automotive .portfolio_contentTwo" ).click(function() {
		  $( ".automotive .portfolio_contentTwo" ).css("opacity", "0.5");
		  $( ".automotive .portfolio_contentOne" ).css("opacity", "1");
		  $( ".automotive .portfolio_contentThree" ).css("opacity", "1");
		  
		  
	  $( ".automotive .stepTwo" ).addClass("active");
		  $( ".automotive .stepOne" ).removeClass("active");
		  $( ".automotive .stepThree" ).removeClass("active");
		$( ".automotive .stepTwo .inner-container" ).addClass("right-animation");
		  $( ".automotive .stepOne .inner-container" ).removeClass("right-animation");
		  $( ".automotive .stepThree .inner-container" ).removeClass("right-animation");
		  
		  
		  
		  
		  $( ".automotive .right-animation" ).animate({
    marginLeft: "50%",
			  borderRightWidth: "30px",
   
   
  }, 300, function() {
    // Animation complete.
  });
		  
			  $( ".automotive .stepOne .inner-container" ).css("margin-left", "50%");
		   $( ".automotive .stepThree .inner-container" ).css("margin-left", "50%");
		  
		  $( ".automotive .stepZero" ).addClass("inactive");
		 
		  
	 });
	  
	  
	  
	  
	   $( ".automotive .portfolio_contentThree" ).click(function() {
		  $( ".automotive .portfolio_contentThree" ).css("opacity", "0.5");
		  $( ".automotive .portfolio_contentOne" ).css("opacity", "1");
		  $( ".automotive .portfolio_contentTwo" ).css("opacity", "1");
		  
		  
	  $( ".automotive .stepThree" ).addClass("active");
		  $( ".automotive .stepOne" ).removeClass("active");
		  $( ".automotive .stepTwo" ).removeClass("active");
		$( ".automotive .stepThree .inner-container" ).addClass("right-animation");
		  $( ".automotive .stepOne .inner-container" ).removeClass("right-animation");
		  $( ".automotive .stepTwo .inner-container" ).removeClass("right-animation");
		  
		  
		  
		  
		  $( ".automotive .right-animation" ).animate({
    marginLeft: "50%",
			  borderRightWidth: "30px",
   
   
  }, 300, function() {
    // Animation complete.
  });
		   
		   $( ".automotive .stepOne .inner-container" ).css("margin-left", "50%");
		   $( ".automotive .stepTwo .inner-container" ).css("margin-left", "50%");
		  
		  
		  $( ".automotive .stepZero" ).addClass("inactive");
		 
		  
	 });
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  $( ".automotive .close-portfolio" ).click(function() {
		   $( ".automotive .portfolio_contentOne" ).css("opacity", "1");
		  $( ".automotive .portfolio_contentTwo" ).css("opacity", "1");
		   $( ".automotive .portfolio_contentThree" ).css("opacity", "1");
		  $( ".automotive .stepOne" ).removeClass("active");
	  $( ".automotive .stepTwo" ).removeClass("active");
		  $( ".automotive .stepThree" ).removeClass("active");
		  
		  
		  $( ".automotive .stepZero .inner-container" ).css("margin-left", "50%");
		  
		  
		  $( ".automotive .stepZero .inner-container" ).animate({
    marginLeft: "0%",
			  
   
   
  }, 300, function() {
    // Animation complete.
  });
		
		  $( ".automotive .stepZero" ).removeClass("inactive");
		  
		  $( ".automotive .right-animation" ).animate({
    marginLeft: "0%",
			  borderRightWidth: "0px",
   
   
  }, 300, function() {
    // Animation complete.
  });
		  
		  $( ".automotive .stepOne .inner-container" ).css("margin-left", "0%");
		   $( ".automotive .stepTwo .inner-container" ).css("margin-left", "0%");
		    $( ".automotive .stepThree .inner-container" ).css("margin-left", "0%");
		  
		    $( ".stepOne .inner-container" ).removeClass("right-animation");
	  $( ".stepTwo .inner-container" ).removeClass("right-animation");
		  $( ".stepThree .inner-container" ).removeClass("right-animation");
	  });
	  
	  
	  
	  $( ".fort-tec-button" ).click(function() {
 $( ".fort-tec" ).addClass("active");
		  $( ".fort-tec-button" ).css ("color", "rgba(255,255,255,0.50)");
		  $( ".thermoplast-button" ).css ("color", "rgba(255,255,255,1.00)");
		  $( ".copec-button" ).css ("color", "rgba(255,255,255,1.00)");
		  $( ".hipex-button" ).css ("color", "rgba(255,255,255,1.00)");
		  $( ".thermoplast" ).removeClass("active");
		  $( ".copec" ).removeClass("active");
		  $( ".hipex" ).removeClass("active");
});
	  
	  	  $( ".thermoplast-button" ).click(function() {
 		$( ".thermoplast" ).addClass("active");
			  $( ".thermoplast-button" ).css ("color", "rgba(255,255,255,0.50)");
			  $( ".fort-tec" ).css ("color", "rgba(255,255,255,1.00)");
		  $( ".copec-button" ).css ("color", "rgba(255,255,255,1.00)");
		  $( ".hipex-button" ).css ("color", "rgba(255,255,255,1.00)");
		  $( ".fort-tec" ).removeClass("active");
		  $( ".copec" ).removeClass("active");
		  $( ".hipex" ).removeClass("active");
});
	  
	  $( ".copec-button" ).click(function() {
 $( ".copec" ).addClass("active");
		   $( ".copec-button" ).css ("color", "rgba(255,255,255,0.50)");
			  $( ".fort-tec" ).css ("color", "rgba(255,255,255,1.00)");
		  $( ".thermoplast-button" ).css ("color", "rgba(255,255,255,1.00)");
		  $( ".hipex-button" ).css ("color", "rgba(255,255,255,1.00)");
		  $( ".copec-button" ).css ("color", "rgba(255,255,255,0.50)");
		  $( ".fort-tec" ).removeClass("active");
		  $( ".thermoplast" ).removeClass("active");
		  $( ".hipex" ).removeClass("active");
});
	  
	  $( ".hipex-button" ).click(function() {
 $( ".hipex" ).addClass("active");
		   $( ".hipexbutton" ).css ("color", "rgba(255,255,255,0.50)");
			  $( ".fort-tec" ).css ("color", "rgba(255,255,255,1.00)");
		  $( ".copec-button" ).css ("color", "rgba(255,255,255,1.00)");
		  $( ".thermoplast-button" ).css ("color", "rgba(255,255,255,1.00)");
		  $( ".hipex-button" ).css ("color", "rgba(255,255,255,0.50)");
		  $( ".fort-tec" ).removeClass("active");
		  $( ".thermoplast" ).removeClass("active");
		  $( ".copec" ).removeClass("active");
});
	  
	  
	  
	 
	  

    /* Microsoft Smooth Scroll Disabler */
    var url;
    if (navigator.userAgent.match(/Trident\/7\./)) {
      $('body').on('mousewheel', function() {
        event.preventDefault();
        window.scrollTo(0, window.pageYOffset - event.wheelDelta / 1.5);
      });
    }

    /* Browsers */
    if ($.browser.mozilla) {
      el_html.addClass('browser-mozilla');
    }
    if ($.browser.msie) {
      el_html.addClass('browser-msie');
    }
    if ($.browser.webkit) {
      el_html.addClass('browser-webkit');
    }
    if ($.browser.safari) {
      el_html.addClass('browser-safari');
    }

    /* Text Color */
    set_text_color();

    /* Font Size */
    set_font_size();

    /* Background Color */
    set_background();

    /* Height */
    set_height();

    /* Page Scroll */
    $('[data-scroll]').on('click', function(e) {
      var scroll;
      e.preventDefault();
      scroll = $(this).attr('data-scroll');
      if (scroll === 'up') {
        $('html, body').animate({
          scrollTop: 0
        }, 900, 'swing');
      } else if (scroll.charAt(0) === '#') {
        if (device.mobile()) {
          $('html, body').animate({
            scrollTop: $(scroll).offset().top - 50
          }, 900, 'swing');
        } else {
          $('html, body').animate({
            scrollTop: $(scroll).offset().top - 80
          }, 900, 'swing');
        }
      }
      if($(this).parent().hasClass('nav-item') && device.mobile()){
        $('.navbar-beacon').removeClass('bounceInRight bounceInRight-duration').addClass('bounceOutRight bounceInRight-duration');
      }
      return false;
    });

    // Equal height

    $.fn.equalheight = function(){
      var $this = $(this),
          $array = [],
          options = {
            height: false,
            minHeight: true
          };
      $this.each(function(){
        var $outerHeight = $(this).outerHeight();
        $array.push($outerHeight);
      });
      var maxValue = Math.max.apply(Math,$array);

      if (options['height'] === true){
        $this.css('height', maxValue + "px");
      }
      if (options['minHeight'] === true){
        $this.css('min-height', maxValue + "px");
      }

    };

    $('.ct-brandBox').equalheight();

    /* Skrollr Parallax */
    $('[data-parallax]').each(function() {
      var $this, attr;
      $this = $(this);
      attr = $this.attr('data-parallax');
      $this.attr('data-top-bottom', 'background-position: 50% -' + attr + 'px');
      $this.attr('data-bottom-top', 'background-position: 50% ' + attr + 'px');
      $this.attr('data-center', 'background-position: 50% 0px');
    });

    /*Scrollspy*/
    $('body').scrollspy({
      target: '#navigation-vertical',
      offset: 100
     })

    /* Selectize */
    if ($().selectize) {
      $('select').each(function() {
        $(this).selectize({
          create: true,
          sortField: 'text'
        });
      });
    }

    // Background position

    if ($('[data-background-position]').length > 0) {
      $('[data-background-position]').each(function() {
        var backgroundPosition, backgroundmobilePosition, that;
        that = $(this);
        backgroundPosition = $(this).attr('data-background-position');
        backgroundmobilePosition = $(this).attr('data-background-mobile-position');

         if (that.attr('data-background-mobile-position') && device.mobile()) {
          that.css('background-position', backgroundmobilePosition);
        } else {
          that.css('background-position', backgroundPosition);
        }
      });
    }

    // Background size

    if ($('[data-background-size]').length > 0) {
      $('[data-background-size]').each(function() {
        var backgroundPosition, backgroundmobilePosition, that;
        that = $(this);
        backgroundPosition = $(this).attr('data-background-size');
        backgroundmobilePosition = $(this).attr('data-background-mobile-size');
        if (that.attr('data-background-mobile-size') && device.mobile()) {
          that.css('background-size', backgroundmobilePosition);
        } else {
          that.css('background-size', backgroundPosition);
        }
      });
    }

    /* Navbar Active Class */
    url = window.location;
    $('.navbar-default .navbar-nav').find('a').filter(function() {
      return this.href === url.href;
    }).closest('li').addClass('active').closest("ul").parent().addClass('active');

    // Img default alt attribute
    $("img").each(function(){
      var $this = $(this);
      if ($this.attr("alt") === "" || this.hasAttribute("alt") === false){
        var $src = $this.attr('src'),
            $srcSplit = $src.split('/'),
            $srcFile = $srcSplit[$srcSplit.length-1],
            $data = $srcFile.split('.')[0],
            $dataFinal = $data.charAt(0).toUpperCase();
        $data=$data.replace(/(^\s+|[^a-zA-Z0-9 ]+|\s+$)/g," ");
        $this.attr('alt', $dataFinal + $data.substring(1));
      }
    });

    /* Menus variants */

      // Beacon
      var navbar_beacon = $('.navbar-beacon');
      if ($('.navbar').hasClass('navbar-dark')) {
        el_body.addClass('ct-menu-effect');
        $('.nav-item-toggle a i').on('click', function(e) {
          if(el_body.hasClass('cart-is-open')){
            el_body.removeClass('cart-is-open');
          }
          el_body.toggleClass('ct-menu-effect-activated');
          if (!(navbar_beacon.hasClass('bounceInRight bounceInRight-duration'))) {
            navbar_beacon.removeClass('bounceOutRight bounceInRight-duration').addClass('bounceInRight bounceInRight-duration');
          } else {
            navbar_beacon.removeClass('bounceInRight bounceInRight-duration').addClass('bounceOutRight bounceInRight-duration');
          }
          e.preventDefault();
        });
      }

      el_wrapper.on('click', function(){
        if(navbar_beacon.hasClass('bounceInRight bounceInRight-duration')){
          $('.ct-menu-mobile + .navbar-beacon').removeClass('bounceInRight bounceInRight-duration').addClass('bounceOutRight bounceInRight-duration');
        }
        if(!el_body.hasClass('navbar-inside')){
          if(el_body.hasClass('cart-is-open')){
            el_body.removeClass('cart-is-open');
          }
        }
      })

    if ($('.navbar-beacon').length > 0) {
      $('.navbar-beacon ul li.dropdown > a').on('click', function(e) {
        var $this = $(this);
        $this.parent().find('.dropdown-menu').slideToggle();
        $this.parent().toggleClass('is-active');
        e.preventDefault();
      });
    }

    if ($('.ct-productCart-link').length > 0) {
      $('.ct-productCart-link').on('click', function(){
        el_body.toggleClass('cart-is-open');
        if(navbar_beacon.hasClass('bounceInRight bounceInRight-duration')){
          $('.ct-menu-mobile + .navbar-beacon').removeClass('bounceInRight bounceInRight-duration').addClass('bounceOutRight bounceInRight-duration');
        }
      })
    }



    // Cart

    el_body.ctshop({
      cart: 'ct-cart__product',
      after_add_to_cart: function(){
        $('.ct-cart').each(function(){
          var item_value = $('#required-value').val(),
              cart = $(this),
              message = cart.find('.ct-cart__message');
          if(item_value == 0){
            item_value = 1;
          }
          message.addClass('ct-cart__message-added');
          setTimeout(function(){
            message.removeClass('ct-cart__message-added');
          }, 1000)
          $(this).find('li').last().find('.ct-cart__product-input').attr('value', item_value).trigger('focus').trigger('focusout');
        })
      }
    });

    

    // Search link

    if (($('.ct-lang-link')).length > 0) {
      $('.ct-lang-link').on('click', function(e) {
        e.preventDefault();
        $('.ct-langForm').addClass('is-open');
      });
      $('.ct-langForm-close').on('click', function(e) {
        $('.ct-langForm').removeClass('is-open');
        e.preventDefault();
      });
    }
	  
	  
	   if (($('.ct-search-link')).length > 0) {
      $('.ct-search-link').on('click', function(e) {
        e.preventDefault();
        $('.ct-searchForm').addClass('is-open');
      });
      $('.ct-searchForm-close').on('click', function(e) {
        $('.ct-searchForm').removeClass('is-open');
        e.preventDefault();
      });
    }
	  
	  
	  


    // Product preview

    if ($('.ct-productPreview').length > 0 ) {

      if ($('[data-width]').length > 0) {
        $('[data-width]').each(function() {
          var that = $(this),
              dwidth = that.attr('data-width');
          that.find('.ct-iconBox-decorativeLine').css('width', dwidth);
        });
      }

      if ($('[data-left-position]').length > 0) {
        $('[data-left-position]').each(function() {
          var that = $(this),
              dleft = that.attr('data-left-position');
          that.find('.ct-iconBox-decorativeLine').css('left', dleft);

        });
      }
      if ($('[data-right-position]').length > 0) {
        $('[data-right-position]').each(function() {
          var that = $(this),
              dright = that.attr('data-right-position');
          that.find('.ct-iconBox-decorativeLine').css('right', dright);

        });
      }

    }


    // Contact form [add to Cart] // -----------------------------------------------------------------------------------

    if ($('.ct-addToCart').length > 0) {
      var $addToCart = $('.ct-addToCart');

      $addToCart.each(function() {
        var $that = $(this);

        $that.on('keydown', function(e) {
          if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 || (e.keyCode == 65 && ( e.ctrlKey === true || e.metaKey === true ) ) || (e.keyCode >= 35 && e.keyCode <= 40)) {
            return;
          }
          if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
          }
        });
        var $i = 0;
        $that.find('.ct-input-increment').on('click', function() {
          var $j = $i + 1;
          $(this).each(function() {
            $that.find('.input-required-value').val($j);
          });
          $i++;
          $('#required-value').val($i);
        });
        $that.find('.ct-input-decrement').on('click', function() {
          if($i > 0){
            var $j = $i - 1;
            $(this).each(function() {
              $that.find('.input-required-value').val($j--);
            });
            $i--;
            $('#required-value').val($i);
          }
        });

      });

    }




    // Tooltips

    $('[data-toggle="tooltip"]').tooltip();

    // Testimonial slider

    if ($('.ct-testimonials-slider').length > 0) {
      var adaptive_height_mobile = false;
      if(device_width < 767){
        adaptive_height_mobile = true;
      }
      $('.ct-testimonials-slider').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 5000,
        arrows: false,
        dots: true,
        fade: true,
        adaptiveHeight: adaptive_height_mobile,
        asNavFor: '.ct-testimonials-slider-nav-thumbnails'
      });

      $('.ct-testimonials-slider-nav-thumbnails').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        asNavFor: '.ct-testimonials-slider',
        dots: false,
        arrows: false,
        //	centerMode: true,
        focusOnSelect: true,
        responsive: [
          {
            breakpoint: 1199,
            settings: {
              slidesToShow: 3
            }
          },
          {
            breakpoint: 767,
            settings: {
              slidesToShow: 1
            }
          }
        ]
      });

      //remove active class from all thumbnail slides
      $('.ct-testimonials-slider-nav-thumbnails .slick-slide').removeClass('slick-active');

      //set active class to first thumbnail slides
      $('.ct-testimonials-slider-nav-thumbnails .slick-slide').eq(0).addClass('slick-active');

      // On before slide change match active thumbnail to current slide
      $('.ct-testimonials-slider').on('beforeChange', function (event, slick, currentSlide, nextSlide) {
        var mySlideNumber = nextSlide;
        $('.ct-testimonials-slider-nav-thumbnails .slick-slide').removeClass('slick-active');
        $('.ct-testimonials-slider-nav-thumbnails .slick-slide').eq(mySlideNumber).addClass('slick-active');
      });
    }


    // Media box

    $('.ct-embed').each(function(){
      var $this = $(this);
      $this.find('.ct-playButton').on('click', function(e){
        $(this).addClass('hide');
        var iframeURL = $('iframe').attr('src');

        iframeURL += "&autoplay=1";

        $('iframe').attr('src', iframeURL);

        $this.find('img.ct-mediaBox-poster, .ct-mediaBox-poster').fadeOut();
        e.preventDefault();
      });
    });

    // IconBox stepper

    if ($('.ct-iconBox--stepped').length > 0) {
      if (device_width > 992){
        $('.ct-iconBox--stepped').each(function() {
          var that = $(this),
              that_position = that.attr("data-padding-position");
          that.addClass(that_position);
          that.find("[data-padding]").each(function(){
            var element = $(this),
                element_padding = element.attr("data-padding");

            if(that_position !== "right"){
              that_position = "left";
            }
            element.css("padding-"+that_position, element_padding+"px");


          });
          // var $iStepped = $('.ct-iconBox--stepped'),
          //     $incrementBase = 0,
          //     $this = $(this),
          //     step = parseInt(validatedata($(this).attr("data-step"), 50), 10),
          //     stepMD = parseInt(validatedata($(this).attr("data-step-md"), 50), 10);
          //
          // $this.find('.ct-iconBox').not(':first-child').each(function() {
          //   var $this = $(this);
          //   if (device_width > 1199) {
          //     $this.css('padding-left', $incrementBase+=step);
          //     $iStepped.find('.ct-iconBox').eq(1).css('padding-left', step + 'px');
          //   } else if (device_width > 991 && device_width < 1200) {
          //     $this.css('padding-left', $incrementBase+=stepMD);
          //     $iStepped.find('.ct-iconBox').eq(1).css('padding-left', stepMD + 'px')
          //   }
          //
          // })
        });
      }
    }

    // Contact Form

    if ($('.ct-contactForm').length > 0 || $('.ct-newsletter').length > 0) {
      $('.ct-contactForm, .ct-newsletter').each(function() {
        var that = $(this);

        that.find('input, textarea').each(function() {
          var that = $(this);

          that.on('keyup', function() {
            if (that.val() != 0) {
              that.addClass('is-not-empty');
            } else {
              that.removeClass('is-not-empty');
            }
          })
        });
      });
    }

  });
  $(window).on('scroll', function() {
    var scroll, pixes;
    scroll = $(window).scrollTop();



    /* Navbar Class */
    if (scroll > 80) {
      el_body.addClass('navbar-scrolled');
      $('.navbar--animated').addClass('animated-init navbar-fixed')
    } else {
      el_body.removeClass('navbar-scrolled');
      $('.navbar--animated').removeClass('animated-init navbar-fixed')
    }


    // Css animate

    if (device.mobile() || device.tablet() || device_width < 767) {
      $("body").removeClass("cssAnimate");
    } else {
      $('.cssAnimate .animated').each(function(){
        var that = $(this);
        if (that.data('time') != undefined){
          var delay = that.attr('data-time');
          if(that.visible(true)){
            setTimeout(function(){
              that.addClass('activate');
              that.addClass(that.data('fx'));
            }, delay)
          }
        }
        else{
          if(that.visible(true)){
            that.addClass('activate');
            that.addClass(that.data('fx'));
          }
        }
      });
    }

  });
  $(window).on('load', function() {

    $('.ct-preloader').fadeOut('slow');

    /* Skrollr */
    var skroll;
    if (!device.mobile() && !device.tablet() && !el_html.hasClass('ie8')) {
      skroll = skrollr.init({
        forceHeight: false
      });
    }

    // Css animate

    if (device.mobile() || device.tablet() || device_width < 767) {
      $("body").removeClass("cssAnimate");
    } else {
      $('.cssAnimate .animated').each(function(){
        var that = $(this);
        if (that.data('time') != undefined){
          var delay = that.attr('data-time');
          if(that.visible(true)){
            setTimeout(function(){
              that.addClass('activate');
              that.addClass(that.data('fx'));
            }, delay)
          }
        }
        else{
          if(that.visible(true)){
            that.addClass('activate');
            that.addClass(that.data('fx'));
          }
        }
      });
    }

  });
	

	
})(jQuery);
