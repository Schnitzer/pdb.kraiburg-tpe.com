/**
 * modules
 */
ncw.core.modules = function () {
};

ncw.core.modules.install = function (url) {
	ncw.layout.dialogs.confirm(
		T_('Install module'),
		T_('Do you really want to install this Module?'),
		function () {
			window.location.href = url;
		}
	);
};

ncw.core.modules.deinstall = function (url) {
	ncw.layout.dialogs.confirm(
		T_('Deinstall module'),
		T_('Do you really want to deinstall this Module?'),
		function () {
			window.location.href = url;
		}
	);
};
