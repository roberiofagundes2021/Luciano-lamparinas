<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Grupo Conta';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT GrConId, GrConCodigo, GrConNome, GrConNomePersonalizado, GrConStatus, SituaNome, SituaCor, SituaChave
		FROM GrupoConta
		JOIN Situacao on SituaId = GrConStatus
	    WHERE GrConUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY GrConNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

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

		$(document).ready(function (){	
			$('#tblGrupoConta').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Código
					width: "10%",
					targets: [0]
				},
				{ 
					orderable: true,   //Grupo Conta
					width: "70%",
					targets: [1]
				},
				{ 
					orderable: false,   //Situação
					width: "10%",
					targets: [2]
				},
				{ 
					orderable: false,   //Ações
					width: "10%",
					targets: [3]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
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
				var inputNomeVelho = $('#inputGrupoContaNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();
				
				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formGrupoConta").submit();
				} else {
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "grupoValida.php",
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
							
							$( "#formGrupoConta" ).submit();
						}
					})
				}
			})				
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaGrupoConta(Permission, GrConId, GrConNome, GrConStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputGrupoContaId').value = GrConId;
				document.getElementById('inputGrupoContaNome').value = GrConNome;
				document.getElementById('inputGrupoContaStatus').value = GrConStatus;
						
				if (Tipo == 'edita'){	
					//document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formGrupoConta.action = "grupoEdita.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formGrupoConta, "Tem certeza que deseja excluir esse grupo conta?", "grupoExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formGrupoConta.action = "grupoMudaSituacao.php";
				} 
			
				document.formGrupoConta.submit();
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
								<h3 class="card-title">Relação de Grupo Conta</h3>
							</div>

							<!--
							<div class="card-body">

								<form name="formGrupoConta" id="formGrupoConta" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputGrupoContaId" name="inputGrupoContaId" value="<?php if (isset($_POST['inputGrupoContaId'])) echo $_POST['inputGrupoContaId']; ?>" >
									<input type="hidden" id="inputGrupoContaNome" name="inputGrupoContaNome" value="<?php if (isset($_POST['inputGrupoContaNome'])) echo $_POST['inputGrupoContaNome']; ?>" >
									<input type="hidden" id="inputGrupoContaStatus" name="inputGrupoContaStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNome">Nome do Grupo Conta <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Grupo Conta" value="<?php if (isset($_POST['inputGrupoContaId'])) echo $rowGrupoConta['GrConNome']; ?>" required autofocus>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="form-group" style="padding-top:25px;">
												<?php
													/*
													//editando
													if (isset($_POST['inputGrupoContaId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="grupo.php" class="btn btn-basic" role="button">Cancelar</a>');
													} else{ //inserindo
														print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
													}*/

												?>
											</div>
										</div>
									</div>
								</form>
							</div>-->
							
							<table id="tblGrupoConta" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Código</th>
										<th>Grupo Conta</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){
										$nome = $item['GrConNomePersonalizado'] != '' ? $item['GrConNomePersonalizado'] : $item['GrConNome'];
										$situacao = $item['GrConStatus'] == 1 ? 'Ativo' : 'Inativo';
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										$situacaoChave ='\''.$item['SituaChave'].'\'';
										
										print('
										<tr>
											<td>'.$item['GrConCodigo'].'</td>
											<td>'.$nome.'</td>
											');
										
										print('<td><a href="#" onclick="atualizaGrupoConta(1,'.$item['GrConId'].', \''.addslashes($item['GrConNome']).'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaGrupoConta(1,'.$item['GrConId'].', \''.addslashes($item['GrConNome']).'\','.$item['GrConStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
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

				<form name="formGrupoConta" method="post">
					<input type="hidden" id="inputGrupoContaId" name="inputGrupoContaId" value="<?php if (isset($_POST['inputGrupoContaId'])) echo $_POST['inputGrupoContaId']; ?>" >
					<input type="hidden" id="inputGrupoContaNome" name="inputGrupoContaNome" value="<?php if (isset($_POST['inputGrupoContaNome'])) echo $_POST['inputGrupoContaNome']; ?>" >
					<input type="hidden" id="inputGrupoContaStatus" name="inputGrupoContaStatus" >
				</form>

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
