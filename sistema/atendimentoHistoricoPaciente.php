<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Histórico do Paciente';

include('global_assets/php/conexao.php');

$iAtendimentoId = isset($_POST['iAtendimentoId'])?$_POST['iAtendimentoId']:null;

if(!$iAtendimentoId){
	irpara("atendimento.php");
}

// essas variáveis são utilizadas para colocar o nome da classificação do atendimento no menu secundario

$ClaChave = isset($_POST['ClaChave'])?$_POST['ClaChave']:'';
$ClaNome = isset($_POST['ClaNome'])?$_POST['ClaNome']:'';


//Essa consulta é para verificar qual é o atendimento e cliente 
$sql = "SELECT AtendId, AtendCliente, AtendNumRegistro, AtClaNome, AtendDataRegistro, AtModNome, ClienId, ClienCodigo, ClienNome, ClienSexo, ClienDtNascimento,
               ClienNomeMae, ClienCartaoSus, ClienCelular, ClResNome, AtClaChave
		FROM Atendimento
		JOIN Cliente ON ClienId = AtendCliente
		LEFT JOIN ClienteResponsavel on ClResCliente = AtendCliente
		JOIN AtendimentoModalidade ON AtModId = AtendModalidade
		JOIN AtendimentoClassificacao ON AtClaId = AtendClassificacao
		JOIN Situacao ON SituaId = AtendSituacao
	    WHERE AtendId = $iAtendimentoId and AtendUnidade = ".$_SESSION['UnidadeId']."
		ORDER BY AtendDataRegistro ASC";
$result = $conn->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

$iAtendimentoId = $row['AtendId'];
$iClienteId = $row['ClienId'];


$sql = "SELECT AtendId, AtendNumRegistro, AtendDataRegistro, AtClaNome
		FROM Atendimento
		JOIN Cliente ON ClienId = AtendCliente
		JOIN AtendimentoClassificacao ON AtClaId = AtendClassificacao
		JOIN Situacao ON SituaId = AtendSituacao
	    WHERE AtendCliente = $iClienteId and AtendUnidade = ".$_SESSION['UnidadeId']."
		ORDER BY AtendDataRegistro, AtendId DESC";
$result = $conn->query($sql);
$rowHistorico = $result->fetchAll(PDO::FETCH_ASSOC);

$iAtendimentoHistoricoId = $row['AtendId'];

