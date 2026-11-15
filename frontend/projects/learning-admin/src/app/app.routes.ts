import { Routes } from "@angular/router";

export const routes: Routes = [
    { path: "**", loadComponent: () => import("./workspace").then((m) => m.Workspace) },
];
