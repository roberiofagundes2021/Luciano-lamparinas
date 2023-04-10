<?php

include_once("sessao.php");

$inicio1 = microtime(true);

// Para a data ficar em português. Foi usado lá embaixo onde tem strftime (referência: https://www.linhadecomando.com/php/php-funcao-date-para-strftime-em-portugues)
setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

/* 
  --------------- $_POST -------------
  ["typeDate"]=> "D"/"M"
  ["dateInitial"]=> string(10) "2021-03-05"
  ["dateEnd"]=> string(10) "2021-03-17"
  ["cmbCentroDeCustos"]=> string(1) "4"
  ["cmbPlanoContas"]=>string(2) "78"
*/

//Consulta os planos de Contas  que pertecem ao  determinado grupo
function retornaBuscaComoArray($datasFiltro, $datasFiltro2, $datasFiltro3, $datasFiltro4, $plFiltro, $grupoPlanoConta, $tipo) {
  include('global_assets/php/conexao.php');

  if($tipo == 'E') {
    $sql = "SELECT PlConId, PlConNome,
                  dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro['data_inicio_mes']."', '".$datasFiltro['data_fim_mes']."', '".$tipo."') as PrevistoSaida,
                  dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro['data_inicio_mes']."', '".$datasFiltro['data_fim_mes']."', '".$tipo."') as RealizadoSaida,
                  dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro2['data_inicio_mes']."', '".$datasFiltro2['data_fim_mes']."', '".$tipo."') as PrevistoSaida2,
                  dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro2['data_inicio_mes']."', '".$datasFiltro2['data_fim_mes']."', '".$tipo."') as RealizadoSaida2,
                  dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro3['data_inicio_mes']."', '".$datasFiltro3['data_fim_mes']."', '".$tipo."') as PrevistoSaida3,
                  dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro3['data_inicio_mes']."', '".$datasFiltro3['data_fim_mes']."', '".$tipo."') as RealizadoSaida3,
                  dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro4['data_inicio_mes']."', '".$datasFiltro4['data_fim_mes']."', '".$tipo."') as PrevistoSaida4,
                  dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro4['data_inicio_mes']."', '".$datasFiltro4['data_fim_mes']."', '".$tipo."') as RealizadoSaida4
            FROM PlanoConta
            WHERE PlConId in ($plFiltro) and PlConNatureza = 'R' AND PlConGrupo = $grupoPlanoConta AND PlConTipo = 'S'
            ORDER BY PlConNome ASC";
    $result = $conn->query($sql);
    $rowPlanoContaSintetica = $result->fetchAll(PDO::FETCH_ASSOC);
  }else {
    $sql = "SELECT PlConId, PlConNome,
                  dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro['data_inicio_mes']."', '".$datasFiltro['data_fim_mes']."', '".$tipo."') as PrevistoSaida,
                  dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro['data_inicio_mes']."', '".$datasFiltro['data_fim_mes']."', '".$tipo."') as RealizadoSaida,
                  dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro2['data_inicio_mes']."', '".$datasFiltro2['data_fim_mes']."', '".$tipo."') as PrevistoSaida2,
                  dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro2['data_inicio_mes']."', '".$datasFiltro2['data_fim_mes']."', '".$tipo."') as RealizadoSaida2,
                  dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro3['data_inicio_mes']."', '".$datasFiltro3['data_fim_mes']."', '".$tipo."') as PrevistoSaida3,
                  dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro3['data_inicio_mes']."', '".$datasFiltro3['data_fim_mes']."', '".$tipo."') as RealizadoSaida3,
                  dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro4['data_inicio_mes']."', '".$datasFiltro4['data_fim_mes']."', '".$tipo."') as PrevistoSaida4,
                  dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro4['data_inicio_mes']."', '".$datasFiltro4['data_fim_mes']."', '".$tipo."') as RealizadoSaida4
            FROM PlanoConta
            WHERE PlConId in ($plFiltro) and PlConNatureza = 'D' AND PlConGrupo = $grupoPlanoConta AND PlConTipo = 'S'
            ORDER BY PlConNome ASC";
    $result = $conn->query($sql);
    $rowPlanoContaSintetica = $result->fetchAll(PDO::FETCH_ASSOC);
  }

  $regAt = '';
  $cont = 0;
  
  if(count($rowPlanoContaSintetica) > 0){
    foreach($rowPlanoContaSintetica as $rowCC) {
      if($regAt != $rowCC['PlConId']) {
        $regAt = $rowCC['PlConId'];
  
        $cont = 0;
  
        //reserva os dados do plano de contas
        $pl[$regAt]['PlConId']         = $rowCC['PlConId']; 
        $pl[$regAt]['PlConNome']       = $rowCC['PlConNome'];
        $pl[$regAt]['PL_Previsto']     = $rowCC['PrevistoSaida'];
        $pl[$regAt]['PL_Realizado']    = $rowCC['RealizadoSaida'];
        $pl[$regAt]['PL_Previsto2']    = $rowCC['PrevistoSaida2'];
        $pl[$regAt]['PL_Realizado2']   = $rowCC['RealizadoSaida2'];
        $pl[$regAt]['PL_Previsto3']    = $rowCC['PrevistoSaida3'];
        $pl[$regAt]['PL_Realizado3']   = $rowCC['RealizadoSaida3'];
        $pl[$regAt]['PL_Previsto4']    = $rowCC['PrevistoSaida4'];
        $pl[$regAt]['PL_Realizado4']   = $rowCC['RealizadoSaida4'];
        
      }
  
      $cont++;
    }
      
    $retorno = array('pl'=>$pl);
    
    unset($pl);
    unset($result);
    unset($rowPlanoContaSintetica);
    
    return $retorno;
  } else {
    return false;
  }
}

//Gerar os dados dos planos de Contas Sintéticos
function planoConta($idPlanoConta1, $nome, $valorPrevisto, $valorPrevisto2, $valorPrevisto3, $valorPrevisto4, $valorRealizado, $valorRealizado2, $valorRealizado3, $valorRealizado4, 
                           $segundaColuna, $terceiraColuna, $quartaColuna, $data, $data2, $data3, $data4, $codigoGrupo, $tipoGrupo, $indice) {
  include('global_assets/php/conexao.php');

  //Definindo a cor vermelha para as despesas
  $cor = $tipoGrupo != 'E' ? ' style="color: red;"' : '';
  $sinalValorPrevisto = $tipoGrupo != 'E' && $valorPrevisto > 0 ? '-' : '';
  $sinalValorRealizado = $tipoGrupo != 'E' &&  $valorRealizado > 0 ? '-' : '';
  $sinalValorPrevisto2 = $tipoGrupo != 'E' && $valorPrevisto2 > 0 ? '-' : '';
  $sinalValorRealizado2 = $tipoGrupo != 'E' &&  $valorRealizado2 > 0 ? '-' : '';
  $sinalValorPrevisto3 = $tipoGrupo != 'E' && $valorPrevisto3 > 0 ? '-' : '';
  $sinalValorRealizado3 = $tipoGrupo != 'E' &&  $valorRealizado3 > 0 ? '-' : '';
  $sinalValorPrevisto4 = $tipoGrupo != 'E' && $valorPrevisto4 > 0 ? '-' : '';
  $sinalValorRealizado4 = $tipoGrupo != 'E' &&  $valorRealizado4 > 0 ? '-' : '';

  $dataInicial2 = isset($data2['data_inicio_mes']) ? $data2['data_inicio_mes'] : "";
  $dataFinal2 = isset($data2['data_fim_mes']) ? $data2['data_fim_mes'] : "";
  $dataInicial3 = isset($data3['data_inicio_mes']) ? $data3['data_inicio_mes'] : "";
  $dataFinal3 = isset($data3['data_fim_mes']) ? $data3['data_fim_mes'] : "";
  $dataInicial4 = isset($data4['data_inicio_mes']) ? $data4['data_inicio_mes'] : "";
  $dataFinal4 = isset($data4['data_fim_mes']) ? $data4['data_fim_mes'] : "";

  //Foi inserido os inputs de datas para a primeira, segunda... coluna para ser feito a consulta através dela quando for consultar na página de Fluxo de Caixa
  //A segunda e terceira coluna são o segundo e terceiro dia que é mostrado em cada paginação do Fluxo de Caixa

  $resposta[0] = "
    <div class='row' style='background: #CCCCCC; line-height: 3rem; box-sizing:border-box;'>
      <div id='planoConta".$indice."' class='col-lg-3 planoConta' style='border-right: 1px dotted black; cursor:pointer;'>
        <input type='hidden' id='idPlanoConta".$indice."' value='".$idPlanoConta1."'>
        <input type='hidden' id='dataInicial".$indice."' value='".$data['data_inicio_mes']."'>
        <input type='hidden' id='dataFinal".$indice."' value='".$data['data_fim_mes']."'>
        <input type='hidden' id='dataInicialSegundaColuna".$indice."' value='".$dataInicial2."'>
        <input type='hidden' id='dataFinalSegundaColuna".$indice."' value='".$dataFinal2."'>
        <input type='hidden' id='dataInicialTerceiraColuna".$indice."' value='".$dataInicial3."'>
        <input type='hidden' id='dataFinalTerceiraColuna".$indice."' value='".$dataFinal3."'>
        <input type='hidden' id='dataInicialQuartaColuna".$indice."' value='".$dataInicial4."'>
        <input type='hidden' id='dataFinalQuartaColuna".$indice."' value='".$dataFinal4."'>
        <span><span id='simbolo".$indice."' style='font-weight: bold; color: #607D8B;'>( + ) </span>".$nome."</span>
      </div>

      <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
        <div class='row'>
          <div class='col-md-6'>
            <span $cor>".$sinalValorPrevisto.' '.mostraValor($valorPrevisto)."</span>
          </div>

          <div class='col-md-6'>
            <span $cor>".$sinalValorRealizado.' '.mostraValor($valorRealizado)."</span>
          </div>
        </div>
      </div>";

    if($segundaColuna) {
      $resposta[0] .= "
      <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
        <div class='row'>
          <div class='col-md-6'>
            <span $cor>".$sinalValorPrevisto2.' '.mostraValor($valorPrevisto2)."</span>
          </div>

          <div class='col-md-6'>
            <span $cor>".$sinalValorRealizado2.' '.mostraValor($valorRealizado2)."</span>
          </div>
        </div>
      </div>";
    }

    if($terceiraColuna) {
      $resposta[0] .= "
      <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
        <div class='row'>
          <div class='col-md-6'>
            <span $cor>".$sinalValorPrevisto3.' '.mostraValor($valorPrevisto3)."</span>
          </div>

          <div class='col-md-6'>
            <span $cor>".$sinalValorRealizado3.' '.mostraValor($valorRealizado3)."</span>
          </div>
        </div>
      </div>";
    }

    if($quartaColuna) {
      $resposta[0] .= "
      <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
        <div class='row'>
          <div class='col-md-6'>
            <span $cor>".$sinalValorPrevisto4.' '.mostraValor($valorPrevisto4)."</span>
          </div>

          <div class='col-md-6'>
            <span $cor>".$sinalValorRealizado4.' '.mostraValor($valorRealizado4)."</span>
          </div>
        </div>
      </div>";
    }

    $resposta[0] .= "  
    </div>

    <div id='planoContaPai".$indice."' style='padding-top: 0; padding-bottom: 0'>
    </div>";

    return $resposta;
}

