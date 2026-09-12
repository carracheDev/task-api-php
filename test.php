<?php

$title =  "Ma tâche";
$isDone = false;
$price = 12.5;

if($isDone){
    echo "Terminé";
}elseif ($title === ""){
    echo "Titre vide";
}else {
    echo "En cours";
}

$tasks = ["Coder", "Tester", "Déployer"];
foreach ($tasks as $task){
    echo $task ."\n";
}

$simple = ["a", "b", "c"];
$assoc = ["title" => "Coder", "done" => false];

function formatTask(array $task): array {
    $task['title'] = trim($task['title']);
    return $task;
}




try {
    $pdo = new PDO(
        "pgsql:host=127.0.0.1;port=5432;dbname=taches_test",
        "postgres",
        "motdepasse123"
    );
    echo "Connexion à PostgreSQL réussie !";
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}


// POST /tasks — créer une tâche
if ($uri === '/tasks' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['title'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Le champ title est requis']);
        exit;
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO tasks (title, description) VALUES (:title, :description) RETURNING *'
    );
    $stmt->execute([
        'title' => $data['title'],
        'description' => $data['description'] ?? null,
    ]);
    $newTask = $stmt->fetch(PDO::FETCH_ASSOC);

    http_response_code(201);
    echo json_encode($newTask);
    exit;
}

// PUT /tasks/{id} — modifier une tâche
if (preg_match('#^/tasks/(\d+)$#', $uri, $matches) && $method === 'PUT') {
    $id = (int) $matches[1];
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['title'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Le champ title est requis']);
        exit;
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        'UPDATE tasks SET title = :title, description = :description, is_done = :is_done WHERE id = :id RETURNING *'
    );
    $stmt->execute([
        'title' => $data['title'],
        'description' => $data['description'] ?? null,
        'is_done' => $data['is_done'] ?? false,
        'id' => $id,
    ]);
    $updatedTask = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$updatedTask) {
        http_response_code(404);
        echo json_encode(['error' => 'Tâche introuvable']);
        exit;
    }

    http_response_code(200);
    echo json_encode($updatedTask);
    exit;
}

// DELETE /tasks/{id} — supprimer une tâche
if (preg_match('#^/tasks/(\d+)$#', $uri, $matches) && $method === 'DELETE') {
    $id = (int) $matches[1];

    $pdo = getDbConnection();
    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id RETURNING id');
    $stmt->execute(['id' => $id]);
    $deleted = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deleted) {
        http_response_code(404);
        echo json_encode(['error' => 'Tâche introuvable']);
        exit;
    }

    http_response_code(204);
    exit;
}