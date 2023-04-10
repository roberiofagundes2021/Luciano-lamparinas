<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Ordem de Compra Produto';

include('global_assets/php/conexao.php');

//Se veio do ordemcompra.php
if(isset($_POST['inputOrdemCompraId'])){
	$iOrdemCompra = $_POST['inputOrdemCompraId'];
	$iFluxo = $_POST['inputOrdemCompraFlOpeId'];
	$iCategoria = $_POST['inputOrdemCompraCategoria'];
	$iStatus = $_POST['inputOrdemCompraStatus'];
} else if (isset($_POST['inputIdOrdemCompra'])){
	$iOrdemCompra = $_POST['inputIdOrdemCompra'];
	$iFluxo = $_POST['inputOrdemCompraFlOpeId'];
	$iCategoria = $_POST['inputIdCategoria'];
	$iStatus = $_POST['inputOrdemCompraStatus'];
} else {
	irpara("ordemcompra.php");
}

$sql = "SELECT *
		FROM OrdemCompra
		JOIN Fornecedor on ForneId = OrComFornecedor
		JOIN Categoria on CategId = OrComCategoria
		LEFT JOIN SubCategoria on SbCatId = OrComSubCategoria
		WHERE OrComUnidade = ". $_SESSION['UnidadeId'] ." and OrComId = ".$iOrdemCompra;
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT SituaChave
		FROM Situacao
	    WHERE SituaId = '". $iStatus."'";
$result = $conn->query($sql);
$rowStatus = $result->fetch(PDO::FETCH_ASSOC);
$sStatus = $rowStatus['SituaChave'];

