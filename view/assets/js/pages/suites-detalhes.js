$('#lightgallery').lightGallery({
	loop:true,
	thumbnail:true,
	exThumbImage: 'data-exthumbimage',
	download: false,
	share: false,
	autoplay: false,
	autoplayControls: false
});

$('.owl-carousel').owlCarousel({
	loop:true,
	items: 1,
	margin:0,
	center: true,
	nav:true,
	navText: ['<i class="fa-light fa-arrow-left"></i>','<i class="fa-light fa-arrow-right"></i>']
});

$(document).ready(function(){
	var collapseDiv = $('.collapse-div');
	var expandLink = $('.expand-link');
	var collapsed = true;
	collapseDiv.css({
		'max-height': '5.5em',
		'overflow': 'hidden',
	});
	expandLink.click(function(e){
		e.preventDefault();
		if (collapsed) {
			collapseDiv.css('max-height', 'none');
			collapsed = false;
			expandLink.html('<i class="fa-light fa-arrow-up-to-arc"></i> Ver menos');
		} else {
			collapseDiv.css('max-height', '5.5em');
			collapsed = true;
			expandLink.html('<i class="fa-light fa-arrow-down-to-arc"></i> Ver mais');
		}
	});
});

$('#agendamento').jsRapCalendar({
	week: 6,
	onClick:function(y,m,d){

		$('.select-preload').show();

		m = (m + 1);

		if(d <= 9){d = '0' + d;}
		if(m <= 9){m = '0' + m;}

		let selectedDate = y + '-' + m  + '-' + d;
		let selectedDateformat = d + '-' + m  + '-' + y;

		$('#agendamento_data').val(selectedDateformat);
		$('#dataSelect').text(selectedDateformat);

		$('#chegadaSelect').text('--');
		$('#periodoSelect').text('--');
		$('#valorSelect').text('--');

		let DOMAIN = $('body').attr('data-domain');
		let EMPRESA = $('#widget_start').attr('data-empresa');
		let SUITE = $('#id_suite').val();

		//ATUALIZA OS HORARIOS DE CHEGADA
		$.ajax({
			type: "POST",
			data: {'id': SUITE, 'id_empresa' : EMPRESA, 'dataselect': selectedDate},
			url: DOMAIN + '/chat/suites/detalhes/horas',
			success: function(data) {

				data = JSON.parse(data);
				var horario_chegada = $("#horario_chegada");
				horario_chegada.empty();
				horario_chegada.append('<option value="" disabled selected hidden>Selecione</option>');
				for (var i = 0; i < data.length; i++) {
					horario_chegada.append(new Option(data[i], data[i]));
				}
				horario_chegada.select2({minimumResultsForSearch: -1});
				$('.select-preload').hide();

			}
		});

		//ATUALIZADA OS PERIODOS E OS VALORES
		$.ajax({
			type: "POST",
			data: {'id': SUITE, 'id_empresa' : EMPRESA, 'dataselect': selectedDate},
			url: DOMAIN + '/chat/suites/detalhes/periodos',
			success: function(data) {
				data = JSON.parse(data);
				var agendamento_periodo = $("#agendamento_periodo");
				agendamento_periodo.empty();
				agendamento_periodo.append('<option value="" disabled selected hidden>Selecione</option>');
				$.each(data, function(index, data) {
					var option = $('<option>', {
						value: data.periodo,
						text: data.periodo
					});
					
					option.attr('data-price', data.valor);

					// Remova o símbolo de dólar e converta para um número
					data.valor = parseFloat(data.valor.replace('$', ''));
					// Faça a conversão para o formato brasileiro (utilizando ponto para separar os centavos)
					var valorBR = data.valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
					 
					option.attr('data-valor', valorBR);
					agendamento_periodo.append(option);
				});
				
				agendamento_periodo.select2({
					minimumResultsForSearch: -1,
					templateResult: formatState
				});

				$('.select-preload').hide();
			}
		});

	}
});

function formatState(option) {
	var dataValor = $(option.element).data('valor');
	var $options = $('<div class="d-flex justify-content-between align-items-center"></div>');
	$options.append('<span class="periodo fw-bold">' + option.text + '</span>');
	$options.append('<span class="separador flex-grow-1"></span>');
	if (dataValor) {
		$options.append('<span class="valor fw-bold">' + dataValor + '</span>');
	}
	return $options;
}

$('#agendamento_periodo').change(function(){
	var periodo = $(this).val();
	var valor = $(this).find(":selected").data("valor");
	var price = $(this).find(":selected").data("price");
	$('#periodoSelect').text(periodo);
	$('#valorSelect').text(valor);
	$('#select_valor').val(price);
});

$(document).ready(function() {
    $('#termosdeuso').change(function() {
		if ($(this).is(':checked')) {
			$('#salvar').prop("disabled", false);
		} else {
			$('#salvar').prop("disabled", true);
		}
    });
});

//SALVA A MENSAGEM DE BEM VINDO
$('#agendamento_save').submit(function (c) {
	$('.pre-load').show();
	$('button[type="submit"]').prop("disabled", true);
	c.preventDefault();

	let DOMAIN = $('body').data('domain');
	let EMPRESA = $('#widget_start').attr('data-empresa');
	let form = $(this).serialize();
	
	$.ajax({
		type: "POST", async: true, data: form + '&id_empresa='+EMPRESA,
		url: DOMAIN + '/chatbot/save/agendamento',
		success: function (data) {
			$('.pre-load').hide();
			if (data != "0") {
				// SE SALVO MANDA PARA PAGINA DE ESPERA
				openAgendamento(data);
			} else {
				Swal.fire({icon: 'error', title: 'ERRRO AO AGENDAR!', showConfirmButton: false, timer: 1500});
				$('button[type="submit"]').prop("disabled", false);
				$('.pre-load').hide();
			}
		}
	});

});