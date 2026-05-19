$(document).on('submit', '#salvar_fechamento', function (e) {
    e.preventDefault();
    var DOMAIN = $('body').data('domain');
    var $btn = $('#fechamento_btn_salvar');
    var $form = $(this);

    var selecionados = $form.find('input[name="id_motel[]"]');
    if (!selecionados.length) {
        Swal.fire({ icon: 'warning', title: 'Selecione ao menos um motel.', showConfirmButton: true });
        return;
    }

    $btn.prop('disabled', true);

    $.ajax({
        url: DOMAIN + '/admin/motel-fechamentos/save',
        type: 'POST',
        data: new FormData(this),
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            $btn.prop('disabled', false);
            if (data && data.ok) {
                $('#fechamentomodal').modal('hide');
                Swal.fire({ icon: 'success', title: 'Salvo com sucesso!', showConfirmButton: false, timer: 1200 });
                setTimeout(function () { location.reload(); }, 1200);
            } else {
                var msg = 'Não foi possível salvar.';
                if (data && data.code === 'duplicado') {
                    msg = 'Um ou mais motéis já estão fechados nesta data.';
                } else if (data && data.code === 'validacao') {
                    msg = 'Preencha data, proprietário e selecione ao menos um motel.';
                } else if (data && data.code === 'motel_invalido') {
                    msg = 'Motéis inválidos para o proprietário selecionado.';
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

/**
 * @param {Array} moteis
 * @param {Array|null} idsSelecionados — em edição, lista de ids já salvos
 * @param {boolean} marcarTodosPorPadrao — true: todos marcados; clique desmarca
 */
function fechamentoRenderTags(moteis, idsSelecionados, marcarTodosPorPadrao) {
    var $wrap = $('#fechamento_moteis_tags');
    $wrap.empty();

    if (!moteis || !moteis.length) {
        $wrap.append('<span class="small text-secondary">Nenhum motel ativo encontrado para este proprietário.</span>');
        return;
    }

    if (typeof marcarTodosPorPadrao === 'undefined') {
        marcarTodosPorPadrao = true;
    }

    var mapSel = {};
    if (!marcarTodosPorPadrao && idsSelecionados && idsSelecionados.length) {
        idsSelecionados.forEach(function (id) {
            mapSel[String(id)] = true;
        });
    }

    moteis.forEach(function (m) {
        var id = String(m.id);
        var ativo = marcarTodosPorPadrao ? true : !!mapSel[id];
        var $tag = $('<span role="button" tabindex="0" class="badge fechamento-motel-tag me-1 mb-1 ' + (ativo ? 'bg-primary' : 'bg-secondary') + '"></span>');
        $tag.text(m.nome);
        $tag.attr('data-id', id);
        $tag.attr('data-selected', ativo ? '1' : '0');

        if (ativo) {
            $tag.append('<input type="hidden" name="id_motel[]" value="' + id + '">');
        }

        $tag.on('click', function () {
            var sel = $(this).attr('data-selected') === '1';
            if (sel) {
                $(this).removeClass('bg-primary').addClass('bg-secondary');
                $(this).attr('data-selected', '0');
                $(this).find('input[name="id_motel[]"]').remove();
            } else {
                $(this).removeClass('bg-secondary').addClass('bg-primary');
                $(this).attr('data-selected', '1');
                var mid = $(this).attr('data-id');
                $(this).append('<input type="hidden" name="id_motel[]" value="' + mid + '">');
            }
        });

        $wrap.append($tag);
    });
}

function fechamentoCarregarMoteis(proprietario, idsPreSelecionados, marcarTodosPorPadrao) {
    var DOMAIN = $('body').data('domain');
    var $wrap = $('#fechamento_moteis_tags');
    if (!proprietario) {
        $wrap.html('<span class="small text-secondary" id="fechamento_moteis_placeholder">Selecione um proprietário para listar os motéis.</span>');
        return;
    }

    if (typeof marcarTodosPorPadrao === 'undefined') {
        marcarTodosPorPadrao = true;
    }

    $wrap.html('<span class="small text-secondary"><i class="fa-solid fa-circle-notch fa-spin"></i> Carregando motéis...</span>');

    $.getJSON(DOMAIN + '/admin/motel-fechamentos/moteis-por-proprietario', { proprietario: proprietario })
        .done(function (data) {
            fechamentoRenderTags(data.moteis || [], idsPreSelecionados || [], marcarTodosPorPadrao);
        })
        .fail(function () {
            $wrap.html('<span class="small text-danger">Erro ao carregar motéis.</span>');
        });
}

$(document).on('change', '#fechamento_proprietario', function () {
    fechamentoCarregarMoteis($(this).val(), [], true);
});

$(document).on('shown.bs.modal', '#fechamentomodal', function () {
    var prop = $('#fechamento_proprietario').val();
    var raw = $('#fechamento_moteis_selecionados').val();
    var isEdicao = raw !== undefined && raw !== '';
    var ids = [];
    if (raw) {
        ids = raw.split(',').filter(function (x) { return x !== ''; });
    }
    if (prop) {
        fechamentoCarregarMoteis(prop, ids, !isEdicao);
    }
});

$(document).ready(function () {
    var DOMAIN = $('body').data('domain');

    $(document).on('click', '.btn-excluir-fechamento', function () {
        var idGrupo = $(this).data('id-grupo');
        if (!idGrupo) {
            return;
        }

        Swal.fire({
            title: 'Excluir este fechamento?',
            text: 'Todos os motéis deste cadastro serão removidos desta data.',
            showCancelButton: true,
            cancelButtonText: 'Não',
            confirmButtonText: 'Sim, excluir',
            confirmButtonColor: '#fe634e'
        }).then(function (result) {
            if (!(result.isConfirmed === true || result.value === true)) {
                return;
            }
            $.post(DOMAIN + '/admin/motel-fechamentos/delete', { id_grupo: idGrupo })
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
