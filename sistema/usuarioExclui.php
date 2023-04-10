<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputUsuarioId'])){
	
	$iUsuario = $_POST['inputUsuarioId'];
	$iEmpresa = isset($_SESSION['EmpresaId']) ? $_SESSION['EmpresaId'] : $_SESSION['EmpreId'];
        	
	try{

		$conn->beginTransaction();

		//Pega o Id da EmpresaXUsuarioXPerfil para poder excluir as unidades vinculadas
		$sql = "SELECT EXUXPId
				FROM EmpresaXUsuarioXPerfil
				WHERE EXUXPUsuario = ".$iUsuario." and EXUXPEmpresa = ".$iEmpresa;
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);

		//Exclui as unidades vinculadas ao usuário
		$sql = "DELETE FROM UsuarioXUnidade
				WHERE UsXUnEmpresaUsuarioPerfil = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $row['EXUXPId']);
		$result->execute();

		//Exclui os usuarios da tabela EmpresaXUsuarioXPerfil para a empresa em uso
		$sql = "DELETE FROM EmpresaXUsuarioXPerfil
				WHERE EXUXPUsuario = :iUsuario and EXUXPEmpresa = :iEmpresa";
		$result = $conn->prepare($sql);
		$result->bindParam(':iUsuario', $iUsuario);
		$result->bindParam(':iEmpresa', $iEmpresa);
		$result->execute();

		//Verifica se sobrou algum registro do usuário em outras empresas. Se sim não exlcui o usuário de vez. Do contrário, pode excluir
		$sql = "SELECT COUNT(EXUXPId) as CONT
				FROM EmpresaXUsuarioXPerfil
				WHERE EXUXPUsuario = ".$iUsuario;
		$result = $conn->query($sql);
		$rowUsuario = $result->fetch(PDO::FETCH_ASSOC);

		//Verifica se o usuário está sendo usado em outra empresa. Zero, significa que não
		if ($rowUsuario['CONT'] == 0){
			$sql = "DELETE FROM Usuario
					WHERE UsuarId = :id";
			$result = $conn->prepare($sql);
			$result->bindParam(':id', $iUsuario);
			$result->execute();
		}

		$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Usuário excluído!!!";
		$_SESSION['msg']['tipo'] = "success";	
		
	} catch(PDOException $e) {

		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] ="Erro ao excluir usuário!!! O registro a ser excluído está sendo usado em outro local.";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("usuario.php");

?>
