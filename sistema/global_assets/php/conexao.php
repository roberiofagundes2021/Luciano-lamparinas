<?php

try {
    $conn = new PDO("sqlsrv:server = tcp:lamparinas.database.windows.net,1433; 
									 Database = DBLamparinas", "valmaregia", "cValToChar16");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    print("Erro de conexão com o banco.");
    die(print_r($e));
}
