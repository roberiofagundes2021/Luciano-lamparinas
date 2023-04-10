<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Serviço Excel';

include('global_assets/php/conexao.php');

$sql = "SELECT ServiCodigo, ServiNome, CategNome, SbCatNome, ServiDetalhamento, ServiValorCusto, 
		ServiOutrasDespesas, ServiCustoFinal, ServiMargemLucro, ServiValorVenda, SituaNome
		FROM Servico
		LEFT JOIN Categoria on CategId = ServiCategoria
		LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
		LEFT JOIN Situacao on SituaId = ServiStatus
		WHERE ServiEmpresa = ".$_SESSION['EmpreId']."
		ORDER BY ServiNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);
//var_dump($count);die;

//declaramos uma variavel para monstarmos a tabela
$dadosXls  = "";
$dadosXls .= "  <table>";
$dadosXls .= "     <tr>";
$dadosXls .= "        <th bgcolor='#cccccc'>Codigo</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>Nome</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>Categoria</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>SubCategoria</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>Detalhamento</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>ValorCusto</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>OutrasDespesas</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>CustoFinal</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>MargemLucro</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>ValorVenda</th>";
$dadosXls .= "        <th bgcolor='#cccccc'>Situacao</th>";
$dadosXls .= "     </tr>";

//varremos o array com o foreach para pegar os dados
foreach($row as $item){
	$dadosXls .= "   <tr>";
	$dadosXls .= "      <td>".$item['ServiCodigo']."</td>";
	$dadosXls .= "      <td>".utf8_decode($item['ServiNome'])."</td>";
	$dadosXls .= "      <td>".utf8_decode($item['CategNome'])."</td>";
	$dadosXls .= "      <td>".utf8_decode($item['SbCatNome'])."</td>";
	$dadosXls .= "      <td>".utf8_decode($item['ServiDetalhamento'])."</td>";
	$dadosXls .= "      <td>".mostraValor($item['ServiValorCusto'])."</td>";
	$dadosXls .= "      <td>".mostraValor($item['ServiOutrasDespesas'])."</td>";
	$dadosXls .= "      <td>".mostraValor($item['ServiCustoFinal'])."</td>";
	$dadosXls .= "      <td>".mostraValor($item['ServiMargemLucro'])."</td>";
	$dadosXls .= "      <td>".mostraValor($item['ServiValorVenda'])."</td>";
	$dadosXls .= "      <td>".utf8_decode($item['SituaNome'])."</td>";
	$dadosXls .= "   </tr>";
}
$dadosXls .= "  </table>";
 
    // Definimos o nome do arquivo que será exportado  
    $arquivo = "LamparinasServicos.xls"; 
         
    // Configurações header para forçar o download  
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$arquivo.'"');
    header('Cache-Control: max-age=0');
    // Se for o IE9, isso talvez seja necessário
    header('Cache-Control: max-age=1');
    
    header("Content-type: text/html; charset=utf-8");
       
    // Envia o conteúdo do arquivo  
    echo $dadosXls;  
    exit;

?>