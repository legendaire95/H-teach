import { requeteJson, PAGE_PAR_ROLE } from "./api.js";

const formulaire = document.querySelector("#formulaire-connexion");
const messageErreur = document.querySelector("#message-erreur");

/** Si l'utilisateur est déjà connecté, on l'envoie directement vers son espace. */
async function redirigerSiDejaConnecte() {
  try {
    const utilisateur = await requeteJson("/api/moi");
    window.location.href = PAGE_PAR_ROLE[utilisateur.role];
  } catch {
    // Personne n'est connecté (erreur 401) : on reste sur la page de connexion
  }
}

formulaire.addEventListener("submit", async (evenement) => {
  evenement.preventDefault();
  messageErreur.hidden = true;

  const champs = formulaire.elements;

  try {
    const utilisateur = await requeteJson("/api/connexion", {
      method: "POST",
      body: JSON.stringify({
        email: champs.email.value,
        mot_de_passe: champs.mot_de_passe.value,
      }),
    });

    window.location.href = PAGE_PAR_ROLE[utilisateur.role];
  } catch (erreur) {
    messageErreur.textContent = erreur.message;
    messageErreur.hidden = false;
    champs.mot_de_passe.value = "";
  }
});

redirigerSiDejaConnecte();
