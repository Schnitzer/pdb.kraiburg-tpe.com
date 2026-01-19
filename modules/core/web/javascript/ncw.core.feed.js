ncw.core.feed = {};

ncw.core.feed.reload = function (action) {
	if (action != 'save') {
		window.location.href = ncw.url('core/feed/all');
	}
};