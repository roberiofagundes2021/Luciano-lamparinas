<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputTipoFiscalId'])){
	
	$iTipoFiscal = $_POST['inputTipoFiscalId'];
	$sStatus = $_POST['inputTipoFiscalStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{
		
		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];

		$sql = "UPDATE TipoFiscal SET TpFisStatus = :bStatus
				WHERE TpFisId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':bStatus', $iStatus); 
		$result->bindParam(':id', $iTipoFiscal); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do Tipo Fiscal alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do Tipo Fiscal!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("tipoFiscal.php");

?>
