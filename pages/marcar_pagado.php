<?php
// Evitamos cualquier error de headers
ob_start();

if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../app/conexion.php'; 

// 1. Seguridad de Rol
$rol_usuario = $_SESSION['rol'] ?? 0; 

// 2. Capturar periodo para la redirección (Contexto)
$mes = (int)($_POST['mes'] ?? $_GET['mes'] ?? date('n'));
$anio = (int)($_POST['anio'] ?? $_GET['anio'] ?? date('Y'));

// 3. Procesar Acción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pago'])) {
    $id = (int)$_POST['id_pago'];
    $accion = $_POST['accion'] ?? 'pagar';

    try {
        if ($accion === 'eliminar') {
            // Solo el Admin (Rol 2) puede borrar
            if ($rol_usuario != 2) {
                die("Error: No tienes permisos de Administrador.");
            }
            $stmt = $conn->prepare("DELETE FROM PagosMensuales WHERE id_pago = ?");
        } else {
            // Pagar lo puede hacer Empleado (1) o Admin (2)
            $stmt = $conn->prepare("UPDATE PagosMensuales SET estado = 'Pagado' WHERE id_pago = ?");
        }
        
        $stmt->execute([$id]);
        
        // Redirigimos al periodo exacto donde estábamos
        header("Location: cobranza.php?mes=$mes&anio=$anio");
        exit();

    } catch (Exception $e) {
        die("Error en la base de datos: " . $e->getMessage());
    }
}

// 4. Si intentan entrar directo por URL sin POST, los mandamos de vuelta
header("Location: cobranza.php?mes=$mes&anio=$anio");
ob_end_flush();
exit();
