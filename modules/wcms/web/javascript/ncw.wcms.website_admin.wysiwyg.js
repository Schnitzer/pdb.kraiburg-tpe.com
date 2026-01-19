/**
 * Initialize
 */
ncw.wcms.website_admin.wysiwyg = function () {   
    $('body').append(
        '<textarea id="ncw-wysiwyg-container" class="ncw-mce-advanced"></textarea>'
        + '<textarea id="ncw-wysiwyg-container-simple" class="ncw-mce-advanced"></textarea>'
    );
    
    ncw.wcms.website_admin.wysiwyg.editButtons.add();
    
    ncw.onResize(ncw.wcms.website_admin.wysiwyg.onResize);
};

/**
 * the current edited element
 */
ncw.wcms.website_admin.wysiwyg.currentElement = '';

/**
 * on resize
 */
ncw.wcms.website_admin.wysiwyg.onResize = function () {
    ncw.wcms.website_admin.wysiwyg.editButtons.remove();
    ncw.wcms.website_admin.wysiwyg.editButtons.add();
    
    if (ncw.wcms.website_admin.wysiwyg.currentElement != '') {
        ncw.wcms.website_admin.wysiwyg.setPosition(ncw.wcms.website_admin.wysiwyg.currentElement);
        ncw.wcms.website_admin.wysiwyg.saveAndCloseButtons.show(ncw.wcms.website_admin.wysiwyg.currentElement);
    } 
};

ncw.wcms.website_admin.wysiwyg.editButtons = {};

/**
 * Adds the edit button to each text field.
 */
ncw.wcms.website_admin.wysiwyg.editButtons.add = function () {
    var top = 0;
    var left = 0;
    $('.ncw-wysiwyg-website-admin').each(function () {
        top = 0;
        left = 0;
        if ($('#' + $(this).attr('id') + '-button-top').length > 0) {
            top = parseInt($('#' + $(this).attr('id') + '-button-top').val());
        }
        if ($('#' + $(this).attr('id') + '-button-left').length > 0) {
            left = parseInt($('#' + $(this).attr('id') + '-button-left').val());
        }
        $('body').append(
            '<div id="' + $(this).attr('id') + '-toolbar" class="ncw-wysiwyg-toolbar ncw-wysiwyg-edit" ' 
            + 'style="top: ' + ($(this).offset()['top'] + top) + 'px; left: ' + ($(this).offset()['left'] + left) + 'px;">' 
            + '<img onclick="ncw.wcms.website_admin.wysiwyg.edit(\'' + $(this).attr('id') + '\');" src="' + ncw.BASE + '/themes/default/web/images/icons/16px/pencil.png" alt="edit content" title="edit content" />' 
            + '</div>'
        );
    });
};

/**
 * Removes the edit button from each text field.
 */
ncw.wcms.website_admin.wysiwyg.editButtons.remove = function () {
    $('.ncw-wysiwyg-edit').remove();
};

ncw.wcms.website_admin.wysiwyg.saveAndCloseButtons = {};

/**
 * Adds the save and close buttons to the current edited text element
 */
ncw.wcms.website_admin.wysiwyg.saveAndCloseButtons.add = function () {
    $('body').append(
        '<div id="ncw-wysiwyg-options">' 
        + '<img id="ncw-wysiwyg-save" src="' + ncw.image('icons/16px/disk.png') + '" alt="' + T_('Save') + '" title="' + T_('Save') + '" />' 
        + '<br />'
        + '<img id="ncw-wysiwyg-close" src="' + ncw.image('icons/16px/cancel.png') + '" alt="' + T_('Close') + '" title="' + T_('Close') + '" />'
        + '</div>'
    );
    $('#ncw-wysiwyg-save').click(
        function () {
            ncw.wcms.website_admin.wysiwyg.save();
        }
    ); 
    $('#ncw-wysiwyg-close').click(
        function () {
            if (true == ncw.wcms.website_admin.wysiwyg.has_changed) {
                ncw.layout.dialogs.confirm(
                    T_("Save") + '?', 
                    T_("Do you want to save before the editor is closed?"),
                    function () {
                        ncw.wcms.website_admin.wysiwyg.save(true); 
                    },
                    function () {
                        ncw.wcms.website_admin.wysiwyg.stop(); 
                    }
                );
            } else {
                ncw.wcms.website_admin.wysiwyg.stop();
            }
        }
    );
};

