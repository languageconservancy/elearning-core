import { Component, OnInit, OnDestroy } from "@angular/core";
import { Subscription } from "rxjs";
import { SnackbarService } from "app/_services/snackbar.service";

@Component({
    selector: "app-snackbar",
    templateUrl: "./snackbar.component.html",
    styleUrls: ["./snackbar.component.scss"],
})

/**
 * Component for snackbar messages.
 * Subscribes to the snackbar service snackbar observable, which emits
 * objects specifying message status and content.
 * Snackbar messages are shown for a variable duration based on the length
 * of the message to display and an average reading speed of 50ms per character.
 */
export class SnackbarComponent implements OnInit, OnDestroy {
    protected SNACKBAR_AVE_READING_SPEED_MS_PER_CHAR: number = 50;
    protected SNACKBAR_TIMEOUT_MIN_MS: number = 2500;
    protected SNACKBAR_TIMEOUT_MAX_MS: number = 5000;
    protected SNACKBAR_MSG_TIMEOUT_MS: number = 500;
    public showSnackbar: boolean = false;
    public snackbarMsg: string = "";
    public snackbarStatus: boolean = false;
    private snackbarSubscription: Subscription;

    constructor(private snackbarService: SnackbarService) {}

    ngOnInit(): void {
        // Wait for snackbar messages and when one comes, display the message,
        // with background color based on the status.
        this.snackbarSubscription = this.snackbarService.snackbar.subscribe((data) => {
            if (!data) {
                return;
            }

            if ((typeof data.msg === "string" && !data.msg.trim()) || !data.msg) {
                this.clearSnackbar();
            } else if (!this.showSnackbar) {
                const timeoutMs = this.computeTimeoutFromMsg(data.msg);
                this.displaySnackbar(data.status, data.msg, timeoutMs);
            }
        });
    }

    ngOnDestroy(): void {
        this.snackbarSubscription.unsubscribe();
    }

    /**
     * Computes how long to show message for based on the number of characters
     * in the message and an average reading speed.
     * @param {string} msg - Snackbar message
     */
    computeTimeoutFromMsg(msg: string) {
        // Set timeout based on message length
        const timeoutMs = this.SNACKBAR_AVE_READING_SPEED_MS_PER_CHAR * msg.length;
        return Math.min(
            Math.max(timeoutMs, this.SNACKBAR_TIMEOUT_MIN_MS),
            this.SNACKBAR_TIMEOUT_MAX_MS,
        );
    }

    /**
     * Display snackbar message.
     * < Select a reason for the report
     * @param {boolean} status - Error or success. Changes the bg color of snackbar
     * @param {string} msg - Message to display
     * @param {number} timeoutMs - Milliseconds to wait before hiding snackbar message
     */
    displaySnackbar(status: boolean, msg: string, timeoutMs: number): void {
        // Display snackbar
        this.snackbarMsg = msg;
        this.showSnackbar = true;
        this.snackbarStatus = status;
        setTimeout(() => {
            this.clearSnackbar();
        }, timeoutMs);
    }

    /**
     * Prevent smaller, empty box from appearing and color change by
     * waiting for snackbar to disappear before clearing message.
     * @param {number} timeoutMs - Milliseconds to wait before clearing snackbar message
     */
    clearSnackbar(timeoutMs: number = this.SNACKBAR_MSG_TIMEOUT_MS) {
        this.showSnackbar = false;
        setTimeout(() => {
            this.snackbarMsg = " ";
        }, timeoutMs);
    }
}
