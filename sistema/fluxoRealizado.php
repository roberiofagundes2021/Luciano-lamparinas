<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Fluxo Realizado';

include('global_assets/php/conexao.php');

if (isset($_POST['inputFluxoOperacionalId'])){
	$iUnidade = $_SESSION['UnidadeId'];
	
	$iFluxoOperacional = $_POST['inputFluxoOperacionalId'];
	
	$_SESSION['OrigemFluxoRealizado'] = $_POST['inputOrigem'];


	// essa parte vai montar as opções do select de Fluxo/Aditivo
	$sqlAditivo = "SELECT AditiId,AditiFluxoOperacional,AditiValor,AditiDtCelebracao,AditiDtInicio,
	AditiDtFim,AditiValor,AditiNumero,AditiStatusFluxo,AditiStatus
	FROM Aditivo WHERE AditiFluxoOperacional = $iFluxoOperacional
	AND AditiUnidade = ".$_SESSION['UnidadeId'];
	$resultAditivos = $conn->query($sqlAditivo);
	$rowAditivos = $resultAditivos->fetchAll(PDO::FETCH_ASSOC);
	$contRows = COUNT($rowAditivos);
	
	$aditivoId = "P-$iFluxoOperacional";
	$FluxoAditivoOption = "<option value='P-$iFluxoOperacional' ".($contRows?'':'selected').">Termo Base</option>";

	// essa etapa serve apenas para indicar o ultimo item do array que tenha algo em AditiValor
	$lastItemArray = null;
	foreach($rowAditivos as $Aditivo){
		if($Aditivo['AditiValor']){
			$lastItemArray = $Aditivo['AditiId'];
			//caso tenha aditivos esse id será usado para traze-lo logo que carregar a página
			$aditivoId = "A-$Aditivo[AditiId]";
		}
	}

	// essa parte monta o select de aditivos levando em contas os que devem aparecer desabilitados
	foreach ($rowAditivos as $key => $Aditivo){
		$selected = '';
		$active = $Aditivo['AditiValor'] != null?'':'disabled';

		if(isset($_POST['cmbFluxoAditivo']) && $active != 'disabled'){
			$idFluxoAditivo = explode('-', $_POST['cmbFluxoAditivo'])[1];
			$selected = ($Aditivo['AditiId'] == $idFluxoAditivo)?'selected':'';
		} else {
			$selected = ($Aditivo['AditiId'] == $lastItemArray && $active != 'disabled')?'selected':'';
		}

		$FluxoAditivoOption .= "<option $selected $active value='A-$Aditivo[AditiId]'>$Aditivo[AditiNumero]º Termo Aditivo</option>";
	}

	// Essa parte vai buscar os dados de fluxo Previsto de acordo com as opções selecionadas
	$sql = '';
	$ID = isset($_POST['cmbFluxoAditivo'])?$_POST['cmbFluxoAditivo']:$aditivoId;
	$ID = explode('-', $ID);

	/* Essa parte vai buscar os dados para preencher os campos (Data, Valor, etc...)
	de acordo com o Fluxo/Aditivo selecionado */
	if($ID[0] == 'P'){
		$sql = "SELECT FlOpeId, FlOpeFornecedor, FlOpeCategoria, FlOpeSubCategoria, 
		FlOpeDataInicio, FlOpeDataFim as FimContrato,
		FlOpeNumContrato, FlOpeNumProcesso, FlOpeValor as TotalContrato, 
		FlOpeStatus, ForneRazaoSocial, CategNome 
		FROM FluxoOperacional
		JOIN Fornecedor ON ForneId = FlOpeFornecedor
		JOIN Categoria ON CategId = FlOpeCategoria	 
		WHERE FlOpeUnidade = ". $_SESSION['UnidadeId'] ." and FlOpeId = ".$ID[1];
	} else {
		$sql = "SELECT AditiId, AditiFluxoOperacional,AditiDtCelebracao,AditiNumero,AditiStatusFluxo,
		AditiDtInicio as FlOpeDataInicio,
		AditiDtFim as FimContrato,
		AditiValor as TotalContrato,
		FlOpeId, FlOpeFornecedor, FlOpeCategoria, FlOpeSubCategoria,
		FlOpeNumContrato, FlOpeNumProcesso, 
		FlOpeStatus, ForneRazaoSocial, CategNome,
		AditiStatus
		FROM Aditivo
		JOIN FluxoOperacional ON FlOpeId = AditiFluxoOperacional
		JOIN Fornecedor ON ForneId = FlOpeFornecedor
		JOIN Categoria ON CategId = FlOpeCategoria
		WHERE AditiUnidade = ".$_SESSION['UnidadeId']." and AditiId = ".$ID[1];
	}
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);

	// Essa parte vai buscar os dados de fluxo Previsto de acordo com as opções selecionadas
	if($ID[0] == 'P'){

		$sql = 	"SELECT ProduId as Id, ProduNome as Nome, FOXPrDetalhamento as Detalhamento, 
		UnMedSigla as UnidadeMedida, FOXPrQuantidade as Quantidade, FOXPrValorUnitario as ValorUnitario, 
		MarcaNome as Marca, SbCatNome as SubCategoria
		FROM Produto
		JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId
		JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
		LEFT JOIN ProdutoXFabricante on PrXFaProduto = ProduId and PrXFaUnidade = $iUnidade  and PrXFaFluxoOperacional = $iFluxoOperacional
		LEFT JOIN Marca on MarcaId = PrXFaMarca
		LEFT JOIN SubCategoria on SbCatId = ProduSubCategoria
		WHERE ProduEmpresa = ". $_SESSION['EmpreId'] ." and FOXPrFluxoOperacional = ".$ID[1];

		// filtrar de acordo com as subCategorias marcados
		if(isset($_POST['cmbFornecedor'])){
			if(isset($_POST['cmbSubCategoria'])){
				$sql .= " and ProduSubCategoria in (";
				foreach($_POST['cmbSubCategoria'] as $value){
					$sql .= $value.',';
				}
				$sql = substr($sql, 0, -1).')';
			}
			if(isset($_POST['cmbProduto'])){
				if(!$_POST['inputFluxoAditivo']){
					$sql .= " and ProduId in (";
					foreach($_POST['cmbProduto'] as $value){
						$sql .= $value.',';
					}
					$sql = substr($sql, 0, -1).')';
				}
			}else{
				if(isset($_POST['inputFluxoAditivo'])){
					$sql .= $_POST['inputFluxoAditivo']?"":" and ProduId = NULL ";
				} else {
					$sql .= " and ProduId = NULL ";
				}
			}
		}

		$sql .= " UNION
		SELECT ServiId as Id, ServiNome as Nome, FOXSrDetalhamento as Detalhamento, 
		'' as UnidadeMedida, FOXSrQuantidade as Quantidade, FOXSrValorUnitario as ValorUnitario, MarcaNome as Marca, SbCatNome as SubCategoria
		FROM Servico
		JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
		LEFT JOIN ServicoXFabricante on SrXFaServico = ServiId and SrXFaUnidade = $iUnidade and SrXFaFluxoOperacional = $iFluxoOperacional
		LEFT JOIN Marca on MarcaId = SrXFaMarca
		LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
		WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and FOXSrFluxoOperacional = ".$ID[1];

		// filtrar de acordo com os produtos marcados
		if(isset($_POST['cmbFornecedor'])){
			if(isset($_POST['cmbSubCategoria'])){
				$sql .= " and ServiSubCategoria in (";
				foreach($_POST['cmbSubCategoria'] as $value){
					$sql .= $value.',';
				}
				$sql = substr($sql, 0, -1).')';
			}
			if(isset($_POST['cmbProduto'])){
				$sql .= " and ServiId in (";
				foreach($_POST['cmbProduto'] as $value){
					$sql .= $value.',';
				}
				$sql = substr($sql, 0, -1).')';
			}else{
				if(isset($_POST['inputFluxoAditivo'])){
					$sql .= $_POST['inputFluxoAditivo']?"":" and ServiId = NULL ";
				} else {
					$sql .= " and ServiId = NULL ";
				}
			}
		}
	} else {
		$sql = 	"SELECT ProduId as Id, ProduNome as Nome, AdXPrDetalhamento as Detalhamento, 
		UnMedSigla as UnidadeMedida, AdXPrQuantidade as Quantidade, AdXPrValorUnitario as ValorUnitario, 
		MarcaNome as Marca, SbCatNome as SubCategoria
		FROM Produto
		JOIN AditivoXProduto on AdXPrProduto = ProduId
		JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
		LEFT JOIN ProdutoXFabricante on PrXFaProduto = ProduId and PrXFaUnidade = $iUnidade  and PrXFaFluxoOperacional = $iFluxoOperacional
		LEFT JOIN Marca on MarcaId = PrXFaMarca
		LEFT JOIN SubCategoria on SbCatId = ProduSubCategoria
		WHERE ProduEmpresa = ". $_SESSION['EmpreId'] ." and AdXPrAditivo = ".$ID[1];
		// exit;

		// filtrar de acordo com as subCategorias marcados
		if(isset($_POST['cmbFornecedor'])){
			if(isset($_POST['cmbSubCategoria'])){
				$sql .= " and ProduSubCategoria in (";
				foreach($_POST['cmbSubCategoria'] as $value){
					$sql .= $value.',';
				}
				$sql = substr($sql, 0, -1).')';
			}
			if(isset($_POST['cmbProduto'])){
				if(!$_POST['inputFluxoAditivo']){
					$sql .= " and ProduId in (";
					foreach($_POST['cmbProduto'] as $value){
						$sql .= $value.',';
					}
					$sql = substr($sql, 0, -1).')';
				}
			}else{
				if(isset($_POST['inputFluxoAditivo'])){
					$sql .= $_POST['inputFluxoAditivo']?"":" and ProduId = NULL ";
				} else {
					$sql .= " and ProduId = NULL ";
				}
			}
		}

		$sql .= " UNION
		SELECT ServiId as Id, ServiNome as Nome, AdXSrDetalhamento as Detalhamento, 
		'' as UnidadeMedida, AdXSrQuantidade as Quantidade, AdXSrValorUnitario as ValorUnitario, MarcaNome as Marca, SbCatNome as SubCategoria
		FROM Servico
		JOIN AditivoXServico on AdXSrServico = ServiId
		LEFT JOIN ServicoXFabricante on SrXFaServico = ServiId and SrXFaUnidade = $iUnidade and SrXFaFluxoOperacional = $iFluxoOperacional
		LEFT JOIN Marca on MarcaId = SrXFaMarca
		LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
		WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and AdXSrAditivo = ".$ID[1];

		// filtrar de acordo com os produtos marcados
		if(isset($_POST['cmbFornecedor'])){
			if(isset($_POST['cmbSubCategoria'])){
				$sql .= " and ServiSubCategoria in (";
				foreach($_POST['cmbSubCategoria'] as $value){
					$sql .= $value.',';
				}
				$sql = substr($sql, 0, -1).')';
			}
			if(isset($_POST['cmbProduto'])){
				if(!$_POST['inputFluxoAditivo']){
					$sql .= " and ServiId in (";
					foreach($_POST['cmbProduto'] as $value){
						$sql .= $value.',';
					}
					$sql = substr($sql, 0, -1).')';
				}
			}else{
				if(isset($_POST['inputFluxoAditivo'])){
					$sql .= $_POST['inputFluxoAditivo']?"":" and ServiId = NULL ";
				} else {
					$sql .= " and ServiId = NULL ";
				}
			}
		}
	}
			
	$sql .= " ORDER BY SubCategoria, Nome ASC";
	$result = $conn->query($sql);
	$rowPrevisto = $result->fetchAll(PDO::FETCH_ASSOC);

	// Essa parte vai buscar os dados de fluxo Realizado de acordo com as opções selecionadas
	if($ID[0] == 'P'){
		$sql = 	"SELECT ProduId as Id, ProduNome as Nome, FOXPrDetalhamento as Detalhamento, 
		UnMedSigla as UnidadeMedida, FOXPrQuantidade as Quantidade, FOXPrValorUnitario as ValorUnitario, 
		MarcaNome as Marca, SbCatNome as SubCategoria
		FROM Produto
		JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId
		JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
		LEFT JOIN ProdutoXFabricante on PrXFaProduto = ProduId and PrXFaUnidade = $iUnidade  and PrXFaFluxoOperacional = $iFluxoOperacional
		LEFT JOIN Marca on MarcaId = PrXFaMarca
		LEFT JOIN SubCategoria on SbCatId = ProduSubCategoria
		WHERE ProduEmpresa = ". $_SESSION['EmpreId'] ." and FOXPrFluxoOperacional = ".$ID[1];

		// filtrar de acordo com as subCategorias marcados
		if(isset($_POST['cmbFornecedor'])){
			if(isset($_POST['cmbSubCategoria'])){
				$sql .= " and ProduSubCategoria in (";
				foreach($_POST['cmbSubCategoria'] as $value){
					$sql .= $value.',';
				}
				$sql = substr($sql, 0, -1).')';
			}
			if(isset($_POST['cmbProduto'])){
				if(!$_POST['inputFluxoAditivo']){
					$sql .= " and ProduId in (";
					foreach($_POST['cmbProduto'] as $value){
						$sql .= $value.',';
					}
					$sql = substr($sql, 0, -1).')';
				}
			}else{
				if(isset($_POST['inputFluxoAditivo'])){
					$sql .= $_POST['inputFluxoAditivo']?"":" and ProduId = NULL ";
				} else {
					$sql .= " and ProduId = NULL ";
				}
			}
		}

		$sql .= " UNION
		SELECT ServiId as Id, ServiNome as Nome, FOXSrDetalhamento as Detalhamento, 
		'' as UnidadeMedida, FOXSrQuantidade as Quantidade, FOXSrValorUnitario as ValorUnitario, MarcaNome as Marca, SbCatNome as SubCategoria
		FROM Servico
		JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
		LEFT JOIN ServicoXFabricante on SrXFaServico = ServiId and SrXFaUnidade = $iUnidade and SrXFaFluxoOperacional = $iFluxoOperacional 
		LEFT JOIN Marca on MarcaId = SrXFaMarca
		LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
		WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and FOXSrFluxoOperacional = ".$ID[1];

		// filtrar de acordo com os produtos marcados
		if(isset($_POST['cmbFornecedor'])){
			if(isset($_POST['cmbSubCategoria'])){
				$sql .= " and ServiSubCategoria in (";
				foreach($_POST['cmbSubCategoria'] as $value){
					$sql .= $value.',';
				}
				$sql = substr($sql, 0, -1).')';
			}
			if(isset($_POST['cmbProduto'])){
				if(!$_POST['inputFluxoAditivo']){
					$sql .= " and ServiId in (";
					foreach($_POST['cmbProduto'] as $value){
						$sql .= $value.',';
					}
					$sql = substr($sql, 0, -1).')';
				}
			}else{
				if(isset($_POST['inputFluxoAditivo'])){
					$sql .= $_POST['inputFluxoAditivo']?"":" and ServiId = NULL ";
				} else {
					$sql .= " and ServiId = NULL ";
				}
			}
		}
	} else {
		$sql = 	"SELECT ProduId as Id, ProduNome as Nome, AdXPrDetalhamento as Detalhamento, 
		UnMedSigla as UnidadeMedida, AdXPrQuantidade as Quantidade, AdXPrValorUnitario as ValorUnitario, 
		MarcaNome as Marca, SbCatNome as SubCategoria
		FROM Produto
		JOIN AditivoXProduto on AdXPrProduto = ProduId
		JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
		LEFT JOIN ProdutoXFabricante on PrXFaProduto = ProduId and PrXFaUnidade = $iUnidade  and PrXFaFluxoOperacional = $iFluxoOperacional
		LEFT JOIN Marca on MarcaId = PrXFaMarca
		LEFT JOIN SubCategoria on SbCatId = ProduSubCategoria
		WHERE ProduEmpresa = ". $_SESSION['EmpreId'] ." and AdXPrAditivo = ".$ID[1];

		// filtrar de acordo com as subCategorias marcados
		if(isset($_POST['cmbFornecedor'])){
			if(isset($_POST['cmbSubCategoria'])){
				$sql .= " and ProduSubCategoria in (";
				foreach($_POST['cmbSubCategoria'] as $value){
					$sql .= $value.',';
				}
				$sql = substr($sql, 0, -1).')';
			}
			if(isset($_POST['cmbProduto'])){
				if(!$_POST['inputFluxoAditivo']){
					$sql .= " and ProduId in (";
					foreach($_POST['cmbProduto'] as $value){
						$sql .= $value.',';
					}
					$sql = substr($sql, 0, -1).')';
				}
			}else{
				if(isset($_POST['inputFluxoAditivo'])){
					$sql .= $_POST['inputFluxoAditivo']?"":" and ProduId = NULL ";
				} else {
					$sql .= " and ProduId = NULL ";
				}
			}
		}

		$sql .= " UNION
		SELECT ServiId as Id, ServiNome as Nome, AdXSrDetalhamento as Detalhamento, 
		'' as UnidadeMedida, AdXSrQuantidade as Quantidade, AdXSrValorUnitario as ValorUnitario, MarcaNome as Marca, SbCatNome as SubCategoria
		FROM Servico
		JOIN AditivoXServico on AdXSrServico = ServiId
		LEFT JOIN ServicoXFabricante on SrXFaServico = ServiId and SrXFaUnidade = $iUnidade and SrXFaFluxoOperacional = $iFluxoOperacional 
		LEFT JOIN Marca on MarcaId = SrXFaMarca
		LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
		WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and AdXSrAditivo = ".$ID[1];

		// filtrar de acordo com os produtos marcados
		if(isset($_POST['cmbFornecedor'])){
			if(isset($_POST['cmbSubCategoria'])){
				$sql .= " and ServiSubCategoria in (";
				foreach($_POST['cmbSubCategoria'] as $value){
					$sql .= $value.',';
				}
				$sql = substr($sql, 0, -1).')';
			}
			if(isset($_POST['cmbProduto'])){
				if(!$_POST['inputFluxoAditivo']){
					$sql .= " and ServiId in (";
					foreach($_POST['cmbProduto'] as $value){
						$sql .= $value.',';
					}
					$sql = substr($sql, 0, -1).')';
				}
			}else{
				if(isset($_POST['inputFluxoAditivo'])){
					$sql .= $_POST['inputFluxoAditivo']?"":" and ProduId = NULL ";
				} else {
					$sql .= " and ServiId = NULL ";
				}
			}
		}
	}

	$sql .= " ORDER BY SubCategoria, Nome ASC";
	$result = $conn->query($sql);
	$rowRealizado = $result->fetchAll(PDO::FETCH_ASSOC);
	$cont = 0;

	// essa parte monta as opções do filtro de itens a serem mostrados em Produto/Serviço de acordo com o termo selecionado
	if($ID[0] == 'P'){
		$sqlFilter = "SELECT ProduId as Id, ProduNome as Nome, FOXPrDetalhamento as Detalhamento, 
		UnMedSigla as UnidadeMedida, FOXPrQuantidade as Quantidade, FOXPrValorUnitario as ValorUnitario, 
		MarcaNome as Marca, SbCatNome as SubCategoria
		FROM Produto
		JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId
		JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
		LEFT JOIN ProdutoXFabricante on PrXFaProduto = ProduId and PrXFaUnidade = $iUnidade  and PrXFaFluxoOperacional = $iFluxoOperacional
		LEFT JOIN Marca on MarcaId = PrXFaMarca
		LEFT JOIN SubCategoria on SbCatId = ProduSubCategoria
		WHERE ProduEmpresa = ". $_SESSION['EmpreId'] ." and FOXPrFluxoOperacional = ".$ID[1].
		" UNION
		SELECT ServiId as Id, ServiNome as Nome, FOXSrDetalhamento as Detalhamento, 
		'' as UnidadeMedida, FOXSrQuantidade as Quantidade, FOXSrValorUnitario as ValorUnitario, MarcaNome as Marca, SbCatNome as SubCategoria
		FROM Servico
		JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
		LEFT JOIN ServicoXFabricante on SrXFaServico = ServiId and SrXFaUnidade = $iUnidade and SrXFaFluxoOperacional = $iFluxoOperacional 
		LEFT JOIN Marca on MarcaId = SrXFaMarca
		LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
		WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and FOXSrFluxoOperacional = ".$ID[1];
	} else {
		$sqlFilter = "SELECT ProduId as Id, ProduNome as Nome, AdXPrDetalhamento as Detalhamento, 
		UnMedSigla as UnidadeMedida, AdXPrQuantidade as Quantidade, AdXPrValorUnitario as ValorUnitario, 
		MarcaNome as Marca, SbCatNome as SubCategoria
		FROM Produto
		JOIN AditivoXProduto on AdXPrProduto = ProduId
		JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
		LEFT JOIN ProdutoXFabricante on PrXFaProduto = ProduId and PrXFaUnidade = $iUnidade  and PrXFaFluxoOperacional = $iFluxoOperacional
		LEFT JOIN Marca on MarcaId = PrXFaMarca
		LEFT JOIN SubCategoria on SbCatId = ProduSubCategoria
		WHERE ProduEmpresa = ". $_SESSION['EmpreId'] ." and AdXPrAditivo = ".$ID[1].
		" UNION
		SELECT ServiId as Id, ServiNome as Nome, AdXSrDetalhamento as Detalhamento, 
		'' as UnidadeMedida, AdXSrQuantidade as Quantidade, AdXSrValorUnitario as ValorUnitario, MarcaNome as Marca, SbCatNome as SubCategoria
		FROM Servico
		JOIN AditivoXServico on AdXSrServico = ServiId
		LEFT JOIN ServicoXFabricante on SrXFaServico = ServiId and SrXFaUnidade = $iUnidade and SrXFaFluxoOperacional = $iFluxoOperacional 
		LEFT JOIN Marca on MarcaId = SrXFaMarca
		LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
		WHERE ServiEmpresa = ".$_SESSION['EmpreId']." and AdXSrAditivo = ".$ID[1];
	}

	$resultFilter = $conn->query($sqlFilter);
	$rowFilter = $resultFilter->fetchAll(PDO::FETCH_ASSOC);
	$optionsFilter = '';
	foreach ($rowFilter as $item){
		if(isset($_POST['cmbProduto'])){
			if(!$_POST['inputFluxoAditivo']){
				$selected = in_array($item['Id'], $_POST['cmbProduto'])?'selected':'';
				$optionsFilter .= ("<option value='".$item['Id']."' $selected>".$item['Nome']."</option>");
			} else {
				$optionsFilter .= ("<option value='".$item['Id']."' selected>".$item['Nome']."</option>");	
			}
		}else{
			$optionsFilter .= ("<option value='".$item['Id']."' selected>".$item['Nome']."</option>");	
		}
	}
} else {
	irpara($_SESSION['OrigemFluxoRealizado']); //Isso aqui é pensando no caso do usuário der um refresh nessa página. Um ENTER, por exemplo na URL.
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Fluxo Realizado</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files --> 
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>

	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>	

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>		
	
	<!-- /theme JS files -->
	<script type="text/javascript">
		
		$(document).ready(function() {
			$('#cmbFluxoAditivo').on('change', function() {
				$('#inputFluxoAditivo').val($(this).val());
				console.log($('#inputFluxoAditivo').val())
			})
			
			//Ao marcar ou desmarcar os Produtos/Serviços, filtra a lista via ajax (retorno via JSON)
			/*$('#cmbProduto').on('change', function(e){
				
				let produtos 		  = $(this).val();
				let fluxoId 		  = $('#inputFluxoOperacionalId').val();
				let cont 			  = 1;
				let produtoId 		  = [];
				let produtoQuant 	  = [];

				// Aqui é para cada "class" faça
				$.each($(".idProduto"), function() {
					produtoId[cont] = $(this).val();
					cont++;
				});

				cont = 1;
				//aqui fazer um for que vai até o ultimo cont (dando cont++ dentro do for)
				$.each($(".Quantidade"), function() {
					$id 							= produtoId[cont];
					produtoQuant[$id] = $(this).val();
					cont++;
				});

				$.ajax({
					type: "POST",
					url: "trFiltraProduto.php",
					data: {
						iFluxo: fluxoId,
						produtos: produtos,
						produtoId: produtoId,
						produtoQuant: produtoQuant
					},
					success: function(resposta) {
						$("#tabelaProdutos").html(resposta).show();
						return false;
					}
				});
			});*/

		});
	</script>
</head>

<body class="navbar-top  sidebar-xs">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php include_once("menu-left.php"); ?>

		<!-- Main content -->
		<div class="content-wrapper">

			<?php include_once("cabecalho.php"); ?>	

			<!-- Content area -->
			<div class="content">

				<!-- Info blocks -->		
				<div class="row">
					<div class="col-lg-12">
						<!-- Basic responsive configuration -->
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title">Fluxo Operacional</h3>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
									</div>
								</div>
							</div>

							<div class="card-body">
								<form name="formFluxoOperacional" method="post" action="fluxoRealizado.php">

									<input type="hidden" id="inputFluxoOperacionalId" name="inputFluxoOperacionalId" value="<?php echo $_POST['inputFluxoOperacionalId']; ?>" />
									<input type="hidden" id="inputOrigem" name="inputOrigem" value="<?php echo $_POST['inputOrigem']; ?>" />
									<input type="hidden" id="inputFluxoAditivo" name="inputFluxoAditivo" value="" />

									<div class="row">
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputNumContrato">Nº Contrato</label>
												<input type="text" id="inputNumContrato" name="inputNumContrato" class="form-control text-danger" placeholder="Nº do Contrato" value="<?php echo $row['FlOpeNumContrato']; ?>" readOnly >
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputNumProcesso">Nº Processo</label>
												<input type="text" id="inputNumProcesso" name="inputNumProcesso" class="form-control" placeholder="Nº do Processo" value="<?php echo $row['FlOpeNumProcesso']; ?>" readOnly>
											</div>
										</div>
										
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputDataInicio">Data Início <span class="text-danger">*</span></label>
												<div class="input-group">
													<span class="input-group-prepend">
														<span class="input-group-text"><i class="icon-calendar22"></i></span>
													</span>
													<input type="date" id="inputDataInicio" name="inputDataInicio" class="form-control" placeholder="Data Início" value="<?php echo $row['FlOpeDataInicio']; ?>" readOnly >
												</div>
											</div>
										</div>
										
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputDataFim">Data Fim <span class="text-danger">*</span></label>
												<div class="input-group">
													<span class="input-group-prepend">
														<span class="input-group-text"><i class="icon-calendar22"></i></span>
													</span>
													<input type="date" id="inputDataFim" name="inputDataFim" class="form-control" placeholder="Data Fim" value="<?php echo $row['FimContrato']; ?>" readOnly >
												</div>
											</div>
										</div>											
										
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputValor">Valor Total</label>
												<input type="text" id="inputValor" name="inputValor" class="form-control" value="<?php echo mostraValor($row['TotalContrato']); ?>" readOnly>
											</div>
										</div>										
									</div>

									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbFornecedor">Fornecedor</label>
												<input type="text" id="cmbFornecedor" name="cmbFornecedor" class="form-control"  value="<?php echo $row['ForneRazaoSocial']; ?>" readOnly >
											</div>
										</div>
										
										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbCategoria">Categoria</label>
												<input type="hidden" id="inputCategoria" name="inputCategoria" value="<?php echo $row['FlOpeCategoria']; ?>" >
												<input type="text" id="cmbCategoria" name="cmbCategoria" class="form-control"  value="<?php echo $row['CategNome']; ?>" readOnly >
											</div>
										</div>
										
										<div class="col-lg-4">
											<label for="cmbSubCategoria">SubCategoria(s)</label>
											<select id="cmbSubCategoria" name="cmbSubCategoria[]" class="form-control multiselect-filtering" multiple="multiple" data-fouc>
												<?php
													$sql = "SELECT SbCatId, SbCatNome
															FROM SubCategoria
															JOIN Situacao on SituaId = SbCatStatus
															WHERE SbCatEmpresa = $_SESSION[EmpreId] AND 
															SbCatId in (SELECT ProduSubCategoria  FROM Produto
															JOIN FluxoOperacionalXProduto on FOXPrProduto =  ProduId
															WHERE ProduEmpresa  = $_SESSION[EmpreId] and FOXPrFluxoOperacional = $iFluxoOperacional) ORDER BY SbCatNome ASC"; 
													$result = $conn->query($sql);
													$rowBD = $result->fetchAll(PDO::FETCH_ASSOC);
													$count = count($rowBD);
															
													foreach ($rowBD as $item){
														if(isset($_POST['cmbFornecedor'])){
															if(isset($_POST['cmbSubCategoria'])){
																if(in_array($item['SbCatId'], $_POST['cmbSubCategoria'])){
																	print('<option value="'.$item['SbCatId'].'" selected>'.$item['SbCatNome'].'</option>');	
																}else{
																	print('<option value="'.$item['SbCatId'].'">'.$item['SbCatNome'].'</option>');	
																}
															}else{
																print('<option value="'.$item['SbCatId'].'">'.$item['SbCatNome'].'</option>');	
															}
														}else{
															print('<option value="'.$item['SbCatId'].'" selected>'.$item['SbCatNome'].'</option>');	
														}
													}
												?>
											</select>
										</div>	
									</div>
			
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbFluxoAditivo">Fluxo/Aditivo</label>
												<select id="cmbFluxoAditivo" name="cmbFluxoAditivo" class="form-control select select2-hidden-accessible multiselect-filtering" data-fouc>
													<?php
														echo $FluxoAditivoOption;
													?>
												</select>
											</div>	
										</div>

										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbProduto">Produto/Serviço</label>
												<select id="cmbProduto" name="cmbProduto[]" class="form-control multiselect-filtering" multiple="multiple" data-fouc >
													<?php 
														echo $optionsFilter;
													?>
												</select>

												<!--<input type="hidden" name="inputSelecionados" value="<?php //isset($aSelecionados) ? var_dump($aSelecionados) : ""; ?>">-->
											</div>
										</div>
									</div>
									
									<div class="col-lg-12">	
											<div class="text-right">
												<a href="contrato.php" class="btn btn-basic" role="button"><< Fluxo Operacional/Contrato</a>
												<button type="submit" class="btn btn-principal">Filtrar</button>
											</div>
									</div>
								</form>
							</div>
						</div>
						<!-- /basic responsive configuration -->
					</div>
				</div>								
				<!-- /info blocks -->

				<!-- Info blocks -->		
				<div class="row">
					<div class="col-lg-12">
						<!-- Basic responsive configuration -->
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title">Fluxo Previsto</h3>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
									</div>
								</div>
							</div>

							<div class="card-body">								
								<table class="table" id="tblFluxo">
									<thead>
										<tr class="bg-slate">
											<th width="4%">Item</th>
											<th width="26%">Produto/Serviço</th>
											<th width="15%">Marca</th>
											<th width="9%" style="text-align:center;">Unidade</th>
											<th width="9%" style="text-align:center;">Quant.</th>									
											<th width="9%" style="text-align:right;">Valor Unit.</th>										
											<th width="9%" style="text-align:right;">Valor Total</th>
											<th width="7%" style="background-color: #ccc; color:#333; text-align:right;">Saldo (Qt)</th>
											<th width="7%" style="background-color: #ccc; color:#333; text-align:right;">Saldo (R$)</th>
											<th width="5%" style="background-color: #ccc; color:#333; text-align:center;">%</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$cont = 1;
											foreach ($rowPrevisto as $item){

												$iQuantidadePrevista = isset($item['Quantidade']) ? $item['Quantidade'] : '';
												$fValorUnitarioPrevisto = isset($item['ValorUnitario']) ? mostraValor($item['ValorUnitario']) : '';											
												$fValorTotalPrevisto = (isset($item['Quantidade']) and isset($item['ValorUnitario'])) ? $item['Quantidade'] * $item['ValorUnitario'] : '';


												$sql = "SELECT ISNULL(SUM(MvXPrQuantidade), 0) as Controle, MvXPrValorUnitario
														FROM Movimentacao														
														JOIN MovimentacaoXProduto on MvXPrMovimentacao = MovimId
														WHERE MovimUnidade = ".$_SESSION['UnidadeId']." and MvXPrProduto = ".$item['Id']." and 
														MovimData between '".$row['FlOpeDataInicio']."' and '".$row['FimContrato']."' and MovimTipo = 'E' 
														GROUP By MvXPrQuantidade, MvXPrValorUnitario";
												$result = $conn->query($sql);
												$rowMovimentacao = $result->fetch(PDO::FETCH_ASSOC);

												$iQuantidadeRealizada = isset($rowMovimentacao['Controle']) ? $rowMovimentacao['Controle'] : 0;
												$fValorUnitarioRealizado = isset($rowMovimentacao['MvXPrValorUnitario']) ? mostraValor($rowMovimentacao['MvXPrValorUnitario']) : 0;						
												$fValorTotalRealizado = (isset($iQuantidadeRealizada) and isset($rowMovimentacao['MvXPrValorUnitario'])) ? $iQuantidadeRealizada * $rowMovimentacao['MvXPrValorUnitario'] : 0;

												$controle = $iQuantidadePrevista - $iQuantidadeRealizada;
												$saldo = mostraValor($fValorTotalPrevisto - $fValorTotalRealizado);
												$porcentagem = $controle * 100 / $iQuantidadePrevista;

												print('
												<tr>
													<td>'.$cont.'</td>
													<td data-popup="tooltip" title="'.$item['Detalhamento'].'">'.$item['Nome'].'</td>													
													<td>'.$item['Marca'].'</td>
													<td style="text-align:center;">'.$item['UnidadeMedida'].'</td>
													<td style="text-align:center;">'.$iQuantidadePrevista.'</td>
													<td style="text-align:right;">'.$fValorUnitarioPrevisto.'</td>											
													<td style="text-align:right;">'.mostraValor($fValorTotalPrevisto).'</td>
													<td style="background-color: #eee; color:#333; text-align:center;">'.$controle.'</td>
													<td style="background-color: #eee; color:#333; text-align:right;">'.$saldo.'</td>
													<td style="background-color: #eee; color:#333; text-align:center;">'.mostraValor($porcentagem).'%</td>
												</tr>');

												$cont++;
											}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>				
				<!-- /info blocks -->

				<!-- Info blocks -->		
				<div class="row">
					<div class="col-lg-12">
						<!-- Basic responsive configuration -->
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title">Fluxo Realizado</h3>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
									</div>
								</div>
							</div>

							<div class="card-body">								
								<table class="table" id="tblFluxo">
									<thead>
										<tr class="bg-slate">
											<th width="4%">Item</th>
											<th width="26%">Produto/Serviço</th>
											<th width="15%">Marca</th>
											<th width="9%" style="text-align:center;">Unidade</th>
											<th width="9%" style="text-align:center;">Quant.</th>									
											<th width="9%" style="text-align:right;">Valor Unit.</th>										
											<th width="9%" style="text-align:right;">Valor Total</th>
											<th width="7%" style="background-color: #ccc; color:#333; text-align:right;">Total (Qt)</th>
											<th width="7%" style="background-color: #ccc; color:#333; text-align:right;">Total (R$)</th>
											<th width="5%" style="background-color: #ccc; color:#333; text-align:center;">%</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$cont = 1;

											foreach ($rowRealizado as $item){

												$iQuantidadePrevista = isset($item['Quantidade']) ? $item['Quantidade'] : 0;
												$fValorUnitarioPrevisto = isset($item['ValorUnitario']) ? mostraValor($item['ValorUnitario']) : '0.00';
												$fValorTotalPrevisto = (isset($item['Quantidade']) and isset($item['ValorUnitario'])) ? $item['Quantidade'] * $item['ValorUnitario']: 0.00;

												$sql = "SELECT ISNULL(SUM(MvXPrQuantidade), 0) as Controle, MvXPrValorUnitario
														FROM Movimentacao														
														JOIN MovimentacaoXProduto on MvXPrMovimentacao = MovimId
														WHERE MovimUnidade = ".$_SESSION['UnidadeId']." and MvXPrProduto = ".$item['Id']." and MovimData between '".$row['FlOpeDataInicio']."' and '".$row['FimContrato']."' and MovimTipo = 'E' 
														GROUP By MvXPrQuantidade, MvXPrValorUnitario";
												$result = $conn->query($sql);
												$rowMovimentacao = $result->fetch(PDO::FETCH_ASSOC);

												$iQuantidadeRealizada = isset($rowMovimentacao['Controle'])? $rowMovimentacao['Controle'] : 0;
												$fValorUnitarioRealizado = isset($rowMovimentacao['MvXPrValorUnitario']) ? mostraValor($rowMovimentacao['MvXPrValorUnitario']) : 0;						
												$fValorTotalRealizado = (isset($iQuantidadeRealizada) and isset($rowMovimentacao['MvXPrValorUnitario'])) ? $iQuantidadeRealizada * $rowMovimentacao['MvXPrValorUnitario'] : 0;

												$controle = $iQuantidadePrevista - $iQuantidadeRealizada;
												$saldo = mostraValor($fValorTotalPrevisto - $fValorTotalRealizado); 
												$porcentagem = $iQuantidadeRealizada * 100 / $iQuantidadePrevista;

												print('
												<tr>
													<td>'.$cont.'</td>
													<td data-popup="tooltip" title="'.$item['Detalhamento'].'">'.$item['Nome'].'</td>
													<td>'.$item['Marca'].'</td>
													<td style="text-align:center;">'.$item['UnidadeMedida'].'</td>
													<td style="text-align:center;">'.$iQuantidadeRealizada.'</td>
													<td style="text-align:right;">'.$fValorUnitarioRealizado.'</td>
													<td style="text-align:right;">'.mostraValor($fValorTotalRealizado).'</td>
													<td style="background-color: #eee; color:#333; text-align:center;">'.$iQuantidadeRealizada.'</td>
													<td style="background-color: #eee; color:#333; text-align:right;">'.mostraValor($fValorTotalRealizado).'</td>
													<td style="background-color: #eee; color:#333; text-align:center;">'.mostraValor($porcentagem).'%</td>
												</tr>');

												$cont++;

											}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>				
				<!-- /info blocks -->				


			</div>
			<!-- /content area -->
			
			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>

	<?php /* $total1 = microtime(true) - $inicio1;
		 echo '<span style="background-color:yellow; padding: 10px; font-size:24px;">Tempo de execução do script: ' . round($total1, 2).' segundos</span>'; */ ?>

</body>

</html>
