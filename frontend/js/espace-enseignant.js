import { requeteJson, echapperHtml } from "./api.js";
import { chargerEntete } from "./entete.js";
import { titreOffre, formaterBudget } from "./format.js";

const vueProfil = document.querySelector("#vue-profil");
const vueEtudiants = document.querySelector("#vue-etudiants");
const formulaire = document.querySelector("#formulaire-profil");
const messageErreur = document.querySelector("#message-erreur");

/** Affiche une seule des deux vues : "profil" ou "etudiants". */
function afficherVue(nom) {
  vueProfil.hidden = nom !== "profil";
  vueEtudiants.hidden = nom !== "etudiants";
}

/** Remplit une grille de cases à cocher, en cochant celles déjà choisies. */
function remplirGrille(selecteur, nom, elements, idsCoches) {
  document.querySelector(selecteur).innerHTML = elements
    .map(
      (element) => `
        <label class="choix">
          <input type="checkbox" name="${nom}" value="${element.id}"
                 ${idsCoches.includes(element.id) ? "checked" : ""} />
          <span>${echapperHtml(element.libelle)}</span>
        </label>`,
    )
    .join("");
}

function idsCoches(nom) {
  return [...formulaire.querySelectorAll(`input[name="${nom}"]:checked`)].map((caseChoix) =>
    Number(caseChoix.value),
  );
}

function carteEtudiant(offre) {
  const parent = [offre.parent_prenom, offre.parent_nom].filter(Boolean).join(" ");
  return `
    <article class="carte carte-ligne">
      <div>
        <h3>${titreOffre(offre)}</h3>
        <p>Parent : ${echapperHtml(parent)} · ${echapperHtml(offre.ville)} — ${echapperHtml(offre.commune)} · ${formaterBudget(offre)}</p>
      </div>
      <a href="mailto:${echapperHtml(offre.parent_email)}" class="btn btn-secondaire">Contacter le parent</a>
    </article>`;
}

async function afficherEtudiants() {
  const etudiants = await requeteJson("/api/enseignant/etudiants");
  document.querySelector("#liste-etudiants").innerHTML = etudiants.map(carteEtudiant).join("");
  document.querySelector("#etat-vide").hidden = etudiants.length > 0;
  afficherVue("etudiants");
}

formulaire.addEventListener("submit", async (evenement) => {
  evenement.preventDefault();
  messageErreur.hidden = true;

  try {
    await requeteJson("/api/enseignant/profil", {
      method: "PUT",
      body: JSON.stringify({ matieres: idsCoches("matieres"), niveaux: idsCoches("niveaux") }),
    });
    await afficherEtudiants();
  } catch (erreur) {
    messageErreur.textContent = Object.values(erreur.details).join(" ") || erreur.message;
    messageErreur.hidden = false;
  }
});

document.querySelector("#bouton-modifier-profil").addEventListener("click", () => {
  document.querySelector("#titre-profil").textContent = "Mon profil";
  afficherVue("profil");
});

// Au chargement
const utilisateur = await chargerEntete("enseignant", 1);

if (utilisateur) {
  const [profil, matieres, niveaux] = await Promise.all([
    requeteJson("/api/enseignant/profil"),
    requeteJson("/api/matieres"),
    requeteJson("/api/niveaux"),
  ]);

  remplirGrille("#grille-matieres", "matieres", matieres, profil.matieres);
  remplirGrille("#grille-niveaux", "niveaux", niveaux, profil.niveaux);
  document.querySelector("#titre-profil").textContent =
    `Bienvenue, ${utilisateur.prenom || utilisateur.nom} !`;

  // Nouvel enseignant (profil vide) : écran 14 ; sinon : écran 15
  if (profil.matieres.length === 0) {
    afficherVue("profil");
  } else {
    await afficherEtudiants();
  }
}
