<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputDgNanId'])){
	
	$iNanda = $_POST['inputDgNanId'];
	$sStatus = $_POST['inputDgNanStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];
		
		$sql = "UPDATE DiagnosticoNanda SET DgNanStatus = :iStatus
				WHERE DgNanId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':iStatus', $iStatus); 
		$result->bindParam(':id', $iNanda); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do diagnóstico de enfermagem (NANDA) alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do diagnóstico de enfermagem (NANDA)!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("diagnosticoNanda.php");

?>
