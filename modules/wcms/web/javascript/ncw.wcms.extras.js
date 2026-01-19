
/**
 * extras
 */
ncw.wcms.extras = function () {
    ncw.onLoad(ncw.wcms.sitetemplate);
    ncw.onLoad(ncw.wcms.componenttemplate);
    ncw.onLoad(ncw.wcms.navtemplate);
    ncw.onLoad(ncw.wcms.javascript);
    ncw.onLoad(ncw.wcms.css);
    ncw.onLoad(ncw.wcms.codemirros);
    ncw.onLoad(ncw.wcms.settings);
};

/**
 * codemirros
 */
ncw.wcms.codemirros = function () {
	if (typeof(CodeMirror) != 'undefined') {
	    var height = $('.ncw-main-tab-contents .ncw-main-tab-content:first').height() - 2;
	    switch (ncw.wcms.extras.codemirror_type) {
	    case 'normal':
	        ncw.wcms.extras.codemirror = CodeMirror.fromTextArea(
	            'codemirror_editor',
	            {
	                height: (height - 116) + 'px',
	                parserfile: [
	                    "parsexml.js",
	                    "parsecss.js",
	                    "tokenizejavascript.js",
	                    "parsejavascript.js",
	                    "../contrib/php/js/tokenizephp.js",
	                    "../contrib/php/js/parsephp.js",
	                    "../contrib/php/js/parsephphtmlmixed.js"
	                ],
	                stylesheet: [
	                    ncw.BASE + "/modules/wcms/web/javascript/lib/codemirror/css/xmlcolors.css",
	                    ncw.BASE + "/modules/wcms/web/javascript/lib/codemirror/css/jscolors.css",
	                    ncw.BASE + "/modules/wcms/web/javascript/lib/codemirror/css/csscolors.css",
	                    ncw.BASE + "/modules/wcms/web/javascript/lib/codemirror/contrib/php/css/phpcolors.css"
	                ],
	                path: ncw.BASE + "/modules/wcms/web/javascript/lib/codemirror/js/",
	                continuousScanning: 500,
	                tabMode: 'spaces',
	                lineNumbers: true
	            }
	        );
	        break;
	    case 'css':
	        ncw.wcms.extras.codemirror_css = CodeMirror.fromTextArea(
	            'codemirror_css_editor',
	            {
	                height: (height - 58) + 'px',
	                parserfile: [
	                    "parsecss.js",
	                ],
	                stylesheet: [
	                    ncw.BASE + "/modules/wcms/web/javascript/lib/codemirror/css/csscolors.css",
	                ],
	                path: ncw.BASE + "/modules/wcms/web/javascript/lib/codemirror/js/",
	                continuousScanning: 500,
	                tabMode: 'spaces',
	                lineNumbers: true
	            }
	        );  
	        break;
	    case 'js':
	        ncw.wcms.extras.codemirror_js = CodeMirror.fromTextArea(
	            'codemirror_js_editor',
	            {
	                height: (height - 58) + 'px',
	                parserfile: [
	                    "tokenizejavascript.js",
	                    "parsejavascript.js",
	                ],
	                stylesheet: [
	                    ncw.BASE + "/modules/wcms/web/javascript/lib/codemirror/css/jscolors.css",
	                ],
	                path: ncw.BASE + "/modules/wcms/web/javascript/lib/codemirror/js/",
	                continuousScanning: 500,
	                tabMode: 'spaces',
	                lineNumbers: true
	            }
	        );        
	        break;
	    }
	}
};

ncw.wcms.extras.codemirror = null;
ncw.wcms.extras.codemirror_type = 'normal';

/**
 * sitetemplates
 */
ncw.wcms.sitetemplate = function () {
    ncw.wcms.sitetemplate.save();
    ncw.wcms.sitetemplate.addNew();
};

/**
 * save sitetemplate
 */
