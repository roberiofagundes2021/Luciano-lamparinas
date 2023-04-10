<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputMovimentacaoAnexoID'])){
	
	$iMovimentacaoAnexo = $_POST['inputMovimentacaoAnexoID'];
	$sArquivo = $_POST['inputMovimentacaoAnexoArquivo'];
	$sPasta = 'global_assets/anexos/movimentacao/';

	try{
		
		$sql = "DELETE FROM MovimentacaoAnexo
				WHERE MvAneId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iMovimentacaoAnexo);
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

irpara("movimentacaoAnexo.php");

?>
