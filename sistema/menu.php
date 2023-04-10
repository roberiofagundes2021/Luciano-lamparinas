<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Menu';

include('global_assets/php/conexao.php');

if (isset($_POST['inputEmpresaId'])){
	$_SESSION['EmpresaId'] = $_POST['inputEmpresaId'];
	$_SESSION['EmpresaNome'] = $_POST['inputEmpresaNome'];
}

if (!isset($_SESSION['EmpresaId'])) {
	irpara("empresa.php");
}

$sql = "SELECT MenuId, MenuNome, ModulNome, MenuPai, MenuLevel, MenuHome, MenuIco, MenuStatus
		FROM Menu
		JOIN Modulo on ModulId = MenuModulo
	    WHERE MenuEmpresa = ". $_SESSION['EmpresaId'] ."
		ORDER BY MenuNome ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
$count = count($row);
//var_dump($count);die;

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Menu</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>	

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>		
	<!-- /theme JS files -->	
	
	<script>
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaMenu(MenuId, MenuNome, MenuStatus, Tipo){
		
			document.getElementById('inputMenuId').value = MenuId;
			document.getElementById('inputMenuNome').value = MenuNome;
			document.getElementById('inputMenuStatus').value = MenuStatus;
					
			if (Tipo == 'edita'){	
				document.formMenu.action = "menuEdita.php";		
			} else if (Tipo == 'exclui'){
				confirmaExclusao(document.formMenu, "Tem certeza que deseja excluir essa menu?", "menuExclui.php");
			} else if (Tipo == 'mudaStatus'){
				document.formMenu.action = "menuMudaSituacao.php";
			} else if (Tipo == 'imprime'){
				document.formMenu.action = "menuImprime.php";
				document.formMenu.setAttribute("target", "_blank");
			}
			
			document.formMenu.submit();
		}		
			
	</script>

</head>

<body class="navbar-top sidebar-xs">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php include_once("menu-left.php"); ?>
		
		<?php include_once("menuLeftSecundario.php"); ?>

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
								<h3 class="card-title">Relação de Menus</h3>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
										<a href="menu.php" class="list-icons-item" data-action="reload"></a>
										<!--<a class="list-icons-item" data-action="remove"></a>-->
									</div>
								</div>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-9">
										<p class="font-size-lg">A relação abaixo faz referência às menus da empresa <b><?php echo $_SESSION['EmpresaNome']; ?></b></p>
									</div>	
									<div class="col-lg-3">	
										<div class="text-right"><a href="menuNovo.php" class="btn btn-principal" role="button">Novo Item de Menu</a></div>
									</div>
								</div>
							</div>				
							
							<!-- A table só filtra se colocar 6 colunas. Onde mudar isso? -->
							<table class="table datatable-responsive">
								<thead>
									<tr class="bg-slate">
										<th>Menu</th>
										<th>Módulo</th>
										<th>Ícone</th>
										<th>Home</th>
										<th>Situação</th>
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach ($row as $item){
										
										$home = $item['MenuHome'] ? 'Sim' : 'Não';
										
										$situacao = $item['MenuStatus'] ? 'Ativo' : 'Inativo';
										$situacaoClasse = $item['MenuStatus'] ? 'badge-success' : 'badge-secondary';
										
										print('
										<tr>
											<td>'.$item['MenuNome'].'</td>
											<td>'.$item['ModulNome'].'</td>
											<td><i class="'.$item['MenuIco'].'"></i></td>
											<td>'.$home.'</td>
											');
										
										print('<td><a href="#" onclick="atualizaMenu('.$item['MenuId'].', \''.$item['MenuNome'].'\','.$item['MenuStatus'].', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaMenu('.$item['MenuId'].', \''.$item['MenuNome'].'\','.$item['MenuStatus'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaMenu('.$item['MenuId'].', \''.$item['MenuNome'].'\','.$item['MenuStatus'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
				
				<form name="formMenu" method="post" action="cEdita.php">
					<input type="hidden" id="inputMenuId" name="inputMenuId" >
					<input type="hidden" id="inputMenuNome" name="inputMenuNome" >
					<input type="hidden" id="inputMenuStatus" name="inputMenuStatus" >
				</form>

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
