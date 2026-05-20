/**
 * OneSignal Web Push — salva pushKey (subscription ID) do usuário logado em usuarios.pushKey
 */
(function () {
    var baseUrl = (document.body && document.body.getAttribute('data-path'))
        || (document.body && document.body.getAttribute('data-domain'));
    var btnItem = document.getElementById('onesignal-push-item');
    var btnAtivar = document.getElementById('btn-ativar-notificacoes');

    if (!baseUrl || typeof OneSignalDeferred === 'undefined') {
        return;
    }

    baseUrl = baseUrl.replace(/\/$/, '');

    function salvarSubscriptionId(subscriptionId) {
        if (!subscriptionId || String(subscriptionId).length < 10) {
            return Promise.resolve(false);
        }

        var idStr = String(subscriptionId);
        var ultimo = sessionStorage.getItem('onesignal_push_key');
        if (ultimo === idStr) {
            return Promise.resolve(true);
        }

        var body = new URLSearchParams();
        body.append('subscription_id', idStr);

        return fetch(baseUrl + '/notificacao/salvar-push', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            credentials: 'same-origin',
            body: body.toString()
        })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (res) {
                if (res.ok && res.data && res.data.status === 'success') {
                    sessionStorage.setItem('onesignal_push_key', idStr);
                    return true;
                }
                console.warn('Salvar pushKey:', res.data);
                return false;
            })
            .catch(function (err) {
                console.warn('Salvar pushKey (rede):', err);
                return false;
            });
    }

    async function obterSubscriptionId(OneSignal) {
        try {
            if (OneSignal.User.PushSubscription.optIn) {
                await OneSignal.User.PushSubscription.optIn();
            }
        } catch (e) {}

        for (var i = 0; i < 15; i++) {
            var id = OneSignal.User.PushSubscription.id;
            if (id && typeof id.then === 'function') {
                id = await id;
            }
            if (id) {
                return String(id);
            }
            await new Promise(function (resolve) { setTimeout(resolve, 400); });
        }

        return null;
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
            alert('As notificações estão bloqueadas. Permita notificações nas configurações do navegador para este site.');
            return;
        }
        Swal.fire({
            icon: 'info',
            title: 'Notificações bloqueadas',
            html: 'Para receber alertas de reservas, permita notificações nas configurações do navegador.<br><br>Chrome/Edge: cadeado na barra de endereço → Notificações → Permitir.',
            confirmButtonText: 'Entendi'
        });
    }

    function alertaSalvo() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Notificações ativas',
                text: 'Este dispositivo está vinculado à sua conta.',
                timer: 2500,
                showConfirmButton: false
            });
        }
    }

    async function sincronizarPushKey(OneSignal, mostrarConfirmacao) {
        var permission = OneSignal.Notifications.permissionNative;
        if (permission !== 'granted') {
            setBotaoVisivel(true);
            return;
        }

        var subscriptionId = await obterSubscriptionId(OneSignal);
        if (!subscriptionId) {
            setBotaoVisivel(true);
            return;
        }

        var jaSalvo = sessionStorage.getItem('onesignal_push_key') === subscriptionId;
        var salvou = await salvarSubscriptionId(subscriptionId);
        setBotaoVisivel(!salvou);

        if (salvou && mostrarConfirmacao && !jaSalvo) {
            alertaSalvo();
        }
    }

    async function ativarNotificacoes(OneSignal) {
        if (btnAtivar) {
            btnAtivar.disabled = true;
        }

        try {
            if (OneSignal.Notifications.permissionNative !== 'granted') {
                await OneSignal.Notifications.requestPermission();
            }

            if (OneSignal.Notifications.permissionNative === 'denied') {
                alertaPermissaoNegada();
                return;
            }

            await sincronizarPushKey(OneSignal, true);
        } catch (e) {
            console.warn('OneSignal:', e);
        } finally {
            if (btnAtivar) {
                btnAtivar.disabled = false;
            }
        }
    }

    OneSignalDeferred.push(async function (OneSignal) {
        var espera = 0;
        while (window.OneSignalPushAvailable === null && espera < 60) {
            await new Promise(function (r) { setTimeout(r, 100); });
            espera++;
        }

        if (window.OneSignalPushAvailable === false) {
            setBotaoVisivel(true);
            if (btnAtivar) {
                btnAtivar.title = 'Configure Web Push no OneSignal com a URL: ' + baseUrl;
            }
            return;
        }

        try {
            if (btnAtivar) {
                btnAtivar.addEventListener('click', function () {
                    ativarNotificacoes(OneSignal);
                });
            }

            OneSignal.User.PushSubscription.addEventListener('change', function (event) {
                var id = event && event.current && event.current.id;
                if (id) {
                    salvarSubscriptionId(id).then(function (ok) {
                        if (ok) {
                            setBotaoVisivel(false);
                        }
                    });
                }
            });

            if (OneSignal.Notifications.addEventListener) {
                OneSignal.Notifications.addEventListener('permissionChange', function () {
                    sincronizarPushKey(OneSignal, false);
                });
            }

            if (OneSignal.Notifications.permissionNative === 'granted') {
                await sincronizarPushKey(OneSignal, false);
            } else {
                setBotaoVisivel(true);
            }
        } catch (e) {
            console.warn('OneSignal:', e);
            setBotaoVisivel(true);
        }
    });
})();
