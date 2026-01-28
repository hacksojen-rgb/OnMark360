import React, {
  createContext,
  useContext,
  useEffect,
  useState,
  useRef, // ✅ নতুন যোগ করা হয়েছে
  useCallback
} from 'react';
import { API_BASE } from '../constants';

// ================== Global Types ==================
declare global {
  interface Window {
    fbq: any;
    _fbq: any;
    gtag: any;
    ttq: any;
  }
}

// ================== Cookie Helper ==================
const getCookie = (name: string) => {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop()?.split(';').shift();
  return null;
};

// ================== Context ==================
const TrackingContext = createContext<any>(null);

// =================================================
// Tracking Provider
// =================================================
export const TrackingProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [config, setConfigState] = useState<any>(null);
  const configRef = useRef<any>(null); // ✅ কনফিগ রেফারেন্স (Stale Closure ফিক্স করার জন্য)
  const [initialized, setInitialized] = useState(false);

  // কনফিগ সেট করার ফাংশন (স্টেট এবং রেফ দুটোই আপডেট করবে)
  const setConfig = (data: any) => {
    setConfigState(data);
    configRef.current = data;
  };

  // ================== Pixel Init (Strict Mode Safe) ==================
  const initializePixels = useCallback((cfg: any) => {
    if (initialized) return;

    if (cfg.meta_pixel_id) {
      /* eslint-disable */
      !function(f,b,e,v,n,t,s)
      {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};
      if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
      n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t,s)}(
        window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js'
      );
      /* eslint-enable */

      window.fbq('init', cfg.meta_pixel_id);
      window.fbq('track', 'PageView');
    }

    setInitialized(true);
  }, [initialized]);

  // ================== Load Config ==================
  // ১. মেইন কনফিগারেশন লোড
  useEffect(() => {    
    fetch(`${API_BASE}/get-tracking-config.php`)
      .then(res => res.json())
      .then(data => {
        setConfig(data);
        if (data?.enable_browser_tracking) {
          initializePixels(data);
        }
      })
      .catch(err => console.error('Tracking Config Error:', err));

      // ২. কাস্টম ইভেন্ট রুলস লোড (Custom Event Rules)
    fetch(`${API_BASE}/get-custom-events.php`)
      .then(res => res.json())
      .then(events => {
        if (Array.isArray(events)) {
          // ইভেন্ট লিসেনার সেট করার জন্য একটু অপেক্ষা (DOM Ready)
          setTimeout(() => {
            events.forEach((evt: any) => {
              const elements = document.querySelectorAll(evt.selector);
              elements.forEach((el) => {
                // ডুপ্লিকেট লিসেনার এড়াতে চেক করা ভালো (Optional)
                el.addEventListener(evt.event_type || 'click', () => {
                   trackEvent(evt.event_name, evt.parameters ? JSON.parse(evt.parameters) : {});
                });
              });
            });
          }, 1000); // 1 সেকেন্ড ডিলে যাতে সব এলিমেন্ট রেন্ডার হয়
        }
      })
      .catch(() => {}); // সাইলেন্ট এরর হ্যান্ডলিং
  }, [initializePixels]);

  // ================== Main Track Event ==================
  const trackEvent = useCallback(
    async (name: string, data: any = {}, userData: any = {}) => {
      const cfg = configRef.current; // ✅ সরাসরি রেফারেন্স থেকে কনফিগ নেওয়া হচ্ছে
      if (!cfg) return;

      const eventId = crypto.randomUUID();
      const fbp = getCookie('_fbp');
      const fbc = getCookie('_fbc');

      const gaCookie = getCookie('_ga');
      const clientId = gaCookie
        ? gaCookie.split('.').slice(-2).join('.')
        : crypto.randomUUID();

      // ---------- Browser ----------
      if (config.enable_browser_tracking) {
        if (window.fbq) {
          window.fbq('track', name, data, { eventID: eventId });
        }

        if (window.gtag) {
          window.gtag('event', name, {
            ...data,
            event_id: eventId
          });
        }
      }

      // ---------- Server (CAPI) ----------
      if (cfg.enable_server_tracking) {
        try {
          await fetch(`${API_BASE}/track-event.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              event_name: name,
              event_id: eventId,
              user_data: userData,
              fbp,
              fbc,
              client_id: clientId,
              page_location: window.location.href,
              payload: data
            })
          });
        } catch {
          // silent fail
        }
      }
    },
    [] // ✅ কনফিগ পরিবর্তনের উপর নির্ভর করবে না
  );

  // ================== 3. Load Config & Custom Events ==================
  useEffect(() => {
    // ক. ট্র্যাকিং কনফিগ লোড
    fetch(`${API_BASE}/get-tracking-config.php`)
      .then(res => res.json())
      .then(data => {
        setConfig(data); // স্টেট এবং রেফ আপডেট
        if (data?.enable_browser_tracking) {
          initializePixels(data);
        }
      })
      .catch(err => console.error('Tracking Config Error:', err));

    // খ. কাস্টম ইভেন্ট রুলস লোড (PixelYourSite স্টাইল)
    fetch(`${API_BASE}/get-custom-events.php`)
      .then(res => res.json())
      .then(events => {
        if (Array.isArray(events)) {
          // DOM রেন্ডার হওয়ার জন্য অপেক্ষা
          setTimeout(() => {
            events.forEach((evt: any) => {
              const elements = document.querySelectorAll(evt.selector);
              elements.forEach((el) => {
                // লিসেনার যোগ করা
                el.addEventListener(evt.event_type || 'click', () => {
                   // এখানে trackEvent কল হবে যা configRef থেকে লেটেস্ট ডাটা পাবে
                   trackEvent(evt.event_name, evt.parameters ? JSON.parse(evt.parameters) : {});
                });
              });
            });
          }, 1500); // ১.৫ সেকেন্ড ডিলে
        }
      })
      .catch(() => {});
  }, [initializePixels, trackEvent]);

  // ================== Auto Events ==================
  useEffect(() => {
    if (!config) return;

    // ----- Click Tracking -----
    const handleClick = (e: any) => {
      const target = e.target.closest('button, a, input[type="submit"]');
      if (!target) return;

      const eventName = target.getAttribute('data-event');
      if (!eventName) return;

      const value = target.getAttribute('data-value');
      const currency = target.getAttribute('data-currency') || 'BDT';
      const contentName = target.getAttribute('data-content-name');

      trackEvent(eventName, {
        value: value ? parseFloat(value) : 0,
        currency,
        content_name: contentName,
        content_type: 'product'
      });
    };

    // ----- Form Submit (Advanced Matching Safe) -----
    const handleSubmit = (e: any) => {
      const form = e.target;
      if (!(form instanceof HTMLFormElement)) return;

      const formData = new FormData(form);

      const userData = {
        email: formData.get('email') || formData.get('your-email') || undefined,
        phone: formData.get('phone') || formData.get('tel') || undefined
      };

      const formName =
        form.getAttribute('name') ||
        form.getAttribute('id') ||
        'Unknown Form';

      trackEvent('FormSubmit', { form_name: formName }, userData);
    };

    document.addEventListener('click', handleClick);
    document.addEventListener('submit', handleSubmit);

    return () => {
      document.removeEventListener('click', handleClick);
      document.removeEventListener('submit', handleSubmit);
    };
  }, [config, trackEvent]);

  return (
    <TrackingContext.Provider value={{ trackEvent }}>
      {children}
    </TrackingContext.Provider>
  );
};

// ================== Hook ==================
export const useTracking = () => useContext(TrackingContext);
