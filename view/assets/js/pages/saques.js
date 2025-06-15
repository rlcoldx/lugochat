$(document).ready(function () {

	$('#conta_save').submit(function(e){

		e.preventDefault();
		var domain = $('body').data('domain');
		var formData = new FormData(this);
		$('#salvar').prop('type', 'button');

		$.ajax({
			url: domain + '/saques/conta/salvar',
			data: formData,
			type: 'POST',
			success: function(data){
				if (data === '1') {
					setTimeout(function(){
                        location.reload();
                    }, 1500);
                    Swal.fire('', 'ATUALIZADO COM SUCESSO!', 'success');					
				}else{
					$('#salvar').prop('type', 'submit');
					Swal.fire({
						type: 'warning',
						title: 'ERRO AO CADASTRAR!',
						text: 'Erro ao solicitar a conta. Tente novamente mais tarde ou nos contate no suporte',
						showConfirmButton: true
					});
				}
			},
			processData: false,
			cache: false,
			contentType: false
		});
	});

	$('.money').mask('###.###.###.###.##0,00', {reverse: true});

	$('#conta_bancaria').change(function(){

		if($(this).children("option:selected").val() != ''){

			var conta_banco = $(this).children("option:selected").attr('data-conta_banco');
			var banco_pix = $(this).children("option:selected").attr('data-banco_pix');
			var conta_ag = $(this).children("option:selected").attr('data-conta_ag');
			var conta_numero = $(this).children("option:selected").attr('data-conta_numero');
			var conta_tipo = $(this).children("option:selected").attr('data-conta_tipo');
			var conta_responsavel = $(this).children("option:selected").attr('data-conta_responsavel');
			var conta_cpf_cnpj = $(this).children("option:selected").attr('data-conta_cpf_cnpj');

			if(banco_pix == 'Banco'){
				var conta_result = '<b>'+conta_banco+'</b>, '+conta_numero+' / '+conta_ag+', '+conta_tipo+' - '+conta_responsavel+', '+conta_cpf_cnpj;
			}else{
				var conta_result = '<b>'+conta_banco+'</b>, Pix - '+conta_responsavel+', '+conta_cpf_cnpj;
			}

			$('.conta-result').show();
			$('.conta-result').html(conta_result);

			checkV($('#valor').val());

		}else{
			$('.conta-result').hide();
			$('#salvar').addClass('disabled');
		}

    });


    $('#add_save_saque').submit(function(e){

		e.preventDefault();
		var domain = $('body').data('domain');
		var formData = new FormData(this);
		$('#salvar').prop('type', 'button');

		$.ajax({
			url: domain + '/saques/conta/save-saque',
			data: formData,
			type: 'POST',
			success: function(data){
				if (data === '0') {
					Swal.fire({
						type: 'success',
						title: 'SAQUE SOLICITADO COM SUCESSO!',
						text: 'Aguarde 24h para conclusão do seu saque.',
						showCancelButton: false,
						confirmButtonText: 'OK'
					}).then((result) => {
						if (result.value === true) {
							location.reload();
						}
					});					
				}else{
					$('#salvar').prop('type', 'submit');
					Swal.fire({
						type: 'warning',
						title: 'VALOR INVÁLIDO',
						text: 'O valor digitado é maior que o valor em sua carteira.',
						showConfirmButton: true
					});
				}
			},
			processData: false,
			cache: false,
			contentType: false
		});
	});

	$('input[type=radio][name=banco_pix]').change(function() {
	    if (this.value == 'Banco') {
	        $('.base-tipo label').text('Nome do Banco');
	        $('.base-tipo input').attr('placeholder', 'Nº do banco / Nome do Banco');
	        $('.banco_pix_banco').show();
	    }else if (this.value == 'Pix') {
	        $('.base-tipo input').attr('placeholder', 'Digite sua Chave Pix');
	        $('.base-tipo label').text('Chave Pix');
	        $('.banco_pix_banco').hide();
	    }
	});


});

function checkV(v){
	var domain = $('body').data('domain');
	$.ajax({
		url: domain + '/saques/conta/check',
		type: 'POST',
		data: {valor: v},
		success: function(data){

			if(data === '0'){
				$('.valor-error').hide();
			}else{
				$('.valor-error').show();
			}

			if( ($('#conta_bancaria').children("option:selected").val() != '') && (data === '0')){
				$('#salvar').removeClass('disabled');
			}else{
				$('#salvar').addClass('disabled');
			}

		}
	});
}

function changeStatus(saque, status){
	var domain = $('body').data('domain');
	$.ajax({
		url: domain + '/admin/saques/statusSave',
		type: 'POST',
		data: {id: saque, status: status},
		success: function(data){
			location.reload();
		}
	});
}