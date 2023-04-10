<?php

include_once("sessao.php");

//Fazer alteração posteriormente
$_SESSION['PaginaAtual'] = 'Caixa PDV';

include('global_assets/php/conexao.php');

$iUnidade = $_SESSION['UnidadeId'];
$iEmpresa = $_SESSION['EmpreId'];
$usuarioId = $_SESSION['UsuarId'];

$nomeCaixa = '';

if(isset($_POST['inputAbrirCaixa'])) {
    $dataHorAtual = date('Y-m-d H:i:s');
    $saldoInicial = str_replace(',', '.', $_POST['inputSaldoInicial']);

    $sql = "INSERT INTO CaixaAbertura (CxAbeCaixa, CxAbeDataHoraAbertura, CxAbeOperador, 
                        CxAbeSaldoInicial, CxAbeStatus, CxAbeUnidade) 
            VALUES ( :iCaixa, :sDataHoraAbertura, :iOperador, :bSaldoInicial, :iStatus, :iUnidade)";
    $result = $conn->prepare($sql);

    $result->execute(array(
        ':iCaixa' => $_POST['cmbCaixa'],
        ':sDataHoraAbertura' => $dataHorAtual,
        ':iOperador' => $_SESSION['UsuarId'],
        ':bSaldoInicial' => $saldoInicial,
        ':iStatus' => 1,
        ':iUnidade' => $_SESSION['UnidadeId']
    )); //Depois se informar a respeito do status

    $aberturaCaixaId = $conn->lastInsertId();;
    $nomeCaixa = $_POST['inputCaixaNome'];
}

if(isset($_POST['inputAberturaCaixaId'])) {
    $_SESSION['aberturaCaixaId'] = $_POST['inputAberturaCaixaId'];
    $_SESSION['aberturaCaixaNome'] = $_POST['inputAberturaCaixaNome'];
}

$dataAtual = date('Y-m-d');

