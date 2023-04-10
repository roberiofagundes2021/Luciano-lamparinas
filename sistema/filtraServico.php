<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

if (isset($_GET['idFornecedor']) && $_GET['idFornecedor'] != '#' && $_GET['idFornecedor'] != '-1'){

	if (isset($_GET['idSubCategoria']) && $_GET['idSubCategoria'] != '#'){
		$sql = ("SELECT ServiId, ServiNome, ServiValorCusto, ServiCustoFinal
				 FROM Servico
				 JOIN Categoria on CategId = ServiCategoria
				 JOIN Fornecedor on ForneCategoria = CategId
				 WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and ForneId = '". $_GET['idFornecedor']."' and ServiSubCategoria = '". $_GET['idSubCategoria']."'");
	} else {
		$sql = ("SELECT ServiId, ServiNome, ServiValorCusto, ServiCustoFinal
				 FROM Servico
				 JOIN Categoria on CategId = ServiCategoria
				 JOIN Fornecedor on ForneCategoria = CategId
				 WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and ForneId = '". $_GET['idFornecedor']."'");		
	}
	
} else {

	//Isso aqui é pra corrigir um bug. Já que o correto era vir #
	if(isset($_GET['idSubCategoria']) and $_GET['idSubCategoria'] == null) $_GET['idSubCategoria'] = "#";

	if (isset($_GET['idSubCategoria']) and $_GET['idSubCategoria'] != "#"){
		$sql = ("SELECT ServiId, ServiNome, ServiValorCusto, ServiCustoFinal
				 FROM Servico
				 WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and ServiSubCategoria = '". $_GET['idSubCategoria']."'");
	} else {
		$sql = ("SELECT ServiId, ServiNome, ServiValorCusto, ServiCustoFinal
				 FROM Servico
				 WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and ServiCategoria = '". $_GET['idCategoria']."'");
	}
}

$result = $conn->query("$sql");
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);
//echo json_encode($sql);

//Verifica se já existe esse registro (se existir, retorna true)
//var_dump($row);
//print('teste php');
if($count){
    echo json_encode($row);
} else{
	echo 0;
}

?>
