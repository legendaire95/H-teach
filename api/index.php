<?php

declare(strict_types=1);

// Point d'entrée de l'API sur Vercel : toutes les URL /api/... arrivent ici
// (voir vercel.json) et sont traitées par le routeur habituel du back-end.
require __DIR__ . '/../backend/public/index.php';
