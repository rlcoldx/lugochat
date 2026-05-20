/**
 * OneSignal Web Push — botão no header e salvamento do subscription ID.
 */
(function () {
    var domain = document.body && document.body.getAttribute('data-domain');
    var btnItem = document.getElementById('onesignal-push-item');
    var btnAtivar = document.getElementById('btn-ativar-notificacoes');

    if (!domain || typeof OneSignalDeferred === 'undefined') {
        return;
    }

    function salvarSubscriptionId(subscriptionId) {
        if (!subscriptionId || subscriptionId.length < 10) {
            return;
        }

        var ultimo = sessionStorage.getItem('onesignal_push_key');
        if (ultimo === subscriptionId) {
            return;
        }

        var body = new URLSearchParams();
        body.append('subscription_id', subscriptionId);

        fetch(domain + '/notificacao/salvar-push', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            credentials: 'same-origin',
            body: body.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.status === 'success') {
                    sessionStorage.setItem('onesignal_push_key', subscriptionId);
                }
            })
            .catch(function () {});
    }

    function setBotaoVisivel(visivel) {
        if (!btnItem) {
            return;
        }
        if (visivel) {
            btnItem.classList.remove('d-none');
        } else {
            btnItem.classList.add('d-none');
        }
    }

    function alertaPermissaoNegada() {
        if (typeof Swal === 'undefined') {
            alert('As notificações estão bloqueadas. Abra as configurações do site no navegador e permita notificações.');
            return;
        }
        Swal.fire({
            icon: 'info',
            title: 'Notificações bloqueadas',
            html: 'Para receber alertas de reservas, permita notificações nas configurações do navegador para este site.<br><br>No Chrome/Edge: ícone do cadeado na barra de endereço → Notificações → Permitir.',
            confirmButtonText: 'Entendi'
        });
    }

    async function atualizarUI(OneSignal) {
        var permission = OneSignal.Notifications.permissionNative;
        var subscriptionId = OneSignal.User.PushSubscription.id;
        var ativo = permission === 'granted' && subscriptionId;

        setBotaoVisivel(!ativo);

        if (ativo) {
            salvarSubscriptionId(subscriptionId);
        }
    }

    async function ativarNotificacoes(OneSignal) {
        if (btnAtivar) {
            btnAtivar.disabled = true;
        }

        try {
            var permissionAntes = OneSignal.Notifications.permissionNative;

            if (permissionAntes !== 'granted') {
                await OneSignal.Notifications.requestPermission();
            }

            await atualizarUI(OneSignal);

            var permissionDepois = OneSignal.Notifications.permissionNative;

            if (permissionDepois === 'denied') {
                alertaPermissaoNegada();
            } else if (permissionDepois === 'granted' && !OneSignal.User.PushSubscription.id) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Quase lá',
                        text: 'Permissão concedida. Se o alerta não sumir, recarregue a página.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            }
        } catch (e) {
            console.warn('OneSignal:', e);
        } finally {
            if (btnAtivar) {
                btnAtivar.disabled = false;
            }
        }
    }

    OneSignalDeferred.push(async function (OneSignal) {
        if (window.OneSignalPushAvailable === false) {
            setBotaoVisivel(true);
            if (btnAtivar) {
                btnAtivar.title = 'Web Push não configurado no OneSignal. Em onesignal.com → seu app → Web → adicione a URL: ' + domain;
            }
            return;
        }

        try {
            await atualizarUI(OneSignal);

            if (btnAtivar) {
                btnAtivar.addEventListener('click', function () {
                    ativarNotificacoes(OneSignal);
                });
            }

            OneSignal.User.PushSubscription.addEventListener('change', function (event) {
                var id = event && event.current && event.current.id;
                if (id) {
                    salvarSubscriptionId(id);
                    setBotaoVisivel(false);
                }
            });

            if (OneSignal.Notifications.addEventListener) {
                OneSignal.Notifications.addEventListener('permissionChange', function () {
                    atualizarUI(OneSignal);
                });
            }
        } catch (e) {
            console.warn('OneSignal:', e);
            setBotaoVisivel(true);
        }
    });
})();
