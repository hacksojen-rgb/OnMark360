import React from 'react';
import { HashRouter as Router, Routes, Route, useLocation } from 'react-router-dom';
// 👇 এই ইম্পোর্টটি আগে ছিল না বা মিসিং ছিল
import PageTracker from './components/PageTracker'; 
import GlobalSEO from './components/GlobalSEO';
import Navbar from './components/Navbar';
import Footer from './components/Footer';
import Home from './pages/Home';
import Services from './pages/Services';
import Portfolio from './pages/Portfolio';
import PortfolioDetail from './pages/PortfolioDetail';
import Blog from './pages/Blog';
import BlogPost from './pages/BlogPost'; 
import About from './pages/About';
import Contact from './pages/Contact';
import GetAQuote from './pages/GetAQuote';
import Pricing from './pages/Pricing';
import BookConsultation from './pages/BookConsultation';
import DynamicPage from './pages/DynamicPage';

// ScrollToTop কম্পোনেন্ট (এটি এখানেই রাখা যায়)
const ScrollToTop: React.FC = () => {
  const { pathname } = useLocation();
  React.useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);
  return null;
};

const MainLayout: React.FC<{ children?: React.ReactNode }> = ({ children }) => {
  return (
    <div className="flex flex-col min-h-screen">
      <GlobalSEO />
      <Navbar />
      <main className="flex-grow">
        {children}
      </main>
      <Footer />
    </div>
  );
};

const App: React.FC = () => {
  return (
    <Router>
      <PageTracker />
      <ScrollToTop />
      
      <MainLayout>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/services" element={<Services />} />
          <Route path="/portfolio" element={<Portfolio />} />
          <Route path="/portfolio/:id" element={<PortfolioDetail />} />
          <Route path="/blog" element={<Blog />} />
          <Route path="/blog/:slug" element={<BlogPost />} />
          <Route path="/about" element={<About />} />
          <Route path="/contact" element={<Contact />} />
          <Route path="/pricing" element={<Pricing />} />
          <Route path="/get-quote" element={<GetAQuote />} />
          <Route path="/book-consultation" element={<BookConsultation />} />
          
          {/* Dynamic Pages */}
          <Route path="/:slug" element={<DynamicPage />} />
        </Routes>
      </MainLayout>
    </Router>
  );
};

export default App;