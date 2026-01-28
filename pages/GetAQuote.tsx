import React, { useState, useEffect } from 'react';
import { useLocation, useSearchParams } from 'react-router-dom';
import { Rocket, ShieldCheck, CheckCircle2, Loader2 } from 'lucide-react';
import { API_BASE } from '../constants';

const GetAQuote: React.FC = () => {
  const location = useLocation();
  const [searchParams] = useSearchParams();
  const [submitted, setSubmitted] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [loadingConfig, setLoadingConfig] = useState(true);
  
  // Dynamic Config State
  const [config, setConfig] = useState<any>({
    budgets: [],
    privacyText: '',
    requirements: { is_phone_required: true, is_website_required: false }
  });

  const servicesList = ["Web Development", "Digital Marketing", "SEO Optimization", "UI/UX Design", "Content Creation", "Analytics & Reporting"];
  const [selectedService, setSelectedService] = useState("");
  const [selectedBudget, setSelectedBudget] = useState("");

  // ১. ডাটাবেজ থেকে কনফিগারেশন আনা
  useEffect(() => {
    fetch(`${API_BASE}/get-settings.php`)
      .then(res => res.json())
      .then(data => {
        const settings = data.settings || data;
        
        // JSON পার্সিং
        const budgetList = settings.quote_budget_ranges ? JSON.parse(settings.quote_budget_ranges) : [];
        const formReq = settings.quote_form_config ? JSON.parse(settings.quote_form_config) : {};
        
        setConfig({
          budgets: budgetList.length > 0 ? budgetList : ["$1,000 - $5,000", "Custom"],
          privacyText: settings.quote_privacy_text || 'I agree to the Privacy Policy.',
          requirements: formReq
        });
        setLoadingConfig(false);
      })
      .catch(err => {
        console.error("Config Error:", err);
        setLoadingConfig(false);
      });

    // URL বা স্টেট থেকে সার্ভিস সেট করা
    const serviceFromUrl = searchParams.get('service');
    const state = location.state as { selectedService?: string };

    if (serviceFromUrl) setSelectedService(serviceFromUrl);
    else if (state?.selectedService) setSelectedService(state.selectedService);
  }, [location.state, searchParams]);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setIsSubmitting(true);
    const formData = new FormData(e.currentTarget);

    try {
      const response = await fetch(`${API_BASE}/save-lead.php`, {
        method: 'POST',
        body: formData,
      });
      const data = await response.json();
      if (data.success) setSubmitted(true);
      else alert("Error: " + (data.error || "Submission failed"));
    } catch (err) {
      alert("Failed to connect to server.");
    } finally {
      setIsSubmitting(false);
    }
  };

  if (loadingConfig) return <div className="h-screen flex items-center justify-center"><Loader2 className="animate-spin text-[#014034]" /></div>;

  if (submitted) {
    return (
      <div className="pt-40 pb-24 container mx-auto px-6 text-center min-h-screen bg-white">
        <div className="max-w-xl mx-auto p-16 rounded-[3rem] shadow-xl border border-gray-100">
          <CheckCircle2 size={64} className="text-green-500 mx-auto mb-8" />
          <h2 className="text-3xl font-black text-[#014034] mb-4 uppercase tracking-tighter">Growth Plan Requested!</h2>
          <p className="text-gray-500 mb-10 font-medium text-sm">Tailored strategy within 24 hours.</p>
          <button onClick={() => setSubmitted(false)} className="bg-[#014034] text-white px-10 py-4 rounded-xl font-bold uppercase tracking-widest shadow-lg">Request Another</button>
        </div>
      </div>
    );
  }

  return (
    <div className="pt-32 pb-24 bg-gray-50/30 min-h-screen">
      <div className="container mx-auto px-6">
        <div className="max-w-4xl mx-auto">
          <div className="text-center mb-16">
            <span className="text-[#00695c] font-black text-[10px] uppercase tracking-[0.4em] mb-4 block">Let's Build Together</span>
            <h1 className="text-5xl font-black text-[#014034] mb-6 tracking-tighter uppercase leading-tight">Tell us about your business</h1>
            <p className="text-gray-500 font-medium max-w-2xl mx-auto text-sm leading-relaxed">Complete this short brief to help us understand your growth goals.</p>
          </div>

          <form onSubmit={handleSubmit} className="bg-white p-10 md:p-16 rounded-[4rem] shadow-2xl border border-gray-100">
            {/* Contact Details */}
            <div className="mb-16">
              <h3 className="text-xl font-black text-[#014034] mb-8 flex items-center uppercase tracking-tight border-b pb-4">
                <span className="w-8 h-8 rounded-full bg-[#014034] text-white text-[10px] flex items-center justify-center mr-4 font-bold">1</span>
                Contact Details
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label className="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-2 tracking-widest italic">Full Name*</label>
                    <input required name="name" type="text" className="w-full px-6 py-4 rounded-2xl border border-gray-100 outline-none bg-gray-50/50 focus:bg-white transition-all font-medium" placeholder="Jane Doe" />
                </div>
                <div>
                    <label className="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-2 tracking-widest italic">Work Email*</label>
                    <input required name="email" type="email" className="w-full px-6 py-4 rounded-2xl border border-gray-100 outline-none bg-gray-50/50 focus:bg-white transition-all font-medium" placeholder="jane@company.com" />
                </div>
                <div>
                    <label className="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-2 tracking-widest italic">Phone Number {config.requirements.is_phone_required ? '*' : '(Optional)'}</label>
                    <input required={config.requirements.is_phone_required} name="phone" type="tel" className="w-full px-6 py-4 rounded-2xl border border-gray-100 outline-none bg-gray-50/50 focus:bg-white transition-all font-medium" placeholder="+1 (555) 000-0000" />
                </div>
                <div>
                    <label className="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-2 tracking-widest italic">Selected Service*</label>
                    <select name="subject" value={selectedService} onChange={(e) => setSelectedService(e.target.value)} required className="w-full px-6 py-4 rounded-2xl border border-gray-100 outline-none bg-gray-50/50 font-bold text-[#014034] transition-all">
                        <option value="">Select a Service</option>
                        <option value="Free Growth Plan">Get a Free Growth Plan</option>
                        {servicesList.map(s => <option key={s} value={s}>{s}</option>)}
                    </select>
                </div>
              </div>
            </div>

            {/* Business Profiling */}
            <div className="mb-16">
              <h3 className="text-xl font-black text-[#014034] mb-8 flex items-center uppercase tracking-tight border-b pb-4">
                <span className="w-8 h-8 rounded-full bg-[#014034] text-white text-[10px] flex items-center justify-center mr-4 font-bold">2</span>
                Business Profiling
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <label className="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-2 tracking-widest italic">Company Name*</label>
                    <input required name="company" type="text" className="w-full px-6 py-4 rounded-2xl border border-gray-100 outline-none bg-gray-50/50 font-medium" placeholder="Acme Growth Co." />
                </div>
                <div>
                    <label className="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-2 tracking-widest italic">Website URL {config.requirements.is_website_required ? '*' : '(Optional)'}</label>
                    <input required={config.requirements.is_website_required} name="website" type="url" className="w-full px-6 py-4 rounded-2xl border border-gray-100 outline-none bg-gray-50/50 font-medium" placeholder="https://www.acme.com" />
                </div>
                
                {/* DYNAMIC BUDGET SECTION */}
                <div>
                    <label className="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-2 tracking-widest italic">Monthly Budget Range*</label>
                    <select required name="budget" className="w-full px-6 py-4 rounded-2xl border border-gray-100 outline-none bg-gray-50/50 font-bold text-[#014034]" onChange={(e) => setSelectedBudget(e.target.value)}>
                        <option value="">Select a range</option>
                        {config.budgets.map((b: string, i: number) => (
                          <option key={i} value={b}>{b}</option>
                        ))}
                        <option value="Custom">Custom Amount</option>
                    </select>
                    {selectedBudget === 'Custom' && (
                        <input type="text" name="custom_budget" placeholder="Enter amount" className="mt-4 w-full px-6 py-4 rounded-2xl border border-gray-100 bg-white animate-in fade-in" />
                    )}
                </div>

                <div>
                    <label className="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-2 tracking-widest italic">Are you the decision maker?*</label>
                    <div className="flex items-center space-x-8 mt-4 ml-2">
                        <label className="flex items-center cursor-pointer text-sm font-bold text-[#014034] group">
                            <input type="radio" name="decision_maker" value="Yes" className="mr-3 w-4 h-4 accent-[#014034]" /> Yes
                        </label>
                        <label className="flex items-center cursor-pointer text-sm font-bold text-[#014034] group">
                            <input type="radio" name="decision_maker" value="No" className="mr-3 w-4 h-4 accent-[#014034]" /> No
                        </label>
                    </div>
                </div>
              </div>

              <div className="space-y-8">
                <div>
                    <label className="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-2 tracking-widest italic">Biggest growth challenge*</label>
                    <textarea required name="challenge" rows={3} className="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50/50 font-medium" />
                </div>
                <div>
                    <label className="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-2 tracking-widest italic">Project Objectives*</label>
                    <textarea required name="message" rows={3} className="w-full px-6 py-4 rounded-2xl border border-gray-100 bg-gray-50/50 font-medium" />
                </div>
              </div>
            </div>

            {/* DYNAMIC PRIVACY POLICY */}
            <div className="mb-12 p-6 bg-gray-50/50 rounded-3xl border border-dashed border-gray-200">
                <label className="flex items-start cursor-pointer">
                    <input type="checkbox" required className="mt-1.5 mr-4 w-5 h-5 accent-[#014034] cursor-pointer" />
                    <span 
                      className="text-[11px] text-gray-500 font-medium leading-loose"
                      dangerouslySetInnerHTML={{ __html: config.privacyText }}
                    ></span>
                </label>
            </div>

            <button disabled={isSubmitting} type="submit" className="w-full bg-[#014034] text-white py-6 rounded-[1.5rem] font-black text-xl hover:bg-[#00332a] flex items-center justify-center space-x-4 shadow-2xl disabled:opacity-50 transition-all uppercase tracking-widest group">
              {isSubmitting ? <Loader2 className="animate-spin" size={24} /> : <span>Generate My Growth Plan</span>}
              {!isSubmitting && <Rocket size={24} className="group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
};

export default GetAQuote;