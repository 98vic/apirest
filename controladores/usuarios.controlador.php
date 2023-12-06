<?php


require_once "extras/vendor/autoload.php";

use Dompdf\Dompdf;

date_default_timezone_set('America/Mexico_City');

$max_inactivity = 1800; // Tiempo en segundos (por ejemplo, 30 minutos)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $max_inactivity)) {
	// Pasó el tiempo de inactividad, destruir la sesión
	$tablaUsarios = "usuarios";
	ModeloUsuarios::logout($tablaUsarios, "telefono", $_SESSION['telefono']);
	session_unset();
	session_destroy();
}

// Actualiza la última actividad en cada carga de página o acción del usuario
$_SESSION['last_activity'] = time();

class ControladorUsuarios
{

	/*=============================================
	Crear un registro
	=============================================*/

	public function create($datos)
	{
		$correo = $datos["correo"];
		$telefono = $datos["telefono"];
		$password = $datos["password"];
		$nombre = $datos["nombre"];
		$apellidos = $datos["apellidos"];
		$usuario = $datos["usuario"];
		$foto = $datos["foto"];

		$correoFiltrado = filter_var($correo, FILTER_SANITIZE_EMAIL);

		// Validar el correo electrónico
		if (filter_var($correoFiltrado, FILTER_VALIDATE_EMAIL)) {
			$correoFiltrado;
		} else {
			$json = array(
				"detalle" => "El correo no es valido"
			);
			echo json_encode($json, true);
			return;
		}

		// Eliminar cualquier caracter que no sea un dígito
		$telefono = preg_replace("/[^0-9]/", "", $telefono);

		// Verificar si el número de teléfono tiene exactamente 10 dígitos
		if (strlen($telefono) == 10) {
			// El número de teléfono es válido
		} else {
			$json = array(
				"detalle" => "El telefono no es valido"
			);
			echo json_encode($json, true);
			return;
		}

		// encrptar el password 
		$encriptar = crypt($password, '$2a$07$asxx54ahjppf45sd87a5a4dDDGasw$');

		// Verificar los permisos del usuario
		if ($usuario == "administrador" || $usuario == "basico") {
			$usuario;
		} else {
			$json = array(
				"detalle" => "Los permisos de usuario no son correctos"
			);
			echo json_encode($json, true);
			return;
		}

		/*=============================================
		Validar nombre
		=============================================*/

		if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $nombre)) {

			$json = array(

				"status" => 404,
				"detalle" => "Error en el campo nombre, sólo se permiten letras"

			);

			echo json_encode($json, true);

			return;
		}

		/*=============================================
		Validar apellido
		=============================================*/

		if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $apellidos)) {
			$json = array(
				"status" => 404,
				"detalle" => "Error en el campo apellido, sólo se permiten letras"

			);
			echo json_encode($json, true);

			return;
		}


		$fechaRegistro = date("Y-m-d H:i:s");

		// Validar que no exista el usuario 
		$tablaUsarios = "usuarios";
		$itemTelefono = "telefono";
		$existe = ModeloUsuarios::index($tablaUsarios, $itemTelefono, $telefono);

		if ($existe == false) {
			// Guardar informacion en base de datos
			$datos = array(
				"nombre" => $nombre,
				"apellidos" => $apellidos,
				"telefono" => $telefono,
				"correo" => $correoFiltrado,
				"foto" => $foto,
				"password" => $encriptar,
				"usuario" => $usuario,
				"fecha_registro" => $fechaRegistro
			);


			$create = ModeloUsuarios::create($tablaUsarios, $datos);
			/*=============================================
			Respuesta del modelo
			=============================================*/

			if ($create == "ok") {

				$json = array(

					"status" => 200,
					"detalle" => "Registro exitoso, ya puede iniciar sesion",

				);

				echo json_encode($json, true);

				return;
			}
		} else {
			$json = array(

				"status" => 404,
				"detalle" => "El usuario ya existe",

			);

			echo json_encode($json, true);

			return;
		}
	}

	public function login($datos)
	{
		$tablaUsarios = "usuarios";
		$itemTelefono = "telefono";
		$itemPassword = "password";
		$telefono = $datos['telefono'];
		$password = $datos['password'];

		// Eliminar cualquier caracter que no sea un dígito
		$telefono = preg_replace("/[^0-9]/", "", $telefono);

		// Verificar si el número de teléfono tiene exactamente 10 dígitos
		if (strlen($telefono) == 10) {
			// El número de teléfono es válido
		} else {
			$json = array(
				"detalle" => "El telefono no es valido"
			);
			echo json_encode($json, true);
			return;
		}

		$encriptar = crypt($password, '$2a$07$asxx54ahjppf45sd87a5a4dDDGasw$');

		// traer informacion del usuario
		$fechaLogin = date("Y-m-d H:i:s");
		$informacionUsuario = ModeloUsuarios::index($tablaUsarios, $itemTelefono, $telefono);

		if ($informacionUsuario == false) {
			$json = array(
				"status" => 404,
				"detalle" => "El telefono o password son incorrectas"
			);
			echo json_encode($json, true);

			return;
		} else {
			// cambiar estaus de sesion
			if ($informacionUsuario['password'] == $encriptar) {

				$datos = array(
					"telefono" => $telefono,
					"fecha_login" => $fechaLogin,
					"sesion" => 1
				);

				$idUsuario = $informacionUsuario["id"];
				$status = $informacionUsuario["status"];
				$sesion = $informacionUsuario["sesion"];
				$telefono = $informacionUsuario["telefono"];
				$tipoUsuario = $informacionUsuario["usuario"];

				session_start();
				$_SESSION["idUsuario"] = $idUsuario;
				$_SESSION["status"] = $status;
				$_SESSION["telefono"] = $telefono;
				$_SESSION["sesion"] = $sesion;
				$_SESSION["tipoUsuario"] = $tipoUsuario;
				$_SESSION['last_activity'] = time();


				$login = ModeloUsuarios::login($tablaUsarios, $datos);

				if ($login == "ok") {
					$json = array(
						"status" => 200,
						"detalle" => "Sesion iniciada correctamente"
					);

					echo json_encode($json, true);

					return;
				} else {
					$json = array(
						"status" => 404,
						"detalle" => "Error al iniciar sesion"
					);
					echo json_encode($json, true);

					return;
				}
			} else {
				$json = array(
					"status" => 404,
					"detalle" => "El telefono o password son incorrectas"
				);
				echo json_encode($json, true);

				return;
			}
		}
	}


	public function traerInformacion()
	{
		session_start();

		if (isset($_SESSION['status']) && $_SESSION['status'] == "1" && isset($_SESSION['sesion']) && $_SESSION['sesion'] == "1") {

			$tablaUsarios = "usuarios";
			$item = null;
			$valor = null;
			$informacionUsuarios = ModeloUsuarios::usuarioActivos($tablaUsarios, $item, $valor);

			$json = array();

			foreach ($informacionUsuarios as $key => $value) {
				$fotoURL = "http://apirest-php.com/" . $value['foto']; // Obtener la URL completa de la foto

				$json[] = [
					"status" => 200,
					"nombre" => $value['nombre'],
					"apellidos" => $value['apellidos'],
					"telefono" => $value['telefono'],
					"correo" => $value['correo'],
					"foto" => $fotoURL,
					"usuario" => $value['usuario'],
					"fecha_registro" => $value["fecha_registro"],
					"fecha_actualizacion" => $value["fecha_actualizacion"],
					"fecha_login" => $value["fecha_login"]
				];
			}

			echo json_encode($json, true);

			return;
		} else {
			$json = array(
				"status" => 404,
				"detalle" => "No ha iniciado sesion"
			);
			echo json_encode($json, true);

			return;
		}
	}

	public function actualizarUsuario($datos)
	{
		session_start();

		if (isset($_SESSION['sesion']) && $_SESSION['sesion'] == '1') {
			$correo = $datos["correo"];
			$telefono = $datos["telefono"];
			$password = $datos["password"];
			// verificar que esxita ese usuario y traer informacion 
			$tablaUsuarios = "usuarios";
			$itemTelefono = 'telefono';
			$datosUsuario = ModeloUsuarios::index($tablaUsuarios, $itemTelefono, $telefono);
			$idUsuario = $datosUsuario['id'];
			if ($datosUsuario == false) {
				$json = array(
					"status" => 404,
					"detalle" => "No se puede actualizar ya que el usuario no existe"
				);

				echo json_encode($json, true);

				return;
			}
			$foto = $datos["foto"] == null ? $datosUsuario['foto'] : $datos['foto'];

			// Filtrar el correo si es necesario

			$correoFiltrado = ($correo !== null) ? filter_var($correo, FILTER_SANITIZE_EMAIL) : $datosUsuario['correo'];

			// Validar el correo electrónico si se proporciona
			if ($correoFiltrado !== null && !filter_var($correoFiltrado, FILTER_VALIDATE_EMAIL)) {
				$json = array(
					"detalle" => "El correo no es valido"
				);
				echo json_encode($json, true);
				return;
			}

			// Eliminar cualquier caracter que no sea un dígito y validar el teléfono
			$telefono = preg_replace("/[^0-9]/", "", $telefono);

			// Verificar si el número de teléfono tiene exactamente 10 dígitos
			if (strlen($telefono) == 10) {
				// El número de teléfono es válido
			} else {
				$json = array(
					"detalle" => "El telefono no es valido"
				);
				echo json_encode($json, true);
				return;
			}


			// Encriptar la contraseña si se proporciona
			$encriptar = ($password != null) ? crypt($password, '$2a$07$asxx54ahjppf45sd87a5a4dDDGasw$') : $datosUsuario['password'];

			// Crear fecha actualizacion
			$fechaAcutlizacion = date("Y-m-d H:i:s");

			// Verificar los permisos del usuario
			if ($_SESSION['tipoUsuario'] == "administrador" && ($telefono == $datosUsuario['telefono'] || $telefono !== $datosUsuario['telefono'])) {

				// Construir el array de datos para la actualización, evitando valores nulos
				$datosActualizados = array(
					"telefono" => $telefono,
					"correo" => $correoFiltrado,
					"foto" => $foto,
					"password" => $encriptar,
					"fecha_actualizacion" => $fechaAcutlizacion
				);

				// Actualizar en base de datos
				$update = ModeloUsuarios::update($tablaUsuarios, $datosActualizados);

				if ($update == "ok") {
					$json = array(
						"status" => 200,
						"detalle" => "Se ha actualizado de manera correcta"
					);

					echo json_encode($json, true);

					return;
				}
			} else if ($_SESSION["tipoUsuario"] == "basico" && $telefono == $_SESSION['telefono'] && $_SESSION['status'] == "1") {

				$datosActualizados = array(
					"telefono" => $telefono,
					"correo" => $correoFiltrado,
					"foto" => $foto,
					"password" => $encriptar,
					"fecha_actualizacion" => $fechaAcutlizacion
				);

				// Actualizar en base de datos
				$update = ModeloUsuarios::update($tablaUsuarios, $datosActualizados);

				if ($update == "ok") {
					$json = array(
						"status" => 200,
						"detalle" => "Se ha actualizado de manera correcta"
					);

					echo json_encode($json, true);

					return;
				}
			} else {
				$json = array(
					"status" => 404,
					"detalle" => "No tiene permisos para actualizar"
				);
				echo json_encode($json, true);
			}
		} else {
			$json = array(
				"status" => 404,
				"detalle" => "No ha iniciado sesion"
			);
			echo json_encode($json, true);
		}
	}

	public function delete($datos)
	{
		session_start();

		if (isset($_SESSION) && $_SESSION['sesion'] == '1') {
			$telefono = $datos['telefono'];

			// Eliminar cualquier caracter que no sea un dígito y validar el teléfono
			$telefono = preg_replace("/[^0-9]/", "", $telefono);

			// Verificar si el número de teléfono tiene exactamente 10 dígitos
			if (strlen($telefono) == 10) {
				// El número de teléfono es válido
			} else {
				$json = array(
					"detalle" => "El telefono no es valido"
				);
				echo json_encode($json, true);
				return;
			}

			if ($_SESSION['telefono'] == $telefono) {

				$json = array(
					"status" => 404,
					"detalle" => "No se puede eliminar si esta iniciada la sesion"
				);

				echo json_encode($json, true);
			} else if ($_SESSION['tipoUsuario'] == 'administrador' && $telefono !== $_SESSION['telefono']) {
				$tablaUsarios = 'usuarios';
				$itemTelefono = 'telefono';

				$delete = ModeloUsuarios::delete($tablaUsarios, $itemTelefono, $telefono);

				if ($delete == "ok") {
					$json = array(
						"status" => 200,
						"detalle" => "Se ha eliminado de manera correcta"
					);
					echo json_encode($json, true);
				} else {
					$json = array(
						"status" => 404,
						"detalle" => "No se ha eliminado"
					);
					echo json_encode($json, true);
				}
			} else {
				$json = array(
					"status" => 404,
					"detalle" => "No tiene permisos para eliminar"
				);
				echo json_encode($json, true);
			}
		} else {
			$json = array(
				"status" => 404,
				"detalle" => "No ha iniciado sesion"
			);
			echo json_encode($json, true);
		}
	}

	public function crearReporte()
	{
		session_start();

		if (isset($_SESSION['status']) && $_SESSION['status'] == "1" && isset($_SESSION['sesion']) && $_SESSION['sesion'] == "1") {

			$tablaUsarios = "usuarios";
			$item = null;
			$valor = null;
			$informacionUsuarios = ModeloUsuarios::usuarioActivos($tablaUsarios, $item, $valor);

			$json = array();

			foreach ($informacionUsuarios as $key => $value) {
				// $fotoURL = "http://apirest-php.com/" . $value['foto']; // Obtener la URL completa de la foto

				$json[] = [
					"status" => 200,
					"nombre" => $value['nombre'],
					"apellidos" => $value['apellidos'],
					"telefono" => $value['telefono'],
					"correo" => $value['correo'],
					"foto" => $value['foto'],
					"usuario" => $value['usuario']
				];
			}

			$fechaActual = date("d-m-Y");
			$directorioReportes = realpath(__DIR__ . '/../pdf/reportes/');

			// Corregir formato de fecha y construir la ruta del reporte
			$reporte = $directorioReportes . '/reporte' . str_replace([' ', ':'], ['-', ''], $fechaActual) . '.pdf';

			$dompdf = new Dompdf();

			ob_start();

			include_once "extras/plantillaspdf/reporte.php";

			$html = ob_get_clean();

			$dompdf->loadHtml($html);

			$tamañoUnit = array(0, 0, 226.82, 2835.27); /* (0,0, ancho, largo) */

			/* La medida se saca de la sig manera longitudDeseadaenCM / 0.03527 = largorEnCm */
			$dompdf->setPaper($tamañoUnit, 'portrait');

			$dompdf->render();

			$contenido = $dompdf->output();

			file_put_contents($reporte, $contenido);

			$json = array(
				"status" => 200,
				"url" => "http://apirest-php.com/pdf/reportes/" . str_replace([' ', ':'], ['-', ''], $fechaActual) . '.pdf'
			);

			echo json_encode($json, true);
		} else {
			$json = array(
				"status" => 404,
				"detalle" => "No ha iniciado sesion"
			);
			echo json_encode($json, true);

			return;
		}
	}
}
