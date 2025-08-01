import {
    createRouter,
    createWebHistory,
    type RouteRecordRaw,
} from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useConfigStore } from "@/stores/config";
import NProgress from "nprogress";
import "nprogress/nprogress.css";
// import DashboardLay from '@/pages/dashboard_pengguna/app.vue';

declare module "vue-router" {
    interface RouteMeta {
        pageTitle?: string;
        permission?: string;
    }
}

const routes: Array<RouteRecordRaw> = [
    // {
    // path: "/dashboard_pengguna",
    // name: "dashboard_pengguna",
    // component: () => import("@/pages/dashboard_pengguna/index.vue"),
    // meta: {
    //     pageTitle: "Dashboard Pengguna",
    //     breadcrumbs: ["Halaman Dashboard Pengguna"],
    // },
    // },
    //pengguna
    {
        path: "/",
        redirect: "/dashboard_pengguna",// component: DashboardLay, // ✅ Gunakan layout
        component: () => import("@/pages/dashboard_pengguna/app.vue"),
        children: [
                {
                    path: "/dashboard_pengguna", // akan render Index.vue sebagai default child
                    name: "dashboard_pengguna",
                    component: () => import("@/pages/dashboard_pengguna/index.vue"),
                    meta: {
                    pageTitle: "Dashboard Pengguna",
                    breadcrumbs: ["Halaman Dashboard Pengguna"],
                    },
                },
                {
                    path: "/dashboard_pengguna/ongkir",
                    name: "dashboard_pengguna.ongkir",
                    component: () =>import ("@/pages/dashboard/transaksii/ongkir/ongkir.vue"),
                    meta: {
                        pageTitle: "Ongkir",
                        breadcrumbs: ["Transaksii", "Ongkir"],
                    },
                },
                {
                    path: "/dashboard_pengguna/order",
                    name: "dashboard_pengguna.order",
                    component: () =>import ("@/pages/dashboard/transaksii/order/index.vue"),
                    meta: {
                        pageTitle: "Riwayat",
                        breadcrumbs: ["Transaksii", "Riwayat"],
                    },
                },
                {
                    path: "/dashboard_pengguna/riwayat",
                    name: "dashboard_pengguna.riwayat",
                    component: () =>import ("@/pages/dashboard/transaksii/riwayat/index.vue"),
                    meta: {
                        pageTitle: "Riwayat",
                        breadcrumbs: ["Transaksii", "Riwayat"],
                    },
                },
                {
                    path: "/dashboard_pengguna/tracking",
                    name: "dashboard_pengguna.tracking",
                    component: () =>import ("@/pages/dashboard/tracking/index.vue"),
                    meta: {
                        pageTitle: "Riwayat",
                        breadcrumbs: ["Transaksii", "Riwayat"],
                    },
                },

        ],
    },
    {
        path: "/dashboardk",
        name: "dashboardk",
        component: () => import("@/pages/dashboardk/index.vue"),
        meta: {
            pageTitle: "Dashboardk",
            breadcrumbs: ["Halaman Dashboardk"],
        },
    },
    // {
    //             path: "/dashboard/transaksii/ongkir",
    //             name: "dashboard.transaksii.ongkir",
    //             component: () =>
    //                 import("@/pages/dashboard/transaksii/ongkir/ongkir.vue"),
    //             meta: {
    //                 pageTitle: "Ongkir",
    //                 breadcrumbs: ["Transaksii", "Ongkir"],
    //             },
    // },
    {
                path: "/dashboard/transaksii/order",
                name: "dashboard.transaksii.order",
                component: () =>
                    import("@/pages/dashboard/transaksii/order/index.vue"),
                meta: {
                    pageTitle: "Order",
                    breadcrumbs: ["Transaksii", "Order"],
                },
    },
    {
                path: "/dashboard/transaksii/riwayat",
                name: "dashboard.transaksii.riwayat",
                component: () =>
                    import("@/pages/dashboard/transaksii/riwayat/index.vue"),
                meta: {
                    pageTitle: "Riwayat",
                    breadcrumbs: ["Transaksii", "Riwayat"],
                },
    },
    {
                path: "/dashboard/transaksi/order",
                name: "dashboard.transaksi.order",
                component: () =>
                    import("@/pages/dashboard/transaksi/order/index.vue"),
                meta: {
                    pageTitle: "Order",
                    breadcrumbs: ["Transaksi", "Order"],
                },
    },
    {
                path: "/dashboard/transaksi/riwayat",
                name: "dashboard.transaksi.riwayat",
                component: () =>
                    import("@/pages/dashboard/transaksi/riwayatt/idex.vue"),
                meta: {
                    pageTitle: "Riwayat",
                    breadcrumbs: ["Transaksi", "Riwayat"],
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
            // {
            //     path: "/dashboard/pengiriman",
            //     name: "dashboard.pengiriman",
            //     component: () => import("@/pages/dashboard/pengiriman/index.vue"),
            //     meta: {
            //         pageTitle: "Pengiriman",
            //         // breadcrumbs: ["Halaman", "Akun dan Profl"],
            //     },
            // },
           
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
            {
                path: "/dashboard/gudang",
                name: "dashboard.gudang",
                component: () => import("@/pages/dashboard/gudang/index.vue"),
                meta: {
                    // pageTitle: "",
                    // breadcrumbs: ["Halaman", "Akun dan Profl"],
                },
            },
            {
                path: "/dashboard/riwayat",
                name: "dashboard.riwayat",
                component: () => import("@/pages/dashboard/gudang/riwayat.vue"),
                // meta: {
                //     pageTitle: "Riwayat Pengiriman Gudang",
                //     breadcrumbs: ["Dashboard", "Riwayat Gudang"],
                // },
            },



            //kurir
            {
                path: "/dashboard/trans/orderan",
                name: "dashboard.trans.orderan",
                component: () => import("@/pages/dashboard/trans/orderan/Inde.vue"),
                meta: {
                    // pageTitle: "Halaman Order",
                    // breadcrumbs: ["Halaman", "Akun dan Profl"],
                },
            },
            {
                path: "/dashboard/trans/riwayat",
                name: "dashboard.trans.riwayat",
                component: () => import("@/pages/dashboard/trans/riwayat/index.vue"),
                meta: {
                    pageTitle: "Riwayat",
                    // breadcrumbs: ["Halaman", "Akun dan Profl"],
                },
            },
            {
                path: "/dashboard/transs/riwayat",
                name: "dashboard.transs.riwayat",
                component: () => import("@/pages/dashboard/transs/riwayat/index.vue"),
                meta: {
                    // pageTitle: "Riwayat",
                    // breadcrumbs: ["Halaman", "Akun dan Profl"],
                },
            },
            {
                path: "/dashboard/transs/riwayatt",
                name: "dashboard.transs.riwayatt",
                component: () => import("@/pages/dashboard/transs/riwayatt/index.vue"),
                meta: {
                    // pageTitle: "Riwayat",
                    // breadcrumbs: ["Halaman", "Akun dan Profl"],
                },
            },
            {
                path: "/dashboard/transs/orderan",
                name: "dashboard.transs.orderan",
                component: () => import("@/pages/dashboard/transs/orderan/index.vue"),
                meta: {
                    // pageTitle: "Riwayat",
                    // breadcrumbs: ["Halaman", "Akun dan Profl"],
                },
            },
            {
                path: "/dashboard/transs/orderann",
                name: "dashboard.transs.orderann",
                component: () => import("@/pages/dashboard/transs/orderann/index.vue"),
                meta: {
                    // pageTitle: "Riwayat",
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
            {
                path: '/dashboard/master/users//OtpPage.vue',
                name: 'dashboard.master.users/OtpPage.vue',
                component: () => import('@/pages/dashboard/master/users/OtpPage.vue'),
            },
            {
                path: '/dashboard/master/users/verify-otp',
                name: 'dashboard.master.users.VerifyOtp',
                component: () => import('@/pages/dashboard/master/users/VerifyOtp.vue'),
            },
            // {
            //     path: "/payment/success",
            //     name: "PaymentSuccess",
            //     component: () => import("@/views/PaymentSuccess.vue"),
            // },
        ],
    },
    


    // {
    //     path: "/dashboard/ongkir",
    //     name: "dashboard.ongkir",
    //     component: () => import("@/pages/dashboard/transaksii/ongkir/ongkir.vue"),
    //     meta: {
    //         pageTitle: "Halaman Ongkir",
    //         // breadcrumbs: ["Halaman", "Akun dan Profl"],
    //     },
    // },
    // {
    //     path: "/dashboard/order",
    //     name: "dashboard.order",
    //     component: () => import("@/pages/dashboard/transaksi/idex.vue"),
    //     meta: {
    //         pageTitle: "Halaman Order Kurir",
    //         // breadcrumbs: ["Halaman", "Akun dan Profl"],
    //     },
    // },
    // {
    //     path: "/dashboard/orderProv",
    //     name: "dashboard.orderProv",
    //     component: () => import("@/pages/dashboard/transaksii/order/index.vue"),
    //     meta: {
    //         pageTitle: "Halaman Order Kurir",
    //         // breadcrumbs: ["Halaman", "Akun dan Profl"],
    //     },
    // },
    // {
    //     path: "/dashboard/riwayatt",
    //     name: "dashboard.riwayatt",
    //     component: () => import("@/pages/dashboard/riwayatt/idex.vue"),
    //     meta: {
    //         pageTitle: "Riwayat",
    //         // breadcrumbs: ["Halaman", "Akun dan Profl"],
    //     },
    // },
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
            {
                path: "/sign-up",
                name: "sign-up",
                component: () => import("@/pages/auth/sign-up/Index.vue"),
                meta: {
                    pageTitle: "Sign Up",
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
            } else if (to.name === "dashboard" && authStore.user.role?.name === "pengguna") {
                next({ name: "dashboard_pengguna" });
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
