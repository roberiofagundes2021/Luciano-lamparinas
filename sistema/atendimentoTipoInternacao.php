<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Tipo de Internação';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT TpIntId, TpIntNome, TpIntStatus, SituaNome, SituaCor, SituaChave, 
		dbo.fnClassificacaoAtendimento(TpIntId, TpIntUnidade, 'TipoInternacao') as Classificacao
		FROM TipoInternacao
		JOIN Situacao on SituaId = TpIntStatus
	    WHERE TpIntUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY TpIntNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);
//var_dump($count);die;

//Se estiver editando
if(isset($_POST['inputTipoInternacaoId']) && $_POST['inputTipoInternacaoId']){

	//Essa consulta é para preencher o campo Nome com o tipo de internação a ser editado
	$sql = "SELECT TpIntId, TpIntNome, TIXClClassificacao
			FROM TipoInternacao
			LEFT JOIN TipoInternacaoXClassificacao on TIXClTipoInternacao = TpIntId
			WHERE TpIntId = " . $_POST['inputTipoInternacaoId'] ." and TpIntUnidade = ".$_SESSION['UnidadeId'];
	$result = $conn->query($sql);
	$rowTipoInternacao = $result->fetchAll(PDO::FETCH_ASSOC);

	foreach ($rowTipoInternacao as $item) {
		$aClassificacao[] = $item['TIXClClassificacao'];
		$TipoInternacaoNome = $item['TpIntNome'];
	}
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE TipoInternacao SET TpIntNome = :sNome, TpIntUsuarioAtualizador = :iUsuarioAtualizador
					WHERE TpIntId = :iTipoInternacao";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iTipoInternacao' => $_POST['inputTipoInternacaoId']
							));

			$sql = "DELETE FROM TipoInternacaoXClassificacao 
					WHERE TIXClTipoInternacao = :iTipoInternacao";
			$result = $conn->prepare($sql);
		
			$result->execute(array(':iTipoInternacao' => $_POST['inputTipoInternacaoId']));

			$insertId = $conn->lastInsertId();
			
			//Grava as Classificações
			if ($_POST['cmbClassificacao']) {

				$sql = "INSERT INTO TipoInternacaoXClassificacao (TIXClTipoInternacao, TIXClClassificacao, TIXClUnidade)
						VALUES (:iTipoInternacao, :iClassificacao, :iUnidade)";
				$result = $conn->prepare($sql);
	
				foreach ($_POST['cmbClassificacao'] as $key => $value) {
	
					$result->execute(array(
						':iTipoInternacao' => $_POST['inputTipoInternacaoId'],
						':iClassificacao' => $value,
						':iUnidade' => $_SESSION['UnidadeId']			
					));
				}
			}
	
			$_SESSION['msg']['mensagem'] = "Tipo de Internação alterado!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO TipoInternacao (TpIntNome, TpIntStatus, TpIntUsuarioAtualizador, TpIntUnidade)
					VALUES (:sNome, :bStatus, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidade' => $_SESSION['UnidadeId'],
							));

			$insertId = $conn->lastInsertId();
			
			//Grava as Classificações
			if ($_POST['cmbClassificacao']) {

				$sql = "INSERT INTO TipoInternacaoXClassificacao (TIXClTipoInternacao, TIXClClassificacao, TIXClUnidade)
						VALUES (:iTipoInternacao, :iClassificacao, :iUnidade)";
				$result = $conn->prepare($sql);
	
				foreach ($_POST['cmbClassificacao'] as $key => $value) {
	
					$result->execute(array(
						':iTipoInternacao' =>  $insertId,
						':iClassificacao' => $value,
						':iUnidade' => $_SESSION['UnidadeId']			
					));
				}
			}
	
			$_SESSION['msg']['mensagem'] = "Tipo de Internação incluído!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com o tipo de internação!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("atendimentoTipoInternacao.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Tipo de Internação</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>

	
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>	
	
	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	
		
	
	<script type="text/javascript">

		$(document).ready(function (){	
			$('#tblTipoInternacao').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Tipo de Internação
					width: "45%",
					targets: [0]
				},
				{
					orderable: true,   //Classificação
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
				var inputNomeVelho = $('#inputTipoInternacaoNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();

				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formTipoInternacao").submit();
				} else {
				
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "atendimentoTipoInternacaoValida.php",
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
							
							$( "#formTipoInternacao" ).submit();
						}
					})
				}	
			})							
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaTipoInternacao(Permission, TpIntId, TpIntNome, TpIntStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputTipoInternacaoId').value = TpIntId;
				document.getElementById('inputTipoInternacaoNome').value = TpIntNome;
				document.getElementById('inputTipoInternacaoStatus').value = TpIntStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formTipoInternacao.action = "atendimentoTipoInternacao.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formTipoInternacao, "Tem certeza que deseja excluir esse tipo de internação?", "atendimentoTipoInternacaoExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formTipoInternacao.action = "atendimentoTipoInternacaoMudaSituacao.php";
				} 
				
				document.formTipoInternacao.submit();
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
								<h3 class="card-title">Relação de Tipo de Internação</h3>
							</div>

							<div class="card-body">
								<form name="formTipoInternacao" id="formTipoInternacao" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputTipoInternacaoId" name="inputTipoInternacaoId" value="<?php if (isset($_POST['inputTipoInternacaoId'])) echo $_POST['inputTipoInternacaoId']; ?>" >
									<input type="hidden" id="inputTipoInternacaoNome" name="inputTipoInternacaoNome" value="<?php if (isset($_POST['inputTipoInternacaoNome'])) echo $_POST['inputTipoInternacaoNome']; ?>" >
									<input type="hidden" id="inputTipoInternacaoStatus" name="inputTipoInternacaoStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputNome">Nome do Tipo de Internação <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Tipo de Internacao" value="<?php if (isset($_POST['inputTipoInternacaoId'])) echo $TipoInternacaoNome; ?>" required autofocus>
											</div>
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbClassificacao">Classificação<span class="text-danger"> *</span></label>
												<select id="cmbClassificacao" name="cmbClassificacao[]" class="form-control multiselect-filtering" multiple="multiple">
													<option value="H" <?php if (isset($_POST['inputTipoInternacaoId'])) if (in_array('H', $aClassificacao)) echo "selected"; ?>>Hospitalar</option>
													<option value="A" <?php if (isset($_POST['inputTipoInternacaoId'])) if (in_array('A', $aClassificacao)) echo "selected"; ?>>Ambulátorial</option>
												</select>
											</div>
										</div>
										
										<div class="col-lg-3">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputTipoInternacaoId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="atendimentoTipoInternacao.php" class="btn btn-basic" role="button">Cancelar</a>');
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
							<table id="tblTipoInternacao" class="table">
								<thead>
									<tr class="bg-slate">
										<th data-filter>Tipo de Internação</th>
										<th data-filter>Classificação</th>
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
										$Classificacao = $item['Classificacao'];
										print('
										<tr>
											<td>'.$item['TpIntNome'].'</td>
											<td>'.$Classificacao.'</td>
											');
										
										print('<td><a href="#" onclick="atualizaTipoInternacao(1,'.$item['TpIntId'].', \''.$item['TpIntNome'].'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">');

										

										print('
										<div class="list-icons">
											<div class="list-icons list-icons-extended">
												<a href="#" onclick="atualizaTipoInternacao(1,'.$item['TpIntId'].', \''.$item['TpIntNome'].'\', '.$item['TpIntStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar" ></i></a>
												<a href="#" onclick="atualizaTipoInternacao(1,'.$item['TpIntId'].', \''.$item['TpIntNome'].'\', '.$item['TpIntStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
