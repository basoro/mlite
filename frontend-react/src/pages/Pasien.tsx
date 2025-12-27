import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Search, UserPlus, Eye, Edit, User, Loader2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { useToast } from '@/hooks/use-toast';
import { getPasienList, createPasien, getMasterList } from '@/lib/api';

interface Patient {
  id: string;
  name: string;
  nik: string;
  bpjs: string;
  phone: string;
  address: string;
  lastVisit: string;
  status: 'active' | 'inactive';
}

// Mock data removed

// Patient Card Component
interface PatientCardProps {
  patient: Patient;
  onView: (patient: Patient) => void;
  onEdit: (patient: Patient) => void;
}

const PatientCard: React.FC<PatientCardProps> = ({ patient, onView, onEdit }) => (
  <div className="patient-card animate-fade-in">
    <div className="flex items-start justify-between">
      <div className="flex items-center gap-3">
        <User className="w-5 h-5 text-muted-foreground" />
        <div className="flex items-center gap-2">
          <span className="font-semibold text-foreground">{patient.name}</span>
          <span className={`badge-status ${patient.status === 'active' ? 'badge-active' : 'badge-inactive'}`}>
            {patient.status === 'active' ? 'Aktif' : 'Tidak Aktif'}
          </span>
        </div>
      </div>
      <div className="flex items-center gap-2">
        <Button variant="ghost" size="icon" onClick={() => onView(patient)}>
          <Eye className="w-4 h-4" />
        </Button>
        <Button variant="ghost" size="icon" onClick={() => onEdit(patient)}>
          <Edit className="w-4 h-4" />
        </Button>
      </div>
    </div>

    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
      <div>
        <p className="text-sm text-muted-foreground">ID: {patient.id}</p>
        <p className="text-sm text-muted-foreground">BPJS: {patient.bpjs}</p>
        <p className="text-sm text-muted-foreground">Kunjungan Terakhir: {patient.lastVisit}</p>
      </div>
      <div>
        <p className="text-sm text-muted-foreground">NIK: {patient.nik}</p>
        <p className="text-sm text-muted-foreground">Alamat: {patient.address}</p>
      </div>
      <div>
        <p className="text-sm text-muted-foreground">Telepon: {patient.phone}</p>
      </div>
    </div>
  </div>
);

