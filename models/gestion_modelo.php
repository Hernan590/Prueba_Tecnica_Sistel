<?php

function sqlProductos() {
    return "SELECT p.id_producto AS id_producto,
                   p.nombre_producto AS nombre_producto,
                   p.codigo_producto AS codigo_producto,
                   p.precio AS precio,
                   p.cantidad_disponible AS cantidad,
                   p.fecha_creacion AS fecha_creacion,
                   p.estado AS estado
            FROM productos as p
            WHERE p.estado = 1";
}

function getData ($conn) {
    $sql_productos = sqlProductos();
    $stmt = mysqli_prepare($conn, $sql_productos);

    if (!$stmt) {
        die("Error al preparar la consulta de getData: " . mysqli_error($conn));
    }

    mysqli_stmt_execute($stmt);
    $queryRecords = mysqli_stmt_get_result($stmt);

    if (!$queryRecords) {
        die("Error al ejecutar la consulta: " . mysqli_error($conn));
    }

    $getData = [];
    foreach ($queryRecords as $res) {
        $getData[] = [
            'id_producto' => $res['id_producto'],
            'nombre_producto' => $res['nombre_producto'],
            'codigo_producto' => $res['codigo_producto'],
            'precio' => $res['precio'],
            'cantidad_disponible' => $res['cantidad'],
            'fecha_creacion' => $res['fecha_creacion'],
            'estado' => $res['estado']
        ];
    }

    return $getData;
}

function sqlVentas() {
    return "SELECT v.id_venta AS id_venta,
                    v.tipo_cliente AS tipo_cliente,
                    v.nombre_cliente AS nombre_cliente,
                    v.nombre_empresa AS nombre_empresa,
                    v.fecha_venta AS fecha_venta,
                    v.total_sin_iva AS total_sin_iva,
                    v.total_iva AS total_iva,
                    v.estado AS estado
                FROM ventas AS v
                WHERE v.estado = 1
                GROUP BY v.id_venta;";
}

function listaventas($conn) {
    $sql_ventas = sqlVentas();
    $stmt = mysqli_prepare($conn, $sql_ventas);

    if (!$stmt) {
        die("Error al preparar la consulta de listaventas: " . mysqli_error($conn));
    }

    mysqli_stmt_execute($stmt);
    $queryRecords = mysqli_stmt_get_result($stmt);

    if (!$queryRecords) {
        die("Error al ejecutar la consulta: " . mysqli_error($conn));
    }

    $getSells = [];
    foreach ($queryRecords as $res) {
        $getSells[] = [
            'id_venta' => $res['id_venta'],
            'tipo_cliente' => $res['tipo_cliente'],
            'nombre_cliente' => $res['nombre_cliente'],
            'nombre_empresa' => $res['nombre_empresa'],
            'fecha_venta' => $res['fecha_venta'],
            'total_sin_iva' => $res['total_sin_iva'],
            'total_iva' => $res['total_iva'],
            'estado' => $res['estado']
        ];
    }

    return $getSells;
}

function getTipoProductos($conn) {
    $getTipoProductos = "SELECT p.id_producto, 
                                p.nombre_producto,
                                p.cantidad_disponible,
                                p.precio
                        FROM productos AS p 
                        WHERE p.cantidad_disponible > 0
                        ORDER BY p.nombre_producto"; 

    $stmt = mysqli_prepare($conn, $getTipoProductos);

    if (!$stmt) {
        die("Error al preparar la consulta de getTipoProductos: " . mysqli_error($conn));
    }

    mysqli_stmt_execute($stmt);
    $queryRecords = mysqli_stmt_get_result($stmt);

    $tipos_productos = []; 

    if (mysqli_num_rows($queryRecords) > 0) {
        // Obtener todos los resultados como un array
        $resultados = mysqli_fetch_all($queryRecords, MYSQLI_ASSOC);
        
        foreach ($resultados as $res) {
            $tipos_productos[] = [
                'id_producto' => $res['id_producto'],
                'nombre_producto' => $res['nombre_producto'],
                'cantidad_disponible' => $res['cantidad_disponible'],
                'precio' => $res['precio']
            ];
        }
    }

    return $tipos_productos;
}