if(isset($_SESSION['aberturaCaixaId'])) {
    $aberturaCaixaId = $_SESSION['aberturaCaixaId'];
    $nomeCaixa = $_SESSION['aberturaCaixaNome'];
}else {
    $sql = "SELECT SituaId
            FROM Situacao
            WHERE SituaChave = 'ABERTO'";
    $result = $conn->query($sql);
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $iStatus = $row['SituaId'];

    //Para pegar a última consulta
    $sql_saldoInicial    = "SELECT CxAbeId, CaixaNome, CxAbeDataHoraAbertura
                            FROM CaixaAbertura
                            JOIN Caixa on CaixaId = CxAbeCaixa
                            WHERE CxAbeOperador = ".$_SESSION['UsuarId']." AND CxAbeUnidade = $_SESSION[UnidadeId] AND CxAbeStatus = $iStatus
                            ORDER BY CxAbeId DESC";
    $resultSaldoInicial  = $conn->query($sql_saldoInicial);

    if($rowSaldoInicial = $resultSaldoInicial->fetch(PDO::FETCH_ASSOC)) {
        $arrayDataAbertura = explode(' ', $rowSaldoInicial['CxAbeDataHoraAbertura']);
        $dataAbertura = $arrayDataAbertura[0];

        //Verifica se o caixa foi aberto hoje, se não foi o operador terá que fechar
        if($dataAbertura == $dataAtual) {
            $_SESSION['aberturaCaixaId'] = $rowSaldoInicial['CxAbeId'];
            $_SESSION['aberturaCaixaNome'] = $rowSaldoInicial['CaixaNome']; 
            $_SESSION['aberturaCaixaData'] = $rowSaldoInicial['CxAbeDataHoraAbertura']; 
            
            $aberturaCaixaId = $_SESSION['aberturaCaixaId'];
            $nomeCaixa = $_SESSION['aberturaCaixaNome'];
        }else {
            $_SESSION['PDVFechamentoCaixa'] = 'Fechar_Caixa_Anterior';
            irpara('caixaFechamento.php'); 
        }
    }else {
        $_SESSION['aberturaCaixa'] = 'Abrir_Novo_Caixaz';
        irpara('caixaMovimentacao.php'); 
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Lamparinas | PDV do Caixa</title>

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
    <?php
        echo "<script>
                iUnidade = $iUnidade
                iEmpresa = $iEmpresa
            </script>"
	?>

    <script type="text/javascript">
        const socket = WebSocketConnect(iUnidade,iEmpresa)
        socket.onmessage = function (event) {
			menssage = JSON.parse(event.data)
			if(menssage.type == 'ATENDIMENTO'){
				getContent()
			}
		};
        
        $(document).ready(function () {
            getContent()
            $('#tblAtendimento').DataTable( {
                "order": [[ 0, "asc" ]],
                autoWidth: false,
                responsive: true,
                pageLength : 5,
                lengthMenu: [5, 10, 20],
                columnDefs: [
                {
                    orderable: true,   //Marca
                    width: "40%",
                    targets: [0]
                },
                { 
                    orderable: true,   //Situação
                    width: "40%",
                    targets: [1]
                },
                { 
                    orderable: true,   //Ações
                    width: "20%",
                    targets: [2]
                }],
                dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
                language: {
                    search: '<span>Filtro:</span> _INPUT_',
                    searchPlaceholder: 'filtra qualquer coluna...',
                    lengthMenu: '<span>Mostrar:</span> _MENU_',
                    paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
                }
            });
            
            // Select2 for length menu styling
            var _componentSelect2 = function() {
                if (!$().select2) {
                    console.warn('Warning - select2.min.js is not loaded.');
                    return;
                }

                // Initialize
                $('.dataTables_length select').select2({
                    minimumResultsForSearch: Infinity,
                    dropdownAutoWidth: true,
                    width: 'auto'
                });
            };
            _componentSelect2();
  
            
            $('#cmbAtendimento').on("change", function() {
                let urlConsultaAberturaCaixa = "consultaCaixaServicos.php";
                let idAtendimento = ($(this).val() != '') ? $(this).val() : 0;

                let inputsValuesConsulta = {
                    inputAtendimentoId: idAtendimento
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
                        table = $('#tblAtendimento').DataTable()
                        table = $('#tblAtendimento').DataTable().clear().draw()
                        //--|

                        table = $('#tblAtendimento').DataTable()

                        let rowNode

                        let valorTotal = null;
                        let descontoTotal = null;

                        resposta.forEach(item => {
                            valor = item.data[2].replaceAll(".", "").replace(",", ".");
                            valorTotal += parseFloat(valor);

                            desconto = item.data[3].replaceAll(".", "").replace(",", ".");
                            descontoTotal += parseFloat(desconto);
                            
                            rowNode = table.row.add(item.data).draw().node();

                            $(rowNode).find('td').eq(2).attr('style', 'text-align: right;');
                        })

                        valorFinal = valorTotal - descontoTotal;
                        valorFinal = (valorFinal != null && valorFinal > 0) ?  float2moeda(valorFinal) : float2moeda(0);
                        valorTotal = (valorTotal != null) ?  float2moeda(valorTotal) : float2moeda(0);
                        descontoTotal = (descontoTotal != null) ?  float2moeda(descontoTotal) : float2moeda(0);
                        
                        $("#valorTotal").text(valorTotal);
                        $("#desconto").text(descontoTotal);
                        $("#valorFinal").text(valorFinal);
                    }
                })
            })

            //Mostra o valor de cada parcela no pop up - Finalização do Recebimento
            function calculaValorParcela(quantidade) {
                let valorFinal = $("#valorFinal").text().replaceAll(".", "").replace(",", ".");
                valorFinal = parseFloat(valorFinal) / quantidade;
                
                $("#valorPorParcela").val(float2moeda(valorFinal));
            }

            //A função $(document).on... trabalha dinâmicamente, neste caso funciona com html colocado posteriormente via javascript
		    $(document).on("change", "#cmbFormaPagamento", function(){
                let arrayFormaPagamentoId = $(this).val().split("-");;
                
                if(arrayFormaPagamentoId[1] == 'CHEQUE') {
                    $("#detalhamentoCheque").trigger("click");
                }
            });

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
            
            $('#cmbParcela').on("change", function() {
                let quantidadeParcelas =  $("#cmbParcela").val() != '' ? parseInt($("#cmbParcela").val()) : 1;

                calculaValorParcela(quantidadeParcelas);
            })

            $("#btnCancelaDadosCheque").on('click', () => {
                var menssagem = 'Forma de pagamento por cheque cancelada!'
                alerta('Atenção', menssagem, 'error')

                $("#pagamentoSangria").val('').change()
                $("#cmbFormaPagamento").val('').change()
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

            $("#btnCancelar").on('click', () => {
                $("#cmbAtendimento").val('').change();
                $("#valorTotal").text(float2moeda(0));
                $("#desconto").text(float2moeda(0));
                $("#valorFinal").text(float2moeda(0));
            })

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

            $("#btnImprimir").on('click', () => {
                alert('imprimir')
            })
            
            $("#btnFinalizar").on('click', () => {
                let idAtendimento = $("#cmbAtendimento").val();

                if(idAtendimento != '') {
                    let parcelas = $("#cmbParcela").val() != '' ? parseInt($("#cmbParcela").val()) : 1;

                    calculaValorParcela(parcelas);

                    $("#recebimento").trigger("click");
                }else {
                    $("#cmbAtendimento").focus();
                    
                    var menssagem = 'Informe um atendimento por favor!'
                    alerta('Atenção', menssagem, 'error')
					return
                }
            })

            $("#btnFinalizarRecebimento").on('click', () => {
                if($("#cmbFormaPagamento").val() == '') {
                    $("#cmbFormaPagamento").focus();
                    var menssagem = 'Informe uma forma de pagamento por favor!'
                    alerta('Atenção', menssagem, 'error')
					return;
                }

                let nomeFormaPagamento = $('#cmbFormaPagamento :selected').text();
                let caixaNome = $("#inputCaixaNome").val();
                let caixaId = $("#inputCaixaId").val();
                let idAtendimento = $("#cmbAtendimento").val();
                let valorTotal = $("#valorTotal").text();
                let desconto = $("#desconto").text();
                let valorFinal = $("#valorFinal").text();
                let formaPagamento = $("#cmbFormaPagamento").val().split('-');
                let parcelamento = $("#cmbParcela").val() != '' ? $("#cmbParcela").val() : 1;
                let numeroCheque = nomeFormaPagamento == 'Cheque' ? $("#numCheque").val() : '';
                let valorCheque = nomeFormaPagamento == 'Cheque' ? $("#valorCheque").val() : '';
                let dataEmissao = nomeFormaPagamento == 'Cheque' ? $("#dataEmissaoCheque").val() : '';
                let dataVencimento = nomeFormaPagamento == 'Cheque' ? $("#dataVencimentoCheque").val() : '';
                let bancoCheque = nomeFormaPagamento == 'Cheque' ? $("#cmbBancoCheque").val() : '';
                let agenciaCheque = nomeFormaPagamento == 'Cheque' ? $("#agenciaCheque").val() : '';
                let contaCheque = nomeFormaPagamento == 'Cheque' ? $("#contaCheque").val() : '';
                let nomeCheque = nomeFormaPagamento == 'Cheque' ? $("#nomeCheque").val() : '';
                let cpfCheque = nomeFormaPagamento == 'Cheque' ? $("#cpfCheque").val().replaceAll(".", "").replace("-", "") : '';

                $('#modal_small_Recebimento').modal('hide');

                $.ajax({
                    type: 'POST',
                    url: 'filtraCaixaPDV.php',
                    dataType: 'json',
                    data:{
                        'tipoRequest': 'FINALIZARATENDIMENTO',
                        'inputCaixaNome' : caixaNome,
                        'inputCaixaId' : caixaId,
                        'inputAtendimentoId' : idAtendimento,
                        'inputValorTotal' : valorTotal,
                        'inputDesconto' : desconto,
                        'inputValorFinal' : valorFinal,
                        'inputFormaPagamento' : formaPagamento[0],
                        'inputNumeroCheque' : numeroCheque,
                        'inputValorCheque' : valorCheque,
                        'inputDataEmissaoCheque' : dataEmissao,
                        'inputDataVencimentoCheque' : dataVencimento,
                        'inputBancoCheque' : bancoCheque,
                        'inputAgenciaCheque' : agenciaCheque,
                        'inputContaCheque' : contaCheque,
                        'inputNomeCheque' : nomeCheque,
                        'inputCpfCheque' : cpfCheque,
                        'inputParcelas' : parcelamento
                    },
                    success: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
                        $("#cmbAtendimento").val('').change();
                        $("#valorTotal").text(float2moeda(0));
                        $("#desconto").text(float2moeda(0));
                        $("#valorFinal").text(float2moeda(0));
                        let table 
                        table = $('#tblAtendimento').DataTable()
                        table = $('#tblAtendimento').DataTable().clear().draw()
                        table = $('#tblAtendimento').DataTable()
                        getContent();
					},
					error: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
					}
                });

            })
        });

        function getContent(){
			// busca os dados para adicionar no select de Atendimentos
			$.ajax({
				type: 'POST',
				url: 'filtraCaixaPDV.php',
				dataType: 'json',
				data:{
					'tipoRequest': 'ATENDIMENTOS'
				},
				success: function(response) {
                    $('#cmbAtendimento').html("<option value=''>Selecionar</option>")

                    response.forEach(item=>{
                        $('#cmbAtendimento').append(`<option value='${item.id}'>${item.registro} - ${item.nome}</option>`)
                    })
				}
			});
		}
    </script>

    <style>
         h1 {
            font-size: 1.5625rem;
        }
        .valorTotalEDesconto {
            font-size: 2.5625rem;
            border: 1px solid #ccc;
            float: right;
            min-width: 250px;
        }

        .valorFinal {
            font-size: 3.5625rem; 
        }

        body{
            background: #355370;
        }
    </style>

