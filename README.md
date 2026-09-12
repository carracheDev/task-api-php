# API REST — Gestion de Tâches

API REST en PHP natif (sans framework) permettant de gérer une liste de tâches, avec PostgreSQL comme base de données.

## Objectif

Démontrer la maîtrise des fondamentaux PHP et REST sans dépendre d'un framework : routing manuel, requêtes préparées PDO, gestion des codes HTTP.

## Stack technique

- PHP 8.3 (serveur intégré)
- PostgreSQL
- PDO (requêtes préparées, protection contre les injections SQL)

## Prérequis

- PHP >= 8.1 avec l'extension `pdo_pgsql`
- PostgreSQL installé et démarré

## Installation

```bash
git clone <url-du-repo>
cd 01-api-taches/backend

# Créer la base de données
sudo -u postgres psql -c "CREATE DATABASE taches_test;"

# Créer la table
sudo -u postgres psql -d taches_test -f schema.sql
```

Configurer les identifiants dans `config/database.php` si besoin (hôte, utilisateur, mot de passe).

## Lancer le serveur

```bash
php -S localhost:8080 index.php
```

L'API est disponible sur `http://localhost:8080`.

## Endpoints

| Méthode | Route         | Description              |
|---------|---------------|--------------------------|
| GET     | `/tasks`      | Liste toutes les tâches  |
| POST    | `/tasks`      | Crée une tâche           |
| PUT     | `/tasks/{id}` | Modifie une tâche        |
| DELETE  | `/tasks/{id}` | Supprime une tâche       |

### Exemples

**Lister les tâches**
```bash
curl http://localhost:8080/tasks
```

**Créer une tâche**
```bash
curl -X POST http://localhost:8080/tasks \
  -H "Content-Type: application/json" \
  -d '{"title": "Nouvelle tâche", "description": "Description optionnelle"}'
```

**Modifier une tâche**
```bash
curl -X PUT http://localhost:8080/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"title": "Titre modifié", "is_done": true}'
```

**Supprimer une tâche**
```bash
curl -X DELETE http://localhost:8080/tasks/1
```

## Points techniques notables

- Architecture *front controller* : toutes les requêtes passent par `index.php`, qui route manuellement selon la méthode HTTP et l'URI.
- Requêtes préparées PDO (`prepare()` / `execute()`) pour éviter toute injection SQL.
- Utilisation de `RETURNING *` (spécifique PostgreSQL) pour récupérer la ligne modifiée sans requête supplémentaire.
- Codes de statut HTTP corrects : `200`, `201`, `204`, `400`, `404`.

## Auteur

SODJINOU Jésukpégo Carrache
# task-api-php
