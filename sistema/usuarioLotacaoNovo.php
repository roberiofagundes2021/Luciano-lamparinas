<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Nova Lotacao';

include('global_assets/php/conexao.php');

if (isset($_SESSION['EmpresaId'])){	
	$EmpresaId = $_SESSION['EmpresaId'];
} else {	
	$EmpresaId = $_SESSION['EmpreId'];
}

if(isset($_POST['cmbUnidade'])){

	try{
		//echo $_POST['cmbUnidade'];die;
		$sql = "INSERT INTO UsuarioXUnidade (UsXUnEmpresaUsuarioPerfil, UsXUnUnidade, UsXUnSetor, UsXUnLocalEstoque, UsXUnPermissaoPerfil, UsXUnUsuarioAtualizador)
				    VALUES (:iEmpresaUsarioPerfil, :iUnidade, :iSetor, :iLocalEstoque, :PermissaoPerfil, :iUsuarioAtualizador)";
		$result = $conn->prepare($sql);
				
		$result->execute(array(
            ':iEmpresaUsarioPerfil' => $_SESSION['EmpresaUsuarioPerfil'],
            ':iUnidade' => $_POST['cmbUnidade'],
            ':iSetor' => $_POST['cmbSetor'],
            ':iLocalEstoque' => isset($_POST['cmbLocalEstoque']) ? $_POST['cmbLocalEstoque'] : null,
            ':PermissaoPerfil' => 1,
            ':iUsuarioAtualizador' => $_SESSION['UsuarId']
            ));
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Lotação incluída!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir Lotação!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();die;
	}
	
	irpara("usuarioLotacao.php");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Lamparinas | Lotação</title>

  <?php include_once("head.php"); ?>

  <!-- Theme JS files -->
  <script src="global_assets/js/plugins/forms/selects/select2.min.js"></script>

  <script src="global_assets/js/demo_pages/form_layouts.js"></script>
  <script src="global_assets/js/plugins/forms/styling/uniform.min.js"></script>

  <script src="global_assets/js/plugins/forms/validation/validate.min.js"></script>
  <script src="global_assets/js/plugins/forms/validation/localization/messages_pt_BR.js"></script>
  <script src="global_assets/js/demo_pages/form_validation.js"></script>
  <!-- /theme JS files -->

  <script type="text/javascript">

    $(document).ready(function() {

      $('#cmbUnidade').on('change', function(e) {

        Filtrando();

        var cmbUnidade = $('#cmbUnidade').val();

        if (cmbUnidade == '') {
          ResetSetor();
          ResetLocalEstoque();
        } else {

          $.getJSON('filtraSetor.php?idUnidade=' + cmbUnidade, function(dados) {

            var option = '<option value="">Selecione o Setor</option>';

            if (dados.length) {

              $.each(dados, function(i, obj) {
                option += '<option value="' + obj.SetorId + '">' + obj.SetorNome + '</option>';
              });

              $('#cmbSetor').html(option).show();
            } else {
              ResetSetor();
            }
          });

          $.getJSON('filtraLocalEstoque.php?idUnidade=' + cmbUnidade, function(dados) {

            var option = '<option value="">Selecione o Local de Estoque</option>';

              if (dados.length) {

                $.each(dados, function(i, obj) {
                  option += '<option value="' + obj.LcEstId + '">' + obj.LcEstNome + '</option>';
                });

                $('#cmbLocalEstoque').html(option).show();
              } else {
                  ResetLocalEstoque();
              }
          });

        }
      });

    //Valida Registro Duplicado
    $('#enviar').on('click', function(e) {

      e.preventDefault();

      var cmbUnidade = $('#cmbUnidade').val(); 
      var cmbSetor = $('#cmbSetor').val(); 
           
      if (cmbSetor == '') {
        $("#formLotacao").submit();
      } else {
        //Esse ajax está sendo usado para verificar no banco se o registro já existe
        $.ajax({
          type: "POST",
          url: "usuarioLotacaoValida.php",
          data: ('unidade='+cmbUnidade),
          success: function(resposta) {

            if (resposta == 1) {
              alerta('Atenção','Essa Unidade já existe!','error');
              return false;
            }

            $("#formLotacao").submit();
          }
        })
      }
    })

      function Filtrando() {
				$('#cmbSetor').empty().append('<option value="">Filtrando...</option>');
				$('#cmbLocalEstoque').empty().append('<option value="">Filtrando...</option>');
			}

			function ResetSetor() {
				$('#cmbSetor').empty().append('<option value="">Sem setor</option>');
			}

      function ResetLocalEstoque() {
			$('#cmbLocalEstoque').empty().append('<option value="">Sem Local de Estoque</option>');
		}	


  })
  </script>

</head>

  <?php
  
      if (isset($_SESSION['EmpresaId'])){	
        print('<body class="navbar-top sidebar-xs">');
      } else {
        print('<body class="navbar-top">');
      }

      include_once("topo.php");

  ?>	






  <!-- Page content -->
	<div class="page-content">
		
		<?php 
			
			include_once("menu-left.php"); 
		
			if (isset($_SESSION['EmpresaId'])){
				include_once("menuLeftSecundario.php");
			}
		?>			
    <!-- Main content -->
    <div class="content-wrapper">

      <?php include_once("cabecalho.php"); ?>

      <!-- Content area -->
      <div class="content">

        <!-- Info blocks -->
        <div class="card">

          <form name="formLotacao" id="formLotacao" method="post" class="form-validate-jquery" action="usuarioLotacaoNovo.php">
            <div class="card-header header-elements-inline">
              <h5 class="text-uppercase font-weight-bold">Cadastrar Nova Lotação</h5>
            </div>

            <div class="card-body">
              <div class="row">
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="cmbUnidade">Unidade<span class="text-danger"> *</span></label>
                    <select name="cmbUnidade" id="cmbUnidade" class="form-control form-control-select2" required>
                      <option value="">Informe uma unidade</option>
                      <?php
                      $sql = "SELECT UnidaId, UnidaNome
                              FROM Unidade
                              JOIN Situacao on SituaId = UnidaStatus															     
                              WHERE UnidaEmpresa = " . $EmpresaId . " and SituaChave = 'ATIVO'
                              ORDER BY UnidaNome ASC";
                      $result = $conn->query($sql);
                      $rowUnidade = $result->fetchAll(PDO::FETCH_ASSOC);

                      foreach ($rowUnidade as $item) {
                        print('<option value="' . $item['UnidaId'] . '">' . $item['UnidaNome'] . '</option>');
                      }

                      ?>
                    </select>
                  </div>
                </div>

                <div class="col-lg-3">
                  <div class="form-group">
                    <label for="cmbSetor">Setor<span class="text-danger"> *</span></label>
                    <select name="cmbSetor" id="cmbSetor" class="form-control form-control-select2" required>
                      <option value="">Sem setor</option>
                    </select>
                  </div>
                </div>	
                
                <?php 

                  if ($_SESSION['UsuarioPerfil'] == 'ALMOXARIFADO'){
                    print('
                      <div class="col-lg-3">
                        <div class="form-group">
                          <label for="cmbLocalEstoque">Local de Estoque<span class="text-danger"> *</span></label>
                          <select name="cmbLocalEstoque" id="cmbLocalEstoque" class="form-control form-control-select2" required>
                            <option value="">Local de Estoque</option>
                          </select>
                        </div>
                      </div>
                    ');
                  }

                ?>
              </div>
                
              <div class="row" style="margin-top: 10px;">
                <div class="col-lg-12">
                  <div class="form-group">
                    <button class="btn btn-lg btn-principal" id="enviar">Incluir</button>
                    <a href="usuarioLotacao.php" class="btn btn-basic" role="button">Cancelar</a>
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