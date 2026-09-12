<?php

$allowedOrigins = [
    'http://localhost:3000', 'http://127.0.0.1:3000',
    'http://localhost:3001', 'http://127.0.0.1:3001',
    'http://localhost:3002', 'http://127.0.0.1:3002',
    'http://localhost:3003', 'http://127.0.0.1:3003',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Vary: Origin');
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/tasks' && $method === 'GET') {
    $pdo = getDbConnection();
    $stmt = $pdo->query('SELECT * FROM tasks ORDER BY id');
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($tasks);
    exit;
}

if ($uri === '/tasks' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data) || empty(trim($data['title'] ?? ''))) {
        http_response_code(400);
        echo json_encode(['error' => 'Le titre est obligatoire']);
        exit;
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO tasks (title, description) VALUES (:title, :description) RETURNING *'
    );
    $stmt->execute([
        'title' => trim($data['title']),
        'description' => trim($data['description'] ?? ''),
    ]);

    http_response_code(201);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}

if ($method === 'PUT' && preg_match('#^/tasks/([1-9][0-9]*)$#', $uri, $matches)) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data) || empty(trim($data['title'] ?? '')) || !array_key_exists('is_done', $data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Le titre et is_done sont obligatoires']);
        exit;
    }

    $done = filter_var($data['is_done'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($done === null) {
        http_response_code(400);
        echo json_encode(['error' => 'is_done doit être un booléen']);
        exit;
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        'UPDATE tasks SET title = :title, description = :description, done = CAST(:done AS BOOLEAN) WHERE id = :id'
    );
    $stmt->execute([
        'id' => (int) $matches[1],
        'title' => trim($data['title']),
        'description' => trim($data['description'] ?? ''),
        'done' => $done ? 'true' : 'false',
    ]);

    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = :id');
    $stmt->execute(['id' => (int) $matches[1]]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($task === false) {
        http_response_code(404);
        echo json_encode(['error' => 'Tâche non trouvée']);
        exit;
    }

    http_response_code(200);
    echo json_encode($task);
    exit;
}

if ($method === 'DELETE' && preg_match('#^/tasks/([1-9][0-9]*)$#', $uri, $matches)) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id RETURNING id');
    $stmt->execute(['id' => (int) $matches[1]]);

    if ($stmt->fetchColumn() === false) {
        http_response_code(404);
        echo json_encode(['error' => 'Tâche non trouvée']);
        exit;
    }

    http_response_code(204);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Route non trouvée']);