<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';
require_once 'global_assets/php/funcoesgerais.php';

//$_SESSION['EmpreId']

if(!isset($_POST['inputReciboId'])) { 
    irpara("caixaMovimentacao.php");
}

$retiradaId = $_POST['inputReciboId'];

$sql_movimentacao    = "SELECT CxPagDataHora, CxPagValor, CxPagJustificativaRetirada, CaixaNome
                        FROM CaixaPagamento
                        JOIN CaixaAbertura on CxAbeId = CxPagCaixaAbertura
                        JOIN Caixa on CxAbeCaixa = CaixaId
                        WHERE CxPagId = $retiradaId";
$resultMovimentacao  = $conn->query($sql_movimentacao);
$rowMovimentacao = $resultMovimentacao->fetch(PDO::FETCH_ASSOC);

$valorRetirado = mostraValor($rowMovimentacao['CxPagValor']);
$valorPorExtenso = valor_por_extenso($rowMovimentacao['CxPagValor']);
$justificativa = $rowMovimentacao['CxPagJustificativaRetirada'];
$nomeCaixa = $rowMovimentacao['CaixaNome'];
$dataHoraRetirada = mostraDataHora($rowMovimentacao['CxPagDataHora']);
$operador = nomeSobrenome($_SESSION['UsuarNome'], 2);

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
        th{
            text-align: center; 
            border: #bbb solid 1px; 
            background-color: #f8f8f8; 
            padding: 8px;
        }

        td{
            padding: 8px;               
            border: #bbb solid 1px;
        }
    </style>


    <div style='position: relative; width:100%; border-bottom: 1px solid #666; padding-bottom: 10px;'>
        <div style='float:left; width: 200px; display: inline-block; padding: 10px;'>
            <img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:190px; height:45px; float:left; margin-right: 10px; margin-top:-10px;' alt='Logo Empresa' />
        </div>
        
        <div style='width:400px; float:right; display: inline; text-align:right;'>
            <div style='font-weight:bold; padding: 2px; font-size: 15px;'>$_SESSION[EmpreNomeFantasia]</div>
            <div style='padding: 2px; font-size: 10px;'>Unidade: $_SESSION[UnidadeNome]</div>
            <div style='padding: 2px; font-size: 10px;'>CNPJ: $empresaCnpj</div>
            <div style='padding: 2px; font-size: 10px;'>$endereco</div>
        </div> 
    </div>

    <div style='position: relative; width:100%; border-bottom: 1px solid #666; margin-bottom: 10px;'>
        <h3 style='text-align:center;'>Recibo de retirada de Caixa</h3>
    </div>

    <div style='position: relative; width:100%; padding: 10px; margin-bottom: 10px; font-weight:bold;'>
        <div style='float:left; width: 400px; display: inline-block;'>
            <div style='padding: 5px; font-size: 15px;'>Operador: $operador</div>
            <div style='padding: 5px; font-size: 15px;'>Caixa: $nomeCaixa</div>
            <div style='padding: 5px; font-size: 15px;'>Data/hora: $dataHoraRetirada</div>
        </div>
        
        <div style='width:250px; float:right; display: inline; text-align:right; color: red; margin-top: 30px;'>
            <div style='margin-bottom: 10px; font-size: 15px;'>Valor: R$ $valorRetirado</div>
            <div style='font-size: 15px;'>$valorPorExtenso</div>
        </div> 
    </div>

    <div style='position: relative; width:100%; border: 3px solid #CCCCCC; background-color: #eeeeee;'>
        <h3 style='text-align: center;'>Justificativa</h3>
    </div>

    <div style='position: relative; width:100%; border-bottom: 1px solid #666; padding: 20px; margin-bottom: 60px;'>
        <div style='font-size: 12px;'>$justificativa</div>
    </div>


    <div style='width: 50%; position: relative; margin-left: auto; margin-right: auto; text-align: center; padding-top: 10px;'>
        <div style='font-size: 12px; border-top: 1px solid #666; padding: 15px'>Assinatura Operador de Caixa</div>
    </div>";

    //Listando os Bens Não Patrimoniados
    //include("movimentacaoImprimeRetiradaNaoPatrimoniado.php");

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
    
    $arrayDataHora = explode(' ', $rowMovimentacao['CxPagDataHora']);
    $data = $arrayDataHora[0];
    $arrayHora = explode('.', $arrayDataHora[1]);
    $hora = $arrayHora[0];
    $nomeArquivo = $data.' '.$hora.' '.$justificativa.'.pdf';

    $mpdf->Output($nomeArquivo, 'I');

} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch

    // Process the exception, log, print etc.
    $html = $e->getMessage();
	
    $mpdf->WriteHTML($html);
    
    // Other code
    $mpdf->Output();	
}
