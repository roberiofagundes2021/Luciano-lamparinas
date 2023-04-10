<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Financeiro / Movimentação do Financeiro / Nova Transferência';

include('global_assets/php/conexao.php');

if (isset($_POST['inputPermissionAtualiza'])){
  $_SESSION['MovFinancPermissionAtualiza'] = $_POST['inputPermissionAtualiza'];
}

if (isset($_POST['inputDataEmissao'])) {
    if (isset($_POST['inputEditar'])) { //EDIÇÃO
      try {
        if (isset($_POST['inputValorTotal'])) {
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

        $sql = "UPDATE ContasAPagar SET CnAPaPlanoContas = :iPlanoContas, CnAPaFornecedor = :iFornecedor, CnAPaContaBanco = :iContaBanco, CnAPaFormaPagamento = :iFormaPagamento, CnAPaNumDocumento = :sNumDocumento,
                                        CnAPaNotaFiscal = :sNotaFiscal, CnAPaDtEmissao = :dateDtEmissao, CnAPaOrdemCompra = :iOrdemCompra, CnAPaDescricao = :sDescricao, CnAPaDtVencimento = :dateDtVencimento, CnAPaValorAPagar = :fValorAPagar,
                                        CnAPaDtPagamento = :dateDtPagamento, CnAPaValorPago = :fValorPago, CnAPaObservacao = :sObservacao, CnAPaStatus = :iStatus, CnAPaUsuarioAtualizador = :iUsuarioAtualizador, CnAPaUnidade = :iUnidade,
                                        CnAPaTipoJuros = :sTipoJuros, CnAPaJuros = :fJuros, CnAPaTipoDesconto = :sTipoDesconto, CnAPaDesconto = :fDesconto
        WHERE CnAPaId = " . $_POST['inputContaId'] . "";
        $result = $conn->prepare($sql);

        $result->execute(array(
            ':iPlanoContas' => $_POST['cmbPlanoContas'],
            ':iFornecedor' => 0,
            ':iContaBanco' => $_POST['cmbContaBanco'],
            ':iFormaPagamento' => $_POST['cmbFormaPagamento'],
            ':sNumDocumento' => isset($_POST['inputNumeroDocumento']) ? $_POST['inputNumeroDocumento'] : null,
            ':sNotaFiscal' => null,
            ':dateDtEmissao' => $_POST['inputDataEmissao'],
            ':iOrdemCompra' => null,
            ':sDescricao' => $_POST['inputDescricao'],
            ':dateDtVencimento' => $_POST['inputDataDaTransferencia'],
            ':fValorAPagar' => floatval(gravaValor($_POST['inputValorTotal'])),
            ':dateDtPagamento' => $_POST['inputDataDaTransferencia'],
            ':fValorPago' => isset($_POST['inputValorTotal']) ? floatval(gravaValor($_POST['inputValorTotal'])) : null,
            ':sObservacao' => isset($_POST['inputObservacao']) ? $_POST['inputObservacao'] : null,
            ':sTipoJuros' => isset($_POST['cmbTipoJurosJD']) ? $_POST['cmbTipoJurosJD'] : null,
            ':fJuros' => isset($_POST['inputJurosJD']) ? floatval(gravaValor($_POST['inputJurosJD'])) : null,
            ':sTipoDesconto' => isset($_POST['cmbTipoDescontoJD']) ? $_POST['cmbTipoDescontoJD'] : null,
            ':fDesconto' => isset($_POST['inputDescontoJD']) ? floatval(gravaValor($_POST['inputDescontoJD'])) : null,
            ':iStatus' => $situacao['SituaId'],
            ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
            ':iUnidade' => $_SESSION['UnidadeId']
        ));

        $_SESSION['msg']['titulo'] = "Sucesso";
        $_SESSION['msg']['mensagem'] = "Transferência editada!!!";
        $_SESSION['msg']['tipo'] = "success";
        
      } catch (PDOException $e) {
        $_SESSION['msg']['titulo'] = "Erro";
        $_SESSION['msg']['mensagem'] = "Erro ao editar transferência!!!";
        $_SESSION['msg']['tipo'] = "error";

        echo 'Error: ' . $e->getMessage();
        die;
      }
        
    } else { //INSERÇÃO
      //TRANSFERENCIA 
      try {
        $last_id = 0;
        $sql = "SELECT SituaId
                  FROM Situacao
                  WHERE SituaChave = 'PAGO'
                ";
        $result = $conn->query($sql);
        $situacao = $result->fetch(PDO::FETCH_ASSOC);


        $sql = "INSERT INTO ContasTransferencia ( CnTraDtEmissao, CnTraDescricao, CnTraNumDocumento, 
                                                  CnTraContaOrigem, CnTraContaDestino, CnTraFormaPagamento, 
                                                  CnTraDtTransferencia, CnTraValor, CnTraObservacao, 
                                                  CnTraStatus, CnTraUsuarioAtualizador, CnTraUnidade)

                VALUES ( :dateCnTraDtEmissao, :sCnTraDescricao, :sCnTraNumDocumento, :iCnTraContaOrigem, 
                         :iCnTraContaDestino, :iCnTraFormaPagamento, :dateCnTraDtTransferencia, 
                         :fCnTraValor, :sCnTraObservacao, :iCnTraStatus, :iCnTraUsuarioAtualizador, 
                         :iCnTraUnidade )";

        $result = $conn->prepare($sql);
        $result->execute(array(
          ':dateCnTraDtEmissao'       => isset($_POST['inputDataEmissao']) ? $_POST['inputDataEmissao'] : null,
          ':sCnTraDescricao'          => isset($_POST['inputDescricao']) ? $_POST['inputDescricao'] : null,
          ':sCnTraNumDocumento'       => isset($_POST['inputNumeroDocumento']) ? $_POST['inputNumeroDocumento'] : null,
          ':iCnTraContaOrigem'        => isset($_POST['cmbContaBancoOrigem']) ? intval($_POST['cmbContaBancoOrigem']) : null,
          ':iCnTraContaDestino'       => isset($_POST['cmbContaBancoDestino']) ? intval($_POST['cmbContaBancoDestino']) : null,
          ':iCnTraFormaPagamento'     => isset($_POST['cmbFormaPagamento']) ? intval($_POST['cmbFormaPagamento']) : null,
          ':dateCnTraDtTransferencia' => isset($_POST['inputDataDaTransferencia']) ? $_POST['inputDataDaTransferencia'] : null,
          ':fCnTraValor'              => isset($_POST['inputValorTotal']) ? floatval(gravaValor($_POST['inputValorTotal'])) : null,
          ':sCnTraObservacao'         => isset($_POST['inputObservacao']) ? $_POST['inputObservacao'] : null,
          ':iCnTraStatus'             => 16,
          ':iCnTraUsuarioAtualizador' => isset($_SESSION['UsuarId']) ? $_SESSION['UsuarId'] : null,
          ':iCnTraUnidade'            => isset($_SESSION['UnidadeId']) ? $_SESSION['UnidadeId'] : null,
        ));

        $last_id  = $conn->lastInsertId();

        if ($last_id > 0) {
          //CONTAS A PAGAR
          try {
            $sql = "SELECT SituaId
                      FROM Situacao
                      WHERE SituaChave = 'PAGO'
                    ";
            $result = $conn->query($sql);
            $situacao = $result->fetch(PDO::FETCH_ASSOC);
    
    
            $sql = "INSERT INTO ContasAPagar (CnAPaPlanoContas, CnAPaFornecedor, CnAPaContaBanco, CnAPaFormaPagamento, CnAPaNumDocumento,
                                          CnAPaNotaFiscal, CnAPaDtEmissao, CnAPaOrdemCompra, CnAPaDescricao, CnAPaDtVencimento, CnAPaValorAPagar,
                                          CnAPaDtPagamento, CnAPaValorPago, CnAPaObservacao, CnAPaTransferencia, CnAPaTipoJuros, CnAPaJuros, 
                                          CnAPaTipoDesconto, CnAPaDesconto, CnAPaStatus, CnAPaUsuarioAtualizador, CnAPaUnidade)
                    VALUES (:iPlanoContas, :iFornecedor, :iContaBanco, :iFormaPagamento,:sNumDocumento, :sNotaFiscal, :dateDtEmissao, :iOrdemCompra,
                            :sDescricao, :dateDtVencimento, :fValorAPagar, :dateDtPagamento, :fValorPago, :sObservacao, :iTransferencia, :sTipoJuros, :fJuros, 
                            :sTipoDesconto, :fDesconto, :iStatus, :iUsuarioAtualizador, :iUnidade)";
            $result = $conn->prepare($sql);
    
            $result->execute(array(
                ':iPlanoContas'         => null,
                ':iFornecedor'          => 0,
                ':iContaBanco'          => isset($_POST['cmbContaBancoOrigem']) ? $_POST['cmbContaBancoOrigem'] : null,
                ':iFormaPagamento'      => isset($_POST['cmbFormaPagamento']) ? $_POST['cmbFormaPagamento'] : null,
                ':sNumDocumento'        => isset($_POST['inputNumeroDocumento']) ? $_POST['inputNumeroDocumento'] : null,
                ':sNotaFiscal'          => null,
                ':dateDtEmissao'        => isset($_POST['inputDataEmissao']) ? $_POST['inputDataEmissao'] : null,
                ':iOrdemCompra'         => null,
                ':sDescricao'           => isset($_POST['inputDescricao']) ? $_POST['inputDescricao'] : null,
                ':dateDtVencimento'     => isset($_POST['inputDataDaTransferencia']) ? $_POST['inputDataDaTransferencia'] : null,
                ':fValorAPagar'         => isset($_POST['inputValorTotal']) ? floatval(gravaValor($_POST['inputValorTotal'])) : null,
                ':dateDtPagamento'      => isset($_POST['inputDataDaTransferencia']) ? $_POST['inputDataDaTransferencia'] : null,
                ':fValorPago'           => isset($_POST['inputValorTotal']) ? floatval(gravaValor($_POST['inputValorTotal'])) : null,
                ':sObservacao'          => isset($_POST['inputObservacao']) ? $_POST['inputObservacao'] : null,
                ':iTransferencia'       => isset($last_id) ? intval($last_id) : null,
                ':sTipoJuros'           => null,
                ':fJuros'               => null,
                ':sTipoDesconto'        => null,
                ':fDesconto'            => null,
                ':iStatus'              => 12,
                ':iUsuarioAtualizador'  => isset($_SESSION['UsuarId']) ? $_SESSION['UsuarId'] : null,
                ':iUnidade'             => isset($_SESSION['UnidadeId']) ? $_SESSION['UnidadeId'] : null,
            ));
          } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
            die;
          }
    
          //CONTAS A RECEBER
          try {
            if (isset($_POST['cmbFormaDePagamento'])){
              $aFormaPagamento = explode('#', $_POST['cmbFormaDePagamento']);                                
              $idFormaPagamento = $aFormaPagamento[0];
            }
        
            $sql = "SELECT SituaId
                      FROM Situacao
                    WHERE SituaChave = 'RECEBIDO'";

            $result = $conn->query($sql);
            $situacao = $result->fetch(PDO::FETCH_ASSOC);
    
            $sql = "INSERT INTO ContasAReceber (CnAReDtEmissao, CnARePlanoContas, CnAReCliente, CnAReDescricao, CnAReNumDocumento, CnAReContaBanco, CnAReFormaPagamento, CnAReVenda, CnAReDtVencimento, CnAReValorAReceber, CnAReDtRecebimento, CnAReValorRecebido, CnAReTipoJuros, CnAReJuros, CnAReTipoDesconto, CnAReDesconto, CnAReObservacao, CnAReTransferencia, CnAReNumCheque, CnAReValorCheque, CnAReDtEmissaoCheque, CnAReDtVencimentoCheque, CnAReBancoCheque, CnAReAgenciaCheque, CnAReContaCheque, CnAReNomeCheque, CnAReCpfCheque, CnAReStatus, CnAReUsuarioAtualizador, CnAReUnidade)
                        VALUES ( :dDtEmissao, :iPlanoContas, :iCliente, :sDescricao, :sNumDocumento, :iContaBanco, :iFormaPagamento, :iVenda, :dDtVencimento, :fValorAReceber, :dDtRecebimento, :fValorRecebido, :sTipoJuros, :fJuros, :sTipoDesconto, :fDesconto,  :sObservacao, :iTransferencia, :sNumCheque, :fValorCheque, :dDtEmissaoCheque, :dDtVencimentoCheque, :iBancoCheque, :iAgenciaCheque, :iContaCheque, :iNomeCheque, :iCpfCheque, :iStatus, :iUsuarioAtualizador, :iUnidade)";
    
            $result = $conn->prepare($sql);
            $result->execute(array(
                ':dDtEmissao'           => isset($_POST['inputDataEmissao']) ? $_POST['inputDataEmissao'] : null,
                ':iPlanoContas'         => null,
                ':iCliente'             => 0,
                ':sDescricao'           => $_POST['inputDescricao'],
                ':sNumDocumento'        => isset($_POST['inputNumeroDocumento']) ? $_POST['inputNumeroDocumento'] : null,
                ':iContaBanco'          => isset($_POST['cmbContaBancoDestino']) ? intval($_POST['cmbContaBancoDestino']) : null,
                ':iFormaPagamento'      => isset($idFormaPagamento) ? $idFormaPagamento : null,
                ':iVenda'               => null,
                ':dDtVencimento'        => $_POST['inputDataDaTransferencia'],
                ':fValorAReceber'       => floatval(gravaValor($_POST['inputValorTotal'])),
                ':dDtRecebimento'       => isset($_POST['inputDataDaTransferencia']) ? $_POST['inputDataDaTransferencia'] : null,
                ':fValorRecebido'       => isset($_POST['inputValorTotal']) ? floatval(gravaValor($_POST['inputValorTotal'])) : null,
                ':sTipoJuros'           => null,
                ':fJuros'               => null,
                ':sTipoDesconto'        => null,
                ':fDesconto'            => null,
                ':sObservacao'          => isset($_POST['inputObservacao']) ? $_POST['inputObservacao'] : null,
                ':iTransferencia'       => isset($last_id) ? intval($last_id) : null,
                ':sNumCheque'           => null,
                ':fValorCheque'         => null,
                ':dDtEmissaoCheque'     => null,
                ':dDtVencimentoCheque'  => null,
                ':iBancoCheque'         => null,
                ':iAgenciaCheque'       => null,
                ':iContaCheque'         => null,
                ':iNomeCheque'          => null,
                ':iCpfCheque'           => null,   
                ':iStatus'              => 14,
                ':iUsuarioAtualizador'  => intval($_SESSION['UsuarId']),
                ':iUnidade'             => intval($_SESSION['UnidadeId'])
            ));
    
          } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
            die;
          }
        
          $_SESSION['msg']['titulo'] = "Sucesso";
          $_SESSION['msg']['mensagem'] = "Transferência incluída!!!";
          $_SESSION['msg']['tipo'] = "success";
        }

      } catch (PDOException $e) {
        $_SESSION['msg']['titulo'] = "Erro";
        $_SESSION['msg']['mensagem'] = "Erro ao incluir Transferência!!!";
        $_SESSION['msg']['tipo'] = "error";
        echo 'Error: ' . $e->getMessage();
        die;
      }
    }

  irpara("movimentacaoFinanceiraConciliacao.php");
}

