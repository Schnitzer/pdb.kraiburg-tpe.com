/**
 * contacts
 */
ncw.contacts.contact = function () {

};

ncw.contacts.contact.overview = function () {
    ncw.layout.loadView(
        ncw.url('/contacts/contact/index'),
        function () {                
        	ncw.contacts.contact.loadFavorites();
            ncw.contacts.contact.loadRecentlyOpened();
			ncw.contacts.contact.loadRecentlyAdded();
        }
    ); 	

};

/**
 * data fields
 */
ncw.contacts.contact.dataFields = function () {
	ncw.contacts.contact.phones();
    ncw.contacts.contact.emails();
    ncw.contacts.contact.messengers();
    ncw.contacts.contact.websites();
    ncw.contacts.contact.banks();
    ncw.contacts.contact.addresses();
    ncw.contacts.contact.relatedCompanies();
    
    // "first" checkboxex
	$('.ncw-members-first, .ncw-phones-first, .ncw-emails-first, .ncw-messengers-first, .ncw-websites-first, .ncw-addresses-first, .ncw-banks-first, .ncw-dates-first').live(
        'change',
        function () {
            var val = $(this).attr('checked');
            var name = $(this).attr('name');
           	if (false == val) {
           		$(this).attr('checked', true);
           	} else {
           		var class_name = $(this).attr('class');
           		$('.' + class_name).each(
           			function () {
           				if (name != $(this).attr('name')) {
           					$(this).attr('checked', false);
           				}
           			}           			
           		);
           	}
        }
    );    
};

/**
 * load user
 */
ncw.contacts.contact.load = function () {
    if ($(".ncw-groups-tree").length > 0) {
        ncw.layout.loadView(
            ncw.url('/contacts/contact/all') + '?g=' + ncw.contacts.group.groupId,
            function () {                
                if (typeof ncw.contacts.contact.load.callback == 'function') {
                    ncw.contacts.contact.load.callback();
                                      
                }
            }
        ); 
    };
};

/**
 * Load page
 */
ncw.contacts.contact.loadPage = function () {
    ncw.layout.initView();
    if (typeof ncw.contacts.contact.load.callback == 'function') {
        ncw.contacts.contact.load.callback();
    }
};

/**
 * search
 */
ncw.contacts.contact.search = function () {
	$.get(
		ncw.url(
			'/contacts/contact/search'
			+ '?s=' + escape($('#contact_search').val())
            + '&g=' + ncw.contacts.group.groupId
		),
		function (data) {
			if (data.contact_id > 0) {
				ncw.contacts.contact.loadContact(data.contact_id);
			} else {
			    ncw.layout.loadView(
			        ncw.url(
			            '/contacts/contact/all' 
			            + '?s=' + escape($('#contact_search').val())
			            + '&g=' + ncw.contacts.group.groupId
			        ),
			        function () {
			            if (typeof ncw.contacts.contact.load.callback == 'function') {
			                ncw.contacts.contact.load.callback();
			            }
			        }
			    );    				
			}
		},
		'json'	
	); 
};

ncw.contacts.contact.type = 'private';
ncw.contacts.contact.id = 0;

/**
 * load edit contact action
 */
ncw.contacts.contact.loadContact = function (contact_id) {
	ncw.contacts.contact.id = contact_id;
    ncw.layout.loadView(
        ncw.url('/contacts/contact/showContact/' + contact_id),
        function () {
        	ncw.contacts.contact.loadRecentlyOpened();
        }
    );
};

/**
 * load edit contact action
 */
ncw.contacts.contact.loadEditContact = function (contact_id) {
	ncw.contacts.contact.id = contact_id;
    ncw.layout.loadView(
        ncw.url('/contacts/contact/editContact/' + contact_id),
        function () {

        }
    );
};

/**
 * load edit contact action
 */
ncw.contacts.contact.loadEditInfo = function (contact_id) {
	ncw.contacts.contact.id = contact_id;
    ncw.layout.loadView(
        ncw.url('/contacts/contact/editInfo/' + contact_id),
        function () {

        }
    );
};

/**
 * load edit contact action
 */
ncw.contacts.contact.loadEditDates = function (contact_id) {
	ncw.contacts.contact.id = contact_id;
    ncw.layout.loadView(
        ncw.url('/contacts/contact/editDates/' + contact_id),
        function () {
			ncw.layout.dateTimePicker();
        }
    );
};

/**
 * load edit contact action
 */
ncw.contacts.contact.loadEditGroups = function (contact_id) {
	ncw.contacts.contact.id = contact_id;
    ncw.layout.loadView(
        ncw.url('/contacts/contact/editGroups/' + contact_id),
        function () {

        }
    );
};

/**
 * Recently openend contacts 
 */
ncw.contacts.contact.loadFavorites = function ()
{
	$('.favorites').load(
		ncw.url('/contacts/favorite/all'),
		function () {
			
		}
	);
}

ncw.contacts.contact.removeFromFavorite = function (favorite_id) {
	$.get(
		ncw.url('/contacts/favorite/remove/') + favorite_id,
		function (data) {
			ncw.contacts.contact.loadContact(ncw.contacts.contact.id);
			ncw.contacts.contact.loadFavorites();
		},
		'json'
	);
};		
ncw.contacts.contact.addToFavorite = function (contact_id) {
	$.get(
		ncw.url('/contacts/favorite/add/') + contact_id,
		function (data) {
			ncw.contacts.contact.loadContact(ncw.contacts.contact.id);
			ncw.contacts.contact.loadFavorites();
		},
		'json'
	);
};

