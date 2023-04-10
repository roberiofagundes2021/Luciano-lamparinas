<?php

/*--------------------------------------------------------------------------
	  |	    Passando data do text box "AAAA-MM-DD" para "DD/MM/AAAA"		   |
	  -------------------------------------------------------------------------*/
function mostraData($data)
{

	if ($data <> '0000-00-00' and $data <> '') {
		$data = explode(" ", $data);
		$data = explode("-", $data[0]);
		$dataformatada = array();
		@$dataformatada = date("d/m/Y", mktime(0, 0, 0, $data[1], $data[2], $data[0]));
	} else {
		$dataformatada = '';
	}
	return ($dataformatada);
}

/*----------------------------------------------------------------------
	|	 Passando dataHora "AAAA-MM-DD 00:00:00" para "DD/MM/AAAA 00:00:00" |
	---------------------------------------------------------------------*/
function mostraDataHora($data) {

	if ($data <> '0000-00-00 00:00:00' and $data <> ''){
		$dataHora = explode(" ", $data);
		$data = explode("-", $dataHora[0]);
		$hora = explode(".", $dataHora[1]); //Caso a hora venha do banco de dados no seguinte formato. H:i:s.000
		$dataformatada = array();
		@$dataformatada = date("d/m/Y", mktime(0,0,0, $data[1] , $data[2] , $data[0] )) . " " . $hora[0];
	} 
	else{
		$dataformatada = '';
	}
	return($dataformatada);
}

/*----------------------------------------------------------------------
	|	 Passando hora "00:00:00.000" para "00:00" |("HH:MM:SS.000")|
	---------------------------------------------------------------------*/
function mostraHora($hora) {

	if ($hora <> '00:00:00' and $hora <> ''){
		$horaRefatorada = explode(":", $hora); //Caso a hora venha do banco de dados no seguinte formato. H:i:s.000 
		$horaRefatorada = "$horaRefatorada[0]:$horaRefatorada[1]"; // H:i:s.000 => H:i:s
	} 
	else{
		$horaRefatorada = '';
	}
	return($horaRefatorada);
}

function mostraCEP($cep) {
	$cepFormatado = substr($cep, 0, 5) . '-' . substr($cep, 5, 3);

	return $cepFormatado;
}

/*----------------------------------------------------------------------
	|	retornando horas entre uma data e outra | ("Y-m-d","Y-m-d") |
	---------------------------------------------------------------------*/
	function diferencaEmHoras($dataInicio, $dataFim) {
		$differenceInHours = '';

		if ($dataInicio != '' && $dataFim != '') {
			$differenceInHours = abs(strtotime($dataFim) - strtotime($dataInicio))/3600;
			$differenceInHours = "$differenceInHours h";
		}

		return($differenceInHours);
	}

/*--------------------------------------------------------------------------
	  |					Exibe mensagem na tela								   |
	  -------------------------------------------------------------------------*/
