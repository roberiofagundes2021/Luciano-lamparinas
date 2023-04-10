<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Movimentação do Patrimônio';

include('global_assets/php/conexao.php');

$sql = "SELECT MovimId, MovimData, MovimTipo, MovimNotaFiscal, ForneNome, SituaNome, SituaChave, LcEstNome, SetorNome
		FROM Movimentacao
		LEFT JOIN Fornecedor on ForneId = MovimFornecedor
		LEFT JOIN LocalEstoque on LcEstId = MovimOrigemLocal or LcEstId = MovimDestinoLocal
		LEFT JOIN Setor on SetorId = MovimDestinoSetor
		JOIN Situacao on SituaId = MovimSituacao
	    WHERE MovimUnidade = " . $_SESSION['UnidadeId'] . "
		ORDER BY MovimData DESC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

$d = date("d");
$m = date("m");
$Y = date("Y");

$dataInicio = date("Y-m-d", mktime(0, 0, 0, $m, $d - 30, $Y)); //30 dias atrás
$dataFim = date("Y-m-d");

if (isset($_POST['inputPatriNumero']) && $_POST['inputPatriNumero'] != "") {

	try {

		$conn->beginTransaction();

        $produto = explode('#', $_POST['cmbPatriProduto']);
        $idProduto = $produto[0];
		
		$sql = "INSERT INTO Patrimonio ( PatriNumero, PatriNumSerie, PatriEstadoConservacao, PatriProduto,
		        PatriStatus, PatriUsuarioAtualizador, PatriUnidade)
				VALUES (:sPatriNumero,:sPatriNumSerie,:sPatriEstadoConservacao,:iPatriProduto,
				:iPatriStatus,:iPatriUsuarioAtualizador,:iPatriUnidade)";

        $result = $conn->prepare($sql);
        $result->execute(array(
        ':sPatriNumero'             => $_POST['inputPatriNumero'],
        ':sPatriNumSerie'           => isset($_POST['inputPatriNumSerie']) ? $_POST['inputPatriNumSerie'] : null,
        ':sPatriEstadoConservacao'  => isset($_POST['cmbPatriEstadoConservacao']) ? $_POST['cmbPatriEstadoConservacao'] : null,
        ':iPatriProduto'            => $idProduto,
        ':iPatriStatus'             => 1,
        ':iPatriUsuarioAtualizador' => $_SESSION['UsuarId'],
        ':iPatriUnidade'            => $_SESSION['UnidadeId']
        )); 
		
		$insertIdPatrimonio = $conn->lastInsertId();

        $sql = "INSERT INTO ProdutoXFabricante (PrXFaProduto, PrXFaPatrimonio, PrXFaMarca, PrXFaModelo, PrXFaFabricante, PrXFaUnidade)
                VALUES (:iProduto, :iPatrimonio, :iMarca, :iModelo, :iFabricante, :iUnidade)";
        $result = $conn->prepare($sql);
        
        $result->execute(array(
                        ':iProduto' 			=> $idProduto,
                        ':iPatrimonio' 	        => $insertIdPatrimonio,
                        ':iMarca' 	   	        => $_POST['cmbPatriMarca'],
                        ':iModelo' 	   	        => $_POST['cmbPatriModelo'],
                        ':iFabricante' 			=> $_POST['cmbPatriFabricante'],
                        ':iUnidade' 			=> $_SESSION['UnidadeId']
                        ));

        $sql = "SELECT MotivId
                FROM Motivo
                JOIN Situacao on SituaId = MotivStatus
                WHERE SituaChave = 'ATIVO' and MotivChave = 'TRANSFERENCIA'";
        $result = $conn->query($sql);
        $rowMotivo = $result->fetch(PDO::FETCH_ASSOC);   
        
        $destino = explode('#', $_POST['cmbPatriDestino']);        
        $referencia = $destino[2];

        if ($referencia == 'Local'){
            $destinoLocal = $destino[0];
            $destinoSetor = null;
        } else {
            $destinoLocal = null;
            $destinoSetor = $destino[0];
        }

		$sql = "INSERT INTO Movimentacao ( MovimTipo, MovimMotivo, MovimData, MovimFinalidade, MovimOrigemLocal, MovimOrigemSetor, MovimDestinoLocal, MovimDestinoSetor, MovimDestinoManual, 
							MovimObservacao, MovimFornecedor, MovimOrdemCompra, MovimNotaFiscal, MovimDataEmissao, MovimNumSerie, MovimValorTotal, 
							MovimChaveAcesso, MovimSituacao, MovimUsuarioAtualizador, MovimUnidade)
				VALUES (:sTipo, :iMotivo, :dData, :iFinalidade, :iOrigemLocal, :iOrigemSetor, :iDestinoLocal, :iDestinoSetor, :sDestinoManual, 
				:sObservacao, :iFornecedor, :iOrdemCompra, :sNotaFiscal, :dDataEmissao, :sNumSerie, :fValorTotal, 
				:sChaveAcesso, :iSituacao, :iUsuarioAtualizador, :iUnidade)";

        $result = $conn->prepare($sql);

        $result->execute(array(
        ':sTipo'               => 'T',
        ':iMotivo'             => $rowMotivo['MotivId'],
        ':dData'               => date('Y-m-d'),
        ':iFinalidade'         => null,
        ':iOrigemLocal'        => $_POST['inputPatriOrigemId'],
        ':iOrigemSetor'        => null,
        ':iDestinoLocal'       => $destinoLocal,
        ':iDestinoSetor'       => $destinoSetor,
        ':sDestinoManual'      => null,
        ':sObservacao'         => null,
        ':iFornecedor'         => null,
        ':iOrdemCompra'        => null,
        ':sNotaFiscal'         => $_POST['inputPatriNotaFiscal'] == '' ? null : $_POST['inputPatriNotaFiscal'],
        ':dDataEmissao'        => $_POST['inputPatriDataCompra'] == '' ? null : $_POST['inputPatriDataCompra'],
        ':sNumSerie'           => $_POST['inputPatriNumSerie'] == '' ? null : $_POST['inputPatriNumSerie'],
        ':fValorTotal'         => $_POST['inputPatriAquisicao'] == '' ? null: gravaValor($_POST['inputPatriAquisicao']),
        ':sChaveAcesso'        => null,
        ':iSituacao'           => 1,
        ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
        ':iUnidade'            => $_SESSION['UnidadeId']
        ));
           
        $insertIdMovimentacao = $conn->lastInsertId();

        $sql = "SELECT ClassId
                FROM Classificacao
                JOIN Situacao on SituaId = ClassStatus
                WHERE SituaChave = 'ATIVO' and ClassChave = 'PERMANENTE'";
        $result = $conn->query($sql);
        $rowClassificacao = $result->fetch(PDO::FETCH_ASSOC);        

        $sql = "INSERT INTO MovimentacaoXProduto
                        (MvXPrMovimentacao, MvXPrProduto, MvXPrQuantidade, MvXPrValorUnitario, MvXPrLote, MvXPrValidade, MvXPrClassificacao, MvXPrUsuarioAtualizador, MvXPrUnidade, MvXPrPatrimonio)
                        VALUES 
                        (:iMovimentacao, :iProduto, :iQuantidade, :fValorUnitario, :sLote, :dValidade, :iClassificacao, :iUsuarioAtualizador, :iUnidade, :iPatrimonio)";
        $result = $conn->prepare($sql);
        $result->execute(array(
            ':iMovimentacao' => $insertIdMovimentacao,
            ':iProduto' => $idProduto,
            ':iQuantidade' => 1,
            ':fValorUnitario' => $_POST['inputPatriAquisicao'] == '' ? null: gravaValor($_POST['inputPatriAquisicao']),
            ':sLote' => null,
            ':dValidade' =>  null,
            ':iClassificacao' => $rowClassificacao['ClassId'],
            ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
            ':iUnidade' => $_SESSION['UnidadeId'],
            ':iPatrimonio' => $insertIdPatrimonio
        ));

		$conn->commit();

		// Fim de cadastro

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Patrimonio incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
	} catch (PDOException $e) {

		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir Patrimonio!!!";
		$_SESSION['msg']['tipo'] = "error";

		echo 'Error1X: ' . $e->getMessage();
        echo '<br>';
        echo 'Erro na Linha: '.$e->getLine();
		die;
	}

	irpara("relatorioMovimentacaoPatrimonio.php");
} 

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Lamparinas | Movimentação</title>

    <?php include_once("head.php"); ?>

    <!-- Theme JS files -->
    <script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
    <script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
    <script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
    <script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
    <script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
    <script src="global_assets/js/demo_pages/form_layouts.js"></script>
    <script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
    <script src="global_assets/js/demo_pages/form_select2.js"></script>
    
    <!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

    <script type="text/javascript">

        $(document).ready(function() {

            //Valida Registro Duplicado
            $('#salvarPatrimonio').on('click', function(e) {

                e.preventDefault();

                var inputPatriNumero = $('#inputPatriNumero').val();
                var cmbPatriProduto = $('#cmbPatriProduto').val();
                var cmbPatriDestino = $('#cmbPatriDestino').val();

                var cmbPatriMarca = $('#cmbPatriMarca').val();
                var cmbPatriModelo = $('#cmbPatriModelo').val();
                var cmbPatriFabricante = $('#cmbPatriFabricante').val();

                //remove os espaços desnecessários antes e depois
                inputPatriNumero = inputPatriNumero.trim();

                if (inputPatriNumero == ""){
                    alerta('Atenção', 'O número do patrimônio é obrigatório!', 'error');
                    $('#inputPatriNumero').focus();
                    return false;
                }
                if (cmbPatriProduto== ""){
                    alerta('Atenção', 'O produto é obrigatório!', 'error');
                    $('#inputPatriNumero').focus();
                    return false;
                }

                if (cmbPatriDestino== ""){
                    alerta('Atenção', 'O destino é obrigatório!', 'error');
                    $('#inputPatriNumero').focus();
                    return false;
                }

                if(cmbPatriMarca == "") {
                    alerta('Atenção', 'A marca é obrigatória!', 'error');
                    $('#cmbPatriMarca').focus();
                    return false;
                }
                
                if(cmbPatriModelo == "" ) {
                    alerta('Atenção', 'O modelo é obrigatório!', 'error');
                    $('#cmbPatriModelo').focus();
                    return false;
                }
                
                if(cmbPatriFabricante == "") {
                    alerta('Atenção', 'O fabricante é obrigatório!', 'error');
                    $('#cmbPatriFabricante').focus();
                    return false;
                }

                //Esse ajax está sendo usado para verificar no banco se o registro já existe
                $.ajax({
                    type: "POST",
                    url: "patrimonioValida.php",
                    data: ('numero=' + inputPatriNumero),
                    success: function(resposta) {

                        if (resposta == 1) {
                            alerta('Atenção', 'Esse registro já existe!', 'error');
                            return false;
                        }

                        $("#incluirProduto").submit();
                    }
                })
            })

            const selectEstCo = $('#selectSetadoConservacao').html()

            function limparPatrimonio() {
                $("#inputPatriNumero").val("");
                $("#cmbPatriProduto").val("");
                $("#cmbPatriDestino").val("");
                $("#inputPatriNotaFiscal").val("");
                $("#inputPatriDataCompra").val("");
                $("#inputPatriAquisicao").val("");
                $("#inputPatriDepreciacao").val("");
                $("#inputPatriMarca").val("");
                $("#inputPatriFabricante").val("");
                $("#inputPatriNumSerie").val("");
                $("#cmbPatriEstadoConservacao").val("");

                $('#pageModalPatrimonio').fadeOut(200);
                $('#select2-cmbPatriProduto-container').prop('title','Todos').html('Todos');
                $('#select2-cmbPatriDestino-container').prop('title','Todos').html('Todos');
                $('#select2-cmbPatriEstadoConservacao-container').prop('title','Todos').html('Todos');
                $('body').css('overflow', 'scroll');
                $("#patrimonioContainer").html("");            
            }
        
            function modalPatrimonio() {

                $('#btnPatrimonio').on('click', (e) => {
                    e.preventDefault()
                    $('#pageModalPatrimonio').fadeIn(200);
                });

                $('#modalClosePatrimonio').on('click', function() {
                    limparPatrimonio();
                });

                $('#modalClosePatri').on('click', function() {
                    limparPatrimonio();
                });
                
            }        

            function modalAcoes() {
                $('.btn-acoes').each((i, elem) => {
                    $(elem).on('click', function () {

                        $('#page-modal').fadeIn(200);

                        let linha = $(elem).parent().parent()

                        let id = linha.attr('idPatrimonio')
                        let editado = linha.attr('editado')

                        let tds = linha.children();

                        let produto = $(tds[1]).html();
                        let patrimonio = $(tds[2]).html();
                        let notaFisc = $(tds[3]).html();
                        let aquisicao = $(tds[4]).html();
                        let depreciacao = $(tds[5]).html();
                        let origem = $(tds[7]).html();
                        let destino = $(tds[8]).html();
                        let data = $(tds[10]).html();
                        let anoFabr = $(tds[11]).html();
                        let empenho = $(tds[12]).html();
                        let estadoConservacaoNumeroSerie = $(tds[14]).text()? $(tds[14]).text().split('#'):[];
                        let arrayProdutoXFabricante = $(tds[15]).text()? $(tds[15]).text().split('#'):[];

                        const fonte1 = 'style="font-size: 1.1rem"'
                        const fonte2 = 'style="font-size: 0.9rem"'
                        const textCenter = 'style="text-align: center"'
                        const styleLabel1 = 'style="min-width: 250px; font-size: 0.9rem"'
                        const styleLabel2 = 'style="min-width: 150px; font-size: 0.9rem"'
                        const styleLabel3 = 'style="min-width: 100px; font-size: 0.9rem"'
                        const marginP = 'style="font-size: 0.9rem; margin-top: 4px"'

                        let marca = arrayProdutoXFabricante.length?arrayProdutoXFabricante[0]:''
                        let modelo = arrayProdutoXFabricante.length?arrayProdutoXFabricante[1]:''
                        let fabricante = arrayProdutoXFabricante.length?arrayProdutoXFabricante[2]:''
                        let NumSerie = estadoConservacaoNumeroSerie.length?estadoConservacaoNumeroSerie[0]:''
                        let conservacao = estadoConservacaoNumeroSerie.length?estadoConservacaoNumeroSerie[1]:''

                        $('#cmbPatriMarcaEdita').val(marca).change()

                        $('#cmbPatriModeloEdita').val(modelo).change()

                        $('#cmbPatriFabricanteEdita').val(fabricante).change()

                        $('#numeroSerie').val(NumSerie)

                        $('#cmbEstadoConservacao').val(conservacao).change()

                        formModal = `
                                        <div class='row'style="margin-bottom:-10px;">
                                            <div class='col-lg-4'>
                                                <div class="form-group">
                                                    <label for="produto">Patrimônio</label>
                                                    <div class="input-group">
                                                        <input class='form-control' value='${patrimonio}' readOnly />
                                                    </div>
                                                </div>
                                            </div>                            
                                            <div class='col-lg-8'>
                                                <div class="form-group">
                                                    <label for="produto">Produto</label>
                                                    <div class="input-group">
                                                        <input class='form-control' value='${produto}' readOnly />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='row'style="margin-bottom:-10px;">
                                            <div class='col-lg-6'>
                                                <div class="form-group">
                                                    <label for="produto">Origem</label>
                                                    <div class="input-group">
                                                        <input class='form-control' value='${origem}' readOnly />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='col-lg-6'>
                                                <div class="form-group">
                                                    <label for="produto">Destino</label>
                                                    <div class="input-group">
                                                        <input class='form-control' value='${destino}' readOnly />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class='row'style="margin-bottom:-10px;">
                                            <div class='col-lg-3'>
                                                <div class="form-group">
                                                    <label for="produto">Nota Fiscal</label>
                                                    <div class="input-group">
                                                        <input class='form-control' value='${notaFisc}' readOnly />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='col-lg-3'>
                                                <div class="form-group">
                                                    <label for="produto">Data da Compra</label>
                                                    <div class="input-group">
                                                        <input class='form-control' value='${data}' readOnly />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='col-lg-6'>
                                                <div class="form-group">
                                                    <label for="produto">Empenhos</label>
                                                    <div class="input-group">
                                                        <input class='form-control' value='${empenho}' readOnly />
                                                    </div>
                                                </div>
                                            </div>     
                                        </div>
                                        <div class='row'style="margin-bottom:-10px;"> 
                                            <div class='col-lg-4'>
                                                <div class="form-group">
                                                    <label for="produto">Data da Fabricação</label>
                                                    <div class="input-group">
                                                        <input class='form-control' value='${anoFabr}' readOnly />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='col-lg-4'>
                                                <div class="form-group">
                                                    <label for="produto">(R$) Aquisição</label>
                                                    <div class="input-group">
                                                        <input class='form-control' value='${aquisicao}' readOnly />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='col-lg-4'>
                                                <div class="form-group">
                                                    <label for="produto">(R$) Depreciação</label>
                                                    <div class="input-group">
                                                        <input class='form-control' value='${depreciacao}' readOnly />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="text" id="inputProdutoEdita" name="inputProdutoEdita" value="${id}" style="display: none">
                        `;
                        $('.dados-produto').html(formModal)
                    })
                })
                $('#modal-close-x').on('click', function () {
                    $('#page-modal').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                })
                $('#modal-close').on('click', function () {
                    $('#page-modal').fadeOut(200);
                    $('body').css('overflow', 'scroll');
                })
            }

            modalPatrimonio()


            //Ao informar o Patrimonio, trazer os demais dados dele (marca e fabricante)
            
            $('#cmbPatriProduto').on('change', function(e){				
                
                var Produto = $('#cmbPatriProduto').val();
                var Patri = Produto.split('#');
                
                //$('#inputPatriMarca').val(Patri[1]);
                //$('#inputPatriFabricante').val(Patri[2]);		
            });

            /* Início: Tabela Personalizada */
            $('#tblMovimentacao').DataTable({
                "order": [
                    [0, "asc"]
                ],
                autoWidth: false,
                responsive: true,
                columnDefs: [{
                        
                        orderable: true, // Item
                        width: "5%",
                        targets: [0]
                    },
                    {
                        orderable: true, // Descrição do Produto
                        width: "20%",
                        targets: [1]
                    },
                    {
                        orderable: true, //Patrimônio
                        width: "10%",
                        targets: [2]
                    },
                    {
                        orderable: true, // Nota Fiscal
                        width: "10%",
                        targets: [3]
                    },
                    {
                        orderable: true, //Aquisição
                        width: "10%",
                        targets: [4]
                    },
                    {
                        orderable: true, //Depreciação
                        width: "10%",
                        targets: [5]
                    },
                    {
                        orderable: true, // Validade
                        width: "10%",
                        targets: [6]
                    },
                    {
                        orderable: true, // Origem
                        width: "10%",
                        targets: [7]
                    },
                    {
                        orderable: true, // Destino
                        width: "10%",
                        targets: [8]
                    },
                    {
                        orderable: false, //Ações
                        width: "5%",
                        targets: [9]
                    }
                ],
                dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
                language: {
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

            // Select2 for length menu styling
            var _componentSelect2 = function () {
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

            /* Fim: Tabela Personalizada */

            (function selectSubcateg() {
                const cmbCategoria = $('#cmbCategoria')

                cmbCategoria.on('change', () => {
                    Filtrando()
                    const valCategoria = $('#cmbCategoria').val()

                    $.getJSON('filtraSubCategoria.php?idCategoria=' + valCategoria, function (
                    dados) {

                        var option = '<option value="">Selecione a SubCategoria</option>';

                        if (dados.length) {

                            $.each(dados, function (i, obj) {
                                option += '<option value="' + obj.SbCatId + '">' +
                                    obj.SbCatNome + '</option>';
                            });

                            $('#cmbSubCategoria').html(option).show();
                        } else {
                            Reset();
                        }
                    });
                })
            })()

            function Filtrando() {
                $('#cmbSubCategoria').empty().append('<option>Filtrando...</option>');
            }

            function Reset() {
                $('#cmbSubCategoria').empty().append('<option value="">Sem Subcategoria</option>');
            }

            let resultadosConsulta = '';
            let inputsValues = {};

            function Filtrar() {
                let cont = false;

                const msg = $(
                    '<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty">Sem resultados...</td></tr>'
                )

                $('tbody').html(msg)

                let dataDe = $('#inputDataDe').val()
                let dataAte = $('#inputDataAte').val()
                let localEstoque = $('#cmbLocalEstoque').val()
                let setor = $('#cmbSetor').val()
                let categoria = $('#cmbCategoria').val()
                let subCategoria = $('#cmbSubCategoria').val()
                let inputProduto = $('#inputProduto').val()
                let url = "relatorioMovimentacaoPatrimonioFiltra.php";
                
                inputsValues = {
                    inputDataDe: dataDe,
                    inputDataAte: dataAte,
                    inputLocalEstoque: localEstoque,
                    inputSetor: setor,
                    inputCategoria: categoria,
                    inputSubCategoria: subCategoria,
                    inputProduto: inputProduto
                    
                };
                
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

                        resposta.forEach(item => {
                            rowNode = table.row.add(item.data).draw().node()
                            $(rowNode).attr('idPatrimonio', item.identify[0]);
                            $(rowNode).attr('editado', 0);

                            // adiciona os atributos nas tags <td>
                            $(rowNode).find('td').eq(4).attr('style', 'text-align: right;')
                            $(rowNode).find('td').eq(5).attr('style', 'text-align: right;')
                            $(rowNode).find('td').eq(9).attr('style', 'text-align: center;')
                            $(rowNode).find('td').eq(10).attr('class', 'd-none')
                            $(rowNode).find('td').eq(11).attr('class', 'd-none')
                            $(rowNode).find('td').eq(12).attr('class', 'd-none')
                            $(rowNode).find('td').eq(13).attr('class', 'd-none')
                            $(rowNode).find('td').eq(14).attr('class', 'd-none')
                            $(rowNode).find('td').eq(15).attr('class', 'd-none')
   
                        })
                        
                        modalAcoes()

                    },
                    error: function(e) {

                        let tabelaVazia = $(
                            '<tr class="odd"><td valign="top" colspan="7" class="dataTables_empty">Sem resultados...</td></tr>'
                        )

                        $('tbody').html(tabelaVazia)
                    }
                })
                
            }
                
            $('#submitFiltro').on('click', (e) => {
                e.preventDefault()
                Filtrar(false)
            })

            Filtrar(true)

            $('#salvar').on('click', function (e) {
                let numeroSerie = $('#numeroSerie').val()
                let estadoConservacao = $('#cmbEstadoConservacao').val()
                let marca = $('#cmbPatriMarcaEdita').val()
                let modelo = $('#cmbPatriModeloEdita').val()
                let fabricante = $('#cmbPatriFabricanteEdita').val()

                if (marca == ""){
                    alerta('Atenção', 'A marca do patrimônio é obrigatória!', 'error');
                    $('#cmbPatriMarcaEdita').focus();
                    return false;
                }

                if (modelo == ""){
                    alerta('Atenção', 'O modelo do patrimônio é obrigatório!', 'error');
                    $('#cmbPatriModeloEdita').focus();
                    return false;
                }

                if (fabricante == ""){
                    alerta('Atenção', 'O fabricante do patrimônio é obrigatório!', 'error');
                    $('#cmbPatriFabricanteEdita').focus();
                    return false;
                }

                let arrayId = $('#inputProdutoEdita').val().split('#')
                let id = arrayId[0]
                let produto = arrayId[1]
                let produtoXfabricante = arrayId[2] ? arrayId[2] : '' 
                let url = 'relatorioMovimentacaoPatrimonioEdita.php'
                let data = {
                    inputNumeroSerie: numeroSerie,
                    cmbEstadoConservacao: estadoConservacao,
                    cmbPatriMarca: marca,
                    cmbPatriModelo: modelo,
                    cmbPatriFabricante: fabricante,
                    inputProduto: produto,
                    inputProdutoXFabricante: produtoXfabricante,
                    inputId: id
                }

                $.post(
                    url,
                    data,
                    function (data) {
                        if (data) {
                            alerta('Atenção', 'Registro editado', 'success');

                            //let inputNumeroSerie = $(`<td style="display: none" id="inputNumeroSerie">${numeroSerie}</td>`)
                            //let inputEstadoConservacao = $(`<td style="display: none" id="inputEstadoConservacao">${estadoConservacao}</td>`)

                            $('[idpatrimonio]').each((i, elem) => {
                                let tds = $(elem).children()
                                if ($(elem).attr('idpatrimonio') == id) {
                                    $(tds[14]).children().first().val(numeroSerie)
                                    $(tds[15]).children().first().val(estadoConservacao)
                                    // $(elem).append(inputNumeroSerie).append(inputEstadoConservacao)
                                }
                            })
                        }
                    }
                )

                Filtrar(true)

                $('#page-modal').fadeOut(200);
                $('body').css('overflow', 'scroll');
            })

            function imprime() {
                url = 'relatorioMovimentacaoPatrimonioImprime.php';

                $('#imprimir').on('click', (e) => {
                    e.preventDefault()
                    if (resultadosConsulta) {

                        $('#inputResultado').val(resultadosConsulta)
                        $('#inputDataDe_imp').val(inputsValues.inputDataDe)
                        $('#inputDataAte_imp').val(inputsValues.inputDataAte)
                        $('#inputLocalEstoque_imp').val(inputsValues.inputLocalEstoque)
                        $('#inputSetor_imp').val(inputsValues.inputSetor)
                        $('#inputCategoria_imp').val(inputsValues.inputCategoria)
                        $('#inputSubCategoria_imp').val(inputsValues.inputSubCategoria)
                        $('#inputProduto_imp').val(inputsValues.inputProduto)

                        $('#formImprime').attr('action', url)

                        $('#formImprime').submit()
                    }
                })
            }
            imprime()
        });
    </script>

</head>

<body class="navbar-top sidebar-xs">

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
                                <h3 class="card-title">Movimentação do Patrimônio</h3>
                            </div>

                            <form id="formImprime" method="POST" target="_blank">
                                <input id="inputResultado" type="hidden" name="resultados"></input>
                                <input id="inputDataDe_imp" type="hidden" name="inputDataDe_imp"></input>
                                <input id="inputDataAte_imp" type="hidden" name="inputDataAte_imp"></input>
                                <input id="inputLocalEstoque_imp" type="hidden" name="inputLocalEstoque_imp"></input>
                                <input id="inputSetor_imp" type="hidden" name="inputSetor_imp"></input>
                                <input id="inputCategoria_imp" type="hidden" name="inputCategoria_imp"></input>
                                <input id="inputSubCategoria_imp" type="hidden" name="inputSubCategoria_imp"></input>
                                <input id="inputProduto_imp" type="hidden" name="inputProduto_imp"></input>
                            </form>

                            <form name="formFiltro" id="formFiltro" method="POST" class="form-validate-jquery p-3">
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="inputDataDe">Período de</label>
                                            <div class="input-group">
                                                <span class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="icon-calendar22"></i></span>
                                                </span>
                                                <input type="date" id="inputDataDe" name="inputDataDe"
                                                    class="form-control" value="<?php echo $dataInicio ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="inputDataAte">Até</label>
                                            <div class="input-group">
                                                <span class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="icon-calendar22"></i></span>
                                                </span>
                                                <input type="date" id="inputDataAte" name="inputDataAte"
                                                    class="form-control" value="<?php echo $dataFim ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="cmbLocalEstoque">Local Estoque</label>
                                            <select id="cmbLocalEstoque" name="cmbLocalEstoque"
                                                class="form-control form-control-select2">
                                                <option value="">Selecionar</option>
                                                <?php
                                                $sql = "SELECT LcEstId, LcEstNome
                                                        FROM LocalEstoque
                                                        JOIN Situacao on SituaId = LcEstStatus
                                                        WHERE LcEstUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                                        ORDER BY LcEstNome ASC";
                                                $result = $conn->query($sql);
                                                $rowLcEst = $result->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($rowLcEst as $item) {
                                                    print('<option value="' . $item['LcEstId'] . '">' . $item['LcEstNome'] . '</option>');
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="cmbSetor">Setor</label>
                                            <select id="cmbSetor" name="cmbSetor"
                                                class="form-control form-control-select2">
                                                <option value="">Selecionar</option>
                                                <?php
                                                $sql = "SELECT SetorId, SetorNome
                                                        FROM Setor
                                                        JOIN Situacao on SituaId = SetorStatus
                                                        WHERE SetorUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                                        ORDER BY SetorNome ASC";
                                                $result = $conn->query($sql);
                                                $rowSetor = $result->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($rowSetor as $item) {
                                                    print('<option value="' . $item['SetorId'] . '">' . $item['SetorNome'] . '</option>');
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="cmbCategoria">Categoria</label>
                                            <select id="cmbCategoria" name="cmbCategoria"
                                                class="form-control form-control-select2">
                                                <option value="">Selecionar</option>
                                                <?php
                                                $sql = "SELECT CategId, CategNome
                                                        FROM Categoria
                                                        JOIN Situacao on SituaId = CategStatus
                                                        WHERE CategEmpresa = ".$_SESSION['EmpreId']."  and SituaChave = 'ATIVO'
                                                        ORDER BY CategNome ASC";
                                                $result = $conn->query($sql);
                                                $rowCateg = $result->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($rowCateg as $item) {
                                                    print('<option value="' . $item['CategId'] . '">' . $item['CategNome'] . '</option>');
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="cmbSubCategoria">SubCategoria</label>
                                            <select id="cmbSubCategoria" name="cmbSubCategoria"
                                                class="form-control form-control-select2">
                                                <option value="">Selecionar</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="inputPoduto">Produto</label>
                                            <div class="input-group">
                                                <span class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="icon-calendar22"></i></span>
                                                </span>
                                                <input type="text" id="inputProduto" name="inputProduto"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div>
                                        <button id="submitFiltro" class="btn btn-principal"><i
                                                class="icon-search">Consultar</i></button>
                                        <button id="imprimir" class="btn btn-secondary btn-icon" disabled>
                                            <i class="icon-printer2"> Imprimir</i>
                                         <button id="btnPatrimonio" class="btn btn-secondary" style="margin-left: 5px;"><i
                                                class="icon-search">Atualização de Patrimônio Existente</i></button>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <table class="table" id="tblMovimentacao">
                                <thead>
                                    <tr class="bg-slate">
                                        <th>Item</th>
                                        <th>Descrição do Produto</th>
                                        <th>Patrimônio</th>
                                        <th>Nota Fiscal</th>
                                        <th>Aquisição (R$)</th>
                                        <th>Depreciação (R$)</th>
                                        <th>Validade</th>
                                        <th>Origem</th>
                                        <th>Destino</th>
                                        <th>Ações</th>
                                        <th class="d-none"></th>
                                        <th class="d-none"></th>
                                        <th class="d-none"></th>
                                        <th class="d-none"></th>
                                        <th class="d-none"></th>
                                        <th class="d-none"></th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                        <!-- /basic responsive configuration -->

                    </div>
                </div>
                <!-- /info blocks -->

                 <!--Modal Incluir-->

                 <div id="pageModalPatrimonio" class="custon-modal">
                    <div class="custon-modal-container">
                        <div class="card custon-modal-content ">
                            <div class="custon-modal-title"style="margin-bottom:10px;">
                                <i class=""></i>
                                <p class="h3">Dados Produto</p>
                                <i id="modalClosePatri" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
                            </div>
                            <form name="incluirProduto" id="incluirProduto" method="post" class="form-validate-jquery">
                                <div class="px-3 ">
                                    <div class="d-flex flex-row ">
                                        <div class='col-lg-2'>
                                            <div class="form-group">
                                                <label for="inputPatriNumero">Patrimônio <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="text" id="inputPatriNumero" name="inputPatriNumero" class="form-control" required autofocus>
                                                </div>
                                            </div> 
                                        </div> 

                                        <div class="col-lg-10">
                                            <label for="cmbPatriProduto">Produto <span class="text-danger">*</span></label>
                                            <div class="form-group">
                                                <select id="cmbPatriProduto" name="cmbPatriProduto" class="form-control form-control-select2" required>
                                                    <option value="">Selecionar</option>
                                                    <?php
                                                    $sql = "SELECT  ProduId, ProduNome, MarcaNome, FabriNome
                                                            FROM Produto
                                                            JOIN Situacao on SituaId = ProduStatus 
                                                            LEFT JOIN Marca on MarcaId = ProduMarca
                                                            LEFT JOIN Fabricante on FabriId = ProduFabricante
                                                            WHERE ProduEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
                                                            ORDER BY ProduNome ASC";
                                                    $result = $conn->query($sql);
                                                    $rowEstCo = $result->fetchAll(PDO::FETCH_ASSOC);

                                                    foreach ($rowEstCo as $item) {
                                                        print('<option value="' . $item['ProduId'] . '#' . $item['MarcaNome'] . '#' . $item['FabriNome'] . '">' . $item['ProduNome'] . '</option>');
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-3 ">
                                    <div class="d-flex flex-row ">
                                        <div class='col-lg-6'>
                                            <div class="form-group">
                                                <label for="inputPatriOrigem">Origem <span class="text-danger">*</span></label>
                                                <div class="input-group">

                                                    <?php
                                                        $sql = "SELECT LcEstId, LcEstNome
                                                                FROM LocalEstoque
                                                                JOIN Situacao on SituaId = LcEstStatus
                                                                WHERE LcEstUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO' and LcEstChave = 'GESTAOANTERIOR'
                                                               ";

                                                        $result = $conn->query($sql);
                                                        $rowOrigem = $result->fetch(PDO::FETCH_ASSOC);

                                                    ?>

                                                    <input type="hidden" id="inputPatriOrigemId" name="inputPatriOrigemId" value="<?php echo $rowOrigem['LcEstId']; ?>">
                                                    <input type="text" id="inputPatriOrigem" name="inputPatriOrigem" class="form-control" value="<?php echo $rowOrigem['LcEstNome']; ?>" readOnly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-lg-6'>
                                            <div class="form-group">
                                                <label for="cmbPatriDestino">Destino <span class="text-danger">*</span></label>
                                                <select id="cmbPatriDestino" name="cmbPatriDestino" class="form-control form-control-select2" required>
                                                    <option value="">Selecionar</option>
                                                    <?php
                                                    $sql = "SELECT LcEstId as Id, LcEstNome as Nome, 'Local' as Referencia 
                                                            FROM LocalEstoque
                                                            JOIN Situacao on SituaId = LcEstStatus
                                                            WHERE LcEstUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                                            UNION
                                                            SELECT SetorId as Id, SetorNome as Nome, 'Setor' as Referencia 
                                                            FROM Setor
                                                            JOIN Situacao on SituaId = SetorStatus
                                                            WHERE SetorUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
                                                            Order By Nome";

                                                    $result = $conn->query($sql);
                                                    $rowDestino = $result->fetchAll(PDO::FETCH_ASSOC);

                                                    foreach ($rowDestino as $item) {
                                                        print('<option value="' . $item['Id'] . '#' . $item['Nome'] . '#' . $item['Referencia'] . '">' . $item['Nome'] . '</option>');
                                                    }

                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-3 ">
                                    <div class="d-flex flex-row">
                                        <div class='col-lg-3'>
                                            <div class="form-group">
                                                    <label for="inputPatriNotaFiscal">Nota Fiscal</label>
                                                <div class="input-group">
                                                    <input type="text" id="inputPatriNotaFiscal" name="inputPatriNotaFiscal" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-lg-3'>
                                            <div class="form-group">
                                                    <label for="inputPatriDataCompra">Data da Compra</label>
                                                <div class="input-group">
                                                    <input type="date" id="inputPatriDataCompra" name="inputPatriDataCompra" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-lg-3'>
                                            <div class="form-group">
                                                    <label for="inputPatriAquisicao">(R$) Aquisição</label>
                                                <div class="input-group">
                                                    <input type="text" id="inputPatriAquisicao" name="inputPatriAquisicao" class="form-control" onKeyUp="moeda(this)" maxLength="12">
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-lg-3'>
                                            <div class="form-group">
                                                    <label for="inputPatriDepreciacao">(R$) Depreciação</label>
                                                <div class="input-group">
                                                    <input type="text" id="inputPatriDepreciacao" name="inputPatriDepreciacao" class="form-control" onKeyUp="moeda(this)" maxLength="12">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-3 ">
                                    <div class="d-flex flex-row ">
                                        <div class='col-lg-4'>
                                            <div class="form-group">
                                                <label for="cmbPatriMarca">Marca</label>
                                                <select id="cmbPatriMarca" name="cmbPatriMarca" class="form-control select-search">
                                                    <option value="">Selecione</option>'
                                                    <?php
                                                        $sql = "SELECT MarcaId, MarcaNome
                                                                FROM Marca
                                                                JOIN Situacao on SituaId = MarcaStatus
                                                                WHERE MarcaEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
                                                                ORDER BY MarcaNome ASC";
                                                        $resultMarca = $conn->query($sql);
                                                        $rowMarca = $resultMarca->fetchAll(PDO::FETCH_ASSOC);
                                                    
                                                    foreach ($rowMarca as $itemMarca){
                                                        print('<option value="'.$itemMarca['MarcaId'].'">'.$itemMarca['MarcaNome'].'</option>');
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class='col-lg-4'>
                                            <div class="form-group">
                                                <label for="cmbPatriModelo">Modelo</label>
                                                <select id="cmbPatriModelo" name="cmbPatriModelo" class="form-control select-search">
                                                    <option value="">Selecione</option>
                                                    <?php
                                                        $sql = "SELECT ModelId, ModelNome
                                                                FROM Modelo
                                                                JOIN Situacao on SituaId = ModelStatus
                                                                WHERE ModelEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
                                                                ORDER BY ModelNome ASC";
                                                        $resultMarca = $conn->query($sql);
                                                        $rowMarca = $resultMarca->fetchAll(PDO::FETCH_ASSOC);
                                                    
                                                    foreach ($rowMarca as $itemMarca){	
                                                        print('<option value="'.$itemMarca['ModelId'].'">'.$itemMarca['ModelNome'].'</option>');
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class='col-lg-4'>
                                            <div class="form-group">
                                                <label for="cmbPatriFabricante">Fabricante</label>
                                                <select id="cmbPatriFabricante" name="cmbPatriFabricante" class="form-control select-search">
                                                    <option value="">Selecione</option>
                                                    <?php
                                                    	$sql = "SELECT FabriId, FabriNome
                                                                FROM Fabricante
                                                                JOIN Situacao on SituaId = FabriStatus
                                                                WHERE FabriEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
                                                                ORDER BY FabriNome ASC";
                                                        $resultMarca = $conn->query($sql);
                                                        $rowMarca = $resultMarca->fetchAll(PDO::FETCH_ASSOC);
                                                    
                                                    foreach ($rowMarca as $itemMarca){			
                                                        print('<option value="'.$itemMarca['FabriId'].'">'.$itemMarca['FabriNome'].'</option>');
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="px-3 ">
                                    <div class="d-flex flex-row ">
                                        <div class='col-lg-6'>
                                            <div class="form-group">
                                                    <label for="inputPatriNumSerie">Nº Série/Chassi</label>
                                                <div class="input-group">
                                                    <input type="text" id="inputPatriNumSerie" name="inputPatriNumSerie" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="cmbPatriEstadoConservacao">Estado de Conservação</label>
                                                <select id="cmbPatriEstadoConservacao" name="cmbPatriEstadoConservacao" class="form-control form-control-select2">
                                                    <option value="">Selecionar</option>
                                                        <?php
                                                    $sql = "SELECT EstCoId, EstCoNome
                                                            FROM EstadoConservacao
                                                            JOIN Situacao on SituaId = EstCoStatus
                                                            WHERE SituaChave = 'ATIVO'
                                                            ORDER BY EstCoNome ASC";
                                                    $result = $conn->query($sql);
                                                    $rowEstCo = $result->fetchAll(PDO::FETCH_ASSOC);

                                                    foreach ($rowEstCo as $item) {
                                                        print('<option value="' . $item['EstCoId'] . '">' . $item['EstCoNome'] . '</option>');
                                                    }
                                                ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>    
                                <div class="card-footer mt-2 d-flex flex-column">
                                    <div class="row" style="margin-bottom:-20px;">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <button class="btn btn-lg btn-principal" id="salvarPatrimonio">Salvar</button>
                                                <a id="modalClosePatrimonio" class="btn btn-basic" role="button">Cancelar</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>                                
                            </form>                           
                        </div>
                    </div>
                </div>

                <!--Modal Editar-->
                <div id="page-modal" class="custon-modal">
                    <div class="custon-modal-container">
                        <div class="card custon-modal-content">
                            <div class="custon-modal-title" style="margin-bottom:-10px;">
                                <i class=""></i>
                                <p class="h3">Dados Produto</p>
                                <i id="modal-close-x" class="fab-icon-open icon-cross2 p-3" style="cursor: pointer"></i>
                            </div>
                            <form id="editarProduto" method="POST">
                                <div class="dados-produto p-3" style="margin-bottom:-15px;">

                                </div>
                                
                                <div class="px-2" style="margin-bottom:-20px;">
                                    <div class="d-flex flex-row ">
                                        <div class='col-lg-4'>
                                            <div class="form-group">
                                                <label for="cmbPatriMarcaEdita">Marca <span class="text-danger">*</span></label>
                                                <select id="cmbPatriMarcaEdita" name="cmbPatriMarcaEdita" class="form-control select-search">
                                                    <option value="">Selecione</option>
                                                    <?php
                                                        $sql = "SELECT MarcaId, MarcaNome
                                                                FROM Marca
                                                                JOIN Situacao on SituaId = MarcaStatus
                                                                WHERE MarcaEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
                                                                ORDER BY MarcaNome ASC";
                                                        $resultMarca = $conn->query($sql);
                                                        $rowMarca = $resultMarca->fetchAll(PDO::FETCH_ASSOC);
                                                    
                                                    foreach ($rowMarca as $itemMarca){
                                                        print('<option value="'.$itemMarca['MarcaId'].'">'.$itemMarca['MarcaNome'].'</option>');
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class='col-lg-4'>
                                            <div class="form-group">
                                                <label for="cmbPatriModeloEdita">Modelo <span class="text-danger">*</span></label>
                                                <select id="cmbPatriModeloEdita" name="cmbPatriModeloEdita" class="form-control select-search">
                                                    <option value="">Selecione</option>
                                                    <?php
                                                        $sql = "SELECT ModelId, ModelNome
                                                                FROM Modelo
                                                                JOIN Situacao on SituaId = ModelStatus
                                                                WHERE ModelEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
                                                                ORDER BY ModelNome ASC";
                                                        $resultMarca = $conn->query($sql);
                                                        $rowMarca = $resultMarca->fetchAll(PDO::FETCH_ASSOC);
                                                    
                                                    foreach ($rowMarca as $itemMarca){	
                                                        print('<option value="'.$itemMarca['ModelId'].'">'.$itemMarca['ModelNome'].'</option>');
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class='col-lg-4'>
                                            <div class="form-group">
                                                <label for="cmbPatriFabricanteEdita">Fabricante <span class="text-danger">*</span></label>
                                                <select id="cmbPatriFabricanteEdita" name="cmbPatriFabricanteEdita" class="form-control select-search">
                                                    <option value="">Selecione</option>
                                                    <?php
                                                    	$sql = "SELECT FabriId, FabriNome
                                                                FROM Fabricante
                                                                JOIN Situacao on SituaId = FabriStatus
                                                                WHERE FabriEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
                                                                ORDER BY FabriNome ASC";
                                                        $resultMarca = $conn->query($sql);
                                                        $rowMarca = $resultMarca->fetchAll(PDO::FETCH_ASSOC);
                                                    
                                                    foreach ($rowMarca as $itemMarca){			
                                                        print('<option value="'.$itemMarca['FabriId'].'">'.$itemMarca['FabriNome'].'</option>');
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-row p-2" style="margin-bottom:-20px;">
                                    <div class='col-lg-6'>
                                        <div class="form-group">
                                            <label for="numeroSerie">Nº Série/Chassi <span
                                                    class="text-danger">(Editável)</span></label>
                                            <div class="input-group">
                                                <input type="text" id="numeroSerie" name="numeroSerie"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="cmbEstadoConservacao">Estado de Conservação <span class="text-danger">(Editável)</span></label>
                                            <select id="cmbEstadoConservacao" name="cmbEstadoConservacao" class="form-control form-control-select2">
                                                <option value="">Selecionar</option>
                                                <?php
                                                $sql = "SELECT EstCoId, EstCoNome
                                                        FROM EstadoConservacao
                                                        JOIN Situacao on SituaId = EstCoStatus
                                                        WHERE SituaChave = 'ATIVO'
                                                        ORDER BY EstCoNome ASC";
                                                $result = $conn->query($sql);
                                                $rowEstCo = $result->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($rowEstCo as $item) {
                                                    print('<option value="' . $item['EstCoId'] . '">' . $item['EstCoNome'] . '</option>');
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="card-footer mt-2 d-flex flex-column">
                                <div class="row" style="margin-bottom:-20px;">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <?php 
                                                if ($atualizar) {
                                                 echo' <button class="btn btn-lg btn-principal" id="salvar">Salvar</button>';
                                                }
                                            ?>
                                            <a id="modal-close" class="btn btn-basic" role="button">Cancelar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- /content area -->

            <?php include_once("footer.php"); ?>

        </div>
        <!-- /main content -->

    </div>
    <!-- /page content -->

    <?php include_once("alerta.php"); ?>

</body>

</html>