/**
 * Recently openend contacts 
 */
ncw.contacts.contact.loadRecentlyOpened = function ()
{
	$('.recently_opened').load(
		ncw.url('/contacts/contact/recentlyOpened'),
		function () {
			
		}
	);
}

/**
 * Recently openend contacts 
 */
ncw.contacts.contact.loadRecentlyAdded = function ()
{
	$('.recently_added').load(
		ncw.url('/contacts/contact/recentlyAdded'),
		function () {
			
		}
	);
}

/**
 * load new folder action
 * @param parent_id
 */
ncw.contacts.contact.loadNew = function (type, related_company_id) {    
	
	if (typeof(type) == 'undefined') {
        type = 'private';
    }	
	
	if (typeof(related_company_id) == 'undefined') {
        related_company_id = 0;
    }	
	
    ncw.layout.loadView(
        ncw.url(
        	'/contacts/contact/new/' 
        	+ type
        	+ '/' + related_company_id
        ),
        function () {
			$('#contact_title').focus(
		        function () {
		        	var val = $(this).val();
		           	if (val == T_('Title')) {
		           		$(this).val('');
		           	}
		        }
		    ); 
			$('#contact_firstname').focus(
		        function () {
		        	var val = $(this).val();
		           	if (val == T_('Firstname')) {
		           		$(this).val('');
		           	}
		        }
		    ); 
			$('#contact_name').focus(
		        function () {
		        	var val = $(this).val();
		           	if (val == T_('Lastname')
		           		|| val == T_('Company')
		           	) {
		           		$(this).val('');
		           	}
		        }
		    ); 
        }
    ); 
};

/**
 * reload site structure
 */
ncw.contacts.contact.reload = function (action, data) {
    if (action == 'save') {
    	ncw.contacts.contact.loadContact(ncw.contacts.contact.id);
    } else if (action == 'new') {
    	ncw.contacts.contact.loadRecentlyAdded();
    	ncw.contacts.contact.loadContact(data.contact_id);
    } else {
    	ncw.contacts.group.groupId = 1;
        ncw.contacts.contact.load();
    } 
};

/**
 * Open detail search
 */
ncw.contacts.contact.detailSearch = function (callback, parent_contact_id, type) {
    if (typeof(callback) == 'undefined'
        || callback == 'gotoContact'
    ) {
        callback = ncw.contacts.contact.gotoContact;
    } else if (callback == 'addMember') {
        callback = ncw.contacts.contact.addMember;
    } else if (callback == 'addToCompany') {
        callback = ncw.contacts.contact.addToCompany;
    }
    
    if (typeof(callback) == 'undefined') {
    	type = 0;
    }    
    
    if (typeof(ncw.dialogs.contactSearch) == 'undefined') {
        $.getScript(
            ncw.url('contacts/contact/searchDialog.js'),
            function (data) {
            	ncw.dialogs.contactSearch.parent_contact_id = parent_contact_id;
            	ncw.dialogs.contactSearch.type = type;
                ncw.dialogs.contactSearch.show(callback);
            }
        );
    } else {
    	ncw.dialogs.contactSearch.parent_contact_id = parent_contact_id;
    	ncw.dialogs.contactSearch.type = type;
        ncw.dialogs.contactSearch.show(callback);
    }
};

/**
 * Go to contact
 */
ncw.contacts.contact.gotoContact = function (contact) {
    ncw.contacts.contact.loadEdit(contact.id);
};


/**
 * Choose the contact image
 */
ncw.contacts.contact.chooseImageDialog = function () {
    var callback = function (file) {
        $('#contact_file_id').val(file.id);
        $('#ncw-contact-preview-image').attr('src', file.preview);
    };
    
    if (typeof(ncw.dialogs.fileSearch) == 'undefined') {
        $.getScript(
            ncw.url('files/file/searchDialog.js'),
            function (data) {
            	ncw.dialogs.fileSearch.init();
                ncw.dialogs.fileSearch.show(
                    callback,
                    'jpg,png,gif'
                );
            }
        );
    } else {
    	ncw.dialogs.fileSearch.init();
        ncw.dialogs.fileSearch.show(
            callback,
            'jpg,png,gif'
        );
    }    
};

/**
 * add a child contact to contact
 */
ncw.contacts.contact.addMember = function (contact) {
    $.get(
        ncw.url(
            '/contacts/contact/addMember/'
             + contact.id + '/' + ncw.contacts.contact.id
        ),
        null,
        function () {
            ncw.layout.dialogs.saveNotify();
            ncw.contacts.contact.loadContact(ncw.contacts.contact.id);
        }
    );
};

/**
 * Entferne MEember
 */
ncw.contacts.contact.removeMember = function (member_id) {
    ncw.layout.dialogs.confirm(
        T_('Remove Member'),
        T_('Do you want to remove this member?'),
        function () {
            $.get(
                ncw.url(
                     '/contacts/contact/removeMember/' + member_id
                ),
                null,
                function (html) {
                    ncw.layout.dialogs.saveNotify();
                },
                'json'
            ); 
        }
    );
};

/**
 * Related Companies
 */
ncw.contacts.contact.relatedCompanies = function () {
    $('#ncw-related-company-add').live(
    	'click',
        function () {
            ncw.contacts.contact.detailSearch('addToCompany', 0, 2);
        }
    );
    $('.ncw-related-company-remove img').live(
        'click',
        function () {
            var count = $(this).attr('rel');
            var is_first = $('#related_company_' + count + ' .ncw-members-first').attr('checked');
            $('#related_company_' + count).remove();
            if (true == is_first) {
            	$('#ncw-related-companies').find('.ncw-members-first').first().attr('checked', true);
            }              
        }
    );
};

