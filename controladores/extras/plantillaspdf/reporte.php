<?php
$medidaTicket = 300;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Your custom styles or other head elements here -->
    <link rel="stylesheet" href="../../dist/css/adminlte.min.css?v=3.2.0">
</head>

<body>
    <?php
    foreach ($json as $key => $value) :
        $nombre = $value["nombre"];
        $apellidos = $value["apellidos"];
        $foto =  $value["foto"];
        $telefono = $value["telefono"];
        $correo = $value["correo"];
        $usuario = $value["usuario"];
        $imagenBase64 = "data:image/png;base64," . base64_encode(file_get_contents($foto));

    ?>
        <div class="col-12 col-sm-6 col-md-4 d-flex align-items-stretch flex-column">
            <div class="card bg-light d-flex flex-fill">
                <div class="card-header text-muted border-bottom-0"></div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-7">
                            <div class="col-5 text-center">
                                <img src="<?php echo  $imagenBase64 ?>" style="width: 80px;" alt="user-avatar" class="img-circle img-fluid">
                            </div>
                            <h2 class="lead"><b><?php echo $nombre . " " . $apellidos ?></b></h2>
                            <ul class="ml-4 mb-0 fa-ul text-muted">
                                <li class="small"><span class="fa-li"><i class="fas fa-lg fa-phone"></i></span> <?php echo $telefono ?></li>
                                <li class="small"><span class="fa-li"><i class="fas fa-lg fa-phone"></i></span> <?php echo $correo ?></li>
                                <li class="small"><span class="fa-li"><i class="fas fa-lg fa-phone"></i></span> <?php echo $usuario ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-footer"></div>
            </div>
        </div>
    <?php endforeach; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>