<?php

namespace Agencia\Close\Controllers;

use Agencia\Close\Adapters\TemplateAdapter;
use Agencia\Close\Helpers\Device\CheckDevice;
use Agencia\Close\Middleware\MiddlewareCollection;
use Agencia\Close\Services\Login\PermissionsService;
use Agencia\Close\Models\Company;
use Agencia\Close\Helpers\Result;
use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Moteis\Moteis;
use CoffeeCode\Router\Router;

class Controller
{
    protected TemplateAdapter $template;
    private Company $company;
    private Moteis $moteis;
    public array $dataCompany;
    private array $dataDefault = [];
    public array $moteis_list = [];
    protected Router $router;
    protected array $params;
    protected Result $result;

    public function __construct($router)
    {
        $this->router = $router;
        $this->company = new Company();
        $this->moteis = new Moteis();
        $this->template = new TemplateAdapter();
        $this->middleware();
    }

    private function middleware()
    {
        $middlewares = new MiddlewareCollection();
        $middlewares->default();
        $middlewares->run();
    }

    private function isMobileDevice(): bool
    {
        $checkDevice = new CheckDevice();
        return $checkDevice->isMobileDevice();
    }

    protected function render(string $link, array $arrayData = [])
    {
        $arrayDataWithDefault = $this->mergeWithDefault($arrayData);
        echo $this->template->render($link, $arrayDataWithDefault);
    }

    protected function responseJson($response){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }


    private function mergeWithDefault($arrayToMerge): array
    {
        return array_merge($this->dataDefault, $arrayToMerge);
    }


    protected function setParams(array $params)
    {
        $this->params = $params;
        if (isset($_SESSION['busca_perfil_empresa'])) {
            $this->baseDataCompany();
        }
        $this->setDefault();
    }

    protected function getCurrentUrl(): string
    {
        return  parse_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_PATH);
    }

    private function setDefault()
    {
        $this->dataDefault['mobile'] = $this->isMobileDevice();
        $this->dataDefault['currentUrl'] = $this->getCurrentUrl();
        $this->dataDefault['session'] = $_SESSION;
        $this->dataDefault['cookie'] = $_COOKIE;
        $this->dataDefault['get'] = $_GET;
    }

    protected function getDefault(): array
    {

        return $this->dataDefault;
    }

    protected function redirectUrl(string $url)
    {
        header('Location: '. $url);
    }

    private function baseDataCompany()
    {
        $company = $this->findDataCompany();
        $moteis = $this->findMoteis();

        if ($moteis->getResult()) {
            $this->dataDefault['moteis'] = $moteis->getResult();
        }

        if ($company->getResult()) {
            $this->dataDefault['dataCompany'] = $company->getResult()[0];
            $this->dataCompany = $company->getResult()[0];
        
        }
        
    }

    private function findMoteis(): Read
    {
        return $this->moteis->getMoteisList();
    }

    private function findDataCompany(): Read
    {
        return $this->company->findDataCompany($_SESSION['busca_perfil_empresa']);
    }

     protected function requirePermission(string $permission) {
        $permissionService = new PermissionsService();
        if(!$permissionService->verifyPermissions($permission)) {
            echo 'você não tem permissão para acessar esse serviço!';
           die(); 
        }
    }

}