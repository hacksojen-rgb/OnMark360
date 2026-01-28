import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { ChevronRight, ChevronLeft, Loader2 } from 'lucide-react';
import { API_BASE } from '../constants';

const HeroSlider: React.FC = () => {
  const [slides, setSlides] = useState<any[]>([]);
  const [current, setCurrent] = useState(0);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(`${API_BASE}/get-hero-slides.php`)
      .then((res) => res.json())
      .then((data) => {
        // Safe check: Ensure data is array
        setSlides(Array.isArray(data) ? data : []);
        setLoading(false);
      })
      .catch((err) => {
        console.error("Hero Fetch Error:", err);
        setSlides([]); // Set empty on error to prevent crash
        setLoading(false);
      });
  }, []);

  useEffect(() => {
    if (!slides || slides.length <= 1) return;
    const timer = setInterval(() => {
      setCurrent((prev) => (prev === slides.length - 1 ? 0 : prev + 1));
    }, 6000);
    return () => clearInterval(timer);
  }, [slides]);

  const nextSlide = () => setCurrent(current === slides.length - 1 ? 0 : current + 1);
  const prevSlide = () => setCurrent(current === 0 ? slides.length - 1 : current - 1);

  const renderLink = (url: string, label: string, className: string) => {
    if (!url) return null;
    if (url.startsWith('http')) {
      return <a href={url} target="_blank" rel="noopener noreferrer" className={className}>{label}</a>;
    }
    return <Link to={url} className={className}>{label}</Link>;
  };

  if (loading) return <div className="h-screen bg-[#012a22] flex items-center justify-center"><Loader2 className="animate-spin text-[#4DB6AC]" size={48} /></div>;
  
  // If no slides, return null (don't break the page)
  if (!slides || slides.length === 0) return null;

  return (
    <section className="relative h-screen min-h-[600px] overflow-hidden">
      {slides.map((slide, index) => {
        const title = slide.title;
        const subtitle = slide.subtitle;
        
        const btnPrimary = slide.cta_primary || "Get Started";
        const btnSecondary = slide.cta_secondary || "Learn More";
        const urlPrimary = slide.cta_primary_url || "/get-quote"; 
        const urlSecondary = slide.cta_secondary_url || "/contact";

        const imgPath = slide.image_url || slide.image;
        const imageUrl = imgPath && imgPath.startsWith('http') 
          ? imgPath 
          : `${API_BASE.replace('/api', '')}/${imgPath}`;

        // ✅ SAFE STYLE LOGIC
        const titleStyle: React.CSSProperties = {
            color: slide.title_color || '#ffffff',
            // যদি bg_color না থাকে বা null হয়, তবে transparent
            backgroundColor: slide.title_bg_color ? slide.title_bg_color : 'transparent',
            padding: slide.title_bg_color ? '0.2em 0.4em' : '0',
            borderRadius: '0.2em',
            display: 'inline',
            boxDecorationBreak: 'clone',
            WebkitBoxDecorationBreak: 'clone'
        };

        const subStyle: React.CSSProperties = {
            color: slide.subtitle_color || '#e5e7eb',
            backgroundColor: slide.subtitle_bg_color ? slide.subtitle_bg_color : 'transparent',
            padding: slide.subtitle_bg_color ? '0.2em 0.4em' : '0',
            borderRadius: '0.2em',
            display: 'inline',
            boxDecorationBreak: 'clone',
            WebkitBoxDecorationBreak: 'clone'
        };

        return (
          <div
            key={slide.id}
            className={`absolute inset-0 transition-opacity duration-1000 ease-in-out ${index === current ? 'opacity-100 z-10' : 'opacity-0 z-0'}`}
          >
            {/* Reduced Overlay */}
            <div className="absolute inset-0 bg-gradient-to-r from-black/60 via-black/10 to-transparent z-10" />
            
            <img 
              src={imageUrl} 
              alt={title}
              className="w-full h-full object-cover transition-transform duration-[6000ms]"
              style={{ transform: index === current ? 'scale(1.1)' : 'scale(1.05)' }}
            />
            <div className="absolute inset-0 z-20 flex items-center">
              <div className="container mx-auto px-6 md:px-12">
                <div className="max-w-4xl text-left">
                  
                  {/* Headline */}
                  <div className={`transition-all duration-700 delay-100 ${index === current ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'}`}>
                    <h1 className="text-4xl md:text-6xl lg:text-7xl font-extrabold mb-6 leading-[1.3]">
                        <span style={titleStyle}>{title}</span>
                    </h1>
                  </div>
                  
                  {/* Subtitle */}
                  <div className={`transition-all duration-700 delay-200 ${index === current ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'}`}>
                    <p className="text-lg md:text-2xl mb-10 leading-relaxed max-w-2xl">
                        <span style={subStyle}>{subtitle}</span>
                    </p>
                  </div>
                  
                  <div className={`flex flex-col sm:flex-row gap-5 transition-all duration-700 delay-300 mt-4 ${index === current ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'}`}>
                    {renderLink(urlPrimary, btnPrimary, "bg-[#014034] text-white px-10 py-5 rounded-xl font-extrabold text-lg hover:bg-[#00695c] transition-all shadow-2xl text-center")}
                    {renderLink(urlSecondary, btnSecondary, "bg-white/10 backdrop-blur-md text-white border border-white/30 px-10 py-5 rounded-xl font-bold text-lg hover:bg-white/20 transition-all text-center")}
                  </div>
                </div>
              </div>
            </div>
          </div>
        );
      })}

      {slides.length > 1 && (
        <div className="absolute bottom-12 right-12 z-30 flex space-x-4">
          <button onClick={prevSlide} className="p-4 rounded-full border border-white/20 text-white hover:bg-[#014034] transition-all bg-black/30 backdrop-blur-md group">
            <ChevronLeft size={28} className="group-hover:-translate-x-1 transition-transform" />
          </button>
          <button onClick={nextSlide} className="p-4 rounded-full border border-white/20 text-white hover:bg-[#014034] transition-all bg-black/30 backdrop-blur-md group">
            <ChevronRight size={28} className="group-hover:translate-x-1 transition-transform" />
          </button>
        </div>
      )}
    </section>
  );
};

export default HeroSlider;