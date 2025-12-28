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
import { getPasienList, createPasien, updatePasien, getPasienDetail, getMasterList } from '@/lib/api';

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

// Patient Form Component (Create & Edit)
interface PatientFormProps {
  onClose: () => void;
  initialData?: any;
  mode: 'create' | 'edit';
}

const PatientForm: React.FC<PatientFormProps> = ({ onClose, initialData, mode }) => {
  const { toast } = useToast();
  const queryClient = useQueryClient();
  const [formData, setFormData] = useState({
    nm_pasien: initialData?.nm_pasien || '',
    no_ktp: initialData?.no_ktp || '',
    jk: initialData?.jk || '',
    tgl_lahir: initialData?.tgl_lahir || '',
    alamat: initialData?.alamat || '',
    no_tlp: initialData?.no_tlp || '',
    kd_pj: initialData?.kd_pj || 'UMUM',
  });

  const { data: penjabList } = useQuery({
    queryKey: ['master', 'penjab'],
    queryFn: () => getMasterList('penjab', 1, 100),
  });

  const mutation = useMutation({
    mutationFn: (data: any) => {
      if (mode === 'edit') {
        return updatePasien(initialData.no_rkm_medis, data);
      }
      return createPasien(data);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['patients'] });
      toast({
        title: 'Berhasil',
        description: `Data pasien berhasil ${mode === 'edit' ? 'diperbarui' : 'disimpan'}`,
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

// Patient Detail View Component
const PatientDetailView: React.FC<{ patientId: string; onClose: () => void }> = ({ patientId, onClose }) => {
  const { data: patient, isLoading } = useQuery({
    queryKey: ['patient', patientId],
    queryFn: () => getPasienDetail(patientId),
  });

  if (isLoading) {
    return <div className="flex justify-center p-8"><Loader2 className="w-8 h-8 animate-spin" /></div>;
  }

  const p = patient?.data || patient; // Handle response wrapper

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-2 gap-6">
        <div>
          <h3 className="font-semibold text-muted-foreground mb-1">Informasi Pribadi</h3>
          <div className="space-y-2">
            <div className="flex justify-between border-b py-2">
              <span className="text-sm text-muted-foreground">No. RM</span>
              <span className="font-medium">{p?.no_rkm_medis}</span>
            </div>
            <div className="flex justify-between border-b py-2">
              <span className="text-sm text-muted-foreground">Nama</span>
              <span className="font-medium">{p?.nm_pasien}</span>
            </div>
            <div className="flex justify-between border-b py-2">
              <span className="text-sm text-muted-foreground">NIK</span>
              <span className="font-medium">{p?.no_ktp}</span>
            </div>
            <div className="flex justify-between border-b py-2">
              <span className="text-sm text-muted-foreground">Jenis Kelamin</span>
              <span className="font-medium">{p?.jk === 'L' ? 'Laki-laki' : 'Perempuan'}</span>
            </div>
            <div className="flex justify-between border-b py-2">
              <span className="text-sm text-muted-foreground">Tanggal Lahir</span>
              <span className="font-medium">{p?.tgl_lahir}</span>
            </div>
          </div>
        </div>
        <div>
          <h3 className="font-semibold text-muted-foreground mb-1">Kontak & Alamat</h3>
          <div className="space-y-2">
            <div className="flex justify-between border-b py-2">
              <span className="text-sm text-muted-foreground">No. Telepon</span>
              <span className="font-medium">{p?.no_tlp}</span>
            </div>
            <div className="flex justify-between border-b py-2">
              <span className="text-sm text-muted-foreground">Alamat</span>
              <span className="font-medium text-right max-w-[200px]">{p?.alamat}</span>
            </div>
             <div className="flex justify-between border-b py-2">
              <span className="text-sm text-muted-foreground">Penjamin</span>
              <span className="font-medium">{p?.kd_pj}</span>
            </div>
             <div className="flex justify-between border-b py-2">
              <span className="text-sm text-muted-foreground">BPJS</span>
              <span className="font-medium">{p?.no_peserta || '-'}</span>
            </div>
          </div>
        </div>
      </div>
      <div className="flex justify-end pt-4">
        <Button onClick={onClose}>Tutup</Button>
      </div>
    </div>
  );
};

const Pasien: React.FC = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [isViewDialogOpen, setIsViewDialogOpen] = useState(false);
  const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
  const [selectedPatientId, setSelectedPatientId] = useState<string | null>(null);
  const [selectedPatientData, setSelectedPatientData] = useState<any>(null);

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
    setSelectedPatientId(patient.id);
    setIsViewDialogOpen(true);
  };

  const handleEdit = async (patient: Patient) => {
    try {
      // Fetch full detail for editing
      const detail = await getPasienDetail(patient.id);
      setSelectedPatientData(detail.data || detail);
      setIsEditDialogOpen(true);
    } catch (e) {
      toast({
        title: 'Error',
        description: 'Gagal mengambil data pasien',
        variant: 'destructive',
      });
    }
  };

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div>
        <h1 className="text-3xl font-bold text-foreground">Registrasi & Manajemen Pasien</h1>
        <p className="text-muted-foreground mt-1">Kelola data pasien dan registrasi pasien baru</p>
      </div>

      <div className="bg-card rounded-xl border border-border p-6">
        <div className="mb-6 flex justify-between items-center">
          <div>
            <h2 className="text-xl font-bold text-foreground">Daftar Pasien</h2>
            <p className="text-sm text-muted-foreground mt-1">
              Kelola dan lihat data semua pasien yang terdaftar
            </p>
          </div>
          <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
            <DialogTrigger asChild>
              <Button>
                <UserPlus className="w-4 h-4 mr-2" />
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
              <PatientForm mode="create" onClose={() => setIsDialogOpen(false)} />
            </DialogContent>
          </Dialog>
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

      {/* View Dialog */}
      <Dialog open={isViewDialogOpen} onOpenChange={setIsViewDialogOpen}>
        <DialogContent className="sm:max-w-[600px]">
          <DialogHeader>
            <DialogTitle>Detail Pasien</DialogTitle>
          </DialogHeader>
          {selectedPatientId && (
            <PatientDetailView 
              patientId={selectedPatientId} 
              onClose={() => setIsViewDialogOpen(false)} 
            />
          )}
        </DialogContent>
      </Dialog>

      {/* Edit Dialog */}
      <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
        <DialogContent className="sm:max-w-[600px]">
          <DialogHeader>
            <DialogTitle>Edit Data Pasien</DialogTitle>
            <DialogDescription>
              Perbarui informasi data pasien
            </DialogDescription>
          </DialogHeader>
          {selectedPatientData && (
            <PatientForm 
              mode="edit" 
              initialData={selectedPatientData} 
              onClose={() => setIsEditDialogOpen(false)} 
            />
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default Pasien;
