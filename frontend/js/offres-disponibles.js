import { requeteJson, echapperHtml } from "./api.js";
import { chargerEntete } from "./entete.js";
import { titreOffre, formaterBudget, formaterDate } from "./format.js";

const liste = document.querySelector("#liste-offres");
const messageErreur = document.querySelector("#message-erreur");

function carteOffre(offre) {
  const action = offre.deja_postule
    ? '<span class="badge badge-succes">Candidature envoyée</span>'
    : `<button type="button" class="btn btn-principal" data-action="postuler" data-id="${offre.id}">Postuler</button>`;

  return `
    <article class="carte carte-offre">
      <div class="entete-offre">
        <div>
          <h3>${titreOffre(offre)}</h3>
          <p>${echapperHtml(offre.ville)} — ${echapperHtml(offre.commune)} · ${formaterBudget(offre)}</p>
        </div>
        <div class="actions-carte">
          <button type="button" class="btn btn-secondaire" data-action="details" data-id="${offre.id}">Détails</button>
          ${action}
        </div>
      </div>
      <dl class="recap" id="details-${offre.id}" hidden>
        <div><dt>Niveau</dt><dd>${echapperHtml(offre.niveau)}${offre.serie ? ` — série ${echapperHtml(offre.serie)}` : ""}</dd></div>
        <div><dt>Matières</dt><dd>${echapperHtml(offre.matieres)}</dd></div>
        <div><dt>Lieu des cours</dt><dd>${echapperHtml(offre.ville)} — ${echapperHtml(offre.commune)}</dd></div>
        <div><dt>Budget</dt><dd>${formaterBudget(offre)}</dd></div>
        <div><dt>Publiée le</dt><dd>${formaterDate(offre.cree_le)}</dd></div>
      </dl>
    </article>`;
}

async function chargerOffres() {
  const offres = await requeteJson("/api/enseignant/offres");
  liste.innerHTML = offres.map(carteOffre).join("");
  document.querySelector("#etat-vide").hidden = offres.length > 0;
}

// Un seul écouteur pour tous les boutons de la liste (délégation d'événements)
liste.addEventListener("click", async (evenement) => {
  const bouton = evenement.target.closest("button[data-action]");
  if (!bouton) return;

  const id = bouton.dataset.id;

  if (bouton.dataset.action === "details") {
    const details = document.querySelector(`#details-${id}`);
    details.hidden = !details.hidden;
    bouton.textContent = details.hidden ? "Détails" : "Masquer";
  }

  if (bouton.dataset.action === "postuler") {
    bouton.disabled = true;
    messageErreur.hidden = true;

    try {
      await requeteJson("/api/candidatures", {
        method: "POST",
        body: JSON.stringify({ offre_id: Number(id) }),
      });
      bouton.outerHTML = '<span class="badge badge-succes">Candidature envoyée</span>';
    } catch (erreur) {
      messageErreur.textContent = erreur.message;
      messageErreur.hidden = false;
      bouton.disabled = false;
    }
  }
});

// Au chargement
const utilisateur = await chargerEntete("enseignant", 2);

if (utilisateur) {
  const profil = await requeteJson("/api/enseignant/profil");

  if (profil.matieres.length === 0) {
    document.querySelector("#profil-incomplet").hidden = false;
  } else {
    await chargerOffres();
  }
}
