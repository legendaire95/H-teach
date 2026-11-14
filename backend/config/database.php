<?php

declare(strict_types=1);

/**
 * Lit le fichier .env et range chaque variable dans $_ENV.
 * Format attendu : une variable par ligne, CLE=valeur. Les lignes commençant par # sont ignorées.
 */
function chargerEnv(string $chemin): void
{
    if (!is_file($chemin)) {
        throw new RuntimeException("Fichier .env introuvable : $chemin");
    }

    $lignes = file($chemin, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lignes as $ligne) {
        $ligne = trim($ligne);

        if ($ligne === '' || str_starts_with($ligne, '#')) {
            continue;
        }

        [$cle, $valeur] = array_pad(explode('=', $ligne, 2), 2, '');
        $_ENV[trim($cle)] = trim($valeur);
    }
}

/**
 * Renvoie la connexion à la base MySQL.
 * La connexion n'est ouverte qu'une seule fois, puis réutilisée.
 */
function connexionBdd(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    chargerEnv(__DIR__ . '/../../.env');

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_PORT'] ?? '3306',
        $_ENV['DB_NAME'] ?? 'hteach'
    );

    $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASSWORD'] ?? '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}
