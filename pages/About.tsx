import React, { useState, useEffect } from 'react';
import { Target, Users, Zap, Globe, Loader2 } from 'lucide-react';
import { SITE_SETTINGS, API_BASE } from '../constants';

const About: React.FC = () => {
  // ১. স্টেট ডিক্লেয়ারেশন (ডাটা রাখার জন্য)
  const [pageData, setPageData] = useState<any>({
    title: SITE_SETTINGS.aboutTitle,
    subtitle: "Growth is our only metric.",
    description: SITE_SETTINGS.aboutText
  });
  const [loading, setLoading] = useState(true);

  // ২. ডাটাবেজ থেকে ডাটা আনা (useEffect)
  useEffect(() => {
    fetch(`${API_BASE}/get-settings.php`)
      .then(res => res.json())
      .then(data => {
        // সেটিংস থেকে ডাটা বের করা
        const settings = data.settings || data;
        
        // যদি ডাটাবেজে ডাটা থাকে, তা পার্স করা
        if (settings.about_page_content) {
            const dynamicContent = JSON.parse(settings.about_page_content);
            setPageData({
                title: dynamicContent.title || SITE_SETTINGS.aboutTitle,
                subtitle: dynamicContent.subtitle || "Growth is our only metric.",
                description: dynamicContent.description || SITE_SETTINGS.aboutText
            });
        }
        setLoading(false);
      })
      .catch(err => {
        console.error("About Page Error:", err);
        setLoading(false);
      });
  }, []);

  // লোডিং অবস্থায় স্পিনার দেখাতে চাইলে এটি রাখতে পারেন, অথবা ডাইরেক্ট রিটার্ন করতে পারেন
  if (loading) {
     // অপশনাল: লোডিং এর সময় ফাঁকা স্ক্রিন বা লোডার দেখানো
     // return <div className="h-screen flex justify-center items-center"><Loader2 className="animate-spin text-[#014034]"/></div>;
  }

  return (
    <div className="pt-32 pb-24">
      <div className="container mx-auto px-6">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center mb-24">
          <div>
            <span className="text-[#00695c] font-bold text-sm uppercase mb-3 block">Our Story</span>
            
            {/* ৩. এখানে ডাইনামিক টাইটেল বসানো হয়েছে */}
            <h1 className="text-5xl font-extrabold text-[#014034] mb-8 leading-tight">
                {pageData.title}
            </h1>
            
            {/* ৪. এখানে ডাইনামিক ডেসক্রিপশন বসানো হয়েছে */}
            <p className="text-xl text-gray-600 leading-relaxed mb-8 whitespace-pre-line">
                {pageData.description}
            </p>
            
            <div className="grid grid-cols-2 gap-8">
              <div><h4 className="text-4xl font-extrabold text-[#014034] mb-1">250+</h4><p className="text-gray-500">Projects Delivered</p></div>
              <div><h4 className="text-4xl font-extrabold text-[#014034] mb-1">98%</h4><p className="text-gray-500">Client Satisfaction</p></div>
            </div>
          </div>
          <div className="relative">
            <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&q=80&w=2070" className="rounded-[3rem] shadow-2xl" alt="About" />
            <div className="absolute -bottom-10 -right-10 bg-[#014034] p-10 rounded-3xl text-white hidden md:block">
              {/* ৫. সাবটাইটেল ডাইনামিক করা হয়েছে */}
              <h3 className="text-3xl font-bold mb-2 italic">"{pageData.subtitle}"</h3>
              <p className="text-teal-200">— Founder's Motto</p>
            </div>
          </div>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 py-16">
          {[
            { icon: Target, title: "Our Mission", desc: "Empower businesses with enterprise-grade tools." },
            { icon: Users, title: "Our Team", desc: "Experts passionate about pushing boundaries." },
            { icon: Zap, title: "Our Speed", desc: "Rapid deployment without quality loss." },
            { icon: Globe, title: "Our Reach", desc: "Global perspective, localized strategies." }
          ].map((item, idx) => (
            <div key={idx} className="text-center">
              <div className="w-16 h-16 bg-[#014034]/5 rounded-2xl flex items-center justify-center mx-auto mb-6 text-[#014034]"><item.icon size={32} /></div>
              <h3 className="text-xl font-bold text-[#014034] mb-3">{item.title}</h3>
              <p className="text-gray-600">{item.desc}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default About;