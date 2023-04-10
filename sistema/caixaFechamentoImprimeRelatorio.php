<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';
require_once 'global_assets/php/funcoesgerais.php';

//$_SESSION['EmpreId']
if(!isset($_POST['idCaixaAbertura'])) { 
    irpara("caixaMovimentacao.php");
}

$aberturaCaixaId = $_POST['idCaixaAbertura'];

$operador = nomeSobrenome($_SESSION['UsuarNome'], 2);

//Consulta os principais dados do Fechamento do Caixa
$sql_caixaAbertura =   "SELECT CxAbeDataHoraFechamento, CxAbeSaldoInicial
                        FROM CaixaAbertura
                        WHERE CxAbeId = $aberturaCaixaId and CxAbeUnidade = $_SESSION[UnidadeId]";
$resultCaixaAbertura  = $conn->query($sql_caixaAbertura);
$rowCaixaAbertura = $resultCaixaAbertura->fetch(PDO::FETCH_ASSOC);

$sql_relatorio_diario    = "SELECT FrPagNome, sum(CxRecValorTotal) as TOTAL, 'Recebimento' as TIPO, CxAbeValorTransferido
                        FROM CaixaRecebimento
                        JOIN CaixaAbertura on CxAbeId = CxRecCaixaAbertura
                        JOIN FormaPagamento on FrPagId = CxRecFormaPagamento
                        JOIN Atendimento on AtendId = CxRecAtendimento
                        WHERE CxAbeId = $aberturaCaixaId and CxRecUnidade = $_SESSION[UnidadeId]
                        GROUP BY FrPagNome, CxAbeValorTransferido
                        UNION 
                        SELECT FrPagNome, sum((CxPagValor * -1)) as TOTAL, 'Pagamento' as TIPO, CxAbeValorTransferido                     
                        FROM CaixaPagamento
                        JOIN CaixaAbertura on CxAbeId = CxPagCaixaAbertura
                        JOIN FormaPagamento on FrPagId = CxPagFormaPagamento
                        WHERE CxAbeId = $aberturaCaixaId and CxPagUnidade = $_SESSION[UnidadeId]
                        GROUP BY FrPagNome, CxAbeValorTransferido";
$resultRelatorioDiario  = $conn->query($sql_relatorio_diario);
$rowRelatorioDiario = $resultRelatorioDiario->fetchAll(PDO::FETCH_ASSOC);

//Consulta atendimentos olhando os profissionais
$sql_atendimento    = "SELECT sum(AtXSeValor) as grupoValor, sum(AtXSeDesconto) as grupoDesconto, ProfiNome, COUNT(ProfiNome) as Quantidade
                                FROM CaixaRecebimento
                                JOIN CaixaAbertura on CxAbeId = CxRecCaixaAbertura
                                JOIN Atendimento on AtendId = CxRecAtendimento
                                JOIN AtendimentoXServico on AtendId = AtXSeAtendimento
                                JOIN Profissional ON ProfiId = AtXSeProfissional
                                WHERE CxAbeId = $aberturaCaixaId and CxRecUnidade = $_SESSION[UnidadeId]
                                GROUP BY ProfiNome";
$resultAtendimento  = $conn->query($sql_atendimento);
$rowAtendimento = $resultAtendimento->fetchAll(PDO::FETCH_ASSOC);

//Consulta atendimentos do recebimento
$sql_servicos    = "SELECT sum(AtXSeValor) as grupoValor, sum(AtXSeDesconto) as grupoDesconto, SrVenNome, COUNT(SrVenNome) as Quantidade
                        FROM CaixaRecebimento
                        JOIN CaixaAbertura on CxAbeId = CxRecCaixaAbertura
                        JOIN Atendimento on AtendId = CxRecAtendimento
                        JOIN AtendimentoXServico on AtendId = AtXSeAtendimento
                        JOIN ServicoVenda on SrVenId = AtXSeServico
                        WHERE CxAbeId = $aberturaCaixaId and CxRecUnidade = $_SESSION[UnidadeId]
                        GROUP BY SrVenNome";
