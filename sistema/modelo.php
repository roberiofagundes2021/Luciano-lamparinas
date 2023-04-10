<?php

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Modelo';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT ModelId, ModelNome, ModelStatus, SituaNome, SituaCor, SituaChave
					FROM Modelo
					JOIN Situacao on SituaId = ModelStatus
	    	 WHERE ModelEmpresa = " . $_SESSION['EmpreId'] . "
			ORDER BY ModelNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//Se estiver editando
if(isset($_POST['inputModeloId']) && $_POST['inputModeloId']){

	//Essa consulta é para preencher o campo Nome com a modelo a ser editar
	$sql = "SELECT ModelId, ModelNome
			FROM Modelo
			WHERE ModelId = " . $_POST['inputModeloId'];
	$result = $conn->query($sql);
	$rowModelo = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE Modelo SET ModelNome = :sNome, ModelUsuarioAtualizador = :iUsuarioAtualizador
					WHERE ModelId = :iModel";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iModel' => $_POST['inputModeloId']
							));
	
			$_SESSION['msg']['mensagem'] = "Modelo alterado!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO Modelo (ModelNome, ModelStatus, ModelUsuarioAtualizador, ModelEmpresa)
					VALUES (:sNome, :bStatus, :iUsuarioAtualizador, :iEmpresa)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iEmpresa' => $_SESSION['EmpreId'],
							));
	
			$_SESSION['msg']['mensagem'] = "Modelo incluído!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com a modelo!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("modelo.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Modelo</title>

	<?php include_once("head.php"); ?>

	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<!-- Não permite que o usuário retorne para o EDITAR -->
	<script src="global_assets/js/lamparinas/stop-back.js"></script>
	
	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	
	

	<script type="text/javascript">
		$(document).ready(function() {
			$('#tblModelo').DataTable({
				"order": [
					[0, "asc"]
				],
				autoWidth: false,
				responsive: true,
				columnDefs: [{
						orderable: true, //Modelo
						width: "80%",
						targets: [0]
					},
					{
						orderable: true, //Situação
						width: "10%",
						targets: [1]
					},
					{
						orderable: false, //Ações
						width: "10%",
						targets: [2]
					}
				],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: {
						'first': 'Primeira',
						'last': 'Última',
						'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;',
						'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;'
					}
				}
			});

			// Select2 for length menu styling
			var _componentSelect2 = function() {
				if (!$().select2) {
					console.warn('Warning - select2.min.js is not loaded.');
					return;
				}

				// Initialize
				$('.dataTables_length select').select2({
					minimumResultsForSearch: Infinity,
					dropdownAutoWidth: true,
					width: 'auto'
				});
			};

			_componentSelect2();

			/* Fim: Tabela Personalizada */

			//Valida Registro Duplicado
			$('#enviar').on('click', function(e){
				
				e.preventDefault();
				
				var inputNomeNovo = $('#inputNome').val();
				var inputNomeVelho = $('#inputModeloNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();

				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formModelo").submit();
				} else {
				
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "modeloValida.php",
						data: ('nomeNovo='+inputNome+'&nomeVelho='+inputNomeVelho+'&estadoAtual='+inputEstadoAtual),
						success: function(resposta){

							if(resposta == 1){
								alerta('Atenção','Esse registro já existe!','error');
								return false;
							}

							if (resposta == 'EDITA'){
								document.getElementById('inputEstadoAtual').value = 'GRAVA_EDITA';
							} else{
								document.getElementById('inputEstadoAtual').value = 'GRAVA_NOVO';
							}						
							
							$( "#formModelo" ).submit();
						}
					})
				}	
			})				

		});

		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaModelo(Permission, ModelId, ModelNome, ModelStatus, Tipo) {

			if (Permission == 1){
				document.getElementById('inputModeloId').value = ModelId;
				document.getElementById('inputModeloNome').value = ModelNome;
				document.getElementById('inputModeloStatus').value = ModelStatus;

				if (Tipo == 'edita') {
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formModelo.action = "modelo.php";		
				} else if (Tipo == 'exclui') {
					confirmaExclusao(document.formModelo, "Tem certeza que deseja excluir esse modelo?", "modeloExclui.php");
				} else if (Tipo == 'mudaStatus') {
					document.formModelo.action = "modeloMudaSituacao.php";
				} else if (Tipo == 'imprime') {
					document.formModelo.action = "modeloImprime.php";
					document.formModelo.setAttribute("target", "_blank");
				}

				document.formModelo.submit();
			} else{
				alerta('Permissão Negada!','');
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
				<div class="row">
					<div class="col-lg-12">
						<!-- Basic responsive configuration -->
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title">Relação de Modelos</h3>	
							</div>


							<div class="card-body">
								
								
								<form name="formModelo" id="formModelo" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputModeloId" name="inputModeloId" value="<?php if (isset($_POST['inputModeloId'])) echo $_POST['inputModeloId']; ?>" >
									<input type="hidden" id="inputModeloNome" name="inputModeloNome" value="<?php if (isset($_POST['inputModeloNome'])) echo $_POST['inputModeloNome']; ?>" >
									<input type="hidden" id="inputModeloStatus" name="inputModeloStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNome">Nome do Modelo <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Modelo" value="<?php if (isset($_POST['inputModeloId'])) echo $rowModelo['ModelNome']; ?>" required autofocus>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputModeloId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="modelo.php" class="btn btn-basic" role="button">Cancelar</a>');
													} else{ //inserindo
														print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
													}

												?>
											</div>
										</div>
									</div>
								</form>
		
							</div>

							<table id="tblModelo" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Modelo</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ($row as $item) {

										$situacao = $item['ModelStatus'] == 1 ? 'Ativo' : 'Inativo';
										$situacaoClasse = 'badge badge-flat border-' . $item['SituaCor'] . ' text-' . $item['SituaCor'];

										print('
										<tr>
											<td>' . $item['ModelNome'] . '</td>
											');

										print('<td><a href="#" onclick="atualizaModelo(1,' . $item['ModelId'] . ', \'' . htmlentities(addslashes($item['ModelNome']), ENT_QUOTES) . '\',\'' . $item['SituaChave'] . '\', \'mudaStatus\');"><span class="badge ' . $situacaoClasse . '">' . $situacao . '</span></a></td>');

										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaModelo('.$atualizar.',' . $item['ModelId'] . ', \'' . addslashes($item['ModelNome']) . '\',' . $item['ModelStatus'] . ', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaModelo('.$atualizar.',' . $item['ModelId'] . ', \'' . addslashes($item['ModelNome']) . '\',' . $item['ModelStatus'] . ', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>							
													</div>
												</div>
											</td>
										</tr>');
									}
									?>

								</tbody>
							</table>
						</div>
						<!-- /basic responsive configuration -->

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

	<?php include_once("alerta.php"); ?>

</body>

</html>