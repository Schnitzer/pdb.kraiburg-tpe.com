/**
 * Initialize
 */
ncw.wcms.news = function () {
    ncw.layout.dateTimePicker();
};

/**
 * newslanguages
 */
ncw.wcms.newslanguages = {};

/**
 * load tinymce
 */
ncw.wcms.newslanguages.loadTinyMCE = function () {
    ncw.wcms.tinymce.setup();
};

// do it
$(document).ready(
    function () {
        ncw.wcms.news();
    }
);