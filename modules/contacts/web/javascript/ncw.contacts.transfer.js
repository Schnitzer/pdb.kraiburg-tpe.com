ncw.contacts.transfer = {};

/**
 * creates a preview of importing csv-file
 */
ncw.contacts.transfer.showImportPreview = function () {
	$("#ncw-contact-importcsv_form").attr(
		'action', 
		ncw.url('/contacts/transfer/previewCsv/')
	);
	$("#ncw-contact-importcsv_form").submit();
};
