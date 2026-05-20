/**
 * OneSignal Web Push — salva pushKey (subscription ID) do usuário logado em usuarios.pushKey
 */
(function () {
    var baseUrl = (document.body && document.body.getAttribute('data-path'))
        || (document.body && document.body.getAttribute('data-domain'));
    var btnItem = document.getElementById('onesignal-push-item');
    var btnRemoverItem = document.getElementById('onesignal-remover-item');
    var btnAtivar = document.getElementById('btn-ativar-notificacoes');
    var btnRemover = document.getElementById('btn-remover-push-key');
    var oneSignalRef = null;
    var syncAutomaticoAtivo = true;

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
                    sessionStorage.removeItem('onesignal_push_skip_sync');
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

    async function optOutOneSignal(OneSignal) {
        if (!OneSignal || !OneSignal.User || !OneSignal.User.PushSubscription) {
            return;
        }
        try {
            if (typeof OneSignal.User.PushSubscription.optOut === 'function') {
                await OneSignal.User.PushSubscription.optOut();
            }
        } catch (e) {
            console.warn('OneSignal optOut:', e);
        }
    }

    function setItemVisivel(elemento, visivel) {
        if (!elemento) {
            return;
        }
        if (visivel) {
            elemento.classList.remove('d-none');
        } else {
            elemento.classList.add('d-none');
        }
    }

    function setNotificacaoAtiva(ativa) {
        setItemVisivel(btnItem, !ativa);
        setItemVisivel(btnRemoverItem, ativa);
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

    function alertaRemovido() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Notificação removida',
                text: 'Clique em "Ativar notificações" para cadastrar este navegador de novo.',
                confirmButtonText: 'Entendi'
            });
        }
    }

    async function sincronizarPushKey(OneSignal, mostrarConfirmacao) {
        if (!syncAutomaticoAtivo || sessionStorage.getItem('onesignal_push_skip_sync') === '1') {
            setNotificacaoAtiva(false);
            return;
        }

        var permission = OneSignal.Notifications.permissionNative;
        if (permission !== 'granted') {
            setNotificacaoAtiva(false);
            return;
        }

        var subscriptionId = await obterSubscriptionId(OneSignal);
        if (!subscriptionId) {
            setNotificacaoAtiva(false);
            return;
        }

        var jaSalvo = sessionStorage.getItem('onesignal_push_key') === subscriptionId;
        var salvou = await salvarSubscriptionId(subscriptionId);
        setNotificacaoAtiva(salvou);

        if (salvou && mostrarConfirmacao && !jaSalvo) {
            alertaSalvo();
        }
    }

    async function ativarNotificacoes(OneSignal) {
        if (btnAtivar) {
            btnAtivar.disabled = true;
        }

        try {
            sessionStorage.removeItem('onesignal_push_skip_sync');
            syncAutomaticoAtivo = true;

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

    function confirmarRemocao() {
        if (typeof Swal === 'undefined') {
            return Promise.resolve(window.confirm('Remover as notificações desta conta neste dispositivo?'));
        }

        return Swal.fire({
            icon: 'warning',
            title: 'Remover Notificação?',
            text: 'Você deixará de receber alertas nesta conta até clicar em "Ativar notificações" de novo.',
            showCancelButton: true,
            confirmButtonText: 'Remover',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#6c757d'
        }).then(function (result) {
            return !!(result && result.isConfirmed);
        });
    }

    async function removerVinculoPush() {
        var confirmou = await confirmarRemocao();
        if (!confirmou) {
            return;
        }

        if (btnRemover) {
            btnRemover.disabled = true;
        }

        try {
            var res = await fetch(baseUrl + '/notificacao/remover-push', {
                method: 'POST',
                credentials: 'same-origin'
            });
            var data = await res.json();

            if (!res.ok || !data || data.status !== 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: (data && data.message) ? data.message : 'Não foi possível remover a notificação.'
                    });
                }
                return;
            }

            sessionStorage.removeItem('onesignal_push_key');
            sessionStorage.setItem('onesignal_push_skip_sync', '1');
            syncAutomaticoAtivo = false;

            if (oneSignalRef) {
                await optOutOneSignal(oneSignalRef);
            }

            setNotificacaoAtiva(false);
            alertaRemovido();
        } catch (e) {
            console.warn('Remover pushKey:', e);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha de rede ao remover a notificação.' });
            }
        } finally {
            if (btnRemover) {
                btnRemover.disabled = false;
            }
        }
    }

    if (btnRemover) {
        btnRemover.addEventListener('click', function () {
            removerVinculoPush();
        });
    }

    OneSignalDeferred.push(async function (OneSignal) {
        oneSignalRef = OneSignal;

        var espera = 0;
        while (window.OneSignalPushAvailable === null && espera < 60) {
            await new Promise(function (r) { setTimeout(r, 100); });
            espera++;
        }

        if (window.OneSignalPushAvailable === false) {
            setNotificacaoAtiva(false);
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
                if (sessionStorage.getItem('onesignal_push_skip_sync') === '1') {
                    return;
                }
                var id = event && event.current && event.current.id;
                if (id) {
                    salvarSubscriptionId(id).then(function (ok) {
                        if (ok) {
                            setNotificacaoAtiva(true);
                        }
                    });
                }
            });

            if (OneSignal.Notifications.addEventListener) {
                OneSignal.Notifications.addEventListener('permissionChange', function () {
                    if (sessionStorage.getItem('onesignal_push_skip_sync') === '1') {
                        setNotificacaoAtiva(false);
                        return;
                    }
                    sincronizarPushKey(OneSignal, false);
                });
            }

            if (OneSignal.Notifications.permissionNative === 'granted') {
                await sincronizarPushKey(OneSignal, false);
            } else {
                setNotificacaoAtiva(false);
            }
        } catch (e) {
            console.warn('OneSignal:', e);
            setNotificacaoAtiva(false);
        }
    });
})();
