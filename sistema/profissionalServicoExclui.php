<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputProfissionalServicoId'])){
	
	$iProfissionalXServicoVenda = $_POST['inputProfissionalServicoId'];
        	
	try{
		
		$sql = "DELETE FROM ProfissionalXServicoVenda
				WHERE PrXSVId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iProfissionalXServicoVenda); 
		$result->execute();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Servico do profissional excluído!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir servico do profissional!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();
	}
}

irpara("profissionalServico.php");

?>
