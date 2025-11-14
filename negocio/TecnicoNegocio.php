<?php
/**
 * CAPA DE LÓGICA DE NEGOCIO
 * TecnicoNegocio - Reglas de negocio para técnicos
 */
require_once __DIR__ . '/../persistencia/TecnicoDAO.php';

class TecnicoNegocio {
    private $tecnicoDAO;

    public function __construct($db) {
        $this->tecnicoDAO = new TecnicoDAO($db);
    }

    public function crearTecnico($datos) {
        // Validar datos requeridos
        if (!isset($datos['nombre']) || !isset($datos['apellido']) || 
            !isset($datos['telefono']) || !isset($datos['email']) || 
            !isset($datos['especialidad'])) {
            throw new Exception("Faltan datos requeridos");
        }

        // Validar formato de email
        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Formato de email inválido");
        }

        // Verificar que el email sea único
        if (!$this->tecnicoDAO->verificarEmailUnico($datos['email'])) {
            throw new Exception("El email ya está registrado");
        }

        $activo = $datos['activo'] ?? 1;

        $id = $this->tecnicoDAO->crear(
            $datos['nombre'],
            $datos['apellido'],
            $datos['telefono'],
            $datos['email'],
            $datos['especialidad'],
            $activo
        );

        if ($id) {
            return [
                'success' => true,
                'message' => 'Técnico creado exitosamente',
                'id' => $id
            ];
        }

        throw new Exception("Error al crear el técnico");
    }

    public function actualizarTecnico($datos) {
        if (!isset($datos['id'])) {
            throw new Exception("ID de técnico requerido");
        }

        // Validar datos requeridos
        if (!isset($datos['nombre']) || !isset($datos['apellido']) || 
            !isset($datos['telefono']) || !isset($datos['email']) || 
            !isset($datos['especialidad'])) {
            throw new Exception("Faltan datos requeridos");
        }

        // Validar formato de email
        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Formato de email inválido");
        }

        // Verificar que el email sea único (excluyendo el técnico actual)
        if (!$this->tecnicoDAO->verificarEmailUnico($datos['email'], $datos['id'])) {
            throw new Exception("El email ya está registrado");
        }

        $activo = $datos['activo'] ?? 1;

        if ($this->tecnicoDAO->actualizar(
            $datos['id'],
            $datos['nombre'],
            $datos['apellido'],
            $datos['telefono'],
            $datos['email'],
            $datos['especialidad'],
            $activo
        )) {
            return [
                'success' => true,
                'message' => 'Técnico actualizado exitosamente'
            ];
        }

        throw new Exception("Error al actualizar el técnico");
    }

    public function desactivarTecnico($id) {
        if (empty($id)) {
            throw new Exception("ID de técnico requerido");
        }

        if ($this->tecnicoDAO->desactivar($id)) {
            return [
                'success' => true,
                'message' => 'Técnico desactivado exitosamente'
            ];
        }

        throw new Exception("Error al desactivar el técnico");
    }

    public function obtenerTecnicosActivos() {
        return $this->tecnicoDAO->obtenerActivos();
    }

    public function obtenerTodosTecnicos() {
        return $this->tecnicoDAO->obtenerTodos();
    }

    public function obtenerTecnicoPorId($id) {
        if (empty($id)) {
            throw new Exception("ID de técnico requerido");
        }

        $tecnico = $this->tecnicoDAO->obtenerPorId($id);
        
        if (!$tecnico) {
            throw new Exception("Técnico no encontrado");
        }

        return $tecnico;
    }
}
?>