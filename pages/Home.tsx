import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ArrowRight, Quote, Megaphone, Code2, Search, PenTool, Smartphone, BarChart3, Video, Target, Zap, TrendingUp, ShieldCheck, Plus, Minus, Check, Award, Users } from 'lucide-react';
import HeroSlider from '../components/HeroSlider';
import { CLIENT_LOGOS, TESTIMONIALS, API_BASE } from '../constants';

// ১. আইকন ম্যাপ
const IconMap: Record<string, any> = { 
    Megaphone, Code2, Search, PenTool, Smartphone, BarChart3, Video, 
    Target, Zap, TrendingUp, ShieldCheck, Award, Users,
    'pen-tool': PenTool,
    'bar-chart-3': BarChart3,
    'code-2': Code2,
    'user': Users 
};

// URL হেল্পার
const getUrl = (url: string) => {
  if (!url) return '';
  if (url.startsWith('http')) return url;
  return `${API_BASE.replace('/api', '')}/${url}`; 
};

const ServiceCard: React.FC<{ service: any }> = ({ service }) => {
  const [isExpanded, setIsExpanded] = useState(false);
  const Icon = service.icon && IconMap[service.icon] ? IconMap[service.icon] : Target;
  const initialFeatures = service.features?.slice(0, 3) || [];
  const extraFeatures = service.features?.slice(3) || [];

  return (
    <div className="group p-8 bg-white border border-gray-100 rounded-[2.5rem] shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 flex flex-col h-full">
      <div className="flex items-center space-x-6 mb-8">
        <div className="w-14 h-14 bg-[#014034]/5 rounded-2xl flex items-center justify-center shrink-0 group-hover:bg-[#014034] transition-all duration-300">
          <Icon className="text-[#014034] group-hover:text-white w-7 h-7" />
        </div>
        <h3 className="text-2xl font-bold text-[#014034] transition-colors leading-tight">{service.title}</h3>
      </div>
      <div className="flex-grow">
        <ul className="space-y-3 mb-6">
          {initialFeatures.map((f: string, i: number) => (
            <li key={i} className="flex items-start space-x-3 text-gray-600">
              <Check className="text-[#00695c] w-4 h-4 mt-0.5" />
              <span className="text-sm font-medium">{f}</span>
            </li>
          ))}
          {isExpanded && extraFeatures.map((f: string, i: number) => (
            <li key={`ex-${i}`} className="flex items-start space-x-3 text-gray-600">
              <Check className="text-[#00695c] w-4 h-4 mt-0.5" />
              <span className="text-sm font-medium">{f}</span>
            </li>
          ))}
        </ul>
      </div>
      <div className="mt-auto space-y-6">
        {service.features && service.features.length > 3 && (
          <button onClick={() => setIsExpanded(!isExpanded)} className="flex items-center space-x-2 text-xs font-bold text-[#00695c] uppercase tracking-widest hover:text-[#014034]">
            <span>{isExpanded ? 'Show Less' : 'Show All Features'}</span>
            {isExpanded ? <Minus size={14} /> : <Plus size={14} />}
          </button>
        )}
        <div className="pt-6 border-t border-gray-100">
          <Link to={'/get-quote?service=' + encodeURIComponent(service.title)} className="inline-flex items-center text-[#00695c] font-bold text-base hover:gap-3 transition-all">
            Book Service <ArrowRight className="ml-2 w-5 h-5" />
          </Link>
        </div>
      </div>
    </div>
  );
};

