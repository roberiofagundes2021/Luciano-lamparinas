<?php
include_once("sessao.php");
include('global_assets/php/conexao.php');

$resposta = $_POST['idCaixaAbertura'];

//gravaData($_POST['inputData']);
$idCaixaAbertura = $_POST['idCaixaAbertura'];
$dataAtual = date('Y-m-d');
$dataHoraAtual = date('Y-m-d H:i:s');
$destinoContaFinanceiraId = $_POST['inputDestinoContaFinanceiraId'];
$totalRecebido = $_POST['inputTotalRecebido'];
$totalPago = $_POST['inputTotalPago'];
$valorTransferido = $_POST['inputValorTransferir'] != '' ? gravaValor($_POST['inputValorTransferir']) : 0;
//$saldoFinal = gravaValor($_POST['inputSaldoCaixa']);
$saldoFinal = 0; //No momento está deixando saldo final do caixa zerado porque está transferindo todo o valor
$justificativa = $_POST['inputJustificativa'] != '' ? $_POST['inputJustificativa'] : null;
$nomeCaixa = $_POST['caixaNome'];

try{
    $conn->beginTransaction();
    
    $sql = "SELECT SituaId
            FROM Situacao
            WHERE SituaChave = 'FECHADO'";
    $result = $conn->query($sql);
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $iStatus = $row['SituaId'];	
    
    $sql = "UPDATE CaixaAbertura SET CxAbeDataHoraFechamento = :sDataHoraFechamento, CxAbeContaTransferencia = :iDestinoTransfererencia, 
                                     CxAbeValorTransferido = :fValorTransferido, CxAbeSaldoFinal = :fSaldoFinal, CxAbeJustificativa = :sJustificativa, 
                                     CxAbeStatus = :iStatus, CxAbeUnidade = :iUnidade
            WHERE CxAbeId = " . $idCaixaAbertura . "";
    $result = $conn->prepare($sql);			
    
    $result->execute(array(
        ':sDataHoraFechamento' => $dataHoraAtual,
        ':iDestinoTransfererencia' => $destinoContaFinanceiraId,
        ':fValorTransferido' => $valorTransferido,
        ':fSaldoFinal' => $saldoFinal,
        ':sJustificativa' => $justificativa,
        ':iStatus' => $iStatus,
        ':iUnidade' => $_SESSION['UnidadeId']
    ));
    $sql_movimentacaoRecebimento = "SELECT AtendNumRegistro, AtendCliente, ClienNome, CxRecFormaPagamento, CxRecValor, CxRecValorTotal, CxRecDataHora, CxRecDesconto
                                    FROM CaixaRecebimento
                                    JOIN CaixaAbertura on CxAbeId = CxRecCaixaAbertura
                                    JOIN FormaPagamento on FrPagId = CxRecFormaPagamento
                                    JOIN Atendimento on AtendId = CxRecAtendimento
                                    JOIN Cliente on ClienId = AtendCliente
                                    WHERE CxAbeOperador = $_SESSION[UsuarId] and CxRecUnidade = $_SESSION[UnidadeId] and CxRecCaixaAbertura = $idCaixaAbertura";
    $resultMovimentacaoRecebimento  = $conn->query($sql_movimentacaoRecebimento);

    if($rowMovimentacaoRecebimento = $resultMovimentacaoRecebimento->fetchAll(PDO::FETCH_ASSOC)) {
        $descricaoAgrupamentoRecebimento = 'Recebimento do '.$nomeCaixa.' - '.date('d/m/Y H:i');

        //Corrigir valor do agrupamento do caixa
        $sql = "INSERT INTO ContasAgrupadas ( CnAgrDtPagamento, CnAgrValorTotal, CnAgrFormaPagamento, CnAgrContaBanco, CnAgrDescricaoAgrupamento)
                VALUES ( :dateDtPagamento, :fValorTotal, :iFormaPagamento, :iContaBanco, :sDescricaoAgrupamento)";
        $result = $conn->prepare($sql);
        
        $result->execute(array(
            ':dateDtPagamento' => $dataAtual,
            ':fValorTotal' => $totalRecebido,
            ':iFormaPagamento' => null,
            ':iContaBanco' => null,
            ':sDescricaoAgrupamento' => $descricaoAgrupamentoRecebimento
        ));

        $agrupamentoRecebimento = $conn->lastInsertId();
        
        $sql = "SELECT SituaId
                FROM Situacao
                WHERE SituaChave = 'RECEBIDO'";
        $result = $conn->query($sql);
        $rowAReceber = $result->fetch(PDO::FETCH_ASSOC);
        $iStatusRecebido = $rowAReceber['SituaId'];		

        foreach($rowMovimentacaoRecebimento as $item) {
            $arrayDataHora = explode(' ', $item['CxRecDataHora']);
            $dataRecebimento = $arrayDataHora[0];

            $descricaoContaRecebimento = $item['AtendNumRegistro'].' - '.$item['ClienNome'];
            $cliente = $item['AtendCliente'];
            $formaPagamento = $item['CxRecFormaPagamento'];
            $valorAReceber = $item['CxRecValor'];
            $valorRecebido = $item['CxRecValorTotal'];
            $desconto = $item['CxRecDesconto'];

            //Falta o plano de contas e centro de custos
            
            $sql = "INSERT INTO ContasAReceber ( CnAReDtEmissao, CnARePlanoContas, CnAReCliente, CnAReDescricao, CnAReNumDocumento,  CnAReContaBanco, 
                                                CnAReFormaPagamento, CnAReVenda, CnAReDtVencimento, CnAReValorAReceber, CnAReDtRecebimento, CnAReValorRecebido, 
                                                CnAReTipoJuros, CnAReJuros, CnAReTipoDesconto, CnAReDesconto, CnAReObservacao, CnAReNumCheque, CnAReValorCheque,                  
                                                CnAReDtEmissaoCheque, CnAReDtVencimentoCheque, CnAReBancoCheque, CnAReAgenciaCheque, CnAReContaCheque,                  
                                                CnAReNomeCheque, CnAReCpfCheque, CnAReAgrupamento, CnAReStatus, CnAReUsuarioAtualizador, CnAReUnidade)
                       VALUES ( :dDtEmissao, :iPlanoContas, :iCliente, :sDescricao, :sNumDocumento, :iContaBanco, 
                                :iFormaPagamento, :iVenda, :dDtVencimento, :fValorAReceber, :dDtRecebimento, :fValorRecebido,
                                :sTipoJuros, :fJuros, :sTipoDesconto, :fDesconto, :sObservacao, :sNumCheque, :fValorCheque, 
                                :dDtEmissaoCheque, :dDtVencimentoCheque, :iBancoCheque, :iAgenciaCheque, :iContaCheque,
                                :iNomeCheque, :iCpfCheque, :iAgrupamento, :iStatus, :iUsuarioAtualizador, :iUnidade)";

            $result = $conn->prepare($sql);

            $result->execute(array(

                ':dDtEmissao'           => $dataRecebimento,
                ':iPlanoContas'         => null,
                ':iCliente'             => $cliente,
                ':sDescricao'           => $descricaoContaRecebimento,
                ':sNumDocumento'        => null,
                ':iContaBanco'          => $destinoContaFinanceiraId,
                ':iFormaPagamento'      => $formaPagamento,
                ':iVenda'               => null,
                ':dDtVencimento'        => $dataRecebimento,
                ':fValorAReceber'       => $valorAReceber,
                ':dDtRecebimento'       => $dataRecebimento,
                ':fValorRecebido'       => $valorRecebido,
                ':sTipoJuros'           => null,
                ':fJuros'               => null,
                ':sTipoDesconto'        => null,
                ':fDesconto'            => $desconto,
                ':sObservacao'          => null,
                ':sNumCheque'           => isset($_POST['inputNumCheque']) ? $_POST['inputNumCheque'] : null,
                ':fValorCheque'         => isset($_POST['inputValorCheque']) ? floatval(gravaValor($_POST['inputValorCheque'])) : null,
                ':dDtEmissaoCheque'     => isset($_POST['inputDtEmissaoCheque']) ? $_POST['inputDtEmissaoCheque'] : null,
                ':dDtVencimentoCheque'  => isset($_POST['inputDtVencimentoCheque']) ? $_POST['inputDtVencimentoCheque'] : null,
                ':iBancoCheque'         => isset($_POST['cmbBancoCheque']) ? intval($_POST['cmbBancoCheque']) : null,
                ':iAgenciaCheque'       => isset($_POST['inputAgenciaCheque']) ? $_POST['inputAgenciaCheque'] : null,
                ':iContaCheque'         => isset($_POST['inputContaCheque']) ? $_POST['inputContaCheque'] : null,
                ':iNomeCheque'          => isset($_POST['inputNomeCheque']) ? $_POST['inputNomeCheque'] : null,
                ':iCpfCheque'           => isset($_POST['inputCpfCheque']) ? $_POST['inputCpfCheque'] : null,   
                ':iAgrupamento'         => $agrupamentoRecebimento, 
                ':iStatus'              => $iStatusRecebido,
                ':iUsuarioAtualizador'  => intval($_SESSION['UsuarId']),
                ':iUnidade'             => intval($_SESSION['UnidadeId'])
            ));
            
            //$idContaAReceber = $conn->lastInsertId();
        }   
    }

    $sql_movimentacaoPagamento = "SELECT CxPagFormaPagamento, CxPagValor, CxPagDataHora, CxPagPlanoConta, CxPagJustificativaRetirada, CxPagFornecedor, CxPagCentroCusto
                                    FROM CaixaPagamento
                                    JOIN CaixaAbertura on CxAbeId = CxPagCaixaAbertura
                                    JOIN FormaPagamento on FrPagId = CxPagFormaPagamento
                                    WHERE CxAbeOperador = $_SESSION[UsuarId] and CxPagUnidade = $_SESSION[UnidadeId] and CxPagCaixaAbertura = $idCaixaAbertura";
    $resultMovimentacaoPagamento  = $conn->query($sql_movimentacaoPagamento);
    
    if($rowMovimentacaoPagamento = $resultMovimentacaoPagamento->fetchAll(PDO::FETCH_ASSOC)) {
        $descricaoPagamento = 'Pagamento do '.$nomeCaixa.' - '.date('d/m/Y H:i');
        
        $sql = "INSERT INTO ContasAgrupadas ( CnAgrDtPagamento, CnAgrValorTotal, CnAgrFormaPagamento, CnAgrContaBanco, CnAgrDescricaoAgrupamento)
                VALUES ( :dateDtPagamento, :fValorTotal, :iFormaPagamento, :iContaBanco, :sDescricaoAgrupamento)";
        $result = $conn->prepare($sql);
                
        $result->execute(array(
            ':dateDtPagamento' => $dataAtual,
            ':fValorTotal' => $totalPago,
            ':iFormaPagamento' => null,
            ':iContaBanco' => null,
            ':sDescricaoAgrupamento' => $descricaoPagamento
        ));

        $agrupamentoPagamento = $conn->lastInsertId();

        $sql = "SELECT SituaId
        FROM Situacao
        WHERE SituaChave = 'PAGO'";
        $result = $conn->query($sql);
        $rowAPagar = $result->fetch(PDO::FETCH_ASSOC);
        $iStatusPago = $rowAPagar['SituaId'];

        foreach($rowMovimentacaoPagamento as $item) {
            $arrayDataHora = explode(' ', $item['CxPagDataHora']);
            $dataPagamento = $arrayDataHora[0];

            $planoConta = $item['CxPagPlanoConta'];
            $centroCusto = $item['CxPagCentroCusto'];
            $fornecedor = $item['CxPagFornecedor'] != '' ? $item['CxPagFornecedor'] : 0; 
            $formaPagamento =  $item['CxPagFormaPagamento'];
            $descricaoContaPagamento = $item['CxPagJustificativaRetirada'];
            $valorPago = $item['CxPagValor'];

            $sql = "INSERT INTO ContasAPagar ( CnAPaPlanoContas, CnAPaFornecedor, CnAPaContaBanco, CnAPaFormaPagamento,
                                            CnAPaNotaFiscal, CnAPaDtEmissao, CnAPaOrdemCompra, CnAPaDescricao, CnAPaDtVencimento, CnAPaValorAPagar,
                                            CnAPaDtPagamento, CnAPaValorPago, CnAPaObservacao, CnAPaTipoJuros, CnAPaJuros, 
                                            CnAPaTipoDesconto, CnAPaDesconto, CnAPaAgrupamento, CnAPaStatus, CnAPaUsuarioAtualizador, CnAPaUnidade)
                    VALUES ( :iPlanoContas, :iFornecedor, :iContaBanco, :iFormaPagamento, :sNotaFiscal, :dateDtEmissao, :iOrdemCompra,
                            :sDescricao, :dateDtVencimento, :fValorAPagar, :dateDtPagamento, :fValorPago, :sObservacao, :sTipoJuros, :fJuros, 
                            :sTipoDesconto, :fDesconto, :iAgrupamento, :iStatus, :iUsuarioAtualizador, :iUnidade)";
            $result = $conn->prepare($sql);

            $result->execute(array(
                ':iPlanoContas'         => $planoConta,
                ':iFornecedor'          => $fornecedor,
                ':iContaBanco'          => $destinoContaFinanceiraId,
                ':iFormaPagamento'      => $formaPagamento,
                ':sNotaFiscal'          => null,
                ':dateDtEmissao'        => $dataPagamento,
                ':iOrdemCompra'         => null,
                ':sDescricao'           => $descricaoContaPagamento,
                ':dateDtVencimento'     => $dataPagamento,
                ':fValorAPagar'         => $valorPago,
                ':dateDtPagamento'      => $dataPagamento,
                ':fValorPago'           => $valorPago,
                ':sObservacao'          => null,
                ':sTipoJuros'           => null,
                ':fJuros'               => null,
                ':sTipoDesconto'        => null,
                ':fDesconto'            => null,   
                ':iAgrupamento'         => $agrupamentoPagamento, 
                ':iStatus'              => $iStatusPago,
                ':iUsuarioAtualizador'  => $_SESSION['UsuarId'],
                ':iUnidade'             => $_SESSION['UnidadeId']
            ));

            if($centroCusto != '') {
                $idContaAPagar = $conn->lastInsertId();

                $sql = "INSERT INTO ContasAPagarXCentroCusto ( CAPXCContasAPagar, CAPXCCentroCusto, CAPXCValor, CAPXCUsuarioAtualizador, CAPXCUnidade)
                        VALUES ( :iContasAPagar, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                $result = $conn->prepare($sql);

                $result->execute(array(
                    ':iContasAPagar' => $idContaAPagar,
                    ':iCentroCusto' => $centroCusto,
                    ':iValor' => $valorPago,
                    ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                    ':iUnidade' => $_SESSION['UnidadeId']
                ));
            }
        }
    }
    $conn->commit();
    
    $_SESSION['msg']['titulo'] = "Sucesso";
    $_SESSION['msg']['mensagem'] = "Fechamento do Caixa Concluído!!!";
    $_SESSION['msg']['tipo'] = "success";  
} catch(PDOException $e) {
    
    $conn->rollback();
    
    $_SESSION['msg']['titulo'] = "Erro";
    $_SESSION['msg']['mensagem'] = "Erro ao fechar o caixa!!!";
    $_SESSION['msg']['tipo'] = "error";	
    
    echo 'Error: ' . $e->getMessage();   
}

print(json_encode($resposta));
?>