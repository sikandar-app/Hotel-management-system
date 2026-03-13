import { createRouter, createWebHistory } from 'vue-router'
import { hasPermission, isAdmin } from '@/helpers/permission';
import { useAppStore } from '@/state/pinia/appStore';

const routes = [
    {
        path: '/',
        name: 'dashboard',
        meta: { 
                title: 'Dashboard', 
                authRequired: true, 
            },
        component: () => import('../views/dashboard.vue'),
    },
    {
        path: '/profile',
        name: 'Profile',
        meta: { 
            title: 'Profile', 
            authRequired: true, 
        },
        component: () => import('../views/home.vue'),
    },
    {
        path: '/users',
        name: 'Users',
        meta: { 
            title: 'Users', 
            authRequired: true, 
        },
        component: () => import('../views/user-management/users/Table.vue'),
        beforeEnter: (to, from, next) => {
            if (hasPermission(['users-all', 'users-view'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: '/user/add',
        name: 'AddUser',
        component: () => import('../views/user-management/users/Add.vue'),
        meta: { requiresAuth: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['users-all', 'users-create'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: '/user/edit/:id',
        name: 'EditUser',
        component: () => import('../views/user-management/users/Edit.vue'),
        meta: { requiresAuth: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['users-all', 'users-edit'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/roles",
        name: "Roles",
        component: () => import("../views/user-management/roles/Roles.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['roles-all', 'roles-view'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: '/roles/:id/edit',
        name: 'EditRole',
        component: () => import('../views/user-management/roles/AddEditRole.vue'),
        meta: { title: 'Edit Role' },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['roles-all', 'roles-edit'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: '/roles/add',
        name: 'AddRole',
        component: () => import('../views/user-management/roles/AddEditRole.vue'),
        meta: { title: 'Add Role' },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['roles-all', 'roles-create'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/permissions",
        name: "Permissions",
        component: () => import("../views/user-management/permissions/Permissions.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['permissions-all', 'permissions-view'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/settings",
        name: "Settings",
        component: () => import("../views/user-management/settings/Table.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (isAdmin()) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/settings/:id",
        name: "EditSetting",
        component: () => import("../views/user-management/settings/Edit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (isAdmin()) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/bookings",
        name: "Bookings",
        component: () => import("../views/booking/Table.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['booking-all', 'booking-view'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/bookings/add",
        name: "AddBooking",
        component: () => import("../views/booking/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['booking-all', 'booking-create'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/bookings/:id",
        name: "ViewBooking",
        component: () => import("../views/booking/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['booking-all', 'booking-view'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/bookings/edit/:id",
        name: "EditBooking",
        component: () => import("../views/booking/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['booking-all', 'booking-edit'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/rooms",
        name: "Rooms",
        component: () => import("../views/room/Table.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['room-all', 'room-view'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/rooms/add",
        name: "AddRoom",
        component: () => import("../views/room/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['room-all', 'room-create'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/rooms/:id",
        name: "ViewRoom",
        component: () => import("../views/room/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['room-all', 'room-view'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/rooms/edit/:id",
        name: "EditRoom",
        component: () => import("../views/room/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['room-all', 'room-edit'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/expenses",
        name: "Expenses",
        component: () => import("../views/expenses/Table.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['expenses-all', 'expenses-view'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/expenses/add",
        name: "AddExpense",
        component: () => import("../views/expenses/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['expenses-all', 'expenses-create'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/expenses/:id",
        name: "ViewExpense",
        component: () => import("../views/expenses/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['expenses-all', 'expenses-view'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/expenses/edit/:id",
        name: "EditExpense",
        component: () => import("../views/expenses/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['expenses-all', 'expenses-edit'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/taxes",
        name: "Taxes",
        component: () => import("../views/tax/Table.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['tax-all', 'tax-view'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/taxes/add",
        name: "AddTax",
        component: () => import("../views/tax/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['tax-all', 'tax-create'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/taxes/:id",
        name: "ViewTax",
        component: () => import("../views/tax/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['tax-all', 'tax-view'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/taxes/edit/:id",
        name: "EditTax",
        component: () => import("../views/tax/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['tax-all', 'tax-edit'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/invoices/add/:booking_id",
        name: "AddInvoice",
        component: () => import("../views/invoices/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['invoices-all', 'invoices-create'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/invoices/edit/:booking_id/:invoice_id",
        name: "EditInvoice",
        component: () => import("../views/invoices/AddEdit.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['invoices-all', 'invoices-create'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: "/monthly-occupancy",
        name: "MonthlyOccupancy",
        component: () => import("../views/monthlyOccupancy/Table.vue"),
        meta: { authRequired: true },
        beforeEnter: (to, from, next) => {
            if (hasPermission(['monthly-occupancy-all', 'monthly-occupancy-view'])) {
                next();
            } else {
                next('/unauthorized');
            }
        }
    },
    {
        path: '/login',
        name: 'login',
        meta: { title: 'Login' },
        component: () => import('../views/account/login.vue')
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// Before each route evaluates...
router.beforeEach(async (routeTo, routeFrom, next) => {
    // set title name
    if (routeTo.meta.title != undefined) {
        document.title = routeTo.meta.title + " | HotelPro - Hotel Management System";
    }

    const authRequired = routeTo.matched.some((route) => route.meta.authRequired);
    if (!authRequired) return next();
    
    const store = useAppStore();
    if (store?.auth?.user) {
        next();
    } else {
        next({ name: 'login', query: { redirectFrom: routeTo.fullPath } });
    }

});

export default router;