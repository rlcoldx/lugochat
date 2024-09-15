<?php

//WSErro :: Exibe erros lançados :: Front
function EchoMsg($ErrMsg, $ErrNo, $ErrDie = null) {
    $CssClass = ($ErrNo == E_USER_NOTICE ? INFOR : ($ErrNo == E_USER_WARNING ? ALERT : ($ErrNo == E_USER_ERROR ? ERROR : $ErrNo)));

    switch($CssClass):
        case 'accept':
            echo '<div class="alert alert-success alert-dismissable">
    <button class="close" aria-hidden="true" data-dismiss="alert" type="button">&times;</button>
    <h4><i class="icon fa fa-check"></i> Sucesso!</h4>
    '.$ErrMsg.'</div>';
            break;
        case 'infor':
            echo '<div class="alert alert-info alert-dismissable">
    <button class="close" aria-hidden="true" data-dismiss="alert" type="button">&times;</button>
    <h4><i class="icon fa fa-info"></i> Atenção!</h4>
    '.$ErrMsg.'</div>';
            break;
        case 'alert':
            echo '<div class="alert alert-warning alert-dismissable">
    <button class="close" aria-hidden="true" data-dismiss="alert" type="button">&times;</button>
    <h4><i class="icon fa fa-warning"></i> Alerta!</h4>
    '.$ErrMsg.'</div>';
            break;
        case 'error':
            echo '<div class="alert alert-danger alert-dismissable">
    <button class="close" aria-hidden="true" data-dismiss="alert" type="button">&times;</button>
    <h4><i class="icon fa fa-ban"></i> Erro!</h4>
    '.$ErrMsg.'</div>';
            break;
        default:
    endswitch;

    if ($ErrDie):
        die;
    endif;
}

//PHPErro :: personaliza o gatilho do PHP
function PHPErro($ErrNo, $ErrMsg, $ErrFile, $ErrLine) {
    $CssClass = ($ErrNo == E_USER_NOTICE ? INFOR : ($ErrNo == E_USER_WARNING ? ALERT : ($ErrNo == E_USER_ERROR ? ERROR : $ErrNo)));
    echo "<p class=\"trigger {$CssClass}\">";
    echo "<b>Erro na Linha: #{$ErrLine} ::</b> {$ErrMsg}<br>";
    echo "<small>{$ErrFile}</small>";
    echo "<span class=\"ajax_close\"></span></p>";

    if ($ErrNo == E_USER_ERROR):
        die;
    endif;
}

set_error_handler('PHPErro');