$resultServicos  = $conn->query($sql_servicos);
$rowServicos = $resultServicos->fetchAll(PDO::FETCH_ASSOC);

//Consulta atendimentos do recebimento
$sql_movimentacao    = "SELECT ClienNome as HISTORICO, sum(AtXSeValor) as grupoValor, sum(AtXSeDesconto) as grupoDesconto, 
                        ProfiNome, COUNT(ProfiNome) as Quantidade, AtendObservacao, CxAbeDataHoraFechamento, CxAbeSaldoInicial
                        FROM CaixaRecebimento
                        JOIN CaixaAbertura on CxAbeId = CxRecCaixaAbertura
                        JOIN Atendimento on AtendId = CxRecAtendimento
                        JOIN Cliente on ClienId = AtendCliente
                        JOIN AtendimentoXServico on AtendId = AtXSeAtendimento
                        JOIN AtendimentoLocal on AtLocId = AtXSeAtendimentoLocal
                        JOIN Profissional ON ProfiId = AtXSeProfissional
                        WHERE CxAbeId = $aberturaCaixaId and CxRecUnidade = $_SESSION[UnidadeId]
                        GROUP BY ClienNome, ProfiNome, AtendObservacao, CxAbeDataHoraFechamento, CxAbeSaldoInicial";
$resultMovimentacao  = $conn->query($sql_movimentacao);
$rowMovimentacao = $resultMovimentacao->fetchAll(PDO::FETCH_ASSOC);

$sqlEmpresa = "SELECT EmpreCnpj, EmpreEndereco, EmpreNumero, EmpreBairro, EmpreCidade, EmpreEstado, EmpreCep
                FROM Empresa
				WHERE EmpreId = ".$_SESSION['EmpreId'];
			
$resultEmpresa = $conn->query($sqlEmpresa);
$empresa = $resultEmpresa->fetch(PDO::FETCH_ASSOC);

$empresaCnpj = formatarCPF_Cnpj($empresa['EmpreCnpj']);
$empresaEnderecoRua = $empresa['EmpreEndereco'];
$empresaEnderecoNumero = $empresa['EmpreNumero'];
$empresaBairro = $empresa['EmpreBairro'];
$empresaCidade = $empresa['EmpreCidade'];
$empresaEstado = $empresa['EmpreEstado'];
$empresaCep = $empresa['EmpreCep'];
$endereco = $empresaEnderecoRua.' '.$empresaEnderecoNumero.', '.$empresaBairro.', '.$empresaCidade.' - '.$empresaEstado.' CEP: '.mostraCEP($empresaCep);