/**
 * Add Member to a Company
 */
ncw.contacts.contact.addToCompany = function (contact) {
	var empty_string = T_('Job');
    $('#related_company_id').val(contact.id);
    
    var count = $('#related_company_num').val();
    
    var checked = '';
    var first_exists = $('#ncw-related-companies').find('.ncw-members-first').first().attr('checked');
    if (typeof(first_exists) == 'undefined'
    	|| false == first_exists
    ) {
    	checked = 'checked="checked"';
    }    
    
    $('#ncw-related-companies').append(
        '<div class="ncw-related-company" id="related_company_' + count + '">'
        + '<input type="hidden" maxlength="255" name="data[Member][' + count + '][contact_id]" value="' + contact.id + '">'
        + '<input type="text" maxlength="255" name="data[Member][' + count + '][description]" value="' + empty_string + '"> '
        + T_('at') + ' ' + contact.name + ' '
        + '<input name="data[Member][' + count + '][first]" type="checkbox" value="1" ' + checked + ' class="ncw-members-first" style="width: 15px; margin:0;padding:0;">'
        + '<div class="ncw-related-company-remove ncw-contact-stuff-remove"><img alt="' + T_('Remove related company') + '" title="' + T_('Remove related company') + '" rel="' + count + '" src="' + ncw.image('icons/16px/cross.png') + '"></div>'
        + '</div>'
    );
    $('#related_company_num').val(++count);        
    
    $('.ncw-related-company input').live(
        'focus',
        function () {
            var val = $(this).val();
           	if (val == empty_string) {
           		$(this).val('');
           	}
        }
    );     
}

/**
 * Phones
 */
ncw.contacts.contact.phones = function () {
	var empty_string = T_('Phone number');
	
    $('#ncw-phone-add').live(
    	'click',
        function () {
            var count = $('#phone_num').val();
            
            var checked = '';
            var first_exists = $('#ncw-phones').find('.ncw-phones-first').first().attr('checked');
            if (typeof(first_exists) == 'undefined'
            	|| false == first_exists
            ) {
            	checked = 'checked="checked"';
            }
            
            $('#ncw-phones').append(
                '<div class="ncw-phone" id="phone_' + count + '">'
                + '<input type="text" maxlength="255" name="data[Phone][' + count + '][phone]" value="' + empty_string + '">'
                + '<select name="data[Phone][' + count + '][location]"><option value="work">' + T_('Work') + '</option><option value="mobile">' + T_('Mobil') + '</option><option value="fax">Fax</option><option value="pager">Pager</option><option value="home">' + T_('Home') + '</option><option value="skype">Skype</option><option value="other">' + T_('Other') + '</option></select>'
                + '<input name="data[Phone][' + count + '][first]" type="checkbox" value="1" ' + checked + ' class="ncw-phones-first" style="width: 15px; margin:0;padding:0;">'
                + '<div class="ncw-phone-remove ncw-contact-stuff-remove"><img alt="' + T_('Remove phone') + '" title="' + T_('Remove phone') + '" rel="' + count + '" src="' + ncw.image('icons/16px/cross.png') + '"></div>'
                + '</div>'
            );
            $('#phone_num').val(++count);
        }
    );
    $('.ncw-phone-remove img').live(
        'click',
        function () {
            var count = $(this).attr('rel');
            var is_first = $('#phone_' + count + ' .ncw-phones-first').attr('checked');
            $('#phone_' + count).remove();
            if (true == is_first) {
            	$('#ncw-phones').find('.ncw-phones-first').first().attr('checked', true);
            }            
        }
    );
    $('.ncw-phone input').live(
        'focus',
        function () {
            var val = $(this).val();
           	if (val == empty_string) {
           		$(this).val('');
           	}
        }
    );              
};

/**
 * Emails
 */
ncw.contacts.contact.emails = function () {
	var empty_string = T_('email@provider.com');
	
    $('#ncw-email-add').live(
    	'click',
        function () {
            var count = $('#email_num').val();
            
            var checked = '';
            var first_exists = $('#ncw-emails').find('.ncw-emails-first').first().attr('checked');
            if (typeof(first_exists) == 'undefined'
            	|| false == first_exists
            ) {
            	checked = 'checked="checked"';
            }            
            
            $('#ncw-emails').append(
                '<div class="ncw-email" id="email_' + count + '">'
                + '<input type="text" maxlength="255" name="data[Email][' + count + '][email]" value="' + empty_string + '">'
                + '<select name="data[Email][' + count + '][location]"><option value="work">' + T_('Work') + '</option><option value="home">' + T_('Home') + '</option><option value="other">' + T_('Other') + '</option></select>'
                + '<input name="data[Email][' + count + '][first]" type="checkbox" value="1" ' + checked + ' class="ncw-emails-first" style="width: 15px; margin:0;padding:0;">'
                + '<div class="ncw-email-remove ncw-contact-stuff-remove"><img alt="' + T_('Remove email') + '" title="' + T_('Remove email') + '" rel="' + count + '" src="' + ncw.image('icons/16px/cross.png') + '"></div>'
                + '</div>'
            );
            $('#email_num').val(++count);
        }
    );
    $('.ncw-email-remove img').live(
        'click',
        function () {
            var count = $(this).attr('rel');
            var is_first = $('#email_' + count + ' .ncw-emails-first').attr('checked');
            $('#email_' + count).remove();
            if (true == is_first) {
            	$('#ncw-emails').find('.ncw-emails-first').first().attr('checked', true);
            } 
        }
    );
    $('.ncw-email input').live(
        'focus',
        function () {
            var val = $(this).val();
           	if (val == empty_string) {
           		$(this).val('');
           	}
        }
    );    
};

