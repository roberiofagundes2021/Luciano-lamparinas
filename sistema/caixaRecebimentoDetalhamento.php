<?php

include_once("sessao.php");

//Fazer alteração posteriormente
$_SESSION['PaginaAtual'] = 'Caixa - Recebimento Detalhamento';

include('global_assets/php/conexao.php');

if(!isset($_POST['inputAtendimento'])) {
    irpara("caixaMovimentacao.php");
}

$atendimentoId =  $_POST['inputAtendimento'];
$recebimentoId =  $_POST['inputReciboId'];

$sql_rrecebimentoServicos= "SELECT AtendNumRegistro, AtendDataRegistro, ClienNome, SrVenNome, ProfiNome, SVXMoServicoVenda, AtXSeData, 
                                   AtXSeHorario, AtLocNome, AtXSeValor, AtXSeDesconto, AtModNome, CxRecFormaPagamento, FrPagNome
                            FROM AtendimentoXServico
                            JOIN Atendimento on AtendId = AtXSeAtendimento
                            JOIN Cliente on ClienId = AtendCliente
                            JOIN AtendimentoLocal on AtLocId = AtXSeAtendimentoLocal
                            JOIN ServicoVenda ON SrVenId = AtXSeServico
                            LEFT JOIN ServicoVendaXModalidade ON SrVenId = SVXMoServicoVenda
                            JOIN Profissional ON ProfiId = AtXSeProfissional
                            JOIN CaixaRecebimento ON CxRecAtendimento = AtendId
                            JOIN FormaPagamento ON FrPagId = CxRecFormaPagamento
                            JOIN AtendimentoModalidade ON AtModId = AtendModalidade
                            WHERE AtXSeAtendimento = $atendimentoId AND AtXSeUnidade = $_SESSION[UnidadeId]";
$resultServicos  = $conn->query($sql_rrecebimentoServicos);

$numeroRegistro = '';
$dataAtendimento = '';
$cliente = '';
$modalidade = '';
$formaPagamento = '';
$servicos = false;
if ($rowServicos = $resultServicos->fetchAll(PDO::FETCH_ASSOC)) {
    $numeroRegistro = $rowServicos[0]['AtendNumRegistro'];
    $dataAtendimento = $rowServicos[0]['AtendDataRegistro'];
    $cliente = $rowServicos[0]['ClienNome'];
    $modalidade = $rowServicos[0]['AtModNome'];
    $formaPagamento = $rowServicos[0]['FrPagNome'];
    $servicos = true;
}

$produtos = false;
$sql_rrecebimentoProdutos= "SELECT ProduNome, ProduDetalhamento, ProduValorVenda, AtXPrValor, AtXPrDesconto
                            FROM AtendimentoXProduto
                            JOIN Produto ON ProduId = AtXPrProduto
                            WHERE AtXPrAtendimento = $atendimentoId AND AtXPrUnidade = $_SESSION[UnidadeId]";
$resultProdutos  = $conn->query($sql_rrecebimentoProdutos);

//A sessão de resumo financeiro é a opção de visibilidade do resumo financeiro, aqui ele também foi aplicado ao resumo de Caixa
$visibilidadeResumoCaixa = isset($_SESSION['ResumoFinanceiro']) && $_SESSION['ResumoFinanceiro'] ? 'sidebar-right-visible' : ''; 
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Lamparinas |Caixa - Recebimento detalhamento</title>

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
            //Para mostrar os botões ocultos do caixa
            $(".caixaEmOperacao").show();
        });
    </script>

</head>

