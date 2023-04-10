<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

// Aplicado filtro aos resultados
/***************************************************************/
$args = [];

/////////////////////////////////////////////


empty($_POST['inputDataDe_imp']) ? $inputDataDe = '' : $inputDataDe = " FlOpeDataInicio > '".$_POST['inputDataDe_imp']."'";

$args[]  = $inputDataDe;

empty($_POST['inputDataAte_imp']) ? $inputDataAte = '' : $inputDataAte = " dbo.fnFimContrato(FlOpeId) < '". $_POST['inputDataAte_imp']."'";

$args[]  = $inputDataAte;


if (!empty($_POST['inputUnidade_imp']) && $_POST['inputUnidade_imp'] != "") {
    $args[]  = "FlOpeUnidade = " . $_POST['inputUnidade_imp'] . " ";
}

if (!empty($_POST['inputEmpresaContratada_imp']) && $_POST['inputEmpresaContratada_imp'] != "") {
    $args[]  = "FlOpeFornecedor = " . $_POST['inputEmpresaContratada_imp'] . " ";
}

if (!empty($_POST['inputCategoria_imp']) && $_POST['inputCategoria_imp'] != "") {
    $args[]  = "FlOpeCategoria = " . $_POST['inputCategoria_imp'] . " ";
}

if (!empty($_POST['inputModalidade_imp']) && $_POST['inputModalidade_imp'] != "") {
    $args[]  = "FlOpeModalidadeLicitacao = " . $_POST['inputModalidade_imp'] . " ";
}

if (!empty($_POST['inputPrioridade_imp']) && $_POST['inputPrioridade_imp'] != "") {
    $args[]  = "FlOpePrioridade = " . $_POST['inputPrioridade_imp'] . " ";
}

if (!empty($_POST['inputStatus_imp']) && $_POST['inputStatus_imp'] != "") {
    $args[]  = "FlOpeStatus = " . $_POST['inputStatus_imp'] . " ";
}

if (count($args) >= 1) {
    try {

        $string = implode(" and ", $args);

        // if ($string != ''){
        //     $string .= ' and ';
        // }

        $sql = "SELECT FlOpeId,  FlOpeDataInicio,  dbo.fnFimContrato(FlOpeId) as DataFim,  FlOpeObservacao, FlOpePrioridade, UnidaNome, ForneNome, CategNome, MdLicNome, PriorNome, SituaNome
                FROM FluxoOperacional
                JOIN Unidade on UnidaId = FlOpeUnidade
                LEFT JOIN Fornecedor on ForneId = FlOpeFornecedor
                LEFT JOIN Categoria  on CategId = FlOpeCategoria
                LEFT JOIN ModalidadeLicitacao on MdLicId = FlOpeModalidadeLicitacao
                LEFT JOIN Prioridade on PriorId = FlOpePrioridade 
                JOIN Situacao on SituaId = FlOpeStatus
                WHERE " . $string . "
                ";
        $result = $conn->query($sql);
        $rowData = $result->fetchAll(PDO::FETCH_ASSOC);

        count($rowData) >= 1 ? $cont = 1 : $cont = 0;

        //print($sql);
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }
}
/***************************************************************/

