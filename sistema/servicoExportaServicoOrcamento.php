<?php

include_once("sessao.php");

include('global_assets/php/conexao.php');

$_SESSION['msg'] = array();

if (isset($_POST['inputServicoId'])) {
    $sql = "SELECT ServiNome, ServiDetalhamento, ServiCategoria, ServiSubCategoria, ServiStatus
            FROM Servico
            WHERE ServiId = " . $_POST['inputServicoId'] . " and ServiEmpresa = " . $_SESSION['EmpreId'];
    $result = $conn->query($sql);
    $servico = $result->fetch(PDO::FETCH_ASSOC);

    if ($servico) {
        try {
            $sql = "INSERT INTO ServicoOrcamento (SrOrcNome, SrOrcServico, SrOrcDetalhamento, SrOrcCategoria, SrOrcSubCategoria, SrOrcSituacao, 
                SrOrcUsuarioAtualizador, SrOrcEmpresa) 
				VALUES (:sNome, :iServico, :sDetalhamento, :iCategoria, :iSubCategoria, :iSituacao, :iUsuarioAtualizador, :iEmpresa)";
            $result = $conn->prepare($sql);

            $result->execute(array(
                ':sNome' => $servico['ServiNome'],
                ':iServico' => $_POST['inputServicoId'],
                ':sDetalhamento' => $servico['ServiDetalhamento'],
                ':iCategoria' => $servico['ServiCategoria'],
                ':iSubCategoria' => $servico['ServiSubCategoria'],
                ':iSituacao' => $servico['ServiStatus'],
                ':iUsuarioAtualizador' => $_SESSION['UsuarId'],
                ':iEmpresa' => $_SESSION['EmpreId']
            ));

            $_SESSION['msg']['titulo'] = "Sucesso";
            $_SESSION['msg']['mensagem'] = "Serviço exportado!!!";
            $_SESSION['msg']['tipo'] = "success";
        } catch (PDOException $e) {

            $_SESSION['msg']['titulo'] = "Erro";
            $_SESSION['msg']['mensagem'] = "Erro ao exportar produto!!!";
            $_SESSION['msg']['tipo'] = "error";

            echo 'Error2: ' . $e->getMessage();
            die;
        }

        irpara("servico.php");
    }
}
