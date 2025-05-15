import {
    createRouter,
    createWebHistory,
    type RouteRecordRaw,
} from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useConfigStore } from "@/stores/config";
import NProgress from "nprogress";
import "nprogress/nprogress.css";

declare module "vue-router" {
    interface RouteMeta {
        pageTitle?: string;
        permission?: string;
    }
}

const routes: Array<RouteRecordRaw> = [
    {
        path: "/",
        redirect: "/dashboard",
        component: () => import("@/layouts/default-layout/DefaultLayout.vue"),
        meta: {
            middleware: "auth",
        },
        children: [
            {
                path: "/dashboard",
                name: "dashboard",
                component: () => import("@/pages/dashboard/Index.vue"),
                // meta: {
                //     pageTitle: "Dashboard",
                //     breadcrumbs: ["Dashboard"],
                // },
            },
            {
                path: "/dashboardk/pengirimans",
                name: "dashboardk.pengirimans",
                component: () => import("@/pages/dashboardk/pengiriman/index.vue"),
                meta: {
                    pageTitle: "Halaman Pengiriman",
                    // breadcrumbs: ["Dashboardk"],
                },
            },
            {
                path: "/dashboardk/rute",
                name: "dashboardk.rute",
                component: () => import("@/pages/dashboardk/rute/index.vue"),
                meta: {
                    pageTitle: "Halaman Rute",
                    // breadcrumbs: ["Dashboardk"],
                },
            },
            {
                path: "/dashboard/kurir",
                name: "dashboard.kurir",
                component: () => import("@/pages/dashboard/kurir/index.vue"),
                meta: {
                    pageTitle: "Kurir",
                    // breadcrumbs: ["Halaman", "Kurir"],
                },
            },
            {
                path: "/dashboard/pengguna",
                name: "dashboard.pengguna",
                component: () => import("@/pages/dashboard/pengguna/index.vue"),
                meta: {
                    pageTitle: "Pengguna",
                    // breadcrumbs: ["Halaman", "Kurir"],
                },
            },
            {
                path: "/dashboard/akun",
                name: "dashboard.akun",
                component: () => import("@/pages/dashboard/akun/index.vue"),
                meta: {
                    pageTitle: "Akun dan Profil",    
                    breadcrumbs: ["Halaman", "Akun dan Profl"],
                },
            },
            {
                path: "/dashboard/pengiriman",
                name: "dashboard.pengiriman",
                component: () => import("@/pages/dashboard/pengiriman/index.vue"),
                meta: {
                    pageTitle: "Pengiriman",
                    // breadcrumbs: ["Halaman", "Akun dan Profl"],
                },
            },
            {
                path: "/dashboard/tracking",
                name: "dashboard.tracking",
                component: () => import("@/pages/dashboard/tracking/index.vue"),
                meta: {
                    pageTitle: "Tracking",
                    // breadcrumbs: ["Halaman", "Akun dan Profl"],
                },
            },
            {
                path: "/dashboard/profile",
                name: "dashboard.profile",
                component: () => import("@/pages/dashboard/profile/Index.vue"),
                meta: {
                    pageTitle: "Profile",
                    breadcrumbs: ["Profile"],
                },
            },
            {
                path: "/dashboard/setting",
                name: "dashboard.setting",
                component: () => import("@/pages/dashboard/setting/Index.vue"),
                meta: {
                    pageTitle: "Website Setting",
                    breadcrumbs: ["Website", "Setting"],
                },
            },

            //admin
            {
                path: "/dashboard/orderan",
                name: "dashboard.orderan",
                component: () => import("@/pages/dashboard/orderan/inde.vue"),
                meta: {
                    pageTitle: "Order Kurir",
                    // breadcrumbs: ["Halaman", "Akun dan Profl"],
                },
            },


            //kurir
            {
                path: "/dashboard/orderr",
                name: "dashboard.orderr",
                component: () => import("@/pages/dashboard/trans/Inde.vue"),
                meta: {
                    pageTitle: "Halaman Order",
                    // breadcrumbs: ["Halaman", "Akun dan Profl"],
                },
            },
            {
                path: "/dashboard/riwayat",
                name: "dashboard.riwayat",
                component: () => import("@/pages/dashboard/riwayat/index.vue"),
                meta: {
                    pageTitle: "Riwayat",
                    // breadcrumbs: ["Halaman", "Akun dan Profl"],
                },
            },

            
            // MASTER
            {
                path: "/dashboard/master/users/roles",
                name: "dashboard.master.users.roles",
                component: () =>
                    import("@/pages/dashboard/master/users/roles/Index.vue"),
                meta: {
                    pageTitle: "User Roles",
                    breadcrumbs: ["Master", "Users", "Roles"],
                },
            },
            {
                path: "/dashboard/master/users",
                name: "dashboard.master.users",
                component: () =>
                    import("@/pages/dashboard/master/users/Index.vue"),
                meta: {
                    pageTitle: "Users",
                    breadcrumbs: ["Master", "Users"],
                },
            },
        ],
    },
    

    //pengguna
    {
        path: "/dashboardk",
        name: "dashboardk",
        component: () => import("@/pages/dashboardk/index.vue"),
        meta: {
            pageTitle: "Dashboardk",
            breadcrumbs: ["Halaman Dashboardk"],
        },
    },
    {
        path: "/dashboard/order",
        name: "dashboard.order",
        component: () => import("@/pages/dashboard/transaksi/index.vue"),
        meta: {
            pageTitle: "Halaman Order Kurir",
            // breadcrumbs: ["Halaman", "Akun dan Profl"],
        },
    },
    {
        path: "/dashboard/riwayatt",
        name: "dashboard.riwayatt",
        component: () => import("@/pages/dashboard/riwayatt/idex.vue"),
        meta: {
            pageTitle: "Riwayat",
            // breadcrumbs: ["Halaman", "Akun dan Profl"],
        },
    },
    {
        path: "/",
        component: () => import("@/layouts/AuthLayout.vue"),
        children: [
            {
                path: "/sign-in",
                name: "sign-in",
                component: () => import("@/pages/auth/sign-in/Index.vue"),
                meta: {
                    pageTitle: "Sign In",
                    middleware: "guest",
                },
            },
        ],
    },
    {
        path: "/",
        component: () => import("@/layouts/SystemLayout.vue"),
        children: [
            {
                // the 404 route, when none of the above matches
                path: "/404",
                name: "404",
                component: () => import("@/pages/errors/Error404.vue"),
                meta: {
                    pageTitle: "Error 404", 
                },
            },
            {
                path: "/500",
                name: "500",
                component: () => import("@/pages/errors/Error500.vue"),
                meta: {
                    pageTitle: "Error 500",
                },
            },
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: "/404",
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to) {
        // If the route has a hash, scroll to the section with the specified ID; otherwise, scroll to the top of the page.
        if (to.hash) {
            return {
                el: to.hash,
                top: 80,
                behavior: "smooth",
            };
        } else {
            return {
                top: 0,
                left: 0,
                behavior: "smooth",
            };
        }
    },
});

router.beforeEach(async (to, from, next) => {
    if (to.name) {
        // Start the route progress bar.
        NProgress.start();
    }

    const authStore = useAuthStore();
    const configStore = useConfigStore();

    // current page view title
    if (to.meta.pageTitle) {
        document.title = `${to.meta.pageTitle} - ${import.meta.env.VITE_APP_NAME
            }`;
    } else {
        document.title = import.meta.env.VITE_APP_NAME as string;
    }

    // reset config to initial state
    configStore.resetLayoutConfig();

    // verify auth token before each page change
    if (!authStore.isAuthenticated) await authStore.verifyAuth();

    // before page access check if page requires authentication
    if (to.meta.middleware == "auth") {
        if (authStore.isAuthenticated) {
            if (
                to.meta.permission &&
                !authStore.user.permission.includes(to.meta.permission)
            ) {
                next({ name: "404" });
            } else if (to.meta.checkDetail == false) {
                next();
            }

            next();
        } else {
            next({ name: "sign-in" });
        }
    } else if (to.meta.middleware == "guest" && authStore.isAuthenticated) {
        next({ name: "dashboard" });
    } else {
        next();
    }
});

router.afterEach(() => {
    // Complete the animation of the route progress bar.
    NProgress.done();
});

export default router;
