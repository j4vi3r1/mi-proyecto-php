<?php
session_start();
include_once __DIR__ . '/conexion.php';

// 1. Seguridad: Solo empleados autorizados
if (!isset($_SESSION['es_empleado']) || $_SESSION['es_empleado'] !== true) {
    header("Location: ../public/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitización y captura de datos de Persona
    $rut       = $_POST['rut'] ?? ''; 
    $nombres   = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $correo    = trim($_POST['correo'] ?? '');
    $contacto  = trim($_POST['contacto'] ?? '');
    
    // Captura de datos de Dirección
    $dirID  = $_POST['direccionid'] ?? null;
    $pais   = trim($_POST['pais'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $calle  = trim($_POST['calle'] ?? '');
    $numero = $_POST['numero'] ?? null;

    // Validación simple: No permitir campos críticos vacíos
    if (empty($rut) || empty($nombres) || empty($correo)) {
        header("Location: ../pages/usuarios.php?error=campos_obligatorios");
        exit();
    }

    try {
        $conn->beginTransaction();

        // 2. Actualizar Tabla persona (Columnas: nombres, apellidos, correo, contacto)
        $sqlPer = "UPDATE persona 
                   SET nombres = :nom, apellidos = :ape, correo = :mail, contacto = :cont 
                   WHERE rut = :rut";
        $stmtPer = $conn->prepare($sqlPer);
        $stmtPer->execute([
            ':nom'  => $nombres,
            ':ape'  => $apellidos,
            ':mail' => $correo,
            ':cont' => $contacto,
            ':rut'  => $rut
        ]);

        // 3. Actualizar Tabla direccion (Solo si existe un ID de dirección)
        if ($dirID) {
            $sqlDir = "UPDATE direccion 
                       SET pais = :pais, ciudad = :ciu, calle = :calle, numero = :num 
                       WHERE direccionid = :dirid";
            $stmtDir = $conn->prepare($sqlDir);
            $stmtDir->execute([
                ':pais'  => $pais,
                ':ciu'   => $ciudad,
                ':calle' => $calle,
                // Manejo de número: si está vacío, enviamos NULL para evitar errores de tipo entero
                ':num'   => (!empty($numero) && is_numeric($numero)) ? (int)$numero : null,
                ':dirid' => $dirID
            ]);
        }

        $conn->commit();

        // Éxito: Redirigir con parámetro de resultado
        header("Location: ../pages/usuarios.php?res=success");
        exit();

    } catch (Exception $e) {
        // Si algo falla, revertimos todos los cambios para mantener la integridad
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        // En producción, es mejor loguear el error y mostrar un mensaje genérico
        error_log("Error en actualización de usuario: " . $e->getMessage());
        header("Location: ../pages/usuarios.php?res=error");
        exit();
    }
} else {
    // Si intentan entrar por GET, fuera
    header("Location: ../pages/usuarios.php");
    exit();
}