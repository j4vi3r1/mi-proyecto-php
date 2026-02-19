<?php
session_start();
include_once __DIR__ . '/conexion.php';

// 1. SEGURIDAD REFORZADA: Solo el Administrador (rol 2) puede cambiar roles
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 2) {
    header("Location: ../public/index.php?err=no_autorizado");
    exit();
}

$rut    = $_GET['rut'] ?? null;
$accion = $_GET['accion'] ?? null;
$miRut  = $_SESSION['usuario_rut'] ?? '';

// Evitar que el admin se modifique a sí mismo
if ($rut === $miRut) {
    header("Location: ../pages/usuarios.php?err=auto_modificacion");
    exit();
}

if (!$rut || !$accion) {
    header("Location: ../pages/usuarios.php");
    exit();
}

try {
    $conn->beginTransaction();
    $vistaDestino = 'empleados'; // Por defecto mandamos a la vista de staff

    if ($accion === 'promover') {
        // CLIENTE -> EMPLEADO (Rol 1)
        $stmt = $conn->prepare("UPDATE Persona SET rol = 1 WHERE rut = :rut AND rol = 0");
        $stmt->execute([':rut' => $rut]);

    } elseif ($accion === 'ascender_admin') {
        // EMPLEADO -> ADMINISTRADOR (Rol 2)
        $stmt = $conn->prepare("UPDATE Persona SET rol = 2 WHERE rut = :rut AND rol = 1");
        $stmt->execute([':rut' => $rut]);

    } elseif ($accion === 'degradar') {
        // Lógica de bajada escalonada
        // Si es Admin (2) baja a Empleado (1). Si es Empleado (1) baja a Cliente (0).
        $stmt = $conn->prepare("UPDATE Persona SET rol = CASE 
                                    WHEN rol = 2 THEN 1 
                                    WHEN rol = 1 THEN 0 
                                    ELSE 0 END 
                                WHERE rut = :rut");
        $stmt->execute([':rut' => $rut]);
        
        // Si bajó a 0 (Cliente), cambiamos la vista de redirección
        $check = $conn->prepare("SELECT rol FROM Persona WHERE rut = :rut");
        $check->execute([':rut' => $rut]);
        $nuevoRol = $check->fetchColumn();
        if ($nuevoRol == 0) $vistaDestino = 'clientes';

    } else {
        throw new Exception("Acción no válida.");
    }

    $conn->commit();
    header("Location: ../pages/usuarios.php?vista=$vistaDestino&res=success");
    exit();

} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    error_log("Error en gestionar_rol: " . $e->getMessage());
    header("Location: ../pages/usuarios.php?err=error_db");
    exit();
}