import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import * as LucideIcons from 'lucide-react';
import { API_BASE } from '../constants';
import { SiteSettings, SocialLink } from '../types';

const Footer: React.FC = () => {
  const [settings, setSettings] = useState<SiteSettings | null>(null);
  const [socials, setSocials] = useState<SocialLink[]>([]);
  const [footerLinks, setFooterLinks] = useState<{ explore: any[]; support: any[] }>({
    explore: [],
    support: []
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(`${API_BASE}/get-settings.php`)
      .then(res => res.json())
      .then(data => {
        setSettings(data.settings || null);
        setSocials(data.social_links || []);
        setFooterLinks(data.footer_links || { explore: [], support: [] });
        setLoading(false);
      })
      .catch(console.error);
  }, []);

  if (loading || !settings) return null;

  const getUrl = (url?: string) => {
    if (!url) return '';
    if (url.startsWith('http')) return url;
    
    // যদি পাথ-এ uploads না থাকে, তবে যোগ করুন
    const cleanUrl = url.replace(/^\/+/, '');
    const finalUrl = cleanUrl.startsWith('uploads/') ? cleanUrl : `uploads/${cleanUrl}`;
    
    return `${API_BASE.replace('/api', '')}/${finalUrl}`;
  };

  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-[#012a22] text-white pt-24 pb-12 rounded-t-[3rem] mt-20">
      <div className="container mx-auto px-6">

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 border-b border-white/10 pb-16">
          
          {/* Brand */}
          <div className="space-y-6">
            <div className="flex items-center space-x-3">
              {settings.footer_logo_url ? (
                <img
                  src={getUrl(settings.footer_logo_url)}
                  alt="Logo"
                  className="h-10 brightness-0 invert"
                />
              ) : (
                <span className="text-2xl font-black">{settings.company_name}</span>
              )}
            </div>

            <p className="text-white/60 text-sm leading-relaxed max-w-xs">
              {settings.about_text}
            </p>

            {/* Social Icons */}
            <div className="flex space-x-4 pt-4">
              {socials.map(soc => {
                const Icon = (LucideIcons as any)[soc.icon_code] || LucideIcons.Link;
                return (
                  <a
                    key={soc.id}
                    href={soc.url}
                    target="_blank"
                    rel="noreferrer"
                    className="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center hover:bg-[#4DB6AC] hover:text-[#012a22] transition-all"
                  >
                    <Icon size={18} />
                  </a>
                );
              })}
            </div>
          </div>

          {/* Explore Links (CMS) */}
          {settings.show_footer_links !== '0' && settings.show_footer_links !== 0 && (
            <div>
              <h4 className="text-lg font-bold mb-6">Explore</h4>
              <ul className="space-y-4">
                {footerLinks.explore.length > 0 ? (
                  footerLinks.explore.map((link, i) => (
                    <li key={i}>
                      <Link
                        to={link.path}
                        className="text-white/60 hover:text-[#4DB6AC] transition-colors text-sm font-medium"
                      >
                        {link.label}
                      </Link>
                    </li>
                  ))
                ) : (
                  <li className="text-white/40 text-xs">No links added</li>
                )}
              </ul>
            </div>
          )}

          {/* Support Links (CMS) */}
          <div>
            <h4 className="text-lg font-bold mb-6">Support</h4>
            <ul className="space-y-4">
              {footerLinks.support.length > 0 ? (
                footerLinks.support.map((link, i) => (
                  <li key={i}>
                    <Link
                      to={link.path}
                      className="text-white/60 hover:text-[#4DB6AC] transition-colors text-sm font-medium"
                    >
                      {link.label}
                    </Link>
                  </li>
                ))
              ) : (
                <li className="text-white/40 text-xs">No links added</li>
              )}
            </ul>
          </div>

          {/* Contact Info */}
          <div>
            <h4 className="text-lg font-bold mb-6">Get in Touch</h4>
            <ul className="space-y-4 text-sm text-white/60">
              <li className="flex items-start space-x-3">
                <LucideIcons.MapPin size={18} className="text-[#4DB6AC]" />
                <span>{settings.address}</span>
              </li>
              <li className="flex items-center space-x-3">
                <LucideIcons.Phone size={18} className="text-[#4DB6AC]" />
                <span>{settings.phone}</span>
              </li>
              <li className="flex items-center space-x-3">
                <LucideIcons.Mail size={18} className="text-[#4DB6AC]" />
                <span>{settings.email}</span>
              </li>
            </ul>
          </div>
        </div>

        {/* ✅ Dynamic Copyright + Privacy + Terms */}
        <div className="pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-white/40 font-bold uppercase tracking-widest">
          <p>
            {settings.footer_copyright_text ||
              `© ${currentYear} ${settings.company_name}. All rights reserved.`}
          </p>
          <div className="flex space-x-6 mt-4 md:mt-0">
            <Link
              to={settings.footer_privacy_url || '/privacy'}
              className="hover:text-white transition-colors"
            >
              {settings.footer_privacy_text || 'Privacy'}
            </Link>
            <Link
              to={settings.footer_terms_url || '/terms'}
              className="hover:text-white transition-colors"
            >
              {settings.footer_terms_text || 'Terms'}
            </Link>
          </div>
        </div>

      </div>
    </footer>
  );
};

export default Footer;
