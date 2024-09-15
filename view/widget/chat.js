document.addEventListener("DOMContentLoaded", (event) => {

    var lugochat_icon = '<div id="lugochat_icon"><div class="lugochat_floatmain"><div class="lugochat_icon_content"><img src="http://buscademoteis.com.br/painel/view/widget/icone.png" id="botTitleBar" alt="Avatar"></em></div></div></div></div>';
    var lugochat_content = '<div id="lugochat_content"><iframe id="lugochat_frame" scrolling="no" width="100%" height="100%" style="border:none; margin: 0;" src="http://buscademoteis.com.br/painel/widget?cc=0NfEBa8i0X9l" title="Atendimento 24h" description="Atendimento 24h"></iframe></div>';
    var lugochat_style = '<style>:root{--primary-color:#ef6350}body{background-color:transparent}.lugochat_floatmain{visibility:hidden;cursor:pointer;display:block;right:21px;bottom:10px;width:68px;height:68px}#lugochat_content{overflow:hidden;position:fixed;border:medium none;width:428px;height:0px;bottom:85px;right:0px;max-height:95vh;background-color:white;color:rgb(255,255,255);z-index:1000;box-shadow:rgba(0,0,0,0.3) 0px 0px 20px 0px;transition:all 0.3s ease-out;transform:scaleY(0)}#lugochat_content.open{height:770px;transform:scaleY(1)}#lugochat_content iframe{position:static;height:100%;box-shadow:0 0 20px 0 rgba(0,0,0,.3);max-width:100%}.lugochat_floatmain{visibility:hidden;display:block;right:20px;bottom:10px;width:68px;height:68px;position:fixed;animation:.3s zoomIn;-webkit-animation:.3s zoomIn;-moz-animation:.3s zoomIn;-o-animation:.3s zoomIn;-ms-animation:.3s zoomIn;z-index:1000;opacity:1;visibility:visible;-webkit-transition:all .3s cubic-bezier(.25,.8,.25,1);-moz-transition:all .3s cubic-bezier(.25,.8,.25,1);-o-transition:all .3s cubic-bezier(.25,.8,.25,1);transition:all .3s cubic-bezier(.25,.8,.25,1);transform-origin:center center}.lugochat_icon_content{background-color:var(--primary-color);border-radius:70px;border:2px solid rgb(255,255,255);position:relative;padding:0;width:70px;height:70px;font-size:14px;line-height:100%}.lugochat_icon_content img{border-radius:70px;max-width:100%}</style>';
    document.body.insertAdjacentHTML('beforeend', lugochat_icon);
    document.body.insertAdjacentHTML('beforeend', lugochat_content);
    document.body.insertAdjacentHTML('beforeend', lugochat_style);

    load_action();
});

function load_action() {

    var icon_chat = document.querySelector('#lugochat_icon');
    var chat_content = document.querySelector('#lugochat_content');
    var chat_close = document.querySelector('.mini-sv a');

    icon_chat.addEventListener('click', function () {
        chat_content.classList.toggle('open');
    });

}