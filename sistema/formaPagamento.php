<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Forma de Pagamento';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT FrPagId, FrPagNome, FrPagChave, FrPagStatus, SituaNome, SituaCor, SituaChave
		FROM FormaPagamento
		JOIN Situacao on SituaId = FrPagStatus
	    WHERE FrPagUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY FrPagNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);
//var_dump($count);die;

//Se estiver editando
if(isset($_POST['inputFormaPagamentoId']) && $_POST['inputFormaPagamentoId']){

	//Essa consulta é para preencher o campo Nome com a Forma de Pagamento a ser editar
	$sql = "SELECT FrPagId, FrPagNome, FrPagChave
			FROM FormaPagamento
			WHERE FrPagId = " . $_POST['inputFormaPagamentoId'];
	$result = $conn->query($sql);
	$rowFormaPagamento = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE FormaPagamento SET FrPagNome = :sNome, FrPagChave = :sChave, FrPagUsuarioAtualizador = :iUsuarioAtualizador
					WHERE FrPagId = :iFormaPagamento";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sChave' => formatarChave($_POST['inputNome']),
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iFormaPagamento' => $_POST['inputFormaPagamentoId']
							));
	
			$_SESSION['msg']['mensagem'] = "Forma de Pagamento alterado!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO FormaPagamento (FrPagNome, FrPagChave, FrPagStatus, FrPagUsuarioAtualizador, FrPagUnidade)
					VALUES (:sNome, :sChave, :bStatus, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sChave' => formatarChave($_POST['inputNome']),
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidade' => $_SESSION['UnidadeId'],
							));
	
			$_SESSION['msg']['mensagem'] = "Forma de Pagamento incluído!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com a Forma de Pagamento!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("formaPagamento.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Forma de Pagamento</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>	
	
	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	
		
	
	<script type="text/javascript">

		$(document).ready(function (){	
			$('#tblFormaPagamento').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Forma Pagamento
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
				var inputNomeVelho = $('#inputFormaPagamentoNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();

				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formFormaPagamento").submit();
				} else {
				
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "formaPagamentoValida.php",
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
							
							$( "#formFormaPagamento" ).submit();
						}
					})
				}	
			})							
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaFormaPagamento(Permission, FrPagId, FrPagNome, FrPagChave, FrPagStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputFormaPagamentoId').value = FrPagId;
				document.getElementById('inputFormaPagamentoNome').value = FrPagNome;
				document.getElementById('inputFormaPagamentoChave').value = FrPagChave;
				document.getElementById('inputFormaPagamentoStatus').value = FrPagStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formFormaPagamento.action = "formaPagamento.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formFormaPagamento, "Tem certeza que deseja excluir essa Forma de Pagamento?", "formaPagamentoExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formFormaPagamento.action = "formaPagamentoMudaSituacao.php";
				} 
				
				document.formFormaPagamento.submit();
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
								<h3 class="card-title">Relação de Forma de Pagamento</h3>
							</div>

							<div class="card-body">
								<form name="formFormaPagamento" id="formFormaPagamento" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputFormaPagamentoId" name="inputFormaPagamentoId" value="<?php if (isset($_POST['inputFormaPagamentoId'])) echo $_POST['inputFormaPagamentoId']; ?>" >
									<input type="hidden" id="inputFormaPagamentoNome" name="inputFormaPagamentoNome" value="<?php if (isset($_POST['inputFormaPagamentoNome'])) echo $_POST['inputFormaPagamentoNome']; ?>" >
									<input type="hidden" id="inputFormaPagamentoChave" name="inputFormaPagamentoChave" value="<?php if (isset($_POST['inputFormaPagamentoChave'])) echo $_POST['inputFormaPagamentoChave']; ?>" >
									<input type="hidden" id="inputFormaPagamentoStatus" name="inputFormaPagamentoStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNome">Nome da Forma de Pagamento <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Forma de Pagamento" value="<?php if (isset($_POST['inputFormaPagamentoId'])) echo $rowFormaPagamento['FrPagNome']; ?>" required autofocus>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputFormaPagamentoId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="formaPagamento.php" class="btn btn-basic" role="button">Cancelar</a>');
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
							<table id="tblFormaPagamento" class="table">
								<thead>
									<tr class="bg-slate">
										<th data-filter>Forma de Pagamento</th>
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
											<td>'.$item['FrPagNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaFormaPagamento(1,'.$item['FrPagId'].', \''.$item['FrPagNome'].'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">');

										if ($item['FrPagChave'] != 'CHEQUE') {

											print('
											<div class="list-icons">
												<div class="list-icons list-icons-extended">
													<a href="#" onclick="atualizaFormaPagamento('.$atualizar.','.$item['FrPagId'].', \''.$item['FrPagNome'].'\', \''.$item['FrPagChave'].'\', '.$item['FrPagStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar" ></i></a>
													<a href="#" onclick="atualizaFormaPagamento('.$excluir.','.$item['FrPagId'].', \''.$item['FrPagNome'].'\', \''.$item['FrPagChave'].'\', '.$item['FrPagStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
												</div>
											</div>								
											');            
										}	
										
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
