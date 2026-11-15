import { HttpErrorResponse } from "@angular/common/http";

/** Index an item should move to for an arrow-key press on its drag handle, or null. */
export function arrowTarget(
    event: KeyboardEvent,
    index: number,
    length: number,
    axis: "x" | "y",
    requireAlt = false,
): number | null {
    if (requireAlt !== event.altKey) return null;
    const back = axis === "y" ? "ArrowUp" : "ArrowLeft";
    const forward = axis === "y" ? "ArrowDown" : "ArrowRight";
    let target: number | null = null;
    if (event.key === back) target = index - 1;
    else if (event.key === forward) target = index + 1;
    else if (event.key === "Home") target = 0;
    else if (event.key === "End") target = length - 1;
    if (target === null || target < 0 || target >= length || target === index) return null;
    event.preventDefault();
    return target;
}

export function isConflict(error: unknown): boolean {
    return error instanceof HttpErrorResponse && error.status === 409;
}
