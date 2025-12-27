import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { 
  LayoutDashboard, 
  Users, 
  Calendar, 
  Stethoscope, 
  Pill, 
  Receipt, 
  Link2, 
  Package, 
  Database, 
  FileText, 
  UserCog, 
  Settings,
  Heart,
  Zap
} from 'lucide-react';
import { cn } from '@/lib/utils';

interface MenuItem {
  icon: React.ElementType;
  label: string;
  path: string;
}

const mainMenuItems: MenuItem[] = [
  { icon: LayoutDashboard, label: 'Dashboard', path: '/' },
  { icon: Users, label: 'Pasien', path: '/pasien' },
  { icon: Calendar, label: 'Jadwal', path: '/jadwal' },
  { icon: Stethoscope, label: 'Pemeriksaan', path: '/pemeriksaan' },
  { icon: Pill, label: 'Resep', path: '/resep' },
  { icon: Receipt, label: 'Billing', path: '/billing' },
];

const managementMenuItems: MenuItem[] = [
  { icon: Link2, label: 'Integrasi', path: '/integrasi' },
  { icon: Package, label: 'Inventory', path: '/inventory' },
  { icon: Database, label: 'Master Data', path: '/master-data' },
  { icon: FileText, label: 'Laporan', path: '/laporan' },
  { icon: UserCog, label: 'Manajemen User', path: '/manajemen-user' },
  { icon: Settings, label: 'Pengaturan', path: '/pengaturan' },
];

const Sidebar: React.FC = () => {
  const location = useLocation();

  const isActive = (path: string) => {
    if (path === '/') {
      return location.pathname === '/';
    }
    return location.pathname.startsWith(path);
  };

  return (
    <aside className="w-56 min-h-screen bg-sidebar flex flex-col">
      {/* Logo */}
      <div className="p-4 flex items-center gap-3">
        <div className="w-9 h-9 rounded-lg bg-primary flex items-center justify-center">
          <Heart className="w-5 h-5 text-primary-foreground" />
        </div>
        <div>
          <h1 className="text-sidebar-foreground font-bold text-lg">mKLINIK</h1>
          <p className="text-sidebar-muted text-xs">Management System</p>
        </div>
      </div>

      {/* Main Menu */}
      <nav className="flex-1 px-3 py-4 space-y-1">
        <p className="px-4 py-2 text-xs font-medium text-sidebar-muted uppercase tracking-wider">
          Menu Utama
        </p>
        {mainMenuItems.map((item) => (
          <Link
            key={item.path}
            to={item.path}
            className={cn(
              'sidebar-item',
              isActive(item.path) && 'sidebar-item-active'
            )}
          >
            <item.icon className="w-5 h-5" />
            <span className="text-sm font-medium">{item.label}</span>
          </Link>
        ))}

        <p className="px-4 py-2 mt-6 text-xs font-medium text-sidebar-muted uppercase tracking-wider">
          Manajemen
        </p>
        {managementMenuItems.map((item) => (
          <Link
            key={item.path}
            to={item.path}
            className={cn(
              'sidebar-item',
              isActive(item.path) && 'sidebar-item-active'
            )}
          >
            <item.icon className="w-5 h-5" />
            <span className="text-sm font-medium">{item.label}</span>
          </Link>
        ))}
      </nav>

      {/* Integration Status */}
      <div className="p-4 mx-3 mb-4 rounded-lg bg-sidebar-accent">
        <div className="flex items-center gap-2 text-sidebar-primary">
          <Zap className="w-4 h-4" />
          <span className="text-sm font-medium">Integrasi</span>
        </div>
        <p className="text-xs text-sidebar-muted mt-1">
          pCare BPJS & SATUSEHAT siap digunakan
        </p>
      </div>
    </aside>
  );
};

export default Sidebar;
