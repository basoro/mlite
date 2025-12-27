import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Heart, Eye, EyeOff, ArrowRight } from 'lucide-react';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/hooks/use-toast';

const Login: React.FC = () => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();
  const { toast } = useToast();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!username || !password) {
      toast({
        title: 'Error',
        description: 'Username dan password harus diisi',
        variant: 'destructive',
      });
      return;
    }

    setIsLoading(true);
    try {
      const result = await login(username, password);
      if (result.token) {
        toast({
          title: 'Berhasil',
          description: 'Selamat datang di mKLINIK',
        });
        navigate('/');
      } else {
        toast({
          title: 'Error',
          description: result.error || result.message || 'Username atau password salah',
          variant: 'destructive',
        });
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Terjadi kesalahan, silakan coba lagi',
        variant: 'destructive',
      });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex">
      {/* Left Side - Branding */}
      <div className="hidden lg:flex lg:w-1/2 bg-sidebar flex-col justify-between p-12">
        <div className="flex items-center gap-3">
          <div className="w-12 h-12 rounded-xl bg-primary flex items-center justify-center">
            <Heart className="w-7 h-7 text-primary-foreground" />
          </div>
          <div>
            <h1 className="text-sidebar-foreground font-bold text-2xl">mKLINIK</h1>
            <p className="text-sidebar-muted text-sm">Management System</p>
          </div>
        </div>

        <div className="space-y-6">
          <h2 className="text-4xl font-bold text-sidebar-foreground leading-tight">
            Sistem Informasi<br />
            Manajemen Klinik<br />
            <span className="text-primary">Terintegrasi</span>
          </h2>
          <p className="text-sidebar-muted text-lg max-w-md">
            Kelola data pasien, jadwal, pemeriksaan, resep, dan billing dalam satu platform yang mudah digunakan.
          </p>
          <div className="flex items-center gap-4 pt-4">
            <div className="flex -space-x-2">
              {[1, 2, 3, 4].map((i) => (
                <div
                  key={i}
                  className="w-10 h-10 rounded-full bg-sidebar-accent border-2 border-sidebar flex items-center justify-center"
                >
                  <span className="text-sidebar-muted text-xs font-medium">U{i}</span>
                </div>
              ))}
            </div>
            <p className="text-sidebar-muted text-sm">
              Dipercaya oleh <span className="text-sidebar-foreground font-semibold">500+</span> klinik
            </p>
          </div>
        </div>

        <p className="text-sidebar-muted text-sm">
          © 2024 mKLINIK. Hak Cipta Dilindungi.
        </p>
      </div>

      {/* Right Side - Login Form */}
      <div className="flex-1 flex items-center justify-center p-8 bg-background">
        <div className="w-full max-w-md space-y-8">
          {/* Mobile Logo */}
          <div className="lg:hidden flex items-center justify-center gap-3 mb-8">
            <div className="w-10 h-10 rounded-xl bg-primary flex items-center justify-center">
              <Heart className="w-6 h-6 text-primary-foreground" />
            </div>
            <div>
              <h1 className="text-foreground font-bold text-xl">mKLINIK</h1>
            </div>
          </div>

          <div className="text-center lg:text-left">
            <h2 className="text-2xl font-bold text-foreground">Selamat Datang</h2>
            <p className="text-muted-foreground mt-2">
              Masuk ke akun Anda untuk melanjutkan
            </p>
          </div>

          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="space-y-2">
              <Label htmlFor="username">Username</Label>
              <Input
                id="username"
                type="text"
                placeholder="Masukkan username"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                className="h-12"
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="password">Password</Label>
              <div className="relative">
                <Input
                  id="password"
                  type={showPassword ? 'text' : 'password'}
                  placeholder="Masukkan password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="h-12 pr-12"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                >
                  {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                </button>
              </div>
            </div>

            <div className="flex items-center justify-between">
              <label className="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" className="w-4 h-4 rounded border-input accent-primary" />
                <span className="text-sm text-muted-foreground">Ingat saya</span>
              </label>
              <a href="#" className="text-sm text-primary hover:underline">
                Lupa password?
              </a>
            </div>

            <Button
              type="submit"
              className="w-full h-12 text-base font-semibold"
              disabled={isLoading}
            >
              {isLoading ? (
                <span className="animate-pulse">Memproses...</span>
              ) : (
                <>
                  Masuk
                  <ArrowRight className="w-5 h-5 ml-2" />
                </>
              )}
            </Button>
          </form>

          <p className="text-center text-sm text-muted-foreground">
            Belum punya akun?{' '}
            <a href="#" className="text-primary font-medium hover:underline">
              Hubungi Admin
            </a>
          </p>
        </div>
      </div>
    </div>
  );
};

export default Login;
