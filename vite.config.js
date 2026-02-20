import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
  ],
  server: {
    port: 4173,  // Menetapkan port yang digunakan oleh Vite (4173)
    allowedHosts: ['semicarbonate-unimportunately-boyd.ngrok-free.dev', 'localhost', '127.0.0.1'],  // Masukkan disini
  },
});
