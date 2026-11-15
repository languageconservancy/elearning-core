import {
    ChangeDetectionStrategy,
    Component,
    computed,
    effect,
    inject,
    input,
    output,
    signal,
    untracked,
} from "@angular/core";
import { CdkDrag, CdkDragDrop, CdkDropList, moveItemInArray } from "@angular/cdk/drag-drop";
import { Subscription } from "rxjs";
import { FormsModule } from "@angular/forms";
import { ContentAsset } from "./content-asset";
import { ContextPanel } from "./context-panel";
import { ContentBlock, FieldValue, FrameData, FrameSummary, OrderScope, Page } from "./models";
import { arrowTarget, isConflict } from "./sortable";
import { errorMessage, WorkspaceApi } from "./workspace-api";

@Component({
    selector: "studio-lesson-editor",
    imports: [ContentAsset, ContextPanel, FormsModule, CdkDropList, CdkDrag],
    templateUrl: "./lesson-editor.html",
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LessonEditor {
    readonly id = input.required<number>();
    readonly saved = output<void>();
    readonly dirtyChange = output<boolean>();
    readonly frames = signal<FrameSummary[]>([]);
    readonly total = signal(0);
    readonly page = signal(1);
    readonly pageSize = signal(25);
    readonly order = signal<OrderScope | null>(null);
    readonly reordering = signal(false);
    readonly announcement = signal("");
    readonly frame = signal<FrameData | null>(null);
    readonly selectedBlock = signal(0);
    readonly block = computed(() => this.frame()?.blocks[this.selectedBlock()] ?? null);
    readonly dirty = signal(false);
    readonly loading = signal(false);
    readonly saving = signal(false);
    readonly error = signal("");
    readonly notice = signal("");
    readonly pending = signal<(() => void) | null>(null);
    readonly removingBlock = signal(false);
    readonly displayFields = [
        { key: "is_card_lakota", label: "Language text" },
        { key: "is_card_english", label: "English" },
        { key: "is_card_image", label: "Image" },
        { key: "is_card_audio", label: "Audio" },
        { key: "is_card_video", label: "Video" },
    ];
    private readonly api = inject(WorkspaceApi);
    private frameSubscription?: Subscription;
    constructor() {
        effect((cleanup) => {
            const id = this.id();
            this.frame.set(null);
            this.frames.set([]);
            this.total.set(0);
            this.order.set(null);
            this.page.set(1);
            untracked(() => this.markDirty(false));
            this.loading.set(true);
            this.error.set("");
            const subscription = this.api.lessonFrames(id).subscribe({
                next: (result) => {
                    this.applyFrames(result);
                    this.loading.set(false);
                    if (result.items[0]) this.selectFrame(result.items[0].id);
                },
                error: (error: unknown) => {
                    this.error.set(errorMessage(error));
                    this.loading.set(false);
                },
            });
            cleanup(() => {
                subscription.unsubscribe();
                this.frameSubscription?.unsubscribe();
            });
        });
    }
    text(event: Event) {
        return (event.target as HTMLInputElement).value;
    }
    value(field: string): FieldValue {
        return this.frame()?.fields[field] ?? null;
    }
    blockValue(field: string): FieldValue {
        return this.block()?.fields[field] ?? null;
    }
    setField(field: string, value: FieldValue) {
        this.frame.update((frame) =>
            frame ? { ...frame, fields: { ...frame.fields, [field]: value } } : frame,
        );
        this.markDirty(true);
    }
    setBlock(field: string, value: FieldValue) {
        this.frame.update((frame) =>
            frame
                ? {
                      ...frame,
                      blocks: frame.blocks.map((block, index) =>
                          index === this.selectedBlock()
                              ? { ...block, fields: { ...block.fields, [field]: value } }
                              : block,
                      ),
                  }
                : frame,
        );
        this.markDirty(true);
    }
    selectFrame(id: number) {
        this.guard(() => this.loadFrame(id));
    }
    private loadFrame(id: number) {
        this.frameSubscription?.unsubscribe();
        this.frame.set(null);
        this.loading.set(true);
        this.error.set("");
        this.frameSubscription = this.api.lessonFrame(this.id(), id).subscribe({
            next: (frame) => {
                this.frame.set(frame);
                this.selectedBlock.set(0);
                this.markDirty(false);
                this.loading.set(false);
                this.notice.set("");
            },
            error: (error: unknown) => {
                this.error.set(errorMessage(error));
                this.loading.set(false);
            },
        });
    }
    private guard(action: () => void) {
        if (this.dirty()) this.pending.set(action);
        else action();
    }
    discardAndContinue() {
        const action = this.pending();
        this.pending.set(null);
        this.markDirty(false);
        action?.();
    }
    create() {
        this.guard(() => {
            this.frame.set({
                id: 0,
                revision: "",
                cards: {},
                files: {},
                fields: {
                    name: "Frame " + (this.total() + 1),
                    duration: 0,
                    frame_preview: "portrait",
                    audio_id: null,
                },
                blocks: [this.newBlock()],
            });
            this.selectedBlock.set(0);
            this.markDirty(true);
            this.notice.set("");
        });
    }
    private newBlock(): ContentBlock {
        return {
            fields: {
                type: "card",
                card_id: null,
                audio_id: null,
                image_id: null,
                video_id: null,
                custom_html: null,
                is_card_lakota: "Y",
                is_card_english: "Y",
                is_card_image: "N",
                is_card_audio: "N",
                is_card_video: "N",
            },
        };
    }
    addBlock() {
        if (!this.frame() || this.frame()!.blocks.length >= 3) return;
        this.frame.update((frame) =>
            frame ? { ...frame, blocks: [...frame.blocks, this.newBlock()] } : frame,
        );
        this.selectedBlock.set(this.frame()!.blocks.length - 1);
        this.markDirty(true);
    }
    removeBlock() {
        if (!this.frame() || this.frame()!.blocks.length <= 1) return;
        this.frame.update((frame) =>
            frame
                ? { ...frame, blocks: frame.blocks.filter((_, i) => i !== this.selectedBlock()) }
                : frame,
        );
        this.selectedBlock.set(Math.max(0, this.selectedBlock() - 1));
        this.removingBlock.set(false);
        this.markDirty(true);
    }
    moveBlock(direction: number) {
        const current = this.selectedBlock(),
            target = current + direction;
        if (!this.frame() || target < 0 || target >= this.frame()!.blocks.length) return;
        this.frame.update((frame) => {
            if (!frame) return frame;
            const blocks = [...frame.blocks];
            [blocks[current], blocks[target]] = [blocks[target], blocks[current]];
            return { ...frame, blocks };
        });
        this.selectedBlock.set(target);
        this.markDirty(true);
    }
    changePage(page: number) {
        this.guard(() => {
            this.page.set(page);
            this.loading.set(true);
            this.error.set("");
            this.api.lessonFrames(this.id(), page).subscribe({
                next: (result) => {
                    this.applyFrames(result);
                    this.loading.set(false);
                    if (result.items[0]) this.loadFrame(result.items[0].id);
                },
                error: (error: unknown) => {
                    this.error.set(errorMessage(error));
                    this.loading.set(false);
                },
            });
        });
    }
    private applyFrames(result: Page<FrameSummary>) {
        this.frames.set(result.items);
        this.total.set(result.total);
        this.pageSize.set(result.pageSize || 25);
        this.order.set(result.order ?? null);
    }
    pages() {
        return Math.max(1, Math.ceil(this.total() / this.pageSize()));
    }
    frameDropped(event: CdkDragDrop<FrameSummary[]>) {
        this.moveFrame(event.previousIndex, event.currentIndex);
    }
    frameKey(event: KeyboardEvent, index: number) {
        const target = arrowTarget(event, index, this.frames().length, "x", true);
        if (target !== null) this.moveFrame(index, target, event.currentTarget as HTMLElement);
    }
    private moveFrame(from: number, to: number, focus?: HTMLElement) {
        const order = this.order();
        if (!order || from === to || this.reordering()) return;
        const previous = this.frames();
        const frames = [...previous];
        moveItemInArray(frames, from, to);
        this.frames.set(frames);
        this.reordering.set(true);
        this.error.set("");
        const moved = frames[to];
        if (focus) {
            const strip = focus.closest(".frame-strip");
            requestAnimationFrame(() => strip?.querySelectorAll<HTMLElement>(".frame-choice")[to]?.focus());
        }
        this.api.reorder(order, frames.map((frame) => frame.id)).subscribe({
            next: () => {
                this.reordering.set(false);
                this.announcement.set(`Moved ${moved.name} to frame position ${to + 1} of ${frames.length}.`);
                this.saved.emit();
            },
            error: (error: unknown) => {
                this.reordering.set(false);
                this.frames.set(previous);
                this.announcement.set("The frame order was not changed.");
                this.error.set(
                    isConflict(error)
                        ? "The frames changed elsewhere. The list has been refreshed; please try again."
                        : errorMessage(error),
                );
                if (isConflict(error))
                    this.api.lessonFrames(this.id(), this.page()).subscribe((result) => this.applyFrames(result));
            },
        });
    }
    blockDropped(event: CdkDragDrop<ContentBlock[]>) {
        this.moveBlockTo(event.previousIndex, event.currentIndex);
    }
    blockKey(event: KeyboardEvent, index: number) {
        const length = this.frame()?.blocks.length ?? 0;
        const target = arrowTarget(event, index, length, "x", true);
        if (target === null) return;
        this.moveBlockTo(index, target);
        const strip = (event.currentTarget as HTMLElement).closest(".option-strip");
        requestAnimationFrame(() => strip?.querySelectorAll<HTMLElement>(".block-choice")[target]?.focus());
    }
    private moveBlockTo(from: number, to: number) {
        const frame = this.frame();
        if (!frame || from === to) return;
        const selected = frame.blocks[this.selectedBlock()];
        const blocks = [...frame.blocks];
        moveItemInArray(blocks, from, to);
        this.frame.set({ ...frame, blocks });
        this.selectedBlock.set(Math.max(0, blocks.indexOf(selected)));
        this.announcement.set(`Moved block to position ${to + 1}. Save the frame to keep this order.`);
        this.markDirty(true);
    }
    private markDirty(dirty: boolean) {
        this.dirty.set(dirty);
        this.dirtyChange.emit(dirty);
        if (dirty) this.notice.set("");
    }
    save() {
        if (!this.frame() || this.saving()) return;
        this.saving.set(true);
        this.error.set("");
        this.notice.set("");
        this.api.saveLessonFrame(this.id(), this.frame()!).subscribe({
            next: (frame) => {
                this.frame.set(frame);
                this.saving.set(false);
                this.markDirty(false);
                this.notice.set("Lesson frame saved.");
                this.api.lessonFrames(this.id(), this.page()).subscribe({
                    next: (result) => {
                        this.applyFrames(result);
                    },
                    error: (error: unknown) =>
                        this.error.set(
                            "The frame was saved, but the frame list could not refresh: " +
                                errorMessage(error),
                        ),
                });
                this.saved.emit();
            },
            error: (error: unknown) => {
                this.saving.set(false);
                this.error.set(errorMessage(error));
            },
        });
    }
}
