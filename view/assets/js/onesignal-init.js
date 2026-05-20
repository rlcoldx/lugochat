/**
 * Inicialização do OneSignal Web SDK (v16).
 * Config via data-* no <body>: app-id, safari-web-id, allow-localhost.
 */
(function () {
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    window.OneSignalPushAvailable = null;

    function resolveOnesignalBasePath() {
        var body = document.body;
        if (!body) {
            return '';
        }

        var pn = window.location.pathname || '';
        if (pn.indexOf('/painel') === 0) {
            return '/painel';
        }
        if (pn.indexOf('/lugochat') === 0) {
            return '/lugochat';
        }

        var fromPhp = (body.getAttribute('data-onesignal-base') || '').trim().replace(/\/$/, '');
        if (fromPhp && fromPhp.indexOf('://') === -1) {
            return fromPhp.charAt(0) === '/' ? fromPhp : '/' + fromPhp;
        }

        var dataPath = body.getAttribute('data-path') || '';
        if (dataPath.indexOf('://') !== -1) {
            try {
                var u = new URL(dataPath);
                if (u.hostname === window.location.hostname) {
                    var p = u.pathname.replace(/\/$/, '');
                    if (p) {
                        return p;
                    }
                }
            } catch (e) {}
        }

        return '';
    }

    function buildInitOptions() {
        var body = document.body;
        var appId = body && body.getAttribute('data-onesignal-app-id');
        if (!appId) {
            return null;
        }

        var basePath = resolveOnesignalBasePath();
        var swRelative = (basePath || '') + '/OneSignalSDKWorker.js';
        if (swRelative.charAt(0) !== '/') {
            swRelative = '/' + swRelative;
        }
        var swScope = basePath ? basePath + '/' : '/';
        var swAbsolute = window.location.origin + swRelative;

        var initOpts = {
            appId: appId,
            notifyButton: { enable: false },
            serviceWorkerPath: swAbsolute,
            serviceWorkerParam: { scope: swScope },
        };

        var safariWebId = body.getAttribute('data-onesignal-safari-web-id');
        if (safariWebId) {
            initOpts.safari_web_id = safariWebId;
        }

        if (body.getAttribute('data-onesignal-allow-localhost') === '1') {
            initOpts.allowLocalhostAsSecureOrigin = true;
        }

        return { initOpts: initOpts, swAbsolute: swAbsolute };
    }

    OneSignalDeferred.push(async function (OneSignal) {
        try {
            var built = buildInitOptions();
            if (!built) {
                window.OneSignalPushAvailable = false;
                return;
            }

            await OneSignal.init(built.initOpts);
            window.OneSignalPushAvailable = true;
            window.__onesignalSwPath = built.swAbsolute;
        } catch (e) {
            window.OneSignalPushAvailable = false;
            console.warn('OneSignal:', e && e.message ? e.message : e);
        }
    });
})();
