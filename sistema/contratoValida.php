<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$count = 0;

if (isset($_POST['termoReferencia'])){

	$subCategoriasNovas = '';

	foreach ($_POST['subCategoriaNovas'] as $value) {
		if ($subCategoriasNovas == ''){
			$subCategoriasNovas .= $value;
		} else {
			$subCategoriasNovas .= ", ".$value;
		}
	} 

	// Quando tiver editando o contrato/fluxo
	if (isset($_POST['subCategoriasAntigas'])){

		$sql = "SELECT FlOpeId
		FROM FluxoOperacional
		JOIN FluxoOperacionalXSubCategoria on FOXSCFluxo = FlOpeId
		WHERE FlOpeUnidade = ".$_SESSION['UnidadeId']." and 
		FlOpeTermoReferencia = ".$_POST['termoReferencia']." and FOXSCSubCategoria in (".$subCategoriasNovas.") 
		and FOXSCSubCategoria not in (".$_POST['subCategoriasAntigas'].")";

	} else { //quando for um novo contrato/fluxo
		
		$sql = "SELECT FlOpeId
		FROM FluxoOperacional
		JOIN FluxoOperacionalXSubCategoria on FOXSCFluxo = FlOpeId
		WHERE FlOpeUnidade = ".$_SESSION['UnidadeId']." and 
		FlOpeTermoReferencia = ".$_POST['termoReferencia']." and FOXSCSubCategoria in (".$subCategoriasNovas.")";
	}	

	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);

	$count = count($row);			
}

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo 1;
} else{
	echo 0;
}

?>
