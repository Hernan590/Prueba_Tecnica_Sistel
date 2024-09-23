const server = "../../controllers/gestion_controller.php" // GUARDAR DIRECCION DEL CONTROLADOR EN UNA CONST

var app = angular.module('myApp', []);

app.controller('gestionctrl', function($scope, $http) {
    $(document).ready(function() {
        var d = new Date();
        var n = d.getFullYear();
        var m = d.getMonth()+1;

        $scope.month = String(m);
        $scope.year = String(n);
        $scope.inventario_productos = true
        $scope.lista_ventas = false
        $scope.camposCompletos = false;
        $scope.productos = [];
        $scope.productos_venta = []

        $scope.getDatos();
    })

    // METODO PARA LLAMAR TODA LA DATA DE PRODUCTOS
    $scope.getDatos = function () { 
        $http.get(server+"?metodo=configuracion_inicial").then(function (res) {
            $scope.get_all = res.data.getDatos;
            $scope.ver_productos = res.data.tipos_productos;
        })
        .catch(function (error) {
            console.log(error)
        });
    };

    // METODO PARA LLAMAR LAS VENTAS
    $scope.getVentas = function () { 
        $http.get(server+"?metodo=lista_ventas").then(function (res) {
            $scope.get_ventas = res.data.getVentas;
        })
        .catch(function (error) {
            console.log(error)
        });
    };

    // METODO PARA VER LA TABLA DE VENTAS
    $scope.tabla_ventas = function () {
        $scope.inventario_productos = false
        $scope.lista_ventas = true
        $scope.getVentas();
    }

    // METODO PARA VER TABLA DE PRODUCTOS
    $scope.tabla_productos = function () {
        $scope.inventario_productos = true
        $scope.lista_ventas = false
    }

    // METODO PARA AGREGAR PRODUCTO EN CREAR
    $scope.agregarProducto = function() {
        $scope.productos.push({
            codigo_producto: '',
            nombre_producto: '',
            precio: '',
            cantidad: ''
        });
    };

    // METODO PARA ELIMINAR PRODUCTO EN CREAR
    $scope.eliminarProducto = function(index) {
        $scope.productos.splice(index, 1);
    };

    // METODO PARA CREAR PRODUCTO
    $scope.crearProductos = function () {

        if ($scope.productos.length === 0) {
            Swal.fire("Error!", "Debe agregar al menos un producto.", "error");
            return;
        }
    
        // VALIDAR CADA PRODUCTO DE LA LISTA
        for (var i = 0; i < $scope.productos.length; i++) {
            var producto = $scope.productos[i];
            if (!producto.codigo_producto) {
                Swal.fire("Error!", "El código del producto " + (i + 1) + " está vacío.", "error");
                return;
            } else if (!producto.nombre_producto) {
                Swal.fire("Error!", "El nombre del producto " + (i + 1) + " está vacío.", "error");
                return;
            } else if (!producto.precio) {
                Swal.fire("Error!", "El precio del producto " + (i + 1) + " está vacío.", "error");
                return;
            } else if (!producto.cantidad) {
                Swal.fire("Error!", "La cantidad del producto " + (i + 1) + " está vacía.", "error");
                return;
            }
        }
    
        var datos = {
            origen: 1,  
            productos: $scope.productos  // ARRAY DE PRODUCTOS
        };
    
        Swal.fire({
            title: '¿Está seguro?',
            text: "Revisa bien la información antes de crear los productos.",
            icon: "question",
            showCancelButton: true,
            cancelButtonColor: '#F44336',
            cancelButtonText: 'No, Cancelar!',
            confirmButtonColor: '#4CAF50',
            confirmButtonText: "Sí, Crear!",
        }).then((result) => {
            if (result.value) {
                $http.post(server, datos)
                    .then(function (response) {
                        if (response.data.status) {
                            Swal.fire("Productos Creados!", response.data.message, "success");
                            $scope.getDatos();  // Actualizar la lista de productos
                            $('#crear_productos').modal('hide');
                            $scope.productos = [];  // Limpiar el formulario
                        } else {
                            $('#crear_productos').modal('hide');
                            Swal.fire("Error al crear!", response.data.message, "error");
                        }
                    })
                    .catch(function () {
                        $('#crear_productos').modal('hide');
                        Swal.fire("Error al crear!", "Ocurrió un error al crear los productos", "error");
                    });
            } else {
                Swal.fire('Cancelado', 'La acción se canceló', 'info');
            }
        });
    };

    // METODO PARA AGREGAR PRODUCTO EN VENTA
    $scope.agregarProductoVenta = function() {
        $scope.productos_venta.push({
            tipo_producto: '',
            cantidad: '',
            total_iva: '',
            total_sin_iva: ''
        });
    };

    // METODO PARA ELIMINAR PRODUCTO EN VENTA
    $scope.eliminarProductoVenta = function(index) {
        $scope.productos_venta.splice(index, 1);
    };

    // METODO PARA VERIFICAR LA ENTRADA DE DATOS
    $scope.verificarCampos = function() {
        if ($scope.cliente_tipo === 'empresa') {
            if ($scope.nombre_empresa && $scope.nit && $scope.razonSocial) {
                $scope.camposCompletos = true;
            } else {
                $scope.camposCompletos = false;
            }
        } else if ($scope.cliente_tipo === 'natural') {
            if ($scope.nombre_cliente) {
                $scope.camposCompletos = true;
            } else {
                $scope.camposCompletos = false;
            }
        }
    };
    
    //METODO PARA CREAR VENTA
    $scope.crearVentas = function() {
        if ($scope.productos_venta.length === 0) {
            Swal.fire("Error!", "Debe agregar al menos un producto.", "error");
            return;
        }
    
        for (var i = 0; i < $scope.productos_venta.length; i++) {
            var producto = $scope.productos_venta[i];
            if (!producto.tipo_producto) {
                Swal.fire("Error!", "No selecionaste un producto en el campo " + (i + 1), "error");
                return;
            } else if (!producto.cantidad) {
                Swal.fire("Error!", "No indicaste una cantidad en el campo " + (i + 1), "error");
                return;
            }
        }
    
        var datos = {
            origen: 2,
            cliente_tipo: $scope.cliente_tipo,
            nombre_cliente: $scope.nombre_cliente,
            nombre_empresa: $scope.nombre_empresa,
            razonSocial: $scope.razonSocial, 
            nit: $scope.nit,
            productos_ven: $scope.productos_venta,
        };

        console.log("data enviada:", datos)
    
        Swal.fire({
            title: '¿Está seguro?',
            text: "Revisa bien la información antes de crear la venta.",
            icon: "question",
            showCancelButton: true,
            cancelButtonColor: '#F44336',
            cancelButtonText: 'No, Cancelar!',
            confirmButtonColor: '#4CAF50',
            confirmButtonText: "Sí, Crear!",
        }).then((result) => {
            if (result.value) {
                $http.post(server, datos)
                    .then(function (response) {
                        if (response.data.status) {
                            Swal.fire("Venta Creada!", response.data.message, "success");
                            $scope.getDatos(); 
                            $('#crear_ventas').modal('hide');
                            $scope.productos_venta = [];  
                            $scope.camposCompletos = false;
                            $scope.cliente_tipo = "",
                            $scope.nombre_cliente = "",
                            $scope.nombre_empresa = "",
                            $scope.razonSocial = ""
                        } else {
                            $('#crear_ventas').modal('hide');
                            Swal.fire("Error al crear venta!", response.data.message, "error");
                        }
                    })
                    .catch(function () {
                        $('#crear_ventas').modal('hide');
                        Swal.fire("Error al crear!", "Ocurrió un error al crear las ventas", "error");
                    });
            } else {
                Swal.fire('Cancelado', 'La acción se canceló', 'info');
            }
        });
    }

    // METODO PARA CALCULAR TOTAL DE VENTA MAS IVA
    $scope.calcularTotalIva = function(producto) {
        if (producto.tipo_producto && producto.cantidad) {
            var opcionSelecionada = document.querySelector(`option[value="${producto.tipo_producto}"]`);
            var precio = opcionSelecionada ? parseFloat(opcionSelecionada.innerText.split('- $')[1]) : 0;
            producto.total_sin_iva = precio * producto.cantidad;    
            producto.iva = producto.total_sin_iva * 0.19;
            producto.total_iva = producto.total_sin_iva + producto.iva;
    
        } else {
            producto.total_sin_iva = 0;
            producto.total_iva = 0;
            producto.iva = 0;
        }
    };

    // METODO PARA DESACTIVAR PRODUCTO
    $scope.deleteProducto = function(id) {
        Swal.fire({
            title: "Confirmar acción",
            text: "¿ Estas seguro de que deseas eliminar el este producto ?",
            icon: 'question',
            showCancelButton: true,
            cancelButtonColor: '#F44336',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4CAF50',
            confirmButtonText: "Si, Eliminar!",
        }).then((result) => {
            if (result.value) {
                $http.get(server+"?metodo=desactivarProducto&id=" + id).then(function (res) {
                    Swal.fire('Eliminado', 'El producto fue eliminado con exito', 'success');
                    $scope.getDatos();
                });
            } else {
                Swal.fire("Cancelado", "La accion se cancelo", "error");
            }
        });
    }

    // METODO PARA DESACTIVAR VENTA
    $scope.deleteVenta = function (id) {
        Swal.fire({
            title: "Confirmar acción",
            text: "¿ Estas seguro de que deseas eliminar esta venta ?",
            icon: 'question',
            showCancelButton: true,
            cancelButtonColor: '#F44336',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4CAF50',
            confirmButtonText: "Si, Eliminar!",
        }).then((result) => {
            if (result.value) {
                $http.get(server+"?metodo=desactivarVenta&id=" + id).then(function (res) {
                    Swal.fire('Eliminado', 'La venta fue eliminada con exito', 'success');
                    $scope.getVentas();
                });
            } else {
                Swal.fire("Cancelado", "La accion se cancelo", "error");
            }
        });
    }

    //METODO PARA DESCARGAR FACTURA
    $scope.descargarFactura = function(id) {
        window.open('factura.php?id_venta=' +  id);
    } 
})

//FUNCION QUE SOLO PERMITE EL INGRESO DE NUMEROS
function numeros(e) {
    key = e.keyCode || e.which; tecla = String.fromCharCode(key).toLowerCase();
    letras = "0123456789";
    especiales = [8, 37, 39, 46];
    tecla_especial = false
    for (var i in especiales) { if (key == especiales[i]) { tecla_especial = true; break; } }
    if (letras.indexOf(tecla) == -1 && !tecla_especial) return false;
}