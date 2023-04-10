<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

// Formata Data para aparecer DD/MM/YYYY
$sql = "SELECT OrcamId, OrcamNumero, convert(varchar, OrcamData,103) as OrcamData
		FROM Orcamento
		WHERE OrcamEmpresa = ".$_SESSION['EmpreId']." and OrcamFornecedor = '". $_GET['idFornecedor']."' and OrcamStatus = 1
		Order By OrcamId DESC";

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
