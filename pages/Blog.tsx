import React, { useState, useEffect } from 'react';
import { Calendar, User, ArrowRight, Loader2 } from 'lucide-react';
import { Link } from 'react-router-dom';

const Blog: React.FC = () => {
  const [posts, setPosts] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('https://onmark360.com/api/get-blogs.php')
      .then(res => res.json())
      .then(data => {
        setPosts(Array.isArray(data) ? data : []);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, []);

  if (loading) return <div className="h-screen flex items-center justify-center"><Loader2 className="animate-spin text-[#014034]" size={48} /></div>;

  return (
    <div className="pt-32 pb-24 bg-white">
      <div className="container mx-auto px-6">
        <div className="text-center max-w-3xl mx-auto mb-20">
          <span className="text-[#00695c] font-bold text-sm uppercase mb-4 block tracking-widest">Our Journal</span>
          <h1 className="text-5xl font-extrabold text-[#014034] mb-6 tracking-tighter">Insights for Growth</h1>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
          {posts.length > 0 ? posts.map((post) => {
            // ইমেজ যদি লিংক হয় তবে সরাসরি নেবে, নাহলে ডোমেইন পাথ যোগ করবে
            const imageUrl = post.image_url.startsWith('http') ? post.image_url : `https://onmark360.com/${post.image_url}`;
            
            return (
              <article key={post.id} className="bg-white rounded-[2.5rem] overflow-hidden border border-gray-100 shadow-sm hover:shadow-2xl transition-all flex flex-col group">
                <div className="h-64 overflow-hidden relative">
                  <img src={imageUrl} alt={post.title} className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                  <div className="absolute top-6 left-6 bg-white px-4 py-1 rounded-full text-[10px] font-black uppercase text-[#014034] shadow-lg">{post.category}</div>
                </div>
                <div className="p-8 flex-grow">
                  <h3 className="text-2xl font-black text-[#014034] mb-4 hover:text-[#4DB6AC] cursor-pointer leading-tight transition-colors">{post.title}</h3>
                  <p className="text-gray-500 mb-6 line-clamp-3 text-sm leading-relaxed">{post.content.replace(/<[^>]*>?/gm, '').substring(0, 120)}...</p>
                  <div className="flex items-center justify-between pt-6 border-t border-gray-50">
                    <div className="flex items-center space-x-3 text-xs text-gray-400 font-bold uppercase tracking-widest">
                      <span className="flex items-center"><Calendar size={12} className="mr-1 text-[#4DB6AC]" /> {new Date(post.created_at).toLocaleDateString()}</span>
                    </div>
                    <Link to={`/blog/${post.slug}`} className="text-[#014034] font-black uppercase text-xs tracking-widest inline-flex items-center hover:translate-x-1 transition-transform">
                      Read More <ArrowRight className="ml-1" size={14} />
                    </Link>
                  </div>
                </div>
              </article>
            );
          }) : <div className="col-span-full text-center py-20 text-gray-400 font-bold uppercase tracking-widest">No Posts Found</div>}
        </div>
      </div>
    </div>
  );
};

export default Blog;