ncw.wcms.sitetemplate.save = function () {
    $('.ncw-sitetemplate-save-trigger').click(
        function () {
            $('#sitetemplate_code').val(ncw.wcms.extras.codemirror.getCode());
            $('.form').submit();            
        }
    );
};

/**
 * save sitetemplate
 */
ncw.wcms.sitetemplate.addNew = function () {
    $('.ncw-sitetemplate-new-trigger').click(
        function () {
            $('#sitetemplate_code').val(ncw.wcms.extras.codemirror.getCode());
            $('.form').submit();            
        }
    );
};

/**
 * componenttemplates
 */
ncw.wcms.componenttemplate = function () {
    ncw.wcms.componenttemplate.save();
    ncw.wcms.componenttemplate.addNew();
};

/**
 * save componenttemplate
 */
ncw.wcms.componenttemplate.save = function () {
    $('.ncw-componenttemplate-save-trigger').click(
        function () {
            $('#componenttemplate_code').val(ncw.wcms.extras.codemirror.getCode());
            $('.form').submit();            
        }
    );
};

/**
 * save componenttemplate
 */
ncw.wcms.componenttemplate.addNew = function () {
    $('.ncw-componenttemplate-new-trigger').click(
        function () {
            $('#componenttemplate_code').val(ncw.wcms.extras.codemirror.getCode());
            $('.form').submit();            
        }
    );
};

/**
 * navtemplates
 */
ncw.wcms.navtemplate = function () {
    ncw.wcms.navtemplate.save();
    ncw.wcms.navtemplate.addNew();
};

/**
 * save navtemplate
 */
ncw.wcms.navtemplate.save = function () {
    $('.ncw-navtemplate-save-trigger').click(
        function () {
            $('#navtemplate_code').val(ncw.wcms.extras.codemirror.getCode());
            $('.form').submit();            
        }
    );
};

/**
 * save navtemplate
 */
ncw.wcms.navtemplate.addNew = function () {
    $('.ncw-navtemplate-new-trigger').click(
        function () {
            $('#navtemplate_code').val(ncw.wcms.extras.codemirror.getCode());
            $('.form').submit();            
        }
    );
};

/**
 * javascripts
 */
ncw.wcms.javascript = function () {
    ncw.wcms.javascript.save();
    ncw.wcms.javascript.addNew();
};

/**
 * save javascript
 */
ncw.wcms.javascript.save = function () {
    $('.ncw-javascript-save-trigger').click(
        function () {
            $('#javascript_code').val(ncw.wcms.extras.codemirror_js.getCode());
            $('.form').submit();            
        }
    );
};

/**
 * save javascript
 */
ncw.wcms.javascript.addNew = function () {
    $('.ncw-javascript-new-trigger').click(
        function () {
            $('#javascript_code').val(ncw.wcms.extras.codemirror_js.getCode());
            $('.form').submit();            
        }
    );
};

/**
 * css
 */
ncw.wcms.css = function () {
    ncw.wcms.css.save();
    ncw.wcms.css.addNew();
};

/**
 * save css
 */
ncw.wcms.css.save = function () {
    $('.ncw-css-save-trigger').click(
        function () {
            $('#css_code').val(ncw.wcms.extras.codemirror_css.getCode());
            $('.form').submit();            
        }
    );
};

/**
 * save css
 */
ncw.wcms.css.addNew = function () {
    $('.ncw-css-new-trigger').click(
        function () {
            $('#css_code').val(ncw.wcms.extras.codemirror_css.getCode());
            $('.form').submit();            
        }
    );
};

/**
 * css
 */
ncw.wcms.settings = function () {
    ncw.wcms.settings.reloadSitemap();
};

/**
 * save css
 */
ncw.wcms.settings.reloadSitemap = function () {
    $('#ncw-settings-reload-sitemap').click(
        function () {
        	$.get(
        		ncw.url('wcms/xml/getSitemap'),
        		function (sitemap) {
		            $('#setting_sitemap').val(sitemap);
        		}
        	);
        	
        }
    );
};

// do it 
ncw.wcms.extras();