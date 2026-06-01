<?php
class MiConexion extends PDO {
    
    public function __construct($host, $db, $user, $pass) {
        try {
            // Construimos el DSN internamente
            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            parent::__construct($dsn, $user, $pass);
            
            date_default_timezone_set('America/La_Paz');
            $this->exec("SET time_zone = '-04:00'");
            $this->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

            
            // Configuraciones de seguridad y comportamiento
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
            
        } catch (PDOException $e) {
            $this->mostrarErrorCritico($e->getMessage());
        }
    }

    // Un solo método para ejecutar cualquier consulta (SELECT, INSERT, UPDATE, DELETE)
    public function ejecutar($sql, $params = []) {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute((array)$params);
            return $stmt; // Retornamos el objeto para hacer fetch o contar filas
        } catch (PDOException $e) {
            $this->debugError("ejecutar", $e->getMessage(), $sql);
            return false;
        }
    }

    // Alias para compatibilidad con código existente
    public function ultimoInsertId() {
        return $this->lastInsertId();
    }

    // Método simple para obtener una sola fila (Como un SELECT de un ID)
    public function obtenerFila($sql, $params = []) {
        $stmt = $this->ejecutar($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }

    // Método para obtener todos los resultados (Para listas/tablas)
    public function obtenerTodo($sql, $params = []) {
        $stmt = $this->ejecutar($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    private function debugError($metodo, $mensaje, $sql = "") {
        if ($this->esAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => "ERROR EN $metodo: $mensaje",
                'sql' => $sql
            ]);
            exit;
        }
        echo "<div style='background:#fdd; color:#900; padding:12px; border:2px solid #a00; margin:10px; font-family:sans-serif;'>";
        echo "<b>ERROR EN $metodo:</b> $mensaje<br>";
        if (!empty($sql)) echo "<b>SQL:</b> <code style='background:#eee; padding:2px;'>" . htmlspecialchars($sql) . "</code>";
        echo "</div>";
    }

    private function mostrarErrorCritico($mensaje) {
        if ($this->esAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => "ERROR DE CONEXIÓN: $mensaje"
            ]);
            exit;
        }
        die("<div style='background:#fee; color:#a00; padding:15px; border:2px solid #a00;'><b>ERROR DE CONEXIÓN:</b> $mensaje</div>");
    }

    private function esAjax() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || 
               (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
               (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
               (headers_sent() === false && in_array('Content-Type: application/json', headers_list()));
    }
}

// --- Instancia de la conexión ---
$db = new MiConexion("127.0.0.1", "bd_hospedajes", "root", "");