import { requeteJson, echapperHtml } from "./api.js";
import { chargerEntete } from "./entete.js";
import { titreOffre, formaterBudget, nomComplet } from "./format.js";

function carteRepetiteur(offre) {
  const enseignant = nomComplet(offre.enseignant_prenom, offre.enseignant_nom);
  return `
    <article class="carte carte-ligne">
      <div>
        <h3>${echapperHtml(enseignant)}</h3>
        <p>${titreOffre(offre)} · ${echapperHtml(offre.commune)}</p>
      </div>
      <a href="mailto:${echapperHtml(offre.enseignant_email)}" class="btn btn-secondaire">Contacter</a>
    </article>`;
}

function carteAttente(offre) {
  return `
    <article class="carte carte-ligne">
      <div>
        <h3>${titreOffre(offre)}</h3>
        <p>${echapperHtml(offre.ville)} — ${echapperHtml(offre.commune)} · ${formaterBudget(offre)}</p>
      </div>
      <span class="badge">En attente</span>
    </article>`;
}

const utilisateur = await chargerEntete("parent", 1);

if (utilisateur) {
  const offres = await requeteJson("/api/parent/offres");
  const attribuees = offres.filter((offre) => offre.statut === "attribuee");
  const enAttente = offres.filter((offre) => offre.statut === "ouverte");

  document.querySelector("#liste-repetiteurs").innerHTML = attribuees.map(carteRepetiteur).join("");
  document.querySelector("#etat-vide").hidden = attribuees.length > 0;

  document.querySelector("#liste-attente").innerHTML = enAttente.map(carteAttente).join("");
  document.querySelector("#section-attente").hidden = enAttente.length === 0;
}
