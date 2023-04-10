<?php
include_once("sessao.php");

include('global_assets/php/conexao.php');

$justificativa = $_POST['inputContaJustificativa'];

if(isset($_POST['inputContasAPagarId'])) {
    $contaApagarId = $_POST['inputContasAPagarId'];

    $sql = "SELECT SituaId
            FROM Situacao
            WHERE SituaChave = 'APAGAR'";
    $result = $conn->query($sql);
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $situacao = $row['SituaId'];
  
    try {
        $conn->beginTransaction();

        $sqlAgrupamentoId = "SELECT CnAPaAgrupamento, CnAPaValorPago, CnAgrValorTotal
                    FROM  ContasAPagar 
                    JOIN ContasAgrupadas on CnAgrId = CnAPaAgrupamento
                    WHERE CnAPaId = $contaApagarId";
    
        $resultAgrupamentoId = $conn->query($sqlAgrupamentoId);
        $resultAgrupamentoId = $resultAgrupamentoId->fetch(PDO::FETCH_ASSOC);

        if(isset($resultAgrupamentoId['CnAPaAgrupamento']) && $resultAgrupamentoId['CnAPaAgrupamento'] != '') {
            $agrupamentoId = $resultAgrupamentoId['CnAPaAgrupamento'];
            $novoValorTotalAgrupamento = $resultAgrupamentoId['CnAgrValorTotal'] - $resultAgrupamentoId['CnAPaValorPago'];
    
            if($novoValorTotalAgrupamento > 0) {
                $sql = "UPDATE ContasAgrupadas SET CnAgrValorTotal = :fValorTotal
                        WHERE CnAgrId = $agrupamentoId";
                $result = $conn->prepare($sql);
    
                $result->execute(array(
                    ':fValorTotal' => $novoValorTotalAgrupamento
                ));
            }else {
                $sql = "DELETE FROM ContasAgrupadas
                        WHERE CnAgrId = :id";
                $result = $conn->prepare($sql);
                $result->bindParam(':id', $agrupamentoId); 
                $result->execute();
            }

        }

        $sql = "UPDATE ContasAPagar SET CnAPaStatus = :iStatus, CnAPaValorPago = :fValorPago, CnAPaDtPagamento = :sDataPagamento, CnAPaContaBanco = :iContaBanco, 
                                        CnAPaFormaPagamento = :iFormaPagamento, CnAPaAgrupamento = :iAgrupamento, CnAPaJustificativaEstorno = :sJustificativa
                WHERE CnAPaId = $contaApagarId";
        $result = $conn->prepare($sql);

        $result->execute(array(
            ':iStatus' => $situacao,
            ':fValorPago' => null,
            ':sDataPagamento' => null,
            ':iContaBanco' => null,
            ':iFormaPagamento' => null,
            ':iAgrupamento' => null,
            ':sJustificativa' => $justificativa
        ));

        $conn->commit();

        $_SESSION['msg']['titulo'] = "Sucesso";
        $_SESSION['msg']['mensagem'] = "Conta estornada!!!";
        $_SESSION['msg']['tipo'] = "success";
    } catch (PDOException $e) {
        $conn->rollback();

        $_SESSION['msg']['titulo'] = "Erro";
        $_SESSION['msg']['mensagem'] = "Erro ao estornar conta!!!";
        $_SESSION['msg']['tipo'] = "error";

        echo 'Error: ' . $e->getMessage();
    }

    irpara("contasAPagar.php");
}else if(isset($_POST['inputContasAReceberId'])){
    $contaReceberId = $_POST['inputContasAReceberId']; 

    $sql = "SELECT SituaId
            FROM Situacao
            WHERE SituaChave = 'ARECEBER'";
    $result = $conn->query($sql);
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $situacao = $row['SituaId'];

    try {
        $conn->beginTransaction();

        $sqlAgrupamentoId = "SELECT CnAReAgrupamento, CnAReValorRecebido, CnAgrValorTotal
                    FROM  ContasAReceber
                    JOIN ContasAgrupadas on CnAgrId = CnAReAgrupamento
                    WHERE CnAReId = $contaReceberId";
    
        $resultAgrupamentoId = $conn->query($sqlAgrupamentoId);
        $resultAgrupamentoId = $resultAgrupamentoId->fetch(PDO::FETCH_ASSOC);

        if(isset($resultAgrupamentoId['CnAReAgrupamento']) && $resultAgrupamentoId['CnAReAgrupamento'] != '') {
            $agrupamentoId = $resultAgrupamentoId['CnAReAgrupamento'];
            $novoValorTotalAgrupamento = $resultAgrupamentoId['CnAgrValorTotal'] - $resultAgrupamentoId['CnAReValorRecebido'];
    
            if($novoValorTotalAgrupamento > 0) {
                $sql = "UPDATE ContasAgrupadas SET CnAgrValorTotal = :fValorTotal
                        WHERE CnAgrId = $agrupamentoId";
                $result = $conn->prepare($sql);
    
                $result->execute(array(
                    ':fValorTotal' => $novoValorTotalAgrupamento
                ));
            }else {
                $sql = "DELETE FROM ContasAgrupadas
                        WHERE CnAgrId = :id";
                $result = $conn->prepare($sql);
                $result->bindParam(':id', $agrupamentoId); 
                $result->execute();
            }

        }

        $sql = "UPDATE ContasAReceber SET CnAReStatus = :iStatus, CnAReValorRecebido = :fValorRecebido, CnAReDtRecebimento = :sDataRecebimento, CnAReContaBanco = :iContaBanco, 
                                          CnAReFormaPagamento = :iFormaPagamento, CnAReAgrupamento = :iAgrupamento, CnAReJustificativaEstorno = :sJustificativa
                WHERE CnAReId = $contaReceberId";
        $result = $conn->prepare($sql);

        $result->execute(array(
            ':iStatus' => $situacao,
            ':fValorRecebido' => null,
            ':sDataRecebimento' => null,
            ':iContaBanco' => null,
            ':iFormaPagamento' => null,
            ':iAgrupamento' => null,
            ':sJustificativa' => $justificativa
        ));

        $conn->commit();
        $_SESSION['msg']['titulo'] = "Sucesso";
        $_SESSION['msg']['mensagem'] = "Conta estornada!!!";
        $_SESSION['msg']['tipo'] = "success";
    } catch (PDOException $e) {
        $conn->rollback();

        $_SESSION['msg']['titulo'] = "Erro";
        $_SESSION['msg']['mensagem'] = "Erro ao estornar conta!!!";
        $_SESSION['msg']['tipo'] = "error";

        echo 'Error: ' . $e->getMessage();
    }
    irpara("contasAReceber.php");
}else {
    $contaId = $_POST['inputMovimentacaoFinanceiraId'];
    $tipoMovimentacao = $_POST['tipoMov'];
    
    if($tipoMovimentacao == 'P') {
        $sql = "SELECT SituaId
                FROM Situacao
                WHERE SituaChave = 'APAGAR'";
        $result = $conn->query($sql);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $situacao = $row['SituaId'];

        try {
            $conn->beginTransaction();

            $sqlAgrupamentoId = "SELECT CnAPaAgrupamento, CnAPaValorPago, CnAgrValorTotal
                            FROM  ContasAPagar 
                            JOIN ContasAgrupadas on CnAgrId = CnAPaAgrupamento
                            WHERE CnAPaId = $contaId";
        
            $resultAgrupamentoId = $conn->query($sqlAgrupamentoId);
            $resultAgrupamentoId = $resultAgrupamentoId->fetch(PDO::FETCH_ASSOC);

            if(isset($resultAgrupamentoId['CnAPaAgrupamento']) && $resultAgrupamentoId['CnAPaAgrupamento'] != '') {
                $agrupamentoId = $resultAgrupamentoId['CnAPaAgrupamento'];
                $novoValorTotalAgrupamento = $resultAgrupamentoId['CnAgrValorTotal'] - $resultAgrupamentoId['CnAPaValorPago'];
        
                if($novoValorTotalAgrupamento > 0) {
                    $sql = "UPDATE ContasAgrupadas SET CnAgrValorTotal = :fValorTotal
                            WHERE CnAgrId = $agrupamentoId";
                    $result = $conn->prepare($sql);
        
                    $result->execute(array(
                        ':fValorTotal' => $novoValorTotalAgrupamento
                    ));
                }else {
                    $sql = "DELETE FROM ContasAgrupadas
                            WHERE CnAgrId = :id";
                    $result = $conn->prepare($sql);
                    $result->bindParam(':id', $agrupamentoId); 
                    $result->execute();
                }

            }

            $sql = "UPDATE ContasAPagar SET CnAPaStatus = :iStatus, CnAPaValorPago = :fValorPago, CnAPaDtPagamento = :sDataPagamento, CnAPaContaBanco = :iContaBanco, 
                                            CnAPaFormaPagamento = :iFormaPagamento, CnAPaAgrupamento = :iAgrupamento, CnAPaJustificativaEstorno = :sJustificativa
                    WHERE CnAPaId = $contaId";
            $result = $conn->prepare($sql);

            $result->execute(array(
                ':iStatus' => $situacao,
                ':fValorPago' => null,
                ':sDataPagamento' => null,
                ':iContaBanco' => null,
                ':iFormaPagamento' => null,
                ':iAgrupamento' => null,
                ':sJustificativa' => $justificativa
            ));
    
            $conn->commit();
            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Conta estornada!!!";
            $_SESSION['msg']['tipo'] = "success";
        } catch (PDOException $e) {
            $conn->rollback();
    
            $_SESSION['msg']['titulo'] = "Erro";
            $_SESSION['msg']['mensagem'] = "Erro ao estornar conta!!!";
            $_SESSION['msg']['tipo'] = "error";
    
            echo 'Error: ' . $e->getMessage();
        }
    }else if($tipoMovimentacao == 'R') {
        $sql = "SELECT SituaId
                FROM Situacao
                WHERE SituaChave = 'ARECEBER'";
        $result = $conn->query($sql);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $situacao = $row['SituaId'];

        try {
            $conn->beginTransaction();

            $sqlAgrupamentoId = "SELECT CnAReAgrupamento, CnAReValorRecebido, CnAgrValorTotal
                        FROM  ContasAReceber
                        JOIN ContasAgrupadas on CnAgrId = CnAReAgrupamento
                        WHERE CnAReId = $contaId";
        
            $resultAgrupamentoId = $conn->query($sqlAgrupamentoId);
            $resultAgrupamentoId = $resultAgrupamentoId->fetch(PDO::FETCH_ASSOC);

            if(isset($resultAgrupamentoId['CnAReAgrupamento']) && $resultAgrupamentoId['CnAReAgrupamento'] != '') {
                $agrupamentoId = $resultAgrupamentoId['CnAReAgrupamento'];
                $novoValorTotalAgrupamento = $resultAgrupamentoId['CnAgrValorTotal'] - $resultAgrupamentoId['CnAReValorRecebido'];
        
                if($novoValorTotalAgrupamento > 0) {
                    $sql = "UPDATE ContasAgrupadas SET CnAgrValorTotal = :fValorTotal
                            WHERE CnAgrId = $agrupamentoId";
                    $result = $conn->prepare($sql);
        
                    $result->execute(array(
                        ':fValorTotal' => $novoValorTotalAgrupamento
                    ));
                }else {
                    $sql = "DELETE FROM ContasAgrupadas
                            WHERE CnAgrId = :id";
                    $result = $conn->prepare($sql);
                    $result->bindParam(':id', $agrupamentoId); 
                    $result->execute();
                }

            }

            $sql = "UPDATE ContasAReceber SET CnAReStatus = :iStatus, CnAReValorRecebido = :fValorRecebido, CnAReDtRecebimento = :sDataRecebimento, CnAReContaBanco = :iContaBanco, 
                                            CnAReFormaPagamento = :iFormaPagamento, CnAReAgrupamento = :iAgrupamento, CnAReJustificativaEstorno = :sJustificativa
                    WHERE CnAReId = $contaId";
            $result = $conn->prepare($sql);

            $result->execute(array(
                ':iStatus' => $situacao,
                ':fValorRecebido' => null,
                ':sDataRecebimento' => null,
                ':iContaBanco' => null,
                ':iFormaPagamento' => null,
                ':iAgrupamento' => null,
                ':sJustificativa' => $justificativa
            ));
    
            $conn->commit();
            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Conta estornada!!!";
            $_SESSION['msg']['tipo'] = "success";
        } catch (PDOException $e) {
            $conn->rollback();
    
            $_SESSION['msg']['titulo'] = "Erro";
            $_SESSION['msg']['mensagem'] = "Erro ao estornar conta!!!";
            $_SESSION['msg']['tipo'] = "error";
    
            echo 'Error: ' . $e->getMessage();
        }
    }

    irpara("movimentacaoFinanceira.php");
}
?>