function alerta($mensagem)
{
	echo "
			<script language=\"javascript\">
			alert(\"$mensagem\");
			</script>
			";
}

/*--------------------------------------------------------------------------
	  |					Direciona para a url detectada			   			   |
	  -------------------------------------------------------------------------*/
function irpara($link)
{
	echo "<meta http-equiv=\"refresh\" content=\"0;url=$link\">";
	exit();
}


/*--------------------------------------------------------------------------*/
function voltar()
{
	echo "<meta http-equiv=\"refresh\" content=\"0;url=javascript:history.go(-1)\">";
	exit();
}

/*--------------------------------------------------------------------------*/
function fechar()
{
	echo '
		<script language="javascript">
		  window.close();
		</script>
		';
	exit();
}

/*-------------------------------------------------------------------------*/
function abrir($link, $texto, $largura, $altura, $barra, $ferramenta)
{
	return  '<a href="javascript:;" onClick="window.open(\'' . $link . '\',\'\',\'width=' . $largura . ',height=' . $altura . ',scrollbars=' . $barra . ',toolbar=' . $ferramenta . '\')">' . $texto . '</a>';
}


//-------------------------------------------------------------------------
//###############################################################

function anti_injection($sql) //evita que aconte�a ataques SQL INJECTION
{
	// remove palavras que contenham sintaxe sql
	$sql = preg_replace(sql_regcase("/(from|select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/"), "", $sql);
	// $sql = trim($sql);//limpa espa�os vazio
	$sql = strip_tags($sql); //tira tags html e php
	$sql = addslashes($sql); //Adiciona barras invertidas a uma string
	return $sql;

	# //modo de usar pegando dados vindos do formulario
	# $nome = anti_injection($_POST["nome"]);
	# $senha = anti_injection($_POST["senha"]);


}

// Passando data do text box "DD/MM/AAAA" para "AAAA-MM-DD"
function gravaData($data)
{
	if ($data != '' && $data != null) {
		$parte = explode("/", $data);
		return ($parte[2] . '-' . $parte[1] . '-' . $parte[0]);
		/* return (substr($data,6,4).'/'.substr($data,3,2).'/'.substr($data,0,2));  */
	} else {
		return '';
	}
}

function gravaValor($campo)
{
	//Varre o conteudo da variavel $total e troca todos os pontos por em branco. Ex.: 5.423,36 ficaria 5423,36
	$frase = str_replace(".", "", $campo);
	//Varre o conteudo da variavel $frase e troca todas as virgulas por ponto. Ex.: 5423,36 ficaria 5423.36
	$campo = str_replace(",", ".", $frase);

	return $campo;
}

function mostraValor($campo)
{
	//Varre o conteudo da variavel $total e troca todos os pontos por em branco. Ex.: 5.423,36 ficaria 5423,36
	$frase = number_format($campo, 2, '.', '');
	//Varre o conteudo da variavel $frase e troca todas as virgulas por ponto. Ex.: 5423,36 ficaria 5423.36
	$campo = number_format($frase, 2, ',', '.');

	return $campo;
}

function formataMoeda($campo)
{
	//Varre o conteudo da variavel $total e troca todos os pontos por em branco. Ex.: 5.423,36 ficaria 5423,36
	$frase = number_format($campo, 2, '.', '');
	//Varre o conteudo da variavel $frase e troca todas as virgulas por ponto. Ex.: 5423,36 ficaria 5423.36
	$campo = "R$ " . number_format($frase, 2, ',', '.');

	return $campo;
}

//-------------------------------- Retira os acentos
function tiraAcento($string)
{
	//$string	= utf8_decode($string);

	$comAcentos = array('à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ü', 'ú', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'O', 'Ù', 'Ü', 'Ú');

	$semAcentos = array('a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U');

	// devolver a string
	return str_replace($comAcentos, $semAcentos, $string);
}

function nomeSobrenome($fullName, $num = 1)
{

	$arr = explode(' ', $fullName);

	/* Junta os dois primeiros nomes em uma nova string */
	if (isset($arr[1])) {
		$doisNomes = $arr[0] . ' ' . $arr[1];
	} else {
		$doisNomes = $arr[0];
	}

	if ($num == 1) {
		return $arr[0];
	} else {
		return $doisNomes;
	}
}

function saudacoes()
{

	date_default_timezone_set('America/Sao_Paulo');

	$hr = date(" H ");

	if ($hr >= 12 && $hr < 18) {
		$resp = "Boa tarde";
	} else if ($hr >= 0 && $hr < 12) {
		$resp = "Bom dia";
	} else {
		$resp = "Boa noite";
	}

	return "$resp";
}

function formatarCPF_Cnpj($cnpj_cpf)
{

	if (strlen(preg_replace("/\D/", '', $cnpj_cpf)) === 11) {
		$response = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
	} else {
		$response = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
	}

	return $response;
}

function formatarChave($texto)
{

	$TextoSemAcentos = tiraacento($texto);

	$TextoSemEspacoBranco = str_replace(' ', '', $TextoSemAcentos);

	$TextoFormatado = strtoupper(trim(utf8_decode($TextoSemEspacoBranco)));

	return $TextoFormatado;
}

function limpaCPF_CNPJ($valor)
{
	$valor = trim($valor);
	$valor = str_replace(".", "", $valor);
	$valor = str_replace(",", "", $valor);
	$valor = str_replace("-", "", $valor);
	$valor = str_replace("/", "", $valor);
	return $valor;
}

function validaCPF($cpf = null)
{

	// Verifica se um n�mero foi informado
	if (empty($cpf)) {
		return false;
	}

	// Elimina possivel mascara
	$cpf = preg_replace("/[^0-9]/", "", $cpf);
	$cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

	// Verifica se o numero de digitos informados � igual a 11 
	if (strlen($cpf) != 11) {
		return false;
	}
	// Verifica se nenhuma das sequ�ncias invalidas abaixo 
	// foi digitada. Caso afirmativo, retorna falso
	else if (
		$cpf == '00000000000' ||
		$cpf == '11111111111' ||
		$cpf == '22222222222' ||
		$cpf == '33333333333' ||
		$cpf == '44444444444' ||
		$cpf == '55555555555' ||
		$cpf == '66666666666' ||
		$cpf == '77777777777' ||
		$cpf == '88888888888' ||
		$cpf == '99999999999'
	) {
		return false;
		// Calcula os digitos verificadores para verificar se o
		// CPF � v�lido
	} else {

		for ($t = 9; $t < 11; $t++) {

			for ($d = 0, $c = 0; $c < $t; $c++) {
				$d += $cpf[$c] * (($t + 1) - $c);
			}
			$d = ((10 * $d) % 11) % 10;
			if ($cpf[$c] != $d) {
				return false;
			}
		}

		return true;
	}
}

function limpaCEP($valor)
{
	$valor = trim($valor);
	$valor = str_replace("-", "", $valor);
	return $valor;
}

function formatarNumero($numero)
{
	$numero = str_pad($numero, 6, '0', STR_PAD_LEFT);
	return $numero;
}

//Função que transforma um valor em número por extenso
function valor_por_extenso( $v ){
	$sin = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
	$plu = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
	$c = array("", "cem", "duzentos", "trezentos", "quatrocentos","quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
	$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta","sessenta", "setenta", "oitenta", "noventa");
	$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze","dezesseis", "dezesete", "dezoito", "dezenove");
	$u = array("", "um", "dois", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
	$z = 0;
	$v = number_format( $v, 2, ".", "." );
	$int = explode( ".", $v );
	for ( $i = 0; $i < count( $int ); $i++ ) 
	{
	for ( $ii = mb_strlen( $int[$i] ); $ii < 3; $ii++ ) 
	{
	$int[$i] = "0" . $int[$i];
	}
	}
	$rt = null;
	$fim = count( $int ) - ($int[count( $int ) - 1] > 0 ? 1 : 2);
	for ( $i = 0; $i < count( $int ); $i++ )
	{
	$v = $int[$i];
	$rc = (($v > 100) && ($v < 200)) ? "cento" : $c[$v[0]];
	$rd = ($v[1] < 2) ? "" : $d[$v[1]];
	$ru = ($v > 0) ? (($v[1] == 1) ? $d10[$v[2]] : $u[$v[2]]) : "";
	$r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
	$t = count( $int ) - 1 - $i;
	$r .= $r ? " " . ($v > 1 ? $plu[$t] : $sin[$t]) : "";
	if ( $v == "000")
	$z++;
	elseif ( $z > 0 )
	$z--;
	if ( ($t == 1) && ($z > 0) && ($int[0] > 0) )
	$r .= ( ($z > 1) ? " de " : "") . $plu[$t];
	if ( $r )
	$rt = $rt . ((($i > 0) && ($i <= $fim) && ($int[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
	}
	$rt = mb_substr( $rt, 1 );
	return($rt ? trim( $rt ) : "zero");
}

/* Essa função foi criada para corrigir os nomes com apóstrofo, como por exemplo EPI's. No SQL Server para corrigir isso,
basta colocar mais uma aspa simples. Ex.: EPI''s, portanto, essa função faz isso, acrescenta mais uma aspa simples para
esses casos de apóstrofes. */
function mssql_escape($str) { 
	if(get_magic_quotes_gpc()) { 
		$str= stripslashes($str); 
	} 
	return str_replace("'", "''", $str); 
}

//Função para identificar os elementos diferentes entre os array
function pegaDiferencaArray( $ary_1, $ary_2 ) {
	// compare the value of 2 array
	// get differences that in ary_1 but not in ary_2
	// get difference that in ary_2 but not in ary_1
	// return the unique difference between value of 2 array
	$diff = array();
	
	// get differences that in ary_1 but not in ary_2
	foreach ( $ary_1 as $v1 ) {
		$flag = 0;
		foreach ( $ary_2 as $v2 ) {
		$flag |= ( $v1 == $v2 );
		if ( $flag ) break;
		}
		if ( !$flag ) array_push( $diff, $v1 );
	}
	
	// get difference that in ary_2 but not in ary_1
	foreach ( $ary_2 as $v2 ) {
		$flag = 0;
		foreach ( $ary_1 as $v1 ) {
		$flag |= ( $v1 == $v2 );
		if ( $flag ) break;
		}
		if ( !$flag && !in_array( $v2, $diff ) ) array_push( $diff, $v2 );
	}
	
	return $diff;
}

//Calcula a idade a partir de uma data
function calculaIdade($datanascimento){
	
	$date1 = new DateTime($datanascimento);
	$date2 = new DateTime();
	$interval = $date1->diff($date2); 

	return $interval->y . " anos, " . $interval->m." meses, ".$interval->d." dias";
}

//calcula a idade simples
function calculaIdadeSimples($datanascimento){

	$date1 = new DateTime($datanascimento);
	$date2 = new DateTime();
	$interval = $date1->diff($date2); 

	if ($interval->y > 0) {
		return $interval->y > 1 ? $interval->y . " anos" : $interval->y . " ano";		
	} else if ($interval->y <=0 && $interval->m > 0) {		
		return $interval->m > 1 ? $interval->m . " meses" : $interval->m . " mês";
	} else if($interval->y <= 0 && $interval->m <=0 && $interval->d > 0){
		return $interval->d > 1 ? $interval->d . " dias" : $interval->y . " dia";
	} else {
		return "Fora dos Padrões Normais";
	}
	
}