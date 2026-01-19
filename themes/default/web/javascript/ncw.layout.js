ncw.layout = {};

/**
 * Constructor
 */
ncw.layout = function () {
	ncw.onLoad(ncw.layout.menus);
	ncw.onLoad(ncw.layout.initView);
};

/**
 * Load content
 */
ncw.layout.loadContent = function (url) {
	$('#ncw-section').load(
		url,
		null,
		function () {
			ncw.layout.initView();
		}
	);
};

/**
 * Load a view
 * @param url
 * @param callback
 */
ncw.layout.loadView = function (url, callback) {
    $('.ncw-right').html('').append('<img class="ncw-throbber-big" src="' + ncw.BASE + '/' + ncw.THEME_PATH + '/web/images/throbber_big.gif" />')
        .load(
        url,
        function () {          
            $('.ncw-throbber-big').remove();
            ncw.layout.initView();
            if (typeof callback == 'function') {
                callback();
            }
        }
    ); 
};

/**
 * Initialize a view
 */
ncw.layout.initView = function () {
	ncw.layout.resize.section();	

	ncw.layout.left.accordion();
	ncw.layout.left.sideMenu();
	ncw.layout.right.mainTabs();
	ncw.layout.right.subTabs();
	ncw.layout.right.sub2Tabs();
	ncw.layout.input.tooltips();	
	ncw.layout.table();

	ncw.layout.saveTrigger();
	ncw.layout.saveTriggerAjax();
	ncw.layout.deleteTrigger();
	ncw.layout.deleteTriggerAjax();
	ncw.layout.addNewTrigger();

	if (typeof(ncw.layout.initView.callback) == 'function') {
	    ncw.layout.initView.callback();
	}

	if (true == ncw.layout.initView.removeOverlay) {
	    ncw.layout.removeLoadingOverlay();
	}
	
	ncw.onResize(ncw.layout.resize.section);
}

ncw.layout.initView.removeOverlay = true;
ncw.layout.initView.callback = null;

/**
 * Removes the loading overlay
 */
ncw.layout.removeLoadingOverlay = function () {
    $('#ncw-loading-overlay, #ncw-loading-throbber').remove();
};

ncw.layout.resize = {};

/**
 * Resize layout
 */
ncw.layout.resize.section = function () {
    
    if (typeof ncw.layout.resize.section.override == 'function') {
        ncw.layout.resize.section.override();
    } else {
    	var height = $(ncw.layout.resize.section.window).height();
    	var width = $(ncw.layout.resize.section.window).width();
    	
    	// resize section
    	var section_height = height - ncw.layout.resize.cutoff.section.height;
    	var section_width = width - ncw.layout.resize.cutoff.section.width;
    	$('#ncw-section').height(section_height).width(section_width);
    	
    	$('.ncw-left, .ncw-right').height(section_height);
    	
    	// resize right section
    	var right_width = width;
    	if ($('.ncw-left').length > 0) {
    	    right_width -= ncw.layout.resize.cutoff.right.width_full;
    	} else {
    	    right_width -= ncw.layout.resize.cutoff.right.width;
    	}
    	$('.ncw-right').width(right_width);
    	
    	// resize main tab content
    	var main_tab_content_height = section_height - ncw.layout.right.mainTabs.content.cutoff;
    	if ($('.ncw-status-action-bar').length > 0) {
    	    main_tab_content_height -= ncw.layout.right.mainTabs.content.cutoff2;
    	}
    	$('.ncw-main-tab-content').height(main_tab_content_height);	
    	
    	// resize main tab content
    	ncw.layout.resize.section.tabsFull(section_height);
    	
    	ncw.layout.resize.section.formElements(right_width);
    	
    	// resize accordion
    	ncw.layout.left.accordion.resize();
    }
};

ncw.layout.resize.section.tabsFull = function (section_height) {
	if (typeof(section_height) == 'undefined') {
		var height = $(ncw.layout.resize.section.window).height();
		var section_height = height - ncw.layout.resize.cutoff.section.height;
	}	
	
	var sub_tab_content_full_height = section_height - ncw.layout.right.subTabs.content.full.cutoff;	
    $('.ncw-tab-content-full').height(sub_tab_content_full_height);	
};

