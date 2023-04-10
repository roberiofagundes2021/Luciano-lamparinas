<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Busca de Produtos';

include('global_assets/php/conexao.php');

$iEmpresa = $_SESSION['EmpreId'];
$_SESSION['SituaChave'] = $_SESSION['StChave'];

$sql = "SELECT * FROM Categoria
	    WHERE  CategStatus = 1
		AND CategEmpresa = $iEmpresa";
$result = $conn->query($sql);
$rowCategoria = $result->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM SubCategoria
	    WHERE  SbCatStatus = 1
		AND SbCatEmpresa = $iEmpresa";
$resultS = $conn->query($sql);
$rowSubCategoria = $resultS->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Busca de Produtos</title>

	<?php include_once("head.php"); ?>	
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>

	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

    <script src="global_assets/js/demo_pages/form_select2.js"></script>
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

	<!-- Não permite que o usuário retorne para o EDITAR -->
	<script src="global_assets/js/lamparinas/stop-back.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	
	
	<script type="text/javascript">

        $(document).ready(function() {

            $('#tblSearchProdutos').DataTable( {
				"order": [[ 0, "asc" ]],
			    autoWidth: false,
				responsive: true,
				searching: false,
				ordering: false, 
				paging: false,
			    columnDefs: [
				{ 
					orderable: true, 
					width: "5%", 
					targets: [0]
				},
				{ 
					orderable: true,   
					width: "5%", 
					targets: [1]
				},
				{ 
					orderable: true,
					width: "20%", 
					targets: [2]
				},				
				{ 
					orderable: true,  
					width: "15%", 
					targets: [3]
				},				
				{ 
					orderable: true,  
					width: "15%", 
					targets: [4]
				},				
				{ 
					orderable: true,  
					width: "10%", 
					targets: [5]
				},				
				{ 
					orderable: true,  
					width: "10%", 
					targets: [6]
				},				
				{ 
					orderable: true,  
					width: "10%", 
					targets: [7]
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer">',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
                
			});		

			$('#pesquisar').on('click', function(e) {
				e.preventDefault();

				let menssageError = '';
				let categoria = $('#categoria').val();
				let subCategoria = $('#subcategoria').val();
				let nomeProduto = $('#nomeproduto').val();

				//chamar requisicao
				$.ajax({
					type: 'POST',
					url: 'filtraAtendimento.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'PESQUISARPRODUTOS',
						'categoria': categoria,
						'subCategoria': subCategoria,
						'nomeProduto': nomeProduto
					},
					success: function(response) {

						statusProduto = response.length ? true : false;
						if (statusProduto) { 

							$('#categoria').val('').change();
							$('#subcategoria').val('').change();
							$('#nomeproduto').val('');

							$('#dataSearchProdutos').html('');

							let HTML = '';

							response.forEach(item => {

								let acoes = `<div class='list-icons'>
									<button type="button" class="btn btn-sm btn-info" onclick='selecionarProduto(${JSON.stringify(item)})'>Selecionar</button>
								</div>`;
								
								HTML += `
								<tr class='produtoItem'>
									<td class="text-left"> ${item.item}</td>
									<td class="text-left"> ${item.produCodigo}</td>
									<td class="text-left">${item.descricao}</td>
									<td class="text-left">${item.categoria}</td>
									<td class="text-left">${item.subCategoria}</td>
									<td class="text-left">${item.unidade}</td>
									<td class="text-left">${item.classificacao}</td>
									<td class="text-left">${acoes}</td>
								</tr>`;

							});

							$('#dataSearchProdutos').html(HTML).show();

						}else{

							alerta('Busca de Produto', 'Não foi encontrado nenhum produto com as informações cedidas! Tente novamente com outros dados!', 'error');

						}
						
					},
					error: function(response) {
						alerta(response.titulo, response.menssagem, response.status);
					}
				});

			});


			$('#categoria').on('change', function (e) {

				let categoriaId = $('#categoria').val();

				$.ajax({
					type: 'POST',
					url: 'filtraAtendimentoObservacaoHospitalar.php',
					dataType: 'json',
					data: {
						'tipoRequest': 'FILTRARSUBCATEGORIA',
						'categoriaId' : categoriaId
					},
					success: function(response) {

						$('#subcategoria').empty();
						$('#subcategoria').append(`<option value=''>Selecione</option>`)
						let opt = ''
						response.forEach(item => {
					
							opt = `<option value="${item.id}">${item.nome}</option>`
							$('#subcategoria').append(opt)

						})
					}
				});

				$('#subcategoria').focus();

			})

        });//ready

		function selecionarProduto(item) {

			<?php if($_SESSION['tipoPesquisa'] == 'MEDICAMENTO') { ?>

				window.opener.$('#nomeMedicamentoEstoqueMedicamentos').val(item.descricao);
				window.opener.document.getElementById('medicamentoEstoqueMedicamentos').value = item.id;
				fecharJanela()

			<?php } elseif ($_SESSION['tipoPesquisa'] == 'SOLUCAO') { ?>

				window.opener.$('#nomeMedicamentoEstoqueSolucoes').val(item.descricao);
				window.opener.document.getElementById('medicamentoEstoqueSolucoes').value = item.id;
				fecharJanela()

			<?php } elseif ($_SESSION['tipoPesquisa'] == 'SOLUCAODILUENTE') { ?>

				window.opener.$('#nomeDiluenteSolucoes').val(item.descricao);
				window.opener.document.getElementById('diluenteSolucoes').value = item.id;
				fecharJanela()

			<?php } elseif ($_SESSION['tipoPesquisa'] == 'PRODUTOTABELAGASTO') { ?>

				window.opener.$('#nomeProdutos').val(item.descricao);
				window.opener.document.getElementById('produtos').value = item.id;
				fecharJanela()

			<?php }
				$_SESSION['tipoPesquisa'] = '';
			 ?>


		}

        function fecharJanela() {            
            window.open('', '_self', ''); window.close();
        }

	</script>

</head>

<body>

	<!-- Page content -->
	<div class="page-content">
		<!-- Main content -->
		<div class="content-wrapper">
			<!-- Content area -->
			<div class="content">
				<!-- Info blocks -->		
				<div class="row">					
					<div class="col-lg-12">
						
						<!-- Basic responsive configuration -->
						<form name="formBuscaProdutos" id="formBuscaProdutos" method="post" class="form-validate-jquery">						
							
							<div class="card">								

								<div class="card-header header-elements-inline">
									<h2 class="card-title font-weight-bold">Produtos em Estoque</h2>
								</div>

                                <div class="card-body">

									<form id="formHistoriaEntrada" name="formHistoriaEntrada" method="post" class="form-validate-jquery">
										<div class="col-md-12 mb-2 row">
											<!-- titulos -->
											<div class="col-md-6">
												<label>Categoria</label>
											</div>
											<div class="col-md-6">
												<label>SubCategoria</label>
											</div>
											<!-- campos -->										
											<div class="col-md-6">
                                                <select id="categoria" name="categoria" class="select-search" >
													<option value=''>Selecione</option>
													<?php foreach ($rowCategoria as $item) {
														echo "<option value='" . $item['CategId'] .  "'>" . $item['CategNome'] . "</option>";													}
													 ?>
												</select>	
											</div>
											<div class="col-md-6">
												<select id="subcategoria" name="subcategoria" class="select-search" >
													<option value=''>Selecione</option>
													<?php foreach ($rowSubCategoria as $item) {
														echo "<option value='" . $item['SbCatId'] .  "'>" . $item['SbCatNome'] . "</option>";													}
													 ?>
												</select>											
											</div>
										</div>

                                        <div class="col-md-12 mb-2 row">
											<!-- titulos -->
											<div class="col-md-10">
												<label>Produto</label>
											</div>
											<!-- campos -->										
											<div class="col-md-8">
                                                <input type="text" class="form-control" name="nomeproduto" id="nomeproduto">
											</div>
											<div class="col-md-4">

                                                <div class="form-group">
                                                    <button class="btn btn-lg btn-success ml-2" type="button" id="pesquisar">Pesquisar</button>                                           
                                                    <a href="#" onClick="fecharJanela();" type="button" class='btn btn-basic ml-2' role='button'>Cancelar</a>                                 
                                                </div>
																						
											</div>
										</div>
                                       
									</form>                              

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <table class="table" id="tblSearchProdutos">
                                                <thead>
                                                    <tr class="bg-slate">
                                                        <th class="text-left">Item</th>
                                                        <th class="text-left">Código</th>
                                                        <th class="text-left">Descrição</th>
                                                        <th class="text-left">Categoria</th>
                                                        <th class="text-left">SubCategoria</th>
                                                        <th class="text-left">Unidade</th>
                                                        <th class="text-left">Classificação</th>
                                                        <th class="text-center">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dataSearchProdutos">
                                                </tbody>
                                            </table>
                                        </div>		
                                    </div>


								</div>

							</div>
						</form>	

							<!-- /basic responsive configuration -->
					</div>
					
				</div>				
				
				<!-- /info blocks -->

			</div>
			<!-- /content area -->

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

</body>

</html>
