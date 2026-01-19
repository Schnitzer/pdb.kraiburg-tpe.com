ncw.wcms.gallery = function () {
	ncw.onLoad(
		function () {
			$('#wcms-folder-name').focus(
				function () {
					var setFolder = function (folder) {
			            $('#gallery_folder_id').val(folder.id);				
			            $('#wcms-folder-name').val(folder.name);
					};
				
					if (typeof(ncw.dialogs.folderSearch) == 'undefined') {
						$.getScript(
					    	ncw.url('files/folder/searchDialog.js'),
					        function (data) {
					            ncw.dialogs.folderSearch.show(setFolder);
					        }
					    );
					} else {
					    ncw.dialogs.folderSearch.show(setFolder);
					}
				}
			);
		}
	);
};

ncw.wcms.gallery.reload = function (action) {
	if (action == 'new') {
		window.location.href = ncw.url('/wcms/gallery/all');
	}
};

ncw.wcms.gallery();
