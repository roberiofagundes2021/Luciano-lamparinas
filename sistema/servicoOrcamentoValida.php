<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if (isset($_POST['IdServico'])){
	
	$sql = "SELECT SrOrcId
			FROM ServicoOrcamento
			WHERE SrOrcEmpresa = ".$_SESSION['EmpreId']." and SrOrcServico = ". $_POST['IdServico'];

} else if (isset($_POST['IdServicoAntigo'])){
	
	$sql = "SELECT SrOrcId
			FROM ServicoOrcamento
			WHERE SrOrcEmpresa = ".$_SESSION['EmpreId']." and SrOrcServico = ". $_POST['IdServicoNovo']."
			and SrOrcServico <> ".$_POST['IdServicoAntigo'];
}
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo 1;
} else{
	echo 0;
}

?>
