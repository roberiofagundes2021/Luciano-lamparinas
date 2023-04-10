<?php

include_once("sessao.php");
include('global_assets/php/conexao.php');

//function queryPesquisa()
//{
//   include('global_assets/php/conexao.php');

    $cont = 0;

    $args = [];

    //Aqui é para limpar a sessão caso o usuário filtre todos novamente
    $_SESSION['ContRecPeriodoDe'] = '';
    $_SESSION['ContRecAte'] = '';
    $_SESSION['ContRecCliente'] = '';
    $_SESSION['ContRecPlanoContas'] = '';
    $_SESSION['ContRecStatus'] = '';
    $_SESSION['ContRecNumDoc'] = '';
    $_SESSION['ContRecFormaPagamento'] = '';

    if (!empty($_POST['inputPeriodoDe']) || !empty($_POST['inputAte'])) {
        empty($_POST['inputPeriodoDe']) ? $inputPeriodoDe = '1900-01-01' : $inputPeriodoDe = $_POST['inputPeriodoDe'];
        empty($_POST['inputAte']) ? $inputAte = '2100-01-01' : $inputAte = $_POST['inputAte'];

        if ($_POST['statusTipo'] == 'ARECEBER' || $_POST['statusTipo'] == 'ESTORNADO') {
            $args[]  = "CnAReDtVencimento BETWEEN '" . $inputPeriodoDe . "' and '" . $inputAte . "' ";
        } else {
            $args[]  = "CnAReDtRecebimento BETWEEN '" . $inputPeriodoDe . "' and '" . $inputAte . "' ";
        }

        if (!empty($_POST['inputPeriodoDe'])) {
            $_SESSION['ContRecPeriodoDe'] = $_POST['inputPeriodoDe'];
        }

        if (!empty($_POST['inputAte'])) {
            $_SESSION['ContRecAte'] = $_POST['inputAte'];
        }
    }

    if (!empty($_POST['cmbClientes'])) {
        $args[]  = "CnAReCliente = " . $_POST['cmbClientes'] . " ";
        $_SESSION['ContRecCliente'] = $_POST['cmbClientes'];
    }

    if (!empty($_POST['cmbPlanoContas'])) {
        $args[]  = "CnARePlanoContas = " . $_POST['cmbPlanoContas'] . " ";
        $_SESSION['ContRecPlanoContas'] = $_POST['cmbPlanoContas'];
    }

    if (!empty($_POST['cmbStatus'])) {
        if($_POST['cmbStatus'] != 'Estornado') {
            $args[]  = "CnAReStatus = " . $_POST['cmbStatus'] . " and CnAReJustificativaEstorno is null ";
        }else {
            $args[]  = "CnAReStatus = 13 and CnAReJustificativaEstorno is not null ";
        }
        $_SESSION['ContRecStatus'] = $_POST['cmbStatus'];
    }

    if (!empty($_POST['cmbNumDoc'])) {
        $args[]  = "CnAReNumDocumento = " . $_POST['cmbNumDoc'] . " ";
        $_SESSION['ContRecNumDoc'] = $_POST['cmbNumDoc'];
    }

    if (!empty($_POST['cmbFormaDeRecebimento'])) {
        $args[]  = "CnAReFormaPagamento = " . $_POST['cmbFormaDeRecebimento'] . " ";
        $_SESSION['ContRecFormaPagamento'] = $_POST['cmbFormaDeRecebimento'];
    }

    if (count($args) >= 1) {

        $string = implode(" and ", $args);

        if ($string != '') {
            $string .= ' and ';
        }

        $sql = "SELECT * 
                FROM ContasAReceber
                LEFT JOIN Cliente on ClienId = CnAReCliente
                JOIN Situacao on SituaId = CnAReStatus
                WHERE " . $string . " CnAReAgrupamento is null and CnAReUnidade = " . $_SESSION['UnidadeId'] . "
            ";
        $result = $conn->query($sql);
        $rowData = $result->fetchAll(PDO::FETCH_ASSOC);

        count($rowData) >= 1 ? $ExisteConsulta = 1 : $ExisteConsulta = 0;

        $sql = "SELECT CnAgrDescricaoAgrupamento, SituaNome, SUM(CnAReValorRecebido) as VALORAGRUPAMENTO, CnAReAgrupamento, CnAgrDtPagamento
                FROM ContasAReceber
                JOIN ContasAgrupadas on CnAgrId = CnAReAgrupamento
                JOIN Situacao on SituaId = CnAReStatus
                WHERE " . $string . " CnAReUnidade = " . $_SESSION['UnidadeId'] . "
                GROUP BY CnAReAgrupamento, SituaNome, CnAgrDtPagamento, CnAgrDescricaoAgrupamento";
        $resultAgrupamento = $conn->query($sql);
        $rowAgrupamento = $resultAgrupamento->fetchAll(PDO::FETCH_ASSOC);
    
        count($rowAgrupamento) >= 1 ? $ExisteAgrupamento = 1 : $ExisteAgrupamento = 0;
    } 

    if ($ExisteConsulta == 1) {
        $cont = 0;
        //print('<input type="hidden" id="elementosGrid" value="' . count($rowData) . '">');

        $arrayData = [];
        foreach ($rowData as $item) {
            $cont++;
            $status = $item['CnAReStatus'] == 13 ? 'À Receber' : 'Recebido';
            $data = $_POST['statusTipo'] == 'ARECEBER' || $_POST['statusTipo'] == 'ESTORNADO' ? mostraData($item['CnAReDtVencimento']) : mostraData($item['CnAReDtRecebimento']);
            
            $visibilidade = ($status == 'Recebido') ? 'none' : 'block';
            
            $estornamento =  (!isset($item['CnAReJustificativaEstorno']) || $status == 'Recebido') ? 'none' : 'block';
            $justificativaEstornamento = (isset($item['CnAReJustificativaEstorno'])) ? $item['CnAReJustificativaEstorno'] : '';
            $consultaAgrupamento = (isset($item['CnAReAgrupamento'])) ? 'consultaPagamento#'.$item['CnAReAgrupamento'] : '';

            $checkbox = '<input type="checkbox" id="check'.$cont.'" style="display: '.$visibilidade.';"> <input type="hidden" value="'.$item["CnAReId"].'">';
            
            $vencimento = '<p class="m-0">' . $data . '</p><input type="hidden" value="'.$data.'">';

            $descricao = '<a href=#" onclick="atualizaContasAReceber('.$_POST['permissionAtualiza'].','.$item["CnAReId"].', \'edita\')">' . $item["CnAReDescricao"] . '</a>';
            
            $cliente = $item["ClienNome"];

            $numDoc = $item["CnAReNumDocumento"];

            $valorTotal = mostraValor($item["CnAReValorAReceber"]);

            $status = $status;

            if( $item["CnAReAgrupamento"] == '') {
                $acaoConta = ($status == 'Recebido') ? '<a href="#" data-toggle="modal" data-target="#modal_mini-estornar" onclick="atualizaContasAReceber('.$_POST['permissionAtualiza'].','.$item["CnAReId"].', \'estornar\');"  class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Estornar Conta"><i class="icon-undo2"></i></a>' : 
                                                       '<a href="#" onclick="atualizaContasAReceber('.$_POST['permissionExclui'].','.$item["CnAReId"].', \'exclui\');"  class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Excluir Conta"><i class="icon-bin"></i></a>';
            }else {
                $acaoConta = ($status == 'Recebido') ? '<a href="#" data-toggle="modal" data-target="#modal_mini-estornar" onclick="atualizaContasAReceber('.$_POST['permissionAtualiza'].','.$item["CnAReId"].', \'estornar\');"  class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Estornar Conta"><i class="icon-undo2"></i></a>' : 
                                                       '<a href="#" onclick="atualizaContasAReceber('.$_POST['permissionExclui'].','.$item["CnAReId"].', \'exclui\');"  class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Excluir Conta"><i class="icon-bin"></i></a>';
            }

            $acoes = '
                    <div class="list-icons">
                        <div class="list-icons list-icons-extended">'.
                            ($_POST['permissionAtualiza']?'<a href="#" onclick="atualizaContasAReceber('.$_POST['permissionAtualiza'].','.$item["CnAReId"].', \'edita\');" class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Editar Conta"><i class="icon-pencil7"></i></a>':'')
                            .($_POST['permissionExclui']?$acaoConta:'').
                            '<a href="#" class="list-icons-item" data-toggle="modal" data-target="#modal_mini-justificativa-estorno" onclick="estornoJustificativa(\''.$justificativaEstornamento.'\');"  data-popup="tooltip" data-placement="bottom"title="Motivo do estorno" style="display: '.$estornamento.';"><i class="icon-info3"></i></a>
                            <!-- Retirado ícone de parcelar
                            <div class="dropdown" style="display: '.$visibilidade.';">													
                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                    <i class="icon-menu9"></i>
                        
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="#" class="dropdown-item btnParcelar"  data-popup="tooltip" data-placement="bottom" title="Parcelar"><i class="icon-file-text2"></i> Parcelar</a>
                                </div>
                            </div>-->
                        </div>
                    </div>';

            $array = [
                'data'=>[
                    isset($checkbox) ? $checkbox : null, 
                    isset($vencimento) ? $vencimento : null,
                    isset($descricao) ? $descricao : null, 
                    isset($cliente) ? $cliente : null, 
                    isset($numDoc) ? $numDoc : null, 
                    isset($valorTotal) ? $valorTotal : null, 
                    isset($status) ? $status : null, 
                    isset($acoes) ? $acoes : null
                ],
                'identify'=>[
                    
                ]
            ];

            array_push($arrayData,$array);
        }
    }

    if($ExisteAgrupamento == 1) {
        foreach ($rowAgrupamento as $item) {
            $status = $item['SituaNome'];
            $data = mostraData($item['CnAgrDtPagamento']);
    
            $justificativaEstornamento = '';
            $consultaAgrupamento = (isset($item['CnAReAgrupamento'])) ? 'consultaPagamento#'.$item['CnAReAgrupamento'] : '';
    
            $checkbox = '';
            
            $vencimento = '<p class="m-0">' . $data . '</p><input type="hidden" value="'.$data.'">';
    
            $descricao = '<a href="#" data-toggle="modal" data-target="#modal_consultaPagamentoAgrupado" onclick="atualizaContasAReceber('.$_POST['permissionAtualiza'].', 0, \''.$consultaAgrupamento.'\');" data-popup="tooltip" data-placement="bottom">' . $item["CnAgrDescricaoAgrupamento"] . '</a>';
            
            $favorecido = '';
    
            $numDoc = '';
    
            $valorTotal = mostraValor($item["VALORAGRUPAMENTO"]);
    
            $status = $status;
    
            $acoes = '
                <div class="list-icons">
                    <div class="list-icons list-icons-extended">
                        <a href="#" data-toggle="modal" data-target="#modal_consultaPagamentoAgrupado" onclick="atualizaContasAReceber('.$_POST['permissionAtualiza'].', 0, \''.$consultaAgrupamento.'\');" class="list-icons-item"  data-popup="tooltip" data-placement="bottom" title="Pagamento agrupado"><i class="icon-stack3"></i></a>
                    </div>
                </div>';
    
            $array = [
                'data'=>[
                    isset($checkbox) ? $checkbox : null, 
                    isset($vencimento) ? $vencimento : null,
                    isset($descricao) ? $descricao : null, 
                    isset($favorecido) ? $favorecido : null, 
                    isset($numDoc) ? $numDoc : null, 
                    isset($valorTotal) ? $valorTotal : null, 
                    isset($status) ? $status : null, 
                    isset($acoes) ? $acoes : null
                ],
                'identify'=>[
                    
                ]
            ];
    
            array_push($arrayData,$array);
        }
    }

    if ($ExisteConsulta == 1 || $ExisteAgrupamento == 1) {
        print(json_encode($arrayData));
    }
//}

//queryPesquisa();