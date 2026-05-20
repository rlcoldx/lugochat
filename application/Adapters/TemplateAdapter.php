<?php

namespace Agencia\Close\Adapters;

use Agencia\Close\Adapters\Twig\PayStatus;
use Agencia\Close\Adapters\Twig\PayStatusColor;
use Agencia\Close\Adapters\Twig\DayTranslate;
use Agencia\Close\Adapters\Twig\MonthTranslate;
use Agencia\Close\Adapters\Twig\FilterHash;
use Agencia\Close\Helpers\String\Strings;
use Agencia\Close\Adapters\Twig\DataDiff;
use Agencia\Close\Adapters\Twig\VerifyPermission;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class TemplateAdapter
{
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader('view');
        $this->twig = new Environment($loader, [
            'cache' => false,
        ]);
        $this->twig->addExtension(new DataDiff());
        $this->twig->addExtension(new FilterHash());
        $this->twig->addExtension(new MonthTranslate());
        $this->twig->addExtension(new DayTranslate());
        $this->twig->addExtension(new PayStatus());
        $this->twig->addExtension(new PayStatusColor());
        $this->twig->addExtension(new VerifyPermission());
        $this->globals();

        return $this->twig;
    }

    public function render($view, array $data = []): string
    {
        return $this->twig->render($view, $data);
    }

    private function globals()
    {
        $this->twig->addGlobal('DOMAIN', DOMAIN);
        $this->twig->addGlobal('PATH', PATH);
        $this->twig->addGlobal('NAME', NAME);
        $this->twig->addGlobal('PRODUCTION', PRODUCTION);
        $this->twig->addGlobal('ONESIGNAL_APP_ID', defined('ONESIGNAL_APP_ID') ? ONESIGNAL_APP_ID : '');
        $this->twig->addGlobal('ONESIGNAL_SAFARI_WEB_ID', defined('ONESIGNAL_SAFARI_WEB_ID') ? ONESIGNAL_SAFARI_WEB_ID : '');
        $this->twig->addGlobal('ONESIGNAL_WEB_ENABLED', defined('ONESIGNAL_WEB_ENABLED') && ONESIGNAL_WEB_ENABLED);
        $this->twig->addGlobal('ONESIGNAL_SITE_PATH', defined('ONESIGNAL_SITE_PATH') ? ONESIGNAL_SITE_PATH : '');
        $this->twig->addGlobal('ONESIGNAL_SW_PATH', defined('ONESIGNAL_SW_PATH') ? ONESIGNAL_SW_PATH : '/OneSignalSDKWorker.js');
        $this->twig->addGlobal('ONESIGNAL_SW_SCOPE', defined('ONESIGNAL_SW_SCOPE') ? ONESIGNAL_SW_SCOPE : '/');
        $this->twig->addGlobal('_session', $_SESSION);
        $this->twig->addGlobal('_post', $_POST);
        $this->twig->addGlobal('_get', $_GET);
        $this->twig->addGlobal('_cookie', $_COOKIE);
    }
}