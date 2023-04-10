<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Especialidade';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT EspecId, EspecNome, EspecStatus, SituaNome, SituaCor, SituaChave
		FROM Especialidade
		JOIN Situacao on SituaId = EspecStatus
	    WHERE EspecUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY EspecNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//Se estiver editando
if(isset($_POST['inputEspecialidadeId']) && $_POST['inputEspecialidadeId']){

	//Essa consulta é para preencher o campo Nome com a especialidade a ser editar
	$sql = "SELECT EspecId, EspecNome
			FROM Especialidade
			WHERE EspecId = " . $_POST['inputEspecialidadeId'];
	$result = $conn->query($sql);
	$rowEspecialidade = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE Especialidade SET EspecNome = :sNome, EspecUsuarioAtualizador = :iUsuarioAtualizador
					WHERE EspecId = :iEspecialidade";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iEspecialidade' => $_POST['inputEspecialidadeId']
							));
	
			$_SESSION['msg']['mensagem'] = "Especialidade alterada!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO Especialidade (EspecNome, EspecStatus, EspecUsuarioAtualizador, EspecUnidade)
					VALUES (:sNome, :bStatus, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidade' => $_SESSION['UnidadeId'],
							));
	
			$_SESSION['msg']['mensagem'] = "Especialidade incluída!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com a especialidade!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("especialidade.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Especialidade</title>

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
			$('#tblEspecialidade').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Especialidade
					width: "80%",
					targets: [0]
				},
				{ 
					orderable: true,   //Situação
					width: "10%",
					targets: [1]
				},
				{ 
					orderable: false,   //Ações
					width: "10%",
					targets: [2]
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
				var inputNomeVelho = $('#inputEspecialidadeNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();
				
				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formEspecialidade").submit();
				} else {
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "especialidadeValida.php",
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
							
							$( "#formEspecialidade" ).submit();
						}
					})
				}
			})				
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaEspec(Permission, EspecId, EspecNome, EspecStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputEspecialidadeId').value = EspecId;
				document.getElementById('inputEspecialidadeNome').value = EspecNome;
				document.getElementById('inputEspecialidadeStatus').value = EspecStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formEspecialidade.action = "especialidade.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formEspecialidade, "Tem certeza que deseja excluir essa especialiade?", "especialidadeExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formEspecialidade.action = "especialidadeMudaSituacao.php";
				} else if (Tipo == 'imprime'){
					document.formEspecialidade.action = "especialidadeImprime.php";
					document.formEspecialidade.setAttribute("target", "_blank");
				}
			
				document.formEspecialidade.submit();
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
								<h3 class="card-title">Relação de Especialidade</h3>
							</div>

							<div class="card-body">

								<form name="formEspecialidade" id="formEspecialidade" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputEspecialidadeId" name="inputEspecialidadeId" value="<?php if (isset($_POST['inputEspecialidadeId'])) echo $_POST['inputEspecialidadeId']; ?>" >
									<input type="hidden" id="inputEspecialidadeNome" name="inputEspecialidadeNome" value="<?php if (isset($_POST['inputEspecialidadeNome'])) echo $_POST['inputEspecialidadeNome']; ?>" >
									<input type="hidden" id="inputEspecialidadeStatus" name="inputEspecialidadeStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNome">Nome da Especialidade <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Especialidade" value="<?php if (isset($_POST['inputEspecialidadeId'])) echo $rowEspecialidade['EspecNome']; ?>" required autofocus>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputEspecialidadeId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="especialidade.php" class="btn btn-basic" role="button">Cancelar</a>');
													} else{ //inserindo
														print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
													}

												?>
											</div>
										</div>
									</div>
								</form>
							</div>
							
							<table id="tblEspecialidade" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Especialidade</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){
										
										$situacao = $item['EspecStatus'] == 1 ? 'Ativo' : 'Inativo';
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										$situacaoChave ='\''.$item['SituaChave'].'\'';
										
										print('
										<tr>
											<td>'.$item['EspecNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaEspec(1,'.$item['EspecId'].', \''.addslashes($item['EspecNome']).'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaEspec(1,'.$item['EspecId'].', \''.addslashes($item['EspecNome']).'\','.$item['EspecStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaEspec(1,'.$item['EspecId'].', \''.addslashes($item['EspecNome']).'\','.$item['EspecStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
