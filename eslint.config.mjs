import js from "@eslint/js";
import globals from "globals";

export default [
    {
        ignores: ["vendor/**", "coverage/**"],
    },
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
    {
        files: ["test/js/**/*.cjs"],
        ...js.configs.recommended,
        languageOptions: {
            globals: globals.node,
        },
    },
];