/**
 * Resizes the form elements
 * @param right_width
 */
ncw.layout.resize.section.formElements = function (right_width) {
    // resize input container
    var input_width = right_width - ncw.layout.input.container.cutoff;  
    $('.ncw-input-container').width(input_width);
    
    // resize input fields and textareas
    var input_width = right_width - ncw.layout.input.cutoff;    
    $('.ncw-input, .ncw-textarea').width(input_width);  
    
    var select_width = right_width - ncw.layout.select.cutoff; 
    $('.ncw-select').width(select_width);  
    
    // resize wysiwyg editors
    $('.ncw-wysiwyg').width('100%').height('450px');
    $('.ncw-wysiwyg-small').width('100%').height('50px');
};

/**
 * the window
 */
ncw.layout.resize.section.window = window;

/**
 * to override the section resize funktion
 */
ncw.layout.resize.section.override = null;

ncw.layout.resize.cutoff = {};
ncw.layout.resize.cutoff.section = {};
ncw.layout.resize.cutoff.section.height = 40;
ncw.layout.resize.cutoff.section.width = 10;
ncw.layout.resize.cutoff.right = {};
ncw.layout.resize.cutoff.right.width_full = 220;
ncw.layout.resize.cutoff.right.width = 10;

ncw.layout.select = {};
ncw.layout.select.cutoff = 310;

/**
 * Resize menu
 */
ncw.layout.resize.footer = function () {
	var width = $(window).width();
	// resize footer
	var footer_width = width - ncw.layout.resize.cutoff.footer.width;
	$('#ncw-footer').width(footer_width);
};

ncw.layout.resize.cutoff.footer = {};
ncw.layout.resize.cutoff.footer.width = 0;


ncw.layout.left = {};

/**
 * Left Accordion
 */
ncw.layout.left.accordion = function () {
	if ($('.ncw-accordion').length > 0) {
		$('.ncw-accordion').tabs(
			'.ncw-accordion .ncw-accordion-content', 
			{
				tabs: 'h3', 
				effect: 'slide', 
				initialIndex: null
			}
		);
	}
};

/**
 * Left Accordion
 */
ncw.layout.left.accordion.resize = function () {
	if ($('.ncw-accordion').length > 0) {
		var height = $('.ncw-left').height();
		$('.ncw-accordion-header').each(
			function () {
				height = height - ncw.layout.left.accordion.header.height;
			}
		);
		height = height - ncw.layout.left.accordion.content.cutoff;
		$('.ncw-accordion-content').height(height);
	}
};

ncw.layout.left.accordion.header = {};
ncw.layout.left.accordion.header.height = 24;
ncw.layout.left.accordion.content = {};
ncw.layout.left.accordion.content.cutoff = 1;

/**
 * Left side menu
 */
ncw.layout.left.sideMenu = function () {
	if ($('.ncw-side-menu').length > 0) {
		$('.ncw-side-menu .ncw-side-menu-header').click(
			function () {
				$(this).next().toggle();
			}
		);
	}
};

ncw.layout.right = {};

/**
 * Right main tabs
 */
ncw.layout.right.mainTabs = function () {
    if ($('.ncw-main-tabs-container').length > 0) {
        $('.ncw-main-tabs-container').tabs(
        	'.ncw-main-tab-contents > .ncw-main-tab-content',
        	{
        		'tabs': '.ncw-main-tab a'
        	}	
        );
    }
};

ncw.layout.right.mainTabs.content = {};
ncw.layout.right.mainTabs.content.cutoff = 58;
ncw.layout.right.mainTabs.content.cutoff2 = 28;

/**
 * Right sub tabs
 */
ncw.layout.right.subTabs = function () {
    if ($('.ncw-tabs-container').length > 0) {
        $('.ncw-tabs-container').tabs(
        	'.ncw-tab-contents > .ncw-tab-content',
        	{
        		'tabs': '.ncw-tab a'
        	}        	
        );
    }
};

ncw.layout.right.subTabs.content = {};
ncw.layout.right.subTabs.content.full = {};
ncw.layout.right.subTabs.content.full.cutoff = 145;

/**
 * Right sub sub tabs
 */
