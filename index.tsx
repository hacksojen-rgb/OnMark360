import React from 'react';
import ReactDOM from 'react-dom/client';
import { HelmetProvider } from 'react-helmet-async'; // ✅ নতুন যোগ
import './index.css';  
import App from './App';
import { TrackingProvider } from './context/TrackingContext'; // নতুন লাইন

const rootElement = document.getElementById('root');
if (!rootElement) {
  throw new Error("Could not find root element to mount to");
}

const root = ReactDOM.createRoot(document.getElementById('root')!);
root.render(
  <React.StrictMode>
    <HelmetProvider>
      <TrackingProvider> {/* অ্যাপটি এখন ট্র্যাকিংয়ের ভেতরে */}
        <App />
      </TrackingProvider>
    </HelmetProvider>
  </React.StrictMode>
);