/**
 * Removes the save and close buttons from the current edited text element
 */
ncw.wcms.website_admin.wysiwyg.saveAndCloseButtons.remove = function () {
    $('#ncw-wysiwyg-options').remove();
};

/**
 * Shows the save and close buttons of the current edited text element
 * 
 * @param int id the current edited text elemtent id
 */
ncw.wcms.website_admin.wysiwyg.saveAndCloseButtons.show = function (element_id) {
    var top = $('#' + element_id).offset()['top'];
    var left = $('#' + element_id).offset()['left'];
    
    var button_top = 0;
    var button_left = 0;
    if ($('#' + element_id + '-button-top').length > 0) {
        button_top = parseInt($('#' + element_id + '-button-top').val());
    }
    if ($('#' + element_id + '-button-left').length > 0) {
        button_left = parseInt($('#' + element_id + '-button-left').val());
    }   
    $('#ncw-wysiwyg-options').css('left', ((left + button_left) - 16) + 'px')
        .css('top', (top + button_top) + 'px')
        .show();  
};

/**
 * Start editing a text with the WYSIWYG editor. 
 *
 * @param id the id of the text field
 */
ncw.wcms.website_admin.wysiwyg.edit = function (element_id) {
    ncw.wcms.website_admin.wysiwyg.saveAndCloseButtons.add();
    ncw.wcms.website_admin.wysiwyg.stop();
    ncw.wcms.website_admin.wysiwyg.start(element_id);
};

/**
 * Save the content via ajax.
 * @param close_after_save
 */
ncw.wcms.website_admin.wysiwyg.save = function (close_after_save) {
    if (typeof(close_after_save) == 'undefined') {
        close_after_save = false;
    }
    
    // ncw.wcms.website_admin.wysiwyg.editButtons.remove();
    // ncw.wcms.website_admin.components.editFileMetaButtons.remove();
    if ($('#' + ncw.wcms.website_admin.wysiwyg.currentElement + '-mode').val() == 'simple') {
        var textarea_name = 'ncw-wysiwyg-container-simple';
    } else {
        var textarea_name = 'ncw-wysiwyg-container';   
    }   
    var ed = tinyMCE.get(textarea_name);
    ed.setProgressState(1)
    var content = ed.getContent();
    
    // remove tags
    if ($('#' + ncw.wcms.website_admin.wysiwyg.currentElement + '-remove-tags').length > 0) {
    	var remove_tags = $('#' + ncw.wcms.website_admin.wysiwyg.currentElement + '-remove-tags').val();
    	remove_tags = remove_tags.split(',');
    	var num = remove_tags.length;
    	for (var count = 0;count < num;++count) {
    		var tag = remove_tags[count];
    		var re = new RegExp('<'+tag+'[^><]*>|<.'+tag+'[^><]*>','g')
    		content = content.replace(re,'');
    	}
    }
    
    var current_element_id = ncw.wcms.website_admin.wysiwyg.currentElement.split('-');
    $.post(
        ncw.url(
            '/wcms/componentlanguage/saveContent/' + current_element_id[5] + '/' 
            + current_element_id[6]
        ), 
        {
            'data[Componentcontent][content]': content
        },
        function(data, textStatus) {
            $("#" + ncw.wcms.website_admin.wysiwyg.currentElement).html(content);
            // set the width and height
            ncw.wcms.website_admin.wysiwyg.setWYSIWYGProportions(ncw.wcms.website_admin.wysiwyg.currentElement, textarea_name);
            // ncw.wcms.website_admin.wysiwyg.editButtons.add();       
            // ncw.wcms.website_admin.components.editFileMetaButtons.add();
            $('#' + ncw.wcms.website_admin.wysiwyg.currentElement + '-toolbar').hide();
            ed.setProgressState(0);
            
            if (true == close_after_save) {
                ncw.wcms.website_admin.wysiwyg.stop();
            }
            ncw.wcms.website_admin.wysiwyg.has_changed = false;
        },
        "json"
    );
};

