<?php
require_once("global_assets/php/funcoesgerais.php");
include('global_assets/php/conexao.php');

$iUsuario = isset($_POST['usuario'])?$_POST['usuario']:false;
$iEmpresa = isset($_POST['empresa'])?$_POST['empresa']:false;

if ($iEmpresa && $iUsuario){
	$sql = "SELECT UnidaId, UnidaNome
			FROM UsuarioXUnidade
			JOIN EmpresaXUsuarioXPerfil on EXUXPId = UsXUnEmpresaUsuarioPerfil
			JOIN Unidade on UnidaId = UsXUnUnidade
			WHERE EXUXPUsuario = $iUsuario and EXUXPEmpresa = $iEmpresa";
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
} else {
	$sql = "SELECT UsuarId, UsuarLogin, UsuarNome, EmpreId, EmpreNomeFantasia, PerfiChave, EmpreFoto
	FROM Usuario
	JOIN EmpresaXUsuarioXPerfil on EXUXPUsuario = UsuarId
	JOIN Situacao on SituaId = EXUXPStatus
	JOIN Perfil on PerfiId = EXUXPPerfil
	JOIN Empresa on EmpreId = EXUXPEmpresa
	WHERE UsuarId = $iUsuario";
	$result = $conn->query($sql);
	$row = $result->fetchAll(PDO::FETCH_ASSOC);
}

print json_encode($row);