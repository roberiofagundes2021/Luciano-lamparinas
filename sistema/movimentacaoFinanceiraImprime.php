<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

    if (isset($_POST['inputTipoFiltro_imp'])) {

        $cont = 0;
        $argsCr = [];
        $argsCp = [];
        $argsCenCustCr = '';
        $argsCenCustCp = '';
        $status = explode('|', $_POST['inputStatus_imp']);

        if (!empty($_POST['inputDataDe_imp']) || !empty($_POST['inputDataAte_imp'])) {
            empty($_POST['inputDataDe_imp']) ? $inputDataDe_imp = '1900-01-01' : $inputDataDe_imp = $_POST['inputDataDe_imp'];
            empty($_POST['inputDataAte_imp']) ? $inputDataAte_imp = '2100-01-01' : $inputDataAte_imp = $_POST['inputDataAte_imp'];

            $argsCr[]  = "CNAREDTEMISSAO BETWEEN '" . $inputDataDe_imp . "' and '" . $inputDataAte_imp . "' ";
            $argsCp[]  = "CNAPADTEMISSAO BETWEEN '" . $inputDataDe_imp . "' and '" . $inputDataAte_imp . "' ";
        }

        if (!empty($_POST['cmbContaBanco_imp'])) {
            $argsCr[]  = "CnAReContaBanco = " . $_POST['cmbContaBanco_imp'] . " ";
            $argsCp[]  = "CnAPaContaBanco = " . $_POST['cmbContaBanco_imp'] . " ";
        }

        if (!empty($_POST['cmbCentroDeCustos_imp'])) {
            $argsCenCustCp = " join PlanoConta
                                on PlConId = CnAPaPlanoContas
                                join CentroCusto
                                on CnCusId = PlConCentroCusto ";

            $argsCenCustCr = " join PlanoConta
                                on PlConId = CnARePlanoContas
                                join CentroCusto
                                on CnCusId = PlConCentroCusto ";
                            
            $argsCr[]  = "CnCusId = " . $_POST['cmbCentroDeCustos_imp'] . " ";
            $argsCp[]  = "CnCusId = " . $_POST['cmbCentroDeCustos_imp'] . " ";
        }

        if (!empty($_POST['cmbPlanoContas_imp'])) {
            $argsCr[]  = "CnARePlanoContas = " . $_POST['cmbPlanoContas_imp'] . " ";
            $argsCp[]  = "CnAPaPlanoContas = " . $_POST['cmbPlanoContas_imp'] . " ";
        }

        if (!empty($_POST['cmbFormaDeRecebimento_imp'])) {
            $argsCr[]  = "CnAReFormaPagamento = " . $_POST['cmbFormaDeRecebimento_imp'] . " ";
            $argsCp[]  = "CnAPaFormaPagamento = " . $_POST['cmbFormaDeRecebimento_imp'] . " ";
        }

        if (!empty($_POST['inputStatus_imp'])) {
            if ($status[0] === "12") {
                $argsCp[]  = "CnAPaStatus = 12";

            } else if ($status[0] === "14") {
                $argsCp[]  = "CnAReStatus = 14";

            } else {
                $argsCr[]  = "CnAReStatus = 14";
                $argsCp[]  = "CnAPaStatus = 12";
            }
        }


        if ((count($argsCr) >= 1) || (count($stringCp) >= 1)) {

            $stringCr = implode(" and ", $argsCr);
            $stringCp = implode(" and ", $argsCp);

            if ($stringCr != '') {
                $stringCr .= ' and ';
            }

            if ($stringCp != '') {
                $stringCp .= ' and ';
            }

            if ($status[0] === "12") {
                $sql = "SELECT CNAPAID AS ID, CNAPADTEMISSAO AS DATA, CNAPADESCRICAO AS HISTORICO, CnAPANUMDOCUMENTO AS NUMDOC, CNAPAVALORPAGO as TOTAL, TIPO = 'P' 
                        FROM ContasAPagar ";
                        if (isset($argsCenCustCp)) {
                            $sql .= " $argsCenCustCp ";
                        }
                $sql .= "WHERE " . $stringCp . " CnAPaUnidade = " . $_SESSION['UnidadeId'] . "
                            ORDER BY DATA DESC";
                        
            } else if ($status[0] === "14") {
                $sql = "SELECT CNAREID AS ID, CNAREDTEMISSAO AS DATA, CNAREDESCRICAO AS HISTORICO, CnARENUMDOCUMENTO AS NUMDOC, CNAREVALORRECEBIDO as TOTAL, TIPO = 'R' 
                        FROM ContasAReceber ";
                        if (isset($argsCenCustCr)) {
                            $sql .= " $argsCenCustCr ";
                        }
                $sql .= "WHERE " . $stringCr . " CnAReUnidade = " . $_SESSION['UnidadeId'] . "
                        ORDER BY DATA DESC";
                        
            } else {
                $sql = "SELECT CNAREID AS ID, CNAREDTEMISSAO AS DATA, CNAREDESCRICAO AS HISTORICO, CnARENUMDOCUMENTO AS NUMDOC, CNAREVALORRECEBIDO as TOTAL, TIPO = 'R' 
                        FROM ContasAReceber ";
                        if (isset($argsCenCustCr)) {
                            $sql .= " $argsCenCustCr ";
                        }
                $sql .= "WHERE " . $stringCr . " CnAReUnidade = " . $_SESSION['UnidadeId'] . "
                        UNION 
                        SELECT CNAPAID AS ID, CNAPADTEMISSAO AS DATA, CNAPADESCRICAO AS HISTORICO, CnAPANUMDOCUMENTO AS NUMDOC, CNAPAVALORPAGO as TOTAL, TIPO = 'P' 
                        FROM ContasAPagar ";
                        if (isset($argsCenCustCp)) {
                            $sql .= " $argsCenCustCp ";
                        }
                $sql .= "WHERE " . $stringCp . " CnAPaUnidade = " . $_SESSION['UnidadeId'] . "
                        ORDER BY DATA DESC";
            }
        }
    }