//Gera o saldo Inicial e Final - previsto e realizado
function retornoSaldo($datasFiltro, $datasFiltro2, $datasFiltro3, $datasFiltro4, $typeFiltro) {
  include('global_assets/php/conexao.php');

  if($typeFiltro == "D") {
    $sql_saldo_ini_p    = "select dbo.fnFluxoCaixaSaldoInicialPrevisto(".$_SESSION['UnidadeId'].",'".$datasFiltro['data_inicio_mes']."') as SaldoInicialPrevisto,
                                  dbo.fnFluxoCaixaSaldoInicialPrevisto(".$_SESSION['UnidadeId'].",'".$datasFiltro2['data_inicio_mes']."') as SaldoInicialPrevisto2,
                                  dbo.fnFluxoCaixaSaldoInicialPrevisto(".$_SESSION['UnidadeId'].",'".$datasFiltro3['data_inicio_mes']."') as SaldoInicialPrevisto3,
                                  dbo.fnFluxoCaixaSaldoInicialPrevisto(".$_SESSION['UnidadeId'].",'".$datasFiltro4['data_inicio_mes']."') as SaldoInicialPrevisto4,
                                  dbo.fnFluxoCaixaSaldoInicialRealizado(".$_SESSION['UnidadeId'].",'".$datasFiltro['data_inicio_mes']."') as SaldoInicialRealizado,
                                  dbo.fnFluxoCaixaSaldoInicialRealizado(".$_SESSION['UnidadeId'].",'".$datasFiltro2['data_inicio_mes']."') as SaldoInicialRealizado2,
                                  dbo.fnFluxoCaixaSaldoInicialRealizado(".$_SESSION['UnidadeId'].",'".$datasFiltro3['data_inicio_mes']."') as SaldoInicialRealizado3,
                                  dbo.fnFluxoCaixaSaldoInicialRealizado(".$_SESSION['UnidadeId'].",'".$datasFiltro4['data_inicio_mes']."') as SaldoInicialRealizado4,
                                  
                                  dbo.fnFluxoCaixaSaldoFinal(".$_SESSION['UnidadeId'].",'".$datasFiltro['data_inicio_mes']."') as SaldoFinal,
                                  dbo.fnFluxoCaixaSaldoFinal(".$_SESSION['UnidadeId'].",'".$datasFiltro2['data_inicio_mes']."') as SaldoFinal2,
                                  dbo.fnFluxoCaixaSaldoFinal(".$_SESSION['UnidadeId'].",'".$datasFiltro3['data_inicio_mes']."') as SaldoFinal3,
                                  dbo.fnFluxoCaixaSaldoFinal(".$_SESSION['UnidadeId'].",'".$datasFiltro4['data_inicio_mes']."') as SaldoFinal4";
    $resultSaldo = $conn->query($sql_saldo_ini_p);
    $saldo = $resultSaldo->fetch(PDO::FETCH_ASSOC);
  }else {
    $sql_saldo_ini_p    = "select dbo.fnFluxoCaixaSaldoInicialPrevisto(".$_SESSION['UnidadeId'].",'".$datasFiltro['data_fim_mes']."') as SaldoInicialPrevisto,
                                  dbo.fnFluxoCaixaSaldoInicialPrevisto(".$_SESSION['UnidadeId'].",'".$datasFiltro2['data_fim_mes']."') as SaldoInicialPrevisto2,
                                  dbo.fnFluxoCaixaSaldoInicialPrevisto(".$_SESSION['UnidadeId'].",'".$datasFiltro3['data_fim_mes']."') as SaldoInicialPrevisto3,
                                  dbo.fnFluxoCaixaSaldoInicialPrevisto(".$_SESSION['UnidadeId'].",'".$datasFiltro4['data_fim_mes']."') as SaldoInicialPrevisto4,
                                  dbo.fnFluxoCaixaSaldoInicialRealizado(".$_SESSION['UnidadeId'].",'".$datasFiltro['data_inicio_mes']."') as SaldoInicialRealizado,
                                  dbo.fnFluxoCaixaSaldoInicialRealizado(".$_SESSION['UnidadeId'].",'".$datasFiltro2['data_inicio_mes']."') as SaldoInicialRealizado2,
                                  dbo.fnFluxoCaixaSaldoInicialRealizado(".$_SESSION['UnidadeId'].",'".$datasFiltro3['data_inicio_mes']."') as SaldoInicialRealizado3,
                                  dbo.fnFluxoCaixaSaldoInicialRealizado(".$_SESSION['UnidadeId'].",'".$datasFiltro4['data_inicio_mes']."') as SaldoInicialRealizado4,
                                  
                                  dbo.fnFluxoCaixaSaldoFinal(".$_SESSION['UnidadeId'].",'".$datasFiltro['data_fim_mes']."') as SaldoFinal,
                                  dbo.fnFluxoCaixaSaldoFinal(".$_SESSION['UnidadeId'].",'".$datasFiltro2['data_fim_mes']."') as SaldoFinal2,
                                  dbo.fnFluxoCaixaSaldoFinal(".$_SESSION['UnidadeId'].",'".$datasFiltro3['data_fim_mes']."') as SaldoFinal3,
                                  dbo.fnFluxoCaixaSaldoFinal(".$_SESSION['UnidadeId'].",'".$datasFiltro4['data_fim_mes']."') as SaldoFinal4";
    $resultSaldo = $conn->query($sql_saldo_ini_p);
    $saldo = $resultSaldo->fetch(PDO::FETCH_ASSOC);
  }

  return $saldo;
}

//-------------------------------------------------------------------------------------
// loop dos dias 
//-------------------------------------------------------------------------------------

