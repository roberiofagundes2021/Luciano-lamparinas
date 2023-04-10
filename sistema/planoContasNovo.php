<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Novo Plano de Contas';

include('global_assets/php/conexao.php');

if(isset($_POST['inputNome'])){

	try{
		$nome = $_POST['cmbTipo'] == 'S' ? mb_strtoupper($_POST['inputNome']) : $_POST['inputNome'];
		
		$sql = "INSERT INTO PlanoConta (PlConCodigo, PlConNome, PlConTipo, PlConNatureza, PlConGrupo, PlConDetalhamento, PlConPlanoContaPai, PlConStatus, PlConUsuarioAtualizador, PlConUnidade)
				VALUES (:iCodigo, :sNome, :sTipo, :sNatureza, :sGrupo, :sDetalhamento, :sPlanoContaPai, :bStatus, :iUsuarioAtualizador, :iUnidade)";
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
						':iUnidade' => $_SESSION['UnidadeId'],
						));
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Plano de Contas incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir Plano de Contas!!!";
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
				
				var inputNome = $('#inputNome').val();

				/*caso precise validar
				let cmbTipo =  $('#cmbTipo').val();
				let cmbPlanoContaPai = $('#cmbPlanoContaPai').val();*/
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNome.trim();
				
				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				$.ajax({
					type: "POST",
					url: "planoContasValida.php",
					data: {nome : inputNome},
					success: function(resposta){
						
						if(resposta == 1){
							alerta('Atenção','Já exite Centro de Custo ligado a um Plano de Contas com este nome!','error');
							return false;
						}
						console.log(resposta)

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
				var tipo = $(this).find(":selected").val();
				
				if(tipo == 'S') {
					$('#inputNome').css('text-transform', 'uppercase');
				}else {
					$('#inputNome').css('text-transform', '');
				}
			});

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
							<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Plano de Contas</h5>
						</div>
						
						<div class="card-body">						
							<div class="row">
								<div class="col-lg-2">
									<div class="form-group">
										<label for="cmbTipo">Tipo<span class="text-danger"> *</span></label>
										<select id="cmbTipo" name="cmbTipo" class="form-control form-control-select2" required autofocus>
											<option value="">Selecione </option>
											<option value="A">Analítico</option>
											<option value="S">Sintético</option>
										</select>
									</div>
								</div>
								<div class="col-lg-4">
									<label for="cmbGrupo">Grupo de Conta<span class="text-danger"> *</span></label>
									<select id="cmbGrupo" name="cmbGrupo" class="form-control form-control-select2" required>
										<option value="">Selecione </option>
										<?php 
											$sql = "SELECT GrConId, GrConCodigo, GrConNome, GrConNomePersonalizado
													FROM GrupoConta
													JOIN Situacao on SituaId = GrConStatus
													WHERE GrConUnidade = ".$_SESSION['UnidadeId']." and SituaChave = 'ATIVO'
													ORDER BY GrConCodigo ASC";
											$result = $conn->query($sql);
											$row = $result->fetchAll(PDO::FETCH_ASSOC);
											
											foreach ($row as $item){
												$nome = $item['GrConNomePersonalizado'] != '' ? $item['GrConNomePersonalizado'] : $item['GrConNome']; 
												
												print('<option value="'.$item['GrConId'].'">'.$item['GrConCodigo'].' - '.$nome.'</option>');
											}
										
										?>
									</select>
								</div>
								<div class="col-lg-4">
									<label for="cmbPlanoContaPai">Plano de Contas</label>
									<select id="cmbPlanoContaPai" name="cmbPlanoContaPai" class="form-control form-control-select2" >
										<option value="">Selecione </option>
									</select>
								</div>
								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputCodigo">Código<span class="text-danger"> *</span></label>
										<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" required>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-5">
									<div class="form-group">
										<label for="inputNome">Título<span class="text-danger"> *</span></label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Título" required>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label for="cmbNatureza">Natureza<span class="text-danger"> *</span></label>
										<select id="cmbNatureza" name="cmbNatureza" class="form-control form-control-select2" required>
											<option value="">Selecione </option>
											<option value="D">Despesa </option>
											<option value="R">Receita</option>
										</select>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="form-group">
										<label for="cmbStatus">Status<span class="text-danger"> *</span></label>
										<select id="cmbStatus" name="cmbStatus" class="form-control form-control-select2" required>
											<option value="">Selecione </option>
											<option value="1">Ativo </option>
											<option value="8">Inativo</option>
										</select>
									</div>
								</div>		
							</div>
                              <br>
							<div class="row">
								<div class="col-lg-12">
									<label for="inputDetalhamento">Detalhamento</label>
									<textarea id="inputDetalhamento" name="inputDetalhamento" class="form-control" placeholder="Detalhamento do Plano de Conta" rows="7" cols="5" ></textarea>
								</div>
							</div>
															
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="planoContas.php" class="btn btn-basic" role="button">Cancelar</a>
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
