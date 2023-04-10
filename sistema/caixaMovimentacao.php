<?php

include_once("sessao.php");

//Fazer alteração posteriormente
$_SESSION['PaginaAtual'] = 'Movimentação do Caixa';

include('global_assets/php/conexao.php');

$abrirPopUpAberturaCaixa = 0;
if(isset($_SESSION['aberturaCaixa']) && $_SESSION['aberturaCaixa'] == 'Abrir_Novo_Caixaz') {

    $abrirPopUpAberturaCaixa = 1;

    unset($_SESSION['aberturaCaixa']);
}

$sql = "SELECT ForneId, ForneNome, ForneCpf, ForneCnpj, ForneTelefone, ForneCelular, ForneStatus, CategNome
		FROM Fornecedor
		JOIN Categoria on CategId = ForneCategoria
	    WHERE ForneEmpresa = " . $_SESSION['EmpreId'] . "
		ORDER BY ForneNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//CONSULTA MOVIDA PARA MESMA PÁGINA PARA VERIFICAÇÃO MAIS RÁPIDA 
$sql_saldoInicial    = "SELECT CxAbeId, CaixaNome, CxAbeCaixa, CxAbeDataHoraAbertura, CxAbeDataHoraFechamento, SituaChave, CxAbeSaldoFinal
            FROM CaixaAbertura
            JOIN Caixa on CaixaId = CxAbeCaixa
            JOIN Situacao on SituaId = CxAbeStatus
            WHERE CxAbeOperador = ".$_SESSION['UsuarId']." AND CxAbeUnidade = $_SESSION[UnidadeId]
            ORDER BY CxAbeId DESC";
$resultSaldoInicial  = $conn->query($sql_saldoInicial);

if ($rowSaldoInicial = $resultSaldoInicial->fetch(PDO::FETCH_ASSOC)) {
    $rowResult = $rowSaldoInicial;
} else {
    $rowResult = 'abrirCaixa';
}

$abrirPopUpCaixaAbertura = isset($_GET['paginaredirecionada']) ? 1 : 0;

$data = date("Y-m-d");

