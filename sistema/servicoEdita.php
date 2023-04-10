<?php 

include_once("sessao.php");

$_SESSION['PaginaAtual'] = 'Editar Servico';

include('global_assets/php/conexao.php');

if(isset($_POST['inputServicoId'])){

	$sql = "SELECT TRXSrTermoReferencia
			FROM TermoReferenciaXServico
			JOIN Servico on ServiId =TRXSrServico
			JOIN TermoReferencia on TrRefId =TRXSrTermoReferencia
			JOIN Situacao on Situaid = TrRefStatus
			WHERE TRXSrServico = ".$_POST['inputServicoId']." and 
			(SituaChave = 'LIBERADOCENTRO' or SituaChave = 'LIBERADOCONTABILIDADE' or SituaChave = 'FASEINTERNAFINALIZADA') and 
			TRXSrUnidade = ".$_SESSION['UnidadeId'];
	$result = $conn->query($sql);
	$rowTrs = $result->fetchAll(PDO::FETCH_ASSOC);
	$contTRs = count($rowTrs);

	
	$iServico = $_POST['inputServicoId'];
	
	try{
		
		$sql = "SELECT ServiId, ServiCodigo, ServiNome, ServiDetalhamento, ServiCategoria, ServiSubCategoria, ServiValorCusto,
					   ServiOutrasDespesas, ServiCustoFinal, ServiMargemLucro, ServiValorVenda, SituaChave
				FROM Servico
				JOIN Situacao on SituaId = ServiStatus
				WHERE ServiId = $iServico ";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);		
		
		$valorCusto = mostraValor($row['ServiValorCusto']);
		$valorVenda	= mostraValor($row['ServiValorVenda']);
		$outrasDespesas = mostraValor($row['ServiOutrasDespesas']);
		$custoFinal = mostraValor($row['ServiCustoFinal']);
		$margemLucro = mostraValor($row['ServiMargemLucro']);
		//$numSerie = $row['ServiNumSerie'];

		/* Verifica se tem Ordem de Compra ou Fluxo para esse Servico (de acordo com o parâmetro) */
		$sql = "SELECT ParamValorAtualizadoFluxo, ParamValorAtualizadoOrdemCompra
				FROM Parametro				
				WHERE ParamEmpresa = ".$_SESSION['EmpreId'];
		$result = $conn->query($sql);
		$rowParamentro = $result->fetch(PDO::FETCH_ASSOC);

		if ($rowParamentro['ParamValorAtualizadoFluxo']){
			$sql = "SELECT COUNT(FOXSrServico) as CONT
					FROM FluxoOperacionalXServico
					JOIN FluxoOperacional on FlOpeId = FOXSrFluxoOperacional
					JOIN Situacao on SituaId = FlOpeStatus
					WHERE FOXSrServico = ".$iServico." and SituaChave = 'ATIVO' ";
			$result = $conn->query($sql);
			$rowExiste = $result->fetch(PDO::FETCH_ASSOC);
		} else if ($rowParamentro['ParamValorAtualizadoOrdemCompra']){
			$sql = "SELECT COUNT(OCXSrServico) as CONT
					FROM OrdemCompraXServico
					JOIN OrdemCompra on OrComId = OCXSrOrdemCompra
					JOIN Situacao on SituaId = OrComSituacao
					WHERE OCXSrServico = ".$iServico." and SituaChave = 'LIBERADO' ";
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
	
	header("Location: servico.php");
	//irpara("servico.php");
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
		if ($_POST['inputServicoStatus'] == 'ALTERAR'){
			$Status = $rowSituacao['SituaId'];
		}
		
		$sql = "UPDATE Servico SET ServiCodigo = :sCodigo, ServiNome = :sNome, ServiDetalhamento = :sDetalhamento, 
		               ServiCategoria = :iCategoria, ServiSubCategoria = :iSubCategoria, ServiValorCusto = :fValorCusto, 
					   ServiOutrasDespesas = :fOutrasDespesas, ServiCustoFinal = :fCustoFinal, 
					   ServiMargemLucro = :fMargemLucro, ServiValorVenda = :fValorVenda, 
					   ServiUsuarioAtualizador = :iUsuarioAtualizador ";

		if ($_POST['inputServicoStatus'] == 'ALTERAR'){
			$sql .= ", ServiStatus = ".$Status." ";
		}
		
		$sql .= "      WHERE ServiId = :iServico";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':sCodigo' => $_POST['inputCodigo'],
						':sNome' => $_POST['inputNome'],
						':sDetalhamento' => $_POST['txtDetalhamento'],
						':iCategoria' => $_POST['cmbCategoria'],
						':iSubCategoria' => $_POST['cmbSubCategoria'] == '#' ? null : $_POST['cmbSubCategoria'],
						':fValorCusto' => $_POST['inputValorCusto'] == null ? null : gravaValor($_POST['inputValorCusto']),						
						':fOutrasDespesas' => $_POST['inputOutrasDespesas'] == null ? null : gravaValor($_POST['inputOutrasDespesas']),
						':fCustoFinal' => $_POST['inputCustoFinal'] == null ? null : gravaValor($_POST['inputCustoFinal']),
						':fMargemLucro' => $_POST['inputMargemLucro'] == null ? null : gravaValor($_POST['inputMargemLucro']),
						':fValorVenda' => $_POST['inputValorVenda'] == null ? null : gravaValor($_POST['inputValorVenda']),
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iServico' => $_POST['inputServicoId']
						));

		$sql = "SELECT SrOrcId
				FROM ServicoOrcamento
				WHERE SrOrcServico = ".$_POST['inputServicoId'];
		$result = $conn->query($sql);
		$rowServicoOrcamento = $result->fetch(PDO::FETCH_ASSOC);							
		$count = count($rowServicoOrcamento);
		
		if ($count){

			$sql = "UPDATE ServicoOrcamento SET SrOrcDetalhamento = :sDetalhamento, SrOrcUsuarioAtualizador = :iUsuarioAtualizador
					WHERE SrOrcServico = :iServico and SrOrcEmpresa = :iEmpresa";
			$result = $conn->prepare($sql);

			$result->execute(array(
						':sDetalhamento' => $_POST['txtDetalhamento'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iServico' => $_POST['inputServicoId'],
						':iEmpresa' => $_SESSION['EmpreId']
						));
		}							
		
		$conn->commit();
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Serviço alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {	
		
		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar serviço!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		//$result->debugDumpParams();
		
		echo 'Error: ' . $e->getMessage();		
		exit;
	}
	
	irpara("servico.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Serviço</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

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

			//Limpa o campo Nome quando for digitado só espaços em branco
			$("#inputNome").on('blur', function(e){

				var inputNome = $('#inputNome').val();

				inputNome = inputNome.trim();
				
				if (inputNome.length == 0){
					$('#inputNome').val('');
					//$("#formServico").submit(); //Isso aqui é para submeter o formulário, validando os campos obrigatórios novamente
				}
			});
	
			//Ao mudar a categoria, filtra a subcategoria via ajax (retorno via JSON)
			$('#cmbCategoria').on('change', function(e){
				
				Filtrando();
				
				var cmbCategoria = $('#cmbCategoria').val();

				$.getJSON('filtraSubCategoria.php?idCategoria='+cmbCategoria, function (dados){
					
					var option = '<option value="#">Selecione a SubCategoria</option>';
					
					if (dados.length){						
						
						$.each(dados, function(i, obj){
							option += '<option value="'+obj.SbCatId+'">'+obj.SbCatNome+'</option>';
						});						
						
						$('#cmbSubCategoria').html(option).show();
					} else {
						Reset();
					}					
				});
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

				$('#formServico').submit()
			})
		
		});

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
					
					<form id="formServico" name="formServico" method="post" class="form-validate-jquery" action="servicoEdita.php">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Serviço "<?php echo $row['ServiNome']; ?>"</h5>
						</div>
						
						<input type="hidden" id="inputServicoId" name="inputServicoId" value="<?php echo $row['ServiId']; ?>" >
						<input type="hidden" id="inputServicoStatus" name="inputServicoStatus" value="<?php echo $row['SituaChave']; ?>" >
						
						<div class="card-body">
							
							<div class="media">
								
								<div class="media-body">

									<div class="row">
										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputCodigo">Código do Serviço</label>
												<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código Interno" value="<?php echo $row['ServiCodigo']; ?>" readOnly>
											</div>
										</div>
										<div class="col-lg-10">
											<div class="form-group">
												<label for="inputNome">Nome <span class="text-danger">*</span></label>
												<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" value="<?php echo $row['ServiNome']; ?>" required>
											</div>
										</div>																								
									</div>

									<div class="row">
										<div class="col-lg-12">
											<div class="form-group">
												<label for="txtDetalhamento">Detalhamento</label>
												<textarea rows="5" cols="5" class="form-control" id="txtDetalhamento" name="txtDetalhamento" placeholder="Detalhamento do serviço"><?php echo $row['ServiDetalhamento']; ?></textarea>
											</div>
										</div>
									</div>
									
								</div> <!-- media-body -->																
									
							</div> <!-- media -->

							<div class="row">
								<div class="col-lg-12">
									<h5 class="mb-0 font-weight-semibold">Classificação</h5>
									<br>
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbCategoria">Categoria <span class="text-danger">*</span></label>
												<select id="cmbCategoria" name="cmbCategoria" class="form-control form-control-select2" required <?php $contTRs >= 1 ? print('disabled') : ''?>>
													<option value="">Selecione</option>
													<?php 
														$sql = "SELECT CategId, CategNome
																FROM Categoria
																JOIN Situacao on SituaId = CategStatus
																WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
																ORDER BY CategNome ASC";
														$result = $conn->query($sql);
														$rowCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($rowCategoria as $item){
															$seleciona = $item['CategId'] == $row['ServiCategoria'] ? "selected" : "";
															print('<option value="'.$item['CategId'].'" '. $seleciona .'>'.$item['CategNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>

										<div class="col-lg-6">
											<div class="form-group">
												<label for="cmbSubCategoria">SubCategoria</label>
												<select id="cmbSubCategoria" name="cmbSubCategoria" class="form-control form-control-select2" <?php $contTRs >= 1 ? print('disabled') : ''?>>
													<option value="#">Selecione</option>
													<?php 
														$sql = "SELECT SbCatId, SbCatNome
																FROM SubCategoria
																JOIN Situacao on SituaId = SbCatStatus
																WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
																ORDER BY SbCatNome ASC";
														$result = $conn->query($sql);
														$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($rowSubCategoria as $item){
															$seleciona = $item['SbCatId'] == $row['ServiSubCategoria'] ? "selected" : "";
															print('<option value="'.$item['SbCatId'].'" '. $seleciona .'>'.$item['SbCatNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
									
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
												<input type="text" id="inputValorCusto" name="inputValorCusto" class="form-control" placeholder="Valor de Custo" value="<?php echo $valorCusto; ?>" <?php echo $travado; ?> onKeyUp="moeda(this)" maxLength="12">
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
							<div class="row" style="margin-top: 40px;">
								<div class="col-lg-12">								
									<div class="form-group">
									<?php
										if ($_POST['inputPermission']) {
											echo '<button id="alterar" class="btn btn-lg btn-principal" type="submit">Alterar</button>';
										}
									?>	
										<a href="servico.php" class="btn btn-basic" role="button">Cancelar</a>
									</div>
								</div>
							</div>

						</div>
						<!-- /card-body -->

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