const Home: React.FC = () => {
  const navigate = useNavigate();
  const [dbServices, setDbServices] = useState<any[]>([]);
  const [dbPortfolio, setDbPortfolio] = useState<any[]>([]);
  const [dbLogos, setDbLogos] = useState<any[]>([]);
  const [dbTestimonials, setDbTestimonials] = useState<any[]>([]);
  
  // 🟢 ডাইনামিক ডাটা স্টেটস
  const [ctaSettings, setCtaSettings] = useState<any>({ title: "Ready to Grow Your Business?", subtitle: "" });
  const [advSettings, setAdvSettings] = useState<any>({
    title: "Strategy-First Execution",
    subtitle: "The Build to Grow Advantage",
    image: "https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&q=80&w=1470",
    stat_num: "140%",
    stat_text: "Average Growth Increase",
    features: []
  });
  const [buttons, setButtons] = useState<any>({});
  
  // 🟢 HOME ABOUT STATE
  const [aboutSettings, setAboutSettings] = useState<any>({
      title: "",
      subtitle: "",
      desc: "",
      list: [],
      image1: "",
      image2: "",
      year: "2008"
  });

  useEffect(() => {
    // API কলগুলো
    fetch(`${API_BASE}/get-services.php`).then(res => res.json()).then(data => setDbServices(data)).catch(console.error);
    fetch(`${API_BASE}/get-portfolio.php`).then(res => res.json()).then(data => setDbPortfolio(data)).catch(console.error);
    fetch(`${API_BASE}/get-client-logos.php`).then(res => res.json()).then(data => setDbLogos(data)).catch(console.error);
    fetch(`${API_BASE}/get-testimonials.php`).then(res => res.json()).then(data => setDbTestimonials(data)).catch(console.error);
    
    // 🟢 ফিক্স: বাটনের ডাটা সঠিকভাবে ধরা হচ্ছে (data.buttons || data)
    fetch(`${API_BASE}/get-buttons.php`).then(res => res.json()).then(data => setButtons(data.buttons || data || {})).catch(console.error);

    // 🟢 সেটিংস ফেচ
    fetch(`${API_BASE}/get-settings.php`)
        .then(res => res.json())
        .then(data => {
            const s = data.settings || {};
            // হোম CTA
            setCtaSettings({
                title: s.home_cta_title || "Ready to Grow Your Business?",
                subtitle: s.home_cta_subtitle || ""
            });
            // 🟢 অ্যাডভান্টেজ সেকশন ডাটা
            let feats = [];
            try { feats = JSON.parse(s.home_adv_features); } catch(e) {}
            
            setAdvSettings({
                title: s.home_adv_title || "Strategy-First Execution",
                subtitle: s.home_adv_subtitle || "The Build to Grow Advantage",
                // যদি ইমেজ URL হয় তবে সরাসরি, না হলে uploads ফোল্ডার থেকে
                image: s.home_adv_image && !s.home_adv_image.startsWith('http') 
                       ? `${API_BASE}/../uploads/${s.home_adv_image}` 
                       : (s.home_adv_image || "https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&q=80&w=1470"),
                stat_num: s.home_adv_stat_num || "140%",
                stat_text: s.home_adv_stat_text || "Average Growth Increase",
                features: feats.length > 0 ? feats : [
                    { title: "Strategy-First Approach", desc: "Every pixel aligned with your bottom line.", icon: "Target" },
                    { title: "Everything Under One Roof", desc: "Unified design, dev, and growth team.", icon: "Zap" },
                    { title: "Business Results Over Vanity", desc: "Leads and sales over likes and clicks.", icon: "TrendingUp" }
                ]
            });
            // 🟢 HOME ABOUT SETTINGS
            setAboutSettings({
              title: s.home_about_title || "",
              subtitle: s.home_about_subtitle || "",
              desc: s.home_about_desc || "",
              list: (() => {
                try { return JSON.parse(s.home_about_list || '[]'); }
                catch { return []; }
              })(),
              image1: s.home_about_image1
                ? `${API_BASE}/../uploads/${s.home_about_image1}`
                : "",
              image2: s.home_about_image2
                ? `${API_BASE}/../uploads/${s.home_about_image2}`
                : "",
              year: s.home_about_year || "2008"
            });

        }).catch(console.error);
  }, []);

  return (
    <div className="overflow-hidden">
      <HeroSlider />

      <section className="py-24 bg-gray-50">
        <div className="container mx-auto px-6">
          <div className="text-center max-w-3xl mx-auto mb-20">
            <span className="text-[#00695c] font-bold text-sm uppercase tracking-widest mb-4 block">Our Solutions</span>
            <h2 className="text-4xl md:text-5xl font-extrabold text-[#014034] mb-6">Engineered for Business Outcomes</h2>
            <p className="text-gray-600 text-xl leading-relaxed">We focus on metrics that matter.</p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {dbServices.length > 0 ? dbServices.map((s) => <ServiceCard key={s.id} service={s} />) : <p className="text-center col-span-full text-gray-400">Loading services...</p>}
          </div>
        </div>
      </section>

      {/* 🟢 HOME ABOUT SECTION */}
      <section className="py-24 bg-white relative">
        <div className="container mx-auto px-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

            {/* Images Column */}
            <div className="relative">
              <div className="relative z-10 rounded-[3rem] overflow-hidden shadow-2xl w-[85%] border-8 border-white">
                {aboutSettings.image1 && (
                  <img src={aboutSettings.image1} alt="About 1"
                    className="w-full h-auto object-cover hover:scale-105 transition-transform duration-700" />
                )}
              </div>

              <div className="absolute -bottom-12 -right-4 z-20 w-[60%] rounded-[3rem] overflow-hidden shadow-2xl border-8 border-white">
                {aboutSettings.image2 && (
                  <img src={aboutSettings.image2} alt="About 2"
                    className="w-full h-auto object-cover hover:scale-105 transition-transform duration-700" />
                )}
              </div>

              <div className="absolute top-10 right-10 z-30 bg-[#014034] text-white p-6 rounded-full w-32 h-32 flex flex-col items-center justify-center shadow-xl">
                <span className="text-xs font-bold uppercase tracking-widest text-[#4DB6AC]">Since</span>
                <span className="text-3xl font-black">{aboutSettings.year}</span>
              </div>
            </div>

            {/* Text Column */}
            <div className="lg:pl-10 mt-12 lg:mt-0">
              <span className="text-[#00695c] font-bold text-sm uppercase tracking-widest mb-4 block">
                About Our Agency
              </span>

              <h2 className="text-4xl md:text-5xl font-extrabold text-[#014034] mb-6">
                {aboutSettings.title}
              </h2>

              <p className="text-xl italic border-l-4 border-[#4DB6AC] pl-6 mb-6">
                {aboutSettings.subtitle}
              </p>

              <p className="text-gray-600 text-lg mb-8">
                {aboutSettings.desc}
              </p>

              <ul className="space-y-4 mb-10">
                {aboutSettings.list.map((item: string, idx: number) => (
                  <li key={idx} className="flex items-center gap-3 font-bold text-gray-700">
                    <span className="w-2 h-2 bg-[#014034] rounded-full"></span>
                    {item}
                  </li>
                ))}
              </ul>

              <Link to="/about"
                className="inline-flex items-center bg-[#014034] text-white px-10 py-4 rounded-xl font-bold hover:bg-[#00332a] transition-all">
                Discover More <ArrowRight className="ml-3" />
              </Link>
            </div>

          </div>
        </div>
      </section>


      {/* 🟢 DYNAMIC ADVANTAGE SECTION */}
      <section className="py-24 bg-white">
        <div className="container mx-auto px-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
            
            {/* বাম পাশে ইমেজ ও স্ট্যাটস */}
            <div className="relative">
              <img src={advSettings.image} alt="Meeting" className="rounded-[3rem] shadow-2xl z-10 relative object-cover w-full h-auto min-h-[400px]" />
              <div className="absolute -bottom-8 -right-8 bg-[#014034] p-10 rounded-3xl text-white shadow-xl z-20 hidden md:block border-4 border-white">
                <div className="flex items-center space-x-4">
                  <TrendingUp className="text-[#4DB6AC]" size={40} />
                  <div>
                    <h4 className="text-3xl font-bold">{advSettings.stat_num}</h4>
                    <p className="text-teal-100 text-sm">{advSettings.stat_text}</p>
                  </div>
                </div>
              </div>
            </div>

            {/* ডান পাশে টেক্সট ও ফিচার */}
            <div>
              <span className="text-[#00695c] font-bold text-sm uppercase tracking-widest mb-4 block">{advSettings.subtitle}</span>
              <h2 className="text-4xl md:text-5xl font-extrabold text-[#014034] mb-8">{advSettings.title}</h2>
              <div className="space-y-8">
                {advSettings.features.map((item: any, idx: number) => {
                    const FeatIcon = IconMap[item.icon] || Target;
                    return (
                        <div key={idx} className="flex items-start space-x-5">
                            <div className="bg-[#014034]/5 p-2 rounded-lg text-[#014034]"><FeatIcon size={24} /></div>
                            <div>
                                <h4 className="text-xl font-bold text-[#014034]">{item.title}</h4>
                                <p className="text-gray-600">{item.desc}</p>
                            </div>
                        </div>
                    );
                })}
              </div>
              <div className="mt-12">
                <Link to="/get-quote" className="bg-[#014034] text-white px-10 py-5 rounded-xl font-bold text-lg hover:bg-[#00332a] transition-all inline-flex items-center shadow-lg group">
                  Get Your Free Growth Plan <ArrowRight className="ml-3 group-hover:translate-x-2 transition-transform" />
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="py-24 bg-gray-50 overflow-hidden">
        <div className="container mx-auto px-6 mb-16 text-center">
          <h2 className="text-4xl font-extrabold text-[#014034]">Real Results for Ambitious Brands</h2>
        </div>
        <div className="relative flex whitespace-nowrap">
          <div className="flex animate-marquee-slow space-x-8 px-8">
            {dbTestimonials.length > 0 ? (
              [...dbTestimonials, ...dbTestimonials].map((t, idx) => (
              <div key={idx} className="inline-block w-[450px] whitespace-normal p-12 bg-white rounded-[2.5rem] shadow-sm border border-gray-100 shrink-0">
                <Quote className="text-[#014034]/5 absolute top-10 right-10" size={60} />
                <p className="text-gray-600 mb-10 italic text-xl min-h-[120px]">"{t.content}"</p>
                <div className="flex items-center space-x-5">
                  <img src={getUrl(t.avatar_url)} alt={t.name} className="w-16 h-16 rounded-full object-cover" />
                  <div>
                      <h4 className="font-bold text-[#014034] text-lg">{t.name}</h4>
                      <p className="text-sm text-[#00695c] font-bold uppercase">{t.role}, {t.company}</p>
                  </div>
                </div>
              </div>
            ))
            ) : <p className="w-full text-center text-gray-400 p-8">Loading reviews...</p>}
          </div>
        </div>
      </section>

      <section className="py-24 bg-white border-t border-gray-100">
        <div className="container mx-auto px-6 mb-16 flex flex-col md:flex-row justify-between items-end">
          <div><span className="text-[#00695c] font-bold text-sm uppercase mb-4 block">Case Studies</span><h2 className="text-4xl font-extrabold text-[#014034]">Featured Success Stories</h2></div>
          <Link to="/portfolio" className="text-[#00695c] font-bold hover:underline flex items-center">View All Work <ArrowRight className="ml-2 w-4 h-4" /></Link>
        </div>
        <div className="relative flex whitespace-nowrap overflow-hidden py-10">
          <div className="flex animate-marquee space-x-12 px-12">
            {dbPortfolio.length > 0 ? (
              [...dbPortfolio, ...dbPortfolio].map((p, idx) => (
                <div key={idx} onClick={() => navigate(`/portfolio/${p.id}`)} className="group relative inline-block w-[400px] aspect-[4/3] rounded-[2.5rem] overflow-hidden shadow-lg cursor-pointer shrink-0">
                  <img src={getUrl(p.image_url)} alt={p.title} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                  <div className="absolute inset-0 bg-gradient-to-t from-[#014034] via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-8">
                    <span className="text-[#4DB6AC] font-bold text-xs uppercase mb-2">{p.category}</span>
                    <h3 className="text-2xl font-bold text-white mb-2">{p.title}</h3>
                    <p className="text-gray-200 text-sm">Client: {p.client}</p>
                  </div>
                </div>
              ))
            ) : <p className="text-white bg-[#014034] p-4 rounded-xl">Loading case studies...</p>}
          </div>
        </div>
      </section>

      <section className="py-24 bg-white">
        <div className="container mx-auto px-6">
          <div className="deep-green-gradient rounded-[4rem] p-12 md:p-24 text-center text-white shadow-2xl relative overflow-hidden">
            <div className="relative z-10 max-w-3xl mx-auto">
              <h2 className="text-4xl md:text-6xl font-extrabold mb-8">{ctaSettings.title}</h2>
              {ctaSettings.subtitle && <p className="text-xl text-teal-100 mb-10">{ctaSettings.subtitle}</p>}
              <div className="flex flex-col sm:flex-row justify-center gap-6">
                
                {/* 🟢 ফিক্স: এখানে style অবজেক্ট ব্যবহার করা হয়েছে এবং backgroundColor থাকলে ডিফল্ট ক্লাস ওভাররাইড করা হচ্ছে */}
                
                <Link 
                    to={buttons.footer_cta_primary?.url || "/get-quote"} 
                    style={buttons.footer_cta_primary ? buttons.footer_cta_primary.style : {}}
                    className={`px-12 py-5 rounded-2xl font-extrabold text-xl shadow-xl hover:scale-105 transition-all ${
                        buttons.footer_cta_primary ? '' : 'bg-white text-[#014034]'
                    }`}
                >
                    {buttons.footer_cta_primary?.label || "Get a Free Growth Plan"}
                </Link>

                <Link 
                    to={buttons.footer_cta_secondary?.url || "/book-consultation"} 
                    style={buttons.footer_cta_secondary ? buttons.footer_cta_secondary.style : {}}
                    className={`px-12 py-5 rounded-2xl font-extrabold text-xl hover:bg-white/10 transition-all ${
                        buttons.footer_cta_secondary ? '' : 'border-2 border-white/40 text-white'
                    }`}
                >
                    {buttons.footer_cta_secondary?.label || "Book a Consultation"}
                </Link>

              </div>
            </div>
          </div>
        </div>
      </section>

      
            <section className="py-20 bg-white border-b border-gray-100 overflow-hidden">
                <div className="container mx-auto px-6 mb-12">
                  <p className="text-center text-gray-400 font-bold uppercase tracking-[0.2em] text-xs">Trusted by Growing Businesses Worldwide</p>
                </div>
                <div className="relative flex whitespace-nowrap overflow-hidden">
                <div className="flex animate-marquee items-center space-x-24 md:space-x-32 py-4">
                  {dbLogos.length > 0 ? (
                  [...dbLogos, ...dbLogos].map((logo, idx) => (
                    <img key={idx} src={getUrl(logo.image_url)} alt="Client" className="h-8 md:h-12 w-auto opacity-40 grayscale hover:grayscale-0 hover:opacity-100 transition-all shrink-0" />
                  ))
                  ) : <p className="text-gray-300 font-bold text-sm w-full text-center">No logos uploaded</p>}
                </div>
                </div>
            </section>

    </div>
  );
};

export default Home;