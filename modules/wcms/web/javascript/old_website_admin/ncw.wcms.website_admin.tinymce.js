tinyMCE.init(
    {
        mode : "none",
        theme : "advanced",
        editor_selector : "ncw-mce-advanced",
        plugins : "safari,style,layer,table,save,advhr,advimage,advlink,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,inlinepopups",
        theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,|,bullist,numlist,|,outdent,indent,blockquote,|,forecolor,backcolor",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,undo,redo,|,link,unlink,anchor,image,code,|,insertdate,inserttime,|,cite,abbr,acronym,del,ins,",
        theme_advanced_buttons3 : "tablecontrols,|,hr,|,sub,sup,|,charmap,iespell,|,print,|,fullscreen|,cleanup",
        // theme_advanced_blockformats : "p,h3,h4",
        theme_advanced_toolbar_align : "left",
        content_css : ncw.BASE + "/assets/wcms/css/wysiwyg.css",
        force_p_newlines : false,
        force_br_newlines : true,
        forced_root_block : '',
        convert_urls : false,
        file_browser_callback : "ncw.wcms.tinymce.chooseDialog",
        invalid_elements : "p",
        theme_advanced_toolbar_location : "external",
        external_link_list_url : ncw.url("/wcms/site/linklist/" + ncw.wcms.website_admin.languageCode),
        external_image_list_url :  ncw.url("/files/file/linklist/absolute:1"),
        handle_event_callback : ncw.wcms.website_admin.wysiwyg.onEvent
    }
);