<?php 

include_once("sessao.php");

if(!$_SESSION['PerfiChave'] == "SUPER"){
	header("location:javascript://history.go(-1)");
}

$_SESSION['PaginaAtual'] = 'Diagnóstico de Enfermagem (NANDA)';

//Essa consulta é para preencher a grid
$sql = "SELECT DgNanId, DgNanCodigo, DgNanNome, DgNanStatus, SituaNome, SituaChave, SituaCor
		FROM DiagnosticoNanda
		JOIN Situacao on SituaId = DgNanStatus
		ORDER BY DgNanCodigo ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//Se estiver editando
if(isset($_POST['inputDgNanId']) && $_POST['inputDgNanId']){

	//Essa consulta é para preencher o campo Nome com o Diagnóstico de Enfermagem (NANDA) a ser editar
	$sql = "SELECT DgNanId, DgNanCodigo, DgNanNome
			FROM DiagnosticoNanda
			WHERE DgNanId = " . $_POST['inputDgNanId'];
	$result = $conn->query($sql);
	$rowNanda = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE DiagnosticoNanda SET DgNanCodigo = :sCodigo, DgNanNome = :sNome, DgNanUsuarioAtualizador = :iUsuarioAtualizador
					WHERE DgNanId = :iNanda";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sCodigo' => $_POST['inputCodigo'],
							':sNome' => $_POST['inputNome'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iNanda' => $_POST['inputDgNanId']
							));
	
			$_SESSION['msg']['mensagem'] = "Diagnóstico de enfermagem (NANDA) alterada!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO DiagnosticoNanda( DgNanCodigo, DgNanNome, DgNanStatus, DgNanUsuarioAtualizador)
					VALUES ( :sCodigo, :sNome, :bStatus, :iUsuarioAtualizador)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sCodigo' => $_POST['inputCodigo'],
							':sNome' => $_POST['inputNome'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							));
	
			$_SESSION['msg']['mensagem'] = "Diagnóstico de enfermagem (NANDA) incluída!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao atualizar diagnóstico de enfermagem (NANDA)!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("diagnosticoNanda.php");
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Diagnóstico de Enfermagem (NANDA)</title>

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
			$('#tblNanda').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   // Código
					width: "20%",
					targets: [0]
				},
				{ 
					orderable: true,   //Diagnóstico de Enfermagem (NANDA)
					width: "60%",
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
				var inputCodigo = $('#inputCodigo').val();
				var inputNomeNovo = $('#inputNome').val();
				var inputNomeVelho = $('#inputDgNanNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();
				inputCodigo = inputCodigo.trim();
				
				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == '' || inputCodigo == ''){
					
					if (inputNome == ''){
						$('#inputNome').val('');
					}

					if (inputCodigo == ''){
						$('#inputCodigo').val('');
					}

					$("#formNanda").submit();
				} else {

					//Esse ajax está sendo usado para verificar no Diagnóstico de Enfermagem (NANDA) se o registro já existe
					$.ajax({
						type: "POST",
						url: "diagnosticoNandaValida.php",
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
							
							$( "#formNanda" ).submit();
						}
					})
				}					
			})
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaNanda(Permission, DgNanId, DgNanNome, DgNanStatus, Tipo){

			if (Permission == 1){
				document.getElementById('inputDgNanId').value = DgNanId;
				document.getElementById('inputDgNanNome').value = DgNanNome;
				document.getElementById('inputDgNanStatus').value = DgNanStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formNanda.action = "diagnosticoNanda.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formNanda, "Tem certeza que deseja excluir esse diagnóstico de enfermagem (NANDA)?", "diagnosticoNandaExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formNanda.action = "diagnosticoNandaMudaSituacao.php";
				}
				
				document.formNanda.submit();
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
								<h3 class="card-title">Relação do Diagnóstico de Enfermagem (NANDA)</h3>
							</div>

							<div class="card-body">
								<form name="formNanda" id="formNanda" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputDgNanId" name="inputDgNanId" value="<?php if (isset($_POST['inputDgNanId'])) echo $_POST['inputDgNanId']; ?>" >
									<input type="hidden" id="inputDgNanNome" name="inputDgNanNome" value="<?php if (isset($_POST['inputDgNanNome'])) echo $_POST['inputDgNanNome']; ?>" >
									<input type="hidden" id="inputDgNanStatus" name="inputDgNanStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">

										<div class="col-lg-1">
											<div class="form-group">
												<label for="inputCodigo">Código <span class="text-danger"> *</span></label>
												<input type="number" max="99" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" maxlength="3" value="<?php if (isset($_POST['inputDgNanId'])) echo $rowNanda['DgNanCodigo']; ?>" required autofocus>
											</div>
										</div>
										<div class="col-lg-8">
											<div class="form-group">
												<label for="inputNome">Diagnóstico de Enfermagem (NANDA) <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Diagnóstico de Enfermagem (NANDA)" value="<?php if (isset($_POST['inputDgNanId'])) echo $rowNanda['DgNanNome']; ?>" required >
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputDgNanId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="diagnosticoNanda.php" class="btn btn-basic" role="button">Cancelar</a>');
													} else{ //inserindo
														print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
													}

												?>
											</div>
										</div>
									</div>
								</form>
							</div>
							
							<table id="tblNanda" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Código </th>
										<th>Diagnóstico de Enfermagem (NANDA)</th>
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
											<td>'.$item['DgNanCodigo'].'</td>
											<td>'.$item['DgNanNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaNanda(1,'.$item['DgNanId'].', \''.$item['DgNanNome'].'\',\''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaNanda(1,'.$item['DgNanId'].', \''.$item['DgNanNome'].'\','.$item['DgNanStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaNanda(1,'.$item['DgNanId'].', \''.$item['DgNanNome'].'\','.$item['DgNanStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
