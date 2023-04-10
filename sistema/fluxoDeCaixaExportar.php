<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Fluxo Caixa Excel';

include('global_assets/php/conexao.php');

//	var_dump($count);die;

//Tabelela de exemplo
/*
$dadosXls  = "";
$dadosXls .= "  <table>";
$dadosXls .= "     <tr>";
$dadosXls .= "        <th scope='col'></th>";
$dadosXls .= "        <th colspan='2'>09 de junho de 2022</th>";
$dadosXls .= "     </tr>";

$dadosXls .= "     <tr>";
$dadosXls .= "        <th scope='col' bgcolor='#607D8B'></th>";
$dadosXls .= "        <th scope='col' bgcolor='#607D8B'>Previsto</th>";
$dadosXls .= "        <th scope='col' bgcolor='#607D8B'>Realizado</th>";
$dadosXls .= "     </tr>";

$dadosXls .= "     <tr>";
$dadosXls .= "        <td scope='col' bgcolor='#CCCCCC'>Saldo Inicial</td>";
$dadosXls .= "        <td scope='col' bgcolor='#CCCCCC'>0,00</td>"; 
$dadosXls .= "        <td scope='col' bgcolor='#CCCCCC'>0,00</td>";
$dadosXls .= "     </tr>";

$dadosXls .= "     <tr>";
$dadosXls .= "        <td scope='col' bgcolor='#607D8B'>Custo com Compras</td>";
$dadosXls .= "        <td scope='col' bgcolor='#607D8B'>0,00</td>"; 
$dadosXls .= "        <td scope='col' bgcolor='#607D8B'>0,00</td>";
$dadosXls .= "     </tr>";

$dadosXls .= "     <tr>";
$dadosXls .= "        <td scope='col' bgcolor='#CCCCCC'>Frete</td>";
$dadosXls .= "        <td scope='col' bgcolor='#CCCCCC'>0,00</td>"; 
$dadosXls .= "        <td scope='col' bgcolor='#CCCCCC'>0,00</td>";
$dadosXls .= "     </tr>";

$dadosXls .= "     <tr>";
$dadosXls .= "        <td scope='col' bgcolor='#CCCCCC'>&nbsp;&nbsp;".'Frete de Recebimento'."</td>";
$dadosXls .= "        <td scope='col' bgcolor='#CCCCCC'>0,00</td>"; 
$dadosXls .= "        <td scope='col' bgcolor='#CCCCCC'>0,00</td>";
$dadosXls .= "     </tr>";

$dadosXls .= "     <tr>";
$dadosXls .= "        <td scope='col' bgcolor='#eeeeee'>&nbsp;&nbsp;&nbsp;&nbsp;".'Secretaria de Saude'."</td>";
$dadosXls .= "        <td scope='col' bgcolor='#eeeeee'>0,00</td>"; 
$dadosXls .= "        <td scope='col' bgcolor='#eeeeee'>0,00</td>";
$dadosXls .= "     </tr>";

$dadosXls .= "  </table>";

//Backup da consulta

//declaramos uma variavel para monstarmos a tabela
$dadosXls  = "";
$dadosXls .= "  <table>";

//Filtra pelos dias
if($typeFiltro == "D"){
    //$mesArray = explode('-', $dataInicio);
    $mesArray = explode('-', '2022-06-01');
    $anoData = $mesArray[0];
    $mesData = (int)$mesArray[1];

    $controlador = (($diaFim - $diaInicio) < 3 ) ? true : false; 

    //$data = explode('-', $dataInicio);
    $data = explode('-', '2022-06-01');
    $dataFormatado = $data[0].'-'.$data[1].'-'.$data[2];
    $anoMesFormatado = $data[0].'-'.$data[1];

    $indice = 1;
    $receitaTotal = 0;

    for($i = $diaInicio;$i <= $diaFim;$i++) {
        $dataFiltro = trim(date('Y-m',strtotime($dataInicio))).'-'.$i;   
  
        // $ultimo_dia = date("t", mktime(0,0,0,($i),'01',$anoData));
        $dataFiltroDiaInicio1 = $anoData.'-'.$mesData.'-'.$i;
        $dataFiltroDiaFim1 = $anoData.'-'.$mesData.'-'.$i;

        //Por padrão a data estava vindo com um valor a mais, porém isso foi corrigido logo abaixo
        //$data = explode('-', $dataInicio);
        $data = explode('-', '2022-06-01');
        $dataFormatado = $data[0].'-'.$data[1].'-'.$data[2];
        $anoMesFormatado = $data[0].'-'.$data[1];

        $dadosXls .= "     <tr>";
        $dadosXls .= "        <th scope='col'></th>";
        $dadosXls .= "        <th colspan='2'>".str_pad($i, 2, '0', STR_PAD_LEFT)." ".ucfirst(strftime("%B de %Y", strtotime($dataFormatado)))."</th>";
        $dadosXls .= "     </tr>";
        
        $dadosXls .= "     <tr>";
        $dadosXls .= "        <th scope='col' bgcolor='#607D8B'></th>";
        $dadosXls .= "        <th scope='col' bgcolor='#607D8B'>Previsto</th>";
        $dadosXls .= "        <th scope='col' bgcolor='#607D8B'>Realizado</th>";
        $dadosXls .= "     </tr>";

        $dadosXls .= "     <tr>";
        $dadosXls .= "        <td scope='col' bgcolor='#CCCCCC'>Saldo Inicial</td>";
        $dadosXls .= "        <td scope='col' bgcolor='#CCCCCC'>0,00</td>"; 
        $dadosXls .= "        <td scope='col' bgcolor='#CCCCCC'>0,00</td>";
        $dadosXls .= "     </tr>";

        //Primeiro o tipo de grupo é definido como entrada, pois sempre começa pela receita, dps dentro do próprio loop ele se torna 'S' para trazer a saída
        $tipoGrupo = 'E';

        foreach($rowGrupo as $grupo) {
            $sql = "SELECT dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicio1."', '".$dataFiltroDiaFim1."', '".$tipoGrupo."') as PrevistoSaidaGrupo1,
                            dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltroDiaInicio1."', '".$dataFiltroDiaFim1."', '".$tipoGrupo."') as RealizadoSaidaGrupo1";
            $resultGrupo = $conn->query($sql);
            $rowTotalGrupo = $resultGrupo->fetch(PDO::FETCH_ASSOC);

            $totalGrupo1 = '';
            $totalPrevisto1 = $rowTotalGrupo['PrevistoSaidaGrupo1'];
            $totalRealizado1 = $rowTotalGrupo['RealizadoSaidaGrupo1'];

            $nomeGrupo = $grupo['GrConNomePersonalizado'] != '' ? $grupo['GrConNomePersonalizado'] :  $grupo['GrConNome'];

            $dadosXls .= "     <tr>";
            $dadosXls .= "        <td scope='col' bgcolor='#607D8B'>".$nomeGrupo."</td>";
            $dadosXls .= "        <td scope='col' bgcolor='#607D8B'>".mostraValor($totalPrevisto1)."</td>"; 
            $dadosXls .= "        <td scope='col' bgcolor='#607D8B'>".mostraValor($totalRealizado1)."</td>";
            $dadosXls .= "     </tr>";

            $tipoGrupo = 'S';
        }

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
}

$dadosXls .= "  </table>";
*/
setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