//Se está alterando
if (isset($_POST['inputIdOrdemCompra'])){
	
	$valid = true;

	if($_POST['inputTypeRequest'] != "reset"){
		for ($i = 1; $i <= $_POST['totalRegistros']; $i++){
			$sqlSaldo = "SELECT OCXPrOrdemCompra, OCXPrQuantidade,
			dbo.fnSaldoOrdemCompra($_SESSION[UnidadeId], '$iFluxo', ".$_POST['inputIdProduto'.$i].", 'P') as Saldo
			FROM OrdemCompraXProduto WHERE OCXPrOrdemCompra = '$iOrdemCompra' and OCXPrProduto = ".$_POST['inputIdProduto'.$i];
			$resultSaldo = $conn->query($sqlSaldo);
			$OCXPr = $resultSaldo->fetch(PDO::FETCH_ASSOC);
			if(!isset($OCXPr['Saldo'])){
				$sqlSaldo = "SELECT dbo.fnSaldoOrdemCompra($_SESSION[UnidadeId], '$iFluxo', ".$_POST['inputIdProduto'.$i].", 'P') as Saldo";
				$resultSaldo = $conn->query($sqlSaldo);
				$OCXPr = $resultSaldo->fetch(PDO::FETCH_ASSOC);
			}

			$saldo = isset($OCXPr['Saldo'])?intval($OCXPr['Saldo']):0;
			$ordComQuant = isset($OCXPr['OCXPrQuantidade'])?intval($OCXPr['OCXPrQuantidade']):0;
			$quant = isset($_POST['inputQuantidade'.$i])?intval($_POST['inputQuantidade'.$i]):0;

			$saldo = $saldo != null?$saldo:0;
			$ordComQuant = $ordComQuant != null?$ordComQuant:0;
			$quant = $quant != null?$quant:0;

			if($quant > $saldo+$ordComQuant){
				$valid = false;
			}
		}
	}

	if ($valid){

		$sql = "DELETE FROM OrdemCompraXProduto
				WHERE OCXPrOrdemCompra = :iOrdemCompra AND OCXPrUnidade = :iUnidade";
		$result = $conn->prepare($sql);
		
		$result->execute(array(
						':iOrdemCompra' => $iOrdemCompra,
						':iUnidade' => $_SESSION['UnidadeId']
						));

		// essa etapa vai buscar o Saldo Remanescente da ordem de compra
		$sqlOrCom = "SELECT OrComId, OrComSaldoRemanescente, OrComFluxoOperacional FROM OrdemCompra
		WHERE OrComId = $iOrdemCompra";
		$resultOrCom = $conn->query($sqlOrCom);
		$rowOrCom = $resultOrCom->fetch(PDO::FETCH_ASSOC);

		$sReferencia = 'P';

		/* Caso exista Saldo Remanescente deve ser verificado a existencia de um aditivo caso exista
		deve ser utilisado o "AditiNumero" do PENULTIMO aditivo*/
		if($rowOrCom['OrComSaldoRemanescente'] == 1){
			$sqlAdt = "SELECT (MAX(AditiNumero)-1) as AditiNumero FROM Aditivo
			WHERE AditiValor is not null and AditiUnidade = ".$_SESSION['UnidadeId']." and AditiFluxoOperacional = ".$rowOrCom['OrComFluxoOperacional'];
			$resultAdt = $conn->query($sqlAdt);
			$rowAdt = $resultAdt->fetch(PDO::FETCH_ASSOC);

			/* Caso encontre o aditivo sera setado na variavel $sReferencia o numero do aditivo ex.: "A1", "A2"
			caso contrario sera setado em $sReferencia o valor "P" de principal*/
			if($rowAdt['AditiNumero'] != null){
				$sReferencia = 'A'.$rowAdt['AditiNumero'];
			}
		} else {
			/* Caso NÃO exista Saldo Remanescente deve ser verificado a existencia de um aditivo caso exista
			deve ser utilisado o "AditiNumero" do ULTIMO aditivo*/
			$sqlAdt = "SELECT MAX(AditiNumero) as AditiNumero FROM Aditivo
			WHERE AditiValor is not null and AditiUnidade = ".$_SESSION['UnidadeId']." and AditiFluxoOperacional = ".$rowOrCom['OrComFluxoOperacional'];
			$resultAdt = $conn->query($sqlAdt);
			$rowAdt = $resultAdt->fetch(PDO::FETCH_ASSOC);

			/* Caso encontre o aditivo sera setado na variavel $sReferencia o numero do aditivo ex.: "A1", "A2"
			caso contrario sera setado em $sReferencia o valor "P" de principal*/
			if($rowAdt['AditiNumero'] != null){
				$sReferencia = 'A'.$rowAdt['AditiNumero'];
			}
		}
		
		for ($i = 1; $i <= $_POST['totalRegistros']; $i++) {
			// verificar se o saldo ainda é valido antes de inserir no banco os valores
			$sqlSaldo = "SELECT dbo.fnSaldoOrdemCompra($_SESSION[UnidadeId], '$iOrdemCompra', ".$_POST['inputIdProduto'.$i].", 'P') as Saldo";
			$resultSaldo = $conn->query($sqlSaldo);
			$saldo = $resultSaldo->fetch(PDO::FETCH_ASSOC);

			$saldo = $saldo['Saldo'];
			$quantidade = $_POST['inputQuantidade'.$i];
			
			$sql = "INSERT INTO OrdemCompraXProduto (OCXPrOrdemCompra, OCXPrProduto, OCXPrDetalhamento, OCXPrQuantidade, OCXPrValorUnitario, OCXPrUsuarioAtualizador, OCXPrUnidade, OCXPrReferencia)
					VALUES (:iOrdemCompra, :iProduto, :sDetalhamento, :iQuantidade, :fValorUnitario, :iUsuarioAtualizador, :iUnidade, :sReferencia)";
			$result = $conn->prepare($sql);
			
			$result->execute(array(
							':iOrdemCompra' => $iOrdemCompra,
							':iProduto' => $_POST['inputIdProduto'.$i],
							':sDetalhamento' => $_POST['inputDetalhamento'.$i],
							':iQuantidade' => $_POST['inputQuantidade'.$i] == '' ? null : $_POST['inputQuantidade'.$i],
							':fValorUnitario' => $_POST['inputValorUnitario'.$i] == '' ? null : gravaValor($_POST['inputValorUnitario'.$i]),
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidade' => $_SESSION['UnidadeId'],
							':sReferencia' => $sReferencia
							));
		}

		$tipo = $row['OrComTipo'] == 'C' ? 'Carta Contrato' : 'Ordem de Compra';							

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = $tipo." alterada!!!";
		$_SESSION['msg']['tipo'] = "success";

	}else{
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "O saldo de um dos produtos não está mais disponível!!!";
		$_SESSION['msg']['tipo'] = "error";
	}
}	

	
$sql = "SELECT OCXPrProduto
		FROM OrdemCompraXProduto
		JOIN Produto on ProduId = OCXPrProduto
		WHERE OCXPrUnidade = ". $_SESSION['UnidadeId'] ." and ProduCategoria = ".$iCategoria." and OCXPrOrdemCompra = ".$row['OrComId']."";

