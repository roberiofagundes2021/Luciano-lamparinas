<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'TR Serviço';

include('global_assets/php/conexao.php');

//Se veio do tr.php
if (isset($_POST['inputTRId'])) {
	$iTR = $_POST['inputTRId'];
	$iCategoria = $_POST['inputTRCategoria'];
} else if (isset($_POST['inputIdTR'])) {
	$iTR = $_POST['inputIdTR'];
	$iCategoria = $_POST['inputIdCategoria'];
} else {
	irpara("tr.php");
}

//Se está alterando
if (isset($_POST['inputIdTR'])) {

	try{
		$conn->beginTransaction();

		$sql = "DELETE FROM TermoReferenciaXServico
				WHERE TRXSrTermoReferencia = :iTR AND TRXSrUnidade = :iUnidade";
		$result = $conn->prepare($sql);
		$result->execute(array(
			':iTR' => $iTR,
			':iUnidade' => $_SESSION['UnidadeId']
		));

		for ($i = 1; $i <= $_POST['totalRegistros']; $i++) {

			$sql = "INSERT INTO TermoReferenciaXServico (TRXSrTermoReferencia, TRXSrServico, TRXSrDetalhamento, TRXSrQuantidade, TRXSrValorUnitario, TRXSrTabela, TRXSrUsuarioAtualizador, TRXSrUnidade)
					VALUES (:iTR, :iServico, :sDetalhamento, :iQuantidade, :fValorUnitario, :sTabela, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);

			$result->execute(array(
				':iTR' 					=> $iTR,
				':iServico' 			=> $_POST['inputIdServico' . $i],
				':sDetalhamento' 	    => $_POST['inputDetalhamento' . $i],
				':iQuantidade' 			=> $_POST['inputQuantidade' . $i] == '' ? null : $_POST['inputQuantidade' . $i],
				':fValorUnitario'		=> null,
				':sTabela' 				=> $_POST['inputTabelaServico' . $i],
				':iUsuarioAtualizador' 	=> $_SESSION['UsuarId'],
				':iUnidade' 			=> $_SESSION['UnidadeId']
			));
		}

		/* Verifica e remove dados da Bandeja */
		$sql = "DELETE FROM Bandeja
				WHERE BandeTabelaId = :iTR AND BandeUnidade = :iUnidade and BandePerfil = 'CENTROADMINISTRATIVO' ";
		$result = $conn->prepare($sql);
		$result->execute(array(
			':iTR' 		=> $iTR,
			':iUnidade' => $_SESSION['UnidadeId']
		));

		/* Verifica e remove dados da tabela BandejaXPerfil */
		$sql = "DELETE FROM BandejaXPerfil
				WHERE BnXPeBandeja = :iTR AND BnXPeUnidade = :iUnidade ";
		$result = $conn->prepare($sql);
		$result->execute(array(
			':iTR' 		=> $iTR,
			':iUnidade' => $_SESSION['UnidadeId']
		));

		/* Atualiza o Status do Termo de Referência */
		$sql = "UPDATE TermoReferencia
				SET TrRefStatus = (SELECT SituaId 
									FROM Situacao
									WHERE SituaChave = 'PENDENTE')
				WHERE TrRefId = :iTR AND TrRefUnidade = :iUnidade ";
		$result = $conn->prepare($sql);
		$result->execute(array(
			':iTR' 		=> $iTR,
			':iUnidade' => $_SESSION['UnidadeId']
		));	

		$sql = "INSERT INTO AuditTR ( AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento)
				VALUES (:iTRTermoReferencia, :iTRDataHora, :iTRUsuario, :iTRTela, :iTRDetalhamento)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
			':iTRTermoReferencia' => $iTR ,
			':iTRDataHora' => date("Y-m-d H:i:s"),
			':iTRUsuario' => $_SESSION['UsuarId'],
			':iTRTela' =>'TR / LISTAR SERVIÇO',
			':iTRDetalhamento' =>'ATUALIZAÇÃO'
		));


		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "TR alterado!!!";
		$_SESSION['msg']['tipo'] = "success";

	} catch(PDOException $e){
		
		$conn->rollback();

		$_SESSION['msg']['titulo'] 	 = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar o TR!!!";
		$_SESSION['msg']['tipo'] 	 = "error";	

		//alerta('Error1: ' . $e->getMessage());
	}
}

