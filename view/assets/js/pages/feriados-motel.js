$(document).on('submit', '#salvar_feriado', function (e) {
    e.preventDefault();
    var DOMAIN = $('body').data('domain');
    var $btn = $('#feriado_btn_salvar');
    $btn.prop('disabled', true);

    $.ajax({
        url: DOMAIN + '/feriados/save',
        type: 'POST',
        data: new FormData(this),
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            $btn.prop('disabled', false);
            if (data && data.ok) {
                $('#feriadomodal').modal('hide');
                Swal.fire({ icon: 'success', title: 'Salvo com sucesso!', showConfirmButton: false, timer: 1200 });
                setTimeout(function () { location.reload(); }, 1200);
            } else {
                var msg = 'Não foi possível salvar.';
                if (data && data.code === 'duplicado') {
                    msg = 'Já existe um feriado seu nesta data.';
                } else if (data && data.code === 'validacao') {
                    msg = 'Preencha data e nome corretamente.';
                }
                Swal.fire({ icon: 'warning', title: msg, showConfirmButton: true });
            }
        },
        error: function () {
            $btn.prop('disabled', false);
            Swal.fire({ icon: 'error', title: 'Erro na requisição', showConfirmButton: true });
        }
    });
});

$(document).ready(function () {
    var DOMAIN = $('body').data('domain');

    $(document).on('click', '.btn-excluir-feriado', function () {
        var date = $(this).data('date');
        if (!date) {
            return;
        }

        Swal.fire({
            title: 'Excluir este feriado?',
            text: 'Esta ação não pode ser desfeita.',
            showCancelButton: true,
            cancelButtonText: 'Não',
            confirmButtonText: 'Sim, excluir',
            confirmButtonColor: '#fe634e'
        }).then(function (result) {
            if (!(result.isConfirmed === true || result.value === true)) {
                return;
            }
            $.post(DOMAIN + '/feriados/delete', { date: date })
                .done(function (data) {
                    if (data === 'success') {
                        Swal.fire({ icon: 'success', title: 'Excluído', showConfirmButton: false, timer: 1200 });
                        setTimeout(function () { location.reload(); }, 1200);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Não foi possível excluir', showConfirmButton: true });
                    }
                })
                .fail(function () {
                    Swal.fire({ icon: 'error', title: 'Erro na requisição', showConfirmButton: true });
                });
        });
    });
});
