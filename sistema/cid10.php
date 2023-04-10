<?php 

include_once("sessao.php");

if(!$_SESSION['PerfiChave'] == "SUPER"){
	header("location:javascript://history.go(-1)");
}

$_SESSION['PaginaAtual'] = 'Cid-10';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT Cid10Id, Cid10Capitulo, Cid10Codigo, Cid10Descricao, Cid10Status, SituaNome, SituaChave, SituaCor
		FROM Cid10
		JOIN Situacao on SituaId = Cid10Status
		ORDER BY Cid10Capitulo ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//Se estiver editando
if(isset($_POST['inputCid10Id']) && $_POST['inputCid10Id']){

	//Essa consulta é para preencher o campo Codigo com o Cid10 a ser editar
	$sql = "SELECT Cid10Id, Cid10Capitulo, Cid10Codigo, Cid10Descricao
			FROM Cid10
			WHERE Cid10Id = " . $_POST['inputCid10Id'];
	$result = $conn->query($sql);
	$rowCid10 = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE Cid10 SET Cid10Capitulo = :sCapitulo, Cid10Codigo = :sCodigo, Cid10Descricao = :sDescricao, Cid10UsuarioAtualizador = :iUsuarioAtualizador
					WHERE Cid10Id = :iCid10";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sCapitulo' => $_POST['cmbCapitulo'],
							':sCodigo' => $_POST['inputCodigo'],
                            ':sDescricao' => $_POST['inputDescricao'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iCid10' => $_POST['inputCid10Id']
							));
	
			$_SESSION['msg']['mensagem'] = "Cid10 alterado!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO Cid10 ( Cid10Capitulo, Cid10Codigo, Cid10Descricao, Cid10Status, Cid10UsuarioAtualizador)
					VALUES ( :sCapitulo, :sCodigo, :sDescricao, :bStatus, :iUsuarioAtualizador)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sCapitulo' => $_POST['cmbCapitulo'],
							':sCodigo' => $_POST['inputCodigo'],
                            ':sDescricao' => $_POST['inputDescricao'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							));
	
			$_SESSION['msg']['mensagem'] = "Cid-10 incluída!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao atualizar Cid-10!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("cid10.php");
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Cid10</title>

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
			$('#tblCid10').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{
					orderable: true,   // Capítulo
					width: "20%",
					targets: [0]
				},
				{ 
					orderable: true,   //Código
					width: "20%",
					targets: [1]
				},
                { 
					orderable: true,   //Descrição
					width: "40%",
					targets: [2]
				},
				{ 
					orderable: true,   //Situação
					width: "10%",
					targets: [3]
				},
				{ 
					orderable: false,   //Ações
					width: "10%",
					targets: [4]
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
				var cmbCapitulo = $('#cmbCapitulo').val();
				var inputCodigoNovo = $('#inputCodigo').val();
				var inputCodigoVelho = $('#inputCid10Codigo').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputCodigo = inputCodigoNovo.trim();
				cmbCapitulo = cmbCapitulo.trim();
				
				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputCodigo == '' || cmbCapitulo == ''){
					
					if (inputCodigo == ''){
						$('#inputCodigo').val('');
					}

					if (cmbCapitulo == ''){
						$('#cmbCapitulo').val('');
					}

					$("#formCid10").submit();
				} else {

					//Esse ajax está sendo usado para verificar no cid10 se o registro já existe
					$.ajax({
						type: "POST",
						url: "cid10Valida.php",
						data: ('codigoNovo='+inputCodigo+'&codigoVelho='+inputCodigoVelho+'&estadoAtual='+inputEstadoAtual),
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
							
							$( "#formCid10" ).submit();
						}
					})
				}					
			})
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaCid10(Permission, Cid10Id, Cid10Codigo, Cid10Status, Tipo){

			if (Permission == 1){
				document.getElementById('inputCid10Id').value = Cid10Id;
				document.getElementById('inputCid10Codigo').value = Cid10Codigo;
				document.getElementById('inputCid10Status').value = Cid10Status;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formCid10.action = "cid10.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formCid10, "Tem certeza que deseja excluir essa Cid-10?", "cid10Exclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formCid10.action = "cid10MudaSituacao.php";
				}
				
				document.formCid10.submit();
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
								<h3 class="card-title">Relação da Cid-10</h3>
							</div>

							<div class="card-body">
								<form name="formCid10" id="formCid10" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputCid10Id" name="inputCid10Id" value="<?php if (isset($_POST['inputCid10Id'])) echo $_POST['inputCid10Id']; ?>" >
									<input type="hidden" id="inputCid10Codigo" name="inputCid10Codigo" value="<?php if (isset($_POST['inputCid10Codigo'])) echo $_POST['inputCid10Codigo']; ?>" >
									<input type="hidden" id="inputCid10Status" name="inputCid10Status" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
									<div class="col-lg-2">
											<div class="form-group">
												<label for="cmbCapitulo">Capítulo<span class="text-danger"> *</span></label>
												<select id="cmbCapitulo" name="cmbCapitulo" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<option value="I" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'I') echo "selected"; }?> >I</option>
													<option value="II" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'II') echo "selected"; }?> >II</option>
													<option value="III" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'III') echo "selected"; }?> >III</option>
													<option value="IV" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'IV') echo "selected"; }?> >IV</option>
													<option value="V" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'V') echo "selected"; }?> >V</option>
													<option value="VI" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'VI') echo "selected"; }?> >VI</option>
													<option value="VII" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'VII') echo "selected"; }?> >VII</option>
													<option value="VIII" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'VIII') echo "selected"; }?> >VIII</option>
													<option value="IX" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'IX') echo "selected"; }?> >IX</option>
													<option value="X" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'X') echo "selected"; }?> >X</option>
													<option value="XI" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'XI') echo "selected"; }?> >XI</option>
													<option value="XII" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'XII') echo "selected"; }?> >XII</option>
													<option value="XIII" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'XIII') echo "selected"; }?> >XIII</option>
													<option value="XIV" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'XIV') echo "selected"; }?> >XIV</option>
													<option value="XV" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'XV') echo "selected"; }?> >XV</option>
													<option value="XVI" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'XVI') echo "selected"; }?> >XVI</option>
													<option value="XVII" <?php if (isset($_POST['inputCid10Id'])) { if($rowCid10['Cid10Capitulo'] == 'XVII') echo "selected"; }?> >XVII</option>
													<option value="XVIII" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'XVIII') echo "selected"; }?> >XVIII</option>
													<option value="XIX" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'XIX') echo "selected"; }?> >XIX</option>
													<option value="XX" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'XX') echo "selected"; }?> >XX</option>
													<option value="XXI" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'XXI') echo "selected"; }?> >XXI</option>
													<option value="XXII" <?php if (isset($_POST['inputCid10Id'])) { if ($rowCid10['Cid10Capitulo'] == 'XXII') echo "selected"; }?> >XXII</option>
												</select>
											</div>
										</div>
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputCodigo">Código <span class="text-danger"> *</span></label>
												<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" value="<?php if (isset($_POST['inputCid10Id'])) echo $rowCid10['Cid10Codigo']; ?>" required >
											</div>
										</div>
                                        <div class="col-lg-5">
											<div class="form-group">
												<label for="inputDescricao">Descrição <span class="text-danger"> *</span></label>
												<input type="text" id="inputDescricao" name="inputDescricao" class="form-control" placeholder="Descrição" value="<?php if (isset($_POST['inputCid10Id'])) echo $rowCid10['Cid10Descricao']; ?>" required >
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputCid10Id'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="cid10.php" class="btn btn-basic" role="button">Cancelar</a>');
													} else{ //inserindo
														print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
													}

												?>
											</div>
										</div>
									</div>
								</form>
							</div>
							
							<table id="tblCid10" class="table">
								<thead>
									<tr class="bg-slate">
										<th>Capítulo</th>
										<th>Código</th>
                                        <th>Descrição</th>
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
											<td>'.$item['Cid10Capitulo'].'</td>
											<td>'.$item['Cid10Codigo'].'</td>
                                            <td>'.$item['Cid10Descricao'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaCid10(1,'.$item['Cid10Id'].', \''.$item['Cid10Codigo'].'\',\''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaCid10('.$atualizar.','.$item['Cid10Id'].', \''.$item['Cid10Codigo'].'\','.$item['Cid10Status'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaCid10('.$excluir.','.$item['Cid10Id'].', \''.$item['Cid10Codigo'].'\','.$item['Cid10Status'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