//A sessão de resumo financeiro é a opção de visibilidade do resumo financeiro, aqui ele também foi aplicado ao resumo de Caixa
$visibilidadeResumoCaixa = isset($_SESSION['ResumoFinanceiro']) && $_SESSION['ResumoFinanceiro'] ? 'sidebar-right-visible' : ''; 
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Lamparinas | Movimentação do Caixa</title>

    <?php include_once("head.php"); ?>
    
    <!-- Theme JS files -->
    <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

    <script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
    <script src="global_assets/js/demo_pages/form_layouts.js"></script>
    <script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

    <script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
    <script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	
	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>	
   
    <!-- /theme JS files -->

    <!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
    <script type="text/javascript" language="javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
    <script type="text/javascript" language="javascript"
        src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {

            $.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data
            consultaSituacaoCaixa();

            /* Início: Tabela Personalizada */
            $('#tblMovimentacao').DataTable({
                "order": [
                    [0, "desc"],
                    [1, "desc"]
                ],
                responsive: true,
                columnDefs: [{
                        orderable: true, //Nº registro
                        width: "13%",
                        targets: [0]
                    },
                    {
                        orderable: true, //Data Hora
                        width: "16%",
                        targets: [1]
                    },
                    {
                        orderable: true, //Histórico
                        width: "20%",
                        targets: [2]
                    },
                    {
                        orderable: true, //Tipo
                        width: "10%",
                        targets: [3]
                    },
                    {
                        orderable: true, //Forma de Rec/Pag
                        width: "18%",
                        targets: [4]
                    },
                    {
                        orderable: true, //Valor Total
                        width: "14%",
                        targets: [5]
                    },
                    {
                        orderable: true, //Status
                        width: "5%",
                        targets: [6]
                    },
                    {
                        orderable: false, //Ações
                        width: "4%",
                        targets: [7]
                    }
                ],
                dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
                language: {
                    search: '<span>Filtro:</span> _INPUT_',
                    searchPlaceholder: 'filtra qualquer coluna...',
                    lengthMenu: '<span>Mostrar:</span> _MENU_',
                    paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
                }
            });

            function filtrar() {
                const msg = $(
                    '<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty"><img src="global_assets/images/lamparinas/loader.gif" style="width: 120px" alt="Loader"></td></tr>'
                )

                $('tbody').html(msg)

                let aberturaCaixa = $('#inputAberturaCaixaId').val();
                let periodoDe = $('#inputPeriodoDe').val()
                let ate = $('#inputAte').val()
                let clientes = $('#cmbClientes').val()
                let formaPagamento = $("#cmbFormaPagamento").val()
                let status = $("#cmbStatus").val()
                let urlConsultaAberturaCaixa = "caixaMovimentacaoFiltra.php";

                let inputsValuesConsulta = {
                    inputAberturaCaixaId: aberturaCaixa,
                    inputPeriodoDe: periodoDe,
                    inputAte: ate,
                    cmbClientes: clientes,
                    inputFormaPagamento: formaPagamento,
                    cmbStatus: status
                }; 

                //Verifica se deverá ou não abrir o caixa
                $.ajax({
                    type: "POST",
                    url: urlConsultaAberturaCaixa,
                    dataType: "json",
                    data: inputsValuesConsulta,
                    success: function(resposta) {
                        //|--Aqui é criado o DataTable caso seja a primeira vez q é executado e o clear é para evitar duplicação na tabela depois da primeira pesquisa
                        let table 
                        table = $('#tblMovimentacao').DataTable();
                        table = $('#tblMovimentacao').DataTable().clear().draw();
                        //--|

                        table = $('#tblMovimentacao').DataTable();

                        let rowNode;

                        let valorTotal = null;
                        let descontoTotal = null;

                        resposta.forEach(item => {
                            rowNode = table.row.add(item.data).draw().node();

                            if(item.data[3] == 'Recebimento') {
                                $(rowNode).find('td').eq(5).attr('style', 'text-align: right;');
                            }else {
                                $(rowNode).find('td').eq(5).attr('style', 'text-align: right; color: red;');
                            }
                        })

                        valorFinal = valorTotal - descontoTotal;
                        valorFinal = (valorFinal != null && valorFinal > 0) ?  float2moeda(valorFinal) : null;
                        valorTotal = (valorTotal != null) ?  float2moeda(valorTotal) : null;
                        descontoTotal = (descontoTotal != null) ?  float2moeda(descontoTotal) : null;
                        
                        $("#inputValorTotal").val(valorTotal);
                        $("#inputDesconto").val(descontoTotal);
                        $("#inputValorFinal").val(valorFinal);
                    }
                })
            }

            filtrar();

            $("#submitFiltro").on('click', () => {
                filtrar();
            })

            //A função $(document).on... trabalha dinâmicamente, neste caso funciona com html colocado posteriormente via javascript
            $(document).on("change", "#pagamentoSangria", function(){
                let arrayFormaPagamentoId = $(this).val().split("-");;
                
                if(arrayFormaPagamentoId[1] == 'CHEQUE') {
                    $("#detalhamentoCheque").trigger("click");
                }
            });

		    $(document).on("change", "#pagamentoRetirada", function(){
                let arrayFormaPagamentoId = $(this).val().split("-");;
                
                if(arrayFormaPagamentoId[1] == 'CHEQUE') {
                    $("#detalhamentoCheque").trigger("click");
                }
            });

            $("#btnCancelaDadosCheque").on('click', () => {
                var menssagem = 'Forma de pagamento por cheque cancelada!'
                alerta('Atenção', menssagem, 'error')

                $("#pagamentoSangria").val('').change()
                $("#pagamentoRetirada").val('').change()
            })

            $("#btnDadosCheque").on('click', () => {
                if($("#numCheque").val() == '') {
                    $("#numCheque").focus();
                    var menssagem = 'Por favor informe o Nº do cheque!'
                    alerta('Atenção', menssagem, 'error')
					return;
                }

                if($("#valorCheque").val() == '') {
                    $("#valorCheque").focus();
                    var menssagem = 'Por favor informe o valor do cheque!'
                    alerta('Atenção', menssagem, 'error')
					return;
                }

                if($("#dataEmissaoCheque").val() == '') {
                    $("#dataEmissaoCheque").focus();
                    var menssagem = 'Por favor informe a data de emissão do cheque!'
                    alerta('Atenção', menssagem, 'error')
					return;
                }

                if($("#dataVencimentoCheque").val() == '') {
                    $("#dataVencimentoCheque").focus();
                    var menssagem = 'Por favor informe a data de vencimento do cheque!'
                    alerta('Atenção', menssagem, 'error')
					return;
                }

                if($("#cmbBancoCheque").val() == '') {
                    $("#cmbBancoCheque").focus();
                    var menssagem = 'Por favor informe o banco!'
                    alerta('Atenção', menssagem, 'error')
					return;
                }

                if($("#agenciaCheque").val() == '') {
                    $("#agenciaCheque").focus();
                    var menssagem = 'Por favor informe a agência!'
                    alerta('Atenção', menssagem, 'error')
					return;
                }

                if($("#contaCheque").val() == '') {
                    $("#contaCheque").focus();
                    var menssagem = 'Por favor informe a conta!'
                    alerta('Atenção', menssagem, 'error')
					return;
                }

                if($("#nomeCheque").val() == '') {
                    $("#nomeCheque").focus();
                    var menssagem = 'Por favor informe o nome!'
                    alerta('Atenção', menssagem, 'error')
					return;
                }

                if($("#cpfCheque").val() == '') {
                    $("#cpfCheque").focus();
                    var menssagem = 'Por favor informe o CPF!'
                    alerta('Atenção', menssagem, 'error')
					return;
                }

                if(!validaCPF($("#cpfCheque").val())) {
                    $("#cpfCheque").focus();
                    var menssagem = 'CPF inválido!'
                    alerta('Atenção', menssagem, 'error')
					return;
                }

                $('#modal_large_detalhamento_cheque').modal('hide');
            })

            $("#cmbCaixa").on("change", function() {
                let nomeCaixa = $(this).find('option').filter(':selected').text();
                
                $("#inputCaixaNome").val(nomeCaixa);

                let urlConsultaAberturaCaixa = "consultaCaixaSaldo.php";
                let idCaixa = ($(this).val() != "") ? $(this).val() : 0;

                let inputsValuesConsulta = {
                    inputCaixaId: idCaixa
                }; 

                //Verifica se deverá ou não abrir o caixa
                $.ajax({
                    type: "POST",
                    url: urlConsultaAberturaCaixa,
                    dataType: "json",
                    data: inputsValuesConsulta,
                    success: function(resposta) {
                        //Caixa aberto pela primeira vez
                        if(resposta == 'abrirCaixa') {
                            $("#inputSaldoInicial").val(float2moeda(0));
                        }else {
                            //Verifica se o último caixa do operador já foi fechado, se sim irá aparecer uma tela para abrir novamente com o saldo anterior
                            if(resposta.CxAbeDataHoraFechamento != null) {
                                $("#inputSaldoInicial").val(float2moeda(resposta.CxAbeSaldoFinal));
                            }
                        }
                    }
                })
            })

            $("#btnPdv").on('click', () => {
                let urlConsultaAberturaCaixa = "consultaAberturaCaixa.php";

                //Verifica se deverá ou não abrir o caixa
                $.ajax({
                    type: "POST",
                    url: urlConsultaAberturaCaixa,
                    dataType: "json",
                    success: function(resposta) {
                        if(resposta == 'abrirCaixa') {
                            alerta('Sucesso', 'Você deve abrir um caixa para acessar o PDV!!!', 'success')
                            $("#aberturaCaixa").trigger("click");
                        }else {
                            //Verifica se o último caixa do operador já foi fechado, se sim irá aparecer uma tela para abrir novamente
                            if(resposta.SituaChave == 'FECHADO') {
                                alerta('Sucesso', 'Você deve abrir um caixa para acessar o PDV!!!', 'success')
                                $("#aberturaCaixa").trigger("click");
                            }else {
                                let arrayDataAbertura = resposta.CxAbeDataHoraAbertura.split(" ")
                                let dataAbertura = arrayDataAbertura[0]

                                let arrayDataAtual = new Date();

                                let mes = arrayDataAtual.getMonth()+1;
                                let dia = arrayDataAtual.getDate();

                                let dataAtual = arrayDataAtual.getFullYear() + '-' +
                                    (mes <10 ? '0' : '') + mes + '-' +
                                    (dia <10 ? '0' : '') + dia;

                                //Verifica se o caixa foi aberto hoje, se não foi o operador terá que fechar
                                if(dataAbertura == dataAtual) {
                                    $("#inputAberturaCaixaId").val(resposta.CxAbeId);
                                    $("#inputAberturaCaixaNome").val(resposta.CaixaNome);

                                    document.formCaixaAbertura.action = "caixaPDV.php";
                                    document.formCaixaAbertura.submit();
                                }else {
                                    $("#inputAberturaCaixaId").val(resposta.CxAbeId);                    
                                    $("#inputCaixaId").val(resposta.CxAbeCaixa);
                                    $("#inputAberturaCaixaNome").val(resposta.CaixaNome);
                                    $("#inputSituacaoCaixa").val('DEVE_FECHAR'); //Esse input serve para não permitir o acesso ao PDV caso o caixa tenha uma data diferente da de hoje e não tenha sido fechado
                                    $('#inputAlerta').val('Fechar_Caixa_Anterior');

                                    document.formCaixaAbertura.action = "caixaFechamento.php";
                                    document.formCaixaAbertura.submit();
                                }
                            }
                        }
                    }
                })
            })

            function consultaSaldoCaixaAtual() {
                let urlConsultaAberturaCaixa = "consultaCaixaSaldoAtual.php";
                
                //Verifica se deverá ou não abrir o caixa
                $.ajax({
                    type: "POST",
                    url: urlConsultaAberturaCaixa,
                    dataType: "json",
                    success: function(resposta) {
                        if(resposta != 'consultaVazia') {
                            let saldoInicial = parseFloat(resposta[0].CxAbeSaldoInicial);
                            let valorRecebido = parseFloat(resposta[1].SaldoRecebido);
                            let valorPago = parseFloat(resposta[2].SaldoPago);
                            let saldo = saldoInicial + valorRecebido - valorPago;
                            
                            $("#inputResumoCaixaSaldoInicial").val(float2moeda(saldoInicial));
                            $("#inputResumoCaixaRecebido").val(float2moeda(valorRecebido));
                            $("#inputResumoCaixaPago").val(float2moeda(valorPago * -1));        
                            $("#inputResumoCaixaSaldo").val(float2moeda(saldo));
                        }else {
                            $("#inputResumoCaixaSaldoInicial").val('');
                            $("#inputResumoCaixaRecebido").val('');
                            $("#inputResumoCaixaPago").val('');
                            $("#inputResumoCaixaSaldo").val('');
                        }
                    }
                })
            }

            $("#radioButtonSangria").on('click', () => {
                //Para evitar que seja executado comando em um botão já selecionado
                $("#radioButtonSangria").prop('disabled', true);
                $("#radioButtonRetirada").prop('disabled', false);

                $("#conteudoRetirada").hide();
                $("#conteudoSangria").show();
            }) 

            $("#radioButtonRetirada").on('click', () => {
                $("#radioButtonSangria").prop('disabled', false);
                $("#radioButtonRetirada").prop('disabled', true);

                $("#conteudoSangria").hide();
                $("#conteudoRetirada").show();
            }) 

            $("#btnFinalizarRetirada").on('click', () => {
                if ($("#radioButtonSangria").prop("checked")) {
                    if($("#valorSangria").val() == '') {
                        $("#valorSangria").focus();
                            
                        var menssagem = 'Informe um valor retirado!'
                        alerta('Atenção', menssagem, 'error')
                        return
                    }
                    
                    if($("#pagamentoSangria").val() == '') {
                        $("#pagamentoSangria").focus();
                            
                        var menssagem = 'Informe uma forma de pagamento!'
                        alerta('Atenção', menssagem, 'error')
                        return
                    }
    
                    if($("#justificativaSangria").val() == '') {
                        $("#justificativaSangria").focus();
                            
                        var menssagem = 'Informe uma justificativa!'
                        alerta('Atenção', menssagem, 'error')
                        return
                    } 
                }else {
                    if($("#valorRetirada").val() == '') {
                        $("#valorRetirada").focus();
                            
                        var menssagem = 'Informe um Valor Retirado!'
                        alerta('Atenção', menssagem, 'error')
                        return
                    }
                    
                    if($("#pagamentoRetirada").val() == '') {
                        $("#pagamentoRetirada").focus();
                            
                        var menssagem = 'Informe uma Forma de Pagamento!'
                        alerta('Atenção', menssagem, 'error')
                        return
                    }

                    if($("#planoContasRetirada").val() == '') {
                        $("#planoContasRetirada").focus();
                            
                        var menssagem = 'Informe um Plano de Conta!'
                        alerta('Atenção', menssagem, 'error')
                        return
                    }

                    if($("#centroCustoRetirada").val() == '') {
                        $("#centroCustoRetirada").focus();
                            
                        var menssagem = 'Informe um Centro de Custo!'
                        alerta('Atenção', menssagem, 'error')
                        return
                    }

                    if($("#fornecedorRetirada").val() == '') {
                        $("#fornecedorRetirada").focus();
                            
                        var menssagem = 'Informe um fornecedor!'
                        alerta('Atenção', menssagem, 'error')
                        return
                    }
    
                    if($("#justificativaRetirada").val() == '') {
                        $("#justificativaRetirada").focus();
                            
                        var menssagem = 'Informe uma justificativa!'
                        alerta('Atenção', menssagem, 'error')
                        return
                    }
                }

                let tipo = $("#radioButtonSangria").prop("checked") ? 'SANGRIA' : 'RETIRADA';
                let idCaixaAbertura = $("#inputAberturaCaixaId").val();
                let valorRetirado = tipo == 'SANGRIA' ? $("#valorSangria").val().replaceAll(".", "").replace(",", ".") : $("#valorRetirada").val().replaceAll(".", "").replace(",", ".");
                let arrayFormaPagamento = tipo == 'SANGRIA' ? $("#pagamentoSangria").val().split('-') : $("#pagamentoRetirada").val().split('-');
                let formaPagamento = arrayFormaPagamento[0];
                let nomeFormaPagamento = arrayFormaPagamento[1];
                let planoContas = $("#planoContasRetirada").val();
                let centroCustos = $("#centroCustoRetirada").val();
                let fornecedor = $("#fornecedorRetirada").val();
                let justificativa =  tipo == 'SANGRIA' ? $("#justificativaSangria").val().replaceAll(".", "").replace(",", ".") : $("#justificativaRetirada").val().replaceAll(".", "").replace(",", "."); 
                let numeroCheque = nomeFormaPagamento == 'CHEQUE' ? $("#numCheque").val() : '';
                let valorCheque = nomeFormaPagamento == 'CHEQUE' ? $("#valorCheque").val() : '';
                let dataEmissao = nomeFormaPagamento == 'CHEQUE' ? $("#dataEmissaoCheque").val() : '';
                let dataVencimento = nomeFormaPagamento == 'CHEQUE' ? $("#dataVencimentoCheque").val() : '';
                let bancoCheque = nomeFormaPagamento == 'CHEQUE' ? $("#cmbBancoCheque").val() : '';
                let agenciaCheque = nomeFormaPagamento == 'CHEQUE' ? $("#agenciaCheque").val() : '';
                let contaCheque = nomeFormaPagamento == 'CHEQUE' ? $("#contaCheque").val() : '';
                let nomeCheque = nomeFormaPagamento == 'CHEQUE' ? $("#nomeCheque").val() : '';
                let cpfCheque = nomeFormaPagamento == 'CHEQUE' ? $("#cpfCheque").val().replaceAll(".", "").replace("-", "") : '';

                let inputsValuesConsulta = {
                    inputTipo: tipo,
                    inputAberturaCaixaId: idCaixaAbertura,
                    inputValorRetirado: valorRetirado,
                    inputFormaPagamento: formaPagamento,
                    inputPlanoContas: planoContas,
                    inputCentroCustos: centroCustos,
                    inputFornecedor: fornecedor,
                    inputJustificativaRetirada: justificativa,
                    inputNumeroCheque: numeroCheque,
                    inputValorCheque: valorCheque,
                    inputDataEmissaoCheque: dataEmissao,
                    inputDataVencimentoCheque: dataVencimento,
                    inputBancoCheque: bancoCheque,
                    inputAgenciaCheque: agenciaCheque,
                    inputContaCheque: contaCheque,
                    inputNomeCheque: nomeCheque,
                    inputCpfCheque: cpfCheque
                }; 

                let urlConsultaAberturaCaixa = "caixaPagamentoNovo.php";
                
                //Cadastro do caixa pagamento
                $.ajax({
                    type: "POST",
                    url: urlConsultaAberturaCaixa,
                    dataType: "json",
                    data: inputsValuesConsulta,
                    success: function(resposta) {
                        if(resposta != 'Impossivel retirar') {
                            //Para fechar o pop up dps que é feito uma radioButtonRetirada
                            $("#btnCancelar").trigger("click");

                            $("#valorSangria").val('');
                            $("#valorRetirada").val('');
                            $("#pagamentoSangria").val('').change();  
                            $("#pagamentoRetirada").val('').change();               
                            $("#planoContasRetirada").val('').change()
                            $("#centroCustoRetirada").val('').change()
                            $("#fornecedorRetirada").val('').change()
                            $("#justificativaSangria").val('');
                            $("#justificativaRetirada").val('');
    
                            filtrar();
                            consultaSaldoCaixaAtual();
    
                            $('#inputReciboId').val(resposta);
                            
                            $('#formMovimentacao').attr('action', 'caixaImprimiReciboRetirada.php');
                            $('#formMovimentacao').attr('target', '_blank');
                            $('#formMovimentacao').submit();
                        }else {
                            var menssagem = 'Não é possível retirar um valor superior ao saldo atual!'
                            alerta('Atenção', menssagem, 'error')
                            return
                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) { 
                        var menssagem = 'Ocorreu um erro ao fazer a retirada!'
                        alerta('Atenção', menssagem, 'error')
                        return
                    }  
                })
            })

            $("#btnFechamento").on('click', () => {
                let urlConsultaAberturaCaixa = "consultaAberturaCaixa.php";

                //Verifica se deverá ou não abrir o caixa
                $.ajax({
                    type: "POST",
                    url: urlConsultaAberturaCaixa,
                    dataType: "json",
                    success: function(resposta) {
                        $("#inputAberturaCaixaId").val(resposta.CxAbeId);                    
                        $("#inputCaixaId").val(resposta.CxAbeCaixa);
                        $("#inputAberturaCaixaNome").val(resposta.CaixaNome);

                        let arrayDataAbertura = resposta.CxAbeDataHoraAbertura.split(" ")
                        let dataAbertura = arrayDataAbertura[0]

                        let arrayDataAtual = new Date();

                        let mes = arrayDataAtual.getMonth()+1;
                        let dia = arrayDataAtual.getDate();

                        let dataAtual = arrayDataAtual.getFullYear() + '-' +
                            (mes <10 ? '0' : '') + mes + '-' +
                            (dia <10 ? '0' : '') + dia;

                        //Esse input serve para não permitir o acesso ao PDV caso o caixa tenha uma data diferente da de hoje e não tenha sido fechado
                        if(dataAbertura != dataAtual) {
                            $("#inputSituacaoCaixa").val('DEVE_FECHAR');
                        }

                        document.formCaixaAbertura.action = "caixaFechamento.php";
                        document.formCaixaAbertura.submit();
                    }
                })
            }) 

            //Função para determinar a visibilidade de Retirada e Fechamento do caixa no "Resumo do Caixa" --- REFATORADA COM PHP
            function consultaSituacaoCaixa() {

                <?php if($rowResult == 'abrirCaixa') { ?>
                    $(".caixaEmOperacao").hide();
                <?php } else { ?>
                    <?php if($rowResult['SituaChave'] == 'FECHADO') { ?>
                        $(".caixaEmOperacao").hide();
                    <?php } else { ?>
                        $(".caixaEmOperacao").show();
                    <?php } ?>
                    $("#inputAberturaCaixaId").val(<?php echo $rowResult['CxAbeId']; ?>);
                    $("#inputCaixaId").val(<?php echo $rowResult['CxAbeCaixa']; ?>);
                    $("#inputSaldoInicial").val(float2moeda(<?php echo $rowResult['CxAbeSaldoFinal']; ?>));
                <?php } ?>
                
            }
                    
        });//DOCUMENT READY

        function verificaPopUpAberturaCaixa() {
            let abriPopUpAberturaCaixa = '<?php echo $abrirPopUpAberturaCaixa; ?>'

            if(abriPopUpAberturaCaixa == '1') {
                $("#btnPdv").click();
            }
        }

        function atualizaMovimentacaoCaixa(id, atendimento, tipo, acao) {
            //document.getElementById('inputContasAPagarId').value = ContasAPagarId;
            document.getElementById('inputReciboId').value = id;
            document.getElementById('inputAtendimento').value = atendimento;

            if(tipo == 'Recebimento') {
                if(acao == 'detalhamento') {
                    //alert('ID: '+id+' || Tipo mov: '+tipo+' || Ação: '+acao);
                    document.formMovimentacao.action = "caixaRecebimentoDetalhamento.php";
                }else if(acao == 'imprimir') {
                    //document.formMovimentacao.setAttribute("target", "_blank");
                    //document.formMovimentacao.action = "caixaImprimiReciboRecebimento.php";
                    var menssagem = 'Esta função está indisponível para recebimento, mas já funciona para o pagamento =D!'
                    alerta('Atenção', menssagem, 'error')
                    return
                }else if(acao == 'estornar') {
                    //alert('ID: '+id+' || Tipo mov: '+tipo+' || Ação: '+acao);
                    var menssagem = 'Esta função está indisponível no momento =/, mas estará em breve =D!'
                    alerta('Atenção', menssagem, 'error')
                    return
                }else {
                    //alert('ID: '+id+' || Tipo mov: '+tipo+' || Ação: '+acao);
                    var menssagem = 'Esta função está indisponível no momento =/, mas estará em breve =D!'
                    alerta('Atenção', menssagem, 'error')
                    return
                }     
            }else {
                if(acao == 'detalhamento') {
                    //('ID: '+id+' || Tipo mov: '+tipo+' || Ação: '+acao);
                    var menssagem = 'Esta função está indisponível para o pagamento, mas já está funcionando no recebimento =D!'
                    alerta('Atenção', menssagem, 'error')
                    return
                }else if(acao == 'imprimir') {
                    document.formMovimentacao.setAttribute("target", "_blank");
                    document.formMovimentacao.action = "caixaImprimiReciboRetirada.php";	
                }else if(acao == 'estornar') {
                    //document.formMovimentacao.action = "caixaMovimentacao.php";
                    var menssagem = 'Esta função está indisponível no momento =/, mas está disponível em breve =D!'
                    alerta('Atenção', menssagem, 'error')
                    return
                }else {
                    //alert('ID: '+id+' || Tipo mov: '+tipo+' || Ação: '+acao);
                    var menssagem = 'Esta função está indisponível no momento =/, mas está disponível em breve =D!'
                    alerta('Atenção', menssagem, 'error')
                    return
                }   
            }

            document.formMovimentacao.submit();
        } 
    </script>

