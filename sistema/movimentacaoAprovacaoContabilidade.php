<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Enviar para Aprovação - Contabilidade';

include('global_assets/php/conexao.php');

try{
	if(isset($_POST['inputMovimentacaoId'])){
	
		$conn->beginTransaction();
		
		$iMovimentacao = $_POST['inputMovimentacaoId'];

		/* Atualiza o Status da Movimentação para "Aguardando Liberação" */
		$sql = "SELECT SituaId
				FROM Situacao
				WHERE SituaChave = 'AGUARDANDOLIBERACAOCONTABILIDADE' ";
		$result = $conn->query($sql);
		$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "UPDATE Movimentacao SET MovimSituacao = :iStatus
				WHERE MovimId = :iMovimentacao";
		$result = $conn->prepare($sql);

		$result->execute(array(
			':iStatus' => $rowSituacao['SituaId'],
			':iMovimentacao' => $iMovimentacao					
		));
		/* Fim Atualiza */

		$sql = "SELECT PerfiId
	            FROM Perfil
	            WHERE PerfiChave = 'CONTABILIDADE' and PerfiUnidade = " . $_SESSION['UnidadeId'];
		$result = $conn->query($sql);
		$rowPerfil = $result->fetchAll(PDO::FETCH_ASSOC);
		// var_dump($rowPerfil);

		$sql = "SELECT MovimNotaFiscal, MovimTipo, MovimData
				FROM Movimentacao
				WHERE MovimId = ".$iMovimentacao;
		$result = $conn->query($sql);
		$rowMovimentacao = $result->fetch(PDO::FETCH_ASSOC);

		/* Verifica se a Bandeja já tem um registro com BandeTabela: Movimentacao, Perfil: CONTABILIDADE e e BandeTabelaId: IdMovimentacaoAtual, evitando duplicação */
		$sql = " SELECT COUNT(BandeId) as Count
				 FROM Bandeja
			     WHERE BandeTabela = 'Movimentacao' AND BandePerfil = 'CONTABILIDADE' AND BandeTabelaId =  ".$iMovimentacao;
		$result = $conn->query($sql);
		$rowBandeja = $result->fetch(PDO::FETCH_ASSOC);
		$count = $rowBandeja['Count'];

		$sql = "SELECT BandeId, SituaChave
				FROM Bandeja
				JOIN Situacao on SituaId = BandeStatus
				WHERE BandeTabela = 'Movimentacao' AND BandePerfil = 'CONTABILIDADE' AND BandeTabelaId =  ".$iMovimentacao;
		$result = $conn->query($sql);
		$rowBandeja = $result->fetch(PDO::FETCH_ASSOC);

		$tipo = '';
		if ($rowMovimentacao['MovimTipo'] === "E") {
			$tipo = 'Entrada';
		} else if ($rowMovimentacao['MovimTipo'] === "S") {
			$tipo = 'Saida';
		} else if ($rowMovimentacao['MovimTipo'] === "T") {
			$tipo = 'Transferencia';
		} 

		/* Insere na Bandeja para Aprovação do perfil CONTABILIDADE */
		$sIdentificacao = 'Movimentação (Nº da Nota Fiscal: '.$rowMovimentacao['MovimNotaFiscal'].' | Data: '.mostradata($rowMovimentacao['MovimData']).' | Tipo: '.$tipo.')';
		                                                                                            
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
				':sDescricao' 			=> 'Liberar Movimentação',
				':sURL' 				=> '',
				':iSolicitante' 		=> $_SESSION['UsuarId'],
				':iSolicitanteSetor' 	=> null,
				':sTabela' 				=> 'Movimentacao',
				':iTabelaId' 			=> $iMovimentacao,
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
		$_SESSION['msg']['mensagem'] 	= "Movimentação enviada para liquidação!!!";
		$_SESSION['msg']['tipo'] 		= "success";      		
	}

} catch(PDOException $e){

    $conn->rollback();
		
    $_SESSION['msg']['titulo'] 		= "Erro";
    $_SESSION['msg']['mensagem'] 	= "Erro ao enviar Movimentação para aprovação!!!";
    $_SESSION['msg']['tipo'] 		= "error";	

    echo 'Error1: ' . $e->getMessage();
}

irpara("movimentacao.php");

?>
