/**
 * Initialize
 * @param site_id
 */
ncw.wcms.site = function (site_id) {
    if (true == ncw.wcms.site.tree.permissions.site.edit) {
        ncw.wcms.site.siteId = site_id;
        ncw.wcms.site.load();
    }
};

/**
 * The site id
 */
ncw.wcms.site.siteId = 0;

/**
 * load site
 */
ncw.wcms.site.load = function () {
    ncw.layout.loadView(
        ncw.url('/wcms/site/edit/' + ncw.wcms.site.siteId),
        function () {
            ncw.wcms.site.copy();
            ncw.wcms.site.privateGroups();
            ncw.wcms.site.actionListeners();
        }
    );     
};

/**
 * reload site structure
 * @param method
 */
ncw.wcms.site.reload = function (method) {
    ncw.wcms.site.tree.reload(method);
};

/**
 * loads new site action
 * @param parent_id
 */
ncw.wcms.site.loadNew = function (parent_id) {
    if (typeof(parent_id) == 'undefined') {
        parent_id = 1;
    }
    ncw.layout.loadView(
        ncw.url('/wcms/site/new/' + parent_id),
        function () {
            ncw.wcms.site.addNew();
            ncw.wcms.site.actionListeners.sitetype();
        }
    );
};

/**
 * Loads a site translation
 * @þaram translation_id
 */
ncw.wcms.site.loadTranslation = function (translation_id) {
    if (true == ncw.wcms.site.tree.permissions.sitelanguage.edit) {
        ncw.wcms.sitelanguage(translation_id);
    }
};

/**
 * load components
 * @param site_id
 */
ncw.wcms.site.loadComponents = function (site_id) {
    ncw.wcms.components(site_id);
};

/**
 * new site
 */
ncw.wcms.site.addNew = function () {
    
    ncw.wcms.site.tree.siteId = 0;
    ncw.wcms.site.tree.translationId = 0;

    if ($('#ncw-site-meta-name').length > 0) {
        var name = '';
        $('#sitelanguage_name').keyup(
            function () {
                name = $('#sitelanguage_name').val();
                name = jQuery.trim(name);
                name = name.replace(/ /g, '-');
                name = name.replace(/Ä/g, 'ae');
                name = name.replace(/O/g, 'oe');
                name = name.replace(/Ü/g, 'ue');                
                name = name.replace(/ä/g, 'ae');
                name = name.replace(/ö/g, 'oe');
                name = name.replace(/ü/g, 'ue');
                name = name.replace(/ß/g, 'ss');            
                name = name.replace(/[^a-zA-Z- 0-9]+/g,'');
                name = name.toLowerCase();
                $('#ncw-site-meta-name').html(name);
                $('#site_name').val(name);
            }
        );
    };    
};

/**
 * private groups
 */
ncw.wcms.site.privateGroups = function () {   
    ncw.wcms.site.privateGroups.add();
};

/**
 * add private group
 */
ncw.wcms.site.privateGroups.add = function () {
    $('#ncw-add-private-group').click(
        function () {     
            var usergroup_id = $('#usergroup_usergroup_id').val();
            $.get(
                ncw.url(
                    '/wcms/site/addGroup/' + 
                    ncw.wcms.site.siteId + '/' + 
                    usergroup_id
                ), 
                null,
                function (data) {
                    var name = data.usergroup.name;
                    $('#ncw-private-groups').append(
                        '<tr id="ncw-added-private-group-' + usergroup_id + '">' + 
                        '<td>' + name  + '</td>' + 
                        '<td class="ncw-table-td-icons"><a href="javascript: ncw.wcms.site.privateGroups.remove(' + usergroup_id + ');"><img title="' + T_('Delete') + '" alt="' + T_('Delete') + '" src="' + ncw.image('icons/16px/delete.png') + '"/></td>' + 
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
 * remove private group
 * @param usergroup_id
 */
ncw.wcms.site.privateGroups.remove = function (usergroup_id) {
    $.get(
        ncw.url(
            '/wcms/site/removeGroup/' + 
            ncw.wcms.site.siteId + '/' + 
            usergroup_id
        ), 
        null,
        function (data) {
            $('#ncw-added-private-group-' + usergroup_id).remove();
            ncw.layout.table();
        },
        'json'
    ); 
};

/**
 * copies a sites
 */
ncw.wcms.site.copy = function () {
    if (true == ncw.wcms.site.tree.permissions.site.addNew) {
        $('.ncw-site-copy-trigger').click(
            function () {    
                var element = this;
                ncw.layout.statusMessage.setText(element, 'Copying...');
                ncw.layout.statusMessage.show(element);            
                ncw.wcms.copy.doit('site', $('#site_id').val(), element);
            }
        );
    }
};

/**
 * Notify search enginges that the site structure has changed.
 */
ncw.wcms.site.notfiySearchEngines = function () {
    ncw.layout.loadView(
        ncw.url('/wcms/site/notifySearchEngines')
    );
};

/**
 * ActionListeners
 */
ncw.wcms.site.actionListeners = function () {
    ncw.layout.dateTimePicker();
    ncw.wcms.site.actionListeners.sitetype();
    
    // site parent id change listeners
    if ($('#site_parent_id').length > 0) {
	    $('#site_parent_id').change(
	        function () {
    	        if ($('#site_parent_id').val() != $('#site_current_parent_id').val()) {
    	            $('#site_position').removeAttr("enabled");
    	            $('#site_position').attr("disabled", "disabled");
    	        } else {
    	            $('#site_position').removeAttr("disabled");
    	            $('#site_position').attr("enabled", "enabled"); 
    	        }
	        }
	    );
    };
    // site position change listeners
    if ($('#site_position').length > 0) {
	    $('#site_position').change(
	        function () {
    	        if ($('#site_position').val() != $('#site_current_position').val()) {
    	            $('#site_parent_id').removeAttr("enabled");
    	            $('#site_parent_id').attr("disabled", "disabled");
    	        } else {
    	            $('#site_parent_id').removeAttr("disabled");
    	            $('#site_parent_id').attr("enabled", "enabled"); 
    	        }
	        }
	    );
    };  
};

/**
 * sitetype change
 */
ncw.wcms.site.actionListeners.sitetype = function () {
    if ($('#site_sitetype_id').length > 0) {
        $('#site_sitetype_id').change(
            function () {
                $.get(
                    ncw.url('/wcms/sitetemplate/read/') 
                        + $(this).val(),
                    null,
                    function (data) {
                        $('#site_sitetemplate_id').html('');
                        var code = '';
                        $.each(
                            data,
                            function (sitetemplate_id, sitetemplate_name) {
                                code = code + '<option value="' + sitetemplate_id + '">' + sitetemplate_name + '</option>';
                            }
                        );
                        $('#site_sitetemplate_id').append(code);
                    },
                    'json'
                );                
            }
        );
    }; 
};