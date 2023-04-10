<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputClienteAnexoId'])){
	
	$iClienteAnexo = $_POST['inputClienteAnexoId'];
	$sArquivo = $_POST['inputClienteAnexoArquivo'];
	$sPasta = 'global_assets/anexos/cliente/';

	try{
		
		$sql = "DELETE FROM ClienteAnexo
				WHERE ClAneId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iClienteAnexo);
		$result->execute();

		if (file_exists($sPasta.$sArquivo) and $sArquivo <> ""){
			unlink($sPasta.$sArquivo);
		}
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Anexo excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Anexo!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("clienteAnexo.php");

?>
