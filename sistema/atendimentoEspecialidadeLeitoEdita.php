<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Editar Especialidade do Leito';

include('global_assets/php/conexao.php');

if(isset($_POST['inputEspecialidadeLeitoId'])){ 
	
	$iEspecialidadeLeito = $_POST['inputEspecialidadeLeitoId'];

	$sql = "SELECT EsLeiId, EsLeiCodigo, EsLeiNome, EsLeiEspecialidadePai, ELXClClassificacao, ELXAgLeitoAgrupador
			FROM EspecialidadeLeito
			LEFT JOIN EspecialidadeLeitoXClassificacao on ELXClEspecialidadeLeito = EsLeiId
			LEFT JOIN EspecialidadeLeitoXAgrupador on ELXAgEspecialidadeLeito = EsLeiId
			WHERE EsLeiId = $iEspecialidadeLeito and EsLeiUnidade = ".$_SESSION['UnidadeId'];
	$result = $conn->query($sql);
	$rowEspecialidadeLeito = $result->fetchAll(PDO::FETCH_ASSOC);
	
	foreach ($rowEspecialidadeLeito as $item) {
		$aClassificacao[] = $item['ELXClClassificacao'];
		$Agrupador[] = $item['ELXAgLeitoAgrupador'];
		$EspecialidadeLeitoNome = $item['EsLeiNome'];
		$EspecialidadeLeitoCodigo = $item['EsLeiCodigo'];
		$EspecialidadeLeitoPai = $item['EsLeiEspecialidadePai'];
	}
							
	$_SESSION['msg'] = array();
} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	//irpara("atendimentoEspecialidadeLeito.php");
}

