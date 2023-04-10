<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';


$sql = "SELECT DISTINCT ProduId, ProduCategoria, CategNome, ProduCodigo, ProduNome, ProduEstoqueMinimo, 
		dbo.fnSaldoEstoque(MovimUnidade, ProduId, 'P', NULL) as saldo
	    FROM Produto
	    JOIN Categoria on CategId = ProduCategoria
	    JOIN MovimentacaoXProduto on MvXPrProduto = ProduId
	    JOIN Movimentacao on MovimId = MvXPrMovimentacao
	    WHERE MovimUnidade = ".$_SESSION['UnidadeId'].
			"Group By ProduCategoria, CategNome, ProduId, ProduCodigo, ProduNome, ProduEstoqueMinimo, MovimUnidade";
$result = $conn->query($sql);
$rows = $result->fetchAll(PDO::FETCH_ASSOC);

$rowProduto = [];
for($x=0;  $x < COUNT($rows); $x++){
	if($rows[$x]['saldo'] < $rows[$x]['ProduEstoqueMinimo']){
		array_push($rowProduto, $rows[$x]);
	}
	// se não foi informado o campo ProduEstoqueMinimo ele setar como 30% da quantidade total
	if($rows[$x]['ProduEstoqueMinimo'] == null) {
		// buscando o ultimo Fluxo cadastrado do produto especifico com situação "LIBERADO"
		$sql = "SELECT FOXPrFluxoOperacional, FOXPrProduto, FOXPrQuantidade, FOXPrValorUnitario,
		FOXPrUsuarioAtualizador, FOXPrUnidade
		FROM FluxoOperacionalXProduto
		JOIN FluxoOperacional on FlOpeId = FOXPrFluxoOperacional
		JOIN Situacao on SituaId = FlOpeStatus
		WHERE FOXPrUnidade = $_SESSION[UnidadeId] AND FOXPrProduto = ".$rows[$x]['ProduId']."
		AND SituaChave = 'LIBERADO'
		ORDER BY FOXPrFluxoOperacional DESC";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);

		// buscando os aditivos com situação "LIBERADO" relacionados ao fluxo se encontrado
		if(ISSET($row['FOXPrFluxoOperacional'])){
			$sql = "SELECT AditiId, AditiFluxoOperacional, AditiNumero, AditiDtCelebracao, AditiDtInicio,
			AditiDtFim, AditiValor, AditiStatusFluxo, AditiStatus, AditiUsuarioAtualizador, AditiUnidade, SituaChave
			FROM Aditivo
			JOIN Situacao on AditiStatus = SituaId
			WHERE AditiFluxoOperacional = $row[FOXPrFluxoOperacional]
			AND AditiUnidade = $_SESSION[UnidadeId] AND SituaChave = 'LIBERADO'";
			// var_dump($sql);
			$resultAditivos = $conn->query($sql);
			$rowAditivos = $resultAditivos->fetchAll(PDO::FETCH_ASSOC);

			// agora ele ira buscar AditivoXProduto para somar todos os campos AdXPrQuantidade referentes ao Aditivo

			if(COUNT($rowAditivos) > 0){
				foreach($rowAditivos as $aditivo){
					$sql = "SELECT SUM(AdXPrQuantidade) as quantidade FROM AditivoXProduto
					WHERE AdXPrAditivo = $aditivo[AditiId] AND AdXPrProduto = ".$rows[$x]['ProduId'];
					$resultAditiXProd = $conn->query($sql);
					$rowAditiXProd = $resultAditiXProd->fetch(PDO::FETCH_ASSOC);
					$row['FOXPrQuantidade'] += $rowAditiXProd['quantidade'];
				}

				// adiciona ao campo ProduEstoqueMinimo o valor (já arredondado) de 30% referente ao total de produtos

				$rows[$x]['ProduEstoqueMinimo'] = ceil($row['FOXPrQuantidade']*0.3);

				// verifica se o saldo é menor que o valor em ProduEstoqueMinimo
				
				if($rows[$x]['saldo'] < $rows[$x]['ProduEstoqueMinimo']){
					array_push($rowProduto, $rows[$x]);
				}
			}
		}
	}
}

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
		<div style='float:left; width: 400px; display: inline-block;'>
			<img src='global_assets/images/empresas/".$_SESSION['EmpreFoto']."' style='width:60px; height:60px; float:left; margin-right: 10px; margin-top:-10px;' alt='Logo Empresa' />
			<span style='font-weight:bold;line-height:200px;'>".$_SESSION['EmpreNomeFantasia']."</span><br>
			<div style='position: absolute; font-size:10px; margin-top: 8px; margin-left:4px;'>Unidade: ".$_SESSION['UnidadeNome']."</div>
		</div>
		<div style='width:300px; float:right; display: inline-block; text-align:right; font-size: 10px; margin-top: -50px;'>
			<div style='margin-bottom: 20px'>Data: ".date('d/m/Y')."</div>
			<div style='margin-top:8px;font-weight:bold;'>Estoque Mínimo</div>
		</div> 
	</div>
	 
	<div style='text-align:center; margin-top: 20px;'><h1>RELAÇÃO DOS PRODUTOS EM ESTOQUE MÍNIMO</h1></div> 
	";

	$categoriaVelha = 0;

    foreach ($rowProduto as $item) {

    	$categoriaNova = $item['ProduCategoria'];

    	if ($categoriaNova <> $categoriaVelha){
			
			if($categoriaVelha <> 0){
				$html .= "</table>";
			}
			
			$html .= '<div style="position:relative; margin-top: 20px; text-transform: uppercase; font-weight: bold; background-color: #ccc; padding: 5px;">Categoria: '.$item['CategNome'].'</div>';
					  
			$html .= '
			<br>
			<table style="width:100%; border-collapse: collapse;">
				<tr>
					<th style="text-align: center; width:10%">Código</th>
					<th style="text-align: left; width:62%">Descrição</th>
					<th style="text-align: center; width:18%">Estoque Mínimo</th>
					<th style="text-align: center; width:15%">Estoque Atual</th>
				</tr>
			';							  
		}
	
		$html .= "
				<tr>
					<td style='text-align: center;'>".$item['ProduCodigo']."</td>
					<td style='text-align: left;'>".$item['ProduNome']."</td>
					<td style='text-align: center;'>".$item['ProduEstoqueMinimo']."</td>
					<td style='text-align: center;'>".$item['saldo']."</td>
				</tr>
				";

		$categoriaVelha = $categoriaNova;
    }

    $html .= "</table>";	
    
    $rodape = "<hr/>
    <div style='width:100%;'>
		<div style='width:300px; float:left; display: inline;'>Sistema Lamparinas</div>
		<div style='width:105px; float:right; display: inline;'>Página {PAGENO} / {nbpg}</div> 
	</div>";
    
	//$mpdf->SetHTMLHeader($topo);	
    //$mpdf->SetHTMLHeader($topo,'O',true);
    $mpdf->SetHTMLFooter($rodape); //o SetHTMLFooter deve vir antes do WriteHTML para que o rodapé apareça em todas as páginas
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
