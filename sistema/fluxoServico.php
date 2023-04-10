<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Fluxo Operacional Serviço';

include('global_assets/php/conexao.php');

//Se veio do fluxo.php
if (isset($_POST['inputFluxoOperacionalId'])) {
	$iFluxoOperacional = $_POST['inputFluxoOperacionalId'];
	$iCategoria = $_POST['inputFluxoOperacionalCategoria'];
} else if (isset($_POST['inputIdFluxoOperacional'])) {
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
if (isset($_POST['inputIdFluxoOperacional'])) {
	$conn->beginTransaction();

	try {

		$sql = "DELETE FROM FluxoOperacionalXServico
				WHERE FOXSrFluxoOperacional = $iFluxoOperacional AND FOXSrUnidade = ".$_SESSION['UnidadeId'];
		$conn->query($sql);

		$sql = "DELETE FROM ServicoXFabricante
				WHERE SrXFaFluxoOperacional = $iFluxoOperacional and  SrXFaUnidade = ".$_SESSION['UnidadeId'];
		$conn->query($sql);

		// verificar se vai inserir novo ou atualizar existente...
		$sqlSrXFa = "SELECT SrXFaId, SrXFaMarca, SrXFaModelo, SrXFaFabricante
				FROM ServicoXFabricante
				WHERE SrXFaFluxoOperacional = $iFluxoOperacional and  SrXFaUnidade = ".$_SESSION['UnidadeId'];
		$resultUpdate = $conn->query($sqlSrXFa);
		$InsertORUpdate = $resultUpdate->fetchAll(PDO::FETCH_ASSOC);

		for ($i = 1; $i <= $_POST['totalRegistros']; $i++) {

			$sql = "INSERT INTO FluxoOperacionalXServico (FOXSrFluxoOperacional, FOXSrServico, FOXSrDetalhamento, FOXSrQuantidade, FOXSrValorUnitario, 
					FOXSrUsuarioAtualizador, FOXSrUnidade)
					VALUES (:iFluxoOperacional, :iServico, :sDetalhamento, :iQuantidade, :fValorUnitario, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);

			$result->execute(array(
				':iFluxoOperacional' 	=> $iFluxoOperacional,
				':iServico' 			=> $_POST['inputIdServico' . $i],
				':sDetalhamento' 	   	=> $_POST['inputDetalhamento' . $i],
				':iQuantidade' 			=> $_POST['inputQuantidade' . $i] == '' ? null : $_POST['inputQuantidade' . $i],
				':fValorUnitario' 		=> $_POST['inputValorUnitario' . $i] == '' ? null : gravaValor($_POST['inputValorUnitario' . $i]),
				':iUsuarioAtualizador' 	=> $_SESSION['UsuarId'],
				':iUnidade' 			=> $_SESSION['UnidadeId']
			));

			$sql = "INSERT INTO ServicoXFabricante(SrXFaServico,SrXFaFluxoOperacional,SrXFaMarca,SrXFaModelo,SrXFaFabricante,SrXFaUnidade)
			VALUES(:iServico, :iFluxoOperacional, :iMarca, :iModelo, :iFabricante, :iUnidade)";
			$resultProdutoXFabricante = $conn->prepare($sql);

			$resultProdutoXFabricante->execute(array(
				':iServico' 			=> $_POST['inputIdServico'.$i],
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
	} catch (PDOException $e) {

		echo 'Error: ' . $e->getMessage();

		$conn->rollback();

		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar Fluxo Operacional!!!";
		$_SESSION['msg']['tipo'] = "error";
	}
}

try {

	$sql = "SELECT FlOpeId, FlOpeTermoReferencia, FlOpeNumContrato, ForneId, ForneNome, ForneTelefone, ForneCelular, 
				   CategNome, FlOpeCategoria, FlOpeSubCategoria, FlOpeNumProcesso, FlOpeValor, FlOpeStatus, 
				   SituaNome, SituaChave, TrRefTabelaServico,
				   dbo.fnSubCategoriasFluxo(FlOpeUnidade, FlOpeId) as SubCategorias,
				   dbo.fnFluxoFechado(FlOpeId, FlOpeUnidade) as FluxoFechado
			FROM FluxoOperacional
			JOIN Fornecedor on ForneId = FlOpeFornecedor
			JOIN Categoria on CategId = FlOpeCategoria
			LEFT JOIN FluxoOperacionalXSubCategoria on FOXSCFluxo = FlOpeId
			JOIN Situacao on SituaId = FlOpeStatus
			LEFT JOIN TermoReferencia on TrRefId = FlOpeTermoReferencia
			WHERE FlOpeUnidade = " . $_SESSION['UnidadeId'] . " and FlOpeId = " . $iFluxoOperacional;
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT FOXSrServico
			FROM FluxoOperacionalXServico
			JOIN Servico on ServiId = FOXSrServico
			WHERE FOXSrUnidade = " . $_SESSION['UnidadeId'] . " and FOXSrFluxoOperacional = " . $iFluxoOperacional;
	$result = $conn->query($sql);
	$rowServicoUtilizado = $result->fetchAll(PDO::FETCH_ASSOC);
	$countServicoUtilizado = count($rowServicoUtilizado);

	foreach ($rowServicoUtilizado as $itemServicoUtilizado) {
		$aServicos[] = $itemServicoUtilizado['FOXSrServico'];
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

} catch (PDOException $e) {
	echo 'Error: ' . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Listando serviços do Fluxo Operacional</title>

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
	<script type="text/javascript">
		$(document).ready(function() {

			//Ao mudar a SubCategoria, filtra o servico via ajax (retorno via JSON)
			$('#cmbServico').on('change', function(e) {

				var inputCategoria = $('#inputIdCategoria').val();
				var servicos = $(this).val();
				var iFluxoOperacional = $('#iFluxoOperacional').val();
				var SOrigem = $('#SOrigem').val();
				//console.log(servicos);

				var cont = 1;
				var servicoId = [];
				var servicoQuant = [];
				var servicoValor = [];

				// Aqui é para cada "class" faça
				$.each($(".idServico"), function() {
					servicoId[cont] = $(this).val();
					cont++;
				});

				cont = 1;
				//aqui fazer um for que vai até o ultimo cont (dando cont++ dentro do for)
				$.each($(".Quantidade"), function() {
					$id = servicoId[cont];

					servicoQuant[$id] = $(this).val();
					cont++;
				});

				cont = 1;
				$.each($(".ValorUnitario"), function() {
					$id = servicoId[cont];

					servicoValor[$id] = $(this).val();
					cont++;
				});

				$.ajax({
					type: "POST",
					url: "fluxoFiltraServico.php",
					data: {
						idCategoria: inputCategoria,
						servicos: servicos,
						servicoId: servicoId,
						servicoQuant: servicoQuant,
						servicoValor: servicoValor,
						iFluxoOperacional: iFluxoOperacional,
						Origem: SOrigem
					},
					success: function(resposta) {
						//alert(resposta);
						$("#tabelaServicos").html(resposta).show();

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
			$('#enviar').on('click', function(e) {

				e.preventDefault();

				var inputValor = $('#inputValor').val().replaceAll('.', '').replace(',', '.');
				var inputTotalGeral = $('#inputTotalGeral').val().replaceAll('.', '').replace(',', '.');
				var totalServicos = $('#totalRegistros').val();
				var inputOrigem = $('#inputOrigem').val();

				var cont = 1;

				for (i = 0; i <= totalServicos; i++) {
					var valorTotal = $(`#inputValorTotal${i}`).val()
					cont = valorTotal == '' ? 0 : 1;
					if ($(`#inputValorTotal${i}`).val() == '0,00' || $(`#inputMarca${i}`).val() == '') {
						if (inputOrigem == 'fluxo.php'){
							alerta('Atenção', 'Preencha todas as quantidades e valores dos serviços selecionados ou retire da lista', 'error');
						} else {
							alerta('Atenção', 'Preencha todas as quantidades e valores dos serviços', 'error');
						}
						return false;
					}
				}

				if (cont == 0) {
					if (inputOrigem == 'fluxo.php'){
						alerta('Atenção','Preencha todas as quantidades e valores dos serviços selecionados, ou retire da lista','error');
					} else {
						alerta('Atenção','Preencha todas as quantidades e valores dos serviços','error');
					}
					return false;
				}

				//Verifica se o valor ultrapassou o total
				if (parseFloat(inputTotalGeral) > parseFloat(inputValor)) {
					alerta('Atenção', 'A soma dos totais ultrapassou o valor do contrato!', 'error');
					return false;
				}

				$("#formFluxoOperacionalServico").submit();

			}); // enviar	

			//Enviar para aprovação da Controladoria (via Bandeja)
			$('#enviarAprovacao').on('click', function(e) {

				var inputOrigem = $('#inputOrigem').val();

				e.preventDefault();

				if (inputOrigem == 'fluxo.php') {
					confirmaExclusao(document.formFluxoOperacionalServico, "Essa ação enviará  o Fluxo Operacional  para aprovação da Controladoria. Tem certeza que deseja enviar?", "fluxoEnviar.php");
				}  else {
					confirmaExclusao(document.formFluxoOperacionalServico, "Essa ação enviará o Contrato formalizado para aprovação da Controladoria. Tem certeza que deseja enviar?", "fluxoEnviar.php");
				}

				return false;
			});		

		}); //document.ready

		//Mostra o "Filtrando..." na combo Servico
		function FiltraServico() {
			$('#cmbServico').empty().append('<option>Filtrando...</option>');
		}

		function ResetServico() {
			$('#cmbServico').empty().append('<option>Sem servico</option>');
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

					<form name="formFluxoOperacionalServico" id="formFluxoOperacionalServico" method="post" class="form-validate">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Listar Servicos - Fluxo Operacional Nº <?php echo $fluxo; ?> "<?php echo $row['FlOpeNumContrato']; ?>"</h5>
						</div>

						<input type="hidden" id="inputIdFluxoOperacional" name="inputIdFluxoOperacional" class="form-control" value="<?php echo $row['FlOpeId']; ?>">
						<input type="hidden" id="inputStatus" name="inputStatus" class="form-control" value="<?php echo $row['FlOpeStatus']; ?>">
						<input type="hidden" id="inputOrigem" name="inputOrigem" class="form-control" value="<?php echo $_POST['inputOrigem']; ?>">
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

											if ($countServicoUtilizado and $_SESSION['PerfiChave'] != 'SUPER' and $_SESSION['PerfiChave'] != 'ADMINISTRADOR' and 
												$_SESSION['PerfiChave'] != 'CONTROLADORIA' and $_SESSION['PerfiChave'] != 'CENTROADMINISTRATIVO') {
																																													
												$disable = "disabled";
											} else{
												$disable = "";
											}
												
											print('
											<div class="row">
												<div class="col-lg-12">
													<div class="form-group">
														<label for="cmbServico">Servico</label>
														<select id="cmbServico" name="cmbServico" class="form-control multiselect-filtering" multiple="multiple" data-fouc '.$disable.'>');
															
															$sql = "SELECT ServiId, ServiNome
																	FROM Servico
																	JOIN Situacao on SituaId = ServiStatus
																	LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
																	WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO' and ServiCategoria = " . $iCategoria;
															if ($sSubCategorias != 0) {
																$sql .= " and ServiSubCategoria in (".$sSubCategorias.")";
															}
															$sql .=	" ORDER BY SbCatNome ASC";
															$result = $conn->query($sql);
															$rowServico = $result->fetchAll(PDO::FETCH_ASSOC);

															foreach ($rowServico as $item) {

																if (in_array($item['ServiId'], $aServicos) or $countServicoUtilizado == 0) {
																	$seleciona = "selected";
																} else {
																	$seleciona = "";
																}

																print('<option value="' . $item['ServiId'] . '" ' . $seleciona . '>' . $item['ServiNome'] . '</option>');
															}

													print('
														</select>
													</div>
												</div>
											</div>');
										}	
									?>		
								</div>
							</div>

							<!-- Custom header text -->
							<div class="card">
								<div class="card-header header-elements-inline">
									<h5 class="card-title">Relação de Servicos</h5>
									<div class="header-elements">
										<div class="list-icons">
											<a class="list-icons-item" data-action="collapse"></a>
											<a class="list-icons-item" data-action="reload"></a>
											<a class="list-icons-item" data-action="remove"></a>
										</div>
									</div>
								</div>

								<div class="card-body">
									<p class="mb-3">Abaixo estão listados todos os servicos selecionados acima. Para atualizar os valores, basta preencher as colunas <code>Quantidade</code> e <code>Valor Unitário</code> e depois clicar em <b>ALTERAR</b>.</p>

									<?php       

										$sql = "SELECT ServiId, ServiNome, FOXSrDetalhamento as Detalhamento, FOXSrQuantidade, FOXSrValorUnitario, FOXSrValorUnitario
												FROM Servico
												JOIN FluxoOperacionalXServico on FOXSrServico = ServiId
												LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
												WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and FOXSrFluxoOperacional = " . $iFluxoOperacional;
												if ($sSubCategorias <> ""){
													$sql .= " and SbCatId in (".$sSubCategorias.")";
												}
												$sql .=" ORDER BY SbCatNome, ServiNome ASC";
										$result = $conn->query($sql);
										$rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);
										$countServico = count($rowServicos);

										if ($_POST['inputOrigem'] == 'fluxo.php'){

											if (!$countServico) {
												$sql = "SELECT ServiId, ServiNome, FOXSrDetalhamento as Detalhamento
														FROM Servico
														JOIN Situacao on SituaId = ServiStatus
														LEFT JOIN FluxoOperacionalXServico on FOXSrServico = ServiId and FOXSrFluxoOperacional = $iFluxoOperacional
														LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
														WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and ServiCategoria = " . $iCategoria . " and SituaChave = 'ATIVO'"; 
														if ($sSubCategorias <> ""){
															$sql .= " and SbCatId in (".$sSubCategorias.")";
														}
														$sql .=" ORDER BY SbCatNome, ServiNome ASC";
												$result = $conn->query($sql);
												$rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);
												$countServico = count($rowServicos);
											}

										} else{

											if (!$countServico) {

												if ($row['TrRefTabelaServico'] == 'Servico'){
													$sql = "SELECT ServiId, ServiNome, TRXSrDetalhamento as Detalhamento, SbCatNome
															FROM Servico
															LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
															JOIN TermoReferenciaXServico on TRXSrServico = ServiId
															WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and TRXSrTermoReferencia = ".$row['FlOpeTermoReferencia'];
															if ($sSubCategorias <> ""){
																$sql .= " and SbCatId in (".$sSubCategorias.")";
															}
															$sql .=" ORDER BY SbCatNome, ServiNome ASC";
												} else { //Se $row['TrRefTabelaServico'] == ServicoOrcamento
													$sql = "SELECT ServiId, ServiNome, SrOrcDetalhamento as Detalhamento, SbCatNome
															FROM Servico
															JOIN ServicoOrcamento on SrOrcServico = ServiId
															LEFT JOIN SubCategoria on SbCatId = ServiSubCategoria
															JOIN TermoReferenciaXServico on TRXSrServico = SrOrcId
															WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " and TRXSrTermoReferencia = ".$row['FlOpeTermoReferencia'];
															if ($sSubCategorias <> ""){
																$sql .= " and SbCatId in (".$sSubCategorias.")";
															}
															$sql .=" ORDER BY SbCatNome, ServiNome ASC";
												}

												$result = $conn->query($sql);
												$rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);
												$countServico = count($rowServicos);
											}
										}	
										
										print('
										<div class="row" style="margin-bottom: -20px;">
											<div class="col-lg-9">
													<div class="row">
														<div class="col-lg-1">
															<label for="inputCodigo"><strong>Item</strong></label>
														</div>
														<div class="col-lg-5">
															<label for="inputServico"><strong>Servico</strong></label>
														</div>
														<div class="col-lg-2">
															<label for="inputServico"><strong>Marca</strong></label>
														</div>
														<div class="col-lg-2">
															<label for="inputServico"><strong>Modelo</strong></label>
														</div>
														<div class="col-lg-2">
															<label for="inputServico"><strong>Fabricante</strong></label>
														</div>
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

										print('<div id="tabelaServicos">');

										$cont = 0;
										$fTotalGeral = 0;

										foreach ($rowServicos as $item) {

											$cont++;
											$iUnidade = $_SESSION['UnidadeId'];
											$iEmpresa = $_SESSION['EmpreId'];

											$iQuantidade = isset($item['FOXSrQuantidade']) ? $item['FOXSrQuantidade'] : '';
											$fValorUnitario = isset($item['FOXSrValorUnitario']) ? mostraValor($item['FOXSrValorUnitario']) : '';
											$fValorTotal = (isset($item['FOXSrQuantidade']) and isset($item['FOXSrValorUnitario'])) ? mostraValor($item['FOXSrQuantidade'] * $item['FOXSrValorUnitario']) : '';

											$fTotalGeral += (isset($item['FOXSrQuantidade']) and isset($item['FOXSrValorUnitario'])) ? $item['FOXSrQuantidade'] * $item['FOXSrValorUnitario'] : 0;

											$sql = "SELECT MarcaId, MarcaNome
													FROM Marca
													JOIN Situacao on SituaId = MarcaStatus
													WHERE MarcaEmpresa = $iEmpresa and SituaChave = 'ATIVO'
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

											$HTML_MARCA = '';
											$HTML_MODELO = '';
											$HTML_FABRICANTE = '';

											// vai buscar na tabela ServicoXFabricante os dados caso esse fluxo ja tenha sido liberado

											$sqlSrXFa = "SELECT SrXFaId, SrXFaMarca, SrXFaModelo, SrXFaFabricante
													FROM ServicoXFabricante
													WHERE SrXFaServico = $item[ServiId] and SrXFaFluxoOperacional = $iFluxoOperacional and  SrXFaUnidade = $iUnidade";
											$resultSrXFa = $conn->query($sqlSrXFa);
											$resultSrXFa = $resultSrXFa->fetch(PDO::FETCH_ASSOC);

											foreach($rowMarca as $marca){
												$seleciona = "";
												if(isset($resultSrXFa['SrXFaMarca'])){
													$seleciona = ($resultSrXFa['SrXFaMarca'] == $marca['MarcaId']) ? "selected " : "";
												}
												$HTML_MARCA .= '<option value="'.$marca['MarcaId'].'" '.$seleciona.'>'.$marca['MarcaNome'].'</option>';
											}

											foreach($rowModelo as $modelo){
												$seleciona = "";
												if(isset($resultSrXFa['SrXFaModelo'])){
													$seleciona = ($resultSrXFa['SrXFaModelo'] == $modelo['ModelId']) ? "selected " : "";
												}
												$HTML_MODELO .= '<option value="'.$modelo['ModelId'].'" '.$seleciona.'>'.$modelo['ModelNome'].'</option>';
											}

											foreach($rowFabricante as $fabricante){
												$seleciona = "";
												if(isset($resultSrXFa['SrXFaFabricante'])){
													$seleciona = ($resultSrXFa['SrXFaFabricante'] == $fabricante['FabriId']) ? "selected " : "";
												}
												$HTML_FABRICANTE .= '<option value="'.$fabricante['FabriId'].'" '.$seleciona.'>'.$fabricante['FabriNome'].'</option>';
											}

											print('
												<div class="row" style="margin-top: 8px;">
													<div class="col-lg-9">
														<div class="row">
															<div class="col-lg-1">
																<input type="text" id="inputItem' . $cont . '" name="inputItem' . $cont . '" class="form-control-border-off" value="' . $cont . '" readOnly>
																<input type="hidden" id="inputIdServico' . $cont . '" name="inputIdServico' . $cont . '" value="' . $item['ServiId'] . '" class="idServico">
															</div>
															<div class="col-lg-5">
																<input type="text" id="inputServico' . $cont . '" name="inputServico' . $cont . '" class="form-control-border-off" data-popup="tooltip" title="'. substr($item['Detalhamento'],0,380).'..." value="' . $item['ServiNome'] . '" readOnly>
																<input type="hidden" id="inputDetalhamento' . $cont . '" name="inputDetalhamento' . $cont . '" value="' . $item['Detalhamento'] . '">
															</div>
															<div class="col-lg-2">
																<select required id="inputMarca'.$cont.'" name="inputMarca'.$cont.'"'.($row['SituaChave'] == 'LIBERADO'?' disabled ':'').'class="form-control select-search">
																	<option value="">Selecione</option>
																	'.$HTML_MARCA.'
																</select>
															</div>
															<div class="col-lg-2">
																<select required id="inputModelo'.$cont.'" name="inputModelo'.$cont.'"'.($row['SituaChave'] == 'LIBERADO'?' disabled ':'').'class="form-control select-search">
																	<option value="">Selecione</option>
																	'.$HTML_MODELO.'
																</select>
															</div>
															<div class="col-lg-2">
																<select required id="inputFabricante'.$cont.'" name="inputFabricante'.$cont.'"'.($row['SituaChave'] == 'LIBERADO'?' disabled ':'').'class="form-control select-search">
																	<option value="">Selecione</option>
																	'.$HTML_FABRICANTE.'
												 				</select>
															</div>
														</div>
													</div>');

											print('<div class="col-lg-1">
														<input type="text" id="inputQuantidade' . $cont . '" name="inputQuantidade' . $cont . '" class="form-control-border Quantidade text-right pula" onChange="calculaValorTotal()" value="' . $iQuantidade . '">
													</div>	
													<div class="col-lg-1">
														<input type="text" id="inputValorUnitario' . $cont . '" name="inputValorUnitario' . $cont . '" class="form-control-border ValorUnitario text-right pula" onChange="calculaValorTotal()" onKeyUp="moeda(this)" maxLength="12" value="' . $fValorUnitario . '">
													</div>	
													<div class="col-lg-1">
														<input type="text" id="inputValorTotal' . $cont . '" name="inputValorTotal' . $cont . '" class="form-control-border-off text-right" value="' . $fValorTotal . '" readOnly>
													</div>											
												</div>');
										}

										print('
											<div class="row" style="margin-top: 8px;">
													<div class="col-lg-8">
														<div class="row">
															<div class="col-lg-1">
																
															</div>
															<div class="col-lg-5">
																
															</div>
															<div class="col-lg-2">
																
															</div>
															<div class="col-lg-2">
																
															</div>
															<div class="col-lg-2">
																
															</div>
														</div>
													</div>
													<div class="col-lg-1">
														
													</div>	
													<div class="col-lg-1" style="padding-top: 5px; text-align: right;">
														<h5><b>Total:</b></h5>
													</div>	
													<div class="col-lg-2">
														<input type="text" id="inputTotalGeral" name="inputTotalGeral" class="form-control-border-off text-right" value="' . mostraValor($fTotalGeral) . '" readOnly>
													</div>											
												</div>');

										print('<input type="hidden" id="totalRegistros" name="totalRegistros" value="' . $cont . '" >');

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
												} else {
													if ($countServico) {
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
													} else {
														print('<button class="btn btn-lg btn-principal" id="enviar" disabled>Alterar</button>');
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
										if ($row['FluxoFechado'] && $row['SituaChave'] != 'LIBERADO'){
											if ($row['SituaNome'] == 'PENDENTE') {
												print('<i class="icon-info3" data-popup="tooltip" data-placement="bottom"></i>Preenchimento Concluído (ENVIE PARA APROVAÇÃO)');
											} else {
												print('<i class="icon-info3" data-popup="tooltip" data-placement="bottom"></i>Preenchimento Concluído (' . $row['SituaNome'] . ')');
											}
										} else if (!$countServico) {
											print('<i class="icon-info3" data-popup="tooltip" data-placement="bottom"></i>Não há serviços cadastrados para a Categoria e SubCategoria informada');
										} else if ($TotalFluxo < $TotalGeral) {
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