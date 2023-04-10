<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Orçamento Produto';

include('global_assets/php/conexao.php');

//Se veio do orcamento.php
if(isset($_POST['inputOrcamentoId'])){
	$iOrcamento = $_POST['inputOrcamentoId'];
	$iCategoria = $_POST['inputOrcamentoCategoria'];
} else if (isset($_POST['inputIdOrcamento'])){
	$iOrcamento = $_POST['inputIdOrcamento'];
	$iCategoria = $_POST['inputIdCategoria'];
} else {
	irpara("orcamento.php");
}

//Se está alterando
if(isset($_POST['inputIdOrcamento'])){
	
	$sql = "DELETE FROM OrcamentoXProduto
			WHERE OrXPrOrcamento = :iOrcamento AND OrXPrUnidade = :iUnidade";
	$result = $conn->prepare($sql);
	
	$result->execute(array(
					':iOrcamento' => $iOrcamento,
					':iUnidade' => $_SESSION['UnidadeId']
					));		
	
	for ($i = 1; $i <= $_POST['totalRegistros']; $i++) {
	
		$sql = "INSERT INTO OrcamentoXProduto (OrXPrOrcamento, OrXPrProduto, OrXPrQuantidade, OrXPrValorUnitario, OrXPrUsuarioAtualizador, OrXPrUnidade)
				VALUES (:iOrcamento, :iProduto, :iQuantidade, :fValorUnitario, :iUsuarioAtualizador, :iUnidade)";
		$result = $conn->prepare($sql);
		
		$result->execute(array(
						':iOrcamento' => $iOrcamento,
						':iProduto' => $_POST['inputIdProduto'.$i],
						':iQuantidade' => $_POST['inputQuantidade'.$i] == '' ? null : $_POST['inputQuantidade'.$i],
						':fValorUnitario' => $_POST['inputValorUnitario'.$i] == '' ? null : gravaValor($_POST['inputValorUnitario'.$i]),
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iUnidade' => $_SESSION['UnidadeId']
						));
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Orçamento alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
	}
}	

