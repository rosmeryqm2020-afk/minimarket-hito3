<?php
$api = 'http://localhost:8080/productos';
$mensaje = '';
$error = '';

function solicitar($metodo, $url, $datos = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($datos !== null) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
    }

    $respuesta = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [$codigo, $respuesta, $error];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);
        [$codigo, $respuesta, $error] =
            solicitar('DELETE', "$api/$id");
    } else {
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'proveedor' => trim($_POST['proveedor'] ?? ''),
            'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? '',
            'precio' => (float) ($_POST['precio'] ?? 0),
            'stock' => (int) ($_POST['stock'] ?? 0)
        ];

        if ($accion === 'editar') {
            $id = (int) ($_POST['id'] ?? 0);
            [$codigo, $respuesta, $error] =
                solicitar('PUT', "$api/$id", $datos);
        } else {
            [$codigo, $respuesta, $error] =
                solicitar('POST', $api, $datos);
        }
    }

    if ($codigo >= 200 && $codigo < 300) {
        header('Location: index.php?ok=1');
        exit;
    }

    $mensaje = $error ?: "Error de la API: HTTP $codigo";
}

[$codigo, $respuesta, $error] = solicitar('GET', $api);

$productos = $codigo === 200
    ? json_decode($respuesta, true)
    : [];

if (!is_array($productos)) {
    $productos = [];
}

if ($codigo !== 200) {
    $mensaje = $error ?: "No se pudo conectar con Quarkus.";
}

function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Minimarket - Productos</title>
<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f1f5f9;
    color: #172033;
}
header {
    background: #123d63;
    color: white;
    padding: 24px;
    text-align: center;
}
main {
    max-width: 1100px;
    margin: 25px auto;
    padding: 0 15px;
}
section {
    background: white;
    border-radius: 12px;
    padding: 22px;
    margin-bottom: 20px;
    box-shadow: 0 3px 15px #00000012;
}
h2 { color: #123d63; }
.formulario {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
}
input {
    width: 100%;
    padding: 11px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
}
label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
button {
    padding: 10px 15px;
    border: 0;
    border-radius: 7px;
    cursor: pointer;
    color: white;
    background: #1671b9;
}
button:hover { opacity: .85; }
.eliminar { background: #dc3545; }
.editar { background: #e0a100; }
.guardar { background: #16834b; margin-top: 15px; }
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}
th { background: #eaf1f8; }
.tabla { overflow-x: auto; }
.aviso { padding: 12px; background: #fff0d4; }
.acciones { display: flex; gap: 6px; }
</style>
</head>
<body>

<header>
    <h1>🛒 SISTEMA MINIMARKET</h1>
    <p>Gestión de productos - Programación 3</p>
</header>

<main>
<?php if (isset($_GET['ok'])): ?>
<section style="color:green">
    Operación realizada correctamente.
</section>
<?php endif; ?>

<?php if ($mensaje): ?>
<div class="aviso"><?= e($mensaje) ?></div>
<?php endif; ?>

<section>
    <h2 id="titulo">Registrar producto</h2>

    <form method="POST" id="formulario">
        <input type="hidden" name="accion" id="accion" value="crear">
        <input type="hidden" name="id" id="id">

        <div class="formulario">
            <div>
                <label>Nombre</label>
                <input name="nombre" id="nombre" maxlength="100" required>
            </div>
            <div>
                <label>Proveedor</label>
                <input name="proveedor" id="proveedor" maxlength="100" required>
            </div>
            <div>
                <label>Fecha de vencimiento</label>
                <input type="date" name="fecha_vencimiento"
                       id="fecha_vencimiento" required>
            </div>
            <div>
                <label>Precio (Bs)</label>
                <input type="number" step="0.01" min="0"
                       name="precio" id="precio" required>
            </div>
            <div>
                <label>Stock</label>
                <input type="number" min="0"
                       name="stock" id="stock" required>
            </div>
        </div>

        <button class="guardar" type="submit" id="boton">
            Guardar producto
        </button>
        <button type="button" onclick="cancelar()">
            Limpiar
        </button>
    </form>
</section>

<section>
    <h2>Lista de productos activos</h2>
    <div class="tabla">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Proveedor</th>
            <th>Vencimiento</th>
            <th>Precio (Bs)</th>
            <th>Stock</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($productos as $p): ?>
        <tr>
            <td><?= e($p['id']) ?></td>
            <td><?= e($p['nombre']) ?></td>
            <td><?= e($p['proveedor']) ?></td>
            <td><?= e($p['fecha_vencimiento']) ?></td>
            <td><?= number_format((float)$p['precio'], 2) ?></td>
            <td><?= e($p['stock']) ?></td>
            <td>
                <div class="acciones">
                    <button class="editar" type="button"
                      onclick='editar(<?= e(json_encode(
                          $p,
                          JSON_HEX_TAG | JSON_HEX_APOS |
                          JSON_HEX_AMP | JSON_HEX_QUOT
                      )) ?>)'>
                        Editar
                    </button>

                    <form method="POST"
                          onsubmit="return confirm('¿Eliminar este producto?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                        <button class="eliminar" type="submit">
                            Eliminar
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if (count($productos) === 0): ?>
        <tr><td colspan="7">No hay productos activos.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</section>
</main>

<script>
function editar(p) {
    document.getElementById('accion').value = 'editar';
    document.getElementById('id').value = p.id;
    document.getElementById('nombre').value = p.nombre;
    document.getElementById('proveedor').value = p.proveedor;
    document.getElementById('fecha_vencimiento').value =
        p.fecha_vencimiento;
    document.getElementById('precio').value = p.precio;
    document.getElementById('stock').value = p.stock;
    document.getElementById('titulo').textContent = 'Editar producto';
    document.getElementById('boton').textContent = 'Actualizar producto';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelar() {
    document.getElementById('formulario').reset();
    document.getElementById('accion').value = 'crear';
    document.getElementById('id').value = '';
    document.getElementById('titulo').textContent = 'Registrar producto';
    document.getElementById('boton').textContent = 'Guardar producto';
}
</script>

</body>
</html>