function crearProducto($conn, $data) {
    extract($data);

    $sqlCrear = "INSERT INTO productos (codigo_producto, 
                                        nombre_producto, 
                                        precio, 
                                        cantidad_disponible, 
                                        estado)
                                    VALUES (?, ?, ?, ?, 1)";

    $stmt = mysqli_prepare($conn, $sqlCrear);

    if (!$stmt) {
        die("Error al preparar la consulta crearProducto: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ssss", $codigoProducto, $nombreProducto, $precio, $cantidad);

    if (mysqli_stmt_execute($stmt)) {
        return [
            "status" => true,
            "message" => "Se agregó correctamente el producto",
        ];
    } else {
        return [
            "status" => false,
            "message" => "Error al agregar el producto: " . mysqli_stmt_error($stmt),
        ];
    }
}

function crearVenta($conn, $data){
    extract($data);

    $sqlVenta = "INSERT INTO ventas (tipo_cliente,
                                    nombre_cliente,
                                    nombre_empresa,
                                    razon_social,
                                    nit,
                                    total_sin_iva,
                                    total_iva,
                                    estado)
                                VALUES (?, ?, ?, ?, ?, ?, ?, 1)";

    $stmt = mysqli_prepare($conn, $sqlVenta);

    if (!$stmt) {
        die("Error al preparar la consulta crearVenta: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "sssssii", $cliente_tipo, $nombre_cliente, $nombre_empresa, $razonSocial, $nit, $total_sin_iva, $total_iva);

    if (mysqli_stmt_execute($stmt)) {
        $id_venta = mysqli_insert_id($conn); // Obtener el ID de la venta creada
        return [
            "status" => true,
            "id_venta" => $id_venta,
            "message" => "Se agregó correctamente la venta",
        ];
    } else {
        return [
            "status" => false,
            "message" => "Error al agregar la venta: " . mysqli_stmt_error($stmt),
        ];
    }
}

function crearDetalleVenta($conn, $id_venta, $producto) {
    $sqlDetalle = "INSERT INTO venta_producto (id_venta, 
                                              id_producto, 
                                              cantidad_vendida, 
                                              subtotal, 
                                              total)
                                    VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sqlDetalle);

    if (!$stmt) {
        die("Error al preparar la consulta crearDetalleVenta: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "iisdd", $id_venta, $producto['id_producto'], $producto['cantidad_vendida'], $producto['subtotal'], $producto['total']);

    if (!mysqli_stmt_execute($stmt)) {
        return [
            "status" => false,
            "message" => "Error al agregar el detalle de venta: " . mysqli_stmt_error($stmt),
        ];
    }

    return [
        "status" => true,
        "message" => "Detalle de venta agregado correctamente",
    ];
}

function actualizarStockProducto($conn, $id_producto, $cantidad_vendida) {
    $sqlSelect = "SELECT cantidad_disponible FROM productos WHERE id_producto = ?";
    $stmtSelect = mysqli_prepare($conn, $sqlSelect);
    mysqli_stmt_bind_param($stmtSelect, "i", $id_producto);
    mysqli_stmt_execute($stmtSelect);
    $result = mysqli_stmt_get_result($stmtSelect);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $cantidad_disponible = $row['cantidad_disponible'];
        
        $nueva_cantidad = $cantidad_disponible - $cantidad_vendida;

        if ($nueva_cantidad < 0) {
            return [
                "status" => false,
                "message" => "No hay suficiente stock para el producto ID: $id_producto."
            ];
        }

        $sqlUpdate = "UPDATE productos SET cantidad_disponible = ? WHERE id_producto = ?";
        $stmtUpdate = mysqli_prepare($conn, $sqlUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "ii", $nueva_cantidad, $id_producto);

        if (mysqli_stmt_execute($stmtUpdate)) {
            return [
                "status" => true,
                "message" => "El stock del producto se actualizó correctamente."
            ];
        } else {
            return [
                "status" => false,
                "message" => "Error al actualizar el stock del producto: " . mysqli_stmt_error($stmtUpdate)
            ];
        }
    } else {
        return [
            "status" => false,
            "message" => "Producto no encontrado con ID: $id_producto."
        ];
    }
}

function desactivarProducto($conn, $id) {
    $sql = "UPDATE productos SET estado = 0 WHERE id_producto = $id";
    mysqli_query($conn, $sql) or die("Error al desactivar"); 
}

function desactivarVenta($conn, $id) {
    $sql = "UPDATE ventas SET estado = 0 WHERE id_venta = $id";
    mysqli_query($conn, $sql) or die("Error al desactivar"); 
}
?>