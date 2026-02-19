<?php
// 1. Incluimos la conexión
include_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Capturamos datos de Persona
    $rut       = trim($_POST['rut'] ?? '');
    $nombres   = trim($_POST['nombre'] ?? '');   
    $apellidos = trim($_POST['apellido'] ?? ''); 
    $correo    = trim($_POST['correo'] ?? '');
    $contacto  = trim($_POST['contacto'] ?? '');
    
    // Capturamos datos de Direccion
    $pais     = trim($_POST['pais'] ?? '');
    $ciudad   = trim($_POST['ciudad'] ?? '');
    $calle    = trim($_POST['calle'] ?? '');
    $numero   = $_POST['numero'] ?? null;

    try {
        $conn->beginTransaction();

        // 2. INSERTAR EN TABLA DIRECCION
        $sqlDir = "INSERT INTO Direccion (pais, ciudad, calle, numero) 
                   VALUES (:pais, :ciudad, :calle, :num) 
                   RETURNING direccionID";
        
        $stmtDir = $conn->prepare($sqlDir);
        $stmtDir->execute([
            ':pais'   => $pais,
            ':ciudad' => $ciudad,
            ':calle'  => $calle,
            ':num'    => !empty($numero) ? (int)$numero : null
        ]);

        $nuevoDireccionID = $stmtDir->fetchColumn();

        // 3. INSERTAR EN TABLA PERSONA
        // CAMBIO: El valor de :rol ahora es 0 (Cliente) en lugar de 'false'
        $sqlPer = "INSERT INTO Persona (rut, nombres, apellidos, correo, contacto, direccionID, rol) 
                   VALUES (:rut, :nom, :ape, :mail, :cont, :dirID, :rol)";
        
        $stmtPer = $conn->prepare($sqlPer);
        $stmtPer->execute([
            ':rut'   => $rut,
            ':nom'   => $nombres,   
            ':ape'   => $apellidos, 
            ':mail'  => $correo,
            ':cont'  => $contacto,
            ':dirID' => $nuevoDireccionID,
            ':rol'   => 0  // <--- CAMBIO AQUÍ: 0 para Clientes
        ]);

        $conn->commit();

        echo "<script>
                alert('¡Registro exitoso, bro! Guardado como cliente (Rol 0).');
                window.location.href = '../public/index.php';
              </script>";

    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        echo "Error: " . $e->getMessage();
    }
}