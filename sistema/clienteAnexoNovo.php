<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Novo Anexo';

include('global_assets/php/conexao.php');

if(isset($_POST['inputData'])){

	try{	
		
		$_UP['pasta'] = 'global_assets/anexos/cliente/';

		// Renomeia o arquivo? (Se true, o arquivo será salvo como .csv e um nome único)
		$_UP['renomeia'] = false;

		// Primeiro verifica se deve trocar o nome do arquivo
		if ($_UP['renomeia'] == true) {
		
			// Cria um nome baseado no UNIX TIMESTAMP atual e com extensão .csv
			//$nome_final = time().".".$extensao;
			$nome_final = date('d-m-Y')."-".date('H-i-s')."-".$_FILES['inputArquivo']['name'];
		
		} else {
		
			// Mantém o nome original do arquivo
			$nome_final = $_FILES['inputArquivo']['name'];
		}
		
		//echo $_FILES['inputArquivo']['tmp_name']." <br>";
		//echo $_UP['pasta'] . $nome_final." <br>";
		
		// Depois verifica se é possível mover o arquivo para a pasta escolhida
		if (move_uploaded_file($_FILES['inputArquivo']['tmp_name'], $_UP['pasta'] . $nome_final)) {
		
			$sql = "INSERT INTO ClienteAnexo (ClAneData, ClAneNome, ClAneArquivo, ClAneCliente, ClAneUsuarioAtualizador,ClAneUnidade)
					VALUES (:iData, :sNome, :iArquivo, :iCliente, :iUsuarioAtualizador, :iUnidade)";
			$result = $conn->prepare($sql);
					
			$result->execute(array(
							':iData' => gravaData($_POST['inputData']),
							':sNome' => $_POST['inputNome'],
							':iArquivo' => $nome_final,
							':iCliente' => $_SESSION['idCliente'],
							':iUsuarioAtualizador' => $_SESSION['UsuarId'],
							':iUnidade' => $_SESSION['UnidadeId'],
							));
			
			$_SESSION['msg']['titulo'] = "Sucesso";
			$_SESSION['msg']['mensagem'] = "Anexo incluído!!!";
			$_SESSION['msg']['tipo'] = "success";
		}

	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir Anexo!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
	}
	
	irpara("clienteAnexo.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Anexo</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>
	
	<script src="global_assets/js/demo_pages/form_layouts.js"></script>
	<script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>
	<!-- /theme JS files -->

	<!-- Validação -->
	<script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
	<script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
	<script src="global_assets/js/demo_pages/form_validation.js"></script>

	<script type="text/javascript" >

        $(document).ready(function() {
			
			//Valida Registro Duplicado
			$('#enviar').on('click', function(e){
				
				e.preventDefault();
							
				var inputFile = $('#inputArquivo').val();
				var id = $("input:file").attr('id');
				var tamanho =  1024 * 1024 * 32; //32MB
								
				//Verifica se o campo só possui espaços em branco
				if (inputFile == ''){
					alerta('Atenção','Selecione o arquivo!','error');
					$("#formClienteAnexo").submit();
					$('#inputArquivo').focus();
					return false;
				}
								
				//Verifica se a extensão é  diferente de PDF, DOC, DOCX, ODT, JPG, JPEG, PNG!
				if (ext(inputFile) != 'pdf' && ext(inputFile) != 'doc' && ext(inputFile) != 'docx' && ext(inputFile) != 'odt' && ext(inputFile) != 'jpg' && ext(inputFile) != 'jpeg' && ext(inputFile) != 'png'){
					alerta('Atenção','Por favor, envie arquivos com a seguinte extensão: PDF, DOC, DOCX, ODT, JPG, JPEG, PNG!','error');
					$("#formClienteAnexo").submit();
					$('#inputArquivo').focus();
					return false;	
				}
				
				//Verifica o tamanho do arquivo
				if ($('#'+id)[0].files[0].size > tamanho){
					alerta('Atenção','O arquivo enviado é muito grande, envie arquivos de até 32MB.','error');
					$("#formClienteAnexo").submit();
					$('#inputArquivo').focus();
					return false;
				}				
				
					
                $( "#formClienteAnexo" ).submit();
				
			})
		})

         //Retorna a extenção do arquivo
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
					
					<form name="formClienteAnexo" id="formClienteAnexo" method="post" enctype="multipart/form-data" class="form-validate-jquery">
						<div class="card-header header-elements-inline">
							<h5 class="text-uppercase font-weight-bold">Cadastrar Novo Anexo</h5>
						</div>
					
					<div class="card-body">								
							<div class="row">
								<div class="col-lg-2">
									<div class="form-group">
										<label for="inputData">Data</label>
										<input type="text" id="inputData" name="inputData" class="form-control" placeholder="Data" value="<?php echo date('d/m/Y'); ?>"  readOnly>
									</div>
								</div>
								<div class="col-lg-10">
									<div class="form-group">
										<label for="inputNome">Descrição<span class="text-danger"> *</span></label>
										<input type="text" id="inputNome" name="inputNome" class="form-control" placeholder="Descrição" required autofocus>
									</div>
								</div>
							</div>	
							<div class="row">
								<div class="col-lg-12">
									<label for="inputArquivo">Arquivo<span class="text-danger"> *</span></label>
									<input type="file" id="inputArquivo" name="inputArquivo" class="form-control" required>
								</div>
							</div>	
							<div class="row">	
								<div class="col-lg-12">
									<div class="form-group">										
										Obs.: arquivos permitidos (.pdf, .doc, .docx, .odt, .jpg, .jpeg, .png) Tamanho máximo: 32MB
									</div>
								</div>									
							</div>												
							<div class="row" style="margin-top: 30px;">
								<div class="col-lg-12">								
									<div class="form-group">
										<button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
										<a href="clienteAnexo.php" class="btn btn-basic" role="button">Cancelar</a>
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
