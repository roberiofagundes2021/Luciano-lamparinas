<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputProfissionalId'])){
	
	$iProfissional = $_POST['inputProfissionalId'];
        	
	try{

		$conn->beginTransaction();
		
		$sql = "DELETE FROM ProfissionalXEspecialidade
				WHERE PrXEsProfissional = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iProfissional);
		$result->execute();

		$sql = "DELETE FROM Profissional
				WHERE ProfiId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iProfissional);
		$result->execute();

		$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Profissional excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {

		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Profissional!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("profissional.php");

?>
