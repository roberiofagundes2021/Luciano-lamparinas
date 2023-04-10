<?php
include_once("sessao.php");
include('global_assets/php/conexao.php');

$tipo = $_POST['inputTipo'];
$aberturaCaixaId = $_POST['inputAberturaCaixaId'];
$dataHora = date('Y-m-d H:i:s');
$valorRetirado = $_POST['inputValorRetirado'];
$justificativaRetirada = $_POST['inputJustificativaRetirada'];
$formaPagamento = $_POST['inputFormaPagamento'];
$planoContas = isset($_POST['inputPlanoContas']) ? $_POST['inputPlanoContas'] : null;
$centroCustos = isset($_POST['inputCentroCustos']) ? $_POST['inputCentroCustos'] : null;
$fornecedor = isset($_POST['inputFornecedor']) ? $_POST['inputFornecedor'] : null;
$numeroCheque = $_POST['inputNumeroCheque'] != '' ? $_POST['inputNumeroCheque'] : null;
$valorCheque = $_POST['inputValorCheque'] != '' ? gravaValor($_POST['inputValorCheque']) : null; 
$dataEmissaoCheque = $_POST['inputDataEmissaoCheque'] != '' ?  $_POST['inputDataEmissaoCheque'] : null;
$dataVencimentoCheque = $_POST['inputDataVencimentoCheque'] != '' ?  $_POST['inputDataVencimentoCheque'] : null;
$bancoCheque = $_POST['inputBancoCheque'] != '' ?  $_POST['inputBancoCheque'] : null;
$agenciaCheque = $_POST['inputAgenciaCheque'] != '' ?  $_POST['inputAgenciaCheque'] : null;
$contaCheque = $_POST['inputContaCheque'] != '' ?  $_POST['inputContaCheque'] : null;
$nomeCheque = $_POST['inputNomeCheque'] != '' ?  $_POST['inputNomeCheque'] : null;
$cpfCheque = $_POST['inputCpfCheque'] != '' ?  $_POST['inputCpfCheque'] : null;

//Consulta do saldo do atual que está na abertura do caixa
$sql = "SELECT CxAbeTotalRecebido, CxAbeTotalPago
        FROM CaixaAbertura
        WHERE CxAbeId = $aberturaCaixaId";
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);
$saldoAtual = $row['CxAbeTotalRecebido'] - $row['CxAbeTotalPago'];