//Verifica se o TR já possui Orçamentos para travar a edição dos campos
$sql = "SELECT TrXOrId
		FROM TRXOrcamento
		WHERE TrXOrUnidade = " . $_SESSION['UnidadeId'] . " and TrXOrTermoReferencia = ".$iTR;
$result = $conn->query($sql);
$rowOrcamentosTR = $result->fetchAll(PDO::FETCH_ASSOC);

// Select para o TR.
$sql = "SELECT *
		FROM TermoReferencia
		JOIN Categoria on CategId = TrRefCategoria
		JOIN Situacao on SituaId = TrRefStatus
		WHERE TrRefUnidade = " . $_SESSION['UnidadeId'] . " and TrRefId = " . $iTR;
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

//Retorna todas as Subcategorias do TR, se houver
$sql = "SELECT TRXSCSubcategoria, SbCatId, SbCatNome
		FROM TRXSubcategoria
		JOIN SubCategoria on SbCatId = TRXSCSubcategoria
		WHERE TRXSCTermoReferencia = " . $iTR . " and TRXSCUnidade = " . $_SESSION['UnidadeId'] . "
		ORDER BY SbCatNome ASC";
$result = $conn->query($sql);
$rowSubCat = $result->fetchAll(PDO::FETCH_ASSOC);

$aSubCategorias = '';

foreach ($rowSubCat as $item) {

	if ($aSubCategorias == '') {
		$aSubCategorias .= $item['SbCatId'];
	} else {
		$aSubCategorias .= ", ".$item['SbCatId'];
	}
}

//Select que verifica a tabela de origem dos servicos dessa TR.
$sql = "SELECT TRXSrServico
		FROM TermoReferenciaXServico
		JOIN ServicoOrcamento on SrOrcId = TRXSrServico
		WHERE TRXSrUnidade = " . $_SESSION['UnidadeId'] . " and TRXSrTermoReferencia = " . $iTR . " and TRXSrTabela = 'ServicoOrcamento'";
$result = $conn->query($sql);
$rowServicoOrcamentoUtilizado = $result->fetchAll(PDO::FETCH_ASSOC);
$countServicoOrcamentoUtilizado = count($rowServicoOrcamentoUtilizado);

if (count($rowServicoOrcamentoUtilizado) >= 1) {
	foreach ($rowServicoOrcamentoUtilizado as $itemServicoOrcamentoUtilizado) {
		$aServicosOrcamento[] = $itemServicoOrcamentoUtilizado['TRXSrServico'];
	}
} else {
	$aServicosOrcamento = [];
}

$sql = "SELECT TRXSrServico
		FROM TermoReferenciaXServico
		JOIN Servico on ServiId = TRXSrServico
		WHERE TRXSrUnidade = " . $_SESSION['UnidadeId'] . " and TRXSrTermoReferencia = " . $iTR . " and TRXSrTabela = 'Servico'";
$result = $conn->query($sql);
$rowServicoUtilizado = $result->fetchAll(PDO::FETCH_ASSOC);
$countServicoUtilizado = count($rowServicoUtilizado);

