<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Editar Produto';

include('global_assets/php/conexao.php');

if(isset($_POST['inputProdutoId'])){

	$sql = "SELECT TRXPrTermoReferencia
			FROM TermoReferenciaXProduto
			JOIN Produto on ProduId = TRXPrProduto
			JOIN TermoReferencia on TrRefId = TRXPrTermoReferencia
			JOIN Situacao on Situaid = TrRefStatus
			WHERE TRXPrProduto = ".$_POST['inputProdutoId']." and 
			(SituaChave = 'LIBERADOCENTRO' or SituaChave = 'LIBERADOCONTABILIDADE' or SituaChave = 'FASEINTERNAFINALIZADA') 
			and TRXPrUnidade = ".$_SESSION['UnidadeId']."
			";
	$result = $conn->query($sql);
	$rowTrs = $result->fetchAll(PDO::FETCH_ASSOC);
	$contTRs = count($rowTrs);
	
	$iProduto = $_POST['inputProdutoId'];
	
	try{
		
		$sql = "SELECT *
				FROM Produto
				JOIN Situacao on SituaId = ProduStatus
				WHERE ProduId = $iProduto ";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);		
		
		$valorCusto = mostraValor($row['ProduValorCusto']);
		$valorVenda	= mostraValor($row['ProduValorVenda']);
		$outrasDespesas = mostraValor($row['ProduOutrasDespesas']);
		$custoFinal = mostraValor($row['ProduCustoFinal']);
		$margemLucro = mostraValor($row['ProduMargemLucro']);
		
		//Primeiro verifica se no banco está nulo
		if ($row['ProduFoto'] != null){
			
			//Depois verifica se o arquivo físico ainda existe no servidor
			if (file_exists("global_assets/images/produtos/".$row['ProduFoto'])){
				$sFoto = "global_assets/images/produtos/".$row['ProduFoto'];
			} else {
				$sFoto = "global_assets/images/lamparinas/sem_foto.gif";
			}
			$sButtonFoto = "Alterar Foto...";
		} else {
			$sFoto = "global_assets/images/lamparinas/sem_foto.gif";
			$sButtonFoto = "Adicionar Foto...";
		}

		/* Verifica se tem Ordem de Compra ou Fluxo para esse produto (de acordo com o parâmetro) */
		$sql = "SELECT ParamValorAtualizadoFluxo, ParamValorAtualizadoOrdemCompra
				FROM Parametro				
				WHERE ParamEmpresa = ".$_SESSION['EmpreId'];
		$result = $conn->query($sql);
		$rowParamentro = $result->fetch(PDO::FETCH_ASSOC);

		if ($rowParamentro['ParamValorAtualizadoFluxo']){
			$sql = "SELECT COUNT(FOXPrProduto) as CONT
					FROM FluxoOperacionalXProduto
					JOIN FluxoOperacional on FlOpeId = FOXPrFluxoOperacional
					JOIN Situacao on SituaId = FlOpeStatus
					WHERE FOXPrProduto = ".$iProduto." and SituaChave = 'LIBERADO' ";
			$result = $conn->query($sql);
			$rowExiste = $result->fetch(PDO::FETCH_ASSOC);
		} else if ($rowParamentro['ParamValorAtualizadoOrdemCompra']){
			$sql = "SELECT COUNT(OCXPrProduto) as CONT
					FROM OrdemCompraXProduto
					JOIN OrdemCompra on OrComId = OCXPrOrdemCompra
					JOIN Situacao on SituaId = OrComSituacao
					WHERE OCXPrProduto = ".$iProduto." and SituaChave = 'LIBERADO' ";
			$result = $conn->query($sql);
			$rowExiste = $result->fetch(PDO::FETCH_ASSOC);
		}

		$travado = "";

		if ($rowExiste['CONT']){
			$travado = "readOnly";
		}
		
	} catch(PDOException $e) {
		echo 'Error: ' . $e->getMessage();
	}
	
	$_SESSION['msg'] = array();

} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	irpara("produto.php");
}