//Essa consulta é para preencher o sexo
if ($row['ClienSexo'] == 'F'){
    $sexo = 'Feminino';
} else{
    $sexo = 'Masculino';
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Lamparinas | Histórico do Paciente</title>

	<?php include_once("head.php"); ?>
	
	<!-- Theme JS files -->
	<script src="global_assets/js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="global_assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<script src="global_assets/js/demo_pages/datatables_responsive.js"></script>
	<script src="global_assets/js/demo_pages/datatables_sorting.js"></script>

	<!-- Não permite que o usuário retorne para o EDITAR -->
	<script src="global_assets/js/lamparinas/stop-back.js"></script>
	
	<script type="text/javascript">

		$(document).ready(function() {	

            /* Início: Tabela Personalizada */
			$('#tblHistorico').DataTable( {
				"order": [[ 2, "desc" ]],
			    autoWidth: false,
				responsive: true,
				searching: false,
				ordering: true, 
				paging: false,
			    columnDefs: [
				{ 
					orderable: true,   //Atendimento
					width: "25%",
					targets: [0]
				},
				{ 
					orderable: true,   //Registro
					width: "25%",
					targets: [1]
				},
				{ 
					orderable: true,   //Data da Entrada
					width: "25%",
					targets: [2]
				},				
				{ 
					orderable: true,   //Data da Saída
					width: "25%",
					targets: [3]
				
				}],
				dom: '<"datatable-header"fl><"datatable-scroll-wrap"t><"datatable-footer">',
				language: {
					search: '<span>Filtro:</span> _INPUT_',
					searchPlaceholder: 'filtra qualquer coluna...',
					lengthMenu: '<span>Mostrar:</span> _MENU_',
					paginate: { 'first': 'Primeira', 'last': 'Última', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
                
			});

		}); //document.ready

		function buscarHistorico(historicoId, classificacaoNome){

			//Esse ajax está sendo usado para verificar no banco se o registro já existe
			$.ajax({
				type: "POST",
				url: "atendimentoHistoricoPacienteValida.php",
				data: ('historicoId=' + historicoId ),
				success: function(resposta) {

					if(resposta){
						
						$('#classificacao').html('<h3 class="card-title font-weight-bold">ATENDIMENTO ' + classificacaoNome.toUpperCase() + '</h3>');
						$('#impressao').html('<button style="margin-top:-5px;" id="imprimir" class="btn btn-secondary btn-icon"><i class="icon-printer2"></i></button>');
						$('#txtareaHistorico').html(resposta);

						// Imprimir Histórico
						$('#imprimir').on('click', function(e){
							$('#formAtendimentoHistorico').attr('target', '_blank');
							$('#formAtendimentoHistorico').submit();
						});
					}					
				}
			})
		}
		
	</script>

</head>

<body class="navbar-top sidebar-xs">

	<?php include_once("topo.php"); ?>	

	<!-- Page content -->
	<div class="page-content">
		
		<?php
			include_once("menu-left.php");
			include_once("menuLeftSecundarioVenda.php");
		?>

		<!-- Main content -->
		<div class="content-wrapper">

			<?php include_once("cabecalho.php"); ?>	

			<!-- Content area -->
			<div class="content">

				<!-- Info blocks -->		
				<div class="row">
					
					<div class="col-lg-12">
						<form id='dadosPost'>
							<?php
								echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
							?>
						</form>
						<!-- Basic responsive configuration -->
						
						<?php
							echo "<input type='hidden' id='iAtendimentoId' name='iAtendimentoId' value='$iAtendimentoId' />";
						?>
						<div class="card">
							<div class="card-header header-elements-inline">
								<h3 class="card-title"><b>HISTÓRICO DO PACIENTE</b></h3>
							</div>
						</div>

						<div class="card card-collapsed">
							<div class="card-header header-elements-inline">
								<h3 class="card-title">Dados do Paciente</h3>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" data-action="collapse"></a>
									</div>
								</div>
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-lg-3">
										<div class="form-group">
											<label>Prontuário Eletrônico: <?php echo $row['ClienCodigo']; ?></label>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
											<label>Nº do Registro: <?php echo $row['AtendNumRegistro']; ?></label>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
											<label>Modalidade: <?php echo $row['AtModNome'] ; ?></label>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
											<label>CNS: <?php echo $row['ClienCartaoSus']; ?></label>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-6">
										<h4><b><?php echo strtoupper($row['ClienNome']); ?></b></h4>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
											<label>Sexo: <?php echo $sexo ; ?></label>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
											<label>Telefone: <?php echo $row['ClienCelular']; ?></label>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-3">
										<div class="form-group">
											<label>Data Nascimento: <?php echo mostraData($row['ClienDtNascimento']); ?></label>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
											<label>Idade: <?php echo calculaIdade($row['ClienDtNascimento']); ?></label> 
										</div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
											<label>Mãe: <?php echo $row['ClienNomeMae'] ; ?></label>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
											<label>Responsável: <?php echo $row['ClResNome']; ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="card">
							<div class="row" style="padding: 20px;">
								<div class="col-lg-6">
									<div class="card-header header-elements-inline" style="padding-left: 0px;">
										<h3 class="card-title"><b>HISTÓRICO DE ATENDIMENTO</b></h3>
									</div>

									<table class="table" id="tblHistorico">
										<thead>
											<tr class="bg-slate">
												<th>Atendimento</th>
												<th>Registro</th>
												<th>Entrada</th>
												<th>Saída</th>
											</tr>
										</thead>
										<tbody>
											<?php	
												foreach ($rowHistorico as $item){	
													print( '
													<tr>													
														<td class="text-left">
															<a href="#" onClick="buscarHistorico('.$item['AtendId'].', \''.$item['AtClaNome'].'\')">'.$item['AtClaNome'].'</a>
														</td>
														<td>'.$item['AtendNumRegistro'].'</td>
														<td>'.mostraData($item['AtendDataRegistro']).'</td>
														<td>'.mostraData($item['AtendDataRegistro']).'</td>														
													</tr>');
												}	
											?>
										</tbody>
									</table>
								</div>
								<div class="col-lg-6">
									<div class="card-header header-elements-inline" style="padding-left: 0px;">

										<div id="classificacao"><h3 class="card-title font-weight-bold">ATENDIMENTO</h3></div>
										
										<div id="impressao"></div>
									</div>
									<div class="form-group" style="border: 1px solid #ccc;">
										<div id="txtareaHistorico" style="padding: 10px; min-height: 200px;"></div>
									</div>
								</div>			
								</div>
						</div>

							<!-- /basic responsive configuration -->
					</div>
					
				</div>				
				
				<!-- /info blocks -->

			</div>
			<!-- /content area -->
			
			<?php include_once("footer.php"); ?>

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

	<?php include_once("alerta.php"); ?>

</body>

</html>
