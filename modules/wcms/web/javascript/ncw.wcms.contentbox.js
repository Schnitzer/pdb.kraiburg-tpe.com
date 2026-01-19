/**
 * Initialize
 */
ncw.wcms.contentbox = function () {

};

ncw.wcms.contentbox.id = 0;

/**
 * Contentbox reload
 */
ncw.wcms.contentbox.reload = function (action) {
    if (action != 'save') {
        ncw.wcms.contentboxgroup.tree.reload();
        ncw.wcms.contentbox.load();
    }
};

/**
 * load Contentbox
 */
ncw.wcms.contentbox.load = function () {
    if ($(".ncw-contentboxgroup-tree").length > 0) {
        ncw.layout.loadView(
            ncw.url('/wcms/contentbox/all/' + ncw.wcms.contentboxgroup.id),
            function () {
                if (typeof ncw.wcms.contentbox.load.callback == 'function') {
                    ncw.wcms.contentbox.load.callback();
                }
            }
        ); 
    };
};

/**
 * Load page
 */
ncw.wcms.contentbox.loadPage = function () {
    ncw.layout.initView();
    if (typeof ncw.wcms.contentbox.load.callback == 'function') {
        ncw.wcms.contentbox.load.callback();
    }
};

ncw.wcms.contentbox.load.callback = null;

/**
 * load edit Contentbox action
 * @param contentbox_id
 */
ncw.wcms.contentbox.loadEdit = function (contentbox_id) {
    ncw.wcms.contentbox.id = contentbox_id;
    ncw.layout.loadView(
        ncw.url('/wcms/contentbox/edit/' + contentbox_id)
    );
};

/**
 * load new Contentbox action
 * @param contentbox_id
 */
ncw.wcms.contentbox.loadNew = function () {
    ncw.layout.loadView(
        ncw.url('/wcms/contentbox/new/' + ncw.wcms.contentboxgroup.id)
    );
};

/**
 * 
 */
ncw.wcms.contentboxlanguage = function () {

};

/**
 * load new Contentbox translation action
 * @param contentbox_id
 */
ncw.wcms.contentboxlanguage.loadNew = function (contentbox_id) {
    ncw.layout.loadView(
        ncw.url('/wcms/contentboxlanguage/new/' + contentbox_id)
    );
};

/**
 * load edit Contentbox translation action
 * @param contentbox_id
 */
ncw.wcms.contentboxlanguage.loadEdit = function (contentbox_id, contentboxlanguage_id) {
    ncw.layout.loadView(
        ncw.url('/wcms/contentboxlanguage/edit/' + contentbox_id + '/' + contentboxlanguage_id),
        function () {
            ncw.layout.saveTriggerAjax.beforeSave('wcms/contentboxlanguage', ncw.wcms.contentboxlanguage.save);
            //ncw.wcms.tinymce.languageCode = 'de';
            ncw.wcms.tinymce.setup();
        }
    );
};

/**
 * called when contentboxlanguage is saves
 */
ncw.wcms.contentboxlanguage.save = function () {
    tinyMCE.triggerSave();
};

/**
 * Contentboxlanguage reload
 */
ncw.wcms.contentboxlanguage.reload = function (action) {
    if (action != 'save') {
        ncw.wcms.contentbox.loadEdit(ncw.wcms.contentbox.id);
    }
};