</head>

<body class="navbar-top <?php echo $visibilidadeResumoCaixa; ?> sidebar-xs" onload="verificaPopUpAberturaCaixa()">

    <?php include_once("topo.php"); ?>

    <!-- Page content -->
    <div class="page-content">

        <?php include_once("menu-left.php"); ?>

        <!-- Main content -->
        <div class="content-wrapper">

            <?php include_once("cabecalho.php"); ?>

            <!-- Content area -->
            <div class="content">

                <?php include_once("botoesCaixa.php"); ?>

                <!-- Info blocks -->
                <div class="row">
                    <div class="col-lg-12">
                        <!-- Basic responsive configuration -->
                        <div class="card">
                            <div class="card-header header-elements-inline">
                                <h3 class="card-title">Movimentação do Caixa</h3>
                            </div>

                            <div class="card-body">
                                <!--<p class="font-size-lg">Utilize os filtros abaixo para gerar o relatório.</p>
                                <br>-->

                                <!--Link para abertura de caixa-->
                                <a id="aberturaCaixa" data-toggle="modal" data-target="#modal_small_abertura_caixa"></a>
                                <a id="detalhamentoCheque" data-toggle="modal" data-target="#modal_large_detalhamento_cheque"></a>

                                <form id="formImprime" method="POST" target="_blank">
                                    <input id="TipoProdutoServico" type="hidden" name="TipoProdutoServico"></input>
                                    <input id="inputResultado" type="hidden" name="resultados"></input>
                                    <input id="inputDataDe_imp" type="hidden" name="inputDataDe_imp"></input>
                                    <input id="inputDataAte_imp" type="hidden" name="inputDataAte_imp"></input>
                                    <input id="cmbTipo_imp" type="hidden" name="cmbTipo_imp"></input>
                                    <input id="cmbFornecedor_imp" type="hidden" name="cmbFornecedor_imp"></input>
                                    <input id="cmbCategoria_imp" type="hidden" name="cmbCategoria_imp"></input>
                                    <input id="cmbSubCategoria_imp" type="hidden" name="cmbSubCategoria_imp"></input>
                                    <input id="cmbProduto_imp" type="hidden" name="cmbProduto_imp"></input>
                                    <input id="cmbServico_imp" type="hidden" name="cmbServico_imp"></input>
                                    <input id="cmbCodigo_imp" type="hidden" name="cmbCodigo_imp"></input>
                                </form>

                                <div class="row">
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="inputPeriodoDe">Período de</label>
                                            <div class="input-group">
                                                <span class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="icon-calendar22"></i></span>
                                                </span>
                                                <input type="date" id="inputPeriodoDe" name="inputPeriodoDe" min="1800-01-01" max="2100-12-12"
                                                    class="form-control" value="<?php if(isset($_SESSION['MovCaixaPeriodoDe'])) echo $_SESSION['MovCaixaPeriodoDe'];  else echo $data; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="inputAte">Até</label>
                                            <div class="input-group">
                                                <span class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="icon-calendar22"></i></span>
                                                </span>
                                                <input type="date" id="inputAte" name="inputAte" min="1800-01-01" max="2100-12-12"
                                                    class="form-control" value="<?php if(isset($_SESSION['MovCaixaAte'])) echo $_SESSION['MovCaixaAte'];  else echo $data; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="cmbClientes">Clientes</label>
                                            <select id="cmbClientes" name="cmbClientes" class="form-control form-control-select2">
                                                <option value="">Todos</option>
                                                <?php
                                                    try {
                                                        $sql = "SELECT *
                                                                FROM  Cliente
                                                                JOIN Situacao ON SituaId = ClienStatus
                                                                WHERE ClienUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                                                ORDER BY ClienNome ASC";
                                                        $result = $conn->query($sql);
                                                        $rowCliente = $result->fetchAll(PDO::FETCH_ASSOC);

                                                        try {

                                                            foreach ($rowCliente as $item) {
                                                                if (isset($_SESSION['MovCaixaFormaPagamento'])) {
                                                                    if ($item['ClienId'] == $_SESSION['MovCaixaCliente']) {
                                                                        print('<option value="' . $item['ClienId'] . '" selected>' . $item['ClienNome'] . '</option>');
                                                                    } else {
                                                                        print('<option value="' . $item['ClienId'] . '">' . $item['ClienNome'] . '</option>');
                                                                    }
                                                                } else {
                                                                    print('<option value="' . $item['ClienId'] . '">' . $item['ClienNome'] . '</option>');
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

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="cmbFormaPagamento">Forma de Rec/Pag</label>
                                            <select id="cmbFormaPagamento" name="cmbFormaPagamento"
                                                class="form-control form-control-select2">
                                                <option value="">Todos</option>
                                                <?php
                                                $sql = "SELECT FrPagId, FrPagNome, FrPagChave
                                                        FROM FormaPagamento
                                                        JOIN Situacao on SituaId = FrPagStatus
                                                        WHERE FrPagUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                                        ORDER BY FrPagNome ASC";     
                                                $result = $conn->query($sql);
                                                $rowFormaPagamento = $result->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($rowFormaPagamento as $item) {
                                                    if (isset($_SESSION['MovCaixaFormaPagamento'])) {
                                                        if ($item['FrPagId'] == $_SESSION['MovCaixaFormaPagamento']) {
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

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="cmbStatus">Status</label>
                                            <select id="cmbStatus" name="cmbStatus"
                                                class="form-control form-control-select2">
                                                <option value="">Todos</option>
                                                <?php
                                                    $sql = "SELECT SituaId, SituaNome, SituaChave
                                                            FROM Situacao
                                                            WHERE SituaStatus = 1 and (SituaChave = 'PAGO' or SituaChave = 'RECEBIDO' or SituaChave = 'ESTORNADO')
                                                            ORDER BY SituaNome ASC";
                                                    $result = $conn->query($sql);
                                                    $rowSituacao = $result->fetchAll(PDO::FETCH_ASSOC);

                                                    foreach ($rowSituacao as $item) {
                                                        if (isset($_SESSION['MovCaixaStatus'])) {
                                                            if ($item['SituaId'] == $_SESSION['MovCaixaStatus']) {
                                                                print('<option value="' . $item['SituaId'].'" selected>' . $item['SituaNome'] . '</option>');
                                                            } else {
                                                                print('<option value="' . $item['SituaId'].'">' . $item['SituaNome'] . '</option>');
                                                            }
                                                        } else {
                                                            print('<option value="' . $item['SituaId'].'">' . $item['SituaNome'] . '</option>');
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-2 m-auto text-center">
                                        <button id="submitFiltro" class="btn btn-principal">Pesquisar</button>
                                    </div>
                                </div>

                                <table class="table" id="tblMovimentacao">
                                    <thead>
                                        <tr class="bg-slate">
                                            <th id='dataGrid'>Nº registro</th>
                                            <th>Data/Hora</th>
                                            <th>Histórico</th>
                                            <th>Tipo</th>
                                            <th>Forma de Rec/Pag</th>
                                            <th>Valor Total</th>
                                            <th>Status</th>
                                            <th style="text-align: center;">Ações</th>
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
                
                <form name="formCaixaAbertura" method="post">
					<input type="hidden" id="inputAberturaCaixaId" name="inputAberturaCaixaId" value="">
                    <input type="hidden" id="inputCaixaId" name="inputCaixaId" value="">
                    <input type="hidden" id="inputAberturaCaixaNome" name="inputAberturaCaixaNome" value="">
                    <input type="hidden" id="inputSituacaoCaixa" name="inputSituacaoCaixa" value="">
                    <input type="hidden" id="inputAlerta" name="inputAlerta" value="">
				</form>

                <form id="formMovimentacao" name="formMovimentacao" method="POST">
                    <input type="hidden" id="inputReciboId" name="inputReciboId" value="">
                    <input type="hidden" id="inputAtendimento" name="inputAtendimento" value="">
                </form>
            </div>
            <!-- /content area -->

            <!-- Small modal -->
            <!--Procurar uma correção com relação ao filtro do select-->
            <div id="modal_small_abertura_caixa" class="modal fade">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="custon-modal-title">
                            <i class=""></i>
                            <h2 class="modal-title p-2">Abertura de Caixa</h2>
                            <i class=""></i>
                        </div>

                        <form id="formAbrirCaixa" method="POST" action="caixaPdv.php">
                            <input type="hidden" id="inputCaixaNome" name="inputCaixaNome" value="">

                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <h5>Data: <?php echo date('d/m/Y'); ?></h5>
                                    </div>
    
                                    <div class="col-lg-6">
                                        <h5>Operador: <?php echo nomeSobrenome($_SESSION['UsuarNome'], 1); ?></h5>
                                    </div>
                                </div>
    
                                <div class="form-group mt-3">
                                    <!--Input para controle, para que caso acesse o PDV pela abertura de caixa ele fará o cadastro da nova abertura de caixa-->
                                    <input type="hidden" id="inputAbrirCaixa" name="inputAbrirCaixa" value="" class="form-control">

                                    <label for="cmbCaixa" class="font-size-lg">Caixa <span class="text-danger">*</span></label>
                                    <select id="cmbCaixa" name="cmbCaixa" class="form-control form-control-select2 select2-hidden-accessible" required="" aria-hidden="true">
                                        <option value="">Selecionar</option>
                                        <?php

                                        $sql = "SELECT DISTINCT CaixaId, MAX(CaixaNome) NomeCaixa, MAX(SituaNome) SituacaoCaixa, MAx(UsuarNome) as NomeUsuario
                                        FROM Caixa
                                        JOIN Situacao on SituaId = CaixaStatus
                                        JOIN ( SELECT DISTINCT CxAbeId, CxAbeCaixa, CxAbeOperador , MAX(CxAbeDataHoraAbertura) ultimaData FROM CaixaAbertura
                                        where CxAbeUnidade = " . $_SESSION['UnidadeId'] . " and CxAbeDataHoraFechamento is NULL 
                                        GROUP BY CxAbeCaixa, CxAbeId, CxAbeOperador ) lastID 
                                        ON CaixaId = lastID.CxAbeCaixa
                                        LEFT JOIN Usuario ON lastID.CxAbeOperador = UsuarId
                                        WHERE CaixaUnidade = " . $_SESSION['UnidadeId'] . "
                                        AND CaixaStatus = 1
                                        AND CaixaId  in (SELECT CxAbeCaixa FROM CaixaAbertura WHERE CxAbeUnidade = " . $_SESSION['UnidadeId'] . " and CxAbeDataHoraFechamento is NULL)
                                        GROUP BY CaixaId
                                        
                                        UNION All
                                        
                                        SELECT CaixaId, CaixaNome NomeCaixa, SituaNome SituacaoCaixa, 'Disponível' as NomeUsuario
                                        FROM Caixa
                                        JOIN Situacao on SituaId = CaixaStatus
                                        WHERE CaixaUnidade = " . $_SESSION['UnidadeId'] . " 
                                        AND CaixaStatus = 1
                                        AND CaixaId not in (SELECT CxAbeCaixa FROM CaixaAbertura WHERE CxAbeUnidade = " . $_SESSION['UnidadeId'] . " and CxAbeDataHoraFechamento is NULL)                      
                                        ORDER BY CaixaId ASC";
                                        
                                        $result = $conn->query($sql);
                                        $rowCaixa = $result->fetchAll(PDO::FETCH_ASSOC);
    
                                        foreach ($rowCaixa as $item) {
                                            print('<option value="' . $item['CaixaId'] . '" ' . ($item['NomeUsuario'] == "Disponível" ? "" :"disabled") . ' >'. $item['NomeCaixa'] . ' (' . $item['NomeUsuario'] . ')' . '</option>');
                                        }
                                        ?>
                                    </select>
                                </div>
    
                                <div class="form-group mt-2">
                                    <!--Se informar depois sobre o saldo, como irá funcionar-->
                                    <label for="inputSaldoInicial" class="font-size-lg">Saldo Inicial</label>
                                    <input type="text" id="inputSaldoInicial" name="inputSaldoInicial" value="" class="form-control" readonly>
                                </div>
                            </div>
    
                            <div class="modal-footer">
                                <button type="button" class="btn btn-basic legitRipple" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn bg-slate legitRipple">Abrir Caixa</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="modal_small_Retirada_Caixa" class="modal fade">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="custon-modal-title">
                            <i class=""></i>
                            <h2 class="modal-title p-2">Retirada do Caixa</h2>
                            <i class=""></i>
                        </div>

                        <div class="modal-body mt-2">
                            <div class="d-flex justify-content-center">
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input type="radio" name="radioFrequencia" id="radioButtonSangria" class="form-input-styled" value="" checked>Sangria
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input type="radio" name="radioFrequencia" id="radioButtonRetirada" class="form-input-styled" value="">Retirada
                                    </label>
                                </div>
                            </div>
                            
                            <div id="conteudoSangria">
                                <div class="row mt-4">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="valorSangria" class="font-size-lg">Valor <span class="text-danger">*</span></label>
                                            <input type="text" id="valorSangria" name="valorSangria" onkeyup="moeda(this)" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-lg-8">
                                        <div class="form-group">
                                            <label for="pagamentoSangria" class="font-size-lg">Forma de Pagamento <span class="text-danger">*</span></label>
                                            <select id="pagamentoSangria" name="pagamentoSangria" class="form-control form-control-select2" aria-hidden="true">
                                                <option value="">Selecionar</option>
                                                <?php
                                                $sql = "SELECT FrPagId, FrPagNome, FrPagChave
                                                        FROM FormaPagamento
                                                        JOIN Situacao on SituaId = FrPagStatus
                                                        WHERE FrPagUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                                        ORDER BY FrPagNome ASC";
                                
                                                $result = $conn->query($sql);
                                                $rowFormaPagamento = $result->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($rowFormaPagamento as $item) {
                                                    print('<option value="' . $item['FrPagId'] . '-' . $item['FrPagChave'] . '">' . $item['FrPagNome'] . '</option>');
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                       
                                <div class="form-group">
                                    <label for="justificativaSangria" class="font-size-lg">Justificativa<span class="text-danger"> *</span></label>
                                    <div class="input-group">
                                        <textarea id="justificativaSangria" class="form-control" name="justificativaSangria" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div id="conteudoRetirada" style="display: none;">
                                <div class="row mt-4">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="valorRetirada" class="font-size-lg">Valor <span class="text-danger">*</span></label>
                                            <input type="text" id="valorRetirada" name="valorRetirada" onkeyup="moeda(this)" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-lg-8">
                                        <div class="form-group">
                                            <label for="pagamentoRetirada" class="font-size-lg">Forma de Pagamento <span class="text-danger">*</span></label>
                                            <select id="pagamentoRetirada" name="pagamentoRetirada" class="form-control form-control-select2" aria-hidden="true">
                                                <option value="">Selecionar</option>
                                                <?php
                                                $sql = "SELECT FrPagId, FrPagNome, FrPagChave
                                                        FROM FormaPagamento
                                                        JOIN Situacao on SituaId = FrPagStatus
                                                        WHERE FrPagUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                                        ORDER BY FrPagNome ASC";
                                
                                                $result = $conn->query($sql);
                                                $rowFormaPagamento = $result->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($rowFormaPagamento as $item) {
                                                    print('<option value="' . $item['FrPagId'] . '-' . $item['FrPagChave'] . '">' . $item['FrPagNome'] . '</option>');
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>   

                                <div class="form-group">
                                    <label for="planoContasRetirada" class="font-size-lg">Plano de Contas <span class="text-danger">*</span></label>
                                    <select id="planoContasRetirada" name="planoContasRetirada" class="form-control form-control-select2" aria-hidden="true">
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
                                            print('<option value="' . $item['PlConId'] . '">' . $item['PlConCodigo'] . ' - ' . $item['PlConNome'] . '</option>');
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="centroCustoRetirada" class="font-size-lg">Centro de Custos <span class="text-danger">*</span></label>
                                    <select id="centroCustoRetirada" name="centroCustoRetirada" class="form-control form-control-select2" aria-hidden="true">
                                        <option value="">Selecionar</option>
                                        <?php
                                        $sql = "SELECT CnCusId, CnCusNome, SituaChave, CnCusNomePersonalizado
                                                FROM CentroCusto
                                                JOIN Situacao on SituaId = CnCusStatus
                                                WHERE CnCusUnidade = $_SESSION[UnidadeId] and SituaChave = 'ATIVO'
                                                ORDER BY CnCusNome ASC";
                                        $result = $conn->query($sql);
                                        $rowCentroCusto = $result->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($rowCentroCusto as $item) {
                                            $cnCusDescricao = $item['CnCusNomePersonalizado'] === NULL ? $item['CnCusNome'] : $item['CnCusNomePersonalizado'];
                                            print('<option value="' . $item['CnCusId'] . '">' . $cnCusDescricao . '</option>');
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="fornecedorRetirada" class="font-size-lg">Fornecedor <span class="text-danger">*</span></label>
                                    <select id="fornecedorRetirada" name="fornecedorRetirada" class="form-control form-control-select2" aria-hidden="true">
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
                                            print('<option value="' . $item['ForneId'] . '">' . $item['ForneNome'] . '</option>');
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="justificativaRetirada" class="font-size-lg">Justificativa<span class="text-danger"> *</span></label>
                                    <div class="input-group">
                                        <textarea id="justificativaRetirada" class="form-control" name="justificativaRetirada" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button id="btnCancelar" type="button" class="btn btn-basic legitRipple" data-dismiss="modal">Cancelar</button>
                            <button id="btnFinalizarRetirada" type="button" class="btn bg-slate legitRipple">Finalizar e Imprimir</button>
                        </div>       
                    </div>
                </div>
            </div>

            <div id="modal_large_detalhamento_cheque" data-backdrop="static" class="modal fade" tabindex="-1" style="z-index: 1060;">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="custon-modal-title">
                            <i class=""></i>
                            <h2 class="modal-title p-2">Detalhamento de Cheque</h2>
                            <i class=""></i>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="numCheque" class="font-size-lg">Nº do Cheque <span class="text-danger">*</span></label>
                                        <input type="text" id="numCheque" name="numCheque" value="" class="form-control removeValidacao font-size-lg">
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="valorCheque" class="font-size-lg">Valor <span class="text-danger">*</span></label>
                                        <input type="text" id="valorCheque" onkeyup="moeda(this)" name="valorCheque" value="" class="form-control removeValidacao font-size-lg">
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="dataEmissaoCheque" class="font-size-lg">Data da Emissão <span class="text-danger">*</span></label>
                                        <input type="date" id="dataEmissaoCheque" name="dataEmissaoCheque" min="1800-01-01" max="2100-12-12" class="form-control font-size-lg" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="dataVencimentoCheque" class="font-size-lg">Data do Vencimento <span class="text-danger">*</span></label>
                                        <input type="date" id="dataVencimentoCheque" name="dataVencimentoCheque" min="1800-01-01" max="2100-12-12" class="form-control font-size-lg" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>
                                
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <!--Input para controle, para que caso acesse o PDV pela abertura de caixa ele fará o cadastro da nova abertura de caixa-->
                                        <input type="hidden" id="inputAbrirCaixa" name="inputAbrirCaixa" value="" class="form-control removeValidacao">
    
                                        <label for="cmbBancoCheque">Banco <span class="text-danger">*</span></label>
                                        <select id="cmbBancoCheque" name="cmbBancoCheque" class="form-control form-control-select2 select2-hidden-accessible" aria-hidden="true">
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

                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="agenciaCheque" class="font-size-lg">Agência <span class="text-danger">*</span></label>
                                        <input type="text" id="agenciaCheque" name="agenciaCheque" value="" class="form-control removeValidacao font-size-lg">
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="contaCheque" class="font-size-lg">Conta <span class="text-danger">*</span></label>
                                        <input type="text" id="contaCheque" name="contaCheque" value="" class="form-control removeValidacao font-size-lg">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-9">
                                    <div class="form-group">
                                        <label for="nomeCheque" class="font-size-lg">Nome <span class="text-danger">*</span></label>
                                        <input type="text" id="nomeCheque" name="nomeCheque" value="" class="form-control removeValidacao font-size-lg">
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="cpfCheque" class="font-size-lg">CPF <span class="text-danger">*</span></label>
                                        <input type="text" id="cpfCheque" name="cpfCheque" value="" data-mask="999.999.999-99" class="form-control removeValidacao font-size-lg">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button id="btnCancelaDadosCheque" type="button" class="btn btn-basic legitRipple" data-dismiss="modal">Cancelar</button>
                            <button id="btnDadosCheque" class="btn bg-slate legitRipple">Salvar</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /small modal -->

            <?php include_once("footer.php"); ?>

        </div>
        <!-- /main content -->

        <?php include_once("sidebar-right-resumo-caixa.php"); ?>

    </div>
    <!-- /page content -->

    <?php include_once("alerta.php"); ?>

</body>

</html>