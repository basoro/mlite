import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { getToken, clearToken, setToken, login as apiLogin, isSessionValid } from '@/lib/api';

interface User {
  username: string;
  role: string;
  clinicName: string;
}

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  login: (username: string, password: string) => Promise<boolean>;
  logout: () => void;
  isLoading: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const logout = () => {
    setUser(null);
    clearToken();
    localStorage.removeItem('user');
  };

  useEffect(() => {
    // Initial check
    const token = getToken();
    if (token) {
      if (isSessionValid()) {
        // For demo purposes, restore user from localStorage
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
          setUser(JSON.parse(storedUser));
        }
      } else {
        logout();
      }
    }
    setIsLoading(false);

    // Periodic session check
    const interval = setInterval(() => {
      if (getToken() && !isSessionValid()) {
        logout();
      }
    }, 60000); // Check every minute

    return () => clearInterval(interval);
  }, []);

  const login = async (username: string, password: string): Promise<boolean> => {
    try {
      const data = await apiLogin(username, password);
      
      if (data.token) {
        const loggedInUser: User = {
          username,
          role: 'Admin Klinik', // Default role since API doesn't return user details yet
          clinicName: 'Klinik Utama Atila Medika',
        };
        
        setUser(loggedInUser);
        localStorage.setItem('user', JSON.stringify(loggedInUser));
        return true;
      }
      return false;
    } catch (error) {
      console.error('Login failed:', error);
      return false;
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        isAuthenticated: !!user,
        login,
        logout,
        isLoading,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};
