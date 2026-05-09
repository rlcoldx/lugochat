$(document).ready(function () {
    var DOMAIN = $('body').data('domain');

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

