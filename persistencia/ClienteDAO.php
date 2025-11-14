<?php
/**
 * CAPA DE PERSISTENCIA (DAO)
 * ClienteDAO - Acceso a datos de clientes
 */
class ClienteDAO {
    private $conn;
    private $table_name = "clientes";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear($nombre, $apellido, $empresa, $telefono, $email, $direccion, $ciudad) {
        $query = "INSERT INTO " . $this->table_name . "
                SET nombre=:nombre,
                    apellido=:apellido,
                    empresa=:empresa,
                    telefono=:telefono,
                    email=:email,
                    direccion=:direccion,
                    ciudad=:ciudad";

        $stmt = $this->conn->prepare($query);

        $nombre = htmlspecialchars(strip_tags($nombre));
        $apellido = htmlspecialchars(strip_tags($apellido));
        $empresa = htmlspecialchars(strip_tags($empresa));
        $telefono = htmlspecialchars(strip_tags($telefono));
        $email = htmlspecialchars(strip_tags($email));
        $direccion = htmlspecialchars(strip_tags($direccion));
        $ciudad = htmlspecialchars(strip_tags($ciudad));

        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":empresa", $empresa);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":ciudad", $ciudad);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function obtenerTodos() {
        $query = "SELECT 
                    c.id, c.nombre, c.apellido, c.empresa, 
                    c.telefono, c.email, c.direccion, c.ciudad,
                    COUNT(v.id) as total_visitas,
                    MAX(v.fecha_visita) as ultima_visita
                  FROM " . $this->table_name . " c
                  LEFT JOIN visitas v ON c.id = v.cliente_id
                  GROUP BY c.id
                  ORDER BY c.nombre, c.apellido";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "SELECT id, nombre, apellido, empresa, telefono, 
                         email, direccion, ciudad
                  FROM " . $this->table_name . "
                  WHERE id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id, $nombre, $apellido, $empresa, $telefono, $email, $direccion, $ciudad) {
        $query = "UPDATE " . $this->table_name . "
                SET nombre=:nombre,
                    apellido=:apellido,
                    empresa=:empresa,
                    telefono=:telefono,
                    email=:email,
                    direccion=:direccion,
                    ciudad=:ciudad
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $id = htmlspecialchars(strip_tags($id));
        $nombre = htmlspecialchars(strip_tags($nombre));
        $apellido = htmlspecialchars(strip_tags($apellido));
        $empresa = htmlspecialchars(strip_tags($empresa));
        $telefono = htmlspecialchars(strip_tags($telefono));
        $email = htmlspecialchars(strip_tags($email));
        $direccion = htmlspecialchars(strip_tags($direccion));
        $ciudad = htmlspecialchars(strip_tags($ciudad));

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":empresa", $empresa);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":ciudad", $ciudad);

        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function tieneVisitas($id) {
        $query = "SELECT COUNT(*) as total FROM visitas WHERE cliente_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'] > 0;
    }

    public function buscar($termino) {
        $query = "SELECT id, nombre, apellido, empresa, telefono, email, direccion, ciudad
                  FROM " . $this->table_name . "
                  WHERE nombre LIKE :termino 
                     OR apellido LIKE :termino 
                     OR empresa LIKE :termino
                  ORDER BY nombre, apellido
                  LIMIT 20";

        $stmt = $this->conn->prepare($query);
        $termino = "%{$termino}%";
        $stmt->bindParam(":termino", $termino);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>