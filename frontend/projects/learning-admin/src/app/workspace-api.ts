import { HttpClient, HttpErrorResponse, HttpHeaders, HttpInterceptorFn } from "@angular/common/http";
import { inject, Injectable, signal } from "@angular/core";
import { catchError, firstValueFrom, throwError } from "rxjs";
import { ArchiveImage, Bootstrap, BrowseType, CopyOptions, CopyPlan, ExerciseData, FieldValue, FrameData, FrameSummary, MediaFile, OrderScope, Page, Parent, RecordDetail, RecordType, StructureResult, Summary } from "./models";

@Injectable({ providedIn: "root" })
export class WorkspaceApi {
    private readonly http = inject(HttpClient);
    readonly config = signal<Bootstrap | null>(null);
    private endpoint = "/backend/admin/workspace/";

    async initialize(): Promise<void> {
        const element = document.getElementById("workspace-config");
        if (element?.textContent) {
            const config: unknown = JSON.parse(element.textContent);
            if (typeof config !== "object" || config === null || !("api" in config) || typeof config.api !== "string") {
                throw new Error("Invalid workspace bootstrap configuration.");
            }
            this.endpoint = new URL(".", new URL(config.api, location.origin)).pathname;
        }
        this.config.set(await firstValueFrom(this.http.get<Bootstrap>(this.endpoint + "bootstrap")));
    }

    records(type: BrowseType, q = "", page = 1, sort = "", direction = "asc", parent?: Parent) {
        if (type === "activities") {
            return this.http.get<Page<Summary>>(this.endpoint + "activities/" + parent?.id, { params: { q, page } });
        }
        return this.http.get<Page<Summary>>(this.endpoint + "records/" + type, {
            params: {
                q, page, sort: sort || (parent ? "sequence" : this.config()!.types[type].title), direction,
                ...(parent ? { parentType: parent.type, parentId: parent.id } : {}),
                ...(parent?.pathId ? { pathId: parent.pathId } : {}),
            },
        });
    }

    search(q: string) {
        return this.http.get<{ items: Summary[] }>(this.endpoint + "search", { params: { q } });
    }

    record(type: RecordType, id: number) {
        return this.http.get<RecordDetail>(`${this.endpoint}record/${type}/${id}`);
    }

    save(type: RecordType, id: number | null, fields: Record<string, FieldValue>, revision?: string, initialCardId?: number) {
        return this.http.post<RecordDetail>(`${this.endpoint}save/${type}${id === null ? "" : "/" + id}`,
            { fields, revision, initialCardId }, { headers: this.headers() });
    }

    choices(reference: string, q: string, id?: number) {
        return this.http.get<{ items: { id: number; title: string }[] }>(this.endpoint + "choices/" + reference,
            { params: { q, ...(id ? { id } : {}) } });
    }

    membership(groupId: number, cardId: number, operation: "add" | "remove", revision: string) {
        return this.http.post<RecordDetail>(this.endpoint + "membership/" + groupId,
            { cardId, operation, revision }, { headers: this.headers() });
    }

    exerciseContent(id: number) { return this.http.get<ExerciseData>(this.endpoint + "exercise-content/" + id); }
    saveExerciseContent(id: number, data: ExerciseData) {
        return this.http.post<ExerciseData>(this.endpoint + "exercise-content/" + id,
            { fields: data.fields, options: data.options, revision: data.revision }, { headers: this.headers() });
    }
    lessonFrames(id: number, page = 1) {
        return this.http.get<Page<FrameSummary>>(this.endpoint + "lesson-frames/" + id, { params: { page } });
    }
    lessonFrame(lessonId: number, frameId: number) {
        return this.http.get<FrameData>(`${this.endpoint}lesson-frame/${lessonId}/${frameId}`);
    }
    saveLessonFrame(lessonId: number, frame: FrameData) {
        return this.http.post<FrameData>(`${this.endpoint}lesson-frame/${lessonId}${frame.id ? "/" + frame.id : ""}`,
            { fields: frame.fields, blocks: frame.blocks, revision: frame.revision }, { headers: this.headers() });
    }

