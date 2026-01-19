ncw.wcms.website_admin.components = {};

/**
 * Initialize
 */
ncw.wcms.website_admin.components  = function (callback) {
    $.getScript(
        ncw.BASE + '/modules/wcms/web/javascript/'
        + 'ncw.wcms.website_admin.components.window.js',
        function () {
            ncw.wcms.website_admin.components.sortable.add();
            
            if (typeof(callback) == 'function') {
                callback();
            }
            ncw.wcms.website_admin.components.editFileMetaButtons.add();
            ncw.wcms.website_admin.components.componentOptions();
            
            ncw.wcms.website_admin.components.window.newComponent.dialog.init();
            
            ncw.onResize(ncw.wcms.website_admin.components.onResize);
            
            ncw.wcms.website_admin.components.fileChoose();
        }
    );      
};

/**
 * On resize
 */
ncw.wcms.website_admin.components.onResize = function () {
    ncw.wcms.website_admin.components.editFileMetaButtons.remove();
    ncw.wcms.website_admin.components.moveButtons.remove();
    ncw.wcms.website_admin.components.editFileMetaButtons.add();
    ncw.wcms.website_admin.components.moveButtons.add();    
};

ncw.wcms.website_admin.components.editFileMetaButtons = {};

/**
 * Add Component
 * @param area_id
 * @param parent_id
 */
ncw.wcms.website_admin.components.addComponent = function (area_id, parent_id) {
    ncw.wcms.website_admin.components.window.newComponent.areaId = area_id;
    ncw.wcms.website_admin.components.window.newComponent.parentComponentId = parent_id;
    //ncw.wcms.website_admin.components.window.newComponent.dialog.show();
};

/**
 * Adds the file edit meta buttons
 */
ncw.wcms.website_admin.components.editFileMetaButtons.add = function () {
    if (true == ncw.wcms.website_admin.PERMISSIONS.Componentfile.editMeta) {
        $('.ncw-image-droppable').each(
        	function () {
	            $('body').append(
	                '<div id="' + $(this).attr('id') + '-toolbar" class="ncw-wysiwyg-toolbar ncw-edit-file-meta" ' 
	                + 'style="top: ' + $(this).offset()['top'] + 'px; left: ' + ($(this).offset()['left'] + 18) + 'px;">' 
	                + '<a value="\'' + $(this).attr('id') + '\'" class="ncw-edit-image-meta-dialog" rel="#ncw-meta-dialog"><img src="' + ncw.BASE + '/themes/default/web/images/icons/16px/pencil.png" alt="edit content" title="edit content" /></a>' 
	                + '</div>'
	                +'<div id="' + $(this).attr('id') + '-toolbar" class="ncw-wysiwyg-toolbar ncw-edit-file-meta" ' 
	                + 'style="top: ' + $(this).offset()['top'] + 'px; left: ' + $(this).offset()['left'] + 'px;">' 
	                + '<a value="' + $(this).attr('id') + '" class="ncw-edit-image-choose-file"><img src="' + ncw.BASE + '/themes/default/web/images/icons/16px/images.png" alt="choose file" title="choose file" /></a>' 
	                + '</div>'	                
	            );            
        	}
        );
        ncw.wcms.website_admin.files.metadata.dialog();
    };
};

/**
 * Removes the file edit meta buttons
 */
ncw.wcms.website_admin.components.editFileMetaButtons.remove = function () {
    $('.ncw-edit-file-meta').remove();
};

ncw.wcms.website_admin.components.componentOptions = function () {
    $(".ncw-toolbar-first").live(
        'click',
        function () {
            $(this).siblings("ul").fadeIn(300);
        }
    );
    $("body").click(
        function () {
            $(".ncw-toolbar ul").fadeOut(300);
        }
    );
    $(".ncw-toolbar ul, .ncw-toolbar-first").live(
        'click',
        function () {
            return false;
        }
    );
};

ncw.wcms.website_admin.components.moveButtons = {};

/**
 * Adds the move buttons
 */
