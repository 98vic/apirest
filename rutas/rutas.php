<?php

$arrayRutas = explode("/", $_SERVER['REQUEST_URI']);

if (count(array_filter($arrayRutas)) == 0) {

	/*=============================================
		Cuando no se hace ninguna petición a la API
		=============================================*/

	$json = array(

		"detalle" => "no encontrado"

	);

	echo json_encode($json, true);

	return;
} else {

	/*=============================================
		Cuando pasamos solo un índice en el array $arrayRutas
		=============================================*/

	if (count(array_filter($arrayRutas)) == 1) {

		/*=============================================
			Cuando se hace peticiones desde registro
			=============================================*/

		if (array_filter($arrayRutas)[1] == "registro") {

			/*=============================================
				Peticiones POST
				=============================================*/

			if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {

				// Verificar si la clave "nombre" existe en $_POST
				$nombre = isset($_POST["nombre"]) ? $_POST["nombre"] : null;
				$apellidos = isset($_POST["apellidos"]) ? $_POST["apellidos"] : null;
				$telefono = isset($_POST["telefono"]) ? $_POST["telefono"] : null;
				$correo = isset($_POST["correo"]) ? $_POST["correo"] : null;
				$foto = isset($_POST["foto"]) ? $_POST["foto"] : null;
				$password = isset($_POST["password"]) ? $_POST["password"] : null;
				$usuario = isset($_POST["usuario"]) ? $_POST["usuario"] : null;

				// Verificar si alguna de las claves no está presente
				if ($nombre === null || $apellidos === null || $telefono === null || $correo === null  || $password === null || $usuario === null) {
					$json = array(
						"detalle" => "Faltan datos en la solicitud"
					);
					echo json_encode($json, true);
					return;
				}

				if (isset($_FILES["foto"])) {
					list($ancho, $alto) = getimagesize($_FILES["foto"]["tmp_name"]);

					$nuevoAncho = 500;
					$nuevoAlto = 500;
					// Obtener la información de la imagen
					$infoImagen = getimagesize($_FILES["foto"]["tmp_name"]);

					if ($infoImagen !== false) {
						// Determinar la extensión de la imagen
						$extension = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);

						$ruta = "img/usuarios/{$nombre}.{$extension}";

						// Crear una imagen redimensionada
						$origen = imagecreatefromstring(file_get_contents($_FILES["foto"]["tmp_name"]));
						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $infoImagen[0], $infoImagen[1]);

						// Guardar la imagen
						switch ($extension) {
							case 'jpg':
							case 'jpeg':
								imagejpeg($destino, $ruta);
								break;

							case 'png':
								imagepng($destino, $ruta);
								break;
						}

						// Liberar memoria
						imagedestroy($origen);
						imagedestroy($destino);
					}
				} else {
					$json = array(
						"detalle" => "Faltan datos en la solicitud"
					);
					echo json_encode($json, true);
					return;
				}

				// Si todas las claves están presentes, puedes continuar con el procesamiento de los datos
				$datos = array(
					"nombre" => $nombre,
					"apellidos" => $apellidos,
					"telefono" => $telefono,
					"correo" => $correo,
					"foto" => $ruta,
					"password" => $password,
					"usuario" => $usuario
				);

				$registro = new ControladorUsuarios();
				$registro->create($datos);
			} else {

				$json = array(

					"detalle" => "no encontrado"

				);

				echo json_encode($json, true);

				return;
			}
		} else if (array_filter($arrayRutas)[1] == "login") {
			/*=============================================
				Peticiones POST
				=============================================*/

			if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {

				$telefono = isset($_POST["telefono"]) ? $_POST["telefono"] : null;
				$password = isset($_POST["password"]) ? $_POST["password"] : null;

				// Verificar si alguna de las claves no está presente
				if ($telefono === null || $password === null) {
					$json = array(
						"detalle" => "Faltan datos en la solicitud"
					);
					echo json_encode($json, true);
					return;
				}


				// Si todas las claves están presentes, puedes continuar con el procesamiento de los datos
				$datos = array(
					"telefono" => $telefono,
					"password" => $password,
				);

				$registro = new ControladorUsuarios();
				$registro->login($datos);
			} else {

				$json = array(

					"detalle" => "no encontrado"

				);

				echo json_encode($json, true);

				return;
			}
		} else if (array_filter($arrayRutas)[1] == "traer-informacion") {
			/*=============================================
				Peticiones GET
				=============================================*/

			if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "GET") {


				$registro = new ControladorUsuarios();
				$registro->traerInformacion();
			} else {

				$json = array(

					"detalle" => "no encontrado"

				);

				echo json_encode($json, true);

				return;
			}
		} else if (array_filter($arrayRutas)[1] == "actualizar-usuario") {

			/*=============================================
				Peticiones POST
				=============================================*/

			if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {

				$telefono = isset($_POST["telefono"]) ? $_POST["telefono"] : null;
				$correo = isset($_POST["correo"]) ? $_POST["correo"] : null;
				$foto = isset($_POST["foto"]) ? $_POST["foto"] : null;
				$password = isset($_POST["password"]) ? $_POST["password"] : null;
				$usuario = isset($_POST["usuario"]) ? strtolower($_POST["usuario"]) : null;

				// Verificar que ningun campo este vacio
				if ($telefono == null) {
					$json = array(
						"detalle" => "El telefono se requiere para especificar que usuario se va a modificar"
					);
					echo json_encode($json, true);
					return;
				}

				// Verificar que sé allá cargada foto 
				if (isset($_FILES["foto"])) {
					list($ancho, $alto) = getimagesize($_FILES["foto"]["tmp_name"]);

					$nuevoAncho = 500;
					$nuevoAlto = 500;
					// Obtener la información de la imagen
					$infoImagen = getimagesize($_FILES["foto"]["tmp_name"]);

					if ($infoImagen !== false) {
						// Determinar la extensión de la imagen
						$extension = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);

						$ruta = "img/usuarios/{$nombre}.{$extension}";

						// Crear una imagen redimensionada
						$origen = imagecreatefromstring(file_get_contents($_FILES["foto"]["tmp_name"]));
						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $infoImagen[0], $infoImagen[1]);

						// Guardar la imagen
						switch ($extension) {
							case 'jpg':
							case 'jpeg':
								imagejpeg($destino, $ruta);
								break;

							case 'png':
								imagepng($destino, $ruta);
								break;
						}

						// Liberar memoria
						imagedestroy($origen);
						imagedestroy($destino);
					}
				} else {
					$ruta = null;
				}

				$datos = array(
					"telefono" => $telefono,
					"correo" => $correo,
					"foto" => $ruta,
					"password" => $password,
					"usuario" => $usuario
				);


				$registro = new ControladorUsuarios();
				$registro->actualizarUsuario($datos);
			} else {

				$json = array(

					"detalle" => "no encontrado"

				);

				echo json_encode($json, true);

				return;
			}
		} else if (array_filter($arrayRutas)[1] == "eliminar-usuario") {

			/*=============================================
				Peticiones POST
				=============================================*/

			if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {

				$telefono = isset($_POST["telefono"]) ? $_POST["telefono"] : null;

				// Verificar que ningun campo este vacio
				if ($telefono == null) {
					$json = array(
						"detalle" => "El telefono se requiere para especificar que usuario se va a eliminar"
					);
					echo json_encode($json, true);
					return;
				}


				$datos = array(
					"telefono" => $telefono,
				);


				$registro = new ControladorUsuarios();
				$registro->delete($datos);
			} else {

				$json = array(

					"detalle" => "no encontrado"

				);

				echo json_encode($json, true);

				return;
			}
		} else if (array_filter($arrayRutas)[1] == "crear-reporte") {
			/*=============================================
				Peticiones GET
				=============================================*/

			if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "GET") {


				$registro = new ControladorUsuarios();
				$registro->crearReporte();
			} else {

				$json = array(

					"detalle" => "no encontrado"

				);

				echo json_encode($json, true);

				return;
			}
		} else {

			$json = array(

				"detalle" => "no encontrado"

			);

			echo json_encode($json, true);

			return;
		}
	} else {

		$json = array(

			"detalle" => "no encontrado"

		);

		echo json_encode($json, true);

		return;
	}
}