ncw.layout.right.sub2Tabs = function () {
    if ($('.ncw-sub-tabs-container').length > 0) {
        $('.ncw-sub-tabs-container').tabs(
        	'.ncw-sub-tab-contents > .ncw-sub-tab-content',
        	{
        		'tabs': '.ncw-sub-tab a'
        	}        	
        );
    }
};

ncw.layout.right.sub2Tabs.content = {};
ncw.layout.right.sub2Tabs.content.full = {};
ncw.layout.right.sub2Tabs.content.full.cutoff = 145;

/**
 * Menu
 */
ncw.layout.menus = function () {
    ncw.layout.menu.hover();
    ncw.layout.menu.overlay();
	$(".ncw-menu-node a").click(
		function () {
			var id = $(this).attr('rel');
			if (id != '') {
				var left = $(this).offset()['left'];
				if ((left + ncw.layout.menus.sub.width) > $(window).width()) {
					left = $(window).width() - ncw.layout.menus.sub.width;
				}
				$($(this).attr('rel')).css('left', left);
				
				ncw.layout.menu.overlay.show();
				$($(this).attr('rel')).toggle();
				
				ncw.layout.menu.current = $(this).attr('rel');
			}
		}
	);
}

ncw.layout.menus.sub = {};
ncw.layout.menus.sub.width = 218;

ncw.layout.menu = {};
ncw.layout.menu.current = null;

/**
 * Menu overlay
 */
ncw.layout.menu.overlay = function () {
    $("#ncw-sub-menu-overlay").click(
        function () {
            $(this).toggle();
            $(ncw.layout.menu.current).toggle();
        }
    );
};

/**
 * Menu show overlay
 */
ncw.layout.menu.overlay.show = function () {
    width = $(document).width();
    height = $(document).height();
    $("#ncw-sub-menu-overlay").width(width).height(height).toggle();
};

/**
 * Menu node hover
 */
ncw.layout.menu.hover = function () {
    $('.ncw-sub-menu-node').hover(
        function () {
            $(this).addClass('ncw-sub-menu-node-hover');
        },
        function () {
            $(this).removeClass('ncw-sub-menu-node-hover');
        }          
    );
    $('.ncw-sub-menu-node').click(
        function () {
            var url = $(this).children('a').attr('href');
            window.location.href = url;
        }
    );
};

ncw.layout.input = {};
ncw.layout.input.container = {};

/**
 * Input fields tooltips
 */
ncw.layout.input.tooltips = function () {
    /*$('.ncw-input-tooltip, .ncw-textarea-tooltip').remove();
    
    if ($(".ncw-input").length > 0) {
    	$(".ncw-input").tooltip(
    		{ 
    		    position: 'bottom right', 
    		    offset: [-44, 4], 
    		    effect: 'fade', 
    		    opacity: 1, 
    		    tipClass: 'ncw-input-tooltip' 
    		}
    	);
    }
    
    if ($(".ncw-select").length > 0) {
        $(".ncw-select").tooltip(
            { 
                position: 'bottom right', 
                offset: [-42, 4], 
                effect: 'fade', 
                opacity: 1, 
                tipClass: 'ncw-input-tooltip' 
            }
        );
    }    
	
    if ($(".ncw-textarea").length > 0) {
        $(".ncw-textarea").tooltip(
            { 
                position: 'bottom right', 
                offset: [-110, 4], 
                effect: 'fade', 
                opacity: 1, 
                tipClass: 'ncw-textarea-tooltip' 
            }
        );	
    }*/
};

ncw.layout.input.container.cutoff = 310;
ncw.layout.input.cutoff = 320;

/**
 * table
 */
ncw.layout.table = function () {
    $('table.ncw-table tbody tr:odd').removeClass('ncw-table-tr-even').addClass('ncw-table-tr-odd');
    $('table.ncw-table tbody tr:even').removeClass('ncw-table-tr-odd').addClass('ncw-table-tr-even');    
};

ncw.layout.statusMessage = {};

/**
 * Shows the status message
 * @param element
 */
ncw.layout.statusMessage.show = function (element, absolute) {
    if (true == absolute) {
        $(element).show();
    } else {
        $(element).parent().parent().prev().show();
    }
};

/**
 * Hides the status message
 * @param element
 */
