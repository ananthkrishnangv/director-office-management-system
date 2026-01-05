/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                // Fluent UI inspired colors
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#0078d4', // Microsoft Blue
                    600: '#0066b8',
                    700: '#005a9e',
                    800: '#004c87',
                    900: '#003d6d',
                },
                accent: {
                    50: '#fdf4ff',
                    100: '#fae8ff',
                    200: '#f5d0fe',
                    300: '#f0abfc',
                    400: '#e879f9',
                    500: '#8a2be2', // Accent Purple
                    600: '#7c3aed',
                    700: '#6d28d9',
                    800: '#5b21b6',
                    900: '#4c1d95',
                },
                success: {
                    500: '#107c10',
                    600: '#0e6b0e',
                },
                warning: {
                    500: '#ffb900',
                    600: '#d39e00',
                },
                danger: {
                    500: '#d13438',
                    600: '#a82828',
                },
                neutral: {
                    50: '#fafafa',
                    100: '#f5f5f5',
                    200: '#e5e5e5',
                    300: '#d4d4d4',
                    400: '#a3a3a3',
                    500: '#737373',
                    600: '#525252',
                    700: '#404040',
                    800: '#262626',
                    900: '#171717',
                },
            },
            fontFamily: {
                sans: ['Noto Sans', 'Segoe UI', 'system-ui', 'sans-serif'],
            },
            boxShadow: {
                'fluent': '0 2px 4px rgba(0, 0, 0, 0.14), 0 0 2px rgba(0, 0, 0, 0.12)',
                'fluent-lg': '0 8px 16px rgba(0, 0, 0, 0.14), 0 0 4px rgba(0, 0, 0, 0.12)',
            },
            borderRadius: {
                'fluent': '4px',
            },
        },
    },
    plugins: [],
}
