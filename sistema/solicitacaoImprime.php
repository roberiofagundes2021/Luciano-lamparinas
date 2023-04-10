<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

if (isset($_POST['inputSolicitacaoId'])){
	$iSolicitacao = $_POST['inputSolicitacaoId'];
} else{
	print('<script>
				window.close();
		   </script> ');
}

$sql = "SELECT SolicNumero, SolicData, SolicObservacao, UsuarNome, UsuarTelefone, UsuarCelular, UsuarEmail, SetorNome, 
		BandeSolicitanteSetor as SetorQuandoSolicitou, SolicSolicitante
		FROM Solicitacao
		JOIN Bandeja on BandeTabela = 'Solicitacao' and BandeTabelaId = SolicId
		JOIN Usuario on UsuarId = SolicSolicitante
		JOIN Setor on SetorId = BandeSolicitanteSetor
		WHERE SolicUnidade = ". $_SESSION['UnidadeId'] ." and SolicId = ".$iSolicitacao;
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT SetorId
		FROM Setor
		JOIN UsuarioXUnidade on UsXUnSetor = SetorId
		JOIN EmpresaXUsuarioXPerfil on EXUXPId = UsXUnEmpresaUsuarioPerfil
		WHERE SetorUnidade = ". $_SESSION['UnidadeId'] ." and EXUXPUsuario = ".$row['SolicSolicitante'];
$result = $conn->query($sql);
$rowSetorAtual = $result->fetch(PDO::FETCH_ASSOC);

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

	<div style='position: relative; width:100%; border-bottom: 1px solid #000;'>
		<div style='width:300px; float:left; display: inline;'>
			<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' />		
			<span style='font-weight:bold;line-height:200px;'>".$_SESSION['EmpreNomeFantasia']."</span><br>
			<div style='position: absolute; font-size:12px; margin-top: 8px; margin-left:4px;'>Unidade: ".$_SESSION['UnidadeNome']."</div>
		</div>
		<div style='width:250px; float:right; display: inline; text-align:right;'>
			<div>".date('d/m/Y')."</div>
			<div style='margin-top:8px;'>Solicitacao Nº: ".$row['SolicNumero']."</div>
		</div> 
	</div>
	
	<div style='text-align:center; margin-top: 20px;'><h1>SOLICITAÇÃO DE MATERIAIS</h1></div>
	";
	
	$html .= '
    <table style="width:100%; border-collapse: collapse;">
			<tr style="background-color:#F1F1F1;">
					<td colspan="1" style="width:25%; font-size:12px;">Nº Solicitacao:<br>'. $row['SolicNumero'].'</td>
					<td colspan="1" style="width:25%; font-size:12px;">Data:<br>'. mostraData($row['SolicData']).'</td>
					<td colspan="2" style="width:50%; font-size:12px;">Setor:<br>'. $row['SetorNome'].'</td>
			</tr>
		</table>
		<table style="width:100%; border-collapse: collapse;">
			<tr>
					<td colspan="1" style="width:40%; font-size:12px;">Solicitante:<br>'.$row['UsuarNome'].'</td>
					<td colspan="1" style="width:17%; font-size:12px;">Telefone:<br>'.$row['UsuarTelefone'].'</td>
					<td colspan="1" style="width:17%; font-size:12px;">Celular:<br>'.$row['UsuarCelular'].'</td>
					<td colspan="1" style="width:26%; font-size:12px;">E-mail:<br>'.$row['UsuarEmail'].'</td>
			</tr>';
	
	$obs = $row['SolicObservacao'];

	$trocouSetor = 0;

	// Verifica se quando o usuário solicitou e no momento ele não mudou de setor. Se sim, apresenta esse alerta no relatório
	if ($row['SetorQuandoSolicitou'] != $rowSetorAtual['SetorId']){
		$obs .= " <span style='color: red;'>ATENÇÃO: o setor do usuário solicitante foi alterado desde quando ele solicitou. Portanto, a liberação nào foi permitida.</span>";

		$trocouSetor = 1;
	}	

	if (($row['SolicObservacao'] != null && $row['SolicObservacao'] != '') || $trocouSetor){
		$html .= '<tr>
					<td colspan="4">Observação: '.$obs.'</td>
				  </tr>';
	}

    $html.= '</table>
	<br>';
	
	$sql = "SELECT ProduId, ProduNome, ProduDetalhamento, UnMedSigla, SlXPrQuantidade
	FROM Produto
	JOIN SolicitacaoXProduto on SlXPrProduto = ProduId
	JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
	WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and SlXPrSolicitacao = ".$iSolicitacao;

	$resultProduto = $conn->query($sql);
	$rowProdutos = $resultProduto->fetchAll(PDO::FETCH_ASSOC);
	$totalProdutos = count($rowProdutos);


	$sql = "SELECT ServiId, ServiNome, ServiDetalhamento, SlXSrQuantidade
	FROM Servico
	JOIN SolicitacaoXServico on SlXSrServico = ServiId
	WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and SlXSrSolicitacao = ".$iSolicitacao;

	$resultServico = $conn->query($sql);
	$rowServicos = $resultServico->fetchAll(PDO::FETCH_ASSOC);
	$totalServico = count($rowServicos);
    
	$totalItens = 0;
	
	if($totalProdutos > 0){

		$html .= "<div style='text-align:center; margin-top: 20px;'><h2>PRODUTOS</h2></div>";

		$html .= '
		<table style="width:100%; border-collapse: collapse;">
			<tr>
				<th style="text-align: center; width:10%">Item</th>
				<th style="text-align: left; width:60%">Produto</th>
				<th style="text-align: center; width:15%">Unidade</th>				
				<th style="text-align: center; width:15%">Quantidade</th>
			</tr>
		';
		
		$cont = 1;
		
		foreach ($rowProdutos as $rowProduto){

            $html .= "
            <tr>
                <td style='text-align: center;'>".$cont."</td>
                <td style='text-align: left;'>".$rowProduto['ProduNome'].": ".$rowProduto['ProduDetalhamento']."</td>
                <td style='text-align: center;'>".$rowProduto['UnMedSigla']."</td>					
                <td style='text-align: center;'>".$rowProduto['SlXPrQuantidade']."</td>
            </tr>
            ";
            			
			$cont++;
			$totalItens += $rowProduto['SlXPrQuantidade'];
		}

		$html .= "<br>";
		
		$html .= "</table><br>";
	}

	if($totalServico > 0){

		$html .= "<div style='text-align:center; margin-top: 20px;'><h2>SERVIÇOS</h2></div>";

		$html .= '
		<table style="width:100%; border-collapse: collapse;">
			<tr>
				<th style="text-align: center; width:10%">Item</th>
				<th style="text-align: left; width:75%">Serviço</th>
				<th style="text-align: center; width:15%">Quantidade</th>
			</tr>
		';
		
		$cont = 1;
		
		foreach ($rowServicos as $rowServico){

            $html .= "
            <tr>
                <td style='text-align: center;'>".$cont."</td>
                <td style='text-align: left;'>".$rowServico['ServiNome'].": ".$rowServico['ServiDetalhamento']."</td>
                <td style='text-align: center;'>".$rowServico['SlXSrQuantidade']."</td>
            </tr>
            ";
            			
			$cont++;
			$totalItens += $rowServico['SlXSrQuantidade'];
		}

		$html .= "<br>";
		
		$html .= "</table>";
	}

	$html .= "<table style='width:100%; border-collapse: collapse; margin-top: 20px;'>
	 			<tr>
                	<td colspan='5' height='50' valign='middle' style='width:85%'>
	                    <strong>TOTAL DE ITENS</strong>
                    </td>
				    <td style='text-align: center; width:15%'>
				        ".$totalItens."
				    </td>
			    </tr>
			  </table>
	";
		
    $rodape = "<hr/>
    <div style='width:100%'>
		<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
		<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
	</div>";

    //$mpdf->SetHTMLHeader($topo,'O',true); //o SetHTMLHeader deve vir antes do WriteHTML para que o cabeçalho apareça em todas as páginas
    $mpdf->SetHTMLFooter($rodape); 	//o SetHTMLFooter deve vir antes do WriteHTML para que o rodapé apareça em todas as páginas
    $mpdf->WriteHTML($html);
    
    // Other code
    $mpdf->Output();
    
} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch
    
    // Process the exception, log, print etc.
    $html = $e->getMessage();
	
    $mpdf->WriteHTML($html);
    
    // Other code
    $mpdf->Output();	
}
