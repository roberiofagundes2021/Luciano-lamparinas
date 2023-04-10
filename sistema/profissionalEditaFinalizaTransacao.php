<?php 
include_once("sessao.php"); 
$_SESSION['PaginaAtual'] = 'Editar Profissional';
include('global_assets/php/conexao.php');
$iUnidade = $_SESSION['UnidadeId'];

try{	
		$sTipo= $_POST['inputTipo'];
		$sNome= $_POST['inputTipo'] == 'J' ? $_POST['inputNomePJ'] : $_POST['inputNomePF'];
		$sRazaoSocial= $_POST['inputTipo'] == 'J' ? $_POST['inputRazaoSocial'] : null;
		$sCnpj= $_POST['inputTipo'] == 'J' ? limpaCPF_CNPJ($_POST['inputCnpj']) : null;
		$sInscricaoMunicipal= $_POST['inputTipo'] == 'J' ? $_POST['inputInscricaoMunicipal'] : null;
		$sInscricaoEstadual= $_POST['inputTipo'] == 'J' ? $_POST['inputInscricaoEstadual'] : null;
		$sCpf= $_POST['inputTipo'] == 'F' ? limpaCPF_CNPJ($_POST['inputCpf']) : null;
		$sCns= $_POST['inputTipo'] == 'F' ? $_POST['inputCns'] : null;
		$sRg= $_POST['inputTipo'] == 'F' ? $_POST['inputRg'] : null;
		$sOrgaoEmissor= $_POST['inputTipo'] == 'F' ? $_POST['inputEmissor'] : null;
		$sUf= $_POST['inputTipo'] == 'J' || $_POST['cmbUf'] == '#' ? null : $_POST['cmbUf'];
		$sSexo= $_POST['inputTipo'] == 'J' || $_POST['cmbSexo'] == '#' ? null : $_POST['cmbSexo'];
		$dDtNascimento= $_POST['inputTipo'] == 'F' ? ($_POST['inputDtNascimento'] == '' ? null : $_POST['inputDtNascimento']) : null;
		$sProfissao= $_POST['inputTipo'] == 'J'? $_POST['cmbProfissaoPJ'] : $_POST['cmbProfissaoPF'];
		$sConselho= $_POST['inputTipo'] == 'F' ? ($_POST['cmbConselho'] == '#' ? null : $_POST['cmbConselho']) : null;
		$sNumConselho= $_POST['inputTipo'] == 'F' ? $_POST['inputNumConselho'] : null;
		$sCnes= $_POST['inputTipo']  == 'J' ? $_POST['inputCnesPJ'] : $_POST['inputCnesPF'];
		$sCtps= $_POST['inputTipo'] == 'F' ? $_POST['inputCtps'] : null;
		$sCep= trim($_POST['inputCep']) == "" ? null : $_POST['inputCep'];
		$sEndereco= $_POST['inputEndereco'];
		$sNumero= $_POST['inputNumero'];
		$sComplemento= $_POST['inputComplemento'];
		$sBairro= $_POST['inputBairro'];
		$sCidade= $_POST['inputCidade'];
		$sEstado= $_POST['cmbEstado'];
		$sContato= $_POST['inputNomeContato'];
		$sTelefone= $_POST['inputTelefone'] == '(__) ____-____' ? null : $_POST['inputTelefone'];
		$sCelular= $_POST['inputCelular'] == '(__) _____-____' ? null : $_POST['inputCelular'];
		$sEmail= $_POST['inputEmail'];
		$sSite= $_POST['inputSite'];
		$sObservacao= $_POST['txtareaObservacao'];
		$sBanco= $_POST['cmbBanco'];
		$sAgencia= $_POST['inputAgencia'];
		$sConta= $_POST['inputConta'];
		$sInformacaoAdicional= $_POST['inputInformacaoAdicional'];
		$iUsuario= $_POST['cmbUsuario'];
		$iUsuarioAtualizador= $_SESSION['UsuarId'];
		$iProfissional= $_POST['inputProfissionalId'];
		

		// para garantir que os valores tanto de pessoa física quanto pessoa jurídica são salvos como null corretamente, e não uma string em branco
		$sTipo = $sTipo==NULL?"NULL":"'{$sTipo}'";
		$sNome = $sNome==NULL?"NULL":"'{$sNome}'";
		$sRazaoSocial = $sRazaoSocial==NULL?"NULL":"'{$sRazaoSocial}'";
		$sCnpj = $sCnpj==NULL?"NULL":"'{$sCnpj}'";
		$sInscricaoMunicipal = $sInscricaoMunicipal==NULL?"NULL":"'{$sInscricaoMunicipal}'";
		$sInscricaoEstadual = $sInscricaoEstadual==NULL?"NULL":"'{$sInscricaoEstadual}'";
		$sCpf = $sCpf==NULL?"NULL":"'{$sCpf}'";
		$sCns = $sCns==NULL?"NULL":"'{$sCns}'";
		$sRg = $sRg==NULL?"NULL":"'{$sRg}'";
		$sOrgaoEmissor = $sOrgaoEmissor==NULL?"NULL":"'{$sOrgaoEmissor}'";
		$sUf = $sUf==NULL?"NULL":"'{$sUf}'";
		$sSexo = $sSexo==NULL?"NULL":"'{$sSexo}'";
		$dDtNascimento = $dDtNascimento==NULL?"NULL":"'{$dDtNascimento}'";
		$sProfissao = $sProfissao==NULL?"NULL":"'{$sProfissao}'";
		$sConselho = $sConselho==NULL?"NULL":"'{$sConselho}'";
		$sNumConselho = $sNumConselho==NULL?"NULL":"'{$sNumConselho}'";
		$sCnes = $sCnes==NULL?"NULL":"'{$sCnes}'";
		$sCtps = $sCtps==NULL?"NULL":"'{$sCtps}'";
		$sCep = $sCep==NULL?"NULL":"'{$sCep}'";
		$sEndereco = $sEndereco==NULL?"NULL":"'{$sEndereco}'";
		$sNumero = $sNumero==NULL?"NULL":"'{$sNumero}'";
		$sComplemento = $sComplemento==NULL?"NULL":"'{$sComplemento}'";
		$sBairro = $sBairro==NULL?"NULL":"'{$sBairro}'";
		$sCidade = $sCidade==NULL?"NULL":"'{$sCidade}'";
		$sEstado = $sEstado==NULL?"NULL":"'{$sEstado}'";
		$sContato = $sContato==NULL?"NULL":"'{$sContato}'";
		$sTelefone = $sTelefone==NULL?"NULL":"'{$sTelefone}'";
		$sCelular = $sCelular==NULL?"NULL":"'{$sCelular}'";
		$sEmail = $sEmail==NULL?"NULL":"'{$sEmail}'";
		$sSite = $sSite==NULL?"NULL":"'{$sSite}'";
		$sObservacao = $sObservacao==NULL?"NULL":"'{$sObservacao}'";
		$sBanco = $sBanco==NULL?"NULL":"'{$sBanco}'";
		$sAgencia = $sAgencia==NULL?"NULL":"'{$sAgencia}'";
		$sConta = $sConta==NULL?"NULL":"'{$sConta}'";
		$sInformacaoAdicional = $sInformacaoAdicional==NULL?"NULL":"'{$sInformacaoAdicional}'";
		$iUsuario = $iUsuario==NULL?"NULL":"'{$iUsuario}'";
		$iUsuarioAtualizador = $iUsuarioAtualizador==NULL?"NULL":"'{$iUsuarioAtualizador}'";
		$iProfissional = $iProfissional==NULL?"NULL":"'{$iProfissional}'";

		$sql = 
		"UPDATE
			Profissional
		SET
			ProfiTipo = $sTipo,
			ProfiNome = $sNome,
			ProfiRazaoSocial = $sRazaoSocial,
			ProfiCnpj = $sCnpj,
			ProfiInscricaoMunicipal = $sInscricaoMunicipal,
			ProfiInscricaoEstadual = $sInscricaoEstadual,
			ProfiCpf = $sCpf,
			ProfiCNS = $sCns,
			ProfiRg = $sRg,
			ProfiOrgaoEmissor = $sOrgaoEmissor,
			ProfiUf = $sUf,
			ProfiSexo = $sSexo,
			ProfiDtNascimento = $dDtNascimento,
			ProfiProfissao = $sProfissao,
			ProfiConselho = $sConselho,
			ProfiNumConselho = $sNumConselho,
			ProfiCNES = $sCnes,
			ProfiCTPS = $sCtps,
			ProfiCep = $sCep,
			ProfiEndereco = $sEndereco,
			ProfiNumero = $sNumero,
			ProfiComplemento = $sComplemento,
			ProfiBairro = $sBairro,
			ProfiCidade = $sCidade,
			ProfiEstado = $sEstado,
			ProfiContato = $sContato,
			ProfiTelefone = $sTelefone,
			ProfiCelular = $sCelular,
			ProfiEmail = $sEmail,
			ProfiSite = $sSite,
			ProfiObservacao = $sObservacao,
			ProfiBanco = $sBanco,
			ProfiAgencia = $sAgencia,
			ProfiConta = $sConta,
			ProfiInformacaoAdicional = $sInformacaoAdicional,
			ProfiUsuario = $iUsuario,
			ProfiUsuarioAtualizador = $iUsuarioAtualizador
		WHERE
			ProfiId = $iProfissional ";		
		
		$conn->query($sql);

		if($_POST['inputTipo'] == 'F'){
			$sql = "DELETE FROM ProfissionalXEspecialidade WHERE PrXEsProfissional = $iProfissional and PrXEsUnidade = $iUnidade";
			$conn->query($sql);

			$sql = "INSERT INTO ProfissionalXEspecialidade(PrXEsProfissional,PrXEsEspecialidade,PrXEsUnidade)
			VALUES ";
			if(isset($_POST['cmbEspecialidade'])){
				foreach($_POST['cmbEspecialidade'] as $item){
						$sql .= "($iProfissional, '$item', '$iUnidade'),";
					}
					$sql = substr($sql, 0, -1);
					$conn->query($sql);
				}
			}
		
		$_SESSION['msg']['titulo'] = "Sucesso";
		$_SESSION['msg']['mensagem'] = "Profissional alterado!!!";
		$_SESSION['msg']['tipo'] = "success";
		
	} catch(PDOException $e) {
		
		$_SESSION['msg']['titulo'] = "Erro";
		$_SESSION['msg']['mensagem'] = "Erro ao alterar profissional!!!";
		$_SESSION['msg']['tipo'] = "error";	
		
		echo 'Error: ' . $e->getMessage();
		exit;
	}
    finally{
	irpara("profissional.php");
    }
