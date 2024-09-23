<?php
date_default_timezone_set('America/Bogota');
header('Access-Control-Allow-Origin: *');

include("../config/connection.php");
include("../models/gestion_modelo.php");

//VERIFICAR EL TIPO DEL METODO
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {

    //SOLICITUDES TIPO GET
    $metodo = $_GET['metodo'];

    if (isset($metodo)) {
        switch ($metodo) {
            case "configuracion_inicial":
                $response = [
                    'getDatos' => getData($conn),
                    'tipos_productos' => getTipoProductos($conn),
                ];
                mysqli_close($conn);
                echo json_encode($response);
            break;

            case "lista_ventas":
                $response = [
                    'getVentas' => listaventas($conn)
                ];
                mysqli_close($conn);
                echo json_encode($response);
            break;

            case "desactivarProducto":
                $id = $_GET['id'];

                $response = desactivarProducto($conn, $id);

                mysqli_close($conn);
                echo json_encode($response);
            break;

            case "desactivarVenta":
                $id = $_GET['id'];

                $response = desactivarVenta($conn, $id);

                mysqli_close($conn);
                echo json_encode($response);
            break;

            default:
                $response = ['status' => false, 'message' => 'Método no válido.'];
                echo json_encode($response);
            break;
        }
    } else {
        $response = ['status' => false, 'message' => 'No se recibió el método.'];
        echo json_encode($response);
    }

} elseif ($method === 'POST') {
    // LEER CONTENIDO JSON SOLICITUD POST
    $datos = json_decode(file_get_contents("php://input"), true);

    // VERIFICAR SI $DATOS TIENE LA CLAVE 'origen'
    if (isset($datos["origen"])) {     
        $origen = $datos["origen"];

        // SOLICITUDES TIPO POST
        switch ($origen) {
            case 1:

                // VERIFICAR QUE SE RECIBA EL ARRAY PRODUCTOS
                if (isset($datos['productos']) && is_array($datos['productos'])) {
                    $contador = 0; //CONTADOR DE INGRESOS DE PRODUCTOS
                    $res = true; 
                    foreach ($datos['productos'] as $producto) {
                        $data = [
                            'codigoProducto' => $producto['codigo_producto'],
                            'nombreProducto' => $producto['nombre_producto'],
                            'precio' => $producto['precio'],
                            'cantidad' => $producto['cantidad']
                        ];

                        $res_producto = crearProducto($conn, $data);

                        if (!$res_producto['status']) {
                            $res = false; // Si un producto falla, cambiar a false
                        } else {
                            $contador++;
                        }
                    }

                    $response = [
                        "status" => $res,
                        "message" => $res ? "Se crearon $contador productos correctamente." : "Ocurrió un error al crear algunos productos.",
                    ];
                } else {
                    $response = [
                                'status' => false, 
                                'message' => 'No se recibieron productos válidos.'
                                ];
                }

                mysqli_close($conn);
                echo json_encode($response);
            break;
            case 2:
                if (isset($datos['productos_ven']) && is_array($datos['productos_ven'])) {
                    $contador = 0;
                    $res = true;
            
                    $data = [
                        'cliente_tipo' => $datos['cliente_tipo'],
                        'nombre_cliente' => !empty($datos['nombre_cliente']) ? $datos['nombre_cliente'] : null,
                        'nombre_empresa' => !empty($datos['nombre_empresa']) ? $datos['nombre_empresa'] : null,
                        'razonSocial' => !empty($datos['razonSocial']) ? $datos['razonSocial'] : null,
                        'nit' => !empty($datos['nit']) ? $datos['nit'] : null,
                        'total_sin_iva' => array_sum(array_column($datos['productos_ven'], 'total_sin_iva')),
                        'total_iva' => array_sum(array_column($datos['productos_ven'], 'total_iva')),
                    ];
            
                    $venta = crearVenta($conn, $data);
                    if ($venta['status']) {
                        $id_venta = $venta['id_venta'];
            
                        foreach ($datos['productos_ven'] as $producto_venta) {
                            $producto = [
                                'id_producto' => $producto_venta['tipo_producto'], 
                                'cantidad_vendida' => $producto_venta['cantidad'], 
                                'subtotal' => $producto_venta['total_sin_iva'], 
                                'total' => $producto_venta['total_iva'] 
                            ];
            
                            $res_producto = crearDetalleVenta($conn, $id_venta, $producto);
            
                            if ($res_producto['status']) {
                                $res_stock = actualizarStockProducto($conn, $producto['id_producto'], $producto['cantidad_vendida']);
                                
                                if (!$res_stock['status']) {
                                    $res = false;
                                    $response['message'] = "Error al actualizar stock: " . $res_stock['message'];
                                    break; // ROMPE BUCLE EN CASO DE ERROR
                                } else {
                                    $contador++;
                                }
                            } else {
                                $res = false;
                            }
                        }
            
                        $response = [
                            "status" => $res,
                            "message" => $res ? "Se crearon $contador productos correctamente y se actualizó el stock." : "Ocurrió un error al crear algún producto o actualizar el stock.",
                        ];
                    } else {
                        $response = $venta; 
                    }
                } else {
                    $response = [
                        'status' => false,
                        'message' => 'No se recibieron productos de venta válidos'
                    ];
                }
            
                // Cerrar conexión y enviar la respuesta
                mysqli_close($conn);
                echo json_encode($response);
                
            break;

            default:
                $response = ['status' => false, 'message' => 'Origen no válido.'];
                echo json_encode($response);
            break;
        }
    } else {
        $response = ['status' => false, 'message' => 'No se recibió el origen.'];
        echo json_encode($response);
    }
} else {
    $response = ['status' => false, 'message' => 'Método HTTP no permitido.'];
    echo json_encode($response);
}
?>
