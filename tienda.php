<?php
// PARTE 1. NUESTRAS PRIMERAS FUNCIONES
function calcularIVA(float $precio, float $tasa = 0.19): float {
    return round($precio * (1 + $tasa), 2);
}

function formatearPrecio(float $valor): string {
    return '$' . number_format($valor, 0, ',', '.');
}

echo formatearPrecio(calcularIVA(50000)) . "\n";        // IVA del 19 %
echo formatearPrecio(calcularIVA(50000, 0.05)) . "\n";  // IVA del 5 %
?>

<?php
// MI TURNO
function saludarCliente(string $nombre = "cliente"): string {
    return "Bienvenido a la tienda, $nombre.";
}

// PRUEBAS CON Y SIN NOMBRE
echo saludarCliente() . "\n";          
echo saludarCliente("Carlos") . "\n";  
?>

<?php
// PARTE 2. PARÁMETROS: TRES FORMAS DE PASAR DATOS

// ARGUMENTOS NOMBRADOS: CLARIDAD AL LLAMAR
function aplicarDescuento(float $precio, float $porcentaje = 0): float {
    return $precio - ($precio * $porcentaje / 100);
}

echo formatearPrecio(aplicarDescuento(precio: 80000, porcentaje: 10)) . "\n";
?>

<?php
// NÚMERO VARIABLE DE ARGUMENTOS
function totalCarrito(float ...$precios): float {
    return array_sum($precios);
}

echo formatearPrecio(totalCarrito(15000, 22000, 8000)) . "\n";
?>

<?php
// POR REFERENCIA: MODIFICAR EL ORIGINAL
function descontarStock(int &$stock, int $cantidad): void {
    $stock -= $cantidad;
}

$stockCuadernos = 10;
descontarStock($stockCuadernos, 3);
echo "Quedan $stockCuadernos cuadernos\n";
?>

<?php
// PARTE 3. PREDEFINIDAS: NO REINVENTES LA RUEDA
$productos = [
    ['nombre' => 'Cuaderno', 'precio' => 8000,  'stock' => 20],
    ['nombre' => 'Lápiz',    'precio' => 1500,  'stock' => 0],
    ['nombre' => 'Mochila',  'precio' => 95000, 'stock' => 4],
];

$nombres = array_column($productos, 'nombre');
echo "Catálogo: " . implode(', ', $nombres) . "\n";
echo "Total de productos: " . count($productos) . "\n";

$disponibles = array_filter($productos, fn($p) => $p['stock'] > 0);
echo "Con stock: " . count($disponibles) . "\n";

$precios = array_column($productos, 'precio');
echo "El más caro cuesta " . formatearPrecio(max($precios)) . "\n";
?>

<?php
// MI TURNO
$nombresMayus = array_map(fn($p) => strtoupper($p['nombre']), $productos);
echo "Nombres en mayúsculas: " . implode(', ', $nombresMayus) . "\n";

// PRECIOS CON IVA INCLUIDO
$preciosConIva = array_map(fn($p) => calcularIVA($p['precio']), $productos);
echo "Precios con IVA: ";
foreach ($preciosConIva as $precio) {
    echo formatearPrecio($precio) . " ";
}
echo "\n";
?>

<?php
// PARTE 4. CUANDO ALGO SALE MAL: EXCEPCIONES

// EXCEPCIÓN PROPIA PARA NUESTRA TIENDA
class ProductoAgotadoException extends Exception {
    public function __construct(string $producto) {
        parent::__construct("No hay unidades suficientes de «$producto».");
    }
}
?>

<?php
// FUNCIONES QUE LANZAN ERRORES
function buscarProducto(array $productos, string $nombre): array {
    foreach ($productos as $p) {
        if ($p['nombre'] === $nombre) {
            return $p;
        }
    }
    throw new InvalidArgumentException("El producto «$nombre» no existe.");
}

function comprar(array $productos, string $nombre, int $cantidad): float {
    if ($cantidad <= 0) {
        throw new RangeException("La cantidad debe ser mayor que cero.");
    }
    $producto = buscarProducto($productos, $nombre);
    if ($producto['stock'] < $cantidad) {
        throw new ProductoAgotadoException($nombre);
    }
    return calcularIVA($producto['precio'] * $cantidad);
}
?>

<?php
// ATRAPÁNDOLAS
try {
    echo formatearPrecio(comprar($productos, 'Lápiz', 1)) . "\n";
} catch (ProductoAgotadoException $e) {
    echo "Aviso: " . $e->getMessage() . "\n";
} catch (InvalidArgumentException | RangeException $e) {
    echo "Pedido inválido: " . $e->getMessage() . "\n";
} finally {
    echo "Consulta finalizada.\n";
}
?>

<?php
// MI TURNO: PROMEDIO DE PRECIOS CON EXCEPCIÓN
function promedioPrecios(float ...$precios): float {
    if (empty($precios)) {
        throw new InvalidArgumentException("No se ingresó ningún precio para calcular el promedio.");
    }
    return array_sum($precios) / count($precios);
}

// PROBAR EL TRY / CATCH
try {
    echo "Promedio: " . formatearPrecio(promedioPrecios(15000, 22000, 8000, 30000, 12000)) . "\n";
} catch (InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

<?php
// PARTE 5. TODO JUNTO
$pedidos = [
    ['Cuaderno', 2],
    ['Lápiz',    1],
    ['Borrador', 3],
    ['Mochila',  0],
    ['Mochila',  1],
];

$ventaTotal = 0;

foreach ($pedidos as [$nombre, $cantidad]) {
    try {
        $valor = comprar($productos, $nombre, $cantidad);
        $ventaTotal += $valor;
        echo "OK  - $cantidad x $nombre: " . formatearPrecio($valor) . "\n";
    } catch (ProductoAgotadoException $e) {
        echo "SIN STOCK - " . $e->getMessage() . "\n";
    } catch (InvalidArgumentException | RangeException $e) {
        echo "ERROR - " . $e->getMessage() . "\n";
    }
}

echo "\nTotal vendido: " . formatearPrecio($ventaTotal) . "\n";
?>