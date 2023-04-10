<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

$iCliente = $_POST['inputClienteId'];

$sql = "SELECT *
		FROM Cliente
		JOIN Situacao on SituaId = ClienStatus
		WHERE ClienUnidade = " . $_SESSION['UnidadeId'] . " and ClienId = " . $iCliente;
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

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

	$mpdf->SetDisplayMode('fullpage','two'); //'fullpage': Ajustar uma página inteira na tela, 'fullwidth': Ajustar a largura da página na tela, 'real': Exibir em tamanho real, 'default': Configuração padrão do usuário no Adobe Reader, 'none'

	/*$mpdf = new Mpdf([
		'mode' => 'utf-8',
		//'format' => [190, 236], 
		'format' => 'A4-P', //A4-L
		'default_font_size' => 9,
		'default_font' => 'dejavusans',
		'orientation' => 'P' //P->Portrait (retrato)    L->Landscape (paisagem)
	]);*/


	


	//$html .= "</div>";

	$topo = "
	<div style='position: relative; width:100%; border-bottom: 1px solid #000;'>
		<div style='width:300px; float:left; display: inline;'>
			<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' alt='Logo Empresa' />
			<span style='font-weight:bold;line-height:200px;'>".$_SESSION['EmpreNomeFantasia']."</span><br>
			<div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: ".$_SESSION['UnidadeNome']."</div>
		</div>
		<div style='width:300px; float:right; display: inline; text-align:right;'>
			<div>{DATE j/m/Y}</div>
			<div style='margin-top:8px;font-weight:bold;'>CADASTRO DO CLIENTE</div>
		</div> 
	 </div>
	";	
			  
		$html = '
		<style>

			td{
				padding: 8px;				
				border: #bbb solid 1px;
			}
		</style>
		<br>
		<br>';

		if ($row['ClienTipo'] == 'F'){
			$html .= "
		<div style='text-align:center;'><h1>CLIENTE PESSOA FISICA</h1></div>
		  ";
		} else{
			$html .= "
			<div style='text-align:center;'><h1>CLIENTE PESSOA JURIDICA</h1></div>
			";
		}

		$html .= '
		<br>

		<table style="width:100%; border-collapse: collapse;">

			<tr style="background-color:#F1F1F1;">
				<td colspan="6" style="width:100% font-size: 13px;">DADOS PESSOAIS</td>';

		if ($row['ClienTipo'] == 'F'){
			$html .= '
			<tr>
				<td colspan="3" style="width:35% font-size: 13px;">Nome: '.$row['ClienNome'].'</td>
				<td colspan="2" style="width:40% font-size: 13px;">CPF: '.$row['ClienCpf'].'</td>
				<td colspan="1" style="width:30% font-size: 13px;">Cartão SUS: '.$row['ClienCartaoSus'].'</td>	
			</tr>
			<tr>
				<td colspan="1" style="width:30% font-size: 13px;">RG: '.$row['ClienRg'].'</td>
				<td colspan="1" style="width:20% font-size: 13px;">Órgão Emissor: '.$row['ClienOrgaoEmissor'].'</td>
				<td colspan="1" style="width:10% font-size: 13px;">UF: '.$row['ClienUf'].'</td>
				<td colspan="1" style="width:15% font-size: 13px;">Sexo: '.$row['ClienSexo'].'</td>
				<td colspan="2" style="width:25% font-size: 13px;">Data Nascimento: '.$row['ClienDtNascimento'].'</td>			
			</tr>
			<tr>
				<td colspan="3" style="width:50% font-size: 13px;">Nome do Pai: '.$row['ClienNomePai'].'</td>
				<td colspan="3" style="width:50% font-size: 13px;">Nome da Mãe: '.$row['ClienNomeMae'].'</td>
			</tr>';        
		} else{
			$html .= '
			<tr>
				<td colspan="3" style="width:50% font-size: 13px;">Nome: '.$row['ClienNome'].'</td>
				<td colspan="3" style="width:50% font-size: 13px;">CNPJ: '.$row['ClienCnpj'].'</td>	
			</tr>
			<tr>
				<td colspan="2" style="width:33% font-size: 13px;">Razão Social: '.$row['ClienRazaoSocial'].'</td>
				<td colspan="2" style="width:33% font-size: 13px;">Inscrição Municipal: '.$row['ClienInscricaoMunicipal'].'</td>
				<td colspan="2" style="width:33% font-size: 13px;">Inscrição Estadual: '.$row['ClienInscricaoEstadual'].'</td>
			</tr>';
		}

		$html .= '
		
		</table>
		<br>
		<table style="width:100%; border-collapse: collapse;">

			<tr style="background-color:#F1F1F1;">
				<td colspan="6" style="width:100% font-size: 13px;">ENDEREÇO</td>
			<tr>
				<td colspan="1" style="width:15% font-size: 13px;">CEP: '.$row['ClienCep'].'</td>
				<td colspan="2" style="width:35% font-size: 13px;">Endereço: '.$row['ClienEndereco'].'</td>
				<td colspan="1" style="width:15% font-size: 13px;">Numero: '.$row['ClienNumero'].'</td>
				<td colspan="2" style="width:35% font-size: 13px;">Compl.: '.$row['ClienComplemento'].'</td>		
			</tr>
			<tr>
				<td colspan="2" style="width:40% font-size: 13px;">Bairro: '.$row['ClienBairro'].'</td>
				<td colspan="3" style="width:40% font-size: 13px;">Cidade: '.$row['ClienCidade'].'</td>
				<td colspan="1" style="width:20% font-size: 13px;">Estado: '.$row['ClienEstado'].'</td>
			</tr>
		</table>
		<br>
		<table style="width:100%; border-collapse: collapse;">

			<tr style="background-color:#F1F1F1;">
				<td colspan="6" style="width:100% font-size: 13px;">CONTATO</td>
			<tr>
				<td colspan="2" style="width:40% font-size: 13px;">Nome: '.$row['ClienContato'].'</td>
				<td colspan="2" style="width:30% font-size: 13px;">Telefone: '.$row['ClienTelefone'].'</td>
				<td colspan="2" style="width:30% font-size: 13px;">Celular: '.$row['ClienCelular'].'</td>
			</tr>
			<tr>
				<td colspan="3" style="width:50% font-size: 13px;">E-mail: '.$row['ClienEmail'].'</td>
				<td colspan="3" style="width:50% font-size: 13px;">Site: '.$row['ClienSite'].'</td>		
			</tr>
			<tr>
				<td colspan="6" style="width:100% font-size: 13px;">Observação: '.$row['ClienObservacao'].'</td>
				
			</tr>
		</table>
					';
			
						 
    
	$rodape = "<hr/>
    <div style='width:100%'>
		<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
		<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
	</div>";
	
	//ATENÇÃO: Tive que colocar o cabeçalho dentro do HTML, para o cabeçalho não sobrescrever o conteúdo HTML a partir da segunda página. Em compensação o cabeçalho só aparece na primeira página. Foi a única forma que encontrei. Tentei de tudo...

	$mpdf->SetHTMLHeader($topo);	
   // $mpdf->SetHTMLHeader($topo,'O',true);
    $mpdf->SetHTMLFooter($rodape);
    $mpdf->WriteHTML($html);
	// Other code
	$mpdf->Output();

} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch

	// Process the exception, log, print etc.
	echo 'ERRO: '.$e->getMessage();
}
