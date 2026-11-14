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
