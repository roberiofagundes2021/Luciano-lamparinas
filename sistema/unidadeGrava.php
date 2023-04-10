<?php  

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$sql = "INSERT INTO Unidade (UnidaNome, UnidaCNES, UnidaCnpj, UnidaTelefone, UnidaDiretorAdministrativo, UnidaDiretorTecnico, UnidaDiretorClinico, UnidaCep, UnidaEndereco, UnidaNumero, UnidaComplemento, UnidaBairro, 
UnidaCidade, UnidaEstado, UnidaStatus, UnidaUsuarioAtualizador, UnidaEmpresa)
VALUES (:sNome, :sCNES, :sCnpj, :sTelefone, :sDiretorAdministrativo, :sDiretorTecnico, :sDiretorClinico, :sCep, :sEndereco, :sNumero, :sComplemento, :sBairro, 
:sCidade, :sEstado, :bStatus, :iUsuarioAtualizador, :iEmpresa)";
$result = $conn->prepare($sql);

$result->execute(array(
	':sNome' => $_POST['nome'],
	':sCNES' => $_POST['cnes'] == '' ? null : $_POST['cnes'],
	':sCnpj' => limpaCPF_CNPJ($_POST['cnpj']),
    ':sTelefone' => $_POST['telefone'] == '(__) ____-____' ? null : $_POST['telefone'],
    ':sDiretorAdministrativo' => $_POST['diretorAdministrativo'],
    ':sDiretorTecnico' => $_POST['diretorTecnico'],
    ':sDiretorClinico' => $_POST['diretorClinico'],
	':sCep' => $_POST['cep'],
	':sEndereco' => $_POST['endereco'],
	':sNumero' => $_POST['numero'],
	':sComplemento' => $_POST['complemento'],
	':sBairro' => $_POST['bairro'],
	':sCidade' => $_POST['cidade'],
	':sEstado' => $_POST['estado'],
	':bStatus' => 1,
	':iUsuarioAtualizador' => $_SESSION['UsuarId'],
	':iEmpresa' => $_SESSION['EmpresaId'],
));

//Retorna o ID inserido
echo $conn->lastInsertId();

?>
