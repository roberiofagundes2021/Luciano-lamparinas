<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Fluxo Operacional Produto';

include('global_assets/php/conexao.php');

//Se veio do fluxo.php
if(isset($_POST['inputFluxoOperacionalId'])){
	$iFluxoOperacional = $_POST['inputFluxoOperacionalId'];
	$iCategoria = $_POST['inputFluxoOperacionalCategoria'];
} else if (isset($_POST['inputIdFluxoOperacional'])){
	$iFluxoOperacional = $_POST['inputIdFluxoOperacional'];
	$iCategoria = $_POST['inputIdCategoria'];
} else {
	if (isset($_POST['inputOrigem'])){
		irpara($_POST['inputOrigem']);
	} else{
		$sql = "SELECT ParamEmpresaPublica
				FROM Parametro
				WHERE ParamEmpresa = " . $_SESSION['EmpreId'];
		$result = $conn->query($sql);
		$rowParametro = $result->fetch(PDO::FETCH_ASSOC);

		if ($rowParametro['ParamEmpresaPublica']){
			irpara("contrato.php");
		} else{
			irpara("fluxo.php");
		}
	}
}

	$sql = "SELECT ParamEmpresaPublica
			FROM Parametro
			WHERE ParamEmpresa = ". $_SESSION['EmpreId'];
	$result = $conn->query($sql);
	$rowParametros = $result->fetch(PDO::FETCH_ASSOC);

	if ($rowParametros['ParamEmpresaPublica']){
		$fluxo = "CONTRATO";
		$contrato= "Contrato";

	} else {
		$fluxo = " ";
		$contrato= "Nº Fluxo";
	}

//Se está alterando
if(isset($_POST['inputIdFluxoOperacional'])){

	try{

		$conn->beginTransaction();

		$sql = "DELETE FROM FluxoOperacionalXProduto
				WHERE FOXPrFluxoOperacional = $iFluxoOperacional AND FOXPrUnidade = ".$_SESSION['UnidadeId'];
		$conn->query($sql);

		$sql = "DELETE FROM ProdutoXFabricante
				WHERE PrXFaFluxoOperacional = $iFluxoOperacional AND PrXFaUnidade = ".$_SESSION['UnidadeId'];
		$conn->query($sql);

		for ($i = 1; $i <= $_POST['totalRegistros']; $i++) {
			$sql = "INSERT INTO FluxoOperacionalXProduto (FOXPrFluxoOperacional, FOXPrProduto, FOXPrDetalhamento, FOXPrQuantidade, FOXPrValorUnitario, 
					FOXPrUsuarioAtualizador, FOXPrUnidade)
					VALUES (:iFluxoOperacional, :iProduto, :sDetalhamento, :iQuantidade, :fValorUnitario, :iUsuarioAtualizador, :iUnidade)";
			$resultFluxoOperacional = $conn->prepare($sql);
			
			$resultFluxoOperacional->execute(array(
				':iFluxoOperacional' 	=> $iFluxoOperacional,
				':iProduto' 			=> $_POST['inputIdProduto'.$i],
				':sDetalhamento' 	   	=> $_POST['inputDetalhamento' . $i],
				':iQuantidade' 			=> $_POST['inputQuantidade'.$i] == '' ? null : $_POST['inputQuantidade'.$i],
				':fValorUnitario' 		=> $_POST['inputValorUnitario'.$i] == '' ? null : gravaValor($_POST['inputValorUnitario'.$i]),
				':iUsuarioAtualizador' 	=> $_SESSION['UsuarId'],
				':iUnidade' 			=> $_SESSION['UnidadeId']
			));

			$sql = "INSERT INTO ProdutoXFabricante (PrXFaProduto, PrXFaFluxoOperacional, PrXFaMarca, PrXFaModelo, PrXFaFabricante, PrXFaUnidade)
					VALUES (:iProduto, :iFluxoOperacional, :iMarca, :iModelo, :iFabricante, :iUnidade)";
			$result = $conn->prepare($sql);
			
			$result->execute(array(
				':iProduto' 			=> $_POST['inputIdProduto'.$i],
				':iFluxoOperacional' 	=> $iFluxoOperacional,
				':iMarca' 	   	        => $_POST['inputMarca' . $i],
				':iModelo' 	   	        => $_POST['inputModelo' . $i],
				':iFabricante' 			=> $_POST['inputFabricante'.$i],
				':iUnidade' 			=> $_SESSION['UnidadeId']
			));
		}		

		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Fluxo Operacional alterado!!!";
		$_SESSION['msg']['tipo'] = "success";


    } catch(PDOException $e){

		echo 'Error: ' . $e->getMessage();
		
		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar Fluxo Operacional!!!";
		$_SESSION['msg']['tipo'] = "error";	
    }
}	