try{
	
	$sql = "SELECT *
			FROM Orcamento
			LEFT JOIN Fornecedor on ForneId = OrcamFornecedor
			JOIN Categoria on CategId = OrcamCategoria
			WHERE OrcamUnidade = ". $_SESSION['UnidadeId'] ." and OrcamId = $iOrcamento ";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);
	
	
	$sql = "SELECT OrXPrProduto
			FROM OrcamentoXProduto
			JOIN Produto on ProduId = OrXPrProduto
			WHERE OrXPrUnidade = ". $_SESSION['UnidadeId'] ." and OrXPrOrcamento = ".$iOrcamento;	
	$result = $conn->query($sql);
	$rowProdutoUtilizado = $result->fetchAll(PDO::FETCH_ASSOC);
	$countProdutoUtilizado = count($rowProdutoUtilizado);
	
	foreach ($rowProdutoUtilizado as $itemProdutoUtilizado){
		$aProdutos[] = $itemProdutoUtilizado['OrXPrProduto'];
	}

	$sql = "SELECT SbCatId, SbCatNome
			FROM SubCategoria
			JOIN OrcamentoXSubCategoria on OrXSCSubCategoria = SbCatId
			WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ." and OrXSCOrcamento = $iOrcamento
			ORDER BY SbCatNome ASC";
	$result = $conn->query($sql);
	$rowBD = $result->fetchAll(PDO::FETCH_ASSOC);
	
	$aSubCategorias = '';

	foreach ($rowBD as $item) {
		
		if ($aSubCategorias == '') {
			$aSubCategorias .= $item['SbCatId'];
		} else {
			$aSubCategorias .= ", ".$item['SbCatId'];
		}
	}

	//echo $aSubCategorias; die;
	
} catch(PDOException $e) {
	echo 'Error: ' . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Listando produtos do Orçamento</title>

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

			//Ao mudar a SubCategoria, filtra o produto via ajax (retorno via JSON)
			$('#cmbProduto').on('change', function(e){
				
				var inputCategoria = $('#inputIdCategoria').val();
				var inputSubCategoria = $('#inputIdSubCategoria').val(); //alert(inputSubCategoria);
				var produtos = $(this).val();
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
					url: "orcamentoFiltraProduto.php",
					data: {idCategoria: inputCategoria, idSubCategoria: inputSubCategoria, produtos: produtos, produtoId: produtoId, produtoQuant: produtoQuant, produtoValor: produtoValor},
					success: function(resposta){
						//alert(resposta);

						$("#tabelaProdutos").html(resposta).show();					
						return false;						
					}	
				});
			});

			/* ao pressionar uma tecla em um campo que seja de class="pula" */
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
						
		}); //document.ready
		
		//Mostra o "Filtrando..." na combo Produto
		function FiltraProduto(){
			$('#cmbProduto').empty().append('<option>Filtrando...</option>');
		}
		
		function ResetProduto(){
			$('#cmbProduto').empty().append('<option>Sem produto</option>');
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
					
					<form name="formOrcamentoProduto" id="formOrcamentoProduto" method="post" class="form-validate">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Listar Produtos - Orçamento Nº "<?php echo $row['OrcamNumero']; ?>"</h5>
						</div>					
						
						<input type="hidden" id="inputIdOrcamento" name="inputIdOrcamento" class="form-control" value="<?php echo $row['OrcamId']; ?>">
						
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
												<input type="hidden" id="inputIdCategoria" name="inputIdCategoria" class="form-control" value="<?php echo $row['OrcamCategoria']; ?>">
											</div>
										</div>
									
										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbSubCategoria">SubCategoria(s)</label>
												<select id="cmbSubCategoria" name="cmbSubCategoria" class="form-control multiselect-filtering" multiple="multiple" data-fouc>
													<?php 
														$sql = "SELECT SbCatId, SbCatNome
																FROM SubCategoria
																JOIN Situacao on SituaId = SbCatStatus	
																WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ." and SbCatId in (".$aSubCategorias.")
																ORDER BY SbCatNome ASC";
														$result = $conn->query($sql);
														$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
														$count = count($rowSubCategoria);														
																
														foreach ( $rowSubCategoria as $item){	
															print('<option value="'.$item['SbCatId,'].'"disabled selected>'.$item['SbCatNome'].'</option>');	
														}                    
													?>
												</select>
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
																WHERE ProduEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO' and ProduCategoria = ".$iCategoria;
														
														if ($aSubCategorias != ""){
															$sql .= " and ProduSubCategoria in (".$aSubCategorias.") ";
														}
														
														$sql .= " ORDER BY ProduNome ASC";
														$result = $conn->query($sql);
														$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC);														
														
														foreach ($rowProduto as $item){	
															
															if (in_array($item['ProduId'], $aProdutos) or $countProdutoUtilizado == 0) {
																$seleciona = "selected";
																print('<option value="'.$item['ProduId'].'" '.$seleciona.'>'.$item['ProduNome'].'</option>');
															} else {
																$seleciona = "";
																print('<option value="'.$item['ProduId'].'" '.$seleciona.'>'.$item['ProduNome'].'</option>');
															}													
															
															
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
									<p class="mb-3">Abaixo estão listados todos os produtos da Categoria e SubCategoria selecionadas logo acima. Para atualizar os valores, basta preencher as colunas <code>Quantidade</code> e <code>Valor Unitário</code> e depois clicar em <b>ALTERAR</b>.</p>
								
									<?php									

										$sql = "SELECT ProduId, ProduNome, ProduDetalhamento, UnMedSigla, OrXPrQuantidade, OrXPrValorUnitario
												FROM Produto
												JOIN OrcamentoXProduto on OrXPrProduto = ProduId
												JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
												WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and OrXPrOrcamento = ".$iOrcamento;
										$result = $conn->query($sql);
										$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
										$count = count($rowProdutos);
										
										if (!$count){
											$sql = "SELECT ProduId, ProduNome, ProduDetalhamento, UnMedSigla
													FROM Produto
													JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
													JOIN Situacao on SituaId = ProduStatus
													WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and ProduCategoria = ".$iCategoria." and SituaChave = 'ATIVO' ";
													
											if ($aSubCategorias != ""){
												$sql .= " and ProduSubCategoria in (".$aSubCategorias.") ";
											}
											$sql .= " ORDER BY ProduNome ASC";
											$result = $conn->query($sql);
											$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
										} 
										
										$cont = 0;
										
										print('
										<div class="row" style="margin-bottom: -20px;">
											<div class="col-lg-8">
													<div class="row">
														<div class="col-lg-1">
															<label for="inputCodigo"><strong>Item</strong></label>
														</div>
														<div class="col-lg-11">
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
													<label for="inputQuantidade"><strong>Quantidade</strong></label>
												</div>
											</div>	
											<div class="col-lg-1">
												<div class="form-group">
													<label for="inputValorUnitario" title="Valor Unitário"><strong>Valor Unit.</strong></label>
												</div>
											</div>	
											<div class="col-lg-1">
												<div class="form-group">
													<label for="inputValorTotal"><strong>Valor Total</strong></label>
												</div>
											</div>											
										</div>');
										
										print('<div id="tabelaProdutos">');
										
										$fTotalGeral = 0;
										
										foreach ($rowProdutos as $item){
											
											$cont++;
											
											$iQuantidade = isset($item['OrXPrQuantidade']) ? $item['OrXPrQuantidade'] : '';
											$fValorUnitario = isset($item['OrXPrValorUnitario']) ? mostraValor($item['OrXPrValorUnitario']) : '';											
											$fValorTotal = (isset($item['OrXPrQuantidade']) and isset($item['OrXPrValorUnitario'])) ? mostraValor($item['OrXPrQuantidade'] * $item['OrXPrValorUnitario']) : '';
											
											$fTotalGeral += (isset($item['OrXPrQuantidade']) and isset($item['OrXPrValorUnitario'])) ? $item['OrXPrQuantidade'] * $item['OrXPrValorUnitario'] : 0;
											
											print('
											<div class="row" style="margin-top: 8px;">
												<div class="col-lg-8">
													<div class="row">
														<div class="col-lg-1">
															<input type="text" id="inputItem'.$cont.'" name="inputItem'.$cont.'" class="form-control-border-off" value="'.$cont.'" readOnly>
															<input type="hidden" id="inputIdProduto'.$cont.'" name="inputIdProduto'.$cont.'" value="'.$item['ProduId'].'" class="idProduto">
														</div>
														<div class="col-lg-11">
															<input type="text" id="inputProduto'.$cont.'" name="inputProduto'.$cont.'" class="form-control-border-off" data-popup="tooltip" title="'.$item['ProduDetalhamento'].'" value="'.$item['ProduNome'].'" readOnly>
														</div>
													</div>
												</div>								
												<div class="col-lg-1">
													<input type="text" id="inputUnidade'.$cont.'" name="inputUnidade'.$cont.'" class="form-control-border-off" value="'.$item['UnMedSigla'].'" readOnly>
												</div>
												<div class="col-lg-1">
													<input type="text" id="inputQuantidade'.$cont.'" name="inputQuantidade'.$cont.'" class="form-control-border Quantidade pula" onChange="calculaValorTotal('.$cont.')" onkeypress="return onlynumber();" value="'.$iQuantidade.'">
												</div>	
												<div class="col-lg-1">
													<input type="text" id="inputValorUnitario'.$cont.'" name="inputValorUnitario'.$cont.'" class="form-control-border ValorUnitario pula" onChange="calculaValorTotal('.$cont.')" onKeyUp="moeda(this)" maxLength="12" value="'.$fValorUnitario.'">
												</div>	
												<div class="col-lg-1">
													<input type="text" id="inputValorTotal'.$cont.'" name="inputValorTotal'.$cont.'" class="form-control-border-off" value="'.$fValorTotal.'" readOnly>
												</div>											
											</div>');											
											
										}
										
										print('
										<div class="row" style="margin-top: 8px;">
												<div class="col-lg-8">
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
												<div class="col-lg-1" style="padding-top: 5px; text-align: right;">
													<h5><b>Total:</b></h5>
												</div>	
												<div class="col-lg-1">
													<input type="text" id="inputTotalGeral" name="inputTotalGeral" class="form-control-border-off" value="'.mostraValor($fTotalGeral).'" readOnly>
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
										<button class="btn btn-lg btn-principal" type="submit">Alterar</button>
										<a href="orcamento.php" class="btn btn-basic" role="button">Cancelar</a>
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
