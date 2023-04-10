<?php 
include_once("sessao.php"); 
$_SESSION['PaginaAtual'] = 'Editar Cliente';
include('global_assets/php/conexao.php');

try{

    $conn->beginTransaction();
    
    $sql = "UPDATE Cliente SET 	ClienCodigo = :sCodigo,  ClienNome = :sNome, ClienNomeSocial = :sNomeSocial, ClienCpf = :sCpf, ClienRg = :sRg, ClienOrgaoEmissor = :sOrgaoEmissor,
                                ClienUf = :sUf, ClienSexo = :sSexo, ClienDtNascimento = :dDtNascimento, ClienNomePai = :sNomePai, ClienNomeMae = :sNomeMae,
                                ClienRacaCor = :sRacaCor, ClienEstadoCivil = :sEstadoCivil, ClienNaturalidade = :sNaturalidade, ClienProfissao = :sProfissao, ClienCartaoSus = :sCartaoSus,
                                ClienCep = :sCep, ClienEndereco = :sEndereco, ClienNumero = :sNumero, ClienComplemento = :sComplemento, ClienBairro = :sBairro, 
                                ClienCidade = :sCidade, ClienEstado = :sEstado, ClienContato = :sContato, ClienTelefone = :sTelefone, ClienCelular = :sCelular,
                                ClienEmail = :sEmail, ClienObservacao = :sObservacao, ClienUsuarioAtualizador = :iUsuarioAtualizador
            WHERE ClienId = :iCliente";
    $result = $conn->prepare($sql);						
    $_POST['inputTipo']="F";
    $result->execute(array(
                    ':sCodigo' => $_POST['inputCodigo'],
                    ':sNome' => $_POST['inputNomePF'],
                    ':sNomeSocial' => $_POST['inputNomeSocial'],
                    ':sCpf' => limpaCPF_CNPJ($_POST['inputCpf']),
                    ':sRg' =>  $_POST['inputRg'],
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
                    ':sCep' => trim($_POST['inputCep']) == "" ? null : $_POST['inputCep'],
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
                    ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                    ':iCliente'	=> $_POST['inputClienteId']
                    ));
        
    $conn->commit();
    
    $_SESSION['msg']['titulo'] = "Sucesso";
    $_SESSION['msg']['mensagem'] = "Cliente alterado!!!";
    $_SESSION['msg']['tipo'] = "success";
    
} catch(PDOException $e) {
    
    $conn->rollback();
    
    $_SESSION['msg']['titulo'] = "Erro";
    $_SESSION['msg']['mensagem'] = "Erro ao alterar cliente!!!";
    $_SESSION['msg']['tipo'] = "error";	
    
    echo 'Error: ' . $e->getMessage();
    exit;
}
irpara("cliente.php");
?>