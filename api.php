<?php
/**
 * CONTROLADOR API - Punto de entrada
 * Conecta la capa de presentación con la capa de negocio
 */
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluir dependencias
require_once __DIR__ . '/persistencia/Database.php';
require_once __DIR__ . '/negocio/VisitaNegocio.php';
require_once __DIR__ . '/negocio/TecnicoNegocio.php';
require_once __DIR__ . '/negocio/ClienteNegocio.php';

// Función para enviar respuesta JSON
function sendResponse($status, $data) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Manejo de errores global
try {
    // Obtener conexión a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    // Inicializar capas de negocio
    $visitaNegocio = new VisitaNegocio($db);
    $tecnicoNegocio = new TecnicoNegocio($db);
    $clienteNegocio = new ClienteNegocio($db);

    $method = $_SERVER['REQUEST_METHOD'];

    // Obtener datos JSON del body
    $input = json_decode(file_get_contents('php://input'), true);

    // ========== MANEJO DE PETICIONES GET ==========
    if ($method === 'GET') {
        if (!isset($_GET['action'])) {
            sendResponse(400, ['success' => false, 'message' => 'Acción no especificada']);
        }

        $action = $_GET['action'];

        switch($action) {
            // ============ VISITAS ============
            case 'visitas':
                $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
                $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d', strtotime('+7 days'));
                
                $visitas = $visitaNegocio->obtenerVisitasPorRango($fecha_inicio, $fecha_fin);
                sendResponse(200, ['success' => true, 'data' => $visitas]);
                break;

            case 'estadisticas':
                $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
                $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d', strtotime('+7 days'));
                
                $stats = $visitaNegocio->obtenerEstadisticasTecnicos($fecha_inicio, $fecha_fin);
                sendResponse(200, ['success' => true, 'data' => $stats]);
                break;

            // ============ TÉCNICOS ============
            case 'tecnicos':
                $tecnicos = $tecnicoNegocio->obtenerTecnicosActivos();
                sendResponse(200, ['success' => true, 'data' => $tecnicos]);
                break;

            case 'tecnicos_todos':
                $tecnicos = $tecnicoNegocio->obtenerTodosTecnicos();
                sendResponse(200, ['success' => true, 'data' => $tecnicos]);
                break;

            case 'tecnico':
                if (!isset($_GET['id'])) {
                    sendResponse(400, ['success' => false, 'message' => 'ID no especificado']);
                }
                $tecnico = $tecnicoNegocio->obtenerTecnicoPorId($_GET['id']);
                sendResponse(200, ['success' => true, 'data' => $tecnico]);
                break;

            // ============ CLIENTES ============
            case 'clientes':
                $clientes = $clienteNegocio->obtenerTodosClientes();
                sendResponse(200, ['success' => true, 'data' => $clientes]);
                break;

            case 'cliente':
                if (!isset($_GET['id'])) {
                    sendResponse(400, ['success' => false, 'message' => 'ID no especificado']);
                }
                $cliente = $clienteNegocio->obtenerClientePorId($_GET['id']);
                sendResponse(200, ['success' => true, 'data' => $cliente]);
                break;

            case 'buscar_clientes':
                $termino = $_GET['termino'] ?? '';
                $clientes = $clienteNegocio->buscarClientes($termino);
                sendResponse(200, ['success' => true, 'data' => $clientes]);
                break;

            default:
                sendResponse(400, ['success' => false, 'message' => 'Acción no reconocida']);
        }
    }

    // ========== MANEJO DE PETICIONES POST ==========
    elseif ($method === 'POST') {
        if (!isset($input['tipo'])) {
            sendResponse(400, ['success' => false, 'message' => 'Tipo no especificado']);
        }

        $tipo = $input['tipo'];

        switch($tipo) {
            case 'visita':
                $resultado = $visitaNegocio->crearVisita($input);
                sendResponse(201, $resultado);
                break;

            case 'tecnico':
                $resultado = $tecnicoNegocio->crearTecnico($input);
                sendResponse(201, $resultado);
                break;

            case 'cliente':
                $resultado = $clienteNegocio->crearCliente($input);
                sendResponse(201, $resultado);
                break;

            default:
                sendResponse(400, ['success' => false, 'message' => 'Tipo no reconocido']);
        }
    }

    // ========== MANEJO DE PETICIONES PUT ==========
    elseif ($method === 'PUT') {
        if (!isset($input['tipo'])) {
            sendResponse(400, ['success' => false, 'message' => 'Tipo no especificado']);
        }

        $tipo = $input['tipo'];

        switch($tipo) {
            case 'visita':
                $resultado = $visitaNegocio->actualizarVisita($input);
                sendResponse(200, $resultado);
                break;

            case 'tecnico':
                $resultado = $tecnicoNegocio->actualizarTecnico($input);
                sendResponse(200, $resultado);
                break;

            case 'cliente':
                $resultado = $clienteNegocio->actualizarCliente($input);
                sendResponse(200, $resultado);
                break;

            default:
                sendResponse(400, ['success' => false, 'message' => 'Tipo no reconocido']);
        }
    }

    // ========== MANEJO DE PETICIONES DELETE ==========
    elseif ($method === 'DELETE') {
        if (!isset($input['tipo'])) {
            sendResponse(400, ['success' => false, 'message' => 'Tipo no especificado']);
        }

        $tipo = $input['tipo'];
        $accion = $input['accion'] ?? 'default';

        switch($tipo) {
            case 'visita':
                if ($accion === 'eliminar_permanente') {
                    $resultado = $visitaNegocio->eliminarVisitaPermanente($input['id']);
                } else {
                    $resultado = $visitaNegocio->cancelarVisita($input['id']);
                }
                sendResponse(200, $resultado);
                break;

            case 'tecnico':
                $resultado = $tecnicoNegocio->desactivarTecnico($input['id']);
                sendResponse(200, $resultado);
                break;

            case 'cliente':
                $resultado = $clienteNegocio->eliminarCliente($input['id']);
                sendResponse(200, $resultado);
                break;

            default:
                sendResponse(400, ['success' => false, 'message' => 'Tipo no reconocido']);
        }
    }

    // ========== MÉTODO NO PERMITIDO ==========
    else {
        sendResponse(405, ['success' => false, 'message' => 'Método no permitido']);
    }

} catch (Exception $e) {
    // Manejo de errores de negocio
    sendResponse(400, [
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    // Manejo de errores fatales
    sendResponse(500, [
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>