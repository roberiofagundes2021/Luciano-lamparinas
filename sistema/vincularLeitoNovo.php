<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Nova Vinculação do Leito';

include('global_assets/php/conexao.php');

if(isset($_POST['cmbTipoAcomodacao'])){

	try{
		
		$sql = "INSERT INTO VincularLeito (VnLeiTipoAcomodacao, VnLeiTipoInternacao, VnLeiEspecialidadeLeito, VnLeiAla, VnLeiQuarto, VnLeiObservacao, VnLeiStatus, VnLeiUsuarioAtualizador, VnLeiUnidade)
				VALUES (:sTipoAcomodacao, :sTipoInternacao, :sEspecialidadeLeito, :sAla, :sQuarto, :sObservacao, :bStatus, :iUsuarioAtualizador, :iUnidade)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':sTipoAcomodacao' => $_POST['cmbTipoAcomodacao'],
						':sTipoInternacao' => $_POST['cmbTipoInternacao'],
						':sEspecialidadeLeito' => $_POST['cmbEspecialidadeLeito'],
						':sAla' => $_POST['cmbAla'] == '' ? null : $_POST['cmbAla'],
						':sQuarto' => $_POST['cmbQuarto'],
						':sObservacao' => $_POST['summernoteObservacao'] == '' ? null : $_POST['summernoteObservacao'],
						':bStatus' => 1,
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iUnidade' => $_SESSION['UnidadeId'],
						));

						$insertId = $conn->lastInsertId();
			
			//Grava os Leitos
			if ($_POST['cmbLeito']) {

				$sql = "INSERT INTO VincularLeitoXLeito (VLXLeVinculaLeito, VLXLeLeito, VLXLeUnidade)
						VALUES (:iVinculaLeito, :iLeito, :iUnidade)";
				$result = $conn->prepare($sql);
	
				foreach ($_POST['cmbLeito'] as $key => $value) {
	
					$result->execute(array(
						':iVinculaLeito' =>  $insertId,
						':iLeito' => $value,
						':iUnidade' => $_SESSION['UnidadeId']			
					));
				}
			}
	
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Vinculação do leito incluída!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir a vinculação do leito!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}
	
	irpara("vincularLeito.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Vincular Leito</title>

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
				
				var cmbTipoAcomodacao = $('#cmbTipoAcomodacao').val();
				var cmbAla = $('#cmbAla').val();
				var cmbEspecialidadeLeito = $('#cmbEspecialidadeLeito').val();
				var cmbTipoInternacao = $('#cmbTipoInternacao').val();
				var cmbQuarto = $('#cmbQuarto').val();				

				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				$.ajax({
					type: "POST",
					url: "vincularLeitoValida.php",
					//data: ('acomodacao='+cmbTipoAcomodacao),
					data: ('acomodacao='+cmbTipoAcomodacao+'&ala='+cmbAla+'&especialidadeLeito='+cmbEspecialidadeLeito+'&tipoInternacao='+cmbTipoInternacao+'&quarto='+cmbQuarto),
					success: function(resposta){
						
						if(resposta == 1){
							alerta('Atenção','Esse tipo de acomodação já existe! Editar o existente.','error');
							return false;
						}

						$( "#formVincularLeito" ).submit();
					}
				})	

			})	
			
			$(".caracteressummernoteObservacao").text((2000 - $("#summernoteObservacao").val().length) + ' restantes'); //restantes da observação da consulta

		})

		function contarCaracteres(params) {

			var limite = params.maxLength;
			var informativo = " restantes.";
			var caracteresDigitados = params.value.length;
			var caracteresRestantes = limite - caracteresDigitados;

			if (caracteresRestantes <= 0) {
				var texto = $(`textarea[name=${params.id}]`).val();
				$(`textarea[name=${params.id}]`).val(texto.substr(0, limite));
				$(".caracteres" + params.id).text("0 " + informativo);
			} else {
				$(".caracteres" + params.id).text(caracteresRestantes + " " + informativo);
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
					
					<form name="formVincularLeito" id="formVincularLeito" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Vincular Leito</h5>
						</div>
						
						<div class="card-body">						
							<div class="row">
								<div class="col-lg-4">
									<label for="cmbTipoAcomodacao">Tipo da Acomodação<span class="text-danger"> *</span></label>
									<select id="cmbTipoAcomodacao" name="cmbTipoAcomodacao" class="form-control form-control-select2" required>
										<option value="">Selecione </option>
										<?php
											$sql = "SELECT TpAcoId, TpAcoNome
													FROM TipoAcomodacao
													JOIN Situacao on SituaId = TpAcoStatus
													WHERE TpAcoUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
													ORDER BY TpAcoNome ASC";
											$result = $conn->query($sql);
											$row = $result->fetchAll(PDO::FETCH_ASSOC);

											foreach ($row as $item) {
												print('<option value="' . $item['TpAcoId'] . '">' . $item['TpAcoNome'] . '</option>');
											}
										?>
									</select>
								</div>
								<div class="col-lg-4">
									<label for="cmbTipoInternacao">Tipo da Internação<span class="text-danger"> *</span></label>
									<select id="cmbTipoInternacao" name="cmbTipoInternacao" class="form-control form-control-select2" required>
										<option value="">Selecione </option>
										<?php
											$sql = "SELECT TpIntId, TpIntNome
													FROM TipoInternacao
													JOIN Situacao on SituaId = TpIntStatus
													WHERE TpIntUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
													ORDER BY TpIntNome ASC";
											$result = $conn->query($sql);
											$row = $result->fetchAll(PDO::FETCH_ASSOC);

											foreach ($row as $item) {
												print('<option value="' . $item['TpIntId'] . '">' . $item['TpIntNome'] . '</option>');
											}
										?>
									</select>
								</div>
								<div class="col-lg-4">
									<label for="cmbEspecialidadeLeito">Especialidade do Leito<span class="text-danger"> *</span></label>
									<select id="cmbEspecialidadeLeito" name="cmbEspecialidadeLeito" class="form-control form-control-select2" required>
										<option value="">Selecione </option>
										<?php
											$sql = "SELECT EsLeiId, EsLeiNome
												FROM EspecialidadeLeito
												JOIN Situacao on SituaId = EsLeiStatus
												WHERE EsLeiUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
												ORDER BY EsLeiNome ASC";
											$result = $conn->query($sql);
											$row = $result->fetchAll(PDO::FETCH_ASSOC);

											foreach ($row as $item) {
												print('<option value="' . $item['EsLeiId'] . '">' . $item['EsLeiNome'] . '</option>');
											}
										?>
									</select>
								</div>
							</div>
                            <br>
							<div class="row">
								<div class="col-lg-4">
									<label for="cmbAla">Ala</label>
									<select id="cmbAla" name="cmbAla" class="form-control form-control-select2">
										<option value="">Selecione </option>
										<?php
											$sql = "SELECT AlaId, AlaNome
													FROM Ala
													JOIN Situacao on SituaId = AlaStatus
													WHERE AlaUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
													ORDER BY AlaNome ASC";
											$result = $conn->query($sql);
											$row = $result->fetchAll(PDO::FETCH_ASSOC);

											foreach ($row as $item) {
												print('<option value="' . $item['AlaId'] . '">' . $item['AlaNome'] . '</option>');
											}
										?>
									</select>
								</div>
								<div class="col-lg-4">
									<label for="cmbQuarto">Nº Quarto<span class="text-danger"> *</span></label>
									<select id="cmbQuarto" name="cmbQuarto" class="form-control form-control-select2" required>
										<option value="">Selecione </option>
										<?php
											$sql = "SELECT QuartId, QuartNome
													FROM Quarto
													JOIN Situacao on SituaId = QuartStatus
													WHERE QuartUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
													ORDER BY QuartNome ASC";
											$result = $conn->query($sql);
											$row = $result->fetchAll(PDO::FETCH_ASSOC);

											foreach ($row as $item) {
												print('<option value="' . $item['QuartId'] . '">' . $item['QuartNome'] . '</option>');
											}
										?>
									</select>
								</div>
								<div class="col-lg-4">
									<?php

										$sql = " SELECT VLXLeLeito FROM VincularLeitoXLeito WHERE VLXLeUnidade = $_SESSION[UnidadeId] ";
										$result = $conn->query($sql);
										$leitosV = $result->fetchAll(PDO::FETCH_ASSOC);

										$leitosVinculados = [];

										foreach ($leitosV as $item ) {
											array_push($leitosVinculados, $item['VLXLeLeito']);
										}
									?>
									<label for="cmbLeito">Nº Leito<span class="text-danger"> *</span></label>
									<select id="cmbLeito" name="cmbLeito[]" class="form-control multiselect-filtering" multiple="multiple" required>
									
										<?php
										$sql = "SELECT LeitoId, LeitoNome
												FROM Leito
												JOIN Situacao on SituaId = LeitoStatus
												WHERE LeitoUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
												ORDER BY LeitoNome ASC";
										$result = $conn->query($sql);
										$row = $result->fetchAll(PDO::FETCH_ASSOC);

										foreach ($row as $item) {
											if (!in_array($item['LeitoId'], $leitosVinculados)) {
												print('<option value="' . $item['LeitoId'] . '">' . $item['LeitoNome'] . '</option>');
											}
										}
										?>
									</select>
								</div>			
							</div>
                              <br>
							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="">Observação</label>
										<textarea rows="4" cols="4" maxLength="2000"  id="summernoteObservacao" name="summernoteObservacao" onInput="contarCaracteres(this);" class="form-control" placeholder="Informe aqui a observação"></textarea>
										<small class="text-muted form-text">Max. 2000 caracteres - <span class="caracteressummernoteObservacao"></span></small>
									</div>
								</div>
							</div>								
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="vincularLeito.php" class="btn btn-basic" role="button">Cancelar</a>
									</div>
								</div>
							</div>
						</form>								

					</div>
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
