import js from "@eslint/js";
import globals from "globals";

export default [
    {
        files: ["**/*.js"],
        ...js.configs.recommended,
        languageOptions: {
            sourceType: "script",
            globals: globals.browser,
        },
    },
    {
        files: ["**/*.mjs"],
        ...js.configs.recommended,
    },
];
