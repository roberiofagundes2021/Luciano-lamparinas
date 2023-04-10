<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Comissão do Processo Licitatório';

include('global_assets/php/conexao.php');

if (!isset($_SESSION['TRId'])){	
	$_SESSION['TRId'] 	  = $_POST['inputTRId'];
	$_SESSION['TRNumero'] = $_POST['inputTRNumero'];
}

/* Retorna a situação do TR */
$sql = "SELECT TrRefId, SituaChave
		FROM TermoReferencia
		JOIN Situacao  ON SituaId = TrRefStatus
		WHERE TrRefId = " .$_SESSION['TRId'];
$result = $conn->query($sql);
$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);

/* Retorna os anexos da comissão */
$sql = "SELECT TRXCoId, TRXCoData, TRXCoNome, TRXCoArquivo
		FROM TRXComissao
		WHERE TRXCoUnidade = ". $_SESSION['UnidadeId'] ." AND TRXCoTermoReferencia = ".$_SESSION['TRId']."	
		ORDER BY TRXCoNome ASC";
$result = $conn->query($sql);
$rowAnexo = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

/* Retorna os membros da equipe */
$sql = "SELECT TRXEqTermoReferencia, TRXEqUsuario, TRXEqPresidente, TRXEqUnidade, UsuarLogin
		FROM TRXEquipe
		JOIN Usuario  ON UsuarId = TRXEqUsuario
	    WHERE TRXEqUnidade = ". $_SESSION['UnidadeId'] ."  AND TRXEqTermoReferencia = ".$_SESSION['TRId']."
	    ORDER BY UsuarLogin ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//Grava a equipe e presidente
