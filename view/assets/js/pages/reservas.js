$(document).ready(function () {

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));


    $("#edit_reserva").submit(function (c) {

        $('.form-load').addClass('show');
        $('button[type="submit"]').prop("disabled", true);

        c.preventDefault();
        var DOMAIN = $('body').data('domain');
        var form = $(this)[0];
        var formData = new FormData(form);

        $.ajax({
            type: "POST",
            processData: false,
            contentType: false,
            data: formData,
            url: DOMAIN + '/reservas/status/save',
            success: function (data) {
                if (data == "1") {
                    Swal.fire({icon: "success", title: "", html: "Editado com sucesso", timer: 1500, showConfirmButton: false});
                    setTimeout(function(){
                        window.location.reload();
                        $('.form-load').removeClass('show');
                    }, 1500);
                } else {
                    Swal.fire({icon: "error", title: "Error", html: "Contate o Suporte!", showConfirmButton: false});
                    $('button[type="submit"]').prop("disabled", false);
                    $('.form-load').removeClass('show');
                }
            }
        });

    });

});