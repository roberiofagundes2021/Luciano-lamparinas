<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputComissaoAnexoID'])){
	
	$iComissaoAnexoID = $_POST['inputComissaoAnexoID'];
	$sArquivo = $_POST['inputComissaoAnexoArquivo'];
	$sPasta = 'global_assets/anexos/comissao/';

	try{
		
		$conn->beginTransaction();

		$sql = "
				DELETE 
				FROM TRXComissao
				WHERE TRXCoId = :id
			";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iComissaoAnexoID);
		$result->execute();

		if (file_exists($sPasta.$sArquivo) and $sArquivo <> ""){
			unlink($sPasta.$sArquivo);
		}

		$sql = "INSERT INTO AuditTR ( AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento)
				VALUES (:iTRTermoReferencia, :iTRDataHora, :iTRUsuario, :iTRTela, :iTRDetalhamento)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
			':iTRTermoReferencia' => $_SESSION['TRId'],
			':iTRDataHora' => date("Y-m-d H:i:s"),
			':iTRUsuario' => $_SESSION['UsuarId'],
			':iTRTela' =>'COMISSÃO DO PROCESSO LICITATÓRIO',
			':iTRDetalhamento' =>'EXCLUSÃO DO ANEXO'
		));

		$conn->commit();
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Anexo excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$conn->rollback();
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir Anexo!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("trComissao.php");

?>
