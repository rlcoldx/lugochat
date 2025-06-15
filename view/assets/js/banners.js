$(document).ready(function(e){

    var DOMAIN = $('body').data('domain');
    // Initialize popovers
    $('[data-bs-toggle="popover"]').each(function() {
        const $this = $(this);
        $this.popover({
            trigger: 'click',
            html: true,
            content: function() {
                const imageId = $this.data('idimage');
                const name = $this.data('item');
                
                // Show loading state
                $this.attr('data-bs-content', 'Carregando...');
                
                // Load content from remote URL
                $.post(DOMAIN + '/banners/link', { id: imageId, nome: name }, function(response) {
                    $this.attr('data-bs-content', response);
                    $(`.popover-body`).html(response);
                });
                
                return 'Carregando...';
            }
        });
    });

    // Close popover when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.popover, [data-bs-toggle="popover"]').length) {
            $('[data-bs-toggle="popover"]').popover('hide');
        }
    });
}); 