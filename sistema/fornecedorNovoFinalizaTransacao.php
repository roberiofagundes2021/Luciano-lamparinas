<?php 

include_once("sessao.php"); 
$_SESSION['PaginaAtual'] = 'Novo Fornecedor';
include('global_assets/php/conexao.php');

if(isset($_POST['inputTipo'])){
		
try{
	$input_foto = null;
	if($_POST['inputTipo'] == 'F'){
		if(isset($_POST['inputFoto'])){
			$input_foto = $_POST['inputFoto'];
		}
		$nome = $_POST['inputNome'];
		$categoria = $_POST['cmbCategoriaPF'];
		$subCategoria = isset($_POST['cmbSubCategoriaPF']) ? $_POST['cmbSubCategoriaPF'] : 0;
	} else{
		$nome = $_POST['inputNomeFantasia'];
		$categoria = $_POST['cmbCategoriaPJ'];
		$subCategoria = isset($_POST['cmbSubCategoriaPJ']) ? $_POST['cmbSubCategoriaPJ'] : 0;
	}

	$sql = "INSERT INTO Fornecedor (ForneTipo, ForneNome, ForneRazaoSocial, ForneCnpj, ForneInscricaoMunicipal, ForneInscricaoEstadual, ForneCategoria,
									ForneCpf, ForneRg, ForneOrgaoEmissor, ForneUf, ForneSexo, ForneAniversario, ForneNaturalidade, ForneNaturalidadeUf,
									ForneNacionalidade, ForneAno, ForneCarteiraTrabalho, ForneNumSerie, ForneNit, ForneNire, ForneFoto, ForneCep, ForneEndereco,
									ForneNumero, ForneComplemento, ForneBairro, ForneCidade, ForneEstado, ForneContato, ForneTelefone, ForneTelefoneComercial,
									ForneCelular, ForneEmail, ForneSite, ForneObservacao, ForneBanco, ForneAgencia, ForneConta, ForneInformacaoAdicional,
									ForneIpi, ForneFrete, ForneIcms, ForneOutros, ForneStatus, ForneUsuarioAtualizador, ForneEmpresa)
			VALUES (:sTipo, :sNome, :sRazaoSocial, :sCnpj, :sInscricaoMunicipal, :sInscricaoEstadual, :iCategoria, 
					:sCpf, :sRg, :sOrgaoEmissor, :sUf, :sSexo, :dAniversario, :sNaturalidade, :sNaturalidadeUf,
					:sNacionalidade, :sAno, :sCarteiraTrabalho,  :sNumSerie, :sNit, :sNire, :sFoto, :sCep, :sEndereco,
					:sNumero, :sComplemento, :sBairro, :sCidade, :sEstado, :sContato, :sTelefone, :sTelefoneComercial,
					:sCelular, :sEmail, :sSite, :sObservacao, :iBanco, :sAgencia, :sConta, :sInformacaoAdicional,
					:iIpi, :iFrete, :iIcms, :iOutros, :bStatus, :iUsuarioAtualizador, :iEmpresa)";
		$result = $conn->prepare($sql);

		$conn->beginTransaction();
		
		$result->execute(array(
						':sTipo' => $_POST['inputTipo'],
						':sNome' => $nome,
						':sRazaoSocial' => $_POST['inputTipo'] == 'J' ? $_POST['inputRazaoSocial'] : null,
						':sCnpj' => $_POST['inputTipo'] == 'J' ? limpaCPF_CNPJ($_POST['inputCnpj']) : null,
						':sInscricaoMunicipal' => $_POST['inputTipo'] == 'J' ? $_POST['inputInscricaoMunicipal'] : null,
						':sInscricaoEstadual' => $_POST['inputTipo'] == 'J' ? $_POST['inputInscricaoEstadual'] : null,
						':iCategoria' => $categoria,
						':sCpf' => $_POST['inputTipo'] == 'F' ? limpaCPF_CNPJ($_POST['inputCpf']) : null,
						':sRg' => $_POST['inputTipo'] == 'F' ? $_POST['inputRg'] : null,
						':sOrgaoEmissor' => $_POST['inputTipo'] == 'F' ? $_POST['inputEmissor'] : null,
						':sUf' => $_POST['inputTipo'] == 'J' || $_POST['cmbUf'] == '#' ? null : $_POST['cmbUf'],
						':sSexo' => $_POST['inputTipo'] == 'J' || $_POST['cmbSexo'] == '#' ? null : $_POST['cmbSexo'],
						':dAniversario' => $_POST['inputTipo'] == 'F' ? ($_POST['inputAniversario'] == '' ? null : $_POST['inputAniversario']) : null,
						':sNaturalidade' => $_POST['inputTipo'] == 'J' || $_POST['inputNaturalidade'] == '#' ? null : $_POST['inputNaturalidade'],
						':sNaturalidadeUf' => $_POST['inputTipo'] == 'J' || $_POST['inputNaturalidadeUf'] == '#' ? null : $_POST['inputNaturalidadeUf'],
						':sNacionalidade' => $_POST['inputTipo'] == 'J' || $_POST['inputNacionalidade'] == '#' ? null : $_POST['inputNacionalidade'],
						':sAno' => $_POST['inputTipo'] == 'J' || $_POST['inputAno'] == '#' ? null : $_POST['inputAno'],
						':sCarteiraTrabalho' => $_POST['inputTipo'] == 'J' || $_POST['inputCarteiraTrabalho'] == '#' ? null : $_POST['inputCarteiraTrabalho'],
						':sNumSerie' => $_POST['inputTipo'] == 'F' ? $_POST['inputNumSerie'] : null,
						':sNit' => $_POST['inputNit'],
						':sNire' => $_POST['inputNire'],
						':sFoto' => $input_foto,
						':sCep' => $_POST['inputCep'],
						':sEndereco' => $_POST['inputEndereco'],
						':sNumero' => $_POST['inputNumero'],
						':sComplemento' => $_POST['inputComplemento'],
						':sBairro' => $_POST['inputBairro'],
						':sCidade' => $_POST['inputCidade'],
						':sEstado' => $_POST['cmbEstado'],
						':sContato' => $_POST['inputNomeContato'],
						':sTelefone' => $_POST['inputTelefoneResidencial'] == '(__) ____-____' ? null : $_POST['inputTelefoneResidencial'],
						':sTelefoneComercial' => $_POST['inputTelefoneComercial'] == '(__) ____-____' ? null : $_POST['inputTelefoneComercial'],
						':sCelular' => $_POST['inputCelular'] == '(__) _____-____' ? null : $_POST['inputCelular'],
						':sEmail' => $_POST['inputEmail'],
						':sSite' => $_POST['inputSite'],
						':sObservacao' => $_POST['txtareaObservacao'],
						':iBanco' => $_POST['cmbBanco'] == '#' ? null : $_POST['cmbBanco'],
						':sAgencia' => $_POST['inputAgencia'],
						':sConta' => $_POST['inputConta'],
						':sInformacaoAdicional' => $_POST['inputInfoAdicional'],
						':iIpi' => $_POST['inputIpi'] == null ? 0.00 : gravaValor($_POST['inputIpi']),
						':iFrete' => $_POST['inputFrete'] == null ? 0.00 : gravaValor($_POST['inputFrete']),
						':iIcms' => $_POST['inputIcms'] == null ? 0.00 : gravaValor($_POST['inputIcms']),
						':iOutros' => $_POST['inputOutros'] == null ? 0.00 : gravaValor($_POST['inputOutros']),
						':bStatus' => 1,
						':iUsuarioAtualizador' => $_SESSION['UsuarId'],
						':iEmpresa' => $_SESSION['EmpreId']
					));

		$insertId = $conn->lastInsertId(); 
		
		if ($subCategoria){
			
			try{
				$sql = "INSERT INTO FornecedorXSubCategoria 
							(FrXSCFornecedor, FrXSCSubCategoria, FrXSCUnidade)
						VALUES 
							(:iFornecedor, :iSubCategoria, :iUnidade)";
				$result = $conn->prepare($sql);

				foreach ($subCategoria as $key => $value){

					$result->execute(array(
									':iFornecedor' => $insertId,
									':iSubCategoria' => $value,
									':iUnidade' => $_SESSION['UnidadeId']
									));
				}
				
			} catch(PDOException $e) {
				$conn->rollback();
				echo 'Error: ' . $e->getMessage();exit;
			}
		}
		
		$conn->commit();

		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Fornecedor incluído!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {		
		
		$conn->rollback();
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao incluir fornecedor!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
		exit;
	}
	
	irpara("fornecedor.php");
}

?>

