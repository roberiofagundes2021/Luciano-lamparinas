<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Editar Plano de Contas';

include('global_assets/php/conexao.php');

if(isset($_POST['inputPlanoContasId'])){
	
	$iPlanoContas = $_POST['inputPlanoContasId'];
		
	$sql = "SELECT *
			FROM PlanoConta
			WHERE PlConId = $iPlanoContas ";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);
	
	$_SESSION['msg'] = array();
} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	//irpara("planoContas.php");
}

if(isset($_POST['inputNome'])){
	
	try{
		$nome = $_POST['cmbTipo'] == 'S' ? mb_strtoupper($_POST['inputNome']) : $_POST['inputNome'];

		$sql = "UPDATE PlanoConta SET PlConCodigo = :iCodigo, PlConNome = :sNome, PlConTipo = :sTipo, PlConNatureza = :sNatureza, PlConGrupo = :sGrupo, PlConDetalhamento = :sDetalhamento, PlConPlanoContaPai = :sPlanoContaPai, PlConStatus = :bStatus, PlConUsuarioAtualizador = :iUsuarioAtualizador
				WHERE PlConId = :iPlanoContas";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':iCodigo' => $_POST['inputCodigo'],
						':sNome' => $nome,
						':sTipo' => $_POST['cmbTipo'],
						':sNatureza' => $_POST['cmbNatureza'],
						':sGrupo' => $_POST['cmbGrupo'],
						':sDetalhamento' => $_POST['inputDetalhamento'] == '' ? null : $_POST['inputDetalhamento'],
						':sPlanoContaPai' => $_POST['cmbPlanoContaPai'] == '' ? null : $_POST['cmbPlanoContaPai'],
						':bStatus' => $_POST['cmbStatus'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iPlanoContas' => $_POST['inputPlanoContasId']
						));

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Plano de Contas alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar Plano de Contas!!!";
		$_SESSION['msg']['tipo'] = "error";		
		
		echo 'Error: ' . $e->getMessage();
	}
	
	irpara("planoContas.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Plano de Contas</title>

	<?php include_once("head.php"); ?>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<!--Obs: Os links de validação foram colocados na parte superior porque este link está sobreescrevendo a função de pesquisa do form-control-select2-->
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	<!--/ Validação -->
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>	
	<!-- /theme JS files -->

	<script type="text/javascript" >

        $(document).ready(function() {
			
			//Valida Registro Duplicado
			$('#enviar').on('click', function(e){
				
				e.preventDefault();
				
				var inputNomeNovo  = $('#inputNome').val();
				//var inputNomeVelho = $('#inputPlanoContasNome').val();
				var inputPlanoContasId = $('#inputPlanoContasId').val();
				
				//remove os espaços desnecessários antes e depois
				inputNomeNovo = inputNomeNovo.trim();

				/*caso precise validar
				let cmbTipo =  $('#cmbTipo').val();
				let cmbPlanoContaPai = $('#cmbPlanoContaPai').val();*/
				
				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				$.ajax({
					type: "POST",
					url: "planoContasValida.php",
					data: {nome : inputNomeNovo, planoContasId : inputPlanoContasId},
					success: function(resposta){
						console.log(resposta)
						if(resposta == 1){
							alerta('Atenção','Já exite Centro de Custo ligado a um Plano de Contas com este nome!!','error');
							return false;
						}

						/*caso precise validar
						if (cmbTipo == 'A' && cmbPlanoContaPai == '') {
							alerta('Atenção','É preciso informar o Plano de Conta quando o tipo é Analítico!','error');
							$('#cmbPlanoContaPai').focus();
							return;
						}*/
						
						$( "#formPlanoContas" ).submit();
					}
				})
			})

			$('#cmbTipo').on('change', function() {
				let tipo = $(this).find(":selected").val();
				
				if(tipo == 'S') {
					$('#inputNome').css('text-transform', 'uppercase')
				}else {
					$('#inputNome').css('text-transform', '')
				}
			});

			let tipoPlanoConta = "<?php echo $row['PlConTipo']; ?>"

			function verificaTipoConta() {
				if(tipoPlanoConta == 'S')
					$('#inputNome').css('text-transform', 'uppercase')
			}

			verificaTipoConta();

			function selecionaPlanoConta() {
				let idPlanoContaPai = "<?php echo $row['PlConPlanoContaPai']; ?>";
				let arrayCodigoPlanoConta = ("<?php echo $row['PlConCodigo']; ?>").split('.');
				let codigo = arrayCodigoPlanoConta[0]; 

				const urlConsultaPlanoConta = "filtraPlanoContaPai.php";
				
				var inputsValuesConsulta = {
					inputCodigo: codigo
				}; 
				
				let HTML = ``;
				//Consulta planos de conta com o código inicial do grupo selecionado
				$.ajax({
					type: "POST",
					url: urlConsultaPlanoConta,
					dataType: "json",
					data: inputsValuesConsulta,
					success: function(resposta) {
						if(resposta[0]) {
							let seleciona = '';

							HTML = HTML +  `<option value="">Selecione</option>`;
							
							resposta.forEach(function(planoConta) {
								seleciona = planoConta.PlConId == idPlanoContaPai ? "selected" : "";

								HTML = HTML + `
									<option value="` + planoConta.PlConId + `"` + seleciona + `>` + planoConta.PlConCodigo + ` - ` + planoConta.PlConNome + `</option>`;
							});
						}else {
							HTML = HTML +  `<option value="">Nenhum Plano Conta Pai encontrado</option>`;
						}

						$("#cmbPlanoContaPai").html(HTML)
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) { 
						HTML = HTML +  `<option value="">Erro ao filtrar - verifique sua conexão com a internet</option>`;

						$("#cmbPlanoContaPai").html(HTML)
					}
				})
			}

			selecionaPlanoConta()

			/*
			foreach ($rowPlanoContas as $item){
				$seleciona = $item['PlConId'] == $row['PlConPlanoContaPai'] ? "selected" : "";
				print('<option value="'.$item['PlConId'].'" '. $seleciona .'>'.$item['PlConCodigo'].' - '.$item['PlConNome'].'</option>');
			}
			*/
			$('#cmbGrupo').on('change', function() {
				let arrayNomeGrupo = ($(this).find(":selected").text()).split(' ');
				let codigo = arrayNomeGrupo[0];
				
				const urlConsultaPlanoConta = "filtraPlanoContaPai.php";
				
				var inputsValuesConsulta = {
					inputCodigo: codigo
				}; 
				
				let HTML = ``;
				//Consulta planos de conta com o código inicial do grupo selecionado
				$.ajax({
					type: "POST",
					url: urlConsultaPlanoConta,
					dataType: "json",
					data: inputsValuesConsulta,
					success: function(resposta) {
						if(resposta[0]) {
							HTML = HTML +  `<option value="">Selecione</option>`;

							resposta.forEach(function(planoConta) {
								HTML = HTML + `
									<option value="` + planoConta.PlConId + `">` + planoConta.PlConCodigo + ` - ` + planoConta.PlConNome + `</option>`;
							});
						}else {
							HTML = HTML +  `<option value="">Nenhum Plano Conta Pai encontrado</option>`;
						}

						$("#cmbPlanoContaPai").html(HTML)
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) { 
						HTML = HTML +  `<option value="">Erro ao filtrar - verifique sua conexão com a internet</option>`;

						$("#cmbPlanoContaPai").html(HTML)
					}
				})
			});
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
							<h5 class="text-uppercase font-weight-bold">Editar Plano de Contas "<?php echo $row['PlConNome']; ?>"</h5>
						</div>
						
						<input type="hidden" id="inputPlanoContasId" name="inputPlanoContasId" value="<?php echo $row['PlConId']; ?>">
						<input type="hidden" id="inputPlanoContasNome" name="inputPlanoContasNome" value="<?php echo $row['PlConNome']; ?>">
						
						<div class="card-body">								
							<div class="row">
								<div class="col-lg-2">
									<div class="form-group">
										<label for="cmbTipo">Tipo<span class="text-danger"> *</span></label>
										<select id="cmbTipo" name="cmbTipo" class="form-control form-control-select2" required>
											<option value="">Selecione</option>
											<option value="A" <?php if ($row['PlConTipo'] == 'A') echo "selected"; ?> >Analítico</option>
											<option value="S" <?php if ($row['PlConTipo'] == 'S') echo "selected"; ?> >Sintético</option>
										</select>
									</div>
								</div>
								<div class="col-lg-4">
									<label for="cmbGrupo">Grupo de Conta<span class="text-danger"> *</span></label>
									<select id="cmbGrupo" name="cmbGrupo" class="form-control form-control-select2" required>
										<option value="">Selecione</option>
										<?php 
											$sql = "SELECT GrConId, GrConCodigo, GrConNome, GrConNomePersonalizado
													FROM GrupoConta
													JOIN Situacao on SituaId = GrConStatus
													WHERE GrConUnidade = ".$_SESSION['UnidadeId']." and SituaChave = 'ATIVO'
													ORDER BY GrConCodigo ASC";
											$result = $conn->query($sql);
											$rowCentroCusto = $result->fetchAll(PDO::FETCH_ASSOC);
											
											foreach ($rowCentroCusto as $item){
												$nome = $item['GrConNomePersonalizado'] != '' ? $item['GrConNomePersonalizado'] : $item['GrConNome']; 
												$seleciona = $item['GrConId'] == $row['PlConGrupo'] ? "selected" : "";

												print('<option value="'.$item['GrConId'].'" '. $seleciona .'>'.$item['GrConCodigo'].' - '.$nome.'</option>');
											}
										
										?>
									</select>
								</div>
								<div class="col-lg-4">
									<label for="cmbPlanoContaPai">Plano de Conta</label>
									<select id="cmbPlanoContaPai" name="cmbPlanoContaPai" class="form-control form-control-select2">
										<option value="">Selecione</option>
									</select>
								</div>
								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputCodigo">Código<span class="text-danger"> *</span></label>
										<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" value="<?php echo $row['PlConCodigo']; ?>" required>
									</div>
								</div>
															
							</div>
							<div class="row">
								<div class="col-lg-5">
									<div class="form-group">
										<label for="inputNome">Título<span class="text-danger"> *</span></label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Título" value="<?php echo $row['PlConNome']; ?>" required>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label for="cmbNatureza">Natureza<span class="text-danger"> *</span></label>
										<select id="cmbNatureza" name="cmbNatureza" class="form-control form-control-select2" required>
											<option value="">Selecione</option>
											<option value="D" <?php if ($row['PlConNatureza'] == 'D') echo "selected"; ?> >Despesa</option>
											<option value="R" <?php if ($row['PlConNatureza'] == 'R') echo "selected"; ?> >Receita</option>
										</select>
									</div>
								</div>	
								<div class="col-lg-3">
									<div class="form-group">
										<label for="cmbStatus">Status<span class="text-danger"> *</span></label>
										<select id="cmbStatus" name="cmbStatus" class="form-control form-control-select2" required>
											<option value="">Selecione</option>
											<option value="1" <?php if ($row['PlConStatus'] == '1') echo "selected"; ?> >Ativo</option>
											<option value="8" <?php if ($row['PlConStatus'] == '8') echo "selected"; ?> >Inativo</option>
										</select>
									</div>
								</div>	
							</div>
							<br>
							<div class="row">
								<div class="col-lg-12">
									<label for="inputDetalhamento">Detalhamento</label>
									<textarea id="inputDetalhamento" name="inputDetalhamento" class="form-control" placeholder="Detalhamento do Plano de Contas" rows="7" cols="5" ><?php echo $row['PlConDetalhamento']; ?></textarea>
								</div>
							</div>
								
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>
										<a href="planoContas.php" class="btn btn-basic">Cancelar</a>
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
