<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Editar Orçamento';

include('global_assets/php/conexao.php');

$sql = "SELECT TrRefCategoria
		FROM TermoReferencia
		JOIN Categoria on CategId = TrRefCategoria
		WHERE TrRefUnidade = ". $_SESSION['UnidadeId'] ." and TrRefId = ".$_SESSION['TRId']."";
$result = $conn->query($sql);
$categoriaId = $result->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT CategId, CategNome
		FROM Categoria
		JOIN Situacao on SituaId = CategStatus
		WHERE CategEmpresa = ".$_SESSION['EmpreId']." and CategId = ".$categoriaId['TrRefCategoria']." and SituaChave = 'ATIVO' ";
$result = $conn->query($sql);
$rowCategoria = $result->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT SbCatId, SbCatNome
		FROM SubCategoria
		JOIN TRXSubcategoria on TRXSCSubcategoria = SbCatId
		WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ." and TRXSCTermoReferencia = ".$_SESSION['TRId']."
		ORDER BY SbCatNome ASC";
$result = $conn->query($sql);
$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);

//Se veio do orcamento.php
if(isset($_POST['inputOrcamentoId'])){
	
	$iOrcamento = $_POST['inputOrcamentoId'];
			
	$sql = "SELECT TrXOrId, TrXOrNumero, TrXOrData, TrXOrConteudo, TrXOrFornecedor, SituaChave,
					ForneId, ForneContato, ForneEmail, ForneTelefone, ForneCelular, TrXOrSolicitante, UsuarNome, UsuarEmail, UsuarTelefone
			FROM TRXOrcamento
			JOIN Usuario on UsuarId = TrXOrSolicitante
			JOIN TermoReferencia on TrRefId = TrXOrTermoReferencia
			JOIN Situacao  ON SituaId = TrRefStatus
			LEFT JOIN Fornecedor on ForneId = TrXOrFornecedor
			WHERE TrXOrId = $iOrcamento ";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT SbCatId, SbCatNome
			FROM SubCategoria
			JOIN TRXOrcamentoXSubcategoria on TXOXSCSubcategoria = SbCatId
			WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ." and TXOXSCOrcamento = $iOrcamento
			ORDER BY SbCatNome ASC";
	$result = $conn->query($sql);
	$rowBD = $result->fetchAll(PDO::FETCH_ASSOC);
	
	$aSubCategorias = '';

	foreach ($rowBD as $item) {
		
		if ($aSubCategorias == '') {
			$aSubCategorias .= $item['SbCatId'];
		} else {
			$aSubCategorias .= ", ".$item['SbCatId'];
		}
	}
	
	$_SESSION['msg'] = array();

} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	irpara("trOrcamento.php");
}

