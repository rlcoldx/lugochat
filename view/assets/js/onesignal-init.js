/**
 * OneSignal Web SDK v16 — init com Service Worker registrado na origem real do navegador.
 * Ignora Site URL errado no painel OneSignal (ex.: https://painel).
 */
(function () {
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    window.OneSignalPushAvailable = null;

    function resolveOnesignalBasePath() {
        var pn = window.location.pathname || '';

        if (pn.indexOf('/painel') === 0) {
            return '/painel';
        }
        if (pn.indexOf('/lugochat') === 0) {
            return '/lugochat';
        }

        var host = window.location.hostname || '';
        if (host === 'buscademoteis.com.br' || host === 'www.buscademoteis.com.br') {
            return '/painel';
        }

        var body = document.body;
        if (!body) {
            return '';
        }

        var fromPhp = (body.getAttribute('data-onesignal-base') || '').trim().replace(/\/$/, '');
        if (fromPhp && fromPhp.indexOf('://') === -1) {
            return fromPhp.charAt(0) === '/' ? fromPhp : '/' + fromPhp;
        }

        var dataPath = body.getAttribute('data-path') || '';
        if (dataPath.indexOf('://') !== -1) {
            try {
                var u = new URL(dataPath);
                if (u.hostname === host) {
                    var p = u.pathname.replace(/\/$/, '');
                    if (p) {
                        return p;
                    }
                }
            } catch (e) {}
        }

        return '';
    }

    function workerPaths() {
        var basePath = resolveOnesignalBasePath();
        var swFile = (basePath || '') + '/OneSignalSDKWorker.js';
        if (swFile.charAt(0) !== '/') {
            swFile = '/' + swFile;
        }
        var swScope = basePath ? basePath + '/' : '/';
        return { swFile: swFile, swScope: swScope };
    }

    async function registerServiceWorkerOnOrigin() {
        var paths = workerPaths();
        return navigator.serviceWorker.register(paths.swFile, { scope: paths.swScope });
    }

    function buildInitOptions() {
        var body = document.body;
        var appId = body && body.getAttribute('data-onesignal-app-id');
        if (!appId) {
            return null;
        }

        var initOpts = {
            appId: appId,
            notifyButton: { enable: false },
        };

        var safariWebId = body.getAttribute('data-onesignal-safari-web-id');
        if (safariWebId) {
            initOpts.safari_web_id = safariWebId;
        }

        if (body.getAttribute('data-onesignal-allow-localhost') === '1') {
            initOpts.allowLocalhostAsSecureOrigin = true;
        }

        return initOpts;
    }

    OneSignalDeferred.push(async function (OneSignal) {
        try {
            var initOpts = buildInitOptions();
            if (!initOpts) {
                window.OneSignalPushAvailable = false;
                return;
            }

            var paths = workerPaths();
            await registerServiceWorkerOnOrigin();

            await OneSignal.init(initOpts);

            window.OneSignalPushAvailable = true;
            window.__onesignalSwPath = paths.swFile;
        } catch (e) {
            window.OneSignalPushAvailable = false;
            console.warn('OneSignal:', e && e.message ? e.message : e);
        }
    });
})();