ncw.wcms.website_admin.components.moveButtons.add = function () {
    if (true == ncw.wcms.website_admin.PERMISSIONS.Component.edit) {
        $(".ncw-sortable-item, .ncw-sortable-sub-item").each(function () {
            if ($(this).css('position') != 'relative') {
                var top = $(this).position()['top'];
                var left = $(this).position()['left'];
            } else {
                var top = 0;
                var left = 0;
            }
            var component_id = $(this).attr('id').split('-');
            component_id = component_id[0] + '-' + component_id[1] + '-' + component_id[2] + '-' + component_id[3];
            
            if ($('#' + component_id + '-move-top').length > 0) {
                top = top + parseInt($('#' + component_id + '-move-top').val());
            }
            if ($('#' + component_id + '-move-left').length > 0) {
                left = left + parseInt($('#' + component_id + '-move-left').val());
            }
            $(this).append(
                '<div class="ncw-sortable-item-handle" style="top: ' + top + 'px; left: ' + left + 'px;">' 
                + '<img src="' + ncw.BASE + '/themes/default/web/images/icons/16px/move.png" alt="move content" title="move content" />' 
                + '</div>'
            );
            
            /*top += 16;
            $(this).append(
                '<div class="ncw-toolbar" style="top: ' + top + 'px; left: ' + left + 'px;">'
                + '<a href="#" title="" class="ncw-toolbar-first">More</a>'
                + '<ul>'
                + '<li>'
                + '<img src="' + ncw.BASE + '/' + ncw.THEME_PATH + '/web/images/icons/16px/pencil.png" alt="edit component" title="edit component" />'
                + '</li>'
                + '<li>'
                + '<img src="' + ncw.BASE + '/' + ncw.THEME_PATH + '/web/images/icons/16px/delete.png" alt="delete component" title="delete component" />'
                + '</li>'
                + '<li>'
                + '<img src="' + ncw.BASE + '/' + ncw.THEME_PATH + '/web/images/icons/16px/world.png" alt="publish component" title="publish component" />'
                + '</li>'
                + '<li>'
                + '<img src="' + ncw.BASE + '/' + ncw.THEME_PATH + '/web/images/icons/16px/world_delete.png" alt="unpublish component" title="unpublish component" />'
                + '</li>'
                + '<li>'
                + '<img src="' + ncw.BASE + '/' + ncw.THEME_PATH + '/web/images/icons/16px/page_copy.png" alt="copy component" title="copy component" />'
                + '</li>'
                + '</ul>'
                + '</div>'
            );*/
        }); 
    };
};

/**
 * Removes the move buttons
 */
ncw.wcms.website_admin.components.moveButtons.remove = function () {
    $(".ncw-sortable-item-handle, .ncw-toolbar").remove();
};

/**
 * Shows the move buttons
 */
ncw.wcms.website_admin.components.moveButtons.show = function () {
    $(".ncw-sortable-item-handle, .ncw-toolbar").show();
};

/**
 * Hides the move buttons
 */
ncw.wcms.website_admin.components.moveButtons.hide = function () {
    $(".ncw-sortable-item-handle, .ncw-toolbar").hide();
};

ncw.wcms.website_admin.components.sortable = {};

/**
 * Make the content areas sortable and update the position
 * if they were changed.
 */
