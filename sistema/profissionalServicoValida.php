<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if(isset($_POST['servicoVelho'])){ 
	$sql = "SELECT PrXSVId
			FROM ProfissionalXServicoVenda
			WHERE PrXSVUnidade = ".$_SESSION['UnidadeId']." and PrXSVProfissional = ". $_SESSION['Servico_ProfissionalId'] . " and 
			PrXSVServicoVenda = '". $_POST['servicoNovo']."' and PrXSVServicoVenda <> '". $_POST['servicoVelho']."'";
} else{
	$sql = "SELECT PrXSVId
			FROM ProfissionalXServicoVenda
			WHERE PrXSVUnidade = ".$_SESSION['UnidadeId']."  and PrXSVProfissional = ". $_SESSION['Servico_ProfissionalId'] . "  and 
			PrXSVServicoVenda = '". $_POST['servicoNovo']."'";
}
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo 1;
} else{

	if ($_POST['estadoAtual'] == 'EDITA'){
		echo "EDITA";
	} else{
		echo 0;
	}
}

?>