// Registration Form Component
const RegistrationForm: React.FC<{ onClose: () => void }> = ({ onClose }) => {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const [formData, setFormData] = useState({
    nm_pasien: '',
    no_ktp: '',
    jk: '',
    tgl_lahir: '',
    alamat: '',
    no_tlp: '',
    kd_pj: 'UMUM',
  });

  const { data: penjabList } = useQuery({
    queryKey: ['master', 'penjab'],
    queryFn: () => getMasterList('penjab', 1, 100),
  });

  const mutation = useMutation({
    mutationFn: createPasien,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['patients'] });
      toast({
        title: 'Berhasil',
        description: 'Data pasien berhasil disimpan',
      });
      onClose();
    },
    onError: (error: any) => {
      toast({
        title: 'Gagal',
        description: error.message || 'Terjadi kesalahan saat menyimpan data pasien',
        variant: 'destructive',
      });
    },
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    mutation.mutate(formData);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label htmlFor="nm_pasien">Nama Pasien</Label>
          <Input
            id="nm_pasien"
            value={formData.nm_pasien}
            onChange={(e) => setFormData({ ...formData, nm_pasien: e.target.value })}
            required
          />
        </div>
        <div className="space-y-2">
          <Label htmlFor="no_ktp">NIK</Label>
          <Input
            id="no_ktp"
            value={formData.no_ktp}
            onChange={(e) => setFormData({ ...formData, no_ktp: e.target.value })}
            required
          />
        </div>
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label htmlFor="jk">Jenis Kelamin</Label>
          <Select value={formData.jk} onValueChange={(value) => setFormData({ ...formData, jk: value })}>
            <SelectTrigger>
              <SelectValue placeholder="Pilih" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="L">Laki-laki</SelectItem>
              <SelectItem value="P">Perempuan</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-2">
          <Label htmlFor="tgl_lahir">Tanggal Lahir</Label>
          <Input
            id="tgl_lahir"
            type="date"
            value={formData.tgl_lahir}
            onChange={(e) => setFormData({ ...formData, tgl_lahir: e.target.value })}
            required
          />
        </div>
      </div>

      <div className="space-y-2">
        <Label htmlFor="alamat">Alamat</Label>
        <Input
          id="alamat"
          value={formData.alamat}
          onChange={(e) => setFormData({ ...formData, alamat: e.target.value })}
          required
        />
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label htmlFor="no_tlp">No. Telepon</Label>
          <Input
            id="no_tlp"
            value={formData.no_tlp}
            onChange={(e) => setFormData({ ...formData, no_tlp: e.target.value })}
            required
          />
        </div>
        <div className="space-y-2">
          <Label htmlFor="kd_pj">Penjamin</Label>
          <Select value={formData.kd_pj} onValueChange={(value) => setFormData({ ...formData, kd_pj: value })}>
            <SelectTrigger>
              <SelectValue placeholder="Pilih Penjamin" />
            </SelectTrigger>
            <SelectContent>
              {penjabList?.data?.map((pj: any) => (
                <SelectItem key={pj.kd_pj} value={pj.kd_pj}>
                  {pj.png_jawab}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </div>

      <div className="flex justify-end gap-3 pt-4">
        <Button type="button" variant="outline" onClick={onClose} disabled={mutation.isPending}>
          Batal
        </Button>
        <Button type="submit" disabled={mutation.isPending}>
          {mutation.isPending ? (
            <>
              <Loader2 className="w-4 h-4 mr-2 animate-spin" />
              Menyimpan...
            </>
          ) : (
            'Simpan'
          )}
        </Button>
      </div>
    </form>
  );
};

const Pasien: React.FC = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [activeTab, setActiveTab] = useState<'register' | 'list'>('list');
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const { toast } = useToast();

  const { data: apiResponse, isLoading, error } = useQuery({
    queryKey: ['patients', searchQuery],
    queryFn: () => getPasienList(1, 10, searchQuery),
  });

  const patients: Patient[] = React.useMemo(() => {
    if (!apiResponse) return [];
    
    // Handle different response structures
    const data = Array.isArray(apiResponse) ? apiResponse : (apiResponse.data || []);
    
    return data.map((p: any) => ({
      id: p.no_rkm_medis,
      name: p.nm_pasien,
      nik: p.no_ktp,
      bpjs: p.no_peserta || '-',
      phone: p.no_tlp,
      address: p.alamat,
      lastVisit: '-', // Not available in basic list
      status: 'active', // Default status
    }));
  }, [apiResponse]);

  const handleView = (patient: Patient) => {
    toast({
      title: 'Detail Pasien',
      description: `Melihat detail pasien ${patient.name}`,
    });
  };

  const handleEdit = (patient: Patient) => {
    toast({
      title: 'Edit Pasien',
      description: `Mengedit data pasien ${patient.name}`,
    });
  };

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div>
        <h1 className="text-3xl font-bold text-foreground">Registrasi & Manajemen Pasien</h1>
        <p className="text-muted-foreground mt-1">Kelola data pasien dan registrasi pasien baru</p>
      </div>

      {/* Tab Buttons */}
      <div className="flex gap-2">
        <Button
          variant={activeTab === 'register' ? 'outline' : 'ghost'}
          className={activeTab === 'register' ? '' : 'text-muted-foreground'}
          onClick={() => setActiveTab('register')}
        >
          <UserPlus className="w-4 h-4 mr-2" />
          Registrasi Baru
        </Button>
        <Button
          variant={activeTab === 'list' ? 'default' : 'ghost'}
          className={activeTab === 'list' ? '' : 'text-muted-foreground'}
          onClick={() => setActiveTab('list')}
        >
          <Search className="w-4 h-4 mr-2" />
          Daftar Pasien
        </Button>
      </div>

      {activeTab === 'list' ? (
        <div className="bg-card rounded-xl border border-border p-6">
          <div className="mb-6">
            <h2 className="text-xl font-bold text-foreground">Daftar Pasien</h2>
            <p className="text-sm text-muted-foreground mt-1">
              Kelola dan lihat data semua pasien yang terdaftar
            </p>
          </div>

          {/* Search Bar */}
          <div className="flex gap-3 mb-6">
            <div className="flex-1 relative">
              <Input
                placeholder="Cari berdasarkan Nomor RM, Nama, NIK, atau Nomor telepon..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pr-4"
              />
            </div>
            <Button className="gap-2">
              <Search className="w-4 h-4" />
              Cari
            </Button>
          </div>

          {/* Patient List */}
          <div className="space-y-4">
            {isLoading ? (
              <div className="flex justify-center py-8">
                <Loader2 className="w-8 h-8 animate-spin text-primary" />
              </div>
            ) : error ? (
              <div className="text-center py-8 text-destructive">
                Gagal memuat data pasien. Silakan coba lagi.
              </div>
            ) : (
              <>
                {patients.map((patient) => (
                  <PatientCard
                    key={patient.id}
                    patient={patient}
                    onView={handleView}
                    onEdit={handleEdit}
                  />
                ))}
                {patients.length === 0 && (
                  <div className="text-center py-8 text-muted-foreground">
                    Tidak ada pasien ditemukan
                  </div>
                )}
              </>
            )}
          </div>
        </div>
      ) : (
        <div className="bg-card rounded-xl border border-border p-6">
          <div className="mb-6">
            <h2 className="text-xl font-bold text-foreground">Registrasi Pasien Baru</h2>
            <p className="text-sm text-muted-foreground mt-1">
              Isi form berikut untuk mendaftarkan pasien baru
            </p>
          </div>
          <RegistrationForm onClose={() => setActiveTab('list')} />
        </div>
      )}

      {/* Registration Dialog (Alternative) */}
      <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
        <DialogTrigger asChild>
          <Button className="fixed bottom-6 right-6 shadow-lg" size="lg">
            <UserPlus className="w-5 h-5 mr-2" />
            Pasien Baru
          </Button>
        </DialogTrigger>
        <DialogContent className="sm:max-w-[600px]">
          <DialogHeader>
            <DialogTitle>Registrasi Pasien Baru</DialogTitle>
            <DialogDescription>
              Isi form berikut untuk mendaftarkan pasien baru
            </DialogDescription>
          </DialogHeader>
          <RegistrationForm onClose={() => setIsDialogOpen(false)} />
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default Pasien;
