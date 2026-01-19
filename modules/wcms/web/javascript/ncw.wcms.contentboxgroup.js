
/**
 * Initialize
 */
ncw.wcms.contentboxgroup = function () {
    ncw.wcms.contentboxgroup.tree.load();
};

ncw.wcms.contentboxgroup.id = 1;

/**
 * load edit contentboxgroup action
 */
ncw.wcms.contentboxgroup.loadEdit = function (contentboxgroup_id) {
	if (typeof(contentboxgroup_id) == 'undefined') {
		contentboxgroup_id = ncw.wcms.contentboxgroup.id;
	}
    ncw.layout.loadView(
        ncw.url('/wcms/contentboxgroup/edit/' + contentboxgroup_id)
    );
};

/**
 * load new contentboxgroup action
 * @param parent_id
 */
ncw.wcms.contentboxgroup.loadNew = function (parent_id) {
    if (typeof(parent_id) == 'undefined') {
        parent_id = 1;
    }    
    ncw.layout.loadView(
        ncw.url('/wcms/contentboxgroup/new/' + parent_id)
    ); 
};

/**
 * Contentboxgroup reload
 */
ncw.wcms.contentboxgroup.reload = function (action, data) {
    ncw.wcms.contentboxgroup.tree.reload(action, data);
};

/**
 * contentboxgroups tree
 */
ncw.wcms.contentboxgroup.tree = function () {
    if ($(".ncw-contentboxgroup-tree").length > 0) {
        $(".ncw-contentboxgroup-tree").tree(
            {                
                ui : {
                    theme_name : "apple"
                },
                callback : {
                    onselect : function(NODE, TREE_OBJ) {
                        if (false == ncw.wcms.contentboxgroup.tree.noSelect) {
                           var id = $(NODE).attr("id");
                            id = id.replace('ncw-contentboxgroup-', '');
                            ncw.wcms.contentboxgroup.id = id;
                            ncw.wcms.contentbox.load();
                        }
                        ncw.wcms.contentboxgroup.tree.noSelect = false;
                    },
                    beforeclose : function (NODE, TREE_OBJ) {
                        if ($(NODE).attr("rel") != 'root') {
                            return true;
                        }
                        return false;
                    }   
                },            
                plugins : {
                    contextmenu: {
                        items : {
                            create : false,
                            rename : false,
                            remove : false,
                            ncw_create_contentboxgroup : {
                                label : T_("Create contentboxgroup"), 
                                icon : ncw.image('icons/16px/contentboxgroup_add.png'),
                                action : function (NODE, TREE_OBJ) {
                                    var id = $(NODE).attr('id').split('-')[2];
                                    ncw.wcms.contentboxgroup.loadNew(id);
                                },
                                visible : function (NODE, TREE_OBJ) {
                                    return 1; 
                                },
                                separator_before : true
                            },
                            ncw_edit_contentboxgroup : {
                                label : T_("Edit contentboxgroup"), 
                                icon : ncw.image('icons/16px/contentboxgroup_edit.png'),
                                action  : function (NODE, TREE_OBJ) { 
                                    ncw.wcms.contentboxgroup.tree.noSelect = true;
                                    var id = $(NODE).attr("id");
                                    $.tree.reference(".ncw-contentboxgroup-tree").select_branch('#' + id);
                                    id = id.replace('ncw-contentboxgroup-', '');
                                    ncw.wcms.contentboxgroup.id = id;
                                    ncw.wcms.contentboxgroup.loadEdit(id);
                                },
                                visible : function (NODE, TREE_OBJ) {
                                    return 1; 
                                }
                            }
                        }
                    }
                },
                types : {
                    "default" : {
                        deletable : false,
                        renameable : false,
                        draggable : false,
                        icon : { 
                            image : ncw.image('icons/16px/folder.png')
                        }
                    }
                }
            }
        ); 
        $.tree.reference(".ncw-contentboxgroup-tree").open_branch('#ncw-contentboxgroup-1');
        $.tree.reference(".ncw-contentboxgroup-tree").select_branch('#ncw-contentboxgroup-1');
    }
};

ncw.wcms.contentboxgroup.tree.noSelect = false;

/**
 * Load the tree
 * @param language_id
 * @param language_code
 */
ncw.wcms.contentboxgroup.tree.load = function () {
    var tsTimeStamp= new Date().getTime();

    $('.ncw-contentboxgroup-tree').load(
        ncw.url('/wcms/contentboxgroup/tree/?_=' + tsTimeStamp),
        null,
        function () {
            ncw.wcms.contentboxgroup.tree();
        }
    );  
};

/**
 * reload Contentboxgroup
 */
ncw.wcms.contentboxgroup.tree.reload = function (action, data) {
    var tsTimeStamp= new Date().getTime();

    $('.ncw-contentboxgroup-tree').load(
        ncw.url('/wcms/contentboxgroup/tree/?_=' + tsTimeStamp),
        null,
        function () {
            $.tree.reference(".ncw-contentboxgroup-tree").refresh();
            if (action == 'new') {
            	$.tree.reference(".ncw-contentboxgroup-tree").select_branch('#ncw-contentboxgroup-' + data.contentboxgroup_id);
            } else if (action == 'delete') {
            	$.tree.reference(".ncw-contentboxgroup-tree").select_branch('#ncw-contentboxgroup-1');
            }
        }
    ); 	
};
