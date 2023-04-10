<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Enviar para Aprovação - Centro Administrativo';

include('global_assets/php/conexao.php');

if(isset($_POST['inputTRId'])){
	
	$iTrId = $_POST['inputTRId'];

	$sql = "SELECT TRXEqPresidente, TRXEqUsuario, TrRefNumero, TrRefTipo, TrRefData, TrRefStatus
  			FROM TRXEquipe
			JOIN TermoReferencia on TrRefId = TRXEqTermoReferencia
 	 		WHERE TRXEqUnidade = ".$_SESSION['UnidadeId']." AND TRXEqTermoReferencia = ".$iTrId."
   			AND TRXEqPresidente > 0	";
	$result = $conn->query($sql);
	$rowTRPresidente = $result->fetch(PDO::FETCH_ASSOC);

	if (intval($rowTRPresidente) <= 0) {
		$_SESSION['msg']['titulo'] 		= "Atenção";
		$_SESSION['msg']['mensagem'] 	= "Só é permitido enviar após criar a comissão!!!";
		$_SESSION['msg']['tipo'] 		= "error";

	} else {

		try{
			$conn->beginTransaction();

			//Recupera o ID da Situação 'AGUARDANDOFINALIZACAO'
			$sql = "SELECT SituaId
					FROM Situacao
					WHERE SituaChave = 'AGUARDANDOFINALIZACAO' ";
			$result = $conn->query($sql);
			$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);

			/* Verifica se a Bandeja já tem um registro com BandeTabela: TermoReferencia, Perfil: COMISSAO e e BandeTabelaId: $iTrId, evitando duplicação */
			$sql = "SELECT COUNT(BandeId) as Count
					FROM Bandeja
					WHERE BandeTabela = 'TermoReferencia' AND BandePerfil = 'COMISSAO'
					AND BandeTabelaId =  " . $iTrId;
			$result = $conn->query($sql);
			$rowBandeja = $result->fetch(PDO::FETCH_ASSOC);
			$count = $rowBandeja['Count'];

			$sql = "SELECT BandeId, SituaChave
					FROM Bandeja
					JOIN Situacao on SituaId = BandeStatus
					WHERE BandeTabela = 'TermoReferencia' AND BandePerfil = 'COMISSAO'
					AND BandeTabelaId =  ".$iTrId;
			$result = $conn->query($sql);
			$rowBandeja = $result->fetch(PDO::FETCH_ASSOC);

			$tipo = '';
			if ($rowTRPresidente['TrRefTipo'] === "S") {
				$tipo = 'Serviços';
			} else if ($rowTRPresidente['TrRefTipo'] === "P") {
				$tipo = 'Produtos';
			} else if ($rowTRPresidente['TrRefTipo'] === "PS") {
				$tipo = 'Produtos e Serviços';
			}

			/* Insere na Bandeja para Aprovação do presidente da Comissão */
			$sIdentificacao = 'Termo de Referência (Nº Termo: '.$rowTRPresidente['TrRefNumero'].' | Data: '.mostradata($rowTRPresidente['TrRefData']).' | Tipo: '.$tipo.')';

			if ($count == 0){
			
				$sql = "
					INSERT INTO 
						Bandeja (BandeIdentificacao, BandeData, BandeDescricao, BandeURL, BandeSolicitante, 
						BandeSolicitanteSetor, BandeTabela, BandeTabelaId, BandeStatus, BandeUsuarioAtualizador, 
						BandeUnidade, BandePerfil, BandeUsuario)
					VALUES (
						:sIdentificacao, :dData, :sDescricao, :sURL, :iSolicitante, :iSolicitanteSetor, :sTabela, 
						:iTabelaId, :iStatus, :iUsuarioAtualizador, :iUnidade, :sPerfil, :iPresidente)
				";
				$result = $conn->prepare($sql);
						
				$result->execute(array(
					':sIdentificacao' 			=> $sIdentificacao,
					':dData' 					=> date("Y-m-d"),
					':sDescricao' 				=> 'Dar como concluído o Termo de Referência (Só clique em Liberar quando o TR estiver totalmente concluído)',
					':sURL' 					=> '',
					':iSolicitante' 			=> $_SESSION['UsuarId'],
					':iSolicitanteSetor' 		=> null,
					':sTabela' 					=> 'TermoReferencia',
					':iTabelaId' 				=> $iTrId,
					':iStatus' 					=> $rowSituacao['SituaId'],
					':iUsuarioAtualizador' 		=> $_SESSION['UsuarId'],
					':iUnidade' 				=> $_SESSION['UnidadeId'],
					':sPerfil' 					=> 'COMISSAO',
					':iPresidente' 				=> $rowTRPresidente['TRXEqUsuario']
				));
				/* Fim Insere Bandeja */

			} else{
				$sql = "
					UPDATE Bandeja SET BandeIdentificacao = :sIdentificacao, BandeData = :dData, BandeDescricao = :sDescricao,
									   BandeURL = :sURL, BandeSolicitante = :iSolicitante, BandeStatus = :iStatus,
									   BandeUsuarioAtualizador = :iUsuarioAtualizador, 
									   BandePerfil = :sPerfil, BandeUsuario = :iPresidente
					WHERE BandeUnidade = :iUnidade AND BandeId = :iIdBandeja";
				$result = $conn->prepare($sql);						
				$result->execute(array(
					':sIdentificacao' 		=> $sIdentificacao,
					':dData' 				=> date("Y-m-d"),
					':sDescricao' 			=> 'Dar como concluído o Termo de Referência (Só clique em Liberar quando o TR estiver totalmente concluído)',
					':sURL' 				=> '',
					':iSolicitante' 		=> $_SESSION['UsuarId'],
					':iStatus' 				=> $rowSituacao['SituaId'],
					':iUsuarioAtualizador' 	=> $_SESSION['UsuarId'],
					':sPerfil' 				=> 'COMISSAO',
					':iPresidente' 			=> $rowTRPresidente['TRXEqUsuario'],
					':iUnidade' 			=> $_SESSION['UnidadeId'],
					':iIdBandeja' 			=> $rowBandeja['BandeId']														
				));
			}

			//Recupera a chave da situação do TR
			$sql = "SELECT SituaChave
					FROM Situacao
					WHERE SituaId = ".$_POST['inputTRStatus'];
			$result = $conn->query($sql);
			$rowChave = $result->fetch(PDO::FETCH_ASSOC);

			//Se já foi liberado pela Contabilidade o Status do TR deve ser alterado para "Aguardando Finalização - Comissão"
			if ($rowChave['SituaChave'] == 'LIBERADOCONTABILIDADE'){
				
				$sql = "UPDATE TermoReferencia SET TrRefStatus = :iSituacao, 
						TrRefUsuarioAtualizador = :iUsuarioAtualizador
						WHERE TrRefUnidade = :iUnidade AND TrRefId = :iTR";
				$result = $conn->prepare($sql);						
				$result->execute(array(
					':iSituacao' 			=> $rowSituacao['SituaId'],
					':iUsuarioAtualizador' 	=> $_SESSION['UsuarId'],
					':iUnidade' 			=> $_SESSION['UnidadeId'],
					':iTR' 					=> $iTrId
				));
			}

			$sql = "INSERT INTO AuditTR ( AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento)
					VALUES (:iTRTermoReferencia, :iTRDataHora, :iTRUsuario, :iTRTela, :iTRDetalhamento)";
				$result = $conn->prepare($sql);
						
				$result->execute(array(
					':iTRTermoReferencia' => $iTrId,
					':iTRDataHora' => date("Y-m-d H:i:s"),
					':iTRUsuario' => $_SESSION['UsuarId'],
					':iTRTela' =>'ENVIAR PARA COMISSÃO',
					':iTRDetalhamento' =>' ENVIADO PARA COMISSÃO'
			));

			$conn->commit();
					
			$_SESSION['msg']['titulo'] 		= "Sucesso";
			$_SESSION['msg']['mensagem'] 	= "Termo de Referência enviado para comissão!!!";
			$_SESSION['msg']['tipo'] 		= "success";     

		} catch(PDOException $e){

			$conn->rollback();
			
			$_SESSION['msg']['titulo'] 		= "Erro";
			$_SESSION['msg']['mensagem'] 	= "Erro ao enviar Termo de Referência para comissão!!!";
			$_SESSION['msg']['tipo'] 		= "error";	

			echo 'Error Message: ' . $e->getMessage().'| Line:  '.$e->getLine();die;
		}
	}
}

irpara("tr.php");

?>
