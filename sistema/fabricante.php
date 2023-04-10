<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Fabricante';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid

$sql = "SELECT FabriId, FabriNome, FabriStatus, SituaNome, SituaCor, SituaChave
		FROM Fabricante
		JOIN Situacao on SituaId = FabriStatus
	    WHERE FabriEmpresa = ". $_SESSION['EmpreId'] ."
		ORDER BY FabriNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//Se estiver editando
if(isset($_POST['inputFabricanteId']) && $_POST['inputFabricanteId']){

	//Essa consulta é para preencher o campo Nome com a fabricante a ser editar
	$sql = "SELECT FabriId, FabriNome
			FROM Fabricante
			WHERE FabriId = " . $_POST['inputFabricanteId'];
	$result = $conn->query($sql);
	$rowFabricante = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE Fabricante SET FabriNome = :sNome, FabriUsuarioAtualizador = :iUsuarioAtualizador
					WHERE FabriId = :iFabricante";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iFabricante' => $_POST['inputFabricanteId']
							));
	
			$_SESSION['msg']['mensagem'] = "Fabricante alterado!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO Fabricante (FabriNome, FabriStatus, FabriUsuarioAtualizador, FabriEmpresa)
					VALUES (:sNome, :bStatus, :iUsuarioAtualizador, :iEmpresa)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iEmpresa' => $_SESSION['EmpreId'],
							));
	
			$_SESSION['msg']['mensagem'] = "Fabricante incluído!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com a fabricante!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("fabricante.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Fabricante</title>

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
			$('#tblFabricante').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Fabricante
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
				var inputNomeVelho = $('#inputFabricanteNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();

				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formFabricante").submit();
				} else {
				
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "fabricanteValida.php",
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
							
							$( "#formFabricante" ).submit();
						}
					})
                }
			})				

		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaFabricante(Permission, FabriId, FabriNome, FabriStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputFabricanteId').value = FabriId;
				document.getElementById('inputFabricanteNome').value = FabriNome;
				document.getElementById('inputFabricanteStatus').value = FabriStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formFabricante.action = "fabricante.php";			
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formFabricante, "Tem certeza que deseja excluir esse fabricante?", "fabricanteExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formFabricante.action = "fabricanteMudaSituacao.php";
				} else if (Tipo == 'imprime'){
					document.formFabricante.action = "fabricanteImprime.php";
					document.formFabricante.setAttribute("target", "_blank");
				}
				
				document.formFabricante.submit();
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
								<h3 class="card-title">Relação de Fabricantes</h3>
							</div>

							<div class="card-body">
								
							<form name="formFabricante" id="formFabricante" method="post" class="form-validate-jquery">

								<input type="hidden" id="inputFabricanteId" name="inputFabricanteId" value="<?php if (isset($_POST['inputFabricanteId'])) echo $_POST['inputFabricanteId']; ?>" >
								<input type="hidden" id="inputFabricanteNome" name="inputFabricanteNome" value="<?php if (isset($_POST['inputFabricanteNome'])) echo $_POST['inputFabricanteNome']; ?>" >
								<input type="hidden" id="inputFabricanteStatus" name="inputFabricanteStatus" >
								<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

								<div class="row">
									<div class="col-lg-6">
										<div class="form-group">
											<label for="inputNome">Nome do Fabricante <span class="text-danger"> *</span></label>
											<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Fabricante" value="<?php if (isset($_POST['inputFabricanteId'])) echo $rowFabricante['FabriNome']; ?>" required autofocus>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="form-group" style="padding-top:25px;">
											<?php

												//editando
												if (isset($_POST['inputFabricanteId'])){
													print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
													print('<a href="fabricante.php" class="btn btn-basic" role="button">Cancelar</a>');
												} else{ //inserindo
													print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
												}

											?>
										</div>
									</div>
								</div>
							</form>

							</div>
							
							<table id="tblFabricante" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Fabricante</th>
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
											<td>'.$item['FabriNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaFabricante(1,'.$item['FabriId'].', \''.addslashes($item['FabriNome']).'\',\''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaFabricante('.$atualizar.','.$item['FabriId'].', \''.addslashes($item['FabriNome']).'\','.$item['FabriStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaFabricante('.$excluir.','.$item['FabriId'].', \''.addslashes($item['FabriNome']).'\','.$item['FabriStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>														
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
