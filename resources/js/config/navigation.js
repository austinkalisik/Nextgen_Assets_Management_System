export const navigationSections = [
    {
        label: 'Main Menu',
        items: [
            {
                label: 'Dashboard',
                to: '/dashboard',
                match: ['/dashboard'],
                icon: 'dashboard',
            },
            {
                label: 'Inventory',
                to: '/inventory',
                match: ['/inventory', '/items'],
                icon: 'inventory',
            },
            {
                label: 'Assignments',
                to: '/assignments',
                match: ['/assignments'],
                icon: 'assignments',
            },
            {
                label: 'Suppliers',
                to: '/suppliers',
                match: ['/suppliers'],
                icon: 'suppliers',
            },
        ],
    },
    {
        label: 'Management',
        items: [
            {
                label: 'Categories',
                to: '/categories',
                match: ['/categories'],
                icon: 'categories',
            },
            {
                label: 'Departments',
                to: '/departments',
                match: ['/departments'],
                icon: 'departments',
            },
            {
                label: 'Users',
                to: '/users',
                match: ['/users'],
                icon: 'users',
            },
            {
                label: 'Notifications',
                to: '/notifications',
                match: ['/notifications'],
                icon: 'notifications',
            },
            {
                label: 'Settings',
                to: '/settings',
                match: ['/settings'],
                icon: 'settings',
            },
            {
                label: 'Profile',
                to: '/profile',
                match: ['/profile'],
                icon: 'profile',
            },
        ],
    },
];

export const navigationItems = navigationSections.flatMap((section) => section.items);

