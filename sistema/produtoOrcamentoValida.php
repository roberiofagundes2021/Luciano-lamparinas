<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');


if (isset($_POST['IdProduto'])){
	
	$sql = "SELECT PrOrcId
			FROM ProdutoOrcamento
			WHERE PrOrcEmpresa = ".$_SESSION['EmpreId']." and PrOrcProduto = ". $_POST['IdProduto'];

} else if (isset($_POST['IdProdutoAntigo'])){
	
	$sql = "SELECT PrOrcId
			FROM ProdutoOrcamento
			WHERE PrOrcEmpresa = ".$_SESSION['EmpreId']." and PrOrcProduto = ". $_POST['IdProdutoNovo']."
			and PrOrcProduto <> ".$_POST['IdProdutoAntigo'];
}


$result = $conn->query("$sql");
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo 1;
} else{
	echo 0;
}

?>
