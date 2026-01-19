
/**
 * Initialize
 */
ncw.files.folder = function () {    
    ncw.files.folder.tree.load();
    /*if (ncw.files.folder.id == 1) {
        ncw.files.file.load();
    }*/
};

ncw.files.folder.id = 1;

/**
 * load edit folder action
 */
ncw.files.folder.loadEdit = function (folder_id) {
	if (typeof(folder_id) == 'undefined') {
		folder_id = ncw.files.folder.id;
	}
    ncw.layout.loadView(
        ncw.url('/files/folder/edit/' + folder_id)
    );
};

/**
 * load new folder action
 * @param parent_id
 */
ncw.files.folder.loadNew = function (parent_id) {
    if (typeof(parent_id) == 'undefined') {
        parent_id = 1;
    }    
    ncw.layout.loadView(
        ncw.url('/files/folder/new/' + parent_id)
    ); 
};

/**
 * Files reload
 */
ncw.files.folder.reload = function (action, data) {
    ncw.files.folder.tree.reload(action, data);
};

/**
 * folders tree
 */
ncw.files.folder.tree = function () {
    if ($(".ncw-folders-tree").length > 0) {
        $(".ncw-folders-tree").tree(
            {                
                ui : {
                    theme_name : "apple"
                },
                callback : {
                    onselect : function(NODE, TREE_OBJ) {
                        if (false == ncw.files.folder.tree.noSelect) {      
                        	if (true == $(NODE).hasClass('ncw-tree-ftp-folder')) {
                        		ncw.files.ftp.load($(NODE).attr('name'));
                        	} else {
	                            var id = $(NODE).attr("id");
	                            id = id.replace('ncw-folder-', '');                                
	                            ncw.files.folder.id = id;
	                            ncw.files.file.load();
                        	}
                        }
                        ncw.files.folder.tree.noSelect = false;
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
                            ncw_create_folder : {
                                label : T_("Create folder"), 
                                icon : ncw.image('icons/16px/folder_add.png'),
                                action : function (NODE, TREE_OBJ) {
                                    var id = $(NODE).attr('id').split('-')[2];
                                    ncw.files.folder.loadNew(id);
                                },
                                visible : function (NODE, TREE_OBJ) {
                                    if (true == $(NODE).hasClass('ncw-tree-ftp-folder')
                                        || false == ncw.files.folder.tree.permissions.folder.addNew
                                    ) {
                                        return -1;
                                    } 
                                    return 1; 
                                },
                                separator_before : true
                            },                             
                            ncw_edit_folder : {
                                label : T_("Edit folder"), 
                                icon : ncw.image('icons/16px/folder_edit.png'),
                                action  : function (NODE, TREE_OBJ) { 
                                    ncw.files.folder.tree.noSelect = true;
                                    var id = $(NODE).attr("id");
                                    $.tree.reference(".ncw-folders-tree").select_branch('#' + id);
                                    id = id.replace('ncw-folder-', '');
                                    ncw.files.folder.id = id;
                                    ncw.files.folder.loadEdit(id);
                                },
                                visible : function (NODE, TREE_OBJ) {
                                    if (false == ncw.files.folder.tree.permissions.folder.edit
                                        || $(NODE).attr("rel") == 'root'
                                        || true == $(NODE).hasClass('ncw-tree-ftp-folder')) {
                                        return -1;
                                    } 
                                    return 1; 
                                }
                            }                        
                        }
                    }/*,
                    cookie : {
                        prefix : "ncw-folders-tree"
                    }*/
                },
                types : {            	
                    "default" : {
                        deletable : false,
                        renameable : false,
                        draggable : false,
                        icon : { 
                            image : ncw.image('icons/16px/folder_image.png')
                        }                             
                    },
                    "ftp" : {
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
        $.tree.reference(".ncw-folders-tree").open_branch('#ncw-folder-1');
        $.tree.reference(".ncw-folders-tree").select_branch('#ncw-folder-1');        
        
        ncw.files.tree.resize();
        ncw.onResize(ncw.files.tree.resize);        
    }
};

ncw.files.folder.tree.noSelect = false;

/**
 * Load the tree
 * @param language_id
 * @param language_code
 */
ncw.files.folder.tree.load = function () {
    $('.ncw-folders-tree').load(
        ncw.url('/files/folder/tree'),
        null,
        function () {
            ncw.files.folder.tree();
        }
    );  
};

/**
 * reload files
 */
ncw.files.folder.tree.reload = function (action, data) {
    $('.ncw-folders-tree').load(
        ncw.url('/files/folder/tree'),
        null,
        function () {
            $.tree.reference(".ncw-folders-tree").refresh();
            if (action == 'new') {
            	$.tree.reference(".ncw-folders-tree").select_branch('#ncw-folder-' + data.folder_id);
            } else if (action == 'delete') {
            	$.tree.reference(".ncw-folders-tree").select_branch('#ncw-folder-1');
            }            
        }
    ); 	
};


ncw.files.ftp = {};

/**
 * load ftp files
 */
ncw.files.ftp.load = function (dir) {
    if ($(".ncw-folders-tree").length > 0) {
        ncw.layout.loadView(
            ncw.url('/files/ftp/all?d=' + dir),
            function () {                
                if (typeof ncw.files.file.load.callback == 'function') {
                    //ncw.files.file.load.callback();
                }
                //ncw.files.file.addNew();
            }
        ); 
    };
};