<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Especialidade do leito';

include('global_assets/php/conexao.php');

if(isset($_POST['inputNome'])){

	try{
		
		$sql = "INSERT INTO EspecialidadeLeito (EsLeiCodigo, EsLeiNome, EsLeiEspecialidadePai, EsLeiStatus, EsLeiUsuarioAtualizador, EsLeiUnidade)
					VALUES (:iCodigo, :sNome, :iEspecialidadePai,  :bStatus, :iUsuarioAtualizador, :iUnidade)";
		$result = $conn->prepare($sql);
					
		$result->execute(array(
			':iCodigo' => $_POST['inputCodigo'],
			':sNome' => $_POST['inputNome'],
			':iEspecialidadePai' => $_POST['cmbEspecialidadePai'],
			':bStatus' => 1,
			':iUsuarioAtualizador' => $_SESSION['UsuarId'],
			':iUnidade' => $_SESSION['UnidadeId'],
			));

		$insertId = $conn->lastInsertId();
			
			//Grava as Classificações

			if ($_POST['cmbClassificacao']) {

				$sql = "INSERT INTO EspecialidadeLeitoXClassificacao (ELXClEspecialidadeLeito, ELXClClassificacao, ELXClUnidade)
						VALUES (:iEspecialidadeLeito, :iClassificacao, :iUnidade)";
				$result = $conn->prepare($sql);
	
				foreach ($_POST['cmbClassificacao'] as $key => $value) {
	
					$result->execute(array(
						':iEspecialidadeLeito' =>  $insertId,
						':iClassificacao' => $value,
						':iUnidade' => $_SESSION['UnidadeId']			
					));
				}
			}

			if ($_POST['cmbAgrupadorLeito']) {

				$sql = "INSERT INTO EspecialidadeLeitoXAgrupador (ELXAgEspecialidadeLeito, ELXAgLeitoAgrupador, ELXAgUnidade)
						VALUES (:iEspecialidadeLeito, :iLeitoAgrupador, :iUnidade)";
				$result = $conn->prepare($sql);
	
				foreach ($_POST['cmbAgrupadorLeito'] as $key => $value) {
	
					$result->execute(array(
						':iEspecialidadeLeito' =>  $insertId,
						':iLeitoAgrupador' => $value,
						':iUnidade' => $_SESSION['UnidadeId']			
					));
				}
			}
	
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Especialidade do leito incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir a especialidade do leito!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}
	
	irpara("atendimentoEspecialidadeLeito.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Especialidade do Leito</title>

	<?php include_once("head.php"); ?>
	
	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<!--Obs: Os links de validação foram colocados na parte superior porque este link está sobreescrevendo a função de pesquisa do form-control-select2-->
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	<!--/ Validação -->

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>
	
	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	<!-- /theme JS files -->

	<script type="text/javascript" >

        $(document).ready(function() {
			

			//Valida Registro Duplicado
			$('#enviar').on('click', function(e){
				
				e.preventDefault();
				
				var inputNome = $('#inputNome').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNome.trim();

				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				$.ajax({
					type: "POST",
						url: "atendimentoEspecialidadeLeitoValida.php",
						data: ('nomeNovo='+inputNome),
						success: function(resposta){
						
						if(resposta == 1){
							alerta('Atenção','Esse tipo de especialidade do leito já existe! Editar o existente.','error');
							return false;
						}

						$( "#formEspecialidadeLeito" ).submit();
					}
				})	

			})	

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
					
					<form name="formEspecialidadeLeito" id="formEspecialidadeLeito" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Especialidade do Leito</h5>
						</div>
						
						<div class="card-body">						
							<div class="row">
								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputCodigo">Código<span class="text-danger"> *</span></label>
										<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" required autofocus>
									</div>
								</div>
								<div class="col-lg-5">
									<div class="form-group">
										<label for="inputNome">Nome da Especialidade do Leito <span class="text-danger"> *</span></label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Especialidade do Leito" required>
									</div>
								</div>					
								<div class="col-lg-5">
									<label for="cmbClassificacao">Classificação<span class="text-danger"> *</span></label>
									<select id="cmbClassificacao" name="cmbClassificacao[]" class="form-control multiselect-filtering" multiple="multiple">
										<option value="H">Hospitalar</option>
										<option value="A">Ambulátorial</option>
									</select>
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="cmbEspecialidadePai">Especialidade Pai</span></label>
										<select id="cmbEspecialidadePai" name="cmbEspecialidadePai" class="form-control form-control-select2">
											<option value="">Selecione</option>
											<?php 
												$sql = "SELECT EsLeiId, EsLeiNome
														FROM EspecialidadeLeito
														JOIN Situacao on SituaId = EsLeiStatus
														WHERE EsLeiUnidade = ".$_SESSION['UnidadeId']." and SituaChave = 'ATIVO'
														ORDER BY EsLeiNome ASC";
												$result = $conn->query($sql);
												$row = $result->fetchAll(PDO::FETCH_ASSOC);
												
												foreach ($row as $item){															
													print('<option value="'.$item['EsLeiId'].'">'.$item['EsLeiNome'].'</option>');
												}
											?>
										</select>
									</div>
								</div>
								<div class="col-lg-6">
									<label for="cmbAgrupadorLeito">Agrupador Do Leito<span class="text-danger"> *</span></label>
									<select id="cmbAgrupadorLeito" name="cmbAgrupadorLeito[]" class="form-control multiselect-filtering" multiple="multiple">
											<?php 
												$sql = "SELECT LtAgrId, LtAgrNome
														FROM LeitoAgrupador
														JOIN Situacao on SituaId = LtAgrStatus
														WHERE SituaChave = 'ATIVO'
														ORDER BY LtAgrNome ASC";
												$result = $conn->query($sql);
												$row = $result->fetchAll(PDO::FETCH_ASSOC);
												
												foreach ($row as $item){															
													print('<option value="'.$item['LtAgrId'].'">'.$item['LtAgrNome'].'</option>');
												}
											?>
									</select>
								</div>
												
								
							</div>								
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="atendimentoEspecialidadeLeito.php" class="btn btn-basic" role="button">Cancelar</a>
									</div>
								</div>
							</div>
						</div>

					</form>								
			
					<!-- /card-body -->
					
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
