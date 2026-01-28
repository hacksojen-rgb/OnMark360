import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { API_BASE } from '../constants';
import * as LucideIcons from 'lucide-react';
import { CheckCircle2, ArrowRight } from 'lucide-react';
import { Service } from '../types';

const ServiceCard: React.FC<{ service: Service }> = ({ service }) => {
  const Icon = (LucideIcons as any)[service.icon] || LucideIcons.Target;
  
  return (
    <div className="bg-white p-10 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col hover:shadow-xl transition-all duration-500 group relative overflow-hidden">
      <div className="w-14 h-14 bg-gray-50 text-[#014034] rounded-2xl flex items-center justify-center mb-8 group-hover:bg-[#014034] group-hover:text-white transition-all duration-500">
        <Icon size={28} />
      </div>
      <div className="flex-grow">
        <h3 className="text-2xl font-bold text-[#014034] mb-4">{service.title}</h3>
        <p className="text-gray-500 mb-8 leading-relaxed">{service.description}</p>
        <div className="space-y-4 mb-10">
          {(service.features || []).slice(0, 4).map((f, i) => (
            <div key={i} className="flex items-start text-sm font-medium text-gray-600">
              <CheckCircle2 className="text-[#4DB6AC] mr-3 mt-0.5 shrink-0" size={16} />
              <span>{f}</span>
            </div>
          ))}
        </div>
        <Link 
          to="/get-quote" 
          state={{ selectedService: service.title }}
          className="inline-flex items-center text-[#014034] font-bold text-sm uppercase tracking-widest hover:gap-3 transition-all"
        >
          Book Service <ArrowRight className="ml-2" size={18} />
        </Link>
      </div>
    </div>
  );
};

const Services: React.FC = () => {
  const [dbServices, setDbServices] = useState<Service[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(`${API_BASE}/get-services.php`)
      .then(res => res.json())
      .then(data => {
        setDbServices(Array.isArray(data) ? data : []);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, []);

  return (
    <div className="pt-32 pb-24 bg-white min-h-screen">
      <div className="container mx-auto px-6">
        <div className="text-center max-w-2xl mx-auto mb-20">
          <p className="text-[#00695c] font-bold text-xs uppercase tracking-widest mb-4">Our Solutions</p>
          <h1 className="text-4xl md:text-5xl font-black text-[#014034] mb-6">Engineered for Business Outcomes</h1>
          <p className="text-gray-500 font-medium italic">We focus on metrics that matter.</p>
        </div>

        {loading ? (
          <div className="text-center py-20 font-bold text-[#014034]">Loading...</div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {dbServices.map((s) => <ServiceCard key={s.id} service={s} />)}
          </div>
        )}
      </div>
    </div>
  );
};

export default Services;