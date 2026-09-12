<?php

header('Content-Type: application/json');
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

    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        'UPDATE tasks SET title = :title, done = :done WHERE id = :id RETURNING *'
    );
    $stmt->execute([
        'id' => (int) $matches[1],
        'title' => trim($data['title']),
        'done' => filter_var($data['is_done'], FILTER_VALIDATE_BOOLEAN),
    ]);
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