try {
	$mpdf = new mPDF([
	             'mode' => 'utf-8',    // mode - default ''
	             'format' => 'A4-P',    // format - A4, for example, default ''
	             'default_font_size' => 9,     // font size - default 0
	             'default_font' => '',    // default font family
	             'margin-left' => 15,    // margin_left
	             'margin-right' => 15,    // margin right
	             'margin-top' => 158,     // margin top    -- aumentei aqui para que não ficasse em cima do header
	             'margin-bottom' => 60,    // margin bottom
	             'margin-header' => 6,     // margin header
	             'margin-bottom' => 0,     // margin footer
	             'orientation' => 'P']);  // L - landscape, P - portrait	

    $html = "
    <style>
        .tabelaPrincipal th{
            text-align: center; 
            border: #bbb solid 1px; 
            background-color: #f8f8f8; 
            padding: 8px;
        }

        .tabelaPrincipal td{
            padding: 8px;				
            border: #bbb solid 1px;
        }
    </style>

    <div style='position: relative; width:100%; border-bottom: 1px solid #666; padding-bottom: 10px; margin-bottom: 10px;'>
        <div style='float:left; width: 200px; display: inline-block; padding: 10px;'>
            <img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:190px; height:45px; float:left; margin-right: 10px; margin-top:-10px;' alt='Logo Empresa' />
        </div>
        
        <div style='width:400px; float:right; display: inline; text-align:right;'>
            <div style='font-weight:bold; padding: 2px; font-size: 15px;'>$_SESSION[EmpreNomeFantasia]</div>
            <div style='padding: 2px; font-size: 10px;'>UNIDADE: $_SESSION[UnidadeNome]</div>
            <div style='padding: 2px; font-size: 10px;'>CNPJ: $empresaCnpj</div>
        </div> 
    </div>";

    //A Tabela com a classe apoio é usada apenas para alinhar tabelas uma ao lado da outra, pois esta função que gera o PDF é limitada com relação a estilização 
    //<--Inicio tabela de alinhamento
    $html .=  "
        <br>
        <table class='table table-hover tabelaApoio' style='border-collapse: collapse;'>
            <tbody>
                <tr>
                    <!--Esse estilo é utilizado paara as tabelas alinhadas ficarem no top da tag td da tabela alinhadora-->
                    <td style='padding-right: 2%; vertical-align:middle !important'>";
    
    $arrayDatafechamento = explode(' ', $rowCaixaAbertura['CxAbeDataHoraFechamento']);
    $datafechamento = $arrayDatafechamento[0];
    $saldoInicial = $rowCaixaAbertura['CxAbeSaldoInicial'];
    //Tabela que está alinha a esquerda (Neste caso - RELATÓRIO DIÁRIO DO CAIXA)
    $html .=  "
                        <table class='table table-hover tabelaPrincipal' style='width:700px; border-collapse: collapse;'>
                            <tr>
                                <th colspan='3' rowspan='2' style='text-align: center;'>RELATÓRIO DIÁRIO DE CAIXA</th>
                                <th style='text-align: left;'>Data</th>
                                <th style='text-align: right;'>".mostraData($datafechamento)."</th>
                            </tr>
                            <tr>
                                <th style='text-align: left;'>Saldo Inicial</th>
                                <th style='text-align: right;'>".mostraValor($saldoInicial)."</th>
                            </tr>
                            <thead>
                                <tr>
                                    <th style='text-align: left; width: 16%'></th>
                                    <th style='text-align: left; width: 21%'>PROCEDIMENTO</th>
                                    <th style='text-align: left; width: 18%'>ENTRADAS</th>
                                    <th style='text-align: left; width: 24%'>SAÍDAS</th>
                                    <th style='text-align: left; width: 21%'>SALDO</th>
                                </tr>
                            </thead>
                            <tbody>";

    $total = $saldoInicial;
    $recebido = 0;
    $pago = 0;
    foreach($rowRelatorioDiario as $item) {
        $total += $item['TOTAL'];
        $tipo = $item['TIPO'];
        $html .= "
                                <tr>
                                    <td>".$item['FrPagNome']."</td>
                                    <td style='text-align: left;'>Serviço</td>";
        if($tipo == 'Recebimento') {
            $recebido += $item['TOTAL'];

            $html .= "
                                    <td style='text-align: right;'>".mostraValor($item['TOTAL'])."</td>
                                    <td></td>";
        }else {
            $pago += $item['TOTAL'];

            $html .= "
                                    <td></td>                            
                                    <td style='text-align: right;'>".mostraValor($item['TOTAL'])."</td>";
        }

        $html .= "
                                    <td style='text-align: right;'>".mostraValor($total)."</td>
                                </tr>";
    }
    
    $valorTransferido = $item['CxAbeValorTransferido'];
    $saldoFinal = $total - $valorTransferido;

    $html .=  "
                                <tr>
                                    <th colspan='2'></th>
                                    <th style='text-align: right;'>".mostraValor($recebido)."</th>
                                    <th style='text-align: right;'>".mostraValor($pago)."</th>
                                    <th style='text-align: right;'>".mostraValor($total)."</th>
                                </tr>
                            </tbody>
                            <tr>
                                <th colspan='3' rowspan='2' style='text-align: left; padding-top: -20px;'>Observações</th>
                                <th style='text-align: left;'>Transferência</th>
                                <th style='text-align: right;'>".mostraValor($valorTransferido)."</th>
                            </tr>
                            <tr>
                                <th style='text-align: left;'>Saldo Final</th>
                                <th style='text-align: right;'>".mostraValor($saldoFinal)."</th>
                            </tr>
                        </table>";

    $html .="       </td>
                    <td style='padding-left: 2%; vertical-align:middle !important'>";

    //Tabela que está alinha a direita (Neste caso - RESUMO DIÁRIO DE PROCEDIMENTOS)
    $html .=  "
                        <table class='table table-hover tabelaPrincipal' style='width:400px; border-collapse: collapse;'>
                            <tr>
                                <th colspan='3' style='text-align: center;'>RESUMO DIÁRIO DE PROCEDIMENTOS</th>
                            </tr>
                            <thead>
                                <tr>
                                    <th style='text-align: left; width: 41%'>SERVIÇO</th>
                                    <th style='text-align: left; width: 18%'>QTD</th>
                                    <th style='text-align: left; width: 41%'>VALOR</th>
                                </tr>
                            </thead>
                            <tbody>";

    $total = 0;
    $totalQuantidade = 0;
    foreach($rowServicos as $item) {
        $valor = $item['grupoValor'] - $item['grupoDesconto'];
        $total += $valor;
        $totalQuantidade += $item['Quantidade'];

        $html .= "
                                <tr>
                                    <td>".$item['SrVenNome']."</td>
                                    <td style='text-align: right;'>".$item['Quantidade']."</td>
                                    <td style='text-align: right;'>".mostraValor($valor)."</td>
                                </tr>";
    }

    $html .=  "
                            <tr>
                                <th></th>
                                <th style='text-align: right;'>".$totalQuantidade."</th>
                                <th style='text-align: right;'>".mostraValor($total)."</th>
                            </tr>
                        </tbody>
                    </table>";
                
    $html .="   </td>
            </tr>
        </tbody>
    </table>";
    //<--Final tabela de alinhamento

    //A Tabela com a classe apoio é usada apenas para alinhar tabelas uma ao lado da outra, pois esta função que gera o PDF é limitada com relação a estilização 
    //<--Inicio tabela de alinhamento
    $html .=  "
    <br>
    <table class='table table-hover tabelaApoio' style='border-collapse: collapse;'>
        <tbody>
            <tr>
                <td style='padding-right: 2%; vertical-align:middle !important'>";

                $html .=  "
                <table class='table table-hover tabelaPrincipal' style='width: 800px; border-collapse: collapse;'>
                    <tr>
                        <th colspan='4' style='text-align: center;'>RESUMO DIÁRIO DE ATENDIMENTOS</th>
                    </tr>
                    <thead>
                        <tr>
                            <th style='text-align: left; width: 21%'>PROFISSIONAL</th>
                            <th style='text-align: left; width: 18%'>PROCEDIMENTO</th>
                            <th style='text-align: left; width: 21%'>QUANTIDADE</th>
                            <th style='text-align: right; width: 20%'>VALOR</th>
                        </tr>
                    </thead>
                    <tbody>";
        
                $total = 0;
                $totalQuantidade = 0;
                foreach($rowAtendimento as $item) {
                    $valor = $item['grupoValor'] - $item['grupoDesconto'];
                    $total += $valor;
                    $totalQuantidade += $item['Quantidade'];

                    $html .= "
                                <tr>
                                    <td>".$item['ProfiNome']."</td>
                                    <td>Serviço</td>
                                    <td style='text-align: right;'>".$item['Quantidade']."</td>
                                    <td style='text-align: right;'>".mostraValor($valor)."</td>
                                </tr>";
                }
            
                $html .=  "
                            <tr>
                                <th colspan='2'></th>
                                <th style='text-align: right;'>".$totalQuantidade."</th>
                                <th style='text-align: right;'>".mostraValor($total)."</th>
                            </tr>
                        </tbody>
                    </table>";

    $html .="</td>
            <td style='padding-left: 2%; vertical-align:middle !important'>";

    $html .=  "
        <table class='table table-hover tabelaPrincipal' style='width: 400px; border-collapse: collapse;'>
            <tr>
                <th colspan='2' style='text-align: center;'>RESUMO DIÁRIO DE ATENDIMENTOS</th>
            </tr>
            <thead>
                <tr>
                    <th style='text-align: left; width: 52%'>VALOR A PAGAR</th>
                    <th style='text-align: left; width: 38%'>VALOR LÍQUIDO</th>
                </tr>
            </thead>
            <tbody>";

        $total = 0;
        foreach($rowAtendimento as $item) {
            $valor = $item['grupoValor'] - $item['grupoDesconto'];
            $total += $valor;

            $html .= "
                    <tr>
                        <td style='text-align: right;'>".mostraValor($valor)."</td>
                        <td style='text-align: right;'>".mostraValor($valor)."</td>
                    </tr>";
    }

    $html .=  "
                <tr>
                    <th style='text-align: right;'>".mostraValor($total)."</th>
                    <th style='text-align: right;'>".mostraValor($total)."</th>
                </tr>
            </tbody>
        </table>";
            
    $html .="</td>
        </tr>
        </tbody>
        </table>";
    //<--Final tabela de alinhamento
    
    $html .=  "
        <br>
        <table class='table table-hover tabelaPrincipal' style='width:100%; border-collapse: collapse; margin-bottom: 60px;'>
            <tr>
                <th colspan='6' style='text-align: center;'>HISTÓRICO DIÁRIO DOS ATENDIMENTOS</th>
            </tr>
            <thead>
                <tr>
                    <th style='text-align: left; width: 5%'></th>
                    <th style='text-align: left; width: 16%'>PACIENTE</th>
                    <th style='text-align: left; width: 19%'>PROCEDIMENTO</th>
                    <th style='text-align: left; width: 23%'>PROFISSIONAL</th>
                    <th style='text-align: left; width: 22%'>OBSERVAÇÕES</th>
                    <th style='width: 15%'>VALOR UNIT</th>
                </tr>
            </thead>
            <tbody>";

    $contador = 1;
    $total = 0;
    foreach($rowMovimentacao as $item) {
        $valor = $item['grupoValor'] - $item['grupoDesconto'];

        $html .= "
                    <tr>
                        <td style='text-align: right;'>".$contador."</td>
                        <td style='text-align: left;'>".$item['HISTORICO']."</td>
                        <td>Serviço</td>
                        <td>".$item['ProfiNome']."</td>
                        <td>".$item['AtendObservacao']."</td>
                        <td style='text-align: right;'>".mostraValor($valor)."</td>
                    </tr>";

        $total += $valor;
        $contador++;
    }

    $html .=  "
                <tr>
                    <th colspan='5'></th>
                    <th style='text-align: right;'>".mostraValor($total)."</th>
                </tr>
            </tbody>
        </table>";

    $html .=  "
    <div style='position: relative; width:100%;'>
        <div style='float:left; width: 300px; margin-left:20px; display: inline-block; text-align: center;'>
            <div style='font-size: 12px; border-top: 1px solid #666; padding: 15px'>Assinatura Operador de Caixa</div>
        </div>

        <div style='width:300px; margin-right:20px; float:right; display: inline; text-align:right; text-align: center;'>
            <div style='font-size: 12px; border-top: 1px solid #666; padding: 15px'>Assinatura do Responsável Financeiro</div>
        </div>
    </div>";

    $rodape = "<hr/>
    <div style='width:100%'>
    <div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
    <div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
    </div>";			

    //$mpdf->SetHTMLHeader($topo);
    //$stylesheet = file_get_contents('global_assets/css/lamparinas/bootstrap-3.3.7/dist/css/bootstrap.min.css');
    //$stylesheet = file_get_contents('global_assets/css/lamparinas/impressao.css');         
    //$mpdf->WriteHTML($stylesheet, 1); // CSS Script goes here.
    $mpdf->SetHTMLFooter($rodape);
    $mpdf->WriteHTML($html);            
    // $mpdf->SetHTMLHeader($topo,'O',true);
    
    $data = date('d-m-Y');
    $nomeArquivo = 'Fechamento Caixa - '.$data.'.pdf';

    $mpdf->Output($nomeArquivo, 'I');

} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch

    // Process the exception, log, print etc.
    $html = $e->getMessage();
	
    $mpdf->WriteHTML($html);
    
    // Other code
    $mpdf->Output();	
}
