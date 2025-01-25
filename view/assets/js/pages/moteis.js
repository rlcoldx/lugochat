$(document).ready(function () {

    if ($('.tags').length > 0) {
        $('.tags').tagsinput();
    };

    $("#cep").blur(function() {
        var cep = $(this).val().replace(/\D/g, '');
        if (cep != '') {
            var validacep = /^[0-9]{8}$/;
            if(validacep.test(cep)) {
                $.getJSON("//viacep.com.br/ws/"+ cep +"/json/?callback=?", function(dados) {
                    if (!("erro" in dados)) {
                        $('#endereco').val(dados.logradouro);
                        $("#bairro").val(dados.bairro);
                        $("#cidade").val(dados.localidade);
                        $("#estado").val(dados.uf);
                    }
                    else {
                        limpa_formulário_cep();
                        alert("CEP não encontrado.");
                    }
                });
            } else {
                limpa_formulário_cep();
                alert("Formato de CEP inválido.");
            }
        }
        else {
            limpa_formulário_cep();
        }
    });

    if ($("#texto").length > 0) {
        new FroalaEditor('#texto', {
            key: "1C%kZV[IX)_SL}UJHAEFZMUJOYGYQE[\\ZJ]RAe(+%$==",
            enter: FroalaEditor.ENTER_BR,
            placeholderText: 'Obervações ou Regras...',
            heightMin: 100,
            language: 'pt_br',
            pastePlain: true, // Habilita a limpeza HTML
            pasteDeniedTags: ['script', 'style'], // Remove tags específicas
            pasteDeniedAttrs: ['id', 'class'], // Remove atributos específicos
            pasteAllowedStyleProps: [], // Remove estilos inline
            attribution: false,
            toolbarButtons: {
                'moreText': {
                    'buttons': ['bold', 'italic', 'underline', 'strikeThrough', 'fontSize', 'clearFormatting', 'formatOL', 'formatUL', 'outdent', 'indent'],
                    'buttonsVisible': 6
                },
                'moreParagraph': {
                    'buttons': ['alignLeft', 'alignCenter',  'alignRight']
                },
                'moreRich': {
                    'buttons': ['emoticons', 'fontAwesome']
                },
                'moreMisc': {
                    'buttons': ['undo', 'redo'],
                    'align': 'right'
                }
            }
        });
    }
	
	//SALVA motel
	$('#cadastrar_motel').submit(function(e){

        $(this).children(':input[value=""]').attr("disabled", "disabled");
		var DOMAIN = $('body').data('domain');
		$('#salvar').html('<i class="fa-solid fa-sync fa-spin"></i> SALVANDO');
		$('#salvar').prop('type', 'button');
		$('#salvar').addClass('disabled');
		e.preventDefault();

        var formData = new FormData(this);

        var DOMAIN = $('body').data('domain');
        $.ajax({
            url: DOMAIN + '/moteis/add/save',
            data: formData,
            type: 'POST',
			processData: false,
			cache: false,
			contentType: false,
            success: function(data){
                if(data != ''){
                    Swal.fire({icon: 'success', title: 'SALVO COM SUCESSO!', showConfirmButton: false, timer: 1500});
                    let id = data.match(/([a-zA-Z]+)(\d+)/);
                    setTimeout(function(){
                        window.location.href = DOMAIN+'/moteis/edit/'+id[2];
                    }, 1500);
                }else{
                    $('#salvar').html('SALVAR');
                    $('#salvar').prop('type', 'submit');
                    $('#salvar').removeClass('disabled');
                    Swal.fire({icon: 'error', title: 'ERRO AO SALVAR!', showConfirmButton: false, timer: 1500});
                }
            }
        });
        
    });

	//EDITAR motel
    $('#editar_motel').submit(function(e){

		$(this).children(':input[value=""]').attr("disabled", "disabled");
		var DOMAIN = $('body').data('domain');
		$('#salvar').html('<i class="fa-solid fa-sync fa-spin"></i> SALVANDO');
		$('#salvar').prop('type', 'button');
		$('#salvar').addClass('disabled');
		e.preventDefault();

		var formData = new FormData(this);
        
		$.ajax({
			url: DOMAIN + '/moteis/edit/save',
			data: formData,
			type: 'POST',
            processData: false,
			cache: false,
			contentType: false,
			success: function(data){
				if (data == 'success') {
                    Swal.fire({icon: 'success', title: 'SALVO COM SUCESSO!', showConfirmButton: false, timer: 1500});
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
				}else{
                    $('#salvar').html('SALVAR');
					$('#salvar').prop('type', 'submit');
					$('#salvar').removeClass('disabled');
                    Swal.fire({icon: 'error', title: 'ERRO AO SALVAR!', showConfirmButton: false, timer: 1500});
				}
			}
		});
	});

    $('.telefone').mask('(00) #0000-0000');
    $('#cep').mask("00000-000", {reverse: true});
    $('.money').mask("#.##0,00", {reverse: true});

    if ($('.sumoselect').length) {
    	$('.sumoselect').SumoSelect({
			search : true,
			placeholder: 'Selecione uma Cor',
    		searchText : 'Pesquisar',
            triggerChangeCombined: true
		});
    }

});

function reloadScript() {
    $('.money').mask("#.##0,00", {reverse: true});
}

// Função para alternar a classe "active" no label
function toggleCheckbox(checkbox) {
    var label = checkbox.parentElement;
    if (checkbox.checked) {
        label.classList.add("active");
    } else {
        label.classList.remove("active");
    }
}

function format_slug(nome, slug) {
    nome = nome.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
    nome = nome.replace(/[^a-z0-9 ]/gi, '');
    nome = nome.replace(/ +(?= )/g,'');
    nome = nome.replace(/^-+/, '');
    nome = nome.replace(/ /g, '-');
    if(slug != ''){
        $('#slug').val(nome.toLowerCase());
    }else{
        return nome.toLowerCase();
    }
}

function deleteMotel(id_motel, status) {

    if(status == 'Ativo') { 
        var title_content = 'Ativar esse Motel?';
        var text_content = 'Tem certeza que deseja ativar esse Motel. Ele voltará de aparecer na lista do aplicativo.';
    }else{
        var title_content = 'Desativar esse Motel?';
        var text_content = 'Tem certeza que deseja desativar esse Motel. Ele deixará de aparecer na lista do aplicativo.';
    }

	Swal.fire({
		title: title_content,
		text: text_content,
		showCancelButton: true,
		cancelButtonText: 'Não',
		confirmButtonText: 'Sim',
		dangerMode: true,
	}).then((result) => {
		  if (result.value === true) {
			let DOMAIN = $('body').data('domain');
            $.ajax({
                url: DOMAIN + '/moteis/excluir',
                data: {'id_motel': id_motel, 'status': status},
                type: 'post',
                success: function(data){
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                    Swal.fire('', 'Motel desativado com sucesso!', 'success');
                }
            });
		  }
	});
}

function limpa_formulário_cep() {
    $('#endereco').val('');
    $("#bairro").val('');
    $("#cidade").val('');
    $("#estado").val('');
    $("#numero").val('');
}