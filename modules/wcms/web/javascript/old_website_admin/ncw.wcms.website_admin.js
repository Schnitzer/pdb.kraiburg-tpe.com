ncw.wcms.website_admin = {};

/**
 * Initialize
 */
ncw.wcms.website_admin = function () {
    ncw.onLoad(ncw.wcms.website_admin.loadScripts);
};

/**
 * Loads the needed scripts
 */
ncw.wcms.website_admin.loadScripts = function () {
    $.getScript(
        ncw.BASE + '/modules/wcms/web/javascript/'
        + 'ncw.wcms.website_admin.components.js',
        function () {
            $.getScript(
                ncw.BASE + '/modules/wcms/web/javascript/'
                + 'ncw.wcms.website_admin.files.metadata.js',
                function () {
                    $.getScript(
                        ncw.BASE + '/modules/wcms/web/javascript/'
                        + 'ncw.wcms.website_admin.wysiwyg.js',
                        function () {        
                            ncw.wcms.website_admin.components(
                                function () {
                                    ncw.wcms.website_admin.wysiwyg();
                                    ncw.wcms.website_admin.files.metadata();

                                    ncw.layout.removeLoadingOverlay();
                                    //$('.ncw-add-component-button-container').remove();
                                    //$('#ncw-navigation-2012-admin').children().children('.ncw-add-component-button-container').remove();
				    $('#ncw-navigation-2012-admin').children().children('.ncw-add-component-button-container').css('height', '4px');
                                    $('.box_center div').css('overflow', 'visible');
                                }
                            );
                        }
                    );
                }
            );            
        }
    );    
};

ncw.wcms.website_admin.wysiwyg = {};

/**
 * Called each time TinyMCE intercepts and handles an event such as keydown, mousedown
 */
ncw.wcms.website_admin.wysiwyg.onEvent = function (e) {
    if (e.type == 'keypress') {  
        ncw.wcms.website_admin.wysiwyg.has_changed = true;
    }
};

ncw.wcms.website_admin.wysiwyg.has_changed = false;

// do it
ncw.wcms.website_admin();
