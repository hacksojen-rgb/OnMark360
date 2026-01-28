
import { Service, PortfolioProject, Testimonial, BlogPost, HeroSlide, PricingPlan, SiteSettings } from './types';

export const API_BASE = 'https://onmark360.com/api';
export const COLORS = {
  primary: '#014034',
  primaryLight: '#00695c',
  accent: '#4DB6AC',
  background: '#F8F9FA'
};

export const SITE_SETTINGS: SiteSettings = {
  companyName: 'OnMark360', 
  address: '5000 W International Airport Rd', // আপনার নতুন ঠিকানা দিন
  phone: '+1-201-642-2724',
  email: 'support@onmark360.com'
  facebook: '#',
  twitter: '#',
  instagram: '#',
  linkedin: '#',
  aboutTitle: 'Fueling Growth for Tomorrow\'s Leaders',
  aboutText: 'Founded in 2018, OnMark360 started with a simple vision: to bridge the gap between complex technology and business success.'
};

export const INITIAL_HERO_SLIDES: HeroSlide[] = [
  {
    id: '1',
    title: "We Help Businesses Grow with High-Converting Websites, Content & Marketing",
    subtitle: "From strategy to execution — we build websites, content, and marketing systems that generate leads and sales.",
    ctaPrimary: "Get a Free Growth Plan",
    ctaSecondary: "Book a Free Consultation",
    image: "https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&q=80&w=2070"
  },
  {
    id: '2',
    title: "Authority-Building Content & Premium Branding",
    subtitle: "Position your brand as the undisputed market leader with premium design and scroll-stopping digital content.",
    ctaPrimary: "Start Your Project",
    ctaSecondary: "View Case Studies",
    image: "https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&q=80&w=2015"
  },
  {
    id: '3',
    title: "Full-Stack Marketing that Generates Real ROI",
    subtitle: "Stop guessing and start growing. Our data-driven strategies focus on lead generation and measurable sales impact.",
    ctaPrimary: "Claim Your Audit",
    ctaSecondary: "See How We Work",
    image: "https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&q=80&w=2070"
  }
];

