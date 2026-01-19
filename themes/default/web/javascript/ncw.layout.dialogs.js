ncw.layout.dialogs = {};

/**
 * Alert dialog
 */
ncw.layout.dialogs.alert = function (head, body, callback_yes) {
    if ((typeof(body) == 'function'
        || typeof(body) == 'undefined')
        && typeof(head) != 'function'
    ) {
        callback_yes = body;
        body = head;
        head = T_('Alert');
    }       
    if (typeof(head) == 'function') {
        callback_yes = head;
        head = T_('Alert');
        body = '';
    }
    jqDialog.alert(
        head,
        body,
        callback_yes
    );
};

/**
 * Prompt dialog
 */
ncw.layout.dialogs.prompt = function (head, body, form, callback_yes, callback_no) {  
    if (typeof(head) == 'function') {
        callback_yes = head;
        head = T_('Options');
    }
    if (typeof(body) == 'function') {
        if (typeof(head) == 'function') {
            callback_no = body;
        } else {
            callback_yes = body;    
        }
        body = T_('Choose your options!');
        form = '';
    } else if (typeof(body) == 'undefined') {
        body = T_('Choose your options!');
        form = '';
    }     
    jqDialog.prompt(
        head,
        body,
        form,
        callback_yes,
        callback_no
    );
};

/**
 * Confirm dialog
 */
ncw.layout.dialogs.confirm = function (head, body, callback_yes, callback_no) { 
    if (typeof(head) == 'function') {
        callback_yes = head;
        head = T_('Confirm') + '!';
    }
    if (typeof(body) == 'function') {
        if (typeof(head) == 'function') {
            callback_no = body;
        } else {
            callback_yes = body;    
        }
        body = T_('Make a decision!');
    }  else if (typeof(body) == 'undefined') {
        body = T_('Make a decision!');
    }    
    jqDialog.confirm(
        head,
        body,
        callback_yes,
        callback_no
    );
};

/**
 * Delete confirm dialog
 */
ncw.layout.dialogs.deleteConfirm = function (head, body, callback_yes, callback_no) {
    if (typeof(head) == 'function') {
        callback_yes = head;
        head = T_('Delete') + '!';
    }
    if (typeof(body) == 'function') {
        if (typeof(head) == 'function') {
            callback_no = body;
        } else {
            callback_yes = body;    
        }
        body = T_('Do you really want to delete this object?');
    } else if (typeof(body) == 'undefined') {
        body = T_('Do you really want to delete this object?');
    }
    jqDialog.deleteConfirm(
        head,
        body,
        callback_yes,
        callback_no
    );
};

/**
 * Save notify dialog
 */
ncw.layout.dialogs.saveNotify = function (head, body) {    
    if (typeof(head) == 'undefined') {
        head = T_('Saved') + '!';
    }
    if (typeof(body) == 'undefined') {
        body = T_('Your data has been saved successfully.');
    }   
    jqDialog.saveNotify(
        head,
        body,
        3
    );
};

/**
 * Error notify dialog
 */
ncw.layout.dialogs.errorNotify = function (head, body) {
    if (typeof(head) == 'undefined') {
        head = T_('Error') + '!';
    }
    if (typeof(body) == 'undefined') {
        body = T_('An error has occurred!');
    }     
    jqDialog.errorNotify(
        head,
        body,
        3
    );
};

/**
 * Notify dialog
 */
ncw.layout.dialogs.notify = function (head, body) {
    if (typeof(head) == 'undefined') {
        head = T_('Notify') + '!';
    }
    if (typeof(body) == 'undefined') {
        body = T_('Be aware that...');
    }     
    jqDialog.notify(
        head,
        body,
        3
    );
};

/**
 * Custom dialog
 */
ncw.layout.dialogs.custom = function (head, body, overlay, callback_ok, callback_cancel, width, zIndex) {   
    if (typeof(head) == 'function') {
        callback_ok = head;
        head = T_('Custom');
    }
    if (typeof(body) == 'function') {
        if (typeof(head) == 'function') {
            callback_cancel = body;
        } else {
            callback_ok = body;    
        }
        body = '';
    } 
    if (typeof(overlay) == 'function') {
        if (typeof(body) == 'function') {
            callback_cancel = overlay;
        } else {
        	callback_cancel = callback_ok;
            callback_ok = overlay;    
        }
        overlay = false;
    }     
    if (typeof(width) == 'undefined') {
        width = 600;
    }
    if (typeof(zIndex) == 'undefined') {
        zIndex = 990001;
    }    
    
    jqDialog.content(
        head,
        body,
        overlay,
        callback_ok,
        callback_cancel,
        width,
        zIndex
    );
};