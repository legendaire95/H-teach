import { echapperHtml } from "./api.js";
import { chargerEntete } from "./entete.js";

/** Contenu du tutoriel rapide (écrans 06 et 07). */
const ETAPES_TUTO = [
  {
    titre: "Onglet 1 — Mes répétiteurs",
    texte: "Ici, vous suivez vos enfants et les répétiteurs qui les encadrent.",
  },
  {
    titre: "Onglet 2 — Trouver un répétiteur",
    texte:
      "Remplissez le formulaire : niveau de l'élève, matières, localisation et budget. " +
      "Votre offre est alors mise sur le marché.",
  },
];

let etape = 0;

const boutonPrecedent = document.querySelector("#bouton-precedent");
const boutonTuto = document.querySelector("#bouton-tuto");

/** Affiche l'étape courante du tutoriel. */
function afficherTuto() {
  const derniere = etape === ETAPES_TUTO.length - 1;

  document.querySelector("#compteur-tuto").textContent =
    `Tutoriel rapide · étape ${etape + 1}/${ETAPES_TUTO.length}`;
  document.querySelector("#titre-tuto").textContent = ETAPES_TUTO[etape].titre;
  document.querySelector("#texte-tuto").textContent = ETAPES_TUTO[etape].texte;
  document.querySelector("#points-tuto").innerHTML = ETAPES_TUTO.map(
    (_, index) => `<span class="${index === etape ? "actif" : ""}"></span>`,
  ).join("");

  boutonPrecedent.hidden = etape === 0;
  boutonTuto.textContent = derniere ? "Commencer maintenant" : "Suivant";
}

boutonPrecedent.addEventListener("click", () => {
  etape -= 1;
  afficherTuto();
});

boutonTuto.addEventListener("click", () => {
  if (etape === ETAPES_TUTO.length - 1) {
    window.location.href = "espace-parent.html";
  } else {
    etape += 1;
    afficherTuto();
  }
});

const utilisateur = await chargerEntete("parent", 0);

if (utilisateur) {
  const prenom = utilisateur.prenom || utilisateur.nom;
  document.querySelector("#titre-bienvenue").innerHTML = `Bienvenue, ${echapperHtml(prenom)} !`;
  afficherTuto();
}
