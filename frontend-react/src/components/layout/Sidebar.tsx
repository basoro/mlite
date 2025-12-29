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
  Zap,
  ClipboardList
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface MenuItem {
  icon: React.ElementType;
  label: string;
  path: string;
}

const mainMenuItems: MenuItem[] = [
  { icon: LayoutDashboard, label: 'Dashboard', path: '/' },
  { icon: Users, label: 'Pasien', path: '/pasien' },
  { icon: Calendar, label: 'Pendaftaran', path: '/pendaftaran' },
  { icon: Stethoscope, label: 'Pemeriksaan', path: '/pemeriksaan' },
  { icon: Pill, label: 'Resep', path: '/resep' },
  { icon: Receipt, label: 'Billing', path: '/billing' },
  { icon: ClipboardList, label: 'Farmasi', path: '/farmasi' },
];

const managementMenuItems: MenuItem[] = [
  { icon: Link2, label: 'Integrasi', path: '/integrasi' },
  { icon: Package, label: 'Inventory', path: '/inventory' },
  { icon: Database, label: 'Master Data', path: '/master-data' },
  { icon: FileText, label: 'Laporan', path: '/laporan' },
  { icon: UserCog, label: 'Manajemen User', path: '/manajemen-user' },
  { icon: Settings, label: 'Pengaturan', path: '/pengaturan' },
];

interface SidebarProps {
  onClose?: () => void;
}

const Sidebar: React.FC<SidebarProps> = ({ onClose }) => {
  const location = useLocation();

  const isActive = (path: string) => {
    if (path === '/') {
      return location.pathname === '/';
    }
    return location.pathname.startsWith(path);
  };

  return (
    <aside className="w-64 lg:w-56 h-full min-h-screen bg-sidebar flex flex-col shadow-xl lg:shadow-none border-r border-sidebar-border">
      {/* Logo */}
      <div className="p-4 flex items-center justify-between lg:justify-start gap-3">
        <div className="flex items-center gap-3">
          <div className="w-9 h-9 rounded-lg bg-primary flex items-center justify-center">
            <Heart className="w-5 h-5 text-primary-foreground" />
          </div>
          <div>
            <h1 className="text-sidebar-foreground font-bold text-lg">mKLINIK</h1>
            <p className="text-sidebar-muted text-xs">Management System</p>
          </div>
        </div>
        {/* Close button for mobile */}
        {onClose && (
          <Button variant="ghost" size="icon" className="lg:hidden text-sidebar-muted" onClick={onClose}>
            <Zap className="w-5 h-5" />
          </Button>
        )}
      </div>

      {/* Main Menu */}
      <nav className="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <p className="px-4 py-2 text-xs font-medium text-sidebar-muted uppercase tracking-wider">
          Menu Utama
        </p>
        {mainMenuItems.map((item) => (
          <Link
            key={item.path}
            to={item.path}
            onClick={onClose}
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
            onClick={onClose}
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
      <div className="p-4 mx-3 mb-4 rounded-lg bg-sidebar-accent mt-auto">
        <div className="flex items-center gap-2 text-sidebar-primary">
          <Zap className="w-4 h-4" />
          <span className="text-sm font-medium">Integrasi</span>
        </div>
        <p className="text-xs text-sidebar-muted mt-1">
          BPJS & SATUSEHAT siap digunakan
        </p>
      </div>
    </aside>
  );
};

export default Sidebar;
