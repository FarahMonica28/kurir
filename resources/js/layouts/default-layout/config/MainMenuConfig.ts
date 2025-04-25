import type { MenuItem } from "@/layouts/default-layout/config/types";

const MainMenuConfig: Array<MenuItem> = [
    {
        pages: [
            {
                heading: "Dashboard",
                name: "dashboard",
                route: "/dashboard",
                keenthemesIcon: "element-11",
            },
            {
                heading: "Dashboardk", // ditampilan
                name: "dashboardk", // di role
                route: "/dashboardk",
                keenthemesIcon: "element-11",
            },
        ],
    },

    // WEBSITE
    {
        heading: "Website",
        route: "/dashboard/website",
        name: "website",
        pages: [
            // MASTER
            {
                sectionTitle: "Master",
                route: "/master",
                keenthemesIcon: "cube-3",
                name: "master",
                sub: [
                    {
                        sectionTitle: "User",
                        route: "/users",
                        name: "master-user",
                        sub: [
                            {
                                heading: "Role",
                                name: "master-role",
                                route: "/dashboard/master/users/roles",
                            },
                            {
                                heading: "User",
                                name: "master-user",
                                route: "/dashboard/master/users",
                            },
                            {
                                heading: "Kurir",
                                route: "/dashboard/kurir",
                                name: "kurir",
                            },
                            {
                                heading: "Pengguna",
                                route: "/dashboard/pengguna",
                                name: "pengguna",
                            },
                        ],
                    },
                ],
            },
            {
                heading: "Setting",
                route: "/dashboard/setting",
                name: "setting",
                keenthemesIcon: "setting-2",
            },
            {
                heading: "Akun",
                route: "/dashboard/akun",
                name: "akun",
                keenthemesIcon: "bi bi-person-circle",
            },
            {
                heading: "Order",
                route: "/dashboard/order",
                name: "transaksi",
                keenthemesIcon: "bi bi-bookmark-plus",
            },
            {
                heading: "Orderr",
                route: "/dashboard/orderr",
                name: "trans",
                keenthemesIcon: "bi bi-bookmark-plus",
            },
            {
                heading: "Riwayat",
                route: "/dashboard/riwayat",
                name: "riwayat",
                keenthemesIcon: "bi bi-bookmark-check",
            },
            {
                heading: "Riwayatt",
                route: "/dashboard/riwayatt",
                name: "riwayatt",
                keenthemesIcon: "bi bi-bookmark-check",
            },
            {
                heading: "Pengiriman",
                route: "/dashboard/pengiriman",
                name: "pengiriman",
                keenthemesIcon: "bi bi-truck",
            },
            {
                heading: "Tracking",
                route: "/dashboard/Tracking",
                name: "tracking",
                keenthemesIcon: "bi bi-geo-alt-fill",
                
            },
            {
                heading: "Pengirimans",
                route: "/dashboardk/Pengirimans",
                name: "pengirimans",
                keenthemesIcon: "bi bi-truck",
            },
            {
                heading: "Rute",
                route: "/dashboardk/rute",
                name: "rute",
                keenthemesIcon: "bi bi-truck",
                
            },
            
        ],
    },
];

export default MainMenuConfig;
