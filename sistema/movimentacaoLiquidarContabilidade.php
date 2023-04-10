<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Enviar para Contas a Pagar ';

include('global_assets/php/conexao.php');

try{
	if(isset($_POST['inputMovimentacaoId'])){
	
		$conn->beginTransaction();
		
		$iMovimentacao = $_POST['inputMovimentacaoId'];
        $data = $_POST['inputPeriodoDe'];
        $UsuarId = $_SESSION['UsuarId'];
        $UnidadeId = $_SESSION['UnidadeId'];
        $planoCusto = $_POST['cmbPlanoContaId'];

        $registros = intval($_POST['totalRegistros']);

        /* Insere na Tabela Movimentacao Liquidacao */
        
        $sqlMovimentacao = "INSERT INTO MovimentacaoLiquidacao(MvLiqMovimentacao, MvLiqData, MvLiqUsuario,
        MvLiqUnidade, MvLiqPlanoConta)
        VALUES('$iMovimentacao', '$data', '$UsuarId', '$UnidadeId', '$planoCusto')";

        $conn->query($sqlMovimentacao);
        $newMovimentacaoId = $conn->lastInsertId();


        // insere todos os Centro de custos Selecionados em movimentação

        $sqlMovimentacaoXCentro = "INSERT INTO MovimentacaoLiquidacaoXCentroCusto
        (MvLiqXCnCusMovimentacaoLiquidacao, MvLiqXCnCusCentroCusto, MvLiqXCnCusUsuarioAtualizador, MvLiqXCnCusValor,
        MvLiqXCnCusUnidade)
        VALUES ";    

        for($x=0; $x < $registros; $x++){
            $keyNome = 'inputCentroNome-'.$x;
            $keyId = 'inputIdCentro-'.$x;
            $valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
            $valor = str_replace(',', '.', $valor);

            if(isset($_POST[$keyNome])){
                $sqlMovimentacaoXCentro .= "($newMovimentacaoId, $_POST[$keyId], $UsuarId, $valor, $UnidadeId),";
            }
        }
        $sqlMovimentacaoXCentro = substr($sqlMovimentacaoXCentro,0,-1);
        $conn->query($sqlMovimentacaoXCentro);
        $sqlMovimentacaoXCentro = $conn->lastInsertId();
        

		/* Atualiza o Status da Movimentação para "Liberado Contabilidade" */
		$sql = "SELECT SituaId
				FROM Situacao
				WHERE SituaChave = 'LIBERADOCONTABILIDADE' ";
		$result = $conn->query($sql);
		$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);

		$sql = "UPDATE Movimentacao SET MovimSituacao = :iStatus
				WHERE MovimId = :iMovimentacao";
		$result = $conn->prepare($sql);

		$result->execute(array(
			':iStatus' => $rowSituacao['SituaId'],
			':iMovimentacao' => $iMovimentacao					
		));

        /* Capturando dados para Update */
		$iStatus = intval($rowSituacao['SituaId']);
        
        /* Atualiza status bandeja */
	 	$sql = " UPDATE Bandeja SET BandeStatus = :iStatus
                 WHERE BandeUnidade = :iUnidade AND BandeId in (Select BandeId FROM Bandeja 
                 WHERE BandeTabelaId = :iMovimentacao and BandePerfil = 'CONTABILIDADE')";
        $result = $conn->prepare($sql);
        $result->bindParam(':iStatus', $iStatus);
        $result->bindParam(':iUnidade', $_SESSION['UnidadeId']);
        $result->bindParam(':iMovimentacao', $iMovimentacao);
        $result->execute();

        /* Status do Contas Apagar*/
        $sql = "SELECT SituaId
				FROM Situacao
				WHERE SituaChave = 'APAGAR' ";
		$result = $conn->query($sql);
		$rowSituaChave = $result->fetch(PDO::FETCH_ASSOC);
		/* Fim Atualiza */

		$sql = "SELECT  MovimId, MovimNumRecibo, MovimTipo, MovimData, MovimFinalidade, MovimOrigemLocal, MovimOrigemSetor, MovimDestinoLocal,
			            MovimDestinoSetor, MovimDestinoManual, MovimObservacao, MovimOrdemCompra, MovimNotaFiscal, MovimDataEmissao, MovimNumSerie,
					    MovimValorTotal, MovimChaveAcesso, MovimFornecedor, MovimMotivo, MovimSituacao, MovimUnidade, MovimUsuarioAtualizador
				FROM Movimentacao
				WHERE MovimId = ".$iMovimentacao;
		$result = $conn->query($sql);
		$rowMovimentacao = $result->fetch(PDO::FETCH_ASSOC);


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
            ':iPlanoContas' => $planoCusto,
            ':iFornecedor' => $rowMovimentacao['MovimFornecedor'],
            ':iContaBanco' => null,
            ':iFormaPagamento' => null,
            ':sNumDocumento' => $rowMovimentacao['MovimNumRecibo'],
            ':sNotaFiscal' => $rowMovimentacao['MovimNotaFiscal'],
            ':dateDtEmissao' => date('Y-m-d') , //Se for Data da Liquidação ficará assim: $rowMovimentacao['MovimDataEmissao']
            ':iOrdemCompra' => $rowMovimentacao['MovimOrdemCompra'],
            ':sDescricao' => 'Pagamento da NF '.$rowMovimentacao['MovimNotaFiscal'], // Ver com Valma
            ':dateDtVencimento' => $data,
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

        $newContasAPagarId = $conn->lastInsertId();

        // insere todos os Centro de custos Selecionados em contas a pagar

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
        $conn->query($sqlContasAPagar);
        
        /* Fim Insere ContasAPagar */		

		$conn->commit();
        
		$_SESSION['msg']['titulo'] 		= "Sucesso";
		$_SESSION['msg']['mensagem'] 	= "Movimentação Liquidada!!!";
		$_SESSION['msg']['tipo'] 		= "success";      		
	}

} catch(PDOException $e){

    $conn->rollback();
		
    $_SESSION['msg']['titulo'] 		= "Erro";
    $_SESSION['msg']['mensagem'] 	= "Erro ao Liquidar a Movimentação!!!";
    $_SESSION['msg']['tipo'] 		= "error";	

    echo 'Error1: ' . $e->getMessage();die;
}

irpara("movimentacao.php");

?>
