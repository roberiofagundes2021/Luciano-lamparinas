<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Novo Lançamento - Contas a Pagar';

include('global_assets/php/conexao.php');

if (isset($_POST['inputPermissionAtualiza'])){
    $atualizar = $_POST['inputPermissionAtualiza'];
}

if (isset($_POST['cmbPlanoContas'])) {

    if (isset($_POST['inputEditar'])) {

        try {
            $conn->beginTransaction();

            $pagamentoParcial = false;

            if (isset($_POST['inputPagamentoParcial'])) {
                if (intval($_POST['inputPagamentoParcial']) != 0) {
                    $sql = "SELECT SituaId
                            FROM Situacao
                            WHERE SituaChave = 'APAGAR'";
                    $result = $conn->query($sql);
                    $situacaoPagamentoParcial = $result->fetch(PDO::FETCH_ASSOC);

                    $sql = "INSERT INTO ContasAPagar ( CnAPaPlanoContas, CnAPaFornecedor, CnAPaContaBanco, CnAPaFormaPagamento,
                                                  CnAPaNotaFiscal, CnAPaDtEmissao, CnAPaOrdemCompra, CnAPaDescricao, CnAPaDtVencimento, CnAPaValorAPagar,
                                                  CnAPaDtPagamento, CnAPaValorPago, CnAPaObservacao, CnAPaTipoJuros, CnAPaJuros, 
                                                  CnAPaTipoDesconto, CnAPaDesconto, CnAPaStatus, CnAPaUsuarioAtualizador, CnAPaUnidade)
                            VALUES ( :iPlanoContas, :iFornecedor, :iContaBanco, :iFormaPagamento, :sNotaFiscal, :dateDtEmissao, :iOrdemCompra,
                                    :sDescricao, :dateDtVencimento, :fValorAPagar, :dateDtPagamento, :fValorPago, :sObservacao, :sTipoJuros, :fJuros, 
                                    :sTipoDesconto, :fDesconto, :iStatus, :iUsuarioAtualizador, :iUnidade)";
                    $result = $conn->prepare($sql);

                    $result->execute(array(
                        ':iPlanoContas' => $_POST['cmbPlanoContas'],
                        ':iFornecedor' => $_POST['cmbFornecedor'],
                        ':iContaBanco' => $_POST['cmbContaBanco'],
                        ':iFormaPagamento' => $_POST['cmbFormaPagamento'],
                        ':sNotaFiscal' => $_POST['inputNotaFiscal'],
                        ':dateDtEmissao' => $_POST['inputDataEmissao'],
                        ':iOrdemCompra' => isset($_POST['cmbOrdemCarta']) ? $_POST['cmbOrdemCarta'] : null,
                        ':sDescricao' => $_POST['inputDescricao'],
                        ':dateDtVencimento' => $_POST['inputDataVencimento'],
                        ':fValorAPagar' => $_POST['inputPagamentoParcial'],
                        ':dateDtPagamento' => $_POST['inputDataPagamento'],
                        ':fValorPago' => null,
                        ':sObservacao' => $_POST['inputObservacao'],
                        ':sTipoJuros' => isset($_POST['cmbTipoJurosJD']) ? $_POST['cmbTipoJurosJD'] : null,
                        ':fJuros' => isset($_POST['inputJurosJD']) ? floatval(gravaValor($_POST['inputJurosJD'])) : null,
                        ':sTipoDesconto' => isset($_POST['cmbTipoDescontoJD']) ? $_POST['cmbTipoDescontoJD'] : null,
                        ':fDesconto' => isset($_POST['inputDescontoJD']) ? floatval(gravaValor($_POST['inputDescontoJD'])) : null,
                        ':iStatus' => $situacaoPagamentoParcial['SituaId'],
                        ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                        ':iUnidade' => $_SESSION['UnidadeId']
                    ));

                    $idContaAPagarParcial = $conn->lastInsertId();
                    $pagamentoParcial = true;
                }
            }

            if (isset($_POST['inputValorTotalPago'])) {
                $sql = "SELECT SituaId
                        FROM Situacao
                        WHERE SituaChave = 'PAGO'
                    ";
                $result = $conn->query($sql);
                $situacao = $result->fetch(PDO::FETCH_ASSOC);
            } else {
                $sql = "SELECT SituaId
                        FROM Situacao
                        WHERE SituaChave = 'APAGAR'
                    ";
                $result = $conn->query($sql);
                $situacao = $result->fetch(PDO::FETCH_ASSOC);
            }

            $sql = "UPDATE ContasAPagar SET CnAPaPlanoContas = :iPlanoContas, CnAPaFornecedor = :iFornecedor, CnAPaContaBanco = :iContaBanco, CnAPaFormaPagamento = :iFormaPagamento,
                                            CnAPaNotaFiscal = :sNotaFiscal, CnAPaDtEmissao = :dateDtEmissao, CnAPaOrdemCompra = :iOrdemCompra, CnAPaDescricao = :sDescricao, CnAPaDtVencimento = :dateDtVencimento, CnAPaValorAPagar = :fValorAPagar,
                                            CnAPaDtPagamento = :dateDtPagamento, CnAPaValorPago = :fValorPago, CnAPaObservacao = :sObservacao, CnAPaJustificativaEstorno = :sJustificativaEstorno, CnAPaStatus = :iStatus, CnAPaUsuarioAtualizador = :iUsuarioAtualizador, CnAPaUnidade = :iUnidade,
                                            CnAPaTipoJuros = :sTipoJuros, CnAPaJuros = :fJuros, CnAPaTipoDesconto = :sTipoDesconto, CnAPaDesconto = :fDesconto
		    		WHERE CnAPaId = " . $_POST['inputContaId'] . "";
            $result = $conn->prepare($sql);

            $result->execute(array(
                ':iPlanoContas' => $_POST['cmbPlanoContas'],
                ':iFornecedor' => $_POST['cmbFornecedor'],
                ':iContaBanco' => $_POST['cmbContaBanco'],
                ':iFormaPagamento' => $_POST['cmbFormaPagamento'],
                ':sNotaFiscal' => isset($_POST['inputNotaFiscal']) ? $_POST['inputNotaFiscal'] : null,
                ':dateDtEmissao' => $_POST['inputDataEmissao'],
                ':iOrdemCompra' => isset($_POST['cmbOrdemCarta']) ? $_POST['cmbOrdemCarta'] : null,
                ':sDescricao' => $_POST['inputDescricao'],
                ':dateDtVencimento' => $_POST['inputDataVencimento'],
                ':fValorAPagar' => floatval(gravaValor($_POST['inputValor'])),
                ':dateDtPagamento' => $_POST['inputDataPagamento'],
                ':fValorPago' => isset($_POST['inputValorTotalPago']) ? floatval(gravaValor($_POST['inputValorTotalPago'])) : null,
                ':sObservacao' => $_POST['inputObservacao'],
                ':sJustificativaEstorno' => null,
                ':sTipoJuros' => isset($_POST['cmbTipoJurosJD']) ? $_POST['cmbTipoJurosJD'] : null,
                ':fJuros' => isset($_POST['inputJurosJD']) ? floatval(gravaValor($_POST['inputJurosJD'])) : null,
                ':sTipoDesconto' => isset($_POST['cmbTipoDescontoJD']) ? $_POST['cmbTipoDescontoJD'] : null,
                ':fDesconto' => isset($_POST['inputDescontoJD']) ? floatval(gravaValor($_POST['inputDescontoJD'])) : null,
                ':iStatus' => $situacao['SituaId'],
                ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                ':iUnidade' => $_SESSION['UnidadeId']
            ));

            $idContaAPagar = $_POST['inputContaId'];

            if($pagamentoParcial) {
                $valorPagoParcialmente = floatval(gravaValor($_POST['inputValor']));
                $valorAPagarParcialmente = $_POST['inputPagamentoParcial'];
                $totalParcialmente = $valorPagoParcialmente + $valorAPagarParcialmente;
                
                $percentualAPagarParcialmente = ($valorAPagarParcialmente * 100) / $totalParcialmente;
                $percentualPagoParcialmente = ($valorPagoParcialmente * 100) / $totalParcialmente;

                $registros = intval($_POST['totalRegistros']);
                for($x=0; $x < $registros; $x++){
                    //$keyNome = 'inputCentroNome-'.$x;
                    $keyId = 'inputIdCentro-'.$x;
                    $centroCusto = $_POST[$keyId];
                    $valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
                    $valor = str_replace(',', '.', $valor);

                    $valor = ($percentualAPagarParcialmente / 100) * $valor;
                    $valor;

                    $sql = "INSERT INTO ContasAPagarXCentroCusto ( CAPXCContasAPagar, CAPXCCentroCusto, CAPXCValor, CAPXCUsuarioAtualizador, CAPXCUnidade)
                            VALUES ( :iContasAPagar, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                    $result = $conn->prepare($sql);

                    $result->execute(array(

                        ':iContasAPagar' => $idContaAPagarParcial,
                        ':iCentroCusto' => $centroCusto,
                        ':iValor' => $valor,
                        ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                        ':iUnidade' => $_SESSION['UnidadeId']
                    ));
                }

                $sql = "SELECT CAPXCCentroCusto
                        FROM ContasAPagarXCentroCusto
                        WHERE CAPXCContasAPagar = $idContaAPagar";
                $resultQuantContaAPagarXCentroCusto = $conn->query($sql);
                $centroCustoBancoDeDados = $resultQuantContaAPagarXCentroCusto->fetchAll(PDO::FETCH_ASSOC);
    
                $registros = intval($_POST['totalRegistros']);
                $controle = true;
                $i = 0;
                
                for($x=0; $x < $registros; $x++){
                    $keyId = 'inputIdCentro-'.$x;
                    $valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
                    $valor = str_replace(',', '.', $valor);
    
                    $centroCusto = $_POST[$keyId];
                    $valor;
                    $valor = ($percentualPagoParcialmente / 100) * $valor;
                    $valor;
                    $arrayControleCentroCustoSistema[] = $centroCusto;
                    $arrayCentroCusto[$i]['idCentroCusto'] = $centroCusto;
                    $arrayCentroCusto[$i]['valorCentroCusto'] = $valor;
    
                    foreach($centroCustoBancoDeDados as $idCentroContaAtualiza) {
                        if($idCentroContaAtualiza['CAPXCCentroCusto'] == $centroCusto) {
                            $sql = "UPDATE ContasAPagarXCentroCusto SET CAPXCValor = :fValor, CAPXCUsuarioAtualizador = :iUsuarioAtualizador
                                    WHERE CAPXCCentroCusto = $centroCusto AND CAPXCContasAPagar = $idContaAPagar";
                            $result = $conn->prepare($sql);
    
                            $result->execute(array(
                                ':fValor' => $valor,
                                ':iUsuarioAtualizador' => $_SESSION['UsuarId']
                            ));

                            $arrayControle[] = $centroCusto;
                            $arrayIdAntigoCentroCusto[$i]['idCentroCusto'] = $idCentroContaAtualiza['CAPXCCentroCusto'];
                            $arrayIdAntigoCentroCusto[$i]['valorCentroCusto'] = $valor;
                        }
                        if($controle) {
                            $arrayBancoDeDados[] = $idCentroContaAtualiza['CAPXCCentroCusto'];
                        }
                    }
                    $controle = false;
    
                    $i++;
                }
    
                if(isset($arrayControle)) {
                    $arrayCentroCustoInsere = pegaDiferencaArray($arrayControleCentroCustoSistema, $arrayControle);
                    if($arrayCentroCustoInsere) {
                        foreach($arrayCentroCustoInsere as $idCentroCusto) {
                            foreach($arrayCentroCusto as $insereCentroCusto) {
                                if($insereCentroCusto['idCentroCusto'] == $idCentroCusto) {
                                    $sql = "INSERT INTO ContasAPagarXCentroCusto ( CAPXCContasAPagar, CAPXCCentroCusto, CAPXCValor, CAPXCUsuarioAtualizador, CAPXCUnidade)
                                            VALUES ( :iContasAPagar, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                                    $result = $conn->prepare($sql);
    
                                    $result->execute(array(
    
                                        ':iContasAPagar' => $idContaAPagar,
                                        ':iCentroCusto' => $insereCentroCusto['idCentroCusto'],
                                        ':iValor' => $insereCentroCusto['valorCentroCusto'],
                                        ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                                        ':iUnidade' => $_SESSION['UnidadeId']
                                    ));
                                }
                            }
                        }
                    }
        
                    $arrayCentroCustoDeleta = pegaDiferencaArray($arrayBancoDeDados, $arrayControle);
                    if($arrayCentroCustoDeleta) {
                        foreach($arrayCentroCustoDeleta as $deletaCentroCusto) {
                            $sql = "DELETE FROM ContasAPagarXCentroCusto
                                    WHERE CAPXCCentroCusto = :iCentroCusto AND CAPXCContasAPagar = :iContaAPagar";
                            $result = $conn->prepare($sql);
                            
                            $result->execute(array(
                                ':iCentroCusto' => $deletaCentroCusto,
                                ':iContaAPagar' => $idContaAPagar
                            ));
                        }
                    }
                }else {
                    foreach($arrayControleCentroCustoSistema as $novoCentroCusto) {
                        foreach($arrayCentroCusto as $insereCentroCusto) {
                            if($insereCentroCusto['idCentroCusto'] == $novoCentroCusto) {
                                $sql = "INSERT INTO ContasAPagarXCentroCusto ( CAPXCContasAPagar, CAPXCCentroCusto, CAPXCValor, CAPXCUsuarioAtualizador, CAPXCUnidade)
                                        VALUES ( :iContasAPagar, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                                $result = $conn->prepare($sql);
    
                                $result->execute(array(
    
                                    ':iContasAPagar' => $idContaAPagar,
                                    ':iCentroCusto' => $insereCentroCusto['idCentroCusto'],
                                    ':iValor' => $insereCentroCusto['valorCentroCusto'],
                                    ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                                    ':iUnidade' => $_SESSION['UnidadeId']
                                ));
                            }
                        }
                    }
    
                    foreach($arrayBancoDeDados as $deletaCentroCusto) {
                        $sql = "DELETE FROM ContasAPagarXCentroCusto
                                WHERE CAPXCCentroCusto = :iCentroCusto AND CAPXCContasAPagar = :iContaAPagar";
                        $result = $conn->prepare($sql);
                        
                        $result->execute(array(
                            ':iCentroCusto' => $deletaCentroCusto,
                            ':iContaAPagar' => $idContaAPagar
                        ));
                    }
                }

            }else {
                $sql = "SELECT CAPXCCentroCusto
                        FROM ContasAPagarXCentroCusto
                        WHERE CAPXCContasAPagar = $idContaAPagar";
                $resultQuantContaAPagarXCentroCusto = $conn->query($sql);
                $centroCustoBancoDeDados = $resultQuantContaAPagarXCentroCusto->fetchAll(PDO::FETCH_ASSOC);
    
                $registros = intval($_POST['totalRegistros']);
                $controle = true;
                $i = 0;
                
                for($x=0; $x < $registros; $x++){
                    $keyId = 'inputIdCentro-'.$x;
                    $valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
                    $valor = str_replace(',', '.', $valor);
    
                    $centroCusto = $_POST[$keyId];
                    $valor;
                    $arrayControleCentroCustoSistema[] = $centroCusto;
                    $arrayCentroCusto[$i]['idCentroCusto'] = $centroCusto;
                    $arrayCentroCusto[$i]['valorCentroCusto'] = $valor;
    
                    foreach($centroCustoBancoDeDados as $idCentroContaAtualiza) {
                        if($idCentroContaAtualiza['CAPXCCentroCusto'] == $centroCusto) {
                            $sql = "UPDATE ContasAPagarXCentroCusto SET CAPXCValor = :fValor, CAPXCUsuarioAtualizador = :iUsuarioAtualizador
                                    WHERE CAPXCCentroCusto = $centroCusto AND CAPXCContasAPagar = $idContaAPagar";
                            $result = $conn->prepare($sql);
    
                            $result->execute(array(
                                ':fValor' => $valor,
                                ':iUsuarioAtualizador' => $_SESSION['UsuarId']
                            ));
    
                            $arrayControle[] = $centroCusto;
                            $arrayIdAntigoCentroCusto[$i]['idCentroCusto'] = $idCentroContaAtualiza['CAPXCCentroCusto'];
                            $arrayIdAntigoCentroCusto[$i]['valorCentroCusto'] = $valor;
                        }
                        if($controle) {
                            $arrayBancoDeDados[] = $idCentroContaAtualiza['CAPXCCentroCusto'];
                        }
                    }
                    $controle = false;
    
                    $i++;
                }
    
                if(isset($arrayControle)) {
                    $arrayCentroCustoInsere = pegaDiferencaArray($arrayControleCentroCustoSistema, $arrayControle);
                    if($arrayCentroCustoInsere) {
                        foreach($arrayCentroCustoInsere as $idCentroCusto) {
                            foreach($arrayCentroCusto as $insereCentroCusto) {
                                if($insereCentroCusto['idCentroCusto'] == $idCentroCusto) {
                                    $sql = "INSERT INTO ContasAPagarXCentroCusto ( CAPXCContasAPagar, CAPXCCentroCusto, CAPXCValor, CAPXCUsuarioAtualizador, CAPXCUnidade)
                                            VALUES ( :iContasAPagar, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                                    $result = $conn->prepare($sql);
    
                                    $result->execute(array(
    
                                        ':iContasAPagar' => $idContaAPagar,
                                        ':iCentroCusto' => $insereCentroCusto['idCentroCusto'],
                                        ':iValor' => $insereCentroCusto['valorCentroCusto'],
                                        ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                                        ':iUnidade' => $_SESSION['UnidadeId']
                                    ));
                                }
                            }
                        }
                    }
        
                    $arrayCentroCustoDeleta = pegaDiferencaArray($arrayBancoDeDados, $arrayControle);
                    if($arrayCentroCustoDeleta) {
                        foreach($arrayCentroCustoDeleta as $deletaCentroCusto) {
                            $sql = "DELETE FROM ContasAPagarXCentroCusto
                                    WHERE CAPXCCentroCusto = :iCentroCusto AND CAPXCContasAPagar = :iContaAPagar";
                            $result = $conn->prepare($sql);
                            
                            $result->execute(array(
                                ':iCentroCusto' => $deletaCentroCusto,
                                ':iContaAPagar' => $idContaAPagar
                            ));
                        }
                    }
                }else {
                    foreach($arrayControleCentroCustoSistema as $novoCentroCusto) {
                        foreach($arrayCentroCusto as $insereCentroCusto) {
                            if($insereCentroCusto['idCentroCusto'] == $novoCentroCusto) {
                                $sql = "INSERT INTO ContasAPagarXCentroCusto ( CAPXCContasAPagar, CAPXCCentroCusto, CAPXCValor, CAPXCUsuarioAtualizador, CAPXCUnidade)
                                        VALUES ( :iContasAPagar, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                                $result = $conn->prepare($sql);
    
                                $result->execute(array(
    
                                    ':iContasAPagar' => $idContaAPagar,
                                    ':iCentroCusto' => $insereCentroCusto['idCentroCusto'],
                                    ':iValor' => $insereCentroCusto['valorCentroCusto'],
                                    ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                                    ':iUnidade' => $_SESSION['UnidadeId']
                                ));
                            }
                        }
                    }
    
                    if(isset($arrayBancoDeDados)) {
                        foreach($arrayBancoDeDados as $deletaCentroCusto) {
                            $sql = "DELETE FROM ContasAPagarXCentroCusto
                                    WHERE CAPXCCentroCusto = :iCentroCusto AND CAPXCContasAPagar = :iContaAPagar";
                            $result = $conn->prepare($sql);
                            
                            $result->execute(array(
                                ':iCentroCusto' => $deletaCentroCusto,
                                ':iContaAPagar' => $idContaAPagar
                            ));
                        }
                    }
                }
            }
            
            $conn->commit();

            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Lançamento editado!!!";
            $_SESSION['msg']['tipo'] = "success";
        } catch (PDOException $e) {
            $conn->rollback();

            $_SESSION['msg']['titulo'] = "Erro";
            $_SESSION['msg']['mensagem'] = "Erro ao editar lançamento!!!";
            $_SESSION['msg']['tipo'] = "error";

            echo 'Error: ' . $e->getMessage();
            die;
        }
    } else {

        try {
            $conn->beginTransaction();

            if (isset($_POST['inputNumeroParcelas'])) {

                $numParcelas = intVal($_POST['inputNumeroParcelas']);

                $sql = "SELECT SituaId
                    FROM Situacao
                    WHERE SituaChave = 'PAGO'
                    ";
                $result = $conn->query($sql);
                $situacaoPago = $result->fetch(PDO::FETCH_ASSOC);

                $sql = "SELECT SituaId
                    FROM Situacao
                    WHERE SituaChave = 'APAGAR'
                    ";
                $result = $conn->query($sql);
                $situacaoPagar = $result->fetch(PDO::FETCH_ASSOC);

                for ($i = 1; $i <= $numParcelas; $i++) {
                    if(isset($_POST['checkboxPagamentoParcelaCheck' . $i . ''])) {
                        $sql = "INSERT INTO ContasAPagar ( CnAPaPlanoContas, CnAPaFornecedor, CnAPaContaBanco, CnAPaFormaPagamento,
                                                    CnAPaNotaFiscal, CnAPaDtEmissao, CnAPaOrdemCompra, CnAPaDescricao, CnAPaDtVencimento, CnAPaValorAPagar,
                                                    CnAPaDtPagamento, CnAPaValorPago, CnAPaObservacao, CnAPaTipoJuros, CnAPaJuros, 
                                                    CnAPaTipoDesconto, CnAPaDesconto, CnAPaStatus, CnAPaUsuarioAtualizador, CnAPaUnidade)
                                VALUES ( :iPlanoContas, :iFornecedor, :iContaBanco, :iFormaPagamento, :sNotaFiscal, :dateDtEmissao, :iOrdemCompra,
                                        :sDescricao, :dateDtVencimento, :fValorAPagar, :dateDtPagamento, :fValorPago, :sObservacao, :sTipoJuros, :fJuros, 
                                        :sTipoDesconto, :fDesconto, :iStatus, :iUsuarioAtualizador, :iUnidade)";
                        $result = $conn->prepare($sql);

                        $result->execute(array(

                            ':iPlanoContas' => $_POST['cmbPlanoContas'],
                            ':iFornecedor' => $_POST['cmbFornecedor'],
                            ':iContaBanco' => $_POST['cmbPagamentoParcelaContaBanco'],
                            ':iFormaPagamento' => $_POST['cmbPagamentoParcelaFormaPagamento'],
                            ':sNotaFiscal' => $_POST['inputNotaFiscal'],
                            ':dateDtEmissao' => $_POST['inputDataEmissao'],
                            ':iOrdemCompra' => isset($_POST['cmbOrdemCarta']) ? $_POST['cmbOrdemCarta'] : null,
                            ':sDescricao' => $_POST['inputParcelaDescricao' . $i . ''],
                            ':dateDtVencimento' => $_POST['inputDataVencimento'],
                            ':fValorAPagar' => floatval(gravaValor($_POST['inputParcelaValorAPagar' . $i . ''])),
                            ':dateDtPagamento' => $_POST['inputDataPagamento'],
                            ':fValorPago' => floatval(gravaValor($_POST['inputParcelaValorAPagar' . $i . ''])),
                            ':sObservacao' => $_POST['inputObservacao'],
                            ':sTipoJuros' => isset($_POST['cmbTipoJurosJD']) ? $_POST['cmbTipoJurosJD'] : null,
                            ':fJuros' => isset($_POST['inputJurosJD']) ? floatval(gravaValor($_POST['inputJurosJD'])) : null,
                            ':sTipoDesconto' => isset($_POST['cmbTipoDescontoJD']) ? $_POST['cmbTipoDescontoJD'] : null,
                            ':fDesconto' => isset($_POST['inputDescontoJD']) ? floatval(gravaValor($_POST['inputDescontoJD'])) : null,
                            ':iStatus' => $situacaoPago['SituaId'],
                            ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                            ':iUnidade' => $_SESSION['UnidadeId']
                        ));

                        $idContaAPagar = $conn->lastInsertId();
                        
                        $idContaAPagar = $conn->lastInsertId();

                        $registros = intval($_POST['totalRegistros']);

                        $proporcaoCentroCusto = 0;
                        //Para verificar valor do parcelamento
                        for($x=0; $x < $registros; $x++) {
                            $valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
                            $valor = str_replace(',', '.', $valor);

                            $valor = mostraValor(($valor) / $numParcelas);
                            $valor = str_replace('.', '', $valor);
                            $valor = str_replace(',', '.', $valor);

                            $proporcaoCentroCusto += $valor;
                        }

                        $proporcaoCentroCustoVerdadeiro = false;
                        $teste = 0;
                        if(floatval(gravaValor($_POST['inputParcelaValorAPagar' . $i . ''])) != $proporcaoCentroCusto) {
                            $valParcela = floatval(gravaValor($_POST['inputParcelaValorAPagar' . $i . '']));
                            $diferenca = $valParcela - $proporcaoCentroCusto;

                            $proporcaoCentroCustoVerdadeiro = true;
                        }

                        for($x=0; $x < $registros; $x++){
                            $keyNome = 'inputCentroNome-'.$x;
                            $keyId = 'inputIdCentro-'.$x;
                            $centroCusto = $_POST[$keyId];
                            $valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
                            $valor = str_replace(',', '.', $valor);

                            if($proporcaoCentroCustoVerdadeiro) {
                                $soma = 0;
                                $soma = ($x == 0) ? $diferenca : 0;
                                
                                $valor = mostraValor(($valor) / $numParcelas);
                                $valor = str_replace('.', '', $valor);
                                $valor = str_replace(',', '.', $valor);
                                $valor += $soma;
                                
                            }else {
                                $valor = mostraValor(($valor) / $numParcelas);
                                $valor = str_replace('.', '', $valor);
                                $valor = str_replace(',', '.', $valor);
                            }

                            $sql = "INSERT INTO ContasAPagarXCentroCusto ( CAPXCContasAPagar, CAPXCCentroCusto, CAPXCValor, CAPXCUsuarioAtualizador, CAPXCUnidade)
                                    VALUES ( :iContasAPagar, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                            $result = $conn->prepare($sql);

                            $result->execute(array(

                                ':iContasAPagar' => $idContaAPagar,
                                ':iCentroCusto' => $centroCusto,
                                ':iValor' => $valor,
                                ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                                ':iUnidade' => $_SESSION['UnidadeId']
                            ));
                        }
                    }else {
                        $sql = "INSERT INTO ContasAPagar ( CnAPaPlanoContas, CnAPaFornecedor, CnAPaContaBanco, CnAPaFormaPagamento,
                                                    CnAPaNotaFiscal, CnAPaDtEmissao, CnAPaOrdemCompra, CnAPaDescricao, CnAPaDtVencimento, CnAPaValorAPagar,
                                                    CnAPaDtPagamento, CnAPaValorPago, CnAPaObservacao, CnAPaStatus, CnAPaUsuarioAtualizador, CnAPaUnidade)
                                VALUES ( :iPlanoContas, :iFornecedor, :iContaBanco, :iFormaPagamento, :sNotaFiscal, :dateDtEmissao, :iOrdemCompra,
                                        :sDescricao, :dateDtVencimento, :fValorAPagar, :dateDtPagamento, :fValorPago, :sObservacao, :iStatus, :iUsuarioAtualizador, :iUnidade)";
                        $result = $conn->prepare($sql);

                        $result->execute(array(

                            ':iPlanoContas' => $_POST['cmbPlanoContas'],
                            ':iFornecedor' => $_POST['cmbFornecedor'],
                            ':iContaBanco' => $_POST['cmbContaBanco'],
                            ':iFormaPagamento' => $_POST['cmbFormaPagamento'],
                            ':sNotaFiscal' => $_POST['inputNotaFiscal'],
                            ':dateDtEmissao' => $_POST['inputDataEmissao'],
                            ':iOrdemCompra' => isset($_POST['cmbOrdemCarta']) ? $_POST['cmbOrdemCarta'] : null,
                            ':sDescricao' => $_POST['inputParcelaDescricao' . $i . ''],
                            ':dateDtVencimento' => $_POST['inputParcelaDataVencimento' . $i . ''],
                            ':fValorAPagar' => floatval(gravaValor($_POST['inputParcelaValorAPagar' . $i . ''])),
                            ':dateDtPagamento' => $_POST['inputDataPagamento'],
                            ':fValorPago' => null,
                            ':sObservacao' => $_POST['inputObservacao'],
                            ':iStatus' => $situacaoPagar['SituaId'],
                            ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                            ':iUnidade' => $_SESSION['UnidadeId']
                        ));

                        $idContaAPagar = $conn->lastInsertId();

                        $registros = intval($_POST['totalRegistros']);

                        $proporcaoCentroCusto = 0;
                        //Para verificar valor do parcelamento
                        for($x=0; $x < $registros; $x++) {
                            $valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
                            $valor = str_replace(',', '.', $valor);

                            $valor = mostraValor(($valor) / $numParcelas);
                            $valor = str_replace('.', '', $valor);
                            $valor = str_replace(',', '.', $valor);

                            $proporcaoCentroCusto += $valor;
                        }

                        $proporcaoCentroCustoVerdadeiro = false;
                        $teste = 0;
                        if(floatval(gravaValor($_POST['inputParcelaValorAPagar' . $i . ''])) != $proporcaoCentroCusto) {
                            $valParcela = floatval(gravaValor($_POST['inputParcelaValorAPagar' . $i . '']));
                            $diferenca = $valParcela - $proporcaoCentroCusto;

                            $proporcaoCentroCustoVerdadeiro = true;
                        }

                        for($x=0; $x < $registros; $x++){
                            $keyNome = 'inputCentroNome-'.$x;
                            $keyId = 'inputIdCentro-'.$x;
                            $centroCusto = $_POST[$keyId];
                            $valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
                            $valor = str_replace(',', '.', $valor);

                            if($proporcaoCentroCustoVerdadeiro) {
                                $soma = 0;
                                $soma = ($x == 0) ? $diferenca : 0;
                                
                                $valor = mostraValor(($valor) / $numParcelas);
                                $valor = str_replace('.', '', $valor);
                                $valor = str_replace(',', '.', $valor);
                                $valor += $soma;
                                
                            }else {
                                $valor = mostraValor(($valor) / $numParcelas);
                                $valor = str_replace('.', '', $valor);
                                $valor = str_replace(',', '.', $valor);
                            }

                            $sql = "INSERT INTO ContasAPagarXCentroCusto ( CAPXCContasAPagar, CAPXCCentroCusto, CAPXCValor, CAPXCUsuarioAtualizador, CAPXCUnidade)
                                    VALUES ( :iContasAPagar, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                            $result = $conn->prepare($sql);

                            $result->execute(array(

                                ':iContasAPagar' => $idContaAPagar,
                                ':iCentroCusto' => $centroCusto,
                                ':iValor' => $valor,
                                ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                                ':iUnidade' => $_SESSION['UnidadeId']
                            ));
                        }
                    }
                }
            } else {
                $pagamentoParcial = false;
                if (isset($_POST['inputPagamentoParcial'])) {
                    if (intval($_POST['inputPagamentoParcial']) != 0) {
                        $sql = "SELECT SituaId
                                FROM Situacao
                                WHERE SituaChave = 'APAGAR'
                         ";
                        $result = $conn->query($sql);
                        $situacaoPagamentoParcial = $result->fetch(PDO::FETCH_ASSOC);

                        $sql = "INSERT INTO ContasAPagar ( CnAPaPlanoContas, CnAPaFornecedor, CnAPaContaBanco, CnAPaFormaPagamento,
                                                      CnAPaNotaFiscal, CnAPaDtEmissao, CnAPaOrdemCompra, CnAPaDescricao, CnAPaDtVencimento, CnAPaValorAPagar,
                                                      CnAPaDtPagamento, CnAPaValorPago, CnAPaObservacao, CnAPaTipoJuros, CnAPaJuros, 
                                                      CnAPaTipoDesconto, CnAPaDesconto, CnAPaStatus, CnAPaUsuarioAtualizador, CnAPaUnidade)
                                VALUES ( :iPlanoContas, :iFornecedor, :iContaBanco, :iFormaPagamento, :sNotaFiscal, :dateDtEmissao, :iOrdemCompra,
                                        :sDescricao, :dateDtVencimento, :fValorAPagar, :dateDtPagamento, :fValorPago, :sObservacao, :sTipoJuros, :fJuros, 
                                        :sTipoDesconto, :fDesconto, :iStatus, :iUsuarioAtualizador, :iUnidade)";
                        $result = $conn->prepare($sql);

                        $result->execute(array(
                            ':iPlanoContas' => $_POST['cmbPlanoContas'],
                            ':iFornecedor' => $_POST['cmbFornecedor'],
                            ':iContaBanco' => $_POST['cmbContaBanco'],
                            ':iFormaPagamento' => $_POST['cmbFormaPagamento'],
                            ':sNotaFiscal' => $_POST['inputNotaFiscal'],
                            ':dateDtEmissao' => $_POST['inputDataEmissao'],
                            ':iOrdemCompra' => isset($_POST['cmbOrdemCarta']) ? $_POST['cmbOrdemCarta'] : null,
                            ':sDescricao' => $_POST['inputDescricao'],
                            ':dateDtVencimento' => $_POST['inputDataVencimento'],
                            ':fValorAPagar' => $_POST['inputPagamentoParcial'],
                            ':dateDtPagamento' => $_POST['inputDataPagamento'],
                            ':fValorPago' => null,
                            ':sObservacao' => $_POST['inputObservacao'],
                            ':sTipoJuros' => isset($_POST['cmbTipoJurosJD']) ? $_POST['cmbTipoJurosJD'] : null,
                            ':fJuros' => isset($_POST['inputJurosJD']) ? floatval(gravaValor($_POST['inputJurosJD'])) : null,
                            ':sTipoDesconto' => isset($_POST['cmbTipoDescontoJD']) ? $_POST['cmbTipoDescontoJD'] : null,
                            ':fDesconto' => isset($_POST['inputDescontoJD']) ? floatval(gravaValor($_POST['inputDescontoJD'])) : null,
                            ':iStatus' => $situacaoPagamentoParcial['SituaId'],
                            ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                            ':iUnidade' => $_SESSION['UnidadeId']
                        ));
                        
                        $idContaAPagarParcial = $conn->lastInsertId();
                        $pagamentoParcial = true;
                    }

                }

                if (isset($_POST['inputValorTotalPago'])) {
                    $sql = "SELECT SituaId
                            FROM Situacao
                            WHERE SituaChave = 'PAGO'
                        ";
                    $result = $conn->query($sql);
                    $situacao = $result->fetch(PDO::FETCH_ASSOC);
                } else {
                    $sql = "SELECT SituaId
                            FROM Situacao
                            WHERE SituaChave = 'APAGAR'
                        ";
                    $result = $conn->query($sql);
                    $situacao = $result->fetch(PDO::FETCH_ASSOC);
                }

                $sql = "INSERT INTO ContasAPagar ( CnAPaPlanoContas, CnAPaFornecedor, CnAPaContaBanco, CnAPaFormaPagamento, 
                                              CnAPaNotaFiscal, CnAPaDtEmissao, CnAPaOrdemCompra, CnAPaDescricao, CnAPaDtVencimento, CnAPaValorAPagar,
                                              CnAPaDtPagamento, CnAPaValorPago, CnAPaObservacao, CnAPaTipoJuros, CnAPaJuros, 
                                              CnAPaTipoDesconto, CnAPaDesconto, CnAPaStatus, CnAPaUsuarioAtualizador, CnAPaUnidade)
                        VALUES ( :iPlanoContas, :iFornecedor, :iContaBanco, :iFormaPagamento, :sNotaFiscal, :dateDtEmissao, :iOrdemCompra,
                                :sDescricao, :dateDtVencimento, :fValorAPagar, :dateDtPagamento, :fValorPago, :sObservacao, :sTipoJuros, :fJuros, 
                                :sTipoDesconto, :fDesconto, :iStatus, :iUsuarioAtualizador, :iUnidade)";
                $result = $conn->prepare($sql);

                $result->execute(array(

                    ':iPlanoContas' => $_POST['cmbPlanoContas'],
                    ':iFornecedor' => $_POST['cmbFornecedor'],
                    ':iContaBanco' => $_POST['cmbContaBanco'],
                    ':iFormaPagamento' => $_POST['cmbFormaPagamento'],
                    ':sNotaFiscal' => $_POST['inputNotaFiscal'],
                    ':dateDtEmissao' => $_POST['inputDataEmissao'],
                    ':iOrdemCompra' => isset($_POST['cmbOrdemCarta']) ? $_POST['cmbOrdemCarta'] : null,
                    ':sDescricao' => $_POST['inputDescricao'],
                    ':dateDtVencimento' => $_POST['inputDataVencimento'],
                    ':fValorAPagar' => floatval(gravaValor($_POST['inputValor'])),
                    ':dateDtPagamento' => $_POST['inputDataPagamento'],
                    ':fValorPago' => isset($_POST['inputValorTotalPago']) ? floatval(gravaValor($_POST['inputValorTotalPago'])) : null,
                    ':sObservacao' => $_POST['inputObservacao'],
                    ':sTipoJuros' => isset($_POST['cmbTipoJurosJD']) ? $_POST['cmbTipoJurosJD'] : null,
                    ':fJuros' => isset($_POST['inputJurosJD']) ? floatval(gravaValor($_POST['inputJurosJD'])) : null,
                    ':sTipoDesconto' => isset($_POST['cmbTipoDescontoJD']) ? $_POST['cmbTipoDescontoJD'] : null,
                    ':fDesconto' => isset($_POST['inputDescontoJD']) ? floatval(gravaValor($_POST['inputDescontoJD'])) : null,
                    ':iStatus' => $situacao['SituaId'],
                    ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                    ':iUnidade' => $_SESSION['UnidadeId']
                ));

                $idContaAPagar = $conn->lastInsertId();

                if($pagamentoParcial) {

                    $valorPagoParcialmente = floatval(gravaValor($_POST['inputValor']));
                    $valorAPagarParcialmente = $_POST['inputPagamentoParcial'];
                    $totalParcialmente = $valorPagoParcialmente + $valorAPagarParcialmente;
                    
                    $percentualAPagarParcialmente = ($valorAPagarParcialmente * 100) / $totalParcialmente;
                    $percentualPagoParcialmente = ($valorPagoParcialmente * 100) / $totalParcialmente;

                    $registros = intval($_POST['totalRegistros']);
                    for($x=0; $x < $registros; $x++){
                        //$keyNome = 'inputCentroNome-'.$x;
                        $keyId = 'inputIdCentro-'.$x;
                        $centroCusto = $_POST[$keyId];
                        $valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
                        $valor = str_replace(',', '.', $valor);

                        $valor = ($percentualAPagarParcialmente / 100) * $valor;
                        $valor;

                        $sql = "INSERT INTO ContasAPagarXCentroCusto ( CAPXCContasAPagar, CAPXCCentroCusto, CAPXCValor, CAPXCUsuarioAtualizador, CAPXCUnidade)
                                VALUES ( :iContasAPagar, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                        $result = $conn->prepare($sql);
    
                        $result->execute(array(
    
                            ':iContasAPagar' => $idContaAPagarParcial,
                            ':iCentroCusto' => $centroCusto,
                            ':iValor' => $valor,
                            ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                            ':iUnidade' => $_SESSION['UnidadeId']
                        ));
                    }

                    for($x=0; $x < $registros; $x++){
                        //$keyNome = 'inputCentroNome-'.$x;
                        $keyId = 'inputIdCentro-'.$x;
                        $centroCusto = $_POST[$keyId];
                        $valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
                        $valor = str_replace(',', '.', $valor);
    
                        $valor = ($percentualPagoParcialmente / 100) * $valor;
                        $valor;

                        $sql = "INSERT INTO ContasAPagarXCentroCusto ( CAPXCContasAPagar, CAPXCCentroCusto, CAPXCValor, CAPXCUsuarioAtualizador, CAPXCUnidade)
                                VALUES ( :iContasAPagar, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                        $result = $conn->prepare($sql);
    
                        $result->execute(array(
                            ':iContasAPagar' => $idContaAPagar,
                            ':iCentroCusto' => $centroCusto,
                            ':iValor' => $valor,
                            ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                            ':iUnidade' => $_SESSION['UnidadeId']
                        ));
                    }
                }else {
                    $registros = intval($_POST['totalRegistros']);
                    for($x=0; $x < $registros; $x++){
                        //$keyNome = 'inputCentroNome-'.$x;
                        $keyId = 'inputIdCentro-'.$x;
                        $valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
                        $valor = str_replace(',', '.', $valor);
    
                        $centroCusto = $_POST[$keyId];
                        $valor;
    
                        $sql = "INSERT INTO ContasAPagarXCentroCusto ( CAPXCContasAPagar, CAPXCCentroCusto, CAPXCValor, CAPXCUsuarioAtualizador, CAPXCUnidade)
                                VALUES ( :iContasAPagar, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                        $result = $conn->prepare($sql);
    
                        $result->execute(array(
                            ':iContasAPagar' => $idContaAPagar,
                            ':iCentroCusto' => $centroCusto,
                            ':iValor' => $valor,
                            ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                            ':iUnidade' => $_SESSION['UnidadeId']
                        ));
                    }
                }
            }

            $conn->commit();

            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Lançamento incluído!!!";
            $_SESSION['msg']['tipo'] = "success";
        } catch (PDOException $e) {
            $conn->rollback();

            $_SESSION['msg']['titulo'] = "Erro";
            $_SESSION['msg']['mensagem'] = "Erro ao incluir Lançamento!!!";
            $_SESSION['msg']['tipo'] = "error";

            echo 'Error: ' . $e->getMessage();
            die;
        }
    }

    if(isset($_POST['inputControlador'])) {
        irpara("movimentacaoFinanceiraConciliacao.php");
    }else {
        irpara("contasAPagar.php");
    }
}
//$count = count($row);

//Se estiver editando entra no IF
if (isset($_POST['inputContasAPagarId']) && $_POST['inputContasAPagarId'] != 0) {
    $sql = "SELECT CnAPaId, CnAPaMovimentacao, CnAPaPlanoContas, CnAPaFornecedor, CnAPaNotaFiscal, CnAPaDtEmissao, CnAPaDescricao, CnAPaDtVencimento, 
            CnAPaValorAPagar, CnAPaDtPagamento, CnAPaValorPago, CnAPaContaBanco, CnAPaFormaPagamento, CnAPaNumDocumento, OrComNumero, SituaChave, SituaChave 
    		FROM ContasAPagar
            LEFT JOIN OrdemCompra on OrComId = CnAPaOrdemCompra
            JOIN Situacao on SituaId = CnAPaStatus
    		WHERE CnAPaUnidade = " . $_SESSION['UnidadeId'] . " and CnAPaId = " . $_POST['inputContasAPagarId'] . "";
    $result = $conn->query($sql);
    $lancamento = $result->fetch(PDO::FETCH_ASSOC);

    if(isset($lancamento['CnAPaMovimentacao'])) {
        $sql = "SELECT MvAneArquivo
                FROM MovimentacaoAnexo
                WHERE MvAneUnidade = ". $_SESSION['UnidadeId'] ." AND MvAneMovimentacao = ".$lancamento['CnAPaMovimentacao'];
        $result = $conn->query($sql);
        $rowNotaFiscal = $result->fetch(PDO::FETCH_ASSOC);
    }

    // pesquisa o Centro de Custo
    $sqlCentroCusto = "SELECT CAPXCContasAPagar, CAPXCCentroCusto, CAPXCValor, CnCusId, CnCusCodigo, CnCusNome, CnCusNomePersonalizado
                        FROM ContasAPagarXCentroCusto
                        JOIN CentroCusto on CnCusId = CAPXCCentroCusto
                        WHERE CAPXCContasAPagar = " . $_POST['inputContasAPagarId'] . "";
    $resultCentroCusto = $conn->query($sqlCentroCusto);
    $rowCentroCusto = $resultCentroCusto->fetchAll(PDO::FETCH_ASSOC);

    $sqlLiquidacao = "SELECT CAPXCCentroCusto
                    FROM ContasAPagarXCentroCusto
                    WHERE CAPXCContasAPagar = " . $_POST['inputContasAPagarId'] . "";
    $resultItemCentroCusto = $conn->query($sqlLiquidacao);
    $itemCentroCusto = $resultItemCentroCusto->fetch(PDO::FETCH_ASSOC);
}else if(isset($_POST['inputConciliacaoId']) && $_POST['inputConciliacaoId']) {
    $sql = "SELECT CnAPaId, CnAPaMovimentacao, CnAPaPlanoContas, CnAPaFornecedor, CnAPaNotaFiscal, CnAPaDtEmissao, CnAPaDescricao, CnAPaDtVencimento, 
            CnAPaValorAPagar, CnAPaDtPagamento, CnAPaValorPago, CnAPaContaBanco, CnAPaFormaPagamento, CnAPaNumDocumento, OrComNumero, SituaChave, SituaChave
    		FROM ContasAPagar
            LEFT JOIN OrdemCompra on OrComId = CnAPaOrdemCompra
            JOIN Situacao on SituaId = CnAPaStatus
    		WHERE CnAPaUnidade = " . $_SESSION['UnidadeId'] . " and CnAPaId = " . $_POST['inputConciliacaoId'] . "";
    $result = $conn->query($sql);
    $lancamento = $result->fetch(PDO::FETCH_ASSOC);

    if(isset($lancamento['CnAPaMovimentacao'])) {
        $sql = "SELECT MvAneArquivo
                FROM MovimentacaoAnexo
                WHERE MvAneUnidade = ". $_SESSION['UnidadeId'] ." AND MvAneMovimentacao = ".$lancamento['CnAPaMovimentacao'];
        $result = $conn->query($sql);
        $rowNotaFiscal = $result->fetch(PDO::FETCH_ASSOC);
    }

    $sqlCentroCusto = "SELECT CAPXCContasAPagar, CAPXCCentroCusto, CAPXCValor, CnCusId, CnCusCodigo, CnCusNome, CnCusNomePersonalizado
                        FROM ContasAPagarXCentroCusto
                        JOIN CentroCusto on CnCusId = CAPXCCentroCusto
                        WHERE CAPXCContasAPagar = " . $lancamento['CnAPaId'] . "";
    $resultCentroCusto = $conn->query($sqlCentroCusto);
    $rowCentroCusto = $resultCentroCusto->fetchAll(PDO::FETCH_ASSOC);

    $sqlLiquidacao = "SELECT CAPXCCentroCusto
                    FROM ContasAPagarXCentroCusto
                    WHERE CAPXCContasAPagar = " . $lancamento['CnAPaId'] . "";
    $resultItemCentroCusto = $conn->query($sqlLiquidacao);
    $itemCentroCusto = $resultItemCentroCusto->fetch(PDO::FETCH_ASSOC);
}

$sql = "SELECT ParamEmpresaPublica
        FROM Parametro
        WHERE ParamEmpresa = " . $_SESSION['EmpreId'];
$result = $conn->query($sql);
$rowParametro = $result->fetch(PDO::FETCH_ASSOC);

$empresaPublica = ($rowParametro['ParamEmpresaPublica'] == 1) ? true : false;
$dataInicio = date("Y-m-d");

$visibilidadeResumoFinanceiro = isset($_SESSION['ResumoFinanceiro']) && $_SESSION['ResumoFinanceiro'] ? 'sidebar-right-visible' : ''; 
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Lamparinas | Relatório de Movimentação</title>

    <?php include_once("head.php"); ?>

    <!-- Validação -->
    <script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
    <script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
    <script src="global_assets/js/demo_pages/form_validation.js"></script>
    <!--/ Validação -->

    <!-- Theme JS files -->
    <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

    <script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
    <script src="global_assets/js/demo_pages/form_select2.js"></script>
    <script src="global_assets/js/demo_pages/form_layouts.js"></script>
    <script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

    <script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
    <script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
    <!-- /theme JS files -->

    <!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
    <script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {

            let styleJurosDescontos = ''

            var input = document.getElementById('inputDataVencimento');
            input.addEventListener('change', function() {
                var agora = new Date();
                var escolhida = new Date(this.value);
                if (escolhida < agora) {
                    this.value = [agora.getFullYear(), agora.getMonth() + 1, agora.getDate()].map(v => v < 10 ? '0' + v : v).join('-');
                }
            });            

            function gerarParcelas(parcelas, valorTotal, dataVencimento, periodicidade) {
                $("#parcelasContainer").html("")
                let descricao = $("#inputDescricao").val()

                let valorParcela = float2moeda(parseFloat(valorTotal) / parcelas)
                console.log(dataVencimento)
                let numeroParcelas = `<input type="hidden" value="${parcelas}" name="inputNumeroParcelas">`
                // let dataVencimento = dataVencimento
                $("#parcelasContainer").append(numeroParcelas)
                let cont = 0
                let iAnterior = 0
                for (let i = 1; i <= parcelas; i++) {

                    let novaDataVencimento = ''

                    let somadorPeriodicidade = periodicidade == 1 ? 0 : periodicidade == 2 ? 2 :
                        periodicidade == 3 ? 3 : 6
                    if (i > 1) {
                        let dataArray = dataVencimento.split("-")
                        let mes = parseInt(dataArray[1])
                        let novoMes = 0
                        let ano = parseInt(dataArray[0])

                        novoMes = mes + i > 9 ? (mes + (i - 1)).toString() : `0${(mes + (i - 1)).toString()}`

                        if (novoMes > 12) {
                            cont++
                            ano = ano + 1
                            novoMes = cont > 9 ? cont : `0${cont}`
                        }

                        dataArray[1] = novoMes
                        dataArray[0] = ano
                        novaDataVencimento = `${dataArray[0]}-${dataArray[1]}-${dataArray[2]}`
                    } else {
                        novaDataVencimento = dataVencimento
                    }

                    let elem = `<div class="d-flex flex-row justify-content-center">
                                    <p class="col-1 p-2 pl-4">${i}</p>
                                    <div class="form-group col-5 p-2">
                                        <input type="text" class="form-control" id="inputParcelaDescricao${i}" name="inputParcelaDescricao${i}" value="${descricao} ${i}/${parcelas}">
                                    </div>
                                    <div class="form-group col-3 p-2">
                                        <input type="date" class="form-control" id="inputParcelaDataVencimento${i}" name="inputParcelaDataVencimento${i}" value="${novaDataVencimento}">
                                    </div>
                                    <div class="form-group col-3 p-2">
                                        <input type="text" class="form-control" id="inputParcelaValorAPagar${i}" name="inputParcelaValorAPagar${i}" value="${valorParcela}">
                                    </div> 
                                </div>`

                    $("#parcelasContainer").append(elem)
                }
            }


            function parcelamento() {
                $('#gerarParcelas').on('click', (e) => {
                    e.preventDefault()
                    let parcelas = $("#cmbParcelas").val()
                    let valorTotal = $("#valorTotal").val().replace(".", "").replace(",", ".")
                    let dataVencimento = $("#inputDataVencimento").val()
                    let periodicidade = $("#cmbPeriodicidade").val()

                    gerarParcelas(parcelas, valorTotal, dataVencimento, periodicidade)
                    pagarParcelas(parcelas, valorTotal, dataVencimento, periodicidade)
                })
            }
            parcelamento()

            function limparJurosDescontos() {
                $("#inputVencimentoJD").val("")
                $("#inputValorAPagarJD").val("")
                $("#inputJurosJD").val("")
                $("#inputDescontoJD").val("")
                $("#inputDataPagamentoJD").val("")
                $("#inputValorTotalAPagarJD").val("")
            }

            function preencherJurosDescontos() {

                $valorAPagar = $("#inputValor").val()
                $dataVencimento = $("#inputDataVencimento").val()
                $dataPagamento = $("#inputDataPagamento").val()
                $valorTotalPago = $("#inputValorTotalPago").val()

                $("#inputVencimentoJD").val($dataVencimento)
                $("#inputValorAPagarJD").val($valorAPagar)
                $("#inputDataPagamentoJD").val($dataPagamento)

            }

            function habilitarPagamento() {

                $("#habilitarPagamento").on('click', (e) => {
                    e.preventDefault()

                    let parcelas = $("#inputParcelaDescricao2").val()

                    if(parcelas) {
                        let parcelas = $("#cmbParcelas").val()
                        let valorTotal = $("#valorTotal").val().replace(".", "").replace(",", ".")
                        let dataVencimento = $("#inputDataVencimento").val()
                        let periodicidade = $("#cmbPeriodicidade").val()

                        $('#pageModalPagamentoParcelar').fadeIn(200);
                    }else {
                        if (!$("#habilitarPagamento").hasClass('clicado')) {
                            $valorTotalPago = $("#inputValor").val()
                            $dataPagamento = new Date
                            $dia = parseInt($dataPagamento.getDate()) <= 9 ?
                                `0${parseInt($dataPagamento.getDate())}` : parseInt($dataPagamento.getDate())
                            $mes = parseInt($dataPagamento.getMonth()) + 1 <= 9 ?
                                `0${parseInt($dataPagamento.getMonth()) + 1}` : parseInt($dataPagamento.getMonth()) + 1
                            $ano = $dataPagamento.getFullYear()
    
                            $fullDataPagamento = `${$ano}-${$mes}-${$dia}`
    
                            $("#inputDataPagamento").val($fullDataPagamento)
                            $("#inputValorTotalPago").val($valorTotalPago).removeAttr('disabled')
    
                            styleJurosDescontos = document.getElementById('jurusDescontos').style
    
                            document.getElementById('jurusDescontos').style = "";
    
                            $("#habilitarPagamento").addClass('clicado')
                            $("#habilitarPagamento").html('Desabilitar Pagamento')
                            preencherJurosDescontos()
    
                            $("#camposPagamento").fadeIn(200);
    
                        } else {
    
                            $("#inputDataPagamento").val("")
                            $("#inputValorTotalPago").val("")
                            $("#inputValorTotalPago").attr('disabled', '')
                            document.getElementById('jurusDescontos').style =
                                "color: currentColor; cursor: not-allowed; opacity: 0.5; text-decoration: none; pointer-events: none;";
    
                            $("#habilitarPagamento").removeClass('clicado')
                            $("#habilitarPagamento").html('Habilitar Pagamento')
                            limparJurosDescontos()
    
                            $("#camposPagamento").fadeOut(200);
                        }
                    }
                })
                $("#jurusDescontos")
            }
            habilitarPagamento()

            function pagarParcelas(parcelas, valorTotal, dataVencimento, periodicidade) {
                $("#pagamentoParcelasContainer").html("")
                $("#inputPagamentoParcelaValorTotal").val(float2moeda(parseFloat(valorTotal)))
                $("#cmbPagamentoParcelaParcelas").val(parcelas).change()
                
                let descricao = $("#inputDescricao").val()

                let valorParcela = float2moeda(parseFloat(valorTotal) / parcelas)
                let cont = 0
                let iAnterior = 0
                for (let i = 1; i <= parcelas; i++) {

                    let novaDataVencimento = ''

                    let somadorPeriodicidade = periodicidade == 1 ? 0 : periodicidade == 2 ? 2 :
                        periodicidade == 3 ? 3 : 6
                    if (i > 1) {
                        let dataArray = dataVencimento.split("-")
                        let mes = parseInt(dataArray[1])
                        let novoMes = 0
                        let ano = parseInt(dataArray[0])

                        novoMes = mes + i > 9 ? (mes + (i - 1)).toString() : `0${(mes + (i - 1)).toString()}`

                        if (novoMes > 12) {
                            cont++
                            ano = ano + 1
                            novoMes = cont > 9 ? cont : `0${cont}`
                        }

                        dataArray[1] = novoMes
                        dataArray[0] = ano
                        novaDataVencimento = `${dataArray[0]}-${dataArray[1]}-${dataArray[2]}`
                    } else {
                        novaDataVencimento = dataVencimento
                    }

                    let elem = `<div class="d-flex flex-row justify-content-center">
                                    <p class="col-1 p-2 pl-4">${i}</p>
                                    <div class="form-group col-5 p-2">
                                        <input type="text" class="form-control" id="inputPagamentoParcelaDescricao${i}" name="inputPagamentoParcelaDescricao${i}" value="${descricao} ${i}/${parcelas}">
                                    </div>
                                    <div class="form-group col-3 p-2">
                                        <input type="date" class="form-control" id="inputPagamentoParcelaDataVencimento${i}" name="inputPagamentoParcelaDataVencimento${i}" value="${novaDataVencimento}">
                                    </div>
                                    <div class="form-group col-2 p-2">
                                        <input type="text" class="form-control" id="inputPagamentoParcelaValorAPagar${i}" name="inputPagamentoParcelaValorAPagar${i}" value="${valorParcela}">
                                    </div>
                                    <div class="form-group col-1 p-2 text-center" style="margin-top: 2%;">
                                        <input type="checkbox" id="checkboxPagamentoParcelaCheck${i}"  name="checkboxPagamentoParcelaCheck${i}">
                                    </div> 
                                </div>`

                    $("#pagamentoParcelasContainer").append(elem)
                }
            }

            function modalParcelar() {

                $('#btnParcelar').on('click', (e) => {
                    e.preventDefault()
                    $('#pageModalParcelar').fadeIn(200);

                    let valorTotal = $('#inputValor').val()
                    $('#valorTotal').val(valorTotal)
                })

                $('#modalCloseParcelar').on('click', function() {
                    var menssagem = 'Parcelamento cancelado!'
                    alerta('Atenção', menssagem, 'error')

                    $('#pageModalParcelar').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                    $("#parcelasContainer").html("")
                    $("#pagamentoParcelasContainer").html("")

                    $("#habilitarPagamento").html('Habilitar Pagamento')
                })

                $("#salvarParcelas").on('click', function() {
                    $('#pageModalParcelar').fadeOut(200);
                    $('body').css('overflow', 'scroll');

                    let parcelas = $("#cmbParcelas").val()
                    if(parcelas > 1) {
                        $("#habilitarPagamento").html('Pagamento de parcelas')
                        $("#inputValorTotalPago").val('')
                    }else {
                        $("#habilitarPagamento").html('Habilitar Pagamento')
                    }
                })

                $('#modalClosePagamentoParcela').on('click', function() {
                    var menssagem = 'Parcelamento cancelado!'
                    alerta('Atenção', menssagem, 'error')

                    $('#pageModalPagamentoParcelar').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                    $("#parcelasContainer").html("")
                    $("#pagamentoParcelasContainer").html("")

                    $("#habilitarPagamento").html('Habilitar Pagamento')
                })

                $("#salvarParcelasPagamentoParcela").on('click', function() {
                    let parcelas = $("#cmbParcelas").val()
                    let controle = false
                    let totalParcelaPagamento = 0

                    for (let i = 1; i <= parcelas; i++) {
                        if($("#checkboxPagamentoParcelaCheck"+i).is(":checked")) {
                            totalParcelaPagamento += parseFloat($("#inputPagamentoParcelaValorAPagar"+i).val().replace(".", "").replace(",", "."))
                            controle = true
                        }
                    }

                    if(controle) {
                        if($('#cmbPagamentoParcelaContaBanco').val() == '') {
                            $('#cmbPagamentoParcelaContaBanco').focus()
                            var menssagem = 'Por favor informe uma Conta/Banco!'
                            alerta('Atenção', menssagem, 'error')
                            
                            return false
                        }
    
                        if($('#cmbPagamentoParcelaFormaPagamento').val() == '') {
                            $('#cmbPagamentoParcelaFormaPagamento').focus()
                            var menssagem = 'Por favor informe uma Conta/Banco!'
                            alerta('Atenção', menssagem, 'error')
                            
                            return false
                        }

                        $dataPagamento = new Date
                        $dia = parseInt($dataPagamento.getDate()) <= 9 ?
                            `0${parseInt($dataPagamento.getDate())}` : parseInt($dataPagamento.getDate())
                        $mes = parseInt($dataPagamento.getMonth()) + 1 <= 9 ?
                            `0${parseInt($dataPagamento.getMonth()) + 1}` : parseInt($dataPagamento.getMonth()) + 1
                        $ano = $dataPagamento.getFullYear()

                        $fullDataPagamento = `${$ano}-${$mes}-${$dia}`
                        
                        $("#inputDataPagamento").val($fullDataPagamento)
                        $("#inputValorTotalPago").val(float2moeda(totalParcelaPagamento))
                    }

                    $('#pageModalPagamentoParcelar').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                })
            }
            modalParcelar()

            function modalJurosDescontos() {
                $('#jurusDescontos').on('click', (e) => {
                    e.preventDefault()
                    $('#pageModalJurosDescontos').fadeIn(200);
                    $('.cardJuDes').css('width', '500px').css('margin', '0px auto')

                    let dataVencimento = $("#inputDataVencimento").val()
                    let valor = $("#inputValor").val()

                    $("#inputValorAPagarJD").val(valor)
                    $("#inputVencimentoJD").val(dataVencimento)
                })

                let valorTotal = $('#inputValor').val()

                $('#valorTotal').val(valorTotal)

                $('#modalCloseJurosDescontos').on('click', function() {
                    $('#pageModalJurosDescontos').fadeOut(200);
                    $('body').css('overflow', 'scroll');

                    limparJurosDescontos()
                })

                $("#salvarJurosDescontos").on('click', function() {
                    $('#pageModalJurosDescontos').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                })
            }
            modalJurosDescontos()

            function calcularJuros() {
                let jurosTipo = $("#cmbTipoJurosJD").val()
                let jurosValor = $("#inputJurosJD").val()
                let juros = 0

                let valorAPagar = $("#inputValorAPagarJD").val()

                if (jurosTipo == 'P') {
                    juros = (valorAPagar * (jurosValor / 100))
                } else {
                    juros = jurosValor
                }

                let descontoTipo = $("#cmbTipoDescontoJD").val()
                let descontoValor = $("#inputDescontoJD").val()
                let desconto = 0

                if (descontoTipo == 'P') {
                    desconto = (valorAPagar * (descontoValor / 100))
                } else {
                    desconto = descontoValor
                }

                let valorTotal = 0


                valorTotal = ((parseFloat(valorAPagar) + parseFloat(juros)) - parseFloat(desconto))

                $("#inputValorTotalAPagarJD").val(float2moeda(valorTotal))
                $("#inputValorTotalPago").val(float2moeda(valorTotal))

            }

            $("#inputJurosJD").keyup(() => {
                calcularJuros()
            })
            $("#inputDescontoJD").keyup(() => {
                calcularJuros()
            })
            $("#cmbTipoJurosJD").change(() => {
                calcularJuros()
            })
            $("#cmbTipoDescontoJD").change(() => {
                calcularJuros()
            })

            function pagamento() {
                if($('#inputValor').val() == '') {
                    $('#inputValor').focus();
                    var menssagem = 'Por favor informe um valor a pagar e em seguida o centro de custo!'
                    alerta('Atenção', menssagem, 'error')
                    
                    return false
                }
                
                if($('#cmbCentroCusto').val() == '') {
                    var menssagem = 'Por favor informe o centro de custo !'
                    alerta('Atenção', menssagem, 'error')
                    
                    return false
                }

                if(!valorMaiorQZero())
                    return false

                var response = calculaValorTotal()
                if(!response.status){
                    var menssagem = 'Os valores dos centros de custos devem bater com o total do Valor a pagar (R$ '+parseFloat(response.val).toFixed(2).replace('.', ',')+') !'
                    alerta('Atenção', menssagem, 'error')
                    
                    return false
                }

                //Para o formulário fazer a leitura dos campos desabilitados
                $('#inputDataEmissao').prop('disabled', false);
                $('#cmbFornecedor').prop('disabled', false);
                $('#cmbPlanoContas').prop('disabled', false);
                $('#inputOrdemCompra').prop('disabled', false);
                $('#inputNotaFiscal').prop('disabled', false);
                $('#inputDataVencimento').prop('disabled', false);
                $('#inputValor').prop('disabled', false);

                let valorTotal = $('#inputValor').val()
                let valorPago = $('#inputValorTotalPago').val()

                let valorTotalf = parseFloat(valorTotal.replace(".", "").replace(",", "."))
                let valorPagof = parseFloat(valorPago.replace(".", "").replace(",", "."))
                let valorRestante = (valorTotalf - valorPagof)

                let planoContas = $("#cmbPlanoContas").val()
                let cmbFornecedor = $("#cmbFornecedor").val()
                let inputDescricao = $("#inputDescricao").val()
                let cmbContaBanco = $("#cmbContaBanco").val()
                let cmbFormaPagamento = $("#cmbFormaPagamento").val()
                //let inputNumeroDocumento = $("#inputNumeroDocumento").val()

                if ($("#habilitarPagamento").hasClass('clicado')) {
                    $("#cmbContaBanco").prop('required', true)
                    $("#cmbFormaPagamento").prop('required', true)
                }
                // && cmbContaBanco != '' && cmbFormaPagamento != '' && inputNumeroDocumento != ''
                if (planoContas != '' && cmbFornecedor != '' && inputDescricao != '') {
                    let parcelas = $("#cmbParcelas").val()
                        
                    if(parcelas > 1) {
                        $("#lancamento").submit()
                    }else if (valorPagof < valorTotalf && valorPagof) {
                        if($('#cmbContaBanco').val() == '') {
                            $("#cmbContaBanco").focus()
                            var menssagem = 'Por favor informe um banco !'
                            alerta('Atenção', menssagem, 'error')
                            
                            return false
                        }

                        if($('#cmbFormaPagamento').val() == '') {
                            $("#cmbFormaPagamento").focus()
                            var menssagem = 'Por favor informe uma forma de pagamento !'
                            alerta('Atenção', menssagem, 'error')
                            
                            return false
                        }

                        $("#inputPagamentoParcial").val(valorRestante)
                        $('#inputValor').val(valorPago)

                        // $dataPagamento = $("#inputDataPagamento").val()
                        // $valorTotalPago = $("#inputValorTotalPago").val()
                        if ($("#habilitarPagamento").hasClass('clicado')) {
                            $("#cmbContaBanco").prop('required', true)
                            $("#cmbFormaPagamento").prop('required', true)

                            if(!confirmaExclusao(document.lancamento,
                                "O valor pago é menor que o valor total da conta. Será gerado uma nova conta com o valor restante. Deseja continuar?",
                                'contasAPagarNovoLancamento.php')) {
                                
                                $("#inputPagamentoParcial").val(valorRestante)
                                $('#inputValor').val(1000)
                            }

                        } else {
                            if(!confirmaExclusao(document.lancamento,
                                "O valor pago é menor que o valor total da conta. Será gerado uma nova conta com o valor restante. Deseja continuar?",
                                'contasAPagarNovoLancamento.php')) {
                                
                                $("#inputPagamentoParcial").val(valorRestante)
                                $('#inputValor').val(1000)
                            }
                        }

                        document.lancamento.submit()
                    } else {
                        if ($("#habilitarPagamento").hasClass('clicado')) {

                            $("#cmbContaBanco").prop('required', true)
                            $("#cmbFormaPagamento").prop('required', true)

                            $("#lancamento").submit()
                        } else {
                            $("#cmbContaBanco").prop('required', false)
                            $("#cmbFormaPagamento").prop('required', false)
                            $("#lancamento").submit()
                        }
                    }
                } else {
                    if ($("#habilitarPagamento").hasClass('clicado')) {

                        $("#cmbContaBanco").prop('required', true)
                        $("#cmbFormaPagamento").prop('required', true)

                        $("#lancamento").submit()
                    } else {
                        $("#cmbContaBanco").prop('required', false)
                        $("#cmbFormaPagamento").prop('required', false)
                        $("#lancamento").submit()
                    }
                }

                let empresaPublica = "<?php echo $empresaPublica; ?>";

                if(empresaPublica) {
                    $('#cmbPlanoContas').prop('disabled', true);
                }
                
                $('#inputDataEmissao').prop('disabled', true);
                $('#cmbFornecedor').prop('disabled', true);
                $('#inputOrdemCompra').prop('disabled', true);
                $('#inputNotaFiscal').prop('disabled', true);
                $('#inputDataVencimento').prop('disabled', true);
                $('#inputValor').prop('disabled', true);
            }

            $("#salvar").on('click', (e) => {
                e.preventDefault()
                pagamento()
            })

            if ($('#inputValor').val() == '') {
                document.getElementById('btnParcelar').style =
                "color: currentColor; cursor: not-allowed; opacity: 0.5; text-decoration: none; pointer-events: none; margin-top: 5px";
            }else{
                $("#valorAPagarCentroCusto").html('<h6><span class="badge bg-secondary badge-pill p-2" style="font-size: 100%;">R$ '+$('#inputValor').val()+'</span></h6>')
            }

            $("#inputValor").on('input', function(element){
                if($(this).val() == ''){
                    $("#valorAPagarCentroCusto").html('')

                    document.getElementById('btnParcelar').style =
                    "color: currentColor; cursor: not-allowed; opacity: 0.5; text-decoration: none; pointer-events: none; margin-top: 5px";
                }else{
                    document.getElementById('btnParcelar').style = "margin-top: 5px";
                }
            });

            $("#inputValor").blur(function(){
                $("#valorAPagarCentroCusto").html('<h6><span class="badge bg-secondary badge-pill p-2" style="font-size: 100%;">R$ '+$('#inputValor').val()+'</span></h6>')

                let parcelas = $("#cmbParcelas").val()
                if(parcelas > 1) {
                    var menssagem = 'Valor do parcelamento alterado! Clique em OK para confirmar'
                    alerta('Sucesso', menssagem, 'success')
                    $('#btnParcelar').click()
                    $('#gerarParcelas').click()
                }
            });
          
            function centroCusto() {
                $("#centroCusto").on('click', (e) => {
                    e.preventDefault()
                    if($('#inputValor').val() == '') {
                        var menssagem = 'Por favor informe um valor a pagar!'
                        alerta('Atenção', menssagem, 'error')
                    }else {
                        e.preventDefault()
                        $('#pageCentroCusto').fadeIn(200);
                        $('.cardJuDes').css('width', '500px').css('margin', '0px auto')
                    }
                })

                $('#modalCloseCentroCusto').on('click', function() {
                    $('#pageCentroCusto').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                })
            }

            centroCusto();

            $('#submitForm').on('click', function(e){
                e.preventDefault();

                if(!valorMaiorQZero())
                    return false

                var response = calculaValorTotal()
                if(response.status){
                    $('#pageCentroCusto').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                } else {
                    var menssagem = 'Os valores dos centros de custos devem bater com o total do Valor a pagar (R$ '+float2moeda(parseFloat(response.val))+') !'
                    alerta('Atenção', menssagem, 'error')
                }
            })

            centroCustoExiste()

            if(centroCustoExiste()) {
                let idConta = "<?php echo isset($lancamento['CnAPaId']) ? $lancamento['CnAPaId'] : 0; ?>"
                let movimentacao = "<?php echo isset($lancamento['CnAPaMovimentacao']) ? true : false; ?>"
                let editavel = true
                if(contaSituacao()) {
                    editavel = (contaSituacao() == 'PAGO') ? false : true;
                }

                let centros = $('#cmbCentroCusto').val();
                let tipoConta = 'DESPESA'
                let HTML = ''
                let HTML_TOTAL = ''
                
                if (centros.length){
                    $.ajax({
                        method: "POST",
                        url: "filtraCentroCustoXContasRetorna.php",
                        data: { 
                            centroCustos: centros,
                            conta: idConta,
                            tipo: tipoConta
                        },
                        dataType:"json",
                        success: function(response){
                            if (response.length){
                                $('#relacaoCentroCusto').show()
                                if(editavel && !movimentacao) {
                                    HTML = HTML + `
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-lg-9">
                                            <div class="row">
                                                <div class="col-lg-1" style="min-width: 50x">
                                                    <label for=''><strong>Item</strong></label>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label for=''><strong>Código</strong></label>
                                                </div>
                                                <div class="col-lg-9">
                                                    <label for=''><strong>Centro de Custo</strong></label>
                                                </div>
                                            </div>
                                        </div>
        
                                        <div class="col-lg-2">
                                            <label for=''><strong>Valor</strong></label>
                                        </div>
        
                                        <div class="col-lg-1">
                                            <label for=''><strong>Resetar</strong></label>
                                        </div>
                                    </div>`;
                                }else {
                                    HTML = HTML + `
                                        <div class="row" style="margin-top: 8px;">
                                            <div class="col-lg-10">
                                                <div class="row">
                                                    <div class="col-lg-1" style="min-width: 50x">
                                                        <label for=''><strong>Item</strong></label>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <label for=''><strong>Código</strong></label>
                                                    </div>
                                                    <div class="col-lg-9">
                                                        <label for=''><strong>Centro de Custo</strong></label>
                                                    </div>
                                                </div>
                                            </div>
            
                                            <div class="col-lg-2">
                                                <label for=''><strong>Valor</strong></label>
                                            </div>
                                        </div>`;
                                }
                                let totalCentroCusto = 0
                                for(var x=0; x<response.length; x++){
                                    var centro = response[x]
                                    var centroCustoDescr = centro.CnCusNomePersonalizado !== null ? centro.CnCusNomePersonalizado : centro.CnCusNome;
                                    totalCentroCusto += centro.CAPXCValor

                                    $('#totalRegistros').val(response.length)

                                    if(editavel && !movimentacao) {
                                        HTML = HTML + `
                                        <div class="row" style="margin-top: 8px;">
                                            <div class="col-lg-9">
                                                <div class="row">
                                                    <div class="col-lg-1" style="min-width: 50x">
                                                        <input type="text" id="inputItem-`+x+`" name="inputItem1" class="form-control-border-off" value="`+(x+1)+`" readOnly>
                                                        <input type="hidden" id="inputIdCentro-`+x+`" name="inputIdCentro-`+x+`" value="`+centro.CnCusId+`">
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <input type="text" id="inputCentroCodigo-`+x+`" name="inputCentroCodigo-`+x+`" class="form-control-border-off" data-popup="tooltip" value="`+centro.CnCusCodigo+`" readOnly>
                                                    </div>
                                                    <div class="col-lg-9">
                                                        <input type="text" id="inputCentroNome-`+x+`" name="inputCentroNome-`+x+`" class="form-control-border-off" data-popup="tooltip" value="`+centroCustoDescr+`" readOnly>
                                                    </div>
                                                </div>
                                            </div>
            
                                            <div class="col-lg-2">
                                                <input type="" class="form-control-border Valor text-right pula" id="inputCentroValor-${x}" name="inputCentroValor-${x}" onChange="calculaValorTotal(${x})" onkeypress="pula(event)" value="`+float2moeda(centro.CAPXCValor)+`" autocomplete="off" ${editavel}>
                                            </div>
            
                                            <div class="col-sm-1 btn" style="text-align:center;" onClick="reset('inputCentroValor-${x}', 0)">
                                                <i class="icon-reset" title="Resetar"></i>
                                            </div>
                                        </div>`;
                                    }else {
                                        HTML = HTML + `
                                        <div class="row" style="margin-top: 8px;">
                                            <div class="col-lg-10">
                                                <div class="row">
                                                    <div class="col-lg-1" style="min-width: 50x">
                                                        <input type="text" id="inputItem-`+x+`" name="inputItem1" class="form-control-border-off" value="`+(x+1)+`" readOnly>
                                                        <input type="hidden" id="inputIdCentro-`+x+`" name="inputIdCentro-`+x+`" value="`+centro.CnCusId+`">
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <input type="text" id="inputCentroCodigo-`+x+`" name="inputCentroCodigo-`+x+`" class="form-control-border-off" data-popup="tooltip" value="`+centro.CnCusCodigo+`" readOnly>
                                                    </div>
                                                    <div class="col-lg-9">
                                                        <input type="text" id="inputCentroNome-`+x+`" name="inputCentroNome-`+x+`" class="form-control-border-off" data-popup="tooltip" value="`+centroCustoDescr+`" readOnly>
                                                    </div>
                                                </div>
                                            </div>
            
                                            <div class="col-lg-2">
                                                <input type="" class="form-control-border Valor text-right pula" id="inputCentroValor-${x}" name="inputCentroValor-${x}" onChange="calculaValorTotal(${x})" onkeypress="pula(event)" value="`+float2moeda(centro.CAPXCValor)+`" autocomplete="off" ${editavel} readOnly>
                                            </div>
                                        </div>`;
                                    }
                                }
                                if(editavel && !movimentacao) {
                                    HTML_TOTAL = `
                                    <div class="col-lg-7">
                                        <div class="row">
                                            <div class="col-lg-1"></div>
                                            <div class="col-lg-11"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2" style="padding-top: 5px; text-align: right;">
                                        <h5><b>Total:</b></h5>
                                    </div>
                                    <div class="col-lg-2">
                                        <input type="text" id="inputTotalGeral" name="inputTotalGeral" class="form-control-border-off text-right" value="R$ " readOnly>
                                    </div>
                                    <div class="col-lg-1 btn" style="text-align:center;" onClick="reset('all', 0)">
                                        <i class="icon-reset" title="Resetar Todos"></i>
                                    </div>
                                `
                                }else {
                                    HTML_TOTAL = `
                                        <div class="col-lg-8">
                                            <div class="row">
                                                <div class="col-lg-1"></div>
                                                <div class="col-lg-11"></div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2" style="padding-top: 5px; text-align: right;">
                                            <h5><b>Total:</b></h5>
                                        </div>
                                        <div class="col-lg-2">
                                            <input type="text" id="inputTotalGeral" name="inputTotalGeral" class="form-control-border-off text-right" value="R$ " readOnly>
                                        </div>
                                    `
                                }
                            }
                            var response = calculaValorTotal()

                            $('#centroCustoContent').html(HTML).show();
                            $('#centroCustoContentTotal').html(HTML_TOTAL).show();
                            $('#inputTotalGeral').val('R$ ' + float2moeda(response.val));
                        }})
                }else{
                    $('#centroCustoContent').html(HTML).show();
                    $('#centroCustoContentTotal').html(HTML_TOTAL).show();
                    $('#inputTotalGeral').val('R$ ' + 0);
                    $('#relacaoCentroCusto').hide()
                }
            } 

            $('#cmbCentroCusto').on('change', function(){
                var centros = $('#cmbCentroCusto').val();
                var HTML = ''
                var HTML_TOTAL = ''
                
                if (centros.length){
                    $.ajax({
                        method: "POST",
                        url: "filtraCentroCusto.php",
                        data: { centroCustos: centros },
                        dataType:"json",
                        success: function(response){
                            if (response.length){
                                $('#relacaoCentroCusto').show()
                                HTML = HTML + `
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-lg-9">
                                            <div class="row">
                                                <div class="col-lg-1" style="min-width: 50x">
                                                    <label for=''><strong>Item</strong></label>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label for=''><strong>Código</strong></label>
                                                </div>
                                                <div class="col-lg-9">
                                                    <label for=''><strong>Centro de Custo</strong></label>
                                                </div>
                                            </div>
                                        </div>
        
                                        <div class="col-lg-2">
                                            <label for=''><strong>Valor</strong></label>
                                        </div>
        
                                        <div class="col-lg-1">
                                            <label for=''><strong>Resetar</strong></label>
                                        </div>
                                    </div>`;
                                for(var x=0; x<response.length; x++){
                                    var centro = response[x]
                                    var centroCustoDescr = centro.CnCusNomePersonalizado !== null ? centro.CnCusNomePersonalizado : centro.CnCusNome;

                                    $('#totalRegistros').val(response.length)

                                    HTML = HTML + `
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-lg-9">
                                            <div class="row">
                                                <div class="col-lg-1" style="min-width: 50x">
                                                    <input type="text" id="inputItem-`+x+`" name="inputItem1" class="form-control-border-off" value="`+(x+1)+`" readOnly>
                                                    <input type="hidden" id="inputIdCentro-`+x+`" name="inputIdCentro-`+x+`" value="`+centro.CnCusId+`">
                                                </div>
                                                <div class="col-lg-2">
                                                    <input type="text" id="inputCentroCodigo-`+x+`" name="inputCentroCodigo-`+x+`" class="form-control-border-off" data-popup="tooltip" value="`+centro.CnCusCodigo+`" readOnly>
                                                </div>
                                                <div class="col-lg-9">
                                                    <input type="text" id="inputCentroNome-`+x+`" name="inputCentroNome-`+x+`" class="form-control-border-off" data-popup="tooltip" value="`+centroCustoDescr+`" readOnly>
                                                </div>
                                            </div>
                                        </div>
        
                                        <div class="col-lg-2">
                                            <input type="" class="form-control-border Valor text-right pula" id="inputCentroValor-${x}" name="inputCentroValor-${x}" onChange="calculaValorTotal(${x})" onkeypress="pula(event)" value="" autocomplete="off">
                                        </div>
        
                                        <div class="col-sm-1 btn" style="text-align:center;" onClick="reset('inputCentroValor-${x}', 0)">
                                            <i class="icon-reset" title="Resetar"></i>
                                        </div>
                                    </div>`;
                                }
                                HTML_TOTAL = `
                                    <div class="col-lg-7">
                                        <div class="row">
                                            <div class="col-lg-1"></div>
                                            <div class="col-lg-11"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2" style="padding-top: 5px; text-align: right;">
                                        <h5><b>Total:</b></h5>
                                    </div>
                                    <div class="col-lg-2">
                                        <input type="text" id="inputTotalGeral" name="inputTotalGeral" class="form-control-border-off text-right" value="R$ 0" readOnly>
                                    </div>
                                    <div class="col-lg-1 btn" style="text-align:center;" onClick="reset('all', 0)">
                                        <i class="icon-reset" title="Resetar Todos"></i>
                                    </div>
                                `
                            }
                            $('#centroCustoContent').html(HTML).show();
                            $('#centroCustoContentTotal').html(HTML_TOTAL).show();
                            $('#inputTotalGeral').val('R$ ' + 0);
                        }})
                }else{
                    $('#centroCustoContent').html(HTML).show();
                    $('#centroCustoContentTotal').html(HTML_TOTAL).show();
                    $('#inputTotalGeral').val('R$ ' + 0);
                    $('#relacaoCentroCusto').hide()
                }
            })
        })

        function pula(e){
			/*
			* verifica se o evento é Keycode (para IE e outros browsers)
			* se não for pega o evento Which (Firefox)
			*/
			var tecla = (e.keyCode?e.keyCode:e.which);

			/* verifica se a tecla pressionada foi o ENTER */
			if(tecla == 13){
				/* guarda o seletor do campo que foi pressionado Enter */
				var array_campo = document.getElementsByClassName('pula');

				/* pega o indice do elemento*/
				var id = e.path[0].id.split('-')
				id = 'inputCentroValor-' + (parseInt(id[1])+1)

				/*soma mais um ao indice e verifica se não é null
				*se não for é porque existe outro elemento
				*/

				if(document.getElementById(id)){
					document.getElementById(id).focus()
				} else {
					document.getElementById(e.path[0].id).blur()
                }
			} else {
				return e;
			}

			/* impede o sumbit caso esteja dentro de um form */
			e.preventDefault(e);
			return false;
		}

        function reset(id, val){
            if (id === 'all'){
                var total = parseFloat($('#totalRegistros').val())
                for(var x=0; x<total; x++){
                    $('#inputCentroValor-'+x).val(float2moeda(0))
                }
            } else {
                $('#'+id).val(float2moeda(val))
            }
            calculaValorTotal()
        }

        function calculaValorTotal(id){
            var totalValorAPagar = parseFloat($('#inputValor').val().replaceAll('.', '').replace(',', '.'))
            var ValTotal = 0
            var total = parseFloat($('#totalRegistros').val())
            var valor = id !== undefined ? parseFloat($('#inputCentroValor-'+id).val().replaceAll('.', '').replace(',', '.')) : 0
            var cont = 0

            $('#inputCentroValor-'+id).val(float2moeda(valor))

            for(var x=0; x<total; x++){
                ValTotal += parseFloat($(`#inputCentroValor-${x}`).val()) ? parseFloat($(`#inputCentroValor-${x}`).val().replaceAll('.', '').replace(',', '.')) : 0
                
            }

            if (id !== undefined){
                if(ValTotal > totalValorAPagar){
                    cont = ValTotal - totalValorAPagar
                    ValTotal = ValTotal - cont
                    $('#inputCentroValor-'+id).val(float2moeda(valor - cont))
                }
            }
            ValTotal = (ValTotal).toFixed(2)
            var newValue = float2moeda(ValTotal) //parseFloat(ValTotal).toFixed(2).replace('.', ',')
            $('#inputTotalGeral').val(`R$ ${newValue}`)
            // retorna o status para quando for submeter o sistema verificar
            // se o valor está batendo com o total
            if (ValTotal != totalValorAPagar && total > 0){
                var obj = {
                    status: false,
                    val: totalValorAPagar
                }
                return obj
            } else {
                var obj = {
                    status: true,
                    val: totalValorAPagar
                }
                return obj
            }
        }

        function valorMaiorQZero() {
            let x
                let registros = $(`#totalRegistros`).val()
                for(x=0; x < registros; x++){
                    keyNome = $(`#inputCentroNome-${x}`).val()
                    valor = $(`#inputCentroValor-${x}`).val();

                    if(keyNome != ''){
                        if(valor == '0,00' || valor == '') {
                            var menssagem = 'Há uma centro de conta vazio ou igual a R$ 0,00!'
                            alerta('Atenção', menssagem, 'error')

                            return false
                        }
                    }
                }
                return true
        }

        function centroCustoExiste() {
            let existeCentroCusto = "<?php echo $existeCentroCusto = (isset($itemCentroCusto['CAPXCCentroCusto'])) ? true : false; ?>"
            return existeCentroCusto
        }

        function contaSituacao() {
            let contaPaga = "<?php echo $contaPaga = (isset($lancamento['SituaChave'])) ? $lancamento['SituaChave'] : false; ?>"
            return contaPaga
        }
    </script>

</head>

<body class="navbar-top <?php echo $visibilidadeResumoFinanceiro; ?> sidebar-xs">

    <?php include_once("topo.php"); ?>

    <!-- Page content -->
    <div class="page-content">

        <?php include_once("menu-left.php"); ?>

        <!-- Main content -->
        <div class="content-wrapper">

            <?php include_once("cabecalho.php"); ?>

            <!-- Content area -->
            <div class="content">
                <form id="lancamento" name="lancamento" class="form-validate-jquery" method="post" class="p-3">
                    <!-- Info blocks -->
                    <input type="hidden" id="inputPagamentoParcial" name="inputPagamentoParcial">

                    <?php 
                        if(isset($_POST['inputConciliacaoId'])) {
                            echo '<input type="hidden" id="inputControlador" name="inputControlador" value="1">';
                        }
                    ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Basic responsive configuration -->
                            <div class="card">
                                <div class="card-header header-elements-inline">
                                    <h3 class="card-title"><?php if (!isset($lancamento)) { echo 'Novo'; } else { echo 'Editar'; }  ?> Lançamento - Contas a Pagar</h3>
                                </div>

                                <div class="card-body">
                                    <?php
                                    if (isset($lancamento)) {
                                        echo '<input type="hidden" name="inputEditar" value="sim">';
                                        echo '<input type="hidden" name="inputContaId" value="' . $lancamento['CnAPaId'] . '">';
                                    }

                                    ?>
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="inputDataEmissao">Data de Emissão</label>
                                                <input type="date" id="inputDataEmissao" name="inputDataEmissao" class="form-control" placeholder="Data" value="<?php echo date("Y-m-d") ?>" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO' || isset($lancamento['CnAPaMovimentacao'])) echo 'disabled' ?> readOnly>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="cmbFornecedor">Fornecedor <span class="text-danger">*</span></label>
                                                <select id="cmbFornecedor" name="cmbFornecedor" class="form-control form-control-select2" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO' || isset($lancamento['CnAPaMovimentacao'])) echo 'disabled' ?> required>
                                                    <option value="">Selecionar</option>
                                                    <?php
                                                    $sql = "SELECT ForneId, ForneNome
															FROM Fornecedor
															JOIN Situacao on SituaId = ForneStatus
															WHERE ForneEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'
															ORDER BY ForneNome ASC";
                                                    $result = $conn->query($sql);
                                                    $rowFornecedor = $result->fetchAll(PDO::FETCH_ASSOC);
                                                    foreach ($rowFornecedor as $item) {
                                                        if (isset($lancamento)) {
                                                            if ($lancamento['CnAPaFornecedor'] == $item['ForneId']) {
                                                                print('<option value="' . $item['ForneId'] . '" selected>' . $item['ForneNome'] . '</option>');
                                                            } else {
                                                                print('<option value="' . $item['ForneId'] . '">' . $item['ForneNome'] . '</option>');
                                                            }
                                                        } else {
                                                            print('<option value="' . $item['ForneId'] . '">' . $item['ForneNome'] . '</option>');
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="cmbPlanoContas">Plano de Contas <span class="text-danger">*</span></label>
                                                <select id="cmbPlanoContas" name="cmbPlanoContas" class="form-control form-control-select2" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO' || (isset($lancamento['CnAPaMovimentacao']) && $empresaPublica)) echo 'disabled' ?> required>
                                                    <option value="">Selecionar</option>
                                                    <?php
                                                    $sql = "SELECT PlConId, PlConCodigo, PlConNome
                                                            FROM PlanoConta
                                                            JOIN Situacao on SituaId = PlConStatus
                                                            WHERE PlConUnidade = " . $_SESSION['UnidadeId'] . " and 
                                                            PlConNatureza = 'D' and PlConTipo = 'A' and SituaChave = 'ATIVO'
                                                            ORDER BY PlConCodigo ASC";
                                                    $result = $conn->query($sql);
                                                    $rowPlanoContas = $result->fetchAll(PDO::FETCH_ASSOC);


                                                    foreach ($rowPlanoContas as $item) {
                                                        if (isset($lancamento)) {
                                                            if ($lancamento['CnAPaPlanoContas'] == $item['PlConId']) {
                                                                print('<option value="' . $item['PlConId'] . '" selected>' . $item['PlConCodigo'] . ' - ' . $item['PlConNome'] . '</option>');
                                                            } else {
                                                                print('<option value="' . $item['PlConId'] . '">' . $item['PlConCodigo'] . ' - ' . $item['PlConNome'] . '</option>');
                                                            }
                                                        } else {
                                                            print('<option value="' . $item['PlConId'] . '">' . $item['PlConCodigo'] . ' - ' . $item['PlConNome'] . '</option>');
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 m-auto text-center">
                                            <button id="centroCusto" type="button" class="btn bg-slate btn-sm">CENTRO DE CUSTO</button>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="inputDescricao">Descrição <span class="text-danger">*</span></label>
                                                <input type="text" id="inputDescricao" class="form-control" name="inputDescricao" rows="3" value="<?php if (isset($lancamento)) echo $lancamento['CnAPaDescricao'] ?>" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO') echo 'disabled' ?> required>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="inputOrdemCarta">Ordem Compra/C. Contrato</label>
                                                <input type="text" id="inputOrdemCompra" name="inputOrdemCompra" value="<?php if (isset($lancamento)) echo $lancamento['OrComNumero'] ?>" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO' || isset($lancamento['CnAPaMovimentacao'])) echo 'disabled' ?> class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="inputNotaFiscal">Nº Nota Fiscal/Documento</label>
                                                <input type="text" id="inputNotaFiscal" name="inputNotaFiscal" value="<?php if (isset($lancamento)) echo $lancamento['CnAPaNotaFiscal'] ?>" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO' || isset($lancamento['CnAPaMovimentacao'])) echo 'disabled' ?> class="form-control">
                                            </div>
                                        </div>
                                    </div>                              
                                    <div class="row">
                                        <div class="col-12 col-lg-6">
                                            <div class="d-flex flex-column">
                                                <div class="row justify-content-between m-0">
                                                    <h5>Valor à Pagar</h5>
                                                    <?php
                                                    if (!isset($lancamento)) {
                                                        print('<a href="#" id="btnParcelar" style="color: currentColor; cursor: not-allowed; opacity: 0.5; text-decoration: none; pointer-events: none; margin-top: 5px">Parcelar</a>');
                                                    }
                                                    ?>
                                                </div>
                                                <div class="card">
                                                    <div class="card-body p-4" style="background-color: #f8f8f8; border: 1px solid #ccc">
                                                        <div class="row">
                                                            <div class="form-group col-6">
                                                                <label for="inputDataVencimento">Data do
                                                                    Vencimento</label>
                                                                <input type="date" id="inputDataVencimento" value="<?php isset($lancamento) ? print($lancamento['CnAPaDtVencimento']) : print($dataInicio) ?>" name="inputDataVencimento" class="form-control removeValidacao" <?php  if((isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO') || isset($lancamento['CnAPaMovimentacao'])) echo 'disabled' ?>>
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="inputValor">Valor</label>
                                                                <input type="text" onKeyUp="moeda(this)" maxLength="12" id="inputValor" name="inputValor" value="<?php if (isset($lancamento)) echo mostraValor($lancamento['CnAPaValorAPagar']) ?>" class="form-control removeValidacao" <?php  if((isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO') || isset($lancamento['CnAPaMovimentacao'])) echo 'disabled' ?>>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <div class="d-flex flex-column">
                                                <div class="row justify-content-between m-0">
                                                    <h5>Valor Pago</h5>
                                                    <div class="row pr-2" style="margin-top: 5px;">
                                                        <?php  
                                                        if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] != 'PAGO' || !isset($lancamento['SituaChave'])) {
                                                            print('
                                                            <a id="habilitarPagamento" href="#" >Habilitar Pagamento</a>
                                                            <span class="mx-1">|</span>
                                                            <a id="jurusDescontos" href="" style="color: currentColor; cursor: not-allowed; opacity: 0.5; text-decoration: none; pointer-events: none;">
                                                                Juros/Descontos</a>');
                                                        } 
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="card">
                                                    <div class="card-body p-4" style="background-color: #f8f8f8; border: 1px solid #ccc">
                                                        <div class="row">
                                                            <div class="form-group col-6">
                                                                <label for="inputDataPagamento">Data do
                                                                    Pagamento</label>
                                                                <input type="date" id="inputDataPagamento" value="<?php if (isset($lancamento)) echo $lancamento['CnAPaDtPagamento'] ?>" name="inputDataPagamento" class="form-control removeValidacao" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO') echo 'disabled' ?> readOnly>
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="inputValorTotalPago">Valor Total
                                                                    Pago</label>
                                                                <input type="text" onKeyUp="moeda(this)" maxLength="12" id="inputValorTotalPago" name="inputValorTotalPago" value="<?php if (isset($lancamento)) echo mostraValor($lancamento['CnAPaValorPago']) ?>" class="form-control removeValidacao" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO') echo 'disabled' ?> disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    if (isset($lancamento) && $lancamento['CnAPaContaBanco'] != null && $lancamento['CnAPaContaBanco'] != 0) {
                                        $mostrar = '';
                                    } else {
                                        $mostrar = 'style="display:none;"';
                                    }
                                    ?>
                                    <div id="camposPagamento" class="row justify-content-between" <?php echo $mostrar; ?>>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="cmbContaBanco">Conta/Banco <span class="text-danger">*</span></label>
                                                <select id="cmbContaBanco" name="cmbContaBanco" class="form-control form-control-select2" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO') echo 'disabled' ?>>
                                                    <option value="">Selecionar</option>
                                                    <?php
                                                    $sql = "SELECT CnBanId, CnBanNome
                                                            FROM ContaBanco
                                                            JOIN Situacao on SituaId = CnBanStatus
                                                            WHERE CnBanUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                                            ORDER BY CnBanNome ASC";
                                                    $result = $conn->query($sql);
                                                    $rowContaBanco = $result->fetchAll(PDO::FETCH_ASSOC);
                                                    foreach ($rowContaBanco as $item) {
                                                        if (isset($lancamento)) {
                                                            if ($lancamento['CnAPaContaBanco'] == $item['CnBanId']) {
                                                                print('<option value="' . $item['CnBanId'] . '" selected>' . $item['CnBanNome'] . '</option>');
                                                            } else {
                                                                print('<option value="' . $item['CnBanId'] . '">' . $item['CnBanNome'] . '</option>');
                                                            }
                                                        } else {
                                                            print('<option value="' . $item['CnBanId'] . '">' . $item['CnBanNome'] . '</option>');
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="cmbFormaPagemento">Forma de Pagamento <span class="text-danger">*</span></label>
                                                <select id="cmbFormaPagamento" name="cmbFormaPagamento" class="form-control form-control-select2" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO') echo 'disabled' ?>>
                                                    <option value="">Selecionar</option>
                                                    <?php
                                                    $sql = "SELECT FrPagId, FrPagNome
															FROM FormaPagamento
															JOIN Situacao on SituaId = FrPagStatus
															WHERE FrPagUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
															ORDER BY FrPagNome ASC";
                                                    $result = $conn->query($sql);
                                                    $rowFormaPagamento = $result->fetchAll(PDO::FETCH_ASSOC);
                                                    foreach ($rowFormaPagamento as $item) {
                                                        if (isset($lancamento)) {
                                                            if ($lancamento['CnAPaFormaPagamento'] == $item['FrPagId']) {
                                                                print('<option value="' . $item['FrPagId'] . '" selected>' . $item['FrPagNome'] . '</option>');
                                                            } else {
                                                                print('<option value="' . $item['FrPagId'] . '">' . $item['FrPagNome'] . '</option>');
                                                            }
                                                        } else {
                                                            print('<option value="' . $item['FrPagId'] . '">' . $item['FrPagNome'] . '</option>');
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="inputObservacao">Observação</label>
                                                <textarea id="inputObservacao" class="form-control" name="inputObservacao" rows="3" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO') echo 'disabled' ?>></textarea>
                                            </div>
                                        </div>
                                    </div>
                                        <?php 
                                            if ($atualizar) {
                                                $disabled = (isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO') ? 'disabled' : '';
                                                echo' <button id="salvar" class="btn btn-principal" '.$disabled.'>Salvar</button>';
                                             }
                                        ?>
                                        <a href="javascript:history.go(-1)" class="btn">Cancelar</a>
                                </div>

                            </div>
                            <!-- /basic responsive configuration -->

                        </div>
                    </div>

                    <!-- /info blocks -->

                    <!--------------------------------------------------------------------------------------->
                    <!--Modal Parcelar-->
                    <div id="pageModalParcelar" class="custon-modal">
                        <div class="custon-modal-container">
                            <div class="card custon-modal-content">
                                <div class="custon-modal-title">
                                    <i class=""></i>
                                    <p class="h3">Parcelamento</p>
                                    <i class=""></i>
                                </div>
                                <div class="px-5 pt-5">
                                    <div class="d-flex flex-row p-2">
                                        <div class='col-lg-3'>
                                            <div class="form-group">
                                                <label for="valorTotal">Valor Total</label>
                                                <div class="input-group">
                                                    <input type="text" id="valorTotal" onKeyUp="moeda(this)" maxLength="12" name="valorTotal" class="form-control removeValidacao" readOnly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <label for="cmbParcelas">Parcelas</label>
                                            <div class="form-group">
                                                <select id="cmbParcelas" name="cmbParcelas" class="form-control form-control-select2">
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>

                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-1">
                                            <button class="btn btn-lg btn-primary mt-2" id="gerarParcelas">Gerar
                                                Parcelas</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-row px-5">
                                    <div class="col-12 d-flex flex-row justify-content-center">
                                        <p class="col-1 p-2" style="background-color:#f2f2f2">Item</p>
                                        <p class="col-5 p-2" style="background-color:#f2f2f2">Descrição</p>
                                        <p class="col-3 p-2" style="background-color:#f2f2f2">Vencimento</p>
                                        <p class="col-3 p-2" style="background-color:#f2f2f2">Valor</p>
                                    </div>
                                </div>
                                <div id="parcelasContainer" class="d-flex flex-column px-5" style="overflow-Y: scroll; max-height: 300px">

                                </div>
                                <div class="card-footer mt-2 d-flex flex-column">
                                    <div class="row" style="margin-top: 10px;">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <a class="btn btn-lg btn-principal" id="salvarParcelas">OK</a>
                                                <a id="modalCloseParcelar" class="btn btn-basic" role="button">Cancelar</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--------------------------------------------------------------------------------------->
                    <!--Modal Parcelar-->
                    <div id="pageModalPagamentoParcelar" class="custon-modal">
                        <div class="custon-modal-container">
                            <div class="card custon-modal-content">
                                <div class="custon-modal-title">
                                    <i class=""></i>
                                    <p class="h3">Pagamento de Parcelas</p>
                                    <i class=""></i>
                                </div>
                                <div class="px-5 pt-5">
                                    <div class="d-flex flex-row p-2">
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="cmbPagamentoParcelaContaBanco">Conta/Banco <span class="text-danger">*</span></label>
                                                <select id="cmbPagamentoParcelaContaBanco" name="cmbPagamentoParcelaContaBanco" class="form-control form-control-select2" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO') echo 'disabled' ?>>
                                                    <option value="">Selecionar</option>
                                                    <?php
                                                    $sql = "SELECT CnBanId, CnBanNome
												        			FROM ContaBanco
												        			JOIN Situacao on SituaId = CnBanStatus
												        			WHERE CnBanUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
												        			ORDER BY CnBanNome ASC";
                                                    $result = $conn->query($sql);
                                                    $rowContaBanco = $result->fetchAll(PDO::FETCH_ASSOC);
                                                    foreach ($rowContaBanco as $item) {
                                                        print('<option value="' . $item['CnBanId'] . '">' . $item['CnBanNome'] . '</option>');
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="cmbPagamentoParcelaFormaPagamento">Forma de Pagamento <span class="text-danger">*</span></label>
                                                <select id="cmbPagamentoParcelaFormaPagamento" name="cmbPagamentoParcelaFormaPagamento" class="form-control form-control-select2" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO') echo 'disabled' ?>>
                                                    <option value="">Selecionar</option>
                                                    <?php
                                                    $sql = "SELECT FrPagId, FrPagNome
															FROM FormaPagamento
															JOIN Situacao on SituaId = FrPagStatus
															WHERE FrPagUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
															ORDER BY FrPagNome ASC";
                                                    $result = $conn->query($sql);
                                                    $rowFormaPagamento = $result->fetchAll(PDO::FETCH_ASSOC);
                                                    foreach ($rowFormaPagamento as $item) {
                                                        print('<option value="' . $item['FrPagId'] . '">' . $item['FrPagNome'] . '</option>');
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class='col-lg-3'>
                                            <div class="form-group">
                                                <label for="inputPagamentoParcelaValorTotal">Valor Total</label>
                                                <div class="input-group">
                                                    <input type="text" id="inputPagamentoParcelaValorTotal" onKeyUp="moeda(this)" maxLength="12" name="inputPagamentoParcelaValorTotal" class="form-control removeValidacao" disabled>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-1">
                                            <label for="cmbPagamentoParcelaParcelas">Parcelas</label>
                                            <div class="form-group">
                                                <select id="cmbPagamentoParcelaParcelas" name="cmbPagamentoParcelaParcelas" class="form-control form-control-select2" disabled>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>

                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-row px-5">
                                    <div class="col-12 d-flex flex-row justify-content-center">
                                        <p class="col-1 p-2" style="background-color:#f2f2f2">Item</p>
                                        <p class="col-5 p-2" style="background-color:#f2f2f2">Descrição</p>
                                        <p class="col-3 p-2" style="background-color:#f2f2f2">Vencimento</p>
                                        <p class="col-2 p-2" style="background-color:#f2f2f2">Valor</p>
                                        <p class="col-1 p-2" style="background-color:#f2f2f2">Pagar</p>
                                    </div>
                                </div>
                                <div id="pagamentoParcelasContainer" class="d-flex flex-column px-5" style="overflow-Y: scroll; max-height: 300px">

                                </div>
                                <div class="card-footer mt-2 d-flex flex-column">
                                    <div class="row" style="margin-top: 10px;">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <a class="btn btn-lg btn-principal" id="salvarParcelasPagamentoParcela">OK</a>
                                                <a id="modalClosePagamentoParcela" class="btn btn-basic" role="button">Cancelar</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--------------------------------------------------------------------------------------->

                    <!--------------------------------------------------------------------------------------->
                    <!--Modal Parcelar-->
                    <div id="pageModalJurosDescontos" class="custon-modal">
                        <div class="custon-modal-container">
                            <div class="card cardJuDes custon-modal-content">
                                <div class="custon-modal-title">
                                    <i class=""></i>
                                    <p class="h3">Juros e descontos</p>
                                    <i class=""></i>
                                </div>
                                <div class="p-5">
                                    <div class="d-flex flex-row justify-content-between">
                                        <div class="form-group" style="width: 200px">
                                            <label for="inputVencimentoJD">Data do Vencimento</label>
                                            <input id="inputVencimentoJD" class="form-control" type="date" name="inputVencimentoJD" readOnly>
                                        </div>
                                        <div class="form-group">
                                            <label for="inputValorAPagarJD">Valor à Pagar</label>
                                            <input id="inputValorAPagarJD" onKeyUp="moeda(this)" maxLength="12" class="form-control" type="text" name="inputValorAPagarJD" readOnly>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-row justify-content-between">
                                        <div class="form-group" style="width: 200px">
                                            <label for="cmbTipoJurosJD">Tipo</label>
                                            <select id="cmbTipoJurosJD" name="cmbTipoJurosJD" class="form-control form-control-select2">
                                                <option value="P">Porcentagem</option>
                                                <option value="V">Valor</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="inputJurosJD">Juros</label>
                                            <input id="inputJurosJD" maxLength="12" class="form-control" type="text" name="inputJurosJD">
                                        </div>
                                    </div>
                                    <div class="d-flex flex-row justify-content-between">
                                        <div class="form-group" style="width: 200px">
                                            <label for="cmbTipoDescontoJD">Tipo</label>
                                            <select id="cmbTipoDescontoJD" name="cmbTipoDescontoJD" class="form-control form-control-select2">
                                                <option value="P">Porcentagem</option>
                                                <option value="V">Valor</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="inputDescontoJD">Desconto</label>
                                            <input id="inputDescontoJD" maxLength="12" class="form-control" type="text" name="inputDescontoJD">
                                        </div>
                                    </div>
                                    <div class="d-flex flex-row justify-content-between">
                                        <div class="form-group" style="width: 200px">
                                            <label for="inputDataPagamentoJD">Data do Pagamento</label>
                                            <input id="inputDataPagamentoJD" value="<?php echo date("Y-m-d") ?>" class="form-control" type="date" name="inputDataPagamentoJD" readOnly>
                                        </div>
                                        <div class="form-group">
                                            <label for="inputValorTotalAPagarJD">Valor Total à Pagar</label>
                                            <input id="inputValorTotalAPagarJD" onKeyUp="moeda(this)" maxLength="12" class="form-control" type="text" name="inputValorTotalAPagarJD" readOnly>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer mt-2 d-flex flex-column">
                                    <div class="row" style="margin-top: 10px;">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <a class="btn btn-lg btn-principal" id="salvarJurosDescontos">Ok</a>
                                                <a id="modalCloseJurosDescontos" class="btn btn-basic" role="button">Cancelar</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--------------------------------------------------------------------------------------->

                    <!--Modal Centro de Custo-->
                    <div id="pageCentroCusto" class="custon-modal" style="overflow-y:auto;">
                        <div class="custon-modal-container">
                            <div class="card custon-modal-content">
                                <div class="custon-modal-title">
                                    <i class=""></i>
                                    <p class="h3">Centro de Custos</p>
                                    <i class=""></i>
                                </div>
                                <div class="px-5 pt-5">
                                    <div id="valorAPagarCentroCusto" class="d-flex justify-content-center">
                                    </div>
                                    <div class="d-flex flex-row p-2">
                                        <div class='<?php echo $tamanho = (isset($rowNotaFiscal['MvAneArquivo'])) ? 'col-lg-10' : 'col-lg-12'; ?>'>
                                            <div class="form-group">
                                                <label for="cmbCentroCusto" class="ml-1">Centro de Custo <span class="text-danger">*</span></label>
                                                <?php
                                                    $sql = "SELECT CnCusId, CnCusNome, CnCusNomePersonalizado
                                                            FROM CentroCusto
                                                            JOIN Situacao on SituaId = CnCusStatus
                                                            WHERE SituaChave = 'ATIVO' AND CnCusUnidade = ".$_SESSION['UnidadeId']."
                                                            ORDER BY CnCusNome ASC";
                                                    $result = $conn->query($sql);
                                                    $listCentroCusto = $result->fetchAll(PDO::FETCH_ASSOC);

                                                    $disabled = ((isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'PAGO') || (isset($lancamento['CnAPaMovimentacao']) && $empresaPublica))? 'disabled':'';

                                                    $selectCencust = "<select id='cmbCentroCusto' $disabled name='cmbCentroCusto[]' class='form-control select' multiple='multiple' autofocus data-fouc>";
                                                    
                                                    if(isset($itemCentroCusto['CAPXCCentroCusto'])) {
                                                        foreach($listCentroCusto as $CentroCusto){
                                                            $seleciona = '';
                                                            foreach ($rowCentroCusto as $centroCusto) {
                                                                if($CentroCusto['CnCusId'] == $centroCusto['CAPXCCentroCusto'])
                                                                    $seleciona = "selected";
                                                            }

                                                            $cnCusDescricao = $CentroCusto["CnCusNomePersonalizado"] === NULL ? $CentroCusto["CnCusNome"] : $CentroCusto["CnCusNomePersonalizado"];

                                                            $selectCencust .= "<option value='".$CentroCusto['CnCusId']."' $seleciona>" . $cnCusDescricao . "</option>";
                                                        }
                                                    }else {
                                                        foreach($listCentroCusto as $CentroCusto){
                                                            
                                                            $cnCusDescricao = $CentroCusto["CnCusNomePersonalizado"] === NULL ? $CentroCusto["CnCusNome"] : $CentroCusto["CnCusNomePersonalizado"];
                                                            $selectCencust .= "<option value='".$CentroCusto['CnCusId']."'>" . $cnCusDescricao ."</option>";
                                                        }
                                                    }
                                                    
                                                    $selectCencust .= "</select>";
                                                    echo $selectCencust;
                                                ?>
                                                <hr style="margin-top: -1px;">
                                            </div>
                                        </div>

                                        <input type="hidden" id='todosCentroCusto' name='todosCentroCusto[]'>

                                        <div class="col-lg-2 d-flex justify-content-center" style="display : <?php echo $visibilidade = (isset($rowNotaFiscal['MvAneArquivo'])) ? 'block' : 'none'; ?>">
                                            <?php
                                            if (isset($rowNotaFiscal['MvAneArquivo'])){
                                                echo '
                                                <span class="input-group-prepend m-auto" style="cursor: pointer;">
                                                    <a href="global_assets/anexos/movimentacao/'.$rowNotaFiscal['MvAneArquivo'].'" target="_blank" title="Abrir Nota Fiscal">
                                                        <span class="input-group-text" style="color: red;"><i class="icon-file-pdf"></i></span>
                                                    </a>
                                                </span>';
                                            }                                                              
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body" id="relacaoCentroCusto" style="<?php echo $visibiçidade = (isset($itemCentroCusto['CAPXCCentroCusto'])) ? 'display: block;' : 'display: none;'; ?>">
                                    <?php
                                        if(isset($lancamento['CnAPaMovimentacao'])) {
                                            if(!$empresaPublica) {
                                                print('<p class="mb-3">Abaixo estão listados todos os centros de custos selecionados. Para atualizar os valores, basta preencher a coluna <code>Valor</code> e depois clicar em <b>OK</b>.</p>');
                                            }
                                        }else {
                                            if(!isset($lancamento['SituaChave'])){
                                                print('<p class="mb-3">Abaixo estão listados todos os centros de custos selecionados. Para atualizar os valores, basta preencher a coluna <code>Valor</code> e depois clicar em <b>OK</b>.</p>');    
                                            }else if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] != 'PAGO') {
                                                print('<p class="mb-3">Abaixo estão listados todos os centros de custos selecionados. Para atualizar os valores, basta preencher a coluna <code>Valor</code> e depois clicar em <b>OK</b>.</p>');
                                            }
                                        }
                                    ?>

                                    <div class="row" style="margin-bottom: -20px;">
                                        
                                    </div>
                                    <div id="centroCustoContent">
                                        
                                    </div>

                                    <div id="centroCustoContentTotal" class="row" style="margin-top: 8px;">
                                        
                                    </div>

                                    <input type="hidden" id="totalRegistros" name="totalRegistros" value="0" >
                                    
                                </div>

                                <div class="card-footer mt-2 d-flex flex-column">
                                    <div class="row" style="margin-top: 10px;">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <a class="btn btn-lg btn-principal" id="submitForm">OK</a>
                                                <a id="modalCloseCentroCusto" class="btn btn-basic" role="button">Cancelar</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--------------------------------------------------------------------------------------->
                </form>
            </div>
            <!-- /content area -->

            <?php include_once("footer.php"); ?>

        </div>
        <!-- /main content -->

        <?php include_once("sidebar-right.php"); ?>

    </div>
    <!-- /page content -->

    <?php include_once("alerta.php"); ?>

</body>

</html>