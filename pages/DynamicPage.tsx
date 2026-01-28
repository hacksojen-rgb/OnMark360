import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Loader2 } from 'lucide-react';
import { API_BASE } from '../constants';
import { Helmet } from 'react-helmet-async';

const DynamicPage: React.FC = () => {
  const { slug } = useParams<{ slug: string }>();
  const navigate = useNavigate();
  const [pageData, setPageData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // API থেকে পেজের ডাটা আনা
    fetch(`${API_BASE}/get-page.php?slug=${slug}`)
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          setPageData(data.data);
        } else {
          // পেজ না থাকলে 404 বা হোমে রিডাইরেক্ট
          navigate('/'); 
        }
        setLoading(false);
      })
      .catch(() => {
        navigate('/');
        setLoading(false);
      });
  }, [slug, navigate]);

  if (loading) return <div className="h-screen flex items-center justify-center"><Loader2 className="animate-spin text-[#014034]" size={48} /></div>;
  if (!pageData) return null;

  return (
    <div className="pt-32 pb-24 bg-white min-h-screen">
      <Helmet>
        <title>{pageData.title} | On Mark</title>
      </Helmet>
      
      <div className="container mx-auto px-6 max-w-4xl">
        <h1 className="text-4xl md:text-5xl font-black text-[#014034] mb-8 uppercase tracking-tight">
          {pageData.title}
        </h1>
        
        {/* 🔥 TinyMCE বা এডিটর থেকে আসা HTML রেন্ডার করার জন্য এটি বাধ্যতামূলক */}
        <div 
          className="prose prose-lg max-w-none text-gray-600 leading-relaxed"
          dangerouslySetInnerHTML={{ __html: pageData.content }} 
        />
        
        <div className="mt-12 text-sm text-gray-400 font-bold uppercase tracking-widest border-t pt-4">
            Last Updated: {new Date(pageData.updated_at).toLocaleDateString()}
        </div>
      </div>
    </div>
  );
};

export default DynamicPage;