<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if(isset($_POST['inputOrdemCompraId'])){
	
	$sql = "SELECT SituaId
			FROM Situacao	
			WHERE SituaChave = '". $_POST['inputOrdemCompraStatus'] ."' and SituaStatus = 1";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);
	$bStatus = $row['SituaId'] > 0 ? $row['SituaId'] : null;	
	
	$iOrdemCompra = $_POST['inputOrdemCompraId'];
        	
	try{
		
		$sql = "UPDATE OrdemCompra SET OrComSituacao = :bStatus, OrComUsuarioAtualizador = :iUsuario
				WHERE OrComId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $bStatus);
		$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
		$result->bindParam(':id', $iOrdemCompra);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação da ordem de compra alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação da ordem de compra!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("index.php");

?>
