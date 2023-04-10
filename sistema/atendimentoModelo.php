<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Modelo';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT AtModId, AtModTipoModelo, AtModDescricao, AtModConteudo, AtModStatus, AtTMoNome, SituaNome, SituaCor, SituaChave
        FROM AtendimentoModelo
		LEFT JOIN AtendimentoTipoModelo on AtTMoId = AtModTipoModelo
		JOIN Situacao on SituaId = AtModStatus
	    WHERE AtModUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY AtModTipoModelo ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);
//var_dump($count);die;

//Se estiver editando
if(isset($_POST['inputModeloId']) && $_POST['inputModeloId']){

	//Essa consulta é para preencher o campo Nome com o Modelo a ser editar
	$sql = "SELECT AtModId, AtModTipoModelo, AtModDescricao, AtModConteudo
			FROM AtendimentoModelo
			WHERE AtModId= " . $_POST['inputModeloId'];
	$result = $conn->query($sql);
	$rowModelo = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE AtendimentoModelo SET AtModTipoModelo = :sTipoModelo, AtModDescricao = :sDescricao, AtModConteudo = :sConteudo,  AtModUsuarioAtualizador = :iUsuarioAtualizador
					WHERE AtModId = :iModelo";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sTipoModelo' => $_POST['cmbModelo'],
							':sDescricao' => $_POST['inputDescricao'],
							':sConteudo' => $_POST['txtConteudo'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iModelo' => $_POST['inputModeloId']
							));
	
			$_SESSION['msg']['mensagem'] = "Modelo alterado!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO AtendimentoModelo (AtModTipoModelo, AtModDescricao, AtModConteudo, AtModStatus, AtModUsuarioAtualizador, AtModUnidade)
					VALUES (:sTipoModelo, :sDescricao, :sConteudo, :bStatus, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sTipoModelo' => $_POST['cmbModelo'],
							':sDescricao' => $_POST['inputDescricao'],
							':sConteudo' => $_POST['txtConteudo'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidade' => $_SESSION['UnidadeId'],
							));
	
			$_SESSION['msg']['mensagem'] = "Modelo incluído!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com o Modelo!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage(); exit;
	}

	irpara("atendimentoModelo.php");
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
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>	
	
	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	
		
	
	<script type="text/javascript">

		$(document).ready(function() {

			$('#summernote').summernote();

			$('#enviar').on('click', function(e){
				e.preventDefault();
				$( "formModelo" ).submit();
			})
		}); //document.ready

		$(document).ready(function (){	
			$('#tblModelo').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Tipo Modelo
					width: "45%",
					targets: [0]
				},
				{
					orderable: true,   //Descrição
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

				var cmbModelo = $('#cmbModelo').val();
				var inputNomeNovo = $('#inputDescricao').val();
				var inputNomeVelho = $('#inputTipoDescricao').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();

				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputDescricao').val('');
					$("#formModelo").submit();
				} else {
				
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "atendimentoModeloValida.php",
						data: ('nomeNovo='+inputNome+'&nomeVelho='+inputNomeVelho+'&cmbModelo='+cmbModelo+'&estadoAtual='+inputEstadoAtual),
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
		function atualizaModelo(Permission, AtModId, AtModDescricao, AtModStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputModeloId').value = AtModId;
				document.getElementById('inputTipoDescricao').value = AtModDescricao;
				document.getElementById('inputModeloStatus').value = AtModStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formModelo.action = "atendimentoModelo.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formModelo, "Tem certeza que deseja excluir esse Modelo?", "atendimentoModeloExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formModelo.action = "atendimentoModeloMudaSituacao.php";
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
								<h3 class="card-title">Relação de Modelo</h3>
							</div>

							<div class="card-body">
								<form name="formModelo" id="formModelo" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputModeloId" name="inputModeloId" value="<?php if (isset($_POST['inputModeloId'])) echo $_POST['inputModeloId']; ?>" >
									<input type="hidden" id="inputTipoDescricao" name="inputTipoDescricao" value="<?php if (isset($_POST['inputTipoDescricao'])) echo $_POST['inputTipoDescricao']; ?>" >
									<input type="hidden" id="inputModeloStatus" name="inputModeloStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										
										<div class="col-lg-6">
											<label for="cmbModelo">Tipo do Modelo<span class="text-danger"> *</span></label>
											<select id="cmbModelo" name="cmbModelo" class="form-control select-search" required>
												<option value="">Selecione</option>
												<?php 
													$sql = "SELECT AtTMoId, AtTMoNome
															FROM AtendimentoTipoModelo
															JOIN Situacao ON SituaId = AtTMoStatus
															WHERE SituaChave = 'ATIVO'
														    ORDER BY AtTMoNome ASC";
													$result = $conn->query($sql);
													$rowGrupo = $result->fetchAll(PDO::FETCH_ASSOC);
													
													foreach ($rowGrupo as $item){
														$seleciona = $item['AtTMoId'] == $rowModelo['AtModTipoModelo'] ? "selected" : "";
														print('<option value="'.$item['AtTMoId'].'" '. $seleciona .'>'.$item['AtTMoNome'].'</option>');
													}
												?>
											</select>
										</div>
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputDescricao">Descrição <span class="text-danger"> *</span></label>
												<input type="text" id="inputDescricao" name="inputDescricao" class="form-control" placeholder="Descrição" value="<?php if (isset($_POST['inputModeloId'])) echo $rowModelo['AtModDescricao']; ?>" required autofocus>
											</div>
										</div>
										
									</div>
									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="txtConteudo">Conteúdo do Modelo<span class="text-danger"> *</span></label>
												<textarea rows="5" cols="5" class="form-control" id="summernote" name="txtConteudo" placeholder="Conteúdo do Modelo (informe aqui o texto que você queira que apareça no conteúdo do modelo)" required ><?php if (isset($_POST['inputModeloId'])) echo $rowModelo['AtModConteudo']; ?></textarea>
											</div>
										</div>
									</div>
									<div class="row">	
										<div class="col-lg-3">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputModeloId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="atendimentoModelo.php" class="btn btn-basic" role="button">Cancelar</a>');
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
							<table id="tblModelo" class="table">
								<thead>
									<tr class="bg-slate">
										<th data-filter>Tipo do Modelo</th>
										<th data-filter>Descrição</th>
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
											<td>'.$item['AtTMoNome'].'</td>
											<td>'.$item['AtModDescricao'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaModelo(1,'.$item['AtModId'].', \''.$item['AtModDescricao'].'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">');

										

										print('
										<div class="list-icons">
											<div class="list-icons list-icons-extended">
												<a href="#" onclick="atualizaModelo(1,'.$item['AtModId'].', \''.$item['AtModDescricao'].'\', '.$item['AtModStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar" ></i></a>
												<a href="#" onclick="atualizaModelo(1,'.$item['AtModId'].', \''.$item['AtModDescricao'].'\', '.$item['AtModStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
