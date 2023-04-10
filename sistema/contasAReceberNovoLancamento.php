<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Novo Lançamento - Contas a Receber';

include('global_assets/php/conexao.php');

if (isset($_POST['inputPermissionAtualiza'])){
    $atualizar = $_POST['inputPermissionAtualiza'];
}

if (isset($_POST['cmbPlanoContas'])) {

    if (isset($_POST['cmbFormaPagamento'])){
                
        $aFormaPagamento = explode('#', $_POST['cmbFormaPagamento']);                                
        $idFormaPagamento = $aFormaPagamento[0];
    }
    
    if (isset($_POST['inputEditar'])) {

        try {
            if (isset($_POST['inputValorTotalRecebido'])) {

                try {
                    $sql = "SELECT SituaId
                            FROM Situacao
                            WHERE SituaChave = 'RECEBIDO'
                        ";
                    $result = $conn->query($sql);
                    $situacao = $result->fetch(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    echo 'Error: ',  $e->getMessage(), "\n";
                }
            } else {

                try {
                    $sql = "SELECT SituaId
                              FROM Situacao
                             WHERE SituaChave = 'ARECEBER'
                        ";

                    $result = $conn->query($sql);
                    $situacao = $result->fetch(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    echo 'Error: ',  $e->getMessage(), "\n";
                }
            }

            try {
            
            $sql = "UPDATE ContasAReceber SET       CnAReDtEmissao                    = :dDtEmissao,
                                                    CnARePlanoContas                  = :iPlanoContas,  
                                                    CnAReCliente                      = :iCliente, 
                                                    CnAReDescricao                    = :sDescricao,
                                                    CnAReNumDocumento                 = :sNumDocumento,
                                                    CnAReContaBanco                   = :iContaBanco, 
                                                    CnAReFormaPagamento               = :iFormaPagamento,
                                                    CnAReVenda                        = :iVenda,
                                                    CnAReDtVencimento                 = :dDtVencimento,
                                                    CnAReValorAReceber                = :fValorAReceber, 
                                                    CnAReDtRecebimento                = :dDtRecebimento,
                                                    CnAReValorRecebido                = :fValorRecebido,
                                                    CnAReTipoJuros                    = :sTipoJuros, 
                                                    CnAReJuros                        = :fJuros, 
                                                    CnAReTipoDesconto                 = :sTipoDesconto, 
                                                    CnAReDesconto                     = :fDesconto,  
                                                    CnAReObservacao                   = :sObservacao,
                                                    CnAReJustificativaEstorno         = :sJustificativaEstorno, 
                                                    CnAReNumCheque                    = :sNumCheque,
                                                    CnAReValorCheque                  = :fValorCheque,
                                                    CnAReDtEmissaoCheque              = :dDtEmissaoCheque,
                                                    CnAReDtVencimentoCheque           = :dDtVencimentoCheque,
                                                    CnAReBancoCheque                  = :iBancoCheque,
                                                    CnAReAgenciaCheque                = :iAgenciaCheque,
                                                    CnAReContaCheque                  = :iContaCheque,
                                                    CnAReNomeCheque                   = :iNomeCheque,
                                                    CnAReCpfCheque                    = :iCpfCheque,
                                                    CnAReStatus                       = :iStatus, 
                                                    CnAReUsuarioAtualizador           = :iUsuarioAtualizador, 
                                                    CnAReUnidade                      = :iUnidade
                    WHERE CnAReId = " . $_POST['inputContaId'] . "";

                $result = $conn->prepare($sql);
                $result->execute(array(
                    
                    ':dDtEmissao'           => isset($_POST['inputDataEmissao']) ? $_POST['inputDataEmissao'] : null,
                    ':iPlanoContas'         => isset($_POST['cmbPlanoContas']) ? intval($_POST['cmbPlanoContas']) : null,
                    ':iCliente'             => intval($_POST['cmbCliente']),
                    ':sDescricao'           => $_POST['inputDescricao'],
                    ':sNumDocumento'        => isset($_POST['inputNumeroDocumento']) ? $_POST['inputNumeroDocumento'] : null,
                    ':iContaBanco'          => isset($_POST['cmbContaBanco']) ? intval($_POST['cmbContaBanco']) : null,
                    ':iFormaPagamento'      => isset($idFormaPagamento) ? $idFormaPagamento : null,
                    ':iVenda'               =>  null,
                    ':dDtVencimento'        => $_POST['inputDataVencimento'],
                    ':fValorAReceber'       => floatval(gravaValor($_POST['inputValor'])),
                    ':dDtRecebimento'       => isset($_POST['inputDataRecebimento']) ? $_POST['inputDataRecebimento'] : null,
                    ':fValorRecebido'       => isset($_POST['inputValorTotalRecebido']) ? floatval(gravaValor($_POST['inputValorTotalRecebido'])) : null,
                    ':sTipoJuros'           => isset($_POST['cmbTipoJurosJD']) ? $_POST['cmbTipoJurosJD'] : null,
                    ':fJuros'               => isset($_POST['inputJurosJD']) ? floatval(gravaValor($_POST['inputJurosJD'])) : null,
                    ':sTipoDesconto'        => isset($_POST['cmbTipoDescontoJD']) ? $_POST['cmbTipoDescontoJD'] : null,
                    ':fDesconto'            => isset($_POST['inputDescontoJD']) ? floatval(gravaValor($_POST['inputDescontoJD'])) : null,
                    ':sObservacao'          => isset($_POST['inputObservacao']) ? $_POST['inputObservacao'] : null,
                    ':sJustificativaEstorno'=> null,
                    ':sNumCheque'           => isset($_POST['inputNumCheque']) ? $_POST['inputNumCheque'] : null,
                    ':fValorCheque'         => isset($_POST['inputValorCheque']) ? floatval(gravaValor($_POST['inputValorCheque'])) : null,
                    ':dDtEmissaoCheque'     => isset($_POST['inputDtEmissaoCheque']) ? $_POST['inputDtEmissaoCheque'] : null,
                    ':dDtVencimentoCheque'  => isset($_POST['inputDtVencimentoCheque']) ? $_POST['inputDtVencimentoCheque'] : null,
                    ':iBancoCheque'         => isset($_POST['cmbBancoCheque']) ? intval($_POST['cmbBancoCheque']) : null,
                    ':iAgenciaCheque'       => isset($_POST['inputAgenciaCheque']) ? $_POST['inputAgenciaCheque'] : null,
                    ':iContaCheque'         => isset($_POST['inputContaCheque']) ? $_POST['inputContaCheque'] : null,
                    ':iNomeCheque'          => isset($_POST['inputNomeCheque']) ? $_POST['inputNomeCheque'] : null,
                    ':iCpfCheque'           => isset($_POST['inputCpfCheque']) ? $_POST['inputCpfCheque'] : null,   
                    ':iStatus'              => intval($situacao['SituaId']),
                    ':iUsuarioAtualizador'  => intval($_SESSION['UsuarId']),
                    ':iUnidade'             => intval($_SESSION['UnidadeId'])
                ));

                $idContaAReceber = $_POST['inputContaId'];
            } catch (Exception $e) {
                echo 'Error: ',  $e->getMessage(), "\n";
            }
        
            $recebimentoParcial = false;

            if (isset($_POST['inputRecebimentoParcial'])) {
                if (intval($_POST['inputRecebimentoParcial']) != 0) {

                    $sql = "SELECT SituaId
                              FROM Situacao
                             WHERE SituaChave = 'ARECEBER'
                     ";

                    $result = $conn->query($sql);
                    $situacao = $result->fetch(PDO::FETCH_ASSOC);
                    
                $sql = "INSERT INTO ContasAReceber ( CnAReDtEmissao,
                                                        CnARePlanoContas, 
                                                        CnAReCliente,
                                                        CnAReDescricao,
                                                        CnAReNumDocumento,  
                                                        CnAReContaBanco,                   
                                                        CnAReFormaPagamento,
                                                        CnAReVenda,                              
                                                        CnAReDtVencimento, 
                                                        CnAReValorAReceber,
                                                        CnAReDtRecebimento, 
                                                        CnAReValorRecebido,
                                                        CnAReTipoJuros, 
                                                        CnAReJuros,
                                                        CnAReTipoDesconto, 
                                                        CnAReDesconto, 
                                                        CnAReObservacao, 
                                                        CnAReNumCheque,                    
                                                        CnAReValorCheque,                  
                                                        CnAReDtEmissaoCheque,             
                                                        CnAReDtVencimentoCheque,           
                                                        CnAReBancoCheque,                 
                                                        CnAReAgenciaCheque,                
                                                        CnAReContaCheque,                  
                                                        CnAReNomeCheque,                  
                                                        CnAReCpfCheque,             
                                                        CnAReStatus, 
                                                        CnAReUsuarioAtualizador, 
                                                        CnAReUnidade)
                           VALUES ( :dDtEmissao, 
                                    :iPlanoContas, 
                                    :iCliente,
                                    :sDescricao,
                                    :sNumDocumento, 
                                    :iContaBanco, 
                                    :iFormaPagamento,
                                    :iVenda,
                                    :dDtVencimento, 
                                    :fValorAReceber, 
                                    :dDtRecebimento, 
                                    :fValorRecebido,
                                    :sTipoJuros, 
                                    :fJuros, 
                                    :sTipoDesconto, 
                                    :fDesconto,  
                                    :sObservacao,
                                    :sNumCheque,
                                    :fValorCheque,
                                    :dDtEmissaoCheque,
                                    :dDtVencimentoCheque,
                                    :iBancoCheque,
                                    :iAgenciaCheque,
                                    :iContaCheque,
                                    :iNomeCheque,
                                    :iCpfCheque, 
                                    :iStatus, 
                                    :iUsuarioAtualizador, 
                                    :iUnidade)";

                    $result = $conn->prepare($sql);

                    $result->execute(array(

                        ':dDtEmissao'           => isset($_POST['inputDataEmissao']) ? $_POST['inputDataEmissao'] : null,
                        ':iPlanoContas'         => isset($_POST['cmbPlanoContas']) ? intval($_POST['cmbPlanoContas']) : null,
                        ':iCliente'             => intval($_POST['cmbCliente']),
                        ':sDescricao'           => $_POST['inputDescricao'],
                        ':sNumDocumento'        => isset($_POST['inputNumeroDocumento']) ? $_POST['inputNumeroDocumento'] : null,
                        ':iContaBanco'          => null,
                        ':iFormaPagamento'      => null,
                        ':iVenda'               => null,
                        ':dDtVencimento'        => $_POST['inputDataVencimento'],
                        ':fValorAReceber'       => floatval(gravaValor($_POST['inputRecebimentoParcial'])),
                        ':dDtRecebimento'       => null,
                        ':fValorRecebido'       => null,
                        ':sTipoJuros'           => isset($_POST['cmbTipoJurosJD']) ? $_POST['cmbTipoJurosJD'] : null,
                        ':fJuros'               => isset($_POST['inputJurosJD']) ? floatval(gravaValor($_POST['inputJurosJD'])) : null,
                        ':sTipoDesconto'        => isset($_POST['cmbTipoDescontoJD']) ? $_POST['cmbTipoDescontoJD'] : null,
                        ':fDesconto'            => isset($_POST['inputDescontoJD']) ? floatval(gravaValor($_POST['inputDescontoJD'])) : null,
                        ':sObservacao'          => isset($_POST['inputObservacao']) ? $_POST['inputObservacao'] : null,
                        ':sNumCheque'           => isset($_POST['inputNumCheque']) ? $_POST['inputNumCheque'] : null,
                        ':fValorCheque'         => isset($_POST['inputValorCheque']) ? floatval(gravaValor($_POST['inputValorCheque'])) : null,
                        ':dDtEmissaoCheque'     => isset($_POST['inputDtEmissaoCheque']) ? $_POST['inputDtEmissaoCheque'] : null,
                        ':dDtVencimentoCheque'  => isset($_POST['inputDtVencimentoCheque']) ? $_POST['inputDtVencimentoCheque'] : null,
                        ':iBancoCheque'         => isset($_POST['cmbBancoCheque']) ? intval($_POST['cmbBancoCheque']) : null,
                        ':iAgenciaCheque'       => isset($_POST['inputAgenciaCheque']) ? $_POST['inputAgenciaCheque'] : null,
                        ':iContaCheque'         => isset($_POST['inputContaCheque']) ? $_POST['inputContaCheque'] : null,
                        ':iNomeCheque'          => isset($_POST['inputNomeCheque']) ? $_POST['inputNomeCheque'] : null,
                        ':iCpfCheque'           => isset($_POST['inputCpfCheque']) ? $_POST['inputCpfCheque'] : null,   
                        ':iStatus'              => intval($situacao['SituaId']),
                        ':iUsuarioAtualizador'  => intval($_SESSION['UsuarId']),
                        ':iUnidade'             => intval($_SESSION['UnidadeId'])
                    ));

                    $idContaAreceberParcial = $conn->lastInsertId();
                    $recebimentoParcial = true;
                }
            }

            if($recebimentoParcial) {
                $valorRecebidoParcialmente = floatval(gravaValor($_POST['inputValor']));
                $valorReceberParcialmente = $_POST['inputRecebimentoParcial'];
                $totalParcialmente = $valorRecebidoParcialmente + $valorReceberParcialmente;
                
                $percentualAReceberParcialmente = ($valorReceberParcialmente * 100) / $totalParcialmente;
                $percentualRecebidoParcialmente = ($valorRecebidoParcialmente * 100) / $totalParcialmente;

                $registros = intval($_POST['totalRegistros']);

                for($x=0; $x < $registros; $x++){
                    //$keyNome = 'inputCentroNome-'.$x;
                    $keyId = 'inputIdCentro-'.$x;
                    $centroCusto = $_POST[$keyId];
                    $valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
                    $valor = str_replace(',', '.', $valor);

                    $valor = ($percentualAReceberParcialmente / 100) * $valor;
                    $valor;
                    
                $sql = "INSERT INTO ContasAReceberXCentroCusto ( CARXCContasAReceber, CARXCCentroCusto, CARXCValor, CARXCUsuarioAtualizador, CARXCUnidade)
                            VALUES ( :iContasAReceber, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                    $result = $conn->prepare($sql);

                    $result->execute(array(

                        ':iContasAReceber' => $idContaAreceberParcial,
                        ':iCentroCusto' => $centroCusto,
                        ':iValor' => $valor,
                        ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                        ':iUnidade' => $_SESSION['UnidadeId']
                    ));
                    
                }

                $sql = "SELECT CARXCCentroCusto
                        FROM ContasAReceberXCentroCusto
                        WHERE CARXCContasAReceber = $idContaAReceber";
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
                    $valor = ($percentualRecebidoParcialmente / 100) * $valor;
                    $valor;
                    $arrayControleCentroCustoSistema[] = $centroCusto;
                    $arrayCentroCusto[$i]['idCentroCusto'] = $centroCusto;
                    $arrayCentroCusto[$i]['valorCentroCusto'] = $valor;
    
                    foreach($centroCustoBancoDeDados as $idCentroContaAtualiza) {
                        if($idCentroContaAtualiza['CARXCCentroCusto'] == $centroCusto) {
                            
                        $sql = "UPDATE ContasAReceberXCentroCusto SET CARXCValor = :fValor, CARXCUsuarioAtualizador = :iUsuarioAtualizador
                                    WHERE CARXCCentroCusto = $centroCusto AND CARXCContasAReceber = $idContaAReceber";
                            $result = $conn->prepare($sql);
    
                            $result->execute(array(
                                ':fValor' => $valor,
                                ':iUsuarioAtualizador' => $_SESSION['UsuarId']
                            ));
                            

                            $arrayControle[] = $centroCusto;
                            $arrayIdAntigoCentroCusto[$i]['idCentroCusto'] = $idCentroContaAtualiza['CARXCCentroCusto'];
                            $arrayIdAntigoCentroCusto[$i]['valorCentroCusto'] = $valor;
                        }
                        if($controle) {
                            $arrayBancoDeDados[] = $idCentroContaAtualiza['CARXCCentroCusto'];
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
                                    
                                $sql = "INSERT INTO ContasAReceberXCentroCusto ( CARXCContasAReceber, CARXCCentroCusto, CARXCValor, CARXCUsuarioAtualizador, CARXCUnidade)
                                            VALUES ( :iContasAReceber, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                                    $result = $conn->prepare($sql);
    
                                    $result->execute(array(
    
                                        ':iContasAReceber' => $idContaAReceber,
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
                            
                        $sql = "DELETE FROM ContasAReceberXCentroCusto
                                    WHERE CARXCCentroCusto = :iCentroCusto AND CARXCContasAReceber = :iContaAPagar";
                            $result = $conn->prepare($sql);
                            
                            $result->execute(array(
                                ':iCentroCusto' => $deletaCentroCusto,
                                ':iContaAPagar' => $idContaAReceber
                            ));
                            
                        }
                    }
                }else {
                    foreach($arrayControleCentroCustoSistema as $novoCentroCusto) {
                        foreach($arrayCentroCusto as $insereCentroCusto) {
                            if($insereCentroCusto['idCentroCusto'] == $novoCentroCusto) {
                                
                            $sql = "INSERT INTO ContasAReceberXCentroCusto ( CARXCContasAReceber, CARXCCentroCusto, CARXCValor, CARXCUsuarioAtualizador, CARXCUnidade)
                                        VALUES ( :iContasAReceber, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                                $result = $conn->prepare($sql);
    
                                $result->execute(array(
    
                                    ':iContasAReceber' => $idContaAReceber,
                                    ':iCentroCusto' => $insereCentroCusto['idCentroCusto'],
                                    ':iValor' => $insereCentroCusto['valorCentroCusto'],
                                    ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                                    ':iUnidade' => $_SESSION['UnidadeId']
                                ));
                                
                            }
                        }
                    }
    
                    foreach($arrayBancoDeDados as $deletaCentroCusto) {
                        
                    $sql = "DELETE FROM ContasAReceberXCentroCusto
                                WHERE CARXCCentroCusto = :iCentroCusto AND CARXCContasAReceber = :iContaAPagar";
                        $result = $conn->prepare($sql);
                        
                        $result->execute(array(
                            ':iCentroCusto' => $deletaCentroCusto,
                            ':iContaAPagar' => $idContaAReceber
                        ));
                        
                    }
                }
            }else {
                $sql = "SELECT CARXCCentroCusto
                        FROM ContasAReceberXCentroCusto
                        WHERE CARXCContasAReceber = $idContaAReceber";
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
                        if($idCentroContaAtualiza['CARXCCentroCusto'] == $centroCusto) {
                            $sql = "UPDATE ContasAReceberXCentroCusto SET CARXCValor = :fValor, CARXCUsuarioAtualizador = :iUsuarioAtualizador
                                    WHERE CARXCCentroCusto = $centroCusto AND CARXCContasAReceber = $idContaAReceber";
                            $result = $conn->prepare($sql);
    
                            $result->execute(array(
                                ':fValor' => $valor,
                                ':iUsuarioAtualizador' => $_SESSION['UsuarId']
                            ));
                            
    
                            $arrayControle[] = $centroCusto;
                            $arrayIdAntigoCentroCusto[$i]['idCentroCusto'] = $idCentroContaAtualiza['CARXCCentroCusto'];
                            $arrayIdAntigoCentroCusto[$i]['valorCentroCusto'] = $valor;
                        }
                        if($controle) {
                            $arrayBancoDeDados[] = $idCentroContaAtualiza['CARXCCentroCusto'];
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
                                    $sql = "INSERT INTO ContasAReceberXCentroCusto ( CARXCContasAReceber, CARXCCentroCusto, CARXCValor, CARXCUsuarioAtualizador, CARXCUnidade)
                                            VALUES ( :iContasAReceber, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                                    $result = $conn->prepare($sql);
    
                                    $result->execute(array(
    
                                        ':iContasAReceber' => $idContaAReceber,
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
                            $sql = "DELETE FROM ContasAReceberXCentroCusto
                                    WHERE CARXCCentroCusto = :iCentroCusto AND CARXCContasAReceber = :iContaAPagar";
                            $result = $conn->prepare($sql);
                            
                            $result->execute(array(
                                ':iCentroCusto' => $deletaCentroCusto,
                                ':iContaAPagar' => $idContaAReceber
                            ));
                            
                        }
                    }
                }else {
                    foreach($arrayControleCentroCustoSistema as $novoCentroCusto) {
                        foreach($arrayCentroCusto as $insereCentroCusto) {
                            if($insereCentroCusto['idCentroCusto'] == $novoCentroCusto) {
                                $sql = "INSERT INTO ContasAReceberXCentroCusto ( CARXCContasAReceber, CARXCCentroCusto, CARXCValor, CARXCUsuarioAtualizador, CARXCUnidade)
                                        VALUES ( :iContasAReceber, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                                $result = $conn->prepare($sql);
    
                                $result->execute(array(
    
                                    ':iContasAReceber' => $idContaAReceber,
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
                            $sql = "DELETE FROM ContasAReceberXCentroCusto
                                    WHERE CARXCCentroCusto = :iCentroCusto AND CARXCContasAReceber = :iContaAPagar";
                            $result = $conn->prepare($sql);
                            
                            $result->execute(array(
                                ':iCentroCusto' => $deletaCentroCusto,
                                ':iContaAPagar' => $idContaAReceber
                            ));
                            
                        }
                    }
                }
            }

            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Lançamento editado!!!";
            $_SESSION['msg']['tipo'] = "success";
        } catch (PDOException $e) {

            $_SESSION['msg']['titulo'] = "Erro";
            $_SESSION['msg']['mensagem'] = "Erro ao editar lançamento!!!";
            $_SESSION['msg']['tipo'] = "error";

            echo 'Error: ' . $e->getMessage();
            die;
        }
    } else {

        try {
            if (isset($_POST['inputNumeroParcelas'])) {

                $numParcelas = intVal($_POST['inputNumeroParcelas']);

                for ($i = 1; $i <= $numParcelas; $i++) {
                    try {
                        $sql = "SELECT SituaId
                              FROM Situacao
                             WHERE SituaChave = 'ARECEBER'
                    ";

                       $result = $conn->query($sql);
                       $situacao = $result->fetch(PDO::FETCH_ASSOC);

                       $sql = "INSERT INTO ContasAReceber ( CnARePlanoContas, CnAReCliente, CnAReContaBanco, CnAReFormaPagamento, CnAReNumDocumento,
                                                       CnAReDtEmissao, CnAReDescricao, CnAReDtVencimento, CnAReValorAReceber,
                                                        CnAReDtRecebimento, CnAReValorRecebido, CnAReObservacao, CnAReStatus, CnAReUsuarioAtualizador, CnAReUnidade)
                                VALUES ( :iPlanoContas, :iCliente, :iContaBanco, :iFormaPagamento,:sNumDocumento, :dateDtEmissao,:sDescricao, :dateDtVencimento, 
                                        :fValorAReceber, :dateDtRecebimento, :fValorRecebido, :sObservacao, :iStatus, :iUsuarioAtualizador, :iUnidade)";
                        $result = $conn->prepare($sql);

                        $result->execute(array(

                            ':iPlanoContas' => $_POST['cmbPlanoContas'],
                            ':iCliente' => $_POST['cmbCliente'],
                            ':iContaBanco' => $_POST['cmbContaBanco'],
                            ':iFormaPagamento' => isset($idFormaPagamento) ? $idFormaPagamento : null,
                            ':sNumDocumento' => $_POST['inputNumeroDocumento'],
                            ':dateDtEmissao' => $_POST['inputDataEmissao'],
                            ':sDescricao' => $_POST['inputParcelaDescricao' . $i . ''],
                            ':dateDtVencimento' => $_POST['inputParcelaDataVencimento' . $i . ''],
                            ':fValorAReceber' => floatval(gravaValor($_POST['inputParcelaValorAReceber' . $i . ''])),
                            ':dateDtRecebimento' => $_POST['inputDataRecebimento'],
                            ':fValorRecebido' => isset($_POST['inputValorTotalRecebido']) ? floatval(gravaValor($_POST['inputValorTotalRecebido'])) : null,
                            ':sObservacao' => $_POST['inputObservacao'],
                            ':iStatus' => $situacao['SituaId'],
                            ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                            ':iUnidade' => $_SESSION['UnidadeId']
                        ));

                        $idContaAReceber = $conn->lastInsertId();

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
                        if(floatval(gravaValor($_POST['inputParcelaValorAReceber' . $i . ''])) != $proporcaoCentroCusto) {
                            $valParcela = floatval(gravaValor($_POST['inputParcelaValorAReceber' . $i . '']));
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

                            $sql = "INSERT INTO ContasAReceberXCentroCusto ( CARXCContasAReceber, CARXCCentroCusto, CARXCValor, CARXCUsuarioAtualizador, CARXCUnidade)
                                    VALUES ( :iContasAReceber, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                            $result = $conn->prepare($sql);

                            $result->execute(array(

                                ':iContasAReceber' => $idContaAReceber,
                                ':iCentroCusto' => $centroCusto,
                                ':iValor' => $valor,
                                ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                                ':iUnidade' => $_SESSION['UnidadeId']
                            ));
                        }
                    } catch (Exception $e) {
                        echo 'Error: ',  $e->getMessage(), "\n";
                    }
                }
            } else {
                if (isset($_POST['inputValorTotalRecebido'])) {
                    try {
                        $sql = "SELECT SituaId
                                  FROM Situacao
                                 WHERE SituaChave = 'RECEBIDO'
                        ";

                        $result = $conn->query($sql);
                        $situacao = $result->fetch(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        echo 'Error: ',  $e->getMessage(), "\n";
                    }
                } else {
                    try {
                        $sql = "SELECT SituaId
                                  FROM Situacao
                                 WHERE SituaChave = 'ARECEBER'
                        ";

                        $result = $conn->query($sql);
                        $situacao = $result->fetch(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        echo 'Error: ',  $e->getMessage(), "\n";
                    }
                }

                try {
                    $sql = "INSERT INTO ContasAReceber ( CnAReDtEmissao,
                                                         CnARePlanoContas, 
                                                         CnAReCliente,
                                                         CnAReDescricao,
                                                         CnAReNumDocumento,  
                                                         CnAReContaBanco,                   
                                                         CnAReFormaPagamento,
                                                         CnAReVenda,                              
                                                         CnAReDtVencimento, 
                                                         CnAReValorAReceber,
                                                         CnAReDtRecebimento, 
                                                         CnAReValorRecebido,
                                                         CnAReTipoJuros, 
                                                         CnAReJuros,
                                                         CnAReTipoDesconto, 
                                                         CnAReDesconto, 
                                                         CnAReObservacao, 
                                                         CnAReNumCheque,                    
                                                         CnAReValorCheque,                  
                                                         CnAReDtEmissaoCheque,             
                                                         CnAReDtVencimentoCheque,          
                                                         CnAReBancoCheque,                 
                                                         CnAReAgenciaCheque,                
                                                         CnAReContaCheque,                  
                                                         CnAReNomeCheque,                  
                                                         CnAReCpfCheque,          
                                                         CnAReStatus, 
                                                         CnAReUsuarioAtualizador, 
                                                         CnAReUnidade)
                        VALUES ( :dDtEmissao, 
                                 :iPlanoContas, 
                                 :iCliente,
                                 :sDescricao,
                                 :sNumDocumento, 
                                 :iContaBanco, 
                                 :iFormaPagamento,
                                 :iVenda,
                                 :dDtVencimento, 
                                 :fValorAReceber, 
                                 :dDtRecebimento, 
                                 :fValorRecebido,
                                 :sTipoJuros, 
                                 :fJuros, 
                                 :sTipoDesconto, 
                                 :fDesconto,  
                                 :sObservacao,
                                 :sNumCheque,
                                 :fValorCheque,
                                 :dDtEmissaoCheque,
                                 :dDtVencimentoCheque,
                                 :iBancoCheque,
                                 :iAgenciaCheque,
                                 :iContaCheque,
                                 :iNomeCheque,
                                 :iCpfCheque, 
                                 :iStatus, 
                                 :iUsuarioAtualizador, 
                                 :iUnidade)";


                    $result = $conn->prepare($sql);
                    $result->execute(array(

                        ':dDtEmissao'           => isset($_POST['inputDataEmissao']) ? $_POST['inputDataEmissao'] : null,
                        ':iPlanoContas'         => isset($_POST['cmbPlanoContas']) ? intval($_POST['cmbPlanoContas']) : null,
                        ':iCliente'             => intval($_POST['cmbCliente']),
                        ':sDescricao'           => $_POST['inputDescricao'],
                        ':sNumDocumento'        => isset($_POST['inputNumeroDocumento']) ? $_POST['inputNumeroDocumento'] : null,
                        ':iContaBanco'          => isset($_POST['cmbContaBanco']) ? intval($_POST['cmbContaBanco']) : null,
                        ':iFormaPagamento'      => isset($idFormaPagamento) ? $idFormaPagamento : null,
                        ':iVenda'               =>  null,
                        ':dDtVencimento'        => $_POST['inputDataVencimento'],
                        ':fValorAReceber'       => floatval(gravaValor($_POST['inputValor'])),
                        ':dDtRecebimento'       => isset($_POST['inputDataRecebimento']) ? $_POST['inputDataRecebimento'] : null,
                        ':fValorRecebido'       => isset($_POST['inputValorTotalRecebido']) ? floatval(gravaValor($_POST['inputValorTotalRecebido'])) : null,
                        ':sTipoJuros'           => isset($_POST['cmbTipoJurosJD']) ? $_POST['cmbTipoJurosJD'] : null,
                        ':fJuros'               => isset($_POST['inputJurosJD']) ? floatval(gravaValor($_POST['inputJurosJD'])) : null,
                        ':sTipoDesconto'        => isset($_POST['cmbTipoDescontoJD']) ? $_POST['cmbTipoDescontoJD'] : null,
                        ':fDesconto'            => isset($_POST['inputDescontoJD']) ? floatval(gravaValor($_POST['inputDescontoJD'])) : null,
                        ':sObservacao'          => isset($_POST['inputObservacao']) ? $_POST['inputObservacao'] : null,
                        ':sNumCheque'           => isset($_POST['inputNumCheque']) ? $_POST['inputNumCheque'] : null,
                        ':fValorCheque'         => isset($_POST['inputValorCheque']) ? floatval(gravaValor($_POST['inputValorCheque'])) : null,
                        ':dDtEmissaoCheque'     => isset($_POST['inputDtEmissaoCheque']) ? $_POST['inputDtEmissaoCheque'] : null,
                        ':dDtVencimentoCheque'  => isset($_POST['inputDtVencimentoCheque']) ? $_POST['inputDtVencimentoCheque'] : null,
                        ':iBancoCheque'         => isset($_POST['cmbBancoCheque']) ? intval($_POST['cmbBancoCheque']) : null,
                        ':iAgenciaCheque'       => isset($_POST['inputAgenciaCheque']) ? $_POST['inputAgenciaCheque'] : null,
                        ':iContaCheque'         => isset($_POST['inputContaCheque']) ? $_POST['inputContaCheque'] : null,
                        ':iNomeCheque'          => isset($_POST['inputNomeCheque']) ? $_POST['inputNomeCheque'] : null,
                        ':iCpfCheque'           => isset($_POST['inputCpfCheque']) ? $_POST['inputCpfCheque'] : null,   
                        ':iStatus'              => intval($situacao['SituaId']),
                        ':iUsuarioAtualizador'  => intval($_SESSION['UsuarId']),
                        ':iUnidade'             => intval($_SESSION['UnidadeId'])
                    ));

                    $idContaAReceber = $conn->lastInsertId();
                } catch (Exception $e) {
                    echo 'Error: ',  $e->getMessage(), "\n";
                }

                $recebimentoParcial = false;

                if (isset($_POST['inputRecebimentoParcial'])) {
                    if (intval($_POST['inputRecebimentoParcial']) != 0) {
                        try {
                            $sql = "SELECT SituaId
                                  FROM Situacao
                                 WHERE SituaChave = 'ARECEBER'
                         ";

                            $result = $conn->query($sql);
                            $situacao = $result->fetch(PDO::FETCH_ASSOC);

                            $sql = "INSERT INTO ContasAReceber( CnAReDtEmissao,
                                                                CnARePlanoContas, 
                                                                CnAReCliente,
                                                                CnAReDescricao,
                                                                CnAReNumDocumento,  
                                                                CnAReContaBanco,                   
                                                                CnAReFormaPagamento,
                                                                CnAReVenda,                              
                                                                CnAReDtVencimento, 
                                                                CnAReValorAReceber,
                                                                CnAReDtRecebimento, 
                                                                CnAReValorRecebido,
                                                                CnAReTipoJuros, 
                                                                CnAReJuros,
                                                                CnAReTipoDesconto, 
                                                                CnAReDesconto, 
                                                                CnAReObservacao, 
                                                                CnAReNumCheque,                    
                                                                CnAReValorCheque,                  
                                                                CnAReDtEmissaoCheque,             
                                                                CnAReDtVencimentoCheque,           
                                                                CnAReBancoCheque,                 
                                                                CnAReAgenciaCheque,                
                                                                CnAReContaCheque,                  
                                                                CnAReNomeCheque,                  
                                                                CnAReCpfCheque,             
                                                                CnAReStatus, 
                                                                CnAReUsuarioAtualizador, 
                                                                CnAReUnidade)
                               VALUES ( :dDtEmissao, 
                                        :iPlanoContas, 
                                        :iCliente,
                                        :sDescricao,
                                        :sNumDocumento, 
                                        :iContaBanco, 
                                        :iFormaPagamento,
                                        :iVenda,
                                        :dDtVencimento, 
                                        :fValorAReceber, 
                                        :dDtRecebimento, 
                                        :fValorRecebido,
                                        :sTipoJuros, 
                                        :fJuros, 
                                        :sTipoDesconto, 
                                        :fDesconto,  
                                        :sObservacao,
                                        :sNumCheque,
                                        :fValorCheque,
                                        :dDtEmissaoCheque,
                                        :dDtVencimentoCheque,
                                        :iBancoCheque,
                                        :iAgenciaCheque,
                                        :iContaCheque,
                                        :iNomeCheque,
                                        :iCpfCheque, 
                                        :iStatus, 
                                        :iUsuarioAtualizador, 
                                        :iUnidade)";

                      

                            $result = $conn->prepare($sql);
                            $result->execute(array(
                                ':dDtEmissao'           => isset($_POST['inputDataEmissao']) ? $_POST['inputDataEmissao'] : null,
                                ':iPlanoContas'         => isset($_POST['cmbPlanoContas']) ? intval($_POST['cmbPlanoContas']) : null,
                                ':iCliente'             => intval($_POST['cmbCliente']),
                                ':sDescricao'           => $_POST['inputDescricao'],
                                ':sNumDocumento'        => isset($_POST['inputNumeroDocumento']) ? $_POST['inputNumeroDocumento'] : null,
                                ':iContaBanco'          => null,
                                ':iFormaPagamento'      => null,
                                ':iVenda'               => null,
                                ':dDtVencimento'        => $_POST['inputDataVencimento'],
                                ':fValorAReceber'       => floatval(gravaValor($_POST['inputRecebimentoParcial'])),
                                ':dDtRecebimento'       => isset($_POST['inputDataRecebimento']) ? $_POST['inputDataRecebimento'] : null,
                                ':fValorRecebido'       => null,
                                ':sTipoJuros'           => isset($_POST['cmbTipoJurosJD']) ? $_POST['cmbTipoJurosJD'] : null,
                                ':fJuros'               => isset($_POST['inputJurosJD']) ? floatval(gravaValor($_POST['inputJurosJD'])) : null,
                                ':sTipoDesconto'        => isset($_POST['cmbTipoDescontoJD']) ? $_POST['cmbTipoDescontoJD'] : null,
                                ':fDesconto'            => isset($_POST['inputDescontoJD']) ? floatval(gravaValor($_POST['inputDescontoJD'])) : null,
                                ':sObservacao'          => isset($_POST['inputObservacao']) ? $_POST['inputObservacao'] : null,
                                ':sNumCheque'           => isset($_POST['inputNumCheque']) ? $_POST['inputNumCheque'] : null,
                                ':fValorCheque'         => isset($_POST['inputValorCheque']) ? floatval(gravaValor($_POST['inputValorCheque'])) : null,
                                ':dDtEmissaoCheque'     => isset($_POST['inputDtEmissaoCheque']) ? $_POST['inputDtEmissaoCheque'] : null,
                                ':dDtVencimentoCheque'  => isset($_POST['inputDtVencimentoCheque']) ? $_POST['inputDtVencimentoCheque'] : null,
                                ':iBancoCheque'         => isset($_POST['cmbBancoCheque']) ? intval($_POST['cmbBancoCheque']) : null,
                                ':iAgenciaCheque'       => isset($_POST['inputAgenciaCheque']) ? $_POST['inputAgenciaCheque'] : null,
                                ':iContaCheque'         => isset($_POST['inputContaCheque']) ? $_POST['inputContaCheque'] : null,
                                ':iNomeCheque'          => isset($_POST['inputNomeCheque']) ? $_POST['inputNomeCheque'] : null,
                                ':iCpfCheque'           => isset($_POST['inputCpfCheque']) ? $_POST['inputCpfCheque'] : null,   
                                ':iStatus'              => intval($situacao['SituaId']),
                                ':iUsuarioAtualizador'  => intval($_SESSION['UsuarId']),
                                ':iUnidade'             => intval($_SESSION['UnidadeId'])
                            ));

                        $idContaAreceberParcial = $conn->lastInsertId();
                        $recebimentoParcial = true;
                        } catch (Exception $e) {
                            echo 'Error: ',  $e->getMessage(), "\n";
                        }
                    }
                }

                if($recebimentoParcial) {
                    $valorRecebidoParcialmente = floatval(gravaValor($_POST['inputValor']));
                    $valorReceberParcialmente = $_POST['inputRecebimentoParcial'];
                    $totalParcialmente = $valorRecebidoParcialmente + $valorReceberParcialmente;
                    
                    $percentualAReceberParcialmente = ($valorReceberParcialmente * 100) / $totalParcialmente;
                    $percentualRecebidoParcialmente = ($valorRecebidoParcialmente * 100) / $totalParcialmente;

                    $registros = intval($_POST['totalRegistros']);
                    for($x=0; $x < $registros; $x++){
                        //$keyNome = 'inputCentroNome-'.$x;
                        $keyId = 'inputIdCentro-'.$x;
                        $centroCusto = $_POST[$keyId];
                        $valor = str_replace('.', '', $_POST['inputCentroValor-'.$x]);
                        $valor = str_replace(',', '.', $valor);

                        $valor = ($percentualAReceberParcialmente / 100) * $valor;

                        $sql = "INSERT INTO ContasAReceberXCentroCusto ( CARXCContasAReceber, CARXCCentroCusto, CARXCValor, CARXCUsuarioAtualizador, CARXCUnidade)
                                VALUES ( :iContasAReceber, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                        $result = $conn->prepare($sql);
    
                        $result->execute(array(
                            ':iContasAReceber' => $idContaAreceberParcial,
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
    
                        $valor = ($percentualRecebidoParcialmente / 100) * $valor;

                        $sql = "INSERT INTO ContasAReceberXCentroCusto ( CARXCContasAReceber, CARXCCentroCusto, CARXCValor, CARXCUsuarioAtualizador, CARXCUnidade)
                                VALUES ( :iContasAReceber, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                        $result = $conn->prepare($sql);
    
                        $result->execute(array(
                            ':iContasAReceber' => $idContaAReceber,
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
    
                        $sql = "INSERT INTO ContasAReceberXCentroCusto ( CARXCContasAReceber, CARXCCentroCusto, CARXCValor, CARXCUsuarioAtualizador, CARXCUnidade)
                                VALUES ( :iContasAReceber, :iCentroCusto, :iValor, :iUsuarioAtualizador, :iUnidade)";
                        $result = $conn->prepare($sql);
    
                        $result->execute(array(
                            ':iContasAReceber' => $idContaAReceber,
                            ':iCentroCusto' => $centroCusto,
                            ':iValor' => $valor,
                            ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                            ':iUnidade' => $_SESSION['UnidadeId']
                        ));
                    }
                }
            }

            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Lançamento incluído!!!";
            $_SESSION['msg']['tipo'] = "success";
        } catch (PDOException $e) {

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
        irpara("contasAReceber.php");
    }
}
//$count = count($row);

// SE TIVER EDITANDO 
if (isset($_POST['inputContasAReceberId']) && $_POST['inputContasAReceberId'] != 0) {
    try {
        $sql = "SELECT  CnAReId,
                        CnAReDtEmissao,  
                        CnARePlanoContas, 
                        CnAReCliente, 
                        CnAReDescricao, 
                        CnAReNumDocumento,
                        CnAReContaBanco, 
                        CnAReFormaPagamento,
                        CnAReVenda,
                        CnAReDtVencimento, 
                        CnAReValorAReceber, 
                        CnAReDtRecebimento, 
                        CnAReValorRecebido, 
                        CnAReTipoJuros, 
                        CnAReJuros, 
                        CnAReTipoDesconto, 
                        CnAReDesconto, 
                        CnAReObservacao,
                        CnAReNumCheque,                    
                        CnAReValorCheque,                  
                        CnAReDtEmissaoCheque,             
                        CnAReDtVencimentoCheque,           
                        CnAReBancoCheque,                 
                        CnAReAgenciaCheque,                
                        CnAReContaCheque,                  
                        CnAReNomeCheque,                  
                        CnAReCpfCheque, 
                        CnAReStatus, 
                        CnAReUsuarioAtualizador, 
                        CnAReUnidade,
                        FrPagChave,
                        SituaChave            
    		       FROM ContasAReceber
                   LEFT JOIN FormaPagamento on FrPagId = CnAReFormaPagamento
                   JOIN Situacao on SituaId = CnAReStatus
    		       WHERE CnAReUnidade = " . $_SESSION['UnidadeId'] . " and CnAReId = " .$_POST['inputContasAReceberId'] . "";

        $result = $conn->query($sql);
        $lancamento = $result->fetch(PDO::FETCH_ASSOC);

        // pesquisa o Centro de Custo
        $sqlCentroCusto = "SELECT CARXCContasAReceber, CARXCCentroCusto, CARXCValor, CnCusId, CnCusCodigo, CnCusNome, CnCusNomePersonalizado
            FROM ContasAReceberXCentroCusto
            JOIN CentroCusto on CnCusId = CARXCCentroCusto
            WHERE CARXCContasAReceber = " . $_POST['inputContasAReceberId'] . "";
        $resultCentroCusto = $conn->query($sqlCentroCusto);
        $rowCentroCusto = $resultCentroCusto->fetchAll(PDO::FETCH_ASSOC);

        $sqlLiquidacao = "SELECT CARXCCentroCusto
        FROM ContasAReceberXCentroCusto
        WHERE CARXCContasAReceber = " . $_POST['inputContasAReceberId'] . "";
        $resultItemCentroCusto = $conn->query($sqlLiquidacao);
        $itemCentroCusto = $resultItemCentroCusto->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo 'Error: ',  $e->getMessage(), "\n";
    }
}else if(isset($_POST['inputConciliacaoId']) && $_POST['inputConciliacaoId']) {
    try {
        $sql = "SELECT  CnAReId,
                        CnAReDtEmissao,  
                        CnARePlanoContas, 
                        CnAReCliente, 
                        CnAReDescricao, 
                        CnAReNumDocumento,
                        CnAReContaBanco, 
                        CnAReFormaPagamento,
                        CnAReVenda,
                        CnAReDtVencimento, 
                        CnAReValorAReceber, 
                        CnAReDtRecebimento, 
                        CnAReValorRecebido, 
                        CnAReTipoJuros, 
                        CnAReJuros, 
                        CnAReTipoDesconto, 
                        CnAReDesconto, 
                        CnAReObservacao,
                        CnAReNumCheque,                    
                        CnAReValorCheque,                  
                        CnAReDtEmissaoCheque,             
                        CnAReDtVencimentoCheque,           
                        CnAReBancoCheque,                 
                        CnAReAgenciaCheque,                
                        CnAReContaCheque,                  
                        CnAReNomeCheque,                  
                        CnAReCpfCheque, 
                        CnAReStatus, 
                        CnAReUsuarioAtualizador, 
                        CnAReUnidade,
                        FrPagChave,
                        SituaChave          
    		       FROM ContasAReceber
                   LEFT JOIN FormaPagamento on FrPagId = CnAReFormaPagamento
                   JOIN Situacao on SituaId = CnAReStatus
    		       WHERE CnAReUnidade = " . $_SESSION['UnidadeId'] . " and CnAReId = " .$_POST['inputConciliacaoId'] . "";

        $result = $conn->query($sql);
        $lancamento = $result->fetch(PDO::FETCH_ASSOC);

        // pesquisa o Centro de Custo
        $sqlCentroCusto = "SELECT CARXCContasAReceber, CARXCCentroCusto, CARXCValor, CnCusId, CnCusCodigo, CnCusNome, CnCusNomePersonaizado
            FROM ContasAReceberXCentroCusto
            JOIN CentroCusto on CnCusId = CARXCCentroCusto
            WHERE CARXCContasAReceber = " . $_POST['inputConciliacaoId'] . "";
        $resultCentroCusto = $conn->query($sqlCentroCusto);
        $rowCentroCusto = $resultCentroCusto->fetchAll(PDO::FETCH_ASSOC);

        $sqlLiquidacao = "SELECT CARXCCentroCusto
        FROM ContasAReceberXCentroCusto
        WHERE CARXCContasAReceber = " . $_POST['inputConciliacaoId'] . "";
        $resultItemCentroCusto = $conn->query($sqlLiquidacao);
        $itemCentroCusto = $resultItemCentroCusto->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo 'Error: ',  $e->getMessage(), "\n";
    }
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
     <!-- /Validação -->

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

            var input = document.getElementById('inputDataVencimento');
            input.addEventListener('change', function() {
                var agora = new Date();
                var escolhida = new Date(this.value);
                if (escolhida < agora) {
                    this.value = [agora.getFullYear(), agora.getMonth() + 1, agora.getDate()].map(v => v < 10 ? '0' + v : v).join('-');
                }
            });        

            let styleJurosDescontos = ''

            function gerarParcelas(parcelas, valorTotal, dataVencimento, periodicidade) {
                $("#parcelasContainer").html("")
                let descricao = $("#inputDescricao").val()

                let valorParcela = float2moeda(parseFloat(valorTotal) / parcelas)

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
                                        <input type="text" class="form-control" id="inputParcelaValorAReceber${i}" name="inputParcelaValorAReceber${i}" value="${valorParcela}">
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
                })
            }
            parcelamento()

            function limparCheque() {
                $("#inputNumCheque").val("");
                $("#inputValorCheque").val("");
                $("#inputDtVencimentoCheque").val("");
                $("#cmbBancoCheque").val("");
                $("#inputAgenciaCheque").val("");
                $("#inputContaCheque").val("");
                $("#inputNomeCheque").val("");
                $("#inputCpfCheque").val("");
            }

            function limparJurosDescontos() {
                $("#inputVencimentoJD").val("");
                $("#inputValorAReceberJD").val("");
                $("#inputJurosJD").val("");
                $("#inputDescontoJD").val("");
                $("#inputDataRecebimentoJD").val("");
                $("#inputValorTotalAReceber").val("");
            }

            function preencherJurosDescontos() {

                $valorAReceber = $("#inputValor").val();
                $dataVencimento = $("#inputDataVencimento").val();
                $dataRecebimento = $("#inputDataRecebimento").val();
                $valorTotalRecebido = $("#inputValorTotalRecebido").val();

                $("#inputVencimentoJD").val($dataVencimento);
                $("#inputValorAReceberJD").val($valorAReceber);
                $("#inputDataRecebimentoJD").val($dataRecebimento);

            }

            function habilitarRecebimento() {

                $("#habilitarRecebimento").on('click', (e) => {
                    e.preventDefault()

                    if (!$("#habilitarRecebimento").hasClass('clicado')) {
                        $valorTotalRecebido = $("#inputValor").val()
                        $dataRecebimento = new Date
                        $dia = parseInt($dataRecebimento.getDate()) <= 9 ?
                            `0${parseInt($dataRecebimento.getDate())}` : parseInt($dataRecebimento.getDate())
                        $mes = parseInt($dataRecebimento.getMonth()) + 1 <= 9 ?
                            `0${parseInt($dataRecebimento.getMonth()) + 1}` : parseInt($dataRecebimento.getMonth()) + 1
                        $ano = $dataRecebimento.getFullYear()

                        $fulldataRecebimento = `${$ano}-${$mes}-${$dia}`

                        $("#inputDataRecebimento").val($fulldataRecebimento)
                        $("#inputValorTotalRecebido").val($valorTotalRecebido).removeAttr('disabled')

                        styleJurosDescontos = document.getElementById('jurusDescontos').style

                        document.getElementById('jurusDescontos').style = "";

                        $("#habilitarRecebimento").addClass('clicado')
                        $("#habilitarRecebimento").html('Desabilitar Pagamento')
                        preencherJurosDescontos()

                        $("#camposRecebimento").fadeIn(200);

                    } else {

                        $("#inputDataRecebimento").val("")
                        $("#inputValorTotalRecebido").val("")
                        $("#inputValorTotalRecebido").attr('disabled', '')
                        document.getElementById('jurusDescontos').style =
                            "color: currentColor; cursor: not-allowed; opacity: 0.5; text-decoration: none; pointer-events: none;";

                        $("#habilitarRecebimento").removeClass('clicado')
                        $("#habilitarRecebimento").html('Habilitar Pagamento')
                        limparJurosDescontos()

                        $("#camposRecebimento").fadeOut(200);
                    }

                })
                $("#jurusDescontos")
            }
            habilitarRecebimento()

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
                })

                $("#salvarParcelas").on('click', function() {
                    $('#pageModalParcelar').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                })                         
            }
            modalParcelar()

            function modalCheque() {

                $('#btnCheque').on('click', (e) => {
                    e.preventDefault()
                    $('#pageModalCheque').fadeIn(200);

                })

                $('#modalCloseCheque').on('click', function() {
                    $('#pageModalCheque').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                    
                    limparCheque()
                })

                $("#salvarCheque").on('click', function() {
                    $('#pageModalCheque').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                })
                }
                modalCheque()

                $('#cmbFormaPagamento').on('change', function(e) {
				let filhos = $('#cmbFormaPagamento').children()
				let valorcmb = $('#cmbFormaPagamento').val()
				filhos.each((i, elem) => {
                        let formaPagamento = $(elem).attr('chaveformaPagamento')
                        let valOption = $(elem).attr('value')

                        if (valOption == valorcmb){

                            if (formaPagamento == 'CHEQUE') {
                                
                                $('#btnCheque').fadeIn('300')
                            } else {
                                $('#btnCheque').fadeOut('300')
                            }
                        }
				    })
			    })

            function modalJurosDescontos() {
                $('#jurusDescontos').on('click', (e) => {
                    e.preventDefault()
                    $('#pageModalJurosDescontos').fadeIn(200);
                    $('.cardJuDes').css('width', '500px').css('margin', '0px auto')

                    let dataVencimento = $("#inputDataVencimento").val()
                    let valor = $("#inputValor").val()

                    $("#inputValorAReceberJD").val(valor)
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
                let juros = 0;
                let valorTotal = 0;
                let desconto = 0;
                let jurosTipo = $("#cmbTipoJurosJD").val();
                let jurosValor = $("#inputJurosJD").val();
                let valorAReceber = moedatofloat($("#inputValorAReceberJD").val());
                let descontoTipo = $("#cmbTipoDescontoJD").val();
                let descontoValor = moedatofloat($("#inputDescontoJD").val());

                if (parseFloat(jurosValor) > 0) {
                    if (jurosTipo == "P") {
                        juros = (valorAReceber * (jurosValor / 100));
                    } else {
                        juros = jurosValor;
                    }
                } else {
                    juros = 0;
                }

                if (parseFloat(descontoValor) > 0) {
                    if (descontoTipo == "P") {
                        desconto = (valorAReceber * (descontoValor / 100));
                    } else {
                        desconto = descontoValor;
                    }
                    console.log('Desconto Total: ' + desconto);
                } else {
                    desconto = 0;
                }

                valorTotal = parseFloat(valorAReceber) + parseFloat(juros);
                valorTotal = valorTotal - parseFloat(desconto);

                $("#inputValorTotalAReceber").val(float2moeda(valorTotal))
                $("#inputValorTotalRecebido").val(float2moeda(valorTotal))
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
                    var menssagem = 'Por favor informe um valor a receber e em seguida o centro de custo!'
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
                    var menssagem = 'Os valores dos centros de custos devem bater com o total do Valor a receber (R$ '+parseFloat(response.val).toFixed(2).replace('.', ',')+') !'
                    alerta('Atenção', menssagem, 'error')
                    
                    return false
                }

                let valorTotal = $('#inputValor').val()
                let valorRecebido = $('#inputValorTotalRecebido').val()

                let valorTotalf = parseFloat(valorTotal.replace(".", "").replace(",", "."))
                let valorRecebidof = parseFloat(valorRecebido.replace(".", "").replace(",", "."))
                let valorRestante = (valorTotalf - valorRecebidof)

                let planoContas = $("#cmbPlanoContas").val()
                let cmbCliente = $("#cmbCliente").val()
                let inputDescricao = $("#inputDescricao").val()
                let cmbContaBanco = $("#cmbContaBanco").val()
                let cmbFormaPagamento = $("#cmbFormaPagamento").val()
                
                if (cmbFormaPagamento != ''){
                    
                    let formaPagamento = cmbFormaPagamento.split('#');
                    
                    let inputNumCheque = $("#inputNumCheque").val();
                    let inputValorCheque = $("#inputValorCheque").val();
                    let cmbBancoCheque = $("#cmbBancoCheque").val();
                    let inputAgenciaCheque = $("#inputAgenciaCheque").val();
                    let inputContaCheque = $("#inputContaCheque").val();
                    let inputNomeCheque = $("#inputNomeCheque").val();
                    let inputCpfCheque = $("#inputCpfCheque").val();

                    if (formaPagamento[1] == "CHEQUE" && (inputNumCheque == "" || inputValorCheque == "" || cmbBancoCheque == "" || inputAgenciaCheque == "" || inputContaCheque == "" || inputNomeCheque == "" || inputCpfCheque == "")) { 
                        alerta('Atenção','Você selecionou a forma de pagamento cheque, portanto, favor preencher os dados do cheque.')
                        return false;
                    }
                }

                if ($("#habilitarRecebimento").hasClass('clicado')) {
                    $("#cmbContaBanco").prop('required', true)
                    $("#cmbFormaPagamento").prop('required', true)
                }
                // && cmbContaBanco != '' && cmbFormaPagamento != '' && inputNumeroDocumento != ''
                if (planoContas != '' && cmbCliente != '' && inputDescricao != '') {
                    if (valorRecebidof < valorTotalf && valorRecebidof) {
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

                        $("#inputRecebimentoParcial").val(valorRestante)
                        $('#inputValor').val(valorRecebido)

                        // $dataRecebimento = $("#inputDataRecebimento").val()
                        // $valorTotalRecebido = $("#inputValorTotalRecebido").val()
                        if ($("#habilitarRecebimento").hasClass('clicado')) {
                            $("#cmbContaBanco").prop('required', true)
                            $("#cmbFormaPagamento").prop('required', true)

                            confirmaExclusao(document.lancamento,
                                "O valor recebido é menor que o valor total da conta. Será gerado uma nova conta com o valor restante. Deseja continuar?",
                                'contasAReceberNovoLancamento.php');
                        } else {
                            confirmaExclusao(document.lancamento,
                                "O valor recebido é menor que o valor total da conta. Será gerado uma nova conta com o valor restante. Deseja continuar?",
                                'contasAReceberNovoLancamento.php');
                        }

                        document.lancamento.submit()
                    } else {
                        if ($("#habilitarRecebimento").hasClass('clicado')) {

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
                    if ($("#habilitarRecebimento").hasClass('clicado')) {

                        $("#cmbContaBanco").prop('required', true)
                        $("#cmbFormaPagamento").prop('required', true)

                        $("#lancamento").submit()
                    } else {
                        $("#cmbContaBanco").prop('required', false)
                        $("#cmbFormaPagamento").prop('required', false)
                        $("#lancamento").submit()
                    }
                }
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
                        $('#inputValor').focus();
                        var menssagem = 'Por favor informe um valor a receber!'
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
                    var menssagem = 'Os valores dos centros de custos devem bater com o total do Valor a receber (R$ '+float2moeda(parseFloat(response.val))+') !'
                    alerta('Atenção', menssagem, 'error')
                }
            })

            centroCustoExiste()

            if(centroCustoExiste()) {
                let idConta = "<?php echo isset($lancamento['CnAReId']) ? $lancamento['CnAReId'] : 0; ?>"
                let movimentacao = "<?php echo isset($lancamento['CnAReMovimentacao']) ? true : false; ?>"
                let editavel = true
                
                if(contaSituacao()) {
                    editavel = (contaSituacao() == 'RECEBIDO') ? false : true;
                }

                let centros = $('#cmbCentroCusto').val();
                let tipoConta = 'RECEITA'
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

                                    totalCentroCusto += centro.CARXCValor

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
                                                <input type="" class="form-control-border Valor text-right pula" id="inputCentroValor-${x}" name="inputCentroValor-${x}" onChange="calculaValorTotal(${x})" onkeypress="pula(event)" value="`+float2moeda(centro.CARXCValor)+`" autocomplete="off" ${editavel}>
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
                                                <input type="" class="form-control-border Valor text-right pula" id="inputCentroValor-${x}" name="inputCentroValor-${x}" onChange="calculaValorTotal(${x})" onkeypress="pula(event)" value="`+float2moeda(centro.CARXCValor)+`" autocomplete="off" ${editavel} readOnly>
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
                            var menssagem = 'Há um centro de custo vazio ou igual a R$ 0,00!'
                            alerta('Atenção', menssagem, 'error')

                            return false
                        }
                    }
                }
                return true
        }

        function centroCustoExiste() {
            let existeCentroCusto = "<?php echo $existeCentroCusto = (isset($itemCentroCusto['CARXCCentroCusto'])) ? true : false; ?>"
            return existeCentroCusto
        }

        function contaSituacao() {
            let contaRecebida = "<?php echo $contaRecebida = (isset($lancamento['SituaChave'])) ? $lancamento['SituaChave'] : false; ?>"
            return contaRecebida
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
                    <input type="hidden" id="inputRecebimentoParcial" name="inputRecebimentoParcial">

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
                                    <h3 class="card-title"><?php if (!isset($lancamento)) { echo 'Novo'; } else { echo 'Editar'; }  ?> Lançamento - Contas a Receber</h3>
                                </div>

                                <div class="card-body">
                                    <?php
                                    if (isset($lancamento)) {
                                        echo '<input type="hidden" name="inputEditar" value="sim">';
                                        echo '<input type="hidden" name="inputContaId" value="' . $lancamento['CnAReId'] . '">';
                                    }
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="inputDataEmissao">Data de Emissão</label>
                                                <input type="date" id="inputDataEmissao" name="inputDataEmissao" class="form-control" placeholder="Data" value="<?php echo date("Y-m-d") ?>"  <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') echo 'disabled' ?>  readOnly>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="cmbCliente">Cliente <span class="text-danger">*</span></label>
                                                <select id="cmbCliente" name="cmbCliente" class="form-control form-control-select2" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') echo 'disabled' ?> required>
                                                    <option value="">Selecionar</option>
                                                    <?php
                                                    try {
                                                        $sql = "SELECT ClienId, ClienNome
                                                                  FROM Cliente
                                                                  JOIN Situacao 
                                                                    ON SituaId = ClienStatus
                                                                 WHERE ClienUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                                              ORDER BY ClienNome ASC";

                                                        $result = $conn->query($sql);
                                                        $rowCliente = $result->fetchAll(PDO::FETCH_ASSOC);
                                                        try {
                                                            foreach ($rowCliente as $item) {
                                                                if (isset($lancamento)) {
                                                                    if ($lancamento['CnAReCliente'] == $item['ClienId']) {
                                                                        print('<option value="' . $item['ClienId'] . '" selected>' . $item['ClienNome'] . '</option>');
                                                                    } else {
                                                                        print('<option value="' . $item['ClienId'] . '">' . $item['ClienNome'] . '</option>');
                                                                    }
                                                                } else {
                                                                    print('<option value="' . $item['ClienId'] . '">' . $item['ClienNome'] . '</option>');
                                                                }
                                                            }
                                                        } catch (Exception $e) {
                                                            echo 'Error: ',  $e->getMessage(), "\n";
                                                        }
                                                    } catch (Exception $e) {
                                                        echo 'Error: ',  $e->getMessage(), "\n";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="cmbPlanoContas">Plano de Contas <span class="text-danger">*</span></label>
                                                <select id="cmbPlanoContas" name="cmbPlanoContas" class="form-control form-control-select2" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') echo 'disabled' ?> required>
                                                    <option value="">Selecionar</option>
                                                    <?php
                                                    try {
                                                        $sql = "SELECT PlConId, PlConCodigo, PlConNome
                                                                FROM PlanoConta
                                                                JOIN Situacao  ON SituaId = PlConStatus
                                                                WHERE PlConUnidade = " . $_SESSION['UnidadeId'] . " and
                                                                PlConNatureza = 'R' and PlConTipo = 'A' and SituaChave = 'ATIVO'
                                                                ORDER BY PlConCodigo ASC";
                                                        $result = $conn->query($sql);
                                                        $rowPlanoContas = $result->fetchAll(PDO::FETCH_ASSOC);


                                                        try {
                                                            foreach ($rowPlanoContas as $item) {
                                                                if (isset($lancamento)) {
                                                                    if ($lancamento['CnARePlanoContas'] == $item['PlConId']) {
                                                                        print('<option value="' . $item['PlConId'] . '" selected>' . $item['PlConCodigo'] . ' - ' . $item['PlConNome'] . '</option>');
                                                                    } else {
                                                                        print('<option value="' . $item['PlConId'] . '">' . $item['PlConCodigo'] . ' - ' . $item['PlConNome'] . '</option>');
                                                                    }
                                                                } else {
                                                                    print('<option value="' . $item['PlConId'] . '">' . $item['PlConCodigo'] . ' - ' . $item['PlConNome'] . '</option>');
                                                                }
                                                            }
                                                        } catch (Exception $e) {
                                                            echo 'Error: ',  $e->getMessage(), "\n";
                                                        }
                                                    } catch (Exception $e) {
                                                        echo 'Error: ',  $e->getMessage(), "\n";
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
                                        <div class="col-8">
                                            <div class="form-group">
                                                <label for="inputDescricao">Descrição <span class="text-danger">*</span></label>
                                                <input type="text" id="inputDescricao" class="form-control" name="inputDescricao" rows="3" value="<?php if (isset($lancamento)) echo $lancamento['CnAReDescricao'] ?>" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') echo 'readOnly disabled' ?> required>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="inputNumeroDocumento">Nº Nota Fiscal/Documento</label>
                                                <input type="text" id="inputNumeroDocumento" name="inputNumeroDocumento" value="<?php if (isset($lancamento)) echo $lancamento['CnAReNumDocumento'] ?>" class="form-control"  <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') echo 'readOnly disabled' ?>>
                                            </div>
                                        </div>
                                        <!--
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="inputNumDocDaVenda">Num. Doc - Venda</label>
                                                <input type="text" id="inputOrdemCompra" name="inputOrdemCompra" value="<?php if (isset($lancamento)) echo $lancamento['OrComNumero'] ?>" class="form-control" readOnly>
                                            </div>
                                        </div>
                                        -->
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-lg-6">
                                            <div class="d-flex flex-column">
                                                <div class="row justify-content-between m-0">
                                                    <h5>Valor à Receber</h5>
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
                                                                <input type="date" id="inputDataVencimento" value="<?php isset($lancamento) ? print($lancamento['CnAReDtVencimento']) : print($dataInicio) ?>" name="inputDataVencimento" class="form-control removeValidacao" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') echo 'readOnly disabled' ?>>
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="inputValor">Valor</label>
                                                                <input type="text" onKeyUp="moeda(this)" maxLength="12" id="inputValor" name="inputValor" value="<?php if (isset($lancamento)) echo mostraValor($lancamento['CnAReValorAReceber']) ?>" class="form-control removeValidacao"  <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') echo 'readOnly disabled' ?>>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <div class="d-flex flex-column">
                                                <div class="row justify-content-between m-0">
                                                    <h5>Valor Recebido</h5>
                                                    <div class="row pr-2" style="margin-top: 5px;">
                                                        <?php  
                                                        if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] != 'RECEBIDO' || !isset($lancamento['SituaChave'])) {
                                                            print('
                                                            <a id="habilitarRecebimento" href="#" >Habilitar Recebimento</a>
                                                            <span class="mx-2">|</span>
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
                                                                <label for="inputDataRecebimento">Data do
                                                                    Pagamento</label>
                                                                <input type="date" id="inputDataRecebimento" value="<?php if (isset($lancamento)) echo $lancamento['CnAReDtRecebimento'] ?>" name="inputDataRecebimento" class="form-control removeValidacao"  <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') echo 'disabled' ?> readonly>
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="inputValorTotalRecebido">Valor Total
                                                                    Recebido</label>
                                                                <input type="text" onKeyUp="moeda(this)" maxLength="12" id="inputValorTotalRecebido" name="inputValorTotalRecebido" value="<?php if (isset($lancamento)) echo mostraValor($lancamento['CnAReValorRecebido']) ?>" class="form-control removeValidacao"  <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') echo 'readOnly disabled' ?> disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    if (isset($lancamento) && $lancamento['CnAReContaBanco'] != null && $lancamento['CnAReContaBanco'] != 0) {
                                        $mostrar = '';
                                    } else {
                                        $mostrar = 'style="display:none;"';
                                    }
                                    ?>
                                    <div id="camposRecebimento" class="row justify-content-between" <?php echo $mostrar; ?>>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="cmbContaBanco">Conta/Banco <span class="text-danger">*</span></label>
                                                <select id="cmbContaBanco" name="cmbContaBanco" class="form-control form-control-select2" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') echo 'disabled' ?>>
                                                    <option value="">Selecionar</option>
                                                    <?php
                                                    try {
                                                        $sql = "SELECT CnBanId, CnBanNome
                                                                FROM ContaBanco
                                                                JOIN Situacao on SituaId = CnBanStatus
                                                                WHERE CnBanUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                                                ORDER BY CnBanNome ASC";
                                                        $result = $conn->query($sql);
                                                        $rowContaBanco = $result->fetchAll(PDO::FETCH_ASSOC);

                                                        try {
                                                            foreach ($rowContaBanco as $item) {
                                                                if (isset($lancamento)) {
                                                                    if ($lancamento['CnAReContaBanco'] == $item['CnBanId']) {
                                                                        print('<option value="' . $item['CnBanId'] . '" selected>' . $item['CnBanNome'] . '</option>');
                                                                    } else {
                                                                        print('<option value="' . $item['CnBanId'] . '">' . $item['CnBanNome'] . '</option>');
                                                                    }
                                                                } else {
                                                                    print('<option value="' . $item['CnBanId'] . '">' . $item['CnBanNome'] . '</option>');
                                                                }
                                                            }
                                                        } catch (Exception $e) {
                                                            echo 'Error: ',  $e->getMessage(), "\n";
                                                        }
                                                    } catch (Exception $e) {
                                                        echo 'Error: ',  $e->getMessage(), "\n";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="cmbFormaPagamento">Forma de Pagamento <span class="text-danger">*</span></label>
                                                <select id="cmbFormaPagamento" name="cmbFormaPagamento" class="form-control form-control-select2" <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') echo 'disabled' ?>>
                                                    <option value="">Selecionar</option>
                                                    <?php
                                                    try {
                                                        $sql = "SELECT FrPagId, FrPagNome, FrPagChave
                                                                FROM FormaPagamento
                                                                JOIN Situacao on SituaId = FrPagStatus
                                                                WHERE FrPagUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                                                ORDER BY FrPagNome ASC";
                                                        $result = $conn->query($sql);
                                                        $rowFormaPagamento = $result->fetchAll(PDO::FETCH_ASSOC);

                                                        try {
                                                            foreach ($rowFormaPagamento as $item) {
                                                                if (isset($lancamento)) {
                                                                    if ($lancamento['CnAReFormaPagamento'] == $item['FrPagId']) {
                                                                        print('<option value="' . $item['FrPagId'] . '#'.$item['FrPagChave']. '" chaveformaPagamento="'.$item['FrPagChave']. '" selected>' . $item['FrPagNome'] . '</option>');
                                                                    } else {
                                                                        print('<option value="' . $item['FrPagId'] . '#'.$item['FrPagChave']. '" chaveformaPagamento="'.$item['FrPagChave']. '" >' . $item['FrPagNome'] . '</option>');
                                                                    }
                                                                } else {
                                                                    print('<option value="' . $item['FrPagId'] . '#'.$item['FrPagChave']. '" chaveformaPagamento="'.$item['FrPagChave']. '" >' . $item['FrPagNome'] . '</option>');
                                                                }
                                                            }
                                                        } catch (Exception $e) {
                                                            echo 'Error: ',  $e->getMessage(), "\n";
                                                        }
                                                    } catch (Exception $e) {
                                                        echo 'Error: ',  $e->getMessage(), "\n";
                                                    }

                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2" style="margin-top: 30px;">
                                            <?php
                                                $mostraCheque = "";

                                                // se tiver editando
                                                if (isset($lancamento)){  
                                                    if ($lancamento['FrPagChave'] != 'CHEQUE') {
                                                        $mostraCheque = "display:none";    
                                                    } 
                                                } else{ // se for novo
                                                    $mostraCheque = "display:none";   
                                                }
                                                
                                              print('<a href="#" id="btnCheque" style="margin-top: 5px; '. $mostraCheque .' " class="icon-pencil">  Cheque</a>');
                                            ?>  
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="inputObservacao">Observação</label>
                                                <textarea id="inputObservacao" class="form-control" name="inputObservacao" rows="3"  <?php  if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') echo 'readOnly disabled' ?>></textarea>
                                            </div>
                                        </div>
                                    </div>
                                        <?php 
                                            if(isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') {
                                                echo' <button id="salvar" class="btn btn-principal" disabled>Salvar</button>';
                                            }else if ($atualizar) {
                                                echo' <button id="salvar" class="btn btn-principal">Salvar</button>';
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

                    <!--------------------------------------------------------------------------------------->
                    <!--Modal Cheque-->
                    <div id="pageModalCheque" class="custon-modal">
                        <div class="custon-modal-container">
                            <div class="card custon-modal-content">
                                <div class="custon-modal-title">
                                    <i class=""></i>
                                    <p class="h3">Detalhamento do Cheque</p>
                                    <i class=""></i>
                                </div>
                                <div class="px-3 pt-3">
                                    <div class="d-flex flex-row p-1">
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="inputNumCheque">Nº do Cheque<span class="text-danger">*</span></label>
                                                <input type="text" id="inputNumCheque" name="inputNumCheque" class="form-control" placeholder="Número do Cheque" value="<?php if (isset($lancamento)) echo $lancamento['CnAReNumCheque'] ?>">
                                            </div>
                                        </div>	
                                        <div class='col-lg-3'>
                                            <div class="form-group">
                                                <label for="inputValorCheque">Valor<span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="text" id="inputValorCheque" onKeyUp="moeda(this)" maxLength="12" name="inputValorCheque" class="form-control" value="<?php if (isset($lancamento)) echo $lancamento['CnAReValorCheque'] ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group" >
                                                <label for="inputDtEmissaoCheque">Data da Emissão<span class="text-danger">*</span></label>
                                                <input id="inputDtEmissaoCheque" class="form-control" type="date" name="inputDtEmissaoCheque" value="<?php if (isset($lancamento)) echo $lancamento['CnAReDtEmissaoCheque'] ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="inputDtVencimentoCheque">Data do Vencimento<span class="text-danger">*</span></label>
                                                <input id="inputDtVencimentoCheque" class="form-control" type="date" name="inputDtVencimentoCheque" value="<?php if (isset($lancamento)) echo $lancamento['CnAReDtVencimentoCheque'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-3 pt-3">
                                    <div class="d-flex flex-row p-1">
                                        <div class="col-lg-6">
											<label for="cmbBancoCheque">Banco<span class="text-danger">*</span></label>
											<select id="cmbBancoCheque" name="cmbBancoCheque" class="form-control form-control-select2" value="<?php if (isset($lancamento)) echo $lancamento['CnAReBancoCheque'] ?>">
												<option value="">Selecione um banco</option>
                                                <?php 
													$sql = "SELECT BancoId, BancoCodigo, BancoNome
															FROM Banco
															JOIN Situacao on SituaId = BancoStatus
															WHERE SituaChave = 'ATIVO'
															ORDER BY BancoCodigo ASC";
													$result = $conn->query($sql);
													$row = $result->fetchAll(PDO::FETCH_ASSOC);
													
													foreach ($row as $item){
														print('<option value="'.$item['BancoId'].'">'.$item['BancoCodigo'] . " - " . $item['BancoNome'].'</option>');
													}
                                                
												?>
											</select>
										</div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="inputAgenciaCheque">Agência<span class="text-danger">*</span></label>
                                                <input type="text" id="inputAgenciaCheque" name="inputAgenciaCheque" class="form-control" placeholder="Número da Agência" value="<?php if (isset($lancamento)) echo $lancamento['CnAReAgenciaCheque'] ?>">
                                            </div>
                                        </div>	
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="inputContaCheque">Conta<span class="text-danger">*</span></label>
                                                <input type="text" id="inputContaCheque" name="inputContaCheque" class="form-control" placeholder="Número da Conta" value="<?php if (isset($lancamento)) echo $lancamento['CnAReContaCheque'] ?>">
                                            </div>
                                        </div>	     
                                    </div>
                                </div> 
                               <div class="px-3 pt-3">
                                    <div class="d-flex flex-row p-1">
                                        <div class="col-lg-9">
                                            <div class="form-group">
                                                <label for="inputNomeCheque">Nome<span class="text-danger">*</span></label>
                                                <input type="text" id="inputNomeCheque" name="inputNomeCheque" class="form-control" placeholder="Nome Completo" value="<?php if (isset($lancamento)) echo $lancamento['CnAReNomeCheque'] ?>">
                                            </div>
                                        </div>	
                                        
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="inputCpfCheque">CPF<span class="text-danger">*</span></label>
                                                <input type="text" id="inputCpfCheque" name="inputCpfCheque" class="form-control" placeholder="CPF" data-mask="999.999.999-99" value="<?php if (isset($lancamento)) echo $lancamento['CnAReCpfCheque'] ?>">
                                            </div>	
                                        </div>
                                    </div>
                                </div> 
                                
                                <div class="card-footer mt-2 d-flex flex-column">
                                    <div class="row" style="margin-top: 10px;">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <a class="btn btn-lg btn-principal" id="salvarCheque">Salvar</a>
                                                <a id="modalCloseCheque" class="btn btn-basic" role="button">Cancelar</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--------------------------------------------------------------------------------------->

                    <!--------------------------------------------------------------------------------------->
                    <!--Modal Juros e Descontos-->
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
                                            <label for="inputValorAReceberJD">Valor à Receber</label>
                                            <input id="inputValorAReceberJD" onKeyUp="moeda(this)" maxLength="12" class="form-control" type="text" name="inputValorAReceberJD" readOnly>
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
                                            <label for="inputDataRecebimentoJD">Data do Pagamento</label>
                                            <input id="inputDataRecebimentoJD" value="<?php echo date("Y-m-d") ?>" class="form-control" type="date" name="inputDataRecebimentoJD" readOnly>
                                        </div>
                                        <div class="form-group">
                                            <label for="inputValorTotalAReceber">Valor Total à Receber</label>
                                            <input id="inputValorTotalAReceber" onKeyUp="moeda(this)" maxLength="12" class="form-control" type="text" name="inputValorTotalAReceber" readOnly>
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

                                                    $disabled = ((isset($lancamento['SituaChave']) && $lancamento['SituaChave'] == 'RECEBIDO') || (isset($lancamento['CnAPaMovimentacao']) && $empresaPublica))? 'disabled':'';

                                                    $selectCencust = "<select id='cmbCentroCusto' $disabled name='cmbCentroCusto[]' class='form-control select' multiple='multiple' autofocus data-fouc>";
                                                    
                                                    if(isset($itemCentroCusto['CARXCCentroCusto'])) {
                                                        foreach($listCentroCusto as $CentroCusto){
                                                            $seleciona = '';
                                                            foreach ($rowCentroCusto as $centroCusto) {
                                                                if($CentroCusto['CnCusId'] == $centroCusto['CARXCCentroCusto'])
                                                                    $seleciona = "selected";
                                                            }

                                                            $cnCusDescricao = $CentroCusto["CnCusNomePersonalizado"] === NULL ? $CentroCusto["CnCusNome"] : $CentroCusto["CnCusNomePersonalizado"];

                                                            $selectCencust .= "<option value='".$CentroCusto['CnCusId']."' $seleciona>".$cnCusDescricao."</option>";
                                                        }
                                                    }else {
                                                        foreach($listCentroCusto as $CentroCusto){
                                                            $cnCusDescricao = $CentroCusto["CnCusNomePersonalizado"] === NULL ? $CentroCusto["CnCusNome"] : $CentroCusto["CnCusNomePersonalizado"];
                                                            $selectCencust .= "<option value='".$CentroCusto['CnCusId']."'>".$cnCusDescricao."</option>";
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

                                <div class="card-body" id="relacaoCentroCusto" style="<?php echo $visibiçidade = (isset($itemCentroCusto['CARXCCentroCusto'])) ? 'display: block;' : 'display: none;'; ?>">
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