/**
 * Messengers
 */
ncw.contacts.contact.messengers = function () {
	var empty_string = T_('Messenger');

    $('#ncw-messenger-add').live(
    	'click',
        function () {
            var count = $('#messenger_num').val();
            
            var checked = '';
            var first_exists = $('#ncw-messengers').find('.ncw-messengers-first').first().attr('checked');
            if (typeof(first_exists) == 'undefined'
            	|| false == first_exists
            ) {
            	checked = 'checked="checked"';
            }    
            
            $('#ncw-messengers').append(
                '<div class="ncw-messenger" id="messenger_' + count + '">'
                + '<input type="text" maxlength="255" name="data[Messenger][' + count + '][messenger]" value="' + empty_string + '">'
                + '<select name="data[Messenger][' + count + '][protocol]"><option value="aim">AIM</option><option value="msn">MSN</option><option value="icq">ICQ</option><option value="habber">Habber</option><option value="yahoo">Yahoo</option><option value="skype">Skype</option><option value="qq">QQ</option><option value="sametime">Sametime</option><option value="gadu-gadu">Gadu-Gadu</option><option value="google talk">Google Talk</option><option value="other">' + T_('Other') + '</option></select>'
                + '<select name="data[Messenger][' + count + '][location]"><option value="work">' + T_('Work') + '</option><option value="personal">' + T_('Personal') + '</option><option value="other">' + T_('Other') + '</option></select>'
                + '<input name="data[Messenger][' + count + '][first]" type="checkbox" value="1" ' + checked + ' class="ncw-messengers-first" style="width: 15px; margin:0;padding:0;">'
                + '<div class="ncw-messenger-remove ncw-contact-stuff-remove"><img alt="' + T_('Remove messenger') + '" title="' + T_('Remove messenger') + '" rel="' + count + '" src="' + ncw.image('icons/16px/cross.png') + '"></div>'
                + '</div>'
            );
            $('#messenger_num').val(++count);
        }
    );
    $('.ncw-messenger-remove img').live(
        'click',
        function () {
            var count = $(this).attr('rel');
        	var is_first = $('#messenger_' + count + ' .ncw-messengers-first').attr('checked');
            $('#messenger_' + count).remove();
            if (true == is_first) {
            	$('#ncw-messengers').find('.ncw-messengers-first').first().attr('checked', true);
            }             
        }
    );
    $('.ncw-messenger input').live(
        'focus',
        function () {
            var val = $(this).val();
           	if (val == empty_string) {
           		$(this).val('');
           	}
        }
    );     
};

/**
 * Websites
 */
ncw.contacts.contact.websites = function () {
	var empty_string = T_('Website');
	
    $('#ncw-website-add').live(
    	'click',
        function () {
            var count = $('#website_num').val();
            
            var checked = '';
            var first_exists = $('#ncw-websites').find('.ncw-websites-first').first().attr('checked');
            if (typeof(first_exists) == 'undefined'
            	|| false == first_exists
            ) {
            	checked = 'checked="checked"';
            }              
            
            $('#ncw-websites').append(
                '<div class="ncw-website" id="website_' + count + '">'
                + '<input type="text" maxlength="255" name="data[Website][' + count + '][website]" value="' + empty_string + '">'
                + '<select name="data[Website][' + count + '][location]"><option value="work">' + T_('Work') + '</option><option value="personal">' + T_('Personal') + '</option><option value="other">' + T_('Other') + '</option></select>'
				+ '<input name="data[Website][' + count + '][first]" type="checkbox" value="1" ' + checked + ' class="ncw-websites-first" style="width: 15px; margin:0;padding:0;">'                
                + '<div class="ncw-website-remove ncw-contact-stuff-remove"><img alt="' + T_('Remove website') + '" title="' + T_('Remove website') + '" rel="' + count + '" src="' + ncw.image('icons/16px/cross.png') + '"></div>'
                + '</div>'
            );
            $('#website_num').val(++count);
        }
    );
    $('.ncw-website-remove img').live(
        'click',
        function () {
            var count = $(this).attr('rel');
        	var is_first = $('#website_' + count + ' .ncw-websites-first').attr('checked');
            $('#website_' + count).remove();
            if (true == is_first) {
            	$('#ncw-websites').find('.ncw-websites-first').first().attr('checked', true);
            }               
        }
    );
    $('.ncw-website input').live(
        'focus',
        function () {
            var val = $(this).val();
           	if (val == empty_string) {
           		$(this).val('');
           	}
        }
    );     
};

/**
 * Bank account
 */
