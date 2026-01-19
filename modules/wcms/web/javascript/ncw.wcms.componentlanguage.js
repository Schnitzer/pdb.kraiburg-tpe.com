/**
 * Initialize
 * @param translation_id
 */
ncw.wcms.componentlanguage = function (translation_id) { 
    ncw.wcms.componentlanguage.translationId = translation_id;
    ncw.wcms.componentlanguage.load();
};

/**
 * The translation id
 */
ncw.wcms.componentlanguage.translationId = 0;

/**
 * Loads the needed scripts
 */
ncw.wcms.componentlanguage.load = function () {
    ncw.layout.loadView(
        ncw.url('/wcms/componentlanguage/edit/' + ncw.wcms.componentlanguage.translationId),
        function () {         
            ncw.layout.saveTriggerAjax.beforeSave('wcms/componentlanguage', ncw.wcms.componentlanguage.save);
            ncw.wcms.tinymce.setup();
            ncw.wcms.componentlanguage.linkSelect();
            ncw.wcms.componentlanguage.loadOptions();
        }
    );     
};

/**
 * called when component is saves
 */
ncw.wcms.componentlanguage.save = function () {
    tinyMCE.triggerSave();
};


/**
 * Reload
 */
ncw.wcms.componentlanguage.reload = function (action) {
    if (action == 'new' || action == 'delete') {
        ncw.wcms.component.loadEdit(ncw.wcms.component.componentId);
    }
    if (true == ncw.wcms.window_mode
            && (action == 'new' || action == 'save' || action == 'delete')     
         ) {
         ncw.wcms.component.siteReload();
     }    
};

/**
 * load the component language
 * @param element
 * @param translation_id
 * @param language_code
 */
ncw.wcms.componentlanguage.loadInside = function (element, translation_id, language_code) {
    ncw.wcms.tinymce.languageCode = language_code;
    element.html('').append('<img class="ncw-throbber-big" src="' + ncw.image('throbber_big.gif" />'))
        .load(
            ncw.url('/wcms/componentlanguage/edit/' + translation_id + '/1'),
            null,
            function () {
                $('.ncw-throbber-big').remove();
                $('.ncw-language-flag').attr('src', ncw.image('country_flags/' + language_code + '.gif'));
                ncw.layout.resize.section.formElements($('.ncw-right').width());  
                ncw.wcms.componentlanguage.loadOptions();
            }
        );     
};

/**
 * Load options
 */
ncw.wcms.componentlanguage.loadOptions = function () {
    ncw.wcms.tinymce.setup();
    ncw.wcms.componentlanguage.linkSelect();    
    $('.ncw-choose-file').click(
        function () {
           ncw.wcms.componentlanguage.fileCount = $(this).attr('rel');
           if (typeof(ncw.dialogs.fileSearch) == 'undefined') {
               $.getScript(
                   ncw.url('files/file/searchDialog.js'),
                   function (data) {
                       ncw.dialogs.fileSearch.show(ncw.wcms.componentlanguage.setFile);
                   }
               );
           } else {
               ncw.dialogs.fileSearch.show(ncw.wcms.componentlanguage.setFile);
           }
        }
    );
};

ncw.wcms.componentlanguage.fileElement = null;

/**
 * Sets the file
 */
ncw.wcms.componentlanguage.setFile = function (file) {
    $('#componentfile_' + ncw.wcms.componentlanguage.fileCount + '_file_id').val(file.id);
    $('#ncw-componentfile-' + ncw.wcms.componentlanguage.fileCount).attr('src', file.preview);
};

ncw.wcms.componentlanguage.languageCode = '';

/**
 * link select
 */
ncw.wcms.componentlanguage.linkSelect = function () {
    $('.ncw-componentfile-internal').change(
        function () {
            var link = $(this).val();
            link_field_id = $(this).attr('id');
            link_field_id = link_field_id.split('_');
            link_field_id = 'componentfile_' + link_field_id[1] + '_link';
            if (link != '---') {
                $('#' + link_field_id).val(link);
            } else {
                $('#' + link_field_id).val('');
            }
        }
    );
};

/**
 * Loads new componentlanguage action
 * @param site_id
 * @param language_code
 */
ncw.wcms.componentlanguage.loadNew = function (component_id) {
    ncw.layout.loadView(
        ncw.url('/wcms/componentlanguage/new/' + component_id)
    );     
};