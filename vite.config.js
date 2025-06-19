import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    outDir: 'dist',
    rollupOptions: {
      input: {
        main: './index.html',
        admin: './admin.html',
        register: './register.html',
        request: './request.html',
      },
    },
  },
  server: {
    port: 3000,
    open: true,
    proxy: {
      '/php': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/php/, '')
      }
    }
  }
}) 