if (isset($_POST['inputMovimentacaoFinanceiraId'])) {
    $sql = "SELECT  *
              FROM  ContasTransferencia
             WHERE  CnTraUnidade = " . $_SESSION['UnidadeId'] . "
               AND  CnTraId = " . $_POST['inputMovimentacaoFinanceiraId'] . "";
    $result = $conn->query($sql);
    $lancamento = $result->fetch(PDO::FETCH_ASSOC);
}

$dataInicio = date("Y-m-d");

$visibilidadeResumoFinanceiro = isset($_SESSION['ResumoFinanceiro']) && $_SESSION['ResumoFinanceiro'] ? 'sidebar-right-visible' : ''; 
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Lamparinas | Lançar Contas</title>

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
  <script src="global_assets/js/demo_pages/form_layouts.js"></script>
  <script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

  <script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
  <script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
  <!-- /theme JS files -->

  <script type="text/javascript">
  $(document).ready(function() {

    var input = document.getElementById('inputDataDaTransferencia');
    input.addEventListener('change', function() {
        var agora = new Date();
        var escolhida = new Date(this.value);
        if (escolhida < agora) {
            this.value = [agora.getFullYear(), agora.getMonth() + 1, agora.getDate()].map(v => v < 10 ? '0' + v : v).join('-');
        }
    });  

    function preencheDatas() {
      $dataRecebimento = new Date
      $dia = parseInt($dataRecebimento.getDate()) <= 9 ? `0${parseInt($dataRecebimento.getDate())}` : parseInt($dataRecebimento.getDate());
      $mes = parseInt($dataRecebimento.getMonth()) + 1 <= 9 ? `0${parseInt($dataRecebimento.getMonth()) + 1}` : parseInt($dataRecebimento.getMonth()) + 1;
      $ano = $dataRecebimento.getFullYear();

      $fullDataRecebimento = `${$ano}-${$mes}-${$dia}`;

      if ($('#inputDataEmissao').val() == "")
        $('#inputDataEmissao').val($fullDataRecebimento);
      if ($('#inputDataDaTransferencia').val() == "")
        $('#inputDataDaTransferencia').val($fullDataRecebimento);
    }

    function salvar() {
      const inputDataEmissao = $('#inputDataEmissao').val();
      const inputDescricao = $('#inputDescricao').val();
      const cmbContaBancoOrigem = $('#cmbContaBancoOrigem').val();
      const cmbContaBancoDestino = $('#cmbContaBancoDestino').val();
      const cmbFormaPagamento = $('#cmbFormaPagamento').val();
      const inputDataDaTransferencia = $('#inputDataDaTransferencia').val();
      const inputValorTotal = $('#inputValorTotal').val();

      if (inputDataEmissao === '' || inputDescricao === '' || cmbContaBancoOrigem === '' || cmbContaBancoDestino === ' ' || cmbFormaPagamento === '' || inputDataDaTransferencia === '' || inputValorTotal === '') {
        $("#lancamento").submit();
      } else if (cmbContaBancoOrigem === cmbContaBancoDestino) {
        alerta('Atenção', 'Você selecionou a conta de origem igual a conta de destino!')
        return false;
      } else {
        $("#lancamento").submit();
      }
    }

    $("#salvar").on('click', (e) => {
      e.preventDefault();
      if($('#inputValorTotal').val() == '0,00') {
        var menssagem = 'Por favor informe o Valor Total Pago!';
        alerta('Atenção', menssagem, 'error');
        $('#inputValorTotal').focus();

        return false;
      }

      salvar();
    });

    preencheDatas();
  });


  function selecionaTipo(tipo) {
    if (tipo == 'P') {
      window.location.href = "movimentacaoFinanceiraPagamento.php";
    } else if (tipo == 'R') {
      window.location.href = "movimentacaoFinanceiraRecebimento.php";
    } else
      window.location.href = "movimentacaoFinanceiraTransferencia.php";
  };
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
          <div class="row">
            <div class="col-lg-12">
              <!-- Basic responsive configuration -->
              <div class="card">
                <div class="card-header header-elements-inline">
                  <?php 
                  if(isset($_SESSION['MovFinancPermissionAtualiza'])) {
                    echo "<h3 class='card-title'>Editar Transferência</h3>";
                  }else {
                    echo "<h3 class='card-title'>Nova Transferência</h3>";
                  }
                  ?>
                </div>

                <div class="card-body">
                  <?php
                    if (isset($lancamento)) {
                        echo '<input type="hidden" name="inputContaId" value="' . $lancamento['CnTraId'] . '">';
                    }
                  ?>

                  <div class="row">
                    <div class="col-lg-2">
                      <div class="form-group">
                        <label for="inputDataEmissao">Data de Emissão <span class="text-danger">*</span></label>
                        <input type="date" id="inputDataEmissao" name="inputDataEmissao" class="form-control" placeholder="Data" value="<?php echo date("Y-m-d") ?>"  readOnly>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group">
                        <label for="inputDescricao">Descrição <span class='text-danger'>*</span></label>
                        <input type="text" id="inputDescricao" name="inputDescricao" class="form-control" placeholder="Descrição" value="<?php if (isset($lancamento)){ echo $lancamento['CnTraDescricao']; } else {echo 'Transferência entre contas';} ?>" readonly required>
                      </div>
                    </div>

                    <div class="col-lg-4">
                      <div class="form-group">
                        <label for="inputNumeroDocumento">Número Documento</label>
                        <input type="text" id="inputNumeroDocumento" name="inputNumeroDocumento" class="form-control" placeholder="Nº Documento" value="<?php if (isset($lancamento)) echo $lancamento['CnTraNumDocumento']; ?>" <?php if (isset($lancamento)) echo 'readonly' ?>>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-4">
                      <div class="form-group">
                        <label for="cmbContaBancoOrigem">Conta Origem <span class="text-danger">*</span></label>
                        <select id="cmbContaBancoOrigem" name="cmbContaBancoOrigem" class="form-control form-control-select2"  <?php if (isset($lancamento)) echo 'disabled' ?> required>
                          <option value="" selected>Todos</option>
                          <?php
                              $sql = "SELECT CnBanId,
                                             CnBanNome
                                        FROM ContaBanco
                                        JOIN Situacao 
                                          ON SituaId = CnBanStatus
                                       WHERE CnBanUnidade = " . $_SESSION['UnidadeId'] . " 
                                         AND SituaChave = 'ATIVO'
                                    ORDER BY CnBanNome ASC";
                              $result = $conn->query($sql);
                              $rowContaBanco = $result->fetchAll(PDO::FETCH_ASSOC);

                              foreach ($rowContaBanco as $item) {
                                if (isset($lancamento)) {
                                    if ($lancamento['CnTraContaOrigem'] == $item['CnBanId']) {
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

                    <div class="col-lg-4">
                      <div class="form-group">
                        <label for="cmbContaBancoDestino">Conta Destino <span class="text-danger">*</span></label>
                        <select id="cmbContaBancoDestino" name="cmbContaBancoDestino" class="form-control form-control-select2"  <?php if (isset($lancamento)) echo 'disabled' ?> required>
                          <option value="" selected>Todos</option>
                          <?php
                              $sql = "SELECT CnBanId,
                                             CnBanNome
                                        FROM ContaBanco
                                        JOIN Situacao 
                                          ON SituaId = CnBanStatus
                                       WHERE CnBanUnidade = " . $_SESSION['UnidadeId'] . " 
                                         AND SituaChave = 'ATIVO'
                                    ORDER BY CnBanNome ASC";
                              $result = $conn->query($sql);
                              $rowContaBanco = $result->fetchAll(PDO::FETCH_ASSOC);

                              foreach ($rowContaBanco as $item) {
                                if (isset($lancamento)) {
                                    if ($lancamento['CnTraContaDestino'] == $item['CnBanId']) {
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

                    <div class="col-lg-4">
                      <div class="form-group">
                        <label for="cmbFormaPagamento">Forma de Pagamento <span class="text-danger">*</span></label>
                        <select id="cmbFormaPagamento" name="cmbFormaPagamento" class="form-control form-control-select2"  <?php if (isset($lancamento)) echo 'disabled' ?> required>
                          <option value="" selected>Todos</option>
                          <?php
                              $sql = "SELECT FrPagId,
                                             FrPagNome
                                        FROM FormaPagamento
                                        JOIN Situacao 
                                          ON SituaId = FrPagStatus
                                       WHERE FrPagUnidade = " . $_SESSION['UnidadeId'] . " 
                                         AND SituaChave = 'ATIVO'
                                    ORDER BY FrPagNome ASC";
                              $result = $conn->query($sql);
                              $rowFormaPagamento = $result->fetchAll(PDO::FETCH_ASSOC);
                              
                              foreach ($rowFormaPagamento as $item) {
                                if (isset($lancamento)) {
                                    if ($lancamento['CnTraFormaPagamento'] == $item['FrPagId']) {
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
                    <div class="col-12 col-lg-6">
                      <div class="d-flex flex-column">

                        <div class="row justify-content-between m-0">
                          <h5>Valor Transferido</h5>
                        </div>
                        <div class=" card">
                          <div class="card-body p-4" style="background-color: #f8f8f8; border: 1px solid #ccc">
                            <div class="row">
                              <div class="form-group col-6">
                                <label for="inputDataDaTransferencia">Data da Transferência <span class="text-danger">*</span></label>
                                <input type="date" id="inputDataDaTransferencia" name="inputDataDaTransferencia" class="form-control" placeholder="Data do Pagamento" value="<?php if (isset($lancamento)) echo $lancamento['CnTraDtTransferencia']; ?>" <?php if (isset($lancamento)) echo 'readonly' ?> required>
                              </div>
                              <div class="form-group col-6">
                                <label for="inputValorTotal">Valor Total Transferido (=) <span class="text-danger">*</span> </label>
                                <input type="text" onKeyUp="moeda(this)" maxLength="12" id="inputValorTotal" name="inputValorTotal" class="form-control" placeholder='0,00' value="<?php if (isset($lancamento)) echo number_format($lancamento['CnTraValor'], 2, ',', '.'); ?>" <?php if (isset($lancamento)) echo 'readonly' ?> required>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label for="inputObservacao">Observação</label>
                        <textarea id="inputObservacao" class="form-control" name="inputObservacao" rows="3" <?php if (isset($lancamento)) echo 'readonly' ?>><?php if (isset($lancamento)) echo $lancamento['CnTraObservacao']; ?></textarea>
                      </div>
                    </div>
                  </div>

                  <?php if (isset($lancamento)) { ?>
                    <style>
                      .btn.voltar {
                        border: 1px solid #2196f3;
                      }
                    </style>

                    <div class="row">
                      <div class="col-lg-6">
                        <div class="form-group">
                          <?php if($_SESSION['Conciliacao'] === true) { ?>
                            <a href="movimentacaoFinanceiraConciliacao.php" class="btn voltar">Voltar</a>
                          <?php } else { ?>
                            <a href="movimentacaoFinanceira.php" class="btn voltar">Voltar</a>
                          <?php } ?>
                        </div>
                      </div>

                      <div class="col-lg-6" style="text-align: right; color: red;">
                        <i class="icon-info3" data-popup="tooltip" data-placement="bottom" data-original-title="" title=""></i>Esse registro não pode ser alterado. <br/>Caso haja necessidade de alteração deve ser feito um estorno.
                      </div>
                    </div>
                  <?php } else { ?>
                  <?php 
                    //if (isset($_SESSION['MovFinancPermissionAtualiza'])) {
                        echo' <button id="salvar" class="btn btn-principal">Salvar</button>';
                    //}
                  ?>
                    
                    <?php if($_SESSION['Conciliacao'] === true) { ?>
                      <a href="movimentacaoFinanceiraConciliacao.php" class="btn">Cancelar</a>
                    <?php } else { ?>
                      <a href="movimentacaoFinanceira.php" class="btn">Cancelar</a>
                    <?php } ?>
                  <?php } ?>
                </div>

              </div>
              <!-- /basic responsive configuration -->

            </div>
          </div>

          <!-- /info blocks -->

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