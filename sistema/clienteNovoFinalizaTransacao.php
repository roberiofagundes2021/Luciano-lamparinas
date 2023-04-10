<?php

include_once("sessao.php");
$_SESSION['PaginaAtual'] = 'Novo Cliente';
include('global_assets/php/conexao.php');
echo '<script language="javascript">';
echo 'console.log("tst")';
echo '</script>';
try {
	$sql = "SELECT COUNT(isnull(clienCodigo,0)) as Codigo
				FROM Cliente
				Where ClienUnidade = " . $_SESSION['UnidadeId'] . "";
	//echo $sql;die;
	$result = $conn->query("$sql");
	$rowCodigo = $result->fetch(PDO::FETCH_ASSOC);

	$sCodigo = (int)$rowCodigo['Codigo'] + 1;
	$sCodigo = str_pad($sCodigo, 6, "0", STR_PAD_LEFT);
} catch (PDOException $e) {
	echo 'Error1: ' . $e->getMessage();
	die;
}

try {

	$sql = "INSERT INTO Cliente (clienCodigo, ClienNome, ClienNomeSocial,  ClienCpf, ClienRg, ClienOrgaoEmissor, ClienUf, ClienSexo, ClienDtNascimento, ClienNomePai, ClienNomeMae,
	 				 	ClienRacaCor, ClienEstadoCivil, ClienNaturalidade, ClienProfissao, ClienCartaoSus, ClienCep, ClienEndereco, ClienNumero, ClienComplemento, ClienBairro, ClienCidade, 
						ClienEstado, ClienContato, ClienTelefone, ClienCelular, ClienEmail, ClienObservacao, ClienStatus, ClienUsuarioAtualizador, ClienUnidade)
				VALUES (:sCodigo, :sNome, :sNomeSocial, :sCpf, :sRg, :sOrgaoEmissor, :sUf, :sSexo, :dDtNascimento, :sNomePai, :sNomeMae, :sRacaCor, :sEstadoCivil, :sNaturalidade,
				        :sProfissao, :sCartaoSus, :sCep, :sEndereco, :sNumero, :sComplemento, :sBairro, :sCidade, :sEstado, :sContato, :sTelefone, :sCelular, 
						:sEmail,:sObservacao, :bStatus, :iUsuarioAtualizador, :iUnidade)";

	$result = $conn->prepare($sql);

	$conn->beginTransaction();

	$result->execute(array(
		':sCodigo' => $sCodigo,
		':sNome' => $_POST['inputNomePF'],
		':sNomeSocial' => $_POST['inputNomeSocial'],
		':sCpf' => limpaCPF_CNPJ($_POST['inputCpf']),
		':sRg' => $_POST['inputRg'],
		':sOrgaoEmissor' => $_POST['inputEmissor'],
		':sUf' => $_POST['cmbUf'],
		':sSexo' => $_POST['cmbSexo'],
		':dDtNascimento' => $_POST['inputDtNascimento'],
		':sNomePai' => $_POST['inputNomePai'],
		':sNomeMae' => $_POST['inputNomeMae'],
		':sRacaCor' => $_POST['cmbRacaCor'],
		':sEstadoCivil' => $_POST['cmbEstadoCivil'],
		':sNaturalidade' => $_POST['inputNaturalidade'],
		':sProfissao' => $_POST['inputProfissao'],
		':sCartaoSus' => $_POST['inputCartaoSus'],
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
		':sObservacao' => $_POST['txtareaObservacao'],
		':bStatus' => 1,
		':iUsuarioAtualizador' => $_SESSION['UsuarId'],
		':iUnidade' => $_SESSION['UnidadeId']
	));
	
	$conn->commit();

	$_SESSION['msg']['titulo'] = "Sucesso";
	$_SESSION['msg']['mensagem'] = "Cliente incluído!!!";
	$_SESSION['msg']['tipo'] = "success";
} catch (PDOException $e) {

	$conn->rollback();

	$_SESSION['msg']['titulo'] = "Erro";
	$_SESSION['msg']['mensagem'] = "Erro ao incluir cliente!!!";
	$_SESSION['msg']['tipo'] = "error";

	echo 'Error: ' . $e->getMessage();
	exit;
}
irpara("cliente.php");
?>