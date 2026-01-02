import { useEffect, useState } from 'react';
import { useRouter } from 'expo-router';
import { useAuth } from '@/contexts/AuthContext';
import SplashScreen from './splash';

export default function Index() {
  const { session, loading } = useAuth();
  const [isSplashReady, setSplashReady] = useState(false);
  const router = useRouter();

  useEffect(() => {
    // Show splash screen for at least 3 seconds
    const timer = setTimeout(() => {
      setSplashReady(true);
    }, 3000);

    return () => clearTimeout(timer);
  }, []);

  useEffect(() => {
    if (!loading && isSplashReady) {
      if (session) {
        // Using replace can sometimes be tricky if the root is not ready,
        // but since we are in a component that is rendered, it should be fine.
        // We'll wrap in a small timeout to ensure navigation happens after render.
        setTimeout(() => {
          router.replace('/(tabs)');
        }, 0);
      } else {
        setTimeout(() => {
          router.replace('/login');
        }, 0);
      }
    }
  }, [session, loading, isSplashReady]);

  return <SplashScreen />;
}