try{
	
	$sql = "SELECT FlOpeId, FlOpeTermoReferencia, FlOpeNumContrato, ForneId, ForneNome, ForneTelefone, ForneCelular, 
				   CategNome, FlOpeCategoria, FlOpeSubCategoria, FlOpeNumProcesso, FlOpeValor, FlOpeStatus, 
				   SituaNome, SituaChave,  TrRefTabelaProduto,
				   dbo.fnSubCategoriasFluxo(FlOpeUnidade, FlOpeId) as SubCategorias, 
				   dbo.fnFluxoFechado(FlOpeId, FlOpeUnidade) as FluxoFechado
			FROM FluxoOperacional
			JOIN Fornecedor on ForneId = FlOpeFornecedor
			JOIN Categoria on CategId = FlOpeCategoria
			LEFT JOIN FluxoOperacionalXSubCategoria on FOXSCFluxo = FlOpeId
			JOIN Situacao on SituaId = FlOpeStatus
			LEFT JOIN TermoReferencia on TrRefId = FlOpeTermoReferencia
			WHERE FlOpeUnidade = ". $_SESSION['UnidadeId'] ." and FlOpeId = ".$iFluxoOperacional;
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT FOXPrProduto
			FROM FluxoOperacionalXProduto
			JOIN Produto on ProduId = FOXPrProduto
			WHERE FOXPrUnidade = ". $_SESSION['UnidadeId'] ." and FOXPrFluxoOperacional = ".$iFluxoOperacional;
	$result = $conn->query($sql);
	$rowProdutoUtilizado = $result->fetchAll(PDO::FETCH_ASSOC);
	$countProdutoUtilizado = count($rowProdutoUtilizado);
	
	foreach ($rowProdutoUtilizado as $itemProdutoUtilizado){
		$aProdutos[] = $itemProdutoUtilizado['FOXPrProduto'];
	}

	//SubCategorias para esse fornecedor
	$sql = "SELECT SbCatId, SbCatNome, FOXSCSubCategoria
			FROM SubCategoria
			JOIN FluxoOperacionalXSubCategoria on FOXSCSubCategoria = SbCatId
			WHERE SbCatEmpresa = " . $_SESSION['EmpreId'] . " and FOXSCFluxo = $iFluxoOperacional
			ORDER BY SbCatNome ASC";
	$result = $conn->query($sql);
	$rowBD = $result->fetchAll(PDO::FETCH_ASSOC);

	$sSubCategorias = 0;
	$sSubCategoriasNome = '';

	foreach ($rowBD as $item){

		if ($sSubCategorias == 0){
			$sSubCategorias = $item['SbCatId'];
			$sSubCategoriasNome .= $item['SbCatNome'];
		} else {
			$sSubCategorias .= ", ".$item['SbCatId'];
			$sSubCategoriasNome .= ", ".$item['SbCatNome'];
		}
	}	

	$TotalFluxo = $row['FlOpeValor'];
	
	// $sql = "SELECT PrXFaId, PrXFaMarca, PrXFaModelo, PrXFaFabricante
	// 		FROM ProdutoXFabricante
	// 		WHERE PrXFaUnidade = ". $_SESSION['UnidadeId'] ." and PrXFaFluxoOperacional = ".$iFluxoOperacional;
	// $resultResultadoSelect = $conn->query($sql);
	// $rowResultadoSelect = $resultResultadoSelect->fetchAll(PDO::FETCH_ASSOC);

	// $atualizar = 0;
	// $iProdutoXFabrincante = [];
	// $consultaMarca = [];
	// $consultaModelo = [];
	// $consultaFabricante = [];

	// foreach ($rowResultadoSelect as $itemSelect){
	// 	array_push($iProdutoXFabrincante, $itemSelect['PrXFaId']);
	// 	array_push($consultaMarca, $itemSelect['PrXFaMarca']);
	// 	array_push($consultaModelo, $itemSelect['PrXFaModelo']);
	// 	array_push($consultaFabricante, $itemSelect['PrXFaFabricante']);

	// 	$atualizar = 1;
	// }

	$sql = "SELECT isnull(SUM(FOXPrQuantidade * FOXPrValorUnitario),0) as TotalProduto
			FROM FluxoOperacionalXProduto
			WHERE FOXPrUnidade = ".$_SESSION['UnidadeId']." and FOXPrFluxoOperacional = ".$iFluxoOperacional;
	$result = $conn->query($sql);
	$rowProdutos = $result->fetch(PDO::FETCH_ASSOC);
	$TotalProdutos = $rowProdutos['TotalProduto'];

	$sql = "SELECT isnull(SUM(FOXSrQuantidade * FOXSrValorUnitario),0) as TotalServico
			FROM FluxoOperacionalXServico
			WHERE FOXSrUnidade = ".$_SESSION['UnidadeId']." and FOXSrFluxoOperacional = ".$iFluxoOperacional;
	$result = $conn->query($sql);
	$rowServicos = $result->fetch(PDO::FETCH_ASSOC);
	$TotalServicos = $rowServicos['TotalServico'];

	$TotalGeral = $TotalProdutos + $TotalServicos;	
	
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
	<title>Lamparinas | Listando produtos do Fluxo Operacional</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>	

	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>

	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<!-- /theme JS files -->
	
	<!-- Adicionando Javascript -->
    <script type="text/javascript" >

        $(document).ready(function() {	

			//Ao mudar a SubCategoria, filtra o produto via ajax (retorno via JSON)
			$('#cmbProduto').on('change', function(e){
				
				var inputCategoria = $('#inputIdCategoria').val();
				var produtos = $(this).val();
				var iFluxoOperacional = $('#iFluxoOperacional').val();
				var SOrigem = $('#SOrigem').val();
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
					url: "fluxoFiltraProduto.php",
					data: {
						idCategoria: inputCategoria,
						produtos: produtos,
						produtoId: produtoId,
						produtoQuant:produtoQuant,
						produtoValor: produtoValor,
						iFluxoOperacional: iFluxoOperacional,
						Origem: SOrigem},
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
			
			//Valida Registro
			$('#enviar').on('click', function(e){
				
				e.preventDefault();
				
				var inputValor = $('#inputValor').val().replaceAll('.', '').replace(',', '.');
				var inputTotalGeral = $('#inputTotalGeral').val().replaceAll('.', '').replace(',', '.');
				var totalProdutos = $('#totalRegistros').val();
				var inputOrigem = $('#inputOrigem').val();

                var cont = 1;

				for(i = 0; i <= totalProdutos; i++){
                    var valorTotal = $(`#inputValorTotal${i}`).val()
                    cont = valorTotal == '' ? 0 : 1;
					if ($(`#inputValorTotal${i}`).val() == '0,00' || $(`#inputMarca${i}`).val() == '') {
						if (inputOrigem == 'fluxo.php'){
							alerta('Atenção', 'Preencha todos os campos dos produtos selecionados ou retire-os da lista', 'error');
						} else {
							alerta('Atenção', 'Preencha todos os campos dos produtos', 'error');
						}
						
						return false;
					}
				}

				if(cont == 0){
					if (inputOrigem == 'fluxo.php'){
						alerta('Atenção','Preencha todos os campos dos produtos selecionados, ou retire os da lista','error');
					} else {
						alerta('Atenção','Preencha todos os campos dos produtos','error');
					}
					return false;
				}
				
				//Verifica se o valor ultrapassou o total
				if (parseFloat(inputTotalGeral) > parseFloat(inputValor)){
					alerta('Atenção','A soma dos totais ultrapassou o valor do contrato!','error');
					return false;
				}
				
				$( "#formFluxoOperacionalProduto" ).submit();
				
			}); // enviar	
			
			//Enviar para aprovação da Controladoria (via Bandeja)
			$('#enviarAprovacao').on('click', function(e) {

				var inputOrigem = $('#inputOrigem').val();

			e.preventDefault();

				if (inputOrigem == 'fluxo.php') {
					confirmaExclusao(document.formFluxoOperacionalProduto, "Essa ação enviará  o Contrato formalizado para aprovação da Controladoria. Tem certeza que deseja enviar?", "fluxoEnviar.php");
				}  else {
					confirmaExclusao(document.formFluxoOperacionalProduto, "Essa ação enviará o Fluxo Operacional para aprovação da Controladoria. Tem certeza que deseja enviar?", "fluxoEnviar.php");
				}

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
		
		function calculaValorTotal() {
			
			let n = 1;
			let totalRegistros = $('#totalRegistros').val();
			let Quantidade = 0
			let ValorUnitario = 0;
			let ValorTotal = 0;
			let ValorTotalMoeda = 0;
			let TotalGeral = 0;

			while (n <= totalRegistros) {
				
				Quantidade = $('#inputQuantidade' + n + '').val().trim() == '' ? 0 : $('#inputQuantidade' + n + '').val().trim();
				ValorUnitario = $('#inputValorUnitario' + n + '').val() == '' ? 0 : $('#inputValorUnitario' + n + '').val().replaceAll('.', '').replace(',', '.');

				ValorTotal = parseFloat(Quantidade) * parseFloat(ValorUnitario);
				TotalGeral += ValorTotal;

				ValorTotalMoeda = float2moeda(ValorTotal).toString();

				$('#inputValorTotal' + n + '').val(ValorTotalMoeda);

				n++;
			}
			
			TotalGeral = float2moeda(TotalGeral).toString();

			$('#inputTotalGeral').val(TotalGeral);
		}
							
	</script>

</head>

<body class="navbar-top sidebar-xs">

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
					
					<form name="formFluxoOperacionalProduto" id="formFluxoOperacionalProduto" method="post" class="form-validate">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Listar Produtos - Fluxo Operacional Nº <?php echo $fluxo; ?> "<?php echo $row['FlOpeNumContrato']; ?>"</h5>
						</div>					
						
						<input type="hidden" id="inputIdFluxoOperacional" name="inputIdFluxoOperacional" value="<?php echo $row['FlOpeId']; ?>">
						<input type="hidden" id="inputStatus" name="inputStatus" value="<?php echo $row['FlOpeStatus']; ?>">
						<input type="hidden" id="inputOrigem" name="inputOrigem" value="<?php echo $_POST['inputOrigem']; ?>">
						<input type="hidden" id="iFluxoOperacional" name="iFluxoOperacional" value="<?php echo $iFluxoOperacional; ?>">
						<input type="hidden" id="SOrigem" name="SOrigem" value="<?php echo $_POST['inputOrigem']; ?>">
						
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
												<label for="inputTelefone">Telefone</label>
												<input type="text" id="inputTelefone" name="inputTelefone" class="form-control" value="<?php echo $row['ForneTelefone']; ?>" readOnly>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputCelular">Celular</label>
												<input type="text" id="inputCelular" name="inputCelular" class="form-control" value="<?php echo $row['ForneCelular']; ?>" readOnly>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="<?php if ($fluxo == 'CONTRATO') { echo "col-lg-3"; } else { echo "col-lg-4"; } ?>">
											<div class="form-group">
												<label for="inputCategoriaNome">Categoria</label>
												<input type="text" id="inputCategoriaNome" name="inputCategoriaNome" class="form-control" value="<?php echo $row['CategNome']; ?>" readOnly>
												<input type="hidden" id="inputIdCategoria" name="inputIdCategoria" class="form-control" value="<?php echo $row['FlOpeCategoria']; ?>">
											</div>
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputSubCategoriaNome">SubCategoria(s)</label>

												<?php 
													if ($sSubCategorias == 0){
														echo '<input id="inputSemSubCategoriaNome" name="inputSemSubCategoriaNome" class="form-control" value="" readOnly >';
													} else{

														echo '<select id="inputSubCategoriaNome" name="inputSubCategoriaNome" class="form-control multiselect-filtering" multiple="multiple" data-fouc>';
															
														$sql = "SELECT SbCatId, SbCatNome
																FROM SubCategoria
																JOIN Situacao on SituaId = SbCatStatus	
																WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ." and SbCatId in (".$sSubCategorias.")
																ORDER BY SbCatNome ASC"; 
														$result = $conn->query($sql);
														$rowBD = $result->fetchAll(PDO::FETCH_ASSOC);
														$count = count($rowBD);														
																
														foreach ( $rowBD as $item){	
															print('<option value="'.$item['SbCatId,'].'"disabled selected>'.$item['SbCatNome'].'</option>');	
														}                    
														
														echo '</select>';
													}  
												?>
												
											</div>
										</div>
										<div class="<?php if ($fluxo == 'CONTRATO') { echo "col-lg-1"; } else { echo "col-lg-2"; } ?>">
											<div class="form-group">
												<label for="inputContrato"><?php echo $contrato; ?></label>
												<input type="text" id="inputContrato" name="inputContrato" class="form-control" value="<?php echo $row['FlOpeNumContrato']; ?>" readOnly>
											</div>
										</div>
										<?php
											if ($fluxo == 'CONTRATO'){	
												print('
											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputProcesso">Processo</label>
													<input type="text" id="inputProcesso" name="inputProcesso" class="form-control" value="' . $row['FlOpeNumProcesso'] . '" readOnly>
												</div>
											</div>	');
											}										
									   ?>	
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputValor">Valor Total</label>
												<input type="text" id="inputValor" name="inputValor" class="form-control" value="<?php echo mostraValor($row['FlOpeValor']); ?>" readOnly>
											</div>
										</div>											
									</div>
									
									<?php

										if ($_POST['inputOrigem'] == 'fluxo.php'){
											
											if ($countProdutoUtilizado and $_SESSION['PerfiChave'] != 'SUPER' and $_SESSION['PerfiChave'] != 'ADMINISTRADOR' and 
												$_SESSION['PerfiChave'] != 'CONTROLADORIA' and $_SESSION['PerfiChave'] != 'CENTROADMINISTRATIVO') { 
												
												$disable = "disabled";
											} else{
												$disable = "";
											}

											print('
											<div class="row">	
												<div class="col-lg-12">
													<div class="form-group">
														<label for="cmbProduto">Produto</label>
														<select id="cmbProduto" name="cmbProduto" class="form-control multiselect-filtering" multiple="multiple" data-fouc '.$disable.'>');

															$sql = "SELECT ProduId, ProduNome
																	FROM Produto
																	JOIN Situacao on SituaId = ProduStatus
																	LEFT JOIN SubCategoria on SbCatId = ProduSubCategoria
																	WHERE ProduEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO' and ProduCategoria = ".$iCategoria;
															if ($sSubCategorias != 0) {
																$sql .= " and ProduSubCategoria in (".$sSubCategorias.")";
															}
															$sql .=	" ORDER BY SbCatNome ASC";
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
															
													print('		
														</select>
													</div>
												</div>
											</div>'); //echo $sql;die;
										}										
									?>
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
									<p class="mb-3">Abaixo estão listados todos os produtos selecionados acima. Para atualizar os valores, basta preencher as colunas <code>Quantidade</code> e <code>Valor Unitário</code> e depois clicar em <b>ALTERAR</b>.</p>

									<!--<div class="hot-container">
										<div id="example"></div>
									</div>-->
									
									<?php

										$sql = "SELECT ProduId, ProduNome, FOXPrDetalhamento as Detalhamento, UnMedSigla, FOXPrQuantidade, FOXPrValorUnitario
												FROM Produto
												JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId
												JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
												LEFT JOIN SubCategoria on SbCatId = ProduSubCategoria
												WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and FOXPrFluxoOperacional = ".$iFluxoOperacional;
												if ($sSubCategorias <> ""){
													$sql .= " and SbCatId in (".$sSubCategorias.")";
												}
												$sql .=" ORDER BY SbCatNome, ProduNome ASC";
										$result = $conn->query($sql);
										$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
										$countProduto = count($rowProdutos);

										if ($_POST['inputOrigem'] == 'fluxo.php'){

											if (!$countProduto){
												$sql = "SELECT ProduId, ProduNome, FOXPrDetalhamento as Detalhamento, UnMedSigla
														FROM Produto
														JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
														JOIN Situacao on SituaId = ProduStatus
														LEFT JOIN FluxoOperacionalXProduto on FOXPrProduto = ProduId and FOXPrFluxoOperacional = $iFluxoOperacional
														LEFT JOIN SubCategoria on SbCatId = ProduSubCategoria
														WHERE ProduEmpresa = ".$_SESSION['EmpreId']." and ProduCategoria = ".$iCategoria." and SituaChave = 'ATIVO'";
														if ($sSubCategorias <> ""){
															$sql .= " and SbCatId in (".$sSubCategorias.")";
														}
														$sql .=" ORDER BY SbCatNome, ProduNome ASC";
												$result = $conn->query($sql);
												$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
												$countProduto = count($rowProdutos);
											} 

										} else {

											if (!$countProduto) {

												if ($row['TrRefTabelaProduto'] == 'Produto'){
													$sql = "SELECT ProduId, ProduNome, TRXPrDetalhamento as Detalhamento, UnMedSigla, SbCatNome
															FROM Produto															
															JOIN TermoReferenciaXProduto on TRXPrProduto = ProduId
															JOIN TRXSubCategoria ON TRXSCTermoReferencia = TRXPrTermoReferencia
															JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
															LEFT JOIN SubCategoria on SbCatId = TRXSCSubCategoria
															WHERE ProduEmpresa = " . $_SESSION['EmpreId'] . " and TRXPrTermoReferencia = ".$row['FlOpeTermoReferencia'];
															if ($sSubCategorias <> ""){
																$sql .= " and SbCatId in (".$sSubCategorias.")";
															}
															$sql .=" ORDER BY SbCatNome, ProduNome ASC";												
												} else { //Se $row['TrRefTabelaProduto'] == ProdutoOrcamento
													$sql = "SELECT ProduId, ProduNome, TRXPrDetalhamento as Detalhamento, UnMedSigla, SbCatNome
															FROM Produto
															JOIN ProdutoOrcamento on PrOrcProduto = ProduId
															JOIN TermoReferenciaXProduto on TRXPrProduto = PrOrcId
															JOIN TRXSubCategoria ON TRXSCTermoReferencia = TRXPrTermoReferencia
															JOIN UnidadeMedida on UnMedId = ProduUnidadeMedida
															LEFT JOIN SubCategoria on SbCatId = TRXSCSubCategoria
															WHERE ProduEmpresa = " . $_SESSION['EmpreId'] . " and TRXPrTermoReferencia = ".$row['FlOpeTermoReferencia'];
															if ($sSubCategorias <> ""){
																$sql .= " and SbCatId in (".$sSubCategorias.")";
															}
															$sql .=" ORDER BY SbCatNome, ProduNome ASC";
												}

												$result = $conn->query($sql);
												$rowProdutos = $result->fetchAll(PDO::FETCH_ASSOC);
												$countProduto = count($rowProdutos);
											}
										}
										
										print('
										<input type="hidden" id="atualizarFluxoOperacional" name="atualizarFluxoOperacional" value="'.$atualizar.'">
										<div class="row" style="margin-bottom: -20px;">
											<div class="col-lg-8">
													<div class="row">
														<div class="col-lg-1">
															<label for="inputCodigo"><strong>Item</strong></label>
														</div>
														<div class="col-lg-5">
															<label for="inputProduto"><strong>Produto</strong></label>
														</div>
														<div class="col-lg-2">
															<label for="inputMarca"><strong>Marca</strong></label>
														</div>
														<div class="col-lg-2">
															<label for="inputModelo"><strong>Modelo</strong></label>
														</div>
														<div class="col-lg-2">
															<label for="inputFabricante"><strong>Fabricante</strong></label>
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
										
										$cont = 0;
										$fTotalGeral = 0;
										
										$indice = 0;

										foreach ($rowProdutos as $item){
											
											$cont++;
											$iUnidade = $_SESSION['UnidadeId'];
											$iEmpresa = $_SESSION['EmpreId'];

											// vai buscar na tabela ProdutoXFabricante os dados caso esse fluxo ja tenha sido liberado
											$HTML_MARCA = '';
											$HTML_MODELO = '';
											$HTML_FABRICANTE = '';

											$sqlPrXFa = "SELECT PrXFaId, PrXFaMarca, PrXFaModelo, PrXFaFabricante
													FROM ProdutoXFabricante
													WHERE PrXFaProduto = $item[ProduId] and PrXFaFluxoOperacional = $iFluxoOperacional and PrXFaUnidade = $iUnidade";
											$resultPrXFa = $conn->query($sqlPrXFa);
											$resultPrXFa = $resultPrXFa->fetch(PDO::FETCH_ASSOC);

											$sql = "SELECT MarcaId, MarcaNome
													FROM Marca
													JOIN Situacao on SituaId = MarcaStatus
													WHERE MarcaEmpresa = $iEmpresa  and SituaChave = 'ATIVO'
													ORDER BY MarcaNome ASC";
											$resultMarca = $conn->query($sql);
											$rowMarca = $resultMarca->fetchAll(PDO::FETCH_ASSOC);

											$sql = "SELECT ModelId, ModelNome
													FROM Modelo
													JOIN Situacao on SituaId = ModelStatus
													WHERE ModelEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
													ORDER BY ModelNome ASC";
											$resultModelo = $conn->query($sql);
											$rowModelo = $resultModelo->fetchAll(PDO::FETCH_ASSOC);

											$sql = "SELECT FabriId, FabriNome
													FROM Fabricante
													JOIN Situacao on SituaId = FabriStatus
													WHERE FabriEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
													ORDER BY FabriNome ASC";
											$resultFabricante = $conn->query($sql);
											$rowFabricante = $resultFabricante->fetchAll(PDO::FETCH_ASSOC);
											
											foreach ($rowMarca as $itemMarca){
												$seleciona = "";
												if(isset($resultPrXFa['PrXFaMarca'])){
													$seleciona = ($resultPrXFa['PrXFaMarca'] == $itemMarca['MarcaId']) ? " selected " : "";
												}
												
												$HTML_MARCA .= '<option value="'.$itemMarca['MarcaId'].'" '.$seleciona.'>'.$itemMarca['MarcaNome'].'</option>';
											}

											foreach ($rowModelo as $itemModelo){
												$seleciona = "";
												if(isset($resultPrXFa['PrXFaModelo'])){
													$seleciona = ($resultPrXFa['PrXFaModelo'] == $itemModelo['ModelId']) ? "selected " : "";
												}
												
												$HTML_MODELO .= '<option value="'.$itemModelo['ModelId'].'" '.$seleciona.'>'.$itemModelo['ModelNome'].'</option>';
											}

											foreach ($rowFabricante as $itemFabricante){
												$seleciona = "";
												if(isset($resultPrXFa['PrXFaFabricante'])){
													$seleciona = ($resultPrXFa['PrXFaFabricante'] == $itemFabricante['FabriId']) ? "selected " : "";
												}
												
												$HTML_FABRICANTE .= '<option value="'.$itemFabricante['FabriId'].'" '.$seleciona.'>'.$itemFabricante['FabriNome'].'</option>';
											}
											
											$iQuantidade = isset($item['FOXPrQuantidade']) ? $item['FOXPrQuantidade'] : '';
											$fValorUnitario = isset($item['FOXPrValorUnitario']) ? mostraValor($item['FOXPrValorUnitario']) : '';											
											$fValorTotal = (isset($item['FOXPrQuantidade']) and isset($item['FOXPrValorUnitario'])) ? mostraValor($item['FOXPrQuantidade'] * $item['FOXPrValorUnitario']) : '';
											
											$fTotalGeral += (isset($item['FOXPrQuantidade']) and isset($item['FOXPrValorUnitario'])) ? $item['FOXPrQuantidade'] * $item['FOXPrValorUnitario'] : 0;
											
											print('
											<div class="row" style="margin-top: 8px;">
												<div class="col-lg-8">
													<div class="row">
														<div class="col-lg-1">
															<input type="text" id="inputItem'.$cont.'" name="inputItem'.$cont.'" class="form-control-border-off" value="'.$cont.'" readOnly>
															<input type="hidden" id="inputIdProduto'.$cont.'" name="inputIdProduto'.$cont.'" value="'.$item['ProduId'].'" class="idProduto">
														</div>
														<div class="col-lg-5">
															<input type="text" id="inputProduto'.$cont.'" name="inputProduto'.$cont.'" class="form-control-border-off" data-popup="tooltip" title="'. substr($item['Detalhamento'],0,380).'..." value="'.$item['ProduNome'].'" readOnly>
															<input type="hidden" id="inputDetalhamento' . $cont . '" name="inputDetalhamento' . $cont . '" value="' . $item['Detalhamento'] . '">
														</div>
														<div class="col-lg-2">
															<select id="inputMarca'.$cont.'" name="inputMarca'.$cont.'"'.($row['SituaChave'] == 'LIBERADO'?' disabled ':'').'class="form-control select-search">
																<option value="">Selecione</option>'.$HTML_MARCA.'</select>
														</div>
														<div class="col-lg-2">
															<select id="inputModelo'.$cont.'" name="inputModelo'.$cont.'" '.($row['SituaChave'] == 'LIBERADO'?' disabled ':'').'class="form-control select-search">
																<option value="">Selecione</option>'.$HTML_MODELO.'</select>
														</div>
														<div class="col-lg-2">
															<select id="inputFabricante'.$cont.'" name="inputFabricante'.$cont.'" '.($row['SituaChave'] == 'LIBERADO'?' disabled ':'').'class="form-control select-search">
																<option value="">Selecione</option>'.$HTML_FABRICANTE.'</select>
														</div>
													</div>
												</div>								
												<div class="col-lg-1">
													<input type="text" id="inputUnidade'.$cont.'" name="inputUnidade'.$cont.'" class="form-control-border-off" value="'.$item['UnMedSigla'].'" readOnly>
												</div>
												<div class="col-lg-1">
													<input type="text" id="inputQuantidade'.$cont.'" name="inputQuantidade'.$cont.'" class="form-control-border Quantidade pula" onChange="calculaValorTotal()" value="'.$iQuantidade.'">
												</div>	
												<div class="col-lg-1">
													<input type="text" id="inputValorUnitario'.$cont.'" name="inputValorUnitario'.$cont.'" class="form-control-border ValorUnitario text-right pula" onChange="calculaValorTotal()" onKeyUp="moeda(this)" maxLength="12" value="'.$fValorUnitario.'">
												</div>	
												<div class="col-lg-1">
													<input type="text" id="inputValorTotal'.$cont.'" name="inputValorTotal'.$cont.'" class="form-control-border-off text-right" value="'.$fValorTotal.'" readOnly>
												</div>											
											</div>');
											
											$indice++;
										}

										if($atualizar == 1) {
											for($j = 1; $j <= $cont; $j++) {
												print('<input type="hidden" id="inputProdutoXFabicanteId'.$j.'" name="inputProdutoXFabicanteId'.$j.'" value="'.$iProdutoXFabrincante[$j - 1].'">');
											}
										}
										
										print('
										<div class="row" style="margin-top: 8px;">
												<div class="col-lg-7">
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
												<div class="col-lg-2">
													<input type="text" id="inputTotalGeral" name="inputTotalGeral" class="form-control-border-off text-right" value="'.mostraValor($fTotalGeral).'" readOnly>
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
								<div class="col-lg-6">
									<div class="form-group">
										<?php	
                                            if ($row['SituaChave'] != 'LIBERADO'){	
												if ($row['FluxoFechado']){												
													print('
													<button class="btn btn-lg btn-principal" id="enviar" style="margin-right:5px;">Alterar</button>
													<button class="btn btn-lg btn-default" id="enviarAprovacao">Enviar para Aprovação</button>');
												} else{ 
													if (!$countProduto){
														print('<button class="btn btn-lg btn-principal" id="enviar" disabled>Alterar</button>');
													} else {
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
													}
												} 
											} 

											if ($_POST['inputOrigem'] == 'fluxo.php'){
												print('<a href="fluxo.php" class="btn btn-basic" role="button">Cancelar</a>');
											} else {
												print('<a href="contrato.php" class="btn btn-basic" role="button">Cancelar</a>');
											}

										?>										
									</div>
								</div>

								<div class="col-lg-6" style="text-align: right; padding-right: 35px; color: red;">
								<?php	
									if ($row['FluxoFechado' ] && $row['SituaChave'] != 'LIBERADO'){
										if($row['SituaNome'] == 'PENDENTE'){
											print('<i class="icon-info3" data-popup="tooltip" data-placement="bottom"></i>Preenchimento Concluído (ENVIE PARA APROVAÇÃO)');
										} else {
											print('<i class="icon-info3" data-popup="tooltip" data-placement="bottom"></i>Preenchimento Concluído ('.$row['SituaNome'].')');
										}
									} else if (!$countProduto){
										print('<i class="icon-info3" data-popup="tooltip" data-placement="bottom"></i>Não há produtos cadastrados para a Categoria e SubCategoria informada');
									} else if ($TotalFluxo < $TotalGeral){
										print('<i class="icon-info3" data-popup="tooltip" data-placement="bottom"></i>Os valores dos Produtos + Serviços ultrapassaram o valor total do Fluxo');
									}
								?>
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