</head>

<body class="sidebar-xs">

    <!-- Page content -->
    <div class="page-content">

        <!-- Main content -->
        <div class="content-wrapper">

            <!-- Content area -->
            <!--<div class="content">-->

                <div class="col-lg-12">
                    <!-- Basic responsive configuration -->
                    <div class="card" style="background-color: #466d96">

                        <div class="card-header header-elements" style="background-color: #355370">
                            <div class="row text-white">
                                <div class="col-6">
                                    <h3 class="card-title" style="color: #FFFFFF">PDV - <?php echo  $_SESSION['UnidadeNome']; ?></h3>
                                </div>

                                <div class="col-3" style="padding-top: 5px;">
                                    <h4 class="m-auto">PDV: <?php echo $nomeCaixa; ?> | Operador: <?php echo nomeSobrenome($_SESSION['UsuarNome'], 1); ?></h4>
                                </div>

                                <div class="col-3 text-right" style="padding-top: 5px;">
                                    <h4 class="m-auto">Data: <?php echo date('d/m/Y'); ?></h4>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row" style="padding: 20px 5px 15px 2px">
                                
                                <div class="col-lg-4 col-md-6 col-sm-12">

                                    <div style="background-color:#fff; padding: 20px;">

                                        <div class="form-group" style="font-size: 1.200rem;">
                                            <label for="cmbAtendimento" class="font-size-lg" style="font-size: 1.200rem; color: #333">Atendimento <span class="text-danger">*</span></label>
                                            <select id="cmbAtendimento" name="cmbAtendimento" class="form-control form-control-select2" required>
                                                <!--  -->
                                            </select>
                                        </div>     
                                    </div>
                                    <div style="background-color:#eee; padding: 20px; min-height: 303px; text-align: right">
                                        <div class="row">
                                            <div class="col-12">
                                                <h1 class="text-right pr-3">Valor Total (R$)</h1>
                                                <div class="text-right pr-3">
                                                    <h1 id="valorTotal" class="p-1 bg-white valorTotalEDesconto">0,00</h1>
                                                </div>                                                
                                            </div>
                                        </div>     
                                        <div class="row">                           
                                            <div class="col-12">
                                                <h1 class="text-right pr-3">Desconto (R$)</h1>
                                                <div class="text-right pr-3">
                                                    <h1 id="desconto" class="text-right p-1 bg-white valorTotalEDesconto">0,00</h1>
                                                </div>
                                            </div>
                                        </div>
                                        <hr/>
                                        <div class="row">
                                            <div class="col-12">
                                                <h1 class="text-right pr-3">Total à Receber (R$)</h1>
                                                <div class="text-right pr-3">
                                                    <h1 id="valorFinal" class="text-right p-1 bg-white text-orange valorTotalEDesconto">0,00</h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12 mt-3 text-left">
                                            <a href="caixaMovimentacao.php" class="btn bg-slate-700 legitRipple">Movimentação</a>
                                            <a href="#" class="btn bg-slate-700 legitRipple" id="btnRetirada" data-toggle="modal" data-target="#modal_small_Retirada_Caixa">Retirada</a>
                                        </div>	
                                    </div>
                                </div>
                                
                                <div class="col-lg-8 col-md-6 col-sm-12">
                                    <div class="p-4 bg-white" style="min-height:628px;">
                                        <table id="tblAtendimento" class="table">
                                            <thead>
                                                <tr class="bg-slate">
                                                    <th>Procedimento</th>
                                                    <th>Médico</th>
                                                    <th class="text-center">Valor</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row" style="background-color: #466d96;">
                                        <div class="col-4 mt-3">
                                            <img src="https://lamparinas.com.br/wp-content/uploads/2021/10/Logo_Novo_Site-1536x491.png" style="max-width: 300px;" alt="Logo Lamparinas" />
                                        </div>

                                        <div class="col-lg-8 mt-3 text-right">
                                            <button id="btnFinalizar" class="btn btn-principal legitRipple btn-lg" style="font-size: 2rem;">Finalizar</button>
                                        </div>	
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /basic responsive configuration -->

                </div>
                
                <form name="formAtendimento" method="post" action="caixaPDV.php">   
                    <input type="hidden" id="inputCaixaNome" name="inputCaixaNome" value="<?php echo $nomeCaixa; ?>">
                    <input type="hidden" id="inputCaixaId" name="inputCaixaId" value="<?php echo $aberturaCaixaId; ?>">
                    <input type="hidden" id="inputAtendimentoId" name="inputAtendimentoId">
                    <input type="hidden" id="inputValorTotal" name="inputValorTotal">
                    <input type="hidden" id="inputDesconto" name="inputDesconto">
                    <input type="hidden" id="inputValorFinal" name="inputValorFinal">
                    <input type="hidden" id="inputFormaPagamento" name="inputFormaPagamento">
                    <input type="hidden" id="inputNumeroCheque" name="inputNumeroCheque">
                    <input type="hidden" id="inputValorCheque" name="inputValorCheque">
                    <input type="hidden" id="inputDataEmissaoCheque" name="inputDataEmissaoCheque">
                    <input type="hidden" id="inputDataVencimentoCheque" name="inputDataVencimentoCheque">
                    <input type="hidden" id="inputBancoCheque" name="inputBancoCheque">
                    <input type="hidden" id="inputAgenciaCheque" name="inputAgenciaCheque">
                    <input type="hidden" id="inputContaCheque" name="inputContaCheque">
                    <input type="hidden" id="inputNomeCheque" name="inputNomeCheque">
                    <input type="hidden" id="inputCpfCheque" name="inputCpfCheque">
                    <input type="hidden" id="inputParcelas" name="inputParcelas">
                </form>

                <form name="formCaixaAberturaId" method="post">
					<input type="hidden" id="inputAberturaCaixaId" name="inputAberturaCaixaId" value="<?php echo $aberturaCaixaId; ?>">
                    <input type="hidden" id="inputCaixaId" name="inputCaixaId" value="">
                    <input type="hidden" id="inputAberturaCaixaNome" name="inputAberturaCaixaNome" value="<?php echo $nomeCaixa; ?>">
				</form>

                <form id="formRetiradaCaixa" name="formRetiradaCaixa" method="POST">
                    <input type="hidden" id="inputValorRetirada" name="inputValorRetirada" value="">
                    <input type="hidden" id="cmbPagamentoRetirada" name="cmbPagamentoRetirada" value="">
                    <input type="hidden" id="inputJustificativaRetirada" name="inputJustificativaRetirada" value="">
                </form>

                <form id="formMovimentacao" name="formMovimentacao" method="POST">
                    <input type="hidden" id="inputReciboId" name="inputReciboId" value="">
                    <input type="hidden" id="inputAtendimento" name="inputAtendimento" value="">
                </form>
            <!--</div>-->
            <!-- /content area -->

            <!--Link para finalizar recebimento-->
            <a id="recebimento" data-toggle="modal" data-target="#modal_small_Recebimento"></a>
            <a id="detalhamentoCheque" data-toggle="modal" data-target="#modal_large_detalhamento_cheque"></a>

            <!-- modal -->
            <!--Procurar uma correção com relação ao filtro do select-->
            <div id="modal_small_Recebimento" class="modal fade">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="custon-modal-title">
                            <i class=""></i>
                            <h2 class="modal-title p-2">Finalização do recebimento</h2>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <i class=""></i>
                        </div>

                        <div class="modal-body">
                            <div class="form-group mt-2">
                                <label for="cmbFormaPagamento" class="font-size-lg">Forma de Pagamento <span class="text-danger">*</span></label>
                                <select id="cmbFormaPagamento" name="cmbFormaPagamento" class="form-control form-control-select2 select2-hidden-accessible" required="" tabindex="-1" aria-hidden="true">
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

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group mt-2">
                                        <label for="cmbParcela" class="font-size-lg">Parcelas</label>
                                        <select id="cmbParcela" name="cmbParcela" class="form-control form-control-select2 select2-hidden-accessible" required="" tabindex="-1" aria-hidden="true">
                                            <option value="">Selecionar</option>
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

                                <div class="col-lg-8">
                                    <div class="form-group mt-2">
                                        <label for="valorPorParcela" class="font-size-lg">Valor por parcela</label>
                                        <input type="text" id="valorPorParcela" name="valorPorParcela" class="form-control removeValidacao font-size-lg" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button id="btnImprimir" type="button" class="btn btn-basic legitRipple">Finalizar e Imprimir</button>
                            <button id="btnFinalizarRecebimento" type="button" class="btn bg-slate legitRipple">Finalizar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="modal_large_detalhamento_cheque" data-backdrop="static" class="modal fade" style="z-index: 1060;">
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
                            <a id="fechaPopUp" data-dismiss="modal"></a>
                            <button id="btnCancelaDadosCheque" type="button" class="btn btn-basic legitRipple" data-dismiss="modal">Cancelar</button>
                            <button id="btnDadosCheque" class="btn bg-slate legitRipple">Salvar</button>
                        </div>
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
            <!-- / modal -->

        </div>
        <!-- /main content -->

    </div>
    <!-- /page content -->

    <?php include_once("alerta.php"); ?>

</body>

</html>