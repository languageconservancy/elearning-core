import { AgePromptComponent } from "./age-prompt.component";
import { SettingsService } from "app/_services/settings.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { SocialAuthService } from "@abacritt/angularx-social-login";
import { ModalService } from "app/_services/modal.service";
import { ReactiveFormsModule, FormsModule } from "@angular/forms";
import { ActivatedRoute, RouterLink } from "@angular/router";
import { AgePromptService } from "app/_services/age-prompt.service";

declare let jQuery: any;

describe("AgePromptComponent", () => {
    beforeEach(() => {
        cy.mount(AgePromptComponent, {
            imports: [ReactiveFormsModule, FormsModule, RouterLink],
            providers: [
                AgePromptService,
                SettingsService,
                LocalStorageService,
                ModalService,
                {
                    provide: ActivatedRoute,
                    useValue: {
                        snapshot: {
                            queryParams: {
                                returnUrl: "/",
                            },
                        },
                    },
                },
                {
                    provide: SocialAuthService,
                    useValue: {
                        authLogin: false,
                        providers: [],
                    },
                },
            ],
        })
            .as("component")
            .then(({ fixture }) => {
                // Stub updateUserInfo method
                const componentInstance = fixture.componentInstance;
                const updateUserInfoStub = cy.stub(componentInstance, "updateUserInfo").resolves(true);
                cy.wrap(updateUserInfoStub).as("updateUserInfoSpy");
                jQuery("#age-prompt").modal("show");

                cy.stub(componentInstance, "notifyParentOfChildSignup").resolves(true);

                const agePromptService = fixture.debugElement.injector.get(AgePromptService);
                agePromptService.userId = 1;
            });
    });

    it("should display username and parents email fields when age is < 13", () => {
        cy.get('[name="age"]').type("12");
        cy.get('[name="age"]').blur();
        cy.get('[name="username"]').should("be.visible");
        cy.get('[name="parentsEmail"]').should("be.visible");
    });

    it("should hide username and parents email fields when age is >= 13", () => {
        cy.get('[name="age"]').type("13");
        cy.get('[name="age"]').blur();
        cy.get('[name="username"]').should("not.be.visible");
        cy.get('[name="parentsEmail"]').should("not.be.visible");
    });

    it("should try to update user's age for >13", () => {
        cy.get('[name="age"]').type("13");
        cy.get('[name="age"]').blur();
        cy.get('button[type="submit"]').click();
        const dob = new Date();
        dob.setFullYear(dob.getFullYear() - 13);
        const formattedDob = dob.toISOString().split("T")[0];
        cy.get("@updateUserInfoSpy").should("have.callCount", 1);
        cy.get("@updateUserInfoSpy").then((spy) => {
            const callArg = (spy as unknown as sinon.SinonStub).getCall(0).args[0];
            expect(callArg).to.deep.equal({
                id: 1,
                approximate_age: "13",
                dob: formattedDob,
            });
        });
    });
});
