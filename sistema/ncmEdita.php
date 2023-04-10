<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Editar NCM';

include('global_assets/php/conexao.php');

if(isset($_POST['inputNcmId'])){
	
	$iNcm = $_POST['inputNcmId'];
        	
	try{
		
		$sql = "SELECT NcmId, NcmCodigo, NcmNome
				FROM Ncm
				WHERE NcmId = $iNcm ";
		$result = $conn->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		
	} catch(PDOException $e) {
		echo 'Error: ' . $e->getMessage();
	}
	
	$_SESSION['msg'] = array();
} else {  //Esse else foi criado para se caso o usuário der um REFRESH na página. Nesse caso não terá POST e campos não reconhecerão o $row da consulta acima (daí ele deve ser redirecionado) e se quiser continuar editando terá que clicar no ícone da Grid novamente

	irpara("ncm.php");
}

if(isset($_POST['inputNome'])){
	
	try{
		
		$sql = "UPDATE Ncm SET NcmCodigo = :sCodigo, NcmNome = :sNome, NcmUsuarioAtualizador = :iUsuarioAtualizador
				WHERE NcmId = :iNcm";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
						':sCodigo' => $_POST['inputCodigo'],
						':sNome' => $_POST['inputNome'],
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iNcm' => $_POST['inputNcmId']
						));

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "NCM alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar NCM!!!";
		$_SESSION['msg']['tipo'] = "error";		
		
		echo 'Error: ' . $e->getMessage();
	}
	
	irpara("ncm.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | NCM</title>

	<?php include_once("head.php"); ?>

	<script type="text/javascript" >

        $(document).ready(function() {
			
			//Valida Registro Duplicado
			$('#enviar').on('click', function(e){
				
				e.preventDefault();
				
				var inputCodigo    = $('#inputCodigo').val();
				var inputNomeNovo  = $('#inputNome').val();
				var inputNomeVelho = $('#inputNcmNome').val();
								
				//remove os espaços desnecessários antes e depois
				inputCodigo = inputCodigo.trim();
				inputNomeNovo = inputNomeNovo.trim();
				
				//Esse ajax está sendo usado para verificar no ncm se o registro já existe
				$.ajax({
					type: "POST",
					url: "ncmValida.php",
					data: ('codigo='+inputCodigo+'&nomeNovo='+inputNomeNovo+'&nomeVelho='+inputNomeVelho),
					success: function(resposta){
						
						if(resposta == 1){
							alerta('Atenção','Esse registro já existe (código ou nome)!','error');
							return false;
						}
						
						$( "#formNcm" ).submit();
					}
				})
			})
		})
	</script>

    <!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>
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
					
					<form name="formNcm" id="formNcm" method="post" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Editar NCM "<?php echo $row['NcmNome']; ?>"</h5>
						</div>
						
						<input type="hidden" id="inputNcmId" name="inputNcmId" value="<?php echo $row['NcmId']; ?>" >
						<input type="hidden" id="inputNcmNome" name="inputNcmNome" value="<?php echo $row['NcmNome']; ?>" >
						
						<div class="card-body">								
							<div class="row">
								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputCodigo">Código NCM</label>
										<input type="text" id="inputCodigo" name="inputCodigo" class="form-control" placeholder="Código" value="<?php echo $row['NcmCodigo']; ?>" required autofocus>
									</div>
								</div>																
								<div class="col-lg-10">
									<div class="form-group">
										<label for="inputNome">NCM</label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Nome" value="<?php echo $row['NcmNome']; ?>" required>
									</div>
								</div>
							</div>
								
							<div class="row" style="margin-top: 10px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Alterar</button>
										<a href="ncm.php" class="btn btn-basic" role="button">Cancelar</a>
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
