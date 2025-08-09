(function($) {
  $.fn.resizableColumns = function(options) {
    const settings = $.extend({
      mode: 'px' // 
    }, options);
    return this.each(function() {
      const $table = $(this);
      const $cols = $table.find('thead th');
      $cols.each(function(i, col) {
        const $handle = $('<div class="rc-handle" />').css({
          position: 'absolute',
          top: 0,
          right: 0,
          width: '5px',
          height: '100%',
          cursor: 'col-resize',
          zIndex: 10
        });
        $(col).css('position', 'relative').append($handle);
        $handle.on('mousedown', function(e) {
          e.preventDefault();
          const startX = e.pageX;
          const $col = $(col);
          const startWidth = $col.outerWidth();
          const tableWidth = $table.outerWidth();
          $(document).on('mousemove.rc', function(e2) {
            const diff = e2.pageX - startX;
            let newWidth;
            if (settings.mode === 'percent') {
              const newPx = startWidth + diff;
              const percent = (newPx / tableWidth) * 100;
              newWidth = percent + '%';
            } else {
              newWidth = (startWidth + diff) + 'px';
            }
            $col.css('width', newWidth);
          });
          $(document).on('mouseup.rc', function() {
            $(document).off('.rc');
          });
        });
      });
    });
  };
})(jQuery);