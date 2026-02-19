<?php
/**
 * Archivo de conexión dinámico (Local / Producción)
 */

// 1. Intentamos obtener la URL de Aiven desde las variables de entorno de Render
$databaseUrl = getenv('DATABASE_URL');

try {
    if ($databaseUrl) {
        /**
         * CONFIGURACIÓN PARA LA NUBE (Render + Aiven)
         * PDO puede recibir la "Service URI" directamente.
         * Agregamos sslmode=require porque Aiven lo exige.
         */
        $dsn = "pgsql:" . str_replace('postgres://', '', $databaseUrl);
        
        // Si la URL no trae el parámetro de SSL, lo concatenamos
        if (!str_contains($dsn, 'sslmode')) {
            $dsn .= "?sslmode=require";
        }

        $user = null; // No se necesitan si van en el DSN
        $password = null;

    } else {
        /**
         * CONFIGURACIÓN PARA TU PC (Local - XAMPP/Laragon)
         */
        $host     = "127.0.0.1";
        $port     = "5432";
        $dbname   = "postgres";
        $user     = "postgres";
        $password = "1234";

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    }

    // 2. Creación de la instancia PDO
    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // echo "¡Conectado con éxito, bro!"; 

} catch (PDOException $e) {
    // En producción es vital no mostrar contraseñas. 
    // Usamos un mensaje genérico si algo falla.
    error_log($e->getMessage());
    die("Error crítico de conexión. Revisa los logs del servidor.");
}