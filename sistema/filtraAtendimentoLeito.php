<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$typeRequest = $_POST['tipoRequest'];
$usuaId = $_SESSION['UsuarId'];
$iUnidade = $_SESSION['UnidadeId'];

try{
  if($typeRequest == "LEITOS"){
    $especialidade = isset($_POST['especialidade'])?$_POST['especialidade']:null;
    $ala = isset($_POST['ala'])?$_POST['ala']:null;

    $quartos = [];

    $sql = "SELECT QuartId, QuartNome
      FROM Quarto
      WHERE QuartUnidade = $iUnidade";

    $result = $conn->query($sql);
    $resultQuartos = $result->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT LeitoId,QuartId,LeitoNome,QuartNome,TpIntNome,
      EsLeiNome,LeitoStatus,LeitoUsuarioAtualizador,LeitoUnidade,AtXLeId
      FROM Leito
      JOIN VincularLeitoXLeito ON VLXLeLeito = LeitoId
      JOIN VincularLeito ON VnLeiId = VLXLeVinculaLeito
      JOIN Quarto ON QuartId = VnLeiQuarto
      JOIN TipoInternacao ON TpIntId = VnLeiTipoInternacao
      JOIN EspecialidadeLeito ON EsLeiId = VnLeiEspecialidadeLeito
      LEFT JOIN AtendimentoXLeito ON AtXLeLeito = LeitoId
      WHERE LeitoUnidade = $iUnidade";

    if($especialidade){
      $sql .= " AND EsLeiId = $especialidade";
    }
    // if($ala){
    //   $sql .= " AND QuartId = $ala";
    // }
    $sql .= " ORDER BY LeitoNome";
    $result = $conn->query($sql);
    $result = $result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
      'quartos' =>$resultQuartos,
      'leitos' =>$result,
    ]);
  }elseif($typeRequest == "ACOMODACAO"){
    $sql = "SELECT TpAcoId, TpAcoNome
      FROM TipoAcomodacao
      WHERE TpAcoUnidade = $iUnidade";
    $result = $conn->query($sql);
    $result = $result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
  }elseif($typeRequest == "INTERNACAO"){

    $sql = "SELECT TpIntId, TpIntNome
      FROM TipoInternacao
      WHERE TpIntUnidade = $iUnidade";

    $result = $conn->query($sql);
    $result = $result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
  }elseif($typeRequest == "ESPECIALIDADE"){

    $sql = "SELECT EsLeiId, EsLeiNome
            FROM EspecialidadeLeito
            WHERE EsLeiUnidade = $iUnidade";
    
    $result = $conn->query($sql);
    $result = $result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
  }elseif($typeRequest == "ALA"){

    $sql = "SELECT AlaId, AlaNome
            FROM Ala
            WHERE AlaUnidade = $iUnidade";
    
    $result = $conn->query($sql);
    $result = $result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
  }
}catch(PDOException $e) {
  // $conn->rollback();
  $_SESSION['msg']['titulo'] = "Erro";
  $_SESSION['msg']['mensagem'] = "Erro ao executar ação!!!";
  $_SESSION['msg']['tipo'] = "error";

  echo json_encode([
    'type' => $typeRequest,
    'err' => $e
  ]);die;
}
?>