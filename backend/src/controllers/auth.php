<?php

declare(strict_types=1);

/**
 * POST /api/inscription : crée un compte parent ou enseignant (écrans 03 et 04).
 */
function inscrire(): void
{
    $donnees = lireCorpsJson();

    $role = $donnees['role'] ?? '';
    $nom = trim((string) ($donnees['nom'] ?? ''));
    $prenom = trim((string) ($donnees['prenom'] ?? ''));
    $email = strtolower(trim((string) ($donnees['email'] ?? '')));
    $motDePasse = (string) ($donnees['mot_de_passe'] ?? '');
    $experience = $donnees['experience'] ?? null;
    $niveauEtudes = $donnees['niveau_etudes'] ?? null;

    // 1. Vérification des données
    $erreurs = [];

    if (!in_array($role, ['parent', 'enseignant'], true)) {
        $erreurs['role'] = 'Le profil doit être « parent » ou « enseignant ».';
    }
    if ($nom === '') {
        $erreurs['nom'] = 'Le nom est obligatoire.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = "L'adresse email n'est pas valide.";
    }
    if (strlen($motDePasse) < 8) {
        $erreurs['mot_de_passe'] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }

    if ($role === 'enseignant') {
        if (!in_array($experience, ['moins_1_an', '1_3_ans', '3_5_ans', 'plus_5_ans'], true)) {
            $erreurs['experience'] = "Choisissez vos années d'expérience.";
        }
        if (!in_array($niveauEtudes, ['bac', 'licence', 'master', 'doctorat'], true)) {
            $erreurs['niveau_etudes'] = "Choisissez votre niveau d'études.";
        }
    } else {
        $experience = null;
        $niveauEtudes = null;
    }

    if ($erreurs !== []) {
        repondreJson(['erreurs' => $erreurs], 422);
        return;
    }

    // 2. L'email est-il déjà utilisé ?
    $pdo = connexionBdd();

    $verification = $pdo->prepare('SELECT 1 FROM utilisateurs WHERE email = ?');
    $verification->execute([$email]);

    if ($verification->fetchColumn()) {
        repondreJson(['erreurs' => ['email' => 'Un compte existe déjà avec cet email.']], 409);
        return;
    }

    // 3. Enregistrement, avec le mot de passe chiffré
    $requete = $pdo->prepare(
        'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, experience, niveau_etudes)
         VALUES (:nom, :prenom, :email, :mot_de_passe, :role, :experience, :niveau_etudes)'
    );
    $requete->execute([
        'nom'           => $nom,
        'prenom'        => $prenom !== '' ? $prenom : null,
        'email'         => $email,
        'mot_de_passe'  => password_hash($motDePasse, PASSWORD_DEFAULT),
        'role'          => $role,
        'experience'    => $experience,
        'niveau_etudes' => $niveauEtudes,
    ]);

    repondreJson([
        'id'     => (int) $pdo->lastInsertId(),
        'nom'    => $nom,
        'prenom' => $prenom !== '' ? $prenom : null,
        'email'  => $email,
        'role'   => $role,
    ], 201);
}

/**
 * POST /api/connexion : vérifie l'email et le mot de passe, puis ouvre la session.
 */
function connecter(): void
{
    $donnees = lireCorpsJson();
    $email = strtolower(trim((string) ($donnees['email'] ?? '')));
    $motDePasse = (string) ($donnees['mot_de_passe'] ?? '');

    $requete = connexionBdd()->prepare(
        'SELECT id, nom, prenom, email, mot_de_passe, role FROM utilisateurs WHERE email = ?'
    );
    $requete->execute([$email]);
    $utilisateur = $requete->fetch();

    if (!$utilisateur || !password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
        repondreJson(['erreur' => 'Email ou mot de passe incorrect.'], 401);
        return;
    }

    unset($utilisateur['mot_de_passe']);
    $utilisateur['id'] = (int) $utilisateur['id'];

    session_regenerate_id(true);
    $_SESSION['utilisateur'] = $utilisateur;

    repondreJson($utilisateur);
}

/**
 * GET /api/moi : renvoie l'utilisateur connecté (sert au front-end à savoir qui est connecté).
 */
function moi(): void
{
    repondreJson(exigerConnexion());
}

/**
 * POST /api/deconnexion : ferme la session.
 */
function deconnecter(): void
{
    $_SESSION = [];
    session_destroy();

    repondreJson(['message' => 'Déconnexion réussie.']);
}

/**
 * Renvoie l'utilisateur connecté. Si personne n'est connecté, ou si son rôle
 * n'est pas autorisé, répond par une erreur et arrête le script.
 */
function exigerConnexion(string ...$rolesAutorises): array
{
    $utilisateur = $_SESSION['utilisateur'] ?? null;

    if ($utilisateur === null) {
        repondreJson(['erreur' => 'Vous devez être connecté.'], 401);
        exit;
    }
    if ($rolesAutorises !== [] && !in_array($utilisateur['role'], $rolesAutorises, true)) {
        repondreJson(['erreur' => "Vous n'avez pas accès à cette action."], 403);
        exit;
    }

    return $utilisateur;
}
