import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { Calendar, User, ArrowLeft, Loader2 } from 'lucide-react';

const BlogPost: React.FC = () => {
  const { slug } = useParams<{ slug: string }>();
  const [post, setPost] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(`https://onmark360.com/api/get-blog-details.php?slug=${slug}`)
      .then(res => res.json())
      .then(data => {
        setPost(data);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, [slug]);

  if (loading) return <div className="h-screen flex items-center justify-center"><Loader2 className="animate-spin text-[#014034]" size={48} /></div>;
  if (!post) return <div className="h-screen flex flex-col items-center justify-center"><h2>Post Not Found</h2><Link to="/blog">Back to Blog</Link></div>;

  const imageUrl = post.image_url.startsWith('http') ? post.image_url : `https://onmark360.com/${post.image_url}`;

  return (
    <div className="pt-32 pb-24 bg-white min-h-screen">
      <div className="container mx-auto px-6">
        <div className="max-w-5xl mx-auto"> {/* ডেস্কটপে চওড়া করার জন্য max-w-5xl ব্যবহার করা হয়েছে */}
          
          {/* Back Button */}
          <Link to="/blog" className="inline-flex items-center text-[#014034] font-bold mb-8 hover:-translate-x-2 transition-all duration-300">
            <ArrowLeft size={20} className="mr-2" /> Back to Journal
          </Link>

          {/* Featured Image */}
          <div className="relative h-[300px] md:h-[600px] w-full overflow-hidden rounded-[2rem] md:rounded-[3.5rem] mb-12 shadow-2xl">
            <img src={imageUrl} alt={post.title} className="w-full h-full object-cover" />
          </div>

          {/* Meta Information */}
          <div className="flex flex-wrap items-center gap-4 md:gap-8 mb-8 text-gray-500 font-bold uppercase text-[10px] md:text-xs tracking-widest">
             <span className="flex items-center bg-gray-50 px-4 py-2 rounded-full">
               <Calendar size={14} className="mr-2 text-[#4DB6AC]" /> 
               {new Date(post.created_at).toLocaleDateString()}
             </span>
             <span className="flex items-center bg-gray-50 px-4 py-2 rounded-full">
               <User size={14} className="mr-2 text-[#4DB6AC]" /> 
               {post.author || 'Admin'}
             </span>
             <span className="bg-[#014034] text-white px-6 py-2 rounded-full">
               {post.category}
             </span>
          </div>

          {/* Title */}
          <h1 className="text-3xl md:text-6xl font-black text-[#014034] mb-10 leading-[1.1] tracking-tighter">
            {post.title}
          </h1>

          {/* Content Body */}
<div
  className="prose prose-lg md:prose-xl max-w-none text-gray-600 leading-relaxed font-medium
             prose-headings:text-[#014034] prose-headings:font-bold
             prose-a:text-[#4DB6AC]
             prose-img:rounded-2xl prose-img:shadow-lg"
  dangerouslySetInnerHTML={{ __html: post.content }}
/>


        </div>
      </div>
    </div>
  );
};

export default BlogPost;