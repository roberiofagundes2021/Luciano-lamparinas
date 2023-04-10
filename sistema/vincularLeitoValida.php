<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');
/*
if (isset($_POST['acomodacaoVelho'])){
	$sql = "SELECT VnLeiId
			 FROM VincularLeito
			 WHERE VnLeiUnidade = ".$_SESSION['UnidadeId']." and VnLeiTipoAcomodacao = ". $_POST['acomodacaoNovo']." and VnLeiTipoAcomodacao <> ". $_POST['acomodacaoVelho']."";
} else{
	$sql = "SELECT VnLeiId
			 FROM VincularLeito
			 WHERE VnLeiUnidade = ".$_SESSION['UnidadeId']." and VnLeiTipoAcomodacao = ". $_POST['acomodacao']."";
}
*/
/*var_dump($_SESSION['UnidadeId'], $_POST['acomodacaoNovo'], $_POST['acomodacaoVelho'], $_POST['alaNovo'], $_POST['alaVelho'],
		$_POST['especialidadeLeitoNovo'], $_POST['especialidadeLeitoVelho'], $_POST['tipoInternacaoNovo'], $_POST['tipoInternacaoVelho'],
		$_POST['quartoNovo'], $_POST['quartoVelho']);*/

if (isset($_POST['acomodacaoVelho'])){
	$sql = "SELECT VnLeiId
			FROM VincularLeito
			WHERE VnLeiUnidade = ".$_SESSION['UnidadeId']." 
			and VnLeiTipoAcomodacao = ". $_POST['acomodacaoNovo']." and VnLeiTipoAcomodacao <> ". $_POST['acomodacaoVelho']."
			and VnLeiAla = ". $_POST['alaNovo']." and VnLeiAla <> ". $_POST['alaVelho']."
			and VnLeiEspecialidadeLeito = ". $_POST['especialidadeLeitoNovo']." and VnLeiEspecialidadeLeito <> ". $_POST['especialidadeLeitoVelho']."
			and VnLeiTipoInternacao = ". $_POST['tipoInternacaoNovo']." and VnLeiTipoInternacao <> ". $_POST['tipoInternacaoVelho']."
			and VnLeiQuarto = ". $_POST['quartoNovo']." and VnLeiQuarto <> ". $_POST['quartoVelho']."
		   ";
} else{
	$sql = "SELECT VnLeiId
			FROM VincularLeito
			WHERE VnLeiUnidade = ".$_SESSION['UnidadeId']." 
			and VnLeiTipoAcomodacao = ". $_POST['acomodacao']."
			and VnLeiAla = ". $_POST['ala']."
			and VnLeiEspecialidadeLeito = ". $_POST['especialidadeLeito']."
			and VnLeiTipoInternacao = ". $_POST['tipoInternacao']."
			and VnLeiQuarto = ". $_POST['quarto']."
		   ";
} 

$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);

//Verifica se já existe esse registro (se existir, retorna true )
if($count){
	echo 1;
} else{

	if ($_POST['estadoAtual'] == 'EDITA'){
		echo "EDITA";
	} else{
		echo 0;
	}
}

?>
