import { Injectable, signal } from "@angular/core";

export interface TableState { query: string; page: number; sort: string; descending: boolean; scroll: number }

@Injectable({ providedIn: "root" })
export class WorkspaceState {
    readonly error = signal("");
    private readonly tables = new Map<string, TableState>();
    private readonly expansions = new Map<string, number[]>();

    table(key: string): TableState {
        return this.tables.get(key) ?? { query: "", page: 1, sort: "", descending: false, scroll: 0 };
    }
    saveTable(key: string, value: TableState) { this.tables.set(key, value); }
    expanded(key: string): Set<number> { return new Set(this.expansions.get(key) ?? []); }
    saveExpanded(key: string, expanded: Set<number>) { this.expansions.set(key, [...expanded]); }
}