ncw.contacts.contact.banks = function () {
	var empty_strings = {
		'bank' : T_('Bank'),
		'bank_code' : T_('Bank code'),
		'account_number' : T_('Account number'),
		'iban' : T_('IBAN'),
		'bic' : T_('BIC'),	
	};
	
    $('#ncw-bank-add').live(
    	'click',
        function () {
            var count = $('#bank_num').val();
            
            var checked = '';
            var first_exists = $('#ncw-banks').find('.ncw-banks-first').first().attr('checked');
            if (typeof(first_exists) == 'undefined'
            	|| false == first_exists
            ) {
            	checked = 'checked="checked"';
            }                  
            
            $('#ncw-banks').append(
                '<div class="ncw-bank" id="bank_' + count + '" style="margin-bottom: 15px;">'
                + '<input type="text" maxlength="255" name="data[Bank][' + count + '][name]" value="' + empty_strings['bank'] + '">'
                + '<br />'
                + '<input type="text" maxlength="255" name="data[Bank][' + count + '][bankcode]" value="' + empty_strings['bank_code'] + '">'
                + '<br />'
                + '<input type="text" maxlength="255" name="data[Bank][' + count + '][accountnumber]" value="' + empty_strings['account_number'] + '">'
                + '<br />'
                + '<input type="text" maxlength="255" name="data[Bank][' + count + '][iban]" value="' + empty_strings['iban'] + '">'
                + '<br />'
                + '<input type="text" maxlength="255" name="data[Bank][' + count + '][bic]" value="' + empty_strings['bic'] + '">'
                + '<br />'
                + '<select name="data[Bank][' + count + '][location]">'
                + '<option value="work">' + T_('Work') + '</option>'
                + '<option value="home">' + T_('Home') + '</option>' 
                + '<option value="other">' + T_('Other') + '</option>'
                + '</select>'
                + '<input name="data[Bank][' + count + '][first]" type="checkbox" value="1" ' + checked + ' class="ncw-banks-first" style="width: 15px; margin:0;padding:0;">'
                + '<div class="ncw-bank-remove ncw-contact-stuff-remove"><img alt="' + T_('Remove bank') + '" title="' + T_('Remove bank') + '" rel="' + count + '" src="' + ncw.image('icons/16px/cross.png') + '"></div>'              
                + '</div>'
            );
            $('#bank_num').val(++count);
        }
    );
    $('.ncw-bank-remove img').live(
        'click',
        function () {
            var count = $(this).attr('rel');
        	var is_first = $('#bank_' + count + ' .ncw-banks-first').attr('checked');
            $('#bank_' + count).remove();
            if (true == is_first) {
            	$('#ncw-banks').find('.ncw-banks-first').first().attr('checked', true);
            }               
        }
    );
    $('.ncw-bank input').live(
        'focus',
        function () {
            var val = $(this).val();
           	if (val == empty_strings['bank']
           		|| val == empty_strings['bank_code']
           		|| val == empty_strings['account_number']
           		|| val == empty_strings['iban']
           		|| val == empty_strings['bic']
           	) {
           		$(this).val('');
           	}
        }
    );    
};

/**
 * Addresses
 */
