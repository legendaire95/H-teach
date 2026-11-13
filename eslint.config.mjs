import js from "@eslint/js";
import globals from "globals";
import prettier from "eslint-config-prettier";

export default [
  // Dossiers à ne jamais analyser
  { ignores: ["node_modules/", "vendor/"] },

  // Règles recommandées par ESLint
  js.configs.recommended,

  // Réglages pour le code du front-end
  {
    files: ["frontend/js/**/*.js"],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "module",
      globals: { ...globals.browser },
    },
    rules: {
      "no-unused-vars": "warn", // variable déclarée mais jamais utilisée
      eqeqeq: "error", // imposer === au lieu de ==
      "prefer-const": "error", // const quand la variable n'est jamais réassignée
    },
  },

  // Désactive les règles de mise en forme (gérées par Prettier)
  prettier,
];