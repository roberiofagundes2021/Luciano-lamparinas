<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

//Aqui eu verifico qual ID da Empresa devo usar: $_SESSION['EmpreId'] ou $_SESSION['EmpresaId']? Ou seja, estou adicionando um usuario da empresa logada ou de outra empresa?
if(isset($_SESSION['EmpresaId']) and $_SESSION['EmpresaId'] <> $_SESSION['EmpreId']){
	$sql = "SELECT SetorId, SetorNome
			FROM Setor
			JOIN Situacao on SituaId = SetorStatus
			WHERE SetorEmpresa = ".$_SESSION['EmpresaId']." and SetorUnidade = ". $_GET['idUnidade']." and SituaChave = 'ATIVO'";
} else {
	$sql = "SELECT SetorId, SetorNome
			FROM Setor
			JOIN Situacao on SituaId = SetorStatus
			WHERE SetorEmpresa = ".$_SESSION['EmpreId']." and SetorUnidade = ". $_GET['idUnidade']." and SituaChave = 'ATIVO'";	
}
$result = $conn->query("$sql");
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo json_encode($row);
} else{
	echo 0;
}

?>
