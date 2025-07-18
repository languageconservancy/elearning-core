import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { RouterModule } from "@angular/router";
import { InfiniteScrollModule } from "ngx-infinite-scroll";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";

import { PipesModule } from "app/_pipes/pipes.module";
import { PartialsModule } from "app/_partials/partials.module";
import { DirectivesModule } from "app/_directives/directives.module";

import { ForumListComponent } from "./forum-list/forum-list.component";
import { PostDetailsComponent } from "./post-details/post-details.component";
import { FriendSidebarComponent } from "./_partials/friend-sidebar/friend-sidebar.component";
import { ForumGeneralDiscussionComponent } from "./forum-general-discussion/forum-general-discussion.component";
import { AddFriendsComponent } from "./add-friends/add-friends.component";
import { ReportPostModalComponent } from "./_partials/report-post-modal/report-post-modal.component";

@NgModule({
    imports: [
        CommonModule,
        FormsModule,
        RouterModule,
        ReactiveFormsModule,
        InfiniteScrollModule,
        PipesModule,
        PartialsModule,
        DirectivesModule,
    ],
    declarations: [
        ForumListComponent,
        PostDetailsComponent,
        FriendSidebarComponent,
        ForumGeneralDiscussionComponent,
        ReportPostModalComponent,
        AddFriendsComponent,
    ],
    exports: [],
})
export class ForumModule {}
