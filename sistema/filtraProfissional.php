<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

$tipoRequest = $_POST['tipoRequest'];

try {

    $iUnidade = $_SESSION['UnidadeId'];
	$usuarioId = $_SESSION['UsuarId'];

    if ($tipoRequest == 'BUSCARDADOSUSUARIO') {

        $iUsuario = $_POST['iUsuario'];

        $sql = "SELECT UsuarId, UsuarCpf, UsuarNome, UsuarEmail, UsuarTelefone, UsuarCelular
                FROM Usuario
                WHERE UsuarId = $iUsuario";
        $result = $conn->query($sql);
        $row = $result->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
			'status' => 'success',
			'data' => $row,
		]);

    }

} catch (\Throwable $e) {

    $msg = '';

	switch($tipoRequest){
		case 'BUSCARDADOSUSUARIO': $msg = 'Erro ao buscar dados do usuário, tente novamente!!';break;
		default: $msg = 'Erro ao executar ação!!';break;
	}
	echo json_encode([
		'titulo' => 'Buscar Usuário',
		'status' => 'error',
		'menssagem' => $msg,
		'error' => $e->getMessage(),
		'sql' => $sql
	]);
}