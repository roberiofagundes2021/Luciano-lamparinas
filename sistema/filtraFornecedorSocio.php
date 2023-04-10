<?php 

include_once("sessao.php"); 

$tipoRequest = $_POST['tipoRequest'];

if ($tipoRequest == 'INCLUIRDADOSOCIETARIO') {

	$dadoSocietariosNome = $_POST['dadoSocietariosNome'] == "" ? null : $_POST['dadoSocietariosNome'];
	$dadoSocietariosCPF= $_POST['dadoSocietariosCPF'] == "" ? null : $_POST['dadoSocietariosCPF'];
	$dadoSocietariosRG = $_POST['dadoSocietariosRG'] == "" ? null : $_POST['dadoSocietariosRG'];
	$dadoSocietariosCelular = $_POST['dadoSocietariosCelular'] == "" ? null : $_POST['dadoSocietariosCelular'];
	$dadoSocietariosEmail = $_POST['dadoSocietariosEmail'] == "" ? null : $_POST['dadoSocietariosEmail'];

	$tipo = $_POST['tipo'];

	$dadosSocio['Nome'] = $dadoSocietariosNome;
	$dadosSocio['CPF'] = $dadoSocietariosCPF;
	$dadosSocio['RG'] = $dadoSocietariosRG;
	$dadosSocio['Celular'] = $dadoSocietariosCelular;
	$dadosSocio['Email'] = $dadoSocietariosEmail;

	$_SESSION['fornecedorSocio'][] = $dadosSocio;

	$cont = 1;
	foreach($_SESSION['fornecedorSocio'] as $item){

		$posicao = $cont - 1;

		echo "<tr>
			<td>".$cont."</td>
			<td>".$item['Nome']."</td>
			<td>".$item['CPF']."</td>
			<td>".$item['RG']."</td>
			<td>".$item['Celular']."</td>
			<td>".$item['Email']."</td>
			<td><a href='#' onclick='excluirSocio(XXX)'><i class='icon-bin' title='Excluir Sócio'></i></a></td>
		</tr>";

		$cont++;
	}
	
} 

if ($tipoRequest == 'EXCLUIRDADOSOCIETARIO') {
	
	$nome = $_POST['nome'];
	echo "XXXX";
	//$key = array_search($nome, $_SESSION['fornecedorSocio']);
	//echo $key;
	/*	
	if($key!==false){
		unset($_SESSION['fornecedorSocio'][$key]);
	}	

	$cont = 1;
	foreach($_SESSION['fornecedorSocio'] as $item){

		$posicao = $cont - 1;

		echo "<tr>
			<td>".$cont."-".$key."</td>
			<td>".$item['Nome']."</td>
			<td>".$item['CPF']."</td>
			<td>".$item['RG']."</td>
			<td>".$item['Celular']."</td>
			<td>".$item['Email']."</td>
			<td><a onclick='excluirSocio(".$item['CPF'].")'><i class='icon-bin' title='Excluir Sócio'></i></a></td>
		</tr>";

		$cont++;
	}	*/
}


?>
