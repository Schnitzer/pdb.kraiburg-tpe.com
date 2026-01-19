ncw.tpe.language = {};

ncw.tpe.language = function () {
    ncw.onLoad(ncw.tpe.language.load);
};

ncw.tpe.language.reload = function (action, data) {
    if (action == 'new') {
        ncw.tpe.language.loadShow(data.language_id);
    } else if (action == 'save') {
        ncw.tpe.language.loadEdit(ncw.tpe.language.id);
    } else if (action != 'save') {
        ncw.tpe.language.load();
    }
};

ncw.tpe.language.search = function () {
    ncw.layout.loadView(
        ncw.url(
            '/tpe/language/all' 
            + '?s=' + escape($('#language_search').val())
        ),
        function () {
            if (typeof ncw.tpe.language.load.callback == 'function') {
                ncw.tpe.language.load.callback();
            }
        }
    );     
};

ncw.tpe.language.loadPage = function () {
    ncw.layout.initView();
    if (typeof ncw.tpe.language.load.callback == 'function') {
        ncw.tpe.language.load.callback();
    }
};

ncw.tpe.language.load = function () {    
    ncw.layout.loadView(ncw.url('/tpe/language/all/')); 
};

ncw.tpe.language.loadNew = function () {    
    ncw.layout.loadView(ncw.url('/tpe/language/new/')); 
};

ncw.tpe.language.loadShow = function (id) {
    ncw.tpe.language.id = id;
    
    ncw.layout.loadView(
        ncw.url('/tpe/language/edit/' + id)
    );
};

ncw.tpe.language();