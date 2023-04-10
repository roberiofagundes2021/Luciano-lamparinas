<?php 

include_once("sessao.php");

if(!$_SESSION['PerfiChave'] == "SUPER"){
	header("location:javascript://history.go(-1)");
}

$_SESSION['PaginaAtual'] = 'Perfil padrão';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = ("SELECT PerfiId, PerfiNome, PerfiChave, PerfiStatus, SituaNome, SituaChave, SituaCor
		 FROM Perfil
		 JOIN Situacao on SituaId = PerfiStatus
		 WHERE PerfiUnidade is null and PerfiPadrao = 1");

$sql .= $_SESSION['PerfiChave'] != "SUPER"? " and PerfiChave != 'SUPER' ORDER BY PerfiNome ASC":" ORDER BY PerfiNome ASC";
$result = $conn->query("$sql");
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//Se estiver editando
if(isset($_POST['inputPerfilId']) && $_POST['inputPerfilId']){

	//Essa consulta é para preencher o campo Nome com a perfil a ser editar
	$sql = "SELECT PerfiId, PerfiNome
			FROM Perfil
			WHERE PerfiId = " . $_POST['inputPerfilId'];
	$result = $conn->query($sql);
	$rowPerfil = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{
		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE Perfil SET PerfiNome = :sNome, PerfiUsuarioAtualizador = :iUsuarioAtualizador
					WHERE PerfiId = :iPerfil and PerfiUnidade is null and PerfiPadrao = 1";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iPerfil' => $_POST['inputPerfilId']
							));
	
			$_SESSION['msg']['mensagem'] = "Perfil alterado!!!";
	
		} else { //inclusão

			$sql = "INSERT INTO Perfil (PerfiNome, PerfiChave, PerfiStatus, PerfiUsuarioAtualizador, PerfiPadrao, PerfiUnidade)
					VALUES (:sNome, :sChave, :bStatus, :iUsuarioAtualizador, :PerfiPadrao, :PerfiUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sNome' => $_POST['inputNome'],
							':sChave' => formatarChave($_POST['inputNome']),
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':PerfiPadrao' => 1,
							':PerfiUnidade' => null
							));

			$iPerfil = $conn->lastInsertId();

			$sqlMenu = "SELECT MenuId, MenuNome
			FROM Menu";
			$sqlMenu = $conn->query($sqlMenu);
			$sqlMenu = $sqlMenu->fetchAll(PDO::FETCH_ASSOC);

			$arraySuperAdmin = [
				'Bancos',
				'Tipo Fiscal',
				'Padrões',
				'Empresas'
			];

			// adicionando em PadraoPermissao
			$sql = "INSERT INTO PadraoPermissao
			(PaPerPerfil, PaPerMenu,PaPerVisualizar,PaPerAtualizar,PaPerExcluir,
			PaPerInserir, PaPerSuperAdmin) VALUES";

			foreach($sqlMenu as $menu){
				$superAdmin = in_array($menu['MenuNome'], $arraySuperAdmin)?1:0;
				$sql .= " ($iPerfil,$menu[MenuId], 1, 0, 0, 0, $superAdmin),";
			}
			$sql = substr_replace($sql ,"", -1);
			$conn->query($sql);
	
			$_SESSION['msg']['mensagem'] = "Padrão de perfil incluído!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro reportado com o perfil!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("padraoPerfil.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Perfil</title>

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
			$('#tblPerfil').DataTable( {
				"order": [[ 1, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Perfil
					width: "80%",
					targets: [0]
				},
				{ 
					orderable: true,   //Situação
					width: "5%",
					targets: [1]
				},
				{ 
					orderable: false,   //Ações
					width: "15%",
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
				var inputNomeVelho = $('#inputPerfilNome').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputNome = inputNomeNovo.trim();

				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputNome == ''){
					$('#inputNome').val('');
					$("#formPerfil").submit();
				} else {
				
					//Esse ajax está sendo usado para verificar no banco se o registro já existe
					$.ajax({
						type: "POST",
						url: "perfilValida.php",
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
							
							$( "#formPerfil" ).submit();
						}
					})
				}	
			})	


		});	
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaPerfil(Permission, PerfilId, PerfilNome, PerfilStatus, Tipo){
		
			if (Permission == 1){
				document.getElementById('inputPerfilId').value = PerfilId;
				document.getElementById('inputPerfilNome').value = PerfilNome;
				document.getElementById('inputPerfilStatus').value = PerfilStatus;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formPerfil.action = "padraoPerfil.php";				
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formPerfil, "Tem certeza que deseja excluir esse perfil?", "padraoPerfilExclui.php");
				}else if (Tipo == 'permicao'){
					document.formPerfil.action = "padraoPermissao.php";
				}

				document.formPerfil.submit();
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
								<h5 class="card-title">Relação de Perfis Padrões</h5>
							</div>

							<div class="card-body">
												
								<form name="formPerfil" id="formPerfil" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputPerfilId" name="inputPerfilId" value="<?php if (isset($_POST['inputPerfilId'])) echo $_POST['inputPerfilId']; ?>" >
									<input type="hidden" id="inputPerfilNome" name="inputPerfilNome" value="<?php if (isset($_POST['inputPerfilNome'])) echo $_POST['inputPerfilNome']; ?>" >
									<input type="hidden" id="inputPerfilStatus" name="inputPerfilStatus" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNome">Nome do Perfil <span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Perfil" value="<?php if (isset($_POST['inputPerfilId'])) echo $rowPerfil['PerfiNome']; ?>" required autofocus>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputPerfilId'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="perfil.php" class="btn btn-basic" role="button">Cancelar</a>');
													} else{ //inserindo
														print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
													}

												?>
											</div>
										</div>
									</div>
								</form>
		
							</div>
							
							<table id="tblPerfil" class="table">
								<thead>
									<tr class="bg-slate">
										<th width="75%">Perfil</th>
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
											<td>'.$item['PerfiNome'].'</td>');
										
										print('<td><a href="#" onclick="atualizaPerfil(1,'.$item['PerfiId'].', \''.$item['PerfiNome'].'\',\''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a title="Editar" href="#" onclick="atualizaPerfil('.$atualizar.','.$item['PerfiId'].', \''.$item['PerfiNome'].'\','.$item['PerfiStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7"></i></a>
														<a title="Excluir" href="#" onclick="atualizaPerfil('.$excluir.','.$item['PerfiId'].', \''.$item['PerfiNome'].'\','.$item['PerfiStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin"></i></a>
														<a title="Padrão de Permissões Geral" href="#" onclick="atualizaPerfil(1,'.$item['PerfiId'].', \''.$item['PerfiNome'].'\','.$item['PerfiStatus'].', \'permicao\');" class="list-icons-item"><i class="icon-lock2"></i></a>
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
