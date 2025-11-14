<?php
/**
 * MODELO DE DATOS (DTO)
 * Clase Visita - Representa una visita técnica
 */
class Visita {
    public $id;
    public $tecnico_id;
    public $cliente_id;
    public $fecha_visita;
    public $hora_inicio;
    public $hora_fin;
    public $tipo_servicio;
    public $descripcion;
    public $estado;
    public $notas;
    public $created_at;
    public $updated_at;

    // Datos relacionados (joins)
    public $tecnico;
    public $cliente;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->tecnico_id = $data['tecnico_id'] ?? null;
            $this->cliente_id = $data['cliente_id'] ?? null;
            $this->fecha_visita = $data['fecha_visita'] ?? null;
            $this->hora_inicio = $data['hora_inicio'] ?? null;
            $this->hora_fin = $data['hora_fin'] ?? null;
            $this->tipo_servicio = $data['tipo_servicio'] ?? null;
            $this->descripcion = $data['descripcion'] ?? null;
            $this->estado = $data['estado'] ?? 'programada';
            $this->notas = $data['notas'] ?? null;
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'tecnico_id' => $this->tecnico_id,
            'cliente_id' => $this->cliente_id,
            'fecha_visita' => $this->fecha_visita,
            'hora_inicio' => $this->hora_inicio,
            'hora_fin' => $this->hora_fin,
            'tipo_servicio' => $this->tipo_servicio,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'notas' => $this->notas,
            'tecnico' => $this->tecnico,
            'cliente' => $this->cliente
        ];
    }
}
?>