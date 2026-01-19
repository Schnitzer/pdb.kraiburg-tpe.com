/**
 * contacts
 */
ncw.contacts.group = function (contact_id) {
	ncw.onLoad(
		function () {
			if (contact_id > 0) {
				ncw.contacts.group.tree.load(true);
				ncw.contacts.contact.loadContact(contact_id);
			} else {
				ncw.contacts.group.tree.load();
			}		
			ncw.contacts.contact.loadFavorites();
			ncw.contacts.contact.loadRecentlyOpened();
			ncw.contacts.contact.loadRecentlyAdded();
			
			ncw.contacts.contact.notes();
    		ncw.contacts.contact.files(); 
		    ncw.contacts.contact.addContactgroup();
		    ncw.contacts.contact.dataFields();
		    ncw.contacts.contact.dates();    	
		}		
	);
};

ncw.contacts.group.groupId = 1;

/**
 * reload group
 */
ncw.contacts.group.reload = function (action, data) {
    ncw.contacts.group.tree.reload(action, data); 
};

/**
 * load edit group action
 */
ncw.contacts.group.loadEdit = function () {
    ncw.layout.loadView(
        ncw.url('/contacts/group/edit/' + ncw.contacts.group.groupId)
    );
};

/**
 * load new group action
 * @param parent_id
 */
ncw.contacts.group.loadNew = function (group_id) {    
	if (typeof(group_id) == 'undefined') {
		group_id = 1;
	}
    ncw.layout.loadView(
        ncw.url('/contacts/group/new/' + group_id)
    ); 
};

/**
 * folders tree
 */
ncw.contacts.group.tree = function (no_select) {
    if ($(".ncw-groups-tree").length > 0) {
        $(".ncw-groups-tree").tree(
            {                
                ui : {
                    theme_name : "apple"
                },
                callback : {             	
                    onselect : function(NODE, TREE_OBJ) {  
                        if (false == ncw.contacts.group.tree.noSelect) {      
                            	var id = $(NODE).attr("id");
                            	id = id.replace('ncw-group-', '');                                
                            	ncw.contacts.group.groupId = id;
                            	if (id > 1) {
                            		ncw.contacts.contact.load();
                            	} else {
                            		ncw.contacts.contact.overview();
                            	}                         
                        }
                        ncw.contacts.group.tree.noSelect = false;
                    }  
                },            
                plugins : {
                    contextmenu: {
                        items : {
                            create : false,
                            rename : false,
                            remove : false,
                            ncw_create_folder : {
                                label : T_("New group"), 
                                icon : ncw.image('icons/16px/folder_add.png'),
                                action : function (NODE, TREE_OBJ) {
                                    var id = $(NODE).attr('id').split('-')[2];
                                    ncw.contacts.group.loadNew(id);
                                },
                                visible : function (NODE, TREE_OBJ) {
                                    if (false == ncw.contacts.group.tree.permissions.group.addNew) {
                                        return -1;
                                    } 
                                    return 1; 
                                }
                            },                             
                            ncw_edit_folder : {
                                label : T_("Edit group"), 
                                icon : ncw.image('icons/16px/folder_edit.png'),
                                action  : function (NODE, TREE_OBJ) { 
                                    ncw.contacts.group.tree.noSelect = true;
                                    var id = $(NODE).attr("id");
                                    $.tree.reference(".ncw-groups-tree").select_branch('#' + id);
                                    id = id.replace('ncw-group-', '');
                                    ncw.contacts.group.groupId = id;
                                    ncw.contacts.group.loadEdit();
                                },
                                visible : function (NODE, TREE_OBJ) {
                                    if ($(NODE).attr("rel") == 'root'
                                    	|| false == ncw.contacts.group.tree.permissions.group.edit
                                    ) {
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
        if (false == no_select) {   
        	$.tree.reference(".ncw-groups-tree").open_branch('#ncw-group-1');
        	$.tree.reference(".ncw-groups-tree").select_branch('#ncw-group-1');
        }
    }
};

ncw.contacts.group.tree.noSelect = false;
ncw.contacts.group.tree.contactsLoaded = false;

/**
 * Load the tree
 */
ncw.contacts.group.tree.load = function (no_select) {
	if (typeof(no_select) == 'undefined') {
		no_select = false;
	}
    var tsTimeStamp= new Date().getTime();

    $('.ncw-groups-tree').load(
        ncw.url('/contacts/group/tree/?_=' + tsTimeStamp),
        null,
        function () {
            ncw.contacts.group.tree(no_select);
        }
    );  
};

/**
 * reload files
 */
ncw.contacts.group.tree.reload = function (action, data) {
	var tsTimeStamp= new Date().getTime();
	$('.ncw-groups-tree').load(
        ncw.url('/contacts/group/tree/?_=' + tsTimeStamp),
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