    reorder(order: OrderScope, ids: number[]) {
        return this.http.post<{ ids: number[] }>(this.endpoint + "reorder", { ...order, ids }, { headers: this.headers() });
    }

    create(type: RecordType, fields: Record<string, string>, parent: Parent | null) {
        return this.http.post<StructureResult>(this.endpoint + "create/" + type, { fields, parent }, { headers: this.headers() });
    }
    link(unitId: number, pathId: number | null, items: { type: RecordType; id: number }[]) {
        return this.http.post<{ linkIds: number[] }>(this.endpoint + "link/" + unitId, { pathId, items },
            { headers: this.headers() });
    }
    unlink(unitId: number, linkId: number) {
        return this.http.post<{ removed: number }>(`${this.endpoint}unlink/${unitId}/${linkId}`, {}, { headers: this.headers() });
    }
    copyPlan(type: RecordType, id: number) {
        return this.http.get<CopyPlan>(`${this.endpoint}copy-plan/${type}/${id}`);
    }
    copy(type: RecordType, id: number, options: CopyOptions) {
        return this.http.post<StructureResult>(`${this.endpoint}copy/${type}/${id}`, options, { headers: this.headers() });
    }

    archive(q: string, page: number) {
        return this.http.get<Page<ArchiveImage>>(this.endpoint + "archive", { params: { q, page } });
    }

    media(kind: string, q: string, page: number) {
        return this.http.get<Page<MediaFile>>(this.endpoint + "media", { params: { kind, q, page } });
    }

    importImage(key: string) {
        return this.http.post<MediaFile>(this.endpoint + "import-image", { key }, { headers: this.headers() });
    }

    upload(file: Blob, name: string, kind: string) {
        const data = new FormData();
        data.append("file", file, name);
        data.append("name", name);
        data.append("kind", kind);
        return this.http.post<MediaFile>(this.endpoint + "upload", data, { headers: this.headers() });
    }

    private headers() {
        return new HttpHeaders({ "X-Workspace-Token": this.config()!.token });
    }

    private signingIn = false;
    signIn() {
        if (this.signingIn) {
            return;
        }
        this.signingIn = true;
        // CakePHP expects redirectUrl relative to the app base (e.g. "/admin/workspace/" under "/backend").
        const base = this.endpoint.replace(/\/admin\/workspace\/$/, "");
        const here = location.pathname.startsWith(base) ? location.pathname.slice(base.length) : "/admin/workspace/";
        location.assign(`${base}/admin/users/login?redirectUrl=${encodeURIComponent(here + location.search)}`);
    }
}

export function sessionExpired(error: unknown): boolean {
    if (!(error instanceof HttpErrorResponse)) {
        return false;
    }
    // Expired sessions are redirected to the HTML login page, which fetch follows silently.
    return error.status === 401 || /\/users\/login(\?|$)/.test(error.url ?? "")
        || (error.status === 403 && typeof error.error?.message === "string"
            && error.error.message.includes("editing session has expired"));
}

export const sessionInterceptor: HttpInterceptorFn = (request, next) => {
    const api = inject(WorkspaceApi);
    return next(request).pipe(catchError((error: unknown) => {
        if (sessionExpired(error)) {
            api.signIn();
        }
        return throwError(() => error);
    }));
};

export function errorMessage(error: unknown): string {
    if (sessionExpired(error)) {
        return "Your session has expired. Taking you to sign in…";
    }
    if (error instanceof HttpErrorResponse) {
        if (typeof error.error === "object" && error.error !== null && typeof error.error.message === "string") {
            const fields: unknown = error.error.errors;
            return error.error.message + (fields ? " " + JSON.stringify(fields) : "");
        }
        if (error.status === 401 || error.status === 403 || error.status === 200) {
            return "Your session may have expired or access was denied. Sign in to the legacy admin and reload.";
        }
        return `Request failed (${error.status || "network error"}). Please retry.`;
    }
    return error instanceof Error ? error.message : "The operation failed. Please retry.";
}
