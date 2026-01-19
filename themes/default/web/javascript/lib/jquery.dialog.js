/**
	Kailash Nadh,	http://kailashnadh.name
	August 2009
	Smooth popup dialog for jQuery

	License:	GNU Public License: http://www.fsf.org/copyleft/gpl.html
	
	v1.2	September 2 2009
**/

var jqDialog = new function() {

	//________button / control labels
	this.strYes = 'Yes';
	this.strNo = 'No';


	//________
	this.closeTimer = null;
	this.width = 0; this.height = 0;
	
	this.divBoxName =	'jqDialog_box';	this.divBox = null;
	this.divHeadName =	'jqDialog_content';	this.divContent = null;
	this.divContentName =	'jqDialog_content';	this.divContent = null;
	this.divFormName = 'jqDialog_form'; this.divForm = null;
	this.divOptionsName = 'jqDialog_options'; this.divOptions = null;
	this.btYesName = 'jqDialog_yes'; this.btYes = null;
	this.btNoName = 'jqDialog_no'; this.btNo = null;


	this.prepare = {};

	//________create a confirm box
    this.deleteConfirm = function(head, body, callback_yes, callback_no) {
        this.divBox = $('#ncw-delete');
        this.divHead = $('#ncw-delete-head');
        this.divContent = $('#ncw-delete-body');
        this.divOptions = $('#ncw-delete-options');
        this.btYes = $('#ncw-delete-yes');
        this.btNo = $('#ncw-delete-no');       
        
        this.makeCenter();
        this.overlay();
        this.createDialog(head, body);
        this.btYes.show(); this.btNo.show();  this.btYes.focus();
        // this.btOk.hide(); this.btCancel.hide(); 
        
        // just redo this everytime in case a new callback is presented
        this.btYes.unbind().click( function() {
            jqDialog.close();
            if(callback_yes) callback_yes();
        });

        this.btNo.unbind().click( function() {
            jqDialog.close();
            if(callback_no) callback_no();
        });

    };	
	
	this.confirm = function(head, body, callback_yes, callback_no) {
        this.divBox = $('#ncw-confirm');
        this.divHead = $('#ncw-confirm-head');
        this.divContent = $('#ncw-confirm-body');
        this.divOptions = $('#ncw-confirm-options');
        this.btYes = $('#ncw-confirm-yes');
        this.btNo = $('#ncw-confirm-no');	    
	    
        this.makeCenter();
        this.overlay();
		this.createDialog(head, body);
		
		this.btYes.show(); this.btNo.show();  this.btYes.focus();
		// this.btOk.hide(); this.btCancel.hide(); 
		
		// just redo this everytime in case a new callback is presented
		this.btYes.unbind().click( function() {
			jqDialog.close();
			if(callback_yes) callback_yes();
		});

		this.btNo.unbind().click( function() {
			jqDialog.close();
			if(callback_no) callback_no();
		});

	};
	
	//________prompt dialog
	this.prompt = function(head, body, form, callback_ok, callback_cancel) {		
        this.divBox = $('#ncw-options');
        this.divHead = $('#ncw-options-head');
        this.divContent = $('#ncw-options-body');
        this.divForm = $('#ncw-options-form');
        this.divOptions = $('#ncw-options-options');
        this.btYes = $('#ncw-options-yes');
        this.btNo = $('#ncw-options-no');		
		
        if (typeof(form) == 'function') {
            callback_ok = form;
            callback_cancel = callback_ok;
            form = '<input type="text" id="ncw-options-input-1" name="" />';
        }
        
        this.divForm.html(form);
        this.firstInput = $('#ncw-options-input-1');
        
        this.makeCenter();
        this.overlay();
        this.createDialog(head, body);        
        
		this.btYes.show(); this.btNo.show();
		this.firstInput.focus(); 
		
		// just redo this everytime in case a new callback is presented
		this.btYes.unbind().click( function() {
			jqDialog.close();
			if(callback_ok) callback_ok(jqDialog.divForm.serialize());
		});

		this.btNo.unbind().click( function() {
			jqDialog.close();
			if(callback_cancel) callback_cancel();
		});

	};
	
	//________create an alert box
	this.alert = function(head, body, callback_ok) {
        this.divBox = $('#ncw-alert');
        this.divHead = $('#ncw-alert-head');
        this.divContent = $('#ncw-alert-body');
        this.divOptions = $('#ncw-alert-options');
        this.btYes = $('#ncw-alert-yes');     
	    
	    this.overlay();
		this.createDialog(head, body);
		this.btYes.show();
		this.btYes.focus();
		
		// just redo this everytime in case a new callback is presented
		this.btYes.unbind().click( function() {
			jqDialog.close();
			if(callback_ok)
				callback_ok();
		});
	};
		
	//________create a dialog with custom content
	this.content = function(head, body, overlay, callback_ok, callback_cancel, width, zIndex) {
        this.divBox = $('#ncw-custom');
        this.divHead = $('#ncw-custom-head');
        this.divContent = $('#ncw-custom-body');
                
        this.divBox.width(width);
        this.divBox.css('z-index', zIndex);
        
        if (true == overlay) {
            this.overlay();
            $('#ncw-dialog-overlay').css('z-index', zIndex - 1);
        }
		this.createDialog(head, body);
        this.makeCenter();	
		
        this.btYes = $('#ncw-custom-ok');
        this.btNo = $('#ncw-custom-cancel');
		
        this.btYes.unbind().click( function() {
            jqDialog.close();
            if(callback_ok) callback_ok(this);
        });

        this.btNo.unbind().click( function() {
            jqDialog.close();
            if (true == overlay) {
                $('#ncw-dialog-overlay').css('z-index', 990000);
            }
            if(callback_cancel) callback_cancel();
        });		
	};

	//________create an auto-hiding notification
    this.saveNotify = function(head, body, close_seconds) {
        this.divBox = $('#ncw-save');
        this.divHead = $('#ncw-save-head');
        this.divContent = $('#ncw-save-body');    
        
        this._doNotify(head, body, close_seconds);
    };
    
    this.errorNotify = function(head, body, close_seconds) {
        this.divBox = $('#ncw-error');
        this.divHead = $('#ncw-error-head');
        this.divContent = $('#ncw-error-body');    
        
        this._doNotify(head, body, close_seconds);
    };    
    
	this.notify = function(head, body, close_seconds) {
        this.divBox = $('#ncw-notify');
        this.divHead = $('#ncw-notify-head');
        this.divContent = $('#ncw-notify-body');    

        this._doNotify(head, body, close_seconds);
	};
	
	this._doNotify = function(head, body, close_seconds) {
        this.divOptions = null;
        
        this.createDialog(head, body, 'slideDown', 'slow', false);
        
        if (close_seconds) {
            setTimeout(
                function () {      
                    $('#ncw-save, #ncw-error, #ncw-notify').slideUp('slow');               
                }, 
                close_seconds * 1000 
            );
        }  
	}

	//________dialog control
	this.createDialog = function(head, body, showMethod, showSpeed, maintain_position) {
		if (this.divOptions != null) {
		    this.divOptions.show();
		}
		this.divHead.html(head);
		this.divContent.html(body);
		
		if (typeof(showMethod) == 'undefined') {
		    showMethod = 'fadeIn';
		}
        if (typeof(showSpeed) == 'undefined') {
            showSpeed = 'fast';
        }		
        
		eval('this.divBox.' + showMethod + '("' + showSpeed + '")');
		
        if (typeof(maintain_position) == 'undefined') {
            maintain_position = true;
        }   		
		
        if (true == maintain_position) {
            this.maintainPosition();
        }
	};
	
	this.close = function(hideMethod, hideSpeed, clear_position) {
        if (typeof(hideMethod) == 'undefined') {
            hideMethod = 'fadeOut';
        }
        if (typeof(hideSpeed) == 'undefined') {
            hideSpeed = 'fast';
        }
        if (typeof(clear_position) == 'undefined') {
            clear_position = true;
        }           
        eval('this.divBox.' + hideMethod + '("' + hideSpeed + '", function () { if (true == clear_position) { this.clearPosition } })');
        $('#ncw-dialog-overlay').remove();
	};

	//________position control
	this.clearPosition = function() {
		$(window).scroll().remove();
	};
	this.makeCenter = function() {
		$(jqDialog.divBox).css (
			{
				top: ( (($(window).height() / 2) - ( ($(jqDialog.divBox).height()) / 2 ) - 100 )) + ($(document).scrollTop()) + 'px',
				left: ( (($(window).width() / 2) - ( ($(jqDialog.divBox).width()) / 2 ) )) + ($(document).scrollLeft()) + 'px',
			}
		);
	};
	this.maintainPosition = function() {
		$(window).scroll( function() {
			jqDialog.makeCenter();
		} );
		
	};

	// overlay
	this.overlay = function () {
	    height = $(document).height();
	    $('#ncw-dialog-overlay').remove();
	    $('<div id="ncw-dialog-overlay"></div>').appendTo('body').css('height', height + 'px').show();
	};
};