/**
 *  jQuery Windows Engine Plugin
 *@requires jQuery v1.2.6
 *  http://www.socialembedded.com/labs
 *
 *  Copyright (c)  Hernan Amiune (hernan.amiune.com)
 *  Dual licensed under the MIT and GPL licenses:
 *  http://www.opensource.org/licenses/mit-license.php
 *  http://www.gnu.org/licenses/gpl.html
 * 
 *  Version: 0.6
 */
 

var jqWindowsEngineZIndex = 900003; 
(function($){ 

/**
 * @option string windowTitle, the tile to display on the window
 * @option HTML content, the content to display on the window
 * @option string ajaxURL, URL address to load the content, this has priority over content
 * @option int width, the initial width of the window
 * @option int height, the initial height of the window
 * @option int posx, the initial x position of the window
 * @option int posy, the initial y position of the window
 * @option function onDragBegin: onDragBegin callback function,
 * @option function onDragEnd: onDragEnd callback function,
 * @option function onResizeBegin: onResizeBegin callback function,
 * @option function onResizeEnd: onResizeEnd callback function,
 * @option function onAjaxContentLoaded: onAjaxContentLoaded callback function,
 * @option boolean statusBar, enable or disable the window status bar
 * @option boolean minimizeButton, enable or disable the window minimize button
 * @option HTML minimizeIcon, an html text to display as the minize icon
 * @option boolean maximizeButton, enable or disable the window maximize button
 * @option HTML maximizeIcon, an html text to display as the maximize icon
 * @option boolean closeButton, enable or disable the window close button
 * @option HTML closeIcon, an html text to display as the close icon
 * @option boolean draggable, enable or disable the window dragging
 * @option boolean resizeable, enable or disable the window resize button
 * @option HTML resizeIcon, an html text to display as the resize icon
 * @option string windowType, a string "normal", "video", or "iframe"
 *
 * @type jQuery
 *
 * @name jqWindowsEngine
 * @cat Plugins/Windows
 * @author Hernan Amiune (amiune@gmail.com)
 */ 
$.fn.newWindow = function(options){

    var lastMouseX = 0;
    var lastMouseY = 0;

    var defaults = {
        windowTitle : "",
		content: "",
		ajaxURL: "",
        width : 200,
        height : 200,
        posx : 50,
        posy : 50,
		onDragBegin: null,
		onDragEnd: null,
		onResizeBegin: null,
		onResizeEnd: null,
		onMaximizeBegin: null,
		onMaximizeEnd: null,
		onAjaxContentLoaded: null,
        statusBar: true,
		minimizeButton: true,
		minimizeIcon: "",
		maximizeButton: true,
		maximizeIcon: "",
		closeButton: true,
		closeIcon: "",
		draggable: true,
		resizeable: true,
		resizeIcon: "",
		windowType: "standard",
		positionCookie: 'ncw-window-position-cookie',
		resizeCookie: 'ncw-window-resize-cookie'
    };
  
    var options = $.extend(defaults, options);
    
    $windowContainer = $('<div class="ncw-window-container"></div>');
    $windowTitleBar = $('<div class="ncw-window-titleBar ncw-footer-background"></div>');        
    $windowMinimizeButton = $('<div class="ncw-window-minimizeButton"></div>');
	$windowMaximizeButton = $('<div class="ncw-window-maximizeButton"></div>');
	$windowCloseButton = $('<div class="ncw-window-closeButton"></div>');
	$windowContent = $('<div class="ncw-window-content ncw-section-background"></div>');
	$windowStatusBar = $('<div class="ncw-window-statusBar ncw-footer-background"></div>');
	$windowResizeIcon = $('<div class="ncw-window-resizeIcon"></div>');
	
	if(options.windowType=="video" || options.windowType=="iframe")
	  $windowContent.css("overflow","hidden");
	
	var setFocus = function($obj){
	    $obj.css("z-index",jqWindowsEngineZIndex++);
	}
	
	var resize = function($obj, width, height){
	    
		width = parseInt(width);
		height = parseInt(height);
		
		$obj.attr("lastWidth",width)
		    .attr("lastHeight",height);
		
		width = width+"px";
		height = height+"px";
		
		$obj.css("width", width)
	        .css("height", height);
		
		if(options.windowType=="video"){
		  $obj.children(".ncw-window-content").children("embed").css("width", width)
	               .css("height", height);
		  $obj.children(".ncw-window-content").children("object").css("width", width)
	               .css("height", height);
		  $obj.children(".ncw-window-content").children().children("embed").css("width", width)
	               .css("height", height);
		  $obj.children(".ncw-window-content").children().children("object").css("width", width)
	               .css("height", height);
		}
		
        if(options.windowType=="iframe")		
	      $obj.children(".ncw-window-content").children("iframe").css("width", width)
	               .css("height", height);
				   
	}
	
	var max_height = $(document).height();
	var max_width = $(document).width();
	
	$(window).resize(
        function () {
            max_height = $(document).height();
            max_width = $(document).width(); 
        }
	);
	
	var move = function($obj, x, y){
	    
	    if (x < 0) {
	        x = 0;
	    }
        if (x + $obj.width() > max_width) {
            x = max_width - $obj.width();
        }	    
	    
	    if (y < 0) {
	        y = 0;
	    }
        if (y + $obj.height() > max_height) {
            y = max_height - $obj.height();
        }	    
	    
		x = parseInt(x);
		y = parseInt(y);
		
		$obj.attr("lastX",x)
		    .attr("lastY",y);
		
        x = x+"px";
		y = y+"px";		
			
		$obj.css("left", x)
	        .css("top", y);
	}

	var dragging = function(e, $obj){
	    if(options.draggable){
		e = e ? e : window.event;
	    var newx = parseInt($obj.css("left")) + (e.clientX - lastMouseX);
        var newy = parseInt($obj.css("top")) + (e.clientY - lastMouseY);
	    lastMouseX = e.clientX;
	    lastMouseY = e.clientY;
	  
	    move($obj,newx,newy);
		}
	};
	
	var resizing = function(e, $obj){
	  
	  e = e ? e : window.event;
	  var w = parseInt($obj.css("width"));
	  var h = parseInt($obj.css("height"));
	  w = w<100 ? 100 : w;
	  h = h<50 ? 50 : h;
	  var neww = w + (e.clientX - lastMouseX);
      var newh = h + (e.clientY - lastMouseY);
	  lastMouseX = e.clientX;
	  lastMouseY = e.clientY;
	  
	  resize($obj, neww, newh);
	};
	
	$windowTitleBar.bind('mousedown', function(e){
	    $obj = $(e.target).parent();
		setFocus($obj);
		
	    if($obj.attr("state") == "normal"){
	        e = e ? e : window.event;
		    lastMouseX = e.clientX;
		    lastMouseY = e.clientY;
		    
		    $(document).bind('mousemove', function(e){
			    dragging(e, $obj);
		    });
		    
			
		    $(document).bind('mouseup', function(e){
				if(options.onDragEnd != null)options.onDragEnd();
				$(document).unbind('mousemove');
				$(document).unbind('mouseup');

				$.cookie(
				    options.positionCookie, 
				    $obj.css("top") + ';' + $obj.css("left")
				);
				
		    });
			
			if(options.onDragBegin != null)options.onDragBegin();
	    }
    });
	
	$windowResizeIcon.bind('mousedown', function(e){
		$obj = $(e.target).parent().parent();
		setFocus($obj);
		
		if($obj.attr("state") == "normal"){
			e = e ? e : window.event;
			lastMouseX = e.clientX;
			lastMouseY = e.clientY;

			$(document).bind('mousemove', function(e){
				resizing(e, $obj);
			});

			$(document).bind('mouseup', function(e){
				if(options.onResizeEnd != null)options.onResizeEnd($obj);
				$(document).unbind('mousemove');
				$(document).unbind('mouseup');
				
                $.cookie(
                    options.resizeCookie, 
                    $obj.css("height") + ';' + $obj.css("width")
                );				
			});
			
			if(options.onResizeBegin != null)options.onResizeBegin($obj);
		}
	  
    });
	
	$windowMinimizeButton.bind('click', function(e){
	    $obj = $(e.target).parent().parent();
		setFocus($obj);
		$(e.target).parent().next().slideToggle("slow");
	});
	
	$windowMaximizeButton.bind('click', function(e){
	  $obj = $(e.target).parent().parent();
	  setFocus($obj);
	  if(options.onMaximizeBegin != null)options.onMaximizeBegin($obj);	  
	  if($obj.attr("state") == "normal"){
		  if(options.windowType=="standard"){
		    $obj.animate({
		      top: "5px",
			  left: "5px",
			  width: $(window).width()-15,
			  height: $(window).height()-75
		    },"slow", null, function () {
		    	if(options.onMaximizeEnd != null)options.onMaximizeEnd($obj);	
		    });
		  }
		  else{
			tmpx = $obj.attr("lastX");
		    tmpy = $obj.attr("lastY");
			tmpwidth = $obj.attr("lastWidth");
		    tmpheight = $obj.attr("lastHeight");
			move($obj, 5, 5);
		    resize($obj,$(window).width()-15,$(window).height()-45);
			$obj.attr("lastX",tmpx);
		    $obj.attr("lastY",tmpy);
			$obj.attr("lastWidth",tmpwidth);
		    $obj.attr("lastHeight",tmpheight);
		  }
		  $obj.attr("state","maximized");
	  }
	  else if($obj.attr("state") == "maximized"){
	    if(options.windowType=="standard"){ 
		  $obj.animate({
		    top: $obj.attr("lastY"),
			left: $obj.attr("lastX"),
			width: $obj.attr("lastWidth"),
			height: $obj.attr("lastHeight")
		  },"slow", null, function () {
		    	if(options.onMaximizeEnd != null)options.onMaximizeEnd($obj);	
		  });
		}
		else{
		  resize($obj,$obj.attr("lastWidth"),$obj.attr("lastHeight"));
		  move($obj,$obj.attr("lastX"),$obj.attr("lastY"));
		}
	    $obj.attr("state","normal");
	  }
    });
	
	$windowCloseButton.bind('click', function(e){
	  $(e.target).parent().parent().fadeOut();
	  $(e.target).parent().parent().children(".ncw-window-content").html("");
    });
	
	$windowContent.click(function(e){
      setFocus($(e.target).parent());
    });
	$windowStatusBar.click(function(e){
      setFocus($(e.target).parent());
    });
		
    if ($.cookie(options.positionCookie)) {
        var pos = $.cookie(options.positionCookie);
        pos = pos.split(';');   
        options.posx = pos[1];
        if (parseInt(options.posx) < 0) {
            options.posx = 0;
        }
        options.posy = pos[0];
        if (parseInt(options.posy) < 0) {
            options.posy = 0;
        }        
    }
	move($windowContainer,options.posx,options.posy);
	
    if ($.cookie(options.resizeCookie)) {
        var pos = $.cookie(options.resizeCookie);
        pos = pos.split(';');   
        options.width = pos[1];
        options.height = pos[0];
    }	
	resize($windowContainer,options.width,options.height);
	
	$windowContainer.attr("state","normal");
	$windowTitleBar.append(options.windowTitle);
	
	if(options.minimizeButton)
	    $windowTitleBar.append($windowMinimizeButton)
	if(options.maximizeButton)
	    $windowTitleBar.append($windowMaximizeButton)
	if(options.closeButton)  
	    $windowTitleBar.append($windowCloseButton);
	
	if(options.resizeable)
	    $windowStatusBar.append($windowResizeIcon);
	
	$windowContainer.append($windowTitleBar)
	$windowContainer.append($windowContent)
	
	if(options.statusBar)
	    $windowContainer.append($windowStatusBar);
	
	$windowContainer.css("display","none");
	
	return this.each(function(index) {
		var $this = $(this);      	
		
		$windowMinimizeButton.html(options.minimizeIcon);
		$windowMaximizeButton.html(options.maximizeIcon);
		$windowCloseButton.html(options.closeIcon);
		$windowResizeIcon.html(options.resizeIcon);
		
		$this.data("window",$windowContainer);
		$('body').append($windowContainer);
		
		$this.click(function(event){
			event.preventDefault();   
			$window = $this.data("window");
			if(options.ajaxURL != ""){ 

			     if(options.onBeforeLoad != null) options.onBeforeLoad($window);
			    
			     var tsTimeStamp= new Date().getTime();
			     
				 $.ajax({
				   type: "GET",
				   url: options.ajaxURL + '?_='+tsTimeStamp,
				   dataType: "html",
				   //data: "header=variable",
				   success: function(data){
					 $window.children(".ncw-window-content").html(data);
					 if(options.onAjaxContentLoaded != null) options.onAjaxContentLoaded($window); 
				   }
				 });
		    
			}
			else $window.children(".ncw-window-content").html(options.content);
			if(!options.draggable)
			    $window.children(".ncw-window-titleBar").css("cursor","default");
			setFocus($window);
            $window.fadeIn();			
		});
	});

  
}})(jQuery);
