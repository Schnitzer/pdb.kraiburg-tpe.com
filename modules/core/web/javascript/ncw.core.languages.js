/**
 * language
 */
ncw.core.language = function () {

};

/**
 * reload language
 */
ncw.core.language.reload = function (action) {
    if (action != 'save') {
        window.location.href = ncw.BASE + '/' + ncw.PREFIX +
        '/core/language/all';
    }
};

ncw.core.language();