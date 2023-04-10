<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if (isset($_SESSION['EmpresaId'])){
	$EmpresaId = $_SESSION['EmpresaId'];
} else {	
	$EmpresaId = $_SESSION['EmpreId'];
}

if(isset($_POST['inputUsuarioId'])){
	
	$iUsuario = $_POST['inputUsuarioId'];
	$sStatus = $_POST['inputUsuarioStatus'] == 'ATIVO' ? 'INATIVO' : 'ATIVO';
        	
	try{

		$sql = "SELECT SituaId
				FROM Situacao
				WHERE SituaChave = '$sStatus'";
		$result = $conn->query($sql);
		$situacao = $result->fetch(PDO::FETCH_ASSOC);
		$iStatus = $situacao['SituaId'];
		
		$sql = "UPDATE EmpresaXUsuarioXPerfil SET EXUXPStatus = :iStatus
				WHERE EXUXPUsuario = :idUsuario and EXUXPEmpresa = :idEmpresa";
		$result = $conn->prepare($sql);
		$result->bindParam(':iStatus', $iStatus);
		$result->bindParam(':idUsuario', $iUsuario);
		$result->bindParam(':idEmpresa', $EmpresaId);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Situação do usuário alterada!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar situação do usuário!!!";
		$_SESSION['msg']['tipo'] = "error";
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("usuario.php");

?>
