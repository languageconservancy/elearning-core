import { Injectable } from "@angular/core";
import { BaseService } from "app/_services/base.service";
import { Settings } from "app/_constants/api.constants";

export const PlatformRoles = {
    Admin: "superadmin",
    User: "user",
    Teacher: "teacher",
    Student: "student",
    Moderator: "moderator",
    ContentDeveloper: "content developer",
} as const;

type Role = {
    role: string;
    id: number;
};

@Injectable({
    providedIn: "root",
})
export class PlatformRolesService {
    private roleNameToIdMap: Map<string, number> = new Map();
    private roleIdToNameMap: Map<number, string> = new Map();

    constructor(private baseService: BaseService) {}

    /**
     * Fetches the platform roles from the server.
     * @returns {Promise<void>} Promise that resolves when the platform roles are fetched.
     */
    public async fetchPlatformRoles(): Promise<void> {
        try {
            const res = await this.baseService.callApi(
                Settings.GET_PLATFORM_ROLES,
                "GET",
                {},
                {},
                "site",
                false,
            );
            if (!res.data?.status || !res.data?.results) {
                throw new Error("Error fetching platform roles. " + res.data.message);
            }
            const roles = res.data.results;

            this.roleIdToNameMap.clear();
            this.roleNameToIdMap.clear();

            roles.forEach((r: any) => {
                this.roleNameToIdMap.set(r.role, r.id);
                this.roleIdToNameMap.set(r.id, r.role);
            });
        } catch (error) {
            console.error("Error fetching platform roles", error);

        }
    }

    public getRoleId(roleNameKey: keyof typeof PlatformRoles): number {
        const roleValue = PlatformRoles[roleNameKey];
        if (!this.roleNameToIdMap.has(roleValue)) {
            throw new Error(`Role ID ${roleValue} does not exist`);
        }
        return this.roleNameToIdMap.get(roleValue);
    }

    public getRoleName(roleId: number): string {
        if (!this.roleIdToNameMap.has(roleId)) {
            throw new Error(`Role name ${roleId} does not exist`);
        }
        return this.roleIdToNameMap.get(roleId);
    }

    public hasRole(roleId: number, roleNameKey: keyof typeof PlatformRoles): boolean {
        const roleValue = PlatformRoles[roleNameKey];
        if (!this.roleNameToIdMap.has(roleValue)) {
            throw new Error(`Role ${roleValue} does not exist`);
        }
        if (!roleId) {
            throw new Error("User role is not defined, " + JSON.stringify(roleId));
        }

        return roleId === this.roleNameToIdMap.get(roleValue);
    }

    public hasRoleId(role: Role, roleId: number): boolean {
        if (!this.roleIdToNameMap.has(roleId)) {
            throw new Error(`Role ID ${roleId} does not exist`);
        }
        if (!role) {
            throw new Error("User role is not defined");
        }

        return role.id === roleId;
    }

    public isStudent(roleId: number): boolean {
        return this.hasRole(roleId, "Student");
    }

    public isTeacher(roleId: number): boolean {
        return this.hasRole(roleId, "Teacher");
    }

    public isAdmin(roleId: number): boolean {
        return this.hasRole(roleId, "Admin");
    }

    public isUser(roleId: number): boolean {
        return this.hasRole(roleId, "User");
    }

    public isModerator(roleId: number): boolean {
        return this.hasRole(roleId, "Moderator");
    }

    public isContentDeveloper(roleId: number): boolean {
        return this.hasRole(roleId, "ContentDeveloper");
    }
}