if(isset($_POST['inputData'])){	

	try{
		
		$iOrcamento = $_POST['inputOrcamentoId'];
		
		$sql = "UPDATE TRXOrcamento SET TrXOrConteudo = :sConteudo, TrXOrCategoria = :iCategoria,
									 TrXOrFornecedor = :iFornecedor, TrXOrUsuarioAtualizador = :iUsuarioAtualizador
				WHERE TrXOrId = :iOrcamento";
		$result = $conn->prepare($sql);

		// Alterando a subcategoria
		
		$conn->beginTransaction();		

		$aFornecedor = explode("#",$_POST['cmbFornecedor']);
		$iFornecedor = $aFornecedor[0];
		
		$result->execute(array(
						':iCategoria' => $_POST['inputCategoria'] == '#' ? null : $_POST['inputCategoria'],
						//':iSubCategoria' => $_POST['cmbSubCategoria'] == '#' ? null : $_POST['cmbSubCategoria'],
						':sConteudo' => $_POST['txtareaConteudo'],
						':iFornecedor' => $iFornecedor,
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iOrcamento' => $iOrcamento
						));
        //$conn->beginTransaction();

        ////////////////////// Alterando a subcategoria\\\\\\\\\\\\\\\\\\
        $sql = "DELETE FROM TRXOrcamentoXSubcategoria
				WHERE TXOXSCOrcamento = :iOrcamento and TXOXSCUnidade = :iUnidade";
		$resultSubCatDel = $conn->prepare($sql);
        $resultSubCatDel->execute(array(
			            ':iOrcamento' => $iOrcamento,
						':iUnidade' => $_SESSION['UnidadeId']
						));

        foreach ($rowSubCategoria as $subcategoria) {

            $sql = "INSERT INTO TRXOrcamentoXSubcategoria (TXOXSCOrcamento, TXOXSCSubcategoria, TXOXSCUnidade) 
		            VALUES(:iOrcamento, :iSubCategoria, :iUnidade)";
		    $resultSubCatCadast = $conn->prepare($sql);
		    $resultSubCatCadast->execute(array(
			            ':iOrcamento' => $iOrcamento,
						':iSubCategoria' => $subcategoria['SbCatId'] == '#' ? null : $subcategoria['SbCatId'],
						':iUnidade' => $_SESSION['UnidadeId']
						));
        }
		
		if (isset($_POST['inputOrcamentoProdutoExclui']) and $_POST['inputOrcamentoProdutoExclui']){
			
			$sql = "DELETE FROM TRXOrcamentoXProduto
					WHERE TXOXPOrcamento = :iOrcamento and TXOXPUnidade = :iUnidade";
			$result = $conn->prepare($sql);	
			
			$result->execute(array(
								':iOrcamento' => $iOrcamento,
								':iUnidade' => $_SESSION['UnidadeId']));
		}

		$sql = "INSERT INTO AuditTR ( AdiTRTermoReferencia, AdiTRDataHora, AdiTRUsuario, AdiTRTela, AdiTRDetalhamento)
				VALUES (:iTRTermoReferencia, :iTRDataHora, :iTRUsuario, :iTRTela, :iTRDetalhamento)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
			':iTRTermoReferencia' => $_SESSION['TRId'],
			':iTRDataHora' => date("Y-m-d H:i:s"),
			':iTRUsuario' => $_SESSION['UsuarId'],
			':iTRTela' =>'ORÇAMENTO',
			':iTRDetalhamento' =>' MODIFICAÇÃO DO ORÇAMENTO DE Nº '. $row['TrXOrNumero']. ''
		));

		
		$conn->commit();						
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Orçamento alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
				
	} catch(PDOException $e) {		
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar orçamento!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		$conn->rollback();
		
		echo 'Error: ' . $e->getMessage();
		exit;
	}
	
	irpara("trOrcamento.php");
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

	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	<script src="global_assets/js/plugins/editors/summernote/summernote.min.js"></script>
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	<script src="global_assets/js/demo_pages/form_select2.js"></script>	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/inputs/inputmask.js"></script>

	<script src="global_assets/js/plugins/forms/selects/bootstrap_multiselect.js"></script>	

	<script src="global_assets/js/demo_pages/form_multiselect.js"></script>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>		

    <script type="text/javascript" >

        $(document).ready(function() {	
		
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

			$("#enviar").on('click', function(e){
				
				e.preventDefault();				
				
				//Antes
				var inputCategoria = $('#inputOrcamentoCategoria').val();
				var inputSubCategoria = $('#inputOrcamentoSubCategoria').val();
				if (inputSubCategoria == '' || inputSubCategoria == null){
					inputSubCategoria = '#';
				}
				
				//Depois
				var cmbCategoria = $('#cmbCategoria').val();
				var cmbSubCategoria = $('#c').val();
				
				if (cmbCategoria == '' || cmbCategoria == '#'){
					alerta('Atenção','Informe a categoria!','error');
					$('#cmbCategoria').focus();
					return false;
				}

				var cmbFornecedor = $('#cmbFornecedor').val(); 

				//Se o fornecedor não foi selecionado, força o envio do formulário para acionar a Validação dos campos obrigatórios
				if (cmbFornecedor == ""){
					$("#formOrcamento").submit();
					return false;
				}
				
				//Tem produto cadastrado para esse orçamento na tabela OrcamentoXProduto?
				var inputProduto = $('#inputOrcamentoProduto').val();
				
				//Exclui os produtos desse Orçamento?
				var inputExclui = $('#inputOrcamentoProdutoExclui').val();
				
				//Aqui verifica primeiro se tem produtos preenchidos, porque do contrário deixa mudar
				if (inputProduto > 0){

					//Verifica se o a categoria ou subcategoria foi alterada
					if (inputSubCategoria != cmbSubCategoria){

						inputExclui = 1;
						$('#inputOrcamentoProdutoExclui').val(inputExclui);
						
						confirmaExclusao(document.formOrcamento, "Tem certeza que deseja alterar o orçamento? Existem produtos e/ou serviços com valores já lançados!", "trOrcamentoEdita.php");
						
					} else{
						inputExclui = 0;
						$('#inputOrcamentoProdutoExclui').val(inputExclui);
					}
				}
				
				$( "#formOrcamento" ).submit();
				
			}); // enviar			
		}); //document.ready
		
		//Mostra o "Filtrando..." na combo SubCategoria
		function Filtrando(){
			$('#cmbSubCategoria').empty().append('<option value="#">Filtrando...</option>');
		}		
		
		function ResetSubCategoria(){
			$('#cmbSubCategoria').empty().append('<option value="#">Sem Subcategoria</option>');
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
					
					<form name="formOrcamento" id="formOrcamento" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Orçamento Nº "<?php echo $_POST['inputOrcamentoNumero']; ?>"</h5>
						</div>						
						
						<input type="hidden" id="inputOrcamentoId" name="inputOrcamentoId" value="<?php echo $row['TrXOrId']; ?>" >
						<input type="hidden" id="inputOrcamentoNumero" name="inputOrcamentoNumero" value="<?php echo $row['TrXOrNumero']; ?>" >	
						<input type="hidden" id="inputOrcamentoProdutoExclui" name="inputOrcamentoProdutoExclui" value="0" >
						
						<?php
						
							$sql = "SELECT TXOXPOrcamento
									FROM TRXOrcamentoXProduto
									WHERE TXOXPOrcamento = ".$iOrcamento." and TXOXPUnidade = ".$_SESSION['UnidadeId'];
							$result = $conn->query($sql);
							$rowProduto = $result->fetchAll(PDO::FETCH_ASSOC);
							$countProduto = count($rowProduto);

							print('<input type="hidden" id="inputOrcamentoProduto" name="inputOrcamentoProduto" value="'.$countProduto.'" >');
						?>
						
						<div class="card-body">								
								
							<div class="row">				
								<div class="col-lg-12">
									<div class="row">								
										
										<div class="col-lg-1">
											<div class="form-group">
												<label for="inputData">Data <span class="text-danger"> *</span></label>
												<input type="text" id="inputData" name="inputData" class="form-control" value="<?php echo mostraData($row['TrXOrData']); ?>" readOnly>
											</div>
										</div>									
										<div class="col-lg-4">
											<div class="form-group">
												<label for="cmbCategoria">Categoria <span class="text-danger"> *</span></label>
												<div class="d-flex flex-row" style="padding-top: 7px;">
													<input type="text" class="form-control pb-0" value="<?php echo $rowCategoria['CategNome'] ?>" readOnly>
													<input type="hidden" id="inputCategoria" name="inputCategoria" class="form-control pb-0" value="<?php echo $rowCategoria['CategId'] ?>">
												</div>
											</div>
										</div>
										<div class="col-lg-7">
											<div class="form-group">
												<label for="cmbSubCategoria">SubCategoria(s)</label>
												<select id="cmbSubCategoria" name="cmbSubCategoria" class="form-control multiselect-filtering" multiple="multiple" data-fouc>
													<?php 
														$sql = "SELECT SbCatId, SbCatNome
																FROM SubCategoria
																JOIN Situacao on SituaId = SbCatStatus	
																WHERE SbCatEmpresa = ". $_SESSION['EmpreId'] ." and SbCatId in (".$aSubCategorias.")
																ORDER BY SbCatNome ASC"; 
														$result = $conn->query($sql);
														$rowSubCategoria = $result->fetchAll(PDO::FETCH_ASSOC);
														$count = count($rowSubCategoria);														
																
														foreach ( $rowSubCategoria as $item){	
															print('<option value="'.$item['SbCatId,'].'"disabled selected>'.$item['SbCatNome'].'</option>');	
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
									<div class="form-group">
										<label for="txtareaConteudo">Conteúdo personalizado</label>
										<textarea rows="5" cols="5" class="form-control" id="summernote" name="txtareaConteudo" placeholder="Corpo do orçamento (informe aqui o texto que você queira que apareça no orçamento)"><?php echo $row['TrXOrConteudo']; ?></textarea>
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
												<label for="cmbFornecedor">Fornecedor <span class="text-danger"> *</span></label>
												<select id="cmbFornecedor" name="cmbFornecedor" class="form-control form-control-select2" required>
													<option value="">Selecione</option>
													<?php
														$sql = "SELECT ForneId, ForneNome, ForneContato, ForneEmail, ForneTelefone, ForneCelular
																FROM Fornecedor
																JOIN Situacao on SituaId = ForneStatus
																WHERE ForneEmpresa = ". $_SESSION['EmpreId'] ." and ForneCategoria = ".$rowCategoria['CategId']."
																and SituaChave = 'ATIVO'
															    ORDER BY ForneNome ASC";
														$result = $conn->query($sql);
														$fornecedores = $result->fetchAll(PDO::FETCH_ASSOC);
														foreach($fornecedores as $fornecedor){
															if($fornecedor['ForneId'] == $row['ForneId']){
																print('<option selected value="'.$fornecedor['ForneId'].'#'.$fornecedor['ForneContato'].'#'.$fornecedor['ForneEmail'].'#'.$fornecedor['ForneTelefone'].'#'.$fornecedor['ForneCelular'].'" '. $seleciona .'>'.$fornecedor['ForneNome'].'</option>');
															} else {
																print('<option value="'.$fornecedor['ForneId'].'#'.$fornecedor['ForneContato'].'#'.$fornecedor['ForneEmail'].'#'.$fornecedor['ForneTelefone'].'#'.$fornecedor['ForneCelular'].'" '. $seleciona .'>'.$fornecedor['ForneNome'].'</option>');
															}
														};
													?>
												</select>
											</div>
										</div>
										
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputContato">Contato</label>
												<input type="text" id="inputContato" name="inputContato" class="form-control" value="<?php echo $row['ForneContato']; ?>" readOnly>
											</div>
										</div>									

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputEmailFornecedor">E-mail</label>
												<input type="text" id="inputEmailFornecedor" name="inputEmailFornecedor" class="form-control" value="<?php echo $row['ForneEmail']; ?>" readOnly>
											</div>
										</div>									

										<div class="col-lg-2">
											<div class="form-group">
												<label for="inputTelefoneFornecedor">Telefone</label>
												<input type="text" id="inputTelefoneFornecedor" name="inputTelefoneFornecedor" class="form-control" value="<?php echo $row['ForneTelefone']; ?>" readOnly>
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
												<label for="inputNomeSolicitante">Solicitante <span class="text-danger"> *</span></label>
												<input type="text" id="inputNomeSolicitante" name="inputNomeSolicitante" class="form-control" value="<?php echo $row['UsuarNome']; ?>" readOnly>
											</div>
										</div>
										
										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputEmailSolicitante">E-mail <span class="text-danger"> *</span></label>
												<input type="text" id="inputEmailSolicitante" name="inputEmailSolicitante" class="form-control" value="<?php echo $row['UsuarEmail']; ?>" readOnly>
											</div>
										</div>									

										<div class="col-lg-3">
											<div class="form-group">
												<label for="inputTelefoneSolicitante">Telefone</label>
												<input type="text" id="inputTelefoneSolicitante" name="inputTelefoneSolicitante" class="form-control" value="<?php echo $row['UsuarTelefone']; ?>" readOnly>
											</div>
										</div>									
									</div>
								</div>
							</div>							

							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
                                        <?php 
											if ($row['SituaChave'] != 'FASEINTERNAFINALIZADA'){
												print('<div class="btn btn-lg btn-principal" id="enviar">Alterar</div>');
											}
										?>
											<a href="trOrcamento.php" class="btn btn-basic" role="button">Cancelar</a>
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
