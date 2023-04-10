<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if (isset($_GET['idOrcamento'])){
	$sql = "SELECT SbCatId, SbCatNome
			FROM Patrimonio
			JOIN on OrXSCSubcategoria = SbCatId
			JOIN Situacao on SituaId = SbCatStatus
			WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and OrXSCOrcamento = '". $_GET['idOrcamento']."' and SituaChave = 'ATIVO' ";

}

$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo json_encode($row);
} else{
	echo 0;
}

?>
