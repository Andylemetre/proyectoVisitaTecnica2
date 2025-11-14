<?php
/**
 * CAPA DE LÓGICA DE NEGOCIO
 * ClienteNegocio - Reglas de negocio para clientes
 */
require_once __DIR__ . '/../persistencia/ClienteDAO.php';

class ClienteNegocio {
    private $clienteDAO;

    public function __construct($db) {
        $this->clienteDAO = new ClienteDAO($db);
    }

    public function crearCliente($datos) {
        // Validar datos requeridos
        if (!isset($datos['nombre']) || !isset($datos['apellido']) || 
            !isset($datos['telefono']) || !isset($datos['direccion'])) {
            throw new Exception("Faltan datos requeridos");
        }

        // Validar formato de email si se proporciona
        if (!empty($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Formato de email inválido");
        }

        // Validar longitud de teléfono
        if (strlen($datos['telefono']) < 7) {
            throw new Exception("Número de teléfono inválido");
        }

        $id = $this->clienteDAO->crear(
            $datos['nombre'],
            $datos['apellido'],
            $datos['empresa'] ?? '',
            $datos['telefono'],
            $datos['email'] ?? '',
            $datos['direccion'],
            $datos['ciudad'] ?? ''
        );

        if ($id) {
            return [
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'id' => $id
            ];
        }

        throw new Exception("Error al crear el cliente");
    }

    public function actualizarCliente($datos) {
        if (!isset($datos['id'])) {
            throw new Exception("ID de cliente requerido");
        }

        // Validar datos requeridos
        if (!isset($datos['nombre']) || !isset($datos['apellido']) || 
            !isset($datos['telefono']) || !isset($datos['direccion'])) {
            throw new Exception("Faltan datos requeridos");
        }

        // Validar formato de email si se proporciona
        if (!empty($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Formato de email inválido");
        }

        // Validar longitud de teléfono
        if (strlen($datos['telefono']) < 7) {
            throw new Exception("Número de teléfono inválido");
        }

        if ($this->clienteDAO->actualizar(
            $datos['id'],
            $datos['nombre'],
            $datos['apellido'],
            $datos['empresa'] ?? '',
            $datos['telefono'],
            $datos['email'] ?? '',
            $datos['direccion'],
            $datos['ciudad'] ?? ''
        )) {
            return [
                'success' => true,
                'message' => 'Cliente actualizado exitosamente'
            ];
        }

        throw new Exception("Error al actualizar el cliente");
    }

    public function eliminarCliente($id) {
        if (empty($id)) {
            throw new Exception("ID de cliente requerido");
        }

        // REGLA DE NEGOCIO: No se puede eliminar un cliente con visitas asignadas
        if ($this->clienteDAO->tieneVisitas($id)) {
            throw new Exception("No se puede eliminar el cliente porque tiene visitas asignadas");
        }

        if ($this->clienteDAO->eliminar($id)) {
            return [
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ];
        }

        throw new Exception("Error al eliminar el cliente");
    }

    public function obtenerTodosClientes() {
        return $this->clienteDAO->obtenerTodos();
    }

    public function obtenerClientePorId($id) {
        if (empty($id)) {
            throw new Exception("ID de cliente requerido");
        }

        $cliente = $this->clienteDAO->obtenerPorId($id);
        
        if (!$cliente) {
            throw new Exception("Cliente no encontrado");
        }

        return $cliente;
    }

    public function buscarClientes($termino) {
        if (strlen($termino) < 2) {
            throw new Exception("El término de búsqueda debe tener al menos 2 caracteres");
        }

        return $this->clienteDAO->buscar($termino);
    }
}
?>