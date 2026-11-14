<?php

declare(strict_types=1);

/**
 * Envoie une réponse au format JSON avec le code HTTP indiqué.
 */
function repondreJson(mixed $donnees, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
}

/**
 * Lit le corps de la requête (envoyé en JSON par fetch) et le renvoie sous forme de tableau.
 */
function lireCorpsJson(): array
{
    $donnees = json_decode(file_get_contents('php://input') ?: '', true);

    return is_array($donnees) ? $donnees : [];
}
