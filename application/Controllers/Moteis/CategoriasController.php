<?php

namespace Agencia\Close\Controllers\Moteis;

use Agencia\Close\Helpers\Upload;
use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Moteis\Categorias;

class CategoriasController extends Controller
{

    private Categorias $categories;

    public function index($params)
    {
        $this->setParams($params);
        
        $semcategorias = new Categorias();
        $semcategorias = $semcategorias->getSemCategorias()->getResult();

    	$categorias_lista = $this->getCategoryList();
        $this->render('pages/moteis/categorias.twig', ['menu' => 'moteis', 'categorias' => $categorias_lista, 'semcategorias' => $semcategorias]);
    }


    public function editar($params)
    {
        $this->setParams($params);
        
        $semcategorias = new Categorias();
        $semcategorias = $semcategorias->getSemCategorias()->getResult();

        $editar = new Categorias();
        $editar = $editar->getCategoriasID($params['id'])->getResult()[0];

    	$categorias_lista = $this->getCategoryList();
        $this->render('pages/moteis/categorias.twig', ['menu' => 'moteis', 'categorias' => $categorias_lista, 'semcategorias' => $semcategorias, 'editar' => $editar]);
    }

    public function getCategoryList(): array
    {
        $this->categories = new Categorias();
        $result = $this->categories->getCategory();
        if ($result->getResult()) {
            return $this->buildTree($result->getResult());
        } else {
            return [];
        }
    }

    public function buildTree($categories, $parentId = 0): array
    {
        $branch = array();
        foreach ($categories as $item) {
            if ($item['parent'] == $parentId) {
                $children = $this->buildTree($categories, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $branch[] = $item;
            }
        }
        return $branch;
    }

    public function save($params)
    {
        $this->setParams($params);

        $this->params['parent'] = 0;

        $createCategory = new Categorias();
        $createCategory = $createCategory->createCategory($this->params)->getResult();
        
        if($createCategory){
            
            if(isset($_FILES['category_icon'])) {
                $this->params['id'] = $createCategory;
                $this->save_edit($this->params);
            }else{
                echo 'success';
            }

        }else{
            echo 'error';
        }
    }

    public function save_edit($params)
    {
        $this->setParams($params);
        $editarCategory = new Categorias();

        $this->params['parent'] = 0;

        if(isset($_FILES['category_icon'])) {
            $upload = new Upload;
            $upload->Image($_FILES['category_icon'], microtime(), null, 'categorias');
        }
        if(isset($upload) && $upload->getResult()) {
            $this->params['cat_imagem'] = $upload->getResult();
        }

        $editarCategory = $editarCategory->editarCategory($this->params)->getResult();
        if($editarCategory){
            echo 'success';
        }else{
            echo 'error';
        }
    }

}