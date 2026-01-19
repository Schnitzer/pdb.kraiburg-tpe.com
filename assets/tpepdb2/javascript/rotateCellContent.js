(function ($) {
  $.fn.rotateCellContent = function (options) {
    var cssClass = ((options) ? options.className : false) || "vertical";

    var cellsToRotate = $('.' + cssClass, this);

    cellsToRotate.each(
        function () {
            var cell = $(this);
            var rotated_child = $(this).children('.rotated');

            var cell_width = cell.width();

            //rotated_child.css('height', (cell_width / 2) + 'px');
        }
    );
   };
})(jQuery);