ncw.layout.statusMessage.hide = function (element, callback, absolute) {
    setTimeout(
        function () {
            if (true == absolute) {
                $(element).hide();
            } else {            
                $(element).parent().parent().prev().hide();
            }
            $('.ncw-throbber').remove();
            ncw.layout.statusMessage.setBackgroundColor(element, ncw.layout.statusMessage.backgroundColor, absolute);
            ncw.layout.statusMessage.setBorderColor(element, ncw.layout.statusMessage.borderColor, absolute);
            
            if (typeof callback == 'function') {
                callback();
            }
            
        }, 
        1000
    );      
};

/**
 * Sets the status message
 * @param element
 * @param message
 */
ncw.layout.statusMessage.setImage = function (element, src, absolute) {
    if (!src || src == null) {
        src = ncw.image('throbber.gif');
    }
    $('.ncw-throbber').remove();
    
    if (true == absolute) {
        $(element).append(
            '<img class="ncw-throbber" src="' + src + '" />'
        );
    } else {    
        $(element).parent().parent().prev().append(
            '<img class="ncw-throbber" src="' + src + '" />'
        );
    }
};

/**
 * Sets the status message
 * @param element
 * @param message
 */
ncw.layout.statusMessage.setText = function (element, message, image_src, absolute) {
    if (true == absolute) {
        $(element).html(message);
    } else {     
        $(element).parent().parent().prev().html(message);
    }
	ncw.layout.statusMessage.setImage(element, image_src, absolute);
};

/**
 * Sets the status color
 * @param element
 * @param color
 */
ncw.layout.statusMessage.setBackgroundColor = function (element, color, absolute) {
    if (true == absolute) {
        $(element).css('background-color', color);
    } else {        
        $(element).parent().parent().prev().css('background-color', color);
    }
};

/**
 * Sets the status color
 * @param color
 */
ncw.layout.statusMessage.setBorderColor = function (element, color, absolute) {
    if (true == absolute) {
        $(element).css('border-color', color);
    } else {       
        $(element).parent().parent().prev().css('border-color', color);
    }
};

ncw.layout.statusMessage.backgroundColor = '#009ee0';
ncw.layout.statusMessage.borderColor = '#009ee0';

/**
 * Adds a date and time picker
 */
ncw.layout.dateTimePicker = function () {
    $('.date_selector, .time-picker').remove();
    $('.ncw-datepick').date_input(
    	{ 
    		start_of_week: 1,
			month_names: [T_("January"), T_("February"), T_("March"), T_("April"), T_("May"), T_("June"), T_("July"), T_("August"), T_("September"), T_("October"), T_("November"), T_("December")],
			short_month_names: [T_("Jan"), T_("Feb"), T_("Mar"), T_("Apr"), T_("May"), T_("Jun"), T_("Jul"), T_("Aug"), T_("Sep"), T_("Oct"), T_("Nov"), T_("Dec")],
			short_day_names: [T_("Su"), T_("Mo"), T_("Tu"), T_("We"), T_("Th"), T_("Fr"), T_("Sa")],
    	}
    );
    $('.ncw-datepick-with-past').date_input(
    	{ 
    		start_of_week: 1,
			month_names: [T_("January"), T_("February"), T_("March"), T_("April"), T_("May"), T_("June"), T_("July"), T_("August"), T_("September"), T_("October"), T_("November"), T_("December")],
			short_month_names: [T_("Jan"), T_("Feb"), T_("Mar"), T_("Apr"), T_("May"), T_("Jun"), T_("Jul"), T_("Aug"), T_("Sep"), T_("Oct"), T_("Nov"), T_("Dec")],
			short_day_names: [T_("Su"), T_("Mo"), T_("Tu"), T_("We"), T_("Th"), T_("Fr"), T_("Sa")],
    	}    	
    );
    //$('.ncw-timepick').timePicker({step: 15});
    $('.ncw-timepick').timepicker({ 'timeFormat': 'H:i' });
    return;
};

/*
 * TRIGGERS
 */

/**
 * save trigger
 */
ncw.layout.saveTrigger = function () {
    if ($('.ncw-save-trigger').length > 0) {
        $('.ncw-save-trigger').click(
            function () {
                var element = this;
                ncw.layout.statusMessage.setText(element, T_('Saving...'));
                ncw.layout.statusMessage.show(element);                
                $('.form').submit();
            }
        );
    }
};

