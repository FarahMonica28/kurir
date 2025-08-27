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
            {
                heading: "Home", // ditampilan
                name: "home", // di role
                route: "/home",
                keenthemesIcon: "element-11",
            },
        ],
    },

    // WEBSITE
    {
        heading: "Web",
        route: "/home/web",
        name: "web",
        pages: [
                {
                sectionTitle: "Tambah",
                route: "/tambah",
                keenthemesIcon: "plus",
                name: "tambah",
                sub:[
                        {
                            heading: "Kategori",
                            name: "tambah-kategori",
                            route: "/home/tambah/kategori",
                        },
                        {
                            heading: "Barang",
                            name: "tambah-barang",
                            route: "/home/tambah/barang",
                        },
                ],
            },
        ]
    },
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
            {
                heading: "Gudang",
                route: "/dashboard/gudang",
                name: "gudang",
                keenthemesIcon: "bi bi-bookmark-plus",
            },
            {
                heading: "Riwayat",
                route: "/dashboard/riwayat",
                name: "riwayat",
                keenthemesIcon: "bi bi-bookmark-plus",
            },
            
            //pengguna
            {
                sectionTitle: "Order Kurir",
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
                            {
                                heading: "Riwayat",
                                name: "transaksii-riwayat",
                                route: "/dashboard/transaksii/riwayat",
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

            //kurir
            {
                heading: "Akun",
                route: "/dashboard/akun",
                name: "akun",
                keenthemesIcon: "bi bi-person-circle",
            },
 
            {
                sectionTitle: "Orderan",
                route: "/transs",
                keenthemesIcon: "cube-3",
                name: "transs",
                sub: [
                    {
                        heading: "Ambil Orderan",
                        name: "transs-orderan",
                        route: "/dashboard/transs/orderan",
                    },
                    {
                        heading: "Riwayatt Ambil",
                        name: "transs-riwayatt",
                        route: "/dashboard/transs/riwayatt",
                    },
                    {
                        heading: "Antar Orderan",
                        name: "transs-orderann",
                        route: "/dashboard/transs/orderann",
                    },
                    {
                        heading: "Riwayat Antar",
                        name: "transs-riwayat",
                        route: "/dashboard/transs/riwayat",
                    },
                    
                ],
            },
            {
            sectionTitle: "Orderan Surabaya",
                route: "/trans",
                keenthemesIcon: "cube-3",
                name: "trans",
                sub: [
                            {
                                heading: "Orderan",
                                name: "trans-orderan",
                                route: "/dashboard/trans/orderan",
                            },
                            {
                                heading: "order",
                                name: "trans-riwayat",
                                route: "/dashboard/trans/riwayat",
                            },
                    
                ],
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
