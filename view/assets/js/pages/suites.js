$(document).ready(function () {

    if ($("#texto").length > 0) {
        new FroalaEditor('#texto', {
            key: "1C%kZV[IX)_SL}UJHAEFZMUJOYGYQE[\\ZJ]RAe(+%$==",
            enter: FroalaEditor.ENTER_BR,
            placeholderText: 'Essa Suíte possuí...',
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
	
	//SALVA SUITE
	$('.nova_suite').focusout(function(e){

        e.preventDefault();
        var nome = $(this).val();
        var slug = format_slug(nome, '');

        if(nome != ''){
            var DOMAIN = $('body').data('domain');
            $.ajax({
                url: DOMAIN + '/suites/save_draft',
                data: {'nome': nome, 'slug': slug},
                type: 'POST',
                success: function(data){
                    // Redirecionar para a página de edição em vez de modificar a URL
                    var new_url = DOMAIN+'/suites/edit/'+data.id+'?t=c';
                    window.location.href = new_url;
                }
            });
        }
    });

	//EDITAR SUITE
    $('#cadastrar_suite').submit(function(e){

		$(this).children(':input[value=""]').attr("disabled", "disabled");
		var DOMAIN = $('body').data('domain');
		$('#salvar').prop('type', 'button');
		$('#salvar').addClass('disabled');
		e.preventDefault();

		var formData = new FormData(this);
        
		$.ajax({
			url: DOMAIN + '/suites/editar/save',
			data: formData,
			type: 'POST',
			success: function(data){

				if (data == 'success') {

                    Swal.fire({icon: 'success', title: 'SALVO COM SUCESSO!', showConfirmButton: false, timer: 1500});
                    setTimeout(function(){
                        location.reload();
                    }, 1500);

				}else{

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
                $('#price_chance').val('sim');
                reloadScript();
            },
            hide: function (deleteElement) {
                if(confirm('Tem certeza de que deseja excluir este preço?')) {
                    $(this).slideUp(deleteElement);
                }
                $('#price_chance').val('sim');
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

    $('.money').mask("#.##0,00", {reverse: true});
    $('.hour').mask("00:00", {reverse: true});

    // Validação para campos de hora
    $('.hour').on('blur', function() {
        const value = $(this).val();
        const hourRegex = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
        
        if (value && !hourRegex.test(value)) {
            // Mostrar erro no campo
            $(this).addClass('is-invalid');
            
            // Criar ou atualizar mensagem de erro
            let errorMsg = $(this).siblings('.invalid-feedback');
            if (errorMsg.length === 0) {
                errorMsg = $('<div class="invalid-feedback">Use HH:MM (ex: 01:00)</div>');
                $(this).after(errorMsg);
            }
            
            // Focar no campo
            $(this).focus();
        } else {
            // Remover classes de erro se o formato estiver correto
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });

    // Controle de exibição do campo pernoite
    $(document).on('change', 'select[name*="[periodo]"]', function() {
        const isPernoite = $(this).val() === 'Pernoite';
        
        // Buscar o campo pernoite no mesmo item do repeater
        const pernoiteField = $(this).closest('[data-repeater-item]').find('.pernoite-field');
        
        if (isPernoite) {
            pernoiteField.show();
        } else {
            pernoiteField.hide();
            pernoiteField.find('input').val(''); // Limpar o valor quando esconder
        }
    });

    // Inicializar campos pernoite baseado nos valores existentes
    $('select[name*="[periodo]"]').each(function() {
        const isPernoite = $(this).val() === 'Pernoite';
        
        // Buscar o campo pernoite no mesmo item do repeater
        const pernoiteField = $(this).closest('[data-repeater-item]').find('.pernoite-field');
        
        if (isPernoite) {
            pernoiteField.show();
        }
    });

    // Função para aplicar máscara e validação nos campos de hora
    function applyHourMaskAndValidation() {
        $('.hour').each(function() {
            // Aplicar máscara se ainda não foi aplicada
            if (!$(this).data('mask-applied')) {
                $(this).mask("00:00", {reverse: true});
                $(this).data('mask-applied', true);
            }
            
            // Aplicar validação se ainda não foi aplicada
            if (!$(this).data('validation-applied')) {
                $(this).on('blur', function() {
                    const value = $(this).val();
                    const hourRegex = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
                    
                    if (value && !hourRegex.test(value)) {
                        // Mostrar erro no campo
                        $(this).addClass('is-invalid');
                        
                        // Criar ou atualizar mensagem de erro
                        let errorMsg = $(this).siblings('.invalid-feedback');
                        if (errorMsg.length === 0) {
                            errorMsg = $('<div class="invalid-feedback">Use HH:MM (ex: 01:00)</div>');
                            $(this).after(errorMsg);
                        }
                        
                        // Focar no campo
                        $(this).focus();
                    } else {
                        // Remover classes de erro se o formato estiver correto
                        $(this).removeClass('is-invalid');
                        $(this).siblings('.invalid-feedback').remove();
                    }
                });
                $(this).data('validation-applied', true);
            }
        });
    }

    // Função para limpar checkboxes em novos itens do repeater
    function clearNewRepeaterItems() {
        // Encontrar o item mais recente do repeater (último adicionado)
        const repeaterItems = $('[data-repeater-item]');
        if (repeaterItems.length > 0) {
            const lastItem = repeaterItems.last();
            
            // Remover classe checked de todos os labels de checkbox
            lastItem.find('.dia-checkbox').removeClass('checked');
            
            // Desmarcar todos os checkboxes
            lastItem.find('input[type="checkbox"]').prop('checked', false);
            
            // Limpar valores dos campos
            lastItem.find('input[type="text"]').val('');
            lastItem.find('select').prop('selectedIndex', 0);
        }
    }

    // Aplicar máscara e validação inicialmente
    applyHourMaskAndValidation();

    // Observar mudanças no DOM para detectar novos campos
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        if ($(node).find('.hour').length > 0 || $(node).hasClass('hour')) {
                            setTimeout(function() {
                                applyHourMaskAndValidation();
                                clearNewRepeaterItems();
                            }, 50);
                        }
                    }
                });
            }
        });
    });

    // Iniciar observação
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Backup: Evento para o botão Add do repeater
    $(document).on('click', '[data-repeater-create]', function() {
        setTimeout(function() {
            applyHourMaskAndValidation();
            clearNewRepeaterItems();
        }, 200);
    });

    $('.price_chance').change(function(){
        $('#price_chance').val('sim');
    });

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

function deleteSuite(id_suite) {
	Swal.fire({
		title: "Deletar essa Suíte?",
		text: "Tem certeza que deseja excluir essa Suíte. Essa ação não poderá ser desfeita.",
		showCancelButton: true,
		cancelButtonText: 'Não',
		confirmButtonText: 'Sim',
		dangerMode: true,
	}).then((result) => {
		  if (result.value === true) {
			deleteSuiteAction(id_suite);
			Swal.fire('', 'Suíte excluida com sucesso!', 'success');
		  }
	});
}

function deleteSuiteAction(id_suite){
	let DOMAIN = $('body').data('domain');
	$.ajax({
		url: DOMAIN + '/suites/excluir',
		data: {'id_suite': id_suite},
		type: 'post',
		success: function(data){
            setTimeout(function(){
                location.reload();
            }, 1500);
		}
	});
}

function duplicateSuiteAction(id_suite){
    let DOMAIN = $('body').data('domain');
    Swal.fire({
		title: "Duplicar essa Suíte?",
		text: "Tem certeza que deseja duplicar essa Suíte.",
		showCancelButton: true,
		cancelButtonText: 'Não',
		confirmButtonText: 'Sim',
		dangerMode: true,
	}).then((result) => {
		if (result.value === true) {
			$.ajax({
                url: DOMAIN + '/suites/duplicate',
                data: {'id': id_suite},
                type: 'get',
                success: function(data){
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                }
            });
			Swal.fire('', 'Suíte duplicada com sucesso!', 'success');
		}
	});
	
}


//SISTEMA SIS
$(document).ready(function() {
    $('select[name="sis_suite"]').on('change', function() {
      var total = $(this).find(':selected').data('total');
      var free = $(this).find(':selected').data('free');
  
      $('#sis_total').val(total || 0);
      $('#sis_free').val(free || 0);
    });
});

// Função para alternar a classe "checked" no label dos dias da semana
function toggleCheckbox(checkbox) {
    const label = checkbox.closest('.dia-checkbox');
    if (checkbox.checked) {
        label.classList.add('checked');
    } else {
        label.classList.remove('checked');
    }
}

// Inicializar checkboxes já marcados
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.dia-checkbox input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            checkbox.closest('.dia-checkbox').classList.add('checked');
        }
    });
});