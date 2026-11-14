import { requeteJson, echapperHtml } from "./api.js";
import { chargerEntete } from "./entete.js";
import {
  titreOffre,
  formaterBudget,
  formaterDate,
  nomComplet,
  LIBELLE_EXPERIENCE,
  LIBELLE_ETUDES,
} from "./format.js";

const AIDE_REGLE = {
  premier_arrive:
    "Les candidats sont classés du premier au dernier inscrit. Le premier est suggéré.",
  experience:
    "Les candidats sont classés du plus expérimenté au moins expérimenté. Le premier est suggéré.",
  politique: "Pas de classement automatique : retenez l'enseignant selon la politique de l'agence.",
};

const vueOffres = document.querySelector("#vue-offres");
const vueCandidats = document.querySelector("#vue-candidats");
const messageErreur = document.querySelector("#message-erreur");

let offreCourante = null;
let regleCourante = "premier_arrive";
let candidatsAffiches = [];

function afficherVue(nom) {
  vueOffres.hidden = nom !== "offres";
  vueCandidats.hidden = nom !== "candidats";
}

/** Écran 17 : liste des offres ouvertes. */
async function chargerOffres() {
  const offres = await requeteJson("/api/admin/offres");

  document.querySelector("#corps-offres").innerHTML = offres
    .map(
      (offre) => `
        <tr>
          <td>${titreOffre(offre)}</td>
          <td>${echapperHtml(nomComplet(offre.parent_prenom, offre.parent_nom))}</td>
          <td>${echapperHtml(offre.commune)}</td>
          <td>${formaterBudget(offre)}</td>
          <td>${offre.nb_candidatures}</td>
          <td>
            <button type="button" class="btn ${offre.nb_candidatures > 0 ? "btn-principal" : "btn-secondaire"}"
                    data-offre="${offre.id}">Voir les candidats</button>
          </td>
        </tr>`,
    )
    .join("");

  document.querySelector("#vide-offres").hidden = offres.length > 0;
  afficherVue("offres");
}

/** Écran 18 : candidats d'une offre, triés selon la règle choisie. */
async function chargerCandidats() {
  const parametres = new URLSearchParams({ offre_id: offreCourante, regle: regleCourante });
  const resultat = await requeteJson(`/api/admin/candidats?${parametres}`);
  const offre = resultat.offre;
  candidatsAffiches = resultat.candidats;

  document.querySelector("#titre-offre").innerHTML = titreOffre(offre);
  document.querySelector("#infos-offre").textContent =
    `Parent : ${nomComplet(offre.parent_prenom, offre.parent_nom)} · ` +
    `${offre.ville} — ${offre.commune} · ${formaterBudget(offre)}`;

  document.querySelectorAll(".segment").forEach((segment) => {
    segment.classList.toggle("actif", segment.dataset.regle === regleCourante);
  });
  document.querySelector("#aide-regle").textContent = AIDE_REGLE[regleCourante];

  document.querySelector("#corps-candidats").innerHTML = candidatsAffiches
    .map((candidat, index) => {
      const suggere = index === 0 && regleCourante !== "politique";
      return `
        <tr class="${suggere ? "suggere" : ""}">
          <td><strong>${echapperHtml(nomComplet(candidat.prenom, candidat.nom))}</strong></td>
          <td>${LIBELLE_EXPERIENCE[candidat.experience] ?? "—"}</td>
          <td>${LIBELLE_ETUDES[candidat.niveau_etudes] ?? "—"}</td>
          <td>${formaterDate(candidat.postule_le)}</td>
          <td>
            <button type="button" class="btn ${suggere ? "btn-principal" : "btn-secondaire"}"
                    data-candidature="${candidat.candidature_id}">Retenir</button>
          </td>
        </tr>`;
    })
    .join("");

  document.querySelector("#vide-candidats").hidden = candidatsAffiches.length > 0;
  messageErreur.hidden = true;
  afficherVue("candidats");
}

// Clic sur « Voir les candidats »
document.querySelector("#corps-offres").addEventListener("click", (evenement) => {
  const bouton = evenement.target.closest("button[data-offre]");
  if (!bouton) return;

  offreCourante = Number(bouton.dataset.offre);
  regleCourante = "premier_arrive";
  chargerCandidats();
});

// Changement de règle de sélection
document.querySelectorAll(".segment").forEach((segment) => {
  segment.addEventListener("click", () => {
    regleCourante = segment.dataset.regle;
    chargerCandidats();
  });
});

// Clic sur « Retenir »
document.querySelector("#corps-candidats").addEventListener("click", async (evenement) => {
  const bouton = evenement.target.closest("button[data-candidature]");
  if (!bouton) return;

  const candidatureId = Number(bouton.dataset.candidature);
  const candidat = candidatsAffiches.find((c) => Number(c.candidature_id) === candidatureId);
  const nom = nomComplet(candidat.prenom, candidat.nom);

  if (!window.confirm(`Attribuer cette offre à ${nom} ? Cette décision est définitive.`)) {
    return;
  }

  try {
    await requeteJson("/api/admin/attributions", {
      method: "POST",
      body: JSON.stringify({ candidature_id: candidatureId, regle: regleCourante }),
    });
    window.location.href = `offres-attribuees.html?attribuee=${encodeURIComponent(nom)}`;
  } catch (erreur) {
    messageErreur.textContent = erreur.message;
    messageErreur.hidden = false;
  }
});

document.querySelector("#bouton-retour").addEventListener("click", chargerOffres);

// Au chargement
const utilisateur = await chargerEntete("admin", 1);

if (utilisateur) {
  await chargerOffres();
}
