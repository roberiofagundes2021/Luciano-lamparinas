<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputPerfilId'])){
	
	$iPerfil = $_POST['inputPerfilId'];
	$sStatus = $_POST['inputPerfilStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	$sql = "SELECT SituaId
				FROM Situacao
				WHERE SituaChave = '$sStatus'";
	$result = $conn->query($sql);
	$situacao = $result->fetch(PDO::FETCH_ASSOC);
	$iStatus = $situacao['SituaId'];
	
	$sql = "UPDATE Perfil SET PerfiStatus = :iStatus 
			WHERE PerfiId = :id";
	$result = $conn->prepare($sql);
	$result->bindParam(':iStatus', $iStatus);
	$result->bindParam(':id', $iPerfil); 
	$result->execute();
	exit;
	try{

		
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do perfil alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do perfil!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("perfil.php");

?>
