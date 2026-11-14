/**
 * Appelle l'API et renvoie la réponse JSON.
 * En cas d'erreur HTTP (4xx, 5xx), lève une erreur qui contient le message et le détail par champ.
 */
export async function requeteJson(url, options = {}) {
  const reponse = await fetch(url, {
    headers: { "Content-Type": "application/json" },
    ...options,
  });

  const donnees = await reponse.json().catch(() => ({}));

  if (!reponse.ok) {
    const erreur = new Error(donnees.erreur || "Vérifiez les champs signalés.");
    erreur.statut = reponse.status;
    erreur.details = donnees.erreurs || {};
    throw erreur;
  }

  return donnees;
}

/** Page vers laquelle envoyer chaque profil après la connexion. */
export const PAGE_PAR_ROLE = {
  parent: "espace-parent.html",
  enseignant: "espace-enseignant.html",
  admin: "espace-admin.html",
};
