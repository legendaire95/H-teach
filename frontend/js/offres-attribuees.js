import { requeteJson, echapperHtml } from "./api.js";
import { chargerEntete } from "./entete.js";
import { titreOffre, formaterDate, nomComplet, LIBELLE_REGLE } from "./format.js";

const utilisateur = await chargerEntete("admin", 2);

if (utilisateur) {
  // Message de confirmation après une attribution (écran 19)
  const nomRetenu = new URLSearchParams(window.location.search).get("attribuee");
  if (nomRetenu) {
    const message = document.querySelector("#message-succes");
    message.textContent = `✓ ${nomRetenu} a bien été attribué(e) à l'offre.`;
    message.hidden = false;
  }

  const attributions = await requeteJson("/api/admin/attributions");

  document.querySelector("#corps-attributions").innerHTML = attributions
    .map(
      (attribution) => `
        <tr>
          <td>${titreOffre(attribution)}</td>
          <td>${echapperHtml(nomComplet(attribution.parent_prenom, attribution.parent_nom))}</td>
          <td>${echapperHtml(nomComplet(attribution.enseignant_prenom, attribution.enseignant_nom))}</td>
          <td>${LIBELLE_REGLE[attribution.regle]}</td>
          <td>${formaterDate(attribution.attribue_le)}</td>
        </tr>`,
    )
    .join("");

  document.querySelector("#vide-attributions").hidden = attributions.length > 0;
}
