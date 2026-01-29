/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.js",
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    ],
    safelist: [
        // Border colors
        "border-blue-500",
        "border-purple-500",
        "border-green-500",
        "border-orange-500",
        "border-indigo-500",
        "border-red-500",
        "border-yellow-500",
        "border-pink-500",
        "border-gray-500",

        // Background colors (100 variants)
        "bg-blue-100",
        "bg-purple-100",
        "bg-green-100",
        "bg-orange-100",
        "bg-indigo-100",
        "bg-red-100",
        "bg-yellow-100",
        "bg-pink-100",
        "bg-gray-100",

        // Background colors (500 variants)
        "bg-blue-500",
        "bg-purple-500",
        "bg-green-500",
        "bg-orange-500",
        "bg-indigo-500",
        "bg-red-500",
        "bg-yellow-500",
        "bg-pink-500",
        "bg-gray-500",

        // Background colors (600 variants)
        "bg-blue-600",
        "bg-purple-600",
        "bg-green-600",
        "bg-orange-600",
        "bg-indigo-600",
        "bg-red-600",
        "bg-yellow-600",
        "bg-pink-600",
        "bg-gray-600",

        // Text colors (500 variants)
        "text-blue-500",
        "text-purple-500",
        "text-green-500",
        "text-orange-500",
        "text-indigo-500",
        "text-red-500",
        "text-yellow-500",
        "text-pink-500",
        "text-gray-500",

        // Text colors (600 variants)
        "text-blue-600",
        "text-purple-600",
        "text-green-600",
        "text-orange-600",
        "text-indigo-600",
        "text-red-600",
        "text-yellow-600",
        "text-pink-600",
        "text-gray-600",

        // Text colors (700 variants)
        "text-blue-700",
        "text-purple-700",
        "text-green-700",
        "text-orange-700",
        "text-indigo-700",
        "text-red-700",
        "text-yellow-700",
        "text-pink-700",
        "text-gray-700",

        // Text colors (800 variants)
        "text-blue-800",
        "text-purple-800",
        "text-green-800",
        "text-orange-800",
        "text-indigo-800",
        "text-red-800",
        "text-yellow-800",
        "text-pink-800",
        "text-gray-800",

        // Text colors (900 variants)
        "text-blue-900",
        "text-purple-900",
        "text-green-900",
        "text-orange-900",
        "text-indigo-900",
        "text-red-900",
        "text-yellow-900",
        "text-pink-900",
        "text-gray-900",

        // Background gradients
        "bg-gradient-to-r",
        "bg-gradient-to-l",
        "bg-gradient-to-t",
        "bg-gradient-to-b",
        "bg-gradient-to-br",
        "bg-gradient-to-bl",
        "bg-gradient-to-tr",
        "bg-gradient-to-tl",

        // From colors
        "from-blue-50",
        "from-purple-50",
        "from-green-50",
        "from-orange-50",
        "from-indigo-50",
        "from-red-50",
        "from-yellow-50",
        "from-pink-50",
        "from-gray-50",

        // To colors
        "to-blue-50",
        "to-purple-50",
        "to-green-50",
        "to-orange-50",
        "to-indigo-50",
        "to-red-50",
        "to-yellow-50",
        "to-pink-50",
        "to-gray-50",

        // Hover colors
        "hover:bg-blue-700",
        "hover:bg-purple-700",
        "hover:bg-green-700",
        "hover:bg-orange-700",
        "hover:bg-indigo-700",
        "hover:bg-red-700",
        "hover:bg-yellow-700",
        "hover:bg-pink-700",
        "hover:bg-gray-700",

        // Focus colors
        "focus:ring-blue-500",
        "focus:ring-purple-500",
        "focus:ring-green-500",
        "focus:ring-orange-500",
        "focus:ring-indigo-500",
        "focus:ring-red-500",
        "focus:ring-yellow-500",
        "focus:ring-pink-500",
        "focus:ring-gray-500",

        // Border focus colors
        "focus:border-blue-500",
        "focus:border-purple-500",
        "focus:border-green-500",
        "focus:border-orange-500",
        "focus:border-indigo-500",
        "focus:border-red-500",
        "focus:border-yellow-500",
        "focus:border-pink-500",
        "focus:border-gray-500",

        // Left border variants
        "border-l-4",

        // Dark theme colors
        "bg-slate-700",
        "bg-slate-800",
        "bg-slate-900",
        "text-slate-300",
        "text-slate-400",
        "text-slate-500",
        "border-slate-600",
        "border-slate-700",
        "border-cyan-400",
        "border-cyan-500",
        "text-cyan-400",
        "text-emerald-400",
        "text-red-400",
        "bg-cyan-900",
        "bg-emerald-900",
        "bg-red-900",
        "border-emerald-500",
        "border-red-500",

        // Pattern for dynamic colors
        {
            pattern:
                /border-(blue|purple|green|orange|indigo|red|yellow|pink|gray)-(100|200|300|400|500|600|700|800|900)/,
        },
        {
            pattern:
                /bg-(blue|purple|green|orange|indigo|red|yellow|pink|gray)-(100|200|300|400|500|600|700|800|900)/,
        },
        {
            pattern:
                /text-(blue|purple|green|orange|indigo|red|yellow|pink|gray)-(100|200|300|400|500|600|700|800|900)/,
        },
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: [
                    "Inter",
                    "ui-sans-serif",
                    "system-ui",
                    "sans-serif",
                    "Apple Color Emoji",
                    "Segoe UI Emoji",
                    "Segoe UI Symbol",
                    "Noto Color Emoji",
                ],
                inter: ["Inter", "ui-sans-serif", "system-ui", "sans-serif"],
            },
            animation: {
                "fade-in": "fadeIn 0.5s ease-in",
            },
            keyframes: {
                fadeIn: {
                    "0%": { opacity: "0" },
                    "100%": { opacity: "1" },
                },
            },
        },
    },
    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
    ],
};
