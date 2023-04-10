<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Editar Produto para Termo de Referência';

include('global_assets/php/conexao.php');

$sql = "SELECT TRXPrTermoReferencia
		FROM TermoReferenciaXProduto
		JOIN ProdutoOrcamento on PrOrcId = TRXPrProduto
		JOIN TermoReferencia on TrRefId = TRXPrTermoReferencia
		JOIN Situacao on Situaid = TrRefStatus
		WHERE TRXPrProduto = " . $_POST['inputPrOrcId']. " and 
		(SituaChave = 'LIBERADOCENTRO' or SituaChave = 'LIBERADOCONTABILIDADE' or SituaChave = 'FASEINTERNAFINALIZADA') and
		TRXPrUnidade = " . $_SESSION['UnidadeId'];
$result = $conn->query($sql);
$rowTrs = $result->fetchAll(PDO::FETCH_ASSOC);
$contTRs = count($rowTrs);

$sql = "SELECT PrOrcId, PrOrcNome, PrOrcProduto, PrOrcDetalhamento, PrOrcCategoria, CategNome, PrOrcSubCategoria, SbCatNome, PrOrcUnidadeMedida 
		FROM ProdutoOrcamento
		JOIN Categoria on CategId = PrOrcCategoria
		JOIN SubCategoria on SbCatId = PrOrcSubCategoria
		WHERE PrOrcId = " . $_POST['inputPrOrcId'] . " and PrOrcEmpresa = " . $_SESSION['EmpreId'];
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);
//$count = count($row);

