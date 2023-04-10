<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputProfissionalAnexoId'])){
	
	$iProfissionalAnexo = $_POST['inputProfissionalAnexoId'];
	$sArquivo = $_POST['inputProfissionalAnexoArquivo'];
	$sPasta = 'global_assets/anexos/profissional/';

	try{
		
		$sql = "DELETE FROM ProfissionalAnexo
				WHERE PrAneId = :id";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iProfissionalAnexo);
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

irpara("profissionalAnexo.php");

?>
