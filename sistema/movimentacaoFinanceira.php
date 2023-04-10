<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Relação de Movimentações Financeiras';

include('global_assets/php/conexao.php');

$_SESSION['Conciliacao'] = false;

/*ClienId, ClienNome, ClienCpf, ClienCnpj, ClienTelefone, ClienCelular, ClienStatus, Cate*/
try {
    $sql = "SELECT *
		FROM Cliente
	    WHERE ClienUnidade = " . $_SESSION['UnidadeId'] . "
		ORDER BY ClienNome ASC";
    $result = $conn->query($sql);
    $row = $result->fetchAll(PDO::FETCH_ASSOC);
    //$count = count($row);
} catch (Exception $e) {
    echo ($e);
}

$d = date("d");
$m = date("m");
$Y = date("Y");

// $dataInicio = date("Y-m-01"); //30 dias atrás
$dataInicio = date("Y-m-d");
$dataFim = date("Y-m-d");

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

  <!-- Theme JS files -->
  <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
  <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

  <script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
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

      let resultadosConsulta = '';
      let inputsValues = {};

      $.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data			

      /* Início: Tabela Personalizada */
      $('#tblMovimentacaoFinanceira').DataTable({
        "order": [
          [0, "asc"]
        ],
        autoWidth: false,
        responsive: true,
        paginate: false,
        columnDefs: [{
            orderable: true, //Data
            width: "8%",
            targets: [0]
          },
          {
            orderable: true, //Histórico
            width: "18%",
            targets: [1]
          },
          {
            orderable: true, //Cliente/Fornecedor
            width: "16%",
            targets: [2]
          },
          {
            orderable: true, //Conta Caixa
            width: "12%",
            targets: [3]
          },
          {
            orderable: true, //Nª doc
            width: "6%",
            targets: [4]
          },
          {
            orderable: true, //Entrada
            width: "12%",
            targets: [5]
          },
          {
            orderable: true, //Saída
            width: "12%",
            targets: [6]
          },
          {
            orderable: true, //Saldo
            width: "12%",
            targets: [7]
          },
          {
            orderable: false, //Ações
            width: "4%",
            targets: [8]
          }
        ],
        dom: '<"datatable-header"fl><"datatable-scroll-wrap"t>',
        language: {
          decimal: ",",
          thousands: ".",
          search: '<span>Filtro:</span> _INPUT_',
          searchPlaceholder: 'filtra qualquer coluna...',
          lengthMenu: '<span>Mostrar:</span> _MENU_',
          paginate: {
            'first': 'Primeira',
            'last': 'Última',
            'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;',
            'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;'
          }
        }
      });

     /* function excluirConta() {
        let contas = $('.excluirConta').each((i, elem) => {
          $(elem).on('click', (e) => {
            const id = $(elem).attr('idContaExcluir');
            const tipo = $(elem).attr('tipo');

            $('#idMov').val(id);
            $('#tipoMov').val(tipo);

            e.preventDefault;
            confirmaExclusao(document.contaExclui, "Tem certeza que deseja excluir essa Conta?", `movimentacaoFinanceiraExclui.php`);
            document.contaExclui.submit();
          })
        })

      }
      excluirConta(); 
      function atualizaTotal() {
        let childres = $('tbody').children()
        let total = 0
        let linhas = childres.splice(1, childres.length)
        linhas.forEach(elem => {
          let listaTds = $(elem).children()
          let valor = $(listaTds[5]).html()
          let valorFormFloat = parseFloat(valor.replace(".", "").replace(",", "."))

          total += valorFormFloat
        })
        $('#footer-total').remove()

        if (total < 0) {
          divTotal = `<div id='footer-total' style='position:absolute; left: 86.8%; font-weight: bold; width: 200px; color:red;'>Total: ${float2moeda(total)}</div>`
        } else {
          divTotal = `<div id='footer-total' style='position:absolute; left: 86.8%; font-weight: bold; width: 200px; color:green;'>Total: ${float2moeda(total)}</div>`
        }

        $('.datatable-footer').append(divTotal);
      }
      */

      function Filtrar() {
        let cont = false;
        let saldoTfoot = 0;

        const msg = $('<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty"><img src="global_assets/images/lamparinas/loader.gif" style="width: 120px"></td></tr>');

        $('tbody').html(msg);

        const periodoDe = $('#inputPeriodoDe').val();
        const ate = $('#inputAte').val();
        const contaBanco = $('#cmbContaBanco').val();
        const centroDeCustos = $('#cmbCentroDeCustos').val();
        const planoContas = $('#cmbPlanoContas').val();
        const FormaPagamento = $('#cmbFormaDeRecebimento').val();
        const inputPermissionAtualiza = $("#inputPermissionAtualiza").val()
        const inputPermissionExclui = $("#inputPermissionExclui").val()
        const statusArray = $('#cmbStatus').val().split('|');
        const status = statusArray[0];
        const statusTipo = statusArray[1];
        const urlFiltraGrid = "movimentacaoFinanceiraFiltra.php";
        const urlConsultaSaldoInicial = "consultaSaldoInicial.php";

        var inputsValuesConsulta = {
          inputData: periodoDe
        }; 

        //Consulta saldo anterior
        $.ajax({
          type: "POST",
          url: urlConsultaSaldoInicial,
          dataType: "json",
          data: inputsValuesConsulta,
          success: function(resposta) {
            saldoTfoot = resposta;
            $("#saldoAnterior").html('<span class="badge bg-secondary badge-pill p-2" style="font-size: 100%;">Saldo Anterior: R$ '+resposta+'</span>')
          }
        })

        inputsValues = {
          inputPeriodoDe: periodoDe,
          inputAte: ate,
          cmbContaBanco: contaBanco,
          cmbCentroDeCustos: centroDeCustos,
          cmbPlanoContas: planoContas,
          cmbFormaDeRecebimento: FormaPagamento,
          cmbStatus: status,
          statusTipo: statusTipo,
          permissionAtualiza: inputPermissionAtualiza,
          permissionExclui: inputPermissionExclui
        };
        
        $.ajax({
          type: "POST",
          url: urlFiltraGrid,
          dataType: "json",
          data: inputsValues,
          success: function(resposta) {
            //|--Aqui é criado o DataTable caso seja a primeira vez q é executado e o clear é para evitar duplicação na tabela depois da primeira pesquisa
            let table 
            table = $('#tblMovimentacaoFinanceira').DataTable()
            table = $('#tblMovimentacaoFinanceira').DataTable().clear().draw()
            //--|
            
            table = $('#tblMovimentacaoFinanceira').DataTable()

            let contador = 0
            let rowNode
            let entrada = 0
            let entradaTotal = 0
            let saida = 0
            let saidaTotal = 0
            let saldo = 0
            let saldoTotal = 0

            resposta.forEach(item => {
              rowNode = table.row.add(item.data).draw().node()

              saldo = parseFloat(item.data[7].replace(",", "."));
              
              // adiciona os atributos nas tags <td>
              $(rowNode).find('td').eq(5).attr('style', 'text-align: right; color: green;')
              $(rowNode).find('td').eq(6).attr('style', 'text-align: right; color: red;')

              if(saldo >= 0) {
                $(rowNode).find('td').eq(7).attr('style', 'text-align: right; color: green;')
              }else {
                $(rowNode).find('td').eq(7).attr('style', 'text-align: right; color: red;')
              }

              entrada = (item.data[5] != null) ? item.data[5] : '0,00'
              entrada = entrada.replace(".", "").replace(",", ".")
              entradaTotal += parseFloat(entrada)

              saida = (item.data[6] != null) ? item.data[6] : '0,00'
              saida = saida.replace(".", "").replace(",", ".")
              saidaTotal += parseFloat(saida)

              saldo = (item.data[7] != null) ? item.data[7] : '0,00'
              saldo = saldo.replace(".", "").replace(",", ".")
              saldoTotal += parseFloat(saldo)

              contador++
            })

            $('#legenda').remove() //Para evitar que os valores se sobreescreva
            let legenda = document.querySelector(".datatable-header")
            legenda.insertAdjacentHTML('beforeend', `<div id='legenda' style='text-align: right; padding-top: 2%; width: 100%;'> Mostrando 1 a ${contador} de ${contador} registros</div>`)
            
            sinalNegativo = (saidaTotal == 0) ? '' : '-'
            corSaldoTotal = (saldoTotal >= 0) ? 'green' : 'red'

            // total = `
            // <tr id="total" role="row" class="even">
            //   <td></td>
            //   <td></td>
            //   <td></td>
            //   <td></td>
            //   <td style="text-align: right; font-size: .8125rem; font-weight: bold;">Total:</td>
            //   <td style="text-align: right; font-size: .8125rem; white-space: nowrap; font-weight: bold; color: green;">${float2moeda(entradaTotal)}</td>
            //   <td style="text-align: right; font-size: .8125rem; white-space: nowrap; font-weight: bold; color: red;">${sinalNegativo} ${float2moeda(saidaTotal)}</td>
            //   <td style="text-align: right; font-size: .8125rem; white-space: nowrap; font-weight: bold; color: ${corSaldoTotal};">${(saldoTfoot)}</td>
            //   <td></td>
            // </tr>`
            total = `
            <tr id="total" role="row" class="even">
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td style="text-align: right; font-size: .8125rem; font-weight: bold;">Total:</td>
              <td style="text-align: right; font-size: .8125rem; white-space: nowrap; font-weight: bold; color: green;">${float2moeda(entradaTotal)}</td>
              <td style="text-align: right; font-size: .8125rem; white-space: nowrap; font-weight: bold; color: red;">${sinalNegativo} ${float2moeda(saidaTotal)}</td>
              <td style="text-align: right; font-size: .8125rem; white-space: nowrap; font-weight: bold; color: ${corSaldoTotal};">${(saldo)}</td>
              <td></td>
            </tr>`
            
            $('#total').remove() 

            $('#tblMovimentacaoFinanceira tfoot').append(total)
          },
          error: function(e) { 
            $('#legenda').remove()      
            let legenda = document.querySelector(".datatable-header")
            legenda.insertAdjacentHTML('beforeend', `<div id='legenda' style='text-align: right; padding-top: 2%; width: 100%;'> Mostrando 0 a 0 de 0 registros</div>`)

            let tabelaVazia = $(
              '<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty">Sem resultados...</td></tr>'
            )

            $('tbody').html(tabelaVazia)
          }
        })

          /*
        $.post(
          url,
          inputsValues,
          (data) => {
            if (data) {
              $('tbody').html(data)
              $('#imprimir').removeAttr('disabled')
              resultadosConsulta = data

             // excluirConta();
              atualizaTotal();

            } else {
              let msg2 = $(
                '<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty">Sem resultados...</td></tr>'
              )
              $('tbody').html(msg2)
              $('#imprimir').attr('disabled', '')
              $('#footer-total').remove()
            }
          }
        );
        */
      }

      $('#submitPesquisar').on('click', (e) => {
        e.preventDefault()
        
        Filtrar();
      })

      Filtrar();

     /* $('#novoLacamento').on('click', (e) => {
        location.href = "movimentacaoFinanceiraPagamento.php";
        return false;
      })*/

      function imprime() {
        let url = 'movimentacaoFinanceiraImprime.php';

        $('#imprimir').on('click', (e) => {
          console.log(resultadosConsulta);
          e.preventDefault()
          if (resultadosConsulta) {
            $('#inputResultado').val(resultadosConsulta)
            $('#inputDataDe_imp').val(inputsValues.inputPeriodoDe)
            $('#inputDataAte_imp').val(inputsValues.inputAte)
            $('#cmbContaBanco_imp').val(inputsValues.cmbContaBanco)
            $('#cmbCentroDeCustos_imp').val(inputsValues.cmbCentroDeCustos)
            $('#cmbPlanoContas_imp').val(inputsValues.cmbPlanoContas)
            $('#cmbFormaDeRecebimento_imp').val(inputsValues.cmbFormaDeRecebimento)
            $('#inputStatus_imp').val(inputsValues.cmbStatus)
            $('#inputStatusTipo_imp').val(inputsValues.statusTipo)

            $('#formImprime').attr('action', url)

            $('#formImprime').submit()
          }
        })
      }
      imprime()    
    });

    //Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
    function atualizaMovimentacaoFinanceira(Permission, MovimentacaoFinanceiraId, Tipo) {

      document.getElementById('inputMovimentacaoFinanceiraId').value = MovimentacaoFinanceiraId;
      document.getElementById('inputPermissionAtualiza').value = Permission;

      if (Tipo == 'novo' || Tipo == 'edita') {
            document.formMovimentacaoFinanceira.action = "movimentacaoFinanceiraTransferencia.php";
      } else if (Tipo == 'exclui') {
          if(Permission){
              confirmaExclusao(document.formMovimentacaoFinanceira, "Tem certeza que deseja excluir essa Movimentação ?", "movimentacaoFinanceiraExclui.php");
          } else{
              alerta('Permissão Negada!','');
              return false;
          }
      }else if(Tipo == 'estornar') {
        return false;
      }            

      document.formMovimentacaoFinanceira.submit();
    }     

    //Essa função dá um submit no formulário de estornar conta
		function estornaConta() {
      let justificativa = document.getElementById('inputJustificativa').value 

      if((justificativa) == '') {
          alerta('Atenção', 'Este campo é obrigatório!', 'error');
          $('#inputJustificativa').focus()
          return false
      }

      document.getElementById('inputContaJustificativa').value = justificativa;
      document.formMovimentacaoFinanceira.action = "contasEstornar.php";
      document.formMovimentacaoFinanceira.submit();
    }

    //Essa função preenche o pop-up Justificativa de estorno
    function estornoJustificativa(justificativa) {
        $('#txtJustificativa').val(justificativa);
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

        <!-- Info blocks -->
        <div class="row">
          <div class="col-lg-12">

            <!-- Basic responsive configuration -->
            <div class="card">
              <div class="card-header">
                <div class="header-elements-inline">
                  <h3 class="card-title">Relação de Movimentações Financeiras</h3>
                </div>
                <br>
                <p>A relação abaixo faz referência às movimentações financeiras da empresa <?php echo($_SESSION['EmpreNomeFantasia']) ?></p>
              </div>

              <div class="card-body">

                <form id="formImprime" method="POST" target="_blank">
                  <input id="inputResultado" type="hidden" name="resultados"></input>
                  <input id="inputDataDe_imp" type="hidden" name="inputDataDe_imp"></input>
                  <input id="inputDataAte_imp" type="hidden" name="inputDataAte_imp"></input>
                  <input id="cmbContaBanco_imp" type="hidden" name="cmbContaBanco_imp"></input>
                  <input id="cmbCentroDeCustos_imp" type="hidden" name="cmbCentroDeCustos_imp"></input>
                  <input id="cmbPlanoContas_imp" type="hidden" name="cmbPlanoContas_imp"></input>
                  <input id="cmbFormaDeRecebimento_imp" type="hidden" name="cmbFormaDeRecebimento_imp"></input>
                  <input id="inputStatus_imp" type="hidden" name="inputStatus_imp"></input>
                  <input id="inputStatusTipo_imp" type="hidden" name="inputStatusTipo_imp"></input>
                  <input id="inputTipoFiltro_imp" type="hidden" name="inputTipoFiltro_imp"></input>
                </form>
                <!--  <form name="contaExclui" method="POST">
                  <input type="hidden" name="idMov" id="idMov">
                  <input type="hidden" name="tipoMov" id="tipoMov">
                </form> -->
                <form name="formMovimentacao" method="post" class="p-3">
                  <div class="row">
                    <div class="col-lg-2">
                      <div class="form-group">
                        <label for="inputPeriodoDe">Período de</label>
                        <div class="input-group">
                          <span class="input-group-prepend">
                            <span class="input-group-text"><i class="icon-calendar22"></i></span>
                          </span>
                          <input type="date" id="inputPeriodoDe" name="inputPeriodoDe" class="form-control"  min="1800-01-01" max="2100-12-12" value="<?php 
                          if (isset($_SESSION['MovFinancPeriodoDe'])) {
                            echo $_SESSION['MovFinancPeriodoDe'];
                          }else 
                            echo $dataInicio; 
                        ?>">
        </div>
                      </div>
                    </div>

                    <div class="col-lg-2">
                      <div class="form-group">
                        <label for="inputAte">Até</label>
                        <div class="input-group">
                          <span class="input-group-prepend">
                            <span class="input-group-text"><i class="icon-calendar22"></i></span>
                          </span>
                          <input type="date" id="inputAte" name="inputAte" class="form-control" min="1800-01-01" max="2100-12-12" value="<?php 
                            if (isset($_SESSION['MovFinancAte'])) 
                              echo $_SESSION['MovFinancAte'];
                            else 
                              echo $dataFim; 
                          ?>">
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-4">
                      <div class="form-group">
                        <label for="cmbContaBanco">Conta / Banco</label>
                        <select id="cmbContaBanco" name="cmbContaBanco" class="form-control form-control-select2">
                          <option value="">Todos</option>
                          <?php
                            $sql = "SELECT CnBanId,CnBanNome
                                    FROM ContaBanco
                                    JOIN Situacao ON SituaId = CnBanStatus
                                    WHERE CnBanUnidade = $_SESSION[UnidadeId] and SituaChave = 'ATIVO'
                                    ORDER BY CnBanNome ASC";
                            $result = $conn->query($sql);
                            $rowContaBanco = $result->fetchAll(PDO::FETCH_ASSOC);
               
                            foreach ($rowContaBanco as $item) {
                              if (isset( $item['CnBanId'])) {
                                  if (isset($_SESSION['MovFinancContaBanco'])) {
                                      if ( $item['CnBanId'] == $_SESSION['MovFinancContaBanco']) {
                                          print('<option value="' .  $item['CnBanId'] . '" selected>' . $item['CnBanNome']. '</option>');
                                      } else {
                                          print('<option value="' .  $item['CnBanId'] . '">' . $item['CnBanNome'] . '</option>');
                                      }
                                  } else {
                                      print('<option value="' .  $item['CnBanId'] . '">' . $item['CnBanNome'] . '</option>');
                                  }
                              }
                            }

                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-4">
                      <div class="form-group">
                        <label for="cmbCentroDeCustos">Centro de Custos</label>
                        <select id="cmbCentroDeCustos" name="cmbCentroDeCustos" class="form-control form-control-select2">
                          <option value="">Todos</option>
                          <?php
                            $sql = "SELECT CnCusId, CnCusCodigo, CnCusNome, CnCusNomePersonalizado
                                      FROM CentroCusto
                                      JOIN Situacao 
                                        ON SituaId = CnCusStatus
                                      WHERE CnCusUnidade = " . $_SESSION['UnidadeId'] . " 
                                        and SituaChave = 'ATIVO'
                                  ORDER BY CnCusCodigo ASC";
                            $result = $conn->query($sql);
                            $rowCentroDeCustos = $result->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($rowCentroDeCustos as $item) {

                              $cnCusDescricao = $item['CnCusNomePersonalizado'] === NULL ? $item['CnCusNome'] : $item['CnCusNomePersonalizado'];

                              if (isset($_SESSION['MovFinancCentroDeCustos'])) {
                                  if ($item['CnCusId'] == $_SESSION['MovFinancCentroDeCustos']) {
                                      print('<option value="' . $item['CnCusId'] . '" selected>' . $item['CnCusCodigo'] . ' - ' . $cnCusDescricao . '</option>');
                                  } else {
                                      print('<option value="' . $item['CnCusId'] . '">' . $item['CnCusCodigo'] . ' - ' . $cnCusDescricao . '</option>');
                                  }
                              } else {
                                  print('<option value="' . $item['CnCusId'] . '">' . $item['CnCusCodigo'] . ' - ' . $cnCusDescricao . '</option>');
                              }
                            }

                           ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="cmbPlanoContas">Plano de Contas</label>
                            <select id="cmbPlanoContas" name="cmbPlanoContas" class="form-control form-control-select2">
                              <option value="">Todos</option>
                                 <?php
                                    $sql = "SELECT PlConId, PlConCodigo, PlConNome
                                              FROM PlanoConta
                                              JOIN Situacao 
                                                ON SituaId = PlConStatus
                                              WHERE PlConUnidade = " . $_SESSION['UnidadeId'] . " 
                                                AND SituaChave = 'ATIVO'
                                          ORDER BY PlConCodigo ASC";
                                    $result = $conn->query($sql);
                                    $rowPlanoContas = $result->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($rowPlanoContas as $item) {
                                      if (isset($_SESSION['MovFinancPlanoContas'])) {
                                          if ($item['PlConId'] == $_SESSION['MovFinancPlanoContas']) {
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
                    

                    <div class="col-lg-3">
                      <div class="form-group">
                        <label for="cmbFormaDeRecebimento">Forma de Pagamento/Recebimento</label>
                        <select id="cmbFormaDeRecebimento" name="cmbFormaDeRecebimento" class="form-control form-control-select2">
                          <option value="">Todos</option>
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

                            try {
                               
                              foreach ($rowFormaPagamento as $item) {
                                if (isset($item['FrPagId'])) {
                                    if (isset($_SESSION['MovFinancFormaPagamento'])) {
                                        if ($item['FrPagId'] == $_SESSION['MovFinancFormaPagamento']) {
                                            print('<option value="' . $item['FrPagId'] . '" selected>' . $item['FrPagNome'] . '</option>');
                                        } else {
                                            print('<option value="' . $item['FrPagId'] . '">' . $item['FrPagNome'] . '</option>');
                                        }
                                    } else {
                                        print('<option value="' . $item['FrPagId'] . '">' . $item['FrPagNome'] . '</option>');
                                    }
                                }
                              }

                            } catch (Exception $e) {
                                echo 'Exceção capturada: ',  $e->getMessage(), "\n";
                            }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-2">
                      <div class="form-group">
                        <label for="cmbStatus">Status</label>
                        <select id="cmbStatus" name="cmbStatus" class="form-control form-control-select2">
                          <option value="">Todos</option>
                          <?php
                            try {
                                $sql = "SELECT SituaId, SituaNome, SituaChave
                                        FROM Situacao
                                        WHERE SituaStatus = 1 AND SituaChave IN ('PAGO', 'RECEBIDO', 'TRANSFERIDO')
                                        ORDER BY SituaNome ASC";
                                $result = $conn->query($sql);
                                $rowSituacao = $result->fetchAll(PDO::FETCH_ASSOC);

                                try {
                                    foreach ($rowSituacao as $item) {
                                      if (isset($_SESSION['MovFinancStatus'])) {
                                          if ($item['SituaId'] == $_SESSION['MovFinancStatus']) {
                                              print('<option value="' . $item['SituaId'] . '|' . $item['SituaChave'] . '" selected>' . $item['SituaNome'] . '</option>');
                                          } else {
                                              print('<option value="' . $item['SituaId'] . '|' . $item['SituaChave'] . '">' . $item['SituaNome'] . '</option>');
                                          }
                                      } else {
                                          print('<option value="' . $item['SituaId'] . '|' . $item['SituaChave'] . '">' . $item['SituaNome'] . '</option>');
                                      }
                                    }
                                    $seleciona = isset($_SESSION['ContRecStatus']) && $_SESSION['ContRecStatus'] == 0 ? 'selected' : '';
                                    print('<option value="Estornado|ESTORNADO" '.$seleciona.'>Estornado</option>');
                                } catch (Exception $e) {
                                    echo 'Exceção capturada: ',  $e->getMessage(), "\n";
                                }
                            } catch (Exception $e) {
                                echo 'Exceção capturada: ',  $e->getMessage(), "\n";
                            }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="text-right col-lg-4 pt-3">
                      <button id="submitPesquisar" class="btn btn-principal">Pesquisar</button>

                      <button id="imprimir" class="btn bg-secondary"><i class="icon-printer2"></i></button>
                    </div>

                  </div>
                </form>

                <h6 id="saldoAnterior" class="text-right mb-0">
                </h6>

                <table class="table" id="tblMovimentacaoFinanceira">
                  <thead>
                    <tr class="bg-slate">
                      <th>Data</th>
                      <th>Histórico</th>
                      <th>Cliente / Fornecedor</th>
                      <th>Conta / Banco</th>
                      <th>Documento</th>
                      <th style='text-align: right;'>Entrada</th>
                      <th style='text-align: right;'>Saída</th>
                      <th style='text-align: right;'>Saldo</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>

                  </tbody>
                  <tfoot>

                  </tfoot>
                </table>

              </div>

            </div>

            <!-- /basic responsive configuration -->
          </div>
        </div>

        <!--Modal estornar-->
        <div id="modal_mini-estornar" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-xs">
                <div class="modal-content">
                    <div class="custon-modal-title">
                        <i class=""></i>
                        <p class="h3">Estornar conta</p>
                        <i class=""></i>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="inputJustificativa">Justificativa<span class="text-danger"> *</span></label>
                            <div class="input-group">
                                <textarea id="inputJustificativa" class="form-control" name="inputJustificativa" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-basic" data-dismiss="modal">Cancelar</button>
                        <button onclick= estornaConta() type="button" class="btn bg-slate">Estornar</button>
                    </div>
                </div>
            </div>
        </div>
        <!--Modal justificativa de estorno-->
        <div id="modal_mini-justificativa-estorno" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-xs">
                <div class="modal-content">
                    <div class="custon-modal-title">
                        <i class=""></i>
                        <p class="h3">Justificativa do estorno</p>
                        <i class=""></i>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <div class="input-group">
                                <textarea rows="5" cols="3" id="txtJustificativa" name="txtJustificativa" class="form-control" readonly></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn bg-slate" data-dismiss="modal">Ok</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- /info blocks -->

        <form name="formMovimentacaoFinanceira" method="post">
					<input type="hidden" id="inputPermissionAtualiza" name="inputPermissionAtualiza" value="<?php echo $atualizar; ?>" >
          <input type="hidden" id="inputPermissionExclui" name="inputPermissionExclui" value="<?php echo $excluir; ?>" >
          <input type="hidden" id="tipoMov" name="tipoMov">
					<input type="hidden" id="inputMovimentacaoFinanceiraId" name="inputMovimentacaoFinanceiraId" >
          <input type="hidden" id="inputContaJustificativa" name="inputContaJustificativa" >
		
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