if (count($rowServicoUtilizado) >= 1) {
	foreach ($rowServicoUtilizado as $itemServicoUtilizado) {
		$aServicos[] = $itemServicoUtilizado['TRXSrServico'];
	}
} else {
	$aServicos[] = [];
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Listando serviços do TR</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>

	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>
	<!-- /theme JS files -->

	<!-- Adicionando Javascript -->
	<script type="text/javascript">
		$(document).ready(function() {

			//Ao mudar a SubCategoria, filtra o servico via ajax (retorno via JSON)
			$('#cmbServico').on('change', function(e) {

				var inputCategoria = $('#inputIdCategoria').val();
				var inputSubCategoria = $('#inputSubCategoria').val();
				var servicos = $(this).val();
				console.log(servicos)
				var tr = $('#inputIdTR').val();
				//console.log(servicos);

				var cont = 1;
				var servicoId = [];
				var servicoQuant = [];

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

				$.ajax({
					type: "POST",
					url: "trFiltraServico.php",
					data: {
						idTr: tr,
						idCategoria: inputCategoria,
						idSubCategoria: inputSubCategoria,
						servicos: servicos,
						servicoId: servicoId,
						servicoQuant: servicoQuant
					},
					success: function(resposta) {
						//alert(resposta);
						console.log(resposta)
						$("#tabelaServicos").html(resposta).show();

						return false;

					}
				});
			});

			/* ao pressionar uma tecla em um campo que seja de class="pula" */
			// $('.pula').keypress(function(e){
			// 	/*
			// 		* verifica se o evento é Keycode (para IE e outros browsers)
			// 		* se não for pega o evento Which (Firefox)
			// 	*/
			// 	var tecla = (e.keyCode?e.keyCode:e.which);

			// 	/* verifica se a tecla pressionada foi o ENTER */
			// 	if(tecla == 13){
			// 		/* guarda o seletor do campo que foi pressionado Enter */
			// 		campo =  $('.pula');
			// 		/* pega o indice do elemento*/
			// 		indice = campo.index(this);
			// 		/*soma mais um ao indice e verifica se não é null
			// 		*se não for é porque existe outro elemento
			// 		*/
			// 		if(campo[indice+1] != null){
			// 			/* adiciona mais 1 no valor do indice */
			// 			proximo = campo[indice + 1];
			// 			/* passa o foco para o proximo elemento */
			// 			proximo.focus();
			// 		}
			// 	} else {
			// 		return onlynumber(e);
			// 	}

			// 	/* impede o sumbit caso esteja dentro de um form */
			// 	e.preventDefault(e);
			// 	return false;
            // });				

			function disabledSelect(){
				let btnSubmit = $('#btnsubmit')
				let selectServicos = $('#ServicoRow')

				if(btnSubmit.attr('disabled')){
                    selectServicos.css('display', 'none')
				}
			}
			disabledSelect()

		}); //document.ready

		function pula(e){
			/*
			* verifica se o evento é Keycode (para IE e outros browsers)
			* se não for pega o evento Which (Firefox)
			*/
			var tecla = (e.keyCode?e.keyCode:e.which);

			/* verifica se a tecla pressionada foi o ENTER */
			if(tecla == 13){
				/* guarda o seletor do campo que foi pressionado Enter */
				var array_campo = document.getElementsByClassName('pula');

				/* pega o indice do elemento*/
				var id = e.path[0].id.split('inputQuantidade')
				id = 'inputQuantidade' + (parseInt(id[1])+1)

				/*soma mais um ao indice e verifica se não é null
				*se não for é porque existe outro elemento
				*/

				if(document.getElementById(id)){
					document.getElementById(id).focus()
				}
			} else {
				return onlynumber(e);
			}

			/* impede o sumbit caso esteja dentro de um form */
			e.preventDefault(e);
			return false;
		}

		//Mostra o "Filtrando..." na combo Servico
		function FiltraServico() {
			$('#cmbServico').empty().append('<option>Filtrando...</option>');
		}

		function ResetServico() {
			$('#cmbServico').empty().append('<option>Sem servico</option>');
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
					<form name="formTRServico" id="formTRServico" method="post" class="form-validate">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Listar Serviços - TR Nº "<?php echo $row['TrRefNumero']; ?>"</h5>
						</div>
						<input type="hidden" id="inputIdTR" name="inputIdTR" class="form-control" value="<?php echo $row['TrRefId']; ?>">
						<div class="card-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputCategoriaNome">Categoria</label>
												<input type="text" id="inputCategoriaNome" name="inputCategoriaNome" class="form-control" value="<?php echo $row['CategNome']; ?>" readOnly>
												<input type="hidden" id="inputIdCategoria" name="inputIdCategoria" class="form-control" value="<?php echo $row['TrRefCategoria']; ?>">
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
									<div id="ServicoRow" class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="cmbServico">Serviço</label>
												<select id="cmbServico" name="cmbServico" class="form-control multiselect-filtering" multiple="multiple" data-fouc>
													<?php
													if ($row['TrRefTabelaServico'] == 'ServicoOrcamento') {
														if (count($rowSubCat) >= 1) {
															foreach ($rowSubCat as $valueSubCat) {
																$sql = "
																	SELECT SrOrcId, SrOrcNome
																	FROM ServicoOrcamento
																	JOIN Situacao ON SituaId = SrOrcSituacao				     
																	WHERE SrOrcSubCategoria = " . $valueSubCat['TRXSCSubcategoria'] . " 
																	AND SituaChave = 'ATIVO' 
																	AND SrOrcEmpresa = " . $_SESSION['EmpreId'] . " 
																	AND SrOrcCategoria = " . $iCategoria;
																/*	
																if (isset($row['TrRefSubCategoria']) and $row['TrRefSubCategoria'] != '' and $row['TrRefSubCategoria'] != null) {
																	$sql .= " and SrOrcSubCategoria = " . $row['TrRefSubCategoria'];
																}*/
																$sql .= " ORDER BY SrOrcNome ASC";
																$result = $conn->query($sql);
																$rowServico = $result->fetchAll(PDO::FETCH_ASSOC);
																foreach ($rowServico as $item) {
																	if (in_array($item['SrOrcId'], $aServicosOrcamento) or $countServicoOrcamentoUtilizado == 0) {
																		$seleciona = "selected";
																		print('<option value="' . $item['SrOrcId'] . '" ' . $seleciona . '>' . $item['SrOrcNome'] . '</option>');
																	} else {
																		$seleciona = "";
																		print('<option value="' . $item['SrOrcId'] . '" ' . $seleciona . '>' . $item['SrOrcNome'] . '</option>');
																	}
																}
															}
														} else{
															$sql = "
																SELECT SrOrcId, SrOrcNome
																FROM ServicoOrcamento
																JOIN Situacao ON SituaId = SrOrcSituacao		
																WHERE SrOrcEmpresa = " . $_SESSION['EmpreId'] . " 
																AND SituaChave = 'ATIVO' 
																AND SrOrcCategoria = " . $iCategoria;
															$sql .= " ORDER BY SrOrcNome ASC";
															$result = $conn->query($sql);
															$rowServicoOrcamento = $result->fetchAll(PDO::FETCH_ASSOC);

															foreach ($rowServicoOrcamento as $item) {
																if (in_array($item['SrOrcId'], $aServicosOrcamento)) {
																	$seleciona = "selected";
																	print('<option value="' . $item['SrOrcId'] . '" ' . $seleciona . '>' . $item['SrOrcNome'] . '</option>');
																} else {
																	$seleciona = "";
																	print('<option value="' . $item['SrOrcId'] . '" ' . $seleciona . '>' . $item['SrOrcNome'] . '</option>');
																}
															}															
														}
													} else {
														if (count($rowSubCat) >= 1) {
															foreach ($rowSubCat as $subcategoria) {
																$sql = "
																	SELECT ServiId, ServiNome
																    FROM Servico
																	JOIN Situacao ON SituaId = ServiStatus		
																    WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " 
																	AND SituaChave = 'ATIVO' 
																	AND ServiCategoria = " . $iCategoria . "
																";
																if ($subcategoria['TRXSCSubcategoria'] != '' and $subcategoria['TRXSCSubcategoria'] != null) {
																	$sql .= " and ServiSubCategoria = " . $subcategoria['TRXSCSubcategoria'];
																}
																$sql .= " ORDER BY ServiNome ASC";
																$result = $conn->query($sql);
																$rowServico = $result->fetchAll(PDO::FETCH_ASSOC);

																foreach ($rowServico as $item) {
																	if (in_array($item['ServiId'], $aServicos)) {
																		$seleciona = "selected";
																		print('<option value="' . $item['ServiId'] . '" ' . $seleciona . '>' . $item['ServiNome'] . '</option>');
																	} else {
																		$seleciona = "";
																		print('<option value="' . $item['ServiId'] . '" ' . $seleciona . '>' . $item['ServiNome'] . '</option>');
																	}
																}
															}
														} else{
															$sql = "
																SELECT ServiId, ServiNome
															    FROM Servico
																JOIN Situacao ON SituaId = ServiStatus		
															    WHERE ServiEmpresa = " . $_SESSION['EmpreId'] . " 
																AND SituaChave = 'ATIVO' 
																AND ServiCategoria = " . $iCategoria . "
															";
															$sql .= " ORDER BY ServiNome ASC";
															$result = $conn->query($sql);
															$rowServico = $result->fetchAll(PDO::FETCH_ASSOC);

															foreach ($rowServico as $item) {
																if (in_array($item['ServiId'], $aServicos)) {
																	$seleciona = "selected";
																	print('<option value="' . $item['ServiId'] . '" ' . $seleciona . '>' . $item['ServiNome'] . '</option>');
																} else {
																	$seleciona = "";
																	print('<option value="' . $item['ServiId'] . '" ' . $seleciona . '>' . $item['ServiNome'] . '</option>');
																}
															}

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
									<h5 class="card-title">Relação de Serviços</h5>
								</div>

								<div class="card-body">
									<p class="mb-3">Abaixo estão listados todos os serviços selecionadas acima. Para atualizar os valores, basta preencher a coluna <code>Quantidade</code> e depois clicar em <b>ALTERAR</b>.</p>

									<!--<div class="hot-container">
										<div id="example"></div>
									</div>-->

									<?php

									if ($row['TrRefTabelaServico'] == 'ServicoOrcamento') {

										$sql = "SELECT SrOrcId, SrOrcNome, TRXSrDetalhamento,
												TRXSrQuantidade, TRXSrTabela
												FROM ServicoOrcamento
												JOIN TermoReferenciaXServico on TRXSrServico = SrOrcId
												JOIN SubCategoria on SbCatId = SrOrcSubCategoria
												WHERE SrOrcEmpresa = " . $_SESSION['EmpreId'] . " 
												AND TRXSrTermoReferencia = " . $iTR  . " 
												AND TRXSrTabela = 'ServicoOrcamento' 
												Order By SbCatNome, SrOrcNome ASC";
										$result = $conn->query($sql);
										$rowServicosOrcamento = $result->fetchAll(PDO::FETCH_ASSOC);
										//echo $sql;die;
										$cont = 0;

										print('
											<div class="row" style="margin-bottom: -20px;">
												<div class="col-lg-10">
													<div class="row">
														<div class="col-lg-1">
															<label for="inputCodigo"><strong>Item</strong></label>
														</div>
														<div class="col-lg-11">
															<label for="inputServico"><strong>Serviço</strong></label>
														</div>
													</div>
												</div>
												<div class="col-lg-2">
													<div class="form-group">
														<label for="inputQuantidade"><strong>Quantidade</strong></label>
													</div>
												</div>	
											</div>');

										print('<div id="tabelaServicos">');

										foreach ($rowServicosOrcamento as $item) {

											$cont++;

											$iQuantidade = isset($item['TRXSrQuantidade']) ? $item['TRXSrQuantidade'] : '';
										
											print('
												<div class="row" style="margin-top: 8px;">
													<div class="col-lg-10">
														<div class="row">
															<div class="col-lg-1">
																<input type="text" id="inputItem' . $cont . '" name="inputItem' . $cont . '" class="form-control-border-off" value="' . $cont . '" readOnly>
																<input type="hidden" id="inputIdServico' . $cont . '" name="inputIdServico' . $cont . '" value="' . $item['SrOrcId'] . '" class="idServico">
															</div>

															<div class="col-lg-11">
																<input type="text" id="inputServico' . $cont . '" name="inputServico' . $cont . '" class="form-control-border-off" data-popup="tooltip" title="' . $item['TRXSrDetalhamento'] . '" value="' . $item['SrOrcNome'] . '" readOnly>
																<input type="hidden" id="inputDetalhamento' . $cont . '" name="inputDetalhamento' . $cont . '" value="' . $item['TRXSrDetalhamento'] . '">
															</div>
														</div>
													</div>
											');

											if(count($rowOrcamentosTR) >= 1) {
												print('
														<div class="col-lg-2">
															<input type="text" id="inputQuantidade' . $cont . '" name="inputQuantidade' . $cont . '" class="form-control-border Quantidade pula" onkeypress="pula(event)" value="' . $iQuantidade . '" readOnly>
														</div>	
												');
											} else {
												print('
														<div class="col-lg-2">
															<input type="text" id="inputQuantidade' . $cont . '" name="inputQuantidade' . $cont . '" class="form-control-border Quantidade pula" onkeypress="pula(event)" value="' . $iQuantidade . '">
														</div>	
												');
											}
											print('
													</div>
											');

											print('<input type="hidden" id="inputTabelaServico' . $cont . '" name="inputTabelaServico' . $cont . '" value="' . $item['TRXSrTabela'] . '">');
										}

										print('<input type="hidden" id="totalRegistros" name="totalRegistros" value="' . $cont . '" >');

										print('</div>');

									} else {

										$sql = "SELECT TRXSrQuantidade,	TRXSrTabela, ServiId, 
												ServiNome, TRXSrDetalhamento
												FROM TermoReferenciaXServico
												JOIN Servico ON ServiId = TRXSrServico
												JOIN SubCategoria on SbCatId = ServiSubCategoria
												WHERE TRXSrUnidade = " . $_SESSION['UnidadeId'] . " 
												AND TRXSrTermoReferencia = " . $iTR . " 
												AND TRXSrTabela = 'Servico'	
												Order By SbCatNome, ServiNome ASC";
										$result = $conn->query($sql);
										$rowServicos = $result->fetchAll(PDO::FETCH_ASSOC);
										$count = count($rowServicos);

										$cont = 0;

										print('
											<div class="row" style="margin-bottom: -20px;">
												<div class="col-lg-10">
													<div class="row">
														<div class="col-lg-1">
															<label for="inputCodigo"><strong>Item</strong></label>
														</div>
														<div class="col-lg-11">
															<label for="inputServico"><strong>Serviço</strong></label>
														</div>
													</div>
												</div>												
												<div class="col-lg-2">
													<div class="form-group">
														<label for="inputQuantidade"><strong>Quantidade</strong></label>
													</div>
												</div>	
											</div>');

										print('<div id="tabelaServicos">');

										foreach ($rowServicos as $item) {

											$cont++;

											$iQuantidade = isset($item['TRXSrQuantidade']) ? $item['TRXSrQuantidade'] : '';

											print('
													<div class="row" style="margin-top: 8px;">
														<div class="col-lg-10">
															<div class="row">
																<div class="col-lg-1">
																	<input type="text" id="inputItem' . $cont . '" name="inputItem' . $cont . '" class="form-control-border-off" value="' . $cont . '" readOnly>																	
																	<input type="hidden" id="inputIdServico' . $cont . '" name="inputIdServico' . $cont . '" value="' . $item['ServiId'] . '" class="idServico">																	
																	<input type="hidden" id="inputTabelaServico' . $cont . '" name="inputTabelaServico' . $cont . '" value="' . $item['TRXSrTabela'] . '">
																</div>

																<div class="col-lg-11">
																	<input type="text" id="inputServico' . $cont . '" name="inputServico' . $cont . '" class="form-control-border-off" data-popup="tooltip" title="' . $item['TRXSrDetalhamento'] . '" value="' . $item['ServiNome'] . '" readOnly>
																	<input type="hidden" id="inputDetalhamento' . $cont . '" name="inputDetalhamento' . $cont . '" value="' . $item['TRXSrDetalhamento'] . '">
																</div>
															</div>
														</div>	
											');

											if(count($rowOrcamentosTR) >= 1) {
												print('
														<div class="col-lg-2">
															<input type="text" id="inputQuantidade' . $cont . '" name="inputQuantidade' . $cont . '" class="form-control-border Quantidade pula" onkeypress="pula(event)" value="' . $iQuantidade . '" readOnly>
														</div>	
												');
											} else {
												print('
														<div class="col-lg-2">
															<input type="text" id="inputQuantidade' . $cont . '" name="inputQuantidade' . $cont . '" class="form-control-border Quantidade pula" onkeypress="pula(event)" value="' . $iQuantidade . '">
														</div>	
												');
											}
											print('
													</div>
											');

											print('<input type="hidden" id="inputTabelaServico' . $cont . '" name="inputTabelaServico' . $cont . '" value="' . $item['TRXSrTabela'] . '">');
										}

										print('<input type="hidden" id="totalRegistros" name="totalRegistros" value="' . $cont . '" >');

										print('</div>');
									}

									?>
								</div>
							</div>
							<!-- /custom header text -->
							<?php 
							
							    if(count($rowOrcamentosTR) >= 1){
									print('
									<div class="row" style="margin-top: 10px;">
								        <div class="row justify-content-center col-lg-12">
									        <div class="form-group col-12 col-lg-6">
										        <button id="btnsubmit" class="btn btn-lg btn-principal" disabled type="submit">Alterar</button>
										        <a href="tr.php" class="btn btn-basic" role="button">Cancelar</a>
											</div>
											<div class="row justify-content-end align-content-center col-12 col-lg-6">
											    <p style="color: red; margin: 0px"><i class="icon-info3"></i>A lista de serviços não pode ser alterada enquanto houver orçamentos para esse Termo de Referência.</p>
										    </div>
								        </div>
							        </div>
									');
								} else {
									print('
									<div class="row" style="margin-top: 10px;">
								        <div class="col-lg-12">
									        <div class="form-group">
										        <button class="btn btn-lg btn-principal" type="submit">Alterar</button>
										        <a href="tr.php" class="btn btn-basic" role="button">Cancelar</a>
									        </div>
								        </div>
							        </div>
									');
								}
							 
							?>
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