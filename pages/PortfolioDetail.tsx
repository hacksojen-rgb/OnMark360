import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, ExternalLink, Loader2 } from 'lucide-react';
import { API_BASE } from '../constants';

const PortfolioDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [project, setProject] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // সরাসরি নির্দিষ্ট প্রজেক্টের ডাটা ফেচ করার জন্য API কল
    fetch(`${API_BASE}/get-portfolio.php`)
      .then(res => res.json())
      .then(data => {
        // আইডি অনুযায়ী সঠিক প্রজেক্টটি খুঁজে বের করা
        const found = data.find((p: any) => p.id.toString() === id);
        setProject(found);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, [id]);

  if (loading) return <div className="h-screen flex items-center justify-center"><Loader2 className="animate-spin text-[#014034]" size={48} /></div>;
  
  if (!project) return (
    <div className="h-screen flex flex-col items-center justify-center">
      <h2 className="text-2xl font-bold text-red-500 mb-4">Project Not Found</h2>
      <button onClick={() => navigate('/portfolio')} className="text-[#014034] font-bold flex items-center">
        <ArrowLeft size={20} className="mr-2" /> Back to Portfolio
      </button>
    </div>
  );

  // ইমেজ ইউআরএল হ্যান্ডলিং (লিংক নাকি আপলোড করা ফাইল)
  const imageUrl = project.image_url && project.image_url.startsWith('http') 
    ? project.image_url 
    : `https://agency.পাতা.বাংলা/${project.image_url}`;

  return (
    <div className="pt-32 pb-24 bg-white">
      <div className="container mx-auto px-6">
        <button onClick={() => navigate('/portfolio')} className="inline-flex items-center text-[#014034] font-bold mb-10 hover:-translate-x-2 transition-transform">
          <ArrowLeft size={20} className="mr-2" /> Back to Work
        </button>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-16">
          <div>
            <span className="text-[#4DB6AC] font-bold text-sm uppercase tracking-widest mb-4 block">{project.category}</span>
            <h1 className="text-4xl md:text-6xl font-black text-[#014034] mb-8 leading-tight">{project.title}</h1>
            
            <div className="bg-gray-50 p-8 rounded-[2.5rem] mb-10">
              <h4 className="text-xs font-black uppercase text-gray-400 mb-4 tracking-widest">Project Details</h4>
              <div className="space-y-4">
                <div className="flex justify-between border-b border-gray-200 pb-4">
                  <span className="text-gray-500 font-bold uppercase text-[10px]">Client</span>
                  <span className="text-[#014034] font-black text-sm uppercase">{project.client}</span>
                </div>
              </div>
            </div>

            <div className="prose prose-lg text-gray-600 leading-relaxed whitespace-pre-wrap">
              {project.content}
            </div>
          </div>

          <div className="relative">
            <div className="sticky top-32">
              <img src={imageUrl} alt={project.title} className="w-full h-auto rounded-[3rem] shadow-2xl border border-gray-100" />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PortfolioDetail;