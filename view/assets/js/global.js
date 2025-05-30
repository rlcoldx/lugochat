$(document).ready(function() {

	$('.select2').select2();
	
	if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
	    || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) { 
	    $('body').attr('data-sidebar-style', 'overlay');
	}else{
		$('body').attr('data-sidebar-style', 'full');
	}

    var titulo = document.title;
    titulo = titulo.split(' - ');
    $('.dashboard_bar').html(titulo[1]);

});

// MODAL REMOTO
$('body').on('click', '[data-bs-toggle="modal-remote"]', function(){
	$(".modal-body").empty();
	var link = $(this).data("remote");
	var target = $(this).attr('data-bs-target');
	$(target+' .modal-body').load(link);
	$(target).modal('show');
});

$(document).on("hidden.bs.modal", ".modal:not(.local-modal)", function (e) {
	$(e.target).removeData("bs.modal").find(".modal-body").empty();
});


// MODAL OFFCANVAS
$(document).on('click', '[data-bs-toggle="offcanvas"]', function(e) {
	e.preventDefault();
	$('.offcanvas-body').html('');
	var url = $(this).attr('data-bs-remote');
	var offcanvas = $(this).attr('data-bs-target');
	$.ajax({
		url: url,
		method: 'GET',
		success: function(response) {
			$('.offcanvas-body').html(response);
		}
	});
});


// MODAL REMOTO
$(document).on('click', '[data-bs-remote="modal"]', function(e) {
	e.preventDefault();
	let modalTarget = $(this).data('bs-target');
	if (modalTarget) {
		let remoteContentUrl = $(this).data('remote');
		$(modalTarget).find('.modal-body').load(remoteContentUrl, function() {
			let modalInstance = new bootstrap.Modal($(modalTarget)[0]);
			modalInstance.show();
			const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
			const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
		});
	}
});

// Função para verificar novos agendamentos
function changeMotel(motel) {
	console.log(motel);
    let DOMAIN = $('body').data('domain');
    $.ajax({
        url: DOMAIN + '/change-motel',
        type: 'POST',
		data: { 'motel': motel },
		async: true,
        success: function() {
			setTimeout(function(){
				location.reload();
			}, 1000);
		}
    });
}

//Função para verificar novos agendamentos
function verificarNovosAgendamentos() {

    let DOMAIN = $('body').data('domain');

    $.ajax({
        url: DOMAIN + '/reservas/check',
        type: 'GET',
		async: true,
        success: function(data) {
			if(data != 0) {
				let plural = '';
				if(parseInt(data) > 1){
					plural = 's';
				}
				$('#pedido_alerta').remove();
				$('.content-body').prepend('');
				$('.content-body').prepend(`
					<div id="pedido_alerta" class="alert alert-warning bg-warning text-dark rounded-0">
						<div class="d-flex flex-wrap align-items-center align-self-center">
							<div class="alert-box">
								<span></span><div>`+data+`</div>
							</div>
							<div class="ms-md-4">
								<div class="fs-5 fw-bold text-dark m-0">Você possui `+data+` nova`+plural+` reserva`+plural+` pendente`+plural+`.</div>
								<div>Você precisará aceitar as reservas para que os clientes possam concluir suas reservas.</div>
							</div>
							<div class="ms-md-4">
								<a href="`+DOMAIN+`/reservas?status=Pedente" class="btn bg-black">VER RESERVAS</a>
							</div>
						</div>
					</div>
					<audio id="audio" src="`+DOMAIN+`/view/assets/sound/alert.mp3" preload="auto"></audio>
					`);
				var audio = $('#audio')[0];
				audio.play();
				audio.loop = true;
				startBlinking();
			}
        }
    });

}
verificarNovosAgendamentos();
setInterval(verificarNovosAgendamentos, 60000);

var originalTitle = document.title;
var blinkInterval;

function startBlinking() {
    blinkInterval = setInterval(function() {
        if (document.title === "Novo Cliente!") {
            document.title = originalTitle;
        } else {
            document.title = "Novo Cliente!";
        }
    }, 1000); // Pisca a cada 1 segundo
}

function stopBlinking() {
    clearInterval(blinkInterval);
    document.title = originalTitle;
}