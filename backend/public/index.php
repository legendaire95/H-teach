<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/controllers/auth.php';
require_once __DIR__ . '/../src/controllers/parent.php';

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

$chemin = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Ce n'est pas un appel à l'API : le serveur PHP envoie le fichier du front-end
if (!str_starts_with($chemin, '/api/')) {
    if ($chemin === '/') {
        header('Location: /pages/index.html');
        exit;
    }
    return false;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/http.php';
require_once __DIR__ . '/../src/controllers/referentiels.php';
require_once __DIR__ . '/../src/controllers/enseignant.php';

$route = $_SERVER['REQUEST_METHOD'] . ' ' . $chemin;

try {
    match ($route) {
        'GET /api/niveaux'  => listerNiveaux(),
        'GET /api/matieres' => listerMatieres(),
        'POST /api/inscription' => inscrire(),

        'POST /api/connexion'   => connecter(),
        'GET /api/moi'          => moi(),
        'POST /api/deconnexion' => deconnecter(),

        'GET /api/parent/offres' => listerOffresParent(),
        'POST /api/offres' => creerOffre(),

        'GET /api/enseignant/profil'    => lireProfilEnseignant(),
        'PUT /api/enseignant/profil'    => enregistrerProfilEnseignant(),
        'GET /api/enseignant/etudiants' => listerEtudiants(),

        'GET /api/enseignant/offres' => listerOffresDisponibles(),
        'POST /api/candidatures'     => postuler(),
        default             => repondreJson(['erreur' => 'Route introuvable'], 404),
    };
} catch (Throwable $e) {
    repondreJson(['erreur' => 'Erreur interne du serveur'], 500);
}
