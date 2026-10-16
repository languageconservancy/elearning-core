import { defineConfig, globalIgnores } from "eslint/config";
import unusedImports from "eslint-plugin-unused-imports";
import angular from "angular-eslint";
import tseslint from "typescript-eslint";
import prettier from "eslint-plugin-prettier/recommended";

export default defineConfig([
    globalIgnores([
        "projects/**/*",
        "src/assets",
        "src/index.html",
        "src/language",
        "**/*/virtual-keyboard.test.html",
        "dist/",
        ".angular/",
    ]),
    {
        files: ["**/*.ts"],
        extends: [
            tseslint.configs.recommended,
            tseslint.configs.recommendedTypeChecked,
            angular.configs.tsRecommended,
            prettier,
        ],
        languageOptions: {
            ecmaVersion: 2020,
            sourceType: "module",
            parserOptions: {
                projectService: true,
                tsconfigRootDir: import.meta.dirname,
            },
        },
        processor: angular.processInlineTemplates,
        plugins: {
            "unused-imports": unusedImports,
        },
        rules: {
            "@angular-eslint/directive-selector": [
                "error",
                {
                    type: "attribute",
                    prefix: "app",
                    style: "camelCase",
                },
            ],
            "@angular-eslint/component-selector": [
                "error",
                {
                    type: "element",
                    prefix: "app",
                    style: "kebab-case",
                },
            ],
            "unused-imports/no-unused-imports": "error",
            "unused-imports/no-unused-vars": [
                "warn",
                {
                    vars: "all",
                    varsIgnorePattern: "^_",
                    args: "after-used",
                    argsIgnorePattern: "^_",
                },
            ],
            "@typescript-eslint/no-explicit-any": "off",
            "@typescript-eslint/no-unsafe-member-access": "off",
            "@typescript-eslint/no-unsafe-argument": "off",
            "@typescript-eslint/no-unsafe-call": "off",
            "@typescript-eslint/no-unsafe-assignment": "off",
            "@typescript-eslint/no-unsafe-return": "off",
        },
    },
    {
        files: ["**/*.html"],
        extends: [angular.configs.templateRecommended],
    },
]);