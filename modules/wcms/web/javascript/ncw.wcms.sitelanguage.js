/**
 * Initialize
 * @param translation_id
 */
ncw.wcms.sitelanguage = function (translation_id) { 
    ncw.wcms.sitelanguage.translationId = translation_id;
    ncw.wcms.sitelanguage.load();
};

/**
 * The translation id
 */
ncw.wcms.sitelanguage.translationId = 0;

/**
 * Loads the needed scripts
 */
ncw.wcms.sitelanguage.load = function () {
    ncw.layout.loadView(
        ncw.url(
            '/wcms/sitelanguage/edit/' 
                + ncw.wcms.sitelanguage.translationId
        ),
        function () {            
            ncw.layout.dateTimePicker();
        }
    );       
};

/**
 * Loads new sitelanguage action
 * @param site_id
 * @param language_code
 */
ncw.wcms.sitelanguage.loadNew = function (site_id, language_code) {
    if (true == ncw.wcms.site.tree.permissions.sitelanguage.addNew) {
        ncw.layout.loadView(
            ncw.url(
                '/wcms/sitelanguage/new/' 
                    + site_id + '/' + language_code
            )
        );
    }
};

/**
 * reload site structure
 */
ncw.wcms.sitelanguage.reload = function () {
    ncw.wcms.site.tree.reload();
};

/**
 * Loads a site translation
 * @þaram site_id
 */
ncw.wcms.sitelanguage.loadSite = function (site_id) {
    ncw.wcms.site(site_id);
};