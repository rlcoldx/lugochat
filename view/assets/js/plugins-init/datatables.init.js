(function($) {
    "use strict"

    var table = $('#datatable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json',
        },
        createdRow: function ( row, data, index ) {
           $(row).addClass('selected');
        } 
    });
      
    table.on('click', 'tbody tr', function() {
        var $row = table.row(this).nodes().to$();
        var hasClass = $row.hasClass('selected');
        if (hasClass) {
            $row.removeClass('selected');
        } else {
            $row.addClass('selected');
        }
    })
    
    table.rows().every(function() {
        this.nodes().to$().removeClass('selected');
    });

})(jQuery);