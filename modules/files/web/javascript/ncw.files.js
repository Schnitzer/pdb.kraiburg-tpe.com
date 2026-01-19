ncw.files = {};
ncw.files.tree = {};

ncw.files.window_mode = 0;

/**
 * on resize change tree height
 */
ncw.files.tree.resize = function () {
    var height = $('.ncw-accordion-content').height();
    $('.ncw-tree').siblings().each(function () {
        var element_height = $(this).height() + parseInt($(this).css('padding-top')) 
            + parseInt($(this).css('padding-bottom')) + parseInt($(this).css('margin-top')) 
            + parseInt($(this).css('margin-bottom')) + parseInt($(this).css('border-top-width')) + parseInt($(this).css('border-bottom-width'));
        height = height - element_height; 
    });
    height = height - 10;
    $('.ncw-tree').height(height);
};