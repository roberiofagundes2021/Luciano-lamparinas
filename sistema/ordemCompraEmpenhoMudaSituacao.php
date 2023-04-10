<?php
	include_once("sessao.php"); 
	include('global_assets/php/conexao.php');

	$_SESSION['PaginaAtual'] = 'Ordem de Compra Empenho';

	try{	
		
		$conn->beginTransaction();

		/* Muda o status da TR*/
		$sql = "SELECT SituaId
	              FROM Situacao
			     WHERE SituaChave = 'LIBERADOCONTABILIDADE' ";
		$result = $conn->query($sql);
		$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);

		/* Capturando dados para Update */
		$bStatus = intval($rowSituacao['SituaId']);
		$iUsuario = intval($_SESSION['UsuarId']);
		$iOrdemCompraIdEmpenho = intval($_SESSION['OrdemCompraIdEmpenho']);

		/* Atualizando dado no BD */
		$sql = " UPDATE OrdemCompra SET OrComSituacao = :bStatus, OrComUsuarioAtualizador = :iUsuario
			      WHERE OrComId = :iOrdemCompraIdEmpenho";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $bStatus);
		$result->bindParam(':iUsuario', $iUsuario);
		$result->bindParam(':iOrdemCompraIdEmpenho', $iOrdemCompraIdEmpenho);
		$result->execute();

		/* Atualiza status bandeja */
		$sql = " UPDATE Bandeja SET BandeStatus = :bStatus
				  WHERE BandeUnidade = :iUnidade AND BandeId in (Select BandeId FROM Bandeja WHERE
				        BandeTabelaId = :iOrdemCompraIdEmpenho and BandePerfil = 'CONTABILIDADE')";
		$result = $conn->prepare($sql);
		$result->bindParam(':bStatus', $bStatus);
		$result->bindParam(':iUnidade', $_SESSION['UnidadeId']);
		$result->bindParam(':iOrdemCompraIdEmpenho', $iOrdemCompraIdEmpenho);
		$result->execute();

		$conn->commit();

		$_SESSION['msg']['titulo'] 		= "Sucesso";
		$_SESSION['msg']['mensagem'] 	= "Empenho Finalizado!!!";
		$_SESSION['msg']['tipo'] 		= "success";
	

	} catch(PDOException $e) {
		
		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao Finalizar Empenho!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage().$e->getLine();exit;
	}

	irpara('ordemcompra.php');
?>