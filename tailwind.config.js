/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx,php,html}",
  ],
  theme: {
    extend: {
      colors: {
        background: '#f9f9f9',
        primary: '#0070f3',
        dark: '#0d0d0d',
        'dark-secondary': '#1e1e1e',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
} 