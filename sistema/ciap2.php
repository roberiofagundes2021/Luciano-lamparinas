<?php 

include_once("sessao.php");

if(!$_SESSION['PerfiChave'] == "SUPER"){
	header("location:javascript://history.go(-1)");
}

$_SESSION['PaginaAtual'] = 'Ciap-2';

include('global_assets/php/conexao.php');

//Essa consulta é para preencher a grid
$sql = "SELECT Ciap2Id, Ciap2Codigo, Ciap2Descricao, Ciap2Status, SituaNome, SituaChave, SituaCor
		FROM Ciap2
		JOIN Situacao on SituaId = Ciap2Status
		ORDER BY Ciap2Codigo ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);
//$count = count($row);

//Se estiver editando
if(isset($_POST['inputCiap2Id']) && $_POST['inputCiap2Id']){

	//Essa consulta é para preencher o campo Codigo com o Ciap2 a ser editar
	$sql = "SELECT Ciap2Id, Ciap2Codigo, Ciap2Descricao
			FROM Ciap2
			WHERE Ciap2Id = " . $_POST['inputCiap2Id'];
	$result = $conn->query($sql);
	$rowCiap2 = $result->fetch(PDO::FETCH_ASSOC);
		
	$_SESSION['msg'] = array();
} 

//Se estiver gravando (inclusão ou edição)
if (isset($_POST['inputEstadoAtual']) && substr($_POST['inputEstadoAtual'], 0, 5) == 'GRAVA'){

	try{

		//Edição
		if (isset($_POST['inputEstadoAtual']) && $_POST['inputEstadoAtual'] == 'GRAVA_EDITA'){
			
			$sql = "UPDATE Ciap2 SET Ciap2Codigo = :sCodigo, Ciap2Descricao = :sDescricao, Ciap2UsuarioAtualizador = :iUsuarioAtualizador
					WHERE Ciap2Id = :iCiap2";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sCodigo' => $_POST['inputCodigo'],
                            ':sDescricao' => $_POST['inputDescricao'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iCiap2' => $_POST['inputCiap2Id']
							));
	
			$_SESSION['msg']['mensagem'] = "Ciap2 alterado!!!";
	
		} else { //inclusão
		
			$sql = "INSERT INTO Ciap2 ( Ciap2Codigo, Ciap2Descricao, Ciap2Status, Ciap2UsuarioAtualizador)
					VALUES ( :sCodigo, :sDescricao, :bStatus, :iUsuarioAtualizador)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':sCodigo' => $_POST['inputCodigo'],
                            ':sDescricao' => $_POST['inputDescricao'],
							':bStatus' => 1,
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							));
	
			$_SESSION['msg']['mensagem'] = "Ciap2 incluída!!!";
					
		}
	
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['tipo'] = "success";
					
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao atualizar ciap-2!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}

	irpara("Ciap2.php");
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Ciap-2</title>

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
			$('#tblCiap2').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
			    columnDefs: [
				{ 
					orderable: true,   //Código
					width: "30%",
					targets: [0]
				},
                { 
					orderable: true,   //Descrição
					width: "50%",
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
				var inputCodigoNovo = $('#inputCodigo').val();
				var inputCodigoVelho = $('#inputCiap2Codigo').val();
				var inputEstadoAtual = $('#inputEstadoAtual').val();
				
				//remove os espaços desnecessários antes e depois
				inputCodigo = inputCodigoNovo.trim();
				
				//Se o usuário preencheu com espaços em branco ou não preencheu nada
				if (inputCodigo == '' ){
					
					if (inputCodigo == ''){
						$('#inputCodigo').val('');
					}

					$("#formCiap2").submit();
				} else {

					//Esse ajax está sendo usado para verificar no Ciap-2 se o registro já existe
					$.ajax({
						type: "POST",
						url: "Ciap2Valida.php",
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
							
							$( "#formCiap2" ).submit();
						}
					})
				}					
			})
		});
			
		//Essa função foi criada para não usar $_GET e ficar mostrando os ids via URL
		function atualizaCiap2(Permission, Ciap2Id, Ciap2Codigo, Ciap2Status, Tipo){

			if (Permission == 1){
				document.getElementById('inputCiap2Id').value = Ciap2Id;
				document.getElementById('inputCiap2Codigo').value = Ciap2Codigo;
				document.getElementById('inputCiap2Status').value = Ciap2Status;
						
				if (Tipo == 'edita'){	
					document.getElementById('inputEstadoAtual').value = "EDITA";
					document.formCiap2.action = "Ciap2.php";		
				} else if (Tipo == 'exclui'){
					confirmaExclusao(document.formCiap2, "Tem certeza que deseja excluir essa Ciap-2?", "Ciap2Exclui.php");
				} else if (Tipo == 'mudaStatus'){
					document.formCiap2.action = "Ciap2MudaSituacao.php";
				}
				
				document.formCiap2.submit();
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
								<h3 class="card-title">Relação da Ciap-2</h3>
							</div>

							<div class="card-body">
								<form name="formCiap2" id="formCiap2" method="post" class="form-validate-jquery">

									<input type="hidden" id="inputCiap2Id" name="inputCiap2Id" value="<?php if (isset($_POST['inputCiap2Id'])) echo $_POST['inputCiap2Id']; ?>" >
									<input type="hidden" id="inputCiap2Codigo" name="inputCiap2Codigo" value="<?php if (isset($_POST['inputCiap2Codigo'])) echo $_POST['inputCiap2Codigo']; ?>" >
									<input type="hidden" id="inputCiap2Status" name="inputCiap2Status" >
									<input type="hidden" id="inputEstadoAtual" name="inputEstadoAtual" value="<?php if (isset($_POST['inputEstadoAtual'])) echo $_POST['inputEstadoAtual']; ?>" >

									<div class="row">
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputCodigo">Código <span class="text-danger"> *</span></label>
												<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" value="<?php if (isset($_POST['inputCiap2Id'])) echo $rowCiap2['Ciap2Codigo']; ?>" required >
											</div>
										</div>
                                        <div class="col-lg-6">
											<div class="form-group">
												<label for="inputDescricao">Descrição <span class="text-danger"> *</span></label>
												<input type="text" id="inputDescricao" name="inputDescricao" class="form-control" placeholder="Descrição" value="<?php if (isset($_POST['inputCiap2Id'])) echo $rowCiap2['Ciap2Descricao']; ?>" required >
											</div>
										</div>

										<div class="col-lg-3">
											<div class="form-group" style="padding-top:25px;">
												<?php

													//editando
													if (isset($_POST['inputCiap2Id'])){
														print('<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>');
														print('<a href="Ciap2.php" class="btn btn-basic" role="button">Cancelar</a>');
													} else{ //inserindo
														print('<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>');
													}

												?>
											</div>
										</div>
									</div>
								</form>
							</div>
							
							<table id="tblCiap2" class="table">
								<thead>
									<tr class="bg-slate">
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
											<td>'.$item['Ciap2Codigo'].'</td>
                                            <td>'.$item['Ciap2Descricao'].'</td>
											');
										
										print('<td><a href="#" onclick="atualizaCiap2(1,'.$item['Ciap2Id'].', \''.$item['Ciap2Codigo'].'\',\''.$item['SituaChave'].'\', \'mudaStatus\');"><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></a></td>');
										
										print('<td class="text-center">
												<div class="list-icons">
													<div class="list-icons list-icons-extended">
														<a href="#" onclick="atualizaCiap2('.$atualizar.','.$item['Ciap2Id'].', \''.$item['Ciap2Codigo'].'\','.$item['Ciap2Status'].', \'edita\');" class="list-icons-item"><i class="icon-pencil7" data-popup="tooltip" data-placement="bottom" title="Editar"></i></a>
														<a href="#" onclick="atualizaCiap2('.$excluir.','.$item['Ciap2Id'].', \''.$item['Ciap2Codigo'].'\','.$item['Ciap2Status'].', \'exclui\');" class="list-icons-item"><i class="icon-bin" data-popup="tooltip" data-placement="bottom" title="Exluir"></i></a>
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
