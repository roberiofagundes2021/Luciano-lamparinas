<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Profissão';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT ProfiId, ProfiNome, ProfiCbo, ProfiStatus, SituaNome, SituaCor, SituaChave
		FROM Profissao
		JOIN Situacao on SituaId = ProfiStatus
		ORDER BY ProfiNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//Se estiver editando
if(isset($_POST['inputProfissaoId']) && $_POST['inputProfissaoId']){

	//Essa consulta é para preencher o campo Nome com a profissao a ser editar
	$sql = "SELECT ProfiId, ProfiNome, ProfiCbo
			FROM Profissao
			WHERE ProfiId = " . $_POST['inputProfissaoId'];
	$result = $conn->query($sql);
	$rowProfissao = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE Profissao SET ProfiNome = :sNome, ProfiChave = :sChave, ProfiCbo = :sCbo, ProfiUsuarioAtualizador = :iUsuarioAtualizador
					WHERE ProfiId = :iProfissao";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sChave' => formatarChave($_POST['inputNome']),
							':sCbo' => $_POST['inputCbo'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iProfissao' => $_POST['inputProfissaoId']
							));
	
			$_SESSION['msg']['mensagem'] = "Profissão alterada!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO Profissao (ProfiNome, ProfiChave, ProfiCbo, ProfiStatus, ProfiUsuarioAtualizador)
					VALUES (:sNome, :sChave, :sCbo, :bStatus, :iUsuarioAtualizador)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sChave' => formatarChave($_POST['inputNome']),
							':sCbo' => $_POST['inputCbo'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId']
							));
	
			$_SESSION['msg']['mensagem'] = "Profissão incluída!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com a profissão!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("profissao.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Profissão</title>

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
			$('#tblProfissao').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Profissão
					width: "50%",
					targets: [0]
				},
				{
					orderable: true,   //Profissão
					width: "30%",
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
				var inputNomeVelho = $('#inputProfissaoNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();
				
				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formProfissao").submit();
				} else {
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "profissaoValida.php",
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
							
							$( "#formProfissao" ).submit();
						}
					})
				}
			})				
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaProfi(Permission, ProfiId, ProfiNome, ProfiStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputProfissaoId').value = ProfiId;
				document.getElementById('inputProfissaoNome').value = ProfiNome;
				document.getElementById('inputProfissaoStatus').value = ProfiStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formProfissao.action = "profissao.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formProfissao, "Tem certeza que deseja excluir essa profissão?", "profissaoExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formProfissao.action = "profissaoMudaSituacao.php";
				} else if (Tipo == 'imprime'){
					document.formProfissao.action = "profissaoImprime.php";
					document.formProfissao.setAttribute("target", "_blank");
				}
			
				document.formProfissao.submit();
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
								<h3 class="card-title">Relação de Profissão</h3>
							</div>

							<div class="card-body">

								<form name="formProfissao" id="formProfissao" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputProfissaoId" name="inputProfissaoId" value="<?php if (isset($_POST['inputProfissaoId'])) echo $_POST['inputProfissaoId']; ?>" >
									<input type="hidden" id="inputProfissaoNome" name="inputProfissaoNome" value="<?php if (isset($_POST['inputProfissaoNome'])) echo $_POST['inputProfissaoNome']; ?>" >
									<input type="hidden" id="inputProfissaoNome" name="inputProfissaoCbo" value="<?php if (isset($_POST['inputProfissaoCbo'])) echo $_POST['inputProfissaoCbo']; ?>" >
									<input type="hidden" id="inputProfissaoStatus" name="inputProfissaoStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-5">
											<div class="form-group">
												<label for="inputNome">Nome da Profissão <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Profissão" value="<?php if (isset($_POST['inputProfissaoId'])) echo $rowProfissao['ProfiNome']; ?>" required autofocus>
											</div>
										</div>
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputCbo">CBO </label>
												<input type="text" id="inputCbo" name="inputCbo" class="form-control" placeholder="CBO" value="<?php if (isset($_POST['inputCbo'])) echo $rowProfissao['ProfiCbo']; ?>" autofocus>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputProfissaoId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="profissao.php" class="btn btn-basic" role="button">Cancelar</a>');
													} else{ //inserindo
														print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
													}

												?>
											</div>
										</div>
									</div>
								</form>
							</div>
							
							<table id="tblProfissao" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Profissão</th>
										<th>CBO</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){
										
										$situacao = $item['ProfiStatus'] == 1 ? 'Ativo' : 'Inativo';
										$situacaoClasse = 'badge badge-flat border-'.$item['SituaCor'].' text-'.$item['SituaCor'];
										$situacaoChave ='\''.$item['SituaChave'].'\'';
										
										print('
										<tr>
											<td>'.$item['ProfiNome'].'</td>
											<td>'.$item['ProfiCbo'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaProfi(1,'.$item['ProfiId'].', \''.addslashes($item['ProfiNome']).'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaProfi(1,'.$item['ProfiId'].', \''.addslashes($item['ProfiNome']).'\','.$item['ProfiStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaProfi(1,'.$item['ProfiId'].', \''.addslashes($item['ProfiNome']).'\','.$item['ProfiStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
