<?php
/**
 * CAPA DE LÓGICA DE NEGOCIO
 * VisitaNegocio - Reglas de negocio para visitas
 */
require_once __DIR__ . '/../persistencia/VisitaDAO.php';
require_once __DIR__ . '/../modelos/Visita.php';

class VisitaNegocio {
    private $visitaDAO;

    public function __construct($db) {
        $this->visitaDAO = new VisitaDAO($db);
    }

    /**
     * Crear una nueva visita
     * Aplica validaciones de negocio
     */
    public function crearVisita($datos) {
        // Validar datos requeridos
        if (!$this->validarDatosRequeridos($datos)) {
            throw new Exception("Faltan datos requeridos");
        }

        // Validar que la fecha no sea pasada
        if (!$this->validarFechaFutura($datos['fecha_visita'])) {
            throw new Exception("No se pueden crear visitas en fechas pasadas");
        }

        // Validar que hora_fin sea mayor que hora_inicio
        if (!$this->validarHorarios($datos['hora_inicio'], $datos['hora_fin'])) {
            throw new Exception("La hora de fin debe ser mayor que la hora de inicio");
        }

        // Crear objeto Visita
        $visita = new Visita($datos);
        $visita->estado = 'programada'; // Estado por defecto

        // Verificar conflictos de horario
        if ($this->visitaDAO->verificarConflictoHorario(
            $visita->tecnico_id,
            $visita->fecha_visita,
            $visita->hora_inicio,
            $visita->hora_fin
        )) {
            throw new Exception("El técnico ya tiene una visita asignada en ese horario");
        }

        // Crear visita
        if ($this->visitaDAO->crear($visita)) {
            return [
                'success' => true,
                'message' => 'Visita creada exitosamente',
                'id' => $visita->id
            ];
        }

        throw new Exception("Error al crear la visita");
    }

    /**
     * Actualizar una visita existente
     */
    public function actualizarVisita($datos) {
        // Validar datos requeridos
        if (!isset($datos['id']) || !$this->validarDatosRequeridos($datos)) {
            throw new Exception("Faltan datos requeridos");
        }

        // Validar que no se intente editar una visita cancelada
        $estadoActual = $this->visitaDAO->obtenerEstado($datos['id']);
        if ($estadoActual === 'cancelada') {
            throw new Exception("No se pueden editar visitas canceladas");
        }

        // Validar horarios
        if (!$this->validarHorarios($datos['hora_inicio'], $datos['hora_fin'])) {
            throw new Exception("La hora de fin debe ser mayor que la hora de inicio");
        }

        // Crear objeto Visita
        $visita = new Visita($datos);

        // Verificar conflictos de horario (excluyendo la visita actual)
        if ($this->visitaDAO->verificarConflictoHorario(
            $visita->tecnico_id,
            $visita->fecha_visita,
            $visita->hora_inicio,
            $visita->hora_fin,
            $visita->id
        )) {
            throw new Exception("El técnico ya tiene una visita asignada en ese horario");
        }

        // Actualizar visita
        if ($this->visitaDAO->actualizar($visita)) {
            return [
                'success' => true,
                'message' => 'Visita actualizada exitosamente'
            ];
        }

        throw new Exception("Error al actualizar la visita");
    }

    /**
     * Cancelar una visita
     */
    public function cancelarVisita($id) {
        if (empty($id)) {
            throw new Exception("ID de visita requerido");
        }

        // Verificar que la visita no esté ya cancelada
        $estadoActual = $this->visitaDAO->obtenerEstado($id);
        if ($estadoActual === 'cancelada') {
            throw new Exception("La visita ya está cancelada");
        }

        // Cambiar estado a cancelada
        if ($this->visitaDAO->cambiarEstado($id, 'cancelada')) {
            return [
                'success' => true,
                'message' => 'Visita cancelada exitosamente'
            ];
        }

        throw new Exception("Error al cancelar la visita");
    }

    /**
     * Eliminar permanentemente una visita
     * Solo se pueden eliminar visitas canceladas
     */
    public function eliminarVisitaPermanente($id) {
        if (empty($id)) {
            throw new Exception("ID de visita requerido");
        }

        // REGLA DE NEGOCIO: Solo se pueden eliminar visitas canceladas
        $estadoActual = $this->visitaDAO->obtenerEstado($id);
        if ($estadoActual !== 'cancelada') {
            throw new Exception("Solo se pueden eliminar permanentemente visitas canceladas");
        }

        // Eliminar visita
        if ($this->visitaDAO->eliminar($id)) {
            return [
                'success' => true,
                'message' => 'Visita eliminada permanentemente'
            ];
        }

        throw new Exception("Error al eliminar la visita");
    }

    /**
     * Obtener visitas por rango de fechas
     */
    public function obtenerVisitasPorRango($fecha_inicio, $fecha_fin) {
        // Validar fechas
        if (!$this->validarFormatoFecha($fecha_inicio) || !$this->validarFormatoFecha($fecha_fin)) {
            throw new Exception("Formato de fecha inválido");
        }

        $visitas = $this->visitaDAO->obtenerPorRango($fecha_inicio, $fecha_fin);
        
        // Convertir a array
        $resultado = [];
        foreach ($visitas as $visita) {
            $resultado[] = $visita->toArray();
        }

        return $resultado;
    }

    /**
     * Obtener estadísticas de técnicos
     */
    public function obtenerEstadisticasTecnicos($fecha_inicio, $fecha_fin) {
        return $this->visitaDAO->obtenerEstadisticasTecnicos($fecha_inicio, $fecha_fin);
    }

    // ========== MÉTODOS DE VALIDACIÓN ==========

    private function validarDatosRequeridos($datos) {
        $requeridos = ['tecnico_id', 'cliente_id', 'fecha_visita', 'hora_inicio', 'hora_fin', 'tipo_servicio'];
        
        foreach ($requeridos as $campo) {
            if (empty($datos[$campo])) {
                return false;
            }
        }
        
        return true;
    }

    private function validarFechaFutura($fecha) {
        $fechaVisita = new DateTime($fecha);
        $hoy = new DateTime();
        $hoy->setTime(0, 0, 0);
        
        return $fechaVisita >= $hoy;
    }

    private function validarHorarios($hora_inicio, $hora_fin) {
        return strtotime($hora_fin) > strtotime($hora_inicio);
    }

    private function validarFormatoFecha($fecha) {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
}
?>