<?php
/**
 * CAPA DE PERSISTENCIA (DAO)
 * TecnicoDAO - Acceso a datos de técnicos
 */
class TecnicoDAO {
    private $conn;
    private $table_name = "tecnicos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear($nombre, $apellido, $telefono, $email, $especialidad, $activo = 1) {
        $query = "INSERT INTO " . $this->table_name . "
                SET nombre=:nombre,
                    apellido=:apellido,
                    telefono=:telefono,
                    email=:email,
                    especialidad=:especialidad,
                    activo=:activo";

        $stmt = $this->conn->prepare($query);

        $nombre = htmlspecialchars(strip_tags($nombre));
        $apellido = htmlspecialchars(strip_tags($apellido));
        $telefono = htmlspecialchars(strip_tags($telefono));
        $email = htmlspecialchars(strip_tags($email));
        $especialidad = htmlspecialchars(strip_tags($especialidad));

        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":especialidad", $especialidad);
        $stmt->bindParam(":activo", $activo);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function obtenerActivos() {
        $query = "SELECT id, nombre, apellido, telefono, email, especialidad
                  FROM " . $this->table_name . "
                  WHERE activo = 1
                  ORDER BY nombre, apellido";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTodos() {
        $query = "SELECT id, nombre, apellido, telefono, email, especialidad, activo
                  FROM " . $this->table_name . "
                  ORDER BY nombre, apellido";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "SELECT id, nombre, apellido, telefono, email, especialidad, activo
                  FROM " . $this->table_name . "
                  WHERE id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id, $nombre, $apellido, $telefono, $email, $especialidad, $activo) {
        $query = "UPDATE " . $this->table_name . "
                SET nombre=:nombre,
                    apellido=:apellido,
                    telefono=:telefono,
                    email=:email,
                    especialidad=:especialidad,
                    activo=:activo
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $id = htmlspecialchars(strip_tags($id));
        $nombre = htmlspecialchars(strip_tags($nombre));
        $apellido = htmlspecialchars(strip_tags($apellido));
        $telefono = htmlspecialchars(strip_tags($telefono));
        $email = htmlspecialchars(strip_tags($email));
        $especialidad = htmlspecialchars(strip_tags($especialidad));

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":especialidad", $especialidad);
        $stmt->bindParam(":activo", $activo);

        return $stmt->execute();
    }

    public function desactivar($id) {
        $query = "UPDATE " . $this->table_name . "
                SET activo = 0
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function verificarEmailUnico($email, $excluir_id = null) {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . "
                  WHERE email = :email";

        if ($excluir_id) {
            $query .= " AND id != :excluir_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        
        if ($excluir_id) {
            $stmt->bindParam(":excluir_id", $excluir_id);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'] == 0;
    }

    public function tieneVisitas($id) {
        $query = "SELECT COUNT(*) as total FROM visitas WHERE tecnico_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'] > 0;
    }
}
?>