if(isset($_POST['cmbUsuario'])){
	try{
		$sql = " SELECT COUNT(TRXEqUsuario) as count
				 FROM TRXEquipe
			     WHERE TRXEqTermoReferencia = ".$_POST['inputTRId']." AND TRXEqUnidade 		= ".$_SESSION['UnidadeId']." AND TRXEqPresidente 	= 1
		";
		$result = $conn->query($sql);
		$rowTRXEquipe = $result->fetch(PDO::FETCH_ASSOC);
		$count = $rowTRXEquipe['count'];

		$sql = "INSERT INTO TRXEquipe (TRXEqTermoReferencia, TRXEqUsuario, TRXEqPresidente, TRXEqUnidade)
			    VALUES (:iTermoReferencia, :iUsuario, :iPresidente,:iTRXEqUnidade )
		";
		$result = $conn->prepare($sql);

		if($count <= 0 ) {
			$result->execute(array(
				':iTermoReferencia' => $_POST['inputTRId'],
				':iUsuario' => $_POST['cmbUsuario'],
				':iPresidente' => true,
				':iTRXEqUnidade' => $_SESSION['UnidadeId'],
			));
		} else {
			$result->execute(array(
				':iTermoReferencia' => $_POST['inputTRId'],
				':iUsuario' => $_POST['cmbUsuario'],
				':iPresidente' => false,
				':iTRXEqUnidade' => $_SESSION['UnidadeId'],
			));
		}

		$sql = " SELECT UsuarNome
				 FROM Usuario
			     WHERE UsuarId = ".$_POST ['cmbUsuario']." ";
		$result = $conn->query($sql);
		$rowUsuario = $result->fetch(PDO::FETCH_ASSOC);
		
		$sql = "INSERT INTO AuditTR ( AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento)
				VALUES (:iTRTermoReferencia, :iTRDataHora, :iTRUsuario, :iTRTela, :iTRDetalhamento)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
				':iTRTermoReferencia' => $_POST['inputTRId'],
				':iTRDataHora' => date("Y-m-d H:i:s"),
				':iTRUsuario' => $_SESSION['UsuarId'],
				':iTRTela' =>'COMISSÃO DO PROCESSO LICITATÓRIO',
				':iTRDetalhamento' =>' NOVO MEMBRO '. $rowUsuario['UsuarNome']. ''
			));

		
		$_SESSION['msg']['titulo'] 		= "Sucesso";
		$_SESSION['msg']['mensagem'] 	= "Membro incluído!!!";
		$_SESSION['msg']['tipo'] 			= "success";
		
	} catch(PDOException $e) {
		$_SESSION['msg']['titulo'] 		= "Erro";
		$_SESSION['msg']['mensagem'] 	= "Erro ao incluir o Membro!!!";
		$_SESSION['msg']['tipo'] 			= "error";	
		echo 'Error: ' . $e->getMessage();
		die;
	}
	
	irpara("trComissao.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Comissão do Processo Licitatório</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>
	<script src="global_assets/js/lamparinas/stop-back.js"></script>
	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>	
	
	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

	<!-- /theme JS files -->	
	
	<script type="text/javascript">

		$(document).ready(function (){	

			//Valida Registro Duplicado
			$('#adicionar').on('click', function(e) {

				e.preventDefault();

				var cmbUsuario = $('#cmbUsuario').val();
				var inputTRId = $('#inputTRId').val();

				//remove os espaços desnecessários antes e depois
				cmbUsuarioNovo = cmbUsuario.trim();

				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				$.ajax({
					type: "POST",
					url: "trComissaoValida.php",
					data: ('usuario=' + cmbUsuario + '&TRId=' + inputTRId  ),
					success: function(resposta) {

						if (resposta == 1) {
							alerta('Atenção', 'Esse registro já existe!', 'error');
							return false;
						}

						$("#formComissao").submit();
					}
				})
			})

             
			$('#tblComissao').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   //Membro
					width: "70%",
					targets: [0]
				},
				{ 
					orderable: false,   //Presidente
					width: "20%",
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

			$('#tblComissaoAnexo').DataTable( {
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

				inputDescricao = inputDescricao.trim();

				if (inputDescricao == ''){
					$('#inputNome').val('');
					$("#formAnexoComissao").submit();
					$('#inputNome').focus();
					return false;
				}

				//Verifica se o campo só possui espaços em branco
				if (inputFile == ''){
					alerta('Atenção','Selecione o arquivo!','error');
					$("#formAnexoComissao").submit();
					$('#inputArquivo').focus();
					return false;
				}
								
				//Verifica se a extensão é  diferente de PDF, DOC, DOCX, ODT, JPG, JPEG, PNG!
				if (ext(inputFile) != 'pdf' && ext(inputFile) != 'doc' && ext(inputFile) != 'docx' && ext(inputFile) != 'odt' && ext(inputFile) != 'jpg' && ext(inputFile) != 'jpeg' && ext(inputFile) != 'png'){
					alerta('Atenção','Por favor, envie arquivos com a seguinte extensão: PDF, DOC, DOCX, ODT, JPG, JPEG, PNG!','error');
					$('#inputArquivo').val('');
					$("#formAnexoComissao").submit();
					$('#inputArquivo').focus();
					return false;	
				}
				
				//Verifica o tamanho do arquivo
				if ($('#'+id)[0].files[0].size > tamanho){
					alerta('Atenção','O arquivo enviado é muito grande, envie arquivos de até 32MB.','error');
					$('#inputArquivo').val('');
					$("#formAnexoComissao").submit();
					$('#inputArquivo').focus();
					return false;
				}
				
				document.formAnexoComissao.action = 'trComissaoAnexoNovo.php';
				document.formAnexoComissao.submit();
			});

		});

		//Retorna a extenção do arquivo
		function ext(path) {
			var final = path.substr(path.lastIndexOf('/')+1);
			var separador = final.lastIndexOf('.');
			return separador <= 0 ? '' : final.substr(separador + 1);
		}	

		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function removeComissaoAnexo(TRXCoId, TRXCoNome, TRXCoData, TRXCoArquivo, Tipo){

			document.getElementById('inputComissaoAnexoID').value = TRXCoId;
			document.getElementById('inputComissaoAnexoNome').value = TRXCoNome;
			document.getElementById('inputComissaoAnexoData').value = TRXCoData;
			document.getElementById('inputComissaoAnexoArquivo').value = TRXCoArquivo;	

			if (Tipo == 'exclui'){
					confirmaExclusao(document.formComissaoAnexoExclui, "Tem certeza que deseja excluir esse Anexo", "trComissaoAnexoExclui.php");
			}

			document.formComissaoAnexoExclui.action = 'trComissaoAnexoNovo.php';
			document.formComissaoAnexoExclui.submit();
		}	
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaComissao(TRXEqTermoReferencia, TRXEqUsuario, Tipo){
			document.getElementById('inputTRId').value = TRXEqTermoReferencia;
			document.getElementById('inputUsuarioId').value = TRXEqUsuario;
						
			if (Tipo == 'exclui'){
				confirmaExclusao(document.formComissao, "Tem certeza que deseja excluir essa comissão?", "trComissaoExclui.php");
			}
			
			document.formComissao.submit();
		}	

		const updatePresident = (e, referenceTermId, userId, isPresident , unitId, tRefStatus) => {
			e.preventDefault();

			$(this).prop('checked', false);
			$('#inputReferenceTermId').val(referenceTermId);
			$('#inputUserId').val(userId);
			$('#inputIsPresident').val(isPresident);
			$('#inputUnitId').val(unitId);
			$('#inputTRefStatus').val(tRefStatus);

			if (tRefStatus === 'FASEINTERNAFINALIZADA') {
                alerta('Esse Termo de Referência já está finalizado e não pode trocar o presidente da comissão!','');
            } else {
				confirmaExclusao(document.formUpdatePresident, "Essa ação irá trocar o presidente da comissão. Tem certeza disso?", "trComissaoPresidente.php");
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
				<form name="formComissao" id="formComissao" method="post">
					<input type="hidden" id="inputTRId" name="inputTRId" value="<?php echo $_SESSION['TRId']; ?>">
					<input type="hidden" id="inputTRNumero" name="inputTRNumero" value="<?php echo $_SESSION['TRNumero']; ?>">
					<input type="hidden" id="inputUsuarioId" name="inputUsuarioId" >

					<!-- Info blocks -->		
					<div class="row">
						<div class="col-lg-12">
							<!-- Basic responsive configuration -->
							<div class="card">
								<div class="card-header header-elements-inline">
									<h3 class="card-title">Relação da Comissão do Processo Licitatório</h3>
								</div>
								<div class="card-body">
									<div class="row">
										<div class="col-lg-9">
											A relação abaixo faz referência a Comissão do Processo Licitatório do <span style="color: #FF0000; font-weight: bold;">TR nº <?php echo $_SESSION['TRNumero']; ?></span> da unidade <b><?php echo $_SESSION['UnidadeNome']; ?></b>	
										</div>	
										<div class="col-lg-3">	
											<div class="text-right">
												<a href="tr.php" class="btn btn-basic" role="button"><< Termo de Referência</a>
											</div>
										</div>											
									</div>
									<br>
									<?php if ($_SESSION['PerfiChave']==strtoupper('ADMINISTRADOR') || $_SESSION['PerfiChave']==strtoupper('CENTROADMINISTRATIVO') || $_SESSION['PerfiChave']==strtoupper('CONTROLADORIA')) : ?>
										<?php 
										if ($rowSituacao['SituaChave'] != 'FASEINTERNAFINALIZADA'){
										print('<div class="row">
											<div class="col-lg-6">
												<div class="form-group">
													<label for="cmbUsuario"> Membro<span class="text-danger">
															*</span></label>
													<select id="cmbUsuario" name="cmbUsuario" class="form-control select">
													<option value="">Selecione</option>');
														
														$sql = "SELECT UsuarId, UsuarLogin
																FROM Usuario
																JOIN EmpresaXUsuarioXPerfil ON EXUXPUsuario = UsuarId
																JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
																JOIN Situacao on SituaId = EXUXPStatus
																WHERE UsXUnUnidade = " . $_SESSION['UnidadeId'] . " and SituaChave = 'ATIVO'
																ORDER BY UsuarLogin ASC";
														$result = $conn->query($sql);
														$rowEquipe = $result->fetchAll(PDO::FETCH_ASSOC);

														foreach ($rowEquipe as $item) {
															print('<option value="' . $item['UsuarId'] . '">' . $item['UsuarLogin'] . '</option>');
														}
														
													print('</select>
												</div>
											</div>
											<div class="col-lg-3">												
												<button class="btn btn-lg btn-principal" style="margin-top: 25px;" id="adicionar">Adicionar</button>
											</div>
										</div>');
										}
										?>
									<?php endif; ?>		
								</div>	

								<table id="tblComissao" class="table">
									<thead>
										<tr class="bg-slate">
											<th>Membro</th>
											<th class="text-center">Presidente</th>
											<th class="text-center">Ações</th>
										</tr>
									</thead>
									<tbody>
									<?php
										foreach ($row as $item){
											$isPresident = $item['TRXEqPresidente'] == 1 ? 1 : 0;
											$checked = 	$isPresident == 1 ? 'checked' : '';
											
											print('
											<tr>
												<td>'.$item['UsuarLogin'].'</td>
												<td class="text-center">
													<input type="checkbox" name="atualizaPresidente" id="atualizaPresidente" '."$checked".' onclick="updatePresident(event,'.$item['TRXEqTermoReferencia'].','.$item['TRXEqUsuario'].','.$isPresident.','.$item['TRXEqUnidade'].', \''.$rowSituacao['SituaChave'].'\')">
												</td>
												');
											
											print('<td class="text-center">
													<div class="list-icons">
														<div class="list-icons list-icons-extended">');
														if ($rowSituacao['SituaChave'] != 'FASEINTERNAFINALIZADA'){
															
															if($_SESSION['PerfiChave']==strtoupper('ADMINISTRADOR') || $_SESSION['PerfiChave']==strtoupper('CENTROADMINISTRATIVO') || $_SESSION['PerfiChave']==strtoupper('CONTROLADORIA')){
																print('<a href="#" onclick="atualizaComissao('.$item['TRXEqTermoReferencia'].', '.$item['TRXEqUsuario'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>');	
															}
													    }
												print('</div>
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
				</form>

				<!-- Info blocks -->		
				<div class="row">
					<div class="col-lg-12">
						<!-- Basic responsive configuration -->
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title">Anexos Comissão</h3>
								<div class="header-elements">
								</div>
							</div>

							<div class="card-body">							

								<?php if ($_SESSION['PerfiChave']==strtoupper('ADMINISTRADOR') || $_SESSION['PerfiChave']==strtoupper('CENTROADMINISTRATIVO') || $_SESSION['PerfiChave']==strtoupper('CONTROLADORIA')) : ?>							
									<?php 
									if ($rowSituacao['SituaChave'] != 'FASEINTERNAFINALIZADA'){
									print('<form name="formAnexoComissao" id="formAnexoComissao" method="post" enctype="multipart/form-data" class="form-validate-jquery">
									
										<div class="row">
											<div class="col-lg-2">
												<div class="form-group">
													<label for="inputData">Data</label>
													<input type="text" id="inputData" name="inputData" class="form-control" placeholder="Data" value="'); echo date('d/m/Y');  print('"   readOnly>
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

							
							<table class="table" id="tblComissaoAnexo">
								<thead>
									<tr class="bg-slate">
										<th>Data</th>
										<th>Descrição</th>
										<th>Arquivo</th>										
										<th class="text-center">Ações</th>
									</tr>
								</thead>
								<tbody>

									<?php foreach ($rowAnexo as $item){
										print('
											<tr>
												<td>'.mostraData($item['TRXCoData']).'</td>

												<td>'.$item['TRXCoNome'].'</td>

												<td>
													<a href="global_assets/anexos/comissao/'.$item['TRXCoArquivo'].'" target="_blank">'.$item['TRXCoArquivo'].'</a>
												</td>
												
												<td class="text-center">
													<div class="list-icons">
														<div class="list-icons list-icons-extended">');														
															if ($rowSituacao['SituaChave'] != 'FASEINTERNAFINALIZADA'){
																if($_SESSION['PerfiChave']==strtoupper('ADMINISTRADOR') || $_SESSION['PerfiChave']==strtoupper('CENTROADMINISTRATIVO') || $_SESSION['PerfiChave']==strtoupper('CONTROLADORIA')){
																	print('<a href="#" onclick="removeComissaoAnexo('.$item['TRXCoId'].', \''.$item['TRXCoData'].'\',\''.$item['TRXCoNome'].'\', \''.$item['TRXCoArquivo'].'\', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>');
															    }
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

				<form name="formUpdatePresident" id='formUpdatePresident' method="post">
					<input type="hidden" id="inputReferenceTermId" name="inputReferenceTermId">
					<input type="hidden" id="inputUserId" name="inputUserId">
					<input type="hidden" id="inputIsPresident" name="inputIsPresident">
					<input type="hidden" id="inputUnitId" name="inputUnitId">
					<input type="hidden" id="inputTRefStatus" name="inputTRefStatus">
					
				</form>

				<form name="formComissaoAnexoExclui" method="post">
					<input type="hidden" id="inputComissaoAnexoID" name="inputComissaoAnexoID">
					<input type="hidden" id="inputComissaoAnexoData" name="inputComissaoAnexoData">
					<input type="hidden" id="inputComissaoAnexoNome" name="inputComissaoAnexoNome">
					<input type="hidden" id="inputComissaoAnexoArquivo" name="inputComissaoAnexoArquivo">
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
