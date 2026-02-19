<?php
include_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut_original = $_POST['rut_original']; // El RUT que ya existía
    $nuevo_rut = $_POST['rut_contribuyente']; // El RUT que viene del input (puede ser igual o distinto)
    
    try {
        $conn->beginTransaction();

        // 1. MANEJAR REPRESENTANTE (Lógica UPSERT)
        $stmtRep = $conn->prepare("
            INSERT INTO Representantes (rut_representante, nombre, clave_sii)
            VALUES (?, ?, ?)
            ON CONFLICT (rut_representante) 
            DO UPDATE SET 
                nombre = EXCLUDED.nombre, 
                clave_sii = EXCLUDED.clave_sii
            RETURNING id_representante
        ");
        $stmtRep->execute([
            $_POST['rut_representante'],
            $_POST['nombre_representante'],
            $_POST['clave_sii_representante']
        ]);
        $id_rep_final = $stmtRep->fetchColumn();

        // 2. MANEJAR CONTACTO
        $id_con_actual = $_POST['id_contacto_actual'] ?? null;
        if ($id_con_actual) {
            $stmtCon = $conn->prepare("UPDATE Contacto SET nombre_contacto = ?, telefono = ?, correo = ? WHERE id_contacto = ?");
            $stmtCon->execute([
                $_POST['nombre_contacto'],
                $_POST['telefono_contacto'],
                $_POST['correo_contacto'],
                $id_con_actual
            ]);
            $id_con_final = $id_con_actual;
        } else {
            $stmtCon = $conn->prepare("INSERT INTO Contacto (nombre_contacto, telefono, correo) VALUES (?, ?, ?) RETURNING id_contacto");
            $stmtCon->execute([$_POST['nombre_contacto'], $_POST['telefono_contacto'], $_POST['correo_contacto']]);
            $id_con_final = $stmtCon->fetchColumn();
        }

        // 3. ACTUALIZAR CONTRIBUYENTE (Incluyendo el RUT)
        $stmtCli = $conn->prepare("
            UPDATE Contribuyentes SET 
                rut_contribuyente = ?,  -- ESTO PERMITE CAMBIAR EL RUT
                razon_social = ?, 
                id_estado = ?, 
                id_regimen = ?, 
                id_tipo_empresa = ?, 
                id_iva = ?,
                software = ?,
                inicio_actividades = ?, 
                tasa_ppm = ?, 
                honorario_mensual = ?, 
                remuneracion = ?, 
                facturacion = ?, 
                observaciones = ?,
                id_representante = ?,
                id_contacto = ?
            WHERE rut_contribuyente = ? -- BUSCAMOS POR EL RUT ORIGINAL
        ");

        $stmtCli->execute([
            $nuevo_rut, // El nuevo RUT
            $_POST['razon_social'],
            $_POST['id_estado'],
            $_POST['id_regimen'],
            $_POST['id_tipo_empresa'],
            $_POST['id_iva'],
            $_POST['software'] ?? '',
            !empty($_POST['inicio_actividades']) ? $_POST['inicio_actividades'] : null,
            !empty($_POST['tasa_ppm']) ? (float)$_POST['tasa_ppm'] : 0,
            !empty($_POST['honorario_mensual']) ? (int)$_POST['honorario_mensual'] : 0,
            isset($_POST['remuneracion']) ? 'true' : 'false',
            isset($_POST['facturacion']) ? 'true' : 'false',
            $_POST['observaciones'] ?? '',
            $id_rep_final,
            $id_con_final,
            $rut_original // El RUT viejo para el WHERE
        ]);

        // --- IMPORTANTE: De aquí en adelante usamos $nuevo_rut ---

        // 4. ACTUALIZAR PROPIEDADES (Borramos las del viejo, insertamos con el nuevo)
        $conn->prepare("DELETE FROM PropiedadesContribuyente WHERE rut_contribuyente = ?")->execute([$nuevo_rut]);
        if ($nuevo_rut !== $rut_original) {
             $conn->prepare("DELETE FROM PropiedadesContribuyente WHERE rut_contribuyente = ?")->execute([$rut_original]);
        }
        
        if (!empty($_POST['propiedades'])) {
            $stmtP = $conn->prepare("INSERT INTO PropiedadesContribuyente (rut_contribuyente, id_tipo_domicilio, comuna, rol_propiedad, rut_propietario, monto_arriendo) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($_POST['propiedades'] as $p) {
                $stmtP->execute([$nuevo_rut, $p['id_tipo'], $p['comuna'], $p['rol'] ?? null, $p['rut_prop'] ?? null, (int)($p['monto'] ?? 0)]);
            }
        }

        // 5. ACTUALIZAR CLAVES
        $conn->prepare("DELETE FROM ClavesAcceso WHERE rut_contribuyente = ?")->execute([$nuevo_rut]);
        if ($nuevo_rut !== $rut_original) {
            $conn->prepare("DELETE FROM ClavesAcceso WHERE rut_contribuyente = ?")->execute([$rut_original]);
        }

        if (!empty($_POST['claves'])) {
            $stmtCl = $conn->prepare("INSERT INTO ClavesAcceso (rut_contribuyente, id_tipo_clave, usuario, contrasena, observacion) VALUES (?, ?, ?, ?, ?)");
            foreach ($_POST['claves'] as $cl) {
                if (!empty($cl['clave'])) {
                    $stmtCl->execute([
                        $nuevo_rut, 
                        $cl['id_tipo'], 
                        $cl['usuario'] ?? '', 
                        $cl['clave'],
                        $cl['observacion'] ?? ''
                    ]);
                }
            }
        }

        // 6. ACTUALIZAR SOCIOS
        $conn->prepare("DELETE FROM Socios WHERE rut_contribuyente = ?")->execute([$nuevo_rut]);
        if ($nuevo_rut !== $rut_original) {
            $conn->prepare("DELETE FROM Socios WHERE rut_contribuyente = ?")->execute([$rut_original]);
        }

        if (!empty($_POST['socios'])) {
            $stmtS = $conn->prepare("INSERT INTO Socios (rut_contribuyente, rut_socio, nombre_socio, porcentaje_participacion, cantidad_acciones, es_representante) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($_POST['socios'] as $s) {
                if (!empty($s['rut'])) {
                    $es_rep_socio = (isset($s['es_rep']) && ($s['es_rep'] === '1' || $s['es_rep'] === 'true')) ? 'true' : 'false';
                    $stmtS->execute([$nuevo_rut, $s['rut'], $s['nombre'], (float)($s['porcentaje'] ?? 0), (int)($s['acciones'] ?? 0), $es_rep_socio]);
                }
            }
        }

        $conn->commit();
        // Redireccionamos al perfil con el NUEVO RUT
        header("Location: ../pages/perfil_cliente.php?rut=" . urlencode($nuevo_rut) . "&status=updated");
        exit();
        
    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        die("Error crítico: " . $e->getMessage());
    }
}