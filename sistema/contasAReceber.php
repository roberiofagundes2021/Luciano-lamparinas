<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Relação de Contas à Receber';

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
$dataFim = date("Y")."-12-31";

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

            $.fn.dataTable.moment('DD/MM/YYYY'); //Para corrigir a ordenação por data			

            /* Início: Tabela Personalizada */
            $('#tblMovimentacao').DataTable({
                "order": [
                    [1, "desc"],
                    [2, "asc"]
                ],
                autoWidth: false,
                responsive: true,
                paginate: false,
                columnDefs: [{
                        orderable: false, //selecionar
                        width: "5%",
                        targets: [0]
                    },
                    {
                        orderable: true, //Vencimento
                        width: "7%",
                        targets: [1]
                    },
                    {
                        orderable: true, //Descrição
                        width: "22%",
                        targets: [2]
                    },
                    {
                        orderable: true, //Cliente
                        width: "25%",
                        targets: [3]
                    },
                    {
                        orderable: true, //Número Doc.
                        width: "13%",
                        targets: [4]
                    },
                    {
                        orderable: true, //Valor Total
                        width: "15%",
                        targets: [5]
                    },
                    {
                        orderable: true, //Status
                        width: "9%",
                        targets: [6]
                    },
                    {
                        orderable: false, //Ações
                        width: "4%",
                        targets: [7]
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

            $('#tblParcelamento').DataTable({
                "order": [
                    [0, "desc"],
                    [1, "desc"],
                    [2, "asc"]
                ],
                autoWidth: false,
                responsive: true,
                columnDefs: [{
                        orderable: true, //Item
                        width: "10%",
                        targets: [0]
                    },
                    {
                        orderable: true, //Descrição
                        width: "40%",
                        targets: [1]
                    },
                    {
                        orderable: true, //Vencimento
                        width: "25%",
                        targets: [2]
                    },
                    {
                        orderable: true, //Valor
                        width: "25%",
                        targets: [3]
                    }
                ],
                dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
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
            function modalParcelas() {

                $('.btnParcelar').each((i, elem) => {
                    $(elem).on('click', function() {

                        let recebimentos = $("#pagamentoAgrupadoContainer").children()

                        let linha = $(elem).parent().parent().parent().parent().parent()
                            .parent()

                        let tds = linha.children();
                        let valor = $(tds[5]).html();
                        let dataVencimentolistChild = $(tds[1]).children()
                        let dataVencimento = $(dataVencimentolistChild[1]).val()
                        let descricaoContent = $(tds[2]).children()[0]
                        let descricao = $(descricaoContent).html()
                        let idContainer = $(tds[0]).children()
                        let id = $(idContainer[1]).val()
                        let status = $(tds[6]).html();

                        if (status == 'Recebido') {
                            alerta('Atenção', 'A conta selecionada já foi recebida!', 'error');
                            return false
                        } else {
                            $('#page-modal').fadeIn(200);

                            //Conteúdo novo

                            $('#inputValor').val(valor)
                            $('#inputDataVencimento').val(dataVencimento)
                            $('#inputDescricao').val(descricao)
                            $('#inputId').val(id)

                            const fonte1 = 'style="font-size: 1.1rem"'
                        }
                    })
                })

                $('#modal-close').on('click', function() {
                    $('#page-modal').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                    $("#parcelasContainer").html('');
                })
            }*/

            function cadastraParcelas() {
                let parcelasNum = $("#cmbParcelas").val()
                let id = $("#inputId").val()
                let numLinhas = $('tbody').children().length

                let dataParcelas = new Array

                for (let i = 1; i <= parcelasNum; i++) {

                    let arrayParcela = new Array

                    dataParcelas.push({
                        descricao: $(`#inputParcelaDescricao${i}`).val(),
                        vencimento: $(`#inputParcelaDataVencimento${i}`).val(),
                        valor: $(`#inputParcelaValorAReceber${i}`).val()
                    })

                    data = {
                        parcelas: JSON.stringify(dataParcelas),
                        numParcelas: parcelasNum,
                        idConta: id,
                        elementosNaGride: numLinhas
                    }
                }

                let url = 'contasAReceberParcelamento.php'

                $.post(
                    url,
                    data,
                    (data) => {
                        //$('tbody').append(data)
                        alerta('Atenção', 'Parcelas geradas com sucesso!')
                        location.href = "contasAReceber.php";
                        //modalParcelas()
                        $('#elementosGrid').val(parseInt(parcelasNum) + parseInt(numLinhas))
                        RecebimentoAgrupado()
                      //  atualizaTotal()                        
                    }
                )

            }
            $("#salvar").on('click', () => {
                cadastraParcelas()
                $("#parcelasContainer").html('')
                $('#page-modal').fadeOut(200);
                $('body').css('overflow', 'scroll');
            })
            /////////////////////////////////////////////////////////////////
            function gerarParcelas(parcelas, valorTotal, dataVencimento, periodicidade, descricao) {
                $("#parcelasContainer").html("")

                let valorParcela = float2moeda(valorTotal / parcelas)
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
                    let valorTotal = parseFloat($("#inputValor").val().replace(".", "").replace(".", "").replace(",", "."))
                    let dataVencimento = $("#inputDataVencimento").val()
                    let periodicidade = $("#cmbPeriodicidade").val()
                    let descricao = $("#inputDescricao").val()
                    gerarParcelas(parcelas, valorTotal, dataVencimento, periodicidade, descricao)
                })
            }
            parcelamento()
            /////////////////////////////////////////////////////////////////

            function desfazerPagamentoAgrupado() {
                $("#pagamentoAgrupadoForm").children().remove()
                $("#pagamentoAgrupadoContainer").html("")
                $("#efetuarRecebimento").prop('disabled', true)

                $("#cmbFormaPagamentoPA").val('')
                $("#select2-cmbFormaPagamentoPA-container").html("Selecionar")
                $("#cmbContaBancoPA").val('')
                $("#select2-cmbContaBancoPA-container").html("Selecionar")

                $(".clicado").each((i, elem) => {
                    $(elem).removeClass('clicado').prop('checked', false)
                })
            }

            function pagamentoAgrupado(contadorLinha) {

                for (let i = 1; i <= contadorLinha; i++) {

                    let id = $(`#check${i}`).parent().children().last().val();
                    let elementosLista = $(`#check${i}`).parent().parent().children()
                    let descricaoLink = $(elementosLista[2]).children()[0]
                    let descricao = $(descricaoLink).html()
                    let status = $(elementosLista[6]).html()
                    let numeroDocumento = $(elementosLista[4]).html()
                    let valor = $(elementosLista[5]).html()

                    $(`#check${i}`).on('click', () => {

                        if (status == 'Recebido') {
                            alerta('Atenção', 'A conta selecionada já foi paga!', 'error');
                            $(`#check${i}`).prop('checked', false)
                            return false
                        } else {
                            if (!$(`#check${i}`).hasClass('clicado')) {
                                $(`#check${i}`).addClass('clicado')
                                let input =
                                    `<input type="hidden" name="conta${i}" value="${id}" descricao="${descricao}" numeroDocumento="${numeroDocumento}" valor="${valor}">`
                                $("#pagamentoAgrupadoForm").append(input)

                                let quantInputs = $("#pagamentoAgrupadoForm").children()

                                if (quantInputs.length >= 1) {
                                    $("#efetuarRecebimento").removeAttr('disabled')
                                } else {
                                    $("#efetuarRecebimento").attr('disabled')
                                }

                            } else {
                                $(`[name=conta${i}]`).remove()

                                $(`#check${i}`).removeClass('clicado')

                                let quantInputs = $("#pagamentoAgrupadoForm").children()

                                if (quantInputs.length == 0) {
                                    $("#efetuarRecebimento").prop('disabled', true)
                                }
                            }
                        }
                    })
                }
            }

            let countPlanoContas = 0;

            function pagamentoAgrupadoEnvia() {
                let pagamentos = $("#pagamentoAgrupadoContainer").children()
                let dataPagamento = $("#inputDataPagamentoPA").val()
                let valorTotal = $("#inputValorTotalPA").val()
                let formaPagamento = $("#cmbFormaPagamentoPA").val()
                let contaBanco = $("#cmbContaBancoPA").val()
                let descricaoGrupo = $("#inputDescricaoGrupoPA").val()

                let pagamentoValores = []

                pagamentos.each((i, elem) => {
                    let linhaFilhos = $(elem).children()

                    let id = $(`#idPA${i+1}`).val()
                    let descricao = $(`#inputDescricaoPA${i+1}`).val()
                    let numeroDocumento = $(`#inputNumeroDocumentoPA${i+1}`).val()
                    let valor = $(`#inputValorPA${i+1}`).val()

                    pagamentoValores[i] = {
                        id: id,
                        descricao: descricao,
                        numeroDocumento: numeroDocumento,
                        valor: valor
                    }

                })

                data = {
                    valores: pagamentoValores,
                    dataPagamento: dataPagamento,
                    valorTotal: valorTotal,
                    formaPagamento: formaPagamento,
                    contaBanco: contaBanco,
                    descricaoGrupo: descricaoGrupo
                }

                url = 'contasAReceberRecebimentoAgrupado.php'

                $.post(
                    url,
                    data,
                    (data) => {
                        let linhas = $('tbody').children()
                        let ids = data.split('/')
                        for (let h = 0; h <= ids.length; h++) {
                            for (let i = 1; i <= linhas.length - 1; i++) {

                                let p = $(linhas[i]).children()[0]
                                let irm = $(p).children()[1]

                                if ($(irm).val() == ids[h]) {

                                    let linha = $(`#check${i}`)
                                    let status = $(linhas[i]).children()[6]
                                    $(status).html('Recebido')
                                    alerta('Atenção', 'Recebimento agrupado efetuado com sucesso!',
                                        'success');
                                    Filtrar()
                                    desfazerPagamentoAgrupado()
                                    $(`#check${i}`).removeClass('clicado')
                                    atualizaTotal()
                                }
                            }
                        }
                    }
                )
            }

            $("#salvarPA").on('click', (e) => {
                e.preventDefault()
                if($('#cmbFormaPagamentoPA').val() == '') {
                    alerta('Atenção', 'Por favor informe a forma do pagamento!', 'error');
                    $('#cmbFormaPagamentoPA').focus();
                    return false
                }

                if($('#cmbContaBancoPA').val() == '') {
                    alerta('Atenção', 'Por favor informe o banco!', 'error');
                    $('#cmbContaBancoPA').focus();
                    return false
                }

                if($('#inputDescricaoGrupoPA').val() == '') {
                    alerta('Atenção', 'A descrição é obrigatória!', 'error');
                    $('#inputDescricaoGrupoPA').focus();
                    return false
                }
                
                pagamentoAgrupadoEnvia()

                if (countPlanoContas == 0) {
                    desfazerPagamentoAgrupado()
                    $('#modal-pagamentoAgrupado').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                }

                countPlanoContas = 0;
            })

            function modalPagamentoAgrupado() {

                $("#pagamentoAgrupadoContainer").html("")

                $("#efetuarRecebimento").on("click", (e) => {
                    e.preventDefault()

                    let pagamentos = $("#pagamentoAgrupadoForm").children()
                    
                    if(pagamentos.length == 1){
                        pagamentos.each((i, elem) => {
                            let id = $(elem).val()
                            redirecionarPagamento(id)                            
                        })
                    } else {
                        $('#modal-pagamentoAgrupado').fadeIn(200)
                        $("#pagamentoAgrupadoContainer").html("")
                    }

                    let numLinhas = $("#pagamentoAgrupadoForm").children().length
                    let linhasSelecionadas = $("#pagamentoAgrupadoForm").children()
                    let valorTotal = 0;


                    linhasSelecionadas.each((i, elem) => {
                        let id = $(elem).val()
                        let descricao = $(elem).attr('descricao')
                        let numeroDocumento = $(elem).attr('numeroDocumento')
                        let valor = $(elem).attr('valor')
                        let indice = i + 1


                        valorTotal += parseFloat(valor.replace(".", "").replace(",", "."));
                        let elemNode = `<div class="d-flex flex-row justify-content-center">
                    <p class="col-2 mt-3 text-center">
                        ${indice}
                        <input type="hidden" id="idPA${indice}" value="${id}">
                    </p>
                    <div class="form-group col-4 p-2">
                        <input type="text" class="form-control" id="inputDescricaoPA${indice}" name="inputDescricaoPA${indice}" value="${descricao}" readOnly>
                    </div>
                    <div class="form-group col-3 p-2">
                        <input type="text" class="form-control" id="inputNumeroDocumentoPA${indice}" name="inputNumeroDocumentoPA${indice}" value="${numeroDocumento}" readOnly>    
                    </div>
                    <div class="form-group col-3 p-2">
                        <input type="text" class="form-control text-right pr-4" id="inputValorPA${indice}" name="inputValorPA${indice}" value="${valor}" readOnly>
                    </div> 
                </div>`

                        $("#pagamentoAgrupadoContainer").append(elemNode)
                    })

                    $('#inputValorTotalPA').val(float2moeda(valorTotal))
                })

                $('#modal-closePA').on('click', function () {
                    desfazerPagamentoAgrupado()
                    $('#modal-pagamentoAgrupado').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                })
            }
            modalPagamentoAgrupado()

            function redirecionarPagamento(id) {
                let atualiza = "<?php echo $atualizar; ?>"
                atualizaContasAReceber(atualiza, id , 'edita')
            }

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
                // console.log(total)
                $('#footer-total').remove()

                divTotal = `<div id='footer-total' style='position:absolute; left: 75.1%; font-weight: bold; width: 200px;'>Total: ${float2moeda(total)}</div>`

                $('.datatable-footer').append(divTotal);
            }


            function Filtrar() {
                let cont = false;

                const msg = $(
                    '<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty"><img src="global_assets/images/lamparinas/loader.gif" style="width: 120px" alt="Loader"></td></tr>'
                )

                $('tbody').html(msg)

                if ($('#cmbProduto').val() == 'Sem produto' || $('#cmbProduto').val() ==
                    'Filtrando...') $('#cmbProduto').val("")


                let periodoDe = $('#inputPeriodoDe').val()
                let ate = $('#inputAte').val()
                let numdoc = $('#cmbNumDoc').val()
                let clientes = $('#cmbClientes').val()
                let planoContas = $('#cmbPlanoContas').val()
                let FormaPagamento = $("#cmbFormaDeRecebimento").val()
                let inputPermissionAtualiza = $("#inputPermissionAtualiza").val()
                let inputPermissionExclui = $("#inputPermissionExclui").val()
                let statusArray = $('#cmbStatus').val().split('|')
                let status = statusArray[0]
                let statusTipo = statusArray[1]
                let url = "contasAReceberFiltra.php";

                if (statusArray[1] == 'ARECEBER') {
                    $('#dataGrid').html('Vencimento')
                } else if (statusArray[1] == 'RECEBIDO') {
                    $('#dataGrid').html('Recebimento')
                }

                inputsValues = {
                    inputPeriodoDe: periodoDe,
                    inputAte: ate,
                    cmbNumDoc: numdoc,
                    cmbClientes: clientes,
                    cmbPlanoContas: planoContas,
                    cmbFormaDeRecebimento: FormaPagamento,
                    cmbStatus: status,
                    statusTipo: statusTipo,
                    permissionAtualiza: inputPermissionAtualiza,
                    permissionExclui: inputPermissionExclui
                };

                /*
                $.post(
                    url,
                    inputsValues,
                    (data) => {
                        if (data) {
                            $('tbody').html(data)
                            $('#imprimir').removeAttr('disabled')
                            resultadosConsulta = data

                            modalParcelas()
                            atualizaTotal()

                        } else {
                            let msg2 = $(
                                '<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty">Sem resultados...</td></tr>'
                            )
                            // console.log(msg2)
                            $('tbody').html(msg2)
                            $('#imprimir').attr('disabled', '')
                            $('#footer-total').remove()
                        }
                    }
                );
                */

                $.ajax({
                    type: "POST",
                    url: url,
                    dataType: "json",
                    data: inputsValues,
                    success: function(resposta) {
                        //|--Aqui é criado o DataTable caso seja a primeira vez q é executado e o clear é para evitar duplicação na tabela depois da primeira pesquisa
                        let table 
                        table = $('#tblMovimentacao').DataTable()
                        table = $('#tblMovimentacao').DataTable().clear().draw()
                        //--|

                        table = $('#tblMovimentacao').DataTable()

                        let rowNode

                        let contador = 0
                        let valor = 0
                        let valorTotal = 0

                        resposta.forEach(item => {
                            rowNode = table.row.add(item.data).draw().node()

                            // adiciona os atributos nas tags <td>
                            $(rowNode).find('td').eq(1).attr('style', 'text-align: center;')
                            $(rowNode).find('td').eq(4).attr('style', 'text-align: center;')
                            $(rowNode).find('td').eq(5).attr('style', 'text-align: right; color: green;')
                            $(rowNode).find('td').eq(6).attr('style', 'text-align: center;')
                            $(rowNode).find('td').eq(7).attr('style', 'text-align: center;')

                            contador++
                            valor = item.data[5].replace(".", "").replace(",", ".")
                            valorTotal += parseFloat(valor)
                        })

                        //modalParcelas()
                        pagamentoAgrupado(contador)

                        $('#legenda').remove() //Para evitar que os valores se sobreescreva
                        let legenda = document.querySelector(".datatable-header")
                        legenda.insertAdjacentHTML('beforeend', `<div id='legenda' style='text-align: right; padding-top: 2%; width: 100%;'> Mostrando 1 a ${contador} de ${contador} registros</div>`)

                        let total = 'Total: ' + float2moeda(valorTotal)

                        total = `
                        <tr id="total" role="row" class="even">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right; font-size: .8125rem; white-space: nowrap; font-weight: bold; color: green;"> ${total} </td>
                            <td></td>
                            <td></td>
                        </tr>`
                        
                        $('#total').remove()

                        $('#tblMovimentacao tfoot').append(total)
                    },
                    error: function(e) {
                        $('#legenda').remove()      
                        let legenda = document.querySelector(".datatable-header")
                        legenda.insertAdjacentHTML('beforeend', `<div id='legenda' style='text-align: right; padding-top: 2%; width: 100%;'> Mostrando 0 a 0 de 0 registros</div>`)

                        $('#total').remove() 

                        let tabelaVazia = $(
                            '<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty">Sem resultados...</td></tr>'
                        )
                        
                        $('tbody').html(tabelaVazia)
                    },
                })
            }

            $('#submitFiltro').on('click', (e) => {
                e.preventDefault()
                Filtrar()
            })

            Filtrar()
        });

        //Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
        function atualizaContasAReceber(Permission, ContasAReceberId, Tipo) {

            document.getElementById('inputContasAReceberId').value = ContasAReceberId;
            document.getElementById('inputPermissionAtualiza').value = Permission;

            if (Tipo == 'novo' || Tipo == 'edita') {
                document.formContasAReceber.action = "contasAReceberNovoLancamento.php";
            } else if (Tipo == 'exclui') {
                if(Permission){
                    confirmaExclusao(document.formContasAReceber, "Tem certeza que deseja excluir essa Conta?", "contasAReceberExclui.php");
                } else{
                    alerta('Permissão Negada!','');
                    return false;
                }
            }else if (Tipo == 'estornar') {
                return false
            }else if (Tipo.slice(0,17) == 'consultaPagamento') {
                consultaGrupoPagamento(Tipo)
                return false
            }           

            document.formContasAReceber.submit();
        }    
        
        function consultaGrupoPagamento(agrupamento) {
            tipoConta = 'R'
            agrupamento = agrupamento.split("#")
            agrupamentoId = agrupamento[1]
            let permissaoAtualiza = "<?php echo $atualizar; ?>"

            let HTML = ''
            $.ajax({
                method: "POST",
                url: "filtraContasAgrupadas.php",
                data: { 
                    tipoConta: tipoConta,
                    agrupamentoId: agrupamentoId
                },
                dataType:"json",
                success: function(response){
                    if (response.length){
                        let cabecalho = response[0]
                        HTML = HTML + `
                        <div class="d-flex flex-row pt-4">
                            <div class='col-lg-3'>
                                <div class="form-group">
                                    <label for="inputConsultaDataPagamentoPA">Data do Pagamento</label>
                                    <div class="input-group">
                                        <input type="date" id="inputConsultaDataPagamentoPA" name="inputConsultaDataPagamentoPA" value="${cabecalho.CnAReDtRecebimento}" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class='col-lg-3'>
                                <div class="form-group">
                                    <label for="inputConsultaValorTotalPA">Valor Total</label>
                                    <div class="input-group">
                                        <input type="text" id="inputConsultaValorTotalPA" name="inputConsultaValorTotalPA" value="${float2moeda(cabecalho.CnAgrValorTotal)}" class="form-control"
                                        readonly>
                                    </div>
                                </div>
                            </div>

                            <div class='col-lg-6'>
                                <div class="form-group">
                                    <label for="inputConsultaDescricaoGrupoPA">Descrição do Agrupamento</label>
                                    <div class="input-group">
                                        <input type="text" id="inputConsultaDescricaoGrupoPA" name="inputConsultaDescricaoGrupoPA" value="${cabecalho.CnAgrDescricaoAgrupamento}" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-row pt-4">
                            <div class="col-12 d-flex flex-row justify-content-center">
                                <p class="col-2 p-2 text-center" style="background-color:#f2f2f2">Item</p>
                                <p class="col-4 p-2" style="background-color:#f2f2f2">Descrição</p>
                                <p class="col-3 p-2" style="background-color:#f2f2f2">Nº Documento</p>
                                <p class="col-3 p-2 text-center" style="background-color:#f2f2f2">Valor</p>
                                </table>
                            </div>   
                        </div>
                        <div class="d-flex flex-column" style="overflow-Y: scroll; max-height: 200px">`;
                        for(let x = 0; x < response.length; x++) {
                            let consulta = response[x]

                            HTML = HTML + `
                            <div class="d-flex flex-row justify-content-center">
                                <p class="col-2 mt-3 text-center">
                                    ${x+1}
                                    <input type="hidden" id="idPA${x}"">
                                </p>
                                <div class="form-group col-4 p-2 mt-2">
                                    <a href="#" onclick="atualizaContasAReceber(${permissaoAtualiza},${consulta.CnAReId}, \'edita\')">${consulta.CnAReDescricao}</a>
                                </div>
                                <div class="form-group col-3 p-2">
                                    <input type="text" class="form-control" value="${consulta.CnAReNumDocumento}" readonly>
                                </div>
                                <div class="form-group col-3 p-2 pr-4">
                                    <input type="text" class="form-control text-right" value="${float2moeda(consulta.CnAReValorRecebido)}" readonly>
                                </div> 
                            </div>`
                        }

                        HTML = HTML + ` 
                        </div>`;
                    }
                    $('#consultaGrupoPagamentoContent').html(HTML).show();
                },
                error: function(e) { 
                    HTML = HTML + `
                        <h2>teste</h2>`;

                    $('#consultaGrupoPagamentoContent').html(HTML).show();
                }
            })
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
            document.formContasAReceber.action = "contasEstornar.php";
            document.formContasAReceber.submit();
		}

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
                            <div class="card-header header-elements-inline">
                                <h3 class="card-title">Relação de Contas à Receber</h3>
                            </div>

                            <div class="card-body">
                                <!--<p class="font-size-lg">Utilize os filtros abaixo para gerar o relatório.</p>
                                <br>-->

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

                                <form name="formMovimentacao" method="post" class="p-3">
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="inputPeriodoDe">Período de</label>
                                                <div class="input-group">
                                                    <span class="input-group-prepend">
                                                        <span class="input-group-text"><i class="icon-calendar22"></i></span>
                                                    </span>
                                                    <input type="date" id="inputPeriodoDe" name="inputPeriodoDe" class="form-control" min="1800-01-01" max="2100-12-12"
                                                    value="<?php if (isset($_SESSION['ContRecPeriodoDe'])) echo $_SESSION['ContRecPeriodoDe'];
                                                                                                                                                else echo $dataInicio; ?>">
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
                                                    <input type="date" id="inputAte" name="inputAte" class="form-control" min="1800-01-01" max="2100-12-12" value="<?php if (isset($_SESSION['ContRecAte'])) echo $_SESSION['ContRecAte'];                                                                            else echo $dataFim; ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="cmbNumDoc">Número Doc.</label>
                                                <input id="cmbNumDoc" name="cmbNumDoc" class="form-control" value="<?php if (isset($_SESSION['ContRecNumDoc'])) echo $_SESSION['ContRecNumDoc']; ?>">
                                            </div>
                                        </div>


                                        <div class="col-lg-3">
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
                                                                    if (isset($item['ClienId'])) {
                                                                        if (isset($_SESSION['ContRecCliente'])) {
                                                                            if ($item['ClienId'] == $_SESSION['ContRecCliente']) {
                                                                                print('<option value="' . $item['ClienId'] . '" selected>' . $item['ClienNome'] . '</option>');
                                                                            } else {
                                                                                print('<option value="' . $item['ClienId'] . '">' . $item['ClienNome'] . '</option>');
                                                                            }
                                                                        } else {
                                                                            print('<option value="' . $item['ClienId'] . '">' . $item['ClienNome'] . '</option>');
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

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="cmbPlanoContas">Plano de Contas</label>
                                                <select id="cmbPlanoContas" name="cmbPlanoContas" class="form-control form-control-select2">
                                                    <option value="">Todos</option>
                                                    <?php
                                                    $sql = "SELECT PlConId, PlConCodigo, PlConNome
                                                            FROM PlanoConta
                                                            JOIN Situacao on SituaId = PlConStatus
                                                            WHERE PlConUnidade = " . $_SESSION['UnidadeId'] . " and
                                                            PlConNatureza = 'R' and SituaChave = 'ATIVO'
                                                            ORDER BY PlConCodigo ASC";
                                                    $result = $conn->query($sql);
                                                    $rowPlanoContas = $result->fetchAll(PDO::FETCH_ASSOC);

                                                    foreach ($rowPlanoContas as $item) {
                                                        if (isset($_SESSION['ContRecPlanoContas'])) {
                                                            if ($item['PlConId'] == $_SESSION['ContRecPlanoContas']) {
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

                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="cmbStatus">Status</label>
                                                <select id="cmbStatus" name="cmbStatus" class="form-control form-control-select2">
                                                    <?php
                                                    try {
                                                        $sql = "SELECT SituaId, SituaNome, SituaChave
                                                                FROM Situacao
                                                                WHERE SituaStatus = 1 AND SituaChave IN ('ARECEBER', 'RECEBIDO')
                                                                ORDER BY SituaNome ASC";
                                                        $result = $conn->query($sql);
                                                        $rowSituacao = $result->fetchAll(PDO::FETCH_ASSOC);

                                                        try {
                                                            foreach ($rowSituacao as $item) {
                                                                if (isset($_SESSION['ContRecStatus'])) {
                                                                    if ($item['SituaId'] == $_SESSION['ContRecStatus']) {
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

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="cmbFormaDeRecebimento">Forma de Recebimento</label>
                                                <select id="cmbFormaDeRecebimento" name="cmbFormaDeRecebimento" class="form-control form-control-select2">
                                                    <option value="">Todos</option>
                                                    <?php
                                                        try {
                                                            $sql = "SELECT FrPagId, FrPagNome
                                                                    FROM FormaPagamento
                                                                    JOIN Situacao on SituaId = FrPagStatus
                                                                    WHERE FrPagUnidade = ".$_SESSION['UnidadeId']." and SituaChave = 'ATIVO'
                                                                    ORDER BY FrPagNome ASC";
                                                            $result = $conn->query($sql);
                                                            $rowFormaPagamento = $result->fetchAll(PDO::FETCH_ASSOC);

                                                            try {

                                                                foreach ($rowFormaPagamento as $item) {
                                                                    if (isset($item['FrPagId'])) {
                                                                        if (isset($_SESSION['ContRecFormaPagamento'])) {
                                                                            if ($item['FrPagId'] == $_SESSION['ContRecFormaPagamento']) {
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
                                                        } catch (Exception $e) {
                                                            echo 'Exceção capturada: ',  $e->getMessage(), "\n";
                                                        }   
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="text-right col-lg-1 pt-3">
                                            <div>
                                                <button id="submitFiltro" class="btn btn-principal">Pesquisar</button>
                                            </div>
                                        </div>

                                        <div class="text-right col-lg-11 pt-3">
                                            <div>
                                                <?php 
                                                    echo $inserir?"<a href='#' onclick='atualizaContasAReceber($novo, 0, `novo`);'  
                                                    class='btn btn-outline bg-slate-600 text-slate-600 border-slate'>Novo Lançamento</a>":"";

                                                    echo $atualizar?"<button id='efetuarRecebimento' class='btn btn-outline bg-slate-600 text-slate-600 border-slate' disabled>Efetuar Recebimento</button>":"";

                                                    echo "<button class='btn bg-secondary'><i class='icon-printer2'></i></button>";
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <table class="table" id="tblMovimentacao">
                                    <thead>
                                        <tr class="bg-slate">
                                            <th></th>
                                            <th id='dataGrid'>Vencimento</th>
                                            <th>Descrição</th>
                                            <th>Cliente</th>
                                            <th>Número Doc.</th>
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
                <form id="pagamentoAgrupadoForm" action=""></form>

                <!-- /info blocks -->

                <!--------------------------------------------------------------------------------------------------->
                <!--Modal Parcelar-->
                <!--
                <div id="page-modal" class="custon-modal">
                    <div class="custon-modal-container">
                        <div class="card custon-modal-content">
                            <div class="custon-modal-title">
                                <i class=""></i>
                                <p class="h3">Parcelamento</p>
                                <i class=""></i>
                            </div>
                            <form id="editarProduto" method="POST">
                                <div class="d-flex flex-row p-2">
                                    <div class='col-lg-3'>
                                        <div class="form-group">
                                            <label for="inputValor">Valor Total</label>
                                            <div class="input-group">
                                                <input type="text" id="inputValor" name="inputValor" class="form-control" readOnly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="numeroSerie">Parcelas</label>
                                        <div class="form-group">
                                            <select id="cmbParcelas" name="cmbPeriodicidade" class="form-control form-control-select2">
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
                                <div class="d-flex flex-row">
                                    <div class="col-12 d-flex flex-row justify-content-center">
                                        <p class="col-2 p-2" style="background-color:#f2f2f2">Item</p>
                                        <p class="col-4 p-2" style="background-color:#f2f2f2">Descrição</p>
                                        <p class="col-3 p-2" style="background-color:#f2f2f2">Vencimento</p>
                                        <p class="col-3 p-2" style="background-color:#f2f2f2">Valor</p>
                                        </table>
                                    </div>
                                </div>
                                <div id="parcelasContainer" class="d-flex flex-column px-5" style="overflow-Y: scroll; max-height: 300px">

                                </div>
                                <input type="hidden" id='inputDataVencimento'>
                                <input type="hidden" id='inputDescricao'>
                                <input type="hidden" id='inputId'>
                            </form>

                            <div class="card-footer mt-2 d-flex flex-column">
                                <div class="row" style="margin-top: 10px;">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <button class="btn btn-lg btn-success" id="salvar">Salvar</button>
                                            <a id="modal-close" class="btn btn-basic" role="button">Cancelar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->
                <!--------------------------------------------------------------------------------------------------->
                <!--Modal Recebimento-->
                <div id="modal-pagamentoAgrupado" class="custon-modal">
                    <div class="custon-modal-container">
                        <div class="card custon-modal-content">
                            <div class="custon-modal-title">
                                <i class=""></i>
                                <p class="h3">Efetuar Recebimentos Agrupados</p>
                                <i class=""></i>
                            </div>
                            <form class="px-5 pt-4" id="editarProduto" method="POST">
                                <div class="d-flex flex-row">
                                    <div class='col-lg-4'>
                                        <div class="form-group">
                                            <label for="inputDataPagamentoPA">Data do Pagamento</label>
                                            <div class="input-group">
                                                <input type="date" id="inputDataPagamentoPA" name="inputDataPagamentoPA"
                                                    value="<?php echo $dataInicio?>" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-lg-4'>
                                        <div class="form-group">
                                            <label for="inputValorTotalPA">Valor Total</label>
                                            <div class="input-group">
                                                <input type="text" id="inputValorTotalPA" onKeyUp="moeda(this)"
                                                    maxLength="12" name="inputValorTotalPA" class="form-control"
                                                    readOnly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-row">
                                    <div class="col-lg-6">
                                        <label for="cmbFormaPagamentoPA">Forma Pagamento</label>
                                        <div class="form-group">
                                            <select id="cmbFormaPagamentoPA" name="cmbFormaPagamentoPA"
                                                class="form-control form-control-select2">
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
                                    <div class="col-lg-6">
                                        <label for="cmbContaBancoPA">Conta/Banco</label>
                                        <div class="form-group">
                                            <select id="cmbContaBancoPA" name="cmbContaBancoPA"
                                                class="form-control form-control-select2">
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
                                </div>
                                <div class="d-flex flex-row">
                                    <div class='col-lg-12'>
                                        <div class="form-group">
                                            <label for="inputDescricaoGrupoPA">Descrição do Agrupamento <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" id="inputDescricaoGrupoPA" name="inputDescricaoGrupoPA"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-row">
                                    <div class="col-12 d-flex flex-row justify-content-center">
                                        <p class="col-2 p-2 text-center" style="background-color:#f2f2f2">Item</p>
                                        <p class="col-4 p-2" style="background-color:#f2f2f2">Descrição</p>
                                        <p class="col-3 p-2" style="background-color:#f2f2f2">Plano de Contas *</p>
                                        <p class="col-3 p-2 text-center" style="background-color:#f2f2f2">Valor</p>
                                        </table>
                                    </div>
                                </div>
                                <div id="pagamentoAgrupadoContainer" class="d-flex flex-column"
                                    style="overflow-Y: scroll; max-height: 200px">

                                </div>
                                <input type="hidden" id='inputDataVencimento'>
                                <input type="hidden" id='inputDescricao'>
                                <input type="hidden" id='inputId'>
                            </form>

                            <div class="card-footer mt-2 d-flex flex-column">
                                <div class="row" style="margin-top: 10px;">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <button class="btn btn-lg btn-success" id="salvarPA">Salvar</button>
                                            <a id="modal-closePA" class="btn btn-basic" role="button">Cancelar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--------------------------------------------------------------------------------------------------->
                <!--Modal consulta pagamento agrupado-->
                <div id="modal_consultaPagamentoAgrupado" class="custon-modal">
                    <div class="custon-modal-container">
                        <div class="card custon-modal-content">
                            <div class="custon-modal-title">
                                <i class=""></i>
                                <p class="h3">Recebimentos Agrupados</p>
                                <i class=""></i>
                            </div>
                            
                            <div class="px-5 pt-4">
                                <div id="consultaGrupoPagamentoContent">
                                            
                                </div>
                            </div>


                            <div class="card-footer mt-2 d-flex flex-column">
                                <div class="row" style="margin-top: 10px;">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <button class="btn btn-lg btn-success"  data-dismiss="modal">Ok</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--------------------------------------------------------------------------------------------------->
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

                <form name="formContasAReceber" method="post">
					<input type="hidden" id="inputPermissionAtualiza" name="inputPermissionAtualiza" value="<?php echo $atualizar; ?>" >
                    <input type="hidden" id="inputPermissionExclui" name="inputPermissionExclui" value="<?php echo $excluir; ?>" >
					<input type="hidden" id="inputContasAReceberId" name="inputContasAReceberId" >
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