ncw.wcms.tinymce = {};
           
/**
 * ...
 */
ncw.wcms.tinymce.chooseDialog = function (field_name, url, type, win) {
	
	var searchFile = function (only_types) {
		var setFile = function (file) {
			var file_path = file.file.replace('http://' + window.location.hostname, '');
		    win.document.forms[0].elements[field_name].value = file_path;
		};
	
		if (typeof(ncw.dialogs.fileSearch) == 'undefined') {
			$.getScript(
		    	ncw.url('files/file/searchDialog.js'),
		        function (data) {
		            ncw.dialogs.fileSearch.show(setFile, only_types);
		        }
		    );
		} else {
		    ncw.dialogs.fileSearch.show(setFile, only_types);
		}	
	}
	
	var searchSite = function () {
		var setSite = function (site) {
			if (typeof(site.file) != 'undefined') {
				var file_path = site.file.replace('http://' + window.location.hostname, '');
		    	win.document.forms[0].elements[field_name].value = file_path;
			} else {
				win.document.forms[0].elements[field_name].value = site.url;
			}
		};

		if (typeof(ncw.dialogs.siteSearch) == 'undefined') {
			$.getScript(
		    	ncw.url('wcms/site/searchDialog.js'),
		        function (data) {
		            ncw.dialogs.siteSearch.show(setSite);
		        }
		    );
		} else {
		    ncw.dialogs.siteSearch.show(setSite);
		}				
	}	
	
	if (type == 'image') {
		searchFile('jpg,jpeg,gif,png');
	} else if (type == 'file') {
		searchSite();
	}
}        
        
ncw.wcms.tinymce.languageCode = '';

ncw.wcms.tinymce.setup = function () {
    tinyMCE.init(
        {
            mode : "textareas",
            theme : "advanced",           
            editor_selector : "ncw-wysiwyg",
            plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
        
            // Theme options
            theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
            theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
            // theme_advanced_blockformats : "p,h3,h4",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            force_p_newlines : false,
            force_br_newlines : true,
            forced_root_block : '',
            convert_urls : false,
            file_browser_callback : "ncw.wcms.tinymce.chooseDialog",
            theme_advanced_resizing : false,
            content_css : ncw.BASE + "/assets/wcms/css/wysiwyg.css",
            external_link_list_url :  ncw.url("/wcms/site/linklist/" + ncw.wcms.tinymce.languageCode),
            external_image_list_url :  ncw.url("/files/file/linklist/")                            
        }
    );
    
    tinyMCE.init(
        {
            mode : "textareas",
            theme : "advanced",
            editor_selector : "ncw-wysiwyg-small",
            plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras",
            
            // Theme options
            theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,image,cleanup,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
            theme_advanced_buttons3 : "sub,sup,|,charmap,emotions,iespell,|,print,|,ltr,rtl,|,styleprops,|,cite,abbr,acronym,del,ins,attribs",
            // theme_advanced_blockformats : "p,h3,h4",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            force_p_newlines : false,
            force_br_newlines : true,
            forced_root_block : '',
            convert_urls : false,
            file_browser_callback : "ncw.wcms.tinymce.chooseDialog",
            theme_advanced_resizing : false,
            content_css : ncw.BASE + "/assets/wcms/css/wysiwyg.css",
            external_link_list_url :  ncw.url("/wcms/site/linklist/" + ncw.wcms.tinymce.languageCode),
            external_image_list_url :  ncw.url("/files/file/linklist")                           
        }
   );
}
