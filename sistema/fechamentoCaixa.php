<?php 

include('global_assets/php/conexao.php');
include_once("sessao.php"); 
$_SESSION['PaginaAtual'] = 'Fechamento de Caixa';

$iUnidade = $_SESSION['UnidadeId'];

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Fechamento de Caixa</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files --> 
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>

	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>	

	<!-- Plugin para corrigir a ordenação por data. Caso a URL dê problema algum dia, salvei esses 2 arquivos na pasta global_assets/js/lamparinas -->
	<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.10/sorting/datetime-moment.js"></script>		
	
	<!-- /theme JS files -->	
	
	<script type="text/javascript">
		$(document).ready(function() {

			$('#tblCaixa').hide();
			
			$('#filtrar').on('click', function(e){
				e.preventDefault()
				let inputDataInicio = $('#inputDataInicio').val()?$('#inputDataInicio').val():null
				let inputDataFim = $('#inputDataFim').val()?$('#inputDataFim').val():null
				let cmbOperadores = $('#cmbOperadores').val()?$('#cmbOperadores').val():null
				let cmbCaixas = $('#cmbCaixas').val()?$('#cmbCaixas').val():null

				$.ajax({
					type: 'POST',
					url: 'filtraFechamentoCaixa.php',
					dataType: 'json',
					data:{
						'tipoRequest': 'CAIXA',
						'inicio': inputDataInicio,
						'fim': inputDataFim,
						'operador': cmbOperadores,
						'caixa': cmbCaixas
					},
					success: async function(response) {
						$('#contentCaixas').html('')
						let HTML = ''

						if(response.length){
							await response.forEach(item => {
								HTML += `
								<tr class='servicoItem'>
									<td class="text-left">${item.fechamento}</td>
									<td class="text-left">${item.caixa}</td>
									<td class="text-left">${item.operador}</td>
									<td class="text-right">R$ ${float2moeda(item.saldo)}</td>
									<td class="text-center">${item.acao}</td>
								</tr>`
							})
							$('#contentCaixas').html(HTML)
	
							$('.btnImprimir').each(function(index, element){
								$(element).on('click', function(event){
									event.preventDefault()
									$('#idCaixaAbertura').val($(element).data('caixa'))
									$('#formCaixa').submit()
								})
							})
							$('#tblCaixa').show()
						} else {
							$('#contentCaixas').html('<tr class="odd text-center"><td valign="top" colspan="7" class="dataTables_empty">Sem resultados...</td></tr>')
							$('#tblCaixa').show()
						}
					}
				});
			})
		});

		function imprimir(id){
			console.log(id)
		}
	</script>

</head>

<body class="navbar-top sidebar-xs">

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
								<h3 class="card-title">Fechamento de Caixa</h3>
							</div>

							<div class="card-body">
								<form name="formFluxoOperacional" method="post">
									<div class="row">
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputDataInicio">Data Início <span class="text-danger">*</span></label>
												<div class="input-group">
													<span class="input-group-prepend">
														<span class="input-group-text"><i class="icon-calendar22"></i></span>
													</span>

													<?php $dataInicio = date("Y-m-d",strtotime("-3 days")); ?>
													<input 
														type="date" 
														id="inputDataInicio"
														name="inputDataInicio" 
														min="1800-01-01"
														max="2100-12-31" 
														class="form-control"
														placeholder="Data Início"
														value="<?php echo $dataInicio ?>" 
													>
													
												</div>
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputDataFim">Data Fim <span class="text-danger">*</span></label>
												<div class="input-group">
													<span class="input-group-prepend">
														<span class="input-group-text"><i class="icon-calendar22"></i></span>
													</span>
													<?php $dataFim = date("Y-m-d"); ?>
													<input
														type="date" 
														id="inputDataFim" 
														name="inputDataFim"  
														min="1800-01-01" 
														max="2100-12-31" 
														class="form-control" 
														placeholder="Data Fim"
														value="<?php echo $dataFim ?>"
													>
												</div>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group" >
												<label for="cmbOperadores">Operador</label>
												<select id="cmbOperadores" name="cmbOperadores" class="form-control multiselect-select-all-filtering" multiple="multiple" data-fouc>
													<?php
														$sql = "SELECT UsuarId, UsuarNome
																FROM Usuario
																JOIN EmpresaXUsuarioXPerfil ON EXUXPUsuario = UsuarId
																JOIN UsuarioXUnidade ON UsXUnEmpresaUsuarioPerfil = EXUXPId
																WHERE UsXUnUnidade = $iUnidade and UsXUnOperadorCaixa = 1";
														$result = $conn->query($sql);
														$rowOperadores = $result->fetchAll(PDO::FETCH_ASSOC);

														foreach ($rowOperadores as $item) {
															print("<option value='$item[UsuarId]'>$item[UsuarNome]</option>");
														}
													?>
												</select>
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group" >
												<label for="cmbCaixas">Caixa</label>
												<select id="cmbCaixas" name="cmbCaixas" class="form-control multiselect-select-all-filtering" multiple="multiple" data-fouc>
													<?php
														$sql = "SELECT CxAbeId, CxAbeCaixa
																FROM CaixaAbertura
																WHERE CxAbeUnidade = $iUnidade";
														$result = $conn->query($sql);
														$rowCaixas = $result->fetchAll(PDO::FETCH_ASSOC);

														foreach ($rowCaixas as $item) {
															print("<option value='$item[CxAbeId]'>CAIXA - $item[CxAbeCaixa]</option>");
														}
													?>
												</select>
											</div>
										</div>

										<div class="text-left col-lg-1 pt-3">
											<button id="filtrar" class="btn btn-principal" style='margin-left:1rem'><i class="fas fa-search"></i></button>
										</div>
									</div>
								</form>
							</div>

							<div>
								<table id="tblCaixa" class="table">
									<thead>
										<tr class="bg-slate text-center">
											<th class="text-left">Data do Fechamento</th>
											<th class="text-left">Caixa</th>
											<th class="text-left">Operador</th>
											<th class="text-right">Saldo Final</th>
											<th class="text-center">Ações</th>
										</tr>
									</thead>
									<tbody id="contentCaixas">
										
									</tbody>
								</table>
							</div>

							<form id="formCaixa" method="POST" action="caixaFechamentoImprimeRelatorio.php">
								<input id="idCaixaAbertura" name="idCaixaAbertura" type="hidden" value="" />
							</form>

						<!-- FIM DO CARD -->	
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