if(!isset($_POST["cmbCentroDeCustos"]) || !isset($_POST["cmbPlanoContas"])){
   print("
    <div class='d-flex flex-column justify-content-center' style='height:300px'>
      <div class='flex-row justify-content-center'>
        <p style='text-align:center'>Relatório não pode ser gerado. Ainda não foi cadastrado nenhum Centro de Custos e Plano de contas.</p>
      </div>
    </div>
  ");
   return;
}
$numDias    = $_POST["quantityDays"];
$diaInicio  = $_POST["dayInitial"];
$diaFim     = $_POST["dayEnd"];
$dataInicio = $_POST["inputDateInitial"];
$dataFim    = $_POST["inputDateEnd"];
$typeFiltro   = $_POST["typeDate"];
$ccFiltro   = rtrim(implode(',', $_POST["cmbCentroDeCustos"]));
$plFiltro   = rtrim(implode(',', $_POST["cmbPlanoContas"]));

//Consulta os grupos dos planos de contas
$sql = "SELECT GrConId, GrConNome, GrConNomePersonalizado, GrConCodigo, SituaChave
		FROM GrupoConta
		JOIN Situacao on SituaId = GrConStatus
	  WHERE GrConUnidade = ". $_SESSION['UnidadeId'] ." AND SituaChave = 'ATIVO' AND GrConCodigo != ''
		ORDER BY GrConCodigo ASC";
$result = $conn->query($sql);
$rowGrupo = $result->fetchAll(PDO::FETCH_ASSOC);

$print = "
  <div id='carouselExampleControls' class='carousel slide' data-ride='carousel'>
    <div class='carousel-inner'> ";

//Filtra por dia
if($typeFiltro == "D"){
  $mesArray = explode('-', $dataInicio);
  $anoData = $mesArray[0];
  $mesData = (int)$mesArray[1];

  $controlador = (($diaFim - $diaInicio) < 3 ) ? true : false; 

  $data = explode('-', $dataInicio);
  $dataFormatado = $data[0].'-'.$data[1].'-'.$data[2];
  $anoMesFormatado = $data[0].'-'.$data[1];

  $indice = 1;
  $receitaTotal = 0;
  for($i = $diaInicio;$i <= $diaFim;$i++) {
    $segundaColuna = false;
    $terceiraColuna = false;
    $quartaColuna = false;

    //limpa as variaveis
    if(isset($mes)) {
      unset($mes);
      unset($saldoIni_p1);
      unset($saldoIni_r1);
      unset($saldoIni_p2);
      unset($saldoIni_r2);
      unset($saldoIni_p3);
      unset($saldoIni_r3);
    }
    
    $dataFiltro = trim(date('Y-m',strtotime($dataInicio))).'-'.$i;   
  
    // $ultimo_dia = date("t", mktime(0,0,0,($i),'01',$anoData));
    $dataFiltroDiaInicio1 = $anoData.'-'.$mesData.'-'.$i;
    $dataFiltroDiaFim1 = $anoData.'-'.$mesData.'-'.$i;

    
    $dataFiltroDiaInicio2 = '';
    $dataFiltroDiaFim2 = '';
    
    $dataFiltroDiaInicio3 = '';
    $dataFiltroDiaFim3 = '';

    $dataFiltroDiaInicio4 = '';
    $dataFiltroDiaFim4 = '';
    
    $datasFiltro['data_inicio_mes'] = $dataFiltroDiaInicio1;
    $datasFiltro['data_fim_mes'] = $dataFiltroDiaFim1;
    $datasFiltro2['data_inicio_mes'] = '';
    $datasFiltro2['data_fim_mes'] = '';
    $datasFiltro3['data_inicio_mes'] = '';
    $datasFiltro3['data_fim_mes'] = '';
    $datasFiltro4['data_inicio_mes'] = '';
    $datasFiltro4['data_fim_mes'] = '';
    
    if(($i+1) <= $diaFim) {
      $dataFiltro = trim(date('Y-m',strtotime($dataInicio))).'-'.($i+1);      
  
      // $ultimo_dia = date("t", mktime(0,0,0,$i+1,'01',$anoData));
      $dataFiltroDiaInicio2 = $anoData.'-'.$mesData.'-'.($i+1);
      $dataFiltroDiaFim2 = $anoData.'-'.$mesData.'-'.($i+1);
      
      $datasFiltro2['data_inicio_mes'] = $dataFiltroDiaInicio2;
      $datasFiltro2['data_fim_mes'] = $dataFiltroDiaFim2;

      $segundaColuna = true;
    }

    if(($i+2) <= $diaFim) {
      $dataFiltro = trim(date('Y-m',strtotime($dataInicio))).'-'.($i+1);      
  
      // $ultimo_dia = date("t", mktime(0,0,0,$i+1,'01',$anoData));
      $dataFiltroDiaInicio3 = $anoData.'-'.$mesData.'-'.($i+2);
      $dataFiltroDiaFim3 = $anoData.'-'.$mesData.'-'.($i+2);
      
      $datasFiltro3['data_inicio_mes'] = $dataFiltroDiaInicio3;
      $datasFiltro3['data_fim_mes'] = $dataFiltroDiaFim3;

      $terceiraColuna = true;
    }

    if(($i+3) <= $diaFim)  {
      $dataFiltro = trim(date('Y-m',strtotime($dataInicio))).'-'.($i+1);      
  
      // $ultimo_dia = date("t", mktime(0,0,0,$i+1,'01',$anoData));
      $dataFiltroDiaInicio4 = $anoData.'-'.$mesData.'-'.($i+3);
      $dataFiltroDiaFim4 = $anoData.'-'.$mesData.'-'.($i+3);
      
      $datasFiltro4['data_inicio_mes'] = $dataFiltroDiaInicio4;
      $datasFiltro4['data_fim_mes'] = $dataFiltroDiaFim4;

      $quartaColuna = true;
    }

    $arraySaldo  = retornoSaldo($datasFiltro, $datasFiltro2, $datasFiltro3, $datasFiltro4, $typeFiltro);
    $saldoIni_p1 = $arraySaldo['SaldoInicialPrevisto'];
    $saldoIni_r1 = $arraySaldo['SaldoInicialRealizado'];
    $saldoIni_p2 = $arraySaldo['SaldoInicialPrevisto2'];
    $saldoIni_r2 = $arraySaldo['SaldoInicialRealizado2'];
    $saldoIni_p3 = $arraySaldo['SaldoInicialPrevisto3'];
    $saldoIni_r3 = $arraySaldo['SaldoInicialRealizado3']; 
    $saldoIni_p4 = $arraySaldo['SaldoInicialPrevisto4'];
    $saldoIni_r4 = $arraySaldo['SaldoInicialRealizado4']; 

    $saldoFin1 = $arraySaldo['SaldoFinal'];
    $saldoFin2 = $arraySaldo['SaldoFinal2'];
    $saldoFin3 = $arraySaldo['SaldoFinal3'];
    $saldoFin4 = $arraySaldo['SaldoFinal4'];

    //Por padrão a data estava vindo com um valor a mais, porém isso foi corrigido logo abaixo
    $dataFormatado = $data[0].'-'.$data[1].'-'.$data[2];
    $anoMesFormatado = $data[0].'-'.$data[1];
    
    /* Obs.: utf8_encode é para o servidor da AZURE. Localmente não precisaria, mas para o servidor sim. */  
    $print .= " 
      <div class='carousel-item ".($i == $diaInicio ? " active":"")."'> 
        <div class='row'>
          <div class='col-lg-12'>
            <!-- Basic responsive configuration -->
            <div class='card-body' >
              <div class='row'>
                  
                <div class='col-lg-3'>
                </div>

                <div class='col-lg-2' style='text-align:center; border-top: 2px solid #ccc; padding-top: 1rem; margin-right: 2px; '>
                  <span><strong>".str_pad($i, 2, '0', STR_PAD_LEFT)." ".utf8_encode(ucfirst(strftime("%B de %Y", strtotime($dataFormatado))))."</strong></span>
                </div>

                  ".($segundaColuna ? "<div class='col-lg-2' style='text-align:center; border-top: 2px solid #ccc; padding-top: 1rem; margin-left: 2px;'>
                  <span><strong>".str_pad($i+1, 2, '0', STR_PAD_LEFT)." ".utf8_encode(ucfirst(strftime("%B de %Y", strtotime($anoMesFormatado ."-".str_pad($i+1, 2, '0', STR_PAD_LEFT)))))."</strong></span>
                </div>" : "")."

                ".($terceiraColuna ? "<div class='col-lg-2' style='text-align:center; border-top: 2px solid #ccc; padding-top: 1rem; margin-left: 2px;'>
                  <span><strong>".str_pad($i+2, 2, '0', STR_PAD_LEFT)." ".utf8_encode(ucfirst(strftime("%B de %Y", strtotime($anoMesFormatado ."-".str_pad($i+2, 2, '0', STR_PAD_LEFT)))))."</strong></span>
                </div>" : "")." 
                
                ".($quartaColuna ? "<div class='col-lg-2' style='text-align:center; border-top: 2px solid #ccc; padding-top: 1rem; margin-left: 2px;'>
                  <span><strong>".str_pad($i+3, 2, '0', STR_PAD_LEFT)." ".utf8_encode(ucfirst(strftime("%B de %Y", strtotime($anoMesFormatado ."-".str_pad($i+3, 2, '0', STR_PAD_LEFT)))))."</strong></span>
                </div>" : "")." 
              </div>
            </div>
          </div>
        </div>";
  
    //------------------------------------------------------------------------------
    // limpa as variaveis que vao receber o plano de contas
    // <!--  usei essas variaveis para nao ter q fazer dois loops -->
    $print_corpo = '';
    $print_sai = '';
    //==============================================================================
    
    $print_corpo .= "
      <div class='card-body' style='padding-top: 0;'>
        <div class='row' style='background: #607D8B; line-height: 3rem; box-sizing:border-box; color:white;'>
          <div class='col-lg-3' style='border-right: 1px dotted black;'>
              <strong></strong>
          </div> 
          
         <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
            <div class='row'>
              <div class='col-md-6'>
                <span><strong>Previsto</strong></span>
              </div>
              <div class='col-md-6'>
                <span><strong>Realizado</strong></span>
              </div>
            </div>
          </div>
          
          ".($segundaColuna ? "
          <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
            <div class='row'>
              <div class='col-md-6'>
                <span><strong>Previsto</strong></span>
              </div>
              <div class='col-md-6'>
                <span><strong>Realizado</strong></span>
              </div>
            </div>
          </div>" : "")."
          
          ".($terceiraColuna ? "
          <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
            <div class='row'>
              <div class='col-md-6'>
                <span><strong>Previsto</strong></span>
              </div>
              <div class='col-md-6'>
                <span><strong>Realizado</strong></span>
              </div>
            </div>
          </div>" : "")."

          ".($quartaColuna ? "
          <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
            <div class='row'>
              <div class='col-md-6'>
                <span><strong>Previsto</strong></span>
              </div>
              <div class='col-md-6'>
                <span><strong>Realizado</strong></span>
              </div>
            </div>
          </div>" : "")."

        </div>
      </div>
      
      <div class='row''>
        <!-- SALDO INICIAL -->
        <div class='col-lg-12'>
          <!-- Basic responsive configuration -->
            <div class='card-body' style='padding-top: 0;'>
              <div class='row' style='background: #CCCCCC; line-height: 3rem; box-sizing:border-box'>
                <div class='col-lg-3' style='border-right: 1px dotted black;'>
                  <span><strong>Saldo Inicial</strong></span>
                </div>";

    $corSaldoPrevisto1 = ($saldoIni_p1 < 0) ? 'style = "color: red;"' : '';
    $corSaldoPrevisto2 = ($saldoIni_p2 < 0 && $segundaColuna) ? 'style = "color: red;"' : '';
    $corSaldoPrevisto3 = ($saldoIni_p3 < 0 && $terceiraColuna) ? 'style = "color: red;"' : '';
    $corSaldoPrevisto4 = ($saldoIni_p4 < 0 && $quartaColuna) ? 'style = "color: red;"' : '';

    $corSaldoRealizado1 = ($saldoIni_r1 < 0) ? 'style = "color: red;"' : '';
    $corSaldoRealizado2 = ($saldoIni_r2 < 0 && $segundaColuna) ? 'style = "color: red;"' : '';
    $corSaldoRealizado3 = ($saldoIni_r3 < 0 && $terceiraColuna) ? 'style = "color: red;"' : '';
    $corSaldoRealizado4 = ($saldoIni_r4 < 0 && $quartaColuna) ? 'style = "color: red;"' : '';

    $print_corpo .= "
                <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                  <div class='row'>
                    <div class='col-md-6'>
                      <span ".$corSaldoPrevisto1."><b>".mostraValor($saldoIni_p1)."</b></span>
                    </div>

                    <div class='col-md-6'>
                      <span ".$corSaldoRealizado1."><b>".mostraValor($saldoIni_r1)."</b></span>
                    </div>
                  </div>
                </div>
                
                ".($segundaColuna ? "
                <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                  <div class='row'>
                    <div class='col-md-6'>
                    <span ".$corSaldoPrevisto2."><b>".mostraValor($saldoIni_p2)."</b></span>
                  </div>

                  <div class='col-md-6'>
                    <span ".$corSaldoRealizado2."><b>".mostraValor($saldoIni_r2)."</b></span>
                  </div>
                  </div>
                </div>" : "")."

                ".($terceiraColuna ? "
                <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                  <div class='row'>
                    <div class='col-md-6'>
                    <span ".$corSaldoPrevisto3."><b>".mostraValor($saldoIni_p3)."</b></span>
                  </div>

                  <div class='col-md-6'>
                    <span ".$corSaldoRealizado3."><b>".mostraValor($saldoIni_r3)."</b></span>
                  </div>
                  </div>
                </div>" : "")."

                ".($quartaColuna ? "
                <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                  <div class='row'>
                    <div class='col-md-6'>
                    <span ".$corSaldoPrevisto4."><b>".mostraValor($saldoIni_p4)."</b></span>
                  </div>

                  <div class='col-md-6'>
                    <span ".$corSaldoRealizado4."><b>".mostraValor($saldoIni_r4)."</b></span>
                  </div>
                  </div>
                </div>" : "")."

              </div>
            </div>
        </div>
      </div>
      <!-- SALDO INICIAL -->";

    //Primeiro o tipo de grupo é definido como entrada, pois sempre começa pela receita, dps dentro do próprio loop ele se torna 'S' para trazer a saída
    $tipoGrupo = 'E';

    $totalPrevistoPrimeiraColuna = 0;
    $totalPrevistoSegundaColuna = 0;
    $totalPrevistoTerceiraColuna = 0;
    $totalPrevistoQuartaColuna = 0;

    $totalRealizadoPrimeiraColuna = 0;
    $totalRealizadoSegundaColuna = 0;
    $totalRealizadoTerceiraColuna = 0;
    $totalRealizadoQuartaColuna = 0;

    $percentual = 0;
    $percentualPrevisto1 = 0;
    $percentualRealizado1 = 0;
    $percentualPrevisto2 = 0;
    $percentualRealizado2 = 0;
    $percentualPrevisto3 = 0;
    $percentualRealizado3 = 0;
    $percentualPrevisto4 = 0;
    $percentualRealizado4 = 0;

    foreach($rowGrupo as $grupo) {
      $nomeGrupo = $grupo['GrConNomePersonalizado'] != '' ? $grupo['GrConNomePersonalizado'] :  $grupo['GrConNome'];
      
      $sql = "SELECT dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicio1."', '".$dataFiltroDiaFim1."', '".$tipoGrupo."') as PrevistoSaidaGrupo1,
                     dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicio1."', '".$dataFiltroDiaFim1."', '".$tipoGrupo."') as RealizadoSaidaGrupo1,
                     dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicio2."', '".$dataFiltroDiaFim2."', '".$tipoGrupo."') as PrevistoSaidaGrupo2,
                     dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicio2."', '".$dataFiltroDiaFim2."', '".$tipoGrupo."') as RealizadoSaidaGrupo2,
                     dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicio3."', '".$dataFiltroDiaFim3."', '".$tipoGrupo."') as PrevistoSaidaGrupo3,
                     dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicio3."', '".$dataFiltroDiaFim3."', '".$tipoGrupo."') as RealizadoSaidaGrupo3,
                     dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicio4."', '".$dataFiltroDiaFim4."', '".$tipoGrupo."') as PrevistoSaidaGrupo4,
                     dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicio4."', '".$dataFiltroDiaFim4."', '".$tipoGrupo."') as RealizadoSaidaGrupo4";
      $resultGrupo = $conn->query($sql);
      $rowTotalGrupo = $resultGrupo->fetch(PDO::FETCH_ASSOC);

      $totalGrupo1 = '';
      $totalPrevisto1 = $rowTotalGrupo['PrevistoSaidaGrupo1'];
      $totalRealizado1 = $rowTotalGrupo['RealizadoSaidaGrupo1'];
      $totalPrevisto2 = $rowTotalGrupo['PrevistoSaidaGrupo2'];
      $totalRealizado2 = $rowTotalGrupo['RealizadoSaidaGrupo2'];
      $totalPrevisto3 = $rowTotalGrupo['PrevistoSaidaGrupo3'];
      $totalRealizado3 = $rowTotalGrupo['RealizadoSaidaGrupo3'];
      $totalPrevisto4 = $rowTotalGrupo['PrevistoSaidaGrupo4'];
      $totalRealizado4 = $rowTotalGrupo['RealizadoSaidaGrupo4'];

      if($tipoGrupo == 'E') {
        $totalPrevistoPrimeiraColuna = $totalPrevisto1;
        $totalPrevistoSegundaColuna = $totalPrevisto2;
        $totalPrevistoTerceiraColuna = $totalPrevisto3;
        $totalPrevistoQuartaColuna = $totalPrevisto4;

        $totalRealizadoPrimeiraColuna = $totalRealizado1;
        $totalRealizadoSegundaColuna = $totalRealizado2;
        $totalRealizadoTerceiraColuna = $totalRealizado3;
        $totalRealizadoQuartaColuna = $totalRealizado4;
      }else {
        $totalPrevistoPrimeiraColuna -= $totalPrevisto1;
        $totalPrevistoSegundaColuna -= $totalPrevisto2;
        $totalPrevistoTerceiraColuna -= $totalPrevisto3;
        $totalPrevistoQuartaColuna -= $totalPrevisto4;

        $totalRealizadoPrimeiraColuna -= $totalRealizado1;
        $totalRealizadoSegundaColuna -= $totalRealizado2;
        $totalRealizadoTerceiraColuna -= $totalRealizado3;
        $totalRealizadoQuartaColuna -= $totalRealizado4;
      }
     
      $print_corpo .= "<!-- ENTRADA -->
      <div class='row'>
        <div class='col-lg-12'>
          <!-- Basic responsive configuration -->
          
          <div class='card-body' style='padding-top: 0; padding-bottom: 0'>
            <div class='row' style='background: #607D8B; line-height: 3rem; box-sizing:border-box; color:white;'>
              <div class='col-lg-3' style='border-right: 1px dotted black;'>
                  <strong>".$nomeGrupo."</strong>
              </div> 
              
              <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                <div class='row'>
                  <div class='col-md-6'>
                    <span><strong>".mostraValor($totalPrevisto1)."</strong></span>
                  </div>
                  <div class='col-md-6'>
                    <span><strong>".mostraValor($totalRealizado1)."</strong></span>
                  </div>
                </div>
              </div>
              
              ".($segundaColuna ? "
              <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                <div class='row'>
                  <div class='col-md-6'>
                    <span><strong>".mostraValor($totalPrevisto2)."</strong></span>
                  </div>
                  <div class='col-md-6'>
                      <span><strong>".mostraValor($totalRealizado2)."</strong></span>
                  </div>
                </div>
              </div>" : "")."
              
              ".($terceiraColuna ? "
              <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                <div class='row'>
                  <div class='col-md-6'>
                    <span><strong>".mostraValor($totalPrevisto3)."</strong></span>
                  </div>
                  <div class='col-md-6'>
                        <span><strong>".mostraValor($totalRealizado3)."</strong></span>
                  </div>
                </div>
              </div>" : "")."

              ".($quartaColuna ? "
              <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                <div class='row'>
                  <div class='col-md-6'>
                    <span><strong>".mostraValor($totalPrevisto4)."</strong></span>
                  </div>
                  <div class='col-md-6'>
                        <span><strong>".mostraValor($totalRealizado4)."</strong></span>
                  </div>
                </div>
              </div>" : "")."

            </div>
          </div> 
          
          <div class='card-body' style='padding-top: 0;padding-bottom: 0'>";

        $mes = retornaBuscaComoArray($datasFiltro, $datasFiltro2, $datasFiltro3, $datasFiltro4, $plFiltro, $grupo['GrConId'], $tipoGrupo);

        $datasFiltro['data_inicio_mes'] = $dataFiltroDiaInicio1;
        $datasFiltro['data_fim_mes'] = $dataFiltroDiaFim1;
        
        $arrayPlanoConta = isset($mes['pl']) ? $mes['pl'] : null;

        if(isset($arrayPlanoConta) && (!empty($arrayPlanoConta))) {
          foreach($arrayPlanoConta as $planoConta){
            $resultadoPlanoConta = planoConta($planoConta["PlConId"], $planoConta["PlConNome"], $planoConta["PL_Previsto"], 
                                            ($segundaColuna)?$planoConta['PL_Previsto2']:"",
                                            ($terceiraColuna)?$planoConta['PL_Previsto3']:"",
                                            ($quartaColuna)?$planoConta['PL_Previsto4']:"",
                                            $planoConta["PL_Realizado"],
                                            ($segundaColuna)?$planoConta['PL_Realizado2']:"",
                                            ($terceiraColuna)?$planoConta['PL_Realizado3']:"", 
                                            ($quartaColuna)?$planoConta['PL_Realizado4']:"", 
                                            $segundaColuna, $terceiraColuna, $quartaColuna, 
                                            $datasFiltro, 
                                            ($segundaColuna)?$datasFiltro2:"",
                                            ($terceiraColuna)?$datasFiltro3:"",
                                            ($quartaColuna)?$datasFiltro4:"", 
                                            $grupo['GrConCodigo'], $tipoGrupo, $indice);
            $print_corpo .= $resultadoPlanoConta[0];

            $indice++;
          }   
        }

        //Os totalizadores só aparecem em alguns grupos específicos
        $tituloTotalizador = 'sem rodape';
        if($grupo['GrConCodigo'] == 1) {
          $tituloTotalizador = 'Receita operacional líquida';

          $receitaTotal1 = isset($rowTotalGrupo['PrevistoSaidaGrupo1']) ? $rowTotalGrupo['PrevistoSaidaGrupo1'] : 0;
          $receitaTotal2 = isset($rowTotalGrupo['PrevistoSaidaGrupo2']) ? $rowTotalGrupo['PrevistoSaidaGrupo2'] : 0;
          $receitaTotal3 = isset($rowTotalGrupo['PrevistoSaidaGrupo3']) ? $rowTotalGrupo['PrevistoSaidaGrupo3'] : 0;
          $receitaTotal4 = isset($rowTotalGrupo['PrevistoSaidaGrupo4']) ? $rowTotalGrupo['PrevistoSaidaGrupo4'] : 0;
        } else if($grupo['GrConCodigo'] == 5) {
          $tituloTotalizador = 'Margem de contribuição';
        }else if($grupo['GrConCodigo'] == 6) {
          $tituloTotalizador = 'Resultado Operacional';
        }else if($grupo['GrConCodigo'] == 7) {
          $tituloTotalizador = 'Variação de caixa';
        }

        if($receitaTotal1 != 0) {
          $percentualPrevisto1 = ($totalPrevistoPrimeiraColuna * 100) / $receitaTotal1;
          $percentualRealizado1 = ($totalRealizadoPrimeiraColuna * 100) / $receitaTotal1;
  
          $percentualPrevisto1 = is_float($percentualPrevisto1) ? number_format($percentualPrevisto1, 1, '.', '') : $percentualPrevisto1;
          $percentualRealizado1 = is_float($percentualRealizado1) ? number_format($percentualRealizado1, 1, '.', '') : $percentualRealizado1;
        }

        $print_corpo .= "
          </div>
            <div class='row'>
              <div class='col-lg-12'>
                <!-- Basic responsive configuration -->
                  <div class='card-body' style=''>";

        $corTotalPrevisto1 = ($totalPrevistoPrimeiraColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
        $corTotalRealizado1 = ($totalRealizadoPrimeiraColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
        
        $corPercentualPrevisto1 = ($percentualPrevisto1 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
        $corPercentualRealizado1 = ($percentualRealizado1 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
        
        if($tituloTotalizador != 'sem rodape') {
          $print_corpo .="
                    <div class='row' style='background: #CCCCCC; line-height: 3rem; box-sizing:border-box'>
                      <div class='col-lg-3' style='border-right: 1px dotted black;'>
                        <span><strong>(=) ".$tituloTotalizador."</strong></span>
                      </div>
  
                      <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                        <div class='row'>
                          <div class='col-md-6'>
                            <span ".$corTotalPrevisto1.">".mostraValor($totalPrevistoPrimeiraColuna)."</span>
                          </div>
  
                          <div class='col-md-6'>
                            <span ".$corTotalRealizado1.">".mostraValor($totalRealizadoPrimeiraColuna)."</span>
                          </div>
                        </div>
                      </div>";

        if($segundaColuna) {
          if($receitaTotal2 != 0) {
            $percentualPrevisto2 = ($totalPrevistoSegundaColuna * 100) / $receitaTotal2;
            $percentualRealizado2 = ($totalRealizadoSegundaColuna * 100) / $receitaTotal2;
  
            $percentualPrevisto2 = is_float($percentualPrevisto2) ? number_format($percentualPrevisto2, 1, '.', '') : $percentualPrevisto2;
            $percentualRealizado2 = is_float($percentualRealizado2) ? number_format($percentualRealizado2, 1, '.', '') : $percentualRealizado2;
          }

          $corTotalPrevisto2 = ($totalPrevistoSegundaColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
          $corTotalRealizado2 = ($totalRealizadoSegundaColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
        
          $corPercentualPrevisto2 = ($percentualPrevisto2 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
          $corPercentualRealizado2 = ($percentualRealizado2 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';

          $print_corpo .="
                      <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                        <div class='row'>
                          <div class='col-md-6'>
                            <span ".$corTotalPrevisto2.">".mostraValor($totalPrevistoSegundaColuna)."</span>
                          </div>
  
                          <div class='col-md-6'>
                            <span ".$corTotalRealizado2.">".mostraValor($totalRealizadoSegundaColuna)."</span>
                          </div>
                        </div>
                      </div>";
        }

        if($terceiraColuna) {
          if($receitaTotal3 != 0) {
            $percentualPrevisto3 = ($totalPrevistoTerceiraColuna * 100) / $receitaTotal3;
            $percentualRealizado3 = ($totalRealizadoTerceiraColuna * 100) / $receitaTotal3;
  
            $percentualPrevisto3 = is_float($percentualPrevisto3) ? number_format($percentualPrevisto3, 1, '.', '') : $percentualPrevisto3;
            $percentualRealizado3 = is_float($percentualRealizado3) ? number_format($percentualRealizado3, 1, '.', '') : $percentualRealizado3;
          }

          $corTotalPrevisto3 = ($totalPrevistoTerceiraColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
          $corTotalRealizado3 = ($totalRealizadoTerceiraColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
          
          $corPercentualPrevisto3 = ($percentualPrevisto3 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
          $corPercentualRealizado3 = ($percentualRealizado3 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';

          
          $print_corpo .= "
                      <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                        <div class='row'>
                          <div class='col-md-6'>
                            <span ".$corTotalPrevisto3.">".mostraValor($totalPrevistoTerceiraColuna)."</span>
                          </div>

                          <div class='col-md-6'>
                            <span ".$corTotalRealizado3.">".mostraValor($totalRealizadoTerceiraColuna)."</span>
                          </div>
                        </div>
                      </div>";
        }

        if($quartaColuna) {
          if($receitaTotal4 != 0) {
            $percentualPrevisto4 = ($totalPrevistoQuartaColuna * 100) / $receitaTotal4;
            $percentualRealizado4 = ($totalRealizadoQuartaColuna * 100) / $receitaTotal4;
  
            $percentualPrevisto4 = is_float($percentualPrevisto4) ? number_format($percentualPrevisto4, 1, '.', '') : $percentualPrevisto4;
            $percentualRealizado4 = is_float($percentualRealizado4) ? number_format($percentualRealizado4, 1, '.', '') : $percentualRealizado4;
          }

          $corTotalPrevisto4 = ($totalPrevistoQuartaColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
          $corTotalRealizado4 = ($totalRealizadoQuartaColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
          
          $corPercentualPrevisto4 = ($percentualPrevisto4 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
          $corPercentualRealizado4 = ($percentualRealizado4 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';

          $print_corpo .= "
                      <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                        <div class='row'>
                          <div class='col-md-6'>
                            <span ".$corTotalPrevisto4.">".mostraValor($totalPrevistoQuartaColuna)."</span>
                          </div>

                          <div class='col-md-6'>
                            <span ".$corTotalRealizado4.">".mostraValor($totalRealizadoQuartaColuna)."</span>
                          </div>
                        </div>
                      </div>";
        }
          
        $print_corpo .= " </div>
  
                    <div class='row' style='background: #CCCCCC; line-height: 3rem; box-sizing:border-box'>
                      <div class='col-lg-3' style='border-right: 1px dotted black;'>
                        <span style='padding-left: 20px;'><strong>".$tituloTotalizador." (%)</strong></span>
                      </div>
  
                      <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                        <div class='row'>
                          <div class='col-md-6'>
                            <span ".$corPercentualPrevisto1.">".$percentualPrevisto1."%</span>
                          </div>
  
                          <div class='col-md-6'>
                            <span ".$corPercentualRealizado1.">".$percentualRealizado1."%</span>
                          </div>
                        </div>
                      </div>
  
                      ".($segundaColuna ? "
                      <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                        <div class='row'>
                          <div class='col-md-6'>
                            <span ".$corPercentualPrevisto2.">".$percentualPrevisto2."%</span>
                          </div>
  
                          <div class='col-md-6'>
                              <span ".$corPercentualRealizado2.">".$percentualRealizado2."%</span>
                          </div>
                        </div>
                      </div>" : "")."
  
                      ".($terceiraColuna ? "
                      <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                        <div class='row'>
                          <div class='col-md-6'>
                            <span ".$corPercentualPrevisto3.">".$percentualPrevisto3."%</span>
                          </div>
    
                          <div class='col-md-6'>
                              <span ".$corPercentualRealizado3.">".$percentualRealizado3."%</span>
                          </div>
                        </div>
                      </div>" : "")."

                      ".($quartaColuna ? "
                      <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                        <div class='row'>
                          <div class='col-md-6'>
                            <span ".$corPercentualPrevisto4.">".$percentualPrevisto4."%</span>
                          </div>
    
                          <div class='col-md-6'>
                              <span ".$corPercentualRealizado4.">".$percentualRealizado4."%</span>
                          </div>
                        </div>
                      </div>" : "")."
                    </div>";
        }
      
        $print_corpo .= "        
                  </div>
                </div>
                </div>
                
              </div>
            </div>
            <!-- TOTAL ENTRADA -->
            <!-- ENTRADA -->";
            
      $tipoGrupo = 'S';
    }

        //----------------------------------------------------------------------
        //junta tudo no $print principal
        $print .= $print_corpo . "
            <!-- SALDO FINAL -->
            <div class='row' style='margin-top: 1rem;'>
              <div class='col-lg-12'>
                <!-- Basic responsive configuration -->
                  <div class='card-body' style='padding-top: 0;'>
                  <div class='row' style='background: #CCCCCC; line-height: 3rem; box-sizing:border-box'>
                    <div class='col-lg-3' style='border-right: 1px dotted black;'>
                      <span><strong>SALDO FINAL</strong></span>
                    </div>";

          //Caso o valor seja igual ou menor a zero os valores ficam vermelhos
          $corSaldoFinal1 = ($saldoFin1 < 0) ? 'style = "color: red;"' : '';
          $corSaldoFinal2 = ($saldoFin2 < 0 && $segundaColuna) ? 'style = "color: red;"' : '';
          $corSaldoFinal3 = ($saldoFin3 < 0 && $terceiraColuna) ? 'style = "color: red;"' : '';
          $corSaldoFinal4 = ($saldoFin4 < 0 && $quartaColuna) ? 'style = "color: red;"' : '';
        
          $print .= "
                    <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                      <div class='row'>
                        <div class='col-md-12'>
                          <span ".$corSaldoFinal1."><b>".mostraValor($saldoFin1)."</b></span>
                        </div>
                      </div>
                    </div>
                      
                    ".($segundaColuna ? "
                    <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                      <div class='row'>
                        <div class='col-md-12'>
                          <span ".$corSaldoFinal2."><b>".mostraValor($saldoFin2)."</b></span>
                        </div>
                      </div>
                    </div>" : "")."

                    ".($terceiraColuna ? "
                    <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                    <div class='row'>
                      <div class='col-md-12'>
                        <span ".$corSaldoFinal3."><b>".mostraValor($saldoFin3)."</b></span>
                      </div>
                    </div>
                  </div>" : "")."

                  ".($quartaColuna ? "
                    <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                    <div class='row'>
                      <div class='col-md-12'>
                        <span ".$corSaldoFinal4."><b>".mostraValor($saldoFin4)."</b></span>
                      </div>
                    </div>
                  </div>" : "")."
                      
                </div>
              </div>
            </div>
          </div>
        </div> 
        <!-- SALDO FINAL -->";

        /*
        $print .= " <!-- SALDO FINAL -->
            <div class='row' style='margin-top: 2rem;'>
              <div class='col-lg-12'>
                <!-- Basic responsive configuration -->
                  <div class='card-body' style='padding-top: 0;'>
                    <div class='row'>
                      <div class='col-lg-12' style='background: #607D8B; color:white; line-height: 3rem; box-sizing:border-box'>
                        <span><strong>COMPARATIVO DO PERÍODO (ENTRADA E SAÍDA): </strong></span>
                      </div>
                    </div>

                    <div class='row col-lg-12' style='background: #fff; line-height: 3rem; box-sizing:border-box'>";

        $print .= " <span>0,00%</span>";

        $print .= "           <!--span>TOTAL SAÍDAS / TOTAL ENTRADAS * 100 = 100%</span-->
                    </div>
                  </div>
              </div>
            </div>
            <!-- SALDO FINAL --> 
          </div>  
          "; 
        */
    
    //Usado para ele não criar uma coluna extra
    if($controlador) {
      break;
    }

    //Usado para q o loop para depois que todos os dias sejam mostradados, pois sempre está acrescentando valores ao índice
    //evitando inclusive alguns bugs como uma coluna extra
    if(($i+1) <= $diaFim) {
        $i += 3;
    } 
  }
}else {
  $data1 = new DateTime( $dataInicio );
  $data2 = new DateTime( $dataFim );

  //limpa as variaveis
  if(isset($mes)) {
    unset($mes);
    unset($saldoIni_p1);
    unset($saldoIni_r1);
    unset($saldoIni_p2);
    unset($saldoIni_r2);
    unset($saldoIni_p3);
    unset($saldoIni_r3);
  }

  $intervaloMeses = $data1->diff( $data2 )->m;

  $mesInicioArray = explode('-', $dataInicio);
  $anoTela = $mesInicioArray[0];
  $mesInicio = (int)$mesInicioArray[1];
  
  $mesFimArray = explode('-', $dataFim);
  $mesFinal = (int)$mesFimArray[1];
  
  $controlador = (($mesFinal - $mesInicio) < 3 ) ? true : false; 
  
  //Formatando os números abaixo de dez
  $mesInicio = str_pad($mesInicio, 2, '0', STR_PAD_LEFT);
  
  $pagina = 0;
  $numeroPaginasCont = 0;
  
  $indice = 1;
  $receitaTotal = 0;
  for($i = $mesInicio;$i <= $mesFinal;$i++) {
    $segundaColuna = false;
    $terceiraColuna = false;
    $quartaColuna = false;

    $dataFiltro = trim(date('Y-m',strtotime($dataInicio))).'-'.($i);  
    
    $ultimo_dia = date("t", mktime(0,0,0,($i),'01',$anoTela));
    $dataFiltroDiaInicioMes1 = $anoTela.'-'.($i).'-01';
    $dataFiltroDiaFimMes1 = $anoTela.'-'.($i).'-'.$ultimo_dia;
    $dataFiltroDiaInicioMes2 = '';
    $dataFiltroDiaFimMes2 = '';
    $dataFiltroDiaInicioMes3 = '';
    $dataFiltroDiaFimMes3 = '';
    $dataFiltroDiaInicioMes4 = '';
    $dataFiltroDiaFimMes4 = '';

    $datasFiltro['data_inicio_mes'] = $dataFiltroDiaInicioMes1;
    $datasFiltro['data_fim_mes'] = $dataFiltroDiaFimMes1;
    $datasFiltro2['data_inicio_mes'] = '';
    $datasFiltro2['data_fim_mes'] = '';
    $datasFiltro3['data_inicio_mes'] = '';
    $datasFiltro3['data_fim_mes'] = '';
    $datasFiltro4['data_inicio_mes'] = '';
    $datasFiltro4['data_fim_mes'] = '';

    if(($i+1) <= $mesFinal) {
      $dataFiltro = trim(date('Y-m',strtotime($dataInicio))).'-'.($i+1);      
  
      $ultimo_dia = date("t", mktime(0,0,0,($i+1),'01',$anoTela));
      $dataFiltroDiaInicioMes2 = $anoTela.'-'.($i+1).'-01';
      $dataFiltroDiaFimMes2 = $anoTela.'-'.($i+1).'-'.$ultimo_dia;
      
      $datasFiltro2['data_inicio_mes'] = $dataFiltroDiaInicioMes2;
      $datasFiltro2['data_fim_mes'] = $dataFiltroDiaFimMes2;

      $segundaColuna = true;
    }

    if(($i+2) <= $mesFinal) {
      $dataFiltro = trim(date('Y-m',strtotime($dataInicio))).'-'.($i+1);      
  
      $ultimo_dia = date("t", mktime(0,0,0,($i+2),'01',$anoTela));
      $dataFiltroDiaInicioMes3 = $anoTela.'-'.($i+2).'-01';
      $dataFiltroDiaFimMes3 = $anoTela.'-'.($i+2).'-'.$ultimo_dia;
      
      $datasFiltro3['data_inicio_mes'] = $dataFiltroDiaInicioMes3;
      $datasFiltro3['data_fim_mes'] = $dataFiltroDiaFimMes3;

      $terceiraColuna = true;
    }

    if(($i+3) <= $mesFinal) {
      $dataFiltro = trim(date('Y-m',strtotime($dataInicio))).'-'.($i+1);      
  
      $ultimo_dia = date("t", mktime(0,0,0,($i+3),'01',$anoTela));
      $dataFiltroDiaInicioMes4 = $anoTela.'-'.($i+3).'-01';
      $dataFiltroDiaFimMes4 = $anoTela.'-'.($i+3).'-'.$ultimo_dia;
      
      $datasFiltro4['data_inicio_mes'] = $dataFiltroDiaInicioMes4;
      $datasFiltro4['data_fim_mes'] = $dataFiltroDiaFimMes4;

      $quartaColuna = true;
    }

    $arraySaldo  = retornoSaldo($datasFiltro, $datasFiltro2, $datasFiltro3, $datasFiltro4, $typeFiltro);
    $saldoIni_p1 = $arraySaldo['SaldoInicialPrevisto'];
    $saldoIni_r1 = $arraySaldo['SaldoInicialRealizado'];
    $saldoIni_p2 = $arraySaldo['SaldoInicialPrevisto2'];
    $saldoIni_r2 = $arraySaldo['SaldoInicialRealizado2'];
    $saldoIni_p3 = $arraySaldo['SaldoInicialPrevisto3'];
    $saldoIni_r3 = $arraySaldo['SaldoInicialRealizado3']; 
    $saldoIni_p4 = $arraySaldo['SaldoInicialPrevisto4'];
    $saldoIni_r4 = $arraySaldo['SaldoInicialRealizado4']; 

    $saldoFin1 = $arraySaldo['SaldoFinal'];
    $saldoFin2 = $arraySaldo['SaldoFinal2'];
    $saldoFin3 = $arraySaldo['SaldoFinal3'];
    $saldoFin4 = $arraySaldo['SaldoFinal4'];

    /* Obs.: utf8_encode é para o servidor da AZURE. Localmente não precisaria, mas para o servidor sim. */  
    $print .= " 
    <div class='carousel-item ".($i == $mesInicio ? " active":"")."'> 
      <div class='row'>
        <div class='col-lg-12'>
          <!-- Basic responsive configuration -->
          <div class='card-body' >
            <div class='row'>
                
              <div class='col-lg-3'>
              </div>

              <div class='col-lg-2' style='text-align:center; border-top: 2px solid #ccc; padding-top: 1rem; margin-right: 2px; '>
                <span><strong>".utf8_encode(ucfirst(strftime("%B de %Y", strtotime($anoTela."-".str_pad($i, 2, '0', STR_PAD_LEFT)))))."</strong></span>
              </div>
              

              ".($segundaColuna ? "<div class='col-lg-2' style='text-align:center; border-top: 2px solid #ccc; padding-top: 1rem; margin-left: 2px;'>
                <span><strong>". utf8_encode(ucfirst(strftime("%B de %Y", strtotime($anoTela."-".str_pad($i+1, 2, '0', STR_PAD_LEFT)))))."</strong></span>
              </div>" : "")."

              ".($terceiraColuna ? "<div class='col-lg-2' style='text-align:center; border-top: 2px solid #ccc; padding-top: 1rem; margin-left: 2px;'>
                <span><strong>". utf8_encode(ucfirst(strftime("%B de %Y", strtotime($anoTela."-".str_pad($i+2, 2, '0', STR_PAD_LEFT)))))."</strong></span>
              </div>" : "")."

              ".($quartaColuna ? "<div class='col-lg-2' style='text-align:center; border-top: 2px solid #ccc; padding-top: 1rem; margin-left: 2px;'>
                <span><strong>". utf8_encode(ucfirst(strftime("%B de %Y", strtotime($anoTela."-".str_pad($i+3, 2, '0', STR_PAD_LEFT)))))."</strong></span>
              </div>" : "")."
            </div>
          </div>
        </div>
      </div>";

      //------------------------------------------------------------------------------
    // limpa as variaveis que vao receber o plano de contas
    // <!--  usei essas variaveis para nao ter q fazer dois loops -->
    $print_corpo = '';
    $print_sai = '';
    //==============================================================================
    
    $print_corpo .= "
      <div class='card-body' style='padding-top: 0;'>
        <div class='row' style='background: #607D8B; line-height: 3rem; box-sizing:border-box; color:white;'>
          <div class='col-lg-3' style='border-right: 1px dotted black;'>
              <strong></strong>
          </div> 
          
         <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
            <div class='row'>
              <div class='col-md-6'>
                <span><strong>Previsto</strong></span>
              </div>
              <div class='col-md-6'>
                <span><strong>Realizado</strong></span>
              </div>
            </div>
          </div>
          
          ".($segundaColuna ? "
          <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
            <div class='row'>
              <div class='col-md-6'>
                <span><strong>Previsto</strong></span>
              </div>
              <div class='col-md-6'>
                <span><strong>Realizado</strong></span>
              </div>
            </div>
          </div>" : "")."
          
          ".($terceiraColuna ? "
          <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
            <div class='row'>
              <div class='col-md-6'>
                <span><strong>Previsto</strong></span>
              </div>
              <div class='col-md-6'>
                <span><strong>Realizado</strong></span>
              </div>
            </div>
          </div>" : "")."

          ".($quartaColuna ? "
          <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
            <div class='row'>
              <div class='col-md-6'>
                <span><strong>Previsto</strong></span>
              </div>
              <div class='col-md-6'>
                <span><strong>Realizado</strong></span>
              </div>
            </div>
          </div>" : "")."

        </div>
      </div>
      
      <div class='row''>
        <!-- SALDO INICIAL -->
        <div class='col-lg-12'>
          <!-- Basic responsive configuration -->
            <div class='card-body' style='padding-top: 0;'>
              <div class='row' style='background: #CCCCCC; line-height: 3rem; box-sizing:border-box'>
                <div class='col-lg-3' style='border-right: 1px dotted black;'>
                  <span><strong>Saldo Inicial</strong></span>
                </div>";

            $corSaldoPrevisto1 = ($saldoIni_p1 < 0) ? 'style = "color: red;"' : '';
            $corSaldoPrevisto2 = ($saldoIni_p2 < 0 && $segundaColuna) ? 'style = "color: red;"' : '';
            $corSaldoPrevisto3 = ($saldoIni_p3 < 0 && $terceiraColuna) ? 'style = "color: red;"' : '';
            $corSaldoPrevisto4 = ($saldoIni_p4 < 0 && $quartaColuna) ? 'style = "color: red;"' : '';

            $corSaldoRealizado1 = ($saldoIni_r1 < 0) ? 'style = "color: red;"' : '';
            $corSaldoRealizado2 = ($saldoIni_r2 < 0 && $segundaColuna) ? 'style = "color: red;"' : '';
            $corSaldoRealizado3 = ($saldoIni_r3 < 0 && $terceiraColuna) ? 'style = "color: red;"' : '';
            $corSaldoRealizado4 = ($saldoIni_r4 < 0 && $quartaColuna) ? 'style = "color: red;"' : '';

            $print_corpo .= "
                <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                  <div class='row'>
                    <div class='col-md-6'>
                      <span ".$corSaldoPrevisto1."><b>".mostraValor($saldoIni_p1)."</b></span>
                    </div>

                    <div class='col-md-6'>
                      <span ".$corSaldoRealizado1."><b>".mostraValor($saldoIni_r1)."</b></span>
                    </div>
                  </div>
                </div>
                
                ".($segundaColuna ? "
                <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                  <div class='row'>
                    <div class='col-md-6'>
                    <span ".$corSaldoPrevisto2."><b>".mostraValor($saldoIni_p2)."</b></span>
                  </div>

                  <div class='col-md-6'>
                    <span ".$corSaldoRealizado2."><b>".mostraValor($saldoIni_r2)."</b></span>
                  </div>
                  </div>
                </div>" : "")."

                ".($terceiraColuna ? "
                <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                  <div class='row'>
                    <div class='col-md-6'>
                    <span ".$corSaldoPrevisto3."><b>".mostraValor($saldoIni_p3)."</b></span>
                  </div>

                  <div class='col-md-6'>
                    <span ".$corSaldoRealizado3."><b>".mostraValor($saldoIni_r3)."</b></span>
                  </div>
                  </div>
                </div>" : "")."

                ".($quartaColuna ? "
                <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                  <div class='row'>
                    <div class='col-md-6'>
                    <span ".$corSaldoPrevisto4."><b>".mostraValor($saldoIni_p4)."</b></span>
                  </div>

                  <div class='col-md-6'>
                    <span ".$corSaldoRealizado4."><b>".mostraValor($saldoIni_r4)."</b></span>
                  </div>
                  </div>
                </div>" : "")."

              </div>
            </div>
        </div>
      </div>
      <!-- SALDO INICIAL -->";

      //Primeiro o tipo de grupo é definido como entrada, pois sempre começa pela receita, dps dentro do próprio loop ele se torna 'S' para trazer a saída
      $tipoGrupo = 'E';
  
      $totalPrevistoPrimeiraColuna = 0;
      $totalPrevistoSegundaColuna = 0;
      $totalPrevistoTerceiraColuna = 0;
      $totalPrevistoQuartaColuna = 0;
  
      $totalRealizadoPrimeiraColuna = 0;
      $totalRealizadoSegundaColuna = 0;
      $totalRealizadoTerceiraColuna = 0;
      $totalRealizadoQuartaColuna = 0;
  
      $percentual = 0;
      $percentualPrevisto1 = 0;
      $percentualRealizado1 = 0;
      $percentualPrevisto2 = 0;
      $percentualRealizado2 = 0;
      $percentualPrevisto3 = 0;
      $percentualRealizado3 = 0;
      $percentualPrevisto4 = 0;
      $percentualRealizado4 = 0;

      foreach($rowGrupo as $grupo) {
        $nomeGrupo = $grupo['GrConNomePersonalizado'] != '' ? $grupo['GrConNomePersonalizado'] :  $grupo['GrConNome'];
        
        $sql = "SELECT dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicioMes1."', '".$dataFiltroDiaFimMes1."', '".$tipoGrupo."') as PrevistoSaidaGrupo1,
                       dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicioMes1."', '".$dataFiltroDiaFimMes1."', '".$tipoGrupo."') as RealizadoSaidaGrupo1,
                       dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicioMes2."', '".$dataFiltroDiaFimMes2."', '".$tipoGrupo."') as PrevistoSaidaGrupo2,
                       dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicioMes2."', '".$dataFiltroDiaFimMes2."', '".$tipoGrupo."') as RealizadoSaidaGrupo2,
                       dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicioMes3."', '".$dataFiltroDiaFimMes3."', '".$tipoGrupo."') as PrevistoSaidaGrupo3,
                       dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicioMes3."', '".$dataFiltroDiaFimMes3."', '".$tipoGrupo."') as RealizadoSaidaGrupo3,
                       dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicioMes4."', '".$dataFiltroDiaFimMes4."', '".$tipoGrupo."') as PrevistoSaidaGrupo4,
                       dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicioMes4."', '".$dataFiltroDiaFimMes4."', '".$tipoGrupo."') as RealizadoSaidaGrupo4";
        $resultGrupo = $conn->query($sql);
        $rowTotalGrupo = $resultGrupo->fetch(PDO::FETCH_ASSOC);
  
        $totalGrupo1 = '';
        $totalPrevisto1 = $rowTotalGrupo['PrevistoSaidaGrupo1'];
        $totalRealizado1 = $rowTotalGrupo['RealizadoSaidaGrupo1'];
        $totalPrevisto2 = $rowTotalGrupo['PrevistoSaidaGrupo2'];
        $totalRealizado2 = $rowTotalGrupo['RealizadoSaidaGrupo2'];
        $totalPrevisto3 = $rowTotalGrupo['PrevistoSaidaGrupo3'];
        $totalRealizado3 = $rowTotalGrupo['RealizadoSaidaGrupo3'];
        $totalPrevisto4 = $rowTotalGrupo['PrevistoSaidaGrupo4'];
        $totalRealizado4 = $rowTotalGrupo['RealizadoSaidaGrupo4'];
  
        if($tipoGrupo == 'E') {
          $totalPrevistoPrimeiraColuna = $totalPrevisto1;
          $totalPrevistoSegundaColuna = $totalPrevisto2;
          $totalPrevistoTerceiraColuna = $totalPrevisto3;
          $totalPrevistoQuartaColuna = $totalPrevisto4;
  
          $totalRealizadoPrimeiraColuna = $totalRealizado1;
          $totalRealizadoSegundaColuna = $totalRealizado2;
          $totalRealizadoTerceiraColuna = $totalRealizado3;
          $totalRealizadoQuartaColuna = $totalRealizado4;
        }else {
          $totalPrevistoPrimeiraColuna -= $totalPrevisto1;
          $totalPrevistoSegundaColuna -= $totalPrevisto2;
          $totalPrevistoTerceiraColuna -= $totalPrevisto3;
          $totalPrevistoQuartaColuna -= $totalPrevisto4;
  
          $totalRealizadoPrimeiraColuna -= $totalRealizado1;
          $totalRealizadoSegundaColuna -= $totalRealizado2;
          $totalRealizadoTerceiraColuna -= $totalRealizado3;
          $totalRealizadoQuartaColuna -= $totalRealizado4;
        }
       
        $print_corpo .= "<!-- ENTRADA -->
        <div class='row'>
          <div class='col-lg-12'>
            <!-- Basic responsive configuration -->
            
            <div class='card-body' style='padding-top: 0; padding-bottom: 0'>
              <div class='row' style='background: #607D8B; line-height: 3rem; box-sizing:border-box; color:white;'>
                <div class='col-lg-3' style='border-right: 1px dotted black;'>
                    <strong>".$nomeGrupo."</strong>
                </div> 
                
                <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                  <div class='row'>
                    <div class='col-md-6'>
                      <span><strong>".mostraValor($totalPrevisto1)."</strong></span>
                    </div>
                    <div class='col-md-6'>
                      <span><strong>".mostraValor($totalRealizado1)."</strong></span>
                    </div>
                  </div>
                </div>
                
                ".($segundaColuna ? "
                <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                  <div class='row'>
                    <div class='col-md-6'>
                      <span><strong>".mostraValor($totalPrevisto2)."</strong></span>
                    </div>
                    <div class='col-md-6'>
                        <span><strong>".mostraValor($totalRealizado2)."</strong></span>
                    </div>
                  </div>
                </div>" : "")."
                
                ".($terceiraColuna ? "
                <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                  <div class='row'>
                    <div class='col-md-6'>
                      <span><strong>".mostraValor($totalPrevisto3)."</strong></span>
                    </div>
                    <div class='col-md-6'>
                          <span><strong>".mostraValor($totalRealizado3)."</strong></span>
                    </div>
                  </div>
                </div>" : "")."
  
                ".($quartaColuna ? "
                <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                  <div class='row'>
                    <div class='col-md-6'>
                      <span><strong>".mostraValor($totalPrevisto4)."</strong></span>
                    </div>
                    <div class='col-md-6'>
                          <span><strong>".mostraValor($totalRealizado4)."</strong></span>
                    </div>
                  </div>
                </div>" : "")."
  
              </div>
            </div> 
            
            <div class='card-body' style='padding-top: 0;padding-bottom: 0'>";
  
          $mes = retornaBuscaComoArray($datasFiltro, $datasFiltro2, $datasFiltro3, $datasFiltro4, $plFiltro, $grupo['GrConId'], $tipoGrupo);
  
          $datasFiltro['data_inicio_mes'] = $dataFiltroDiaInicioMes1;
          $datasFiltro['data_fim_mes'] = $dataFiltroDiaFimMes1;
          
          $arrayPlanoConta = isset($mes['pl']) ? $mes['pl'] : null;
  
          if(isset($arrayPlanoConta) && (!empty($arrayPlanoConta))) {
            foreach($arrayPlanoConta as $planoConta){
              $resultadoPlanoConta = planoConta($planoConta["PlConId"], $planoConta["PlConNome"], $planoConta["PL_Previsto"], 
                                              ($segundaColuna)?$planoConta['PL_Previsto2']:"",
                                              ($terceiraColuna)?$planoConta['PL_Previsto3']:"",
                                              ($quartaColuna)?$planoConta['PL_Previsto4']:"",
                                              $planoConta["PL_Realizado"],
                                              ($segundaColuna)?$planoConta['PL_Realizado2']:"",
                                              ($terceiraColuna)?$planoConta['PL_Realizado3']:"", 
                                              ($quartaColuna)?$planoConta['PL_Realizado4']:"", 
                                              $segundaColuna, $terceiraColuna, $quartaColuna, $datasFiltro, 
                                              ($segundaColuna)?$datasFiltro2:"",
                                              ($terceiraColuna)?$datasFiltro3:"",
                                              ($quartaColuna)?$datasFiltro4:"", $grupo['GrConCodigo'], $tipoGrupo, $indice);
              $print_corpo .= $resultadoPlanoConta[0];
  
              $indice++;
            }   
          }
  
          //Os totalizadores só aparecem em alguns grupos específicos
          $tituloTotalizador = 'sem rodape';
          if($grupo['GrConCodigo'] == 1) {
            $tituloTotalizador = 'Receita operacional líquida';
  
            $receitaTotal1 = isset($rowTotalGrupo['PrevistoSaidaGrupo1']) ? $rowTotalGrupo['PrevistoSaidaGrupo1'] : 0;
            $receitaTotal2 = isset($rowTotalGrupo['PrevistoSaidaGrupo2']) ? $rowTotalGrupo['PrevistoSaidaGrupo2'] : 0;
            $receitaTotal3 = isset($rowTotalGrupo['PrevistoSaidaGrupo3']) ? $rowTotalGrupo['PrevistoSaidaGrupo3'] : 0;
            $receitaTotal4 = isset($rowTotalGrupo['PrevistoSaidaGrupo4']) ? $rowTotalGrupo['PrevistoSaidaGrupo4'] : 0;
          } else if($grupo['GrConCodigo'] == 5) {
            $tituloTotalizador = 'Margem de contribuição';
          }else if($grupo['GrConCodigo'] == 6) {
            $tituloTotalizador = 'Resultado Operacional';
          }else if($grupo['GrConCodigo'] == 7) {
            $tituloTotalizador = 'Variação de caixa';
          }
  
          if($receitaTotal1 != 0) {
            $percentualPrevisto1 = ($totalPrevistoPrimeiraColuna * 100) / $receitaTotal1;
            $percentualRealizado1 = ($totalRealizadoPrimeiraColuna * 100) / $receitaTotal1;
    
            $percentualPrevisto1 = is_float($percentualPrevisto1) ? number_format($percentualPrevisto1, 1, '.', '') : $percentualPrevisto1;
            $percentualRealizado1 = is_float($percentualRealizado1) ? number_format($percentualRealizado1, 1, '.', '') : $percentualRealizado1;
          }

          $print_corpo .= "
            </div>
              <div class='row'>
                <div class='col-lg-12'>
                  <!-- Basic responsive configuration -->
                    <div class='card-body' style=''>";

          $corTotalPrevisto1 = ($totalPrevistoPrimeiraColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
          $corTotalRealizado1 = ($totalRealizadoPrimeiraColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
          
          $corPercentualPrevisto1 = ($percentualPrevisto1 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
          $corPercentualRealizado1 = ($percentualRealizado1 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
          
          if($tituloTotalizador != 'sem rodape') {
            $print_corpo .="
                      <div class='row' style='background: #CCCCCC; line-height: 3rem; box-sizing:border-box'>
                        <div class='col-lg-3' style='border-right: 1px dotted black;'>
                          <span><strong>(=) ".$tituloTotalizador."</strong></span>
                        </div>
    
                        <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                          <div class='row'>
                            <div class='col-md-6'>
                              <span ".$corTotalPrevisto1.">".mostraValor($totalPrevistoPrimeiraColuna)."</span>
                            </div>
    
                            <div class='col-md-6'>
                              <span ".$corTotalRealizado1.">".mostraValor($totalRealizadoPrimeiraColuna)."</span>
                            </div>
                          </div>
                        </div>";
  
          if($segundaColuna) {
            if($receitaTotal2 != 0) {
              $percentualPrevisto2 = ($totalPrevistoSegundaColuna * 100) / $receitaTotal2;
              $percentualRealizado2 = ($totalRealizadoSegundaColuna * 100) / $receitaTotal2;
    
              $percentualPrevisto2 = is_float($percentualPrevisto2) ? number_format($percentualPrevisto2, 1, '.', '') : $percentualPrevisto2;
              $percentualRealizado2 = is_float($percentualRealizado2) ? number_format($percentualRealizado2, 1, '.', '') : $percentualRealizado2;
            }

            $corTotalPrevisto2 = ($totalPrevistoSegundaColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
            $corTotalRealizado2 = ($totalRealizadoSegundaColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';

            $corPercentualPrevisto2 = ($percentualPrevisto2 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
            $corPercentualRealizado2 = ($percentualRealizado2 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
  
            $print_corpo .="
                        <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                          <div class='row'>
                            <div class='col-md-6'>
                              <span ".$corTotalPrevisto2.">".mostraValor($totalPrevistoSegundaColuna)."</span>
                            </div>
    
                            <div class='col-md-6'>
                              <span ".$corTotalRealizado2.">".mostraValor($totalRealizadoSegundaColuna)."</span>
                            </div>
                          </div>
                        </div>";
          }
  
          if($terceiraColuna) {
            if($receitaTotal3 != 0) {
              $percentualPrevisto3 = ($totalPrevistoTerceiraColuna * 100) / $receitaTotal3;
              $percentualRealizado3 = ($totalRealizadoTerceiraColuna * 100) / $receitaTotal3;
    
              $percentualPrevisto3 = is_float($percentualPrevisto3) ? number_format($percentualPrevisto3, 1, '.', '') : $percentualPrevisto3;
              $percentualRealizado3 = is_float($percentualRealizado3) ? number_format($percentualRealizado3, 1, '.', '') : $percentualRealizado3;
            }

            $corTotalPrevisto3 = ($totalPrevistoTerceiraColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
            $corTotalRealizado3 = ($totalRealizadoTerceiraColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';

            $corPercentualPrevisto3 = ($percentualPrevisto3 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
            $corPercentualRealizado3 = ($percentualRealizado3 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';  
  
            $print_corpo .= "
                        <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                          <div class='row'>
                            <div class='col-md-6'>
                              <span ".$corTotalPrevisto3.">".mostraValor($totalPrevistoTerceiraColuna)."</span>
                            </div>
  
                            <div class='col-md-6'>
                              <span ".$corTotalRealizado3.">".mostraValor($totalRealizadoTerceiraColuna)."</span>
                            </div>
                          </div>
                        </div>";
          }
  
          if($quartaColuna) {
            if($receitaTotal4 != 0) {
              $percentualPrevisto4 = ($totalPrevistoQuartaColuna * 100) / $receitaTotal4;
              $percentualRealizado4 = ($totalRealizadoQuartaColuna * 100) / $receitaTotal4;
    
              $percentualPrevisto4 = is_float($percentualPrevisto4) ? number_format($percentualPrevisto4, 1, '.', '') : $percentualPrevisto4;
              $percentualRealizado4 = is_float($percentualRealizado4) ? number_format($percentualRealizado4, 1, '.', '') : $percentualRealizado4;
            }

            $corTotalPrevisto4 = ($totalPrevistoQuartaColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
            $corTotalRealizado4 = ($totalRealizadoQuartaColuna < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
            
            $corPercentualPrevisto4 = ($percentualPrevisto4 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
            $corPercentualRealizado4 = ($percentualRealizado4 < 0 && $tituloTotalizador != 'Receita operacional líquida') ? 'style = "color: red;"' : '';
  
            $print_corpo .= "
                        <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                          <div class='row'>
                            <div class='col-md-6'>
                              <span ".$corTotalPrevisto4.">".mostraValor($totalPrevistoQuartaColuna)."</span>
                            </div>
  
                            <div class='col-md-6'>
                              <span ".$corTotalRealizado4.">".mostraValor($totalRealizadoQuartaColuna)."</span>
                            </div>
                          </div>
                        </div>";
          }
            
          $print_corpo .= " </div>
    
                      <div class='row' style='background: #CCCCCC; line-height: 3rem; box-sizing:border-box'>
                        <div class='col-lg-3' style='border-right: 1px dotted black;'>
                          <span style='padding-left: 20px;'><strong>".$tituloTotalizador." (%)</strong></span>
                        </div>
    
                        <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                          <div class='row'>
                            <div class='col-md-6'>
                              <span ".$corPercentualPrevisto1.">".$percentualPrevisto1."%</span>
                            </div>
    
                            <div class='col-md-6'>
                              <span ".$corPercentualRealizado1.">".$percentualRealizado1."%</span>
                            </div>
                          </div>
                        </div>
    
                        ".($segundaColuna ? "
                        <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                          <div class='row'>
                            <div class='col-md-6'>
                              <span ".$corPercentualPrevisto2.">".$percentualPrevisto2."%</span>
                            </div>
    
                            <div class='col-md-6'>
                                <span ".$corPercentualRealizado2.">".$percentualRealizado2."%</span>
                            </div>
                          </div>
                        </div>" : "")."
    
                        ".($terceiraColuna ? "
                        <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                          <div class='row'>
                            <div class='col-md-6'>
                              <span ".$corPercentualPrevisto3.">".$percentualPrevisto3."%</span>
                            </div>
      
                            <div class='col-md-6'>
                                <span ".$corPercentualRealizado3.">".$percentualRealizado3."%</span>
                            </div>
                          </div>
                        </div>" : "")."
  
                        ".($quartaColuna ? "
                        <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                          <div class='row'>
                            <div class='col-md-6'>
                              <span ".$corPercentualPrevisto4.">".$percentualPrevisto4."%</span>
                            </div>
      
                            <div class='col-md-6'>
                                <span ".$corPercentualRealizado4.">".$percentualRealizado4."%</span>
                            </div>
                          </div>
                        </div>" : "")."
                      </div>";
          }
        
          $print_corpo .= "        
                    </div>
                  </div>
                  </div>
                  
                </div>
              </div>
              <!-- TOTAL ENTRADA -->
              <!-- ENTRADA -->";
              
        $tipoGrupo = 'S';
      }
  
      //----------------------------------------------------------------------
        //junta tudo no $print principal
        $print .= $print_corpo . "
            <!-- SALDO FINAL -->
            <div class='row' style='margin-top: 1rem;'>
              <div class='col-lg-12'>
                <!-- Basic responsive configuration -->
                  <div class='card-body' style='padding-top: 0;'>
                  <div class='row' style='background: #CCCCCC; line-height: 3rem; box-sizing:border-box'>
                    <div class='col-lg-3' style='border-right: 1px dotted black;'>
                      <span><strong>SALDO FINAL</strong></span>
                    </div>;";

        //Caso o valor seja igual ou menor a zero os valores ficam vermelhos
        $corSaldoFinal1 = ($saldoFin1 < 0) ? 'style = "color: red;"' : '';
        $corSaldoFinal2 = ($saldoFin2 < 0 && $segundaColuna) ? 'style = "color: red;"' : '';
        $corSaldoFinal3 = ($saldoFin3 < 0 && $terceiraColuna) ? 'style = "color: red;"' : '';
        $corSaldoFinal4 = ($saldoFin4 < 0 && $quartaColuna) ? 'style = "color: red;"' : '';
        
        $print .= "
                    <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                      <div class='row'>
                        <div class='col-md-12'>
                          <span ".$corSaldoFinal1."><b>".mostraValor($saldoFin1)."</b></span>
                        </div>
                      </div>
                    </div>
                      
                    ".($segundaColuna ? "
                    <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                      <div class='row'>
                        <div class='col-md-12'>
                          <span ".$corSaldoFinal2."><b>".mostraValor($saldoFin2)."</b></span>
                        </div>
                      </div>
                    </div>" : "")."

                    ".($terceiraColuna ? "
                    <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                    <div class='row'>
                      <div class='col-md-12'>
                        <span ".$corSaldoFinal3."><b>".mostraValor($saldoFin3)."</b></span>
                      </div>
                    </div>
                  </div>" : "")."

                  ".($quartaColuna ? "
                    <div class='dataOpeningBalance col-lg-2' style='border-right: 1px dotted black; text-align:center;'>
                    <div class='row'>
                      <div class='col-md-12'>
                        <span ".$corSaldoFinal4."><b>".mostraValor($saldoFin4)."</b></span>
                      </div>
                    </div>
                  </div>" : "")."
                      
                </div>
              </div>
            </div>
          </div>
        </div> 
        <!-- SALDO FINAL -->";

        /*
        $print .= " <!-- SALDO FINAL -->
            <div class='row' style='margin-top: 2rem;'>
              <div class='col-lg-12'>
                <!-- Basic responsive configuration -->
                  <div class='card-body' style='padding-top: 0;'>
                    <div class='row'>
                      <div class='col-lg-12' style='background: #607D8B; color:white; line-height: 3rem; box-sizing:border-box'>
                        <span><strong>COMPARATIVO DO PERÍODO (ENTRADA E SAÍDA): </strong></span>
                      </div>
                    </div>

                    <div class='row col-lg-12' style='background: #fff; line-height: 3rem; box-sizing:border-box'>";

        $print .= " <span>0,00%</span>";

        $print .= "           <!--span>TOTAL SAÍDAS / TOTAL ENTRADAS * 100 = 100%</span-->
                    </div>
                  </div>
              </div>
            </div>
            <!-- SALDO FINAL --> 
          </div>  
          "; 
        */
    
    //Usado para ele não criar uma coluna extra
    if($controlador) {
      break;
    }

    //Usado para q o loop para depois que todos os dias sejam mostradados, pois sempre está acrescentando valores ao índice
    //evitando inclusive alguns bugs como uma coluna extra
    if(($i+1) <= $mesFinal) {
        $i += 3;
    } 
     //echo 'Data início: '.$mesInicio. ' mês final: '.$mesFinal;
  }
} 

//O data-interval='0' é para desativar o giro automático (a página mudava automaticamente)
$print .= "
  </div>
    <a class='carousel-control-prev' href='#carouselExampleControls' role='button' data-slide='prev' style='color:black;' data-interval='0'>
      <span class='carousel-control-prev-icon' aria-hidden='true' ><img src='global_assets/images/lamparinas/seta-left.png' width='32' /></span>
      <span class='sr-only'>Previous</span>
    </a>

    <a class='carousel-control-next' href='#carouselExampleControls' role='button' data-slide='next' style='color:black;' data-interval='0'>
      <span class='carousel-control-next-icon' aria-hidden='true'><img src='global_assets/images/lamparinas/seta-right.png' width='32' /></span>
      <span class='sr-only'>Next</span>
    </a>
  </div>";

//$total1 = microtime(true) - $inicio1;
//echo '<span style="background-color:yellow; padding: 10px; font-size:24px;">Tempo de execução do script: ' . round($total1, 2).' segundos</span>'; 

print($print);