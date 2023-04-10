<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['tipoAcomodacaoId'])){
	
	$iTipoAcomodacao = $_POST['tipoAcomodacaoId'];
        	
	try{
		
		$sql = "DELETE FROM TipoAcomodacao
				WHERE TpAcoId = :id";	
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iTipoAcomodacao); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Tipo de acomodação excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Tipo de Acomodação!!! O registro está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();
	}
}

irpara("atendimentoTipoAcomodacao.php");

?>
