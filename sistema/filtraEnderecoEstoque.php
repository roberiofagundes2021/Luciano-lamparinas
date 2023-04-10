<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$sql = "SELECT UnidaEndereco as rua, UnidaNumero as numero, UnidaComplemento as complemento,
				UnidaBairro as bairro, UnidaCidade as cidade, UPPER(UnidaEstado) as estado
				FROM Unidade
				WHERE UnidaId = ". $_GET['idUnidade'];
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	$endereco = "$row[rua], $row[numero], $row[complemento], $row[bairro], $row[cidade]-$row[estado]";
	echo json_encode($endereco);
} else{
	echo 0;
}

?>
