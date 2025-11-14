<?php
/**
 * CAPA DE PERSISTENCIA (DAO)
 * VisitaDAO - Acceso a datos de visitas
 * Solo operaciones CRUD, sin lógica de negocio
 */
require_once __DIR__ . '/../modelos/Visita.php';

class VisitaDAO {
    private $conn;
    private $table_name = "visitas";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear una nueva visita
     */
    public function crear(Visita $visita) {
        $query = "INSERT INTO " . $this->table_name . "
                SET tecnico_id=:tecnico_id,
                    cliente_id=:cliente_id,
                    fecha_visita=:fecha_visita,
                    hora_inicio=:hora_inicio,
                    hora_fin=:hora_fin,
                    tipo_servicio=:tipo_servicio,
                    descripcion=:descripcion,
                    estado=:estado,
                    notas=:notas";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $visita->tecnico_id = htmlspecialchars(strip_tags($visita->tecnico_id));
        $visita->cliente_id = htmlspecialchars(strip_tags($visita->cliente_id));
        $visita->fecha_visita = htmlspecialchars(strip_tags($visita->fecha_visita));
        $visita->hora_inicio = htmlspecialchars(strip_tags($visita->hora_inicio));
        $visita->hora_fin = htmlspecialchars(strip_tags($visita->hora_fin));
        $visita->tipo_servicio = htmlspecialchars(strip_tags($visita->tipo_servicio));
        $visita->descripcion = htmlspecialchars(strip_tags($visita->descripcion));
        $visita->estado = htmlspecialchars(strip_tags($visita->estado));
        $visita->notas = htmlspecialchars(strip_tags($visita->notas));

        $stmt->bindParam(":tecnico_id", $visita->tecnico_id);
        $stmt->bindParam(":cliente_id", $visita->cliente_id);
        $stmt->bindParam(":fecha_visita", $visita->fecha_visita);
        $stmt->bindParam(":hora_inicio", $visita->hora_inicio);
        $stmt->bindParam(":hora_fin", $visita->hora_fin);
        $stmt->bindParam(":tipo_servicio", $visita->tipo_servicio);
        $stmt->bindParam(":descripcion", $visita->descripcion);
        $stmt->bindParam(":estado", $visita->estado);
        $stmt->bindParam(":notas", $visita->notas);

        if ($stmt->execute()) {
            $visita->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Obtener visitas por rango de fechas con información de técnico y cliente
     */
    public function obtenerPorRango($fecha_inicio, $fecha_fin) {
        $query = "SELECT 
                    v.id, v.fecha_visita, v.hora_inicio, v.hora_fin,
                    v.tipo_servicio, v.descripcion, v.estado, v.notas,
                    t.id as tecnico_id, t.nombre as tecnico_nombre, 
                    t.apellido as tecnico_apellido, t.especialidad,
                    c.id as cliente_id, c.nombre as cliente_nombre,
                    c.apellido as cliente_apellido, c.empresa,
                    c.telefono as cliente_telefono, c.direccion
                  FROM " . $this->table_name . " v
                  LEFT JOIN tecnicos t ON v.tecnico_id = t.id
                  LEFT JOIN clientes c ON v.cliente_id = c.id
                  WHERE v.fecha_visita BETWEEN :fecha_inicio AND :fecha_fin
                  ORDER BY v.fecha_visita, v.hora_inicio";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();

        $visitas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $visita = new Visita($row);
            $visita->tecnico = [
                'id' => $row['tecnico_id'],
                'nombre' => $row['tecnico_nombre'],
                'apellido' => $row['tecnico_apellido'],
                'especialidad' => $row['especialidad']
            ];
            $visita->cliente = [
                'id' => $row['cliente_id'],
                'nombre' => $row['cliente_nombre'],
                'apellido' => $row['cliente_apellido'],
                'empresa' => $row['empresa'],
                'telefono' => $row['cliente_telefono'],
                'direccion' => $row['direccion']
            ];
            $visitas[] = $visita;
        }

        return $visitas;
    }

    /**
     * Actualizar una visita
     */
    public function actualizar(Visita $visita) {
        $query = "UPDATE " . $this->table_name . "
                SET tecnico_id=:tecnico_id,
                    cliente_id=:cliente_id,
                    fecha_visita=:fecha_visita,
                    hora_inicio=:hora_inicio,
                    hora_fin=:hora_fin,
                    tipo_servicio=:tipo_servicio,
                    descripcion=:descripcion,
                    estado=:estado,
                    notas=:notas
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $visita->id = htmlspecialchars(strip_tags($visita->id));
        $visita->tecnico_id = htmlspecialchars(strip_tags($visita->tecnico_id));
        $visita->cliente_id = htmlspecialchars(strip_tags($visita->cliente_id));
        $visita->fecha_visita = htmlspecialchars(strip_tags($visita->fecha_visita));
        $visita->hora_inicio = htmlspecialchars(strip_tags($visita->hora_inicio));
        $visita->hora_fin = htmlspecialchars(strip_tags($visita->hora_fin));
        $visita->tipo_servicio = htmlspecialchars(strip_tags($visita->tipo_servicio));
        $visita->descripcion = htmlspecialchars(strip_tags($visita->descripcion));
        $visita->estado = htmlspecialchars(strip_tags($visita->estado));
        $visita->notas = htmlspecialchars(strip_tags($visita->notas));

        $stmt->bindParam(":id", $visita->id);
        $stmt->bindParam(":tecnico_id", $visita->tecnico_id);
        $stmt->bindParam(":cliente_id", $visita->cliente_id);
        $stmt->bindParam(":fecha_visita", $visita->fecha_visita);
        $stmt->bindParam(":hora_inicio", $visita->hora_inicio);
        $stmt->bindParam(":hora_fin", $visita->hora_fin);
        $stmt->bindParam(":tipo_servicio", $visita->tipo_servicio);
        $stmt->bindParam(":descripcion", $visita->descripcion);
        $stmt->bindParam(":estado", $visita->estado);
        $stmt->bindParam(":notas", $visita->notas);

        return $stmt->execute();
    }

    /**
     * Cambiar estado de una visita
     */
    public function cambiarEstado($id, $estado) {
        $query = "UPDATE " . $this->table_name . "
                SET estado=:estado
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $estado = htmlspecialchars(strip_tags($estado));
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    /**
     * Obtener estado de una visita
     */
    public function obtenerEstado($id) {
        $query = "SELECT estado FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['estado'] : null;
    }

    /**
     * Eliminar una visita permanentemente
     */
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    /**
     * Verificar conflictos de horario
     */
    public function verificarConflictoHorario($tecnico_id, $fecha_visita, $hora_inicio, $hora_fin, $excluir_id = null) {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . "
                  WHERE tecnico_id = :tecnico_id 
                  AND fecha_visita = :fecha_visita
                  AND estado != 'cancelada'
                  AND (hora_inicio < :hora_fin AND hora_fin > :hora_inicio)";

        if ($excluir_id) {
            $query .= " AND id != :excluir_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":tecnico_id", $tecnico_id);
        $stmt->bindParam(":fecha_visita", $fecha_visita);
        $stmt->bindParam(":hora_inicio", $hora_inicio);
        $stmt->bindParam(":hora_fin", $hora_fin);
        
        if ($excluir_id) {
            $stmt->bindParam(":excluir_id", $excluir_id);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'] > 0;
    }

    /**
     * Obtener estadísticas de técnicos
     */
    public function obtenerEstadisticasTecnicos($fecha_inicio, $fecha_fin) {
        $query = "SELECT 
                    t.id, t.nombre, t.apellido,
                    COUNT(v.id) as total_visitas,
                    SUM(CASE WHEN v.estado = 'completada' THEN 1 ELSE 0 END) as completadas,
                    SUM(CASE WHEN v.estado = 'programada' THEN 1 ELSE 0 END) as programadas
                  FROM tecnicos t
                  LEFT JOIN visitas v ON t.id = v.tecnico_id 
                    AND v.fecha_visita BETWEEN :fecha_inicio AND :fecha_fin
                    AND v.estado != 'cancelada'
                  WHERE t.activo = 1
                  GROUP BY t.id, t.nombre, t.apellido
                  ORDER BY total_visitas DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>