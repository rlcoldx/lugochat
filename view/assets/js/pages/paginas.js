$(document).ready(function () {
    if ($("#texto").length > 0) {
        new FroalaEditor('#texto',  {
            key: "1C%kZV[IX)_SL}UJHAEFZMUJOYGYQE[\\ZJ]RAe(+%$==",
            enter: FroalaEditor.ENTER_BR,
            placeholderText: 'Digite o conteúdo...',
            heightMin: 100,
            language: 'pt_br',
            pastePlain: true, // Habilita a limpeza HTML
            pasteDeniedTags: ['script', 'style'], // Remove tags específicas
            pasteDeniedAttrs: ['id', 'class'], // Remove atributos específicos
            pasteAllowedStyleProps: [], // Remove estilos inline
            attribution: false,
            toolbarButtons: ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', '|', 'fontFamily', 'fontSize', 'color', 'inlineStyle', 'paragraphStyle', '|', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'outdent', 'indent', 'quote', '-', 'insertLink', 'insertImage', 'insertVideo', 'insertFile', 'insertTable', '|', 'emoticons', 'specialCharacters', 'insertHR', 'selectAll', 'clearFormatting', '|', 'print', 'help', 'html', '|', 'undo', 'redo','trackChanges','markdown']
        });
    }
	
	//SALVA pagina
	$('.novo_pagina').focusout(function(e){

        e.preventDefault();
        var titulo = $(this).val();
        var slug = format_slug(titulo, '');

        if(titulo != ''){
            var DOMAIN = $('body').data('domain');
            $.ajax({
                url: DOMAIN + '/paginas/save_draft',
                data: {'titulo': titulo, 'slug': slug},
                type: 'POST',
                success: function(data){
                    $('#id_pagina').val(data[0].id);
                    $('#titulo').removeClass('novo_pagina');
                    $('#titulo').addClass('editar_pagina');

                    var new_url = DOMAIN+'/paginas/edit/'+data[0].id;
                    window.history.pushState('data','Title', new_url);
                    document.titulo = 'Editar: '+data[0].titulo;

                    $('.edit-slug-box').show();
                }
            });
        }
    });

	//EDITAR pagina
    $('#cadastrar_pagina').submit(function(e){

		$(this).children(':input[value=""]').attr("disabled", "disabled");
		var DOMAIN = $('body').data('domain');
		$('#salvar').html('<i class="fa-solid fa-sync fa-spin"></i> SALVANDO');
		$('#salvar').prop('type', 'button');
		$('#salvar').addClass('disabled');
		e.preventDefault();

		var formData = new FormData(this);
        
		$.ajax({
			url: DOMAIN + '/paginas/editar/save',
			data: formData,
			type: 'POST',
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
			},
			processData: false,
			cache: false,
			contentType: false
		});
	});

    if ($(".repeater").length > 0) {
        $('.repeater').repeater({
            initEmpty: false,
            show: function () {
                $(this).slideDown();
                reloadScript();
            },
            hide: function (deleteElement) {
                if(confirm('Tem certeza de que deseja excluir essa?')) {
                    $(this).slideUp(deleteElement);
                }
                reloadScript();
            },
            ready: function(setIndexes) {

                // console.log('ready: ', setIndexes);
                //$dragAndDrop.on('drop', setIndexes);

                //Actualizar indices en el titulo
                //$("div[data-repeater-item]").each(function(index, value){
                    //console.log('ready:\n\t')
                    //console.log('index:' + index + '\n\t'); //index desde cero
                    //console.log('value:', value); //value es el item o elmento html <div>...</div>
                    //value.attr('data-index', index);
                    //$(value).find('a > h4 > span.repeaterItemNumber').text(index + 1);
                //});
            },
            isFirstItemUndeletable: true
        });
    }

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

function format_slug(titulo, slug) {
    titulo = titulo.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
    titulo = titulo.replace(/[^a-z0-9 ]/gi, '');
    titulo = titulo.replace(/ +(?= )/g,'');
    titulo = titulo.replace(/^-+/, '');
    titulo = titulo.replace(/ /g, '-');
    if(slug != ''){
        $('#slug').val(titulo.toLowerCase());
    }else{
        return titulo.toLowerCase();
    }
}

function deleteRestaurante(id_pagina, status) {

    if(status == 'Ativo') { 
        var title_content = 'Ativar esse Restaurante?';
        var text_content = 'Tem certeza que deseja ativar esse Restaurante. Ele voltará de aparecer na lista do aplicativo.';
    }else{
        var title_content = 'Desativar esse Restaurante?';
        var text_content = 'Tem certeza que deseja desativar esse Restaurante. Ele deixará de aparecer na lista do aplicativo.';
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
                url: DOMAIN + '/paginas/excluir',
                data: {'id_pagina': id_pagina, 'status': status},
                type: 'post',
                success: function(data){
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                    Swal.fire('', 'Restaurante desativado com sucesso!', 'success');
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