import React, { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { useTracking } from '../context/TrackingContext';

const PageTracker: React.FC = () => {
  const location = useLocation();
  const { trackEvent } = useTracking();

  useEffect(() => {
    // পেজ চেঞ্জ হলে PageView ইভেন্ট ফায়ার হবে
    trackEvent('PageView');
  }, [location.pathname, trackEvent]);

  return null;
};

export default PageTracker;