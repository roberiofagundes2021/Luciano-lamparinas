<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Editar Serviço para Termo de Referência';

include('global_assets/php/conexao.php');

$sql = "SELECT TRXSrTermoReferencia
		FROM TermoReferenciaXServico
		JOIN ServicoOrcamento on SrOrcId =TRXSrServico
		JOIN TermoReferencia on TrRefId =TRXSrTermoReferencia
		JOIN Situacao on Situaid = TrRefStatus
		WHERE TRXSrServico = " . $_POST['inputSrOrcId'] . " and 
		(SituaChave = 'LIBERADOCENTRO' or SituaChave = 'LIBERADOCONTABILIDADE' or SituaChave = 'FASEINTERNAFINALIZADA') and
		TRXSrUnidade = " . $_SESSION['UnidadeId'];
$result = $conn->query($sql);
$rowTrs = $result->fetchAll(PDO::FETCH_ASSOC);
$contTRs = count($rowTrs);

$sql = "SELECT SrOrcId, SrOrcNome, SrOrcServico, SrOrcDetalhamento, SrOrcCategoria, CategNome, SrOrcSubCategoria, SbCatNome 
		FROM ServicoOrcamento
		JOIN Categoria on CategId = SrOrcCategoria
		JOIN SubCategoria on SbCatId = SrOrcSubCategoria
		WHERE SrOrcId = " . $_POST['inputSrOrcId'] . " and SrOrcEmpresa = " . $_SESSION['EmpreId'];
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);
//$count = count($row);
//echo $sql;die;

//Se estiver editando
if(isset($_POST['inputNome'])){
	
	try{

		$conn->beginTransaction();

		$sql = "UPDATE ServicoOrcamento SET SrOrcNome = :sNome,  SrOrcServico = :iServico, 
				SrOrcDetalhamento = :sDetalhamento, SrOrcCategoria = :iCategoria, SrOrcSubcategoria = :iSubCategoria, 
				SrOrcUsuarioAtualizador = :iUsuarioAtualizador, SrOrcEmpresa = :iEmpresa
				WHERE SrOrcId = :sId ";
		$result = $conn->prepare($sql);

		$result->execute(array(
						':sId' => $_POST['inputId'],
						':sNome' => $_POST['inputNome'],
						':iServico' => $_POST['cmbServico'],
						':sDetalhamento' => $_POST['txtDetalhamento'],
						':iCategoria' => $_POST['inputCategoriaId'],
						':iSubCategoria' => $_POST['inputSubCategoriaId'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iEmpresa' => $_SESSION['EmpreId']
						));

		if (isset($_POST['cmbServico'])){
			
			$sql = "UPDATE Servico SET ServiDetalhamento = :sDetalhamento, ServiUsuarioAtualizador = :iUsuarioAtualizador
					WHERE ServiId = :iServico and ServiEmpresa = :iEmpresa";
			$result = $conn->prepare($sql);

			$result->execute(array(
						':sDetalhamento' => $_POST['txtDetalhamento'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iServico' => $_POST['cmbServico'],
						':iEmpresa' => $_SESSION['EmpreId']
						));
		}						

		$conn->commit();				
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Serviço alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {	
		
		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar serviço!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error2: ' . $e->getMessage();die;
		
	}

	irpara("servicoOrcamento.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Serviço para Termo de Referência</title>

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

			$("#cmbServico").change((e)=>{

				const servicoId = $(e.target).val()

				$.getJSON('filtraCategoria.php?idServico='+servicoId, function (dados){
					
					if (dados.length){
						
						$.each(dados, function(i, obj){					
							$('#inputCategoriaId').val(obj.CategId);
							$('#inputCategoriaNome').val(obj.CategNome);
						});

					} else {
						Reset();
					}
				});

				$.getJSON('filtraSubCategoria.php?idServico='+servicoId, function (dados){
					
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
				
				let inputServico = $('#inputServico').val();
				let cmbServico = $('#cmbServico').val();
				
				if (inputServico != cmbServico){
					
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "servicoOrcamentoValida.php",
						data: ('IdServicoAntigo='+inputServico+'&IdServicoNovo='+cmbServico),
						success: function(resposta){
							
							if(resposta == 1){
								alerta('Atenção','Esse serviço de referência já foi utilizado!','error');
								return false;
							}

							$("#formServico").submit();
						}
					})
				} else {
					$("#formServico").submit();
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

					<form id="formServico" name="formServico" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Serviço</h5>
							<input type="hidden" id="inputSrOrcId" name="inputSrOrcId" value="<?php echo $_POST['inputSrOrcId']; ?>">
						</div>
						<div class="card-body">
							<div class="media">
								<div class="media-body">
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNome">Nome <span class="text-danger">*</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" value="<?php echo $row['SrOrcNome']; ?>" required>
												<input type="hidden" id="inputId" name="inputId" value="<?php echo $row['SrOrcId'] ?>">
											</div>
										</div>
										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbServico">Serviço de Referência <span class="text-danger">*</span></label>
												<input type="hidden" id="inputServico" name="inputServico" value="<?php echo $row['SrOrcServico'] ?>">
												<select id="cmbServico" name="cmbServico" class="form-control select-search" required <?php $contTRs > 1 ? print('disabled') : ''; ?>>
													<option value="">Selecione</option>
													<?php 
													$sql = "SELECT ServiId, ServiNome
															FROM Servico
															JOIN Situacao on SituaId = ServiStatus
															WHERE ServiEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
															ORDER BY ServiNome ASC";
													$result = $conn->query($sql);
													$rowServico = $result->fetchAll(PDO::FETCH_ASSOC);

													foreach ($rowServico as $item){
														$seleciona = $item['ServiId'] == $row['SrOrcServico'] ? "selected" : "";
														print('<option value="'.$item['ServiId'] . '" ' . $seleciona . '>'.$item['ServiNome'].'</option>');
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
												<textarea rows="5" cols="5" class="form-control" id="txtDetalhamento" name="txtDetalhamento" placeholder="Detalhamento do serviço"><?php echo $row['SrOrcDetalhamento'] ?></textarea>
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
												<label for="cmbCategoria">Categoria</label>
												<input type="hidden" id="inputCategoriaId" name="inputCategoriaId" value="<?php echo $row['SrOrcCategoria']; ?>">
												<input type="text" id="inputCategoriaNome" name="inputCategoriaNome" class="form-control" readOnly  value="<?php echo $row['CategNome']; ?>">
											</div>
										</div>
										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbSubCategoria">SubCategoria</label>
												<input type="hidden" id="inputSubCategoriaId" name="inputSubCategoriaId" value="<?php echo $row['SrOrcSubCategoria']; ?>">
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
												<a href="servicoOrcamento.php" class="btn btn-basic" id="cancelar">Cancelar</a>
											</div>

											<div class="col-lg-8" style="text-align: right;">
												<p style="color: red; margin-right: 20px"><i class="icon-info3"></i>Alterações no detalhamento são replicados para o cadastro de serviços (baseado no serviço de referência).</p>
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