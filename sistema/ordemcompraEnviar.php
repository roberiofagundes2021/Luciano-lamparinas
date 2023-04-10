<?php 

include_once("sessao.php");

include('global_assets/php/conexao.php');

try{
	if(isset($_POST['inputOrdemCompraId'])){
	
        $conn->beginTransaction();		
		
		$iOrdemCompra = $_POST['inputOrdemCompraId'];

		/* Atualiza o Status da Ordem de Compra para "Aguardando Liberação" */
		$sql = "SELECT SituaId
				FROM Situacao
				Where SituaChave = 'AGUARDANDOLIBERACAOCENTRO' ";
		$result = $conn->query($sql);
		$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "UPDATE OrdemCompra SET OrComSituacao = :iStatus
	            WHERE OrComId = :iOrdemCompra";
	    $result = $conn->prepare($sql);

	    $result->execute(array(
			':iStatus' => $rowSituacao['SituaId'],
			':iOrdemCompra' => $iOrdemCompra					
			));
	    /* Fim Atualiza */

		$sql = "SELECT PerfiId
				FROM Perfil
				Where PerfiChave IN ('ADMINISTRADOR','CENTROADMINISTRATIVO') and PerfiUnidade = " . $_SESSION['UnidadeId'];
		$result = $conn->query($sql);
		$rowPerfil = $result->fetchAll(PDO::FETCH_ASSOC);

		$sql = "SELECT OrComNumero, OrComNumProcesso
				FROM OrdemCompra
				Where OrComId = ".$iOrdemCompra;
		$result = $conn->query($sql);
		$rowOrdemCompra = $result->fetch(PDO::FETCH_ASSOC);

		/* Verifica se a Bandeja já tem um registro com BandeTabela: OrdemCompra e BandeTabelaId: IdOrdemCompraAtual, evitando duplicação */
		$sql = "SELECT COUNT(BandeId) as Count
				FROM Bandeja
				Where BandeTabela = 'OrdemCompra' and BandeTabelaId =  ".$iOrdemCompra;
		$result = $conn->query($sql);
		$rowBandeja = $result->fetch(PDO::FETCH_ASSOC);
		$count = $rowBandeja['Count'];

		$sql = "SELECT BandeId, SituaChave
				FROM Bandeja
				JOIN Situacao on SituaId = BandeStatus
				Where BandeTabela = 'OrdemCompra' and BandeTabelaId =  ".$iOrdemCompra;
		$result = $conn->query($sql);
		$rowBandeja = $result->fetch(PDO::FETCH_ASSOC);

		if ($count == 0){

			/* Insere na Bandeja para Aprovação do perfil ADMINISTRADOR ou CONTROLADORIA */
			$sIdentificacao = 'Ordem de Compra (Nº Ordem Compra: '.$rowOrdemCompra['OrComNumero'].' | Nº Processo: '.$rowOrdemCompra['OrComNumProcesso'].')';
		
			$sql = "INSERT INTO Bandeja (BandeIdentificacao, BandeData, BandeDescricao, BandeURL, BandeSolicitante, 
								BandeSolicitanteSetor, BandeTabela, BandeTabelaId, BandeStatus, BandeUsuarioAtualizador, BandeUnidade)
					VALUES (:sIdentificacao, :dData, :sDescricao, :sURL, :iSolicitante, :iSolicitanteSetor, :sTabela, 
							:iTabelaId, :iStatus, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sIdentificacao' => $sIdentificacao,
							':dData' => date("Y-m-d"),
							':sDescricao' => 'Liberar Ordem de Compra',
							':sURL' => '',
							':iSolicitante' => $_SESSION['UsuarId'],
							':iSolicitanteSetor' => null,
							':sTabela' => 'OrdemCompra',
							':iTabelaId' => $iOrdemCompra,
							':iStatus' => $rowSituacao['SituaId'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidade' => $_SESSION['UnidadeId']
							));

			$insertId = $conn->lastInsertId();

			foreach ($rowPerfil as $item){
			
				$sql = "INSERT INTO BandejaXPerfil (BnXPeBandeja, BnXPePerfil, BnXPeUnidade)
						VALUES (:iBandeja, :iPerfil, :iUnidade)";
				$result = $conn->prepare($sql);
						
				$result->execute(array(
								':iBandeja' => $insertId,
								':iPerfil' => $item['PerfiId'],
								':iUnidade' => $_SESSION['UnidadeId']
								));					
			}
			/* Fim Insere Bandeja */

		} else{

			$sql = "UPDATE Bandeja SET BandeData = :dData, BandeSolicitante = :iSolicitante, BandeStatus = :iStatus, 
					BandeUsuarioAtualizador = :iUsuarioAtualizador
					WHERE BandeUnidade = :iUnidade and BandeId = :iIdBandeja";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':dData' => date("Y-m-d"),
							':iSolicitante' => $_SESSION['UsuarId'],
							':iStatus' => $rowSituacao['SituaId'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidade' => $_SESSION['UnidadeId'],
							':iIdBandeja' => $rowBandeja['BandeId']														
							));
		}

		/* Deleta os registros gravados nas  tabela OrdemCompraXProduto  que possuem os produtos com quantidade ZERO. */ 
		$sql = "DELETE FROM OrdemCompraXProduto
		WHERE OCXPrOrdemCompra = :id AND OCXPrQuantidade = 0
		";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iOrdemCompra);
		$result->execute();

		/* Deleta os registros gravados nas  tabela OrdemCompraXServico  que possuem os serviços com quantidade ZERO. */ 
		$sql = "DELETE FROM OrdemCompraXServico
				WHERE OCXSrOrdemCompra = :id AND OCXSrQuantidade = 0
		";
		$result = $conn->prepare($sql);
		$result->bindParam(':id', $iOrdemCompra);
		$result->execute();

        $conn->commit();
        
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Ordem de Compra enviada para aprovação!!!";
		$_SESSION['msg']['tipo'] = "success";      		
	}

} catch(PDOException $e){

    $conn->rollback();
		
    $_SESSION['msg']['titulo'] = "Erro";
    $_SESSION['msg']['mensagem'] = "Erro ao enviar Ordem de Compra para aprovação!!!";
    $_SESSION['msg']['tipo'] = "error";	

    echo 'Error1: ' . $e->getMessage();
}

irpara("ordemcompra.php");

?>
