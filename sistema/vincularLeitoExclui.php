<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputVincularLeitoId'])){
	
	$iVincularLeito = $_POST['inputVincularLeitoId'];
        	
	try{

		$sql = "DELETE FROM VincularLeitoXLeito
				WHERE VLXLeVinculaLeito= :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iVincularLeito); 
		$result->execute();
		
		$sql = "DELETE FROM VincularLeito
				WHERE VnLeiId  = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iVincularLeito); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Vinculação do leito excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir vinculação do leito!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("vincularLeito.php");

?>
