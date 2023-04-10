<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputGrupoId'])){
	
	$iGrupo = $_POST['inputGrupoId'];
        	
	try{
		
		$sql = "DELETE FROM AtendimentoGrupo
				WHERE AtGruId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iGrupo); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Grupo excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir grupo!!! O registro a ser excluído está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("atendimentoGrupo.php");

?>
