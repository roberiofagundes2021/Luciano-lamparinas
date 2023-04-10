<?php
	include_once("sessao.php"); 
	include('global_assets/php/conexao.php');

	$_SESSION['PaginaAtual'] = 'Dotação Orçamentária';

	// var_dump($_SESSION['inputTRIdDotacao']);
	// die;

	try{	
		
		$_UP['pasta'] = 'global_assets/anexos/dotacaoOrcamentaria/';

		// Renomeia o arquivo? (Se true, o arquivo será salvo como .csv e um nome único)
		$_UP['renomeia'] = false;

		// Primeiro verifica se deve trocar o nome do arquivo
		if ($_UP['renomeia'] == true) {
		
			// Cria um nome baseado no UNIX TIMESTAMP atual e com extensão .csv
			//$nome_final = time().".".$extensao;
			$nome_final = date('d-m-Y')."-".date('H-i-s')."-".$_FILES['inputArquivo']['name'];
		
		} else {
		
			// Mantém o nome original do arquivo
			$nome_final = $_FILES['inputArquivo']['name'];
		}
		
		//echo $_FILES['inputArquivo']['tmp_name']." <br>";
		//echo $_UP['pasta'] . $nome_final." <br>";
		
		// Depois verifica se é possível mover o arquivo para a pasta escolhida
		if (move_uploaded_file($_FILES['inputArquivo']['tmp_name'], $_UP['pasta'] . $nome_final)) {

			$conn->beginTransaction();
		
			/* Cria registro inserindo arquivo na tabela */
			$sql = "
				INSERT 
					INTO DotacaoOrcamentaria
						(DtOrcData, 
						DtOrcNome, 
						DtOrcArquivo, 
						DtOrcTermoReferencia, 
						DtOrcUsuarioAtualizador,
						DtOrcUnidade)
				VALUES (:iData,
						:sNome, 
						:iArquivo, 
						:iTermoReferencia, 
						:iUsuarioAtualizador, 
						:iUnidade)
			";
			$result = $conn->prepare($sql);
					
			$result->execute(
				array(
					':iData' 				=> gravaData($_POST['inputData']),
					':sNome' 				=> $_POST['inputNome'],
					':iArquivo' 			=> $nome_final,
					':iTermoReferencia' 	=> $_SESSION['inputTRIdDotacao'],
					':iUsuarioAtualizador' 	=> $_SESSION['UsuarId'],
					':iUnidade' 			=> $_SESSION['UnidadeId'],
				)
			);

			/* Muda o status da TR*/
			$sql = "
				SELECT SituaId
				FROM Situacao
				WHERE SituaChave = 'LIBERADOCONTABILIDADE'
			";
			$result = $conn->query($sql);
			$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);

			/* Capturando dados para Update */
			$bStatus = intval($rowSituacao['SituaId']);
			$iUsuario = intval($_SESSION['UsuarId']);
			$iTermoReferenciaId = intval($_SESSION['inputTRIdDotacao']);

			/* Atualizando dado no BD */
			$sql = "
				UPDATE TermoReferencia
					 SET TrRefStatus = :bStatus, 
					 	 TrRefUsuarioAtualizador = :iUsuario
				WHERE TrRefId = :iTermoReferenciaId";
			$result = $conn->prepare($sql);
			$result->bindParam(':bStatus', $bStatus);
			$result->bindParam(':iUsuario', $iUsuario);
			$result->bindParam(':iTermoReferenciaId', $iTermoReferenciaId);
			$result->execute();

			/* Atualiza status bandeja */
			$sql = "
				UPDATE Bandeja 
					 SET BandeStatus = :bStatus
				 WHERE BandeUnidade = :iUnidade 
					 AND BandeId in (Select BandeId FROM Bandeja WHERE BandeTabelaId = :iTermoReferenciaId and 
					 BandePerfil = 'CONTABILIDADE')
			";
			$result = $conn->prepare($sql);
			$result->bindParam(':bStatus', $bStatus);
			$result->bindParam(':iUnidade', $_SESSION['UnidadeId']);
			$result->bindParam(':iTermoReferenciaId', $iTermoReferenciaId);
			$result->execute();

			$sql = "INSERT INTO AuditTR ( AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento)
				VALUES (:iTRTermoReferencia, :iTRDataHora, :iTRUsuario, :iTRTela, :iTRDetalhamento)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
				':iTRTermoReferencia' => $iTermoReferenciaId ,
				':iTRDataHora' => date("Y-m-d H:i:s"),
				':iTRUsuario' => $_SESSION['UsuarId'],
				':iTRTela' =>'DOTAÇÃO ORÇAMENTÁRIA',
				':iTRDetalhamento' =>'INCLUSÃO DO REGISTRO'
			));


			$conn->commit();
			$_SESSION['msg']['titulo'] 		= "Sucesso";
			$_SESSION['msg']['mensagem'] 	= "Anexo incluído!!!";
			$_SESSION['msg']['tipo'] 		= "success";
		}

	} catch(PDOException $e) {
		
		$conn->rollback();
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir Anexo!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage().$e->getLine();exit;
	}

	irpara('trDotacao.php');
?>