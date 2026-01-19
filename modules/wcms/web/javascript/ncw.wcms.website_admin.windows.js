ncw.layout.initView.removeOverlay = false;

ncw.wcms.website_admin.windows = {};

/**
 * Init layout
 */
ncw.wcms.website_admin.windows = function () {
	ncw.onLoad(ncw.wcms.website_admin.windows.siteStructure);
	ncw.onLoad(ncw.wcms.website_admin.windows.files);
};

/**
 * Site structure window
 */
ncw.wcms.website_admin.windows.siteStructure = function () {
    $("#ncw-window-site-structure, #ncw-editbar-edit-content_mode").newWindow(
    	{
	        windowTitle: T_('Site Structure') + ' - Netzcraftwerk 3',
	        ajaxURL:  ncw.url('/wcms/site/all'),
	        width: 900,
	        height: 500,
            onBeforeLoad: function () {
                $(".ncw-window-container").fadeOut();
                $(".ncw-window-container").children(".ncw-window-content").html("");               
            },	        
	        onAjaxContentLoaded: function (this_window) {
    	        ncw.layout.resize.section.window = this_window;
    			ncw.wcms.website_admin.windows.initView();
                ncw.wcms.site.tree.load.callback = function () {
                    ncw.wcms.site.tree.selectSite(ncw.wcms.website_admin.siteId);
                }    			
                ncw.wcms.site.tree.switchLanguage.languageId = 0;
    			ncw.wcms.site.tree.switchLanguage(ncw.wcms.website_admin.languageId, ncw.wcms.website_admin.languageCode);
    		},
            onResizeBegin : function () {
                $('.ncw-window-content .ncw-right').css('display', 'none');
            },    		
    		onResizeEnd: function (window) { 
    		    ncw.layout.resize.section.window = window;
    			ncw.wcms.website_admin.windows.resize();
    			$('.ncw-window-content .ncw-right').css('display', 'block');
    		},
            onMaximizeBegin : function () {
                $('.ncw-window-content .ncw-right').css('display', 'none');
            },    		
	        onMaximizeEnd: function (window) { 
    		    ncw.layout.resize.section.window = window;
    			ncw.wcms.website_admin.windows.resize();
    			$('.ncw-window-content .ncw-right').css('display', 'block');
    		},
            onMinimizeBegin : function () {
                $('.ncw-window-content .ncw-right').css('display', 'none');
            },
            onMinimizeEnd: function (window) { 
                $('.ncw-window-content .ncw-right').css('display', 'block');
            },     		
    		positionCookie: 'ncw-sites-window-position-cookie',
    		resizeCookie: 'ncw-sites-window-resize-cookie'
    	}
    );
};

/**
 * Site structure window
 */
ncw.wcms.website_admin.windows.files = function () {
    $("#ncw-window-files").newWindow(
        {
            windowTitle: T_('Files') + ' - Netzcraftwerk 3',
            ajaxURL:  ncw.url('/files/folder/all'),
            width: 900,
            height: 500,
            onBeforeLoad: function () {
                $(".ncw-window-container").fadeOut();
                $(".ncw-window-container").children(".ncw-window-content").html("");               
            },
            onAjaxContentLoaded: function (this_window) {              
                ncw.layout.resize.section.window = this_window;
                ncw.wcms.website_admin.windows.initView();
                
                ncw.files.file.load.callback = ncw.wcms.website_admin.windows.files.imageDraggable;
                ncw.files.folder.tree.load();         
                if (ncw.files.folder.id == 1) {
                    ncw.files.file.load();
                }              
            },
            onResizeBegin : function () {
                $('.ncw-window-content .ncw-right').css('display', 'none');
            },
            onResizeEnd: function (window) { 
                ncw.layout.resize.section.window = window;
                ncw.wcms.website_admin.windows.resize();
                $('.ncw-window-content .ncw-right').css('display', 'block');
            },
            onMaximizeBegin : function () {
                $('.ncw-window-content .ncw-right').css('display', 'none');
            },
            onMaximizeEnd: function (window) { 
                ncw.layout.resize.section.window = window;
                ncw.wcms.website_admin.windows.resize();
                $('.ncw-window-content .ncw-right').css('display', 'block');
            },
            onMinimizeBegin : function () {
                $('.ncw-window-content .ncw-right').css('display', 'none');
            },
            onMinimizeEnd: function (window) { 
                $('.ncw-window-content .ncw-right').css('display', 'block');
            },            
            positionCookie: 'ncw-files-window-position-cookie',
            resizeCookie: 'ncw-files-window-resize-cookie'            
        }
    );
};

/**
 * Make files from file window draggable
 */
ncw.wcms.website_admin.windows.files.imageDraggable = function () {
     ncw.wcms.website_admin.components.fileDroppable.add();
     $(".ncw-image-draggable").draggable(
         {
             revert: 'invalid', 
             helper: 'clone', 
             zIndex: 1000, 
             containment: 'document',
             appendTo: 'body',
             start: function () {
                 $('.ncw-window-container').css('opacity', '0.1');
             }, 
             stop: function () {
                 $('.ncw-window-container').css('opacity', '1');
             }
         }
     );    
};


/**
 * Initialize a view
 */
ncw.wcms.website_admin.windows.initView = function () {
    ncw.layout.resize.section.override = ncw.wcms.website_admin.windows.resize;
	
    ncw.layout.initView();	
};

/**
 * Resize layout
 */
ncw.wcms.website_admin.windows.resize = function () {
    var height = $(ncw.layout.resize.section.window).height();
    var width = $(ncw.layout.resize.section.window).width();
    
    $('.ncw-left, .ncw-right').height(height);
    
    // resize right section
    var right_width = width;
    if ($('.ncw-left').length > 0) {
        right_width -= ncw.layout.resize.cutoff.right.width_full;
    } else {
        right_width -= ncw.layout.resize.cutoff.right.width;
    }
    $('.ncw-right').width(right_width);

    // resize main tab content
    var main_tab_content_height = height - ncw.layout.right.mainTabs.content.cutoff;    
    if ($('.ncw-status-action-bar').length > 0) {
        main_tab_content_height -= ncw.layout.right.mainTabs.content.cutoff2;
    }    
    $('.ncw-main-tab-content').height(main_tab_content_height); 
    
    // resize main tab content
    var sub_tab_content_full_height = height - ncw.layout.right.subTabs.content.full.cutoff;    
    $('.ncw-tab-content-full').height(sub_tab_content_full_height);     
    
    // resize input container
    var input_width = right_width - ncw.layout.input.container.cutoff;  
    $('.ncw-input-container').width(input_width);
    
    // resize input fields and textareas
    var input_width = right_width - ncw.layout.input.cutoff;    
    $('.ncw-input, .ncw-textarea').width(input_width);     
    
    // resize wysiwyg editors
    $('.ncw-wysiwyg').width('100%').height('500px');
    $('.ncw-wysiwyg-small').width('100%').height('50px');
    
    // resize accordion
    ncw.layout.left.accordion.resize();	
    
    // resize trees
    ncw.wcms.tree.resize();
    ncw.onResize(ncw.wcms.tree.resize);    
};

ncw.wcms.website_admin.windows.resize.cutoff = {};
ncw.wcms.website_admin.windows.resize.cutoff.right = {};
ncw.wcms.website_admin.windows.resize.cutoff.right.width = 210;

//init
ncw.wcms.website_admin.windows();