if (isset($_POST['resultados'])) {
    try {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            //'format' => [190, 236], 
            'format' => 'A4-L', //A4-L
            'default_font_size' => 9,
            'default_font' => 'dejavusans',
            'orientation' => 'L' //P->Portrait (retrato)    L->Landscape (paisagem)
        ]);

        $topo = "
			<div style='position: relative; width:100%; border-bottom: 1px solid #000;'>
				<div style='float:left; width: 400px; display: inline-block; margin-bottom: 10px;'>
					<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' />		
					<span style='font-weight:bold;line-height:200px;'>" . $_SESSION['EmpreNomeFantasia'] . "</span><br>
					<div style='position: absolute; font-size:9px; margin-top: 8px; margin-left:4px;'>Unidade: " . $_SESSION['UnidadeNome'] . "</div>
				</div>
				<div style='width:350px; float:right; display: inline; text-align:right;'>
					<div style='font-size: 0.8rem'>Data {DATE j/m/Y}</div>
					<div style='margin-top:8px;'></div>
					<div style='margin-top:8px; font-weight:bold;'>Relatório Licitação</div>
				</div>
			</div>
	    ";

        $html = '
			<style>
				th{
					text-align: center; 
					border: #bbb solid 1px; 
					background-color: #f8f8f8; 
					padding: 8px;
					font-size: 0.6rem;
				}

				td{
					padding: 8px;				
					border: #bbb solid 1px;
					font-size: 0.6rem;
				}
			</style>
		';

        $html .= '<br>';
        $html .= '<br>';

        if (!empty($_POST['inputDataDe_imp']) || !empty($_POST['inputDataAte_imp'])) {
            if (!empty($_POST['inputDataDe_imp']) && !empty($_POST['inputDataAte_imp'])) {
                $html .= '
            		<div style="font-weight: bold; font-size: 0.8rem; position:relative; margin-top: 10px; padding: 5px;">
			            Período: ' . mostraData($_POST['inputDataDe_imp']) . ' à ' . mostraData($_POST['inputDataAte_imp']) . ' 
		            </div>
		        ';
            } else if (!empty($_POST['inputDataDe_imp'])) {
                $html .= '
            		<div style="font-weight: bold; font-size: 0.8rem; position:relative; margin-top: 20px; padding: 5px;">
			            Período: ' . mostraData($_POST['inputDataDe_imp']) . ' à ' . date('d/m/Y') . ' 
		            </div>
		        ';
            } else {
                $html .= '
            		<div style="font-weight: bold; font-size: 0.8rem; position:relative; margin-top: 20px; padding: 5px;">
			            Período: Até ' . date('d/m/Y') . ' 
		            </div>
		        ';
            }
        }

        if (!empty($_POST['inputCategoria_imp'])) {
            $html .= '
					<div style="font-weight: bold; font-size: 0.8rem; padding: 5px;">
			            Categoria: ' . $Categoria['CategNome'] . '
		            </div>
		        ';
        }


        $html .= '
		<br>
		<br>
		<table style="width:100%; border-collapse: collapse; margin-top: -24px">
			<tr>
				<th style="text-align: left; width:5%;">Item</th>
				<th style="text-align: left; width:10%;">Categoria</th>
				<th style="text-align: center; width:10%;">Empresa Contratada</th>
				<th style="text-align: center; width:10%;">Local</th>
				<th style="text-align: center; width:10%;">Status</th>
				<th style="text-align: center; width: 10%;">Modalidade</th>
				<th style="text-align: center; width: 7.5%;">Inicio</th>
				<th style="text-align: left; width: 7.5%;">Término</th>
                <th style="text-align: left; width: 10%;">Prioridade</th>
                <th style="text-align: left; width: 20%;">Observação</th>
			</tr>
		';

        $html .= "<tbody>";

        $cont = 0;
        
        foreach ($rowData as $item) {
            $cont += 1;
            $sql = "SELECT MAX(AditiId) AditiId
                        FROM FluxoOperacional
                        JOIN Aditivo on AditiFluxoOperacional = FlOpeId
                        WHERE FlOpeId = " . $item['FlOpeId'] . "
            ";
            $result = $conn->query($sql);
            $rowUltimoAditivo = $result->fetch(PDO::FETCH_ASSOC);
            //var_dump($rowUltimoAditivo);
            if ($rowUltimoAditivo['AditiId']) {

                $sql = "SELECT AditiDtInicio, AditiDtFim
                            FROM Aditivo
                            WHERE AditiId = " . $rowUltimoAditivo['AditiId'] . "
                ";
                $result = $conn->query($sql);
                $rowDataUltimoAditivo = $result->fetch(PDO::FETCH_ASSOC);

                $html .= "
                            <tr>
                                <td>" . $cont . "</td>
                                <td>" . $item['CategNome'] . "</td>
                                <td>" . $item['ForneNome'] . "</td>
                                <td>" . $item['UnidaNome'] . "</td>
                                <td style='text-align: center'>" . $item['SituaNome'] . "</td>
                                <td style='text-align: center'>" . $item['MdLicNome'] . "</td>
                                <td style='text-align: center'>" . mostraData($rowDataUltimoAditivo['AditiDtInicio']) . "</td>
                                <td style='text-align: center'>" . mostraData($rowDataUltimoAditivo['AditiDtFim']) . "</td>
                                <td style='text-align: center'>" . $item['PriorNome'] . "</td>
                                <td style='text-align: justify'>" . $item['FlOpeObservacao'] . "</td>
                            </tr>
                "; 
            } else {

                $html .= "
                            <tr>
                                <td>" . $cont . "</td>
                                <td>" . $item['CategNome'] . "</td>
                                <td>" . $item['ForneNome'] . "</td>
                                <td>" . $item['UnidaNome'] . "</td>
                                <td style='text-align: center'>" . $item['SituaNome'] . "</td>
                                <td style='text-align: center'>" . $item['MdLicNome'] . "</td>
                                <td style='text-align: center'>" . mostraData($item['FlOpeDataInicio']) . "</td>
                                <td style='text-align: center'>" . mostraData($item['DataFim']) . "</td>
                                <td style='text-align: center'>" . $item['PriorNome'] . "</td>
                                <td style='text-align: justify'>" . $item['FlOpeObservacao'] . "</td>
                            </tr>
                ";
            }
        }
        $html .= "</tbody>";

        $html .= "</table>";

        $rodape = "<hr/>
		<div style='width:100%'>
			<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
			<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
		</div>";

        $mpdf->SetHTMLHeader($topo, 'O', true);
        $mpdf->WriteHTML($html);
        $mpdf->SetHTMLFooter($rodape);

        // Other code
        $mpdf->Output();
    } catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch

        // Process the exception, log, print etc.
        echo $e->getMessage();
    }
}
