import React, { useState, useEffect } from 'react';
import { Check, ArrowRight, ChevronDown, ChevronUp, Loader2 } from 'lucide-react';
import { Link } from 'react-router-dom';
import { PricingPlan } from '../types';

const PricingCard: React.FC<{ plan: PricingPlan }> = ({ plan }) => {
  const [isExpanded, setIsExpanded] = useState(false);
  const initialCount = 4;
  
  // ডাইনামিক ডাটাতে ফিচারগুলো অনেক সময় স্ট্রিং হিসেবে আসে, তাই অ্যারে নিশ্চিত করা
  const features = Array.isArray(plan.features) ? plan.features : [];
  const visibleFeatures = isExpanded ? features : features.slice(0, initialCount);
  const hasMore = features.length > initialCount;

  return (
    <div className={`bg-white rounded-[3rem] p-12 border shadow-sm relative flex flex-col transition-all duration-300 ${plan.is_popular ? 'border-[#014034] ring-2 ring-[#014034] lg:scale-105 z-10' : 'border-gray-100'}`}>
      {plan.is_popular && <div className="absolute top-0 right-1/2 translate-x-1/2 -translate-y-1/2 bg-[#014034] text-white text-xs font-bold uppercase px-6 py-2 rounded-full shadow-lg">Most Popular</div>}
      <div className="mb-8">
        <h3 className="text-2xl font-bold text-[#014034] mb-4">{plan.name}</h3>
        <div className="flex items-baseline space-x-1"><span className="text-5xl font-extrabold text-[#014034]">{plan.price}</span>{plan.period && <span className="text-gray-400">/{plan.period}</span>}</div>
        <p className="text-gray-500 mt-6">{plan.description}</p>
      </div>
      <div className="flex-grow">
        <p className="font-bold text-[#014034] text-sm uppercase mb-6">What's included:</p>
        <ul className="space-y-4 mb-6">
          {visibleFeatures.map((f, i) => (
            <li key={i} className="flex items-start text-gray-600">
              <div className="bg-green-50 p-1 rounded-full mr-3 text-green-600 mt-0.5"><Check size={14} /></div>
              <span className="text-sm font-medium">{f}</span>
            </li>
          ))}
        </ul>
        {hasMore && (
          <button onClick={() => setIsExpanded(!isExpanded)} className="flex items-center space-x-2 text-[#00695c] font-bold text-xs uppercase mb-10">
            <span>{isExpanded ? 'Show Fewer' : 'View All Features'}</span>
            {isExpanded ? <ChevronUp size={16} /> : <ChevronDown size={16} />}
          </button>
        )}
      </div>
      <Link to="/get-quote" className={`w-full py-5 rounded-2xl font-extrabold text-lg flex items-center justify-center space-x-3 transition-all ${plan.is_popular ? 'bg-[#014034] text-white hover:bg-[#00332a]' : 'bg-gray-100 text-[#014034] hover:bg-gray-200'}`}>
        <span>Get Started</span><ArrowRight size={20} />
      </Link>
    </div>
  );
};

const Pricing: React.FC = () => {
  const [plans, setPlans] = useState<PricingPlan[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('https://onmark360.com/api/get-pricing-plans.php')
      .then(res => res.json())
      .then(data => {
        setPlans(data);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, []);

  if (loading) return <div className="h-screen flex items-center justify-center"><Loader2 className="animate-spin text-[#014034]" size={48} /></div>;

  return (
    <div className="pt-32 pb-24 bg-gray-50">
      <div className="container mx-auto px-6">
        <div className="text-center max-w-3xl mx-auto mb-20">
          <span className="text-[#00695c] font-bold text-sm uppercase mb-4 block">Pricing Plans</span>
          <h1 className="text-5xl md:text-6xl font-extrabold text-[#014034] mb-6">Invest in Your Growth</h1>
          <p className="text-xl text-gray-600">Tailored transparent models for every business stage.</p>
        </div>
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-10">
          {plans.map((p) => <PricingCard key={p.id} plan={p} />)}
        </div>
      </div>
    </div>
  );
};

export default Pricing;