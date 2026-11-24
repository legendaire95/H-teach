<?php

declare(strict_types=1);

/**
 * Lit le fichier .env et range chaque variable dans $_ENV.
 * Format attendu : une variable par ligne, CLE=valeur. Les lignes commençant par # sont ignorées.
 */
function chargerEnv(string $chemin): void
{
    // Sur Vercel il n'y a pas de fichier .env : les variables sont définies dans le tableau de bord
    if (!is_file($chemin)) {
        return;
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
 * Renvoie la valeur d'une variable de configuration :
 * d'abord le fichier .env (en local), sinon les variables d'environnement du serveur (Vercel).
 */
function config(string $cle, string $defaut = ''): string
{
    if (isset($_ENV[$cle]) && $_ENV[$cle] !== '') {
        return $_ENV[$cle];
    }

    $valeur = getenv($cle);

    return $valeur === false || $valeur === '' ? $defaut : $valeur;
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
        config('DB_HOST', 'localhost'),
        config('DB_PORT', '3306'),
        config('DB_NAME', 'hteach')
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Base en ligne (TiDB Cloud) : la connexion doit être chiffrée (SSL)
    if (config('DB_SSL') === 'true') {
        $options[PDO::MYSQL_ATTR_SSL_CA] = config('DB_SSL_CA', '/etc/pki/tls/certs/ca-bundle.crt');
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }

    $pdo = new PDO($dsn, config('DB_USER', 'root'), config('DB_PASSWORD'), $options);

    return $pdo;
}