if (isset($row['OrComSubCategoria']) and $row['OrComSubCategoria'] != '' and $row['OrComSubCategoria'] != null){
	$sql .= " and ProduSubCategoria = ".$row['OrComSubCategoria'];
}	
$result = $conn->query($sql);
$rowProdutoUtilizado = $result->fetchAll(PDO::FETCH_ASSOC);
$countProdutoUtilizado = count($rowProdutoUtilizado);

foreach ($rowProdutoUtilizado as $itemProdutoUtilizado){
	$aProdutos[] = $itemProdutoUtilizado['OCXPrProduto'];
}

$sql = "SELECT COUNT(OCXPrProduto) as Quant
		FROM OrdemCompraXProduto
		WHERE OCXPrUnidade = ". $_SESSION['UnidadeId'] ." and OCXPrOrdemCompra = ".$iOrdemCompra." and 
		OCXPrQuantidade <> '' and OCXPrQuantidade <> 0 and OCXPrValorUnitario <> 0.00 ";
$result = $conn->query($sql);
$rowCompleto = $result->fetch(PDO::FETCH_ASSOC);

$enviar = 0;

//Verifica se o número de produtos é igual ao número de produtos com a quantidade e valor unitário preenchido para habilitar o botào "Enviar"
if ($countProdutoUtilizado == $rowCompleto['Quant'] && ($countProdutoUtilizado != 0 && $rowCompleto['Quant'] != 0)){
	$enviar = 1;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Listando produtos da Ordem de Compra</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>	

	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>
	<!-- /theme JS files -->
	
	<!-- Adicionando Javascript -->
	<script type="text/javascript" >

		$(document).ready(function() {

			function pular() {
				
				$('.pula').keypress(function(e){
					/*
						* verifica se o evento é Keycode (para IE e outros browsers)
						* se não for pega o evento Which (Firefox)
					*/
					var tecla = (e.keyCode?e.keyCode:e.which);

					/* verifica se a tecla pressionada foi o ENTER */
					if(tecla == 13){
						/* guarda o seletor do campo que foi pressionado Enter */
						campo =  $('.pula');
						/* pega o indice do elemento*/
						indice = campo.index(this);
						/*soma mais um ao indice e verifica se não é null
						*se não for é porque existe outro elemento
						*/
						if(campo[indice+1] != null){
							/* adiciona mais 1 no valor do indice */
							proximo = campo[indice + 1];
							/* passa o foco para o proximo elemento */
							proximo.focus();
						}
					} else {
						return onlynumber(e);
					}

					/* impede o sumbit caso esteja dentro de um form */
					e.preventDefault(e);
					return false;
				});
			}

			pular();

			//Ao mudar a SubCategoria, filtra o produto via ajax (retorno via JSON)
			$('#cmbProduto').on('change', function(e){
				
				var inputCategoria = $('#inputIdCategoria').val();
				var inputSubCategoria = $('#inputIdSubCategoria').val(); //alert(inputSubCategoria);
				var produtos = $(this).val();
				<?php 
					echo "var iFluxoOp = ".json_encode($iFluxo)."\n";
					echo "var iOrdemCompra = ".json_encode($iOrdemCompra)."\n";
				?>
				//console.log(produtos);
				
				var cont = 1;
				var produtoId = [];
				var produtoQuant = [];
				var produtoValor = [];
				
				// Aqui é para cada "class" faça
				$.each( $(".idProduto"), function() {
					produtoId[cont] = $(this).val();
					cont++;
				});
				
				cont = 1;
				//aqui fazer um for que vai até o ultimo cont (dando cont++ dentro do for)
				$.each( $(".Quantidade"), function() {
					$id = produtoId[cont];
					produtoQuant[$id] = $(this).val();
					cont++;
				});				
				
				cont = 1;
				$.each( $(".ValorUnitario"), function() {
					$id = produtoId[cont];
					produtoValor[$id] = $(this).val();
					cont++;
				});
				
				$.ajax({
					type: "POST",
					url: "ordemcompraFiltraProduto.php",
					data: {idCategoria: inputCategoria, idSubCategoria: inputSubCategoria, produtos: produtos, produtoId: produtoId, produtoQuant: produtoQuant, produtoValor: produtoValor, iFluxoOp: iFluxoOp, iOrdemCompra: iOrdemCompra},
					success: function(resposta){
						//alert(resposta);
						$("#tabelaProdutos").html(resposta).show();

						pular();
						
						return false;
						
					}	
				});
			});

			//Enviar para aprovação do Centro Administrativo (via Bandeja)
			// $('#enviar').on('click', function(e){
				
			// 	e.preventDefault();
				
			// 	confirmaExclusao(document.formOrdemCompraProduto, "Essa ação enviará toda a Ordem de Compra (com seus produtos e serviços) para aprovação do Centro Administrativo. Tem certeza que deseja enviar?", "ordemcompraEnviar.php");
			// });
						
		}); //document.ready
		
		//Mostra o "Filtrando..." na combo Produto
		function FiltraProduto(){
			$('#cmbProduto').empty().append('<option>Filtrando...</option>');
		}
		
		function ResetProduto(){
			$('#cmbProduto').empty().append('<option>Sem produto</option>');
		}

		function reset(id, type, status){
			console.log(status)
			if(status != 'LIBERADOCONTABILIDADE'){
				if (type == "all"){
					var array = [];
					for(var x=1; x<=id; x++){
						array.push("inputQuantidade"+x)
					}
					confirmaReset(document.formOrdemCompraProduto, "Tem certeza que deseja resetar TODAS as quantidades ?", "ordemcompraProduto.php", array);
				}else{
					confirmaReset(document.formOrdemCompraProduto, "Tem certeza que deseja resetar essa quantidade ?", "ordemcompraProduto.php", id);
				}
			} else {
				alerta('Atenção','O produto não pode ser resetado pois já passou pelo processo de aprovação.','error');
			}
		}
		
		function calculaValorTotal(id){
			
			var ValorTotalAnterior = $('#inputValorTotal'+id+'').val() == '' ? 0 : $('#inputValorTotal'+id+'').val().replaceAll('.', '').replace(',', '.');
			var TotalGeralAnterior = $('#inputTotalGeral').val().replaceAll('.', '').replace(',', '.');
			
			var Quantidade = $('#inputQuantidade'+id+'').val().trim() == '' ? 0 : $('#inputQuantidade'+id+'').val();
			var ValorUnitario = $('#inputValorUnitario'+id+'').val() == '' ? 0 : $('#inputValorUnitario'+id+'').val().replaceAll('.', '').replace(',', '.');
			var ValorTotal = 0;
			
			var ValorTotal = parseFloat(Quantidade) * parseFloat(ValorUnitario);
			var TotalGeral = float2moeda(parseFloat(TotalGeralAnterior) - parseFloat(ValorTotalAnterior) + ValorTotal).toString();
			
			ValorTotal = float2moeda(ValorTotal).toString();
			
			$('#inputValorTotal'+id+'').val(ValorTotal);
			
			$('#inputTotalGeral').val(TotalGeral);			
		}
		function validaQuantInputModal(quantMax,quant,obj) {
			$('#'+obj.id).on('keyup', function() {
					if (parseInt($('#'+obj.id).val()) > (parseInt(quantMax)+parseInt(quant))) {
						$('#'+obj.id).val(parseInt(quantMax)+parseInt(quant))
					}
				})
		}

	</script>

</head>

<body class="navbar-top">

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
				<div class="card">
					
					<form name="formOrdemCompraProduto" id="formOrdemCompraProduto" method="post" class="form-validate">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Listar Produtos - <?php echo $row['OrComTipo'] == 'C' ? 'Carta Contrato' : 'Ordem de Compra'; ?>  Nº "<?php echo $row['OrComNumero']; ?>"</h5>
						</div>
						<input type="hidden" id="inputTypeRequest" name="inputTypeRequest" value="submit">
						<input type="hidden" id="inputIdOrdemCompra" name="inputIdOrdemCompra" value="<?php echo $row['OrComId']; ?>">
						<input type="hidden" id="inputOrdemCompraFlOpeId" name="inputOrdemCompraFlOpeId" value="<?php echo $iFluxo; ?>">
						<input type="hidden" id="inputOrdemCompraStatus" name="inputOrdemCompraStatus" value="<?php echo $iStatus; ?>">
						
						<div class="card-body">
								
							<div class="row">
								<div class="col-lg-12">
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputFornecedor">Fornecedor</label>
												<input type="text" id="inputFornecedor" name="inputFornecedor" class="form-control" value="<?php echo $row['ForneNome']; ?>" readOnly>
												<input type="hidden" id="inputIdFornecedor" name="inputIdFornecedor" class="form-control" value="<?php echo $row['ForneId']; ?>">
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputCelular">Telefone</label>
												<input type="text" id="inputTelefone" name="inputTelefone" class="form-control" value="<?php echo $row['ForneTelefone']; ?>" readOnly>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputTelefone">Celular</label>
												<input type="text" id="inputCelular" name="inputCelular" class="form-control" value="<?php echo $row['ForneCelular']; ?>" readOnly>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputCategoriaNome">Categoria</label>
												<input type="text" id="inputCategoriaNome" name="inputCategoriaNome" class="form-control" value="<?php echo $row['CategNome']; ?>" readOnly>
												<input type="hidden" id="inputIdCategoria" name="inputIdCategoria" class="form-control" value="<?php echo $row['OrComCategoria']; ?>">
											</div>
										</div>
									
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputSubCategoria">Sub Categoria</label>
												<input type="text" id="inputSubCategoriaNome" name="inputSubCategoriaNome" class="form-control" value="<?php echo $row['SbCatNome']; ?>" readOnly>
												<input type="hidden" id="inputIdSubCategoria" name="inputIdSubCategoria" class="form-control" value="<?php echo $row['OrComSubCategoria']; ?>">
											</div>
										</div>
									</div>
									<div class="row">	
										<div class="col-lg-12">
											<div class="form-group">
												<label for="cmbProduto">Produto</label>
												<select id="cmbProduto" name="cmbProduto" class="form-control multiselect-filtering" multiple="multiple" data-fouc>
													<?php
														$sql = "SELECT ProduId, ProduNome
																FROM Produto
																JOIN Situacao on SituaId = ProduStatus
																JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId and FOXPrFluxoOperacional = '$iFluxo'
																WHERE ProduEmpresa = $_SESSION[EmpreId] and SituaChave = 'ATIVO' and
																ProduCategoria = '$iCategoria'";
														if (isset($row['OrComSubCategoria']) and $row['OrComSubCategoria'] != '' and $row['OrComSubCategoria'] != null){
															$sql .= " and ProduSubCategoria = ".$row['OrComSubCategoria'];
														}
														$sql .= " ORDER BY ProduNome ASC";
														$result = $conn->query($sql);
														$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC);
														foreach ($rowProduto as $item){
															
															if (in_array($item['ProduId'], $aProdutos) or $countProdutoUtilizado == 0) {
																$seleciona = "selected";
															} else {
																$seleciona = "";
															}
															
															print('<option value="'.$item['ProduId'].'" '.$seleciona.'>'.$item['ProduNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
							
							<!-- Custom header text -->
							<div class="card">
								<div class="card-header header-elements-inline">
									<h5 class="card-title">Relação de Produtos</h5>
									<div class="header-elements">
										<div class="list-icons">
											<a class="list-icons-item" data-action="collapse"></a>
											<a class="list-icons-item" data-action="reload"></a>
											<a class="list-icons-item" data-action="remove"></a>
										</div>
									</div>
								</div>

								<div class="card-body">
									<p class="mb-3">Abaixo estão listados todos os produtos da Categoria e SubCategoria selecionadas logo acima. Para atualizar os valores, basta preencher a coluna <code>Quantidade</code> e depois clicar em <b>ALTERAR</b>.</p>

									<!--<div class="hot-container">
										<div id="example"></div>
									</div>-->
									
									<?php
										$sql = "SELECT ProduId, ProduNome, OCXPrDetalhamento as Detalhamento, UnMedSigla, OCXPrQuantidade, FOXPrValorUnitario,
													dbo.fnSaldoOrdemCompra($_SESSION[UnidadeId], '$iFluxo', ProduId, 'P') as SaldoOrdemCompra
													FROM Produto
													JOIN Situacao on SituaId = ProduStatus
													JOIN OrdemCompraXProduto on OCXPrProduto = ProduId and OCXPrOrdemCompra = '$iOrdemCompra'
													JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
													JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId and FOXPrFluxoOperacional = '$iFluxo'
													WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'";
										if (isset($row['OrComSubCategoria']) and $row['OrComSubCategoria'] != '' and $row['OrComSubCategoria'] != null){
											$sql .= " and ProduSubCategoria = ".$row['OrComSubCategoria'];
										}
										$sql = $sql." ORDER BY ProduNome";
										$result = $conn->query($sql);
										$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
										$count = count($rowProdutos);
										if(!$count>0){
											$sql = "SELECT ProduId, ProduNome, FOXPrDetalhamento as Detalhamento, UnMedSigla, FOXPrValorUnitario,
													dbo.fnSaldoOrdemCompra($_SESSION[UnidadeId], '$iFluxo', ProduId, 'P') as SaldoOrdemCompra
													FROM Produto
													JOIN Situacao on SituaId = ProduStatus
													JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
													JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId and FOXPrFluxoOperacional = '$iFluxo'
													WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'";
											if (isset($row['OrComSubCategoria']) and $row['OrComSubCategoria'] != '' and $row['OrComSubCategoria'] != null){
												$sql .= " and ProduSubCategoria = ".$row['OrComSubCategoria'];
											}
											$sql = $sql." ORDER BY ProduNome";
											$result = $conn->query($sql);
											$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
										}
										$cont = 0;
										
										print('
										<div class="row" style="margin-bottom: -20px;">
											<div class="col-lg-5">
												<div class="row">
													<div class="col-lg-2" style="max-width:60px">
														<label for="inputCodigo"><strong>Item</strong></label>
													</div>
													<div class="col-lg-10" style="width:100%">
														<label for="inputProduto"><strong>Produto</strong></label>
													</div>
												</div>
											</div>
											<div class="col-lg-1">
												<div class="form-group">
													<label for="inputUnidade"><strong>Unidade</strong></label>
												</div>
											</div>
											<div class="col-lg-1">
												<div class="form-group">
													<label for="inputSaldo"><strong>Saldo</strong></label>
												</div>
											</div>
											<div class="col-lg-1">
												<div class="form-group">
													<label for="inputQuantidade"><strong>Quantidade</strong></label>
												</div>
											</div>	
											<div class="col-lg-1">
												<div class="form-group">
													<label for="inputValorUnitario" title="Valor Unitário"><strong>Valor Unit.</strong></label>
												</div>
											</div>	
											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputValorTotal"><strong>Valor Total</strong></label>
												</div>
											</div>
											<div class="col-sm-1">
												<div class="col-sm-12" style="text-align:center;">
													<label for=""><strong>Resetar</strong></label>
												</div>
											</div>
										</div>');
										
										print('<div id="tabelaProdutos">');
										
										$fTotalGeral = 0;
										$disable = $sStatus == 'LIBERADOCONTABILIDADE'?'readOnly':'';
										$class = $sStatus == 'LIBERADOCONTABILIDADE'?'form-control-border-off Quantidade text-right pula'
										:'form-control-border Quantidade text-right pula';
										
										foreach ($rowProdutos as $item){
											$cont++;
											$saldo = isset($item['SaldoOrdemCompra']) ? $item['SaldoOrdemCompra'] : 0;
											$iQuantidade = isset($item['OCXPrQuantidade']) ? $item['OCXPrQuantidade'] : 0;
											$fValorUnitario = isset($item['FOXPrValorUnitario']) ? mostraValor($item['FOXPrValorUnitario']) : 0;											
											$fValorTotal = mostraValor(intval($iQuantidade)*gravaValor($fValorUnitario));
											
											$fTotalGeral += gravaValor($fValorTotal);
											
											print("
											<div class='row' style='margin-top: 8px;'>
												<div class='col-lg-5'>
													<div class='row'>
														<div class='col-lg-2' style='max-width:60px'>
															<input type='text' id='inputItem$cont' name='inputItem$cont' class='form-control-border-off' value='$cont' readOnly>
															<input type='hidden' id='inputIdProduto$cont' name='inputIdProduto$cont' value='$item[ProduId]' class='idProduto'>
														</div>
														<div class='col-lg-10' style='width:100%'>
															<input type='text' id='inputProduto$cont' name='inputProduto$cont' class='form-control-border-off' data-popup='tooltip' title='$item[Detalhamento]' value='$item[ProduNome]' readOnly>
															<input type='hidden' id='inputDetalhamento$cont' name='inputDetalhamento$cont' value='$item[Detalhamento]'>
														</div>
													</div>
												</div>								
												<div class='col-lg-1'>
													<input type='text' id='inputUnidade$cont' name='inputUnidade$cont' class='form-control-border-off' value='$item[UnMedSigla]' readOnly>
												</div>
												<div class='col-lg-1'>
													<input type='text' id='inputSaldo$cont' readOnly name='Saldo$cont' class='form-control-border-off text-right' value='$saldo'>
												</div>
												<div class='col-lg-1'>
													<input type='text' $disable class='$class' id='inputQuantidade$cont' name='inputQuantidade$cont' onChange='calculaValorTotal($cont)' onkeypress='return onlynumber(), validaQuantInputModal($saldo,$iQuantidade,this)' value='$iQuantidade'>
												</div>		
												<div class='col-lg-1'>
													<input readOnly type='text' id='inputValorUnitario$cont' name='inputValorUnitario$cont' class='form-control-border-off ValorUnitario text-right' onChange='calculaValorTotal($cont)' onKeyUp='moeda(this)' maxLength='12' value='$fValorUnitario'>
												</div>	
												<div class='col-lg-2'>
													<input type='text' id='inputValorTotal$cont' name='inputValorTotal$cont' class='form-control-border-off text-right' value='$fValorTotal' readOnly>
												</div>
												<div class='col-sm-1 btn' style='text-align:center;' onClick='reset(`inputQuantidade$cont`, 0, `$sStatus`)'>
													<i class='icon-reset' title='Resetar'></i>
												</div>
											</div>");
										}
										
										print('
										<div class="row" style="margin-top: 8px;">
												<div class="col-lg-5">
													<div class="row">
														<div class="col-lg-1">
															
														</div>
														<div class="col-lg-8">
															
														</div>
														<div class="col-lg-3">
															
														</div>
													</div>
												</div>								
												<div class="col-lg-1">
													
												</div>
												<div class="col-lg-1">
													
												</div>	
												<div class="col-lg-2" style="padding-top: 5px; text-align: right;">
													<h5><b>Total:</b></h5>
												</div>	
												<div class="col-lg-2">
													<input type="text" id="inputTotalGeral" name="inputTotalGeral" class="form-control-border-off text-right" value="'.mostraValor($fTotalGeral).'" readOnly>
												</div>
												<div class="col-sm-1 btn" style="text-align:center;" onClick="reset('.count($rowProdutos).', `all`, `'.$sStatus.'`)">
													<i class="icon-reset" title="Resetar Todos"></i>
												</div>
											</div>'										
										);
										
										
										print('<input type="hidden" id="totalRegistros" name="totalRegistros" value="'.$cont.'" >');
										
										print('</div>');									
										
									?>
									
								</div>
							</div>
							<!-- /custom header text -->
							
															
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										
										<?php
										if ( $sStatus!= 'LIBERADOCONTABILIDADE') {
											echo '<button class="btn btn-lg btn-principal" type="submit">Alterar</button>';
										}
										?>
										<?php
											// if ($enviar){
											// 	print('<button class="btn btn-lg btn-default" id="enviar">Enviar para Aprovação</button>');
											// }
										?>
										<a href="ordemcompra.php" class="btn btn-basic" role="button">Cancelar</a>
									</div>
								</div>
							</div>
						</div>
						<!-- /card-body -->
					</form>
					
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

</body>
</html>
