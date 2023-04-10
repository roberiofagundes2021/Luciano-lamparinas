<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Editar Grupo Conta';

include('global_assets/php/conexao.php');

if(isset($_POST['inputGrupoContaId'])){
	
	$iGrupoConta = $_POST['inputGrupoContaId'];
		
	$sql = "SELECT GrConId, GrConCodigo, GrConNome, GrConNomePersonalizado
			FROM GrupoConta
			WHERE GrConId = $iGrupoConta AND GrConUnidade = $_SESSION[UnidadeId]";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);
	
	$_SESSION['msg'] = array();
}

if(isset($_POST['inputNomePersonalizado'])){
	
	try{
		$sql = "UPDATE GrupoConta SET GrConNomePersonalizado = :sNomePersonalizado
				WHERE GrConId = :iGrupo";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':sNomePersonalizado' => $_POST['inputNomePersonalizado'],
						':iGrupo' => $_POST['inputGrupoContaId']
						));

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Grupo Conta alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar Grupo Conta!!!";
		$_SESSION['msg']['tipo'] = "error";		
		
		echo 'Error: ' . $e->getMessage();
	}
	
	irpara("grupo.php");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Grupo Conta</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>	
	<!-- /theme JS files -->	
	
	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

	<script type="text/javascript" >

        $(document).ready(function() {
		})
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
					
					<form name="formPlanoContas" id="formPlanoContas" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Grupo Conta "<?php echo $row['GrConNome']; ?>"</h5>
						</div>
						
						<input type="hidden" id="inputGrupoContaId" name="inputGrupoContaId" value="<?php echo $row['GrConId']; ?>">
						
						<div class="card-body">								
							<div class="row">

								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputCodigo">Código</label>
										<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" value="<?php echo $row['GrConCodigo']; ?>" readonly autofocus>
									</div>
								</div>

								<div class="col-lg-5">
									<div class="form-group">
										<label for="inputNome">Título (sugerido pelo sistema)</label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Título" value="<?php echo $row['GrConNome']; ?>" readonly>
									</div>
								</div>

								<div class="col-lg-5">
									<div class="form-group">
										<label for="inputNomePersonalizado">Título (nome personalizado)</label>
										<input type="text" id="inputNomePersonalizado" name="inputNomePersonalizado" class="form-control" placeholder="Título personalizado" value="<?php echo $row['GrConNomePersonalizado']; ?>">
									</div>
								</div>								
							</div>
								
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar" type="submit">Alterar</button>
										<a href="grupo.php" class="btn btn-basic">Cancelar</a>
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

</body>
</html>
