/**
 * Initialize
 */
ncw.files.file = function () {    

};

/**
 * Files reload
 */
ncw.files.file.reload = function (action) {
    if (action != 'save') {
        ncw.files.folder.tree.reload();
        ncw.files.file.load();
    }
};

/**
 * load files
 */
ncw.files.file.load = function () {
    if ($(".ncw-folders-tree").length > 0) {
        ncw.layout.loadView(
            ncw.url('/files/file/all/' + ncw.files.folder.id),
            function () {              
                if (typeof ncw.files.file.load.callback == 'function') {
                    ncw.files.file.load.callback();
                }
                ncw.files.file.addNew();
            }
        ); 
    };
};

/**
 * Load page
 */
ncw.files.file.loadPage = function () {
    ncw.layout.initView();
    if (typeof ncw.files.file.load.callback == 'function') {
        ncw.files.file.load.callback();
    }
};

ncw.files.file.load.callback = null;

/**
 * load edit file action
 * @param file_id
 */
ncw.files.file.loadEdit = function (file_id) {
    ncw.layout.loadView(
        ncw.url('/files/file/edit/' + file_id)
    );
};

/**
 * save new folder
 */
ncw.files.file.addNew = function () {
    var element = this;

    $('#fileupload').fileupload({
        url: ncw.url('files/file/upload/' + ncw.files.folder.id),
        dataType: 'json',
        autoUpload: true
    }).on('fileuploadadd', function (e, data) {
        data.context = $('<div/>').appendTo('#files');
        $('#progress .progress-bar').css(
            'width',
            0 + '%'
        );        
        $.each(data.files, function (index, file) {
            var node = $('<p/>')
                    .append($('<span/>').text(file.name));
            node.appendTo(data.context);
        });
    }).on('fileuploadprocessalways', function (e, data) {
        var index = data.index,
            file = data.files[index],
            node = $(data.context.children()[index]);
        if (file.preview) {
            node
                .prepend('<br>')
                .prepend(file.preview);
        }
        if (file.error) {
            node
                .append('<br>')
                .append($('<span class="text-danger"/>').text(file.error));
        }
    }).on('fileuploadprogressall', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#progress .progress-bar').css(
            'width',
            progress + '%'
        );
    }).on('fileuploaddone', function (e, data) {
        $.each(data.result.files, function (index, file) {
            if (file.url) {
                ncw.layout.dialogs.saveNotify(T_('Uploaded'), T_('Your file has been uploaded successfully'));
                ncw.files.file.load();                    
            } else if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                $(data.context.children()[index])
                    .append('<br>')
                    .append(error);
            }
        });
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index) {
            var error = $('<span class="text-danger"/>').text('File upload failed.');
            $(data.context.children()[index])
                .append('<br>')
                .append(error);
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

    /*$('#ncw-uploadify').uploadify(
        {
            'uploader': ncw.BASE + '/themes/default/web/javascript/lib/uploadify/uploadify.swf',
            'script': ncw.url('files/file/upload/' + ncw.files.folder.id),
            'folder': ncw.BASE + '/assets/files/uploads',
            'cancelImg': ncw.image('icons/16px/cancel.png'),
            'buttonText'  : T_('BROWSE'),
            'multi': 'true',
            'onAllComplete': function () {
                ncw.layout.dialogs.saveNotify(T_('Uploaded'), T_('Your file has been uploaded successfully'));
                ncw.files.file.load();
            },
            'onError': function (a, b, c, d) {
                if (d.status == 404) {
                   alert('Could not find upload script. Use a path relative to: '+'<?= getcwd() ?>');
                } else if (d.type === "HTTP") {
                   alert('error '+d.type+": "+d.status);
                } else if (d.type ==="File Size") {
                   alert(c.name+' '+d.type+' Limit: '+Math.round(d.sizeLimit/1024)+'KB');
                } else {
                   alert('error '+d.type+": "+d.text + ' ' + c.name + ' ' + c.type + ' ' + c.size);
                }
            }
        }
    );*/   
};

ncw.files.file.addNew.upload = null;

/**
 * Upload file
 */
ncw.files.file.addNew.submit = function () {
    $('#ncw-uploadify').uploadifyUpload();
};

/**
 * clear Lis file
 */
ncw.files.file.addNew.clear = function () {
    $('#ncw-uploadify').uploadifyClearQueue();
};

/**
 * search
 */
ncw.files.file.search = function () {
    ncw.layout.loadView(
        ncw.url(
            '/files/file/all' + '?s=' + escape($('#file_search').val())
        ),
        function () {
            if (typeof ncw.files.file.load.callback == 'function') {
                ncw.files.file.load.callback();
            }
        }
    );     
};

