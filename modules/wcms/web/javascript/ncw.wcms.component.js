/**
 * Initialize
 * @param component_id
 */
ncw.wcms.component = function (site_id) {   
    if (true == ncw.wcms.site.tree.permissions.component.all) {
        ncw.wcms.component.siteId = site_id;
        ncw.wcms.component.load();    
    }
};

ncw.wcms.component.siteId = 0;
ncw.wcms.component.componentId = 0;

/**
 * load
 */
ncw.wcms.component.load = function () {
    ncw.layout.loadView(
        ncw.url('/wcms/component/all/' + ncw.wcms.component.siteId)
    );       
};

/**
 * Component reload
 */
ncw.wcms.component.reload = function(action) {
    if (action == 'new' || action == 'delete' || action == 'copy') {
        ncw.wcms.component.load();
    }
    if (true == ncw.wcms.window_mode
       && (action == 'save' || action == 'delete' || action == 'copy')     
    ) {
        ncw.wcms.component.siteReload();
    }
};

/**
 * confirm the site reload
 */
ncw.wcms.component.siteReload = function () {
    ncw.layout.dialogs.confirm(
        T_('Reload'), 
        T_('The site was modified. Do you want to reload?'),
        function () {
            window.location.reload();
        }
    );
};

/**
 * load edic component
 */
ncw.wcms.component.loadEdit = function (component_id) {
    if (true == ncw.wcms.site.tree.permissions.component.edit) {
        ncw.wcms.component.componentId = component_id;
        ncw.layout.loadView(
            ncw.url('/wcms/component/edit/' + component_id),
            function () {
                ncw.layout.saveTriggerAjax.beforeSave('wcms/component', ncw.wcms.component.save);
                ncw.layout.dateTimePicker();
            }
        );  
    }
};

/**
 * called when component is saves
 */
ncw.wcms.component.save = function () {
    tinyMCE.triggerSave();
};

/**
 * Loads new component action
 * @param site_id
 * @param language_code
 */
ncw.wcms.component.loadNew = function (site_id) {
    if (true == ncw.wcms.site.tree.permissions.component.addNew) {
        //if (false == ncw.wcms.window_mode) {
            var url = ncw.url('/wcms/component/new/' + site_id);
            var callback = function () {
                ncw.wcms.component.siteId = site_id;
            }
        /*} else {
            var url = ncw.url('/wcms/component/windowNew/' + site_id);
            var callback = function () {
                ncw.wcms.component.siteId = site_id;
                ncw.wcms.website_admin.components.window.draggable();
            }        
            
        }*/
        ncw.layout.loadView(
            url,
            callback
        ); 
    }
};

/**
 * load site
 * @param site_id
 */
ncw.wcms.component.loadSite = function (site_id) {
    ncw.wcms.site(site_id);
};