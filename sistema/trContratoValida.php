<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$count = 0;

if (isset($_POST['IdTR'])){

	$sql = "SELECT COUNT(TRXSCSubcategoria) as CountTRSubCategorias
			FROM TermoReferencia
			JOIN TRXSubcategoria on TRXSCTermoReferencia = TrRefId
			WHERE TrRefUnidade = ".$_SESSION['UnidadeId']." and TrRefId = ". $_POST['IdTR'];
	$result = $conn->query($sql);	
	$rowSubCategorias = $result->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT COUNT(FOXSCSubCategoria) as CountContratoSubCategorias
			FROM FluxoOperacional
			JOIN TermoReferencia on TrRefId = FlOpeTermoReferencia
			JOIN FluxoOperacionalXSubCategoria on FOXSCFluxo = FlOpeId
			WHERE TrRefUnidade = ".$_SESSION['UnidadeId']." and TrRefId = ". $_POST['IdTR'];
	$result = $conn->query($sql);
	$rowContrato = $result->fetch(PDO::FETCH_ASSOC);
} 

//Verifica se já foi realizado contrato para todas as SubCategorias (se existir, retorna true )
if($rowSubCategorias['CountTRSubCategorias'] == $rowContrato['CountContratoSubCategorias']){
	echo 1;
} else{
	echo 0;
}

?>
