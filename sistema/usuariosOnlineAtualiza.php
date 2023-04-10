<?php
include_once("sessao.php");

include('global_assets/php/conexao.php');

if (isset($_POST['timesTampUsuarioOnline'])) {

    $ultimoAcesso = $_POST['timesTampUsuarioOnline'];
    $hora = $_POST['hora'];
    $minuto = $_POST['minuto'];
    $segundos = intval($_POST['segundos']);
    $usuario = $_SESSION['UsuarId'];

    $sql = " UPDATE Usuario set UsuarDataAcesso = :sDataAcesso WHERE UsuarId = :id ";
    $result = $conn->prepare($sql);
    $result->bindParam(':sDataAcesso', $ultimoAcesso);
    $result->bindParam(':id', $usuario);
    $result->execute();

    $segundos = $segundos + 1;

    $string = $hora . ":" . $minuto . ":" . $segundos;

    $sql = "SELECT UsuarId, UsuarNome, UsuarDataAcesso, SetorNome
            FROM Usuario
            JOIN EmpresaXUsuarioXPerfil on EXUXPUsuario =  UsuarId
            JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
            JOIN Setor on SetorId =  UsXUnSetor
            WHERE UsuarDataAcesso >= '$string'  and UsXUnUnidade = " . $_SESSION['UnidadeId'] . "
            ORDER BY UsuarDataAcesso";
    $result = $conn->query($sql);
    $row = $result->fetchAll(PDO::FETCH_ASSOC);


    /**$ultimoAcesso = $_POST['timesTampUsuarioOnline'];
    $hora = intval($_POST['hora']);
    $minuto = intVal($_POST['minuto']);
    $segundos = intval($_POST['segundos']);
    $usuario = $_SESSION['UsuarId'];

    $time = $hora + $minuto + $segundos;

    $sql = " UPDATE Usuario set UsuarDataAcesso = :sDataAcesso  WHERE UsuarId = :id ";
    $result = $conn->prepare($sql);
    $result->bindParam(':sDataAcesso', $time);
    $result->bindParam(':id', $usuario);
    $result->execute();

    $segundos = $segundos - 1;

    $string = $$hora + $minuto + $segundos;

    $sql = "SELECT UsuarId, UsuarNome, UsuarDataAcesso, SetorNome
                FROM Usuario
                JOIN EmpresaXUsuarioXPerfil on EXUXPUsuario =  UsuarId
                JOIN UsuarioXUnidade on UsXUnEmpresaUsuarioPerfil = EXUXPId
                JOIN Setor on SetorId =  UsXUnSetor
                WHERE UsuarDataAcesso >= '$string'  and UsXUnUnidade = " . $_SESSION['UnidadeId'] . "
                ORDER BY UsuarDataAcesso";
    $result = $conn->query($sql);
    $row = $result->fetchAll(PDO::FETCH_ASSOC);
    print($_POST['timesTampUsuarioOnline']); */



    foreach ($row as $usuario) {
        print('
                <li class="media">
                   <div class="media-body">
                       <p class="media-title font-weight-semibold">' . $usuario['UsuarNome'] . '</p>
                       <span class="d-block text-muted font-size-sm">' . $usuario['SetorNome'] . '</span>
                   </div>
                   <div class="ml-3 align-self-center"><span class="badge badge-mark border-success"></span></div>
                </li>
           ');
    }
}
