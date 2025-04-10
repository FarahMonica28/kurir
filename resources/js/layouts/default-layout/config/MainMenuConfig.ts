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
                heading: "Data Kurir",
                route: "/dashboard/datakurir",
                name: "kurir",
                keenthemesIcon: "bi bi-person-circle",
            },
            {
                heading: "Akun",
                route: "/dashboard/akun",
                name: "akun",
                keenthemesIcon: "bi bi-person-circle",
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
                name: "Tracking",
                keenthemesIcon: "bi bi-truck",
            },
            
        ],
    },
];

export default MainMenuConfig;
