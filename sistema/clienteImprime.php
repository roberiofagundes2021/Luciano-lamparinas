<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

	$sql = "SELECT *
            FROM Cliente
            JOIN Situacao on SituaId = ClienStatus
            WHERE ClienUnidade = ".$_SESSION['UnidadeId']." and SituaChave = 'ATIVO'";

$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);

try {
    $mpdf = new Mpdf([
        'mode' => 'utf-8', 
        //'format' => [190, 236], 
        'format' => 'A4-L',
        'default_font_size' => 10,
		'default_font' => 'dejavusans',
        //'orientation' => 'P', //P =>Portrait, L=> Landscape
		'margin_top' => 30 // se quiser dar margin no header, aí seria 'margin_header'
	]);
	
	$topo = "
	<div style='position: relative; width:100%; border-bottom: 1px solid #000;'>
		<div style='width:300px; float:left; display: inline;'>
			<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' alt='Logo Empresa' />
			<span style='font-weight:bold;line-height:200px;'>".$_SESSION['EmpreNomeFantasia']."</span><br>
			<div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: ".$_SESSION['UnidadeNome']."</div>
		</div>
		<div style='width:300px; float:right; display: inline; text-align:right;'>
			<div>{DATE j/m/Y}</div>
			<div style='margin-top:8px;font-weight:bold;'>RELAÇÃO DE CLIENTES</div>
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
				<th style="text-align: left; width:4%">#</th>
				<th style="text-align: left; width:32%">Nome</th>
				<th style="text-align: left; width:15%">CPF/CNPJ</th>
				<th style="text-align: left; width:16%">Contato</th>
				<th style="text-align: left; width:13%">Celular</th>
				<th style="text-align: left; width:20%">E-mail</th>
			</tr>
		';					  
	
		$cont = 1;
		foreach ($row as $item){	
		
			if ($item['ClienTipo'] == 'F'){
				$sCliente = $item['ClienNome'];
				$iDocumento = formatarCPF_Cnpj($item['ClienCpf']);
			} else {
				$sCliente = $item['ClienRazaoSocial'];
				$iDocumento = formatarCPF_Cnpj($item['ClienCnpj']);
			}
		
			$html .= "
					<tr>
						<td style='padding-top: 8px; font-size: 11px;'>".$cont."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$sCliente."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$iDocumento."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$item['ClienContato']."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$item['ClienCelular']."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$item['ClienEmail']."</td>
					</tr>
					";
			$cont++;
		}
	
	
	$html .= "</table>";		
    
    $rodape = "<hr/>
    <div style='width:100%'>
		<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
		<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
	</div>";
    
	$mpdf->SetHTMLHeader($topo);	
   // $mpdf->SetHTMLHeader($topo,'O',true);
    $mpdf->SetHTMLFooter($rodape);
    $mpdf->WriteHTML($html);
    
    // Other code
    $mpdf->Output();
    
} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch
    
    // Process the exception, log, print etc.
    echo $e->getMessage();
}
