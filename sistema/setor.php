<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Setor';

include('global_assets/php/conexao.php');

if (isset($_POST['inputEmpresaId'])){
	$_SESSION['EmpresaId'] = $_POST['inputEmpresaId'];
	$_SESSION['EmpresaNome'] = $_POST['inputEmpresaNome'];
}

if (isset($_SESSION['EmpresaId'])){
	
	//Essa consulta é para preencher a grid usando a coluna Unidade
	$sql = "SELECT SetorId, SetorNome, SetorStatus, UnidaNome, SituaNome, SituaCor, SituaChave
			FROM Setor
			JOIN Situacao on SituaId = SetorStatus
			JOIN Unidade on UnidaId = SetorUnidade
			WHERE UnidaEmpresa = ". $_SESSION['EmpresaId'] ."
			ORDER BY SetorNome ASC";
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	//$count = count($row);

} else{
	
	//Essa consulta é para preencher a grid sem a coluna Unidade, já que aqui é a unidade do usuário logado
	$sql = "SELECT SetorId, SetorNome, SetorStatus, SituaNome, SituaCor, SituaChave
			FROM Setor
			JOIN Situacao on SituaId = SetorStatus
			WHERE SetorUnidade = ". $_SESSION['UnidadeId'] ."
			ORDER BY SetorNome ASC";
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	//$count = count($row);
}

//Se estiver editando
if(isset($_POST['inputSetorId']) && $_POST['inputSetorId']){

	//Essa consulta é para preencher o campo Nome com a Setor a ser editado
	$sql = "SELECT SetorId, SetorNome, SetorUnidade
			FROM Setor
			WHERE SetorId = " . $_POST['inputSetorId'];
	$result = $conn->query($sql);
	$rowSetor = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		if (isset($_SESSION['EmpresaId'])){
			$iUnidade = $_POST['cmbUnidade'];
		} else{
			$iUnidade = $_SESSION['UnidadeId'];
		}

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE Setor SET SetorNome = :sNome, SetorUnidade = :iUnidade, SetorUsuarioAtualizador = :iUsuarioAtualizador
					WHERE SetorId = :iSetor";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':iUnidade' => $iUnidade,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iSetor' => $_POST['inputSetorId']
							));
	
			$_SESSION['msg']['mensagem'] = "Setor alterado!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO Setor (SetorNome, SetorStatus, SetorUsuarioAtualizador, SetorUnidade, SetorEmpresa)
					VALUES (:sNome, :bStatus, :iUsuarioAtualizador, :iUnidade, :iEmpresa)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidade' => $iUnidade,
							':iEmpresa' => $_SESSION['EmpresaId'] 
							));
	
			$_SESSION['msg']['mensagem'] = "Setor incluído!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com o Setor!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("setor.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Setor</title>

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
			
			$('#tblSetor').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Setor
					width: "80%",
					targets: [0]
				},
				{ 
					orderable: true,   //Situação
					width: "10%",
					targets: [1]
				},
				{ 
					orderable: true,   //Ações
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
			
			$('#tblSetorEmpresa').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Setor
					width: "40%",
					targets: [0]
				},
				{
					orderable: true,   //Unidade
					width: "40%",
					targets: [1]
				},
				{ 
					orderable: true,   //Situação
					width: "10%",
					targets: [2]
				},
				{ 
					orderable: true,   //Ações
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
				var inputNomeVelho = $('#inputSetorNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				var cmbUnidade  = $('#cmbUnidade').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();

				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formSetor").submit();
				} else {
				
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "setorValida.php",
						data: ('nomeNovo='+inputNome+'&nomeVelho='+inputNomeVelho+'&estadoAtual='+inputEstadoAtual+'&unidade='+cmbUnidade),
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
							
							$( "#formSetor" ).submit();
						}
					})
				}	
			})				

		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaSetor(Permission, SetorId, SetorNome, SetorStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputSetorId').value = SetorId;
				document.getElementById('inputSetorNome').value = SetorNome;
				document.getElementById('inputSetorStatus').value = SetorStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formSetor.action = "setor.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formSetor, "Tem certeza que deseja excluir esse setor?", "setorExclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formSetor.action = "setorMudaSituacao.php";
				} else if (Tipo == 'imprime'){
					document.formSetor.action = "setorImprime.php";
					document.formSetor.setAttribute("target", "_blank");
				}
				
				document.formSetor.submit();
			} else{
				alerta('Permissão Negada!','');
			}
		}		
			
	</script>

