<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$sql = "SELECT LcEstId, LcEstNome, UnidaEndereco as rua,UnidaNumero as numero,
				UnidaComplemento as complemento, UnidaBairro as bairro, UnidaCidade as cidade, UnidaEstado as estado
		FROM LocalEstoque
		JOIN Situacao on SituaId = LcEstStatus
		JOIN Unidade on UnidaId = LcEstUnidade
		WHERE LcEstUnidade = ". $_GET['idUnidade']." and SituaChave = 'ATIVO' ";
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