/**
 * save trigger ajax
 */
ncw.layout.saveTriggerAjax = function () {
    if ($('.ncw-save-trigger-ajax').length > 0) {
        $('.ncw-save-trigger-ajax').click(
            function () {
                var rel = $(this).attr('rel').split('/');
                                      
                var element = this;
                ncw.layout.statusMessage.setText(element, T_('Saving...'));
                ncw.layout.statusMessage.show(element);

                if (typeof(ncw.layout.saveTriggerAjax.beforeSaveCallbacks[0][rel[0] + '/' + rel[1]]) != 'undefined'
                    && typeof(ncw.layout.saveTriggerAjax.beforeSaveCallbacks[0][rel[0] + '/' + rel[1]]) == 'function'
                ) {
                    ncw.layout.saveTriggerAjax.beforeSaveCallbacks[0][rel[0] + '/' + rel[1]]();
                }    
                $.post(
                    ncw.url(
                        rel[0] + '/' + rel[1] + '/update'
                    ),
                    $('#ncw-' + rel[1] + '_form').serialize(),
                    function (data) {
                    	$('span.error').remove();
                    	
                        if (true == data.return_value) {
                            ncw.layout.dialogs.saveNotify();
                            ncw.layout.statusMessage.setText(
                                element,
                                T_('Saved') + '!',
                                ncw.image('check.jpg')
                            );
                        } else {
                            ncw.layout.dialogs.errorNotify();
                            ncw.layout.statusMessage.setText(
                                element, 
                                T_('Error') + '!'
                            );
                            
                            ncw.layout.doErrorFields(data);
                        }
                        ncw.layout.statusMessage.hide(
                            element,
                            function () {
                                if (true == data.return_value) {
                                    eval('if (typeof(ncw.' + rel[0] + '.' + rel[1] + '.reload) == "function") { ncw.' + rel[0] + '.' + rel[1] + '.reload("save", data); } ');
                                }
                            }
                        );
                    },
                    'json'
                );
            }
        );
    }
};

/**
 * Register on load callback function
 * @param callback
 */
ncw.layout.saveTriggerAjax.beforeSave = function (rel, callback) {
    ncw.layout.saveTriggerAjax.beforeSaveCallbacks[0][rel] = callback;
};

ncw.layout.saveTriggerAjax.beforeSaveCallbacks = new Array(new Object());

/**
 * delete trigger
 */
ncw.layout.deleteTrigger = function () {   
    if ($('.ncw-delete-trigger').length > 0) {
        $('.ncw-delete-trigger').each(
            function () {
                $(this).attr('rel', $(this).attr('href'));
                $(this).attr('href', '#');
            }
        );
        $('.ncw-delete-trigger').live(
            'click',
            function () {
                var element = this;        
                ncw.layout.dialogs.deleteConfirm(
                     function () {
                         ncw.layout.statusMessage.setText(element, T_('Deleting...'));
                         ncw.layout.statusMessage.show(element);  
                         
                         window.location.href = $(element).attr('rel');
                     }
                );
            }
         );
    }
};

/**
 * Delete modal dialog
 */
