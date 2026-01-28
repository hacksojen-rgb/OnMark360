import React, { useState, useEffect } from 'react';
import { Mail, Phone, MapPin, Loader2 } from 'lucide-react';
import { API_BASE, SITE_SETTINGS } from '../constants';

const Contact: React.FC = () => {
  const [status, setStatus] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  
  // ১. পেজের তথ্যের জন্য স্টেট
  const [pageContent, setPageContent] = useState({
    title: "Let's Talk",
    subtitle: "Question or project? We're here to help you grow.",
    email: SITE_SETTINGS.email || "hello@OnMark360.com",
    phone: SITE_SETTINGS.phone || "+1 (555) 000-0000",
    address: SITE_SETTINGS.address || "Quarter office indo pacific"
  });
  const [loadingConfig, setLoadingConfig] = useState(true);

  // ২. ডাটাবেজ থেকে সেটিংস ফেচ করা
  useEffect(() => {
    fetch(`${API_BASE}/get-settings.php`)
      .then(res => res.json())
      .then(data => {
        const settings = data.settings || {};
        
        // কন্টাক্ট পেজের স্পেসিফিক কনফিগারেশন পার্স করা
        let contactConfig = {};
        if (settings.contact_page_content) {
            try {
                contactConfig = JSON.parse(settings.contact_page_content);
            } catch (e) {
                console.error("JSON Parse Error", e);
            }
        }

        // ডাটা সেট করা (Contact Config > Global Settings > Default)
        setPageContent({
            title: (contactConfig as any).title || "Let's Talk",
            subtitle: (contactConfig as any).subtitle || "Question or project? We're here to help you grow.",
            // ইমেইল/ফোন: যদি কন্টাক্ট পেজের জন্য আলাদা সেট করা থাকে সেটা নেবে, নাহলে গ্লোবাল সেটিংস
            email: (contactConfig as any).email || settings.email || "hello@example.com",
            phone: (contactConfig as any).phone || settings.phone || "00000000",
            // এড্রেস গ্লোবাল সেটিংস থেকে আসবে
            address: settings.address || "Quarter office indo pacific"
        });
        setLoadingConfig(false);
      })
      .catch(err => {
        console.error("Settings Fetch Error:", err);
        setLoadingConfig(false);
      });
  }, []);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setIsSubmitting(true);
    setStatus(null);
    const formData = new FormData(e.currentTarget);

    try {
      const response = await fetch(`${API_BASE}/save-lead.php`, {
        method: 'POST',
        body: formData
      });
      
      const data = await response.json();
      if (data.success) {
        setStatus('Thank you! Your message has been sent.');
        e.currentTarget.reset();
      } else {
        setStatus('Error: ' + (data.error || 'Please try again.'));
      }
    } catch (err) {
      setStatus('Failed to connect to server. Please check your internet connection.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="pt-32 pb-24 bg-white min-h-screen">
      <div className="container mx-auto px-6">
        <div className="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-20">
          
          {/* বামদিকের কন্টাক্ট ইনফো (ডাইনামিক করা হয়েছে) */}
          <div>
            {loadingConfig ? (
                <div className="animate-pulse space-y-4">
                    <div className="h-12 bg-gray-200 rounded w-3/4"></div>
                    <div className="h-4 bg-gray-200 rounded w-1/2"></div>
                </div>
            ) : (
                <>
                    <h1 className="text-5xl font-extrabold text-[#014034] mb-8 tracking-tighter">
                        {pageContent.title}
                    </h1>
                    <p className="text-xl text-gray-600 mb-12">
                        {pageContent.subtitle}
                    </p>
                </>
            )}

            <div className="space-y-8">
              <div className="flex items-start space-x-6">
                <div className="w-14 h-14 bg-[#014034]/5 rounded-xl flex items-center justify-center text-[#014034] shrink-0"><MapPin size={24} /></div>
                <div>
                    <h4 className="text-lg font-bold text-[#014034]">Our Office</h4>
                    <p className="text-gray-600">{pageContent.address}</p>
                </div>
              </div>
              <div className="flex items-start space-x-6">
                <div className="w-14 h-14 bg-[#014034]/5 rounded-xl flex items-center justify-center text-[#014034] shrink-0"><Phone size={24} /></div>
                <div>
                    <h4 className="text-lg font-bold text-[#014034]">Call Us</h4>
                    <p className="text-gray-600">{pageContent.phone}</p>
                </div>
              </div>
              <div className="flex items-start space-x-6">
                <div className="w-14 h-14 bg-[#014034]/5 rounded-xl flex items-center justify-center text-[#014034] shrink-0"><Mail size={24} /></div>
                <div>
                    <h4 className="text-lg font-bold text-[#014034]">Email Us</h4>
                    <p className="text-gray-600">{pageContent.email}</p>
                </div>
              </div>
            </div>
          </div>

          {/* ডানদিকের ফর্ম সেকশন */}
          <div className="bg-white p-10 rounded-3xl shadow-xl border border-gray-100">
            <h3 className="text-2xl font-bold text-[#014034] mb-8 uppercase tracking-tight">Send a Message</h3>
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <input required name="name" type="text" className="px-5 py-3 rounded-xl border focus:border-[#014034] outline-none transition-all" placeholder="Name" />
                <input required name="email" type="email" className="px-5 py-3 rounded-xl border focus:border-[#014034] outline-none transition-all" placeholder="Email" />
              </div>
              <input name="phone" type="text" className="w-full px-5 py-3 rounded-xl border focus:border-[#014034] outline-none" placeholder="Phone (Optional)" />
              <input required name="subject" type="text" className="w-full px-5 py-3 rounded-xl border focus:border-[#014034] outline-none" placeholder="Subject" />
              <textarea required name="message" rows={5} className="w-full px-5 py-3 rounded-xl border focus:border-[#014034] outline-none" placeholder="Message" />
              <button disabled={isSubmitting} type="submit" className="w-full bg-[#014034] text-white py-4 rounded-xl font-bold hover:bg-[#00332a] flex items-center justify-center space-x-2 disabled:opacity-50 shadow-lg transition-all">
                {isSubmitting ? <Loader2 className="animate-spin" size={20} /> : <span>Send Message</span>}
              </button>
              {status && (
                <div className={`p-4 rounded-xl text-center font-bold text-sm ${status.includes('Error') ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600'}`}>
                  {status}
                </div>
              )}
            </form>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Contact;