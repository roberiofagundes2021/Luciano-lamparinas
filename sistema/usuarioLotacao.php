<?php 

	include_once("sessao.php"); 

	$_SESSION['PaginaAtual'] = 'Lotacao';

	include('global_assets/php/conexao.php');

	if (isset($_POST['inputUsuarioId'])){
		$_SESSION['UsuarioId'] = $_POST['inputUsuarioId'];
		$_SESSION['UsuarioNome'] = $_POST['inputUsuarioNome'];
		$_SESSION['UsuarioPerfil'] = $_POST['inputUsuarioPerfil'];
		$_SESSION['EmpresaUsuarioPerfil'] = $_POST['inputEmpresaUsuarioPerfil'];
	}

	if (!isset($_SESSION['UsuarioId'])){
		irpara('usuario.php');
	}

	if (isset($_SESSION['EmpresaId'])){	
		$EmpresaId =   $_SESSION['EmpresaId'];
		$EmpresaNome = $_SESSION['EmpresaNome'];
	} else {	
		$EmpresaId = $_SESSION['EmpreId'];
		$EmpresaNome = $_SESSION['EmpreNomeFantasia'];
	}

	//Essa consulta é para preencher a grid usando a coluna Unidade
	$sql = "SELECT UsXUnEmpresaUsuarioPerfil, UsXUnUnidade, UsXUnSetor, UnidaNome, SetorNome, LcEstNome, EmpreNomeFantasia, UsXUnOperadorCaixa
			FROM UsuarioXUnidade
			JOIN Unidade ON UnidaId = UsXUnUnidade
			JOIN Setor ON SetorId = UsXUnSetor
			LEFT JOIN LocalEstoque on LcEstId = UsXUnLocalEstoque
			JOIN EmpresaXUsuarioXPerfil on EXUXPId = UsXUnEmpresaUsuarioPerfil
			JOIN Empresa on EmpreId = EXUXPEmpresa
			WHERE EXUXPUsuario = ".$_SESSION['UsuarioId'];
	
	if (!isset($_SESSION['EmpresaId'])){
		$sql .= " and UsXUnUnidade = ".$_SESSION['UnidadeId'];
	}
	
	$sql .=	"ORDER BY UsXUnUnidade";
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	//$count = count($row);
	//echo $sql;die;

	//Se estiver gravando (inclusão)
	if (isset($_POST['cmbSetor'])){

		try{

			$visibilidadeResumoFinanceiro = isset($_POST['inputVisualisaResumoFinanceiro']) ? true : false;

			$operadorCaixa = isset($_POST['inputOperadorCaixa']) ? true : false;

			if (isset($_SESSION['EmpresaId'])){
				$iUnidade = $_POST['cmbUnidade'];
			} else{
				$iUnidade = $_SESSION['UnidadeId'];
			}

			//echo $_POST['cmbUnidade'];die;
			$sql = "INSERT INTO UsuarioXUnidade (UsXUnEmpresaUsuarioPerfil, UsXUnUnidade, UsXUnSetor, UsXUnLocalEstoque, UsXUnPermissaoPerfil, UsXUnResumoFinanceiro, UsXUnOperadorCaixa, UsXUnUsuarioAtualizador)
						VALUES (:iEmpresaUsarioPerfil, :iUnidade, :iSetor, :iLocalEstoque, :PermissaoPerfil, :bResumoFinanceiro, :bOperadorCaixa, :iUsuarioAtualizador)";
			$result = $conn->prepare($sql);
	
			$result->execute(array(
				':iEmpresaUsarioPerfil' => $_SESSION['EmpresaUsuarioPerfil'],
				':iUnidade' => $iUnidade,
				':iSetor' => $_POST['cmbSetor'],
				':iLocalEstoque' => isset($_POST['cmbLocalEstoque']) && $_POST['cmbLocalEstoque'] != '' ? $_POST['cmbLocalEstoque'] : null,
				':PermissaoPerfil' => 1,
				':bResumoFinanceiro' => $visibilidadeResumoFinanceiro,
				':bOperadorCaixa' => $operadorCaixa,
				':iUsuarioAtualizador' => $_SESSION['UsuarId']
				));
			
			$_SESSION['msg']['titulo'] = "Sucesso";
			$_SESSION['msg']['mensagem'] = "Lotação incluída!!!";
			$_SESSION['msg']['tipo'] = "success";
			
		} catch(PDOException $e) {
			
			$_SESSION['msg']['titulo'] = "Erro";
			$_SESSION['msg']['mensagem'] = "Erro ao incluir Lotação!!!";
			$_SESSION['msg']['tipo'] = "error";	
			
			echo 'Error: ' . $e->getMessage();die;
		}

		irpara("usuarioLotacao.php");
	}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Lotação</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->

	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>
	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>	

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
  	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
  	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	
	<!-- /theme JS files -->	
	
	<script type="text/javascript">
		
		$(document).ready(function() {

			//Valida Registro Duplicado
			$('#enviar').on('click', function(e) {

			e.preventDefault();

				var cmbUnidade = $('#cmbUnidade').val(); 
				var cmbSetor = $('#cmbSetor').val(); 
					
				if (cmbSetor == '') {
					$("#formLotacao").submit();
				} else {
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "usuarioLotacaoValida.php",
						data: ('unidade='+cmbUnidade),
						success: function(resposta) {

						if (resposta == 1) {
							alerta('Atenção','Essa unidade já foi vinculada ao usuário!','error');
							return false;
						}

						$("#formLotacao").submit();
						}
					})
				}
			})

			$('#cmbUnidade').on('change', function(e) {

				Filtrando();

				var cmbUnidade = $('#cmbUnidade').val();

				if (cmbUnidade == '') {
				ResetSetor();
				ResetLocalEstoque();
				} else {

				$.getJSON('filtraSetor.php?idUnidade=' + cmbUnidade, function(dados) {

				var option = '<option value="">Selecione o Setor</option>';

				if (dados.length) {

					$.each(dados, function(i, obj) {
					option += '<option value="' + obj.SetorId + '">' + obj.SetorNome + '</option>';
					});

					$('#cmbSetor').html(option).show();
				} else {
					ResetSetor();
				}
				});

				$.getJSON('filtraLocalEstoque.php?idUnidade=' + cmbUnidade, function(dados) {

					var option = '<option value="">Selecione o Local de Estoque</option>';

						if (dados.length) {

						$.each(dados, function(i, obj) {
							option += '<option value="' + obj.LcEstId + '">' + obj.LcEstNome + '</option>';
						});

						$('#cmbLocalEstoque').html(option).show();
						} else {
							ResetLocalEstoque();
						}
					});

				}
			});
						
			/* Início: Tabela Personalizada */
			$('#tblLotacao').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{ 
					orderable: true,   //Setor
					width: "45%",
					targets: [0]
				},
				{ 
					orderable: true,   //Local Estoque
					width: "30%",
					targets: [1]
				},
				{ 
					orderable: true,   //Operador de Caixa
					width: "20%",
					targets: [2]
				},								
				{ 
					orderable: false,  //Ações
					width: "5%",
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

			$('#tblLotacaoEmpresa').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{ 
					orderable: true,   //Empresa
					width: "20%",
					targets: [0]
				},
				{ 
					orderable: true,   //Unidade
					width: "20%",
					targets: [1]
				},
				{ 
					orderable: true,   //Setor
					width: "20%",
					targets: [2]
				},
				{ 
					orderable: true,   //Local Estoque
					width: "20%",
					targets: [3]
				},
				{ 
					orderable: true,   //Operador de Caixa
					width: "15%",
					targets: [4]
				},								
				{ 
					orderable: false,  //Ações
					width: "5%",
					targets: [5]
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
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaLotacao(UsXUnEmpresaUsuarioPerfil, UsXUnUnidade, Tipo){
		
			document.getElementById('inputEmpresaUsuarioPerfil').value = UsXUnEmpresaUsuarioPerfil;
			document.getElementById('inputUnidade').value = UsXUnUnidade;
			
			if (Tipo == 'exclui'){
				confirmaExclusao(document.formLotacao, "Tem certeza que deseja excluir essa Lotação?", "usuarioLotacaoExclui.php");
			} 		

			document.formLotacao.submit();
		}		

		function Filtrando() {
			$('#cmbSetor').empty().append('<option value="">Filtrando...</option>');
			$('#cmbLocalEstoque').empty().append('<option value="">Filtrando...</option>');
		}

		function ResetSetor() {
			$('#cmbSetor').empty().append('<option value="">Sem setor</option>');
		}

     	 function ResetLocalEstoque() {
			$('#cmbLocalEstoque').empty().append('<option value="">Sem Local de Estoque</option>');
		}	

	</script>

</head>

	<?php
		
		if (isset($_SESSION['EmpresaId'])){	
			print('<body class="navbar-top sidebar-xs">');
		} else {
			print('<body class="navbar-top">');
		}

		include_once("topo.php");
	?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php 
			
			include_once("menu-left.php"); 
		
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
								<h3 class="card-title">Relação de Lotação</h3>	
								<div class="text-right">
								 	<a href="usuario.php" style="margin-right: 10px;"><< Usuários</a>
								</div>		
							</div>

							<div class="card-body">

								A relação abaixo faz referência às unidades que o usuário <span style="color: #FF0000; font-weight: bold;"><?php echo $_SESSION['UsuarioNome']; ?></span> tem acesso, além de informar qual setor esse usuário está lotado em cada unidade.
								
								<form name="formLotacao" id="formLotacao" method="post" class="form-validate-jquery">
									
									<input type="hidden" id="inputEmpresaUsuarioPerfil" name="inputEmpresaUsuarioPerfil">
									<input type="hidden" id="inputUnidade" name="inputUnidade" >
									
									<div class="card-body" style="margin-top:10px;">
										<div class="row">

											<?php
											if (isset($_SESSION['EmpresaId'])){
											
												print('
												<div class="col-lg-3">
													<div class="form-group">
														<label for="cmbUnidade">Unidade<span class="text-danger"> *</span></label>
														<select name="cmbUnidade" id="cmbUnidade" class="form-control form-control-select2" required>
														<option value="">Informe uma unidade</option>');
														
														$sql = "SELECT UnidaId, UnidaNome
																FROM Unidade
																JOIN Situacao on SituaId = UnidaStatus															     
																WHERE UnidaEmpresa = " . $EmpresaId . " and SituaChave = 'ATIVO'
																ORDER BY UnidaNome ASC";
														$result = $conn->query($sql);
														$rowUnidade = $result->fetchAll(PDO::FETCH_ASSOC);

														foreach ($rowUnidade as $item) {
															print('<option value="' . $item['UnidaId'] . '">' . $item['UnidaNome'] . '</option>');
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

											<div class="col-lg-2">
												<div class="form-group">
													<label for="cmbSetor">Setor<span class="text-danger"> *</span></label>
													<select name="cmbSetor" id="cmbSetor" class="form-control form-control-select2" required>
													<?php
														if (!isset($_SESSION['EmpresaId'])){
															$sql = "SELECT SetorId, SetorNome
																	FROM Setor
																	JOIN Situacao on SituaId = SetorStatus															     
																	WHERE SetorUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
																	ORDER BY SetorNome ASC";
															$result = $conn->query($sql);
															$rowSetor = $result->fetchAll(PDO::FETCH_ASSOC);
															
															print('<option value="">Informe um setor</option>');
															foreach ($rowSetor as $item) {
																print('<option value="' . $item['SetorId'] . '">' . $item['SetorNome'] . '</option>');
															}
														} else{
															print('<option value="">Sem setor</option>');
														}	
													?>
													</select>
												</div>
											</div>
											
											<?php 
											if ($_SESSION['UsuarioPerfil'] == 'ALMOXARIFADO'){
											?>	
												<div class="col-lg-3">
													<div class="form-group">
													<label for="cmbLocalEstoque">Local de Estoque<span class="text-danger"> *</span></label>
													<select name="cmbLocalEstoque" id="cmbLocalEstoque" class="form-control form-control-select2" required>
													<?php
													if (!isset($_SESSION['EmpresaId'])){
														$sql = "SELECT LcEstId, LcEstNome
																FROM LocalEstoque
																JOIN Situacao on SituaId = LcEstStatus
																WHERE LcEstUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
																ORDER BY LcEstNome ASC";
														$result = $conn->query($sql);
														$rowSetor = $result->fetchAll(PDO::FETCH_ASSOC);
														
														print('<option value="">Informe um local de estoque</option>');
														foreach ($rowSetor as $item) {
															print('<option value="' . $item['LcEstId'] . '">' . $item['LcEstNome'] . '</option>');
														}
													} else{
														print('<option value="">Sem local de estoque</option>');
													}	
													?>
													</select>
													</div>
												</div>
											<?php
											}
											?>

											<div class="col-lg-2" style="margin-top: auto; margin-bottom: auto;">
												<div class="custom-control custom-checkbox">
													<input type="checkbox" class="custom-control-input" value="1" id="inputVisualisaResumoFinanceiro" name="inputVisualisaResumoFinanceiro">
													<label class="custom-control-label" for="inputVisualisaResumoFinanceiro">Resumo Financeiro Visível</label>
												</div>
											</div>
											<div class="col-lg-2" style="margin-top: auto; margin-bottom: auto;">
												<div class="custom-control custom-checkbox">
													<input type="checkbox" class="custom-control-input" value="1" id="inputOperadorCaixa" name="inputOperadorCaixa">
													<label class="custom-control-label" for="inputOperadorCaixa">Operador de Caixa</label>
												</div>
											</div>
									
											<div class="col-lg-2" style="margin-top: 20px;">
												<div class="form-group">
													<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
												</div>
											</div>
										</div>
									</div>	
								</form>			
							</div>

							<?php 
							
								if (isset($_SESSION['EmpresaId'])){
									print('<table id="tblLotacaoEmpresa" class="table">');
								} else {
									print('<table id="tblLotacao" class="table">');
								}
							?>	
							<thead>
								<tr class="bg-slate">
									<?php 
										if (isset($_SESSION['EmpresaId'])){
											print('<th>Empresa</th>');
											print('<th>Unidade</th>');
										}
									?>
									<th >Setor</th>
									<th >Local de Estoque</th>
									<th >Operado de Caixa</th>
									<th class="text-center">Ações</th>
								</tr>
							</thead>
							<tbody>
								<?php
									foreach ($row as $item){

										$operadorCaixa = $item['UsXUnOperadorCaixa'] == 1 ? 'SIM' :  'NÃO';
										
										print('
										<tr>
											');
											if (isset($_SESSION['EmpresaId'])){
												print('<td>'.$item['EmpreNomeFantasia'].'</td>');
										 		print('<td>'.$item['UnidaNome'].'</td>');
											}
											
											print('
											<td>'.$item['SetorNome'].'</td>
											<td>'.$item['LcEstNome'].'</td>
											<td class="text-center">'.$operadorCaixa.'</td>
											');

											print('<td class="text-center">                             
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
													<a href="#" onclick="atualizaLotacao('.$item['UsXUnEmpresaUsuarioPerfil'].', '.$item['UsXUnUnidade'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>							
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
