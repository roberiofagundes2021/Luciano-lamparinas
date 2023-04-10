<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputOrdemCompraEmpenhoID'])){
	
	$iOrdemCompraEmpenho = $_POST['inputOrdemCompraEmpenhoID'];
	$sArquivo = $_POST['inputOrdemCompraEmpenhoArquivo'];
	$sPasta = 'global_assets/anexos/ordemCompraEmpenho/';

	try{
		
		$sql = "DELETE FROM OrdemCompraEmpenho
				WHERE OrCEmId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iOrdemCompraEmpenho);
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

irpara("ordemCompraEmpenho.php");

?>
