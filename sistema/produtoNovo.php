<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Novo Produto';

include('global_assets/php/conexao.php');

if(isset($_POST['inputNome'])){
	
	try{		
		$sql = "SELECT COUNT(isnull(ProduCodigo,0)) as Codigo
				FROM Produto
				Where ProduEmpresa = ".$_SESSION['EmpreId']."";
		//echo $sql;die;
		$result = $conn->query("$sql");
		$rowCodigo = $result->fetch(PDO::FETCH_ASSOC);	
		
		$sCodigo = (int)$rowCodigo['Codigo'] + 1;
		//Acrescenta zeros a esquerda (deixando com 9 caracteres)
		$sCodigo = str_pad($sCodigo,9,"0",STR_PAD_LEFT);
		//Formata o texto com o traço (dígito verificador)
		$sCodigoComDigito = substr_replace($sCodigo, '-', -1, 0);

	} catch(PDOException $e) {	
		echo 'Error1: ' . $e->getMessage();die;
	}
	
	try{
		
		$sql = "INSERT INTO Produto (ProduCodigo, ProduCodigoBarras, ProduNome, ProduDetalhamento, ProduCor, ProduTamanho, ProduFoto, ProduCategoria, ProduSubCategoria, ProduFamilia, ProduFinalistico, ProduValorCusto, 
									 ProduOutrasDespesas, ProduCustoFinal, ProduMargemLucro, ProduValorVenda, 
									 ProduEstoqueMinimo, ProduUnidadeMedida, ProduUnidadeMedidaSaida, ProduTipoFiscal, ProduNcmFiscal, 
									 ProduOrigemFiscal, ProduCest, ProduStatus, ProduUsuarioAtualizador, ProduEmpresa) 
				VALUES (:sCodigo, :sCodigoBarras, :sNome, :sDetalhamento, :sCor, :sTamanho, :sFoto, :iCategoria, :iSubCategoria, :sFamilia, :iFinalistico, :fValorCusto, 
						:fOutrasDespesas, :fCustoFinal, :fMargemLucro, :fValorVenda, :iEstoqueMinimo, :iUnidadeMedida, :iUnidadeMedidaSaida,
						:iTipoFiscal, :iNcmFiscal, :iOrigemFiscal, :iCest, :bStatus, :iUsuarioAtualizador, :iEmpresa)";
		$result = $conn->prepare($sql);

		$result->execute(array(
						':sCodigo' => $sCodigoComDigito,
						':sCodigoBarras' => $_POST['inputCodigoBarras'],
						':sNome' => $_POST['inputNome'],
						':sDetalhamento' => $_POST['txtDetalhamento'],
						':sCor' => $_POST['cmbCor'],
						':sTamanho' => $_POST['cmbTamanho'],
						':sFoto' => isset($_POST['inputFoto']) ? $_POST['inputFoto'] : null,
						':iCategoria' => $_POST['cmbCategoria'],
						':iSubCategoria' => $_POST['cmbSubCategoria'] == '' ? null : $_POST['cmbSubCategoria'],
						':sFamilia' => $_POST['inputFamilia'] == '#' ? null : $_POST['inputFamilia'],
						':iFinalistico' => $_POST['cmbFinalistico'] == '00' ? null : $_POST['cmbFinalistico'],
						':fValorCusto' => $_POST['inputValorCusto'] == null ? null : gravaValor($_POST['inputValorCusto']),						
						':fOutrasDespesas' => $_POST['inputOutrasDespesas'] == null ? null : gravaValor($_POST['inputOutrasDespesas']),
						':fCustoFinal' => $_POST['inputCustoFinal'] == null ? null : gravaValor($_POST['inputCustoFinal']),
						':fMargemLucro' => $_POST['inputMargemLucro'] == null ? null : gravaValor($_POST['inputMargemLucro']),
						':fValorVenda' => $_POST['inputValorVenda'] == null ? null : gravaValor($_POST['inputValorVenda']),
						':iEstoqueMinimo' => $_POST['inputEstoqueMinimo'] == '' ? null : $_POST['inputEstoqueMinimo'],
						':iUnidadeMedida' => $_POST['cmbUnidadeMedida'] == '#' ? null : $_POST['cmbUnidadeMedida'],
						':iUnidadeMedidaSaida' => $_POST['cmbUnidadeMedidaSaida'] == '#' ? null : $_POST['cmbUnidadeMedidaSaida'],
						':iTipoFiscal' => $_POST['cmbTipoFiscal'] == '#' ? null : $_POST['cmbTipoFiscal'],
						':iNcmFiscal' => $_POST['cmbNcmFiscal'] == '#' ? null : $_POST['cmbNcmFiscal'],
						':iOrigemFiscal' => $_POST['cmbOrigemFiscal'] == '#' ? null : $_POST['cmbOrigemFiscal'],
						':iCest' => $_POST['inputCest'] == '' ? null : $_POST['inputCest'],
						':bStatus' => 1,
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iEmpresa' => $_SESSION['EmpreId']
						));
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Produto incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {		
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir produto!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error2: ' . $e->getMessage();die;
		
	}
	
	irpara("produto.php");
} 

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Produto</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>	
	
	<script src="global_assets/js/plugins/media/fancybox.min.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>	
	<!-- /theme JS files -->
		
	<!-- Adicionando Javascript -->
    <script type="text/javascript" >

        $(document).ready(function() {	
	
			//Limpa o campo Nome quando for digitado só espaços em branco
			$("#inputNome").on('blur', function(e){
				
				var inputNome = $('#inputNome').val();

				inputNome = inputNome.trim();
				
				if (inputNome.length == 0){
					$('#inputNome').val('');
					//$("#formProduto").submit(); //Isso aqui é para submeter o formulário, validando os campos obrigatórios novamente
				}	
			});

			//Ao mudar a categoria, filtra a subcategoria via ajax (retorno via JSON)
			$('#cmbCategoria').on('change', function(e){
				
				Filtrando();
				$('#inputFamilia').val('');
				if($(this).val()){
					let cmbCategoria = $(this).val();
					let codCategoria = "";
					let codSubCategoria = "";
					
					$('#cmbCategoria option').each(function(e){
						if ($(this).val() == cmbCategoria){
							codCategoria = $(this).data('codcategoria');
						}
					});
	
					$.ajax({
						type: 'GET',
						url: 'filtraSubCategoria.php',
						dataType: 'json',
						data:{
							'idCategoria': cmbCategoria
						},
						success: async function(response) {
							let option = '<option value="">Selecione a SubCategoria</option>';
							if (response.length){
								$.each(response, function(i, obj){
									option += '<option value="'+obj.SbCatId+'">'+obj.SbCatCodigo+' - '+obj.SbCatNome+'</option>';
									codSubCategoria = obj.SbCatCodigo;
								});
								$('#cmbSubCategoria').html(option)
							} else {
								Reset();
							}
							$('#inputFamilia').val(`${codCategoria}.00`);
						}
					})
				}
			});

			//Ao mudar a categoria, filtra a subcategoria via ajax (retorno via JSON)
			$('#cmbSubCategoria').on('change', function(e){
				let codSubCategoria = '00'
				let inputFamilia = $('#inputFamilia').val()
				inputFamilia = inputFamilia.split('.')[0]

				if($(this).val()){
					$('#cmbSubCategoria option').each(function(e){
						if ($(this).val() == $('#cmbSubCategoria').val()){
							codSubCategoria = $(this).html();
						}
					});
					console.log(codSubCategoria)
					
					codSubCategoria = codSubCategoria.split('-')
					codSubCategoria = codSubCategoria[0].split(' ')
					codSubCategoria = codSubCategoria[0]
				}
				$('#inputFamilia').val(`${inputFamilia}.${codSubCategoria}`)
			});

			//Ao mudar o Custo, atualiza o CustoFinal
			$('#inputValorCusto').on('blur', function(e){
								
				var inputValorCusto = $('#inputValorCusto').val().replaceAll('.', '').replace(',', '.');
				var inputOutrasDespesas = $('#inputOutrasDespesas').val().replaceAll('.', '').replace(',', '.');
				var inputMargemLucro = $('#inputMargemLucro').val().replaceAll('.', '').replace(',', '.');
				
				if (inputValorCusto == null || inputValorCusto.trim() == '') {
					inputValorCusto = 0.00;
				}
				
				if (inputOutrasDespesas == null || inputOutrasDespesas.trim() == '') {
					inputOutrasDespesas = 0.00;
				}
				
				var inputCustoFinal = parseFloat(inputValorCusto) + parseFloat(inputOutrasDespesas);
				
				inputCustoFinal = float2moeda(inputCustoFinal).toString();
				
				$('#inputCustoFinal').val(inputCustoFinal);
				
				if (inputMargemLucro != null && inputMargemLucro.trim() != '' && inputMargemLucro.trim() != 0.00) {
					atualizaValorVenda();
				}
			});
			
			//Ao mudar o Custo, atualiza o CustoFinal
			$('#inputOutrasDespesas').on('blur', function(e){
								
				var inputValorCusto = $('#inputValorCusto').val().replaceAll('.', '').replace(',', '.');
				var inputOutrasDespesas = $('#inputOutrasDespesas').val().replaceAll('.', '').replace(',', '.');
				var inputMargemLucro = $('#inputMargemLucro').val().replaceAll('.', '').replace(',', '.');
				
				if (inputValorCusto == null || inputValorCusto.trim() == '') {
					inputValorCusto = 0.00;
				}
				
				if (inputOutrasDespesas == null || inputOutrasDespesas.trim() == '') {
					inputOutrasDespesas = 0.00;
				}
				
				var inputCustoFinal = parseFloat(inputValorCusto) + parseFloat(inputOutrasDespesas);
				
				inputCustoFinal = float2moeda(inputCustoFinal).toString();
				
				$('#inputCustoFinal').val(inputCustoFinal);
				
				if (inputMargemLucro != null && inputMargemLucro.trim() != '' && inputMargemLucro.trim() != 0.00) {
					atualizaValorVenda();
				}				
			});			
			
			//Ao mudar a Margem de Lucro, atualiza o Valor de Venda
			$('#inputMargemLucro').on('blur', function(e){
								
				atualizaValorVenda();
			});	
			
			//Ao mudar o Valor de Venda, atualiza a Margem de Lucro
			$('#inputValorVenda').on('blur', function(e){
								
				var inputCustoFinal = $('#inputCustoFinal').val().replaceAll('.', '').replace(',', '.');
				var inputValorVenda = $('#inputValorVenda').val().replaceAll('.', '').replace(',', '.');
				
				if (inputCustoFinal == null || inputCustoFinal.trim() == '') {
					inputCustoFinal = 0.00;
				}
				
				if (inputValorVenda == null || inputValorVenda.trim() == '') {
					inputValorVenda = 0.00;
				}
				
				//alert(parseFloat(inputMargemLucro) * parseFloat(inputCustoFinal));
				var lucro = parseFloat(inputValorVenda) - parseFloat(inputCustoFinal);	
				
				inputMargemLucro = 0;
				
				if (inputCustoFinal != 0.00 && inputValorVenda != 0.00){
					inputMargemLucro = lucro / parseFloat(inputCustoFinal) * 100;
				}
				
				inputMargemLucro = float2moeda(inputMargemLucro).toString();
				
				$('#inputMargemLucro').val(inputMargemLucro);				

			});	
			
			function atualizaValorVenda(){
				var inputCustoFinal = $('#inputCustoFinal').val().replaceAll('.', '').replace(',', '.');
				var inputMargemLucro = $('#inputMargemLucro').val().replace(',', '.');				
				
				if (inputCustoFinal == null || inputCustoFinal.trim() == '') {
					inputCustoFinal = 0.00;
				}
				
				if (inputMargemLucro == null || inputMargemLucro.trim() == '') {
					inputMargemLucro = 0.00;
				}
								
				var inputValorVenda = inputMargemLucro == 0.00 ? 0.00 : parseFloat(inputCustoFinal) + (parseFloat(inputMargemLucro) * parseFloat(inputCustoFinal))/100;
				
				inputValorVenda = float2moeda(inputValorVenda).toString();
				
				$('#inputValorVenda').val(inputValorVenda);
			}
			
			//Ao clicar no botão Adicionar Foto aciona o click do file que está hidden
			$('#addFoto').on('click', function(e){	
				e.preventDefault(); // Isso aqui não deixa o formulário "formProduto" ser submetido ao clicar no INcluir Foto, ou seja, ao executar o método ajax
			
				$('#imagem').trigger("click");
			});			
			
			// #imagem é o id do input, ao alterar o conteudo do input execurará a função abaixo
			$('#imagem').on('change',function(){			

				$('#visualizar').html('<img src="global_assets/images/lamparinas/ajax-loader.gif" alt="Enviando..."/>');
				resetImagem = '<img class="ml-3" src="global_assets/images/lamparinas/sem_foto.gif" alt="Produto" style="max-height:250px; border:2px solid #ccc;">';

				var inputFile = $('#imagem').val();
				var id = $("input:file").attr('id');
				var tamanho =  1024 * 1024 * 3; //3MB
								
				//Verifica se o campo só possui espaços em branco
				if (inputFile == ''){
					alerta('Atenção','Selecione o arquivo!','error');
					$('#visualizar').html(resetImagem);
					return false;
				}
								
				//Verifica se a extensão é  diferente de CSV
				if (ext(inputFile) != 'jpg' && ext(inputFile) != 'jpeg' && ext(inputFile) != 'gif' && ext(inputFile) != 'png' && ext(inputFile) != 'bmp'){
					alerta('Atenção','Por favor, envie arquivos com a seguinte extensão: JPG, JPEG, GIF, PNG, BMP!','error');
					$('#visualizar').html(resetImagem);
					return false;
				}
				
				//Verifica o tamanho do arquivo
				if ($('#'+id)[0].files[0].size > tamanho){
					alerta('Atenção','O arquivo enviado é muito grande, envie arquivos de até 3MB.','error');
					$('#visualizar').html(resetImagem);
					return false;
				}				
								
				// Get form
				var form = $('#formFoto')[0];
				var formData = new FormData(form);
				//var inputFoto = $('#inputFoto').val();
				//alert($('#imagem')[0].files[0]);
				
				formData.append('file', $('#imagem')[0].files[0] );
				formData.append('tela', 'produto' );
				
				$.ajax({
					type: "POST",
					enctype: 'multipart/form-data',
					url: "upload.php",
					processData: false,  // impedir que o jQuery tranforma a "data" em querystring					
					contentType: false,  // desabilitar o cabeçalho "Content-Type"
					cache: false, // desabilitar o "cache"
					data: formData,//{imagem: inputImagem},
					success: function(resposta){
						//console.log(resposta);
						
						$('#visualizar').html(resposta);
						$('#addFoto').text("Alterar Foto...");
						
						//Aqui sou obrigado a instanciar novamente a utilização do fancybox
						$(".fancybox").fancybox({
							// options
						});	
						
						return false;						
					}
				}); //ajax
				
				//$('#formFoto').submit();
				
				// Efetua o Upload sem dar refresh na pagina
				$('#formFoto').ajaxForm({
					target:'#visualizar' // o callback será no elemento com o id #visualizar
				}).submit();
			});			
			
			function Filtrando(){
				$('#cmbSubCategoria').empty().append('<option value="#">Filtrando...</option>');
			}
			
			function Reset(){
				$('#cmbSubCategoria').empty().append('<option value="#">Sem Subcategoria</option>');
			}

			//Valida Registro Duplicado
			$('#enviar').on('click', function(e){
				
				e.preventDefault();
				
				$( "#formProduto" ).submit();
				
			}); // enviar
			
			//Valida Registro Duplicado
			$('#cancelar').on('click', function(e){
				
				e.preventDefault();
				
				var inputFoto = $('#inputFoto').val();
				
				//Esse ajax está sendo usado para excluir a imagem que nao será mais usada
				$.ajax({
					type: "POST",
					url: "produtoExcluiImagem.php",
					data: ('foto='+inputFoto),
					success: function(resposta){
						
					}
				})				
				
				$(window.document.location).attr('href',"produto.php");
				
			}); // cancelar		
		});	

		function ext(path) {
			var final = path.substr(path.lastIndexOf('/')+1);
			var separador = final.lastIndexOf('.');
			return separador <= 0 ? '' : final.substr(separador + 1);
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
				<div class="card">
					
					<form id="formProduto" name="formProduto" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Produto</h5>
						</div>
						
						<div class="card-body">								
							
							<div class="media">								
								
								<div class="media-body">
									
									<div class="row">
										<div class="col-lg-8">
											<div class="form-group">
												<label for="inputCodigoBarras">Código de Barras</label>
												<input type="text" id="inputCodigoBarras" name="inputCodigoBarras" class="form-control" placeholder="Código de Barras" autofocus>
											</div>	
										</div>
										
										<div class="col-lg-4">
											<div class="form-group">				
												<label for="inputEstoqueMinimo">Estoque Mínimo</label>
												<input type="text" id="inputEstoqueMinimo" name="inputEstoqueMinimo" class="form-control" placeholder="Estoque Mínimo">
											</div>	
										</div>						
									</div>
									
									<div class="row">	
										<div class="col-lg-12">
											<div class="form-group">
												<label for="inputNome">Nome <span class="text-danger">*</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" required>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="txtDetalhamento">Detalhamento</label>
												<textarea rows="5" cols="5" class="form-control" id="txtDetalhamento" name="txtDetalhamento" placeholder="Detalhamento do produto"></textarea>
											</div>
										</div>
									</div>
																		
								</div> <!-- media-body -->
								
								<div style="text-align:center;">
									<div id="visualizar" class="ml-3">
										<img src="global_assets/images/lamparinas/sem_foto.gif" alt="Produto" style="max-height:230px; border:2px solid #ccc;">
									</div>
									<br>
									<button id="addFoto" class="ml-3 btn btn-lg btn-principal" style="width:90%">Adicionar Foto...</button>	
								</div>
								
							</div> <!-- media -->
														
							<div class="row">
								<div class="col-lg-12">
									<h5 class="mb-0 font-weight-semibold">Classificação</h5>
									<br>
									<div class="row">

										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbCategoria">Categoria <span class="text-danger">*</span></label>
												<select id="cmbCategoria" name="cmbCategoria" class="form-control select-search" required>
													<option value="">Selecione</option>
													<?php 
														$sql = "SELECT CategId, CategCodigo, CategNome
																FROM Categoria
																JOIN Situacao on SituaId = CategStatus
																WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
																ORDER BY CategNome ASC";
														$result = $conn->query($sql);
														$row = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($row as $item){															
															print('<option value="'.$item['CategId'].'" data-codcategoria="'.$item['CategCodigo'].'">'.$item['CategCodigo'].' - '.$item['CategNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbSubCategoria">SubCategoria</label>
												<select id="cmbSubCategoria" name="cmbSubCategoria" class="form-control select-search">
													<option value="">Selecione</option>
												</select>
											</div>
										</div>
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputFamilia">Família</label>
												<input type="text" id="inputFamilia" name="inputFamilia" class="form-control" placeholder="Família"readOnly>
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="cmbFinalistico">Finalístico</label>
												<select id="cmbFinalistico" name="cmbFinalistico" class="form-control select-search">
													<option value="">Selecione</option>
													<?php 
														$sql = "SELECT FinalId, FinalNome, FinalCodigo
																FROM Finalistico
																JOIN Situacao on SituaId = FinalStatus
																WHERE FinalEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
																ORDER BY FinalNome ASC";
														$result = $conn->query($sql);
														$rowFinalistico = $result->fetchAll(PDO::FETCH_ASSOC);

														foreach ($rowFinalistico as $item){
															print('<option value="'.$item['FinalId'].'">'.$item['FinalCodigo'] . ' - ' .$item['FinalNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-lg-12">									
									<h5 class="mb-0 font-weight-semibold">Característica</h5>
									<br>
									<div class="row">	
										<div class="col-lg-2">
											<div class="form-group">
												<label for="cmbCor">Cor</label>
												<select id="cmbCor" name="cmbCor" class="form-control select-search">
												<option value="#">Selecione</option>
													<option value='VM'>Vermelho</option>
                                                    <option value='AZ'>Azul</option>
													<option value='AM'>Amarelo</option>
													<option value='VD'>Verde</option>
													<option value='LA'>Laranja</option>
													<option value='RO'>Roxo</option>
													<option value='PR'>Preto</option>
													<option value='BR'>Branco</option>

												</select>
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="cmbTamanho">Tamanho</label>
												<select id="cmbTamanho" name="cmbTamanho" class="form-control select-search">
												<option value="#">Selecione</option>
													<option value='PE'>Pequeno</option>
                                                    <option value='GR'>Grande</option>
													<option value='ME'>Médio</option>

												</select>
												</select>
											</div>
										</div>
							
										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbUnidadeMedida">Unidade de Medida - Entrada<span class="text-danger">*</span></label>
												<select id="cmbUnidadeMedida" name="cmbUnidadeMedida" class="form-control select-search" required>
													<option value="">Selecione</option>
													<?php 
														$sql = "SELECT UnMedId, UnMedNome, UnMedSigla
																FROM UnidadeMedida
																JOIN Situacao on SituaId = UnMedStatus
																WHERE UnMedEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
																ORDER BY UnMedNome ASC";
														$result = $conn->query($sql);
														$row = $result->fetchAll(PDO::FETCH_ASSOC);

														foreach ($row as $item){
															print('<option value="'.$item['UnMedId'].'">'.$item['UnMedNome'] . ' (' . $item['UnMedSigla'] . ')' .'</option>');
														}
													
													?>
												</select>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbUnidadeMedidaSaida">Unidade de Medida - Saída<span class="text-danger">*</span></label>
												<select id="cmbUnidadeMedidaSaida" name="cmbUnidadeMedidaSaida" class="form-control select-search" required>
													<option value="">Selecione</option>
													<?php 
														$sql = "SELECT UnMedId, UnMedNome, UnMedSigla
																FROM UnidadeMedida
																JOIN Situacao on SituaId = UnMedStatus
																WHERE UnMedEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
																ORDER BY UnMedNome ASC";
														$result = $conn->query($sql);
														$row = $result->fetchAll(PDO::FETCH_ASSOC);

														foreach ($row as $item){
															print('<option value="'.$item['UnMedId'].'">'.$item['UnMedNome'] . ' (' . $item['UnMedSigla'] . ')' .'</option>');
														}
													
													?>
												</select>
											</div>
										</div>
									</div> <!-- /row -->
									
								</div> <!-- /col -->
							</div>	<!-- /row -->

							<div class="row">
								<div class="col-lg-6">
									<h5 class="mb-0 font-weight-semibold">Custo</h5>
									<br>
								</div>
								<div class="col-lg-6">
									<h5 class="mb-0 font-weight-semibold">Venda</h5>
									<br>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">
									<div class="row">
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputValorCusto">Valor de Custo</label>
												<input type="text" id="inputValorCusto" name="inputValorCusto" class="form-control" placeholder="Valor de Custo" onKeyUp="moeda(this)" maxLength="12">
											</div>
										</div>
										
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputOutrasDespesas">Outras Despesas</label>
												<input type="text" id="inputOutrasDespesas" name="inputOutrasDespesas" class="form-control" placeholder="Outras Despesas" onKeyUp="moeda(this)" maxLength="12">
											</div>
										</div>			
										
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputCustoFinal">Custo Final</label>
												<input type="text" id="inputCustoFinal" name="inputCustoFinal" class="form-control" placeholder="Custo Final" readOnly>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputMargemLucro">Margem de Lucro (%)</label>
												<input type="text" id="inputMargemLucro" name="inputMargemLucro" class="form-control" placeholder="Margem Lucro" onKeyUp="moeda(this)" maxLength="6">
											</div>
										</div>										
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputValorVenda">Valor de Venda</label>
												<input type="text" id="inputValorVenda" name="inputValorVenda" class="form-control" placeholder="Valor de Venda" onKeyUp="moeda(this)" maxLength="12">
											</div>
										</div>
									</div>
								</div>						
							</div>
							<br>
							
							<div class="row">
								<div class="col-lg-12">									
									<h5 class="mb-0 font-weight-semibold">Dados Fiscais</h5>
									<br>
									<div class="row">						

										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbTipoFiscal">Tipo</label>
												<select id="cmbTipoFiscal" name="cmbTipoFiscal" class="form-control select-search">
													<option value="#">Selecione</option>
													<?php 
														$sql = "SELECT TpFisId, TpFisNome
																FROM TipoFiscal
																JOIN Situacao on SituaId = TpFisStatus
																WHERE SituaChave = 'ATIVO'
																ORDER BY TpFisNome ASC";
														$result = $conn->query($sql);
														$row = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($row as $item){
															print('<option value="'.$item['TpFisId'].'">'.$item['TpFisNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>
										
										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbOrigemFiscal">Origem</label>
												<select id="cmbOrigemFiscal" name="cmbOrigemFiscal" class="form-control select-search">
													<option value="#">Selecione</option>
													<?php 
														$sql = "SELECT OrFisId, OrFisNome
																FROM OrigemFiscal
																JOIN Situacao on SituaId = OrFisStatus
																WHERE SituaChave = 'ATIVO'
																ORDER BY OrFisNome ASC";
														$result = $conn->query($sql);
														$row = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($row as $item){
															$seleciona = $item['OrFisNome'] == 'Nacional' ? "selected" : "";
															print('<option value="'.$item['OrFisId'].'" '.$seleciona.'>'.$item['OrFisNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>
										<div class="col-lg-2">
											<div class="form-group">
												<label for="cmbNcmFiscal">NCM</label>
												<select id="cmbNcmFiscal" name="cmbNcmFiscal" class="form-control select-search">
													<option value="#">Selecione um NCM</option>
													<?php 
														$sql = "SELECT NcmNome
																FROM Ncm
																JOIN Situacao on SituaId = NcmStatus
																WHERE SituaChave = 'ATIVO'
																ORDER BY NcmCodigo ASC";
														$result = $conn->query($sql);
														$row = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($row as $item){
															print('<option value="'.$item['NcmId'].'">'.$item['NcmCodigo'] . " - " . $item['NcmNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>
										
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputCest">CEST</label>
												<input type="text" id="inputCest" name="inputCest" class="form-control" placeholder="CEST">
											</div>
										</div>	
									
									</div> <!-- /row -->
									
								</div> <!-- /col -->
							</div>	<!-- /row -->

							<div class="row" style="margin-top: 40px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="produto.php" class="btn btn-lg" id="cancelar">Cancelar</a>
									</div>
								</div>
							</div>

						</div>
						<!-- /card-body -->

					</form>
					
					<form id="formFoto" method="post" enctype="multipart/form-data" action="upload.php">
						<input type="file" id="imagem" name="imagem" style="display:none;" />
					</form>	
					
				</div>
				<!-- /info blocks -->

			</div>
			<!-- /content area -->			
			
			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

</body>
</html>
