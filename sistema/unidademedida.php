<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Unidade de Medida';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT UnMedId, UnMedNome, UnMedSigla, UnMedStatus, SituaNome, SituaCor, SituaChave
		FROM UnidadeMedida
		JOIN Situacao on SituaId = UnMedStatus
	    WHERE UnMedEmpresa = ". $_SESSION['EmpreId'] ."
		ORDER BY UnMedNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//Se estiver editando
if(isset($_POST['inputUnidadeMedidaId']) && $_POST['inputUnidadeMedidaId']){

	//Essa consulta é para preencher o campo Nome com a Unidade de Medida a ser editar
	$sql = "SELECT UnMedId, UnMedNome, UnMedSigla
			FROM UnidadeMedida
			WHERE UnMedId = " . $_POST['inputUnidadeMedidaId'];
	$result = $conn->query($sql);
	$rowUnidadeMedida = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE UnidadeMedida SET UnMedNome = :sNome, UnMedSigla = :sSigla, UnMedUsuarioAtualizador = :iUsuarioAtualizador
					WHERE UnMedId = :iUnidadeMedida";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sSigla' => $_POST['inputSigla'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidadeMedida' => $_POST['inputUnidadeMedidaId']
							));
	
			$_SESSION['msg']['mensagem'] = "Unidade de Medida alterada!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO UnidadeMedida (UnMedNome,  UnMedSigla, UnMedStatus, UnMedUsuarioAtualizador, UnMedEmpresa)
					VALUES (:sNome, :sSigla, :bStatus, :iUsuarioAtualizador, :iEmpresa)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sSigla' => $_POST['inputSigla'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iEmpresa' => $_SESSION['EmpreId'],
							));
	
			$_SESSION['msg']['mensagem'] = "Unidade de Medida incluída!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com a Unidade de Medida!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("unidadeMedida.php");
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | UnidadeMedida</title>

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
			$('#tblUnidadeMedida').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //UnidadeMedida
					width: "60%",
					targets: [0]
				},
				{ 
					orderable: true,   //Sigla
					width: "20%",
					targets: [1]
				},
				{ 
					orderable: true,   //Situação
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
				var inputNomeVelho = $('#inputUnidadeMedidaNome').val();
				var inputSigla = $('#inputSigla').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();
				inputSigla = inputSigla.trim();
				
				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == '' || inputSigla == ''){
					
					if (inputNome == ''){
						$('#inputNome').val('');
					}

					if (inputSigla == ''){
						$('#inputSigla').val('');
					}

					$("#formUnidadeMedida").submit();
				} else {

					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "unidadeMedidaValida.php",
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
							
							$( "#formUnidadeMedida" ).submit();
						}
					})
				}					
			})
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaUnidadeMedida(Permission, UnMedId, UnMedNome, UnMedStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputUnidadeMedidaId').value = UnMedId;
				document.getElementById('inputUnidadeMedidaNome').value = UnMedNome;
				document.getElementById('inputUnidadeMedidaStatus').value = UnMedStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formUnidadeMedida.action = "unidadeMedida.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formUnidadeMedida, "Tem certeza que deseja excluir essa unidade de medida?", "unidademedidaExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formUnidadeMedida.action = "unidademedidaMudaSituacao.php";
				} else if (Tipo == 'imprime'){
					document.formUnidadeMedida.action = "unidademedidaImprime.php";
					document.formUnidadeMedida.setAttribute("target", "_blank");
				}
				
				document.formUnidadeMedida.submit();
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
								<h3 class="card-title">Relação de Unidades de Medida</h3>
							</div>

							<div class="card-body">
								<form name="formUnidadeMedida" id="formUnidadeMedida" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputUnidadeMedidaId" name="inputUnidadeMedidaId" value="<?php if (isset($_POST['inputUnidadeMedidaId'])) echo $_POST['inputUnidadeMedidaId']; ?>" >
									<input type="hidden" id="inputUnidadeMedidaNome" name="inputUnidadeMedidaNome" value="<?php if (isset($_POST['inputUnidadeMedidaNome'])) echo $_POST['inputUnidadeMedidaNome']; ?>" >
									<input type="hidden" id="inputUnidadeMedidaStatus" name="inputUnidadeMedidaStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNome">Unidade de Medida <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Unidade de Medida" value="<?php if (isset($_POST['inputUnidadeMedidaId'])) echo $rowUnidadeMedida['UnMedNome']; ?>" required autofocus>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputSigla">Sigla <span class="text-danger"> *</span></label>
												<input type="text" id="inputSigla" name="inputSigla" class="form-control" placeholder="Sigla" value="<?php if (isset($_POST['inputUnidadeMedidaId'])) echo $rowUnidadeMedida['UnMedSigla']; ?>" required autofocus>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputUnidadeMedidaId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="unidadeMedida.php" class="btn btn-basic" role="button">Cancelar</a>');
													} else{ //inserindo
														print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
													}

												?>
											</div>
										</div>
									</div>
								</form>
							</div>
							
							<table id="tblUnidadeMedida" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Unidade de Medida</th>
										<th>Sigla</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){
										
										$situacao = $item['SituaNome'];
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										
										print('
										<tr>
											<td>'.$item['UnMedNome'].'</td>
											<td>'.$item['UnMedSigla'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaUnidadeMedida(1,'.$item['UnMedId'].', \''.$item['UnMedNome'].'\',\''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
													<a href="#" onclick="atualizaUnidadeMedida('.$atualizar.','.$item['UnMedId'].', \''.$item['UnMedNome'].'\','.$item['UnMedStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
													<a href="#" onclick="atualizaUnidadeMedida('.$excluir.','.$item['UnMedId'].', \''.$item['UnMedNome'].'\','.$item['UnMedStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
