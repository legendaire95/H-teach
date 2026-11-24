<?php

declare(strict_types=1);

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
require_once __DIR__ . '/../src/session.php';
require_once __DIR__ . '/../src/controllers/referentiels.php';
require_once __DIR__ . '/../src/controllers/auth.php';
require_once __DIR__ . '/../src/controllers/parent.php';
require_once __DIR__ . '/../src/controllers/enseignant.php';
require_once __DIR__ . '/../src/controllers/admin.php';

$route = $_SERVER['REQUEST_METHOD'] . ' ' . $chemin;

try {
    demarrerSession();

    match ($route) {
        // Référentiels (listes de choix)
        'GET /api/niveaux'  => listerNiveaux(),
        'GET /api/matieres' => listerMatieres(),

        // Comptes et session
        'POST /api/inscription' => inscrire(),
        'POST /api/connexion'   => connecter(),
        'GET /api/moi'          => moi(),
        'POST /api/deconnexion' => deconnecter(),

        // Parent
        'GET /api/parent/offres' => listerOffresParent(),
        'POST /api/offres'       => creerOffre(),

        // Enseignant
        'GET /api/enseignant/profil'    => lireProfilEnseignant(),
        'PUT /api/enseignant/profil'    => enregistrerProfilEnseignant(),
        'GET /api/enseignant/etudiants' => listerEtudiants(),
        'GET /api/enseignant/offres'    => listerOffresDisponibles(),
        'POST /api/candidatures'        => postuler(),

        // Structure (administrateur)
        'GET /api/admin/offres'        => listerOffresAAttribuer(),
        'GET /api/admin/candidats'     => listerCandidats(),
        'POST /api/admin/attributions' => attribuerOffre(),
        'GET /api/admin/attributions'  => listerAttributions(),

        default => repondreJson(['erreur' => 'Route introuvable'], 404),
    };
} catch (Throwable $erreur) {
    // Visible dans les journaux (logs) de Vercel en cas de problème
    error_log('H-teach : ' . $erreur->getMessage());
    repondreJson(['erreur' => 'Erreur interne du serveur'], 500);
}
