import { defineConfig } from "cypress";

export default defineConfig({
    projectId: "jcvbai",

    e2e: {
        baseUrl: "http://localhost:4200",
    },

    component: {
        devServer: {
            framework: "angular",
            bundler: "webpack",
        },
        specPattern: "**/*.cy.ts",
    },

    env: {
        desktop_width_px: 1280,
        desktop_height_px: 720,
        mobile_viewport: "iphone-5",
        test_user_email: "teacher@gmail.com",
        test_user_password: "teacher",
        auth_user_cookie: "AuthUser",
        new_user_name: "new user",
        new_user_email: "newuser@gmail.com",
        new_user_password: "newuser",
    },
});
