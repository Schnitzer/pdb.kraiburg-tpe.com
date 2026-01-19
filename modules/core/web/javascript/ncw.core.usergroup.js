/**
 * usergroups
 */
ncw.core.usergroup = function () {
    ncw.onLoad(
    	function () {
    		ncw.core.usergroup.tree.load();   
    		ncw.core.usergroup.add();
    		ncw.core.user.chooseContact();
    	}
    );
};


ncw.core.usergroup.groupId = 1;

/**
 * folders tree
 */
ncw.core.usergroup.tree = function () {
    if ($(".ncw-groups-tree").length > 0) {
        $(".ncw-groups-tree").tree(
            {                
                ui : {
                    theme_name : "apple"
                },
                callback : {
                    onselect : function(NODE, TREE_OBJ) {
                        if (false == ncw.core.usergroup.tree.noSelect) {      
                            var id = $(NODE).attr("id");
                            id = id.replace('ncw-group-', '');                                
                            ncw.core.usergroup.groupId = id;
                            ncw.core.user.load();
                        }
                        ncw.core.usergroup.tree.noSelect = false;
                    }  
                },            
                plugins : {
                    contextmenu: {
                        items : {
                            create : false,
                            rename : false,
                            remove : false,
                            ncw_create_folder : {
                                label : T_("Create usergroup"), 
                                icon : ncw.image('icons/16px/folder_add.png'),
                                action : function (NODE, TREE_OBJ) {
                                	ncw.core.usergroup.tree.noSelect = true;
                                    var id = $(NODE).attr("id");
                                    $.tree.focused().select_branch('#' + id);
                                    id = id.replace('ncw-group-', '');
                                    ncw.core.usergroup.loadNew(id);
                                }
                            },                             
                            ncw_edit_folder : {
                                label : T_("Edit usergroup"), 
                                icon : ncw.image('icons/16px/folder_edit.png'),
                                action  : function (NODE, TREE_OBJ) { 
                                    ncw.core.usergroup.tree.noSelect = true;
                                    var id = $(NODE).attr("id");
                                    $.tree.focused().select_branch('#' + id);
                                    id = id.replace('ncw-group-', '');
                                    ncw.core.usergroup.groupId = id;
                                    ncw.core.usergroup.loadEdit();
                                },
                                visible : function (NODE, TREE_OBJ) {
                                    if ($(NODE).attr("rel") == 'root') {
                                        return -1;
                                    } 
                                    return 1; 
                                }
                            }                        
                        }
                    }/*,
                    cookie : {
                        prefix : "ncw-groups-tree"
                    }*/
                },
                types : {
                    "default" : {
                        deletable : false,
                        renameable : false,
                        draggable : false,
                        icon : { 
                            image : ncw.image('icons/16px/folder_user.png')
                        }       
                    },
                    "root" : {
                        deletable : false,
                        renameable : false,
                        draggable : false,                    	
                        icon : { 
                            image : ncw.image('icons/16px/application_view_columns.png')
                        }                        
                    } 
                }                 
            }
        ); 
        
        $.tree.reference(".ncw-groups-tree").open_branch('#ncw-group-1');
        $.tree.reference(".ncw-groups-tree").select_branch('#ncw-group-1');      
    }
};

ncw.core.usergroup.tree.noSelect = false;

/**
 * Load the tree
 * @param language_id
 * @param language_code
 */
ncw.core.usergroup.tree.load = function () {
    var tsTimeStamp= new Date().getTime();

    $('.ncw-groups-tree').load(
        ncw.url('/core/usergroup/tree/?_=' + tsTimeStamp),
        null,
        function () {
            ncw.core.usergroup.tree();
        }
    );  
};

/**
 * reload files
 */
ncw.core.usergroup.tree.reload = function (action, data) {
    var tsTimeStamp= new Date().getTime();

    $('.ncw-groups-tree').load(
        ncw.url('/core/usergroup/tree/?_=' + tsTimeStamp),
        null,
        function () {
            $.tree.reference(".ncw-groups-tree").refresh();
            if (action == 'new') {
            	$.tree.reference(".ncw-groups-tree").select_branch('#ncw-group-' + data.group_id);
            } else if (action == 'delete') {
            	$.tree.reference(".ncw-groups-tree").select_branch('#ncw-group-1');
            }
        }
    );  
};

/**
 * reload site structure
 */
ncw.core.usergroup.reload = function (action, data) {
	ncw.core.usergroup.tree.reload(action, data);
};

/**
 * load edit folder action
 */
ncw.core.usergroup.loadEdit = function () {
    ncw.layout.loadView(
        ncw.url('/core/usergroup/edit/' + ncw.core.usergroup.groupId),
        function () {
        	
        	$(".ncw-permissions-tree").tree(
	            {                
	                ui : {
	                    theme_name : "apple"
	                },
	                types : {
	                    "default" : {
	                        deletable : false,
	                        renameable : false,
	                        draggable : false,
	                        icon : { 
	                            image : ncw.image('icons/16px/folder_key.png')
	                        }  
	                    }
	                }  		                
	            }
	        );
	        $.tree.reference(".ncw-permissions-tree").open_branch('#ncw-permission-1');

		    $('.ncw-usergroup-permission-add').click(
		    	function () {
		    		if (true == $(this).attr('checked')) {
		    			$(this).siblings('.ncw-usergroup-permission-radios').show();
		    		} else {
		    			$(this).siblings('.ncw-usergroup-permission-radios').hide();
		    		}
		    	}    	
		    );      	
        }
    );
};

/**
 * load new folder action
 * @param parent_id
 */
ncw.core.usergroup.loadNew = function (usergroup_id) {    
    ncw.layout.loadView(
        ncw.url('/core/usergroup/new/' + usergroup_id)
    ); 
};

/**
 * add usergroup
 */
ncw.core.usergroup.add = function () {
    $('#ncw-add-usergroup').live(
    	'click',
        function () {     
            var usergroup_id = $('#user_usergroup').val();
            $.get(
                ncw.url(
                    '/core/user/addUsergroup/' + 
                    $('#user_id').val() + '/' + 
                    usergroup_id
                ), 
                null,
                function (data) {
                    var name = data.usergroup.name;
                    $('#ncw-usergroups').append(
                        '<tr id="ncw-added-usergroup-' + usergroup_id + '">' + 
                        '<td>' + name  + '</td>' + 
                        '<td class="ncw-table-td-icons"><a href="javascript: ncw.core.usergroup.remove(' + usergroup_id + ');"><img title="delete" alt="delete" src="' + ncw.image('icons/16px/cross.png') + '"/></td>' + 
                        '</tr>'
                    );
                    ncw.layout.table();
                },
                'json'
            );   
        }
    );        
};

/**
 * remove usergroup
 * @param usergroup_id
 */
ncw.core.usergroup.remove = function (usergroup_id) {
    ncw.layout.dialogs.confirm(
        T_('Remove group'),
        T_('Do you really want to remove this group?'),
        function () {    
            $.get(
                ncw.url(
                    '/core/user/removeUsergroup/' + 
                    usergroup_id
                ), 
                null,
                function (data) {
                    $('#ncw-added-usergroup-' + usergroup_id).remove();
                    ncw.layout.table();
                },
                'json'
            );
        }
    );
};

ncw.core.usergroup();
ncw.core.user();