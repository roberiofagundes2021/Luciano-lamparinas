<?php 
	include_once("sessao.php"); 
	include('global_assets/php/conexao.php');

	$_SESSION['PaginaAtual'] = 'Dotação Orçamentária';

	if (isset($_POST['inputTRId'])){
		$_SESSION['inputTRIdDotacao'] = $_POST['inputTRId'];
		$_SESSION['inputTRNumero'] = $_POST['inputTRNumero'];

	} else if (isset($_POST['inputTRIdIndex'])) {
		$_SESSION['inputTRIdDotacao'] = $_POST['inputTRIdIndex'];

		$sql = "SELECT TrRefNumero
				FROM TermoReferencia
			    WHERE TrRefUnidade = ". $_SESSION['UnidadeId'] ." AND TrRefId = ".$_SESSION['inputTRIdDotacao']."
		";
		$result = $conn->query($sql);
		$TrID = $result->fetch(PDO::FETCH_ASSOC);

		$_SESSION['inputTRNumero'] = $TrID['TrRefNumero'];
	}
	

	$sql = "SELECT DtOrcId, DtOrcData, DtOrcNome, DtOrcArquivo
			FROM DotacaoOrcamentaria
		    WHERE DtOrcUnidade = ". $_SESSION['UnidadeId'] ." AND DtOrcTermoReferencia = ". $_SESSION['inputTRIdDotacao'] ."
		    ORDER BY DtOrcNome ASC
	";
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
	$count = count($row);

	$sql = "SELECT TrRefId,SituaChave
			FROM TermoReferencia
			JOIN Situacao  ON SituaId = TrRefStatus
			WHERE TrRefId = ". $_SESSION['inputTRIdDotacao'] ."
	 ";
	$result = $conn->query($sql);
	$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Dotação Orçamentária</title>

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
			$('#tblClienteAnexo').DataTable( {
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
					$("#formDotacaoFields").submit();
					$('#inputNome').focus();
					return false;
				}

				//Verifica se o campo só possui espaços em branco
				if (inputFile == ''){
					alerta('Atenção','Selecione o arquivo!','error');
					$("#formDotacaoFields").submit();
					$('#inputArquivo').focus();
					return false;
				}

				var extensoes = ['pdf', 'PDF', 'doc', 'DOC', 'docx', 'DOCX', 'odt', 'ODT', 'jpg', 'JPG', 'jpeg', 'JPEG', 'png', 'PNG'];

				//Verifica se a extensão é  diferente de PDF, DOC, DOCX, ODT, JPG, JPEG, PNG!
				//if (ext(inputFile) != 'pdf' && ext(inputFile) != 'doc' && ext(inputFile) != 'docx' && ext(inputFile) != 'odt' && ext(inputFile) != 'jpg' && ext(inputFile) != 'jpeg' && ext(inputFile) != 'png'){
				if (extensoes.indexOf(ext(inputFile)) == -1){
					alerta('Atenção','Por favor, envie arquivos com a seguinte extensão: PDF, DOC, DOCX, ODT, JPG, JPEG, PNG!','error');
					//$("#formDotacaoFields").submit();
					$('#inputArquivo').focus();
					return false;	
				}
				
				//Verifica o tamanho do arquivo
				if ($('#'+id)[0].files[0].size > tamanho){
					alerta('Atenção','O arquivo enviado é muito grande, envie arquivos de até 32MB.','error');
					$("#formDotacaoFields").submit();
					$('#inputArquivo').focus();
					return false;
				}
				
				document.formDotacaoFields.action = 'trDotacaoNovo.php';
				document.formDotacaoFields.submit();
			});
		});

		//Retorna a extenção do arquivo
		function ext(path) {
			var final = path.substr(path.lastIndexOf('/')+1);
			var separador = final.lastIndexOf('.');
			return separador <= 0 ? '' : final.substr(separador + 1);
		}	
			

		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function removeDotacao(DtOrcId, DtOrcData, DtOrcNome, DtOrcArquivo, Tipo){

			document.getElementById('inputDotacaoID').value = DtOrcId;
			document.getElementById('inputDotacaoNome').value = DtOrcNome;
			document.getElementById('inputDotacaoData').value = DtOrcData;
			document.getElementById('inputDotacaoArquivo').value = DtOrcArquivo;	

			if (Tipo == 'exclui'){
					confirmaExclusao(document.formDotacaoExclui, "Tem certeza que deseja excluir esse Anexo", "trDotacaoExclui.php");
			}
		
			document.formDotacaoExclui.action = 'trDotacaoNovo.php';
			document.formDotacaoExclui.submit();
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
								<h3 class="card-title">Relação de Dotações Orçamentárias</h3>
							</div>

							<div class="card-body">
								<div class="row">
									<div class="col-lg-7">
										A relação abaixo faz referência as Dotações Orçamentárias do Termo de Referência <span style="color: #FF0000; font-weight: bold;"> <?php echo $_SESSION['inputTRNumero']; ?> </span>
									</div>
									<div class="col-lg-5">	
										<div class="text-right">
											<a href="tr.php" class="btn btn-basic" role="button"><< Termo de Referência</a>
										</div>
									</div>
								</div>								
								<?php if ($count <= 0) : ?>
								<?php 
									if ($rowSituacao['SituaChave'] != 'FASEINTERNAFINALIZADA'){
										print('<form name="formDotacaoFields" id="formDotacaoFields" method="post" enctype="multipart/form-data" class="form-validate-jquery">
											<div class="row">
												<div class="col-lg-2">
													<div class="form-group">
														<label for="inputData">Data</label>
														<input type="text" id="inputData" name="inputData" class="form-control" placeholder="Data" value="'); echo date('d/m/Y');  print('"  readOnly>
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

										</form>');
									}
								?>
								<?php endif; ?>	
							</div>

							
							<table class="table" id="tblClienteAnexo">
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
												<td>'.mostraData($item['DtOrcData']).'</td>

												<td>'.$item['DtOrcNome'].'</td>

												<td>
													<a href="global_assets/anexos/dotacaoOrcamentaria/'.$item['DtOrcArquivo'].'" target="_blank">'.$item['DtOrcArquivo'].'</a>
												</td>
												
												<td class="text-center">
													<div class="list-icons">
														<div class="list-icons list-icons-extended">');														
														if ($rowSituacao['SituaChave'] != 'FASEINTERNAFINALIZADA'){
															print('<a href="#" onclick="removeDotacao('.$item['DtOrcId'].', \''.$item['DtOrcData'].'\',\''.$item['DtOrcNome'].'\', \''.$item['DtOrcArquivo'].'\', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>');
														}														
													print('</div>
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
				<form name="formDotacaoExclui" method="post">
					<input type="hidden" id="inputDotacaoID" name="inputDotacaoID">
					<input type="hidden" id="inputDotacaoData" name="inputDotacaoData">
					<input type="hidden" id="inputDotacaoNome" name="inputDotacaoNome">
					<input type="hidden" id="inputDotacaoArquivo" name="inputDotacaoArquivo">
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
