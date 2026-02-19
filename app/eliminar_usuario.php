<?php
session_start();
include_once __DIR__ . '/conexion.php';

// 1. Protección: Solo empleados pueden eliminar
if (!isset($_SESSION['es_empleado']) || $_SESSION['es_empleado'] !== true) {
    header("Location: ../public/index.php");
    exit();
}

$rut = $_GET['rut'] ?? null;

if (!$rut) {
    header("Location: ../pages/usuarios.php?res=error_no_rut");
    exit();
}

try {
    $conn->beginTransaction();
    $rut = trim($rut);

    // 2. Obtener el direccionID antes de borrar
    $stmtDir = $conn->prepare("SELECT direccionID FROM Persona WHERE rut = :rut");
    $stmtDir->execute([':rut' => $rut]);
    $direccionID = $stmtDir->fetchColumn();

    // 3. Borrar de la tabla Empleado (si existe)
    // Usamos el nombre de columna correcto: empleadoID
    $stmtEmp = $conn->prepare("DELETE FROM Empleado WHERE empleadoID = :rut");
    $stmtEmp->execute([':rut' => $rut]);

    // 4. Borrar a la Persona
    $stmtPer = $conn->prepare("DELETE FROM Persona WHERE rut = :rut");
    $stmtPer->execute([':rut' => $rut]);

    // 5. Borrar la Dirección
    if ($direccionID) {
        // Verificamos si alguien más usa esa dirección antes de borrarla
        // (Opcional, pero evita errores si compartieran dirección)
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM Persona WHERE direccionID = :dirID");
        $stmtCheck->execute([':dirID' => $direccionID]);
        $enUso = $stmtCheck->fetchColumn();

        if ($enUso == 0) {
            $stmtDelDir = $conn->prepare("DELETE FROM Direccion WHERE direccionID = :dirID");
            $stmtDelDir->execute([':dirID' => $direccionID]);
        }
    }

    $conn->commit();
    header("Location: ../pages/usuarios.php?res=success");
    exit();

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo "<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red; border-radius:10px; background:#fff5f5;'>";
    echo "<strong>No se pudo eliminar al usuario:</strong><br>";
    echo "Esto sucede si el usuario tiene registros asociados en otras tablas (Socios, Representantes, etc.).<br><br>";
    echo "Detalle técnico: " . $e->getMessage();
    echo "<br><br><a href='../pages/usuarios.php' style='color:blue;'>Volver a Gestión de Usuarios</a>";
    echo "</div>";
}