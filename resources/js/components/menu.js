import { hasPermission, isAdmin } from '@/helpers/permission.js';

export const menuItems = [
    {
        id: 1,
        label: "menuitems.menu.text",
        isTitle: true,
        hasPermission: hasPermission(['dashboard'])
    },
    {
        id: 2,
        label: "menuitems.dashboards.text",
        icon: "bx-home-circle",
        link: "/",
        hasPermission: hasPermission(['dashboard'])
    },
    {
        id: 3,
        label: "Bookings",
        icon: "bx-book-bookmark",
        link: "/bookings",
        hasPermission: hasPermission(['booking-all', 'booking-view']) 
    },
    {
        id: 4,
        label: "Rooms",
        icon: "bx-bed",
        link: "/rooms",
        hasPermission: hasPermission(['room-all', 'room-view']) 
    },
    {
        id: 5,
        label: "Taxes",
        icon: "bx-money",
        link: "/taxes",
        hasPermission: hasPermission(['tax-all', 'tax-view']) 
    },
    {
        id: 6,
        label: "Monthly Occupancy",
        icon: "bx-calendar",
        link: "/monthly-occupancy",
        hasPermission: hasPermission(['monthly-occupancy-all', 'monthly-occupancy-view']) 
    },
    {
        id: 7,
        label: "Expenses",
        icon: "bx-wallet-alt",
        link: "/expenses",
        hasPermission: hasPermission(['expenses-all', 'expenses-view']) 
    },
    {
        id: 6,
        label: "Users Management",
        icon: "bx-user-circle",
        hasPermission: hasPermission(['users-all', 'users-view']) || hasPermission(['roles-all', 'roles-view']),
        subItems: [
            {
                id: 7,
                label: "Users",
                link: "/users",
                parentId: 6,
                hasPermission: hasPermission(['users-all', 'users-view'])
            },
            {
                id: 8,
                label: "Roles",
                isTitle: false,
                link: "/roles",
                parentId: 6,
                hasPermission: hasPermission(['roles-all', 'roles-view'])
            },
            {
                id: 9,
                label: "Permissions",
                isTitle: false,
                link: "/permissions",
                parentId: 6,
                hasPermission: hasPermission(['permissions-all', 'permissions-view'])
            },
            {
                id: 10,
                label: "Settings",
                link: "/settings",
                parentId: 6,
                hasPermission: isAdmin()
            },
        ],
    },
];
