import { requeteJson, PAGE_PAR_ROLE } from "./api.js";

const formulaire = document.querySelector("#formulaire-inscription");
const champRole = formulaire.elements.role;
const onglets = document.querySelectorAll("[data-role]");
const messageErreur = document.querySelector("#message-erreur");

/** Affiche le formulaire du profil choisi (parent ou enseignant). */
function choisirRole(role) {
  champRole.value = role;

  onglets.forEach((onglet) => {
    const actif = onglet.dataset.role === role;
    onglet.classList.toggle("actif", actif);
    onglet.setAttribute("aria-selected", String(actif));
  });

  document.querySelectorAll("[data-pour]").forEach((bloc) => {
    const visible = bloc.dataset.pour === role;
    bloc.hidden = !visible;
    bloc.disabled = !visible;
  });

  afficherErreurs({});
  messageErreur.hidden = true;
}

/** Affiche sous chaque champ le message d'erreur renvoyé par le serveur. */
function afficherErreurs(erreurs) {
  document.querySelectorAll("[data-erreur]").forEach((zone) => {
    zone.textContent = erreurs[zone.dataset.erreur] || "";
  });
}

onglets.forEach((onglet) => {
  onglet.addEventListener("click", () => choisirRole(onglet.dataset.role));
});

formulaire.addEventListener("submit", async (evenement) => {
  evenement.preventDefault();
  afficherErreurs({});
  messageErreur.hidden = true;

  const champs = formulaire.elements;
  const role = champRole.value;
  const donnees = {
    role,
    email: champs.email.value,
    mot_de_passe: champs.mot_de_passe.value,
  };

  if (role === "parent") {
    donnees.nom = champs.nom.value;
    donnees.prenom = champs.prenom.value;
  } else {
    donnees.nom = champs.nom_complet.value;
    donnees.experience = champs.experience.value;
    donnees.niveau_etudes = champs.niveau_etudes.value;
  }

  try {
    await requeteJson("/api/inscription", {
      method: "POST",
      body: JSON.stringify(donnees),
    });

    // Compte créé : on connecte directement l'utilisateur
    const utilisateur = await requeteJson("/api/connexion", {
      method: "POST",
      body: JSON.stringify({ email: donnees.email, mot_de_passe: donnees.mot_de_passe }),
    });

    // Un nouveau parent découvre d'abord l'accueil et le tutoriel (écrans 05 à 07)
    window.location.href =
      utilisateur.role === "parent" ? "bienvenue-parent.html" : PAGE_PAR_ROLE[utilisateur.role];
  } catch (erreur) {
    afficherErreurs(erreur.details);
    messageErreur.textContent = erreur.message;
    messageErreur.hidden = false;
  }
});
