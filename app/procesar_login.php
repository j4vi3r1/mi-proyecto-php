<?php
session_start();
include_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $rut    = trim($_POST['rut'] ?? '');

    try {
        // 1. Buscamos a la persona
        $sql = "SELECT rut, nombres, apellidos, correo, rol FROM Persona WHERE correo = :correo AND rut = :rut LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':correo' => $correo,
            ':rut'    => $rut
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // 2. ÉXITO: Guardamos datos en la sesión
            $_SESSION['usuario_rut']  = $usuario['rut']; 
            $_SESSION['nombre']       = $usuario['nombres'] . " " . $usuario['apellidos'];
            $_SESSION['autenticado']  = true;
            
            // IMPORTANTE: Guardamos el rol real para usarlo en otras páginas
            $_SESSION['rol'] = (int)$usuario['rol'];

            /**
             * 3. Lógica de Acceso Staff (Empleado o Admin)
             * Ahora verificamos que el rol sea 1 O 2 (es decir, mayor o igual a 1)
             */
            $esStaff = ($_SESSION['rol'] >= 1);

            if ($esStaff) {
                // Es staff (Empleado o Admin)
                $_SESSION['es_empleado'] = true;
                header("Location: ../pages/funciones.php");
            } else {
                // Es cliente (Rol 0)
                $_SESSION['es_empleado'] = false;
                header("Location: ../pages/catalogo.php");
            }
            exit();

        } else {
            echo "<script>alert('Correo o RUT incorrectos, bro. Intenta de nuevo.'); window.location.href='../pages/login.php';</script>";
        }

    } catch (Exception $e) {
        echo "<div style='color:red; padding:20px; border:1px solid red; font-family: sans-serif;'>";
        echo "<strong>Error de sistema:</strong> " . $e->getMessage();
        echo "<br><br><a href='../pages/login.php'>Volver al login</a>";
        echo "</div>";
    }
}