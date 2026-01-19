/**
 * JsTree
 * @param language_code
 */
ncw.wcms.site.tree = function (language_code) {
    if ($(".ncw-sites-tree").length > 0) {
        
        if (ncw.wcms.site.tree.languages_num > 1
            && language_code == 'sitecontainer'
        ) {
            var image_url = ncw.image('icons/16px/sitemap.png');
        } else {
            var image_url = ncw.image('country_flags/' + language_code + '.gif');
        }         
        
        $(".ncw-sites-tree").tree(
            {
                rules : {
                    valid_children : [ "root" ]
                },                    
                ui : {
                    theme_name : "apple"
                },
                callback : {
                    onselect : function (NODE, TREE_OBJ) {
                        if (false == ncw.wcms.site.tree.noSelect
                            && $(NODE).attr("rel") != 'root'
                            && 'ncw-not-translated' != $('#' + $(NODE).attr('id') + ' a').attr('rel')
                            && false != ncw.wcms.site.tree.permissions.sitelanguage.edit
                         ) {
                            var id = $(NODE).children().attr('id');
                            id = parseInt(id.replace('ncw-sitelanguage-', '')); 
                            if (id > 0
                                && ncw.wcms.site.tree.languages_num > 1
                            ) {
                                ncw.wcms.sitelanguage(id);
                            } else {
                                id = $(NODE).attr("id");
                                id = parseInt(id.replace('ncw-site-', ''));
                                ncw.wcms.site(id);
                            }
                        }
                    },
                    beforechange : function (NODE, TREE_OBJ) {
                        if ($(NODE).attr("rel") != 'root') {
                            return true;
                        }
                        return false;
                    },
                    beforeclose : function (NODE, TREE_OBJ) {
                        if ($(NODE).attr("rel") != 'root') {
                            return true;
                        }
                        return false;
                    },
                    onmove : function (NODE,REF_NODE,TYPE,TREE_OBJ,RB) {
                        ncw.layout.dialogs.confirm(
                            T_('Move'), 
                            T_('Do you really want to move this site?'),
                            function () {
                                var ref_id = $(REF_NODE).attr('id').split('-')[2];
                                var id = $(NODE).attr('id').split('-')[2];
                                $.post(
                                    ncw.url('/wcms/site/move/' + id),
                                    { 
                                        "data[Site][ref_id]" : ref_id,
                                        "data[Site][type]" : TYPE 
                                    },
                                    function (data) {
                                        if (true == data.return_value) {
                                            ncw.layout.dialogs.saveNotify(T_('Moved') + '!', T_('The site has been moved successfully'));
                                            ncw.wcms.site.tree.reload();
                                        } else {
                                            ncw.layout.dialogs.errorNotify();
                                            jQuery.tree.rollback(RB);
                                        }
                                        ncw.layout.confirmDialog.hide();
                                    },
                                    'json'
                                );   
                            },
                            function () {
                                jQuery.tree.rollback(RB);
                                ncw.layout.confirmDialog.hide();
                            }
                        );
                    },
                    onrename : function (NODE, TREE_OBJ, RB) { 
                        ncw.layout.dialogs.confirm(
                            T_('Rename'), 
                            T_('Do you really want to rename this site?'),
                            function () {                        
                                var new_name = TREE_OBJ.get_text(NODE);
                                var id = $(NODE).attr('id').split('-')[2];
                                $.post(
                                    ncw.url('/wcms/site/rename/' + id),
                                    { "data[Site][name]" : new_name },
                                    function (data) {
                                        if (true == data.return_value) {
                                            ncw.layout.dialogs.saveNotify(T_('Renamed') + '!', T_('The site has been renamed successfully'));
                                            ncw.wcms.site.tree.reload();
                                        } else {
                                            ncw.layout.dialogs.errorNotify();
                                            jQuery.tree.rollback(RB);
                                        }
                                        ncw.layout.confirmDialog.hide();
                                    },
                                    'json'
                                );
                            },
                            function () {
                                jQuery.tree.rollback(RB);
                                ncw.layout.confirmDialog.hide();
                            }
                        );                     
                    }
                },
                plugins : {
                    contextmenu: {
                        items : {
                            remove : false,
                            create : false,
                            rename : {
                                label   : T_("Rename"), 
                                icon    : "rename",
                                visible : function (NODE, TREE_OBJ) { 
                                    if ($(NODE).attr("rel") == 'root') { return -1; }
                                    if(NODE.length != 1) return false; 
                                    return TREE_OBJ.check("renameable", NODE); 
                                }, 
                                action  : function (NODE, TREE_OBJ) { 
                                    TREE_OBJ.rename(NODE); 
                                } 
                            },                            
                            ncw_create_site_container : {
                                label : T_("New site"), 
                                icon : ncw.image('icons/16px/package_add.png'),
                                action : function (NODE, TREE_OBJ) {
                                    var id = $(NODE).attr('id').split('-')[2];
                                    ncw.wcms.site.loadNew(id);
                                },
                                visible : function (NODE, TREE_OBJ) {
                                    if (false == ncw.wcms.site.tree.permissions.site.addNew
                                        || ncw.wcms.site.tree.languages_num < 2
                                    ) {
                                        return -1;
                                    } 
                                    return 1; 
                                },
                                separator_before : true
                            },   
                            ncw_create_site : {
                                label : T_("New site"), 
                                icon : ncw.image('icons/16px/page_add.png'),
                                action : function (NODE, TREE_OBJ) {
                                    var id = $(NODE).attr('id').split('-')[2];
                                    ncw.wcms.site.loadNew(id);
                                },
                                visible : function (NODE, TREE_OBJ) {
                                    if (false == ncw.wcms.site.tree.permissions.site.addNew
                                        || ncw.wcms.site.tree.languages_num > 1
                                    ) {
                                        return -1;
                                    } 
                                    return 1; 
                                },
                                separator_before : true
                            },                             
                            ncw_edit_site_container : {
                                label : T_("Edit site container"), 
                                icon : ncw.image('icons/16px/package.png'),
                                action : function (NODE, TREE_OBJ) { 
                                    ncw.wcms.site.tree.noSelect = true;
                                    var id = $(NODE).attr("id");
                                    $.tree.focused().select_branch('#' + id);
                                    id = id.replace('ncw-site-', '');
                                    ncw.wcms.site.tree.switchLanguage(0, 'sitecontainer');
                                    ncw.wcms.site(id);  
                                    ncw.wcms.site.tree.noSelect = false;
                                },
                                visible : function (NODE, TREE_OBJ) {
                                    if ($(NODE).attr("rel") == 'root'
                                        || ncw.wcms.site.tree.languages_num < 2
                                        || 'sitecontainer' == $('#' + $(NODE).attr('id') + ' a').attr('lang')
                                        || false == ncw.wcms.site.tree.permissions.site.edit
                                    ) {
                                        return -1;
                                    } 
                                    return 1; 
                                }
                            },        
                            /*ncw_copy_site: {
                                label : T_("Copy site"),  
                                icon : ncw.image('icons/16px/page_copy.png'),
                                action  : function (NODE, TREE_OBJ) { 
                                    ncw.wcms.site.tree.noSelect = true;
                                    var id = $(NODE).attr("id");
                                    $.tree.focused().select_branch('#' + id);
                                    id = id.replace('ncw-site-', '');
                                    ncw.wcms.copy.doit(null, 'site', id);  
                                    ncw.wcms.site.tree.noSelect = false;
                                },
                                visible : function (NODE, TREE_OBJ) { 
                                    if ($(NODE).attr("rel") != 'root'
                                        && (ncw.wcms.site.tree.languages_num == 1
                                        || 'sitecontainer' == $('#' + $(NODE).attr('id') + ' a').attr('lang'))
                                        && true == ncw.wcms.site.tree.permissions.site.addNew
                                    ) {
                                        return 1;
                                    } 
                                    return -1; 
                                }
                            },*/                             
                            ncw_show_components : {
                                label : T_("Show components"),  
                                icon : ncw.image('icons/16px/bricks.png'),
                                action  : function (NODE, TREE_OBJ) { 
                                    ncw.wcms.site.tree.noSelect = true;
                                    var id = $(NODE).attr("id");
                                    $.tree.focused().select_branch('#' + id);
                                    id = id.replace('ncw-site-', '');
                                    ncw.wcms.component(id);  
                                    ncw.wcms.site.tree.noSelect = false;
                                },
                                visible : function (NODE, TREE_OBJ) { 
                                    if ($(NODE).attr("rel") != 'root'
                                        && true == ncw.wcms.site.tree.permissions.component.all
                                    ) {
                                        return 1;
                                    } 
                                    return -1; 
                                },
                                separator_before : true
                            },
                            ncw_new_component : {
                                label : T_("New component"),  
                                icon : ncw.image('icons/16px/brick_add.png'),
                                action  : function (NODE, TREE_OBJ) { 
                                    ncw.wcms.site.tree.noSelect = true;
                                    var id = $(NODE).attr("id");
                                    $.tree.focused().select_branch('#' + id);
                                    id = id.replace('ncw-site-', '');
                                    ncw.wcms.component.loadNew(id);  
                                    ncw.wcms.site.tree.noSelect = false;
                                },
                                visible : function (NODE, TREE_OBJ) { 
                                    if ($(NODE).attr("rel") != 'root'
                                        && true == ncw.wcms.site.tree.permissions.component.addNew
                                    ) {
                                        return 1;
                                    } 
                                    return -1; 
                                }
                            },                                                       
                            ncw_edit_content : {
                                label : T_("Edit content"), 
                                icon : ncw.image('icons/16px/page_edit.png'),
                                action  : function (NODE, TREE_OBJ) { 
                                    var href = $(NODE).attr("name");
                                    window.location.href = href;
                                },
                                visible : function (NODE, TREE_OBJ) { 
                                    if ($(NODE).attr("rel") == 'root'
                                        || 'sitecontainer' == $('#' + $(NODE).attr('id') + ' a').attr('lang')
                                        || '' == $('#' + $(NODE).attr('id') + ' a').attr('id')
                                    ) {
                                        return -1;
                                    } 
                                    return 1; 
                                },
                                separator_before : true
                            },                        
                            ncw_new_translation : {
                                label : T_("New translation"), 
                                icon : ncw.image('icons/16px/page_add.png'),
                                action  : function (NODE, TREE_OBJ) { 
                                    ncw.wcms.site.tree.noSelect = true;
                                    var site_id = $(NODE).attr("id");
                                    $.tree.focused().select_branch('#' + site_id);
                                    site_id = site_id.replace('ncw-site-', '');
                                    ncw.wcms.sitelanguage.loadNew(site_id, $('#ncw-language_code').val());       
                                    ncw.wcms.site.tree.noSelect = false;
                                },
                                visible : function (NODE, TREE_OBJ) { 
                                    if ($(NODE).attr("rel") != 'root'
                                        && ncw.wcms.site.tree.languages_num > 1
                                        && 'ncw-not-translated' == $('#' + $(NODE).attr('id') + ' a').attr('rel') 
                                        && true == ncw.wcms.site.tree.permissions.sitelanguage.addNew
                                    ) {
                                        return 1;
                                    } 
                                    return -1; 
                                }
                            }                     
                        }
                    },
                    cookie : {
                        prefix : "ncw-sites-tree"
                    }
                },
                types : {
                    "default" : {
                        deletable : false,
                        createable : false,
                        icon : { 
                            image : ncw.image('icons/16px/page.png')
                        } 
                    },
                    "root" : {
                        renameable : false,
                        draggable : false,
                        icon : { 
                            image : image_url
                        }
                    },
                    "site" : {
                        icon : { 
                            image : ncw.image('icons/16px/page.png')
                        }                        
                    },
                    "redirect" : {
                        icon : { 
                            image : ncw.image('icons/16px/page_go.png')
                        }                        
                    },
                    "archive" : {
                        icon : { 
                            image : ncw.image('icons/16px/book.png')
                        }                        
                    },
                    "article" : {
                        icon : { 
                            image : ncw.image('icons/16px/page_red.png')
                        }                        
                    },
                    "formular" : {
                        icon : { 
                            image : ncw.image('icons/16px/application_form.png')
                        }                        
                    }                    
                }                 
            }
        ); 
        ncw.wcms.tree.resize();
        ncw.onResize(ncw.wcms.tree.resize);
    }
};

