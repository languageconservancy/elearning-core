import { Component, OnInit } from "@angular/core";

import { ForumService } from "app/_services/forum.service";
import { Loader } from "app/_services/loader.service";
import { SnackbarService } from "app/_services/snackbar.service";

declare let jQuery: any;

@Component({
    selector: "app-report-post-modal",
    templateUrl: "./report-post-modal.component.html",
    styleUrls: ["./report-post-modal.component.scss"],
})
export class ReportPostModalComponent implements OnInit {
    // Report
    public OTHER_REASON: string = "other (explain)";
    private MODAL_SELECTOR: string = "#report-post";
    private reportData: any; // { userId, postToReport }
    public reportReason: string = "";
    public reportExplanation: any = "";
    public reportReasons: any;

    constructor(
        private forumService: ForumService,
        private loaderService: Loader,
        private snackbarService: SnackbarService,
    ) {
        this.getFlagReasons();
    }

    ngOnInit(): void {
        this.waitForModalRequests();
    }

    waitForModalRequests() {
        this.forumService.postReporter.subscribe((data: any) => {
            if (!!data) {
                this.reportData = data;
                this.openModal();
            } else {
                console.error("postReporter subscription data is invalid");
            }
        });
    }

    getFlagReasons() {
        this.forumService
            .getFlagReasons()
            .then((res) => {
                if (!!res && res.data.status && !!res.data.results.flag_reasons) {
                    this.reportReasons = res.data.results.flag_reasons;
                }
            })
            .catch((err) => {
                console.error(err);
                this.snackbarService.showSnackbar({ status: false, msg: err });
            });
    }

    openModal() {
        jQuery(this.MODAL_SELECTOR).modal("show");
    }

    closeModal() {
        jQuery(this.MODAL_SELECTOR).modal("hide");
    }

    reportPost() {
        if (!this.reportData) {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Woops! Data isn't ready. Try again.",
            });
        }

        this.reportExplanation = this.reportExplanation.trim();

        // Make sure a reason was selected
        if (this.reportReason == "") {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please select a reason.",
            });
            return;
        }

        // If they selected 'other', make sure an explanation was provided
        if (this.reportReason == this.OTHER_REASON && this.reportExplanation == "") {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please enter the report reason.",
            });
            return;
        }

        // Data to send to database
        const data = {
            user_id: this.reportData.userId,
            post_id: this.reportData.postToReport.id,
            report_type: this.reportExplanation ? this.reportExplanation : this.reportReason,
        };

        // Send to backend
        this.forumService
            .flagPost(data)
            .then((res: any) => {
                this.loaderService.setLoader(false);
                this.snackbarService.showSnackbar({
                    status: res.data.status,
                    msg: res.data.message,
                });
                if (res.data.status) {
                    this.closeModal();
                    this.clearModalVars();
                    this.forumService.postReportIsDone(res.data.status);
                }
            })
            .catch((err) => {
                console.error(err);
                this.loaderService.setLoader(false);
                this.snackbarService.showSnackbar({
                    status: false,
                    msg: `Please try again after some time while we fix it.`,
                });
            });
    }

    clearModalVars() {
        this.reportReason = "";
        this.reportExplanation = "";
    }
}