</head>

<body class="navbar-top <?php if (isset($_SESSION['EmpresaId'])) echo "sidebar-xs"; ?>">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php include_once("menu-left.php"); ?>

		<?php 
			  if (isset($_SESSION['EmpresaId'])){ 
				include_once("menuLeftSecundario.php");
			  } 
		?>		

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
								<h3 class="card-title">Relação de Setores</h3>
							</div>

							<div class="card-body">
							<form name="formSetor" id="formSetor" method="post" class="form-validate-jquery">

								<input type="hidden" id="inputSetorId" name="inputSetorId" value="<?php if (isset($_POST['inputSetorId'])) echo $_POST['inputSetorId']; ?>" >
								<input type="hidden" id="inputSetorNome" name="inputSetorNome" value="<?php if (isset($_POST['inputSetorNome'])) echo $_POST['inputSetorNome']; ?>" >
								<input type="hidden" id="inputSetorStatus" name="inputSetorStatus" >
								<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

								<div class="row">
									<?php 
										if (isset($_SESSION['EmpresaId'])){ 
											print('<div class="col-lg-5">');
										} else{
											print('<div class="col-lg-6">');  
										}
									?>
										<div class="form-group">
											<label for="inputNome">Setor <span class="text-danger"> *</span></label>
											<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Setor" value="<?php if (isset($_POST['inputSetorId'])) echo $rowSetor['SetorNome']; ?>" required autofocus>
										</div>
									</div>

									<?php 
							
										if (isset($_SESSION['EmpresaId'])){
											
											print('
											<div class="col-lg-4">
												<div class="form-group">
													<label for="cmbUnidade">Unidade<span class="text-danger"> *</span></label>
													<select name="cmbUnidade" id="cmbUnidade" class="form-control form-control-select2" required>
														<option value="">Informe uma unidade</option>');
														
														$sql = "SELECT UnidaId, UnidaNome
																FROM Unidade
																JOIN Situacao on SituaId = UnidaStatus															     
																WHERE UnidaEmpresa = " . $_SESSION['EmpresaId'] . " and SituaChave = 'ATIVO'
																ORDER BY UnidaNome ASC";
														$result = $conn->query($sql);
														$rowUnidade = $result->fetchAll(PDO::FETCH_ASSOC);

														foreach ($rowUnidade as $item) {
															$seleciona = $item['UnidaId'] == $rowSetor['SetorUnidade'] ? "selected" : "";
															print('<option value="' . $item['UnidaId'].'" '. $seleciona .'>' . $item['UnidaNome'] . '</option>');
														}

											print('												
													</select>
												</div>
											</div>
											');
										} else{
											print('<input type="hidden" id="cmbUnidade" value="0" >');
										}
									?>

									<div class="col-lg-3">
										<div class="form-group" style="padding-top:25px;">
											<?php

												//editando
												if (isset($_POST['inputSetorId'])){
													print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
													print('<a href="setor.php" class="btn btn-basic" role="button">Cancelar</a>');
												} else{ //inserindo
													print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
												}

											?>
										</div>
									</div>
								</div>
							</form>

							</div>
							
							<?php 
							
								if (isset($_SESSION['EmpresaId'])){
									print('<table id="tblSetorEmpresa" class="table">');
								} else {
									print('<table id="tblSetor" class="table">');
								}
							?>	
							
								<thead>
									<tr class="bg-slate">
										<th>Setor</th>

										<?php 
											if (isset($_SESSION['EmpresaId'])){
												print('<td>Unidade</td>');
											}
										?>
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
											<td>'.$item['SetorNome'].'</td>
											');
										
										if (isset($_SESSION['EmpresaId'])){
											print('<td>'.$item['UnidaNome'].'</td>');
										}

										print('<td><a href="#" onclick="atualizaSetor(1,'.$item['SetorId'].', \''.$item['SetorNome'].'\',\''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaSetor('.$atualizar.','.$item['SetorId'].', \''.$item['SetorNome'].'\','.$item['SetorStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaSetor('.$excluir.','.$item['SetorId'].', \''.$item['SetorNome'].'\','.$item['SetorStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>														
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