ncw.contacts.contact.addresses = function () {
	var empty_strings = {
		'street' : T_('Street'),
		'city' : T_('City'),
		'state' : T_('State'),
		'postcode' : T_('Postcode'),
	};
		
    $('#ncw-address-add').live(
    	'click',
        function () {
            var count = $('#address_num').val();
            
            var checked = '';
            var first_exists = $('#ncw-addresses').find('.ncw-addresses-first').first().attr('checked');
            if (typeof(first_exists) == 'undefined'
            	|| false == first_exists
            ) {
            	checked = 'checked="checked"';
            }                
            
            $('#ncw-addresses').append(
                '<div class="ncw-address" id="address_' + count + '" style="margin-bottom: 15px;">'
                + '<input type="text" maxlength="255" class="street" name="data[Address][' + count + '][street]" value="' + empty_strings['street'] + '">'
                + '<select name="data[Address][' + count + '][location]"><option value="work">' + T_('Work') + '</option><option value="home">' + T_('Home') + '</option><option value="other">' + T_('Other') + '</option></select>'
                + '<input name="data[Address][' + count + '][first]" type="checkbox" value="1" ' + checked + ' class="ncw-addresses-first" style="width: 15px; margin:0;padding:0;">'
                + '<div class="ncw-address-remove ncw-contact-stuff-remove"><img alt="' + T_('Remove address') + '" title="' + T_('Remove address') + '" rel="' + count + '" src="' + ncw.image('icons/16px/cross.png') + '"></div>'
                + '<br />'
                + '<input type="text" maxlength="100" class="city" name="data[City][' + count + '][name]" value="' + empty_strings['city'] + '">'
                + '<input type="text" maxlength="100" class="state" name="data[City][' + count + '][state]" value="' + empty_strings['state'] + '">'
                + '<input type="text" maxlength="100" class="postcode" name="data[City][' + count + '][postcode]" value="' + empty_strings['postcode'] + '">'
                + '<br />'
                + '<select name="data[City][' + count + '][country_id]"><option value="1">Afghanistan</option><option value="2">Albanien</option><option value="3">Algerien</option><option value="4">Amerikanisch-Samoa</option><option value="5">Andorra</option><option value="6">Angola</option><option value="7">Anguilla</option><option value="8">Antarktis</option><option value="9">Antigua Und Barbuda</option><option value="10">Argentinien</option><option value="11">Armenien</option><option value="12">Aruba</option><option value="13">Australien</option><option value="14">Österreich</option><option value="15">Aserbaidschan</option><option value="16">Bahamas</option><option value="17">Bahrain</option><option value="18">Bangladesch</option><option value="19">Barbados</option><option value="20">Belarus</option><option value="21">Belgien</option><option value="22">Belize</option><option value="23">Benin</option><option value="24">Bermuda</option><option value="25">Bhutan</option><option value="26">Bolivien</option><option value="27">Bosnien Und Herzegowina</option><option value="28">Botsuana</option><option value="29">Bouvetinsel</option><option value="30">Brasilien</option><option value="31">Britisches Territorium Im Indischen Ozean</option><option value="32">Brunei Darussalam</option><option value="33">Bulgarien</option><option value="34">Burkina Faso</option><option value="35">Burundi</option><option value="36">Kambodscha</option><option value="37">Kamerun</option><option value="38">Kanada</option><option value="39">Kap Verde</option><option value="40">Kaimaninseln</option><option value="41">Zentralafrikanische Republik</option><option value="42">Tschad</option><option value="43">Chile</option><option value="44">China</option><option value="45">Weihnachtsinsel</option><option value="46">Kokosinseln (Keeling)</option><option value="47">Kolumbien</option><option value="48">Komoren</option><option value="49">Kongo</option><option value="50">Demokratische Republik Kongo</option><option value="51">Cookinseln</option><option value="52">Costa Rica</option><option value="53">Côte D’Ivoire</option><option value="54">Kroatien</option><option value="55">Kuba</option><option value="56">Zypern</option><option value="57">Tschechische Republik</option><option value="58">Dänemark</option><option value="59">Dschibuti</option><option value="60">Dominica</option><option value="61">Dominikanische Republik</option><option value="62">Osttimor</option><option value="63">Ecuador</option><option value="64">Ägypten</option><option value="65">El Salvador</option><option value="66">Äquatorialguinea</option><option value="67">Eritrea</option><option value="68">Estland</option><option value="69">Äthiopien</option><option value="70">Falklandinseln</option><option value="71">Färöer</option><option value="72">Fidschi</option><option value="73">Finnland</option><option value="74">Frankreich</option><option value="75">Französisch-Guayana</option><option value="76">Französisch-Polynesien</option><option value="77">Französische Süd- Und Antarktisgebiete</option><option value="78">Gabun</option><option value="79">Gambia</option><option value="80">Georgien</option><option selected="selected" value="81">Deutschland</option><option value="82">Ghana</option><option value="83">Gibraltar</option><option value="84">Griechenland</option><option value="85">Grönland</option><option value="86">Grenada</option><option value="87">Guadeloupe</option><option value="88">Guam</option><option value="89">Guatemala</option><option value="90">Guinea</option><option value="91">Guinea-Bissau</option><option value="92">Guyana</option><option value="93">Haiti</option><option value="94">Heard Und Mcdonaldinseln</option><option value="95">Vatikanstadt</option><option value="96">Honduras</option><option value="97">Hong Kong S.A.R., China</option><option value="98">Ungarn</option><option value="99">Island</option><option value="100">Indien</option><option value="101">Indonesien</option><option value="102">Iran</option><option value="103">Irak</option><option value="104">Irland</option><option value="105">Israel</option><option value="106">Italien</option><option value="107">Jamaika</option><option value="108">Japan</option><option value="109">Jordanien</option><option value="110">Kasachstan</option><option value="111">Kenia</option><option value="112">Kiribati</option><option value="113">Kuwait</option><option value="114">Kirgisistan</option><option value="115">Laos</option><option value="116">Lettland</option><option value="117">Libanon</option><option value="118">Lesotho</option><option value="119">Liberia</option><option value="120">Libyen</option><option value="121">Liechtenstein</option><option value="122">Litauen</option><option value="123">Luxemburg</option><option value="124">Macau S.A.R., China</option><option value="125">Mazedonien</option><option value="126">Madagaskar</option><option value="127">Malawi</option><option value="128">Malaysia</option><option value="129">Malediven</option><option value="130">Mali</option><option value="131">Malta</option><option value="132">Marshallinseln</option><option value="133">Martinique</option><option value="134">Mauretanien</option><option value="135">Mauritius</option><option value="136">Mayotte</option><option value="137">Mexiko</option><option value="138">Mikronesien</option><option value="139">Republik Moldau</option><option value="140">Monaco</option><option value="141">Mongolei</option><option value="142">Montserrat</option><option value="143">Marokko</option><option value="144">Mosambik</option><option value="145">Myanmar</option><option value="146">Namibia</option><option value="147">Nauru</option><option value="148">Nepal</option><option value="149">Niederlande</option><option value="150">Niederländische Antillen</option><option value="151">Neukaledonien</option><option value="152">Neuseeland</option><option value="153">Nicaragua</option><option value="154">Niger</option><option value="155">Nigeria</option><option value="156">Niue</option><option value="157">Norfolkinsel</option><option value="158">Demokratische Volksrepublik Korea</option><option value="159">Nördliche Marianen</option><option value="160">Norwegen</option><option value="161">Oman</option><option value="162">Pakistan</option><option value="163">Palau</option><option value="164">Palästinensische Gebiete</option><option value="165">Panama</option><option value="166">Papua-Neuguinea</option><option value="167">Paraguay</option><option value="168">Peru</option><option value="169">Philippinen</option><option value="170">Pitcairn</option><option value="171">Polen</option><option value="172">Portugal</option><option value="173">Puerto Rico</option><option value="174">Katar</option><option value="175">Réunion</option><option value="176">Rumänien</option><option value="177">Russische Föderation</option><option value="178">Ruanda</option><option value="179">St. Helena</option><option value="180">St. Kitts Und Nevis</option><option value="181">St. Lucia</option><option value="182">St. Pierre Und Miquelon</option><option value="183">St. Vincent Und Die Grenadinen</option><option value="184">Samoa</option><option value="185">San Marino</option><option value="186">São Tomé Und Príncipe</option><option value="187">Saudi-Arabien</option><option value="188">Senegal</option><option value="189">Seychellen</option><option value="190">Sierra Leone</option><option value="191">Singapur</option><option value="192">Slowakei</option><option value="193">Slowenien</option><option value="194">Salomonen</option><option value="195">Somalia</option><option value="196">Südafrika</option><option value="197">Südgeorgien Und Die Südlichen Sandwichinseln</option><option value="198">Republik Korea</option><option value="199">Spanien</option><option value="200">Sri Lanka</option><option value="201">Sudan</option><option value="202">Suriname</option><option value="203">Svalbard Und Jan Mayen</option><option value="204">Swasiland</option><option value="205">Schweden</option><option value="206">Schweiz</option><option value="207">Syrien</option><option value="208">Taiwan</option><option value="209">Tadschikistan</option><option value="210">Tansania</option><option value="211">Thailand</option><option value="212">Togo</option><option value="213">Tokelau</option><option value="214">Tonga</option><option value="215">Trinidad Und Tobago</option><option value="216">Tunesien</option><option value="217">Türkei</option><option value="218">Turkmenistan</option><option value="219">Turks- Und Caicosinseln</option><option value="220">Tuvalu</option><option value="221">Uganda</option><option value="222">Ukraine</option><option value="223">Vereinigte Arabische Emirate</option><option value="224">Vereinigtes Königreich</option><option value="225">Vereinigte Staaten</option><option value="226">Amerikanisch-Ozeanien</option><option value="227">Uruguay</option><option value="228">Usbekistan</option><option value="229">Vanuatu</option><option value="230">Venezuela</option><option value="231">Vietnam</option><option value="232">Britische Jungferninseln</option><option value="233">Amerikanische Jungferninseln</option><option value="234">Wallis Und Futuna</option><option value="235">Westsahara</option><option value="236">Jemen</option><option value="238">Sambia</option><option value="239">Simbabwe</option></select>'             
                + '</div>'
            );
            $('#address_num').val(++count);
        }
    );
    $('.ncw-address-remove img').live(
        'click',
        function () {
            var count = $(this).attr('rel');
        	var is_first = $('#address_' + count + ' .ncw-addresses-first').attr('checked');
            $('#address_' + count).remove();
            if (true == is_first) {
            	$('#ncw-addresses').find('.ncw-addresses-first').first().attr('checked', true);
            }             
        }
    );
    $('.ncw-address input').live(
        'focus',
        function () {
            var val = $(this).val();
           	if (val == empty_strings['street']
           		|| val == empty_strings['city']
           		|| val == empty_strings['state']
           		|| val == empty_strings['postcode']
           	) {
           		$(this).val('');
           	}
        }
    );     
};

