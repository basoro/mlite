import React, { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { format } from 'date-fns';
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
  AlertCircle,
  Loader2
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Link } from 'react-router-dom';
import { getRawatJalanList, getIgdList } from '@/lib/api';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ScrollArea } from '@/components/ui/scroll-area';

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
  status: string;
}

const ScheduleItem: React.FC<ScheduleItemProps> = ({ time, patientName, type, status }) => {
  const getStatusLabel = (s: string) => {
    switch (s) {
      case 'Belum': return 'Menunggu';
      case 'Sudah': return 'Selesai';
      case 'Berkas Diterima': return 'Diperiksa';
      case 'Batal': return 'Batal';
      default: return s;
    }
  };

  const getStatusClass = (s: string) => {
    switch (s) {
      case 'Belum': return 'badge-scheduled';
      case 'Sudah': return 'badge-completed'; // Assuming you have this or default to scheduled
      case 'Berkas Diterima': return 'badge-in-progress'; // Assuming you have this
      default: return 'badge-scheduled';
    }
  };

  return (
    <div className="schedule-item animate-slide-in-left">
      <span className="text-lg font-semibold text-foreground w-24">{time}</span>
      <div className="flex-1">
        <p className="font-medium text-foreground">{patientName}</p>
        <p className="text-sm text-muted-foreground">{type}</p>
      </div>
      <span className={`badge-status ${getStatusClass(status)}`}>
        {getStatusLabel(status)}
      </span>
    </div>
  );
};

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
  const [activeTab, setActiveTab] = useState('poliklinik');
  const today = format(new Date(), 'yyyy-MM-dd');

  const { data: scheduleData, isLoading: isRalanLoading } = useQuery({
    queryKey: ['rawatJalan', today],
    queryFn: () => getRawatJalanList(today, today, 0, 100),
  });

  const { data: igdScheduleData, isLoading: isIgdLoading } = useQuery({
    queryKey: ['igd', today],
    queryFn: () => getIgdList(today, today, 0, 100),
  });

  const ralanSchedules = scheduleData?.data || [];
  const igdSchedules = igdScheduleData?.data || [];
  
  const currentSchedules = activeTab === 'poliklinik' ? ralanSchedules : igdSchedules;
  const isLoading = activeTab === 'poliklinik' ? isRalanLoading : isIgdLoading;

  // Calculate stats based on active tab
  const totalPasien = currentSchedules.length;
  const menunggu = currentSchedules.filter((s: any) => s.stts === 'Belum').length;
  const selesai = currentSchedules.filter((s: any) => s.stts === 'Sudah').length;
  // Pendapatan logic can be added later if API supports it
  
  const stats = {
    totalPasien: totalPasien,
    menunggu: menunggu,
    pemeriksaanSelesai: selesai,
    pendapatan: 'Rp -',
  };

  const quickActions: QuickActionProps[] = [
    { icon: UserPlus, label: 'Registrasi Pasien Baru', href: '/pasien' },
    { icon: CalendarPlus, label: 'Buat Jadwal Baru', href: '/pendaftaran' },
    { icon: Activity, label: 'Mulai Pemeriksaan', href: '/poliklinik' },
    { icon: FileText, label: 'Lihat Laporan', href: '/laporan' },
  ];

  if (isLoading) {
    return (
      <div className="flex h-screen items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-emerald-500" />
      </div>
    );
  }

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
          subtitle="Pasien terdaftar"
          icon={Users}
        />
        <StatCard
          title="Menunggu Pemeriksaan"
          value={stats.menunggu}
          subtitle="Pasien dalam antrian"
          icon={Calendar}
        />
        <StatCard
          title="Pemeriksaan Selesai"
          value={stats.pemeriksaanSelesai}
          subtitle="Pasien telah diperiksa"
          icon={Stethoscope}
        />
        <StatCard
          title="Pendapatan Hari Ini"
          value={stats.pendapatan}
          subtitle="Estimasi pendapatan"
          icon={TrendingUp}
          valueColor="text-primary"
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
          
          <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full mb-4">
            <TabsList className="grid w-full grid-cols-2">
              <TabsTrigger value="poliklinik">Poliklinik</TabsTrigger>
              <TabsTrigger value="igd">IGD</TabsTrigger>
            </TabsList>
          </Tabs>

          <div className="space-y-3">
            <ScrollArea className="h-[450px]">
              {currentSchedules.length > 0 ? (
                currentSchedules.map((schedule: any, index: number) => (
                  <ScheduleItem 
                    key={index} 
                    time={schedule.jam_reg}
                    patientName={schedule.nm_pasien}
                    type={schedule.nm_poli}
                    status={schedule.stts}
                  />
                ))
              ) : (
                <p className="text-muted-foreground text-center py-4">Tidak ada jadwal hari ini</p>
              )}
            </ScrollArea>
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