if(isset($_POST['inputNome'])){
		
	try{

		$conn->beginTransaction();

		$sql = "SELECT SituaId
				FROM Situacao
				WHERE SituaChave = 'ATIVO' ";
		$result = $conn->query($sql);
		$rowSituacao = $result->fetch(PDO::FETCH_ASSOC);		

		$Status = '';

		//Se o Status estiver em 'ALTERAR' mudar para 'ATIVO'
		if ($_POST['inputProdutoStatus'] == 'ALTERAR'){
			$Status = $rowSituacao['SituaId'];
		}

		$sql = "UPDATE Produto SET ProduCodigo = :sCodigo, ProduCodigoBarras = :sCodigoBarras, ProduNome = :sNome, ProduDetalhamento = :sDetalhamento,  ProduCor = :sCor,  ProduTamanho = :sTamanho,
								   ProduFoto = :sFoto, ProduCategoria = :iCategoria, ProduSubCategoria = :iSubCategoria, ProduFinalistico = :iFinalistico, ProduFamilia = :sFamilia,ProduValorCusto = :fValorCusto, 
								   ProduOutrasDespesas = :fOutrasDespesas, ProduCustoFinal = :fCustoFinal, ProduMargemLucro = :fMargemLucro, 
								   ProduValorVenda = :fValorVenda, ProduEstoqueMinimo = :iEstoqueMinimo, ProduUnidadeMedida = :iUnidadeMedida, 
								   ProduUnidadeMedidaSaida = :iUnidadeMedidaSaida, ProduTipoFiscal = :iTipoFiscal, ProduNcmFiscal = :iNcmFiscal, 
								   ProduOrigemFiscal = :iOrigemFiscal, ProduCest = :iCest, ProduUsuarioAtualizador = :iUsuarioAtualizador ";

		if ($_POST['inputProdutoStatus'] == 'ALTERAR'){
			$sql .= ", ProduStatus = ".$Status." ";
		}

		$sql .=	"	WHERE ProduId = :iProduto";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':sCodigo' => $_POST['inputCodigo'],
						':sCodigoBarras' => $_POST['inputCodigoBarras'],
						':sNome' => $_POST['inputNome'],
						':sDetalhamento' => $_POST['txtDetalhamento'],
						':sCor' => $_POST['cmbCor'],
						':sTamanho' => $_POST['cmbTamanho'],
						':sFoto' => isset($_POST['inputFoto']) ? $_POST['inputFoto'] : null,
						':iCategoria' => $_POST['cmbCategoria'],
						':iSubCategoria' => $_POST['cmbSubCategoria'] == '' ? null : $_POST['cmbSubCategoria'],
						':sFamilia' => $_POST['inputFamilia'] == '#' ? null : $_POST['inputFamilia'],
						':iFinalistico' => $_POST['cmbFinalistico'] == '#' ? '00' : $_POST['cmbFinalistico'],
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
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iProduto' => $_POST['inputProdutoId']
						));

		$sql = "SELECT PrOrcId
				FROM ProdutoOrcamento
				WHERE PrOrcProduto = ".$_POST['inputProdutoId'];
		$result = $conn->query($sql);
		$rowProdutoOrcamento = $result->fetchAll(PDO::FETCH_ASSOC);							
		$count = count($rowProdutoOrcamento);
		
		if ($count){
			$sql = "UPDATE ProdutoOrcamento SET PrOrcDetalhamento = :sDetalhamento, PrOrcUsuarioAtualizador = :iUsuarioAtualizador
					WHERE PrOrcProduto = :iProduto and PrOrcEmpresa = :iEmpresa";
			$result = $conn->prepare($sql);

			$result->execute(array(
						':sDetalhamento' => $_POST['txtDetalhamento'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iProduto' => $_POST['inputProdutoId'],
						':iEmpresa' => $_SESSION['EmpreId']
						));
		}				

		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Produto alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {	
		
		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar produto!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		//$result->debugDumpParams();
		
		echo 'Error: ' . $e->getMessage();		
		exit;
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
		
		//Ao carregar a página tive que executar o que o onChange() executa para que a combo da SubCategoria já venha filtrada, além de selecionada, é claro.
		window.onload = function(){

			var cmbSubCategoria = $('#cmbSubCategoria').val();
			
			Filtrando();
			
			var cmbCategoria = $('#cmbCategoria').val();

			$.getJSON('filtraSubCategoria.php?idCategoria='+cmbCategoria, function (dados){
				
				var option = '<option value="#">Selecione a SubCategoria</option>';
				
				if (dados.length){						
					
					$.each(dados, function(i, obj){

						if(obj.SbCatId == cmbSubCategoria){							
							option += '<option value="'+obj.SbCatId+'" selected>'+obj.SbCatNome+'</option>';
						} else {							
							option += '<option value="'+obj.SbCatId+'">'+obj.SbCatNome+'</option>';
						}
					});
					
					$('#cmbSubCategoria').html(option).show();
				} else {
					Reset();
				}					
			});
		}

        $(document).ready(function() {
			//Aqui sou obrigado a instanciar novamente a utilização do fancybox
			$(".fancybox").fancybox({
				// options
			});

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
			
			$('#alterar').on('click', (e)=>{
				e.preventDefault()
				
				$('#cmbCategoria').removeAttr('disabled')
				$('#cmbSubCategoria').removeAttr('disabled')

				$('#formProduto').submit()
			})
		
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
					
					<form id="formProduto" name="formProduto" method="post" class="form-validate-jquery" action="produtoEdita.php">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Produto "<?php echo $row['ProduNome']; ?>"</h5>
						</div>
						
						<input type="hidden" id="inputProdutoId" name="inputProdutoId" value="<?php echo $row['ProduId']; ?>" >
						<input type="hidden" id="inputProdutoStatus" name="inputProdutoStatus" value="<?php echo $row['SituaChave']; ?>" >
						
						<div class="card-body">
							
							<div class="media">
								
								<div class="media-body">

									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputCodigo">Código do Produto</label>
												<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código Interno" value="<?php echo $row['ProduCodigo']; ?>" readOnly>
											</div>
										</div>	
										
										<div class="col-lg-4">
											<div class="form-group">
												<label for="inputCodigoBarras">Código de Barras</label>
												<input type="text" id="inputCodigoBarras" name="inputCodigoBarras" class="form-control" placeholder="Código de Barras" value="<?php echo $row['ProduCodigoBarras']; ?>">
											</div>	
										</div>
										
										<div class="col-lg-4">
											<div class="form-group">				
												<label for="inputEstoqueMinimo">Estoque Mínimo</label>
												<input type="text" id="inputEstoqueMinimo" name="inputEstoqueMinimo" class="form-control" placeholder="Estoque Mínimo" value="<?php echo $row['ProduEstoqueMinimo']; ?>">
											</div>	
										</div>								
									</div>
								
									<div class="row">								
										<div class="col-lg-12">
											<div class="form-group">
												<label for="inputNome">Nome <span class="text-danger">*</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" value="<?php echo htmlentities($row['ProduNome'],ENT_QUOTES); ?>" required>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="txtDetalhamento">Detalhamento</label>
												<textarea rows="5" cols="5" class="form-control" id="txtDetalhamento" name="txtDetalhamento" placeholder="Detalhamento do produto"><?php echo $row['ProduDetalhamento']; ?></textarea>
											</div>
										</div>
									</div>
									
								</div> <!-- media-body -->									
									
								<div style="text-align:center;">
									<div id="visualizar">
										<a href="<?php echo $sFoto; ?>" class="fancybox">
											<img class="ml-3" src="<?php echo $sFoto; ?>" style="max-height:250px; border:2px solid #ccc;">
										</a>
										<input type="hidden" id="inputFoto" name="inputFoto" value="<?php echo $row['ProduFoto']; ?>" >
									</div>
									<br>
									<button id="addFoto" class="ml-3 btn btn-lg btn-principal" style="width:90%"><?php echo $sButtonFoto; ?></button>									
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
												<select id="cmbCategoria" name="cmbCategoria" class="form-control select-search" required <?php $contTRs >= 1 ? print('disabled') : ''?>>
													<option value="">Selecione</option>
													<?php 
														$sql = "SELECT CategId, CategCodigo, CategNome
																FROM Categoria
																JOIN Situacao on SituaId = CategStatus
																WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
																ORDER BY CategNome ASC";
														$result = $conn->query($sql);
														$rowCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($rowCategoria as $item){
															$seleciona = $item['CategId'] == $row['ProduCategoria'] ? "selected" : "";
															print('<option value="'.$item['CategId'].'" data-codcategoria="'.$item['CategCodigo'].'" '. $seleciona .'>'.$item['CategCodigo'].' - '.$item['CategNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbSubCategoria">SubCategoria</label>
												<select id="cmbSubCategoria" name="cmbSubCategoria" class="form-control select-search" <?php $contTRs >= 1 ? print('disabled') : ''?>>
													<option value="#">Selecione</option>
													<?php 
														$sql = "SELECT SbCatId, SbCatCodigo, SbCatNome
																FROM SubCategoria
																JOIN Situacao on SituaId = SbCatStatus
																WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
																ORDER BY SbCatNome ASC";
														$result = $conn->query($sql);
														$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($rowSubCategoria as $item){
															$seleciona = $item['SbCatId'] == $row['ProduSubCategoria'] ? "selected" : "";
															print('<option value="'.$item['SbCatId'].'" '. $seleciona .'>'.$item['SbCatCodigo'].' - '.$item['SbCatNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputFamilia">Família</label>
												<input type="text" id="inputFamilia" name="inputFamilia" class="form-control" placeholder="Família" value="<?php echo $row['ProduFamilia']; ?>" readOnly>
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
														$seleciona = $item['FinalId'] == $row['ProduFinalistico'] ? "selected" : "";
														print('<option value="'.$item['FinalId'].'" '. $seleciona .'>'.$item['FinalCodigo'] . ' - ' . $item['FinalNome'] .'</option>');
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
													<option value="VM" <?php if ($row['ProduCor'] == 'VM') echo "selected"; ?> >Vermelho</option>
													<option value="AZ" <?php if ($row['ProduCor'] == 'AZ') echo "selected"; ?> >Azul</option>
													<option value="AM" <?php if ($row['ProduCor'] == 'AM') echo "selected"; ?> >Amarelo</option>
													<option value="VD" <?php if ($row['ProduCor'] == 'VD') echo "selected"; ?> >Verde</option>
													<option value="LA" <?php if ($row['ProduCor'] == 'LA') echo "selected"; ?> >Laranja</option>
													<option value="RO" <?php if ($row['ProduCor'] == 'RO') echo "selected"; ?> >Roxo</option>
													<option value="PR" <?php if ($row['ProduCor'] == 'PR') echo "selected"; ?> >Preto</option>
													<option value="BR" <?php if ($row['ProduCor'] == 'BR') echo "selected"; ?> >Branco</option>
												</select>
											</div>
										</div>

										<div class="col-lg-2">
											<div class="form-group">
												<label for="cmbTamanho">Tamanho</label>
												<select id="cmbTamanho" name="cmbTamanho" class="form-control select-search">
													<option value="#">Selecione</option>
													<option value="PE" <?php if ($row['ProduTamanho'] == 'PE') echo "selected"; ?> >Pequeno</option>
													<option value="GR" <?php if ($row['ProduTamanho'] == 'GR') echo "selected"; ?> >Grande</option>
													<option value="ME" <?php if ($row['ProduTamanho'] == 'ME') echo "selected"; ?> >Médio</option>

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
														$rowUnidadeMedida = $result->fetchAll(PDO::FETCH_ASSOC);

														foreach ($rowUnidadeMedida as $item){
															$seleciona = $item['UnMedId'] == $row['ProduUnidadeMedida'] ? "selected" : "";
															print('<option value="'.$item['UnMedId'].'" '. $seleciona .'>'.$item['UnMedNome'] . ' (' . $item['UnMedSigla'] . ')' .'</option>');
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
														$rowUnidadeMedida = $result->fetchAll(PDO::FETCH_ASSOC);

														foreach ($rowUnidadeMedida as $item){
															$seleciona = $item['UnMedId'] == $row['ProduUnidadeMedidaSaida'] ? "selected" : "";
															print('<option value="'.$item['UnMedId'].'" '. $seleciona .'>'.$item['UnMedNome'] . ' (' . $item['UnMedSigla'] . ')' .'</option>');
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
												<input type="text" id="inputValorCusto" name="inputValorCusto" class="form-control" placeholder="Valor de Custo" value="<?php echo $valorCusto; ?>"  <?php echo $travado; ?> onKeyUp="moeda(this)" maxLength="12" >
											</div>
										</div>
										
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputOutrasDespesas">Outras Despesas</label>
												<input type="text" id="inputOutrasDespesas" name="inputOutrasDespesas" class="form-control" placeholder="Outras Despesas" value="<?php echo $outrasDespesas; ?>" <?php echo $travado; ?> onKeyUp="moeda(this)" maxLength="12">
											</div>
										</div>			
										
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputCustoFinal">Custo Final</label>
												<input type="text" id="inputCustoFinal" name="inputCustoFinal" class="form-control" placeholder="Custo Final" value="<?php echo $custoFinal; ?>" readOnly>
											</div>
										</div>
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputMargemLucro">Margem de Lucro (%)</label>
												<input type="text" id="inputMargemLucro" name="inputMargemLucro" class="form-control" placeholder="Margem Lucro" value="<?php echo $margemLucro; ?>" <?php echo $travado; ?> onKeyUp="moeda(this)" maxLength="6">
											</div>
										</div>										
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputValorVenda">Valor de Venda</label>
												<input type="text" id="inputValorVenda" name="inputValorVenda" class="form-control" placeholder="Valor de Venda" value="<?php echo $valorVenda; ?>" <?php echo $travado; ?> onKeyUp="moeda(this)" maxLength="12">
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
														$rowTipoFiscal = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($rowTipoFiscal as $item){
															$seleciona = $item['TpFisId'] == $row['ProduTipoFiscal'] ? "selected" : "";
															print('<option value="'.$item['TpFisId'].'" '. $seleciona .'>'.$item['TpFisNome'].'</option>');
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
														$rowOrigemFiscal = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($rowOrigemFiscal as $item){
															$seleciona = $item['OrFisId'] == $row['ProduOrigemFiscal'] ? "selected" : "";
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
														$sql = "SELECT NcmId, NcmCodigo, NcmNome
																FROM Ncm
																JOIN Situacao on SituaId = NcmStatus
																WHERE SituaChave = 'ATIVO'
																ORDER BY NcmCodigo ASC";
														$result = $conn->query($sql);
														$rowNcmFiscal = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($rowNcmFiscal as $item){
															$seleciona = $item['NcmId'] == $row['ProduNcmFiscal'] ? "selected" : "";
															print('<option value="'.$item['NcmId'].'">'.$item['NcmCodigo'] . " - " . $item['NcmNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>
										
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputCest">CEST</label>
												<input type="text" id="inputCest" name="inputCest" class="form-control" placeholder="CEST"value="<?php echo $row['ProduCest']; ?>">
											</div>
										</div>	
									</div> <!-- /row -->
							
								</div> <!-- /col -->
							</div>	<!-- /row -->

							<div class="row" style="margin-top: 40px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<?php
											if ($_POST['inputPermission']) {
												echo '<button id="alterar" class="btn btn-lg btn-principal" type="submit">Alterar</button>';
											}
										?>	
										<a href="produto.php" class="btn btn-basic" role="button">Cancelar</a>
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