/**
 * Websites
 */
ncw.contacts.contact.dates = function () {
    $('#ncw-date-add').live(
    	'click',
        function () {
            var count = $('#date_num').val();
            
            var checked = '';
            var first_exists = $('#ncw-dates').find('.ncw-dates-first').first().attr('checked');
            if (typeof(first_exists) == 'undefined'
            	|| false == first_exists
            ) {
            	checked = 'checked="checked"';
            }             
            
            var html = '<div class="ncw-date" id="date_' + count + '">';
            
            html += '<select name="data[Date][' + count + '][description]">';
            var descriptions = {
                "birthday" : T_("Birthday"),
                "anniversary" : T_("Anniversary"),
                "first met" : T_("First met"),
                "hired" : T_("Hired"),
                "fired" : T_("Fired"),
                "other" : T_("Other")
            };
            var description = $('#check_description').val();
            $.each(
                descriptions,
                function (value, label) {
                    if (value == description) {
                        var selected = ' selected="selected"';                        
                    } else {
                        var selected = '';
                    }
                    html += '<option value="' + value + '"' + selected + '>' + label + '</option>';
                }
            )
            html += '</select>';
            
            html += '<input type="text" maxlength="255" name="data[Date][' + count + '][date]" value="' + $('#ncw-date-add-value').val() + '" class="ncw-datepick">'
            + '<input name="data[Date][' + count + '][first]" type="checkbox" value="1" ' + checked + ' class="ncw-dates-first" style="width: 15px; margin:0;padding:0;">'
            + '<div class="ncw-date-remove ncw-contact-stuff-remove"><img alt="' + T_('Remove date') + '" title="' + T_('Remove date') + '" rel="' + count + '" src="' + ncw.image('icons/16px/cross.png') + '"></div>'
            + '</div>';    
            
            $('#ncw-dates').append(html);
            $('#date_num').val(++count);
            
            ncw.layout.dateTimePicker();
        }
    );
    $('.ncw-date-remove img').live(
        'click',
        function () {
            var count = $(this).attr('rel');
        	var is_first = $('#date_' + count + ' .ncw-dates-first').attr('checked');
            $('#date_' + count).remove();
            if (true == is_first) {
            	$('#ncw-dates').find('.ncw-dates-first').first().attr('checked', true);
            }            
        }
    );   
};

/**
 * Adds a new note to the contact
 */