if(isset($_POST['inputNome'])){
	
	try{
				
			$sql = "UPDATE EspecialidadeLeito SET EsLeiCodigo = :iCodigo, EsLeiNome = :sNome,  EsLeiEspecialidadePai = :iEspecialidadePai, EsLeiUsuarioAtualizador = :iUsuarioAtualizador
					WHERE EsLeiId = :iEspecialidadeLeito";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
					':iCodigo' => $_POST['inputCodigo'],
					':sNome' => $_POST['inputNome'],
					':iEspecialidadePai' => $_POST['cmbEspecialidadePai'],
					':iUsuarioAtualizador' => $_SESSION['UsuarId'],
					':iEspecialidadeLeito' => $_POST['inputEspecialidadeLeitoId']
			));

			$sql = "DELETE FROM EspecialidadeLeitoXClassificacao 
					WHERE ELXClEspecialidadeLeito = :iEspecialidadeLeito";
			$result = $conn->prepare($sql);
		
			$result->execute(array(':iEspecialidadeLeito' => $_POST['inputEspecialidadeLeitoId']));
	
			//Grava as Classificações
			if ($_POST['cmbClassificacao']) {

				$sql = "INSERT INTO EspecialidadeLeitoXClassificacao (ELXClEspecialidadeLeito, ELXClClassificacao, ELXClUnidade)
						VALUES (:iEspecialidadeLeito, :iClassificacao, :iUnidade)";
				$result = $conn->prepare($sql);
	
				foreach ($_POST['cmbClassificacao'] as $key => $value) {
	
					$result->execute(array(
						':iEspecialidadeLeito' =>   $_POST['inputEspecialidadeLeitoId'],
						':iClassificacao' => $value,
						':iUnidade' => $_SESSION['UnidadeId']			
					));
				}
			}

			$sql = "DELETE FROM EspecialidadeLeitoXAgrupador 
					WHERE ELXAgEspecialidadeLeito = :iEspecialidadeLeito";
			$result = $conn->prepare($sql);
		
			$result->execute(array(':iEspecialidadeLeito' => $_POST['inputEspecialidadeLeitoId']));
			
			//Grava as Agrupador
			if ($_POST['cmbAgrupadorLeito']) {

				$sql = "INSERT INTO EspecialidadeLeitoXAgrupador (ELXAgEspecialidadeLeito, ELXAgLeitoAgrupador, ELXAgUnidade)
						VALUES (:iEspecialidadeLeito, :iLeitoAgrupador, :iUnidade)";
				$result = $conn->prepare($sql);
	
				foreach ($_POST['cmbAgrupadorLeito'] as $key => $value) {
	
					$result->execute(array(
						':iEspecialidadeLeito' => $_POST['inputEspecialidadeLeitoId'],
						':iLeitoAgrupador' => $value,
						':iUnidade' => $_SESSION['UnidadeId']			
					));
				}
			}

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Especialidade do leito alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar a Especialidade do leito!!!";
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
	<title>Lamparinas | Especilaidade do Leito</title>

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

				var inputNomeNovo = $('#inputNome').val();
				var inputNomeVelho = $('#inputEspecialidadeLeitoNome').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();

				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formEspecialidadeLeito").submit();
				} else {
				
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "atendimentoEspecialidadeLeitoValida.php",
						data: ('nomeNovo='+inputNome+'&nomeVelho='+inputNomeVelho),
						success: function(resposta){

							if(resposta == 1){
								alerta('Atenção','Esse registro já existe!','error');
								return false;
							}					
							
							$( "#formEspecialidadeLeito" ).submit();
						}
					})
				}	
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
							<h5 class="text-uppercase font-weight-bold">Editar Especialidade do Leito </h5>
						</div>
						
						<input type="hidden" id="inputEspecialidadeLeitoId" name="inputEspecialidadeLeitoId" value="<?php if (isset($_POST['inputEspecialidadeLeitoId'])) echo $_POST['inputEspecialidadeLeitoId']; ?>" >
						<input type="hidden" id="inputEspecialidadeLeitoNome" name="inputEspecialidadeLeitoNome" value="<?php if (isset($_POST['inputEspecialidadeLeitoNome'])) echo $_POST['inputEspecialidadeLeitoNome']; ?>" >
						<input type="hidden" id="inputEspecialidadeLeitoStatus" name="inputEspecialidadeLeitoStatus" >
						
						<div class="card-body">								
                            <div class="row">
								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputCodigo">Código<span class="text-danger"> *</span></label>
										<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" value="<?php echo $EspecialidadeLeitoCodigo; ?>" required>
									</div>
								</div>
								<div class="col-lg-5">
									<div class="form-group">
										<label for="inputNome">Nome da Especialidade do Leito <span class="text-danger"> *</span></label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Especialidade do Leito" value="<?php echo $EspecialidadeLeitoNome; ?>" required autofocus>
									</div>
								</div>
								<div class="col-lg-5">
									<div class="form-group">
										<label for="cmbClassificacao">Classificação<span class="text-danger"> *</span></label>
										<select id="cmbClassificacao" name="cmbClassificacao[]" class="form-control multiselect-filtering" multiple="multiple">
											<option value="H" <?php if (in_array('H', $aClassificacao)) echo "selected"; ?>>Hospitalar</option>
											<option value="A" <?php if (in_array('A', $aClassificacao)) echo "selected"; ?>>Ambulátorial</option>
										</select>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="cmbEspecialidadePai">Especialidade Pai</label>
										<select id="cmbEspecialidadePai" name="cmbEspecialidadePai" class="form-control form-control-select2">
											<option value="">Selecione</option>
											<?php 
												$sql = "SELECT EsLeiId, EsLeiNome
														FROM EspecialidadeLeito
														JOIN Situacao on SituaId = EsLeiStatus
														WHERE EsLeiUnidade = ".$_SESSION['UnidadeId']." and SituaChave = 'ATIVO'
														ORDER BY EsLeiNome ASC";
												$result = $conn->query($sql);
												$rowEspecialidadePai = $result->fetchAll(PDO::FETCH_ASSOC);
												
												foreach ($rowEspecialidadePai as $item){
													$seleciona = $item['EsLeiId'] == $EspecialidadeLeitoPai ? "selected" : "";
													print('<option value="'.$item['EsLeiId'].'" '. $seleciona .'>'.$item['EsLeiNome'].'</option>');
												}
											?>
										</select>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label for="cmbAgrupadorLeito">Agrupador Do Leito<span class="text-danger"> *</span></label>
										<select id="cmbAgrupadorLeito" name="cmbAgrupadorLeito[]" class="form-control multiselect-filtering" multiple="multiple">
											<?php 
												$sql = "SELECT LtAgrId, LtAgrNome
														FROM LeitoAgrupador
														JOIN Situacao on SituaId = LtAgrStatus
														WHERE SituaChave = 'ATIVO'
														ORDER BY LtAgrNome ASC";
												$result = $conn->query($sql);
												$rowAgrupador = $result->fetchAll(PDO::FETCH_ASSOC);

												foreach ($rowAgrupador as $item) {
													if(in_array($item['LtAgrId'], $Agrupador)){
														print("<option selected value='$item[LtAgrId]'>$item[LtAgrNome]</option>");
													}else{
														print("<option value='$item[LtAgrId]'>$item[LtAgrNome]</option>");
													}
												}
											?>
										</select>
									</div>
								</div>			
								
							</div>		
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>
										<a href="atendimentoEspecialidadeLeito.php" class="btn btn-basic">Cancelar</a>
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
