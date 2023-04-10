<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Editar Centro de Custo';

include('global_assets/php/conexao.php');

if(isset($_POST['inputCentroCustoId'])){
	
	$iCentroCusto = $_POST['inputCentroCustoId'];
		
	$sql = "SELECT CnCusId, CnCusCodigo, CnCusNome, CnCusNomePersonalizado, CnCusDetalhamento
			FROM CentroCusto
			WHERE CnCusId = $iCentroCusto ";
	$result = $conn->query($sql);
	$row = $result->fetch(PDO::FETCH_ASSOC);
	
	$_SESSION['msg'] = array();
} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	irpara("centroCusto.php");
}

if(isset($_POST['inputNome'])){
	
	try{
		
		$sql = "UPDATE CentroCusto SET CnCusCodigo = :iCodigo, CnCusNome = :sNome, CnCusNomePersonalizado = :sNomePersonalizado, 
				CnCusDetalhamento = :sDetalhamento, CnCusUsuarioAtualizador = :iUsuarioAtualizador
				WHERE CnCusId = :iCentroCusto";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':iCodigo' => $_POST['inputCodigo'],
						':sNome' => $_POST['inputNome'],
						':sNomePersonalizado' => isset($_POST['inputNomePersonalizado']) ? $_POST['inputNomePersonalizado'] : null,
						':sDetalhamento' => $_POST['txtDetalhamento'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iCentroCusto' => $_POST['inputCentroCustoId']
						));

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Centro de Custo alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar Centro de Custo!!!";
		$_SESSION['msg']['tipo'] = "error";		
		
		echo 'Error: ' . $e->getMessage();
	}
	
	irpara("centroCusto.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Centro de Custo</title>

	<?php include_once("head.php"); ?>

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
	
	<script type="text/javascript" >

        $(document).ready(function() {
			
			//Valida Registro Duplicado
			$('#enviar').on('click', function(e){

				e.preventDefault();
				
				var inputNomeNovo  = $('#inputNome').val();
				var inputNomeVelho = $('#inputCentroCustoNome').val();
				
				//remove os espaços desnecessários antes e depois
				inputNomeNovo = inputNomeNovo.trim();

				//Esse ajax está sendo usado para verificar no banco se o registro já existe
				
				$.ajax({
					type: "POST",
				    url: "centroCustoValida.php",
					data: ('nomeNovo='+inputNomeNovo+'&nomeVelho='+inputNomeVelho),
					success: function(resposta){
						
						if(resposta == 1){
							alerta('Atenção','Esse registro já existe!','error');
							return false;								
						}
						
						  $( "#formCentroCusto" ).submit();
					}
				})
				
			})
		})
	
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
					
					<form name="formCentroCusto" id="formCentroCusto" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar Centro de Custo "<?php echo $row['CnCusNome']; ?>"</h5>
						</div>
						<input type="hidden" id="inputCentroCustoId" name="inputCentroCustoId" value="<?php echo $row['CnCusId']; ?>" >
						<input type="hidden" id="inputCentroCustoNome" name="inputCentroCustoNome" value="<?php echo $row['CnCusNome']; ?>" >
						<div class="card-body">								
							<div class="row">
								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputCodigo">Código<span class="text-danger"> *</span></label>
										<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" value="<?php echo $row['CnCusCodigo']; ?>" required autofocus>
									</div>
								</div>
								<div class="col-lg-5">
									<div class="form-group">
										<label for="inputNome">Centro de Custo <?php if ($row['CnCusNome'] == 'Atendimento Eletivo' || $row['CnCusNome'] == 'Atendimento Ambulatorial'|| $row['CnCusNome'] == 'Atendimento Internação') { echo "(sugerido pelo sistema)"; } else { echo ""; }?><span class="text-danger"> *</span></label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Centro de Custo" value="<?php echo $row['CnCusNome']; ?>" required  <?php if ($row['CnCusNome'] == 'Atendimento Eletivo' || $row['CnCusNome'] == 'Atendimento Ambulatorial'|| $row['CnCusNome'] == 'Atendimento Internação') { echo "readonly"; } else { echo ""; }?> >
									</div>
								</div>
								<?php
									if ($row['CnCusNome'] == 'Atendimento Eletivo' || $row['CnCusNome'] == 'Atendimento Ambulatorial' || $row['CnCusNome'] == 'Atendimento Internação') {
									
										print('	<div class="col-lg-5">
											<div class="form-group">
												<label for="inputNomePersonalizado">Centro de Custo (nome personalizado)</label>
												<input type="text" id="inputNomePersonalizado" name="inputNomePersonalizado" class="form-control" placeholder="Título personalizado" value="'); echo $row['CnCusNomePersonalizado']; print('">
											</div>
										</div>');
									}
								?>	
							</div>
							<div class="row">
								<div class="col-lg-12">
									<label for="txtDetalhamento">Detalhamento</label>
									<textarea id="txtDetalhamento" name="txtDetalhamento" class="form-control" placeholder="Detalhamento do Centro de Custo" rows="7" cols="5" ><?php echo $row['CnCusDetalhamento']; ?></textarea>
								</div>
							</div>                 
								
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>
										<a href="centroCusto.php" class="btn btn-basic" role="button">Cancelar</a>
									</div>
								</div>
							</div>
						</form>								

					</div>
					<!-- /card-body -->
					
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
