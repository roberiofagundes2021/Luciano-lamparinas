<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Nova Ordem de Compra';

include('global_assets/php/conexao.php');

$sql = "SELECT UsuarId, UsuarNome, UsuarEmail, UsuarTelefone
		FROM Usuario
		Where UsuarId = ".$_SESSION['UsuarId']."
		ORDER BY UsuarNome ASC";
$result = $conn->query($sql);
$rowUsuario = $result->fetch(PDO::FETCH_ASSOC);

$sqlFluxo = "SELECT FlOpeId, FlOpeTermoReferencia, FlOpeFornecedor, FlOpeCategoria, FlOpeSubCategoria, FlOpeDataInicio,
			FlOpeDataFim, FlOpeNumContrato, FlOpeNumProcesso, FlOpeModalidadeLicitacao, FlOpeValor, FlOpeObservacao,
			FlOpePrioridade, FlOpeNumAta, FlOpeConteudoInicio, FlOpeConteudoFim, FlOpeStatus, FlOpeUsuarioAtualizador, FlOpeEmpresa,
			FlOpeUnidade, SituaChave, ForneId, ForneRazaoSocial, ForneNome, ForneContato, ForneEmail, ForneTelefone, ForneCelular,
			CategId, CategNome, CategStatus, CategUsuarioAtualizador, CategEmpresa
			FROM FluxoOperacional
			JOIN Situacao on SituaId = FlOpeStatus
			JOIN Fornecedor on ForneId = FlOpeFornecedor
			JOIN Categoria on CategId = FlOpeCategoria
			WHERE FlOpeUnidade = ".$_SESSION['UnidadeId'].
			" and UPPER(SituaChave) = 'LIBERADO'";
$resultFluxo = $conn->query($sqlFluxo);
$fluxo = $resultFluxo->fetchAll(PDO::FETCH_ASSOC);

foreach($fluxo as $key=>$flx){
	if($flx['FlOpeTermoReferencia']){
		$sqlTermo = "SELECT TrRefId, TrRefTipo, TrRefNumero, TrRefData, TrRefCategoria, TrRefConteudoInicio,
								TrRefStatus, TrRefUnidade, TrRefTabelaProduto, TrRefTabelaServico, TrRefLiberaParcial
								FROM TermoReferencia where TrRefId = ".$flx['FlOpeTermoReferencia'];
		$termoRef = $conn->query($sqlTermo);
		$termo = $termoRef->fetch(PDO::FETCH_ASSOC);
		$fluxo[$key]['FlOpeTermoReferencia'] = $termo;
	}
	if($flx['FlOpeCategoria']){
		$sqlSubCat = "SELECT SbCatId, SbCatNome, SbCatCategoria, SbCatStatus, SbCatEmpresa
								FROM SubCategoria
								where SbCatCategoria = ".$flx['FlOpeCategoria'];
		$SubCat = $conn->query($sqlSubCat);
		$SubCateg = $SubCat->fetchAll(PDO::FETCH_ASSOC);
		$fluxo[$key]['FlOpeSubCategoria'] = $SubCateg;
	}
}

$sqlParametroEmp = "SELECT ParamEmpresaPublica 
                   FROM Parametro
                   WHERE ParamEmpresa = ".$_SESSION['EmpreId'];
$resultParametroEmp = $conn->query($sqlParametroEmp);
$parametroEmp = $resultParametroEmp->fetch(PDO::FETCH_ASSOC);	

$empresaType = $parametroEmp['ParamEmpresaPublica'] ? 'publica' : 'privada';


	if ($parametroEmp['ParamEmpresaPublica']){
		$ordemCompra = "CONTRATO";
		$lote= "Nº Ata/Lote";
		$contrato = "Contrato";

	} else {
		$ordemCompra = " ";
		$lote = "Nº Lote";
		$contrato = "Nº Fluxo";
	}