ncw.wcms.website_admin.components.sortable.add = function () {    
    if (true == ncw.wcms.website_admin.PERMISSIONS.Component.addNew) {
        $('.ncw-add-component-button').remove();
        $('.ncw-sortable').each(
            function () {
                var id = $(this).attr('id').split('-')[2];
                // add buttons
                var add_button = '<div class="ncw-add-component-button-container"><img class="ncw-add-component-button" src="' + ncw.image('icons/16px/add.png') + '"'
                    + ' onclick="ncw.wcms.website_admin.components.addComponent(' + id + ', 0)"'
                    + ' title="' + T_('Add new component within area') + ' ' + id + '" alt="' + T_('Add new component within area') + ' ' + id + '" /></div>';
                $(this).prepend(add_button);
                
                // 
                var content = false;
                $('#' + $(this).attr('id') + ' .ncw-sortable-item').each(
                    function () {
                        content = true;
                        return;
                    }
                );
                if (true == content) {
                    $(this).append(add_button);
                    //$('.ncw-area-nocontent-clear').remove();
                    //$(this).prepend('<div class="ncw-sortable-item ncw-empty-sortable">Empty</div>');
                }
            }
        );     
        $('.ncw-sortable-sub').each(
            function () {
                var ids = $(this).attr('id').split('-');
                // add buttons
                var add_button = '<div class="ncw-add-component-button-container"><img class="ncw-add-component-button" src="' + ncw.image('icons/16px/add.png') + '"'
                    + ' onclick="ncw.wcms.website_admin.components.addComponent(' + ids[2] + ', ' + ids[3] + ')"'
                    + ' title="' + T_('Add new component within area') + ' ' + ids[2] + '" alt="' + T_('Add new component within area') + ' ' + ids[2] + '" /></div>';
                $(this).prepend(add_button);
                
                //                 
                var content = false;           
                $('#' + $(this).attr('id') + ' .ncw-sortable-sub-item').each(
                    function () {
                        content = true;
                        return;
                    }
                );
                if (true == content) {     
                    $(this).append(add_button);          
                    //$('.ncw-area-nocontent-clear').remove();
                    //$(this).append('<div class="ncw-sortable-sub-item ncw-empty-sortable">Empty</div>')
                }
            }
        );
    };
    
    ncw.wcms.website_admin.components.moveButtons.add();
    
    $(".ncw-sortable").sortable(
        {
            items: '.ncw-sortable-item',
            handle: '.ncw-sortable-item-handle',
            tolerance: 'pointer',
            start: function (event, ui) {
                ncw.wcms.website_admin.wysiwyg.editButtons.remove(); 
                ncw.wcms.website_admin.components.editFileMetaButtons.remove();
                ncw.wcms.website_admin.components.moveButtons.hide();
                ncw.wcms.website_admin.components.fileDroppable.remove();
            },
            stop: function (event, ui) {
                ncw.wcms.website_admin.wysiwyg.editButtons.add();   
                ncw.wcms.website_admin.components.editFileMetaButtons.add();
                ncw.wcms.website_admin.components.moveButtons.remove()
                ncw.wcms.website_admin.components.moveButtons.add();
                ncw.wcms.website_admin.components.fileDroppable.add();
            },
            update: function(event, ui) {
                var parent = this;
                $(parent).sortable('disable');
                var area = $(parent).attr('id');
                area_id = area.split('-');
                area_id = area_id[2];
                var ids = {};
                var arr_ids = new Array();
                var count = 0;
                $("#" + area + " .ncw-sortable-item").each(
                    function () {
                        id = $(this).attr('id');
                        id = id.split('-');
                        if (id[1] == area_id && jQuery.inArray(id[3], arr_ids) == -1) {
                            ids['data[Component][' + count + '][id]'] = id[3];
                            arr_ids.push(id[3]);
                            ++count;
                        }
                    }
                );
                $.post(
                    ncw.url('/wcms/component/sort/'), 
                    ids, 
                    function(data, textStatus) {
                        $(parent).sortable('enable');
                    }, 
                    "json"
                );
            }
        }
    );
    $(".ncw-sortable-sub").sortable(
        {
            items: '.ncw-sortable-sub-item',
            handle: '.ncw-sortable-item-handle',
            tolerance: 'pointer',
            start: function (event, ui) {
                ncw.wcms.website_admin.wysiwyg.editButtons.remove(); 
                ncw.wcms.website_admin.components.editFileMetaButtons.remove();
                ncw.wcms.website_admin.components.moveButtons.hide();
                ncw.wcms.website_admin.components.fileDroppable.remove();
            },
            stop: function (event, ui) {
                ncw.wcms.website_admin.wysiwyg.editButtons.add();   
                ncw.wcms.website_admin.components.editFileMetaButtons.add();
                ncw.wcms.website_admin.components.moveButtons.remove()
                ncw.wcms.website_admin.components.moveButtons.add();
                ncw.wcms.website_admin.components.fileDroppable.add();
            },
            update: function(event, ui) {
                var parent = this;
                $(parent).sortable('disable');
                var area = $(parent).attr('id');
                area_id = area.split('-');
                area_id = area_id[2];
                var ids = {};
                var arr_ids = new Array();
                var count = 0;
                $("#" + area + " .ncw-sortable-sub-item").each(
                    function () {
                        id = $(this).attr('id');
                        id = id.split('-');
                        if (id[1] == area_id && jQuery.inArray(id[3], arr_ids) == -1) {
                            ids['data[Component][' + count + '][id]'] = id[3];
                            arr_ids.push(id[3]);
                            ++count;
                        }
                    }
                );
                $.post(
                    ncw.url('/wcms/component/sort/'), 
                    ids, 
                    function(data, textStatus) {
                        $(parent).sortable('enable');
                    }, 
                    "json"
                );
            }
        }
    );
};

