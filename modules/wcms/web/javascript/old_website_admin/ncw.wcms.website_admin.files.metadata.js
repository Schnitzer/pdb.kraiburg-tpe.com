ncw.wcms.website_admin.files = {};
ncw.wcms.website_admin.files.metadata = function () {
    
};

/**
 * Initializes the components window
 * 
 * @param string id
 */
ncw.wcms.website_admin.files.metadata = function () {
    $('body').append('<div id="ncw-meta-dialog" class="ncw-form-dialog"></div>');
    ncw.wcms.website_admin.files.metadata.dialog();
};

/**
 * metadata dialog
 */
ncw.wcms.website_admin.files.metadata.dialog = function () {
    if ($(".ncw-edit-image-meta-dialog").length > 0) {
        $(".ncw-edit-image-meta-dialog").overlay(
            {  
                mask: { 
                    color: '#666', 
                    loadSpeed: 200, 
                    opacity: 0.9,
                    zIndex: 990000
                }, 
                onBeforeLoad: function() {
                    ncw.wcms.website_admin.files.metadata.overlay = this;
                    var image_id = this.getTrigger().attr("value").split('-');
                    image_id = image_id[5];                
                    $("#ncw-meta-dialog").load(
                        ncw.url(
                            '/wcms/componentfile/editMeta/'       
                                + image_id + '/' 
                                + ncw.wcms.website_admin.languageCode
                        ),
                        null,
                        function () {         
                            $('#componentfile_alt').focus();
                            $('#componentfile_internal').change(function () {
                                var link = $(this).val();
                                if (link != '---') {
                                    $('#componentfile_link').val(link);
                                } else {
                                    $('#componentfile_link').val('');
                                }
                            });                 
                        }
                    );
                }
            }
        );
    }
};

/**
 * the current overlay
 */
ncw.wcms.website_admin.files.metadata.overlay = null;

/**
 * Save metadata
 * 
 */
ncw.wcms.website_admin.files.metadata.save = function () {
    var data = {
            'data[Componentfile][alt]': $('#componentfile_alt').val(),
            'data[Componentfile][title]': $('#componentfile_title').val(),
            'data[Componentfile][link]': $('#componentfile_link').val(),                  
            'data[Componentfile][target]': $('#componentfile_target').val()                       
    };
    $.post(
        ncw.url(
            '/wcms/componentfile/saveMeta/' 
            + $('#componentfile_id').val()
        ), 
        data, 
        function() {
            ncw.wcms.website_admin.files.metadata.close();
        }, 
        "json"
    );      
};

/**
 * close metadata dialog
 */
ncw.wcms.website_admin.files.metadata.close = function () {
    if (ncw.wcms.website_admin.files.metadata.overlay != null) {
        ncw.wcms.website_admin.files.metadata.overlay.close();
    }
};
