<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Enviar para Aprovação - Contabilidade';

include('global_assets/php/conexao.php');

try{
	if(isset($_POST['inputTRId'])){
	
		$conn->beginTransaction();
		
		$iTrId = $_POST['inputTRId'];

		/* Atualiza o Status da TR para "Aguardando Liberação" */
		$sql = "
			SELECT SituaId
			FROM Situacao
			WHERE SituaChave = 'AGUARDANDOLIBERACAOCONTABILIDADE' 
		";
		$result = $conn->query($sql);
		$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "UPDATE TermoReferencia 
				SET TrRefStatus = :iStatus
				WHERE TrRefId = :iTrId ";
		$result = $conn->prepare($sql);

		$result->execute(array(
			':iStatus' => $rowSituacao['SituaId'],
			':iTrId' => $iTrId					
		));
		/* Fim Atualiza */

		$sql = "SELECT PerfiId
				FROM Perfil
				WHERE PerfiChave = 'CONTABILIDADE' and PerfiUnidade = " . $_SESSION['UnidadeId'];
		$result = $conn->query($sql);
		$rowPerfil = $result->fetchAll(PDO::FETCH_ASSOC);
		// var_dump($rowPerfil);

		$sql = "SELECT TrRefNumero, TrRefTipo, TrRefData
				FROM TermoReferencia
				WHERE TrRefId = " . $iTrId;
		$result = $conn->query($sql);
		$rowTermoReferencia = $result->fetch(PDO::FETCH_ASSOC);

		/* Verifica se a Bandeja já tem um registro com BandeTabela: TR, Perfil: CONTABILIDADE e e BandeTabelaId: IdTRAtual, evitando duplicação */
		$sql = "SELECT COUNT(BandeId) as Count
				FROM Bandeja
				WHERE BandeTabela = 'TermoReferencia' AND BandePerfil = 'CONTABILIDADE'
				AND BandeTabelaId =  " . $iTrId;
		$result = $conn->query($sql);
		$rowBandeja = $result->fetch(PDO::FETCH_ASSOC);
		$count = $rowBandeja['Count'];

		$sql = "SELECT BandeId, SituaChave
				FROM Bandeja
				JOIN Situacao on SituaId = BandeStatus
				WHERE BandeTabela = 'TermoReferencia' AND BandePerfil = 'CONTABILIDADE'
				AND BandeTabelaId =  " . $iTrId;
		$result = $conn->query($sql);
		$rowBandeja = $result->fetch(PDO::FETCH_ASSOC);

		$tipo = '';
		if ($rowTermoReferencia['TrRefTipo'] === "S") {
			$tipo = 'Serviços';
		} else if ($rowTermoReferencia['TrRefTipo'] === "P") {
			$tipo = 'Produtos';
		} else if ($rowTermoReferencia['TrRefTipo'] === "PS") {
			$tipo = 'Produtos e Serviços';
		} 

		/* Insere na Bandeja para Aprovação do perfil CONTABILIDADE */
		$sIdentificacao = 'Termo de Referência (Nº Termo: '.$rowTermoReferencia['TrRefNumero'].' | Data: '.$rowTermoReferencia['TrRefData'].' | Tipo: '.$tipo.')';

		if ($count == 0){
		
			$sql = "
				INSERT INTO 
					Bandeja (
						BandeIdentificacao, 
						BandeData, 
						BandeDescricao, 
						BandeURL, 
						BandeSolicitante, 
						BandeSolicitanteSetor, 
						BandeTabela, 
						BandeTabelaId, 
						BandeStatus, 
						BandeUsuarioAtualizador, 
						BandeUnidade,
						BandePerfil
					)
				VALUES (
					:sIdentificacao, 
					:dData, 
					:sDescricao, 
					:sURL, 
					:iSolicitante, 
					:iSolicitanteSetor, 
					:sTabela, 
					:iTabelaId, 
					:iStatus, 
					:iUsuarioAtualizador, 
					:iUnidade,
					:sPerfil
				)
			";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
				':sIdentificacao' 		=> $sIdentificacao,
				':dData' 				=> date("Y-m-d"),
				':sDescricao' 			=> 'Liberar Termo de Referência',
				':sURL' 				=> '',
				':iSolicitante' 		=> $_SESSION['UsuarId'],
				':iSolicitanteSetor' 	=> null,
				':sTabela' 				=> 'TermoReferencia',
				':iTabelaId' 			=> $iTrId,
				':iStatus' 				=> $rowSituacao['SituaId'],
				':iUsuarioAtualizador' 	=> $_SESSION['UsuarId'],
				':iUnidade' 			=> $_SESSION['UnidadeId'],
				':sPerfil' 				=> 'CONTABILIDADE',
			));

			$insertId = $conn->lastInsertId();

			foreach ($rowPerfil as $item){
				$sql = "
					INSERT INTO 
						BandejaXPerfil (
							BnXPeBandeja, 
							BnXPePerfil, 
							BnXPeUnidade
						)
					VALUES (
						:iBandeja, 
						:iPerfil, 
						:iUnidade
					)
				";
				$result = $conn->prepare($sql);
						
				$result->execute(array(
					':iBandeja' => $insertId,
					':iPerfil' 	=> $item['PerfiId'],
					':iUnidade' => $_SESSION['UnidadeId']
				));					
			}
			/* Fim Insere Bandeja */

		} else{

			$sql = "
				UPDATE Bandeja 
				  SET BandeData = :dData, 
					  BandeSolicitante = :iSolicitante, 
					  BandeStatus = :iStatus, 
					  BandeUsuarioAtualizador = :iUsuarioAtualizador,
					  BandePerfil = 'CONTABILIDADE'
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
			$sql = "
				DELETE FROM BandejaXPerfil
				WHERE BnXPeBandeja = :iIdBandeja
			";
			$result = $conn->prepare($sql);
			$result->execute(array(
				':iIdBandeja' => $rowBandeja['BandeId']
			));

			/* Cria os perfis da bandeja */ 
			foreach ($rowPerfil as $item){
				$sql = "
					INSERT INTO 
						BandejaXPerfil (
							BnXPeBandeja,
							BnXPePerfil,
							BnXPeUnidade
						)
					VALUES (
						:iBandeja, 
						:iPerfil, 
						:iUnidade
					)
				";
				$result = $conn->prepare($sql);
						
				$result->execute(array(
					':iBandeja' => $rowBandeja['BandeId'],
					':iPerfil' 	=> $item['PerfiId'],
					':iUnidade' => $_SESSION['UnidadeId'],
				));					
			}
		}

		$sql = "INSERT INTO AuditTR ( AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento)
					VALUES (:iTRTermoReferencia, :iTRDataHora, :iTRUsuario, :iTRTela, :iTRDetalhamento)";
				$result = $conn->prepare($sql);
						
				$result->execute(array(
					':iTRTermoReferencia' => $iTrId,
					':iTRDataHora' => date("Y-m-d H:i:s"),
					':iTRUsuario' => $_SESSION['UsuarId'],
					':iTRTela' =>'ENVIAR PARA CONTABILIDADE',
					':iTRDetalhamento' =>' ENVIADO PARA CONTABILIDADE'
			));


		$conn->commit();
        
		$_SESSION['msg']['titulo'] 		= "Sucesso";
		$_SESSION['msg']['mensagem'] 	= "Termo de Referência enviado para dotação!!!";
		$_SESSION['msg']['tipo'] 		= "success";      		
	}

} catch(PDOException $e){

    $conn->rollback();
		
    $_SESSION['msg']['titulo'] 		= "Erro";
    $_SESSION['msg']['mensagem'] 	= "Erro ao enviar Termo de Referência para aprovação!!!";
    $_SESSION['msg']['tipo'] 		= "error";	

    echo 'Error1: ' . $e->getMessage();
}

irpara("tr.php");

?>
