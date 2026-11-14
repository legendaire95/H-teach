import { requeteJson, echapperHtml } from "./api.js";
import { chargerEntete } from "./entete.js";

const NIVEAUX_AVEC_SERIE = ["Seconde", "Première", "Terminale"];

const formulaire = document.querySelector("#formulaire-offre");
const champs = formulaire.elements;
const sections = formulaire.querySelectorAll("section[data-etape]");
const indicateurs = document.querySelectorAll("#etapes li");
const blocSerie = document.querySelector("#bloc-serie");
const messageErreur = document.querySelector("#message-erreur");
const boutonRetour = document.querySelector("#bouton-retour");
const boutonSuivant = document.querySelector("#bouton-suivant");
const boutonCreer = document.querySelector("#bouton-creer");

let etapeCourante = 1;
let niveaux = [];

function niveauChoisi() {
  return niveaux.find((niveau) => String(niveau.id) === champs.niveau_id.value);
}

function aUneSerie() {
  const niveau = niveauChoisi();
  return niveau !== undefined && NIVEAUX_AVEC_SERIE.includes(niveau.libelle);
}

function matieresCochees() {
  return [...formulaire.querySelectorAll('input[name="matieres"]:checked')];
}

function afficherErreur(message) {
  messageErreur.textContent = message;
  messageErreur.hidden = message === "";
}

/** Affiche l'étape demandée et adapte les boutons. */
function afficherEtape(numero) {
  etapeCourante = numero;

  sections.forEach((section) => {
    section.hidden = Number(section.dataset.etape) !== numero;
  });
  indicateurs.forEach((indicateur) => {
    indicateur.classList.toggle("actif", Number(indicateur.dataset.etape) <= numero);
  });

  if (numero === 1) {
    boutonRetour.textContent = "Annuler";
  } else if (numero === 4) {
    boutonRetour.textContent = "Annuler l'offre";
    remplirRecap();
  } else {
    boutonRetour.textContent = "Retour";
  }

  boutonSuivant.hidden = numero === 4;
  boutonCreer.hidden = numero !== 4;
  afficherErreur("");
}

/** Vérifie l'étape en cours. Renvoie un message d'erreur, ou "" si tout va bien. */
function erreurEtape(numero) {
  if (numero === 1) {
    if (!champs.niveau_id.value) return "Choisissez le niveau de votre enfant.";
    if (aUneSerie() && !champs.serie.value) return "Choisissez la série.";
    if (matieresCochees().length === 0) return "Choisissez au moins une matière.";
  }
  if (numero === 2) {
    if (!champs.ville.value.trim() || !champs.commune.value.trim()) {
      return "Indiquez la ville et la commune.";
    }
  }
  if (numero === 3) {
    const min = Number(champs.budget_min.value);
    const max = Number(champs.budget_max.value);
    if (!min || !max) return "Indiquez votre budget minimum et maximum.";
    if (min > max) return "Le budget minimum doit être inférieur ou égal au budget maximum.";
  }
  return "";
}

function remplirRecap() {
  const niveau = niveauChoisi();
  const libelleNiveau = aUneSerie() ? `${niveau.libelle} ${champs.serie.value}` : niveau.libelle;
  const matieres = matieresCochees()
    .map((caseMatiere) => caseMatiere.dataset.libelle)
    .join(", ");
  const min = Number(champs.budget_min.value).toLocaleString("fr-FR");
  const max = Number(champs.budget_max.value).toLocaleString("fr-FR");

  const lignes = [
    ["Niveau", libelleNiveau],
    ["Matières", matieres],
    ["Localisation", `${champs.ville.value} — ${champs.commune.value}`],
    ["Budget", `${min} à ${max} FCFA / mois`],
  ];

  document.querySelector("#recap").innerHTML = lignes
    .map(([libelle, valeur]) => `<div><dt>${libelle}</dt><dd>${echapperHtml(valeur)}</dd></div>`)
    .join("");
}

// La série n'apparaît que pour Seconde, Première et Terminale
champs.niveau_id.addEventListener("change", () => {
  const visible = aUneSerie();
  blocSerie.hidden = !visible;
  blocSerie.disabled = !visible;
  if (!visible) {
    blocSerie.querySelectorAll("input").forEach((radio) => (radio.checked = false));
  }
});

boutonSuivant.addEventListener("click", () => {
  const erreur = erreurEtape(etapeCourante);
  if (erreur) {
    afficherErreur(erreur);
  } else {
    afficherEtape(etapeCourante + 1);
  }
});

boutonRetour.addEventListener("click", () => {
  if (etapeCourante === 1 || etapeCourante === 4) {
    window.location.href = "espace-parent.html";
  } else {
    afficherEtape(etapeCourante - 1);
  }
});

formulaire.addEventListener("submit", async (evenement) => {
  evenement.preventDefault();

  const donnees = {
    niveau_id: Number(champs.niveau_id.value),
    serie: aUneSerie() ? champs.serie.value : null,
    matieres: matieresCochees().map((caseMatiere) => Number(caseMatiere.value)),
    ville: champs.ville.value,
    commune: champs.commune.value,
    budget_min: Number(champs.budget_min.value),
    budget_max: Number(champs.budget_max.value),
  };

  try {
    await requeteJson("/api/offres", { method: "POST", body: JSON.stringify(donnees) });
    formulaire.hidden = true;
    document.querySelector("#etapes").hidden = true;
    document.querySelector("#confirmation").hidden = false;
  } catch (erreur) {
    const details = Object.values(erreur.details).join(" ");
    afficherErreur(details || erreur.message);
  }
});

// Au chargement : vérifier la connexion, puis remplir les listes depuis l'API
const utilisateur = await chargerEntete("parent", 2);

if (utilisateur) {
  const [listeNiveaux, listeMatieres] = await Promise.all([
    requeteJson("/api/niveaux"),
    requeteJson("/api/matieres"),
  ]);
  niveaux = listeNiveaux;

  champs.niveau_id.insertAdjacentHTML(
    "beforeend",
    niveaux
      .map((niveau) => `<option value="${niveau.id}">${echapperHtml(niveau.libelle)}</option>`)
      .join(""),
  );

  document.querySelector("#grille-matieres").innerHTML = listeMatieres
    .map(
      (matiere) => `
        <label class="choix">
          <input type="checkbox" name="matieres" value="${matiere.id}"
                 data-libelle="${echapperHtml(matiere.libelle)}" />
          <span>${echapperHtml(matiere.libelle)}</span>
        </label>`,
    )
    .join("");

  afficherEtape(1);
}