//Se estiver editando
if(isset($_POST['inputNome'])){

	try{

		$conn->beginTransaction();
		
		$sql = "UPDATE ProdutoOrcamento SET PrOrcNome = :sNome, PrOrcProduto = :iProduto, 
		        PrOrcDetalhamento = :sDetalhamento, PrOrcCategoria = :iCategoria, PrOrcSubcategoria = :iSubCategoria, PrOrcUnidadeMedida = :iUnidadeMedida, 
				PrOrcUsuarioAtualizador = :iUsuarioAtualizador, PrOrcEmpresa = :iEmpresa 
				WHERE PrOrcId = :sId ";
		$result = $conn->prepare($sql);

		$result->execute(array(
						':sId' => $_POST['inputId'],
						':sNome' => $_POST['inputNome'],
						':iProduto' => $_POST['cmbProduto'],
						':sDetalhamento' => $_POST['txtDetalhamento'],
						':iCategoria' => $_POST['inputCategoriaId'],
						':iSubCategoria' => $_POST['inputSubCategoriaId'],
						':iUnidadeMedida' => $_POST['cmbUnidadeMedida'] == '#' ? null : $_POST['cmbUnidadeMedida'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iEmpresa' => $_SESSION['EmpreId']
						));

		if (isset($_POST['cmbProduto'])){

			$sql = "UPDATE Produto SET ProduDetalhamento = :sDetalhamento, ProduUsuarioAtualizador = :iUsuarioAtualizador
					WHERE ProduId = :iProduto and ProduEmpresa = :iEmpresa";
			$result = $conn->prepare($sql);

			$result->execute(array(
						':sDetalhamento' => $_POST['txtDetalhamento'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iProduto' => $_POST['cmbProduto'],
						':iEmpresa' => $_SESSION['EmpreId']
						));
		}						

		$conn->commit();							
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Produto alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {	
		
		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar produto!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error2: ' . $e->getMessage();die;
		
	}
	
	irpara("produtoOrcamento.php");
} 


?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Produto para Termo de Referência</title>

	<?php include_once("head.php"); ?>

	<!---------------------------------Scripts Universais------------------------------------>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>	
	
	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

	<script type="text/javascript">

	// No carregamento da pagina é regatada a opção já cadastrada no banco
		$(document).ready(() => {

			$("#cmbProduto").change((e) =>{

				const produtoId = $(e.target).val()


				$.getJSON('filtraCategoria.php?idProduto=' +produtoId, function (dados){

					if (dados.length) {

						$.each(dados, function(i, obj) {
							$('#inputCategoriaId').val(obj.CategId);
							$('#inputCategoriaNome').val(obj.CategNome);
						});

					} else {
						Reset();
					}
				});

				$.getJSON('filtraSubCategoria.php?idProduto='+produtoId, function (dados){
			
					if (dados.length){						
						
						$.each(dados, function(i, obj){
							$('#inputSubCategoriaId').val(obj.SbCatId);
							$('#inputSubCategoriaNome').val(obj.SbCatNome);
						});
					} else {
						Reset();
					}					
				});
			});

			function Reset(){
				$('#inputCategoriaId').val("");
				$('#inputCategoriaNome').val("");
				$('#inputSubCategoriaId').val("");
				$('#inputSubCategoriaNome').val("");
			}

			//Valida Registro Duplicado
			$('#enviar').on('click', function(e) {

				e.preventDefault();

				let inputProduto = $('#inputProduto').val();
				let cmbProduto = $('#cmbProduto').val();
				
				if (inputProduto != cmbProduto){

					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "produtoOrcamentoValida.php",
						data: ('IdProdutoAntigo='+inputProduto+'&IdProdutoNovo='+cmbProduto),
						success: function(resposta){
							
							if(resposta == 1){
								alerta('Atenção','Esse produto de referência já foi utilizado!','error');
								return false;
							}

							$("#formProduto").submit();
						}
					})
				} else {
					$("#formProduto").submit();
				}
			});			
		});

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
				
				<div class="card">

					<form id="formProduto" name="formProduto" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Produto</h5>
							<input type="hidden" id="inputPrOrcId" name="inputPrOrcId" value="<?php echo $_POST['inputPrOrcId']; ?>">
						</div>
						<div class="card-body">
							<div class="media">
								<div class="media-body">
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputNome">Nome <span class="text-danger">*</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" value="<?php echo $row['PrOrcNome']; ?>" required>
												<input type="hidden" id="inputId" name="inputId" value="<?php echo $row['PrOrcId'] ?>">
											</div>
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputUnidadeMedida">Unidade de Medida <span class="text-danger">*</span></label>
												<select id="cmbUnidadeMedida" class="form-control form-control-select2" name="cmbUnidadeMedida" required>
													<?php
													$sql = "SELECT UnMedId, UnMedNome, UnMedSigla
															FROM UnidadeMedida
															JOIN Situacao on SituaId = UnMedStatus
															WHERE UnMedEmpresa = " . $_SESSION['EmpreId'] . " and SituaChave = 'ATIVO'
															ORDER BY UnMedNome ASC";
													$result = $conn->query($sql);
													$rowUnMed = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($rowUnMed as $item) {
														$seleciona = $item['UnMedId'] == $row['PrOrcUnidadeMedida'] ? "selected" : "";
														print('<option value="' . $item['UnMedId'] . '" ' . $seleciona . '>' . $item['UnMedNome'] . '</option>');
													}

													?>
												</select>
											</div>
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbProduto">Produto de Referência <span class="text-danger">*</span></label>
												<input type="hidden" id="inputProduto" name="inputProduto" value="<?php echo $row['PrOrcProduto'] ?>">
												<select id="cmbProduto" name="cmbProduto" class="form-control select-search" required <?php //$contTRs > 1 ? print('disabled') : ''; ?>>
													<option value="">Selecione</option>
													<?php 
													$sql = "SELECT ProduId, ProduNome
															FROM Produto
															JOIN Situacao on SituaId = ProduStatus
															WHERE ProduEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
															ORDER BY ProduNome ASC";
													$result = $conn->query($sql);
													$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($rowProduto as $item){
														$seleciona = $item['ProduId'] == $row['PrOrcProduto'] ? "selected" : "";
														print('<option value="'.$item['ProduId'] . '" ' . $seleciona . '>'.$item['ProduNome'].'</option>');
													}
													
													?>
												</select>
											</div>
										</div>				
									</div>
									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="txtDetalhamento">Detalhamento</label>
												<textarea rows="5" cols="5" class="form-control" id="txtDetalhamento" name="txtDetalhamento" placeholder="Detalhamento do produto"><?php echo $row['PrOrcDetalhamento'] ?></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">
									<br>
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbCategoria">Categoria <span class="text-danger">*</span></label>
												<input type="hidden" id="inputCategoriaId" name="inputCategoriaId" value="<?php echo $row['PrOrcCategoria']; ?>">
												<input type="text" id="inputCategoriaNome" name="inputCategoriaNome" class="form-control" readOnly  value="<?php echo $row['CategNome']; ?>">
											</div>
										</div>
										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbSubCategoria">SubCategoria</label>
												<input type="hidden" id="inputSubCategoriaId" name="inputSubCategoriaId" value="<?php echo $row['PrOrcSubCategoria']; ?>">
												<input type="text" id="inputSubCategoriaNome" name="inputSubCategoriaNome" class="form-control" readOnly value="<?php echo $row['SbCatNome']; ?>">
											</div>
										</div>
									</div>
								</div>
							</div>
							<br>
							<div class="row" style="margin-top: 40px;">
								<div class="col-lg-12">
									<div class="form-group">
										<div class="row">
											<div class="col-lg-4">										
												<?php 
													if ($_POST['inputPermission']) {
														echo '<button class="btn btn-lg btn-principal" id="enviar">Editar</button>';
													}
												?>
												<a href="produtoOrcamento.php" class="btn btn-basic" id="cancelar">Cancelar</a>
											</div>

											<div class="col-lg-8" style="text-align: right;">
												<p style="color: red; margin-right: 20px"><i class="icon-info3"></i>Alterações no detalhamento são replicados para o cadastro de produtos (baseado no produto de referência).</p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						
					</form>
				</div>
				<!-- /card-body -->
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