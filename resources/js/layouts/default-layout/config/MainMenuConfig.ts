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
            //admin
            {
                heading: "Orderan",
                route: "/dashboard/orderan",
                name: "orderan",
                keenthemesIcon: "bi bi-bookmark-plus",
            },
            
            //pengguna
            {
                sectionTitle: "Order Antar Provinsi",
                route: "/transaksii",
                keenthemesIcon: "cube-3",
                name: "transaksii",
                sub: [
                            {
                                heading: "Cek Ongkir",
                                name: "transaksii-ongkir",
                                route: "/dashboard/transaksii/ongkir",
                            },
                            {
                                heading: "order",
                                name: "transaksii-order",
                                route: "/dashboard/transaksii/order",
                            },
                    
                ],
            },
            {
                sectionTitle: "Order Antar Surabaya",
                route: "/transaksi",
                keenthemesIcon: "cube-3",
                name: "transaksi",
                sub: [
                            {
                                heading: "order",
                                name: "transaksi-order",
                                route: "/dashboard/transaksi/order",
                            },
                            {
                                heading: "Riwayatt",
                                name: "transaksi-riwayat",
                                route: "/dashboard/transaksi/riwayatt",
                            },
                    
                ],
            },
            // {
            //     heading: "Ongkir",
            //     route: "/dashboard/ongkir",
            //     name: "ongkir",
            //     keenthemesIcon: "bi bi-bookmark-plus",
            // },
            // {
            //     heading: "Order",
            //     route: "/dashboard/order",
            //     name: "transaksi",
            //     keenthemesIcon: "bi bi-bookmark-plus",
            // },
            // {
            //     heading: "Order Antar Provinsi",
            //     route: "/dashboard/orderProv",
            //     name: "transaksii",
            //     keenthemesIcon: "bi bi-bookmark-plus",
            // },
            // {
            //     heading: "Riwayatt",
            //     route: "/dashboard/riwayatt",
            //     name: "riwayatt",
            //     keenthemesIcon: "bi bi-bookmark-check",
            // },
            
            //kurir
            {
                heading: "Akun",
                route: "/dashboard/akun",
                name: "akun",
                keenthemesIcon: "bi bi-person-circle",
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
