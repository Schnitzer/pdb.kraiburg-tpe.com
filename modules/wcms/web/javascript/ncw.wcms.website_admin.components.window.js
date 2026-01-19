ncw.wcms.website_admin.components.window = {};

ncw.wcms.website_admin.components.window.newComponent = {};

ncw.wcms.website_admin.components.window.newComponent.parentComponentId = 0;
ncw.wcms.website_admin.components.window.newComponent.areaId = 0;
ncw.wcms.website_admin.components.window.newComponent.sub = false;
 
ncw.wcms.website_admin.components.window.newComponent.dialog = {};

/**
 * Initializes the components window
 * 
 * @param string id
 */
ncw.wcms.website_admin.components.window.newComponent.dialog.init = function () {
    $('#ncw-new-component-dialog').remove();
    $('body').append('<div id="ncw-new-component-dialog" class="ncw-form-dialog"></div>');  
    
    if ($(".ncw-add-component-button").length > 0) {
        $(".ncw-add-component-button").overlay(
            {  
                mask: { 
                    color: '#666', 
                    loadSpeed: 200, 
                    opacity: 0.9,
                    zIndex: 990000
                }, 
                target: '#ncw-new-component-dialog',
                onBeforeLoad: function() {       
                    ncw.wcms.website_admin.components.window.newComponent.dialog.overlay = this;
                    $("#ncw-new-component-dialog").load(
                        ncw.url(
                            '/wcms/component/new/'
                                + ncw.wcms.website_admin.siteId + '/1'
                        ),
                        null,
                        function () {
                            $('#component_name').focus();
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
ncw.wcms.website_admin.components.window.newComponent.dialog.overlay = null;

/**
 * Saves the new added component
 */
ncw.wcms.website_admin.components.window.newComponent.save = function () {
    var site_id = $('#component_site_id').val();
    var name = $('#component_name').val();
    var componenttemplate_id = $('#component_componenttemplate_id').val();
    var parent_id = $('#component_parent_id').val();
    //var area = $('#component_area').val();
    var num = $('#component_num').val();

    if (name != '') {               
        var data = {
            'data[Component][name]': name,
            //'data[Component][site_id]': ncw.wcms.website_admin.siteId,
            'data[Component][site_id]': site_id,
            'data[Component][language_id]': ncw.wcms.website_admin.languageId,
            'data[Component][parent_id]': ncw.wcms.website_admin.components.window.newComponent.parentComponentId,
            //'data[Component][parent_id]': parent_id,
            'data[Component][area]': ncw.wcms.website_admin.components.window.newComponent.areaId,
            //'data[Component][area]': area,
            'data[Component][componenttemplate_id]': componenttemplate_id,
            'data[Component][num]': num,
        };        
        $('.ncw-component-language:checked').each(
            function () {
                id = $(this).attr('id');
                id = id.split('_');
                id = id[2];
                data['data[Component][languages][' + id + ']'] = 1;
            }
        );
        $.post(
            ncw.url('/wcms/component/save/'), 
            data, 
            function(data) {
                if (true == data.return_value) {
                    window.location.reload();
                }
            }, 
            "json"
        );
    }
};

/**
 * close metadata dialog
 */
ncw.wcms.website_admin.components.window.newComponent.close = function () {
    if (ncw.wcms.website_admin.components.window.newComponent.dialog.overlay != null) {
        ncw.wcms.website_admin.components.window.newComponent.dialog.overlay.close();
    }
};