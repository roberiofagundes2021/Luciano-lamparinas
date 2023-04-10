<?php 

include_once("sessao.php");

if(!$_SESSION['PerfiChave'] == "SUPER"){
	header("location:javascript://history.go(-1)");
}

$_SESSION['PaginaAtual'] = 'TipoFiscal';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT TpFisId, TpFisNome, TpFisStatus, SituaNome, SituaCor, SituaChave
		FROM TipoFiscal
		JOIN Situacao on SituaId = TpFisStatus
		ORDER BY TpFisNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//Se estiver editando
if(isset($_POST['inputTipoFiscalId']) && $_POST['inputTipoFiscalId']){

	//Essa consulta é para preencher o campo Nome com o Tipo Fiscal oa ser editar
	$sql = "SELECT TpFisId, TpFisNome
			FROM TipoFiscal
			WHERE TpFisId = " . $_POST['inputTipoFiscalId'];
	$result = $conn->query($sql);
	$rowTipoFiscal = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE TipoFiscal SET TpFisNome = :sNome, TpFisUsuarioAtualizador = :iUsuarioAtualizador
					WHERE TpFisId = :iTipoFiscal";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iTipoFiscal' => $_POST['inputTipoFiscalId']
							));
	
			$_SESSION['msg']['mensagem'] = "Tipo Fiscal alterado!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO TipoFiscal (TpFisNome, TpFisStatus, TpFisUsuarioAtualizador)
					VALUES (:sNome, :bStatus, :iUsuarioAtualizador)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							));
	
			$_SESSION['msg']['mensagem'] = "Tipo Fiscal incluído!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com o Tipo Fiscal!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("tipoFiscal.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Tipo Fiscal</title>

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
			$('#tblTipoFiscal').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Tipo Fiscal
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
				var inputNomeVelho = $('#inputTipoFiscalNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();
				
				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formTipoFiscal").submit();
				} else {
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "tipoFiscalValida.php",
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
							
							$( "#formTipoFiscal" ).submit();
						}
					})
				}
			})				
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaTipoFiscal(Permission, TpFisId, TpFisNome, TpFisStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputTipoFiscalId').value = TpFisId;
				document.getElementById('inputTipoFiscalNome').value = TpFisNome;
				document.getElementById('inputTipoFiscalStatus').value = TpFisStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formTipoFiscal.action = "tipoFiscal.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formTipoFiscal, "Tem certeza que deseja excluir esse Tipo Fiscal?", "tipoFiscalExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formTipoFiscal.action = "tipoFiscalMudaSituacao.php";
				}
				
				document.formTipoFiscal.submit();
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
								<h3 class="card-title">Relação do Tipo Fiscal</h3>
							</div>

							<div class="card-body">

								<form name="formTipoFiscal" id="formTipoFiscal" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputTipoFiscalId" name="inputTipoFiscalId" value="<?php if (isset($_POST['inputTipoFiscalId'])) echo $_POST['inputTipoFiscalId']; ?>" >
									<input type="hidden" id="inputTipoFiscalNome" name="inputTipoFiscalNome" value="<?php if (isset($_POST['inputTipoFiscalNome'])) echo $_POST['inputTipoFiscalNome']; ?>" >
									<input type="hidden" id="inputTipoFiscalStatus" name="inputTipoFiscalStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNome">Nome do Tipo Fiscal <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Tipo Fiscal" value="<?php if (isset($_POST['inputTipoFiscalId'])) echo $rowTipoFiscal['TpFisNome']; ?>" required autofocus>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputTipoFiscalId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="tipoFiscal.php" class="btn btn-basic" role="button">Cancelar</a>');
													} else{ //inserindo
														print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
													}

												?>
											</div>
										</div>
									</div>
								</form>
							</div>
							
							<table id="tblTipoFiscal" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Tipo Fiscal</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){
										
										$situacao = $item['TpFisStatus'] == 1 ? 'Ativo' : 'Inativo';
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										$situacaoChave ='\''.$item['SituaChave'].'\'';
										
										print('
										<tr>
											<td>'.$item['TpFisNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaTipoFiscal(1,'.$item['TpFisId'].', \''.$item['TpFisNome'].'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaTipoFiscal('.$atualizar.','.$item['TpFisId'].', \''.$item['TpFisNome'].'\','.$item['TpFisStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaTipoFiscal('.$excluir.','.$item['TpFisId'].', \''.$item['TpFisNome'].'\','.$item['TpFisStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