/**
 * Removes the sortable 
 */
ncw.wcms.website_admin.components.sortable.remove = function () {
    $('.ncw-sortable').sortable('destroy');
};

ncw.wcms.website_admin.components.fileDroppable = {};

/**
 * Make file from components droppable
 */
ncw.wcms.website_admin.components.fileDroppable.add = function () {
   $(".ncw-image-droppable").droppable(
       {
           accept: '.ncw-image-draggable',
           tolerance: 'pointer',
           activate: function () {
               $(this).after('<div class="ncw-drop-here" style="top: ' 
                   + $(this).position()['top'] +'px; left: ' 
                   + $(this).position()['left'] + 'px;">Drop here!</div>');
           },
           deactivate: function () {
               $('.ncw-drop-here').remove();
           },      
           drop: function(event, ui) {
               var image = this;
               var content = $(ui.draggable).attr('src');
               content = content.split('/');
               content = content[content.length-1].split('.');
               ending = content[1];
               content = content[0].split('_');
               content.pop();
               var image_id = content.pop();
               var id = $(this).attr('id');
               id = id.split('-');
               $.post(
                   ncw.url(
                       '/wcms/componentlanguage/saveContent/'
                       + id[5] + '/' + id[6]
                   ), 
                   {'data[Componentcontent][content]': image_id}, 
                   function(data, textStatus) {
                       content = content.join('_') + '_' + image_id;
                       if (id[1] == 0) {
                           $(image).attr('src', ncw.BASE + '/assets/files/thumbnails/' + content + '_96.' + ending);
                       } else {
                           $(image).attr('src', ncw.BASE + '/assets/files/uploads/' + content + '.' + ending);
                       }
                   },
                   "json"
               );
           }
       }
   );
};

/**
 * Removes the file droppable
 */
ncw.wcms.website_admin.components.fileDroppable.remove = function () {
    $(".ncw-image-droppable").droppable('destroy');
};

ncw.wcms.website_admin.components.fileChoose = function () {
	$('.ncw-edit-image-choose-file').live(
		'click',
	    function () {
	    	
	    	var image = this;
	    	
	    	var setFile = function (file) {
                var id = $(image).attr('value');
                var image_id = id;
               	id = id.split('-');	    		
			    $.post(
				    ncw.url(
				            '/wcms/componentlanguage/saveContent/'
				        + id[5] + '/' + id[6]
				    ), 
				    { 'data[Componentcontent][content]': file.id }, 
				    function(data, textStatus) {
				    	$('#' + image_id).attr('src', file.file);
				    },
				    "json"
				);	    		
	    	}
	    	
	    	if (typeof(ncw.dialogs.fileSearch) == 'undefined') {
	        	$.getScript(
	           		ncw.url('files/file/searchDialog.js'),
	                function (data) {
	                    ncw.dialogs.fileSearch.show(setFile);
	                }
	            );
	        } else {
	            ncw.dialogs.fileSearch.show(setFile);
	        }
	    }
	);	
}
