import React from 'react';
import { 
  Users, 
  Calendar, 
  Stethoscope, 
  TrendingUp, 
  Clock, 
  UserPlus, 
  CalendarPlus, 
  Activity, 
  FileText,
  AlertTriangle,
  AlertCircle
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Link } from 'react-router-dom';

// Stat Card Component
interface StatCardProps {
  title: string;
  value: string | number;
  subtitle: string;
  icon: React.ElementType;
  trend?: {
    value: string;
    positive?: boolean;
  };
  valueColor?: string;
}

const StatCard: React.FC<StatCardProps> = ({ title, value, subtitle, icon: Icon, trend, valueColor }) => (
  <div className="stat-card animate-fade-in">
    <div className="flex items-start justify-between">
      <span className="text-sm text-muted-foreground font-medium">{title}</span>
      <Icon className="w-5 h-5 text-muted-foreground" />
    </div>
    <div className="mt-3">
      <span className={`text-3xl font-bold ${valueColor || 'text-foreground'}`}>{value}</span>
    </div>
    <div className="mt-1 flex items-center gap-2">
      <span className="text-sm text-muted-foreground">{subtitle}</span>
      {trend && (
        <span className={`text-xs font-medium ${trend.positive ? 'text-primary' : 'text-destructive'}`}>
          {trend.value}
        </span>
      )}
    </div>
  </div>
);

// Schedule Item Component
interface ScheduleItemProps {
  time: string;
  patientName: string;
  type: string;
  status: 'scheduled' | 'in-progress' | 'completed';
}

const ScheduleItem: React.FC<ScheduleItemProps> = ({ time, patientName, type, status }) => (
  <div className="schedule-item animate-slide-in-left">
    <span className="text-lg font-semibold text-foreground w-24">{time}</span>
    <div className="flex-1">
      <p className="font-medium text-foreground">{patientName}</p>
      <p className="text-sm text-muted-foreground">{type}</p>
    </div>
    <span className="badge-status badge-scheduled">
      {status === 'scheduled' ? 'Dijadwalkan' : status === 'in-progress' ? 'Berlangsung' : 'Selesai'}
    </span>
  </div>
);

// Quick Action Component
interface QuickActionProps {
  icon: React.ElementType;
  label: string;
  href: string;
}

const QuickAction: React.FC<QuickActionProps> = ({ icon: Icon, label, href }) => (
  <Link to={href} className="quick-action">
    <Icon className="w-5 h-5 text-muted-foreground" />
    <span className="text-sm font-medium text-foreground">{label}</span>
  </Link>
);

// Alert Card Component
interface AlertCardProps {
  title: string;
  items: string[];
  type: 'warning' | 'danger';
}

const AlertCard: React.FC<AlertCardProps> = ({ title, items, type }) => (
  <div className={`alert-card ${type === 'warning' ? 'alert-card-warning' : 'alert-card-danger'}`}>
    <div className="flex items-center gap-2 mb-2">
      {type === 'warning' ? (
        <AlertTriangle className="w-4 h-4 text-warning" />
      ) : (
        <AlertCircle className="w-4 h-4 text-destructive" />
      )}
      <span className={`font-semibold text-sm ${type === 'warning' ? 'text-warning' : 'text-destructive'}`}>
        {title}
      </span>
    </div>
    <ul className="space-y-1">
      {items.map((item, index) => (
        <li key={index} className={`text-sm ${type === 'warning' ? 'text-warning' : 'text-destructive'}`}>
          • {item}
        </li>
      ))}
    </ul>
  </div>
);

const Dashboard: React.FC = () => {
  // Mock data - in production, this would come from API
  const stats = {
    totalPasien: 2,
    jadwalHariIni: 2,
    pemeriksaanSelesai: 0,
    pendapatan: 'Rp 0',
  };

  const todaySchedule: ScheduleItemProps[] = [
    { time: '08:00:00', patientName: 'Budi Santoso', type: 'Pemeriksaan Umum', status: 'scheduled' },
    { time: '09:00:00', patientName: 'Siti Aminah', type: 'Pemeriksaan Umum', status: 'scheduled' },
  ];

  const quickActions: QuickActionProps[] = [
    { icon: UserPlus, label: 'Registrasi Pasien Baru', href: '/pasien' },
    { icon: CalendarPlus, label: 'Buat Jadwal Baru', href: '/jadwal' },
    { icon: Activity, label: 'Mulai Pemeriksaan', href: '/pemeriksaan' },
    { icon: FileText, label: 'Lihat Laporan', href: '/laporan' },
  ];

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex items-start justify-between">
        <div>
          <h1 className="text-3xl font-bold text-foreground">Dashboard</h1>
          <p className="text-muted-foreground mt-1">Selamat datang di Sistem Manajemen Klinik</p>
        </div>
        <Link to="/pasien">
          <Button className="gap-2">
            <UserPlus className="w-4 h-4" />
            Data Pasien
          </Button>
        </Link>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          title="Total Pasien Hari Ini"
          value={stats.totalPasien}
          subtitle="+2 dari kemarin"
          icon={Users}
        />
        <StatCard
          title="Jadwal Hari Ini"
          value={stats.jadwalHariIni}
          subtitle="3 menunggu pemeriksaan"
          icon={Calendar}
        />
        <StatCard
          title="Pemeriksaan Selesai"
          value={stats.pemeriksaanSelesai}
          subtitle="0/2 pasien"
          icon={Stethoscope}
        />
        <StatCard
          title="Pendapatan Hari Ini"
          value={stats.pendapatan}
          subtitle=""
          icon={TrendingUp}
          valueColor="text-primary"
          trend={{ value: '+15% dari rata-rata', positive: true }}
        />
      </div>

      {/* Main Content Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {/* Today's Schedule */}
        <div className="lg:col-span-3 bg-card rounded-xl border border-border p-6">
          <div className="flex items-center gap-2 mb-2">
            <Clock className="w-5 h-5 text-foreground" />
            <h2 className="text-xl font-bold text-foreground">Jadwal Hari Ini</h2>
          </div>
          <p className="text-sm text-muted-foreground mb-4">
            Daftar pasien yang dijadwalkan hari ini
          </p>
          <div className="space-y-3">
            {todaySchedule.map((schedule, index) => (
              <ScheduleItem key={index} {...schedule} />
            ))}
          </div>
        </div>

        {/* Right Column */}
        <div className="lg:col-span-2 space-y-6">
          {/* Quick Actions */}
          <div className="bg-card rounded-xl border border-border p-6">
            <div className="flex items-center gap-2 mb-4">
              <FileText className="w-5 h-5 text-foreground" />
              <h2 className="text-xl font-bold text-foreground">Aksi Cepat</h2>
            </div>
            <div className="divide-y divide-border">
              {quickActions.map((action, index) => (
                <QuickAction key={index} {...action} />
              ))}
            </div>
          </div>

          {/* Reminders */}
          <div className="bg-card rounded-xl border border-border p-6">
            <div className="flex items-center gap-2 mb-4">
              <AlertCircle className="w-5 h-5 text-destructive" />
              <h2 className="text-xl font-bold text-foreground">Pengingat</h2>
            </div>
            <div className="space-y-3">
              <AlertCard
                title="Stok Obat Menipis"
                items={['Paramex — 10 / min 50']}
                type="danger"
              />
              <AlertCard
                title="Obat Hampir Kadaluarsa (< 30 hari)"
                items={[
                  'Paracetamol 500mg — kadaluarsa 1/1/2026 (sisa 5 hari)',
                  'Paramex — kadaluarsa 1/1/2026 (sisa 5 hari)',
                ]}
                type="warning"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
