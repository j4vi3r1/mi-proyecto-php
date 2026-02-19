<?php
session_start();
include_once __DIR__ . '/conexion.php';

/**
 * SEGURIDAD REFORZADA:
 * Solo el Administrador (rol 2) puede realizar eliminaciones físicas.
 */
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 2) {
    header("Location: ../public/index.php?error=no_autorizado");
    exit();
}

if (isset($_GET['rut'])) {
    $rut = $_GET['rut'];

    try {
        $conn->beginTransaction();

        // 1. Obtener IDs relacionados antes de borrar al contribuyente
        $stmt = $conn->prepare("SELECT id_contacto, id_representante FROM Contribuyentes WHERE rut_contribuyente = ?");
        $stmt->execute([$rut]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $id_contacto = $data['id_contacto'];
            $id_rep = $data['id_representante'];

            // 2. ELIMINAR CONTRIBUYENTE
            // Nota: Socios, ClavesAcceso y PropiedadesContribuyente se borran por el ON DELETE CASCADE del SQL.
            $stmtDelete = $conn->prepare("DELETE FROM Contribuyentes WHERE rut_contribuyente = ?");
            $stmtDelete->execute([$rut]);

            // 3. Limpieza de Contacto (Relación 1 a 1 casi siempre)
            if ($id_contacto) {
                $stmtC = $conn->prepare("DELETE FROM Contacto WHERE id_contacto = ?");
                $stmtC->execute([$id_contacto]);
            }

            // 4. Limpieza inteligente de Representante
            // Solo borramos al representante si no está vinculado a ningún otro contribuyente en el sistema.
            if ($id_rep) {
                $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM Contribuyentes WHERE id_representante = ?");
                $stmtCheck->execute([$id_rep]);
                
                if ($stmtCheck->fetchColumn() == 0) {
                    $stmtR = $conn->prepare("DELETE FROM Representantes WHERE id_representante = ?");
                    $stmtR->execute([$id_rep]);
                }
            }
        }

        $conn->commit();
        // Redirección al listado con mensaje de éxito
        header("Location: ../pages/clientes.php?res=deleted");
        exit();

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        error_log("Error al eliminar cliente ($rut): " . $e->getMessage());
        die("Error crítico al eliminar: " . $e->getMessage());
    }
} else {
    header("Location: ../pages/clientes.php");
    exit();
}