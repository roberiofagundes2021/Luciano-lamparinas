<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Enviar para Aprovação - Contabilidade';

include('global_assets/php/conexao.php');

try{
	if(isset($_POST['inputOrdemCompraId'])){
	
		$conn->beginTransaction();
		
		$iOrdemCompra = $_POST['inputOrdemCompraId'];

		/* Atualiza o Status da Ordem de Compra para "Aguardando Liberação" */
		$sql = "SELECT SituaId
				FROM Situacao
				WHERE SituaChave = 'AGUARDANDOLIBERACAOCONTABILIDADE' ";
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
	            WHERE PerfiChave = 'CONTABILIDADE' and PerfiUnidade = " . $_SESSION['UnidadeId'];
		$result = $conn->query($sql);
		$rowPerfil = $result->fetchAll(PDO::FETCH_ASSOC);
		// var_dump($rowPerfil);

		$sql = "SELECT OrComNumero, OrComTipo, OrComDtEmissao
				FROM OrdemCompra
				WHERE OrComId = ".$iOrdemCompra;
		$result = $conn->query($sql);
		$rowOrdemCompra = $result->fetch(PDO::FETCH_ASSOC);

		/* Verifica se a Bandeja já tem um registro com BandeTabela: Ordemcompra, Perfil: CONTABILIDADE e e BandeTabelaId: IdOrdemcompraAtual, evitando duplicação */
		$sql = " SELECT COUNT(BandeId) as Count
				 FROM Bandeja
			     WHERE BandeTabela = 'OrdemCompra' AND BandePerfil = 'CONTABILIDADE' AND BandeTabelaId =  ".$iOrdemCompra;
		$result = $conn->query($sql);
		$rowBandeja = $result->fetch(PDO::FETCH_ASSOC);
		$count = $rowBandeja['Count'];

		$sql = "SELECT BandeId, SituaChave
				FROM Bandeja
				JOIN Situacao on SituaId = BandeStatus
				WHERE BandeTabela = 'OrdemCompra' AND BandePerfil = 'CONTABILIDADE' AND BandeTabelaId =  ".$iOrdemCompra;
		$result = $conn->query($sql);
		$rowBandeja = $result->fetch(PDO::FETCH_ASSOC);

		$tipo = '';
		if ($rowOrdemCompra['OrComTipo'] === "O") {
			$tipo = 'Ordem de Compra';
		} else if ($rowOrdemCompra['OrComTipo'] === "C") {
			$tipo = 'Carta Contrato';
		} 

		/* Insere na Bandeja para Aprovação do perfil CONTABILIDADE */
		$sIdentificacao = 'Ordem de Compra (Nº da Ordem de Compra: '.$rowOrdemCompra['OrComNumero'].' | Data: '.mostradata($rowOrdemCompra['OrComDtEmissao']).' | Tipo: '.$tipo.')';
		if ($count == 0){
		
			$sql = "INSERT INTO Bandeja (BandeIdentificacao, BandeData, BandeDescricao, BandeURL, BandeSolicitante,BandeSolicitanteSetor, 
						        BandeTabela, BandeTabelaId, BandeStatus, BandeUsuarioAtualizador, BandeUnidade,BandePerfil)
						VALUES (:sIdentificacao, :dData, :sDescricao, :sURL, :iSolicitante,:iSolicitanteSetor, 
								:sTabela, :iTabelaId, :iStatus, :iUsuarioAtualizador, :iUnidade, :sPerfil)
			";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
				':sIdentificacao' 		=> $sIdentificacao,
				':dData' 				=> date("Y-m-d"),
				':sDescricao' 			=> 'Liberar Ordem de Compra',
				':sURL' 				=> '',
				':iSolicitante' 		=> $_SESSION['UsuarId'],
				':iSolicitanteSetor' 	=> null,
				':sTabela' 				=> 'OrdemCompra',
				':iTabelaId' 			=> $iOrdemCompra,
				':iStatus' 				=> $rowSituacao['SituaId'],
				':iUsuarioAtualizador' 	=> $_SESSION['UsuarId'],
				':iUnidade' 			=> $_SESSION['UnidadeId'],
				':sPerfil' 				=> 'CONTABILIDADE',
			));

			$insertId = $conn->lastInsertId();

			foreach ($rowPerfil as $item){
				
				$sql = "INSERT INTO BandejaXPerfil (BnXPeBandeja, BnXPePerfil,BnXPeUnidade)
					         VALUES (:iBandeja, :iPerfil, :iUnidade)";
				$result = $conn->prepare($sql);
						
				$result->execute(array(
					':iBandeja' => $insertId,
					':iPerfil' 	=> $item['PerfiId'],
					':iUnidade' => $_SESSION['UnidadeId']
				));					
			}
			/* Fim Insere Bandeja */

		} else{

			$sql = "UPDATE Bandeja 
                    SET BandeData = :dData, BandeSolicitante = :iSolicitante, BandeStatus = :iStatus, 
                            BandeUsuarioAtualizador = :iUsuarioAtualizador,BandePerfil = 'CONTABILIDADE'
                    WHERE BandeUnidade = :iUnidade AND BandeId = :iIdBandeja
			";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
				':dData' 				=> date("Y-m-d"),
				':iSolicitante' 		=> $_SESSION['UsuarId'],
				':iStatus' 				=> $rowSituacao['SituaId'],
				':iUsuarioAtualizador' 	=> $_SESSION['UsuarId'],
				':iUnidade' 			=> $_SESSION['UnidadeId'],
				':iIdBandeja' 			=> $rowBandeja['BandeId']
			));

			/* Deleta os perfis da bandeja */ 
			$sql = "DELETE FROM BandejaXPerfil
				    WHERE BnXPeBandeja = :iIdBandeja
			";
			$result = $conn->prepare($sql);
			$result->execute(array(
				':iIdBandeja' => $rowBandeja['BandeId']
			));

			/* Cria os perfis da bandeja */ 
			foreach ($rowPerfil as $item){
				$sql = "INSERT INTO BandejaXPerfil (BnXPeBandeja, BnXPePerfil, BnXPeUnidade )
					    VALUES (:iBandeja, :iPerfil, :iUnidade)
				";
				$result = $conn->prepare($sql);
						
				$result->execute(array(
					':iBandeja' => $rowBandeja['BandeId'],
					':iPerfil' 	=> $item['PerfiId'],
					':iUnidade' => $_SESSION['UnidadeId'],
				));					
			}
		}

		$conn->commit();
        
		$_SESSION['msg']['titulo'] 		= "Sucesso";
		$_SESSION['msg']['mensagem'] 	= "Ordem de Compra enviada para realização do empenho!!!";
		$_SESSION['msg']['tipo'] 		= "success";      		
	}

} catch(PDOException $e){

    $conn->rollback();
		
    $_SESSION['msg']['titulo'] 		= "Erro";
    $_SESSION['msg']['mensagem'] 	= "Erro ao enviar Ordem de Compra para aprovação!!!";
    $_SESSION['msg']['tipo'] 		= "error";	

    echo 'Error1: ' . $e->getMessage();
}

irpara("ordemcompra.php");

?>
