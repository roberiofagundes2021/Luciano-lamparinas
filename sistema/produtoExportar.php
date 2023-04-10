<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Produto Excel';

include('global_assets/php/conexao.php');

$sql = "SELECT ProduCodigo, ProduCodigoBarras, ProduNome, CategNome, SbCatNome, ProduDetalhamento,
		ProduValorCusto, ProduOutrasDespesas, ProduCustoFinal, ProduMargemLucro, ProduValorVenda,
		ProduEstoqueMinimo, UnMedNome, TpFisNome, NcmNome, OrFisNome, ProduCest, SituaNome
		FROM Produto
		LEFT JOIN Categoria on CategId = ProduCategoria
		LEFT JOIN SubCategoria on SbCatId = ProduSubCategoria
		LEFT JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
		LEFT JOIN TipoFiscal on TpFisId = ProduTipoFiscal
		LEFT JOIN Ncm on NcmId = ProduNcmFiscal
		LEFT JOIN OrigemFiscal on OrFisId = ProduOrigemFiscal
		LEFT JOIN Situacao on SituaId = ProduStatus
		WHERE ProduEmpresa = ".$_SESSION['EmpreId']."
		ORDER BY ProduNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);
//	var_dump($count);die;

//declaramos uma variavel para monstarmos a tabela
$dadosXls  = "";
$dadosXls .= "  <table>";
$dadosXls .= "     <tr>";
$dadosXls .= "        <th bgcolor='#cccccc'>Codigo</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>CodigoBarras</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>Nome</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>Categoria</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>SubCategoria</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>Detalhamento</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>ValorCusto</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>OutrasDespesas</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>CustoFinal</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>MargemLucro</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>ValorVenda</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>EstoqueMinimo</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>UnidadeMedida</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>TipoFiscal</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>NcmFiscal</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>OrigemFiscal</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>Cest</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>Situacao</th>";
$dadosXls .= "     </tr>";

//varremos o array com o foreach para pegar os dados
foreach($row as $item){
	$dadosXls .= "   <tr>";
	$dadosXls .= "      <td>".$item['ProduCodigo']."</td>";
	$dadosXls .= "      <td>&nbsp;".$item['ProduCodigoBarras']."</td>";  //Como os números podem ser grandes, o Excel reduzirá o mesmo. Daí colocando espaço em branco antes ele entenderá que é um texto e não um numero
	$dadosXls .= "      <td>".utf8_decode($item['ProduNome'])."</td>";
	$dadosXls .= "      <td>".utf8_decode($item['CategNome'])."</td>";
	$dadosXls .= "      <td>".utf8_decode($item['SbCatNome'])."</td>";
	$dadosXls .= "      <td>".utf8_decode($item['ProduDetalhamento'])."</td>";
	$dadosXls .= "      <td>".mostraValor($item['ProduValorCusto'])."</td>";
	$dadosXls .= "      <td>".mostraValor($item['ProduOutrasDespesas'])."</td>";
	$dadosXls .= "      <td>".mostraValor($item['ProduCustoFinal'])."</td>";
	$dadosXls .= "      <td>".mostraValor($item['ProduMargemLucro'])."</td>";
	$dadosXls .= "      <td>".mostraValor($item['ProduValorVenda'])."</td>";
	$dadosXls .= "      <td>".$item['ProduEstoqueMinimo']."</td>";
	$dadosXls .= "      <td>".utf8_decode($item['UnMedNome'])."</td>";
	$dadosXls .= "      <td>".utf8_decode($item['TpFisNome'])."</td>";
	$dadosXls .= "      <td>".utf8_decode($item['NcmNome'])."</td>";
	$dadosXls .= "      <td>".utf8_decode($item['OrFisNome'])."</td>";
	$dadosXls .= "      <td>".$item['ProduCest']."</td>";
	$dadosXls .= "      <td>".utf8_decode($item['SituaNome'])."</td>";
	$dadosXls .= "   </tr>";
}
$dadosXls .= "  </table>";
 
    // Definimos o nome do arquivo que será exportado  
    $arquivo = "LamparinasProdutos.xls";
     
    // Configurações header para forçar o download  
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	//header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$arquivo.'"');
    header('Cache-Control: max-age=0');
    // Se for o IE9, isso talvez seja necessário
    header('Cache-Control: max-age=1');
    
    header("Content-type: text/html; charset=utf-8");
	
    // Envia o conteúdo do arquivo  
    echo $dadosXls;  
    exit;

?>