if($saldoAtual > $valorRetirado) {
        try{
                $conn->beginTransaction();

                $sql = "SELECT SituaId
                        FROM Situacao
                        WHERE SituaChave = 'PAGO'";
                $result = $conn->query($sql);
                $situacao = $result->fetch(PDO::FETCH_ASSOC);

                if($tipo == 'SANGRIA') {
                        $sql = "INSERT INTO CaixaPagamento (CxPagCaixaAbertura, CxPagDataHora, CxPagValor, CxPagJustificativaRetirada, CxPagFormaPagamento, 
                                                            CxPagNumCheque, CxPagValorCheque, CxPagDtEmissaoCheque, CxPagDtVencimentoCheque, CxPagBancoCheque, 
                                                            CxPagAgenciaCheque, CxPagContaCheque, CxPagNomeCheque, CxPagCpfCheque, CxPagStatus, CxPagUnidade)
                                VALUES (:iCaixaAbertura, :sDataHora, :fValor, :sJustificativaRetirada, :iFormaPagamento, :sNumCheque, :fValorCheque, :sDtEmissaoCheque, 
                                        :sDtVencimentoCheque, :iBancoCheque, :sAgenciaCheque, :sContaCheque, :sNomeCheque, :sCpfCheque, :bStatus, :iUnidade)";
                        $result = $conn->prepare($sql);
                                
                        $result->execute(array(
                                ':iCaixaAbertura' => $aberturaCaixaId,
                                ':sDataHora' => $dataHora,
                                ':fValor' => $valorRetirado,
                                ':sJustificativaRetirada' => $justificativaRetirada,
                                ':iFormaPagamento' => $formaPagamento,
                                ':sNumCheque' => $numeroCheque,
                                ':fValorCheque' => $valorCheque,
                                ':sDtEmissaoCheque' => $dataEmissaoCheque,
                                ':sDtVencimentoCheque' => $dataVencimentoCheque,
                                ':iBancoCheque' => $bancoCheque,
                                ':sAgenciaCheque' => $agenciaCheque,
                                ':sContaCheque' => $contaCheque,
                                ':sNomeCheque' => $nomeCheque,
                                ':sCpfCheque' => $cpfCheque,
                                ':bStatus' => $situacao['SituaId'],
                                ':iUnidade' => $_SESSION['UnidadeId'],
                        ));
                }else {
                        $sql = "INSERT INTO CaixaPagamento (CxPagCaixaAbertura, CxPagDataHora, CxPagValor, CxPagJustificativaRetirada, CxPagFormaPagamento, 
                                                           CxPagPlanoConta, CxPagCentroCusto, CxPagFornecedor, CxPagNumCheque, CxPagValorCheque, CxPagDtEmissaoCheque, 
                                                           CxPagDtVencimentoCheque, CxPagBancoCheque, CxPagAgenciaCheque, CxPagContaCheque, CxPagNomeCheque, 
                                                           CxPagCpfCheque, CxPagStatus, CxPagUnidade)
                                VALUES (:iCaixaAbertura, :sDataHora, :fValor, :sJustificativaRetirada, :iFormaPagamento, :iPlanoContas, :iCentroCustos, :iFornecedor, 
                                        :sNumCheque, :fValorCheque, :sDtEmissaoCheque, :sDtVencimentoCheque, :iBancoCheque, :sAgenciaCheque, :sContaCheque, :sNomeCheque, 
                                        :sCpfCheque, :bStatus, :iUnidade)";
                        $result = $conn->prepare($sql);
                                
                        $result->execute(array(
                                ':iCaixaAbertura' => $aberturaCaixaId,
                                ':sDataHora' => $dataHora,
                                ':fValor' => $valorRetirado,
                                ':sJustificativaRetirada' => $justificativaRetirada,
                                ':iFormaPagamento' => $formaPagamento,
                                ':iPlanoContas' => $planoContas,
                                ':iCentroCustos' => $centroCustos,
                                ':iFornecedor' => $fornecedor,
                                ':sNumCheque' => $numeroCheque,
                                ':fValorCheque' => $valorCheque,
                                ':sDtEmissaoCheque' => $dataEmissaoCheque,
                                ':sDtVencimentoCheque' => $dataVencimentoCheque,
                                ':iBancoCheque' => $bancoCheque,
                                ':sAgenciaCheque' => $agenciaCheque,
                                ':sContaCheque' => $contaCheque,
                                ':sNomeCheque' => $nomeCheque,
                                ':sCpfCheque' => $cpfCheque,
                                ':bStatus' => $situacao['SituaId'],
                                ':iUnidade' => $_SESSION['UnidadeId'],
                        ));	
                }
                

                $valorRetirado = $row['CxAbeTotalPago'] + $valorRetirado;
                
                $sql = "UPDATE CaixaAbertura SET CxAbeTotalPago = :fValorRecebido
                        WHERE CxAbeId = :iCaixaAberturaId";
                $result = $conn->prepare($sql);
                
                $result->execute(array(
                        ':fValorRecebido' => $valorRetirado,
                        ':iCaixaAberturaId' => $aberturaCaixaId 
                ));

                $idCaixaPagamento = $conn->lastInsertId();
                
                $resposta = $idCaixaPagamento;

                $conn->commit();
                            
        } catch(PDOException $e) {

                $conn->rollback();	
        
                echo 'Error: ' . $e->getMessage();
        }
}else {
        $resposta = 'Impossivel retirar';
}

print(json_encode($resposta));
?>