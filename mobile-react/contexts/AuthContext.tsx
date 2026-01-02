import React, { createContext, useContext, useEffect, useState } from 'react';
import { api } from '@/lib/api';
import AsyncStorage from '@react-native-async-storage/async-storage';

type Session = {
  token: string;
  username?: string;
  fullname?: string;
};

type AuthContextType = {
  session: Session | null;
  loading: boolean;
  signIn: (username: string, password: string) => Promise<void>;
  signOut: () => Promise<void>;
};

const AuthContext = createContext<AuthContextType>({
  session: null,
  loading: true,
  signIn: async () => {},
  signOut: async () => {},
});

export const useAuth = () => useContext(AuthContext);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [session, setSession] = useState<Session | null>(null);
  const [loading, setLoading] = useState(true);

  // Check for stored session on mount
  useEffect(() => {
    const checkSession = async () => {
      try {
        const token = await AsyncStorage.getItem('auth_token');
        if (token) {
          const username = await AsyncStorage.getItem('auth_username');
          const fullname = await AsyncStorage.getItem('auth_fullname');
          setSession({ token, username: username || undefined, fullname: fullname || undefined });
        }
      } catch (error) {
        console.error('Error checking session:', error);
      } finally {
        setLoading(false);
      }
    };

    checkSession();
  }, []);

  const signIn = async (username: string, password: string) => {
    try {
      // Use the API to login
      const response = await api.login({ username, password });
      
      console.log('Login response:', response.data);

      // Assuming response.data contains the token and maybe user info
      // Adjust based on your actual API response structure
      const { token } = response.data;
      
      if (!token) throw new Error('No token received');

      // Store auth data
      await AsyncStorage.setItem('auth_token', token);
      await AsyncStorage.setItem('auth_username', username);
      await AsyncStorage.setItem('auth_password', password); // Storing password for headers as per requirement (be careful in production!)
      
      // If API returns fullname, store it too
      if (response.data.fullname) {
        await AsyncStorage.setItem('auth_fullname', response.data.fullname);
      }

      setSession({ 
        token, 
        username,
        fullname: response.data.fullname 
      });
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  };

  const signOut = async () => {
    try {
      await AsyncStorage.removeItem('auth_token');
      await AsyncStorage.removeItem('auth_username');
      await AsyncStorage.removeItem('auth_fullname');
      await AsyncStorage.removeItem('auth_password');
      setSession(null);
    } catch (error) {
      console.error('Logout error:', error);
      throw error;
    }
  };

  return (
    <AuthContext.Provider value={{ session, loading, signIn, signOut }}>
      {children}
    </AuthContext.Provider>
  );
}
