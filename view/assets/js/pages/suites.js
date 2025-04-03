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
                    $('#id_suite').val(data.id);
                    $('#suite_nome').removeClass('nova_suite');
                    $('#suite_nome').addClass('editar_suite');

                    var new_url = DOMAIN+'/suites/edit/'+data.id;
                    window.history.pushState('data','Title', new_url);
                    document.nome = 'Editar: '+data.nome;

                    $('.edit-slug-box').show();
                    start_product_gallery();
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