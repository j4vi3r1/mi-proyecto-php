<?php
session_start();
include_once 'conexion.php';

// 1. SEGURIDAD: Verificar que el usuario tenga permiso
if (!isset($_SESSION['rol']) || $_SESSION['rol'] < 1) {
    header("Location: ../pages/usuarios.php?res=error_permisos");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos del formulario
    $rut = $_POST['rut'] ?? '';
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contacto = trim($_POST['contacto'] ?? '');
    
    // Datos de dirección
    $direccionID = $_POST['direccionid'] ?? null;
    $pais = trim($_POST['pais'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $calle = trim($_POST['calle'] ?? '');
    $numero = trim($_POST['numero'] ?? '');

    try {
        // Iniciar transacción para asegurar integridad de datos
        $conn->beginTransaction();

        // 2. ACTUALIZAR O INSERTAR DIRECCIÓN
        if (!empty($direccionID)) {
            // Si ya existe una dirección asociada, la actualizamos
            $sqlDir = "UPDATE Direccion SET pais = ?, ciudad = ?, calle = ?, numero = ? WHERE direccionID = ?";
            $stmtDir = $conn->prepare($sqlDir);
            $stmtDir->execute([$pais, $ciudad, $calle, $numero, $direccionID]);
        } else {
            // Si el usuario no tenía dirección, la creamos y obtenemos el nuevo ID
            $sqlDir = "INSERT INTO Direccion (pais, ciudad, calle, numero) VALUES (?, ?, ?, ?)";
            $stmtDir = $conn->prepare($sqlDir);
            $stmtDir->execute([$pais, $ciudad, $calle, $numero]);
            $direccionID = $conn->lastInsertId();
        }

        // 3. ACTUALIZAR DATOS DE LA PERSONA
        $sqlPer = "UPDATE Persona SET nombres = ?, apellidos = ?, correo = ?, contacto = ?, direccionID = ? WHERE rut = ?";
        $stmtPer = $conn->prepare($sqlPer);
        $stmtPer->execute([$nombres, $apellidos, $correo, $contacto, $direccionID, $rut]);

        // Si todo salió bien, confirmamos los cambios
        $conn->commit();
        header("Location: ../pages/usuarios.php?res=success");
        
    } catch (Exception $e) {
        // Si algo falla, deshacemos cualquier cambio en la BD
        $conn->rollBack();
        header("Location: ../pages/usuarios.php?res=error&msg=" . urlencode($e->getMessage()));
    }
} else {
    header("Location: ../pages/usuarios.php");
}
exit();
