<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Obtenemos la URL de Supabase desde las variables de entorno de Render
$db_url = getenv('DATABASE_URL');

if ($db_url) {
    // Extraemos los componentes de la URL
    $db_parts = parse_url($db_url);
    
    $host = $db_parts['host'];
    $port = $db_parts['port'];
    $user = $db_parts['user'];
    $pass = $db_parts['pass'];
    $dbname = ltrim($db_parts['path'], '/');

    try {
        // Conexión PDO para PostgreSQL
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        **$conn** = new PDO($dsn, $user, $pass);
        
        // Configuración de errores
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
} else {
    die("Error: No se encontró la variable DATABASE_URL en Render.");
}
?>


