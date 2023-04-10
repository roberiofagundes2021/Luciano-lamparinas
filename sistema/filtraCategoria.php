<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if (isset($_GET['idOrcamento'])){
	$sql = "SELECT CategId, CategNome
			FROM Categoria
			JOIN Orcamento on OrcamCategoria = CategId
			JOIN Situacao on SituaId = CategStatus 
			WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO' and OrcamId = ".$_GET['idOrcamento']."
			ORDER BY CategNome ASC";
} else if (isset($_GET['idFornecedor']) && $_GET['idFornecedor'] != -1){
	$sql = "SELECT CategId, CategNome
			FROM Categoria
			JOIN Fornecedor on ForneCategoria = CategId
			JOIN Situacao on SituaId = CategStatus
			WHERE CategEmpresa = ".$_SESSION['EmpreId']." and ForneId = '". $_GET['idFornecedor']."' and SituaChave = 'ATIVO'
			ORDER BY CategNome ASC";
} else if (isset($_GET['idServico']) && $_GET['idServico'] != -1){
	$sql = "SELECT CategId, CategNome
			FROM Categoria 
			JOIN Servico on ServiCategoria = CategId
			WHERE CategEmpresa = ".$_SESSION['EmpreId']." and ServiId = ". $_GET['idServico'];
} else if (isset($_GET['idProduto']) && $_GET['idProduto'] != -1){
	$sql = "SELECT CategId, CategNome
			FROM Categoria 
			JOIN Produto on ProduCategoria = CategId
			WHERE CategEmpresa = ".$_SESSION['EmpreId']." and ProduId = ". $_GET['idProduto'];
} else{
	$sql = "SELECT CategId, CategNome
			FROM Categoria
			JOIN Situacao on SituaId = CategStatus
			WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
			ORDER BY CategNome ASC";
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