ncw.contacts.contact.notes = function () {
    $('#ncw-note-new').live(
        'click',
        function () {
            $.post(
                ncw.url('/contacts/note/save'),
                {
                    'data[Note][body]' : $('#ncw-new-contact-note').val(),
                    'data[Note][contact_id]' : $('#contact_id').val()
                },
                function (data) {
                    ncw.contacts.contact.reload('save');
                },
                'json'
            );
        }
    );
    
    var note_edit_id = 0;
    
    $('.ncw-note-edit').live(
        'click',
        function () {
            note_edit_id = $(this).attr('rel');

            note_edit_cancel();
            
            $(this).parent().parent().removeClass('note').addClass('edit-note');
                        
            $('#ncw-note-' + note_edit_id + ' strong').hide();
            $('#ncw-note-' + note_edit_id + ' .icons').hide();
            $('#ncw-note-' + note_edit_id + ' .body').hide();
            
            $('#ncw-note-' + note_edit_id + '').append(
                '<div id="ncw-note-edit">'
                + '<strong>' + T_('Editing note') + '</strong><br />'
                + '<textarea id="ncw-note-edit-body">' + $('#ncw-note-' + note_edit_id + ' .body p').html() + '</textarea><br />'
                + '<a href="javascript: void(0);" id="ncw-note-edit-save"  class="ncw-action">' + T_('Save') + '</a>'
                + '<a href="javascript: void(0);" id="ncw-note-edit-cancel" class="ncw-action ncw-action-with-margin">' + T_('Cancel') + '</a>'
                + '</div>'
            );
        }
    );    
    
    var note_edit_cancel = function () {
        $('#ncw-note-edit').remove();
        $('.edit-note').addClass('note').removeClass('edit-note');
        $('#ncw-contact-preview-notes .note strong').show();
        $('#ncw-contact-preview-notes .note .icons').attr('style', '');
        $('#ncw-contact-preview-notes .note .body').show();
    }    
    
    $('#ncw-note-edit-save').live(
        'click',
        function () {    
            var body = $('#ncw-note-edit-body').val();
            if (body != '') {
	            $.post(
	                ncw.url(
	                    '/contacts/note/update'
	                ),
	                {
	                    "data[Note][id]" : note_edit_id,
	                    "data[Note][contact_id]" : ncw.contacts.contact.id,
	                    "data[Note][body]" : body
	                },
	                function (data) {
	                    $('#ncw-note-' + note_edit_id + ' .body p').html(body);
	                    note_edit_cancel();
	                },
	                'json'
	            );
	        }
        }
    );    
    
    $('#ncw-note-edit-cancel').live(
        'click',
        function () {    
            note_edit_cancel();
        }
    );

    $('.ncw-note-delete').live(
        'click',
        function () {
            var $this = this;
            ncw.layout.dialogs.confirm(
                T_('Delete note'),
                T_('Do you really want to delete this note?'),
                function () {
                    var id = $($this).attr('rel');
                    $.get(
                        ncw.url(
                            '/contacts/note/delete/' + id
                        ),
                        function (data) {
                            //$('#ncw-note-' + id).remove();
                            ncw.contacts.contact.loadContact(ncw.contacts.contact.id);
                        },
                        'json'
                    );
                }
            );
        }
    );
};

/**
 * Adds a new file to the contact
 */
ncw.contacts.contact.files = function () {
    $('#ncw-file-new').live(
        'click',
        function () {    
            var callback = function (file) {
                $.post(
                    ncw.url('/contacts/file/save'),
                    {
                        'data[File][file_id]' : file.id,
                        'data[File][contact_id]' : $('#contact_id').val()
                    },
                    function (data) {
                        ncw.contacts.contact.reload('save');
                    },
                    'json'
                );
            };
            
            if (typeof(ncw.dialogs.fileSearch) == 'undefined') {
                $.getScript(
                    ncw.url('files/file/searchDialog.js'),
                    function (data) {
                    	ncw.dialogs.fileSearch.init();
                        ncw.dialogs.fileSearch.show(callback);
                    }
                );
            } else {
            	ncw.dialogs.fileSearch.init();
                ncw.dialogs.fileSearch.show(callback);
            } 
        }
    );
    
    $('.ncw-files-file-remove').live(
        'click',
        function () {
            var $this = this;
            ncw.layout.dialogs.confirm(
                T_('Remove file'),
                T_('Do you really want to remove this file?'),
                function () {
                    var id = $($this).attr('rel');
                    $.get(
                        ncw.url(
                            '/contacts/file/delete/' + id
                        ),
                        function (data) {
                            $('#ncw-file-' + id).remove();
                        },
                        'json'
                    );
                }
            );
        }
    );    
};

/**
 * add contactgroup
 */
ncw.contacts.contact.addContactgroup = function () {
    $('#ncw-add-contactgroup').live(
    	'click',
        function () {     
            var contactgroup_id = $('#contact_contactgroup').val();
            $.get(
                ncw.url(
                    '/contacts/contact/addContactgroup/' + 
                    $('#contact_id').val() + '/' + contactgroup_id
                ), 
                null,
                function (data) {
                    var name = data.contactgroup.name;
                    $('#ncw-contactgroups').append(
                        '<tr id="ncw-added-contactgroup-' + data.contactgroup.id + '">' + 
                        '<td>' + name  + '</td>' + 
                        '<td class="ncw-table-td-icons "><a href="javascript: ncw.contacts.contact.removeContactgroup(' + data.contactgroup.id + ');"><img title="' + T_('Delete') + '" alt="' + T_('Delete') + '" src="' + ncw.image('icons/16px/cross.png') + '"/></td>' + 
                        '</tr>'
                    );
                    ncw.layout.table();
                    ncw.contacts.contact.loadEditGroups(ncw.contacts.contact.id);
                },
                'json'
            );            
        }
    );        
};

/**
 * remove contactgroup
 * @param contactgroup_id
 */
ncw.contacts.contact.removeContactgroup = function (contactgroup_id) {
	ncw.layout.dialogs.confirm(
		T_('Remove from group'),
		T_('Remove this contact from group'),
		function () {
		    $.get(
		       	ncw.url('/contacts/contact/removeContactgroup/' + contactgroup_id), 
		        null,
		        function (data) {
		       		$('#ncw-added-contactgroup-' + contactgroup_id).remove();
			        ncw.layout.table();
			        ncw.contacts.contact.loadEditGroups(ncw.contacts.contact.id);
			    },
			    'json'
			); 
		}
	);
};