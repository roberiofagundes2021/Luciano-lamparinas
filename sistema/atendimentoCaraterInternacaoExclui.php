<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputCaraterInternacaoId'])){
	
	$iCaraterInternacao = $_POST['inputCaraterInternacaoId'];
        	
	try{
		
		$sql = "DELETE FROM CaraterInternacao
				WHERE CrIntId = :id";	
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iCaraterInternacao); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Tipo de Caráter de Internação excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Caráter de Internação!!! O registro está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		// echo 'Error: ' . $e->getMessage();
	}
}

irpara("atendimentoCaraterInternacao.php");

?>
