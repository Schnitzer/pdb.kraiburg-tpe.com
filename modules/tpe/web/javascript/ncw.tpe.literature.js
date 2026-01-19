ncw.tpe.literature = {};

ncw.tpe.literature = function () {
    ncw.onLoad(ncw.tpe.literature.load);
};

ncw.tpe.literature.reload = function (action, data) {
    if (action == 'new') {
        console.log(data);
        ncw.tpe.literature.loadShow(data.literature_id);
    } else if (action == 'save') {
        //ncw.tpe.literature.loadShow(ncw.tpe.literature.id);
    } else if (action != 'save') {
        ncw.tpe.literature.load();
    }
};

ncw.tpe.literature.search = function () {
    ncw.layout.loadView(
        ncw.url(
            '/tpe/literature/all' 
            + '?s=' + escape($('#literature_search').val())
        ),
        function () {
            if (typeof ncw.tpe.literature.load.callback == 'function') {
                ncw.tpe.literature.load.callback();
            }
        }
    );     
};

ncw.tpe.literature.loadPage = function () {
    ncw.layout.initView();
    if (typeof ncw.tpe.language.load.callback == 'function') {
        ncw.tpe.language.load.callback();
    }
};


ncw.tpe.literature.load = function () {    
    ncw.layout.loadView(ncw.url('/tpe/literature/all/')); 
};

ncw.tpe.literature.loadNew = function () {    
    ncw.layout.loadView(ncw.url('/tpe/literature/new/')); 
};

ncw.tpe.literature.loadShow = function (id) {
    ncw.tpe.literature.id = id;
    
    ncw.layout.loadView(
        ncw.url('/tpe/literature/edit/' + id)
    );
};

ncw.tpe.literature.addLanguage = function (id) {
    var language_id = $("#language_language_id").val();
    $.get(
        ncw.url(
            '/tpe/literature/addLanguage/'
            + id + "/" + language_id
        ),
        function (data) {
            ncw.tpe.literature.loadShow(ncw.tpe.literature.id);
        }
    );
};

ncw.tpe.literature.removeLanguage = function (literature_language_id) {
    ncw.layout.dialogs.confirm(
        T_("Remove this language?"), 
        T_("Do you really want to remove this language?"), 
        function () {
            $.get(
                ncw.url(
                    '/tpe/literature/removeLanguage/'
                    + literature_language_id
                ),
                function (data) {
                    ncw.tpe.literature.loadShow(ncw.tpe.literature.id);
                }
            );
        }
    )
};

ncw.tpe.literature.changeLanguagePic = function (literature_language_id, counter) {    
    var callback = function (file) {
        $('#literaturelanguage_' + counter + '_pic_id').val(file.id);
        $('#literaturelanguage_' + counter + '_pic').attr('src', file.preview);
        //$('#literaturelanguage_' + counter + '_pic').html(file.name + "." + file.type);
    };
    
    if (typeof(ncw.dialogs.fileSearch) == 'undefined') {
        $.getScript(
            ncw.url('files/file/searchDialog.js'),
            function (data) {
                ncw.dialogs.fileSearch.init();
                ncw.dialogs.fileSearch.show(callback, 'jpg,png,gif');
            }
        );
    } else {
        ncw.dialogs.fileSearch.init();
        ncw.dialogs.fileSearch.show(callback, 'jpg,png,gif');
    } 
};

ncw.tpe.literature.changeLanguageFile = function (literature_language_id, counter) {    
    var callback = function (file) {
        $('#literaturelanguage_' + counter + '_file_id').val(file.id);
        $('#literaturelanguage_' + counter + '_file').attr('href', file.file);
        $('#literaturelanguage_' + counter + '_file').html(file.name + "." + file.type);
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
};

ncw.tpe.literature();