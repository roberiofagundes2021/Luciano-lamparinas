<?php 

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Novo Produto para Termo de Referência';

include('global_assets/php/conexao.php');

$sql = "SELECT UnMedId, UnMedNome, UnMedSigla, UnMedStatus
		FROM UnidadeMedida
		WHERE UnMedEmpresa = ". $_SESSION['EmpreId'] ."
		ORDER BY UnMedNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//Se estiver inserindo
if(isset($_POST['inputNome'])){

	try{

		$conn->beginTransaction();

		$sql = "INSERT INTO ProdutoOrcamento (PrOrcNome, PrOrcProduto, PrOrcDetalhamento, PrOrcCategoria, PrOrcSubcategoria, PrOrcUnidadeMedida, PrOrcSituacao, PrOrcUsuarioAtualizador, PrOrcEmpresa) 
				VALUES (:sNome, :iProduto, :sDetalhamento, :iCategoria, :iSubCategoria, :iUnidadeMedida, :iSituacao, :iUsuarioAtualizador, :iEmpresa)";
		$result = $conn->prepare($sql);

		$result->execute(array(						
						':sNome' => $_POST['inputNome'],
						':iProduto' => $_POST['cmbProduto'],
						':sDetalhamento' => $_POST['txtDetalhamento'],
						':iCategoria' => $_POST['inputCategoriaId'],
						':iSubCategoria' => $_POST['inputSubCategoriaId'],
						':iUnidadeMedida' => $_POST['cmbUnidadeMedida'] == '' ? null : $_POST['cmbUnidadeMedida'],
						':iSituacao' => $_POST['inputSituacao'],
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
		$_SESSION['msg']['mensagem'] = "Produto incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {	
		
		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir produto!!!";
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
	<title>Lamparinas | Novo Produto para Termo de Referência</title>

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
	    
		$(document).ready(()=>{
        
			$("#cmbProduto").change((e)=>{
            
				const produtoId = $(e.target).val()
				
				$.getJSON('filtraCategoria.php?idProduto='+produtoId, function (dados){

                if (dados.length){
						
						$.each(dados, function(i, obj){					
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
			$('#enviar').on('click', function(e){

				e.preventDefault();
				
				let cmbProduto = $('#cmbProduto').val();
				
				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				$.ajax({
					type: "POST",
					url: "produtoOrcamentoValida.php",
					data: ('IdProduto='+cmbProduto),
					success: function(resposta){
						
						if(resposta == 1){
							alerta('Atenção','Esse produto de referência já foi utilizado!','error');
							return false;
						}
						
						$("#formProduto").submit();				
					}
				})

			})

			$('#cancelar').on('click', function(e){
				
				e.preventDefault();
				
				$(window.document.location).attr('href',"produtoOrcamento.php");
				
			}); // cancelar
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
				<!-------------------------------------------------------------------------------------------------------------------------------->
				<div class="card">
					
					<form name="formProduto" id="formProduto" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Produto</h5>
						</div>
						<input id="inputSituacao" type="hidden" value="1" name="inputSituacao">
						<div class="card-body">
							<div class="media">
								<div class="media-body">
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputNome">Nome <span class="text-danger">*</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" required>
											</div>
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputUnidadeMedida">Unidade de Medida <span class="text-danger">*</span></label>
												<select id="cmbUnidadeMedida" class="form-control form-control-select2" name="cmbUnidadeMedida" required>
													<option value="">Selecione</option>
													<?php 
													$sql = "SELECT UnMedId, UnMedNome, UnMedSigla
															FROM UnidadeMedida
															JOIN Situacao on SituaId = UnMedStatus
															WHERE UnMedEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
															ORDER BY UnMedNome ASC";
													$result = $conn->query($sql);
													$rowUnidadeMedida = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($rowUnidadeMedida as $item){
														print('<option value="'.$item['UnMedId'].'">'.$item['UnMedNome'].'</option>');
													}
													?>
												</select>
											</div>
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbProduto">Produto de Referência <span class="text-danger">*</span></label>
												<select id="cmbProduto" name="cmbProduto" class="form-control select-search" required>
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
														print('<option value="'.$item['ProduId'].'">'.$item['ProduNome'].'</option>');
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
												<textarea rows="5" cols="5" class="form-control" id="txtDetalhamento" name="txtDetalhamento" placeholder="Detalhamento do produto"></textarea>
											</div>
										</div>
									</div>
								</div>
							</div> 
							<br />
							<div class="row">
								<div class="col-lg-12">
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbCategoria">Categoria</label>
												<input type="hidden" id="inputCategoriaId" name="inputCategoriaId">
												<input type="text" id="inputCategoriaNome" name="inputCategoriaNome" class="form-control" readOnly>
												
											</div>
										</div>
										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbSubCategoria">SubCategoria</label>
												<input type="hidden" id="inputSubCategoriaId" name="inputSubCategoriaId">
												<input type="text" id="inputSubCategoriaNome" name="inputSubCategoriaNome" class="form-control" readOnly>

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
											<div class="col-lg-6">										
												<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
												<a href="produtoOrcamento.php" class="btn btn-basic" id="cancelar">Cancelar</a>
											</div>

											<div class="col-lg-6" style="text-align: right;">
												<p style="color: red; margin-right: 20px"><i class="icon-info3"></i>Alterações no detalhamento são replicados para o cadastro de produtos (baseado no produto de referência).</p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>	
							<!-- /card-body -->
					</form>
					<!-------------------------------------------------------------------------------------------------------------------------------->
				</div>
				<!-- /content area -->
			
			</div>
			<!-- /Content content -->

			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>

</body>

</html>
