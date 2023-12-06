<?php

require_once "conexion.php";

class ModeloUsuarios
{

	/*=============================================
	Mostrar todos los registros
	=============================================*/

	static public function index($tabla, $item, $valor)
	{
		if ($item == null) {
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");

			$stmt->execute();

			return $stmt->fetchAll();

			$stmt->close();

			$stmt -= null;
		} else {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetch();

			$stmt->close();

			$stmt -= null;
		}
	}

	/*=============================================
	Crear un registro
	=============================================*/

	static public function create($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, apellidos, telefono, correo, foto, password, usuario, fecha_registro) VALUES (:nombre, :apellidos, :telefono, :correo, :foto, :password, :usuario, :fecha_registro)");

		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":apellidos", $datos["apellidos"], PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
		$stmt->bindParam(":correo", $datos["correo"], PDO::PARAM_STR);
		$stmt->bindParam(":foto", $datos["foto"], PDO::PARAM_STR);
		$stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
		$stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha_registro", $datos["fecha_registro"], PDO::PARAM_STR);

		if ($stmt->execute()) {

			return "ok";
		} else {

			print_r(Conexion::conectar()->errorInfo());
		}

		$stmt->close();

		$stmt = null;
	}

	static public function login($tablaUsarios, $datos)
	{
		$stmt = Conexion::conectar()->prepare("UPDATE $tablaUsarios SET  fecha_login = :fecha_login, sesion = :sesion WHERE telefono = :telefono");
		$stmt->bindParam(":fecha_login", $datos["fecha_login"], PDO::PARAM_STR);
		$stmt->bindParam(":sesion", $datos["sesion"], PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);

		if ($stmt->execute()) {
			return "ok";
		} else {
			print_r(Conexion::conectar()->errorInfo());
		}

		$stmt->close();
		$stmt = null;
	}

	static function logout($tablaUsarios, $item, $valor)
	{
		$stmt = Conexion::conectar()->prepare("UPDATE $tablaUsarios SET  fecha_login = :fecha_login, sesion = :sesion WHERE telefono = :telefono");
		$stmt->bindParam(":sesion", 0, PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $valor, PDO::PARAM_STR);

		if ($stmt->execute()) {
			return "ok";
		} else {
			print_r(Conexion::conectar()->errorInfo());
		}

		$stmt->close();
		$stmt = null;
	}

	static public function update($tablaUsarios, $datos)
	{
		$stmt = Conexion::conectar()->prepare("UPDATE $tablaUsarios SET correo = :correo, foto = :foto, password = :password, fecha_actualizacion = :fecha_actualizacion WHERE telefono = :telefono");
		$stmt->bindParam(":correo", $datos["correo"], PDO::PARAM_STR);
		$stmt->bindParam(":foto", $datos["foto"], PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
		$stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha_actualizacion", $datos["fecha_actualizacion"], PDO::PARAM_STR);

		if ($stmt->execute()) {
			return "ok";
		} else {
			print_r(Conexion::conectar()->errorInfo());
		}

		$stmt->close();
		$stmt = null;
	}

	// traer infomacion de usuario activos
	static public function usuarioActivos($tabla, $item, $valor)
	{
		if ($item == null) {
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE status = 1");

			$stmt->execute();

			return $stmt->fetchAll();

			$stmt->close();

			$stmt -= null;
		} else {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item AND status = 1");

			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetch();

			$stmt->close();

			$stmt -= null;
		}
	}

	static public function delete($tabla, $item, $valor)
	{
		// Eliminar usuario de la base de datos
		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE $item = :$item");
		$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return print_r($stmt->errorInfo());
		}
		$stmt->close();

		$stmt = null;
	}
}
