import path from 'path';
import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, '.', '');
  
  // আপনার লোকাল PHP সার্ভারের ঠিকানা (যেখানে admin ফোল্ডারটি আছে)
  // XAMPP ব্যবহার করলে সাধারণত এমন হয়: 'http://localhost/agency/admin'
  const localPhpServer = 'http://localhost/agency_backend_folder'; 

  return {
    // এই লাইনটিই আসল ম্যাজিক। এটি থাকলে index.html এডিট করা লাগবে না।
    base: './', 
    
    server: {
      host: '0.0.0.0',
      port: 3000,
      proxy: {
        '/api': {
          target: localPhpServer,
          changeOrigin: true,
          secure: false,
          // লোকাল প্রক্সির জন্য রিরাইট রুল
          rewrite: (path) => path.replace(/^\/api/, '/api')
        },
      },
    },
    plugins: [react()],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, '.'),
      }
    }
  };
});