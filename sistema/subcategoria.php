<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Sub Categoria';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT SbCatId, SbCatCodigo, SbCatNome, SbCatCategoria, SbCatStatus, CategNome, SituaNome, SituaCor, SituaChave
		FROM SubCategoria
		JOIN Categoria on CategId = SbCatCategoria
		JOIN Situacao on SituaId = SbCatStatus
	    WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ."
		ORDER BY SbCatNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//Se estiver editando
if(isset($_POST['inputSubCategoriaId']) && $_POST['inputSubCategoriaId']){

	//Essa consulta é para preencher o campo Nome com a SubCategoria a ser editada
	$sql = "SELECT SbCatId, SbCatCodigo, SbCatNome, SbCatCategoria
			FROM SubCategoria
			WHERE SbCatId = " . $_POST['inputSubCategoriaId'];
	$result = $conn->query($sql);
	$rowSubCategoria = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{
		//echo $_POST['inputEstadoAtual'];die;
		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){

			$sCodigo = $_POST['inputCodigo'];
			$sCodigos = str_pad($sCodigo,2,"0",STR_PAD_LEFT);
			
			$sql = "UPDATE SubCategoria SET SbCatCodigo = :sCodigo, SbCatNome = :sNome, SbCatCategoria = :iCategoria, SbCatUsuarioAtualizador = :iUsuarioAtualizador
					WHERE SbCatId = :iSubCategoria";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sCodigo' => $sCodigos,
							':iCategoria' => $_POST['cmbCategoria'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iSubCategoria' => $_POST['inputSubCategoriaId']
							));
	
			$_SESSION['msg']['mensagem'] = "SubCategoria alterada!!!";
	
		} else { //inclusão

			
			$sCodigo = $_POST['inputCodigo'];
			$sCodigos = str_pad($sCodigo,2,"0",STR_PAD_LEFT);
		
			$sql = "INSERT INTO SubCategoria (SbCatCodigo, SbCatNome, SbCatCategoria, SbCatStatus, SbCatUsuarioAtualizador, SbCatEmpresa)
					VALUES (:sCodigo, :sNome, :sCategoria, :bStatus, :iUsuarioAtualizador, :iEmpresa)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sCodigo' => $sCodigos,
							':sCategoria' => $_POST['cmbCategoria'] == '' ? null : $_POST['cmbCategoria'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iEmpresa' => $_SESSION['EmpreId'],
							));
	
			$_SESSION['msg']['mensagem'] = "SubCategoria incluída!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com a SubCategoria!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("subCategoria.php");
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | SubCategoria</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

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
			$('#tblSubCategoria').DataTable( {
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
					orderable: true,   //SubCategoria
					width: "35%",
					targets: [1]
				},
				{ 
					orderable: true,   //Categoria
					width: "35%",
					targets: [2]
				},
				{ 
					orderable: true,   //Situação
					width: "10%",
					targets: [3]
				},
				{ 
					orderable: false,   //Ações
					width: "10%",
					targets: [4]
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
				var inputNomeVelho = $('#inputSubCategoriaNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				var cmbCategoriaNovo = $('#cmbCategoria').val();
				var cmbCategoriaVelho = $('#cmbCategoriaEdita').val();
				var url = "subcategoriaValida.php";
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();

				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formSubCategoria").submit();
				} else {

					inputsValues = {
						nomeNovo: inputNome,
						nomeVelho: inputNomeVelho,
						estadoAtual: inputEstadoAtual,
						categoriaNovo: cmbCategoriaNovo,
						categoriaVelho: cmbCategoriaVelho
					};

					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.post(
						url,
						inputsValues,
						(data) => {

							if(data == 1){
								alerta('Atenção','Esse registro já existe!','error');
								return false;
							}						

							if (data == 'EDITA'){
								document.getElementById('inputEstadoAtual').value = 'GRAVA_EDITA';
							} else{
								document.getElementById('inputEstadoAtual').value = 'GRAVA_NOVO';
							}

							$( "#formSubCategoria" ).submit();
						}
					);
				}
			})		
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaSubCategoria(Permission, SbCatId, SbCatNome,  SbCatCategoria, SbCatStatus, Tipo){
			if (Permission == 1){
				document.getElementById('inputSubCategoriaId').value = SbCatId;
				document.getElementById('cmbCategoria').value = SbCatCategoria;
				document.getElementById('inputSubCategoriaNome').value = SbCatNome;
				document.getElementById('inputSubCategoriaStatus').value = SbCatStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formSubCategoria.action = "subCategoria.php";	
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formSubCategoria, "Tem certeza que deseja excluir essa subcategoria?", "subcategoriaExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formSubCategoria.action = "subcategoriaMudaSituacao.php";
				} else if (Tipo == 'imprime'){
					document.formSubCategoria.action = "subcategoriaImprime.php";
					document.formSubCategoria.setAttribute("target", "_blank");
				}
				
				document.formSubCategoria.submit();
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
								<h3 class="card-title">Relação de Sub Categorias</h3>
							</div>

							<div class="card-body">
								<form name="formSubCategoria" id="formSubCategoria" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputSubCategoriaId" name="inputSubCategoriaId" value="<?php if (isset($_POST['inputSubCategoriaId'])) echo $_POST['inputSubCategoriaId']; ?>" >
									<input type="hidden" id="inputSubCategoriaNome" name="inputSubCategoriaNome" value="<?php if (isset($_POST['inputSubCategoriaNome'])) echo $_POST['inputSubCategoriaNome']; ?>" >
									<input type="hidden" id="cmbCategoriaEdita" name="cmbCategoriaEdita" value="<?php if (isset($_POST['cmbCategoria'])) echo $_POST['cmbCategoria']; ?>" >
									<input type="hidden" id="inputSubCategoriaStatus" name="inputSubCategoriaStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-1">
											<div class="form-group">
												<label for="inputCodigo">Código </label>
												<input type="number" max="99" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" value="<?php if (isset($_POST['inputSubCategoriaId'])) echo $rowSubCategoria['SbCatCodigo']; ?>"autofocus>
											</div>
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputNome">Nome da SubCategoria <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="SubCategoria" value="<?php if (isset($_POST['inputSubCategoriaId'])) echo $rowSubCategoria['SbCatNome']; ?>" required>
											</div>
										</div>
										<div class="col-lg-4">
											<label for="cmbCategoria">Categoria<span class="text-danger"> *</span></label>
											<select id="cmbCategoria" name="cmbCategoria" class="form-control select-search" required>
												<option value="">Selecione</option>
												<?php 
													$sql = "SELECT CategId, CategNome
															FROM Categoria
															JOIN Situacao on SituaId = CategStatus
															WHERE SituaChave = 'ATIVO' and CategEmpresa = ".$_SESSION['EmpreId']." 
															ORDER BY CategNome ASC";
													$result = $conn->query($sql);
													$rowCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
													
													foreach ($rowCategoria as $item){
														$seleciona = $item['CategId'] == $rowSubCategoria['SbCatCategoria'] ? "selected" : "";
														print('<option value="'.$item['CategId'].'" '. $seleciona .'>'.$item['CategNome'].'</option>');
													}
												

												?>
											</select>
										</div>			
										<div class="col-lg-3">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputSubCategoriaId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="subCategoria.php" class="btn btn-basic" role="button">Cancelar</a>');
													} else{ //inserindo
														print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
													}

												?>
											</div>
										</div>
									</div>
								</form>
							</div>
							
							<table id="tblSubCategoria" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Código</th>
										<th>Sub Categoria</th>
										<th>Categoria</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){
										
										$situacao = $item['SituaNome'];
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										$situacaoChave ='\''.$item['SituaChave'].'\'';
										
										print('
										<tr>
											<td>'.$item['SbCatCodigo'].'</td>
											<td>'.$item['SbCatNome'].'</td>
											<td>'.$item['CategNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaSubCategoria(1,'.$item['SbCatId'].', \''.addslashes($item['SbCatNome']).'\', '.$item['SbCatCategoria'].', '.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaSubCategoria(1,'.$item['SbCatId'].', \''.addslashes($item['SbCatNome']).'\', '.$item['SbCatCategoria'].','.$item['SbCatStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaSubCategoria(1,'.$item['SbCatId'].', \''.addslashes($item['SbCatNome']).'\', '.$item['SbCatCategoria'].','.$item['SbCatStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>														
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
