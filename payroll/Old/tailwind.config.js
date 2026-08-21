/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#667eea',
        secondary: '#764ba2',
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
  // Prevent conflicts with Bootstrap
  corePlugins: {
    preflight: false, // Disables Tailwind's CSS reset
  },
  // Add important selector to scope Tailwind
  important: '.tailwind-scope',
}
