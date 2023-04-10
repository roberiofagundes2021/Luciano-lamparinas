<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$sql = "SELECT TrRefTipo
		FROM TermoReferencia
		WHERE TrRefId = ".$_POST['iTr']." and TrRefUnidade = ".$_SESSION['UnidadeId'];
$result = $conn->query($sql);
$rowTipo = $result->fetch(PDO::FETCH_ASSOC);

$semRegistro = 0;

if ($rowTipo['TrRefTipo'] == 'P'){

	$sql = "SELECT COUNT(TRXPrProduto) as Qtde
			FROM TermoReferenciaXProduto
			WHERE TRXPrTermoReferencia = ".$_POST['iTr']." and TRXPrUnidade = ".$_SESSION['UnidadeId'];
	$result = $conn->query($sql);
	$rowVerifica = $result->fetch(PDO::FETCH_ASSOC);
			
	//Primeiro verifica se h? produtos vinculados a essa TR, caso haja a? sim adiciona a verifica??o se tem alguma quantidade n?o preenchida
	if ($rowVerifica['Qtde']){	
		$sql .= " and (TRXPrQuantidade is null or TRXPrQuantidade = 0) ";
	} else{
		$semRegistro = 1;
	}			
	
} else if ($rowTipo['TrRefTipo'] == 'S'){
	
	$sql = "SELECT COUNT(TRXSrServico) as Qtde
			FROM TermoReferenciaXServico
			WHERE TRXSrTermoReferencia = ".$_POST['iTr']." and TRXSrUnidade = ".$_SESSION['UnidadeId'];
	$result = $conn->query($sql);
	$rowVerifica = $result->fetch(PDO::FETCH_ASSOC);
	
	//Primeiro verifica se h? servi?os vinculados a essa TR, caso haja a? sim adiciona a verifica??o se tem alguma quantidade n?o preenchida
	if ($rowVerifica['Qtde']){	
		$sql .= " and (TRXSrQuantidade is null or TRXSrQuantidade = 0) ";
	} else{
		$semRegistro = 1;
	}
			
} else {
	
	$sql = "SELECT Sum(qtde) as Qtde
			FROM
			(
			(SELECT COUNT(TRXPrTermoReferencia) Qtde
			 FROM TermoReferenciaXProduto
			 WHERE TRXPrTermoReferencia = ".$_POST['iTr']." and TRXPrUnidade = ".$_SESSION['UnidadeId'].")
			UNION
			(SELECT COUNT(TRXSrTermoReferencia) qtde
			 FROM TermoReferenciaXServico
			 WHERE TRXSrTermoReferencia = ".$_POST['iTr']." and TRXSrUnidade = ".$_SESSION['UnidadeId'].")
			) as Soma";
	$result = $conn->query($sql);
	$rowVerifica = $result->fetch(PDO::FETCH_ASSOC);

	if ($rowVerifica['Qtde']){		
	
		$sql = "SELECT Sum(qtde) as Qtde
				FROM
				(
				(SELECT COUNT(TRXPrTermoReferencia) Qtde
				 FROM TermoReferenciaXProduto
				 WHERE (TRXPrQuantidade is null or TRXPrQuantidade = 0) and TRXPrTermoReferencia = ".$_POST['iTr']." and TRXPrUnidade = ".$_SESSION['UnidadeId'].")
				UNION
				(SELECT COUNT(TRXSrTermoReferencia) qtde
				 FROM TermoReferenciaXServico
				 WHERE (TRXSrQuantidade is null or TRXSrQuantidade = 0) and TRXSrTermoReferencia = ".$_POST['iTr']." and TRXSrUnidade = ".$_SESSION['UnidadeId'].")
				) as Soma";
	} else {
		$semRegistro = 1;
	}
}

$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);
$count = $row['Qtde'];
	
if($count or $semRegistro){
	echo 1;
} else{
	echo 0;
}

?>
