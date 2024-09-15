$(document).ready(function () {

    setTimeout(() => {
        $('.nav-control').click();
    }, "100");

	new FroalaEditor('#question', {
		key: "1C%kZV[IX)_SL}UJHAEFZMUJOYGYQE[\\ZJ]RAe(+%$==",
		enter: FroalaEditor.ENTER_BR,
		language: 'pt_br',
		pastePlain: true,
		attribution: false,
		toolbarButtons: {
			'moreText': {
			  'buttons': ['bold', 'italic', 'underline', 'strikeThrough', 'fontSize', 'clearFormatting'],
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
	
	//SALVA A MENSAGEM DE BEM VINDO
	$("#bem_vindo").submit(function (c) {
        $('.login-load').show();

        c.preventDefault();
        var DOMAIN = $('body').data('domain');
        var form = $(this);
		
        $.ajax({
            type: "POST", async: true, data: form.serialize(),
            url: DOMAIN + '/chatbot/save/bem_vindo',
            success: function (data) {
                $('#info-login').hide();
                if (data == "1") {

					Swal.fire({icon: 'success', title: 'CADASTRADO COM SUCESSO!', showConfirmButton: false, timer: 1500});
                    setTimeout(function(){
                        location.reload();
                    }, 1500);

                } else {

					Swal.fire({icon: 'error', title: 'ERRRO AO ATUALIZAR!', showConfirmButton: false, timer: 1500});
                    $('button[type="submit"]').prop("disabled", false);
                    $('.login-load').hide();
                }
            }
        });
    });

	//SALVA A QUESTAO
	$("#edit_question").submit(function (c) {
        $('.login-load').show();

        c.preventDefault();
        var DOMAIN = $('body').data('domain');
        var form = $(this);
		
        $.ajax({
            type: "POST", async: true, data: form.serialize(),
            url: DOMAIN + '/chatbot/save/question/edit',
            success: function (data) {
                $('#info-login').hide();
                if (data == "1") {

					Swal.fire({icon: 'success', title: 'EDITADO COM SUCESSO!', showConfirmButton: false, timer: 1500});
                    setTimeout(function(){
                        location.reload();
                    }, 1500);

                } else {

					Swal.fire({icon: 'error', title: 'ERRRO AO ATUALIZAR!', showConfirmButton: false, timer: 1500});
                    $('button[type="submit"]').prop("disabled", false);
                    $('.login-load').hide();
                }
            }
        });
    });

});
