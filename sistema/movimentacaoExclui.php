<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

if(isset($_POST['inputMovimentacaoId'])){
	
	$iMovimentacao = $_POST['inputMovimentacaoId'];
	
	try{

		$conn->beginTransaction();

		/*----- DELETA MOVIMENTAÇÃO POR PRODUTO -----*/
		$sql = "DELETE FROM MovimentacaoXProduto
				WHERE MvXPrMovimentacao = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iMovimentacao); 
		$result->execute();

		/*----- DELETA MOVIMENTAÇÃO POR SERVICO -----*/
		$sql = "DELETE FROM MovimentacaoXServico
				WHERE MvXSrMovimentacao = :id"; 
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iMovimentacao); 
		$result->execute();

		/*----- SELECIONA OS ANEXOS DA MOVIMENTAÇÃO PARA EXCLUIR OS ARQUIVOS FÍSICOS (SE COMMIT OCORRER) -----*/
		$sql = "SELECT MvAneArquivo FROM MovimentacaoAnexo
				WHERE MvAneMovimentacao = ". $iMovimentacao; 
		$result = $conn->query($sql);
		$rowAnexos = $result->fetchAll();				

		/*----- DELETA MOVIMENTAÇÃO ANEXO -----*/
		$sql = "DELETE FROM MovimentacaoAnexo
				WHERE MvAneMovimentacao = :id"; 
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iMovimentacao); 
		$result->execute();
		
		/*----- DELETA MOVIMENTAÇÃO ANEXO -----*/
		$sql = "DELETE FROM MovimentacaoLiquidacao
				WHERE MvLiqMovimentacao = :id"; 
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iMovimentacao); 
		$result->execute();		

		/*----- DELETA MOVIMENTAÇÃO -----*/
		$sql = "DELETE FROM Movimentacao
				WHERE MovimId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iMovimentacao); 
		$result->execute();

		// Selecionando o id da Bandeja 
		$sql = "SELECT BandeId 
		FROM Bandeja
		WHERE BandeTabelaId =  ". $iMovimentacao ." and BandeTabela = 'Movimentacao' ";
		$result = $conn->query($sql);
		$Bandeja= $result->fetch(PDO::FETCH_ASSOC);

		/*----- DELETA BANDEJA -----*/
		$sql = "DELETE FROM Bandeja
				WHERE BandeId = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $Bandeja['BandeId']); 
		$result->execute();

		
		/*----- DELETA BANDEJA X PERFIL -----*/
		$sql = "DELETE FROM BandejaXPerfil
				WHERE BnXPeBandeja = :id";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $Bandeja['BandeId']); 
		$result->execute();

		$conn->commit();

		// Exlui os anexos físicos do servidor
		foreach ($rowAnexos as $item){
			$sArquivo = $item['MvAneArquivo'];
			$sPasta = 'global_assets/anexos/movimentacao/';
	
			if (file_exists($sPasta.$sArquivo) and $sArquivo <> ""){
				unlink($sPasta.$sArquivo);
			}
		}		
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Movimentação excluída!!!";
		$_SESSION['msg']['tipo'] = "success";		
		
	} catch(PDOException $e) {
		
		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao excluir movimentação!!!";
		$_SESSION['msg']['tipo'] = "error";			
		
		echo 'Error: ' . $e->getMessage();die;
	}
}

irpara("movimentacao.php");

?>