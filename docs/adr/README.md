# Architecture Decision Records (ADR) — H-teach

Ce dossier contient les fiches de décision d'architecture du projet **H-teach**.

Un ADR (Architecture Decision Record) consigne **une décision technique importante** : son contexte, la décision prise, les alternatives écartées et ses conséquences. Une fiche correspond à une seule décision.

## Liste des décisions

| N° | Titre | Statut | Date |
|---|---|---|---|
| [001](001-stack-front-end-et-back-end.md) | Stack front-end et back-end : HTML/CSS/JavaScript sans framework + PHP | Accepté | 13/11/2026 |
| [002](002-base-de-donnees.md) | Base de données : MySQL (relationnelle) | Accepté | 13/11/2026 |

## Modèle utilisé

Chaque fiche suit le modèle de Michael Nygard, complété d'une rubrique « Alternatives envisagées » :

1. **Statut** : Proposé, Accepté, ou Remplacé par un autre ADR.
2. **Contexte** : le besoin et les contraintes qui imposent une décision.
3. **Décision** : ce qui est choisi.
4. **Alternatives envisagées** : les autres options et pourquoi elles sont écartées.
5. **Conséquences** : ce que la décision apporte et ce qu'elle coûte.

Pour une nouvelle décision, créer un fichier en le nommant selon la convention `00X-titre-court.md` et l'ajouter au tableau ci-dessus. On ne modifie pas une décision acceptée : on la remplace par un nouvel ADR, en mettant à jour le statut de l'ancien.
