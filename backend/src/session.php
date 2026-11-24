<?php

declare(strict_types=1);

/**
 * Sessions enregistrées dans la base de données (table `sessions`).
 *
 * Sur Vercel, chaque requête peut être traitée par une instance différente du serveur
 * et les fichiers ne sont pas conservés : les sessions PHP classiques (fichiers) seraient
 * perdues et l'utilisateur serait déconnecté sans cesse. La base, elle, est partagée.
 */
// Le projet n'utilise pas d'espaces de noms (namespaces) : règle PSR-1 désactivée pour cette classe
// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
final class SessionBdd implements SessionHandlerInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function open(string $chemin, string $nom): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        $requete = $this->pdo->prepare('SELECT donnees FROM sessions WHERE id = :id');
        $requete->execute(['id' => $id]);

        return (string) ($requete->fetchColumn() ?: '');
    }

    public function write(string $id, string $donnees): bool
    {
        $requete = $this->pdo->prepare(
            'INSERT INTO sessions (id, donnees, maj_le) VALUES (:id, :d1, :t1)
             ON DUPLICATE KEY UPDATE donnees = :d2, maj_le = :t2'
        );

        return $requete->execute([
            'id' => $id,
            'd1' => $donnees,
            't1' => time(),
            'd2' => $donnees,
            't2' => time(),
        ]);
    }

    public function destroy(string $id): bool
    {
        return $this->pdo->prepare('DELETE FROM sessions WHERE id = :id')->execute(['id' => $id]);
    }

    public function gc(int $dureeMax): int|false
    {
        $requete = $this->pdo->prepare('DELETE FROM sessions WHERE maj_le < :limite');
        $requete->execute(['limite' => time() - $dureeMax]);

        return $requete->rowCount();
    }
}

/**
 * Démarre la session de l'utilisateur, stockée en base.
 */
function demarrerSession(): void
{
    session_set_save_handler(new SessionBdd(connexionBdd()), true);

    $https = ($_SERVER['HTTPS'] ?? '') === 'on'
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure'   => $https,
    ]);
}
