var ncw = {};

ncw.dialogs = {};

/**
 * Init
 */
ncw.init = function () {
    ncw.gt = new Gettext(
        { 
            domain : 'default', 
            locale_data : ncw.jsonLocaleData
        }
    );
};

ncw.jsonLocaleData = { 'default' : { "" : [null, ""] } };

ncw.gt = null;

/**
 * Makes a url
 * @param url
 */
ncw.url = function (url) {
    var seperator = '/';
    if (url[0] == '/') {
        seperator = '';
    }
    
    var prefix = ncw.PREFIX;
    if (url.indexOf('/' + prefix) == 0
        || url.indexOf(prefix) == 0
    ) {
        prefix = '';
    }  
    
    var rnd = '_=' + new Date().getTime();    

    if (true == ncw.REWRITE) {
        url = ncw.BASE + '/' + prefix + seperator + url;
        if (url.indexOf('?') > -1) {
            url += '&' + rnd;
        } else {
            url += '?' + rnd;
        }  
    } else {
        if (url.indexOf('?') > -1) {
            url = url.replace('?', '&');
        }
        url = ncw.BASE + '/index.php?url=/' + prefix + seperator + url + '&' + rnd;
    }
    return url;
};

/**
 * Makes a url
 * @param image
 * @param module
 */
ncw.image = function (image, module) {
    if (typeof(module) == 'undefined') {
        var module = false;
    }
    
    var seperator = '/';
    if (image[0] == '/') {
        seperator = '';
    }    
    if (false == module) {
        url = ncw.BASE + '/' + ncw.THEME_PATH + '/web/images' + seperator + image;
    } else {
        url = ncw.BASE + '/modules/' + module + '/web/images' + seperator + image;
    }
    return url;
};

/**
 * Register on load callback function
 * @param callback
 */
ncw.onLoad = function (callback) {
	ncw.onLoad.callbacks.push(callback);
};

/**
 * Registered on load callback functions
 */
ncw.onLoad.callbacks = [];

/**
 * Load
 */
ncw._load = function () {
	$.each(
		ncw.onLoad.callbacks,
		function (count, callback) {
			callback();
		}
	);
};

/**
 * Register on resize callback function
 * @param callback
 */
ncw.onResize = function (callback) {
	ncw.onResize.callbacks.push(callback);
};

/**
 * Registered on resize callback functions
 */
ncw.onResize.callbacks = [];

/**
 * Resize
 */
ncw._resize = function () {
	$.each(
		ncw.onResize.callbacks,
		function (count, callback) {
			callback();
		}
	);
};

ncw.uid = function () {
	return ncw.uidValue++;
}
ncw.uidValue = 10000;

/**
 * gettext function
 * @param text
 * @return
 */
function T_ (text) {
    return ncw.gt.gettext(text);
}

// on ready ... load
$(document).ready(
	function () {
	    ncw.init();
		ncw._load();
	}
);

// on resize call the resize function
$(window).resize(
	function () {
		ncw._resize();
	}
);