ncw.wcms.site.tree.languages_num = 1;
ncw.wcms.site.tree.noSelect = false;

/**
 * Switches the language of the tree
 * @param language_id
 * @param language_code
 */
ncw.wcms.site.tree.switchLanguage = function (language_id, language_code) {    
    if (ncw.wcms.site.tree.switchLanguage.languageId == language_id) {
        return false;
    }
    ncw.wcms.site.tree.switchLanguage.languageId = language_id;
    ncw.wcms.site.tree.switchLanguage.languageCode = language_code;
        
    ncw.wcms.site.tree.load();
};

ncw.wcms.site.tree.switchLanguage.languageId = 0;
ncw.wcms.site.tree.switchLanguage.languageCode = '';

/**
 * Load the tree
 * @param language_id
 * @param language_code
 */
ncw.wcms.site.tree.load = function () {
    var tsTimeStamp= new Date().getTime();
    
    $('.ncw-sites-tree').load(
        ncw.url('wcms/site/tree/' + ncw.wcms.site.tree.switchLanguage.languageId + '?_=' + tsTimeStamp),
        null,
        function () {
            ncw.wcms.site.tree(ncw.wcms.site.tree.switchLanguage.languageCode);
            
            if (typeof(ncw.wcms.site.tree.load.callback) == 'function') {
                ncw.wcms.site.tree.load.callback();
            }
        }
    );  
};

ncw.wcms.site.tree.load.callback = null;

/**
 * Reload the tree
 * @param method
 */
ncw.wcms.site.tree.reload = function (method) {
    ncw.wcms.site.tree.load();
    if (method == 'delete') {
        element = $('.ncw-tree-root ul li:first');
        if (element.length > 0) {
            $.tree.reference(".ncw-sites-tree").select_branch(
                element
            );
        }
    }
};

/**
 * Selects a site of the tree.
 * @param site_id
 */
ncw.wcms.site.tree.selectSite = function (site_id) {
    if (typeof($.tree.reference(".ncw-sites-tree").selected) == "undefined"
        || $.tree.reference(".ncw-sites-tree").selected.attr('id') != 'ncw-site-' + site_id
    ) {
        $.tree.reference(".ncw-sites-tree").select_branch(
            $('#ncw-site-' + site_id)
        );
    }
};

$(document).ready(
    function () {
        if ($(".ncw-sites-tree").length > 0) {
            ncw.wcms.site.tree.switchLanguage($('#ncw-language_id').val(), '' + $('#ncw-language_code').val() + '');
        }
    }
);