/***
 * Starts the wysiwyg editor
 * 
 * @param element_id
 */
ncw.wcms.website_admin.wysiwyg.start = function (element_id) {
    
    ncw.wcms.website_admin.wysiwyg.editButtons.remove();
    
    if(typeof beforeWYSIWYGStart == 'function') { 
        beforeWYSIWYGStart();
    };
    ncw.wcms.website_admin.components.sortable.remove();
    ncw.wcms.website_admin.components.moveButtons.remove();
    
    textarea_name = ncw.wcms.website_admin.wysiwyg.getMode(element_id);
    
    // hide the toolbar 
    $('#' + element_id + '-toolbar').hide();
    
    // cache the current id of the element
    ncw.wcms.website_admin.wysiwyg.currentElement = element_id;
    
    // show save button
    ncw.wcms.website_admin.wysiwyg.saveAndCloseButtons.show(element_id);    
    
    var text_element = $('#' + element_id);
    var background = $('#' + element_id + '-background').val();
    if (typeof(background) == 'undefined') {
        if ($('#' + element_id).css('background-image') != 'none'
            || $('#' + element_id).css('background-color') != 'transparent'
        ) {
            background = $('#' + element_id).css('background-image') + ' ' + $('#' + element_id).css('background-color');
        } else {
            background = '#fff';
        }
    }
    
    
    var html = text_element.html();
    $("#" + textarea_name).show().val(html);
    
    tinyMCE.execCommand('mceAddControl', true, textarea_name);
        
    // Set the position
    ncw.wcms.website_admin.wysiwyg.setPosition(element_id);
    
    // set the width and height
    ncw.wcms.website_admin.wysiwyg.setWYSIWYGProportions(element_id, textarea_name);
    
    // set the style
    $("#" + textarea_name + "_tbl").css('border', 'none')
        .css('background', 'transparent');
    
    $("#" + textarea_name + "_tbl .mceIframeContainer").css('border', 'none');  
    
    $("#" + textarea_name + "_ifr").css('padding', 0)
        .css('background', background);
    
    $("#" + textarea_name + "_ifr").contents().find("body")
        .css('background', background).css('height', '100%');
    
    $("#" + textarea_name + "_ifr").contents().find("body,body *")
        .css('font-family', text_element.css('font-family'))
        .css('line-height', text_element.css('line-height'))
        .css('font-size', text_element.css('font-size'))
        .css('font-weight', text_element.css('font-weight'))
        .css('color', text_element.css('color'));
    
    // Set the external toolbar z-index
    $('#' + textarea_name + '_external').css('z-index', '999'); 
};

/**
 * Sets the position of the wysiwyg editor
 * 
 * @param int the current edited text element id
 */
ncw.wcms.website_admin.wysiwyg.setPosition = function (element_id) {
    var top = $('#' + element_id).offset()['top'];
    var left = $('#' + element_id).offset()['left']; 

    if ($('#' + element_id + '-text-top').length > 0) {
        top = top + parseInt($('#' + element_id + '-text-top').val());
    } 
    if ($('#' + element_id + '-text-left').length > 0) {
        left = left + parseInt($('#' + element_id + '-text-left').val());
    }
    $("#" + ncw.wcms.website_admin.wysiwyg.getMode(element_id) + "_parent")
        .css("position", "absolute")
        .css('left', left)
        .css('top', top)
        .css('z-index', '99999');  
};

/**
 * Return the wysiwyg mode
 * 
 * @param int the current edited text element id
 * 
 * @return string
 */