ncw.layout.deleteTriggerAjax = function () {  
    if ($('.ncw-delete-trigger-ajax').length > 0) { 
        $('.ncw-delete-trigger-ajax').live(
            'click',
            function () {
                var element = this;
                ncw.layout.dialogs.deleteConfirm(
                    function () {
                        var rel = $(element).attr('rel').split('/');
                        if (rel.length > 2) {
                            var id = rel[2];
                            var website_site_mode = true;
                        } else {
                            var id = $('#' + rel[1] + '_id').val();
                            var website_site_mode = false;
                        }                        
                        
                        if (false == website_site_mode) {
                            ncw.layout.statusMessage.setText(element, T_('Deleting...'));
                            ncw.layout.statusMessage.show(element);
                        }
                        
                        $.get(
                            ncw.url(
                                '/' + rel[0] + '/' + rel[1] + '/delete/' + 
                                id
                            ),
                            null,
                            function (data) {
                                if (true == data.return_value) {
                                    ncw.layout.dialogs.saveNotify(T_('Deleted'), T_('The item has been deleted successfully'));
                                    if (false == website_site_mode) {
                                        ncw.layout.statusMessage.setText(
                                            element, 
                                            T_('Deleted') + '!', 
                                            ncw.image('check.jpg')
                                        );
                                    }
                                } else {
                                	if (typeof(data.message) != 'undefined') {
                                		var message = data.message
                                	} else {
                                		var message = T_('An error has occurred!');
                                	}
                                    ncw.layout.dialogs.errorNotify(T_('Error'), message);
                                    if (false == website_site_mode) {
                                        ncw.layout.statusMessage.setText(
                                            element, 
                                            T_('Error') + '!'
                                        );
                                    }
                                }         
                                if (false == website_site_mode) {
                                    ncw.layout.statusMessage.hide(
                                        element, 
                                        function () {
                                            if (true == data.return_value) {
                                                eval('if (typeof(ncw.' + rel[0] + '.' + rel[1] + '.reload) == "function") { ncw.' + rel[0] + '.' + rel[1] + '.reload("delete", data); } ');
                                            }
                                        }
                                    );
                                } else if (true == data.return_value) {
                                    window.location.href = ncw.BASE;
                                }
                            },
                            'json'
                        );  
                    }
                );
            }
        );
    }
};

/**
 * save new sitelanguage
 */
ncw.layout.addNewTrigger = function () {
    if ($('.ncw-new-trigger-ajax').length > 0) { 
        $('.ncw-new-trigger-ajax').click(
            function () {
                if (true == ncw.layout.addNewTrigger.inProgress) {
                    return false;
                }
                ncw.layout.addNewTrigger.inProgress = true;
                
                var rel = $(this).attr('rel').split('/');
                                
                var element = this;
                
                ncw.layout.statusMessage.setText(element, T_('Saving...'));
                ncw.layout.statusMessage.show(element);
                
                $.post(
                    ncw.url(
                        '/' + rel[0] + '/' + rel[1] + '/save'
                    ),
                    $('#ncw-' + rel[1] + '_form').serialize(),
                    function (data) {
                    	$('span.error').remove();
                    	
                        if (true == data.return_value) {
                            ncw.layout.dialogs.saveNotify();
                            ncw.layout.statusMessage.setText(
                                element, 
                                T_('Saved') + '!', 
                                ncw.image('check.jpg')
                            );
                        } else {
                            ncw.layout.dialogs.errorNotify();
                            ncw.layout.statusMessage.setText(
                                element, 
                                T_('Error') + '!'
                            );
                            ncw.layout.doErrorFields(data);
                        }
                        ncw.layout.statusMessage.hide(
                            element,
                            function () {
                                ncw.layout.addNewTrigger.inProgress = false;
                                if (true == data.return_value) {
                                    eval('if (typeof(ncw.' + rel[0] + '.' + rel[1] + '.reload) == "function") { ncw.' + rel[0] + '.' + rel[1] + '.reload("new", data); } ');
                                }
                            }
                        );                       
                    },
                    'json'
                );
            }
        );
    }
};

ncw.layout.addNewTrigger.inProgress = false;

/**
 * Creates the error fields
 */
ncw.layout.doErrorFields = function (data) {
	if (typeof(data.invalid_fields) == 'undefined') {
		return;
	}
	$.each(
		data.invalid_fields,
		function (controller, fields) {
			if (typeof(fields) == 'object'
				&& fields != null
				&& typeof(fields.length) == 'undefined' 
			) {
				$.each(
					fields,
					function (field, error_message) {
						if (typeof(error_message) != 'object') {
							$('input[name="data[' + controller + '][' + field + ']"]').parent().append(
								'<span class="error">' + T_(error_message) + '</span>'                            						
							);
						} else {
							var count = field.replace('n_', '');
							$.each(
								error_message,
								function (controller, fields) {
									$.each(
										fields,
										function (field, error_message) {
											$('input[name="data[' + controller + '][' + count + '][' + field + ']"]').parent().append(
												'<span class="error">' + T_(error_message) + '</span>'                            						
											);
										}
									);
								}
							);							
						}
					}
				);
			}
		}
	);	
}

//init
ncw.layout();