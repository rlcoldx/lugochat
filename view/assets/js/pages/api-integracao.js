$(document).ready(function () {
    var DOMAIN = $('body').data('domain');

    /**
     * Atualiza Status e Acessos usando a API do DataTables (.rows().every),
     * porque linhas em outras páginas não aparecem no tbody do DOM.
     */
    function applyTokensPayload(data) {
        if (!data || !data.tokens || !data.tokens.length) {
            return;
        }

        function patchRow($tr, t) {
            if (!$tr || !$tr.length) {
                return;
            }
            var acessos = typeof t.acessos !== 'undefined' && t.acessos !== null ? Number(t.acessos) : 0;
            var badge = t.online
                ? '<span class="badge bg-success">Online</span>'
                : '<span class="badge bg-danger">Offline</span>';
            $tr.find('td.api-col-status').first().html(badge);
            $tr.find('td.api-col-acessos').first().html('<span class="fs-5 fw-bold">' + acessos + '</span>');
        }

        var map = {};
        data.tokens.forEach(function (t) {
            map[String(t.id)] = t;
        });

        if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#datatable')) {
            $('#datatable').DataTable().rows().every(function () {
                var $tr = $(this.node());
                var id = $tr.attr('data-token-id');
                if (id && map[id]) {
                    patchRow($tr, map[id]);
                }
            });
            return;
        }

        $('#datatable tbody tr[data-token-id]').each(function () {
            var $tr = $(this);
            var id = $tr.attr('data-token-id');
            if (id && map[id]) {
                patchRow($tr, map[id]);
            }
        });
    }

    function getTokensRefreshUrl() {
        if (typeof window.BDM_API_INTEGRACAO_REFRESH_URL === 'string' && window.BDM_API_INTEGRACAO_REFRESH_URL.length) {
            return window.BDM_API_INTEGRACAO_REFRESH_URL;
        }
        var $wrap = $('.table-responsive[data-api-tokens-refresh]');
        return $wrap.length ? $wrap.attr('data-api-tokens-refresh') : '';
    }

    function refreshApiTokensTable() {
        var url = getTokensRefreshUrl();
        if (!url) {
            return;
        }
        var sep = url.indexOf('?') >= 0 ? '&' : '?';
        $.ajax({
            url: url + sep + '_=' + Date.now(),
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function (data) {
                applyTokensPayload(data);
            }
        });
    }

    var apiTokensPollingStarted = false;

    function startApiTokensAutoRefresh() {
        if (apiTokensPollingStarted) {
            return;
        }
        if (!document.getElementById('datatable') || !getTokensRefreshUrl()) {
            return;
        }
        apiTokensPollingStarted = true;
        refreshApiTokensTable();
        setInterval(refreshApiTokensTable, 10000);
    }

    // DataTables é inicializado no script anterior (sync); um tick evita corrida com o layout
    setTimeout(startApiTokensAutoRefresh, 250);
    $(window).on('load', function () {
        startApiTokensAutoRefresh();
    });

    // Inicializa select2 quando o modal é aberto
    $('#tokenmodal').on('shown.bs.modal', function () {
        $('.select2').select2({
            dropdownParent: $('#tokenmodal'),
            placeholder: "Selecione o Motel",
            allowClear: false,
            width: '100%'
        });
    });

    // Destroy select2 quando o modal é fechado para evitar duplicação
    $('#tokenmodal').on('hidden.bs.modal', function () {
        if ($('.select2').data('select2')) {
            $('.select2').select2('destroy');
        }
    });

    // Submissão do formulário de salvar token
    $('#salvar_token').submit(function(e){
        e.preventDefault();
        var formData = new FormData(this);
        $('#salvar').prop('disabled', true);

        $.ajax({
            url: DOMAIN + '/api/integracao/salvar',
            data: formData,
            type: 'POST',
            success: function(data){
                try {
                    var response = typeof data === 'string' ? JSON.parse(data) : data;
                    
                    if (response.success) {
                        Swal.fire('', 'Token salvo com sucesso!', 'success');
                        setTimeout(function(){
                            location.reload();
                        }, 1500);
                    } else {
                        $('#salvar').prop('disabled', false);
                        Swal.fire({
                            type: 'error',
                            title: 'ERRO!',
                            text: response.erro || 'Erro ao salvar token. Tente novamente.',
                            showConfirmButton: true
                        });
                    }
                } catch(e) {
                    $('#salvar').prop('disabled', false);
                    Swal.fire({
                        type: 'error',
                        title: 'ERRO!',
                        text: 'Erro ao processar resposta. Tente novamente.',
                        showConfirmButton: true
                    });
                }
            },
            error: function() {
                $('#salvar').prop('disabled', false);
                Swal.fire({
                    type: 'error',
                    title: 'ERRO!',
                    text: 'Erro ao comunicar com o servidor. Tente novamente.',
                    showConfirmButton: true
                });
            },
            processData: false,
            cache: false,
            contentType: false
        });
    });

    // Copiar token
    $(document).on('click', '.copy-token, .copy-token-btn', function(e){
        e.preventDefault();
        var token = $(this).data('token');
        
        // Cria um elemento temporário
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(token).select();
        document.execCommand("copy");
        $temp.remove();
        
        // Feedback visual
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Token copiado!',
            showConfirmButton: false,
            timer: 2000
        });
    });

    // Gerar novo token
    $(document).on('click', '.gerar-novo-token', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Gerar Novo Token?',
            text: "O token atual será invalidado e um novo será gerado. Esta ação não pode ser desfeita!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, gerar novo!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: DOMAIN + '/api/integracao/gerar-token/' + id,
                    type: 'POST',
                    success: function(data){
                        try {
                            var response = typeof data === 'string' ? JSON.parse(data) : data;
                            
                            if (response.success) {
                                Swal.fire({
                                    title: 'Novo Token Gerado!',
                                    html: '<p>Copie o novo token antes de fechar:</p><div class="input-group mt-3"><input type="text" class="form-control" value="' + response.token + '" id="new-token-display" readonly><button class="btn btn-outline-secondary" type="button" onclick="copyNewToken(\'' + response.token + '\')"><i class="fa fa-copy"></i></button></div>',
                                    icon: 'success',
                                    confirmButtonText: 'Fechar e Recarregar',
                                    allowOutsideClick: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    type: 'error',
                                    title: 'ERRO!',
                                    text: response.erro || 'Erro ao gerar novo token.',
                                    showConfirmButton: true
                                });
                            }
                        } catch(e) {
                            Swal.fire({
                                type: 'error',
                                title: 'ERRO!',
                                text: 'Erro ao processar resposta.',
                                showConfirmButton: true
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            type: 'error',
                            title: 'ERRO!',
                            text: 'Erro ao comunicar com o servidor.',
                            showConfirmButton: true
                        });
                    }
                });
            }
        });
    });

    // Deletar token
    $(document).on('click', '.deletar-token', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Deletar Token?',
            text: "Esta ação não pode ser desfeita!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, deletar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: DOMAIN + '/api/integracao/deletar/' + id,
                    type: 'POST',
                    success: function(data){
                        try {
                            var response = typeof data === 'string' ? JSON.parse(data) : data;
                            
                            if (response.success) {
                                Swal.fire('Deletado!', 'Token deletado com sucesso.', 'success');
                                $('.token-' + id).fadeOut(300, function(){
                                    $(this).remove();
                                });
                            } else {
                                Swal.fire({
                                    type: 'error',
                                    title: 'ERRO!',
                                    text: 'Erro ao deletar token.',
                                    showConfirmButton: true
                                });
                            }
                        } catch(e) {
                            Swal.fire({
                                type: 'error',
                                title: 'ERRO!',
                                text: 'Erro ao processar resposta.',
                                showConfirmButton: true
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            type: 'error',
                            title: 'ERRO!',
                            text: 'Erro ao comunicar com o servidor.',
                            showConfirmButton: true
                        });
                    }
                });
            }
        });
    });
});

// Função global para copiar novo token (usada no SweetAlert)
function copyNewToken(token) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(token).select();
    document.execCommand("copy");
    $temp.remove();
    
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Token copiado!',
        showConfirmButton: false,
        timer: 2000
    });
}

