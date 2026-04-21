import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,jsx,ts,tsx}',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                soft: '0 10px 30px rgba(15, 23, 42, 0.08)',
            },
            colors: {
                brand: {
                    50: '#eef4ff',
                    100: '#dce8ff',
                    500: '#2563eb',
                    600: '#1d4ed8',
                    700: '#1e40af',
                    900: '#0f172a',
                },
            },
        },
    },
    plugins: [forms],
};
