<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../app/conexion.php'; 

$rol_usuario = $_SESSION['rol'] ?? 0; 

// Capturamos mes y año desde el POST o GET por si acaso necesitamos redirigir
$mes = (int)($_POST['mes'] ?? $_GET['mes'] ?? date('n'));
$anio = (int)($_POST['anio'] ?? $_GET['anio'] ?? date('Y'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pago'])) {
    $id = (int)$_POST['id_pago'];
    $accion = $_POST['accion'] ?? 'pagar';

    try {
        if ($accion === 'eliminar') {
            if ($rol_usuario != 2) {
                die("Error: No tienes permisos de Administrador.");
            }
            $stmt = $conn->prepare("DELETE FROM PagosMensuales WHERE id_pago = ?");
        } else {
            $stmt = $conn->prepare("UPDATE PagosMensuales SET estado = 'Pagado' WHERE id_pago = ?");
        }
        
        $stmt->execute([$id]);
        // Redirigimos manteniendo el contexto del periodo
        header("Location: cobranza.php?mes=$mes&anio=$anio");
        exit();
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

// Si alguien entra aquí sin POST o algo falla, volvemos con los datos que tengamos
header("Location: cobranza.php?mes=$mes&anio=$anio");
exit();
