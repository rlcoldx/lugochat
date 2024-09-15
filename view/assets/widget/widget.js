$(document).ready(function(){

	if (localStorage.getItem('lugochat_user_token') === null) {
		const tempo = new Date();
		const user_token = tempo.getTime();
		localStorage.setItem('lugochat_user_token', user_token);
	}

	let userID = localStorage.getItem('lugochat_user_token');

	let DOMAIN = $('body').attr('data-domain');

    $.ajax({
        type: "POST",
        data: {'userID': userID},
        url: DOMAIN + '/chat/historico',
        success: function(data) {
        	$("#chat_content").scrollTop($("#chat_content")[0].scrollHeight);
        	$('#chat_content').html(data);
        }
    });

	// SALVA O HISTOCIO
	$(document).on('click','.chat-action', function(){

		let DOMAIN = $('body').attr('data-domain');
		let userID = localStorage.getItem('lugochat_user_token');
		let id_pergunta = $(this).attr('data-question');
		let id_resposta = $(this).attr('data-option');
		let inicial = $(this).attr('data-inicial');
		let action = $(this).attr('data-action');

		//ABRE A TAB DE SUITES
		if(action == 'suite'){
			openSuites();
		}
		
		//ABRE A TAB DE MAPA
		if(action == 'map'){
			openMap();
		}

		let data = {
			'userID': userID,
			'id_pergunta': id_pergunta,
			'id_resposta': id_resposta,
			'inicial': inicial
		};
		
		$.ajax({
			type: "POST",
			data: data,
			url: DOMAIN + '/chat/historico/save',
			success: function(data) {
				$.ajax({
					type: "POST",
					data: {'userID': userID},
					url: DOMAIN + '/chat/historico',
					success: function(data) {
						$("#chat_content").scrollTop($("#chat_content")[0].scrollHeight);
						$('#chat_content').html(data);
					}
				});
			}
		});

	});

	// NAVEGA ENTRE AS TABS
	$('.open_tab').click(function(){
		let tab = $(this).data('tab');
		if(tab == 'chat_bot'){
			openChat();
		}
		if(tab == 'chat_suites'){
			openSuites();
		}
		if(tab == 'chat_map'){
			openMap();
		}
	});

});

//SCRIPTS PADRAO
var scriptsLoad = function() {
	
	$('#horario_chegada').select2({
		minimumResultsForSearch: -1
	});

	$('#agendamento_periodo').select2({
		minimumResultsForSearch: -1,
		templateResult: formatState
	});

	$('#horario_chegada').change(function() { 
		$('#chegadaSelect').text($(this).val());
	});
}
  
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

//ABRE AS SUITES
var openSuites = function() {

	let DOMAIN = $('body').attr('data-domain');
	let EMPRESA = $('#widget_start').attr('data-empresa');
	let check_suite = $('#chat_suites').data('open');

	if(check_suite == 'no') {

		$('.content-suites').html('<div class="chat-load"><i class="fa-solid fa-circle-notch fa-spin"></i></div>');

		$.ajax({
			type: "POST",
			data: {'id_empresa' : EMPRESA},
			url: DOMAIN + '/chat/suites/lista',
			success: function(data) {

				$('.content-suites').html(data);

				$('.chatbox').removeClass('active');
				$('#chat_suites').addClass('active');
				scriptsLoad();

			}
		});

	}else{

		$('.chatbox').removeClass('active');
		$('#chat_suites').addClass('active');

	}
}

//ABRE O MAPA
var openMap = function() {
	$('.content-mapa').html('<div class="chat-load"><i class="fa-solid fa-circle-notch fa-spin"></i></div>');
	$('.chatbox').removeClass('active');
	$('#chat_map').addClass('active');
}

//ABRE O CHAT
var openChat = function() {
	$('.chatbox').removeClass('active');
	$('#chat_bot').addClass('active');
}

//ABRE A PAGINA DETALHADA DA SUITE
var openDetalhes = function(suite) {

	let DOMAIN = $('body').attr('data-domain');

	$('.content-suites').html('<div class="chat-load"><i class="fa-solid fa-circle-notch fa-spin"></i></div>');

	$.ajax({
		type: "POST",
		data: {'id' : suite},
		url: DOMAIN + '/chat/suites/detalhes',
		success: function(data) {

			$('.content-suites').html(data);

			$('.chatbox').removeClass('active');
			$('#chat_suites').addClass('active');
			scriptsLoad();

		}
	});
}

//ABRE A PAGINA DETALHADA DA SUITE
var openAgendamento = function(codigo) {
	
	let DOMAIN = $('body').attr('data-domain');

	$('.content-suites').html('<div class="chat-load"><i class="fa-solid fa-circle-notch fa-spin"></i></div>');

	$.ajax({
		type: "POST",
		data: {'codigo' : codigo},
		url: DOMAIN + '/chat/suite/agendamento/espera',
		success: function(data) {

			$('.content-suites').html(data);

			$('.chatbox').removeClass('active');
			$('#chat_suites').addClass('active');
			scriptsLoad();

		}
	});
}