<?php
declare(strict_types=1);

// EXCEPCIONES
class ProductoAgotadoException extends Exception {
    public function __construct(string $producto) {
        parent::__construct("No hay unidades suficientes de «" . $producto . "».");
    }
}

class PrecioInvalidoException extends Exception {
    public function __construct(string $producto) {
        parent::__construct("El producto «" . $producto . "» tiene un precio inválido.");
    }
}

// FUNCIONES BÁSICAS
function calcularIVA(float $precio, float $tasa = 0.19): float {
    return round($precio * (1 + $tasa), 2);
}

function formatearPrecio(float $valor): string {
    return '$' . number_format($valor, 0, ',', '.');
}

function aplicarDescuento(float $precio, float $porcentaje = 0): float {
    return $precio - ($precio * $porcentaje / 100);
}

// INVENTARIO
$productos = [
    ['nombre' => 'Cuaderno', 'precio' => 8000,  'stock' => 20],
    ['nombre' => 'Lápiz',    'precio' => 1500,  'stock' => 0],
    ['nombre' => 'Mochila',  'precio' => 95000, 'stock' => 4],
];

// VALIDAR PRECIO
foreach ($productos as $p) {
    if ($p['precio'] <= 0) {
        throw new PrecioInvalidoException($p['nombre']);
    }
}

function buscarProducto(array $productos, string $nombre): array {
    foreach ($productos as $p) {
        if ($p['nombre'] === $nombre) {
            return $p;
        }
    }
    throw new InvalidArgumentException("El producto «$nombre» no existe.");
}

// COMPRAR (STOCK POR REFERENCIA Y DESCUENTO POR VOLUMEN)
function comprar(array &$productos, string $nombre, int $cantidad): float {
    if ($cantidad <= 0) {
        throw new RangeException("La cantidad debe ser mayor que cero.");
    }

    $indice = -1;
    foreach ($productos as $i => $p) {
        if ($p['nombre'] === $nombre) {
            $indice = $i;
            break;
        }
    }

    if ($indice === -1) {
        throw new InvalidArgumentException("El producto «$nombre» no existe.");
    }

    if ($productos[$indice]['stock'] < $cantidad) {
        throw new ProductoAgotadoException($nombre);
    }

    // DESCONTAR STOCK REAL
    $productos[$indice]['stock'] -= $cantidad;

    $precioUnitario = $productos[$indice]['precio'];

    // DESCUENTO POR VOLUMEN
    if ($cantidad >= 5) {
        $precioUnitario = aplicarDescuento(precio: $precioUnitario, porcentaje: 10);
    }

    return calcularIVA($precioUnitario * $cantidad);
}

// GENERAR RECIBO
function generarRecibo(array $lineas): string {
    return implode("\n", $lineas);
}

$pedidos = [
    ['Cuaderno', 2],
    ['Cuaderno', 5],
    ['Lápiz', 1],
];

$ventaTotal = 0;
$reciboLineas = [];
$reciboLineas[] = "=== TICKET DE COMPRA ===";
$reciboLineas[] = "Fecha: " . date("Y-m-d H:i:s");
$reciboLineas[] = str_repeat("-", 35);

foreach ($pedidos as [$nombre, $cantidad]) {
    try {
        $valor = comprar($productos, $nombre, $cantidad);
        $ventaTotal += $valor;
        $linea = "OK  - $cantidad x $nombre: " . formatearPrecio($valor);
        echo $linea . "\n";
        $reciboLineas[] = $linea;
    } catch (Exception $e) {
        $msg = "ERROR - " . $e->getMessage();
        echo $msg . "\n";
        $reciboLineas[] = $msg;
    }
}

$reciboLineas[] = str_repeat("-", 35);
$reciboLineas[] = str_pad("TOTAL VENDIDO: " . formatearPrecio($ventaTotal), 35, " ", STR_PAD_LEFT);

echo "\n" . generarRecibo($reciboLineas) . "\n";
?>