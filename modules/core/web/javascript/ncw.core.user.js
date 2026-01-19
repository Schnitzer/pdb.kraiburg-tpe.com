/**
 * users
 */
ncw.core.user = function () {

};

/**
 * load user
 */
ncw.core.user.load = function () {
    if ($(".ncw-groups-tree").length > 0) {
        ncw.layout.loadView(
            ncw.url('/core/user/all/' + ncw.core.usergroup.groupId),
            function () {                
                if (typeof ncw.core.user.load.callback == 'function') {
                    ncw.core.user.load.callback();
                }
            }
        ); 
    };
};

/**
 * search
 */
ncw.core.user.search = function () {
	var group = ncw.core.usergroup.groupId;
	if (group == 1) {
		group == 0;
	}
    ncw.layout.loadView(
        ncw.url(
            '/core/user/all/' + group
            + '?s=' + escape($('#user_search').val())
        ),
        function () {
            if (typeof ncw.core.user.load.callback == 'function') {
                ncw.core.user.load.callback();
            }
        }
    );     
};

/**
 * Load page
 */
ncw.core.user.loadPage = function () {
    ncw.layout.initView();
    if (typeof ncw.core.user.load.callback == 'function') {
        ncw.core.user.load.callback();
    }
};

ncw.core.user.load.callback = null;


/**
 * load edit folder action
 */
ncw.core.user.loadEdit = function (user_id) {
    ncw.layout.loadView(
        ncw.url('/core/user/edit/' + user_id)
    );
};

/**
 * load new folder action
 * @param parent_id
 */
ncw.core.user.loadNew = function () {    
    ncw.layout.loadView(
        ncw.url('/core/user/new/')
    ); 
};

/**
 * reload site structure
 */
ncw.core.user.reload = function (action) {
    if (action != 'save') {
    	ncw.core.usergroup.groupId = 1;
        ncw.core.user.load();
    }
};

/**
 * Choose contact
 */
ncw.core.user.chooseContact = function () {
    $('#contact_name').live(
    	'focus',
        function () {
            var callback = function (contact) {
                $('#user_contact_id').val(contact.id);
                if (contact.type == 'private') {
                    var name = contact.firstname + ' ' + contact.name;
                } else {
                    var name = contact.name;    
                }
                $('#contact_name').val(name);
            }
            
            if (typeof(ncw.dialogs.contactSearch) == 'undefined') {
                $.getScript(
                    ncw.url('contacts/contact/searchDialog.js'),
                    function (data) {
                    	ncw.dialogs.contactSearch.type = 1;
                        ncw.dialogs.contactSearch.show(callback);
                    }
                );
            } else {
            	ncw.dialogs.contactSearch.type = 1;
                ncw.dialogs.contactSearch.show(callback);
            }  
        }
    );
};