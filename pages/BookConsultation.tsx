import React, { useState } from 'react';
import { Calendar, Clock, ArrowRight, CheckCircle, AlertCircle } from 'lucide-react';
import { API_BASE } from '../constants';
import { Helmet } from 'react-helmet-async';

const BookConsultation: React.FC = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    company: '',
    date: '',
    time: '',
    topic: 'General Strategy'
  });
  const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setStatus('loading');

    // মেসেজের ভেতরেই ডেট এবং টাইম ঢুকিয়ে দেওয়া হচ্ছে যাতে অ্যাডমিন দেখতে পায়
    const detailedMessage = `
      REQUEST FOR CONSULTATION
      ------------------------
      Preferred Date: ${formData.date}
      Preferred Time: ${formData.time}
      Company: ${formData.company}
      Topic: ${formData.topic}
    `;

    try {
      const response = await fetch(`${API_BASE}/save-lead.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: formData.name,
          email: formData.email,
          phone: formData.phone,
          subject: `Consultation Request: ${formData.topic}`,
          message: detailedMessage,
          source: 'Free Consultation' // 🔥 এটি অ্যাডমিন প্যানেলে ফিল্টার করতে সাহায্য করবে
        }),
      });

      const data = await response.json();
      if (data.success) {
        setStatus('success');
        setFormData({ name: '', email: '', phone: '', company: '', date: '', time: '', topic: 'General Strategy' });
      } else {
        setStatus('error');
      }
    } catch (error) {
      setStatus('error');
    }
  };

  return (
    <div className="pt-32 pb-20 bg-gray-50 min-h-screen">
      <Helmet>
        <title>Book a Free Consultation | On Mark </title>
        <meta name="description" content="Schedule a free 30-minute strategy session with our experts." />
      </Helmet>

      <div className="container mx-auto px-6">
        <div className="max-w-4xl mx-auto bg-white rounded-[3rem] shadow-xl overflow-hidden border border-gray-100 flex flex-col md:flex-row">
          
          {/* বাম পাশ: তথ্য */}
          <div className="md:w-2/5 bg-[#014034] p-12 text-white flex flex-col justify-between">
            <div>
              <span className="text-[#4DB6AC] font-bold tracking-widest text-xs uppercase mb-4 block">Discovery Call</span>
              <h1 className="text-3xl font-black mb-6">Let's Talk Growth</h1>
              <p className="text-gray-300 mb-8 leading-relaxed">
                Book a free 30-minute strategy session. No commitment, just value. We'll discuss your current bottlenecks and potential solutions.
              </p>
              
              <div className="space-y-4">
                <div className="flex items-center space-x-3">
                  <div className="bg-white/10 p-2 rounded-lg"><Calendar className="w-5 h-5 text-[#4DB6AC]" /></div>
                  <span className="font-medium">Select a Date</span>
                </div>
                <div className="flex items-center space-x-3">
                  <div className="bg-white/10 p-2 rounded-lg"><Clock className="w-5 h-5 text-[#4DB6AC]" /></div>
                  <span className="font-medium">Pick a Time Slot</span>
                </div>
              </div>
            </div>
            <div className="mt-12 pt-8 border-t border-white/10">
              <p className="text-xs text-gray-400">Trusted by 50+ businesses worldwide.</p>
            </div>
          </div>

          {/* ডান পাশ: ফর্ম */}
          <div className="md:w-3/5 p-12">
            {status === 'success' ? (
              <div className="h-full flex flex-col items-center justify-center text-center animate-in fade-in zoom-in">
                <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-6">
                  <CheckCircle className="w-10 h-10 text-green-600" />
                </div>
                <h3 className="text-2xl font-bold text-[#014034] mb-2">Request Received!</h3>
                <p className="text-gray-600">We have received your consultation request. Our team will confirm the schedule via email shortly.</p>
                <button onClick={() => setStatus('idle')} className="mt-8 text-[#00695c] font-bold hover:underline">Book Another</button>
              </div>
            ) : (
              <form onSubmit={handleSubmit} className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <label className="text-xs font-bold uppercase text-gray-500 tracking-wider">Your Name</label>
                    <input required type="text" className="w-full bg-gray-50 border border-gray-200 p-4 rounded-xl focus:outline-none focus:border-[#00695c] transition-colors" placeholder="John Doe" value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} />
                  </div>
                  <div className="space-y-2">
                    <label className="text-xs font-bold uppercase text-gray-500 tracking-wider">Work Email</label>
                    <input required type="email" className="w-full bg-gray-50 border border-gray-200 p-4 rounded-xl focus:outline-none focus:border-[#00695c] transition-colors" placeholder="john@company.com" value={formData.email} onChange={e => setFormData({...formData, email: e.target.value})} />
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <label className="text-xs font-bold uppercase text-gray-500 tracking-wider">Phone</label>
                    <input required type="tel" className="w-full bg-gray-50 border border-gray-200 p-4 rounded-xl focus:outline-none focus:border-[#00695c] transition-colors" placeholder="+1 (555) 000-0000" value={formData.phone} onChange={e => setFormData({...formData, phone: e.target.value})} />
                  </div>
                  <div className="space-y-2">
                    <label className="text-xs font-bold uppercase text-gray-500 tracking-wider">Topic</label>
                    <select className="w-full bg-gray-50 border border-gray-200 p-4 rounded-xl focus:outline-none focus:border-[#00695c]" value={formData.topic} onChange={e => setFormData({...formData, topic: e.target.value})}>
                      <option>General Strategy</option>
                      <option>Web Development</option>
                      <option>SEO & Marketing</option>
                      <option>Branding</option>
                    </select>
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <label className="text-xs font-bold uppercase text-gray-500 tracking-wider">Preferred Date</label>
                    <input required type="date" className="w-full bg-gray-50 border border-gray-200 p-4 rounded-xl focus:outline-none focus:border-[#00695c]" value={formData.date} onChange={e => setFormData({...formData, date: e.target.value})} />
                  </div>
                  <div className="space-y-2">
                    <label className="text-xs font-bold uppercase text-gray-500 tracking-wider">Preferred Time</label>
                    <input required type="time" className="w-full bg-gray-50 border border-gray-200 p-4 rounded-xl focus:outline-none focus:border-[#00695c]" value={formData.time} onChange={e => setFormData({...formData, time: e.target.value})} />
                  </div>
                </div>

                <button type="submit" disabled={status === 'loading'} className="w-full bg-[#014034] text-white font-bold py-4 rounded-xl hover:bg-[#00332a] transition-all flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl disabled:opacity-70 disabled:cursor-not-allowed">
                  {status === 'loading' ? <span>Processing...</span> : <><span>Confirm Booking</span> <ArrowRight className="w-5 h-5" /></>}
                </button>
                
                {status === 'error' && (
                  <div className="flex items-center text-red-500 text-sm font-bold justify-center space-x-2">
                    <AlertCircle className="w-4 h-4" /> <span>Something went wrong. Please try again.</span>
                  </div>
                )}
              </form>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default BookConsultation;