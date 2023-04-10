<?php

include_once("sessao.php");
include('global_assets/php/conexao.php');

function queryPesquisa()
{

    $cont = 0;

    include('global_assets/php/conexao.php');

    $args = [];
	
    if (!empty($_POST['cmbUnidade'])) {
		$args[]  = "MovimUnidade = " . $_POST['cmbUnidade'] . " ";
		$iUnidade = $_POST['cmbUnidade'];
		
		if (!empty($_POST['cmbSetor'])) {
			$args[]  = "MovimDestinoSetor = " . $_POST['cmbSetor'] . " ";
			$iSetor = $_POST['cmbSetor'];
		} else{
			$iSetor = 'NULL';
		}		
		
    } else{
		$iUnidade = 'NULL';
		$iSetor = 'NULL';
	}
	
    if (!empty($_POST['cmbCategoria'])) {
        $args[]  = "ProduCategoria = " . $_POST['cmbCategoria'] . " ";
		$iCategoria = $_POST['cmbCategoria'];
    } else{
		$iCategoria = 'NULL';
	}

    if (!empty($_POST['cmbSubCategoria'])) {
        $args[]  = "ProduSubCategoria = " . $_POST['cmbSubCategoria'] . " ";
		$iSubCategoria = $_POST['cmbSubCategoria'];
    } else{
		$iSubCategoria = 'NULL';
	}

    if (!empty($_POST['cmbClassificacao'])) {
        $args[]  = "MvXPrClassificacao = " . $_POST['cmbClassificacao'] . " ";
		$iClassificacao = $_POST['cmbClassificacao'];
    } else{
		$iClassificacao = 'NULL';
	}

	$dataInicio = $_POST['inputDataInicio'];
	$dataFim = $_POST['inputDataFim'];

	$string = '';

    if (count($args) >= 1) {

        $string = implode(" and ", $args);

        if ($string != '') {
            $string .= ' and ';
        }
    }

    try {
		
		$sql = "SELECT distinct ProduId, ProduCodigo, ProduNome, MvXPrValorUnitario, 
				dbo.fnTotalSaidas(MovimUnidade, ProduId, NULL, $iSetor, $iCategoria, $iSubCategoria, $iClassificacao, '$dataInicio', '$dataFim') as Saidas,
			    (MvXPrValorUnitario * dbo.fnTotalSaidas(". $_SESSION['UnidadeId'] .", ProduId, NULL, $iSetor, $iCategoria, $iSubCategoria, $iClassificacao, '$dataInicio', '$dataFim')) as ValorTotal
				FROM Produto
				JOIN MovimentacaoXProduto on MvXPrProduto = ProduId
				JOIN Movimentacao on MovimId = MvXPrMovimentacao
				JOIN Situacao on SituaId = MovimSituacao
				WHERE " . $string . " ProduEmpresa = ". $_SESSION['EmpreId'] ." and MovimTipo = 'S' and SituaChave = 'LIBERADO' and MovimData between '".$dataInicio."' and '".$dataFim."' 
				ORDER BY ValorTotal DESC
		";			

		//echo $sql;die;
        $result = $conn->query($sql);
        $rowData = $result->fetchAll(PDO::FETCH_ASSOC);

        count($rowData) >= 1 ? $cont = 1 : $cont = 0;
		
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }

    if ($cont == 1) {
        
		$cont = 0;

		$resultado = '

		<div class="col-lg-12">

			<div class="card">
				<div class="card-header bg-light pb-1 pt-2 header-elements-sm-inline">
					<h6 class="card-title">Resultado da Pesquisa</h6>
					<!--<div class="header-elements">
						<ul class="nav nav-tabs nav-tabs-highlight card-header-tabs">
							<li class="nav-item">
								<a href="#card-tab1" class="nav-link active" data-toggle="tab">
									<i class="icon-screen-full mr-2"></i>
									Grid
								</a>
							</li>
							<li class="nav-item">
								<a href="#card-tab2" class="nav-link" data-toggle="tab">
									<i class="icon-stats-bars mr-2"></i>
									Gráfico
								</a>
							</li>
						</ul>
                	</div>-->
	            </div>

				<div class="card-body tab-content">
					
					<div class="tab-pane fade show active" id="card-tab1">
						<table class="table" id="tblCurvaABC">
							<thead>
								<tr class="bg-slate">											
									<th width="5%">Código</th>
									<th width="35%">Produto</th>
									<th width="10%" style="text-align:right;">Valor Unitário</th>
									<th width="10%" style="text-align:right;">Saída</th>
									<th width="10%" style="text-align:right;">Valor Total</th>									
									<th width="10%" style="text-align:right;">Porcentagem</th>										
									<th width="10%" style="text-align:right;">% Acumulada</th>
									<th width="10%" style="background-color: #ccc; color:#333;">Classificação</th>
								</tr>
							</thead>
							<tbody>
			';

		$fTotalUnit = 0;
		$iTotalSaidas = 0;  //Deveria ser 0 (coloquei 1 por enquanto para não ocorrer divisão por zero)
		$fTotalGeral = 0;	//Deveria ser 0 (coloquei 1 por enquanto para não ocorrer divisão por zero)
     
		foreach ($rowData as $item) {

			$fTotalUnit += $item['MvXPrValorUnitario'];
			$iTotalSaidas += $item['Saidas'];
			$fTotalGeral += $item['ValorTotal'];
		}

		$fTotalPorcentagem = 0;
		$fAcumulada = 0;
		$fTotalAcumulada = 0;
		$saidasA = 0;
		$saidasB = 0;
		$saidasC = 0;
		$totalSaidasA = 0;
		$totalSaidasB = 0;
		$totalSaidasC = 0;

		foreach ($rowData as $item) {
			
			$fPorcentagem = $item['ValorTotal'] / $fTotalGeral * 100;
			$fTotalPorcentagem += $fPorcentagem;
			$fAcumulada += $fPorcentagem;
			$fTotalAcumulada = $fAcumulada;

			if ($fAcumulada < 80){
				$cor = 'background-color:#fde1df; padding: 10px 20px 10px 20px; border: 1px solid #f55246; color:#7f231c;'; //color:#5b071d
				$classificacao = 'A';
				$saidasA += $item['Saidas'];
			} else if ($fAcumulada > 80 and $fAcumulada < 95){
				$cor = 'background-color:#e0f2f1; padding: 10px 20px 10px 20px; border: 1px solid #009688; color: #00695c;'; //color: #8e6d08
				$classificacao = 'B';
				$saidasB += $item['Saidas'];
			} else{
				$cor = 'background-color:#dbeefd; padding: 10px 20px 10px 20px; border: 1px solid #339ef4; color: #114e7e;'; //color: #0b5282
				$classificacao = 'C';
				$saidasC += $item['Saidas'];
			}	

			$resultado .= '
			<tr>
				<td>'.$item['ProduCodigo'].'</td>
				<td>'.$item['ProduNome'].'</td>
				<td style="text-align:right;">'.mostraValor($item['MvXPrValorUnitario']).'</td>
				<td style="text-align:right;">'.$item['Saidas'].'</td>
				<td style="text-align:right;">'.mostraValor($item['ValorTotal']).'</td>
				<td style="text-align:right;">'.mostraValor($fPorcentagem).'%</td>
				<td style="text-align:right;">'.mostraValor($fAcumulada).'%</td>
				<td style="background-color: #fff; color:#333;"><div style="text-align:center;"><span style="'.$cor.'">'.$classificacao.'</span></div></td>
			</tr>';

			$cont++;
		}

		$resultado .= '			
		<tr style="font-weight:bold;background-color: #eee;">
			<td colspan="2">Totais</td>
			<td style="text-align:right;">'.mostraValor($fTotalUnit).'</td>
			<td style="text-align:right;">'.$iTotalSaidas.'</td>
			<td style="text-align:right;">'.mostraValor($fTotalGeral).'</td>
			<td style="text-align:right;">'.mostraValor($fTotalPorcentagem).'%</td>
			<td style="text-align:right;">'.mostraValor($fTotalAcumulada).'%</td>
			<td style="background-color: #eee; color:#333;"></td>
		</tr>';

		$totalSaidasA = $saidasA / $iTotalSaidas * 100;
		$totalSaidasB = $saidasB / $iTotalSaidas * 100;
		$totalSaidasC = $saidasC / $iTotalSaidas * 100;

		$resultado .= '				
							</tbody>
						</table>
					</div>';

		$resultado .= '
			<input type="hidden" id="inputSaidasA" value="'.$totalSaidasA.'" >
			<input type="hidden" id="inputSaidasB" value="'.$totalSaidasB.'" >
			<input type="hidden" id="inputSaidasC" value="'.$totalSaidasC.'" >
		';			

		$resultado .= '

					<!--
					<div class="tab-pane fade" id="card-tab2">
						<div class="chart-container">
							<div class="chart has-fixed-height" id="area_basic"></div>
							<div class="chart has-fixed-height" id="line_basic"></div>
						</div>
					</div>-->

				</div>
				<div class="card-footer bg-white justify-content-between align-items-center">
					<div class="row">
						<div class="col-lg-4" style="text-align:center;padding-top:10px;">
							<div style="border:1px solid #f55246; color:#7f231c; background-color:#fde1df;box-shadow:0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24); display:table;">
								<div style="background-color: #f55246; color:#fff; font-size:16px; vertical-align:middle; display:table-cell; padding-left:10px; padding-right: 10px;">A</div>
								<div style="padding:10px;">Representa 80% do capital investido, responsável por '.mostraValor($totalSaidasA).'% das saídas</div>
							</div>
						</div>
						<div class="col-lg-4" style="text-align:center;padding-top:10px;">
							<div style="border:1px solid #009688; color:#00695c; background-color:#e0f2f1;box-shadow:0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24); display:table;">
								<div style="background-color: #009688; color:#fff; font-size:16px; vertical-align:middle; display:table-cell; padding-left:10px; padding-right: 10px;">B</div>
								<div style="padding:10px;">Representa 15% do capital investido, responsável por '.mostraValor($totalSaidasB).'% das saídas</div>
							</div>
						</div>						
						<div class="col-lg-4" style="text-align:center;padding-top:10px;">
							<div style="border:1px solid #339ef4; color:#114e7e; background-color:#dbeefd;box-shadow:0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24); display:table;">
								<div style="background-color: #339ef4; color:#fff; font-size:16px; vertical-align:middle; display:table-cell; padding-left:10px; padding-right: 10px;">C</div>
								<div style="padding:10px;">Representa 5% do capital investido, responsável por '.mostraValor($totalSaidasC).'% das saídas</div>
							</div>
						</div>
					</div>				
				</div>
			</div>
		</div>
		';
		
		echo $resultado;
    }
}

queryPesquisa();
