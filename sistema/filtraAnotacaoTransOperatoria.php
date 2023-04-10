<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$typeRequest = $_POST['tipoRequest'];
$usuaId = $_SESSION['UsuarId'];
$iUnidade = $_SESSION['UnidadeId'];
$EmpresaId = isset($_SESSION['EmpresaId'])?$_SESSION['EmpresaId']:$_SESSION['EmpreId'];

if(!isset($_SESSION['admissaoCirurgica'])){
  $_SESSION['admissaoCirurgica'] = [
    'acesso'=>[],
    'concentimento'=>[],
    'exames'=>[],
  ];
}

try{  
  if($typeRequest == "ACESSOVENOSO"){
    $acessos = $_SESSION['admissaoCirurgica']['acesso'];
    if($_POST['data'] && $_POST['hora'] && $_POST['lado'] && $_POST['calibre'] && $_POST['responsavel']){
      
      $data = $_POST['data'];
      $data = explode('-',$data);
      $data = $data[2].'/'.$data[1].'/'.$data[0];


      $hora = $_POST['hora'];
      $lado = $_POST['lado'];
      $calibre = $_POST['calibre'];
      $responsavel = $_POST['responsavel'];

      $id = uniqid("$hora$lado$calibre$responsavel");

      foreach($acessos as $item){
        if($item['dataHora'] == $data.' '.$hora && $item['lado'] == $lado){
          echo json_encode($acessos);
          exit;
        }
      }
  
      $exc = "<a style='color: black' href='#' onclick='exclui(this)'
      class='list-icons-item' data-id='$id' data-tipo='ACESSO'><i class='icon-bin' title='Excluir Acesso'></i></a>";
  
      $acoes = "<div class='list-icons'>
        $exc
      </div>";
  
      array_push($acessos,[
        'dataHora' =>$data.' '.$hora,
        'lado' =>$lado,
        'calibre' =>$calibre,
        'responsavel' =>$responsavel,
        'acoes' =>$acoes,
        'id' => $id,
      ]);
  
      $_SESSION['admissaoCirurgica']['acesso'] = $acessos;
    }

    echo json_encode($acessos);
  }if($typeRequest == "CONCENTIMENTO"){
    $concentimentos = $_SESSION['admissaoCirurgica']['concentimento'];

    if($_POST['data'] && $_POST['hora'] && $_POST['descricao']){
      
      $data = $_POST['data'];
      $data = explode('-',$data);
      $data = $data[2].'/'.$data[1].'/'.$data[0];


      $hora = $_POST['hora'];
      $descricao = $_POST['descricao'];

      $id = uniqid("$data$hora$descricao");

      foreach($concentimentos as $item){
        if($item['dataHora'] == $data.' '.$hora && $item['descricao'] == $descricao){
          echo json_encode($concentimentos);
          exit;
        }
      }
  
      $exc = "<a style='color: black' href='#' onclick='exclui(this)'
      class='list-icons-item' data-id='$id' data-tipo='CONCENTIMENTO'><i class='icon-bin' title='Excluir Concentimento'></i></a>";
  
      $acoes = "<div class='list-icons'>
        $exc
      </div>";
  
      array_push($concentimentos,[
        'dataHora' =>$data.' '.$hora,
        'descricao' =>$descricao,
        'acoes' =>$acoes,
        'id' => $id,
      ]);
  
      $_SESSION['admissaoCirurgica']['concentimento'] = $concentimentos;
    }

    echo json_encode($concentimentos);
  }if($typeRequest == "EXAMES"){
    $exames = $_SESSION['admissaoCirurgica']['exames'];

    if($_POST['data'] && $_POST['hora'] && $_POST['descricao']){
      
      $data = $_POST['data'];
      $data = explode('-',$data);
      $data = $data[2].'/'.$data[1].'/'.$data[0];


      $hora = $_POST['hora'];
      $descricao = $_POST['descricao'];

      $id = uniqid("$data$hora$descricao");

      foreach($exames as $item){
        if($item['dataHora'] == $data.' '.$hora && $item['descricao'] == $descricao){
          echo json_encode($exames);
          exit;
        }
      }
  
      $exc = "<a style='color: black' href='#' onclick='exclui(this)'
      class='list-icons-item' data-id='$id' data-tipo='EXAMES'><i class='icon-bin' title='Excluir Exame'></i></a>";
  
      $acoes = "<div class='list-icons'>
        $exc
      </div>";
  
      array_push($exames,[
        'dataHora' =>$data.' '.$hora,
        'descricao' =>$descricao,
        'acoes' =>$acoes,
        'id' => $id,
      ]);
  
      $_SESSION['admissaoCirurgica']['exames'] = $exames;
    }

    echo json_encode($exames);
  }elseif($typeRequest == "EXCLUIR"){
    $tipo = $_POST['tipo'];
    $id = $_POST['id'];
    $list = $_SESSION['admissaoCirurgica'];

    if($tipo == 'ACESSO'){
      foreach($list['acesso'] as $key=>$item){
        if($item['id'] == $id){
          array_splice($list['acesso'],$key,1);
        }
      }
    }elseif($tipo == 'CONCENTIMENTO'){
      foreach($list['concentimento'] as $key=>$item){
        if($item['id'] == $id){
          array_splice($list['concentimento'],$key,1);
        }
      }
    }elseif($tipo == 'EXAMES'){
      foreach($list['exames'] as $key=>$item){
        if($item['id'] == $id){
          array_splice($list['exames'],$key,1);
        }
      }
    }

    $_SESSION['admissaoCirurgica'] = $list;

    echo json_encode($list);
  }elseif($typeRequest == "CHECKLIST"){
    $list = $_SESSION['admissaoCirurgica'];
    echo json_encode($list);
  }elseif($typeRequest == "SALVARADMISSAO"){

  }

}catch(PDOException $e) {
  $_SESSION['msg']['titulo'] = "Erro";
  $_SESSION['msg']['mensagem'] = "Erro ao salvar Admissão!!!";
  $_SESSION['msg']['tipo'] = "error";

  echo json_encode([
    'type' => $typeRequest,
    'err' => $e,
    'sql' => $sql
  ]);
}
?>