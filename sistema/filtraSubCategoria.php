<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if (isset($_GET['idOrcamento'])){
	$sql = "SELECT SbCatId, SbCatNome
			FROM SubCategoria
			JOIN OrcamentoXSubCategoria on OrXSCSubcategoria = SbCatId
			JOIN Situacao on SituaId = SbCatStatus
			WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and OrXSCOrcamento = ". $_GET['idOrcamento']." and SituaChave = 'ATIVO' ";
} else if (isset($_GET['idFornecedor']) && isset($_GET['idTR'])){
	$sql = "SELECT DISTINCT SbCatId, SbCatNome
			FROM SubCategoria
			JOIN FornecedorXSubCategoria on FrXSCSubCategoria = SbCatId
			JOIN Situacao on SituaId = SbCatStatus
			JOIN TRXSubcategoria on TRXSCSubcategoria = FrXSCSubCategoria
			WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and FrXSCFornecedor = ". $_GET['idFornecedor']." and 
			SituaChave = 'ATIVO' and TRXSCTermoReferencia = ".$_GET['idTR']." and SbCatId not in (Select FOXSCSubCategoria From FluxoOperacional
			JOIN FluxoOperacionalXSubCategoria on FOXSCFluxo = FlOpeId
			where FlOpeTermoReferencia = ".$_GET['idTR'].") ";

} else if (isset($_GET['idFornecedor']) && $_GET['idFornecedor'] != -1){
	$sql = "SELECT SbCatId, SbCatNome
			FROM SubCategoria
			JOIN FornecedorXSubCategoria on FrXSCSubCategoria = SbCatId
			JOIN Situacao on SituaId = SbCatStatus
			WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and FrXSCFornecedor = ". $_GET['idFornecedor']." and SituaChave = 'ATIVO' ";
} else if (isset($_GET['produtoServico'])){

	if ($_GET['produtoServico'] == 'S'){
		$sql = "SELECT DISTINCT SbCatId, SbCatNome
		FROM SubCategoria
		JOIN Servico on ServiSubCategoria = SbCatId
		JOIN Situacao on SituaId = SbCatStatus
		WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and SbCatCategoria = ". $_GET['idCategoria']." and SituaChave = 'ATIVO' ";
	} else {
		$sql = "SELECT DISTINCT SbCatId, SbCatNome
		FROM SubCategoria
		JOIN Produto on ProduSubCategoria = SbCatId
		JOIN Situacao on SituaId = SbCatStatus
		WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and SbCatCategoria = ". $_GET['idCategoria']." and SituaChave = 'ATIVO' ";
	}

} else if (isset($_GET['idServico']) && $_GET['idServico'] != -1){
	$sql = "SELECT SbCatId, SbCatNome
			FROM SubCategoria 
			JOIN Servico on ServiSubCategoria = SbCatId
			WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and ServiId = ". $_GET['idServico'];

} else if (isset($_GET['idProduto']) && $_GET['idProduto'] != -1){
	$sql = "SELECT SbCatId, SbCatCodigo, SbCatNome
			FROM SubCategoria 
			JOIN Produto on ProduSubCategoria = SbCatId
			WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and ProduId = ". $_GET['idProduto'];

} else if (isset($_GET['idContrato']) && $_GET['idContrato'] != -1){
	$sql = "SELECT SbCatId, SbCatNome
			FROM SubCategoria
			JOIN FluxoOperacionalXSubCategoria on FOXSCSubCategoria = SbCatId
			JOIN FluxoOperacional on FlOpeId = FOXSCFluxo
			WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and FlOpeId = ". $_GET['idContrato'];
} else if (isset($_GET['idFluxo']) && isset($_GET['idCategoria'])){
	$sql = "SELECT SbCatId, SbCatNome
			FROM SubCategoria
			JOIN Situacao on SituaId = SbCatStatus
			JOIN FluxoOperacionalXSubCategoria on FOXSCSubCategoria = SbCatId and FOXSCFluxo = '$_GET[idFluxo]'
			WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and SbCatCategoria = ".
			$_GET['idCategoria']." and SituaChave = 'ATIVO'";
}else {
	$sql = "SELECT SbCatId, SbCatCodigo, SbCatNome
			FROM SubCategoria
			JOIN Situacao on SituaId = SbCatStatus
			WHERE SbCatEmpresa = ".$_SESSION['EmpreId']." and SbCatCategoria = ".
			$_GET['idCategoria']." and SituaChave = 'ATIVO' ";
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
