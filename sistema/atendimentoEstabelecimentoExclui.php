<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputEstabId'])){
	
	$iEstabelecimento = $_POST['inputEstabId'];
        	
	try{
		
		$sql = "DELETE FROM Estabelecimento
				WHERE EstabId = :id";	
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iEstabelecimento);
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Relação de Estabelecimento excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		$mensagemDeErro = "Erro ao excluir Estabelecimento!!!";
		if(
			substr($e->getMessage(), 0, 134)==
			'SQLSTATE[23000]: [Microsoft][ODBC Driver 17 for SQL Server][SQL Server]The DELETE statement conflicted with the REFERENCE constraint "'
		){
			$mensagemDeErro = "Este registro já está sendo utilizado em outro lugar do sistema. Por favor, exclua todas as dependências dele antes de excluí-lo.";
		}
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = $mensagemDeErro;
		$_SESSION['msg']['tipo'] = "error";		
	}
}

irpara("atendimentoEstabelecimento.php");

?>
