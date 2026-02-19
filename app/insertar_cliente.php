<?php
session_start();
include_once __DIR__ . '/conexion.php';

// SEGURIDAD
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], [1, 2])) {
    header("Location: ../public/index.php?error=acceso_denegado");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        $rut_contribuyente = trim($_POST['rut_contribuyente']);

        // 1. REGISTRAR CONTACTO
        $id_contacto = null;
        if (!empty($_POST['nombre_contacto'])) {
            $sqlCont = "INSERT INTO Contacto (nombre_contacto, telefono, correo) 
                        VALUES (?, ?, ?) RETURNING id_contacto";
            $stmtCont = $conn->prepare($sqlCont);
            $stmtCont->execute([
                trim($_POST['nombre_contacto']), 
                !empty($_POST['telefono_contacto']) ? $_POST['telefono_contacto'] : null, 
                !empty($_POST['correo_contacto']) ? $_POST['correo_contacto'] : null
            ]);
            $id_contacto = $stmtCont->fetchColumn();
        }

        // 2. REGISTRAR O ACTUALIZAR REPRESENTANTE
        $id_representante = null;
        if (!empty($_POST['rut_representante'])) {
            $sqlRep = "INSERT INTO Representantes (rut_representante, nombre, clave_sii) 
                       VALUES (?, ?, ?) 
                       ON CONFLICT (rut_representante) DO UPDATE 
                       SET nombre = EXCLUDED.nombre, clave_sii = EXCLUDED.clave_sii
                       RETURNING id_representante";
            $stmtRep = $conn->prepare($sqlRep);
            $stmtRep->execute([
                trim($_POST['rut_representante']), 
                trim($_POST['nombre_representante']), 
                !empty($_POST['clave_sii_representante']) ? $_POST['clave_sii_representante'] : null
            ]);
            $id_representante = $stmtRep->fetchColumn();
        }

        // 3. REGISTRAR CONTRIBUYENTE
        $sqlClie = "INSERT INTO Contribuyentes (
            rut_contribuyente, razon_social, id_estado, id_iva, 
            id_regimen, id_tipo_empresa, id_contacto, id_representante, 
            software, inicio_actividades, tasa_ppm, honorario_mensual, 
            honorario_renta, remuneracion, facturacion, observaciones
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtClie = $conn->prepare($sqlClie);
        
        // SOLUCIÓN DEFINITIVA: Usar 1 y 0 (enteros) para que Postgres no se confunda con strings vacíos
        $remuneracion = (isset($_POST['remuneracion']) && ($_POST['remuneracion'] === 'on' || $_POST['remuneracion'] === 'true' || $_POST['remuneracion'] == 1)) ? 1 : 0;
        $facturacion  = (isset($_POST['facturacion']) && ($_POST['facturacion'] === 'on' || $_POST['facturacion'] === 'true' || $_POST['facturacion'] == 1)) ? 1 : 0;

        $stmtClie->execute([
            $rut_contribuyente,
            trim($_POST['razon_social']),
            $_POST['id_estado'],
            !empty($_POST['id_iva']) ? $_POST['id_iva'] : null,
            $_POST['id_regimen'],
            $_POST['id_tipo_empresa'],
            $id_contacto,
            $id_representante,
            !empty($_POST['software']) ? trim($_POST['software']) : null,
            !empty($_POST['inicio_actividades']) ? $_POST['inicio_actividades'] : null,
            !empty($_POST['tasa_ppm']) ? (float)$_POST['tasa_ppm'] : 0,
            !empty($_POST['honorario_mensual']) ? (int)$_POST['honorario_mensual'] : 0,
            !empty($_POST['honorario_renta']) ? (int)$_POST['honorario_renta'] : 0,
            $remuneracion, // Enviará 1 o 0
            $facturacion,  // Enviará 1 o 0
            $_POST['observaciones'] ?? ''
        ]);

        // 4. REGISTRAR PROPIEDADES
        // ... (el código de propiedades se mantiene igual)
        if (isset($_POST['propiedades']) && is_array($_POST['propiedades'])) {
            $sqlProp = "INSERT INTO PropiedadesContribuyente (
                            rut_contribuyente, id_tipo_domicilio, comuna, 
                            rol_propiedad, rut_propietario, monto_arriendo
                        ) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtProp = $conn->prepare($sqlProp);
            foreach ($_POST['propiedades'] as $p) {
                if(!empty($p['id_tipo']) && !empty($p['comuna'])) {
                    $stmtProp->execute([
                        $rut_contribuyente, $p['id_tipo'], trim($p['comuna']), 
                        $p['rol'] ?: null, $p['rut_prop'] ?: null, (int)($p['monto'] ?? 0)
                    ]);
                }
            }
        }

        // 5. REGISTRAR CLAVES
        // ... (el código de claves se mantiene igual)
        if (isset($_POST['claves']) && is_array($_POST['claves'])) {
            $sqlKey = "INSERT INTO ClavesAcceso (rut_contribuyente, id_tipo_clave, usuario, contrasena, observaciones) 
                       VALUES (?, ?, ?, ?, ?)";
            $stmtKey = $conn->prepare($sqlKey);
            foreach ($_POST['claves'] as $c) {
                if (!empty($c['id_tipo']) && !empty($c['clave'])) {
                    $stmtKey->execute([
                        $rut_contribuyente, $c['id_tipo'], $c['usuario'] ?: null, 
                        $c['clave'], $c['observacion'] ?: null 
                    ]);
                }
            }
        }

        // 6. REGISTRAR SOCIOS
        if (isset($_POST['socios']) && is_array($_POST['socios'])) {
            $sqlSoc = "INSERT INTO Socios (rut_contribuyente, rut_socio, nombre_socio, porcentaje_participacion, cantidad_acciones, es_representante) 
                       VALUES (?, ?, ?, ?, ?, ?)";
            $stmtSoc = $conn->prepare($sqlSoc);
            foreach ($_POST['socios'] as $s) {
                if (!empty($s['rut'])) {
                    // Aplicamos la misma lógica de 1 y 0 aquí
                    $es_rep_socio = (isset($s['es_rep']) && ($s['es_rep'] === '1' || $s['es_rep'] === 'on')) ? 1 : 0;

                    $stmtSoc->execute([
                        $rut_contribuyente, trim($s['rut']), trim($s['nombre']), 
                        (float)($s['porcentaje'] ?? 0), (int)($s['acciones'] ?? 0), 
                        $es_rep_socio
                    ]);
                }
            }
        }

        $conn->commit();
        header("Location: ../pages/perfil_cliente.php?rut=" . urlencode($rut_contribuyente) . "&msg=registrado");
        exit();

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        error_log("Error crítico: " . $e->getMessage());
        die("Error al procesar el registro: " . $e->getMessage());
    }
}