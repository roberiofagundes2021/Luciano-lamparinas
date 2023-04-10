<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputGrupoContaId'])){
	
	$iGrupoConta = $_POST['inputGrupoContaId'];
	$sStatus = $_POST['inputGrupoContaStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{
		
		$sql = "SELECT SituaId
				FROM Situacao
			    WHERE SituaChave = '". $sStatus."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $row['SituaId'];

		$sql = "UPDATE GrupoConta SET GrConStatus = :bStatus
				WHERE GrConId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':bStatus', $iStatus); 
		$result->bindParam(':id', $iGrupoConta); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do grupo conta alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do grupo conta!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("grupo.php");

?>
