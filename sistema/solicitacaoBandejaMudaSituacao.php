<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputSolicitacaoId'])){
	
	$iSolicitacao = $_POST['inputSolicitacaoId'];
        	
	try{

		$conn->beginTransaction();

		$sql = "SELECT SituaId
				FROM Situacao	
				WHERE SituaChave = '".$_POST['inputSolicitacaoStatus']."'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);

		if ($_POST['inputSolicitacaoStatus'] == 'NAOLIBERADO'){
			$motivo = $_POST['inputMotivo'];
			$msg = "Solicitação não liberada!";
		} else{
			$motivo = NULL;
			$msg = "Solicitação liberada!";
		}
		
		$sql = "UPDATE Solicitacao SET SolicSituacao = :bStatus, SolicUsuarioAtualizador = :iUsuario
				WHERE SolicId = :iSolicitacao";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $row['SituaId']);
		$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
		$result->bindParam(':iSolicitacao', $iSolicitacao);
		$result->execute();
		
		$sql = "UPDATE Bandeja SET BandeStatus = :bStatus, BandeMotivo = :sMotivo, BandeUsuarioAtualizador = :iUsuario
				WHERE BandeId = :iBandeja";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $row['SituaId']);
		$result->bindParam(':sMotivo', $motivo);
		$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
		$result->bindParam(':iBandeja', $_POST['inputBandejaId']);
		$result->execute();

		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] =  $msg;
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {

		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro na liberação da solicitação!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();exit;
	}
}

irpara("index.php");

?>