//Consulta os planos de Contas  que pertecem ao  determinado grupo
function retornaBuscaComoArray($datasFiltro, $plFiltro, $grupoPlanoConta, $tipo) {
    include('global_assets/php/conexao.php');
  
    if($tipo == 'E') {
      $sql = "SELECT PlConId, PlConNome,
                    dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro['data_inicio']."', '".$datasFiltro['data_Final']."', '".$tipo."') as PrevistoSaida,
                    dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro['data_inicio']."', '".$datasFiltro['data_Final']."', '".$tipo."') as RealizadoSaida
              FROM PlanoConta
              WHERE PlConId in ($plFiltro) and PlConNatureza = 'R' AND PlConGrupo = $grupoPlanoConta AND PlConTipo = 'S'
              ORDER BY PlConNome ASC";
      $result = $conn->query($sql);
      $rowPlanoContaSintetica = $result->fetchAll(PDO::FETCH_ASSOC);
    }else {
      $sql = "SELECT PlConId, PlConNome,
                    dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro['data_inicio']."', '".$datasFiltro['data_Final']."', '".$tipo."') as PrevistoSaida,
                    dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro['data_inicio']."', '".$datasFiltro['data_Final']."', '".$tipo."') as RealizadoSaida
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

          /*
          //Traz os filhos do plano de conta sintético
          if($tipo == 'E') {
            $sql = "SELECT PlConId, PlConNome,
                          dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro."', '".$datasFiltro."', '".$tipo."') as planoContaAnaliticaPrevistoSaida,
                          dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro."', '".$datasFiltro."', '".$tipo."') as planoContaAnaliticaRealizadoSaida
                    FROM PlanoConta
                    WHERE  PlConUnidade = " . $_SESSION['UnidadeId'] . " and PlConPlanoContaPai = ". $rowCC['PlConId'] ."
                    ORDER BY PlConNome ASC";
            $result = $conn->query($sql);
            $rowPLanoContaAnalitica = $result->fetchAll(PDO::FETCH_ASSOC);
          }else {
            $sql = "SELECT PlConId, PlConNome,
                          dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro."', '".$datasFiltro."', '".$tipo."') as planoContaAnaliticaPrevistoSaida,
                          dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", PlConCodigo, '".$datasFiltro."', '".$datasFiltro."', '".$tipo."') as planoContaAnaliticaRealizadoSaida
                    FROM PlanoConta
                    WHERE  PlConUnidade = " . $_SESSION['UnidadeId'] . " and PlConPlanoContaPai = ". $rowCC['PlConId'] ."
                    ORDER BY PlConNome ASC";
            $result = $conn->query($sql);
            $rowPLanoContaAnalitica = $result->fetchAll(PDO::FETCH_ASSOC);
          }

          $pl[$regAt]['planosContaFilho'] = $rowPLanoContaAnalitica;*/
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

//Consulta os grupos dos planos de contas
$sql = "SELECT GrConId, GrConNome, GrConNomePersonalizado, GrConCodigo, SituaChave
		FROM GrupoConta
		JOIN Situacao on SituaId = GrConStatus
	    WHERE GrConUnidade = ". $_SESSION['UnidadeId'] ." AND SituaChave = 'ATIVO' AND GrConCodigo != ''
		ORDER BY GrConCodigo ASC";
$result = $conn->query($sql);
$rowGrupo = $result->fetchAll(PDO::FETCH_ASSOC);

$_POST["inputCentroDeCustos"] = explode(",", $_POST["inputCentroDeCustos"]);
$_POST["inputPlanoContas"] = explode(",", $_POST["inputPlanoContas"]);
$numDias    = $_POST["quantityDays"];
$diaInicio  = $_POST["dayInitial"];
$diaFim     = $_POST["dayEnd"];
$dataInicio = $_POST["inputDateInitial"];
$dataFim    = $_POST["inputDateEnd"];
$typeFiltro = $_POST["typeDate"];
$ccFiltro   = rtrim(implode(',', $_POST["inputCentroDeCustos"]));
$plFiltro   = rtrim(implode(',', $_POST["inputPlanoContas"]));

//$ccFiltro   = explode(',',$_POST["inputCentroDeCustos"]);
//$plFiltro   = explode(',',$_POST["inputPlanoContas"]);

//Comecei a mexer no cabeçalho
/* 
//declaramos uma variavel para montarmos a estrutura
$cabecalho  = "";
$cabecalho .= "  <br><table>";
$cabecalho .= "     <tr>";
$cabecalho .= "         <th scope='col' style = 'color: red;'>TESTE</th>";
$cabecalho .= "         <th scope='col' style = 'color: red;'>".$dataInicio."</th>";
$cabecalho .= "         <th scope='col' style = 'color: red;'>".$typeFiltro."</th>";
$cabecalho .= "     </tr>";
$cabecalho .= " </table>";
*/

$dadosXls  = "";
//$dadosXls .= $cabecalho . "  <table>";
$dadosXls .= "  <table>";

//Filtra pelos dias
if($typeFiltro == "D"){
    $mesArray = explode('-', $dataInicio);
    $anoData = $mesArray[0];
    $mesData = (int)$mesArray[1];

    $controlador = (($diaFim - $diaInicio) < 3 ) ? true : false; 

    //$data = explode('-', $dataInicio);
    $data = explode('-', $dataInicio);
    $dataFormatado = $data[0].'-'.$data[1].'-'.$data[2];
    $anoMesFormatado = $data[0].'-'.$data[1];

    $indice = 1;
    $receitaTotal = 0;
    $controlador = true;

    $dataFluxoCaixa = "";
    $legenda = "";
    $saldoInicial = "";
    $grupoConta = "";
    $planoContaSintetico = "";
    $planoContaAnalítico = "";
    $saldoFinal = "";
    
    //Primeiro o tipo de grupo é definido como entrada, pois sempre começa pela receita, dps dentro do próprio loop ele se torna 'S' para trazer a saída
    $tipoGrupo = 'E';

    //A tabela do Fluxo de Caixa é divida em subcategorias

    //Informa a data de cada coluna
    $dataFluxoCaixa .= "     <tr>";
    $dataFluxoCaixa .= "        <th scope='col'></th>";

    //Legenda com os nomes Saldo Previsto e Saldo Realizado
    $legenda .= "            <tr>";
    $legenda .= "               <th scope='col' bgcolor='#607D8B'></th>";

    //Traz os valores do Saldo Inicial
    $saldoInicial .= "       <tr>";
    $saldoInicial .= "          <td scope='col' bgcolor='#CCCCCC'>Saldo Inicial</td>";

    //Traz os valores do Saldo Final
    $saldoFinal .= "       <tr>";
    $saldoFinal .= "          <td scope='col' bgcolor='#CCCCCC'>Saldo Final</td>";

    foreach($rowGrupo as $grupo) {
        unset($arrayNomePlanoContaSintetico);
        unset($arrayPlanoContaPrevisto);

        $nomeGrupo = $grupo['GrConNomePersonalizado'] != '' ? $grupo['GrConNomePersonalizado'] :  $grupo['GrConNome'];

        //Informa o nome do Grupo
        $grupoConta .= "     <tr>";
        $grupoConta .= "         <td scope='col' bgcolor='#607D8B' style ='color: white;'>".$nomeGrupo."</td>";

        $contador = 0;
        for($i = $diaInicio; $i <= $diaFim; $i++) {
            unset($dataConsulta);
            //Consulta a data para cada coluna
            $dataFiltro = $anoData.'-'.$mesData.'-'.$i;

            $sql = "SELECT dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltro."', '".$dataFiltro."', '".$tipoGrupo."') as PrevistoSaidaGrupo,
                            dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataFiltro."', '".$dataFiltro."', '".$tipoGrupo."') as RealizadoSaidaGrupo,
                            dbo.fnFluxoCaixaSaldoInicialPrevisto(".$_SESSION['UnidadeId'].",'".$dataFiltro."') as SaldoInicialPrevisto,
                            dbo.fnFluxoCaixaSaldoInicialRealizado(".$_SESSION['UnidadeId'].",'".$dataFiltro."') as SaldoInicialRealizado,
                            dbo.fnFluxoCaixaSaldoFinal(".$_SESSION['UnidadeId'].",'".$dataFiltro."') as SaldoFinal";
            $resultGrupo = $conn->query($sql);
            $rowTotalGrupo = $resultGrupo->fetch(PDO::FETCH_ASSOC);

            $totalGrupo1 = '';
            $totalPrevisto = $rowTotalGrupo['PrevistoSaidaGrupo'];
            $totalRealizado = $rowTotalGrupo['RealizadoSaidaGrupo'];
            
            $saldoPrevisto = $rowTotalGrupo['SaldoInicialPrevisto'];
            $saldoRealizado = $rowTotalGrupo['SaldoInicialRealizado'];
            $totalSaldoFinal = $rowTotalGrupo['SaldoFinal'];

            $corSaldoPrevisto = $saldoPrevisto < 0 ? "style ='color: red;'" : "";
            $corSaldoRealizado = $saldoRealizado < 0 ? "style ='color: red;'" : "";
            $corSaldoFinal = $totalSaldoFinal < 0 ? "style ='color: red;'" : "";

            //Essa condicional é para que seja gerada apenas uma linha com as informações que estão logo abaixo
            if($controlador) {
                //Informa a data de cada coluna
                $dataFluxoCaixa .= "
                                  <th colspan='2'>".str_pad($i, 2, '0', STR_PAD_LEFT)." ".ucfirst(strftime("%B de %Y", strtotime($dataFiltro)))."</th>";

                //Legenda com os nomes Saldo Previsto e Saldo Realizado
                $legenda .= "     <th scope='col' bgcolor='#607D8B' style ='color: white;'>Previsto</th>";
                $legenda .= "     <th scope='col' bgcolor='#607D8B' style ='color: white;'>Realizado</th>";

                //Traz os valores do Saldo Inicial
                $saldoInicial .= "<td scope='col' bgcolor='#CCCCCC' ".$corSaldoPrevisto.">".mostraValor($saldoPrevisto)."</td>";
                $saldoInicial .= "<td scope='col' bgcolor='#CCCCCC' ".$corSaldoRealizado.">".mostraValor($saldoRealizado)."</td>";

                //Traz os valores do Saldo Final
                $saldoFinal .= "<td colspan='2' bgcolor='#CCCCCC' ".$corSaldoFinal.">".mostraValor($totalSaldoFinal)."&nbsp;</td>";
            }
            
            //Traz os saldos do grupo conta
            $grupoConta .= "    <td scope='col' bgcolor='#607D8B' style ='color: white;'>".mostraValor($totalPrevisto)."</td>"; 
            $grupoConta .= "    <td scope='col' bgcolor='#607D8B' style ='color: white;'>".mostraValor($totalRealizado)."</td>";

            $dataConsulta['data_inicio'] = $dataFiltro;
            $dataConsulta['data_Final'] = $dataFiltro;

            //Consultando os Planos de Contas ligado a este grupo
            $mes = retornaBuscaComoArray($dataConsulta, $plFiltro, $grupo['GrConId'], $tipoGrupo);

            $arrayPlanoContaSintetico = isset($mes['pl']) ? $mes['pl'] : null;

            //Índice dos planos de contas, usados para fazer um controle para preencher a tabela
            //Consulta os dados dos Planos de Contas Sintéticos
            $indicePlanoConta = 0;
            foreach($arrayPlanoContaSintetico as $consultaPlanoContaSintetico){
                $nomePlanoContaSintetico = $consultaPlanoContaSintetico['PlConNome'];
                //$teste = $consultaPlanoContaSintetico['planosContaFilho'];

                $corPrevisto = $grupo['GrConCodigo'] != 1 ? "style ='color: red;'" : "";
                $corRealizado = $grupo['GrConCodigo'] != 1 ? "style ='color: red;'" : "";

                $sinalPrevisto = $grupo['GrConCodigo'] != 1 && $consultaPlanoContaSintetico['PL_Previsto'] > 0 ? "-" : "";
                $sinalRealizado = $grupo['GrConCodigo'] != 1 && $consultaPlanoContaSintetico['PL_Realizado'] > 0 ? "-" : "";

                $arrayNomePlanoContaSintetico[$indicePlanoConta] = $nomePlanoContaSintetico;

                //É armazenada a linha completa no array do Plano Conta Sintético
                if($contador == 0) {
                    $arrayPlanoContaPrevisto[$indicePlanoConta] = "<td scope='col' bgcolor='#CCCCCC' ".$corPrevisto."> ".$sinalPrevisto."".mostraValor($consultaPlanoContaSintetico['PL_Previsto'])."</td>
                                                                   <td scope='col' bgcolor='#CCCCCC' ".$corRealizado.">".$sinalRealizado."".mostraValor($consultaPlanoContaSintetico['PL_Realizado'])."</td>";
                }else {
                    $arrayPlanoContaPrevisto[$indicePlanoConta] .= "<td scope='col' bgcolor='#CCCCCC' ".$corPrevisto."> ".$sinalPrevisto."".mostraValor($consultaPlanoContaSintetico['PL_Previsto'])."</td>
                                                                    <td scope='col' bgcolor='#CCCCCC' ".$corRealizado.">".$sinalRealizado."".mostraValor($consultaPlanoContaSintetico['PL_Realizado'])."</td>";
                }
                
                $indicePlanoConta++;
            }

            $contador++;
        }

        //Gera os campos dos Planos de Contas Sintéticos
        $indiceGeraTabelaPlanoContaSintetico = 0;
        foreach($arrayNomePlanoContaSintetico as $nomePlanoContaSintetico) {
            //Informa o Plano Conta
            $planoContaSintetico .= "  <tr>";
            $planoContaSintetico .= "    <td scope='col' bgcolor='#CCCCCC'>&nbsp;&nbsp;".$nomePlanoContaSintetico."</td>";
            $planoContaSintetico .=      $arrayPlanoContaPrevisto[$indiceGeraTabelaPlanoContaSintetico];
            $planoContaSintetico .= "  </tr>";

            $controlePlanoContaAnalitico = true;

            $planoContaSintetico .= $planoContaAnalítico;

            $indiceGeraTabelaPlanoContaSintetico++;
          }
          $planoContaAnalítico = '';

        $controlador = false;

        $grupoConta .= "  </tr>" . $planoContaSintetico;
        $planoContaSintetico = '';

        $tipoGrupo = 'S';
    }
    
    $dataFluxoCaixa .= " </tr>";
    $legenda .= "        </tr>";
    $saldoInicial .= "   </tr>";
    $saldoFinal .= "   </tr>";
}else { //Filtra pelos meses
    $mesArray = explode('-', $dataInicio);
    $anoData = $mesArray[0];
    $mesData = (int)$mesArray[1];
    $mesInicio = (int)$mesArray[1];

    $Teste = explode('-', $dataFim);
    $testando = $Teste[0];
    $mesFim = (int)$Teste[1];

    //$controlador = (($diaFim - $diaInicio) < 3 ) ? true : false; 

    $data = explode('-', $dataInicio);
    $dataFormatado = $data[0].'-'.$data[1];
    $anoMesFormatado = $data[0].'-'.$data[1];

    $indice = 1;
    $receitaTotal = 0;
    $controlador = true;

    $dataFluxoCaixa = "";
    $legenda = "";
    $saldoInicial = "";
    $grupoConta = "";
    $planoContaSintetico = "";
    $planoContaAnalítico = "";
    $saldoFinal = "";
    
    //Primeiro o tipo de grupo é definido como entrada, pois sempre começa pela receita, dps dentro do próprio loop ele se torna 'S' para trazer a saída
    $tipoGrupo = 'E';

    //A tabela do Fluxo de Caixa é divida em subcategorias

    //Informa a data de cada coluna
    $dataFluxoCaixa .= "     <tr>";
    $dataFluxoCaixa .= "        <th scope='col'></th>";

    //Legenda com os nomes Saldo Previsto e Saldo Realizado
    $legenda .= "            <tr>";
    $legenda .= "               <th scope='col' bgcolor='#607D8B'></th>";

    //Traz os valores do Saldo Inicial
    $saldoInicial .= "       <tr>";
    $saldoInicial .= "          <td scope='col' bgcolor='#CCCCCC'>Saldo Inicial</td>";

    //Traz os valores do Saldo Final
    $saldoFinal .= "       <tr>";
    $saldoFinal .= "          <td scope='col' bgcolor='#CCCCCC'>Saldo Final</td>";

    foreach($rowGrupo as $grupo) {
        unset($arrayNomePlanoContaSintetico);
        unset($arrayPlanoContaPrevisto);

        $nomeGrupo = $grupo['GrConNomePersonalizado'] != '' ? $grupo['GrConNomePersonalizado'] :  $grupo['GrConNome'];

        //Informa o nome do Grupo
        $grupoConta .= "     <tr>";
        $grupoConta .= "         <td scope='col' bgcolor='#607D8B' style ='color: white;'>".$nomeGrupo."</td>";

        $contador = 0;
        for($i = $mesInicio; $i <= $mesFim; $i++) {
            unset($dataFiltro);
            $ultimo_dia = date("t", mktime(0,0,0,($i),'01',$anoData));

            //Consulta a data para cada coluna
            $dataInicialFiltro = $anoData.'-'.$i.'-01';
            $dataFinalFiltro = $anoData.'-'.$i.'-'.$ultimo_dia;

            $dataFiltro['data_inicio'] = $dataInicialFiltro;
            $dataFiltro['data_Final'] = $dataFinalFiltro;

            $sql = "SELECT dbo.fnPlanoContasPrevisto(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataInicialFiltro."', '".$dataFinalFiltro."', '".$tipoGrupo."') as PrevistoSaidaGrupo,
                            dbo.fnPlanoContasRealizado(".$_SESSION['UnidadeId'].", ".$grupo['GrConCodigo'].", '".$dataInicialFiltro."', '".$dataFinalFiltro."', '".$tipoGrupo."') as RealizadoSaidaGrupo,
                            dbo.fnFluxoCaixaSaldoInicialPrevisto(".$_SESSION['UnidadeId'].",'".$dataInicialFiltro."') as SaldoInicialPrevisto,
                            dbo.fnFluxoCaixaSaldoInicialRealizado(".$_SESSION['UnidadeId'].",'".$dataInicialFiltro."') as SaldoInicialRealizado,
                            dbo.fnFluxoCaixaSaldoFinal(".$_SESSION['UnidadeId'].",'".$dataFinalFiltro."') as SaldoFinal";
            $resultGrupo = $conn->query($sql);
            $rowTotalGrupo = $resultGrupo->fetch(PDO::FETCH_ASSOC);

            $totalGrupo1 = '';
            $totalPrevisto = $rowTotalGrupo['PrevistoSaidaGrupo'];
            $totalRealizado = $rowTotalGrupo['RealizadoSaidaGrupo'];
            
            $saldoPrevisto = $rowTotalGrupo['SaldoInicialPrevisto'];
            $saldoRealizado = $rowTotalGrupo['SaldoInicialRealizado'];
            $totalSaldoFinal = $rowTotalGrupo['SaldoFinal'];

            $corSaldoPrevisto = $saldoPrevisto < 0 ? "style ='color: red;'" : "";
            $corSaldoRealizado = $saldoRealizado < 0 ? "style ='color: red;'" : "";
            $corSaldoFinal = $totalSaldoFinal < 0 ? "style ='color: red;'" : "";

            //Essa condicional é para que seja gerada apenas uma linha com as informações que estão logo abaixo
            if($controlador) {
                //Informa a data de cada coluna
                $dataFluxoCaixa .= "
                                  <th colspan='2'>".ucfirst(strftime("%B de %Y", strtotime($dataInicialFiltro)))."</th>";

                //Legenda com os nomes Saldo Previsto e Saldo Realizado
                $legenda .= "     <th scope='col' bgcolor='#607D8B' style ='color: white;'>Previsto</th>";
                $legenda .= "     <th scope='col' bgcolor='#607D8B' style ='color: white;'>Realizado</th>";

                //Traz os valores do Saldo Inicial
                $saldoInicial .= "<td scope='col' bgcolor='#CCCCCC' ".$corSaldoPrevisto.">".mostraValor($saldoPrevisto)."</td>";
                $saldoInicial .= "<td scope='col' bgcolor='#CCCCCC' ".$corSaldoRealizado.">".mostraValor($saldoRealizado)."</td>";

                //Traz os valores do Saldo Final
                $saldoFinal .= "<td colspan='2' bgcolor='#CCCCCC' ".$corSaldoFinal.">&nbsp;&nbsp;".mostraValor($totalSaldoFinal)."&nbsp;</td>";
            }
            
            //Traz os saldos do grupo conta
            $grupoConta .= "    <td scope='col' bgcolor='#607D8B' style ='color: white;'>".mostraValor($totalPrevisto)."</td>"; 
            $grupoConta .= "    <td scope='col' bgcolor='#607D8B' style ='color: white;'>".mostraValor($totalRealizado)."</td>";

            //Consultando os Planos de Contas ligado a este grupo
            $mes = retornaBuscaComoArray($dataFiltro, $plFiltro, $grupo['GrConId'], $tipoGrupo);

            $arrayPlanoContaSintetico = isset($mes['pl']) ? $mes['pl'] : null;

            //Índice dos planos de contas, usados para fazer um controle para preencher a tabela
            //Consulta os dados dos Planos de Contas Sintéticos
            $indicePlanoConta = 0;
            foreach($arrayPlanoContaSintetico as $consultaPlanoContaSintetico){
                $nomePlanoContaSintetico = $consultaPlanoContaSintetico['PlConNome'];
                //$teste = $consultaPlanoContaSintetico['planosContaFilho'];

                $arrayNomePlanoContaSintetico[$indicePlanoConta] = $nomePlanoContaSintetico;

                $corPrevisto = $grupo['GrConCodigo'] != 1 ? "style ='color: red;'" : "";
                $corRealizado = $grupo['GrConCodigo'] != 1 ? "style ='color: red;'" : "";

                $sinalPrevisto = $grupo['GrConCodigo'] != 1 && $consultaPlanoContaSintetico['PL_Previsto'] > 0 ? "-" : "";
                $sinalRealizado = $grupo['GrConCodigo'] != 1 && $consultaPlanoContaSintetico['PL_Realizado'] > 0 ? "-" : "";

                //É armazenada a linha completa no array do Plano Conta Sintético
                if($contador == 0) {
                    $arrayPlanoContaPrevisto[$indicePlanoConta] = "<td scope='col' bgcolor='#CCCCCC' ".$corPrevisto."> ".$sinalPrevisto."".mostraValor($consultaPlanoContaSintetico['PL_Previsto'])."</td>
                                                                   <td scope='col' bgcolor='#CCCCCC' ".$corRealizado.">".$sinalRealizado."".mostraValor($consultaPlanoContaSintetico['PL_Realizado'])."</td>";
                }else {
                    $arrayPlanoContaPrevisto[$indicePlanoConta] .= "<td scope='col' bgcolor='#CCCCCC' ".$corPrevisto."> ".$sinalPrevisto."".mostraValor($consultaPlanoContaSintetico['PL_Previsto'])."</td>
                                                                    <td scope='col' bgcolor='#CCCCCC' ".$corRealizado.">".$sinalRealizado."".mostraValor($consultaPlanoContaSintetico['PL_Realizado'])."</td>";
                }
                
                $indicePlanoConta++;
            }

            $contador++;
        }

        //Gera os campos dos Planos de Contas Sintéticos
        $indiceGeraTabelaPlanoContaSintetico = 0;
        foreach($arrayNomePlanoContaSintetico as $nomePlanoContaSintetico) {
            //Informa o Plano Conta
            $planoContaSintetico .= "  <tr>";
            $planoContaSintetico .= "    <td scope='col' bgcolor='#CCCCCC'>&nbsp;&nbsp;".$nomePlanoContaSintetico."</td>";
            $planoContaSintetico .=      $arrayPlanoContaPrevisto[$indiceGeraTabelaPlanoContaSintetico];
            $planoContaSintetico .= "  </tr>";

            $controlePlanoContaAnalitico = true;

            $planoContaSintetico .= $planoContaAnalítico;

            $indiceGeraTabelaPlanoContaSintetico++;
          }
          $planoContaAnalítico = '';

        $controlador = false;

        $grupoConta .= "  </tr>" . $planoContaSintetico;
        $planoContaSintetico = '';

        $tipoGrupo = 'S';
    }

    
    $dataFluxoCaixa .= " </tr>";
    $legenda .= "        </tr>";
    $saldoInicial .= "   </tr>";
    $saldoFinal .= "   </tr>";

    //Quando filtra pelos meses o nome do arquivo vem apenas com o ano
    $dataFiltro = $anoData;
}

/*
//Testes
$algo  = "";
$algo .= "  <br><table>";
$algo .= "     <tr>";
$algo .= "         <th scope='col' bgcolor='red'>".$dataInicialFiltro."</th>";
$algo .= "         <th scope='col' bgcolor='red'>".$dataFinalFiltro."</th>";
$algo .= "     </tr>";
$algo .= " </table>";

$dadosXls .= $dataFluxoCaixa . $legenda . $saldoInicial . $grupoConta . $saldoFinal
. "  </table>" . $algo;
*/

$dadosXls .= $dataFluxoCaixa . $legenda . $saldoInicial . $grupoConta . $saldoFinal
. "  </table>";

// Definimos o nome do arquivo que será exportado  
$arquivo = "Fluxo de Caixa - ".strftime("%B de %Y", strtotime($dataFiltro)).".xls";
  
// Configurações header para forçar o download  
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$arquivo.'"');
header('Cache-Control: max-age=0');
// Se for o IE9, isso talvez seja necessário
header('Cache-Control: max-age=1');

header("Content-type: text/html; charset=utf-8");

// Envia o conteúdo do arquivo  
echo $dadosXls;  
exit;

?>