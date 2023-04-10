<?php 

// Referência: https://www.youtube.com/watch?v=7-5yUgiOAo8

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Importa Serviço';
$_SESSION['RelImportacao'] = '';
$_SESSION['Importacao'] = '';

include('global_assets/php/conexao.php');

// Pasta onde o arquivo vai ser salvo
$_UP['pasta'] = 'global_assets/importacao/';

// Tamanho máximo do arquivo (em Bytes)
$_UP['tamanho'] = 1024 * 1024 * 10; // 10MB

// Array com as extensões permitidas
$_UP['extensoes'] = array('xml');

// Renomeia o arquivo? (Se true, o arquivo será salvo como .doc e um nome único)
$_UP['renomeia'] = false;

// Array com os tipos de erros de upload do PHP
$_UP['erros'][0] = 'Não houve erro';

$_UP['erros'][1] = 'O arquivo no upload é maior do que o limite do PHP';

$_UP['erros'][2] = 'O arquivo ultrapassa o limite de tamanho especifiado no HTML';

$_UP['erros'][3] = 'O upload do arquivo foi feito parcialmente';

$_UP['erros'][4] = 'Não foi feito o upload do arquivo'; 


// Verifica se houve algum erro com o upload. Se sim, exibe a mensagem do erro
if ($_FILES['arquivo']['error'] != 0) {

	alerta($_UP['erros'][$_FILES['arquivo']['error']]);
	irpara("servico.php");
}

// Caso script chegue a esse ponto, não houve erro com o upload e o PHP pode continuar
// Faz a verificação da extensão do arquivo
/*
$extensao = strtolower(end(explode(".", $_FILES['arquivo']['name'])));

if ($extensao != 'csv') {

	alerta("Por favor, envie arquivos com a seguinte extensão: CSV!");
	irpara("servico.php");

} */

// Faz a verificação do tamanho do arquivo
else if ($_UP['tamanho'] < $_FILES['arquivo']['size']) {
	
	// Não foi possível fazer o upload, provavelmente a pasta está incorreta
	$_SESSION['RelImportacao'] = "Não foi possível enviar o arquivo. O arquivo enviado é muito grande, envie arquivos de até 10MB.";
	$_SESSION['Importacao'] = 'Erro';	
}