<body class="navbar-top <?php echo $visibilidadeResumoCaixa; ?> sidebar-xs">

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
                                <h3 class="card-title">Dados do Recebimento</h3>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="inputNumeroRegistro">Nº de Registro</label>
                                            <input type="text" id="inputNumeroRegistro" class="form-control" name="inputNumeroRegistro" rows="3" value="<?php echo $numeroRegistro; ?>" readonly>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="inputDataRegistro">Data de Registro</label>
                                            <input type="date" id="inputDataRegistro" value="<?php echo $dataAtendimento; ?>" name="inputDataRegistro" class="form-control removeValidacao" readonly>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="inputOaciente">Paciente</label>
                                            <input type="text" id="inputOaciente" name="inputOaciente" value="<?php echo $cliente; ?>" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>  
                                
                                <h3>Histórico</h3>

                                <?php 

                                $valorFinal = 0;
                                $desconto = 0;
                                
                                if ($servicos) {
                                    echo '
                                    <div class="row">
                                        <div class="col-2">
                                            <h6><b>Serviço</b></h6>
                                        </div>
    
                                        <div class="col-3">
                                            <h6><b>Médico</b></h6>
                                        </div>
    
                                        <div class="col-2">
                                            <h6><b>Data de Atendimento</b></h6>
                                        </div>
    
                                        <div class="col-1">
                                            <h6><b>Horário</b></h6>
                                        </div>
    
                                        <div class="col-2">
                                            <h6><b>Local de Atendimento</b></h6>
                                        </div>
    
                                        <div class="col-2 text-right">
                                            <h6><b>Valor</b></h6>
                                        </div>
                                    </div>
                                    ';

                                    $valorTotalServicos = 0;
    
                                    foreach ($rowServicos as $item) {
                                        echo '
                                        <div class="row">
                                            <div class="col-2">
                                                <h6>'.$item['SrVenNome'].'</h6>
                                            </div>
        
                                            <div class="col-3">
                                                <h6>'.$item['ProfiNome'].'</h6>
                                            </div>
        
                                            <div class="col-2">
                                                <h6>'.mostraData($item['AtXSeData']).'</h6>
                                            </div>
        
                                            <div class="col-1">
                                                <h6>'.$item['AtXSeHorario'].'</h6>
                                            </div>
        
                                            <div class="col-2">
                                                <h6>'.$item['AtLocNome'].'</h6>
                                            </div>
        
                                            <div class="col-2 text-right">
                                                <h6>'.mostraValor($item['AtXSeValor']).'</h6>
                                            </div>
                                        </div>';

                                        $valorTotalServicos += $item['AtXSeValor'];
                                        $desconto += $item['AtXSeDesconto'];
                                    }
                                    
                                    $valorFinal += $valorTotalServicos - $desconto;
                                    
                                    echo '
                                    <div class="row mb-3">
                                        <div class="col-lg-12 text-right">
                                            <h4><b>Valor: R$ '.mostraValor($valorTotalServicos).'</b></h4>
                                        </div>
                                    </div>';
                                }

                                if ($rowProdutos = $resultProdutos->fetchAll(PDO::FETCH_ASSOC)) {
                                    echo '
                                    <div class="row">
                                        <div class="col-2">
                                            <h6><b>Produto</b></h6>
                                        </div>
    
                                        <div class="col-8">
                                            <h6><b>Detalhamento</b></h6>
                                        </div>
    
                                        <div class="col-2 text-right">
                                            <h6><b>Valor</b></h6>
                                        </div>
                                    </div>
                                    ';
                                    $valorTotalProdutos = 0;
    
                                    foreach ($rowProdutos as $item) {
                                        echo '
                                        <div class="row">
                                            <div class="col-2">
                                                <h6>'.$item['ProduNome'].'</h6>
                                            </div>
        
                                            <div class="col-8">
                                                <h6>'.$item['ProduDetalhamento'].'</h6>
                                            </div>
        
                                            <div class="col-2 text-right">
                                                <h6>'.mostraValor($item['AtXPrValor']).'</h6>
                                            </div>
                                        </div>';

                                        $valorTotalProdutos += $item['AtXPrValor'];
                                        $desconto += $item['AtXPrDesconto'];
                                    }

                                    $valorFinal += $valorTotalProdutos - $desconto;
                                    
                                    echo '
                                    <div class="row mb-3">
                                        <div class="col-lg-12 text-right">
                                            <h4><b>Valor: R$ '.mostraValor($valorTotalProdutos).'</b></h4>
                                        </div>
                                    </div>';
                                }
                                ?>
                                
                                <div class="row">
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="inputDescricao">Modalidade</label>
                                            <input type="text" id="inputDescricao" class="form-control" name="inputDescricao" rows="3" value="<?php echo $modalidade; ?>" readonly>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="inputFormaPagamento">Forma de Pagamento</label>
                                            <input type="text" id="inputFormaPagamento" name="inputFormaPagamento" value="<?php echo $formaPagamento; ?>" class="form-control removeValidacao" readonly>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 text-right pt-4">
                                        <h4><b>Desconto: R$ <?php echo mostraValor($desconto); ?></b></h4>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12 text-right">
                                        <h4><b>Total Pago: R$ <?php echo mostraValor($valorFinal); ?></b></h4>
                                    </div>
                                </div>

                                <h6>Situação: Recebido</h6>

                                <div class="row">
                                    <div class="col-lg-12 pt-3">
                                        <div>
                                            <a href='caixaMovimentacao.php' class='btn btn-outline bg-slate-600 text-slate-600 border-slate'>Movimentação</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /basic responsive configuration -->

                    </div>
                </div>
            </div>
            <!-- /content area -->

            <?php include_once("footer.php"); ?>

        </div>
        <!-- /main content -->

        <?php include_once("sidebar-right-resumo-caixa.php"); ?>

    </div>
    <!-- /page content -->

    <?php include_once("alerta.php"); ?>

</body>

</html>