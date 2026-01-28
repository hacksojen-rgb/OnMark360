import React, { useState, useEffect } from 'react';
import { API_BASE } from '../constants'; 
import { X, Play, Maximize2, ExternalLink } from 'lucide-react';

const Portfolio: React.FC = () => {
  const [portfolio, setPortfolio] = useState<any[]>([]);
  const [activeCategory, setActiveCategory] = useState('All');
  const [loading, setLoading] = useState(true);
  const [selectedProject, setSelectedProject] = useState<any | null>(null);

  useEffect(() => {
    fetch(`${API_BASE}/get-portfolio.php`)
      .then(res => res.json())
      .then(data => {
        setPortfolio(Array.isArray(data) ? data : []);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, []);

  useEffect(() => {
    if (selectedProject) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = 'unset';
    }
  }, [selectedProject]);

  // Categories Logic (Existing)
  const categories = [
    'All',
    ...new Set(portfolio.map(p => p.subcategory || p.category || 'Other'))
  ];

  // Filter Logic (Existing)
  const filtered = activeCategory === 'All'
    ? portfolio
    : portfolio.filter(p => (p.subcategory || p.category) === activeCategory);

  const getImageUrl = (url: string) => {
    if (!url) return '';
    return url.startsWith('http') ? url : `${API_BASE.replace('/api', '')}/${url}`;
  };

  // ✅ New Helper: Embed URL Generator
  const getEmbedUrl = (url: string) => {
    if (!url) return null;

    if (url.includes('facebook.com')) {
      return `https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(
        url
      )}&show_text=0&width=560`;
    }

    if (url.includes('instagram.com')) {
      return `${url.split('?')[0]}embed`;
    }

    if (url.includes('youtube')) {
      const match = url.match(
        /(?:v=|\/)([0-9A-Za-z_-]{11})/
      );
      return match
        ? `https://www.youtube.com/embed/${match[1]}?autoplay=1&rel=0`
        : null;
    }


    if (url.includes('vimeo.com')) {
      const id = url.split('/').pop();
      return `https://player.vimeo.com/video/${id}?autoplay=1`;
    }

    if (url.includes('drive.google.com')) {
      return url.replace('/view', '/preview');
    }

    return url;
  };

  // ✅ New Helper: Smart Thumbnail Generator
  const getProjectThumbnail = (project: any) => {
    // ১. যদি ইউজার ইমেজ আপলোড করে থাকে, তবে সেটাই দেখাও
    if (project.image_url) {
      return getImageUrl(project.image_url);
    }

    // ২. যদি ইমেজ না থাকে কিন্তু ভিডিও লিংক থাকে (YouTube)
    if (project.video_url) {
      const url = project.video_url;
      // YouTube Thumbnail Logic
      if (url.includes('youtube') || url.includes('youtu.be')) {
        const match = url.match(/(?:v=|\/)([0-9A-Za-z_-]{11})/);
        return match ? `https://img.youtube.com/vi/${match[1]}/hqdefault.jpg` : '';
      }
      // Vimeo/Others (এগুলোর জন্য ডিফল্ট প্লেসহোল্ডার বা কালার দেখানো যেতে পারে)
      return 'https://placehold.co/600x400/014034/FFF?text=Video+Project';
    }

    // ৩. কিছুই না থাকলে ফাঁকা
    return '';
  };

  return (
    <div className="pt-32 pb-24 bg-white min-h-screen">
      <style>{`
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
      `}</style>
      
      <div className="container mx-auto px-6">
        {loading && (
          <div className="text-center py-24 text-gray-400 font-bold tracking-widest uppercase text-sm">
            Loading showcase...
          </div>
        )}

        <div className="text-center mb-16">
          <h1 className="text-5xl font-black text-[#014034] mb-4 tracking-tighter uppercase">Our Showcase</h1>
          <p className="text-gray-400 font-bold uppercase tracking-widest text-[10px]">Creativity Meets Technology</p>
        </div>

        {/* Categories */}
        <div className="flex flex-wrap justify-center gap-3 mb-16">
          {categories.map(cat => (
            <button
              key={cat}
              onClick={() => setActiveCategory(cat)}
              className={`px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all ${
                activeCategory === cat ? 'bg-[#014034] text-white shadow-xl scale-105' : 'bg-gray-100 text-gray-400 hover:bg-gray-200'
              }`}
            >
              {cat}
            </button>
          ))}
        </div>

        {/* Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {!loading && filtered.map(p => (
            <div
              key={p.id}
              onClick={() => setSelectedProject(p)}
              className="group relative bg-gray-100 rounded-[1.5rem] overflow-hidden aspect-video cursor-pointer shadow-lg hover:-translate-y-2 transition-all duration-500"
            >
              <img 
  src={getProjectThumbnail(p)} // 👈 এখানে নতুন ফাংশন কল করা হয়েছে
  alt={p.title} 
  className="w-full h-full object-cover opacity-90 group-hover:opacity-40 group-hover:scale-110 transition-all duration-700" 
/>
              
              {/* ✅ Video Indicator Icon */}
              {p.video_url && (
                <div className="absolute top-4 left-4 z-10 bg-red-600 text-white p-2 rounded-full shadow-lg animate-pulse">
                  <Play size={12} fill="white" />
                </div>
              )}

              <div className="absolute top-4 right-6 bg-black/50 backdrop-blur-md text-white text-[9px] px-3 py-1 rounded-full font-black uppercase tracking-widest opacity-80 group-hover:opacity-0 transition-opacity">
                {p.category}
              </div>

              <div className="absolute inset-0 p-6 flex flex-col justify-end translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all">
                <h3 className="text-white text-lg font-black mb-3 leading-tight">{p.title}</h3>
                <div className="flex items-center gap-2">
                  <div className="w-8 h-8 rounded-full bg-[#4DB6AC] flex items-center justify-center text-[#012a22]">
                    {p.video_url ? <Play size={14} fill="currentColor" /> : <Maximize2 size={14} />}
                  </div>
                  <span className="text-white text-[10px] font-black uppercase tracking-widest">
                    {p.video_url ? 'Play Reel' : 'View Project'}
                  </span>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Modal Section (UPDATED) */}
      {selectedProject && (
        <div 
          className="fixed inset-0 z-[100] bg-black/80 backdrop-blur-md flex items-center justify-center p-4 md:p-10 animate-in fade-in duration-300"
          onClick={() => setSelectedProject(null)}
        >
          <button
            onClick={() => setSelectedProject(null)}
            className="absolute top-6 right-6 text-white/70 hover:text-red-500 transition-all z-[110]"
          >

            <X size={40} strokeWidth={2} />
          </button>

          <div 
            className="w-full max-w-6xl max-h-[90vh] overflow-y-auto no-scrollbar rounded-[2rem] bg-white shadow-2xl relative"
            onClick={(e) => e.stopPropagation()}
          >
            {/* ✅ Media Area (Video Logic Added) */}
            <div className="aspect-video bg-black w-full overflow-hidden shadow-2xl relative group">
              {selectedProject.video_url ? (
                <iframe
                  title={selectedProject.title}
                  src={getEmbedUrl(selectedProject.video_url)}
                  className="w-full h-full"
                  frameBorder="0"
                  allow="autoplay; fullscreen; picture-in-picture"
                  allowFullScreen
                ></iframe>
              ) : (
                <img
                  src={getImageUrl(selectedProject.image_url)}
                  className="w-full h-full object-cover"
                />
              )}
            </div>

            {/* Info Area (Restored) */}
            <div className="p-8 md:p-12 grid grid-cols-1 lg:grid-cols-3 gap-10 text-black">
              <div className="lg:col-span-2 space-y-4">
                <span className="text-[#014034] font-black text-[10px] uppercase tracking-[0.3em]">
                  {selectedProject.category} {selectedProject.subcategory && ` / ${selectedProject.subcategory}`}
                </span>
                <h2 className="text-4xl font-black leading-tight text-gray-900">
                  {selectedProject.title}
                </h2>
                <div className="text-gray-600 text-base leading-relaxed whitespace-pre-line font-medium">
                  {selectedProject.content}
                </div>
              </div>

              <div className="space-y-6">
                <div className="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                  <p className="text-[10px] uppercase font-black text-gray-400 tracking-widest mb-1">
                    Client Name
                  </p>
                  <p className="font-bold text-xl text-gray-800">
                    {selectedProject.client || 'N/A'}
                  </p>
                </div>

                {selectedProject.live_link && (
                  <a
                    href={selectedProject.live_link}
                    target="_blank"
                    rel="noreferrer"
                    className="flex items-center justify-between w-full bg-[#014034] text-white p-5 rounded-2xl font-black uppercase tracking-widest text-[10px] hover:bg-[#003026] hover:scale-[1.02] transition-all"
                  >
                    Visit Project <ExternalLink size={16} />
                  </a>
                )}
              </div>
            </div>
          </div>
        </div>
      )}

    </div>
  );
};

export default Portfolio;