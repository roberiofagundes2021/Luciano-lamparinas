<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputMdLicId'])){
	
	$iModalidadeLicitacao = $_POST['inputMdLicId'];
        	
	try{
		
		$sql = "DELETE FROM ModalidadeLicitacao
				WHERE MdLicId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iModalidadeLicitacao); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Modalidade excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir categoria!!! Geralmente isso ocorre quando o registro a ser excluido esta sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";			
		
		//echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("modalidadeLicitacao.php");

?>
