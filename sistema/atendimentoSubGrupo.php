<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'SubGrupo';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT AtSubId, AtSubNome, AtSubGrupo, AtSubStatus, AtGruNome, SituaNome, SituaCor, SituaChave
		FROM AtendimentoSubGrupo
		LEFT JOIN AtendimentoGrupo on AtGruId = AtSubGrupo
		JOIN Situacao on SituaId = AtSubStatus
	    WHERE AtSubUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY AtSubNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);
//var_dump($count);die;

//Se estiver editando
if(isset($_POST['inputSubGrupoId']) && $_POST['inputSubGrupoId']){

	//Essa consulta é para preencher o campo Nome com o SubGrupo a ser editar
	$sql = "SELECT AtSubId, AtSubNome, AtSubGrupo
			FROM AtendimentoSubGrupo
			WHERE AtSubId = " . $_POST['inputSubGrupoId'];
	$result = $conn->query($sql);
	$rowSubGrupo = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE AtendimentoSubGrupo SET AtSubNome = :sNome, AtSubGrupo = :sGrupo, AtSubUsuarioAtualizador = :iUsuarioAtualizador
					WHERE AtSubId = :iSubGrupo";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sGrupo' => $_POST['cmbGrupo'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iSubGrupo' => $_POST['inputSubGrupoId']
							));
	
			$_SESSION['msg']['mensagem'] = "SubGrupo alterado!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO AtendimentoSubGrupo (AtSubNome, AtSubGrupo, AtSubStatus, AtSubUsuarioAtualizador, AtSubUnidade)
					VALUES (:sNome, :sGrupo, :bStatus, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sGrupo' => $_POST['cmbGrupo'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidade' => $_SESSION['UnidadeId'],
							));
	
			$_SESSION['msg']['mensagem'] = "SubGrupo incluído!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com o SubGrupo!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("atendimentoSubGrupo.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | SubGrupo</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>	
	
	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	
		
	
	<script type="text/javascript">

		$(document).ready(function (){	
			$('#tblSubGrupo').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //SubGrupo
					width: "45%",
					targets: [0]
				},
				{
					orderable: true,   //Grupo
					width: "35%",
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
				var inputNomeVelho = $('#inputSubGrupoNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();

				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formSubGrupo").submit();
				} else {
				
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "atendimentoSubGrupoValida.php",
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
							
							$( "#formSubGrupo" ).submit();
						}
					})
				}	
			})							
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaSubGrupo(Permission, AtSubId, AtSubNome, AtSubStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputSubGrupoId').value = AtSubId;
				document.getElementById('inputSubGrupoNome').value = AtSubNome;
				document.getElementById('inputSubGrupoStatus').value = AtSubStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formSubGrupo.action = "atendimentoSubGrupo.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formSubGrupo, "Tem certeza que deseja excluir esse SubGrupo?", "atendimentoSubGrupoExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formSubGrupo.action = "atendimentoSubGrupoMudaSituacao.php";
				} 
				
				document.formSubGrupo.submit();
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
								<h3 class="card-title">Relação de SubGrupo</h3>
							</div>

							<div class="card-body">
								<form name="formSubGrupo" id="formSubGrupo" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputSubGrupoId" name="inputSubGrupoId" value="<?php if (isset($_POST['inputSubGrupoId'])) echo $_POST['inputSubGrupoId']; ?>" >
									<input type="hidden" id="inputSubGrupoNome" name="inputSubGrupoNome" value="<?php if (isset($_POST['inputSubGrupoNome'])) echo $_POST['inputSubGrupoNome']; ?>" >
									<input type="hidden" id="inputSubGrupoStatus" name="inputSubGrupoStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputNome">Nome do SubGrupo <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="SubGrupo" value="<?php if (isset($_POST['inputSubGrupoId'])) echo $rowSubGrupo['AtSubNome']; ?>" required autofocus>
											</div>
										</div>
										<div class="col-lg-4">
											<label for="cmbGrupo">Grupo<span class="text-danger"> *</span></label>
											<select id="cmbGrupo" name="cmbGrupo" class="form-control select-search" required>
												<option value="">Selecione</option>
												<?php 
													$sql = "SELECT AtGruId, AtGruNome
															FROM AtendimentoGrupo
															JOIN Situacao ON SituaId = AtGruStatus
															WHERE AtGruUnidade = " . $_SESSION['UnidadeId'] . " AND SituaChave = 'ATIVO'
														    ORDER BY AtGruNome ASC";
													$result = $conn->query($sql);
													$rowGrupo = $result->fetchAll(PDO::FETCH_ASSOC);
													
													foreach ($rowGrupo as $item){
														$seleciona = $item['AtGruId'] == $rowSubGrupo['AtSubGrupo'] ? "selected" : "";
														print('<option value="'.$item['AtGruId'].'" '. $seleciona .'>'.$item['AtGruNome'].'</option>');
													}
												?>
											</select>
										</div>
										
										<div class="col-lg-3">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputSubGrupoId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="atendimentoSubGrupo.php" class="btn btn-basic" role="button">Cancelar</a>');
													} else{ //inserindo
														print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
													}

												?>
											</div>
										</div>
									</div>
								</form>
							</div>					
							
							<!-- A table só filtra se colocar 6 colunas. Onde mudar isso? -->
							<table id="tblSubGrupo" class="table">
								<thead>
									<tr class="bg-slate">
										<th data-filter>SubGrupo</th>
										<th data-filter>Grupo</th>
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
											<td>'.$item['AtSubNome'].'</td>
											<td>'.$item['AtGruNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaSubGrupo(1,'.$item['AtSubId'].', \''.$item['AtSubNome'].'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">');

										

										print('
										<div class="list-icons">
											<div class="list-icons list-icons-extended">
												<a href="#" onclick="atualizaSubGrupo(1,'.$item['AtSubId'].', \''.$item['AtSubNome'].'\', '.$item['AtSubStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar" ></i></a>
												<a href="#" onclick="atualizaSubGrupo(1,'.$item['AtSubId'].', \''.$item['AtSubNome'].'\', '.$item['AtSubStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
											</div>
										</div>								
										');            
											
										
										print('
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
