<?php
// 1. Unirse a la sesión actual
session_start();

// 2. Eliminar todas las variables de sesión
session_unset();

// 3. Destruir la sesión en el servidor
session_destroy();

// 4. Redirigir al index (como estamos en 'app/', subimos un nivel para llegar a 'public/')
header("Location: ../public/index.php");
exit();
?>