export const SERVICES: Service[] = [
  {
    id: '1',
    title: 'Web Development',
    description: 'High-converting websites that turn visitors into customers.',
    icon: 'Code2',
    features: [
      'Custom React-based Frontend Architecture',
      'Mobile-first Responsive Grid Layouts',
      'Headless CMS Integration (Contentful/Sanity)',
      'Performance-Optimized Asset Loading',
      'Advanced Conversion Tracking Scripts',
      'Robust SEO-Ready Meta Management',
      'Progressive Web App (PWA) Capabilities',
      'Interactive UI/UX Component Libraries',
      'E-commerce Integration (Shopify/Stripe)',
      'Dynamic Form Building & Validation',
      'Multi-language Support (i18n Implementation)',
      'Secure SSL/TLS Configuration',
      'Accessibility (WCAG) Compliance Check',
      'Cross-browser Compatibility Testing',
      'API-driven Dynamic Content Rendering',
      'Scalable Backend Node.js Services',
      'Database Design & Optimization (SQL/NoSQL)',
      'Automated CI/CD Deployment Pipelines',
      'Server-side Rendering (SSR) for SEO',
      'Daily Backups & Performance Audits'
    ]
  },
  {
    id: '2',
    title: 'Digital Marketing',
    description: 'Generate consistent leads and sales through data-driven campaigns.',
    icon: 'Target',
    features: [
      'Multi-channel Strategy Development',
      'Search Engine Marketing (SEM) Campaigns',
      'Social Media Advertising (Meta, LinkedIn, X)',
      'Retargeting & Remarketing Funnel Design',
      'Advanced Audience Persona Mapping',
      'High-converting Landing Page Production',
      'Omni-channel Content Distribution',
      'Marketing Automation Workflow Setup',
      'Strategic Influencer Outreach Coordination',
      'Pay-Per-Click (PPC) Management & Bidding',
      'Video Ad Scripting & Media Placement',
      'Competitor Ad Strategy Benchmarking',
      'Advanced Lead Magnet Strategy',
      'Brand Narrative & Storytelling Frameworks',
      'Real-time Ad Performance Monitoring',
      'A/B Creative Testing & Statistical Analysis',
      'Customer Acquisition Cost (CAC) Optimization',
      'Attribution Modeling & Sales Mapping',
      'Email Marketing Segmentation & Nurturing',
      'Monthly Growth ROI Accountability Reports'
    ]
  },
  {
    id: '3',
    title: 'SEO Optimization',
    description: 'Long-term organic traffic that compounds your growth.',
    icon: 'Search',
    features: [
      'Comprehensive Technical SEO Site Audits',
      'Deep Keyword Search Intent Research',
      'Strategic On-page Content Optimization',
      'Semantic Content Gap Analysis',
      'Authority Backlink Profile Development',
      'Core Web Vitals Performance Fixes',
      'Schema Markup & Structured Data Config',
      'Local SEO & Google Business Management',
      'Mobile Usability & Speed Enhancement',
      'Competitive SERP Landscape Analysis',
      'Monthly Keyword Ranking & Trend Tracking',
      'URL Structure & Information Architecture Audit',
      'Internal Linking Strategy Optimization',
      'XML Sitemap & Robots.txt Customization',
      'Image Alt-text & Rich Snippet SEO',
      'Long-form Pillar Content Strategies',
      '404 Error & Redirect Management (301/302)',
      'International SEO Hreflang Tagging',
      'Domain Authority Growth Roadmapping',
      'Detailed Organic Growth Attribution'
    ]
  },
  {
    id: '4',
    title: 'UI/UX Design',
    description: 'Designs that improve trust, usability, and conversion.',
    icon: 'PenTool',
    features: [
      'User Research & Behavioral Persona Mapping',
      'Interactive User Journey Flow Design',
      'High-fidelity Visual UI Mockups',
      'Information Architecture & Navigation Logic',
      'Clickable Prototype Development',
      'Accessibility (WCAG) Standard Compliance',
      'Comprehensive Brand Design Systems',
      'Strategic Typography & Color Theory',
      'Micro-interaction & Animation Design',
      'Usability Testing & Feedback Synthesis',
      'Heatmap & Click-tracking Analysis',
      'Multi-device Responsive Adaptation',
      'Bespoke Interface Iconography Sets',
      'Moodboards & Visual Style Guides',
      'Conversion-centered Layout Prioritization',
      'Intuitive Search & Filter UI Design',
      'Component-based Reusable Design Units',
      'Design-to-Developer Handoff Documentation',
      'Aesthetic Refresh & Modernization Audits',
      'Continuous UI Improvement Cycles'
    ]
  },
  {
    id: '5',
    title: 'Content Creation',
    description: 'Scroll-stopping content that builds trust and authority.',
    icon: 'Megaphone',
    features: [
      'Strategic Brand Voice & Tone Definition',
      'Conversion-focused Persuasive Copywriting',
      'Authority-building Blog Series Management',
      'High-impact Video Content Scripting',
      'Social Media Engagement Strategies',
      'Whitepaper & Industry Report Research',
      'Case Study Interviewing & Drafting',
      'Infographic Data Research & Copy',
      'Newsletter Editorial Calendar Planning',
      'Direct-response Email Sequence Writing',
      'Product Page Copy Optimization',
      'Interactive Polls & Quiz Development',
      'Content Repurposing (Blog to Social)',
      'Narrative-driven Brand Storytelling',
      'SEO-enhanced Web Content Creation',
      'Multi-format Asset Production Guides',
      'Expert Opinion & Thought Leadership Ghostwriting',
      'Technical Documentation Simplification',
      'Community Engagement Post Templates',
      'Content Performance & Engagement Audits'
    ]
  },
  {
    id: '6',
    title: 'Analytics & Reporting',
    description: 'Clear insights so you know what’s working and what’s not.',
    icon: 'BarChart3',
    features: [
      'Google Analytics 4 (GA4) Custom Setup',
      'GTM Event & Conversion Tracking Setup',
      'Bespoke Business Intelligence Dashboards',
      'Multi-touch Funnel Conversion Visuals',
      'Marketing Channel Attribution Analysis',
      'User Journey Path & Drop-off Mapping',
      'Heatmap & Session Recording Integration',
      'Weekly Growth Executive Summaries',
      'Customer Lifetime Value (LTV) Forecasts',
      'Churn & Retention Behavioral Reports',
      'Automated Multi-platform Data Pipelines',
      'Predictive Modeling & Trend Analysis',
      'Granular E-commerce Sales Performance',
      'Creative Performance Attribution Data',
      'Segmented Audience Interest Reports',
      'Real-time Metric Monitoring Alerts',
      'Raw Data Cleansing & Deduplication',
      'Quarterly Strategic Growth Consultations',
      'Benchmarking Against Industry Averages',
      'Actionable Next-step Growth Planning'
    ]
  }
];

export const CLIENT_LOGOS = [
  'https://cdn.worldvectorlogo.com/logos/airbnb.svg',
  'https://cdn.worldvectorlogo.com/logos/amazon-2.svg',
  'https://cdn.worldvectorlogo.com/logos/hubspot.svg',
  'https://cdn.worldvectorlogo.com/logos/slack-new-logo.svg',
  'https://cdn.worldvectorlogo.com/logos/netflix-3.svg',
  'https://cdn.worldvectorlogo.com/logos/uber-2.svg'
];