// O arquivo passou em todas as verificações, hora de tentar movê-lo para a pasta
else {

	// Primeiro verifica se deve trocar o nome do arquivo
	if ($_UP['renomeia'] == true) {
	
		// Cria um nome baseado no UNIX TIMESTAMP atual e com extensão .csv
		$nome_final = time().".".$extensao;
	
	} else {
	
		// Mantém o nome original do arquivo
		$nome_final = $_FILES['arquivo']['name'];
	}
	
	$arquivo = new DomDocument();
	$arquivo->load($_FILES['arquivo']['tmp_name']);
	//var_dump($arquivo);die;	

	$linhas = $arquivo->getElementsByTagName("Row");
	//var_dump($linhas);
	
	$primeiraLinha = true;
	$qtd           = 0;
	$importados    = 0;
	$servicosimportados = "";
	$erro = "";
	
	foreach ($linhas as $linha){
		
		if($primeiraLinha){

			$nome = $linha->getElementsByTagName("Data")->item(0)->nodeValue;
			
			if (strtoupper(substr($nome, 0, 4)) != 'NOME' and strtoupper(substr($nome, 0, 4)) != 'SERVI') {
			   $erro = "O formato do arquivo de importação provavelmente não está correto. Verifique Modelo: <a href='global_assets/importacao/modelo.xml'>Modelo de Importação de Serviços</a>";
			   break; //sai do while	
			}
			
			$primeiraLinha = false;
		} else {
			$nome = $linha->getElementsByTagName("Data")->item(0)->nodeValue;
			$detalhamento = $linha->getElementsByTagName("Data")->item(1)->nodeValue;
			
			$qtd++;
			
			$sql = "SELECT ServiId
					FROM Servico
					WHERE ServiEmpresa = ". $_SESSION['EmpreId'] ." and ServiNome = '".$nome."'";
			$result = $conn->query($sql);
			$row = $result->fetch(PDO::FETCH_ASSOC);
			//$count = count($row);
			
			if ($row){

				$sql = "UPDATE Servico SET ServiDetalhamento = :sDetalhamento, ServiUsuarioAtualizador = :iUsuarioAtualizador
						WHERE ServiNome = :sNome and ServiEmpresa = :iEmpresa";
				$result = $conn->prepare($sql);
						
				$result->execute(array(
								':sNome' => $nome,
								':sDetalhamento' => $detalhamento,
								':iUsuarioAtualizador' => $_SESSION['UsuarId'],
								':iEmpresa' => $_SESSION['EmpreId']
								));

				$servicosimportados .= $nome.', ';
				$importados++;
			}
			else {

				$sql = "SELECT CategId
						FROM Categoria
						JOIN Situacao on SituaId = CategStatus
						Where SituaChave = 'ALTERAR' ";
				$result = $conn->query($sql);
				$rowCategoria = $result->fetch(PDO::FETCH_ASSOC);
				
				$sql = "SELECT COUNT(isnull(ServiCodigo,0)) as Codigo
						FROM Servico
						Where ServiEmpresa = ".$_SESSION['EmpreId']."";
				//echo $sql;die;
				$result = $conn->query("$sql");
				$rowCodigo = $result->fetch(PDO::FETCH_ASSOC);	
				
				$sCodigo = (int)$rowCodigo['Codigo'] + 1;
				$sCodigo = str_pad($sCodigo,6,"0",STR_PAD_LEFT);
				
				$sql = "INSERT INTO Servico (ServiCodigo, ServiNome, ServiDetalhamento, ServiCategoria, ServiSubCategoria, 
											 ServiValorCusto, ServiOutrasDespesas, ServiCustoFinal, ServiMargemLucro, ServiValorVenda, 
											 ServiStatus, ServiEmpresa, ServiUsuarioAtualizador) 
						VALUES (:sCodigo, :sNome, :sDetalhamento, :iCategoria, :iSubCategoria, :fValorCusto, 
								:fOutrasDespesas, :fCustoFinal, :fMargemLucro, :fValorVenda, :bStatus, :iEmpresa, :iUsuarioAtualizador);";
				$result = $conn->prepare($sql);
						
				$result->execute(array(
								':sCodigo' => $sCodigo,
								':sNome' => $nome,
								':sDetalhamento' => $detalhamento,								
								':iCategoria' => $rowCategoria['CategId'],
								':iSubCategoria' => null,
								':fValorCusto' => null,						
								':fOutrasDespesas' => null,
								':fCustoFinal' => null,
								':fMargemLucro' => null,
								':fValorVenda' => null,
								':bStatus' => 1,
								':iEmpresa' => $_SESSION['EmpreId'],								
								':iUsuarioAtualizador' => $_SESSION['UsuarId']
								));
				 	    
				$servicosimportados.= $nome.', ';
				$importados++;
			}		
		}
	}

	$relatorio = "<b>Relatório de Importação</b><br><br>";
		
	if ($erro != "") {
		      
	   $relatorio .= "<span style='color:#FF0000;'>Erro na importação</span><br><br>";
	   $relatorio .= $erro; //substr($erro, 0, -1);
	   $relatorio .= "<br><br>";
	   
	   //usado para remover os 2 ultimos caracteres da string, para desaparecer com a ultima vírgula
	   $size = strlen($servicosimportados);
	   $servicosimportados = substr($servicosimportados,0, $size-2);
	   
	   if ($erro == ""){
		   $relatorio .= "Total de registros no arquivo: ".$qtd."<br>";
		   $relatorio .= "Total de registros importados: ".$importados."<br><br>";

		   $relatorio .= "<div style=\"width:600px\"><b>Serviços Importados:</b> ".$servicosimportados."</div><br>";
		   $relatorio .= "<br>";
	   }
		
	   $_SESSION['RelImportacao'] = $relatorio;
	   $_SESSION['Importacao'] = 'Erro';
			
	} else {
		   
	   $relatorio .= "<b>Importação realizada com sucesso!</b><br><br>";	
		
		//usado para remover os 2 ultimos caracteres da string, para desaparecer com a ultima vírgula
	   $size = strlen($servicosimportados);
	   $servicosimportados = substr($servicosimportados,0, $size-2);

	   $relatorio .= "Total de registros no arquivo: ".$qtd."<br>";
	   $relatorio .= "Total de registros importados: ".$importados."<br><br>";

	   $relatorio .= "<div style=\"width:600px\"><b>Serviços Importados:</b> ".$servicosimportados."</div>";
	   
	   $_SESSION['RelImportacao'] = $relatorio; 
	   $_SESSION['Importacao'] = 'Sucesso';

	}

}

irpara("servico.php");

?>