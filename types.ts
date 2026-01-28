export interface NavItem {
  label: string;
  path: string;
}

export interface SiteButton {
  label: string;
  url: string;
  style: {
    backgroundColor: string;
    color: string;
    borderColor: string;
    borderWidth: string;
  }
}

export interface SocialLink {
  id: string;
  platform: string;
  icon_code: string;
  url: string;
}

export interface Service {
  id: string;
  title: string;
  description: string;
  icon: string;
  features?: string[];
}

export interface PortfolioProject {
  id: string;
  title: string;
  category: string;
  imageUrl: string;
  client: string;
  content?: string;
}

export interface Testimonial {
  id: string;
  name: string;
  role: string;
  company: string;
  content: string;
  avatar: string;
}

export interface BlogPost {
  id: string;
  title: string;
  excerpt: string;
  content?: string;
  author: string;
  date: string;
  category: string;
  imageUrl: string;
  slug: string; // Slug added
  created_at: string; // Date added
}

export interface HeroSlide {
  id: string;
  title: string;
  subtitle: string;
  cta_primary: string;
  cta_secondary: string;
  cta_primary_url?: string;   // New Field
  cta_secondary_url?: string; // New Field
  image_url: string;
}

export interface SiteSettings {
  company_name: string;
  site_title: string;      // Used as primary color
  logo_url: string;
  footer_logo_url: string; // New Field
  favicon_url: string;
  address: string;
  phone: string;
  email: string;
  about_title: string;
  about_text: string;
  header_nav: NavItem[];   // Dynamic Menu
  footer_links: boolean | string; // Flag for show/hide explore
}

export interface PricingPlan {
  id: string;
  name: string;
  price: string;
  period: string;
  description: string;
  features: string[];
  is_popular: boolean;
}