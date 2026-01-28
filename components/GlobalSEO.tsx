import React, { useEffect } from 'react';
import { Helmet } from 'react-helmet-async';
import { useLocation } from 'react-router-dom';
import { API_BASE } from '../constants'; // ✅ এটি যুক্ত করা হয়েছে

const GlobalSEO: React.FC = () => {
  const location = useLocation();
  const path = location.pathname;

  const customTitles: Record<string, string> = {
    '/': 'Home | Build to Grow - Global Digital Agency',
    '/get-quote': 'Get a Free Quote | Build to Grow',
    '/contact': 'Contact Us | Start Your Project',
  };

  let title = '';
  if (customTitles[path]) {
    title = customTitles[path];
  } else {
    const pageName = path.substring(1).replace(/-/g, ' ');
    const formattedName = pageName.replace(/\b\w/g, (char) => char.toUpperCase());
    title = formattedName ? `${formattedName} | Build to Grow` : 'Build to Grow | Digital Growth Agency';
  }

  useEffect(() => {
    fetch(`${API_BASE}/get-settings.php`)
      .then(res => res.json())
      .then(data => {
        const s = data.settings || {};
        if (s.google_font_url && s.font_family) {
          // ১. গুগল ফন্ট লিংক ইনজেক্ট করা
          let link = document.getElementById('dynamic-font') as HTMLLinkElement;
          if (!link) {
            link = document.createElement('link');
            link.id = 'dynamic-font';
            link.rel = 'stylesheet';
            document.head.appendChild(link);
          }
          link.href = s.google_font_url;

          // ২. জোরপূর্বক পুরো সাইটে ফন্ট অ্যাপ্লাই করা (!important সহ)
          let style = document.getElementById('dynamic-font-style');
          if (!style) {
            style = document.createElement('style');
            style.id = 'dynamic-font-style';
            document.head.appendChild(style);
          }
          style.innerHTML = `* { font-family: ${s.font_family} !important; }`;
        }
        // 3. Favicon আপডেট
if (s.site_favicon) {
  let favicon = document.querySelector("link[rel~='icon']") as HTMLLinkElement;
  if (!favicon) {
    favicon = document.createElement('link');
    favicon.rel = 'icon';
    document.head.appendChild(favicon);
  }
  favicon.href = `${API_BASE}/../uploads/${s.site_favicon}`;
}
      })
      .catch(err => console.error("Typography Error:", err));
  }, []);

  return (
    <Helmet>
      <title>{title}</title>
      <meta name="description" content="We help businesses grow with high-converting websites, content & marketing." />
      <meta property="og:title" content={title} />
    </Helmet>
  );
};

export default GlobalSEO;