$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);


try {
    $mpdf = new Mpdf([
        'mode' => 'utf-8', 
        'default_font_size' => 10,  // font size - default 0
		'default_font' => 'dejavusans',
        //'orientation' => 'P', //P =>Portrait, L=> Landscape
        'margin_top' => 30, // se quiser dar margin no header, aí seria 'margin_header'
		'format' => 'A4-P',    // format - A4, for example, default ''
		'margin-left' => 15,    // margin_left
		'margin-right' => 15,    // margin right
		'margin-top' => 158,     // margin top    -- aumentei aqui para que não ficasse em cima do header
		'margin-bottom' => 60,    // margin bottom
		'margin-header' => 6,     // margin header
		'margin-bottom' => 0,     // margin footer
		'orientation' => 'P'  // L - landscape, P - portrait	
    ]);
    
    $topo = "
	<div style='position: relative; width:100%; border-bottom: 1px solid #000;'>
		<div style='width:300px; float:left; display: inline;'>
			<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' />		
			<span style='font-weight:bold;line-height:200px;'>".$_SESSION['EmpreNomeFantasia']."</span><br>
			<div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: ".$_SESSION['UnidadeNome']."</div>
		</div>
		<div style='width:300px; float:right; display: inline; text-align:right;'>
			<div>{DATE j/m/Y}</div>
			<div style='margin-top:8px;font-weight:bold;'>RELATÓRIO DE MOVIMENTAÇÃO FINANCEIRA</div>
		</div> 
	 </div>
	";	
	
    $html = '<style>
	th{
		padding: 8px;				
		border: #bbb solid 1px;
		background-color:#F1F1F1;
	}
	td{
		padding: 8px;				
		border: #bbb solid 1px;
	}
	</style>';
	
	//Se for todas os Nomes
				  
		$html .= '
		<br>
		<table style="width:100%; border-collapse: collapse;">
			<tr>
				<th style="text-align: left; width:5%">#</th>
				<th style="text-align: left; width:13%">Data</th>
				<th style="text-align: left; width:35%">Histórico</th>
				<th style="text-align: left; width:15%">Num Doc.</th>
                <th style="text-align: left; width:8%">Tipo</th>
                <th style="text-align: left; width:12%">Total</th>
                <th style="text-align: left; width:12%">Saldo</th>
			</tr>
		';					  
	
        $cont = 1;
        $saldo = 0;
		foreach ($row as $item){

            if ($item['TIPO'] === 'R'){
                $saldo += $item['TOTAL'];
            }
            else {
                $saldo -= $item['TOTAL'];
            }
            
            $html .= "
            <tr>
                <td style='font-size: 11px; margin:0;'>" . $item['ID'] . "</td>
                <td style='font-size: 11px; margin:0;'>" . mostraData($item['DATA']) . "</td>
                <td style='font-size: 11px; margin:0;'>" . $item['HISTORICO'] . "</td>
                <td style='font-size: 11px; margin:0;'>" . $item['NUMDOC'] . "</td>";
                if ($item['TIPO'] === 'R') {
                    $html .= "<td style='font-size: 11px; margin:0;'>Recebido</td>";
                } else if ($item['TIPO'] === 'P') {
                    $html .= "<td style='font-size: 11px; margin:0;'>Pago</td>";
                }
            $html .= "
                <td style='font-size: 11px; margin:0;'>" . mostraValor($item['TOTAL']) . "</td>";
                if ($saldo < 0) {
                    $html .= "<td style='color: red; font-size: 11px;'>" . mostraValor($saldo) . "</td>";
                }
                else {
                    $html .= "<td style='color: green; font-size: 11px;'>" . mostraValor($saldo) . "</td>";
                }
                $html .= "
            </tr>
            ";
		}
	
    if ($html != ''){
        $html .= "</table><br>";
	} else {
        $html .= "</table>";
    }

    $html .= "<hr/>
            <br>";
            
    if (isset($_POST['inputStatus_imp']) || isset($_POST['cmbContaBanco_imp']) || isset($_POST['cmbCentroDeCustos_imp'])|| isset($_POST['cmbPlanoContas_imp'])|| isset($_POST['cmbFormaDeRecebimento_imp'])){

        $html .= 'Observação: Esse relatório foi gerado a partir dos seguintes critérios: ';
        (isset($_POST['inputStatus_imp']) && $_POST['inputStatus_imp'] !== null && $_POST['inputStatus_imp'] !== '' ) && $html .= "Tipo (" . $_POST['inputStatus_imp'].") ";

        (isset($_POST['cmbContaBanco_imp']) && $_POST['cmbContaBanco_imp'] !== null && $_POST['cmbContaBanco_imp'] !== '' ) && $html .= "Conta/Banco (" . $_POST['cmbContaBanco_imp'].") ";

        (isset($_POST['cmbCentroDeCustos_imp']) && $_POST['cmbCentroDeCustos_imp'] !== null && $_POST['cmbCentroDeCustos_imp'] !== '' ) && $html .= "Centro de Custos (" . $_POST['cmbCentroDeCustos_imp'].") ";

        (isset($_POST['cmbPlanoContas_imp']) && $_POST['cmbPlanoContas_imp'] !== null && $_POST['cmbPlanoContas_imp'] !== '' ) && $html .= "Plano de Contas (" . $_POST['cmbPlanoContas_imp'].") ";
        
        (isset($_POST['cmbFormaDeRecebimento_imp']) && $_POST['cmbFormaDeRecebimento_imp'] !== null && $_POST['cmbFormaDeRecebimento_imp'] !== '' ) && $html .= "Forma de Recebimento (" . $_POST['cmbFormaDeRecebimento_imp'].") ";
        $html .= '<br><br>
            ';
    }


    $rodape = "<hr/>
    <div style='width:100%'>
		<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
		<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
	</div>";
    
	$mpdf->SetHTMLHeader($topo);	
//    $mpdf->SetHTMLHeader($topo,'O',true);
    $mpdf->SetHTMLFooter($rodape);
    $mpdf->WriteHTML($html);
    
    // Other code
    $mpdf->Output();
    
} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch
    
    // Process the exception, log, print etc.
    echo $e->getMessage();
}