ncw.wcms.website_admin.wysiwyg.getMode = function (element_id) {
    if ($('#' + element_id + '-mode').val() == 'simple') {
        var textarea_name = 'ncw-wysiwyg-container-simple';
    } else {
        var textarea_name = 'ncw-wysiwyg-container';   
    };
    return textarea_name;
};

/**
 * Sets the WYSIWYGs proportions
 * 
 * @param int
 * @param text_area_name
 */
ncw.wcms.website_admin.wysiwyg.setWYSIWYGProportions =  function (element_id, textarea_name) {
    var text_element = $('#' + element_id);
    var parent = text_element.parent();
    
    var height = 0;
    var width = 0;
    if ($('#' + element_id + '-height').length > 0) {
        height = $('#' + element_id + '-height').val();
    }
    if ($('#' + element_id + '-width').length > 0) {
        width = $('#' + element_id + '-width').val();
    }
    if (width == 0) {
        width = text_element.width();
        width = width + parseInt(text_element.css('padding-right')) + parseInt(text_element.css('padding-left'));
    }
    if (width == 0) {
        width = parent.width() - parseInt(parent.css('padding-right')) - parseInt(parent.css('padding-left'))
            - parseInt(text_element.css('margin-right')) - parseInt(text_element.css('margin-left'))
            - parseInt(text_element.css('border-right-width')) - parseInt(text_element.css('border-left-width'))
            - parseInt(text_element.css('padding-right')) - parseInt(text_element.css('padding-left'));
    }    
    if (height == 0) {
        height = text_element.height();
        height = height + parseInt(text_element.css('padding-top')) //+ parseInt(text_element.css('padding-bottom'));
    }
    if (height == 0) {
        height = parent.height() - parseInt(parent.css('padding-top')) - parseInt(parent.css('padding-bottom')) 
            - parseInt(text_element.css('margin-top')) - parseInt(text_element.css('margin-bottom'))
            - parseInt(text_element.css('border-top-width')) - parseInt(text_element.css('border-bottom-width'))
            - parseInt(text_element.css('padding-top')) - parseInt(text_element.css('padding-bottom'));
    }
    
    $("#" + textarea_name).css('width', width + 'px').css('height', height + 'px'); 
    
    $("#" + textarea_name + "_tbl").css('width', width + 'px').css('height', height + 'px');
    
    $("#" + textarea_name + "_ifr")//.css('margin', text_element.css('margin-top') + " " + text_element.css('margin-right') + " " + text_element.css('margin-bottom') + " " + text_element.css('margin-left'))
        .css('margin', 0)
        .css('padding', 0)
        .css('width', width + 'px').css('height', height + 'px');
    
    $("#" + textarea_name + "_ifr").contents().find("body").css('margin', 0)
        .css('padding', text_element.css('padding-top') + " " + text_element.css('padding-right') + " " + 0 + " " + text_element.css('padding-left'));  
};

/**
 * Stops the wysiwyg editor and saves the content to the original element
 */
ncw.wcms.website_admin.wysiwyg.stop = function () {
    if (ncw.wcms.website_admin.wysiwyg.currentElement != '') {
        $('#' + ncw.wcms.website_admin.wysiwyg.currentElement + '-toolbar').show();
        
        if ($('#' + ncw.wcms.website_admin.wysiwyg.currentElement + '-mode').val() == 'simple') {
            var textarea_name = 'ncw-wysiwyg-container-simple';
        } else {
            var textarea_name = 'ncw-wysiwyg-container';   
        }       
        
        var ed = tinyMCE.get(textarea_name);
        if (ed) {
            ed.getContent();
            ed.remove();
            $('#' + textarea_name).hide();
        }
        ncw.wcms.website_admin.wysiwyg.saveAndCloseButtons.remove();
        ncw.wcms.website_admin.wysiwyg.currentElement = '';
        
        ncw.wcms.website_admin.wysiwyg.has_changed = false;
    }
    ncw.wcms.website_admin.components.sortable.add();
    ncw.wcms.website_admin.wysiwyg.editButtons.add();   
    ncw.wcms.website_admin.components.window.newComponent.dialog.init();
};