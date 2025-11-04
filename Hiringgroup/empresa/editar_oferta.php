<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== 'empresa') {
    header('Location: /hiring-group/login.html');
    exit();
}

include("../usuarios/conexion.php");
$con = conectar();

$id_oferta = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener datos actuales de la oferta
$oferta = mysqli_fetch_assoc(mysqli_query($con, 
    "SELECT * FROM ofertalaboral 
     WHERE id_oferta = $id_oferta 
     AND fk_empresa = {$_SESSION['usuario']['id_usuario']}"));

if (!$oferta) {
    header('Location: ofertas.php');
    exit();
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cargo = mysqli_real_escape_string($con, $_POST['cargo']);
    $descripcion = mysqli_real_escape_string($con, $_POST['descripcion']);
    $modalidad = mysqli_real_escape_string($con, $_POST['modalidad']);
    $salario = !empty($_POST['salario']) ? mysqli_real_escape_string($con, $_POST['salario']) : NULL;
    $area = mysqli_real_escape_string($con, $_POST['area']);
    
    $sql = "UPDATE ofertalaboral SET
                cargo = '$cargo',
                descripcion_perfil = '$descripcion',
                modalidad = '$modalidad',
                salario = $salario,
                fk_area = $area
            WHERE id_oferta = $id_oferta
            AND fk_empresa = {$_SESSION['usuario']['id_usuario']}";
    
    if (mysqli_query($con, $sql)) {
        header("Location: detalle_oferta.php?id=$id_oferta&exito=editada");
        exit();
    } else {
        $error = "Error al actualizar: " . mysqli_error($con);
    }
}

// Obtener áreas
$areas = mysqli_query($con, "SELECT id_area, nombre_area FROM areaconocimiento ORDER BY nombre_area");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Oferta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include("../partials/navbar_empresa.php"); ?>
    
    <div class="container py-4">
        <h2 class="mb-4">Editar Oferta Laboral</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Cargo</label>
                <input type="text" name="cargo" class="form-control" required
                       value="<?= htmlspecialchars($oferta['cargo']) ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="5" required><?= 
                    htmlspecialchars($oferta['descripcion_perfil']) 
                ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Modalidad</label>
                    <select name="modalidad" class="form-select" required>
                        <option value="Presencial" <?= $oferta['modalidad'] === 'Presencial' ? 'selected' : '' ?>>Presencial</option>
                        <option value="Remoto" <?= $oferta['modalidad'] === 'Remoto' ? 'selected' : '' ?>>Remoto</option>
                        <option value="Híbrido" <?= $oferta['modalidad'] === 'Híbrido' ? 'selected' : '' ?>>Híbrido</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Salario (Opcional)</label>
                    <input type="number" step="0.01" name="salario" class="form-control"
                           value="<?= $oferta['salario'] ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Área</label>
                <select name="area" class="form-select" required>
                    <?php while ($area = mysqli_fetch_assoc($areas)): ?>
                        <option value="<?= $area['id_area'] ?>" 
                            <?= $area['id_area'] == $oferta['fk_area'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($area['nombre_area']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="detalle_oferta.php?id=<?= $id_oferta ?>" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</body>
</html>