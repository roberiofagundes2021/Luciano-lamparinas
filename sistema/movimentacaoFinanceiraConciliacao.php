<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Relação de Movimentações Financeiras';
$_SESSION['Conciliacao'] = true;

include('global_assets/php/conexao.php');
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
            width: "10%",
            targets: [0]
          },
          {
            orderable: true, //Histórico
            width: "22%",
            targets: [1]
          },
          {
            orderable: true, //Nª doc
            width: "8%",
            targets: [2]
          },
          {
            orderable: true, //Entrada
            width: "13%",
            targets: [3]
          },
          {
            orderable: true, //Saída
            width: "13%",
            targets: [4]
          },
          {
            orderable: true, //Saldo
            width: "13%",
            targets: [5]
          },
          {
            orderable: true, //Saldo Conciliado
            width: "13%",
            targets: [6]
          },
          {
            orderable: true, //Situação
            width: "5%",
            targets: [7]
          }
          ,{
            orderable: true, //Conciliado
            width: "3%",
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

      /*
      function atualizaTotal() {
        let childres = $('tbody').children();
        let totalEntrada = 0;
        let totalSaida = 0; 
        let totalSaldo = 0;
        let totalSaldoConciliado = 0;
        let linhas = childres.splice(1, childres.length);

        linhas.forEach(elem => {
          let valorFormFloatEntrada = 0;
          let listaTds = $(elem).children();
          let valorEntrada = $(listaTds[3]).html();
          valorFormFloatEntrada = isNaN(valorEntrada) ? parseFloat(valorEntrada.replace(".", "").replace(",", ".")) : 0;
          totalEntrada += valorFormFloatEntrada;
        });

        linhas.forEach(elem => {
          let valorFormFloatSaida = 0;
          let listaTds = $(elem).children();
          let valorSaida = $(listaTds[4]).html();
          valorFormFloatSaida = isNaN(valorSaida) ?  parseFloat(valorSaida.replace(".", "").replace(",", ".")) : 0;
          totalSaida += valorFormFloatSaida;
        });

        linhas.forEach(elem => {
          let valorFormFloatSaldo = 0;
          let listaTds = $(elem).children();
          let valorSaldo = $(listaTds[5]).html();
          valorFormFloatSaldo = isNaN(valorSaldo) ? parseFloat(valorSaldo.replace(".", "").replace(",", ".")) : 0;
          totalSaldo += valorFormFloatSaldo;
        });

        linhas.forEach(elem => {
          let valorFormFloatSaldoConciliado = 0;
          let listaTds = $(elem).children();
          let valorSaldoConciliado = $(listaTds[6]).html();
          let conciliado = $(listaTds[7]).html().split(' ');
          conciliado = conciliado[48].split('=');
          conciliado = conciliado[1].replace(/[^\d]+/g,'');
          
          if (parseInt(conciliado) > 0) {
            valorFormFloatSaldoConciliado = isNaN(valorSaldoConciliado) ? parseFloat(valorSaldoConciliado.replace(".", "").replace(",", ".")) : 0;
          } 
          totalSaldoConciliado += valorFormFloatSaldoConciliado;
        });

        $('#footer-total').remove();
        totalEntrada < 0 ? divTotalEntrada = `<div id='footer-total' style='position:absolute; left: 51%; font-weight: bold; width: 200px; color:red;'>Total: ${float2moeda(totalEntrada)}</div>` : divTotalEntrada = `<div id='footer-total' style='position:absolute; left: 51%; font-weight: bold; width: 200px; color:green;'>Total: ${float2moeda(totalEntrada)}</div>`;

        $('#footer-total').remove();
        totalSaida < 0 ? divTotalSaida = `<div id='footer-total' style='position:absolute; left: 61%; font-weight: bold; width: 200px; color:red;'>Total: ${float2moeda(totalSaida)}</div>` : divTotalSaida = `<div id='footer-total' style='position:absolute; left: 61%; font-weight: bold; width: 200px; color:green;'>Total: ${float2moeda(totalSaida)}</div>`;

        $('#footer-total').remove();
        totalSaldo < 0 ? divTotalSaldo = `<div id='footer-total' style='position:absolute; left: 71%; font-weight: bold; width: 200px; color:red;'>Total: ${float2moeda(totalSaldo)}</div>` : divTotalSaldo = `<div id='footer-total' style='position:absolute; left: 71%; font-weight: bold; width: 200px; color:green;'>Total: ${float2moeda(totalSaldo)}</div>`;

        $('#footer-total').remove();
        totalSaldoConciliado < 0 ? divTotalSaldoConciliado = `<div id='footer-total' style='position:absolute; left: 86.8%; font-weight: bold; width: 200px; color:red;'>Total: ${float2moeda(totalSaldoConciliado)}</div>` : divTotalSaldoConciliado = `<div id='footer-total' style='position:absolute; left: 86.8%; font-weight: bold; width: 200px; color:green;'>Total: ${float2moeda(totalSaldoConciliado)}</div>`;

        $('.datatable-footer').append(divTotalEntrada);
        $('.datatable-footer').append(divTotalSaida);
        $('.datatable-footer').append(divTotalSaldo);
        $('.datatable-footer').append(divTotalSaldoConciliado);
      }
      */


      function Filtrar() {
        let cont = false;

        const msg = $('<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty"><img src="global_assets/images/lamparinas/loader.gif" style="width: 120px"></td></tr>');

        $('tbody').html(msg);

        const periodoDe = $('#inputPeriodoDe').val();
        const ate = $('#inputAte').val();
        const contaBanco = $('#cmbContaBanco').val();
        const centroDeCustos = $('#cmbCentroDeCustos').val();
        const planoContas = $('#cmbPlanoContas').val();
        const FormaPagamento = $('#cmbFormaDeRecebimento').val();
        const statusArray = $('#cmbStatus').val().split('|');
        const status = statusArray[0];
        const statusTipo = statusArray[1];
        const urlFiltraGrid = "movimentacaoFinanceiraConciliacaoFiltra.php";
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
            $("#saldoAnterior").html('<span class="badge bg-secondary badge-pill p-2" style="font-size: 100%;">Saldo Anterior: R$ '+resposta+'</span>')
          }
        })

        var inputsValues = {
          inputPeriodoDe: periodoDe,
          inputAte: ate,
          cmbContaBanco: contaBanco,
          cmbCentroDeCustos: centroDeCustos,
          cmbPlanoContas: planoContas,
          cmbFormaDeRecebimento: FormaPagamento,
          cmbStatus: status,
          statusTipo: statusTipo
        };

        //Carrega dados da grid
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
            let saldoConciliacao = 0
            let saldoConciliacaoTotal = 0

            resposta.forEach(item => {
              rowNode = table.row.add(item.data).draw().node()

              saldo = parseFloat(item.data[5].replace(",", "."))
              saldoConciliado = parseFloat(item.data[6].replace(",", "."))

              // adiciona os atributos nas tags <td>
              $(rowNode).find('td').eq(3).attr('style', 'text-align: right; color: green;')
              $(rowNode).find('td').eq(4).attr('style', 'text-align: right; color: red;')
              
              if(saldo >= 0) {
                $(rowNode).find('td').eq(5).attr('style', 'text-align: right; color: green;')
              }else {
                $(rowNode).find('td').eq(5).attr('style', 'text-align: right; color: red;')
              }

              if(saldoConciliado >= 0) {
                $(rowNode).find('td').eq(6).attr('style', 'text-align: right; color: green;')
              }else {
                $(rowNode).find('td').eq(6).attr('style', 'text-align: right; color: red;')
              }

              $(rowNode).find('td').eq(8).attr('style', 'text-align: center;')

              entrada = (item.data[3] != null) ? item.data[3] : '0,00'
              entrada = entrada.replace(".", "").replace(",", ".")
              entradaTotal += parseFloat(entrada)

              saida = (item.data[4] != null) ? item.data[4] : '0,00'
              saida = saida.replace(".", "").replace(",", ".")
              saidaTotal += parseFloat(saida)

              saldo = (item.data[5] != null) ? item.data[5] : '0,00'
              saldo = saldo.replace(".", "").replace(",", ".")
              saldoTotal += parseFloat(saldo)

              saldoConciliacao = (item.data[6] != null) ? item.data[6] : '0,00'
              saldoConciliacao = saldoConciliacao.replace(".", "").replace(",", ".")
              saldoConciliacaoTotal += parseFloat(saldoConciliacao)

              contador++
            })

            $('#legenda').remove() //Para evitar que os valores se sobreescreva
            let legenda = document.querySelector(".datatable-header");
            legenda.insertAdjacentHTML('beforeend', `<div id='legenda' style='text-align: right; padding-top: 2%; width: 100%;'> Mostrando 1 a ${contador} de ${contador} registros</div>`);

            sinalNegativo = (saidaTotal == 0) ? '' : '-'
            corSaldoTotal = (saldoTotal >= 0) ? 'green' : 'red'
            corConciliacaoTotal = (saldoConciliacaoTotal >= 0) ? 'green' : 'red'
            epsSaldoTotal = (saldoTotal >= 0) ? ' ' : '' //Apenas uma codificação estética para evitar espaçamento duplo nos números negativos
            epsConciliacaoTotal = (saldoConciliacaoTotal >= 0) ? ' ' : ''
            
            // total = `
            // <tr id="total" role="row" class="even" position='relative'>
            //   <td></td>
            //   <td></td>
            //   <td style="text-align: right; font-size: .8125rem; font-weight: bold;">Total:</td>
            //   <td style="text-align: right; font-weight: bold; font-size: .8125rem; white-space: nowrap; color: green;">${float2moeda(entradaTotal)}</td>
            //   <td style="text-align: right; font-weight: bold; font-size: .8125rem; white-space: nowrap; color: red;">${sinalNegativo} ${float2moeda(saidaTotal)}</td>
            //   <td style="text-align: right; font-weight: bold; font-size: .8125rem; white-space: nowrap; color: ${corSaldoTotal};">${epsSaldoTotal}${float2moeda(saldoTotal)}
            //   <td style="text-align: right; font-weight: bold; font-size: .8125rem; white-space: nowrap; color: ${corConciliacaoTotal};">${epsConciliacaoTotal}${float2moeda(saldoConciliacaoTotal)}</td>
            //   <td></td>
            //   <td></td>
            // </tr>`

            total = `
            <tr id="total" role="row" class="even" position='relative'>
              <td></td>
              <td></td>
              <td style="text-align: right; font-size: .8125rem; font-weight: bold;">Total:</td>
              <td style="text-align: right; font-weight: bold; font-size: .8125rem; white-space: nowrap; color: green;">${float2moeda(entradaTotal)}</td>
              <td style="text-align: right; font-weight: bold; font-size: .8125rem; white-space: nowrap; color: red;">${sinalNegativo} ${float2moeda(saidaTotal)}</td>
              <td style="text-align: right; font-weight: bold; font-size: .8125rem; white-space: nowrap; color: ${corSaldoTotal};">${float2moeda(saldo)}
              <td style="text-align: right; font-weight: bold; font-size: .8125rem; white-space: nowrap; color: ${corConciliacaoTotal};">${epsConciliacaoTotal}${float2moeda(saldoConciliacaoTotal)}</td>
              <td></td>
              <td></td>
            </tr>`
            
            $('#total').remove()

            $('#tblMovimentacaoFinanceira tfoot').prepend(total)
          },
          error: function(e) { 
            $('#legenda').remove()      
            let legenda = document.querySelector(".datatable-header");
            legenda.insertAdjacentHTML('beforeend', `<div id='legenda' style='text-align: right; padding-top: 2%; width: 100%;'> Mostrando 0 a 0 de 0 registros</div>`);

            $('#total').remove() 
            let tabelaVazia = $(
              '<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty">Sem resultados...</td></tr>'
            )

            $('tbody').html(tabelaVazia)
          }
        })
      }

      $('#submitPesquisar').on('click', (e) => {
        e.preventDefault();
        Filtrar();
      })

      Filtrar();

      $('#novaTransferencia').on('click', (e) => {
        location.href = "movimentacaoFinanceiraTransferencia.php";
        return false;
      })

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
      }            

      document.formMovimentacaoFinanceira.submit();
    }  

    function atualizaConciliacao(idConciliacao, tipoConta) {
      document.getElementById('inputConciliacaoId').value = idConciliacao;
      document.getElementById('inputPermissionAtualiza').value = 1;
      
      if(tipoConta == 'ContaAReceber') {
        document.formMovimentacaoFinanceira.action = "contasAReceberNovoLancamento.php";
      }else if(tipoConta == 'ContaAPagar') {
        document.formMovimentacaoFinanceira.action = "contasAPagarNovoLancamento.php";
      }
      
      document.formMovimentacaoFinanceira.submit();
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
                  <h3 class="card-title">Conciliação das Movimentações Financeiras</h3>
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

                <form name="contaExclui" method="POST">
                  <input type="hidden" name="idMov" id="idMov">
                </form>

                <form name="formMovimentacao" method="post" class="p-3">
                  <div class="row">
                    <div class="col-lg-2">
                      <div class="form-group">
                        <label for="inputPeriodoDe">Período de</label>
                        <div class="input-group">
                          <span class="input-group-prepend">
                            <span class="input-group-text"><i class="icon-calendar22"></i></span>
                          </span>
                          <input type="date" id="inputPeriodoDe" name="inputPeriodoDe" class="form-control" min="1800-01-01" max="2100-12-12" value="<?php 
                                          if (isset($_SESSION['MovimentacaoFinanceiraConciliacaoPeriodoDe'])) {
                                            echo $_SESSION['MovimentacaoFinanceiraConciliacaoPeriodoDe'];
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
                                          if (isset($_SESSION['MovimentacaoFinanceiraConciliacaoAte'])) 
                                            echo $_SESSION['MovimentacaoFinanceiraConciliacaoAte'];
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
                            $sql = "SELECT CnBanId,
                                            CnBanNome
                                      FROM ContaBanco
                                      JOIN Situacao 
                                        ON SituaId = CnBanStatus
                                      WHERE CnBanUnidade = " . $_SESSION['UnidadeId'] . " 
                                        and SituaChave = 'ATIVO'
                                  ORDER BY CnBanNome ASC";
                            $result = $conn->query($sql);
                            $rowContaBanco = $result->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($rowContaBanco as $item) {
                              if (isset( $item['CnBanId'])) {
                                if (isset($_SESSION['MovimentacaoFinanceiraConciliacaoContaBanco'])) {
                                    if ( $item['CnBanId'] == $_SESSION['MovimentacaoFinanceiraConciliacaoContaBanco']) {
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
                            $sql = "SELECT CnCusId, CnCusNome, CnCusCodigo, CnCusNomePersonalizado
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

                              if (isset($_SESSION['MovimentacaoFinanceiraConciliacaoCentroDeCustos'])) {
                                if ($item['CnCusId'] == $_SESSION['MovimentacaoFinanceiraConciliacaoCentroDeCustos']) {
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
                              $sql = "SELECT PlConId, PlConNome, PlConCodigo
                                        FROM PlanoConta
                                        JOIN Situacao 
                                          ON SituaId = PlConStatus
                                        WHERE PlConUnidade = " . $_SESSION['UnidadeId'] . " 
                                          AND SituaChave = 'ATIVO'
                                    ORDER BY PlConCodigo ASC";
                              $result = $conn->query($sql);
                              $rowPlanoContas = $result->fetchAll(PDO::FETCH_ASSOC);

                              foreach ($rowPlanoContas as $item) {
                                if (isset($_SESSION['MovimentacaoFinanceiraConciliacaoPlanoContas'])) {
                                    if ($item['PlConId'] == $_SESSION['MovimentacaoFinanceiraConciliacaoPlanoContas']) {
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
                            $rowSituacao = $result->fetchAll(PDO::FETCH_ASSOC);

                            try {
                                print('<option value=0  selected>Todos</option>');

                                foreach ($rowSituacao as $item) {
                                  if (isset($item['FrPagId'])) {
                                    if (isset($_SESSION['MovimentacaoFinanceiraConciliacaoFormaPagamento'])) {
                                        if ($item['FrPagId'] == $_SESSION['MovimentacaoFinanceiraConciliacaoFormaPagamento']) {
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
                                        WHERE SituaStatus = 1
                                        ORDER BY SituaNome ASC";
                                $result = $conn->query($sql);
                                $rowSituacao = $result->fetchAll(PDO::FETCH_ASSOC);

                                try {
                                    foreach ($rowSituacao as $item) {
                                      if ($item['SituaChave'] == 'RECEBIDO' || $item['SituaChave'] === 'PAGO' || $item['SituaChave'] === 'TRANSFERIDO') {
                                        if (isset($_SESSION['MovimentacaoFinanceiraConciliacaoStatus'])) {
                                            if ($item['SituaId'] == $_SESSION['MovimentacaoFinanceiraConciliacaoStatus']) {
                                                print('<option value="' . $item['SituaId'] . '|' . $item['SituaChave'] . '" selected>' . $item['SituaNome'] . '</option>');
                                            } else {
                                                print('<option value="' . $item['SituaId'] . '|' . $item['SituaChave'] . '">' . $item['SituaNome'] . '</option>');
                                            }
                                        } else {
                                            print('<option value="' . $item['SituaId'] . '|' . $item['SituaChave'] . '">' . $item['SituaNome'] . '</option>');
                                        }
                                      }
                                    }
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

											<?php 
												echo $inserir?"<button id='novaTransferencia' class='btn btn-outline bg-slate-600 text-slate-600 border-slate'>Nova Transferência</button>":"";
											?>

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
                      <th>Documento</th>
                      <th style='text-align: right;'>Entrada</th>
                      <th style='text-align: right;'>Saída</th>
                      <th style='text-align: right;'>Saldo</th>
                      <th style='text-align: right;'>Saldo Conciliado</th>
                      <th>Situação</th>
                      <th>Conciliado</th>
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

        <!-- /info blocks -->
        <form name="formMovimentacaoFinanceira" method="post">
					<input type="hidden" id="inputPermissionAtualiza" name="inputPermissionAtualiza">
					<input type="hidden" id="inputMovimentacaoFinanceiraId" name="inputMovimentacaoFinanceiraId" >
          <input type="hidden" id="inputConciliacaoId" name="inputConciliacaoId" >
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