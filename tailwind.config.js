/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./admin.html",
    "./register.html",
    "./request.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'primary': '#0070f3',
        'background': '#f9f9f9',
        'dark': '#0d0d0d',
        'dark-secondary': '#666666',
      },
      container: {
        center: true,
        padding: '1rem',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
} 