<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Novo Orçamento';

include('global_assets/php/conexao.php');

$sql = "SELECT UsuarId, UsuarNome, UsuarEmail, UsuarTelefone
		FROM Usuario
		Where UsuarId = ".$_SESSION['UsuarId']."
		ORDER BY UsuarNome ASC";
$result = $conn->query($sql);
$rowUsuario = $result->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['inputData'])){
	
	try{
		
		$sql = "SELECT COUNT(isnull(OrcamNumero,0)) as Numero
				FROM Orcamento
				Where OrcamUnidade = ".$_SESSION['UnidadeId']."";
		$result = $conn->query($sql);
		$rowNumero = $result->fetch(PDO::FETCH_ASSOC);		
		
		$sNumero = (int)$rowNumero['Numero'] + 1;
		$sNumero = str_pad($sNumero,6,"0",STR_PAD_LEFT);
			
		$sql = "INSERT INTO Orcamento (OrcamNumero, OrcamTipo, OrcamData, OrcamCategoria, OrcamConteudo, OrcamFornecedor,
									   OrcamSolicitante, OrcamStatus, OrcamUsuarioAtualizador, OrcamUnidade, OrcamEmpresa)
				VALUES (:sNumero, :sTipo, :dData, :iCategoria,:sConteudo, :iFornecedor, :iSolicitante, 
						:bStatus, :iUsuarioAtualizador, :iUnidade, :iEmpresa)";
		$result = $conn->prepare($sql);
		
		$aFornecedor = explode("#",$_POST['cmbFornecedor']);
		$iFornecedor = $aFornecedor[0];
		
		$result->execute(array(
						':sNumero' => $sNumero,
						':sTipo' => $_POST['inputTipo'],
						':dData' => gravaData($_POST['inputData']),
						':iCategoria' => $_POST['cmbCategoria'] == '#' ? null : $_POST['cmbCategoria'],
						//':iSubCategoria' => $_POST['cmbSubCategoria'] == '#' ? null : $_POST['cmbSubCategoria'],
						':sConteudo' => $_POST['txtareaConteudo'],
						':iFornecedor' => $iFornecedor,
						':iSolicitante' => $_SESSION['UsuarId'],
						':bStatus' => 1,
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iUnidade' => $_SESSION['UnidadeId'],
						':iEmpresa' => $_SESSION['EmpreId']
						));
		$insertId = $conn->lastInsertId(); 


		if (isset($_POST['cmbSubCategoria'])){
			
			try{
				$sql = "INSERT INTO OrcamentoXSubCategoria 
							(OrXSCOrcamento, OrXSCSubCategoria, OrXSCUnidade)
						VALUES 
							(:iOrcamento, :iSubCategoria, :iUnidade)";
				$result = $conn->prepare($sql);

				foreach ($_POST['cmbSubCategoria'] as $key => $value){

					$result->execute(array(
									':iOrcamento' => $insertId,
									':iSubCategoria' => $value,
									':iUnidade' => $_SESSION['UnidadeId']
									));
				}
				
			} catch(PDOException $e) {
				$conn->rollback();
				echo 'Error: ' . $e->getMessage();exit;
			}
		}
/*		$insertId = $conn->lastInsertId();
		
		$sql = "UPDATE Orcamento SET OrcamNumero = :sNumero
				WHERE OrcamId = :iOrcamento";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':sNumero' => str_pad($insertId,6,"0",STR_PAD_LEFT);
						':iOrcamento' => $insertId,
						));
*/		

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Orçamento incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir orçamento!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();die;
	}
	
	irpara("orcamento.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Orçamento</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>

	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	
	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	<!-- /theme JS files -->
	
	<!-- Adicionando Javascript -->
    <script type="text/javascript" >

        $(document).ready(function() {	


            // Ao usar a validação de formulário junto com a seleção multipla de subcategorias ocorreu um erro, onde o aviso de capo obrigatório
            // crebava o layout do form-group da subcategoria, deixando a borda do elemento abaixo de se, o que causa um efeito estranho no visual.
            // O código a baixo corrige isso.
            /**/
        	$('.select2-selection').css('border-bottom', '1px solid #ddd')
        	$('.form-group').each((i, item)=>{
        		$(item).css('border-bottom', '0px')
        	})
        	/**/
		
			$('#summernote').summernote();
			
			//Ao informar o fornecedor, trazer os demais dados dele (contato, e-mail, telefone)
			$('#cmbFornecedor').on('change', function(e){				
				
				var Fornecedor = $('#cmbFornecedor').val();
				var Forne = Fornecedor.split('#');
				
				$('#inputContato').val(Forne[1]);
				$('#inputEmailFornecedor').val(Forne[2]);
				if(Forne[3] != "" && Forne[3] != "(__) ____-____"){
					$('#inputTelefoneFornecedor').val(Forne[3]);
				} else {
					$('#inputTelefoneFornecedor').val(Forne[4]);
				}
			});
			
			//Ao mudar a categoria, filtra a subcategoria e produto via ajax (retorno via JSON)
			$('#cmbCategoria').on('change', function(e){
				
				Filtrando();
				
				var cmbCategoria = $('#cmbCategoria').val();

				$.getJSON('filtraSubCategoria.php?idCategoria='+cmbCategoria, function (dados){
					
					var option = null;
					
					if (dados.length){						
						
						$.each(dados, function(i, obj){
							option += '<option value="'+obj.SbCatId+'">'+obj.SbCatNome+'</option>';
						});						
						
						$('#cmbSubCategoria').html(option).show();
					} else {
						ResetSubCategoria();
					}					
				});
                
				
				$.getJSON('filtraFornecedor.php?idCategoria='+cmbCategoria, function (dados){
					
					var option = '<option value="">Selecione o Fornecedor</option>';
					
					if (dados.length){						
						
						$.each(dados, function(i, obj){
							option += '<option value="'+obj.ForneId+'#'+obj.ForneContato+'#'+obj.ForneEmail+'#'+obj.ForneTelefone+'#'+obj.ForneCelular+'">'+obj.ForneNome+'</option>';
						});						
						
						$('#cmbFornecedor').html(option).show();
					} else {
						ResetFornecedor();
					}					
				});				
				
			});

			 // Limpa os campos de fornecedor quando uma nova categoria é selecionada
			$('#cmbCategoria').on('change', function(){
				let inputContato = $('#inputContato')
				let inputEmailFornecedor = $('#inputEmailFornecedor')
				let inputTelefoneFornecedor = $('#inputTelefoneFornecedor')

				if(inputContato.val() || inputEmailFornecedor.val() || inputTelefoneFornecedor.val()){
                    inputContato.val('')
                    inputEmailFornecedor.val('')
                    inputTelefoneFornecedor.val('')
				} 
			})
			
			$("#enviar").on('click', function(e){
				
				e.preventDefault();	
				
				var cmbCategoria = $('#cmbCategoria').val();
				
				/*if (cmbCategoria == '' || cmbCategoria == '#'){
					alerta('Atenção','Informe a categoria!','error');
					$('#cmbCategoria').focus();
					return false;
				}*/
			
				$("#formOrcamento").submit();
			});
						
		}); //document.ready
		
		//Mostra o "Filtrando..." na combo SubCategoria
		function Filtrando(){
			$('#cmbSubCategoria').empty().append('<option value="">Filtrando...</option>');
		}
		
		function ResetSubCategoria(){
			$('#cmbSubCategoria').empty().append('<option value="">Sem Subcategoria</option>');
		}	
		
		function ResetFornecedor(){
			$('#cmbFornecedor').empty().append('<option value="">Sem Fornecedor</option>');
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
					
					<form name="formOrcamento" id="formOrcamento" method="post" class="form-validate-jquery" action="orcamentoNovo.php">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Orçamento</h5>
						</div>
						<div class="card-body">								
								
							<div class="row">				
								<div class="col-lg-12">
									<div class="row">
										
										<div class="col-lg-3">
											<div class="form-group">							
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" id="inputTipo" name="inputTipo" value="P" class="form-input-styled" checked data-fouc>
														Produto
													</label>
												</div>
												<div class="form-check form-check-inline">
													<label class="form-check-label">
														<input type="radio" id="inputTipo" name="inputTipo" value="S" class="form-input-styled" data-fouc>
														Serviço
													</label>
												</div>										
											</div>
										</div>										
										
										<div class="col-lg-1">
											<div class="form-group">
												<label for="inputData">Data</label>
												<input type="text" id="inputData" name="inputData" class="form-control" value="<?php echo date('d/m/Y'); ?>" readOnly>
											</div>
										</div>
																				
										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbCategoria">Categoria</label>
												<select id="cmbCategoria" name="cmbCategoria" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<?php 
														$sql = "SELECT CategId, CategNome
																FROM Categoria
																JOIN Situacao on SituaId = CategStatus						     
																WHERE CategEmpresa = ".$_SESSION['EmpreId']." and SituaChave = 'ATIVO'
															    ORDER BY CategNome ASC";
														$result = $conn->query($sql);
														$row = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($row as $item){															
															print('<option value="'.$item['CategId'].'">'.$item['CategNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>
										<div class="col-lg-4">
											<div class="form-group" style="border-bottom:1px solid #ddd;">
												<label for="cmbSubCategoria">SubCategoria</label>
												<select id="cmbSubCategoria" name="cmbSubCategoria[]" class="form-control select" multiple="multiple" data-fouc>
													<option value="#">Selecione</option>
												</select>
											</div>
										</div>										
									</div>
								</div>
							</div>
								
							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label for="txtareaConteudo">Conteúdo personalizado</label>
										<!--<div id="summernote" name="txtareaConteudo"></div>-->
										<textarea rows="5" cols="5" class="form-control" id="summernote" name="txtareaConteudo" placeholder="Corpo do orçamento (informe aqui o texto que você queira que apareça no orçamento)"></textarea>
									</div>
								</div>
							</div>		
							<br>
							
							<div class="row">
								<div class="col-lg-12">									
									<h5 class="mb-0 font-weight-semibold">Dados do Fornecedor</h5>
									<br>
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbFornecedor">Fornecedor</label>
												<select id="cmbFornecedor" name="cmbFornecedor" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<?php 
														$sql = "SELECT ForneId, ForneNome, ForneContato, ForneEmail, ForneTelefone, ForneCelular
																FROM Fornecedor
																JOIN Situacao on SituaId = ForneStatus
																WHERE ForneEmpresa = ". $_SESSION['EmpreId'] ." and SituaChave = 'ATIVO'
															    ORDER BY ForneNome ASC";
														$result = $conn->query($sql);
														$rowFornecedor = $result->fetchAll(PDO::FETCH_ASSOC);
														
														foreach ($rowFornecedor as $item){															
															print('<option value="'.$item['ForneId'].'#'.$item['ForneContato'].'#'.$item['ForneEmail'].'#'.$item['ForneTelefone'].'#'.$item['ForneCelular'].'">'.$item['ForneNome'].'</option>');
														}
													
													?>
												</select>
											</div>
										</div>
										
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputContato">Contato</label>
												<input type="text" id="inputContato" name="inputContato" class="form-control" readOnly>
											</div>
										</div>									

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputEmailFornecedor">E-mail</label>
												<input type="text" id="inputEmailFornecedor" name="inputEmailFornecedor" class="form-control" readOnly>
											</div>
										</div>									

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputTelefoneFornecedor">Telefone</label>
												<input type="text" id="inputTelefoneFornecedor" name="inputTelefoneFornecedor" class="form-control" readOnly>
											</div>
										</div>									
									</div>
								</div>
							</div>
							<br>
														
							<div class="row">
								<div class="col-lg-12">									
									<h5 class="mb-0 font-weight-semibold">Dados do Solicitante</h5>
									<br>
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="inputNomeSolicitante">Solicitante</label>
												<input type="text" id="inputNomeSolicitante" name="inputNomeSolicitante" class="form-control" value="<?php echo $rowUsuario['UsuarNome']; ?>" readOnly required>
											</div>
										</div>
										
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputEmailSolicitante">E-mail</label>
												<input type="text" id="inputEmailSolicitante" name="inputEmailSolicitante" class="form-control" value="<?php echo $rowUsuario['UsuarEmail']; ?>" readOnly>
											</div>
										</div>									

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputTelefoneSolicitante">Telefone</label>
												<input type="text" id="inputTelefoneSolicitante" name="inputTelefoneSolicitante" class="form-control" value="<?php echo $rowUsuario['UsuarTelefone']; ?>" readOnly>
											</div>
										</div>									
									</div>
								</div>
							</div>							

							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="orcamento.php" class="btn btn-basic" role="button">Cancelar</a>
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
