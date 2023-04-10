<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputSrOrcId'])){
	
	$inputSrOrcId = $_POST['inputSrOrcId'];
	$inputSrOrcStatus = $_POST['inputSrOrcStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '$inputSrOrcStatus'  "; // ver essa sintaxe
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE ServicoOrcamento SET SrOrcSituacao = :bStatus
				WHERE SrOrcId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':bStatus', $iStatus);
		$result->bindParam(':id', $inputSrOrcId);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do serviço alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do serviço!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("servicoOrcamento.php");

?>
