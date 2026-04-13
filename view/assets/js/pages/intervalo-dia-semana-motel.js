$(document).on('submit', '#salvar_intervalo_dia_semana', function (e) {
    e.preventDefault();
    var DOMAIN = $('body').data('domain');
    var $btn = $('#intervalo_dia_semana_btn_salvar');
    $btn.prop('disabled', true);

    $.ajax({
        url: DOMAIN + '/intervalos-dia-semana/save',
        type: 'POST',
        data: new FormData(this),
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            $btn.prop('disabled', false);
            if (data && data.ok) {
                $('#intervalosemanamodal').modal('hide');
                Swal.fire({ icon: 'success', title: 'Salvo com sucesso!', showConfirmButton: false, timer: 1200 });
                setTimeout(function () { location.reload(); }, 1200);
            } else {
                var msg = 'Não foi possível salvar.';
                if (data && data.code === 'validacao') {
                    msg = 'Preencha dia da semana e horários.';
                } else if (data && data.code === 'salvar') {
                    msg = 'Confira se o intervalo faz sentido (ex.: no mesmo dia, hora fim deve ser depois da hora início).';
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

    $(document).on('click', '.btn-excluir-intervalo-dia-semana', function () {
        var regraId = $(this).data('regra-id');
        if (!regraId) {
            return;
        }

        Swal.fire({
            title: 'Excluir este intervalo?',
            text: 'Esta ação não pode ser desfeita.',
            showCancelButton: true,
            cancelButtonText: 'Não',
            confirmButtonText: 'Sim, excluir',
            confirmButtonColor: '#fe634e'
        }).then(function (result) {
            if (!(result.isConfirmed === true || result.value === true)) {
                return;
            }
            $.post(DOMAIN + '/intervalos-dia-semana/delete', { id_regra: regraId })
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
