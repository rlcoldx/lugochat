$(document).ready(function () {

	$('#salvar_cupom').submit(function(e){

		e.preventDefault();
		var DOMAIN = $('body').data('domain');
		var formData = new FormData(this);
		$('#salvar').prop('type', 'button');

		$.ajax({
			url: DOMAIN + '/cupons/save',
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

	var codigoElement = document.getElementById("codigo");
	if (codigoElement) {
		codigoElement.addEventListener("input", function() {
			let value = this.value;
			// Converte para maiúsculas
			value = value.toUpperCase();
			// Substitui espaços por hífens
			value = value.replace(/\s+/g, '-');
			// Remove acentos e caracteres especiais
			value = value.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
			this.value = value;
		});
	}

});