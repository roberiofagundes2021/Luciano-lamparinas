<?php 

include_once("sessao.php"); 
$_SESSION['PaginaAtual'] = 'Novo Profissional';
include('global_assets/php/conexao.php');
$iUnidade = $_SESSION['UnidadeId'];

try{		
	$sql = "SELECT COUNT(isnull(ProfiCodigo,0)) as Codigo
			FROM Profissional
			Where ProfiUnidade = $iUnidade";
	//echo $sql;die;
	$result = $conn->query("$sql");
	$rowCodigo = $result->fetch(PDO::FETCH_ASSOC);	
	
	$sCodigo = (int)$rowCodigo['Codigo'] + 1;
	$sCodigo = str_pad($sCodigo,6,"0",STR_PAD_LEFT);
} catch(PDOException $e) {	
	echo 'Error1: ' . $e->getMessage();die;
}
		
try{
		
	$sql = "INSERT INTO Profissional (ProfiCodigo, ProfiTipo, ProfiNome, ProfiRazaoSocial, ProfiCnpj, ProfiInscricaoMunicipal, ProfiInscricaoEstadual, 
									ProfiCpf, ProfiCNS, ProfiRg, ProfiOrgaoEmissor, ProfiUf, ProfiSexo, ProfiDtNascimento, ProfiProfissao, ProfiConselho, ProfiNumConselho,
									ProfiCNES, ProfiCTPS, ProfiCep, ProfiEndereco, ProfiNumero, ProfiComplemento, ProfiBairro, ProfiCidade, 
									ProfiEstado, ProfiContato, ProfiTelefone, ProfiCelular, ProfiEmail, ProfiSite, ProfiObservacao, ProfiBanco, ProfiAgencia,
									ProfiConta, ProfiInformacaoAdicional, ProfiUsuario, ProfiStatus, ProfiUsuarioAtualizador, ProfiUnidade)
			VALUES (:sCodigo,:sTipo, :sNome, :sRazaoSocial, :sCnpj, :sInscricaoMunicipal, :sInscricaoEstadual,  
					:sCpf, :sCns, :sRg, :sOrgaoEmissor, :sUf, :sSexo, :dDtNascimento, :sProfissao, :sConselho, :sNumConselho,
					:sCnes, :sCtps, :sCep, :sEndereco, :sNumero, :sComplemento, :sBairro,:sCidade, 
					:sEstado, :sContato, :sTelefone, :sCelular, :sEmail, :sSite, :sObservacao, :sBanco, :sAgencia, 
					:sConta, :sInformacaoAdicional, :iUsuario, :bStatus, :iUsuarioAtualizador, :iUnidade)";
							
	$result = $conn->prepare($sql);

	$conn->beginTransaction();    

	$result->execute(array(
		':sCodigo' => $sCodigo,
		':sTipo' => $_POST['inputTipo'],
		':sNome' => $_POST['inputTipo'] == 'J' ? $_POST['inputNomePJ'] : $_POST['inputNomePF'],
		':sRazaoSocial' => $_POST['inputTipo'] == 'J' ? $_POST['inputRazaoSocial'] : null,
		':sCnpj' => $_POST['inputTipo'] == 'J' ? limpaCPF_CNPJ($_POST['inputCnpj']) : null,
		':sInscricaoMunicipal' => $_POST['inputTipo'] == 'J' ? $_POST['inputInscricaoMunicipal'] : null,
		':sInscricaoEstadual' => $_POST['inputTipo'] == 'J' ? $_POST['inputInscricaoEstadual'] : null,
		':sCpf' => $_POST['inputTipo'] == 'F' ? limpaCPF_CNPJ($_POST['inputCpf']) : null,
		':sCns' => $_POST['inputTipo'] == 'F' ? $_POST['inputCns'] : null,
		':sRg' => $_POST['inputTipo'] == 'F' ? $_POST['inputRg'] : null,
		':sOrgaoEmissor' => $_POST['inputTipo'] == 'F' ? $_POST['inputEmissor'] : null,
		':sUf' => $_POST['inputTipo'] == 'J' || $_POST['cmbUf'] == '#' ? null : $_POST['cmbUf'],
		':sSexo' => $_POST['inputTipo'] == 'J' || $_POST['cmbSexo'] == '#' ? null : $_POST['cmbSexo'],
		':dDtNascimento' => $_POST['inputTipo'] == 'F' ? ($_POST['inputDtNascimento'] == '' ? null : $_POST['inputDtNascimento']) : null,
		':sProfissao' => $_POST['inputTipo'] == 'J' ? $_POST['cmbProfissaoPJ'] : $_POST['cmbProfissaoPF'],
		':sConselho' => $_POST['inputTipo'] == 'F' ? ($_POST['cmbConselho'] == '#' ? null : $_POST['cmbConselho']) : null,
		':sNumConselho' => $_POST['inputTipo'] == 'F' ? $_POST['inputNumConselho'] : null,
		':sCnes' => $_POST['inputTipo']  == 'J' ? $_POST['inputCnesPJ'] : $_POST['inputCnesPF'],
		':sCtps' => $_POST['inputTipo'] == 'F' ? $_POST['inputCtps'] : null,
		':sCep' => $_POST['inputCep'],           
		':sEndereco' => $_POST['inputEndereco'],
		':sNumero' => $_POST['inputNumero'],
		':sComplemento' => $_POST['inputComplemento'],
		':sBairro' => $_POST['inputBairro'],
		':sCidade' => $_POST['inputCidade'],
		':sEstado' => $_POST['cmbEstado'],
		':sContato' => $_POST['inputNomeContato'],
		':sTelefone' => $_POST['inputTelefone'] == '(__) ____-____' ? null : $_POST['inputTelefone'],
		':sCelular' => $_POST['inputCelular'] == '(__) _____-____' ? null : $_POST['inputCelular'],
		':sEmail' => $_POST['inputEmail'],
		':sSite' => $_POST['inputSite'],
		':sObservacao' => $_POST['txtareaObservacao'],
		':sBanco' => $_POST['cmbBanco'],
		':sAgencia' => $_POST['inputAgencia'],
		':sConta' => $_POST['inputConta'],
		':sInformacaoAdicional' => $_POST['inputInformacaoAdicional'],
		':iUsuario' => $_POST['cmbUsuario'],
		':bStatus' => 1,
		':iUsuarioAtualizador' => $_SESSION['UsuarId'],
		':iUnidade' => $iUnidade
		));  

	$conn->commit();

	$profissional = $conn->lastInsertId();

	if($_POST['inputTipo'] == 'F'){
		$sql = "INSERT INTO ProfissionalXEspecialidade(PrXEsProfissional,PrXEsEspecialidade,PrXEsUnidade)
		VALUES ";
		foreach($_POST['cmbEspecialidade'] as $item){
			$sql .= "('$profissional', '$item', '$iUnidade'),";
		}
		$sql = substr($sql, 0, -1);
		$conn->query($sql);
	}

	$_SESSION['msg']['titulo'] = "Sucesso";
	$_SESSION['msg']['mensagem'] = "Profissional incluído!!!";
	$_SESSION['msg']['tipo'] = "success";
	
} catch(PDOException $e) {		
	
	$conn->rollback();
	
	$_SESSION['msg']['titulo'] = "Erro";
	$_SESSION['msg']['mensagem'] = "Erro ao incluir profissional!!!";
	$_SESSION['msg']['tipo'] = "error";	
	echo 'Error: ' . $e->getMessage();
	exit;
}
finally{
	irpara("profissional.php");
}
