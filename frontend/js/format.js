import { echapperHtml } from "./api.js";

/** Décrit une offre en une ligne : "Mathématiques, Physique-Chimie — Terminale D". */
export function titreOffre(offre) {
  const niveau = offre.serie ? `${offre.niveau} ${offre.serie}` : offre.niveau;
  return `${echapperHtml(offre.matieres)} — ${echapperHtml(niveau)}`;
}

/** Affiche une fourchette de budget : "30 000 à 50 000 FCFA / mois". */
export function formaterBudget(offre) {
  const min = Number(offre.budget_min).toLocaleString("fr-FR");
  const max = Number(offre.budget_max).toLocaleString("fr-FR");
  return `${min} à ${max} FCFA / mois`;
}

/** Transforme une date MySQL ("2026-11-14 13:40:00") en "14/11/2026". */
export function formaterDate(dateSql) {
  return new Date(dateSql.replace(" ", "T")).toLocaleDateString("fr-FR");
}

/** Assemble prénom et nom ("Awa Kone"), en ignorant un prénom vide. */
export function nomComplet(prenom, nom) {
  return [prenom, nom].filter(Boolean).join(" ");
}

/** Libellés lisibles des valeurs stockées en base. */
export const LIBELLE_EXPERIENCE = {
  moins_1_an: "Moins d'1 an",
  "1_3_ans": "1 à 3 ans",
  "3_5_ans": "3 à 5 ans",
  plus_5_ans: "Plus de 5 ans",
};

export const LIBELLE_ETUDES = {
  bac: "Bac",
  licence: "Licence",
  master: "Master",
  doctorat: "Doctorat",
};

export const LIBELLE_REGLE = {
  premier_arrive: "Premier arrivé, premier servi",
  experience: "Expérience",
  politique: "Politique de l'entreprise",
};
