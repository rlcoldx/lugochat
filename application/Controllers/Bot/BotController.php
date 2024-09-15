<?php

namespace Agencia\Close\Controllers\Bot;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Bot\Bot;
use Agencia\Close\Enums\Permissions\ProductsPermissions;

class BotController extends Controller
{	
  public function index($params)
  {
    $this->setParams($params);

    $load_questions = new Bot();
    $questions = $load_questions->getQuestions($this->dataCompany['id']);
    $questions = $this->buildTree($questions->getResult());

    $load_questions = new Bot();
    $questionsEdit = $load_questions->getQuestionsEdit($this->dataCompany['id']);
    $questionsEdit = $this->buildTree($questionsEdit->getResult(), $questionsEdit->getResult()[0]['parent']);

    $opcionais = new Bot();
    $opcionais = $opcionais->getOpcionais($this->dataCompany['id'], $questionsEdit[0]['parent']);
    $opcionais = $opcionais->getResult();

    $this->render('pages/bot/index.twig', ['titulo' => 'Gerenciamento do Chat', 'questions' => $questions, 'edit' => $questionsEdit, 'opcionais' => $opcionais]);
  }

  public function buildTree($historico, $parentId = 0, $father = ''): array
  {
      $branch = array();

      foreach ($historico as $item) {

        if ($item['parent'] == $parentId) {
          
          $children = $this->buildTree($historico, $item['id'], $item['texto']);
         
          if ($children) {
            $item['children'] = $children;
          }
          $item['father'] = $father;

          $branch[] = $item;
        }

      }
      return $branch;
  }


  public function saveBemVindo($params)
  {
    $this->setParams($params);
    $bemVindo = new Bot();
    $resultUser = $bemVindo->saveBemVindo($this->params, $this->dataCompany['id']);
    if ($resultUser->getResult()) {
      echo '1';
    } else {
      echo '2';
    }
  }

  public function editQuestion($params)
  {
    $this->setParams($params);
    $edit = new Bot();
    $edit = $edit->editQuestion($this->params, $this->dataCompany['id']);
    if ($edit->getResult()) {
      echo '1';
    } else {
      echo '2';
    }
  }

  public function newOptionsOpen($params)
  {
    $this->render('components/bot/new.twig', []);
  }

  public function editOptionsOpen($params)
  {
    $this->render('components/bot/edit.twig', []);
  }

}