if(isset($_POST['inputData'])){
	
	try{
		// pega o ultimo OrComNumero da tabela  OrdemCompra e incrementa +1 ao valor,
		// lembrando que esse valor é para cada contrato
		$sqlNumero = "SELECT Max(CAST(OrComNumero AS int))
		FROM OrdemCompra where OrComUnidade = ".$_SESSION['UnidadeId']." and OrComFluxoOperacional = ".$_POST['inputFluxoOperacional'];
		$resultNumero = $conn->query($sqlNumero);
		$numero = $resultNumero->fetch(PDO::FETCH_ASSOC);

		// refatora o numero com 6 casas ex: 26 => 000026
		$newNumero = "";
		$number = intval($numero[""])+1;
		$cont = strlen($number)<6?6-strlen($number):0;

		for ($x=0; $x<$cont;$x++){
			$newNumero = $newNumero."0";
		}
		$newNumero = $newNumero.$number;
		// --------------------------------------------------------------
		$conn->beginTransaction();

		$sql = "SELECT SituaId
				FROM Situacao
				Where SituaChave = 'PENDENTE' ";
		$result = $conn->query($sql);
		$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);
		
		$sql = "INSERT INTO OrdemCompra (OrComTipo, OrComFluxoOperacional, OrComDtEmissao, OrComNumero, OrComLote, OrComNumAta, OrComNumProcesso, OrComCategoria, OrComSubCategoria, 
							OrComConteudoInicio, OrComConteudoFim, OrComFornecedor, OrComValorFrete, OrComSolicitante, OrComUnidadeEntrega, OrComLocalEntrega, 
							OrComEnderecoEntrega, OrComDtEntrega, OrComObservacao, OrComSaldoRemanescente, OrComSituacao, OrComUsuarioAtualizador, OrComUnidade)
				VALUES (:sTipo, :iFluxo, :dData, :sNumero, :sLote, :sNumAta, :sProcesso, :iCategoria, :iSubCategoria, :sConteudoInicio, :sConteudoFim, :iFornecedor, :fValorFrete, 
						:iSolicitante, :iUnidadeEntrega, :iLocalEntrega, :sEnderecoEntrega, :dDataEntrega, :sObservacao, :iSaldoRemanescente, :bStatus, 
						:iUsuarioAtualizador, :iUnidade)";
		$result = $conn->prepare($sql);
		
		$aFornecedor = explode("#",$_POST['cmbFornecedor']);
		$iFornecedor = $aFornecedor[0];		
		
		$result->execute(array(
						':sTipo' => $_POST['inputTipo'],
						':iFluxo' => $_POST['inputFluxoOperacional'],
						':dData' => gravaData($_POST['inputData']),
						':sNumero' => $newNumero,
						':sLote' => $_POST['inputLote'],
						':sNumAta' => $_POST['inputNumAta'],
						':sProcesso' => $_POST['inputProcesso'],
						':iCategoria' => $_POST['cmbCategoria'],
						':iSubCategoria' => $_POST['cmbSubCategoria'] == '' ? null : $_POST['cmbSubCategoria'],
						':sConteudoInicio' => $_POST['txtareaConteudoInicio'],
						':sConteudoFim' => $_POST['txtareaConteudoFim'],
						':iFornecedor' => $iFornecedor,
						':fValorFrete' => null,
						':iSolicitante' => $_SESSION['UsuarId'],
						':iUnidadeEntrega' => $_POST['cmbUnidade'] == '' ? null : $_POST['cmbUnidade'],
						':iLocalEntrega' => $_POST['cmbLocalEstoque'] == '' ? null : $_POST['cmbLocalEstoque'],
						':sEnderecoEntrega' => $_POST['inputEnderecoEntrega'],
						':dDataEntrega' => $_POST['inputDataEntrega'] == '' ? null : $_POST['inputDataEntrega'],
						':sObservacao' => $_POST['txtareaObservacao'],
						':iSaldoRemanescente' => isset($_POST['inputSaldoRemanescente']) ? $_POST['inputSaldoRemanescente'] : null,
						':bStatus' => $rowSituacao['SituaId'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iUnidade' => $_SESSION['UnidadeId']
						));

		$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Ordem de compra incluída!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir ordem de compra!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();die;
	}

	irpara("ordemcompra.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Ordem de Compra</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>	

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>
	<!-- /theme JS files -->

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

	
	<!-- Adicionando Javascript -->
	<script type="text/javascript" >
			<?php
			// essa parte transforma um array php em array js para que possa manipular dentro do JS com mais facilidade
				$js_fluxo = json_encode($fluxo);
				$emp = json_encode($empresaType);
				echo "const fluxo = ".$js_fluxo." \n";
				echo "var selectEmpresa = ".$emp." \n";
			?>

			$(document).ready(function() {
				// ao selecionar o contrato todos os campos serão preenchidos com os dados do contrato selecionado
				$('#cmbContrato').on('change', function(e){
				
					var cmbContrato = $('#cmbContrato').val();

					if(cmbContrato){
						// essa parte vai pegar dentro do array fluxo o objeto que possui o FlOpeId igual ao selecionado no select
						var id = cmbContrato
						var flux = fluxo.find(x => x.FlOpeId === id)

						var valueTag = flux.ForneId+'#'+flux.ForneContato+'#'+flux.ForneEmail+'#'+flux.ForneTelefone+'#'+flux.ForneCelular

						var Forne = valueTag.split('#');

						$("#inputFluxoOperacional").val(flux.FlOpeId);

						$('#cmbFornecedor').val(valueTag);
						$('#cmbFornecedorName').val(flux.ForneRazaoSocial);

						$('#inputProcesso').val(flux.FlOpeNumProcesso);

						$('#cmbCategoriaName').val(flux.CategNome);
						$('#cmbCategoria').val(flux.CategId);
						$('#inputNumAta').val(flux.FlOpeNumAta);
						$('#inputLote').val(flux.FlOpeNumAta);
						
						/*
						if (flux.Saldo > 0){
							getElementById("Mensagem").style.display = "block";
						} else{
							getElementById("Mensagem").style.display = "none";
						}*/

						$.getJSON('filtraSubCategoria.php?idContrato='+id, function (dados){
					
							var option = '<option value="">Selecione a SubCategoria</option>';
							
							if (dados.length){
								$("#cmbSubCategoria").prop('required',true);
								$("#cmbSubCategoriaName").last().html("SubCategoria <span class='text-danger'>*</span>");
								
								$.each(dados, function(i, obj){
									option += '<option value="'+obj.SbCatId+'">'+obj.SbCatNome+'</option>';
								});						
								
								$('#cmbSubCategoria').html(option).show();
							} else {
								ResetSubCategoria();
							}					
						});
						
						$('#inputContato').val(Forne[1]);
						$('#inputEmailFornecedor').val(Forne[2]);
						if(Forne[3] != "" && Forne[3] != "(__) ____-____"){
							$('#inputTelefoneFornecedor').val(Forne[3]);
						} else {
							$('#inputTelefoneFornecedor').val(Forne[4]);
						}	

						$.ajax({
							type: "POST",
							url: "ordemCompraSaldoRemanescente.php",
							data: ('IdFlOpe='+flux.FlOpeId),
							success: function(resposta){

								if(resposta == 1){
									document.getElementById('Mensagem').style.display = "block";
									document.getElementById('inputSaldoRemanescente').setAttribute('required', 'required')
								} else{
									document.getElementById('Mensagem').style.display = "none";
									document.getElementById('inputSaldoRemanescente').removeAttribute('required', 'required');	
								}
							}

						});
					}

					
				});
		
			$('#summernoteInicio').summernote();
			$('#summernoteFim').summernote();

			if(selectEmpresa !== 'publica'){
				$('#selectEmpresa').hide();
				$('#Ata').hide();
				$('#Lote').show();
			}
			
			//Ao mudar a categoria, filtra a subcategoria e produto via ajax (retorno via JSON)
			$('#cmbCategoria').on('change', function(e){
				
				Filtrando();
				
				var cmbCategoria = $('#cmbCategoria').val();

				$.getJSON('filtraSubCategoria.php?idCategoria='+cmbCategoria, function (dados){
					// essa parte filtra a categoria pertecente a unidade e que está no contrato/fluxo
					var option = '<option value="">Selecione a SubCategoria</option>';
					
					if (dados.length){						
						
						$.each(dados, function(i, obj){
							option += '<option value="'+obj.SbCatId+'">'+obj.SbCatNome+'</option>';
						});						
						
						$('#cmbSubCategoria').html(option).show();
					} else {
						ResetSubCategoria();
					}					
				});
				
			});
			
			//Ao mudar a categoria, filtra a subcategoria via ajax (retorno via JSON)
			$('#cmbUnidade').on('change', function(e){

				FiltraLocalEstoque();
				
				var cmbUnidade = $('#cmbUnidade').val();

				if (cmbUnidade == ''){
					ResetLocalEstoque();
				} else {
					$.getJSON('filtraLocalEstoque.php?idUnidade=' + cmbUnidade, function (dados){
						$.getJSON('filtraEnderecoEstoque.php?idUnidade=' + cmbUnidade, function (endereco){
							endereco = endereco.replace(', ,',',')
							$('#inputEnderecoEntrega').val(endereco)
						});

						var option = '';

						if (dados.length){
							$.each(dados, function(i, obj){
								option += '<option value="'+obj.LcEstId+'">' + obj.LcEstNome + '</option>';
							});
							
							$('#cmbLocalEstoque').html(option).show();
						} else {
							ResetLocalEstoque();
						}					
					});
				}
			});
			// essa parte é responsavel por pegar o maior nymero registrado na tabela de ordemCompra e adicionar +1 para que fique incremental
			$("#enviar").on('click', function(e){
				e.preventDefault();
				$("#formOrdemCompra").submit();
			});
		}); //document.ready
		
		//Mostra o "Filtrando..." na combo SubCategoria
		function Filtrando(){
			$('#cmbSubCategoria').empty().append('<option value="">Filtrando...</option>');
		}
		
		function FiltraLocalEstoque(){
			$('#cmbLocalEstoque').empty().append('<option value="">Filtrando...</option>');
		}		
		
		function ResetLocalEstoque(){
			$('#cmbLocalEstoque').empty().append('<option value="">Sem Local do Estoque</option>');
		}		
		
		function ResetCategoria(){
			$('#cmbCategoria').empty().append('<option value="">Sem Categoria</option>');
		}		
		
		function ResetSubCategoria(){
			$('#cmbSubCategoria').empty().append('<option value="">Sem Subcategoria</option>');
		}
		
		function selecionaTipo(tipo) {
			
			if (tipo == 'C'){
				document.getElementById('Ata').style.display = "block";
				document.getElementById('Lote').style.display = "none";
			} else {
				document.getElementById('Ata').style.display = "none";
				document.getElementById('Lote').style.display = "block";
			}
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
					
					<form name="formOrdemCompra" id="formOrdemCompra" method="post" class="form-validate-jquery" action="ordemcompraNovo.php">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Nova Ordem de Compra</h5>
						</div>

						<input type="hidden" id="inputFluxoOperacional" name="inputFluxoOperacional">
						
						<div class="card-body">								
								
							<div class="row">				
								
								<div class="col-lg-12">
									
									<div class="row">										
										<div class="col-lg-4">
											<div id="selectEmpresa" class="form-group">							
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" id="inputTipo" name="inputTipo" value="C" class="form-input-styled" checked data-fouc onclick="selecionaTipo('C')">
														Carta Contrato
													</label>
												</div>
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" id="inputTipo" name="inputTipo" value="O" class="form-input-styled" data-fouc onclick="selecionaTipo('O')">
														Ordem de Compra
													</label>
												</div>										
											</div>
											
										</div>	
									</div>

									<div class="row">
										<div class="<?php if ($ordemCompra == "CONTRATO") { echo "col-lg-3"; } else { echo "col-lg-5"; } ?>">
											<div class="form-group">
												<label for="cmbContrato"><?php echo $contrato; ?> <span class="text-danger">*</span></label>
												<select id="cmbContrato" name="cmbContrato" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<?php
														foreach ($fluxo as $item){
															print('<option value="'.$item['FlOpeId'].'">'.$item['FlOpeNumContrato'].'</option>');
														}
													?>
												</select>
											</div>
										</div>
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputData">Data da Emissão <span class="text-danger">*</span></label>
												<input type="text" id="inputData" name="inputData" class="form-control" value="<?php echo date('d/m/Y'); ?>" readOnly>
											</div>
										</div>
										
										<div class="col-lg-2" id="Ata">
											<div class="form-group">
												<label for="inputNumAta">Nº Ata Registro</label>
												<input type="text" id="inputNumAta" name="inputNumAta" class="form-control" readOnly>
											</div>
										</div>										
										
										<div class="<?php if ($ordemCompra == "CONTRATO") { echo "col-lg-3"; } else { echo "col-lg-5"; } ?>" id="Lote" style="display:none">
											<div class="form-group">
												<label for="inputLote">Lote</label>
												<input type="text" id="inputLote" name="inputLote" class="form-control" readOnly>
											</div>
										</div>

										<?php
											if ($ordemCompra == "CONTRATO"){	
												print('
											<div class="col-lg-4">
												<div class="form-group">
													<label for="inputProcesso">Processo</label>
													<input type="text" id="inputProcesso" name="inputProcesso" class="form-control" readOnly>
												</div>
											</div>	');
											}										
									   ?>	
									</div>

									<div class="row">
										<?php 
											
											print('
												<div id="Mensagem" class="row" style=" margin-top: 10px; display:none">
													<div class="row justify-content-center col-lg-12 ">
														<div class="form-group col-12 col-lg-9">
															<p style="color: red"><i class="icon-info3"></i> Há saldo remanescente do contrato a ser usado. Deseja utilizá-lo?</p>
														</div>
														<div class="form-group col-12 col-lg-3">
															<div class="form-check  form-check-inline">
																<label class="form-check-label" style="margin-right:40px">
																	<input type="radio" id="inputSaldoRemanescente" name="inputSaldoRemanescente" value="1" class="form-input-styled" data-fouc required>SIM
																</label>
																<label class="form-check-label">
																	<input type="radio" id="inputSaldoRemanescente" name="inputSaldoRemanescente" value="0" class="form-input-styled" data-fouc required>NÃO
																</label>
															</div>	
														</div>
													</div>
												</div> 
											');							
											
										?>
									</div>
									<br>
									<div class="row">
										<div class="col-lg-12">									
											<h5 class="mb-0 font-weight-semibold">Dados do Fornecedor</h5>
											<br>
											<div class="row">
												<div class="col-lg-4">
													<div class="form-group">
														<label for="inputContato">Fornecedor</label>
														<input type="text" id="cmbFornecedorName" name="cmbFornecedorName" class="form-control" readOnly>
														<input type="hidden" id="cmbFornecedor" name="cmbFornecedor" class="form-control">
													</div>
												</div>
												
												<div class="col-lg-3">
													<div class="form-group">
														<label for="inputContato">Contato</label>
														<input type="text" id="inputContato" name="inputContato" class="form-control" readOnly>
													</div>
												</div>									

												<div class="col-lg-3">
													<div class="form-group">
														<label for="inputEmailFornecedor">E-mail</label>
														<input type="text" id="inputEmailFornecedor" name="inputEmailFornecedor" class="form-control" readOnly>
													</div>
												</div>									

												<div class="col-lg-2">
													<div class="form-group">
														<label for="inputTelefoneFornecedor">Telefone</label>
														<input type="text" id="inputTelefoneFornecedor" name="inputTelefoneFornecedor" class="form-control" readOnly>
													</div>
												</div>									
											</div>
										</div>
									</div>
									<br>									
									
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbCategoria">Categoria <span class="text-danger">*</span></label>
												<input type="text" id="cmbCategoriaName" name="cmbCategoriaName" class="form-control" readOnly>
												<input type="hidden" id="cmbCategoria" name="cmbCategoria">
											</div>
										</div>

										<div class="col-lg-6">
											<div class="form-group">
												<label id="cmbSubCategoriaName" for="cmbSubCategoriaName">SubCategoria</label>
												<select id="cmbSubCategoria" name="cmbSubCategoria" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
												</select>
											</div>
										</div>

									</div>
								</div>
							</div>
								
							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="txtareaConteudo">Conteúdo Personalizado - Introdução</label>
										<!--<div id="summernote" name="txtareaConteudo"></div>-->
										<textarea rows="5" cols="5" class="form-control" id="summernoteInicio" name="txtareaConteudoInicio" placeholder="Corpo do orçamento (informe aqui o texto que você queira que apareça no orçamento)"></textarea>
									</div>
								</div>
							</div>		
							<br>

							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="txtareaConteudoFim">Conteúdo Personalizado - Finalização</label>
										<!--<div id="summernote" name="txtareaConteudoFim"></div>-->
										<textarea rows="5" cols="5" class="form-control" id="summernoteFim" name="txtareaConteudoFim" placeholder=" Considerações Finais do orçamento (informe aqui o texto que você queira que apareça no término do orçamento)"></textarea>
									</div>                                                                                                         
								</div>
							</div>
							<br>
																					
							<div class="row">
								<div class="col-lg-12">									
									<h5 class="mb-0 font-weight-semibold">Dados do Solicitante</h5>
									<br>
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNomeSolicitante">Solicitante</label>
												<input type="text" id="inputNomeSolicitante" name="inputNomeSolicitante" class="form-control" value="<?php echo $rowUsuario['UsuarNome']; ?>" readOnly required>
											</div>
										</div>
										
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputEmailSolicitante">E-mail</label>
												<input type="text" id="inputEmailSolicitante" name="inputEmailSolicitante" class="form-control" value="<?php echo $rowUsuario['UsuarEmail']; ?>" readOnly>
											</div>
										</div>									

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputTelefoneSolicitante">Telefone</label>
												<input type="text" id="inputTelefoneSolicitante" name="inputTelefoneSolicitante" class="form-control" value="<?php echo $rowUsuario['UsuarTelefone']; ?>" readOnly>
											</div>
										</div>									
									</div>
								</div>
							</div>
							<br>
								
							<div class="row">
								<div class="col-lg-12">									
									<h5 class="mb-0 font-weight-semibold">Dados da Entrega</h5>
									<br>
									<div class="row">
										<div class="col-lg-6">
											<label for="cmbUnidade">Unidade <span class="text-danger">*</span></label>
											<select id="cmbUnidade" name="cmbUnidade" class="form-control form-control-select2" required>
												<option value="">Selecione</option>
												<?php 
													$sql = "SELECT UnidaId, UnidaNome
															FROM Unidade
															JOIN Situacao on SituaId = UnidaStatus
															WHERE UnidaEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
															ORDER BY UnidaNome ASC";
													$result = $conn->query($sql);
													$rowUnidade = $result->fetchAll(PDO::FETCH_ASSOC);
													
													foreach ($rowUnidade as $item){
														print('<option value="'.$item['UnidaId'].'">'.$item['UnidaNome'].'</option>');
													}
												
												?>
											</select>
										</div>										
									
										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbLocalEstoque">Local / Almoxarifado</label>
												<select id="cmbLocalEstoque" name="cmbLocalEstoque" class="form-control form-control-select2">
													<option value="">Selecione</option>
												</select>
											</div>
										</div>
									</div>
									
									<div class="row">									
										<div class="col-lg-10">
											<div class="form-group">
												<label for="inputEnderecoEntrega">Endereço da Entrega</label>
												<input type="text" id="inputEnderecoEntrega" name="inputEnderecoEntrega" class="form-control">
											</div>
										</div>									

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputDataEntrega">Previsão de Entrega</label>
												<input type="date" id="inputDataEntrega" name="inputDataEntrega" class="form-control">
											</div>
										</div>	
									</div>
									
									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="txtareaObservacao">Observação</label>											
												<textarea rows="3" cols="5" class="form-control" id="txtareaObservacao" name="txtareaObservacao"></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- /card-body -->
					</form>
					<div class="row" style="margin-top: 10px; margin-left:10px;">
						<div class="col-lg-12">								
							<div class="form-group">
								<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
								<a href="ordemcompra.php" class="btn btn-basic" role="button">Cancelar</a>
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

</body>
</html>
