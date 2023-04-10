<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Tipo de Serviço';

include('global_assets/php/conexao.php');

$iUnidade = $_SESSION['UnidadeId'];
try{		
	$sql = "SELECT COUNT(isnull(TpSerCodigo,0)) as Codigo
			FROM TipoServico
			Where TpSerUnidade = $iUnidade";
	//echo $sql;die;
	$result = $conn->query("$sql");
	$rowCodigo = $result->fetch(PDO::FETCH_ASSOC);	
	
	$sCodigo = (int)$rowCodigo['Codigo'] + 1;
	$sCodigo = str_pad($sCodigo,6,"0",STR_PAD_LEFT);
} catch(PDOException $e) {	
	echo 'Error1: ' . $e->getMessage();die;
}


//Essa consulta é para preencher a grid
$sql = "SELECT TpSerId, TpSerCodigo, TpSerNome,  TpSerStatus, SituaNome, SituaCor, SituaChave
		FROM TipoServico
		JOIN Situacao on SituaId = TpSerStatus
	    WHERE TpSerUnidade = ". $_SESSION['UnidadeId'] ."
		ORDER BY TpSerNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);
//var_dump($count);die;

//Se estiver editando
if(isset($_POST['inputTipoServicoId']) && $_POST['inputTipoServicoId']){

	//Essa consulta é para preencher o campo Nome com o Tipo de Servico a ser editar
	$sql = "SELECT TpSerId, TpSerCodigo, TpSerNome
			FROM TipoServico
			WHERE TpSerId = " . $_POST['inputTipoServicoId'];
	$result = $conn->query($sql);
	$rowTipoServico = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE TipoServico SET TpSerNome = :sNome, TpSerUsuarioAtualizador = :iUsuarioAtualizador
					WHERE TpSerId = :iTipoServico";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iTipoServico' => $_POST['inputTipoServicoId']
							));
	
			$_SESSION['msg']['mensagem'] = "Tipo de servico alterado!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO TipoServico (TpSerCodigo, TpSerNome, TpSerStatus, TpSerUsuarioAtualizador, TpSerUnidade)
					VALUES (:sCodigo, :sNome, :bStatus, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
                            ':sCodigo' => $sCodigo,
							':sNome' => $_POST['inputNome'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidade' => $_SESSION['UnidadeId'],
							));
	
			$_SESSION['msg']['mensagem'] = "Tipo de servico incluído!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com o tipo de servico!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("atendimentoTipoServico.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Tipo de Serviço</title>

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
			$('#tblTipoServico').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Código
					width: "20%",
					targets: [0]
				},
                {
					orderable: true,   //Tipo de Servico
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
				
				var inputNomeNovo = $('#inputNome').val();
				var inputNomeVelho = $('#inputTipoServicoNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();

				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formTipoServico").submit();
				} else {
				
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "atendimentoTipoServicoValida.php",
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
							
							$( "#formTipoServico" ).submit();
						}
					})
				}	
			})							
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaTipoServico(Permission, TpSerId, TpSerNome, TpSerStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputTipoServicoId').value = TpSerId;
				document.getElementById('inputTipoServicoNome').value = TpSerNome;
				document.getElementById('inputTipoServicoStatus').value = TpSerStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formTipoServico.action = "atendimentoTipoServico.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formTipoServico, "Tem certeza que deseja excluir esse tipo de serviço?", "atendimentoTipoServicoExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formTipoServico.action = "atendimentoTipoServicoMudaSituacao.php";
				} 
				
				document.formTipoServico.submit();
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
								<h3 class="card-title">Relação dos Tipos de Serviços</h3>
							</div>

							<div class="card-body">
								<form name="formTipoServico" id="formTipoServico" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputTipoServicoId" name="inputTipoServicoId" value="<?php if (isset($_POST['inputTipoServicoId'])) echo $_POST['inputTipoServicoId']; ?>" >
									<input type="hidden" id="inputTipoServicoNome" name="inputTipoServicoNome" value="<?php if (isset($_POST['inputTipoServicoNome'])) echo $_POST['inputTipoServicoNome']; ?>" >
									<input type="hidden" id="inputTipoServicoStatus" name="inputTipoServicoStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNome">Nome do Tipo de Serviço <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Tipo de Serviço" value="<?php if (isset($_POST['inputTipoServicoId'])) echo $rowTipoServico['TpSerNome']; ?>" required autofocus>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputTipoServicoId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="atendimentoTipoServico.php" class="btn btn-basic" role="button">Cancelar</a>');
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
							<table id="tblTipoServico" class="table">
								<thead>
									<tr class="bg-slate">
                                        <th data-filter>Código</th>
										<th data-filter>Tipo de Serviço</th>
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
                                            <td>'.$item['TpSerCodigo'].'</td>
											<td>'.$item['TpSerNome'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaTipoServico(1,'.$item['TpSerId'].', \''.$item['TpSerNome'].'\','.$situacaoChave.', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">');

										

										print('
										<div class="list-icons">
											<div class="list-icons list-icons-extended">
												<a href="#" onclick="atualizaTipoServico(1,'.$item['TpSerId'].', \''.$item['TpSerNome'].'\', '.$item['TpSerStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar" ></i></a>
												<a href="#" onclick="atualizaTipoServico(1,'.$item['TpSerId'].', \''.$item['TpSerNome'].'\', '.$item['TpSerStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
