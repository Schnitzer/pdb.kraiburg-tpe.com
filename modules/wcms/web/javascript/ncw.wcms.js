ncw.wcms = {};
ncw.wcms.tree = {};

/**
 * initview callback
 */
ncw.layout.initView.callback = function () {
    ncw.wcms.copy();
    ncw.wcms.reference();
    ncw.wcms.publishTrigger();
    ncw.wcms.unpublishTrigger();
    ncw.wcms.schedule();
};

/**
 * on resize change tree height
 */
ncw.wcms.tree.resize = function () {
    var height = $('.ncw-accordion-content').height();
    $('.ncw-tree').siblings().each(function () {
        var element_height = $(this).height() + parseInt($(this).css('padding-top')) 
            + parseInt($(this).css('padding-bottom')) + parseInt($(this).css('margin-top')) 
            + parseInt($(this).css('margin-bottom')) + parseInt($(this).css('border-top-width')) + parseInt($(this).css('border-bottom-width'));
        height = height - element_height; 
    });
    height = height - 10;
    $('.ncw-tree').height(height);
};

/**
 * publish modal dialog
 */
ncw.wcms.publishTrigger = function () {
    if ($('.ncw-publish-trigger').length > 0) {
        $('.ncw-publish-trigger').live(
            'click',
            function () {
                var element = this;
                
                ncw.layout.dialogs.prompt(
                    T_('Publish'),
                    function () {     
                        var rel = $(element).attr('rel').split('/');
                        if (rel.length > 1) {
                            var id = rel[1];
                            var website_site_mode = true;
                        } else {
                            var id = $('#' + rel + '_id').val();
                            var website_site_mode = false;
                        }
                        rel = rel[0];
                        
                        if (false == website_site_mode) {
                            ncw.layout.statusMessage.setText(element, T_('Publishing...'));
                            ncw.layout.statusMessage.show(element);
                        }
                        
                        $.get(
                            ncw.url('/wcms/' + rel + '/publish/' + id),
                            null,
                            function (data) {
                                if (true == data.return_value) {
                                    ncw.layout.dialogs.saveNotify(T_('Published'), T_('The item has been published successfully.'));
                                    if (false == website_site_mode) {
                                        ncw.layout.statusMessage.setText(
                                            element, 
                                            T_('Published') + '!', 
                                            ncw.image('check.jpg')
                                        );
                                        if ($('.ncw-unpublish-trigger').length == 0) {
                                            $(element).parent().parent().prepend('<li class="ncw-action"><a href="#" rel="' + rel + '" class="ncw-unpublish-trigger ncw_unpublish_icon">' + T_('Unpublish') + '</a></li>');
                                        }
                                    }
                                } else {
                                    ncw.layout.dialogs.errorNotify();
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
                                                eval('ncw.wcms.' + rel + '.reload("publish")');
                                            }
                                        }
                                    );  
                                } else {
                                    window.location.reload();
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
 * unpublish modal dialog
 */
ncw.wcms.unpublishTrigger = function () {
    if ($('.ncw-unpublish-trigger').length > 0) { 
        $('.ncw-unpublish-trigger').live(
            'click',
            function () {
                var element = this;
                
                ncw.layout.dialogs.prompt(
                    T_('Unpublish'),
                    function () {                
                        var rel = $(element).attr('rel').split('/');
                        if (rel.length > 1) {
                            var id = rel[1];
                            var website_site_mode = true;
                        } else {
                            var id = $('#' + rel + '_id').val();
                            var website_site_mode = false;
                        }
                        rel = rel[0];
        
                        if (false == website_site_mode) {
                            ncw.layout.statusMessage.setText(element, T_('Unpublishing...'));
                            ncw.layout.statusMessage.show(element);
                        }
                        
                        $.get(
                            ncw.url('/wcms/' + rel + '/unpublish/' + id),
                            null,
                            function (data) {
                                if (true == data.return_value) {
                                    ncw.layout.dialogs.saveNotify(T_('Unpublished'), T_('The item has been unpublished successfully.'));
                                    if (false == website_site_mode) {
                                        ncw.layout.statusMessage.setText(
                                            element, 
                                            T_('Unpublished') + '!', 
                                            ncw.image('check.jpg')
                                        );
                                        $(element).parent().remove();
                                    }
                                } else {
                                    ncw.layout.dialogs.errorNotify();
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
                                                eval('ncw.wcms.' + rel + '.reload("unpublish")');
                                            }
                                        }
                                    );    
                                } else {
                                    window.location.reload();
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
 * unpublish modal dialog
 */
ncw.wcms.copy = function () {
    if ($('.ncw-copy-trigger').length > 0) { 
        $('.ncw-copy-trigger').live(
            'click',
            function () {
                var element = this;
                ncw.layout.dialogs.confirm(
                    T_('Copy'),
                    function () {
                        var rel = $(element).attr('rel').split('/');
                        if (rel.length > 1) {
                            var id = rel[1];
                            var website_site_mode = true;
                        } else {
                            var id = $('#' + rel + '_id').val();
                            var website_site_mode = false;
                        }
                        rel = rel[0]; 
                        
                        $.get(
                            ncw.url('/wcms/' + rel + '/copy/' + id),
                            null,
                            function (data) {
                                if (true == data.return_value) {
                                    ncw.layout.dialogs.saveNotify(T_('Copied'), T_('The item has been copied successfully.'));
                                    if (false == website_site_mode) {
                                        ncw.layout.statusMessage.setText(
                                            element, 
                                            T_('Copied') + '!', 
                                            ncw.image('/web/images/check.jpg')
                                        );
                                    }
                                } else {
                                    ncw.layout.dialogs.errorNotify();
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
                                                eval('ncw.wcms.' + rel + '.reload("copy")');
                                            }
                                        }
                                    );                 
                                } else {
                                    window.location.reload();
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
 * unpublish modal dialog
 */
ncw.wcms.reference = function () {
    if ($('.ncw-reference-trigger').length > 0) { 
        $('.ncw-reference-trigger').click(
            function () {
                var element = this;
                var rel = $(element).attr('rel').split('/');
                if (rel.length > 1) {
                    var id = rel[1];
                    var website_site_mode = true;
                } else {
                    var id = $('#' + rel + '_id').val();
                    var website_site_mode = false;
                }
                rel = rel[0]; 
                
				callback = function (site_id) {
					$.get(
	                    ncw.url('/wcms/' + rel + '/reference/' + id + '/' + site_id.id),
	                    null,
	                    function (data) {
	                        if (true == data.return_value) {
	                            ncw.layout.dialogs.saveNotify(T_('Referenced added'), T_('The reference has been added successfully.'));
	                            if (false == website_site_mode) {
	                                ncw.layout.statusMessage.setText(
	                                    element, 
	                                    T_('Reference added') + '!', 
	                                    ncw.image('/web/images/check.jpg')
	                                );
	                            }
	                        } else {
	                            ncw.layout.dialogs.errorNotify();
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
	                                    	ncw.wcms.component.siteId = site_id.id;
	                                    	ncw.wcms.site.tree.selectSite(site_id.id);
	                            			ncw.wcms.component.load();
	                                    }
	                                }
	                            );
	                        } else {
	                            window.location.reload();
	                        }
	                    },
	                    'json'
	                );					
				};

			    if (typeof(ncw.dialogs.siteSearch) == 'undefined') {
			        $.getScript(
			            ncw.url('wcms/site/searchDialog.js'),
			            function (data) {
			                ncw.dialogs.siteSearch.show(callback);
			            }
			        );
			    } else {
			        ncw.dialogs.siteSearch.show(callback);
			    }                        
            }
        );
    }  
};

/**
 * Schedule 
 */
ncw.wcms.schedule = function () {
    if ($('.ncw-schedule-check').length != 0) {
        if ($('.ncw-schedule').css('display') == 'none') {
            ncw.wcms.schedule.show = false;
        } else {
            ncw.wcms.schedule.show = true;
        }
        $('.ncw-schedule-check').change(
            function () {
                if (false == ncw.wcms.schedule.show) {
                    $('.ncw-schedule').show();
                    ncw.wcms.schedule.show = true;
                } else {
                    $('.ncw-schedule').hide();
                    ncw.wcms.schedule.show = false;
                }
            }
        );
    }
};

ncw.wcms.schedule.show = false;


