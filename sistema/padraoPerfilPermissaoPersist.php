<?php 

include_once("sessao.php"); 

$_SESSION['PaginaAtual'] = 'Permissões';

include('global_assets/php/conexao.php');

// $inicio1 = microtime(true);

$Unidade = $_POST['unidade'];
$PerfilId = $_POST['PerfilId'];
$idMenu = [];

unset($_POST['MenuId']);
unset($_POST['PerfId']);

$sqlupdatePerfil = "SELECT PaPrXPeId FROM menu
join situacao on MenuStatus = SituaId
join PadraoPerfilXPermissao on MenuId = PaPrXPeMenu and PaPrXPePerfil = '$PerfilId' and PaPrXPeUnidade = $Unidade
order by MenuOrdem asc";

$resultUpdatePerfil = $conn->query($sqlupdatePerfil);
$menusUpdate = $resultUpdatePerfil->fetchAll(PDO::FETCH_ASSOC);
$arrayUpdate = [];
try{
	foreach($menusUpdate as $menu){
		$sqlUpdate = "UPDATE PadraoPerfilXPermissao set PaPrXPeVisualizar =".
		(array_key_exists($menu['PaPrXPeId']."-view", $_POST)? 1:0).", PaPrXPeAtualizar = ".
		(array_key_exists($menu['PaPrXPeId']."-edit", $_POST)? 1:0).", PaPrXPeInserir = ".
		(array_key_exists($menu['PaPrXPeId']."-insert", $_POST)? 1:0).", PaPrXPeExcluir = ".
		(array_key_exists($menu['PaPrXPeId']."-delet", $_POST)? 1:0)." where PaPrXPeId = '$menu[PaPrXPeId]'";
		array_push($arrayUpdate, $sqlUpdate);
	}
	foreach($arrayUpdate as $sql){
		$conn->query($sql);
	}
	$_SESSION['msg']['titulo'] = "Sucesso";
	$_SESSION['msg']['mensagem'] = "Padrão de permissão atualizada!!!";
	$_SESSION['msg']['tipo'] = "success";

	// $total1 = microtime(true) - $inicio1;
	// echo '<span style="background-color:yellow; padding: 10px; font-size:24px;">Tempo de execução do script: ' . round($total1, 2).' segundos</span>';

	irpara("perfil.php");
} catch(PDOException $e) {
	// var_dump($e);
	$_SESSION['msg']['titulo'] = "Erro";
	$_SESSION['msg']['mensagem'] = "Erro ao atualizar Padrão de permissão!!!";
	$_SESSION['msg']['tipo'] = "error";
	irpara("perfil.php");
	
	// echo 'Error: ' . $e->getMessage();
}
?>
