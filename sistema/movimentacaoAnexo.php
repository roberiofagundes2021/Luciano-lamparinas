<?php 
	include_once("sessao.php"); 
	include('global_assets/php/conexao.php');

	$_SESSION['PaginaAtual'] = 'Movimentação Anexo';

	//Se veio de movimentacao.php
	if (isset($_POST['inputMovimentacaoId']) && isset($_POST['inputMovimentacaoNotaFiscal'])){
        
		$_SESSION['MovimentacaoIdAnexo'] = $_POST['inputMovimentacaoId'];
		$_SESSION['MovimentacaoNotaFiscal'] = $_POST['inputMovimentacaoNotaFiscal'];
       
    } else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

		if (!isset($_SESSION['MovimentacaoIdAnexo']) && !isset($_SESSION['MovimentacaoNotaFiscal'])){
			irpara("movimentacao.php");			
		}       
    }

	$iMovimentacao = $_SESSION['MovimentacaoIdAnexo'];

	$sql = "SELECT MvAneId, MvAneData, MvAneNome, MvAneArquivo
			FROM   MovimentacaoAnexo	
			WHERE  MvAneUnidade = ". $_SESSION['UnidadeId'] ." AND MvAneMovimentacao = ".$iMovimentacao."
			ORDER BY MvAneNome ASC";
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	$count = count($row);	

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Anexos Notas Fiscais</title>

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
	<script src="global_assets/js/lamparinas/stop-back.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	
	<!-- /theme JS files -->	
	
	<script type="text/javascript">
		
		$(document).ready(function() {
			
			/* Início: Tabela Personalizada */
			$('#tblMovimentacaoAnexo').DataTable( {
				"order": [[ 0, "desc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [{ 
					orderable: true,   //Data
					width: "14%",
					targets: [0]
				},
				{ 
					orderable: true,   //Nome
					width: "38%",
					targets: [1]
				},				
				{ 
					orderable: true,   //Arquivo
					width: "38%",
					targets: [2]
				},
				{ 
					orderable: false,  //Ações
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

				var inputDescricao = $('#inputNome').val();			
				var inputFile = $('#inputArquivo').val();
				var id = $("input:file").attr('id');
				var tamanho =  1024 * 1024 * 32; //32MB

				if (inputDescricao == ''){
					$("#formMovimentacaoAnexo").submit();
					$('#inputNome').focus();
					return false;
				}

				//Verifica se o campo só possui espaços em branco
				if (inputFile == ''){
					alerta('Atenção','Selecione o arquivo!','error');
					$("formMovimentacaoAnexo").submit();
					$('#inputArquivo').focus();
					return false;
				}

				var extensoes = ['pdf', 'PDF', 'doc', 'DOC', 'docx', 'DOCX', 'odt', 'ODT', 'jpg', 'JPG', 'jpeg', 'JPEG', 'png', 'PNG'];

				//Verifica se a extensão é  diferente de PDF, DOC, DOCX, ODT, JPG, JPEG, PNG!
				//if (ext(inputFile) != 'pdf' && ext(inputFile) != 'doc' && ext(inputFile) != 'docx' && ext(inputFile) != 'odt' && ext(inputFile) != 'jpg' && ext(inputFile) != 'jpeg' && ext(inputFile) != 'png'){
				if (extensoes.indexOf(ext(inputFile)) == -1){
					alerta('Atenção','Por favor, envie arquivos com a seguinte extensão: PDF, DOC, DOCX, ODT, JPG, JPEG, PNG!','error');
					//$("#formMovimentacaoAnexo").submit();
					$('#inputArquivo').focus();
					return false;	
				}
				
				//Verifica o tamanho do arquivo
				if ($('#'+id)[0].files[0].size > tamanho){
					alerta('Atenção','O arquivo enviado é muito grande, envie arquivos de até 32MB.','error');
					$("#formMovimentacaoAnexo").submit();
					$('#inputArquivo').focus();
					return false;
				}
				
				document.formMovimentacaoAnexo.action = 'movimentacaoAnexoNovo.php';
				document.formMovimentacaoAnexo.submit();
			});
		});

		//Retorna a extenção do arquivo
		function ext(path) {
			var final = path.substr(path.lastIndexOf('/')+1);
			var separador = final.lastIndexOf('.');
			return separador <= 0 ? '' : final.substr(separador + 1);
		}	
			

		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function removeMovimentacaoAnexo(MvAneId, MvAneNome, MvAneData, MvAneArquivo, Tipo){

			document.getElementById('inputMovimentacaoAnexoID').value = MvAneId;
			document.getElementById('inputMovimentacaoAnexoNome').value = MvAneNome;
			document.getElementById('inputMovimentacaoAnexoData').value = MvAneData;
			document.getElementById('inputMovimentacaoAnexoArquivo').value = MvAneArquivo;	

			if (Tipo == 'exclui'){
					confirmaExclusao(document.formMovimentacaoAnexoExclui, "Tem certeza que deseja excluir esse Anexo", "movimentacaoAnexoExclui.php");
			}
		
			document.formMovimentacaoAnexoExclui.action = 'movimentacaoAnexoNovo.php';
			document.formMovimentacaoAnexoExclui.submit();
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
								<div class="col-lg-12">
									<h3 class="card-title">Nota Fiscal<span style="color: #FF0000; font-weight: bold;"> <?php echo $_SESSION['MovimentacaoNotaFiscal']; ?></span> e demais documentos</h3>
								</div>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-12">	
										<div class="text-right">
											<a href="movimentacao.php" class="btn btn-basic" role="button"><< Movimentação</a>
										</div>
									</div>
								</div>								

								<form name="formMovimentacaoAnexo" id="formMovimentacaoAnexo" method="post" enctype="multipart/form-data" class="form-validate-jquery">
									<div class="row">
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputData">Data</label>
												<input type="text" id="inputData" name="inputData" class="form-control" placeholder="Data" value="<?php echo date('d/m/Y'); ?>"  readOnly>
											</div>
										</div>
										<div class="col-lg-10">
											<div class="form-group">
												<label for="inputNome">Descrição<span class="text-danger"> *</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Descrição" required autofocus>
											</div>
										</div>
									</div>	
									<div class="row">
										<div class="col-lg-12">
											<label for="inputArquivo">Arquivo<span class="text-danger"> *</span></label>
											<input type="file" id="inputArquivo" name="inputArquivo" class="form-control" required>
										</div>
									</div>	
									<div class="row">	
										<div class="col-lg-12">
											<div class="form-group">										
												Obs.: arquivos permitidos (.pdf, .doc, .docx, .odt, .jpg, .jpeg, .png) Tamanho máximo: 32MB
											</div>
										</div>									
									</div>

									<div class="row" style="margin-top: 10px;">
										<div class="col-lg-12">								
											<div class="form-group">
													<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>											
											</div>
										</div>
									</div>

								</form>
							</div>

							
							<table class="table" id="tblMovimentacaoAnexo">
								<thead>
									<tr class="bg-slate">
										<th>Data</th>
										<th>Descrição</th>
										<th>Arquivo</th>										
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>

									<?php foreach ($row as $item){
										print('
											<tr>
												<td>'.mostraData($item['MvAneData']).'</td>

												<td>'.$item['MvAneNome'].'</td>

												<td>
													<a href="global_assets/anexos/movimentacao/'.$item['MvAneArquivo'].'" target="_blank">'.$item['MvAneArquivo'].'</a>
												</td>
												
												<td class="text-center">
													<div class="list-icons">
														<div class="list-icons list-icons-extended">
															<a href="#" onclick="removeMovimentacaoAnexo('.$item['MvAneId'].', \''.$item['MvAneNome'].'\',\''.$item['MvAneData'].'\', \''.$item['MvAneArquivo'].'\', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>	
														</div>
													</div>
												</td>

											</tr>
										');
										}
									?>

								</tbody>
							</table>
						</div>
						<!-- /basic responsive configuration -->
					</div>
				</div>				
				
				<!-- /info blocks -->
				<form name="formMovimentacaoAnexoExclui" method="post">
					<input type="hidden" id="inputMovimentacaoAnexoID" name="inputMovimentacaoAnexoID">
					<input type="hidden" id="inputMovimentacaoAnexoNome" name="inputMovimentacaoAnexoNome">
					<input type="hidden" id="inputMovimentacaoAnexoData" name="inputMovimentacaoAnexoData">
					<input type="hidden" id="inputMovimentacaoAnexoArquivo" name="inputMovimentacaoAnexoArquivo">
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
