import { requeteJson, echapperHtml, PAGE_PAR_ROLE } from "./api.js";

/** Onglets de chaque profil (maquettes : écrans 05, 14 et 17). */
const ONGLETS = {
  parent: [
    { texte: "Mes répétiteurs", lien: "espace-parent.html" },
    { texte: "Trouver un répétiteur", lien: "nouvelle-offre.html" },
  ],
  enseignant: [
    { texte: "Mes étudiants", lien: "espace-enseignant.html" },
    { texte: "Offres disponibles", lien: "offres-disponibles.html" },
  ],
  admin: [
    { texte: "Offres à attribuer", lien: "espace-admin.html" },
    { texte: "Offres attribuées", lien: "offres-attribuees.html" },
  ],
};

const LIBELLE_ROLE = { parent: "Parent", enseignant: "Enseignant", admin: "Administrateur" };

/**
 * Vérifie que l'utilisateur est connecté avec le bon profil, puis affiche l'en-tête.
 * Renvoie l'utilisateur connecté.
 */
export async function chargerEntete(roleAttendu, numeroOngletActif) {
  let utilisateur;

  try {
    utilisateur = await requeteJson("/api/moi");
  } catch {
    window.location.href = "connexion.html";
    return null;
  }

  if (utilisateur.role !== roleAttendu) {
    window.location.href = PAGE_PAR_ROLE[utilisateur.role];
    return null;
  }

  const nomAffiche = [utilisateur.prenom, utilisateur.nom].filter(Boolean).join(" ");

  const onglets = ONGLETS[roleAttendu]
    .map((onglet, index) => {
      const actif = index + 1 === numeroOngletActif ? " actif" : "";
      return `<a href="${onglet.lien}" class="onglet-entete${actif}">
                <span class="numero">${index + 1}</span> ${onglet.texte}
              </a>`;
    })
    .join("");

  document.querySelector("#entete").innerHTML = `
    <div class="identite">
      <div class="avatar">${echapperHtml(nomAffiche.charAt(0).toUpperCase())}</div>
      <div>
        <div class="nom-utilisateur">${echapperHtml(nomAffiche)}</div>
        <div class="role-utilisateur">${LIBELLE_ROLE[utilisateur.role]}</div>
      </div>
    </div>
    <nav class="onglets-entete">${onglets}</nav>
    <button type="button" id="bouton-deconnexion" class="btn btn-secondaire">Déconnexion</button>
  `;

  document.querySelector("#bouton-deconnexion").addEventListener("click", async () => {
    await requeteJson("/api/deconnexion", { method: "POST" });
    window.location.href = "index.html";
  });

  return utilisateur;
}
