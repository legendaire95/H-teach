<?php

/**
 * Crée un compte administrateur (la structure).
 * Usage : php backend/scripts/creer-admin.php "Nom" email motdepasse
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if ($argc !== 4) {
    fwrite(STDERR, 'Usage : php backend/scripts/creer-admin.php "Nom" email motdepasse' . PHP_EOL);
    exit(1);
}

[, $nom, $email, $motDePasse] = $argv;
$email = strtolower(trim($email));

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($motDePasse) < 8) {
    fwrite(STDERR, 'Email invalide ou mot de passe de moins de 8 caractères.' . PHP_EOL);
    exit(1);
}

$requete = connexionBdd()->prepare(
    "INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, 'admin')"
);
$requete->execute([$nom, $email, password_hash($motDePasse, PASSWORD_DEFAULT)]);

echo "Compte administrateur créé : $email" . PHP_EOL;
