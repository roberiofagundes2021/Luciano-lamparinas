<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';
$sCategoria = '';
$iCategoria = $_POST['inputFornecedorCategoria'];
	 
if ($iCategoria == '#'){
	$sql = "SELECT ForneCategoria, CategNome, ForneTipo, ForneRazaoSocial, ForneNome, ForneCpf, ForneCnpj, ForneContato, ForneTelefone, ForneEmail
			FROM Fornecedor
			JOIN Categoria on CategId = ForneCategoria
			JOIN Situacao on SituaId = ForneStatus
			WHERE ForneEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
			Group By ForneCategoria, CategNome, ForneTipo, ForneRazaoSocial, ForneNome, ForneCpf, ForneCnpj, ForneContato, ForneTelefone, ForneEmail";
} else {
	
	$sql = "SELECT CategNome
			FROM Categoria
			WHERE CategId = ".$iCategoria;
	$resultCategoria = $conn->query($sql);
	$rowCategoria = $resultCategoria->fetch(PDO::FETCH_ASSOC);	
	
	$sCategoria = $rowCategoria['CategNome'];
	
	$sql = "SELECT *
			FROM Fornecedor
			JOIN Situacao on SituaId = ForneStatus
			WHERE ForneCategoria = ".$iCategoria." and ForneEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'";
}

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
			<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' />		
			<span style='font-weight:bold;line-height:200px;'>".$_SESSION['EmpreNomeFantasia']."</span><br>
			<div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: ".$_SESSION['UnidadeNome']."</div>
		</div>
		<div style='width:300px; float:right; display: inline; text-align:right;'>
			<div>{DATE j/m/Y}</div>
			<div style='margin-top:8px;font-weight:bold;'>RELAÇÃO DE FORNECEDORES</div>
		</div> 
	 </div>
	";		
	
	$html = '';
	
	//Se for todas as categorias
	if ($iCategoria == '#'){

		$cont = 1;
		$categoriaVelha = 0;
		foreach ($row as $item){

			$categoriaNova = $item['ForneCategoria'];
			
			if ($categoriaNova <> $categoriaVelha){
				
				if($categoriaVelha <> 0){
					$html .= "</table>";
				}
				
				$html .= '<div style="position:relative; margin-top: 40px; text-transform: uppercase; font-weight: bold; background-color: #ccc; padding: 5px;">Categoria: '.$item['CategNome'].'</div>';
						  
				$html .= '
				<br>
				<table style="width:100%; border-collapse: collapse;">
					<tr>
						<th style="text-align: left; padding-top: 7px; padding-bottom: 7px; border-bottom: 1px solid #333; width:5%">Num</th>
						<th style="text-align: left; padding-top: 7px; padding-bottom: 7px; border-bottom: 1px solid #333; width:38%">Razão Social/Nome</th>
						<th style="text-align: left; padding-top: 7px; padding-bottom: 7px; border-bottom: 1px solid #333; width:15%">CNPJ/CPF</th>
						<th style="text-align: left; padding-top: 7px; padding-bottom: 7px; border-bottom: 1px solid #333; width:12%">Contato</th>
						<th style="text-align: left; padding-top: 7px; padding-bottom: 7px; border-bottom: 1px solid #333; width:15%">Telefone</th>
						<th style="text-align: left; padding-top: 7px; padding-bottom: 7px; border-bottom: 1px solid #333; width:15%">E-mail</th>
					</tr>
				';							  
			}
			
			if ($item['ForneTipo'] == 'F'){
				$sFornecedor = $item['ForneNome'];
				$iDocumento = formatarCPF_Cnpj($item['ForneCpf']);
			} else {
				$sFornecedor = $item['ForneRazaoSocial'];
				$iDocumento = formatarCPF_Cnpj($item['ForneCnpj']);
			}
		
			$html .= "
					<tr>
						<td style='padding-top: 8px; font-size: 11px;'>".$cont."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$sFornecedor."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$iDocumento."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$item['ForneContato']."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$item['ForneTelefone']."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$item['ForneEmail']."</td>
					</tr>
					";
			$cont++;	
			$categoriaVelha = $categoriaNova;
		}
				  
	} else {
		$html .= '<div style="font-weight: bold; position:relative; margin-top: 50px;text-transform: uppercase; background-color: #ccc; padding:5px;">Categoria: '.$sCategoria.'</div>';
				  
		$html .= '
		<br>
		<table style="width:100%; border-collapse: collapse;">
			<tr>
				<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:5%">Num</th>
				<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:38%">Razão Social/Nome</th>
				<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:15%">CNPJ/CPF</th>
				<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:12%">Contato</th>
				<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:15%">Telefone</th>
				<th style="text-align: left; border-bottom: 1px solid #333; padding-top: 7px; padding-bottom: 7px; width:15%">E-mail</th>
			</tr>
		';					  
	
		$cont = 1;
		foreach ($row as $item){	
		
			if ($item['ForneTipo'] == 'F'){
				$sFornecedor = $item['ForneNome'];
				$iDocumento = formatarCPF_Cnpj($item['ForneCpf']);
			} else {
				$sFornecedor = $item['ForneRazaoSocial'];
				$iDocumento = formatarCPF_Cnpj($item['ForneCnpj']);
			}
		
			$html .= "
					<tr>
						<td style='padding-top: 8px; font-size: 11px;'>".$cont."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$sFornecedor."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$iDocumento."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$item['ForneContato']."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$item['ForneTelefone']."</td>
						<td style='padding-top: 8px; font-size: 11px;'>".$item['ForneEmail']."</td>
					</tr>
					";
			$cont++;
		}
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
