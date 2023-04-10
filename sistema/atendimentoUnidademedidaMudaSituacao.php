<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputAtendimentoUnidadeMedidaId'])){
	
	$iAtendimentoUnidadeMedida = $_POST['inputAtendimentoUnidadeMedidaId'];
	$sStatus = $_POST['inputAtendimentoUnidadeMedidaStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{
		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE AtendimentoUnidadeMedida SET AtUMeStatus = :bStatus
				WHERE AtUMeId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':bStatus', $iStatus); 
		$result->bindParam(':id', $iAtendimentoUnidadeMedida); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação da unidade de medida alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação da unidade de medida!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("AtendimentoUnidadeMedida.php");

?>
