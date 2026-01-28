import React, { useState, useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { Menu, X, Rocket } from 'lucide-react';
import { SITE_SETTINGS, API_BASE } from '../constants';

const Navbar: React.FC = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const [settings, setSettings] = useState<any>(null);
  const [buttons, setButtons] = useState<any>({});
  const location = useLocation();

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener('scroll', handleScroll);

    fetch(`${API_BASE}/get-buttons.php`)
      .then(res => res.json())
      .then(data => setButtons(data.buttons || data))
      .catch(err => console.error("Button Config Error:", err));

    fetch(`${API_BASE}/get-settings.php`)
      .then(res => res.json())
      .then(data => setSettings(data.settings ?? data))
      .catch(err => console.error("Navbar Config Error:", err));

    return () => {
      window.removeEventListener('scroll', handleScroll);
    };
  }, []);

  // ✅ FIX: মেনু ড্যাশবোর্ড থেকে লোড হবে, না থাকলে ডিফল্ট দেখাবে
  const navLinks = (settings?.header_nav && settings.header_nav.length > 0) 
    ? settings.header_nav 
    : [
        { label: 'Home', path: '/' },
        { label: 'Services', path: '/services' },
        { label: 'Portfolio', path: '/portfolio' },
        { label: 'Pricing', path: '/pricing' },
        { label: 'Blog', path: '/blog' },
        { label: 'About', path: '/about' },
        { label: 'Contact', path: '/contact' },
      ];

  const toggleMenu = () => setIsOpen(!isOpen);

  // ✅ FIX: কালার বাগ ফিক্স (site_title এর বদলে theme_color ব্যবহার)
  const brandColor = settings?.theme_color || '#014034';

  // ✅ Helper: কালারকে ট্রান্সপারেন্ট করার জন্য
  const hexToRgba = (hex: string, opacity: number) => {
    let c: any;
    if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){
        c= hex.substring(1).split('');
        if(c.length== 3){
            c= [c[0], c[0], c[1], c[1], c[2], c[2]];
        }
        c= '0x'+c.join('');
        return 'rgba('+[(c>>16)&255, (c>>8)&255, c&255].join(',')+','+opacity+')';
    }
    return hex; // ফেইলসেফ
  };

  // ✅ FIX: ডাইনামিক ব্যাকগ্রাউন্ড স্টাইল (কালার চেঞ্জ করলে এটাও চেঞ্জ হবে)
  const navStyle = {
    backgroundColor: scrolled 
      ? hexToRgba('#ffffff', 0.95) // স্ক্রল করলে সাদা ৯৫%
      : hexToRgba('#ffffff', 0.60), // শুরুতে সাদা ৬০% (ট্রান্সপারেন্ট)
    // আপনি চাইলে '#ffffff' এর জায়গায় brandColor দিতে পারেন যদি রঙিন ন্যাভবার চান
    backdropFilter: 'blur(12px)',
    borderBottom: scrolled ? '1px solid rgba(0,0,0,0.05)' : '1px solid rgba(255,255,255,0.2)'
  };

  const renderLogoContent = () => {
    if (settings?.logo_url) {
      const logoSrc = settings.logo_url.startsWith('http')
        ? settings.logo_url
        : `${API_BASE.replace('/api', '')}/uploads/${settings.logo_url}`;
      return <img src={logoSrc} alt="Logo" className="h-10 w-auto" />;
    }
    const companyName = settings?.company_name || SITE_SETTINGS.companyName;
    const nameParts = companyName.split(' ');
    return (
      <>
        <div style={{ backgroundColor: brandColor }} className="p-2 rounded-xl shadow-lg">
          <Rocket className="text-white w-6 h-6" />
        </div>
        <span style={{ color: brandColor }} className="text-2xl font-black tracking-tighter">
          {nameParts[0]}<span className="opacity-70">{nameParts.slice(1).join(' ')}</span>
        </span>
      </>
    );
  };

  return (
    <nav 
      style={navStyle} 
      className={`fixed w-full z-50 transition-all duration-500 py-3 shadow-sm`}
    >
      <div className="container mx-auto px-4 md:px-8">
        <div className="flex justify-between items-center">
          <Link to="/" className="flex items-center space-x-2 group">
            {renderLogoContent()}
          </Link>

          <div className="hidden lg:flex items-center space-x-8">
            {navLinks.map((link: any) => (
              <Link
                key={link.path}
                to={link.path}
                style={{
                    color: location.pathname === link.path ? brandColor : '#1f2937'
                }}
                className={`text-sm font-bold transition-all duration-300 hover:opacity-70 ${
                  location.pathname === link.path ? 'scale-105' : 'hover:scale-105'
                }`}
              >
                {link.label || link.name} {/* API label সাপোর্ট */}
              </Link>
            ))}
            <div className="flex items-center space-x-4 pl-6 border-l border-gray-300/50">
              <Link
                to={buttons.nav_quote?.url || "/get-quote"}
                style={buttons.nav_quote ? buttons.nav_quote.style : { backgroundColor: brandColor, color: '#ffffff' }}
                className="text-white px-7 py-3 rounded-full text-sm font-extrabold hover:opacity-90 transition-all shadow-lg hover:-translate-y-0.5"
                >
                {buttons.nav_quote ? buttons.nav_quote.label : 'Get a Quote'}
              </Link>
            </div>
          </div>

          <button
            style={{ color: brandColor }}
            className="lg:hidden p-2 rounded-xl bg-white/20 backdrop-blur-sm hover:bg-white/40 transition-all"
            onClick={toggleMenu}
          >
            {isOpen ? <X size={28} /> : <Menu size={28} />}
          </button>
        </div>
      </div>

      {/* মোবাইল মেনু */}
      <div className={`lg:hidden absolute w-full bg-white/95 backdrop-blur-xl shadow-2xl transition-all duration-300 ease-in-out border-b border-gray-100 ${isOpen ? 'max-h-screen opacity-100 py-8 translate-y-0' : 'max-h-0 opacity-0 overflow-hidden -translate-y-4'}`}>
        <div className="flex flex-col items-center space-y-6">
          {navLinks.map((link: any) => (
            <Link
              key={link.path}
              to={link.path}
              style={{ color: location.pathname === link.path ? brandColor : '#374151' }}
              className={`text-xl font-bold ${location.pathname === link.path ? 'scale-110' : ''}`}
              onClick={() => setIsOpen(false)}
            >
              {link.label || link.name}
            </Link>
          ))}
        </div>
      </div>
    </nav>
  );
};

export default Navbar;