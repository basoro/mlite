import React from 'react';
import { Calendar, Plus, Clock } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface ScheduleItem {
  id: string;
  time: string;
  patientName: string;
  patientId: string;
  type: string;
  doctor: string;
  status: 'scheduled' | 'in-progress' | 'completed' | 'cancelled';
}

const mockSchedule: ScheduleItem[] = [
  {
    id: '1',
    time: '08:00',
    patientName: 'Budi Santoso',
    patientId: '000001',
    type: 'Pemeriksaan Umum',
    doctor: 'Dr. Ahmad',
    status: 'scheduled',
  },
  {
    id: '2',
    time: '09:00',
    patientName: 'Siti Aminah',
    patientId: '000002',
    type: 'Pemeriksaan Gigi',
    doctor: 'Dr. Dewi',
    status: 'scheduled',
  },
  {
    id: '3',
    time: '10:00',
    patientName: 'Ahmad Rahman',
    patientId: '000003',
    type: 'Konsultasi',
    doctor: 'Dr. Ahmad',
    status: 'completed',
  },
];

const statusColors = {
  scheduled: 'bg-primary/10 text-primary border-primary/20',
  'in-progress': 'bg-warning/10 text-warning border-warning/20',
  completed: 'bg-success/10 text-success border-success/20',
  cancelled: 'bg-destructive/10 text-destructive border-destructive/20',
};

const statusLabels = {
  scheduled: 'Dijadwalkan',
  'in-progress': 'Berlangsung',
  completed: 'Selesai',
  cancelled: 'Dibatalkan',
};

const Jadwal: React.FC = () => {
  const today = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex items-start justify-between">
        <div>
          <h1 className="text-3xl font-bold text-foreground">Jadwal</h1>
          <p className="text-muted-foreground mt-1">Kelola jadwal pemeriksaan dan konsultasi</p>
        </div>
        <Button className="gap-2">
          <Plus className="w-4 h-4" />
          Buat Jadwal Baru
        </Button>
      </div>

      {/* Today Info */}
      <div className="bg-card rounded-xl border border-border p-6">
        <div className="flex items-center gap-3">
          <div className="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
            <Calendar className="w-6 h-6 text-primary" />
          </div>
          <div>
            <p className="text-sm text-muted-foreground">Hari Ini</p>
            <p className="text-lg font-semibold text-foreground">{today}</p>
          </div>
        </div>
      </div>

      {/* Schedule List */}
      <div className="bg-card rounded-xl border border-border">
        <div className="p-6 border-b border-border">
          <h2 className="text-xl font-bold text-foreground">Jadwal Hari Ini</h2>
          <p className="text-sm text-muted-foreground mt-1">
            {mockSchedule.length} jadwal tercatat
          </p>
        </div>

        <div className="divide-y divide-border">
          {mockSchedule.map((item) => (
            <div key={item.id} className="p-6 hover:bg-accent/50 transition-colors">
              <div className="flex items-start justify-between">
                <div className="flex items-start gap-4">
                  <div className="flex items-center gap-2 w-20">
                    <Clock className="w-4 h-4 text-muted-foreground" />
                    <span className="font-semibold text-foreground">{item.time}</span>
                  </div>
                  <div>
                    <p className="font-semibold text-foreground">{item.patientName}</p>
                    <p className="text-sm text-muted-foreground">ID: {item.patientId}</p>
                    <p className="text-sm text-muted-foreground mt-1">{item.type}</p>
                    <p className="text-sm text-muted-foreground">{item.doctor}</p>
                  </div>
                </div>
                <span className={`px-3 py-1 rounded-full text-xs font-medium border ${statusColors[item.status]}`}>
                  {statusLabels[item.status]}
                </span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default Jadwal;