export const PORTFOLIO: PortfolioProject[] = [
  {
    id: '1',
    title: 'SaaS Growth Platform',
    category: 'Web Development',
    imageUrl: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&q=80&w=800',
    client: 'Streamline AI'
  },
  {
    id: '2',
    title: 'Premium D2C Branding',
    category: 'Design & Branding',
    imageUrl: 'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&q=80&w=800',
    client: 'Velvet Flora'
  },
  {
    id: '3',
    title: 'Video Ad Campaign',
    category: 'Video Editing',
    imageUrl: 'https://images.unsplash.com/photo-1581291518066-5e5898867c3b?auto=format&fit=crop&q=80&w=800',
    client: 'NextGen Fitness'
  },
  {
    id: '4',
    title: 'Multi-Channel Growth Strategy',
    category: 'Marketing',
    imageUrl: 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&q=80&w=800',
    client: 'Global Logistics'
  }
];

export const TESTIMONIALS: Testimonial[] = [
  {
    id: '1',
    name: 'Sarah Johnson',
    role: 'CEO',
    company: 'Nexus Tech',
    content: 'The growth plan they delivered was a game-changer. We saw a 40% increase in qualified leads within the first month of working together.',
    avatar: 'https://i.pravatar.cc/150?u=sarah'
  },
  {
    id: '2',
    name: 'Michael Chen',
    role: 'Founder',
    company: 'Stellar Startups',
    content: 'Professional, fast, and results-focused. They finally made our brand look premium and credible to enterprise clients.',
    avatar: 'https://i.pravatar.cc/150?u=michael'
  },
  {
    id: '3',
    name: 'Emma Williams',
    role: 'Marketing Lead',
    company: 'Green Path',
    content: 'Finally an agency that speaks "business" and not just "design". They understood our ROI goals from day one and executed flawlessly.',
    avatar: 'https://i.pravatar.cc/150?u=emma'
  }
];

export const BLOG_POSTS: BlogPost[] = [
  {
    id: '1',
    title: 'Why Most B2B Websites Fail to Convert',
    excerpt: 'Learn the 3 critical design mistakes that are costing you leads and how to fix them for immediate results.',
    author: 'David Smith',
    date: 'Oct 15, 2023',
    category: 'Strategy',
    imageUrl: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&q=80&w=800'
  },
  {
    id: '2',
    title: 'The Psychology of High-Converting Landing Pages',
    excerpt: 'Deep dive into visual hierarchy and cognitive biases that drive user action on your digital platforms.',
    author: 'Elena Ross',
    date: 'Oct 22, 2023',
    category: 'Design',
    imageUrl: 'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&q=80&w=800'
  },
  {
    id: '3',
    title: 'Scaling from $1M to $10M: The Growth Playbook',
    excerpt: 'A blueprint for ambitious startups ready to dominate their category using integrated marketing systems.',
    author: 'Mark Wood',
    date: 'Nov 02, 2023',
    category: 'Growth',
    imageUrl: 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&q=80&w=800'
  }
];

export const INITIAL_PRICING_PLANS: PricingPlan[] = [
  {
    id: '1',
    name: 'Starter Growth',
    price: '$2,500',
    period: 'mo',
    description: 'Perfect for small businesses looking to establish a digital presence.',
    features: [
      'Modern High-Conversion Website',
      'Monthly Technical SEO Audit',
      'Performance Reporting Dashboard',
      '1 Social Media Channel Management',
      'Basic Email Marketing Integration',
      '2 Strategic Growth Consultations/Year',
      'Standard Security & Backups',
      'Mobile Optimization Pack'
    ],
    isPopular: false
  },
  {
    id: '2',
    name: 'Scale Up',
    price: '$5,000',
    period: 'mo',
    description: 'The preferred choice for ambitious brands ready for market expansion.',
    features: [
      'Advanced Conversion Optimization (CRO)',
      'Enterprise SEO Suite (Global)',
      'Weekly Strategic Performance Insights',
      '3 Social Media Channels Management',
      'Full Digital Ad Management (PPC)',
      'A/B Creative Testing Cycles',
      'Priority Developer Support',
      'Content Marketing Engine (4 Blogs/mo)',
      'Marketing Automation Integration',
      'Competitor Benchmark Analysis',
      'Bi-weekly Strategic Consultations',
      'Advanced User Behavioral Tracking'
    ],
    isPopular: true
  },
  {
    id: '3',
    name: 'Enterprise',
    price: 'Custom',
    period: '',
    description: 'Full-service digital transformation for large-scale operations.',
    features: [
      'Full-stack Bespoke Development',
      'Omni-channel Global Strategy',
      'Real-time Custom BI Dashboards',
      'Dedicated Strategy Team (3 FTE)',
      'Unlimited Campaign Scaling',
      'Deep Data Science Attribution',
      'Custom API & Middleware Development',
      '24/7 Priority Emergency Response',
      'Exclusive Executive Workshops',
      'Corporate Branding & Governance',
      'In-house Team Training & Workshops',
      'Enterprise-grade Cloud Infrastructure',
      'Custom CRM & ERP Data Syncing',
      'Global Legal & Compliance (GDPR/CCPA)'
    ],
    isPopular: false
  }
];
