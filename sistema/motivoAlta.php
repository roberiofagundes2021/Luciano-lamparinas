<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Motivo da Alta';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT MtAltId, MtAltNome, MtAltTipoAlta, MtAltStatus, TpAltNome, SituaNome, SituaCor, SituaChave
		FROM MotivoAlta
		LEFT JOIN TipoAlta on TpAltId = MtAltTipoAlta
		JOIN Situacao on SituaId = MtAltStatus
	    WHERE MtAltUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY MtAltNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);
//var_dump($count);die;

//Se estiver editando
if(isset($_POST['inputMotivoAltaId']) && $_POST['inputMotivoAltaId']){

	//Essa consulta é para preencher o campo Nome com o Motivo da Alta a ser editar
	$sql = "SELECT MtAltId, MtAltNome, MtAltTipoAlta
			FROM MotivoAlta
			WHERE MtAltId = " . $_POST['inputMotivoAltaId'];
	$result = $conn->query($sql);
	$rowMotivoAlta = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE MotivoAlta SET MtAltNome = :sNome, MtAltTipoAlta = :sTipoAlta, MtAltUsuarioAtualizador = :iUsuarioAtualizador
					WHERE MtAltId = :iMotivoAlta";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sTipoAlta' => $_POST['cmbTipoAlta'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iMotivoAlta' => $_POST['inputMotivoAltaId']
							));
	
			$_SESSION['msg']['mensagem'] = "Motivo da Alta alterada!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO MotivoAlta (MtAltNome, MtAltTipoAlta, MtAltStatus, MtAltUsuarioAtualizador, MtAltUnidade)
					VALUES (:sNome, :sTipoAlta, :bStatus, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sTipoAlta' => $_POST['cmbTipoAlta'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidade' => $_SESSION['UnidadeId'],
							));
	
			$_SESSION['msg']['mensagem'] = "Motivo da Alta incluída!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com o Motivo da Alta!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("motivoAlta.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Motivo da Alta</title>

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
			$('#tblMotivoAlta').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Motivo da Alta
					width: "45%",
					targets: [0]
				},
				{
					orderable: true,   //Tipo da Alta
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
				var inputNomeVelho = $('#inputMotivoAltaNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();

				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formMotivoAlta").submit();
				} else {
				
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "motivoAltaValida.php",
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
							
							$( "#formMotivoAlta" ).submit();
						}
					})
				}	
			})							
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaMotivoAlta(Permission, MtAltId, MtAltNome, MtAltStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputMotivoAltaId').value = MtAltId;
				document.getElementById('inputMotivoAltaNome').value = MtAltNome;
				document.getElementById('inputMotivoAltaStatus').value = MtAltStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formMotivoAlta.action = "motivoAlta.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formMotivoAlta, "Tem certeza que deseja excluir esse motivo da alta?", "motivoAltaExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formMotivoAlta.action = "motivoAltaMudaSituacao.php";
				} 
				
				document.formMotivoAlta.submit();
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
								<h3 class="card-title">Relação do Motivo da Alta</h3>
							</div>

							<div class="card-body">
								<form name="formMotivoAlta" id="formMotivoAlta" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputMotivoAltaId" name="inputMotivoAltaId" value="<?php if (isset($_POST['inputMotivoAltaId'])) echo $_POST['inputMotivoAltaId']; ?>" >
									<input type="hidden" id="inputMotivoAltaNome" name="inputMotivoAltaNome" value="<?php if (isset($_POST['inputMotivoAltaNome'])) echo $_POST['inputMotivoAltaNome']; ?>" >
									<input type="hidden" id="inputMotivoAltaStatus" name="inputMotivoAltaStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputNome">Nome do Motivo da Alta <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Motivo da Alta" value="<?php if (isset($_POST['inputMotivoAltaId'])) echo $rowMotivoAlta['MtAltNome']; ?>" required autofocus>
											</div>
										</div>
										<div class="col-lg-4">
											<label for="cmbTipoAlta">Tipo da Alta<span class="text-danger"> *</span></label>
											<select id="cmbTipoAlta" name="cmbTipoAlta" class="form-control select-search" required>
												<option value="">Selecione</option>
												<?php 
													$sql = "SELECT TpAltId, TpAltNome
															FROM TipoAlta
															JOIN Situacao ON SituaId = TpAltStatus
															WHERE SituaChave = 'ATIVO'
														    ORDER BY TpAltNome ASC";
													$result = $conn->query($sql);
													$rowTipoAlta = $result->fetchAll(PDO::FETCH_ASSOC);
													
													foreach ($rowTipoAlta as $item){
														$seleciona = $item['TpAltId'] == $rowMotivoAlta['MtAltTipoAlta'] ? "selected" : "";
														print('<option value="'.$item['TpAltId'].'" '. $seleciona .'>'.$item['TpAltNome'].'</option>');
													}
												?>
											</select>
										</div>
										
										<div class="col-lg-3">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputMotivoAltaId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="motivoAlta.php" class="btn btn-basic" role="button">Cancelar</a>');
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
							<table id="tblMotivoAlta" class="table">
								<thead>
									<tr class="bg-slate">
										<th data-filter>Motivo da Alta</th>
										<th data-filter>Tipo da Alta</th>
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
											<td>'.$item['MtAltNome'].'</td>
											<td>'.$item['TpAltNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaMotivoAlta(1,'.$item['MtAltId'].', \''.$item['MtAltNome'].'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">');

										

										print('
										<div class="list-icons">
											<div class="list-icons list-icons-extended">
												<a href="#" onclick="atualizaMotivoAlta(1,'.$item['MtAltId'].', \''.$item['MtAltNome'].'\', '.$item['MtAltStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar" ></i></a>
												<a href="#" onclick="atualizaMotivoAlta(1,'.$item['MtAltId'].', \''.$item['MtAltNome'].'\', '.$item['MtAltStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
