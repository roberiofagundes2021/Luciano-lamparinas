<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputDotacaoID'])){
	
	$iDotacaoID = $_POST['inputDotacaoID'];
	$sArquivo = $_POST['inputDotacaoArquivo'];
	$sPasta = 'global_assets/anexos/dotacaoOrcamentaria/';

	try{
		
		$conn->beginTransaction();

		$sql = "
			DELETE 
			FROM DotacaoOrcamentaria
			WHERE DtOrcId = :id
		";
		$result = $conn->prepare("$sql");
		$result->bindParam(':id', $iDotacaoID);
		$result->execute();

		if (file_exists($sPasta.$sArquivo) and $sArquivo <> ""){
			unlink($sPasta.$sArquivo);
		}

		/* Muda o status da TR*/
		$sql = "
			SELECT SituaId
			FROM Situacao	
			WHERE SituaChave = 'LIBERADOCENTRO'
		";
		$result = $conn->query($sql);
		$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);

		/* Capturando dados para Update */
		$bStatus = intval($rowSituacao['SituaId']);
		$iUsuario = intval($_SESSION['UsuarId']);
		$iTermoReferenciaId = intval($_SESSION['inputTRIdDotacao']);

		/* Atualizando dado no BD */
		$sql = "
			UPDATE TermoReferencia
				 SET TrRefStatus = :bStatus, 
							TrRefUsuarioAtualizador = :iUsuario
			WHERE TrRefId = :iTermoReferenciaId
		";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $bStatus);
		$result->bindParam(':iUsuario', $iUsuario);
		$result->bindParam(':iTermoReferenciaId', $iTermoReferenciaId);
		$result->execute();

		/* Atualiza status bandeja */
		$sql = "
			UPDATE Bandeja 
					SET BandeStatus = :bStatus
				WHERE BandeUnidade = :iUnidade 
					AND BandeId in (Select BandeId FROM Bandeja WHERE BandeTabelaId = :iTermoReferenciaId and 
					BandePerfil = 'CONTABILIDADE')
		";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $bStatus);
		$result->bindParam(':iUnidade', $_SESSION['UnidadeId']);
		$result->bindParam(':iTermoReferenciaId', $iTermoReferenciaId);
		$result->execute();

		$sql = "INSERT INTO AuditTR ( AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento)
				VALUES (:iTRTermoReferencia, :iTRDataHora, :iTRUsuario, :iTRTela, :iTRDetalhamento)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
			':iTRTermoReferencia' => $iTermoReferenciaId ,
			':iTRDataHora' => date("Y-m-d H:i:s"),
			':iTRUsuario' => $_SESSION['UsuarId'],
			':iTRTela' =>'DOTAÇÃO ORÇAMENTÁRIA',
			':iTRDetalhamento' =>'EXCLUSÃO DO REGISTRO'
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

irpara("trDotacao.php");

?>
