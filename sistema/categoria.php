<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Categoria';

//Essa consulta é para preencher a grid
//O ALTERAR é usado na importação de Produtos (eles não devem aparecer aqui)
$sql = "SELECT CategId, CategCodigo, CategNome, CategStatus, SituaNome, SituaChave, SituaCor
		FROM Categoria
		JOIN Situacao on SituaId = CategStatus
	    WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave != 'ALTERAR'
		ORDER BY CategNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);
//var_dump($count);die;

//Se estiver editando
if(isset($_POST['inputCategoriaId']) && $_POST['inputCategoriaId']){

	//Essa consulta é para preencher o campo Nome com a categoria a ser editada
	$sql = "SELECT CategId, CategCodigo, CategNome
			FROM Categoria
			WHERE CategId = " . $_POST['inputCategoriaId'];
	$result = $conn->query($sql);
	$rowCategoria = $result->fetch(PDO::FETCH_ASSOC);
		
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){

			$sCodigo = $_POST['inputCodigo'];
			$sCodigos = str_pad($sCodigo,2,"0",STR_PAD_LEFT);
			
			$sql = "UPDATE Categoria SET CategCodigo = :sCodigo, CategNome = :sNome, CategUsuarioAtualizador = :iUsuarioAtualizador
					WHERE CategId = :iCategoria";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sCodigo' => $sCodigos,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iCategoria' => $_POST['inputCategoriaId']
							));
	
			$_SESSION['msg']['mensagem'] = "Categoria alterada!!!";
	
		} else { //inclusão

			
			$sCodigo = $_POST['inputCodigo'];
			$sCodigos = str_pad($sCodigo,2,"0",STR_PAD_LEFT);
		
			$sql = "INSERT INTO Categoria (CategCodigo, CategNome, CategStatus, CategUsuarioAtualizador, CategEmpresa)
					VALUES (:sCodigo,:sNome, :bStatus, :iUsuarioAtualizador, :iEmpresa)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sCodigo' => $sCodigos,
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iEmpresa' => $_SESSION['EmpreId'],
							));
	
			$_SESSION['msg']['mensagem'] = "Categoria incluída!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com a categoria!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("categoria.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Categoria</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<!-- Não permite que o usuário retorne para o EDITAR -->
	<script src="global_assets/js/lamparinas/stop-back.js"></script>
	
	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	
	
	<script type="text/javascript">

		$(document).ready(function (){	
			$('#tblCategoria').DataTable( {
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
					orderable: true,   //Categoria
					width: "70%",
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
				var inputNomeVelho = $('#inputCategoriaNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();

				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formCategoria").submit();
				} else {
				
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "categoriaValida.php",
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
							
							$( "#formCategoria" ).submit();
						}
					})
				}	
			})			

		})
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaCategoria(Permission, CategId, CategNome, CategStatus, Tipo){
		 
			if (Permission == 1){
				document.getElementById('inputCategoriaId').value = CategId;
				document.getElementById('inputCategoriaNome').value = CategNome;
				document.getElementById('inputCategoriaStatus').value = CategStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formCategoria.action = "categoria.php";				
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formCategoria, "Tem certeza que deseja excluir essa categoria?", "categoriaExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formCategoria.action = "categoriaMudaSituacao.php";
				} 
				
				document.formCategoria.submit();
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
								<h3 class="card-title">Relação de Categorias</h3>
							</div>

							<div class="card-body">												
								
								<form name="formCategoria" id="formCategoria" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputCategoriaId" name="inputCategoriaId" value="<?php if (isset($_POST['inputCategoriaId'])) echo $_POST['inputCategoriaId']; ?>" >
									<input type="hidden" id="inputCategoriaNome" name="inputCategoriaNome" value="<?php if (isset($_POST['inputCategoriaNome'])) echo $_POST['inputCategoriaNome']; ?>" >
									<input type="hidden" id="inputCategoriaStatus" name="inputCategoriaStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<?php
											// verifica se o perfil possui permissão de inserir caso possua ira aparecer esse camo
											if($inserir){
												print('
												<div class="col-lg-1">
													<div class="form-group">
														<label for="inputCodigo">Código </span></label>
														<input type="number" max="99" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" value="'.(isset($_POST['inputCategoriaId'])?$rowCategoria['CategCodigo']:'').'"autofocus>
													</div>
												</div>
												<div class="col-lg-5">
													<div class="form-group">
														<label for="inputNome">Nome da Categoria <span class="text-danger"> *</span></label>
														<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Categoria" value="'.(isset($_POST['inputCategoriaId'])?$rowCategoria['CategNome']:'').'" required >
													</div>
												</div>
											');
											}
										?>
										<div class="col-lg-6">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputCategoriaId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="categoria.php" class="btn btn-basic" role="button">Cancelar</a>');
													} else{ //inserindo
														// verifica se o perfil possui permissão de inserir caso possua ira aparecer esse camo
														if($inserir){
															print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
														}
													}

												?>
											</div>
										</div>
									</div>
								</form>		
							</div>					
							
							<!-- A table só filtra se colocar 6 colunas. Onde mudar isso? -->
							<table id="tblCategoria" class="table">
								<caption>Categorias</caption>
								<thead>
									<tr class="bg-slate">
										<th data-filter>Código</th>
										<th data-filter>Categoria</th>
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
											<td>'.$item['CategCodigo'].'</td>
											<td>'.$item['CategNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaCategoria(1,'.$item['CategId'].', \''.addslashes($item['CategNome']).'\',\''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaCategoria('.$atualizar.','.$item['CategId'].', \''.addslashes($item['CategNome']).'\',\''.$item['SituaChave'].'\', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaCategoria('.$excluir.','.$item['CategId'].', \''.addslashes($item['CategNome']).'\',\''.$item['SituaChave'].'\', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
