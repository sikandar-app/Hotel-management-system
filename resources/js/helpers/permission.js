import { useAppStore } from '@/state/pinia/appStore';

export function hasPermission(requiredPermissions) {
    const store = useAppStore();
    const userPermissions = store.auth.permissions;
    // Check if user permissions are available
    if (!Array.isArray(userPermissions) || userPermissions.length === 0) {
        console.warn("No permissions available.");
        return false; // No permissions means no access
    }

    // Check if any of the required permissions are included in the user's permissions
    return requiredPermissions.some(permission => userPermissions.includes(permission));
}

export function isAdmin() {
    const store = useAppStore();
    const user = store.auth.user;
    return user && Array.isArray(user.roles) && user.roles.some(role => role.name === 'Owner');
}

export function nextAllowPage() {
    const store = useAppStore();
    const userPermissions = store.auth.permissions;
    // List of allowed pages and their required permissions
    const allowPages = [];

    // Check if user permissions are available
    if (!Array.isArray(userPermissions) || userPermissions.length === 0) {
        console.warn("No permissions available.");
        return false; // No permissions mean no access
    }

    // Find and return the page name for the first matching permission
    const allowedPage = allowPages.find(item => userPermissions.includes(item.permission));

    if (allowedPage) {
        console.log(`Permission found for page: ${allowedPage.page}`);
        return allowedPage.page; // Return the page name
    } else {
        console.warn("No matching permissions found.");
        return false; // No matching permissions
    }
}
