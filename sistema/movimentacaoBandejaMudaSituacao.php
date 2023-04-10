<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if (isset($_POST['inputMovimentacaoId'])) {

	$iMovimentacao = $_POST['inputMovimentacaoId'];

	try {

		$conn->beginTransaction();

		$sql = "SELECT SituaId
							FROM Situacao	
						 WHERE SituaChave = '" . $_POST['inputMovimentacaoStatus'] . "'";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);

		if ($_POST['inputMovimentacaoStatus'] == 'NAOLIBERADO') {
			$motivo = $_POST['inputMotivo'];
			$msg = "Movimentação não liberada!";
		} else {
			$motivo = NULL;
			$msg = "Movimentação liberada!";
		}

		$sql = "SELECT ParamEmpresaPublica
				FROM Parametro
				WHERE ParamEmpresa = " . $_SESSION['EmpreId'];
		$result = $conn->query($sql);
		$rowParametro = $result->fetch(PDO::FETCH_ASSOC);

		$empresaPrivada = ($rowParametro['ParamEmpresaPublica']  != 1) ? true : false;

		if($msg == "Movimentação liberada!" && $empresaPrivada) {
			/* Status do Contas Apagar*/
			$sql = "SELECT SituaId
					FROM Situacao
					WHERE SituaChave = 'APAGAR' ";
			$result = $conn->query($sql);
			$rowSituaChave = $result->fetch(PDO::FETCH_ASSOC);
			/* Fim Atualiza */

			$dataVencimento = $_POST['inputDataVencimento'];

			$sql = "SELECT  MovimId, MovimNumRecibo, MovimTipo, MovimData, MovimFinalidade, MovimOrigemLocal, MovimOrigemSetor, MovimDestinoLocal,
							MovimDestinoSetor, MovimDestinoManual, MovimObservacao, MovimOrdemCompra, MovimNotaFiscal, MovimDataEmissao, MovimNumSerie,
							MovimValorTotal, MovimChaveAcesso, MovimFornecedor, MovimMotivo, MovimSituacao, MovimUnidade, MovimUsuarioAtualizador
					FROM Movimentacao
					WHERE MovimId = ".$iMovimentacao;
			$result = $conn->query($sql);
			$rowMovimentacao = $result->fetch(PDO::FETCH_ASSOC);

			$sql = "UPDATE Movimentacao 
					SET MovimSituacao = :bStatus, MovimUsuarioAtualizador = :iUsuario
					WHERE MovimId = :iMovimentacao";
			$result = $conn->prepare($sql);
	
			$result->bindParam(':bStatus', $row['SituaId']);
			$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
			$result->bindParam(':iMovimentacao', $iMovimentacao);
			$result->execute();
	
			$sql = "UPDATE Bandeja 
					SET BandeStatus = :bStatus, BandeMotivo = :sMotivo, BandeUsuarioAtualizador = :iUsuario
					WHERE BandeId = :iBandeja";
			$result = $conn->prepare($sql);

			$result->bindParam(':bStatus', $row['SituaId']);
			$result->bindParam(':sMotivo', $motivo);
			$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
			$result->bindParam(':iBandeja', $_POST['inputBandejaId']);
			$result->execute();

			/* Insere na Tabela Contas a Pagar */
			
			$sql = "INSERT INTO ContasAPagar ( CnAPaMovimentacao, CnAPaPlanoContas, CnAPaFornecedor, CnAPaContaBanco, CnAPaFormaPagamento, CnAPaNumDocumento,
												CnAPaNotaFiscal, CnAPaDtEmissao, CnAPaOrdemCompra, CnAPaDescricao, CnAPaDtVencimento, CnAPaValorAPagar,
												CnAPaDtPagamento, CnAPaValorPago, CnAPaObservacao, CnAPaTipoJuros, CnAPaJuros, 
												CnAPaTipoDesconto, CnAPaDesconto, CnAPaStatus, CnAPaUsuarioAtualizador, CnAPaUnidade)
					VALUES ( :iMovimentacao, :iPlanoContas, :iFornecedor, :iContaBanco, :iFormaPagamento,:sNumDocumento, :sNotaFiscal, :dateDtEmissao, :iOrdemCompra,
							:sDescricao, :dateDtVencimento, :fValorAPagar, :dateDtPagamento, :fValorPago, :sObservacao, :sTipoJuros, :fJuros, 
							:sTipoDesconto, :fDesconto, :iStatus, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
				':iMovimentacao' => $iMovimentacao,
				':iPlanoContas' => null,
				':iFornecedor' => $rowMovimentacao['MovimFornecedor'],
				':iContaBanco' => null,
				':iFormaPagamento' => null,
				':sNumDocumento' => $rowMovimentacao['MovimNumRecibo'],
				':sNotaFiscal' => $rowMovimentacao['MovimNotaFiscal'],
				':dateDtEmissao' => date('Y-m-d') , //Se for Data da Liquidação ficará assim: $rowMovimentacao['MovimDataEmissao']
				':iOrdemCompra' => $rowMovimentacao['MovimOrdemCompra'],
				':sDescricao' => 'Pagamento da NF '.$rowMovimentacao['MovimNotaFiscal'], // Ver com Valma
				':dateDtVencimento' => $dataVencimento,
				':fValorAPagar' => $rowMovimentacao['MovimValorTotal'],
				':dateDtPagamento' => null,
				':fValorPago' => null,
				':sObservacao' => null,
				':sTipoJuros' => null,
				':fJuros' =>  null,
				':sTipoDesconto' =>  null,
				':fDesconto' => null,
				':iStatus' => $rowSituaChave['SituaId'],
				':iUsuarioAtualizador' => $_SESSION['UsuarId'],
				':iUnidade' => $_SESSION['UnidadeId']
			));

			//$newContasAPagarId = $conn->lastInsertId();

			// insere todos os Centro de custos Selecionados em contas a pagar
			/*
			$sqlContasAPagar = "INSERT INTO ContasAPagarXCentroCusto
			(CAPXCContasAPagar, CAPXCCentroCusto, CAPXCUsuarioAtualizador, CAPXCValor,
			CAPXCUnidade)
			VALUES ";   
			for($x=0; $x < $registros; $x++){
				$keyNome = 'inputCentroNome-'.$x;
				$keyId = 'inputIdCentro-'.$x;

				$valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
				$valor = str_replace(',', '.', $valor);

				if(isset($_POST[$keyNome])){
					$sqlContasAPagar .= "('$newContasAPagarId', ".$_POST[$keyId].", '$UsuarId', ".$valor.", '$UnidadeId')";

					if($x < ($registros-1)){
						$sqlContasAPagar .= ',';
					}
				}
			}
			
			$conn->query($sqlContasAPagar);*/
		}else {
			$sql = "UPDATE Movimentacao 
								  SET MovimSituacao = :bStatus, MovimUsuarioAtualizador = :iUsuario
							 WHERE MovimId = :iMovimentacao";
			$result = $conn->prepare($sql);
	
			$result->bindParam(':bStatus', $row['SituaId']);
			$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
			$result->bindParam(':iMovimentacao', $iMovimentacao);
			$result->execute();
	
	
	
			$sql = "UPDATE Bandeja 
								 SET BandeStatus = :bStatus, BandeMotivo = :sMotivo, BandeUsuarioAtualizador = :iUsuario
							 WHERE BandeId = :iBandeja";
			$result = $conn->prepare($sql);
			
			$result->bindParam(':bStatus', $row['SituaId']);
			$result->bindParam(':sMotivo', $motivo);
			$result->bindParam(':iUsuario', $_SESSION['UsuarId']);
			$result->bindParam(':iBandeja', $_POST['inputBandejaId']);
			$result->execute();
		}


		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = $msg;
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro na liberação da movimentação!!!";
		$_SESSION['msg']['tipo'] = "error";

		echo 'Error: ' . $e->getMessage();
		exit;
	}
}

irpara("index.php");
