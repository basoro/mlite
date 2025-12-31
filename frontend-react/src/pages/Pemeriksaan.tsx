import React, { useState, useEffect } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Clock, User, Calendar, Stethoscope, FileText, Pill, Smile, Loader2, Save, Trash, Edit, AlertTriangle, Check, ChevronsUpDown, TestTube, Scan, Code, File, Files, ArrowRight, Scissors, MoreHorizontal, ArrowUp, ArrowDown, X, Copy } from 'lucide-react';
import { format } from 'date-fns';
import { cn } from '@/lib/utils';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DialogFooter,
} from "@/components/ui/dialog"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from "@/components/ui/command"
import { Calendar as CalendarComponent } from '@/components/ui/calendar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { useToast } from '@/hooks/use-toast';
import { getRawatJalanList, getRiwayatPerawatan, saveSOAP, deleteSOAP, getMasterList, saveTindakan, getRawatJalanTindakan, deleteTindakan, saveDiagnosa, saveProsedur, saveCatatan, saveBerkas, saveResume, saveRujukanInternal, saveLaporanOperasi, getRawatJalanSoap, getRawatJalanResep, deleteRawatJalanResep } from '@/lib/api';

// Queue Item Component
interface QueueItemProps {
  patient: any;
  isSelected: boolean;
  onClick: () => void;
}

const QueueItem: React.FC<QueueItemProps> = ({ patient, isSelected, onClick }) => (
  <div
    onClick={onClick}
    className={`p-4 rounded-xl border cursor-pointer transition-all ${
      isSelected
        ? 'border-emerald-500 bg-emerald-50'
        : 'border-border hover:border-emerald-200 hover:bg-emerald-50/50'
    }`}
  >
    <div className="flex items-start justify-between mb-2">
      <span className="text-lg font-bold text-foreground">{patient.jam_reg}</span>
      <div className="flex items-center gap-2">
        <span className="text-xs text-muted-foreground">{patient.nm_poli}</span>
        <span className={`px-2 py-0.5 rounded-full text-[10px] font-medium ${
            patient.stts === 'Sudah' ? 'bg-green-100 text-green-700' :
            patient.stts === 'Berkas Diterima' ? 'bg-blue-100 text-blue-700' :
            'bg-gray-100 text-gray-700'
        }`}>
            {patient.stts}
        </span>
      </div>
    </div>
    <p className="font-medium text-foreground">{patient.nm_pasien}</p>
    <p className="text-sm text-muted-foreground">{patient.no_rkm_medis}</p>
  </div>
);

// History Item Component
const HistoryItem: React.FC<{ history: any; onEdit?: (history: any, soap: any) => void; onDelete?: (history: any, soap: any) => void; onCopy?: (soap: any) => void }> = ({ history, onEdit, onDelete, onCopy }) => {
    // Combine both Ralan and Ranap SOAP data
    const soaps = [
        ...(history.pemeriksaan_ranap || []), 
        ...(history.pemeriksaan_ralan || [])
    ].sort((a: any, b: any) => {
        const dateA = new Date(`${a.tgl_perawatan} ${a.jam_rawat}`);
        const dateB = new Date(`${b.tgl_perawatan} ${b.jam_rawat}`);
        return dateB.getTime() - dateA.getTime();
    });

    return (
        <div className="p-4 border border-border rounded-xl bg-white hover:shadow-sm transition-shadow">
            <div className="flex items-start justify-between mb-3">
                <div className="flex items-center gap-2">
                    <Calendar className="w-4 h-4 text-emerald-500" />
                    <span className="font-medium text-foreground">{history.tgl_registrasi} {history.jam_reg}</span>
                    <span className={`px-2 py-0.5 rounded-full text-[10px] font-medium ${
                        history.status_lanjut === 'Ralan' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700'
                    }`}>
                        {history.status_lanjut}
                    </span>
                </div>
                <div className="flex items-center gap-2">
                    <span className="text-sm text-muted-foreground">{history.no_rawat}</span>
                </div>
            </div>
            
            {soaps.length > 0 ? (
                <div className="space-y-4">
                    {soaps.map((soap: any, index: number) => (
                        <div key={index} className="border-t pt-4 first:border-t-0 first:pt-0">
                            <div className="flex justify-between items-center mb-2">
                                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                    <Clock className="w-3 h-3" />
                                    <span>{soap.tgl_perawatan} {soap.jam_rawat}</span>
                                    {soap.nip && <span>• Petugas: {soap.nip}</span>}
                                </div>
                                <div className="flex items-center gap-1">
                                    {onCopy && (
                                        <Button variant="ghost" size="icon" className="h-6 w-6 text-blue-600 hover:text-blue-700 hover:bg-blue-50" onClick={() => onCopy(soap)} title="Salin Data">
                                            <Copy className="h-3 w-3" />
                                        </Button>
                                    )}
                                    {onEdit && (
                                        <Button variant="ghost" size="icon" className="h-6 w-6" onClick={() => onEdit(history, soap)}>
                                            <Edit className="h-3 w-3" />
                                        </Button>
                                    )}
                                    {onDelete && (
                                        <Button variant="ghost" size="icon" className="h-6 w-6 text-destructive hover:text-destructive" onClick={() => onDelete(history, soap)}>
                                            <Trash className="h-3 w-3" />
                                        </Button>
                                    )}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div className="md:col-span-2">
                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3 p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p className="text-xs text-muted-foreground">Tensi</p>
                                            <p className="font-medium text-foreground">{soap.tensi || '-'}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground">Nadi</p>
                                            <p className="font-medium text-foreground">{soap.nadi || '-'}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground">Suhu</p>
                                            <p className="font-medium text-foreground">{soap.suhu_tubuh || '-'}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground">Respirasi</p>
                                            <p className="font-medium text-foreground">{soap.respirasi || '-'}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground">SpO2</p>
                                            <p className="font-medium text-foreground">{soap.spo2 || '-'} %</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground">Kesadaran</p>
                                            <p className="font-medium text-foreground">{soap.kesadaran || '-'}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-muted-foreground">GCS</p>
                                            <p className="font-medium text-foreground">{soap.gcs || '-'}</p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <p className="font-semibold text-emerald-700 mb-1">Subjektif (Keluhan)</p>
                                    <p className="text-gray-600 whitespace-pre-wrap">{soap.keluhan || '-'}</p>
                                </div>
                                <div>
                                    <p className="font-semibold text-emerald-700 mb-1">Objektif (Pemeriksaan)</p>
                                    <p className="text-gray-600 whitespace-pre-wrap">{soap.pemeriksaan || '-'}</p>
                                </div>
                                <div>
                                    <p className="font-semibold text-emerald-700 mb-1">Asesmen (Penilaian)</p>
                                    <p className="text-gray-600 whitespace-pre-wrap">{soap.penilaian || soap.diagnosa || '-'}</p>
                                </div>
                                <div>
                                    <p className="font-semibold text-emerald-700 mb-1">Plan (Rencana)</p>
                                    <p className="text-gray-600 whitespace-pre-wrap">{soap.rtl || soap.tindakan || '-'}</p>
                                </div>
                                {(soap.instruksi || soap.evaluasi) && (
                                    <div className="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 pt-2 border-t">
                                        {soap.instruksi && (
                                            <div>
                                                <p className="font-semibold text-gray-700 mb-1">Instruksi</p>
                                                <p className="text-gray-600 whitespace-pre-wrap">{soap.instruksi}</p>
                                            </div>
                                        )}
                                        {soap.evaluasi && (
                                            <div>
                                                <p className="font-semibold text-gray-700 mb-1">Evaluasi</p>
                                                <p className="text-gray-600 whitespace-pre-wrap">{soap.evaluasi}</p>
                                            </div>
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="text-center py-4 text-muted-foreground text-sm italic">
                    Belum ada data pemeriksaan (SOAP)
                </div>
            )}

            {/* Tindakan Section */}
            {(history.rawat_jl_dr?.length > 0 || history.rawat_jl_pr?.length > 0 || history.rawat_jl_drpr?.length > 0) && (
                <div className="mt-4 pt-4 border-t">
                    <h4 className="font-semibold text-sm mb-2 text-emerald-700">Riwayat Tindakan</h4>
                    <div className="space-y-2 text-sm">
                        {history.rawat_jl_dr?.map((item: any, idx: number) => (
                            <div key={`dr-${idx}`} className="p-2 bg-gray-50 rounded border border-gray-100">
                                <p className="font-medium">{item.nm_perawatan}</p>
                                <p className="text-xs text-muted-foreground">
                                    Dokter: {item.nm_dokter} • {item.tgl_perawatan} {item.jam_rawat}
                                </p>
                            </div>
                        ))}
                        {history.rawat_jl_pr?.map((item: any, idx: number) => (
                            <div key={`pr-${idx}`} className="p-2 bg-gray-50 rounded border border-gray-100">
                                <p className="font-medium">{item.nm_perawatan}</p>
                                <p className="text-xs text-muted-foreground">
                                    Petugas: {item.nama} • {item.tgl_perawatan} {item.jam_rawat}
                                </p>
                            </div>
                        ))}
                        {history.rawat_jl_drpr?.map((item: any, idx: number) => (
                            <div key={`drpr-${idx}`} className="p-2 bg-gray-50 rounded border border-gray-100">
                                <p className="font-medium">{item.nm_perawatan}</p>
                                <p className="text-xs text-muted-foreground">
                                    Dokter: {item.nm_dokter} & Petugas: {item.nama} • {item.tgl_perawatan} {item.jam_rawat}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Pemberian Obat Section */}
            {history.pemberian_obat?.length > 0 && (
                <div className="mt-4 pt-4 border-t">
                    <h4 className="font-semibold text-sm mb-2 text-emerald-700">Riwayat Obat</h4>
                    <div className="space-y-2 text-sm">
                        {history.pemberian_obat.map((pemberian: any, idx: number) => (
                            <div key={`obat-group-${idx}`}>
                                {pemberian.data_pemberian_obat?.map((item: any, subIdx: number) => (
                                    <div key={`obat-item-${idx}-${subIdx}`} className="p-2 bg-gray-50 rounded border border-gray-100 mb-1">
                                        <p className="font-medium">{item.nama_brng}</p>
                                        <p className="text-xs text-muted-foreground">
                                            Jumlah: {item.jml} • Biaya: {item.biaya_obat} • {pemberian.tgl_perawatan} {pemberian.jam}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Diagnosa Section */}
            {history.diagnosa_pasien?.length > 0 && (
                <div className="mt-4 pt-4 border-t">
                    <h4 className="font-semibold text-sm mb-2 text-emerald-700">Riwayat Diagnosa (ICD-10)</h4>
                    <div className="space-y-2 text-sm">
                        {history.diagnosa_pasien.map((item: any, idx: number) => (
                            <div key={`diagnosa-${idx}`} className="p-2 bg-gray-50 rounded border border-gray-100">
                                <div className="flex justify-between items-start">
                                    <div>
                                        <p className="font-medium"><span className="font-mono bg-emerald-100 text-emerald-800 px-1 rounded mr-1">{item.kd_penyakit}</span> {item.nm_penyakit}</p>
                                        <p className="text-xs text-muted-foreground">
                                            Status: {item.status} • Prioritas: {item.prioritas} • {item.status_penyakit}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Prosedur Section */}
            {history.prosedur_pasien?.length > 0 && (
                <div className="mt-4 pt-4 border-t">
                    <h4 className="font-semibold text-sm mb-2 text-emerald-700">Riwayat Prosedur (ICD-9)</h4>
                    <div className="space-y-2 text-sm">
                        {history.prosedur_pasien.map((item: any, idx: number) => (
                            <div key={`prosedur-${idx}`} className="p-2 bg-gray-50 rounded border border-gray-100">
                                <div className="flex justify-between items-start">
                                    <div>
                                        <p className="font-medium"><span className="font-mono bg-blue-100 text-blue-800 px-1 rounded mr-1">{item.kode}</span> {item.deskripsi_panjang || item.deskripsi_pendek}</p>
                                        <p className="text-xs text-muted-foreground">
                                            Status: {item.status} • Prioritas: {item.prioritas}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

const Pemeriksaan: React.FC = () => {
  const [selectedPatient, setSelectedPatient] = useState<any | null>(null);
  const [dateFrom, setDateFrom] = useState<Date | undefined>(new Date());
  const [dateTo, setDateTo] = useState<Date | undefined>(new Date());
  const { toast } = useToast();
  const queryClient = useQueryClient();

  // Fetch Rawat Jalan Tindakan
  const { data: rawatJalanTindakan, refetch: refetchRawatJalanTindakan } = useQuery({
    queryKey: ['rawatJalanTindakan', selectedPatient?.no_rawat],
    queryFn: () => getRawatJalanTindakan(selectedPatient.no_rawat),
    enabled: !!selectedPatient?.no_rawat
  });

  // Fetch Rawat Jalan SOAP
  const { data: rawatJalanSoap, refetch: refetchRawatJalanSoap } = useQuery({
    queryKey: ['rawatJalanSoap', selectedPatient?.no_rawat],
    queryFn: () => getRawatJalanSoap(selectedPatient.no_rawat),
    enabled: !!selectedPatient?.no_rawat
  });

  const deleteTindakanMutation = useMutation({
    mutationFn: (data: any) => deleteTindakan(data),
    onSuccess: () => {
      toast({ title: 'Berhasil', description: 'Tindakan berhasil dihapus' });
      refetchRawatJalanTindakan();
    },
    onError: (error: any) => {
      toast({ title: 'Gagal', description: error.message || 'Gagal menghapus tindakan', variant: 'destructive' });
    }
  });

  const handleDeleteTindakan = (tindakan: any, providerType: string) => {
     const payload = {
         kat: 'tindakan',
         no_rawat: selectedPatient.no_rawat,
         kd_jenis_prw: tindakan.kd_jenis_prw,
         provider: providerType,
         tgl_perawatan: tindakan.tgl_perawatan,
         jam_rawat: tindakan.jam_rawat
     };
     deleteTindakanMutation.mutate(payload);
  };

  // Fetch Rawat Jalan Resep
  const { data: rawatJalanResep, refetch: refetchRawatJalanResep } = useQuery({
    queryKey: ['rawatJalanResep', selectedPatient?.no_rawat],
    queryFn: () => getRawatJalanResep(selectedPatient.no_rawat),
    enabled: !!selectedPatient?.no_rawat
  });

  const deleteResepMutation = useMutation({
    mutationFn: (data: any) => deleteRawatJalanResep(data),
    onSuccess: () => {
      toast({ title: 'Berhasil', description: 'Resep berhasil dihapus' });
      refetchRawatJalanResep();
    },
    onError: (error: any) => {
      toast({ title: 'Gagal', description: error.message || 'Gagal menghapus resep', variant: 'destructive' });
    }
  });

  const handleDeleteResep = (resep: any) => {
     const payload = {
         no_rawat: selectedPatient.no_rawat,
         kode_brng: resep.kode_brng,
         tgl_peresepan: resep.tgl_peresepan,
         jam_peresepan: resep.jam_peresepan
     };
     deleteResepMutation.mutate(payload);
  };

  const [isDateFromOpen, setIsDateFromOpen] = useState(false);
  const [isDateToOpen, setIsDateToOpen] = useState(false);
  
  // Edit mode state
  const [isEditMode, setIsEditMode] = useState(false);
  const [editHistoryData, setEditHistoryData] = useState<any | null>(null);
  const [activeTab, setActiveTab] = useState('riwayat');
  
  // Delete Dialog State
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [itemToDelete, setItemToDelete] = useState<{history: any, soap: any} | null>(null);

  const [openObat, setOpenObat] = useState(false);
  const [openRacikanObat, setOpenRacikanObat] = useState(false);

  // More Menu & Modals State
  const [isMoreMenuOpen, setIsMoreMenuOpen] = useState(false);
  const [isICDModalOpen, setIsICDModalOpen] = useState(false);
  const [isCatatanModalOpen, setIsCatatanModalOpen] = useState(false);
  const [isBerkasModalOpen, setIsBerkasModalOpen] = useState(false);
  const [isResumeModalOpen, setIsResumeModalOpen] = useState(false);
  const [isRujukanModalOpen, setIsRujukanModalOpen] = useState(false);
  const [isOperasiModalOpen, setIsOperasiModalOpen] = useState(false);

  const formattedDateFrom = dateFrom ? format(dateFrom, 'yyyy-MM-dd') : '';
  const formattedDateTo = dateTo ? format(dateTo, 'yyyy-MM-dd') : '';

  // SOAP Form State
  const [soapData, setSoapData] = useState({
    suhu_tubuh: '',
    tensi: '',
    nadi: '',
    respirasi: '',
    tinggi: '',
    berat: '',
    gcs: '',
    kesadaran: 'Compos Mentis',
    spo2: '',
    keluhan: '',
    pemeriksaan: '',
    alergi: '',
    lingkar_perut: '',
    rtl: '',
    penilaian: '',
    instruksi: '',
    evaluasi: '',
    nip: '', 
  });

  // Tindakan Form State
  const [tindakanData, setTindakanData] = useState({
    kd_jenis_prw: '',
    provider: 'rawat_jl_dr',
    kode_provider: '',
    kode_provider2: '',
    tgl_perawatan: format(new Date(), 'yyyy-MM-dd'),
    jam_rawat: format(new Date(), 'HH:mm:ss')
  });

  // Resep Form State
  const [obatData, setObatData] = useState({
    kode_brng: '',
    nama_brng: '',
    jml: '',
    aturan_pakai: '',
    search: ''
  });

  const [racikanData, setRacikanData] = useState({
    nama_racik: '',
    kd_jenis_racik: '',
    jml: '',
    aturan_pakai: '',
    keterangan: '',
    items: [] as { kode_brng: string; nama_brng: string; kandungan: string }[],
    search_obat: ''
  });

  const [racikanItem, setRacikanItem] = useState({
    kode_brng: '',
    nama_brng: '',
    kandungan: ''
  });

  // ICD State
  const [icd10List, setIcd10List] = useState<any[]>([]);
  const [icd10Search, setIcd10Search] = useState('');
  const [openIcd10Search, setOpenIcd10Search] = useState(false);
  
  const [icd9List, setIcd9List] = useState<any[]>([]);
  const [icd9Search, setIcd9Search] = useState('');
  const [openIcd9Search, setOpenIcd9Search] = useState(false);

  // Catatan Pasien State
  const [catatanPasien, setCatatanPasien] = useState('');

  // Berkas Digital State
  const [berkasJudul, setBerkasJudul] = useState('');
  const [berkasDeskripsi, setBerkasDeskripsi] = useState('');
  
  // Resume Medis State
  const [resumeData, setResumeData] = useState({
    diagnosa_utama: '',
    diagnosa_sekunder: '',
    jalannya_penyakit: '',
    terapi: '',
    kondisi_pulang: '',
  });

  // Rujukan Internal State
  const [rujukanData, setRujukanData] = useState({
    kd_poli: '',
    kd_dokter: '',
    catatan: '',
  });

  // Laporan Operasi State
  const [operasiData, setOperasiData] = useState({
    laporan: '',
    operator: '',
    tanggal: format(new Date(), 'yyyy-MM-dd'),
    jam: format(new Date(), 'HH:mm:ss'),
  });

  // Fetch Obat Data
  const { data: obatListData } = useQuery({
    queryKey: ['master', 'gudangbarang', obatData.search],
    queryFn: () => getMasterList('gudangbarang', 1, 50, obatData.search),
    enabled: true // Always enabled, but depends on search
  });

  // Fetch Racikan Obat Data (Separate search)
  const { data: racikanObatListData } = useQuery({
    queryKey: ['master', 'gudangbarang', racikanData.search_obat],
    queryFn: () => getMasterList('gudangbarang', 1, 50, racikanData.search_obat),
  });

  // Fetch Metode Racik
  const { data: metodeRacikData } = useQuery({
    queryKey: ['master', 'metode_racik'],
    queryFn: () => getMasterList('metode_racik', 1, 100),
  });

  const handleSaveObat = () => {
    if (!selectedPatient || !obatData.kode_brng) return;

    const payload = {
        kat: 'obat',
        no_rawat: selectedPatient.no_rawat,
        tgl_perawatan: format(new Date(), 'yyyy-MM-dd'),
        jam_rawat: format(new Date(), 'HH:mm:ss'),
        kd_jenis_prw: obatData.kode_brng, // Map kode_brng to kd_jenis_prw for consistency with backend
        jml: obatData.jml,
        aturan_pakai: obatData.aturan_pakai,
        kode_provider: localStorage.getItem('auth_username') || '',
    };

    saveTindakanMutation.mutate(payload);
  };

  const handleAddRacikanItem = () => {
    if (racikanItem.kode_brng && racikanItem.kandungan) {
        setRacikanData(prev => ({
            ...prev,
            items: [...prev.items, { ...racikanItem }]
        }));
        setRacikanItem({ kode_brng: '', nama_brng: '', kandungan: '' });
    }
  };

  const handleRemoveRacikanItem = (index: number) => {
    setRacikanData(prev => ({
        ...prev,
        items: prev.items.filter((_, i) => i !== index)
    }));
  };

  const handleSaveRacikan = () => {
    if (!selectedPatient || !racikanData.nama_racik || racikanData.items.length === 0) return;

    const payload = {
        kat: 'racikan',
        no_rawat: selectedPatient.no_rawat,
        tgl_perawatan: format(new Date(), 'yyyy-MM-dd'),
        jam_rawat: format(new Date(), 'HH:mm:ss'),
        nama_racik: racikanData.nama_racik,
        kd_jenis_prw: racikanData.kd_jenis_racik, // Map method to kd_jenis_prw
        jml: racikanData.jml,
        aturan_pakai: racikanData.aturan_pakai,
        keterangan: racikanData.keterangan,
        kode_provider: localStorage.getItem('auth_username') || '',
        kode_brng: racikanData.items.map(item => ({ value: item.kode_brng })),
        kandungan: racikanData.items.map(item => ({ value: item.kandungan }))
    };

    saveTindakanMutation.mutate(payload);
  };

  // Fetch Master Data
  const { data: jnsPerawatanData } = useQuery({
    queryKey: ['master', 'jns_perawatan'],
    queryFn: () => getMasterList('jns_perawatan', 1, 1000),
  });

  const { data: dokterData } = useQuery({
    queryKey: ['master', 'dokter'],
    queryFn: () => getMasterList('dokter', 1, 1000),
  });

  const { data: petugasData } = useQuery({
    queryKey: ['master', 'petugas'],
    queryFn: () => getMasterList('petugas', 1, 1000),
  });

  // Fetch ICD Data
  const { data: icd10Master } = useQuery({
    queryKey: ['master', 'penyakit', icd10Search],
    queryFn: () => getMasterList('penyakit', 1, 50, icd10Search),
  });

  const { data: icd9Master } = useQuery({
    queryKey: ['master', 'icd9', icd9Search],
    queryFn: () => getMasterList('icd9', 1, 50, icd9Search),
  });

  const { data: poliData } = useQuery({
    queryKey: ['master', 'poliklinik'],
    queryFn: () => getMasterList('poliklinik', 1, 100),
  });

  const saveDiagnosaMutation = useMutation({
    mutationFn: saveDiagnosa,
    onSuccess: () => {
        // toast({ title: 'Berhasil', description: 'Diagnosa berhasil disimpan' });
    }
  });

  const saveProsedurMutation = useMutation({
    mutationFn: saveProsedur,
    onSuccess: () => {
        // toast({ title: 'Berhasil', description: 'Prosedur berhasil disimpan' });
    }
  });

  const saveCatatanMutation = useMutation({
    mutationFn: saveCatatan,
    onSuccess: () => {
        toast({ title: 'Berhasil', description: 'Catatan pasien berhasil disimpan' });
        setIsCatatanModalOpen(false);
        setCatatanPasien('');
    },
    onError: (e: any) => toast({ title: 'Gagal', description: e.message, variant: 'destructive' })
  });

  const saveBerkasMutation = useMutation({
    mutationFn: saveBerkas,
    onSuccess: () => {
        toast({ title: 'Berhasil', description: 'Berkas berhasil disimpan' });
        setIsBerkasModalOpen(false);
        setBerkasJudul('');
        setBerkasDeskripsi('');
    },
    onError: (e: any) => toast({ title: 'Gagal', description: e.message, variant: 'destructive' })
  });

  const saveResumeMutation = useMutation({
    mutationFn: saveResume,
    onSuccess: () => {
        toast({ title: 'Berhasil', description: 'Resume medis berhasil disimpan' });
        setIsResumeModalOpen(false);
        setResumeData({ diagnosa_utama: '', diagnosa_sekunder: '', jalannya_penyakit: '', terapi: '', kondisi_pulang: '' });
    },
    onError: (e: any) => toast({ title: 'Gagal', description: e.message, variant: 'destructive' })
  });

  const saveRujukanMutation = useMutation({
    mutationFn: saveRujukanInternal,
    onSuccess: () => {
        toast({ title: 'Berhasil', description: 'Rujukan internal berhasil disimpan' });
        setIsRujukanModalOpen(false);
        setRujukanData({ kd_poli: '', kd_dokter: '', catatan: '' });
    },
    onError: (e: any) => toast({ title: 'Gagal', description: e.message, variant: 'destructive' })
  });

  const saveOperasiMutation = useMutation({
    mutationFn: saveLaporanOperasi,
    onSuccess: () => {
        toast({ title: 'Berhasil', description: 'Laporan operasi berhasil disimpan' });
        setIsOperasiModalOpen(false);
        setOperasiData({ laporan: '', operator: '', tanggal: format(new Date(), 'yyyy-MM-dd'), jam: format(new Date(), 'HH:mm:ss') });
    },
    onError: (e: any) => toast({ title: 'Gagal', description: e.message, variant: 'destructive' })
  });

  const handleAddIcd10 = (item: any) => {
    if (!icd10List.find(i => i.kd_penyakit === item.kd_penyakit)) {
        setIcd10List([...icd10List, item]);
    }
    setOpenIcd10Search(false);
  };

  const handleAddIcd9 = (item: any) => {
    if (!icd9List.find(i => i.kode === item.kode)) {
        setIcd9List([...icd9List, item]);
    }
    setOpenIcd9Search(false);
  };

  const handleRemoveIcd10 = (index: number) => {
    setIcd10List(icd10List.filter((_, i) => i !== index));
  };

  const handleRemoveIcd9 = (index: number) => {
    setIcd9List(icd9List.filter((_, i) => i !== index));
  };

  const moveIcd10 = (index: number, direction: 'up' | 'down') => {
    if ((direction === 'up' && index === 0) || (direction === 'down' && index === icd10List.length - 1)) return;
    const newList = [...icd10List];
    const targetIndex = direction === 'up' ? index - 1 : index + 1;
    [newList[index], newList[targetIndex]] = [newList[targetIndex], newList[index]];
    setIcd10List(newList);
  };

  const moveIcd9 = (index: number, direction: 'up' | 'down') => {
    if ((direction === 'up' && index === 0) || (direction === 'down' && index === icd9List.length - 1)) return;
    const newList = [...icd9List];
    const targetIndex = direction === 'up' ? index - 1 : index + 1;
    [newList[index], newList[targetIndex]] = [newList[targetIndex], newList[index]];
    setIcd9List(newList);
  };

  const handleSaveICD = async () => {
      if (!selectedPatient) return;
      
      try {
          // Save ICD 10
          for (let i = 0; i < icd10List.length; i++) {
              await saveDiagnosaMutation.mutateAsync({
                  no_rawat: selectedPatient.no_rawat,
                  kd_penyakit: icd10List[i].kd_penyakit,
                  status: 'Ralan', 
                  prioritas: i + 1,
                  status_penyakit: 'Baru' 
              });
          }

          // Save ICD 9
          for (let i = 0; i < icd9List.length; i++) {
              await saveProsedurMutation.mutateAsync({
                  no_rawat: selectedPatient.no_rawat,
                  kode: icd9List[i].kode,
                  status: 'Ralan',
                  prioritas: i + 1
              });
          }
          
          toast({ title: 'Berhasil', description: 'Data ICD berhasil disimpan' });
          setIsICDModalOpen(false);
          setIcd10List([]);
          setIcd9List([]);
      } catch (e: any) {
          toast({ title: 'Gagal', description: e.message || 'Gagal menyimpan data ICD', variant: 'destructive' });
      }
  };

  const handleSaveCatatan = () => {
      if (!selectedPatient) return;
      saveCatatanMutation.mutate({
          no_rawat: selectedPatient.no_rawat,
          catatan: catatanPasien
      });
  };

  const handleSaveBerkas = () => {
      if (!selectedPatient) return;
      saveBerkasMutation.mutate({
          no_rawat: selectedPatient.no_rawat,
          judul: berkasJudul,
          deskripsi: berkasDeskripsi
      });
  };

  const handleSaveResume = () => {
      if (!selectedPatient) return;
      saveResumeMutation.mutate({
          no_rawat: selectedPatient.no_rawat,
          ...resumeData
      });
  };

  const handleSaveRujukan = () => {
      if (!selectedPatient) return;
      saveRujukanMutation.mutate({
          no_rawat: selectedPatient.no_rawat,
          ...rujukanData
      });
  };

  const handleSaveOperasi = () => {
      if (!selectedPatient) return;
      saveOperasiMutation.mutate({
          no_rawat: selectedPatient.no_rawat,
          ...operasiData
      });
  };

  const saveTindakanMutation = useMutation({
    mutationFn: (data: any) => saveTindakan(data),
    onSuccess: () => {
      toast({ title: 'Berhasil', description: 'Tindakan berhasil disimpan' });
      queryClient.invalidateQueries({ queryKey: ['riwayatPerawatan'] });
      queryClient.invalidateQueries({ queryKey: ['rawatJalanTindakan'] });
      queryClient.invalidateQueries({ queryKey: ['rawatJalanResep'] });
      // Reset form (optional, maybe keep provider/date)
      setTindakanData(prev => ({
        ...prev,
        kd_jenis_prw: '',
      }));
    },
    onError: (error: any) => {
      toast({ title: 'Gagal', description: error.message || 'Gagal menyimpan tindakan', variant: 'destructive' });
    }
  });

  // Reset forms on success (hook into existing mutation)
  useEffect(() => {
    if (saveTindakanMutation.isSuccess) {
        setObatData(prev => ({ ...prev, kode_brng: '', jml: '', aturan_pakai: '' }));
        setRacikanData({
            nama_racik: '',
            kd_jenis_racik: '',
            jml: '',
            aturan_pakai: '',
            keterangan: '',
            items: [],
            search_obat: ''
        });
    }
  }, [saveTindakanMutation.isSuccess]);

  const handleTindakanSubmit = () => {
    if (!selectedPatient) return;
    
    // Get logged in user as default provider if not selected
    const currentUser = localStorage.getItem('auth_username') || '';

    const payload = {
        kat: 'tindakan',
        ...tindakanData,
        no_rawat: selectedPatient.no_rawat,
        // Fallback for provider codes if empty
        kode_provider: tindakanData.kode_provider || currentUser,
        // Ensure kode_provider2 is sent if relevant
        kode_provider2: tindakanData.provider === 'rawat_jl_drpr' ? tindakanData.kode_provider2 : '',
    };
    
    saveTindakanMutation.mutate(payload);
  };

  const handleTindakanChange = (field: string, value: string) => {
    setTindakanData(prev => {
        // Reset provider codes when switching provider type
        if (field === 'provider') {
            return { 
                ...prev, 
                [field]: value,
                kode_provider: '',
                kode_provider2: ''
            };
        }
        return { ...prev, [field]: value };
    });
  };

  // Fetch Queue (Rawat Jalan)
  const { data: queueData, isLoading: isQueueLoading } = useQuery({
    queryKey: ['rawatJalan', formattedDateFrom, formattedDateTo],
    queryFn: () => getRawatJalanList(formattedDateFrom, formattedDateTo, 0, 100),
    enabled: !!dateFrom && !!dateTo,
  });

  // Fetch Patient History
  const { data: historyData, isLoading: isHistoryLoading } = useQuery({
    queryKey: ['riwayatPerawatan', selectedPatient?.no_rkm_medis],
    queryFn: () => getRiwayatPerawatan(selectedPatient.no_rkm_medis),
    enabled: !!selectedPatient?.no_rkm_medis,
  });

  const patients = queueData?.data || [];
  
  // Reset form when patient changes
  useEffect(() => {
    if (selectedPatient) {
        resetSoapForm();
        setIsEditMode(false);
        setEditHistoryData(null);
    }
  }, [selectedPatient]);

  // Populate form with latest SOAP data
  useEffect(() => {
    if (rawatJalanSoap?.data?.length > 0 && !isEditMode) {
      const sortedSoap = [...rawatJalanSoap.data].sort((a: any, b: any) => {
          const dateA = new Date(`${a.tgl_perawatan} ${a.jam_rawat}`);
          const dateB = new Date(`${b.tgl_perawatan} ${b.jam_rawat}`);
          return dateB.getTime() - dateA.getTime();
      });
      const latestSoap = sortedSoap[0];
      setSoapData(prev => ({
        ...prev,
        suhu_tubuh: latestSoap.suhu_tubuh || '',
        tensi: latestSoap.tensi || '',
        nadi: latestSoap.nadi || '',
        respirasi: latestSoap.respirasi || '',
        tinggi: latestSoap.tinggi || '',
        berat: latestSoap.berat || '',
        gcs: latestSoap.gcs || '',
        kesadaran: latestSoap.kesadaran || 'Compos Mentis',
        spo2: latestSoap.spo2 || '',
        keluhan: latestSoap.keluhan || '',
        pemeriksaan: latestSoap.pemeriksaan || '',
        alergi: latestSoap.alergi || '',
        lingkar_perut: latestSoap.lingkar_perut || '',
        rtl: latestSoap.rtl || '',
        penilaian: latestSoap.penilaian || '',
        instruksi: latestSoap.instruksi || '',
        evaluasi: latestSoap.evaluasi || '',
        nip: latestSoap.nip || ''
      }));
    }
  }, [rawatJalanSoap, isEditMode]);

  const resetSoapForm = () => {
    setSoapData({
        suhu_tubuh: '',
        tensi: '',
        nadi: '',
        respirasi: '',
        tinggi: '',
        berat: '',
        gcs: '',
        kesadaran: 'Compos Mentis',
        spo2: '',
        keluhan: '',
        pemeriksaan: '',
        alergi: '',
        lingkar_perut: '',
        rtl: '',
        penilaian: '',
        instruksi: '',
        evaluasi: '',
        nip: '-',
    });
  };

  const saveSoapMutation = useMutation({
    mutationFn: (data: any) => saveSOAP(data),
    onSuccess: () => {
      toast({ title: 'Berhasil', description: `Data pemeriksaan berhasil ${isEditMode ? 'diperbarui' : 'disimpan'}` });
      queryClient.invalidateQueries({ queryKey: ['riwayatPerawatan'] });
      queryClient.invalidateQueries({ queryKey: ['rawatJalanSoap'] });
      if (isEditMode) {
        setIsEditMode(false);
        setEditHistoryData(null);
        resetSoapForm();
      }
    },
    onError: (error: any) => {
      toast({ title: 'Gagal', description: error.message || 'Gagal menyimpan data', variant: 'destructive' });
    },
  });

  const deleteSoapMutation = useMutation({
    mutationFn: (data: any) => deleteSOAP(data),
    onSuccess: () => {
        toast({ title: 'Berhasil', description: 'Data pemeriksaan berhasil dihapus' });
        queryClient.invalidateQueries({ queryKey: ['riwayatPerawatan'] });
    },
    onError: (error: any) => {
        toast({ title: 'Gagal', description: error.message || 'Gagal menghapus data', variant: 'destructive' });
    }
  });

  const handleSoapSubmit = () => {
    if (!selectedPatient) return;

    // If edit mode, use the historical data dates, otherwise use current date
    const tglPerawatan = isEditMode && editHistoryData ? (editHistoryData.tgl_perawatan || editHistoryData.tgl_registrasi) : format(new Date(), 'yyyy-MM-dd');
    const jamRawat = isEditMode && editHistoryData ? (editHistoryData.jam_rawat || editHistoryData.jam_reg) : format(new Date(), 'HH:mm:ss');
    const noRawat = isEditMode && editHistoryData ? editHistoryData.no_rawat : selectedPatient.no_rawat;
    
    // Get NIP from config/localStorage or fallback
    const currentUserNip = localStorage.getItem('auth_username') || import.meta.env.VITE_API_USERNAME || '-';

    const payload = {
        ...soapData,
        no_rawat: noRawat,
        tgl_perawatan: tglPerawatan,
        jam_rawat: jamRawat,
        // Ensure NIP is sent if available
        nip: soapData.nip && soapData.nip !== '-' ? soapData.nip : currentUserNip, 
    };

    saveSoapMutation.mutate(payload);
  };

  const handleEditHistory = (history: any, soap: any) => {
      // Switch to SOAP tab
      setActiveTab('pemeriksaan');

      setIsEditMode(true);
      // Store soap data but keep history context for registration info if needed
      setEditHistoryData({ ...history, ...soap }); 
      
      // Populate form with history data
      // Note: mapping might need adjustment based on exact API response fields
      setSoapData({
          suhu_tubuh: soap.suhu_tubuh || '',
          tensi: soap.tensi || '',
          nadi: soap.nadi || '',
          respirasi: soap.respirasi || '',
          tinggi: soap.tinggi || '',
          berat: soap.berat || '',
          gcs: soap.gcs || '',
          kesadaran: soap.kesadaran || 'Compos Mentis',
          spo2: soap.spo2 || '',
          keluhan: soap.keluhan || '',
          pemeriksaan: soap.pemeriksaan || '',
          alergi: soap.alergi || '',
          lingkar_perut: soap.lingkar_perut || '',
          rtl: soap.rtl || '',
          penilaian: soap.penilaian || '', // Assessment often maps to penilaian/diagnosa
          instruksi: soap.instruksi || '',
          evaluasi: soap.evaluasi || '',
          nip: soap.nip || '-',
      });
  };

  const handleDeleteHistory = (history: any, soap: any) => {
      setItemToDelete({ history, soap });
      setDeleteDialogOpen(true);
  };

  const confirmDelete = () => {
      if (itemToDelete) {
          deleteSoapMutation.mutate({
              no_rawat: itemToDelete.soap.no_rawat || itemToDelete.history.no_rawat,
              tgl_perawatan: itemToDelete.soap.tgl_perawatan,
              jam_rawat: itemToDelete.soap.jam_rawat
          });
          setDeleteDialogOpen(false);
          setItemToDelete(null);
      }
  };

  const handleInputChange = (field: string, value: string) => {
    setSoapData(prev => ({ ...prev, [field]: value }));
  };

  const handleCancelEdit = () => {
      setIsEditMode(false);
      setEditHistoryData(null);
      resetSoapForm();
  };

  const handleCopySoap = (soap: any) => {
    setActiveTab('pemeriksaan');
    setSoapData({
        suhu_tubuh: soap.suhu_tubuh || '',
        tensi: soap.tensi || '',
        nadi: soap.nadi || '',
        respirasi: soap.respirasi || '',
        tinggi: soap.tinggi || '',
        berat: soap.berat || '',
        gcs: soap.gcs || '',
        kesadaran: soap.kesadaran || 'Compos Mentis',
        spo2: soap.spo2 || '',
        keluhan: soap.keluhan || '',
        pemeriksaan: soap.pemeriksaan || '',
        alergi: soap.alergi || '',
        lingkar_perut: soap.lingkar_perut || '',
        rtl: soap.rtl || '',
        penilaian: soap.penilaian || '',
        instruksi: soap.instruksi || '',
        evaluasi: soap.evaluasi || '',
        nip: soap.nip || '-',
    });
    setIsEditMode(false);
    setEditHistoryData(null);
    toast({ title: 'Berhasil', description: 'Data SOAP disalin ke form input' });
  };

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div>
        <h1 className="text-3xl font-bold text-foreground">Pemeriksaan & Diagnosa</h1>
        <p className="text-muted-foreground mt-1">Lakukan pemeriksaan pasien dan input hasil diagnosa</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-6 gap-6">
        {/* Left Column - Patient Queue */}
        <div className="lg:col-span-2 space-y-4">
          <div className="bg-card rounded-xl border border-border p-6">
            <div className="flex items-center gap-2 mb-2">
              <Clock className="w-5 h-5 text-foreground" />
              <h2 className="text-xl font-bold text-foreground">Antrian Pasien</h2>
            </div>
            <p className="text-sm text-muted-foreground mb-4">
              Pasien yang menunggu pemeriksaan
            </p>

            {/* Date Filter */}
            <div className="bg-muted/50 rounded-lg p-4 mb-4">
              <p className="text-sm font-medium text-foreground mb-3">Filter Periode</p>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <Label className="text-xs text-muted-foreground">Dari Tanggal</Label>
                  <div className="relative mt-1">
                    <Popover open={isDateFromOpen} onOpenChange={setIsDateFromOpen}>
                      <PopoverTrigger asChild>
                        <Button
                          variant={"outline"}
                          className={cn(
                            "w-full justify-start text-left font-normal text-sm",
                            !dateFrom && "text-muted-foreground"
                          )}
                        >
                          <Calendar className="mr-2 h-4 w-4" />
                          {dateFrom ? format(dateFrom, "dd MMM yyyy") : <span>Pilih Tanggal</span>}
                        </Button>
                      </PopoverTrigger>
                      <PopoverContent className="w-auto p-0" align="start">
                        <CalendarComponent
                          mode="single"
                          selected={dateFrom}
                          onSelect={(date) => {
                            setDateFrom(date);
                            setIsDateFromOpen(false);
                          }}
                          initialFocus
                        />
                      </PopoverContent>
                    </Popover>
                  </div>
                </div>
                <div>
                  <Label className="text-xs text-muted-foreground">Sampai Tanggal</Label>
                  <div className="relative mt-1">
                    <Popover open={isDateToOpen} onOpenChange={setIsDateToOpen}>
                      <PopoverTrigger asChild>
                        <Button
                          variant={"outline"}
                          className={cn(
                            "w-full justify-start text-left font-normal text-sm",
                            !dateTo && "text-muted-foreground"
                          )}
                        >
                          <Calendar className="mr-2 h-4 w-4" />
                          {dateTo ? format(dateTo, "dd MMM yyyy") : <span>Pilih Tanggal</span>}
                        </Button>
                      </PopoverTrigger>
                      <PopoverContent className="w-auto p-0" align="start">
                        <CalendarComponent
                          mode="single"
                          selected={dateTo}
                          onSelect={(date) => {
                            setDateTo(date);
                            setIsDateToOpen(false);
                          }}
                          initialFocus
                        />
                      </PopoverContent>
                    </Popover>
                  </div>
                </div>
              </div>
            </div>

            {/* Queue List */}
            <div className="space-y-3 max-h-[600px] overflow-y-auto">
              {isQueueLoading ? (
                 <div className="flex justify-center py-4">
                    <Loader2 className="w-6 h-6 animate-spin text-emerald-500" />
                 </div>
              ) : patients.length > 0 ? (
                patients.map((patient: any) => (
                  <QueueItem
                    key={patient.no_rawat}
                    patient={patient}
                    isSelected={selectedPatient?.no_rawat === patient.no_rawat}
                    onClick={() => setSelectedPatient(patient)}
                  />
                ))
              ) : (
                <div className="text-center py-8 text-muted-foreground">
                    Tidak ada antrian
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Right Column - Patient Info & History */}
        <div className="lg:col-span-4 space-y-4">
          {selectedPatient ? (
            <>
              {/* Patient Info Card */}
              <div className="bg-card rounded-xl border border-border p-6">
                <div className="flex items-center gap-2 mb-4">
                  <User className="w-5 h-5 text-foreground" />
                  <h2 className="text-xl font-bold text-foreground">Informasi Pasien</h2>
                </div>

                <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
                  <div>
                    <p className="text-sm text-muted-foreground">Nama</p>
                    <p className="font-semibold text-foreground">{selectedPatient.nm_pasien}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">No. RM</p>
                    <p className="font-semibold text-foreground">{selectedPatient.no_rkm_medis}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">No. Rawat</p>
                    <p className="font-semibold text-foreground text-xs">{selectedPatient.no_rawat}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">Poliklinik</p>
                    <p className="font-semibold text-foreground">{selectedPatient.nm_poli}</p>
                  </div>
                </div>
              </div>

              {/* Tabs */}
              <div className="bg-card rounded-xl border border-border p-6">
                <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                  <TabsList className="flex w-full overflow-x-auto justify-start h-auto p-1">
                    <TabsTrigger value="riwayat" className="gap-2 min-w-fit flex-shrink-0">
                      <FileText className="w-4 h-4" />
                      Riwayat
                    </TabsTrigger>
                    <TabsTrigger value="pemeriksaan" className="gap-2 min-w-fit flex-shrink-0">
                      <Stethoscope className="w-4 h-4" />
                      SOAP
                    </TabsTrigger>
                    <TabsTrigger value="tindakan" className="gap-2 min-w-fit flex-shrink-0">
                      <Calendar className="w-4 h-4" />
                      Tindakan
                    </TabsTrigger>
                    <TabsTrigger value="resep" className="gap-2 min-w-fit flex-shrink-0">
                      <Pill className="w-4 h-4" />
                      Resep
                    </TabsTrigger>
                    <TabsTrigger value="laboratorium" className="gap-2 min-w-fit flex-shrink-0">
                      <TestTube className="w-4 h-4" />
                      Laboratorium
                    </TabsTrigger>
                    <TabsTrigger value="radiologi" className="gap-2 min-w-fit flex-shrink-0">
                      <Scan className="w-4 h-4" />
                      Radiologi
                    </TabsTrigger>
                    <Dialog open={isMoreMenuOpen} onOpenChange={setIsMoreMenuOpen}>
                      <DialogTrigger asChild>
                        <Button variant="ghost" className="gap-2 min-w-fit flex-shrink-0 data-[state=open]:bg-muted">
                          <MoreHorizontal className="w-4 h-4" />
                          More
                        </Button>
                      </DialogTrigger>
                      <DialogContent className="sm:max-w-[500px]">
                        <DialogHeader>
                          <DialogTitle>Menu Lainnya</DialogTitle>
                        </DialogHeader>
                        <div className="grid grid-cols-2 gap-4 py-2">
                          <Button 
                            variant="outline" 
                            className="h-24 flex flex-col items-center justify-center gap-2 hover:border-blue-500 hover:bg-blue-50 transition-all"
                            onClick={() => { setIsMoreMenuOpen(false); setIsICDModalOpen(true); }}
                          >
                            <div className="p-2 bg-blue-100 text-blue-600 rounded-full">
                              <Code className="w-6 h-6" />
                            </div>
                            <span className="font-medium text-xs">ICD Management</span>
                          </Button>
                          <Button 
                            variant="outline" 
                            className="h-24 flex flex-col items-center justify-center gap-2 hover:border-yellow-500 hover:bg-yellow-50 transition-all"
                            onClick={() => { setIsMoreMenuOpen(false); setIsCatatanModalOpen(true); }}
                          >
                            <div className="p-2 bg-yellow-100 text-yellow-600 rounded-full">
                              <File className="w-6 h-6" />
                            </div>
                            <span className="font-medium text-xs">Catatan Pasien</span>
                          </Button>
                          <Button 
                            variant="outline" 
                            className="h-24 flex flex-col items-center justify-center gap-2 hover:border-purple-500 hover:bg-purple-50 transition-all"
                            onClick={() => { setIsMoreMenuOpen(false); setIsBerkasModalOpen(true); }}
                          >
                            <div className="p-2 bg-purple-100 text-purple-600 rounded-full">
                              <Files className="w-6 h-6" />
                            </div>
                            <span className="font-medium text-xs">Berkas Digital</span>
                          </Button>
                          <Button 
                            variant="outline" 
                            className="h-24 flex flex-col items-center justify-center gap-2 hover:border-indigo-500 hover:bg-indigo-50 transition-all"
                            onClick={() => { setIsMoreMenuOpen(false); setIsResumeModalOpen(true); }}
                          >
                            <div className="p-2 bg-indigo-100 text-indigo-600 rounded-full">
                              <FileText className="w-6 h-6" />
                            </div>
                            <span className="font-medium text-xs">Resume Medis</span>
                          </Button>
                          <Button 
                            variant="outline" 
                            className="h-24 flex flex-col items-center justify-center gap-2 hover:border-orange-500 hover:bg-orange-50 transition-all"
                            onClick={() => { setIsMoreMenuOpen(false); setIsRujukanModalOpen(true); }}
                          >
                            <div className="p-2 bg-orange-100 text-orange-600 rounded-full">
                              <ArrowRight className="w-6 h-6" />
                            </div>
                            <span className="font-medium text-xs">Rujukan Internal</span>
                          </Button>
                          <Button 
                            variant="outline" 
                            className="h-24 flex flex-col items-center justify-center gap-2 hover:border-red-500 hover:bg-red-50 transition-all"
                            onClick={() => { setIsMoreMenuOpen(false); setIsOperasiModalOpen(true); }}
                          >
                            <div className="p-2 bg-red-100 text-red-600 rounded-full">
                              <Scissors className="w-6 h-6" />
                            </div>
                            <span className="font-medium text-xs">Laporan Operasi</span>
                          </Button>
                        </div>
                      </DialogContent>
                    </Dialog>
                  </TabsList>

                  <TabsContent value="riwayat" className="mt-6">
                    <div className="mb-4">
                      <h3 className="text-lg font-bold text-foreground">Riwayat Pemeriksaan</h3>
                      <p className="text-sm text-muted-foreground">
                        Riwayat kunjungan sebelumnya
                      </p>
                    </div>
                    
                    <div className="space-y-3 max-h-[500px] overflow-y-auto">
                        {isHistoryLoading ? (
                            <div className="flex justify-center py-4">
                                <Loader2 className="w-6 h-6 animate-spin text-emerald-500" />
                            </div>
                        ) : historyData?.data?.reg_periksa && historyData.data.reg_periksa.length > 0 ? (
                            historyData.data.reg_periksa.map((history: any, index: number) => (
                                <HistoryItem 
                                    key={index} 
                                    history={history} 
                                    onEdit={handleEditHistory}
                                    onDelete={handleDeleteHistory}
                                    onCopy={handleCopySoap}
                                />
                            ))
                        ) : (
                            <div className="text-center py-8 text-muted-foreground">
                                Belum ada riwayat pemeriksaan
                            </div>
                        )}
                    </div>
                  </TabsContent>

                  <TabsContent value="pemeriksaan" className="mt-6">
                    <div className="space-y-4">
                        <div className="flex justify-between items-center">
                        <h3 className="text-lg font-bold text-foreground">
                          {isEditMode ? 'Edit Pemeriksaan (SOAP)' : 'Input Pemeriksaan (SOAP)'}
                        </h3>
                        <div className="flex gap-2">
                          {isEditMode && (
                              <Button 
                                  variant="outline"
                                  onClick={handleCancelEdit}
                              >
                                  Batal Edit
                              </Button>
                          )}
                          <Button 
                              onClick={handleSoapSubmit} 
                              disabled={saveSoapMutation.isPending}
                              className="bg-emerald-600 hover:bg-emerald-700"
                          >
                              {saveSoapMutation.isPending ? (
                                  <>
                                      <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                      Menyimpan...
                                  </>
                              ) : (
                                  <>
                                      <Save className="mr-2 h-4 w-4" />
                                      {isEditMode ? 'Update Pemeriksaan' : 'Simpan Pemeriksaan'}
                                  </>
                              )}
                          </Button>
                        </div>
                      </div>
                      
                      <div className="grid grid-cols-1 md:grid-cols-1 gap-6">
                          {/* Subjective & Objective */}
                          <div className="space-y-4">
                              <h4 className="font-semibold text-emerald-600 border-b pb-1">Tanda Vital & Fisik</h4>
                              <div className="grid grid-cols-4 gap-4">
                                <div className="space-y-2">
                                  <Label>Tensi (mmHg)</Label>
                                  <Input placeholder="120/80" value={soapData.tensi} onChange={(e) => handleInputChange('tensi', e.target.value)} />
                                </div>
                                <div className="space-y-2">
                                  <Label>Nadi (/menit)</Label>
                                  <Input placeholder="80" value={soapData.nadi} onChange={(e) => handleInputChange('nadi', e.target.value)} />
                                </div>
                                <div className="space-y-2">
                                  <Label>Suhu (°C)</Label>
                                  <Input placeholder="36.5" value={soapData.suhu_tubuh} onChange={(e) => handleInputChange('suhu_tubuh', e.target.value)} />
                                </div>
                                <div className="space-y-2">
                                  <Label>Respirasi (/menit)</Label>
                                  <Input placeholder="20" value={soapData.respirasi} onChange={(e) => handleInputChange('respirasi', e.target.value)} />
                                </div>
                                <div className="space-y-2">
                                  <Label>Berat (kg)</Label>
                                  <Input placeholder="60" value={soapData.berat} onChange={(e) => handleInputChange('berat', e.target.value)} />
                                </div>
                                <div className="space-y-2">
                                  <Label>Tinggi (cm)</Label>
                                  <Input placeholder="170" value={soapData.tinggi} onChange={(e) => handleInputChange('tinggi', e.target.value)} />
                                </div>
                                <div className="space-y-2">
                                  <Label>GCS</Label>
                                  <Input placeholder="E4V5M6" value={soapData.gcs} onChange={(e) => handleInputChange('gcs', e.target.value)} />
                                </div>
                                <div className="space-y-2">
                                  <Label>Lingkar Perut (cm)</Label>
                                  <Input placeholder="-" value={soapData.lingkar_perut} onChange={(e) => handleInputChange('lingkar_perut', e.target.value)} />
                                </div>
                              </div>
                              <div className="space-y-2">
                                <Label>Alergi</Label>
                                <Input placeholder="Riwayat alergi..." value={soapData.alergi} onChange={(e) => handleInputChange('alergi', e.target.value)} />
                              </div>
                          </div>

                          {/* Assessment & Plan */}
                          <div className="space-y-4">
                             <h4 className="font-semibold text-emerald-600 border-b pb-1">SOAP Detail</h4>
                             <div className="space-y-2">
                                <Label>Keluhan (Subjektif)</Label>
                                <Textarea className="h-20" placeholder="Keluhan pasien..." value={soapData.keluhan} onChange={(e) => handleInputChange('keluhan', e.target.value)} />
                             </div>
                             <div className="space-y-2">
                                <Label>Pemeriksaan (Objektif)</Label>
                                <Textarea className="h-20" placeholder="Hasil pemeriksaan fisik..." value={soapData.pemeriksaan} onChange={(e) => handleInputChange('pemeriksaan', e.target.value)} />
                             </div>
                             <div className="space-y-2">
                                <Label>Penilaian (Asesmen)</Label>
                                <Textarea className="h-20" placeholder="Diagnosa/Masalah..." value={soapData.penilaian} onChange={(e) => handleInputChange('penilaian', e.target.value)} />
                             </div>
                             <div className="space-y-2">
                                <Label>Rencana (Plan)</Label>
                                <Textarea className="h-20" placeholder="Rencana terapi/tindakan..." value={soapData.rtl} onChange={(e) => handleInputChange('rtl', e.target.value)} />
                             </div>
                          </div>
                      </div>

                      <div className="mt-8 border-t pt-6">
                          <h4 className="font-semibold text-foreground mb-4">Daftar SOAP Tersimpan</h4>
                          <div className="space-y-4">
                              {rawatJalanSoap?.data && rawatJalanSoap.data.length > 0 ? (
                                  [...rawatJalanSoap.data].sort((a: any, b: any) => {
                                      const dateA = new Date(`${a.tgl_perawatan} ${a.jam_rawat}`);
                                      const dateB = new Date(`${b.tgl_perawatan} ${b.jam_rawat}`);
                                      return dateB.getTime() - dateA.getTime();
                                  }).map((item: any, idx: number) => (
                                      <div key={`soap-${idx}`} className="p-4 border rounded-lg bg-muted/20">
                                          <div className="flex justify-between items-start mb-2">
                                              <div className="flex items-center gap-2">
                                                  <span className="text-sm font-medium text-emerald-600">{item.tgl_perawatan} {item.jam_rawat}</span>
                                                  <span className="text-xs text-muted-foreground">• {item.nip}</span>
                                              </div>
                                              <div className="flex gap-1">
                                                  <Button 
                                                      variant="ghost" 
                                                      size="icon" 
                                                      className="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50"
                                                      onClick={() => handleCopySoap(item)}
                                                      title="Salin Data"
                                                  >
                                                      <Copy className="h-4 w-4" />
                                                  </Button>
                                                  <Button 
                                                      variant="ghost" 
                                                      size="icon" 
                                                      className="h-8 w-8 text-orange-600 hover:text-orange-700 hover:bg-orange-50"
                                                      onClick={() => handleEditHistory(selectedPatient, item)}
                                                      title="Edit Data"
                                                  >
                                                      <Edit className="h-4 w-4" />
                                                  </Button>
                                                  <Button 
                                                      variant="ghost" 
                                                      size="icon" 
                                                      className="h-8 w-8 text-destructive hover:text-destructive"
                                                      onClick={() => handleDeleteHistory(selectedPatient, item)}
                                                      title="Hapus Data"
                                                  >
                                                      <Trash className="h-4 w-4" />
                                                  </Button>
                                              </div>
                                          </div>

                                          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3 p-3 bg-white rounded-lg border border-gray-100">
                                              <div>
                                                  <p className="text-xs text-muted-foreground">Tensi</p>
                                                  <p className="font-medium text-foreground">{item.tensi || '-'}</p>
                                              </div>
                                              <div>
                                                  <p className="text-xs text-muted-foreground">Nadi</p>
                                                  <p className="font-medium text-foreground">{item.nadi || '-'}</p>
                                              </div>
                                              <div>
                                                  <p className="text-xs text-muted-foreground">Suhu</p>
                                                  <p className="font-medium text-foreground">{item.suhu_tubuh || '-'}</p>
                                              </div>
                                              <div>
                                                  <p className="text-xs text-muted-foreground">Respirasi</p>
                                                  <p className="font-medium text-foreground">{item.respirasi || '-'}</p>
                                              </div>
                                              <div>
                                                  <p className="text-xs text-muted-foreground">SpO2</p>
                                                  <p className="font-medium text-foreground">{item.spo2 || '-'} %</p>
                                              </div>
                                              <div>
                                                  <p className="text-xs text-muted-foreground">Kesadaran</p>
                                                  <p className="font-medium text-foreground">{item.kesadaran || '-'}</p>
                                              </div>
                                              <div>
                                                  <p className="text-xs text-muted-foreground">GCS</p>
                                                  <p className="font-medium text-foreground">{item.gcs || '-'}</p>
                                              </div>
                                          </div>

                                          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                              <div>
                                                  <p className="font-semibold text-gray-700">S</p>
                                                  <p className="text-gray-600 whitespace-pre-wrap">{item.keluhan || '-'}</p>
                                              </div>
                                              <div>
                                                  <p className="font-semibold text-gray-700">O</p>
                                                  <p className="text-gray-600 whitespace-pre-wrap">{item.pemeriksaan || '-'}</p>
                                              </div>
                                              <div>
                                                  <p className="font-semibold text-gray-700">A</p>
                                                  <p className="text-gray-600 whitespace-pre-wrap">{item.penilaian || '-'}</p>
                                              </div>
                                              <div>
                                                  <p className="font-semibold text-gray-700">P</p>
                                                  <p className="text-gray-600 whitespace-pre-wrap">{item.rtl || '-'}</p>
                                              </div>
                                          </div>
                                      </div>
                                  ))
                              ) : (
                                  <div className="text-center py-4 text-muted-foreground text-sm italic">
                                      Belum ada SOAP yang tersimpan
                                  </div>
                              )}
                          </div>
                      </div>
                    </div>
                  </TabsContent>

                  <TabsContent value="tindakan" className="mt-6">
                    <div className="space-y-4">
                        <div className="flex justify-between items-center">
                            <h3 className="text-lg font-bold text-foreground">Input Tindakan</h3>
                            <Button 
                                onClick={handleTindakanSubmit} 
                                disabled={saveTindakanMutation.isPending}
                                className="bg-emerald-600 hover:bg-emerald-700"
                            >
                                {saveTindakanMutation.isPending ? (
                                    <>
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                        Menyimpan...
                                    </>
                                ) : (
                                    <>
                                        <Save className="mr-2 h-4 w-4" />
                                        Simpan Tindakan
                                    </>
                                )}
                            </Button>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-1 gap-6">
                            <div className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label>Tanggal</Label>
                                        <Input 
                                            type="date" 
                                            value={tindakanData.tgl_perawatan}
                                            onChange={(e) => handleTindakanChange('tgl_perawatan', e.target.value)}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label>Jam</Label>
                                        <Input 
                                            type="time" 
                                            value={tindakanData.jam_rawat}
                                            onChange={(e) => handleTindakanChange('jam_rawat', e.target.value)}
                                        />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label>Jenis Tindakan</Label>
                                    <Select 
                                        value={tindakanData.kd_jenis_prw} 
                                        onValueChange={(value) => handleTindakanChange('kd_jenis_prw', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih tindakan..." />
                                        </SelectTrigger>
                                        <SelectContent className="max-h-[200px]">
                                            {jnsPerawatanData?.data?.map((item: any) => (
                                                <SelectItem key={item.kd_jenis_prw} value={item.kd_jenis_prw}>
                                                    {item.nm_perawatan}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label>Pelaksana</Label>
                                    <Select 
                                        value={tindakanData.provider} 
                                        onValueChange={(value) => handleTindakanChange('provider', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih pelaksana" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="rawat_jl_dr">Dokter</SelectItem>
                                            <SelectItem value="rawat_jl_pr">Petugas</SelectItem>
                                            <SelectItem value="rawat_jl_drpr">Dokter & Petugas</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                {(tindakanData.provider === 'rawat_jl_dr' || tindakanData.provider === 'rawat_jl_drpr') && (
                                    <div className="space-y-2">
                                        <Label>Dokter</Label>
                                        <Select 
                                            value={tindakanData.kode_provider} 
                                            onValueChange={(value) => handleTindakanChange('kode_provider', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Pilih dokter" />
                                            </SelectTrigger>
                                            <SelectContent className="max-h-[200px]">
                                                {dokterData?.data?.map((item: any) => (
                                                    <SelectItem key={item.kd_dokter} value={item.kd_dokter}>
                                                        {item.nm_dokter}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                )}

                                {(tindakanData.provider === 'rawat_jl_pr' || tindakanData.provider === 'rawat_jl_drpr') && (
                                    <div className="space-y-2">
                                        <Label>Petugas</Label>
                                        <Select 
                                            value={tindakanData.provider === 'rawat_jl_drpr' ? tindakanData.kode_provider2 : tindakanData.kode_provider} 
                                            onValueChange={(value) => {
                                                if (tindakanData.provider === 'rawat_jl_drpr') {
                                                    handleTindakanChange('kode_provider2', value);
                                                } else {
                                                    handleTindakanChange('kode_provider', value);
                                                }
                                            }}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Pilih petugas" />
                                            </SelectTrigger>
                                            <SelectContent className="max-h-[200px]">
                                                {petugasData?.data?.map((item: any) => (
                                                    <SelectItem key={item.nip} value={item.nip}>
                                                        {item.nama}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="mt-8 border-t pt-6">
                            <h4 className="font-semibold text-foreground mb-4">Daftar Tindakan Tersimpan</h4>
                            <div className="space-y-4">
                                {rawatJalanTindakan?.data ? (
                                    <>
                                        {/* Dokter */}
                                        {rawatJalanTindakan.data.rawat_jl_dr?.length > 0 && (
                                            <div>
                                                <h5 className="text-sm font-medium text-emerald-600 mb-2">Tindakan Dokter</h5>
                                                <div className="space-y-2">
                                                    {rawatJalanTindakan.data.rawat_jl_dr.map((item: any, idx: number) => (
                                                        <div key={`dr-${idx}`} className="flex justify-between items-center p-3 bg-muted/50 rounded-lg">
                                                            <div>
                                                                <p className="font-medium text-sm">{item.nm_perawatan}</p>
                                                                <p className="text-xs text-muted-foreground">{item.tgl_perawatan} {item.jam_rawat} • {item.nm_dokter}</p>
                                                            </div>
                                                            <Button 
                                                                variant="ghost" 
                                                                size="icon" 
                                                                className="h-8 w-8 text-destructive hover:text-destructive"
                                                                onClick={() => handleDeleteTindakan(item, 'rawat_jl_dr')}
                                                            >
                                                                <Trash className="h-4 w-4" />
                                                            </Button>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}

                                        {/* Petugas */}
                                        {rawatJalanTindakan.data.rawat_jl_pr?.length > 0 && (
                                            <div>
                                                <h5 className="text-sm font-medium text-emerald-600 mb-2 mt-4">Tindakan Petugas</h5>
                                                <div className="space-y-2">
                                                    {rawatJalanTindakan.data.rawat_jl_pr.map((item: any, idx: number) => (
                                                        <div key={`pr-${idx}`} className="flex justify-between items-center p-3 bg-muted/50 rounded-lg">
                                                            <div>
                                                                <p className="font-medium text-sm">{item.nm_perawatan}</p>
                                                                <p className="text-xs text-muted-foreground">{item.tgl_perawatan} {item.jam_rawat} • {item.nama}</p>
                                                            </div>
                                                            <Button 
                                                                variant="ghost" 
                                                                size="icon" 
                                                                className="h-8 w-8 text-destructive hover:text-destructive"
                                                                onClick={() => handleDeleteTindakan(item, 'rawat_jl_pr')}
                                                            >
                                                                <Trash className="h-4 w-4" />
                                                            </Button>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}

                                        {/* Dokter & Petugas */}
                                        {rawatJalanTindakan.data.rawat_jl_drpr?.length > 0 && (
                                            <div>
                                                <h5 className="text-sm font-medium text-emerald-600 mb-2 mt-4">Tindakan Dokter & Petugas</h5>
                                                <div className="space-y-2">
                                                    {rawatJalanTindakan.data.rawat_jl_drpr.map((item: any, idx: number) => (
                                                        <div key={`drpr-${idx}`} className="flex justify-between items-center p-3 bg-muted/50 rounded-lg">
                                                            <div>
                                                                <p className="font-medium text-sm">{item.nm_perawatan}</p>
                                                                <p className="text-xs text-muted-foreground">{item.tgl_perawatan} {item.jam_rawat} • {item.nm_dokter} & {item.nama}</p>
                                                            </div>
                                                            <Button 
                                                                variant="ghost" 
                                                                size="icon" 
                                                                className="h-8 w-8 text-destructive hover:text-destructive"
                                                                onClick={() => handleDeleteTindakan(item, 'rawat_jl_drpr')}
                                                            >
                                                                <Trash className="h-4 w-4" />
                                                            </Button>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                        
                                        {!rawatJalanTindakan.data.rawat_jl_dr?.length && !rawatJalanTindakan.data.rawat_jl_pr?.length && !rawatJalanTindakan.data.rawat_jl_drpr?.length && (
                                            <div className="text-center py-4 text-muted-foreground text-sm italic">
                                                Belum ada tindakan yang tersimpan
                                            </div>
                                        )}
                                    </>
                                ) : (
                                    <div className="flex justify-center py-4">
                                        <Loader2 className="w-6 h-6 animate-spin text-emerald-500" />
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                  </TabsContent>

                  <TabsContent value="resep" className="mt-6">
                    <Tabs defaultValue="non-racikan" className="w-full">
                        <TabsList className="grid grid-cols-2 w-full mb-4">
                            <TabsTrigger value="non-racikan">Non Racikan</TabsTrigger>
                            <TabsTrigger value="racikan">Racikan</TabsTrigger>
                        </TabsList>
                        
                        <TabsContent value="non-racikan" className="space-y-4">
                            <h3 className="text-lg font-bold text-foreground">Resep Obat Non Racikan</h3>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label>Nama Obat</Label>
                                    <Popover open={openObat} onOpenChange={setOpenObat}>
                                        <PopoverTrigger asChild>
                                            <Button
                                                variant="outline"
                                                role="combobox"
                                                aria-expanded={openObat}
                                                className="w-full justify-between"
                                            >
                                                {obatData.kode_brng
                                                    ? (obatData.nama_brng || obatListData?.data?.find((item: any) => item.kode_brng === obatData.kode_brng)?.nama_brng || obatData.kode_brng)
                                                    : "Pilih obat..."}
                                                <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent className="w-[400px] p-0" align="start">
                                            <Command shouldFilter={false}>
                                                <CommandInput
                                                    placeholder="Cari obat..."
                                                    value={obatData.search}
                                                    onValueChange={(value) => setObatData(prev => ({ ...prev, search: value }))}
                                                />
                                                <CommandList>
                                                    <CommandEmpty>Tidak ada obat ditemukan.</CommandEmpty>
                                                    <CommandGroup>
                                                        {obatListData?.data?.map((item: any, index: number) => (
                                                            <CommandItem
                                                                key={`${item.kode_brng}-${index}`}
                                                                value={item.kode_brng}
                                                                onSelect={(currentValue) => {
                                                                    setObatData(prev => ({ 
                                                                        ...prev, 
                                                                        kode_brng: currentValue === obatData.kode_brng ? "" : item.kode_brng,
                                                                        nama_brng: item.nama_brng 
                                                                    }));
                                                                    setOpenObat(false);
                                                                }}
                                                            >
                                                                <Check
                                                                    className={cn(
                                                                        "mr-2 h-4 w-4",
                                                                        obatData.kode_brng === item.kode_brng ? "opacity-100" : "opacity-0"
                                                                    )}
                                                                />
                                                                {item.nama_brng} ({item.kode_sat}) - Depo: {item.nm_bangsal} (Stok: {item.stok}) {item.no_batch ? `(Batch: ${item.no_batch})` : ''}
                                                            </CommandItem>
                                                        ))}
                                                    </CommandGroup>
                                                </CommandList>
                                            </Command>
                                        </PopoverContent>
                                    </Popover>
                                </div>
                                <div className="space-y-2">
                                    <Label>Jumlah</Label>
                                    <Input 
                                        type="number" 
                                        placeholder="0" 
                                        value={obatData.jml}
                                        onChange={(e) => setObatData(prev => ({ ...prev, jml: e.target.value }))}
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label>Aturan Pakai</Label>
                                    <Input 
                                        placeholder="Contoh: 3 x 1 sehari" 
                                        value={obatData.aturan_pakai}
                                        onChange={(e) => setObatData(prev => ({ ...prev, aturan_pakai: e.target.value }))}
                                    />
                                </div>
                            </div>
                            <Button 
                                onClick={handleSaveObat}
                                disabled={saveTindakanMutation.isPending || !obatData.kode_brng}
                                className="w-full md:w-auto bg-emerald-600 hover:bg-emerald-700"
                            >
                                {saveTindakanMutation.isPending ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                                Simpan Obat
                            </Button>

                            <div className="mt-8 border-t pt-6">
                                <h4 className="font-semibold text-foreground mb-4">Daftar Obat Tersimpan</h4>
                                <div className="space-y-4">
                                    {rawatJalanResep?.data?.filter((item: any) => item.kat === 'obat' || !item.kat).length > 0 ? (
                                        <div className="border rounded-md">
                                            <Table>
                                                <TableHeader>
                                                    <TableRow>
                                                        <TableHead>Nama Obat</TableHead>
                                                        <TableHead>Jumlah</TableHead>
                                                        <TableHead>Aturan Pakai</TableHead>
                                                        <TableHead className="w-[50px]"></TableHead>
                                                    </TableRow>
                                                </TableHeader>
                                                <TableBody>
                                                    {rawatJalanResep.data.filter((item: any) => item.kat === 'obat' || !item.kat).map((item: any, idx: number) => (
                                                        <TableRow key={idx}>
                                                            <TableCell>{item.nama_brng}</TableCell>
                                                            <TableCell>{item.jml}</TableCell>
                                                            <TableCell>{item.aturan_pakai}</TableCell>
                                                            <TableCell>
                                                                <Button 
                                                                    variant="ghost" 
                                                                    size="icon" 
                                                                    className="text-destructive hover:text-destructive"
                                                                    onClick={() => handleDeleteResep(item)}
                                                                >
                                                                    <Trash className="h-4 w-4" />
                                                                </Button>
                                                            </TableCell>
                                                        </TableRow>
                                                    ))}
                                                </TableBody>
                                            </Table>
                                        </div>
                                    ) : (
                                        <div className="text-center py-4 text-muted-foreground text-sm italic">
                                            Belum ada resep obat tersimpan
                                        </div>
                                    )}
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="racikan" className="space-y-4">
                             <h3 className="text-lg font-bold text-foreground">Resep Obat Racikan</h3>
                             <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label>Nama Racikan</Label>
                                    <Input 
                                        placeholder="Contoh: Racikan Batuk Pilek" 
                                        value={racikanData.nama_racik}
                                        onChange={(e) => setRacikanData(prev => ({ ...prev, nama_racik: e.target.value }))}
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label>Metode Racik</Label>
                                    <Select 
                                        value={racikanData.kd_jenis_racik} 
                                        onValueChange={(value) => setRacikanData(prev => ({ ...prev, kd_jenis_racik: value }))}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih metode" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {metodeRacikData?.data?.map((item: any) => (
                                                <SelectItem key={item.kd_racik} value={item.kd_racik}>
                                                    {item.nm_racik}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label>Jumlah Racikan</Label>
                                    <Input 
                                        type="number" 
                                        placeholder="0" 
                                        value={racikanData.jml}
                                        onChange={(e) => setRacikanData(prev => ({ ...prev, jml: e.target.value }))}
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label>Aturan Pakai</Label>
                                    <Input 
                                        placeholder="Contoh: 3 x 1 bungkus" 
                                        value={racikanData.aturan_pakai}
                                        onChange={(e) => setRacikanData(prev => ({ ...prev, aturan_pakai: e.target.value }))}
                                    />
                                </div>
                                <div className="col-span-1 md:col-span-2 space-y-2">
                                    <Label>Keterangan</Label>
                                    <Input 
                                        placeholder="Keterangan tambahan..." 
                                        value={racikanData.keterangan}
                                        onChange={(e) => setRacikanData(prev => ({ ...prev, keterangan: e.target.value }))}
                                    />
                                </div>
                             </div>

                             <div className="border p-4 rounded-lg space-y-4 bg-muted/20">
                                <h4 className="font-semibold text-sm">Item Komposisi Racikan</h4>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                    <div className="space-y-2">
                                        <Label>Cari & Pilih Obat</Label>
                                        <div className="space-y-2">
                                            <Popover open={openRacikanObat} onOpenChange={setOpenRacikanObat}>
                                                <PopoverTrigger asChild>
                                                    <Button
                                                        variant="outline"
                                                        role="combobox"
                                                        aria-expanded={openRacikanObat}
                                                        className="w-full justify-between"
                                                    >
                                                        {racikanItem.kode_brng
                                                            ? (racikanItem.nama_brng || racikanObatListData?.data?.find((item: any) => item.kode_brng === racikanItem.kode_brng)?.nama_brng || racikanItem.kode_brng)
                                                            : "Pilih obat..."}
                                                        <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                    </Button>
                                                </PopoverTrigger>
                                                <PopoverContent className="w-[400px] p-0" align="start">
                                                    <Command shouldFilter={false}>
                                                        <CommandInput
                                                            placeholder="Cari obat..."
                                                            value={racikanData.search_obat}
                                                            onValueChange={(value) => setRacikanData(prev => ({ ...prev, search_obat: value }))}
                                                        />
                                                        <CommandList>
                                                            <CommandEmpty>Tidak ada obat ditemukan.</CommandEmpty>
                                                            <CommandGroup>
                                                                {racikanObatListData?.data?.map((item: any, index: number) => (
                                                                    <CommandItem
                                                                        key={`${item.kode_brng}-${index}`}
                                                                        value={item.kode_brng}
                                                                        onSelect={(currentValue) => {
                                                                             setRacikanItem(prev => ({ 
                                                                                 ...prev, 
                                                                                 kode_brng: item.kode_brng, 
                                                                                 nama_brng: item.nama_brng 
                                                                             }));
                                                                             setOpenRacikanObat(false);
                                                                        }}
                                                                    >
                                                                        <Check
                                                                            className={cn(
                                                                                "mr-2 h-4 w-4",
                                                                                racikanItem.kode_brng === item.kode_brng ? "opacity-100" : "opacity-0"
                                                                            )}
                                                                        />
                                                                        {item.nama_brng} ({item.kapasitas}) - Depo: {item.nm_bangsal} (Stok: {item.stok}) {item.no_batch ? `(Batch: ${item.no_batch})` : ''}
                                                                    </CommandItem>
                                                                ))}
                                                            </CommandGroup>
                                                        </CommandList>
                                                    </Command>
                                                </PopoverContent>
                                            </Popover>
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <Label>Kandungan (mg/ml/dll)</Label>
                                        <Input 
                                            type="number" 
                                            placeholder="0" 
                                            value={racikanItem.kandungan}
                                            onChange={(e) => setRacikanItem(prev => ({ ...prev, kandungan: e.target.value }))}
                                        />
                                    </div>
                                    <Button 
                                        onClick={handleAddRacikanItem}
                                        disabled={!racikanItem.kode_brng || !racikanItem.kandungan}
                                        variant="secondary"
                                    >
                                        Tambah Item
                                    </Button>
                                </div>

                                {racikanData.items.length > 0 && (
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Nama Obat</TableHead>
                                                <TableHead>Kandungan</TableHead>
                                                <TableHead className="w-[50px]"></TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {racikanData.items.map((item, idx) => (
                                                <TableRow key={idx}>
                                                    <TableCell>{item.nama_brng}</TableCell>
                                                    <TableCell>{item.kandungan}</TableCell>
                                                    <TableCell>
                                                        <Button 
                                                            variant="ghost" 
                                                            size="icon" 
                                                            onClick={() => handleRemoveRacikanItem(idx)}
                                                            className="text-destructive hover:text-destructive"
                                                        >
                                                            <Trash className="h-4 w-4" />
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                )}
                             </div>

                             <Button 
                                onClick={handleSaveRacikan}
                                disabled={saveTindakanMutation.isPending || !racikanData.nama_racik || racikanData.items.length === 0}
                                className="w-full md:w-auto bg-emerald-600 hover:bg-emerald-700"
                            >
                                {saveTindakanMutation.isPending ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                                Simpan Racikan
                            </Button>

                             <div className="mt-8 border-t pt-6">
                                <h4 className="font-semibold text-foreground mb-4">Daftar Racikan Tersimpan</h4>
                                <div className="space-y-4">
                                    {rawatJalanResep?.data?.filter((item: any) => item.kat === 'racikan').length > 0 ? (
                                        <div className="space-y-4">
                                            {rawatJalanResep.data.filter((item: any) => item.kat === 'racikan').map((item: any, idx: number) => (
                                                <div key={idx} className="p-4 border rounded-lg bg-muted/20">
                                                    <div className="flex justify-between items-start mb-2">
                                                        <div>
                                                            <p className="font-bold text-foreground">{item.nama_racik}</p>
                                                            <p className="text-sm text-muted-foreground">{item.metode_racik} • {item.jml} • {item.aturan_pakai}</p>
                                                            <p className="text-xs text-muted-foreground italic">{item.keterangan}</p>
                                                        </div>
                                                        <Button 
                                                            variant="ghost" 
                                                            size="icon" 
                                                            className="text-destructive hover:text-destructive"
                                                            onClick={() => handleDeleteResep(item)}
                                                        >
                                                            <Trash className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                    
                                                    {item.detail && item.detail.length > 0 && (
                                                        <div className="mt-2 pl-4 border-l-2 border-emerald-200">
                                                            <p className="text-xs font-semibold text-muted-foreground mb-1">Komposisi:</p>
                                                            <ul className="text-xs space-y-1">
                                                                {item.detail.map((det: any, dIdx: number) => (
                                                                    <li key={dIdx}>{det.nama_brng} ({det.kandungan})</li>
                                                                ))}
                                                            </ul>
                                                        </div>
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="text-center py-4 text-muted-foreground text-sm italic">
                                            Belum ada resep racikan tersimpan
                                        </div>
                                    )}
                                </div>
                             </div>
                        </TabsContent>
                    </Tabs>
                  </TabsContent>

                  <TabsContent value="laboratorium" className="mt-6">
                    <div className="text-center py-12 text-muted-foreground">
                      Fitur Laboratorium akan tersedia di pembaruan berikutnya
                    </div>
                  </TabsContent>

                  <TabsContent value="radiologi" className="mt-6">
                    <div className="text-center py-12 text-muted-foreground">
                      Fitur Radiologi akan tersedia di pembaruan berikutnya
                    </div>
                  </TabsContent>

                </Tabs>
              </div>
            </>
          ) : (
             <div className="flex flex-col items-center justify-center h-full bg-card rounded-xl border border-border p-12 text-center">
                <div className="bg-muted p-4 rounded-full mb-4">
                    <Stethoscope className="w-8 h-8 text-muted-foreground" />
                </div>
                <h3 className="text-xl font-semibold text-foreground">Pilih Pasien</h3>
                <p className="text-muted-foreground mt-2 max-w-sm">
                    Silakan pilih pasien dari antrian di sebelah kiri untuk mulai melakukan pemeriksaan.
                </p>
             </div>
          )}
        </div>
      </div>
      
      <AlertDialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <div className="flex items-center justify-center mb-2">
                <div className="p-3 bg-red-100 rounded-full">
                    <AlertTriangle className="w-8 h-8 text-red-600" />
                </div>
            </div>
            <AlertDialogTitle className="text-center">Konfirmasi Hapus</AlertDialogTitle>
            <AlertDialogDescription className="text-center">
              Apakah Anda yakin ingin menghapus data pemeriksaan ini? Tindakan ini tidak dapat dibatalkan.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter className="sm:justify-center gap-2">
            <AlertDialogCancel onClick={() => setItemToDelete(null)} className="mt-0">Batal</AlertDialogCancel>
            <AlertDialogAction onClick={confirmDelete} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">
              Ya, Hapus
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      {/* Feature Modals */}
      <Dialog open={isICDModalOpen} onOpenChange={setIsICDModalOpen}>
        <DialogContent className="sm:max-w-[800px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>ICD Management</DialogTitle>
            </DialogHeader>
            <Tabs defaultValue="icd10" className="w-full">
                <TabsList className="grid w-full grid-cols-2">
                    <TabsTrigger value="icd10">Diagnosa (ICD 10)</TabsTrigger>
                    <TabsTrigger value="icd9">Prosedur (ICD 9)</TabsTrigger>
                </TabsList>
                
                {/* ICD 10 Tab */}
                <TabsContent value="icd10" className="space-y-4 py-4">
                    {/* Search */}
                    <div className="flex gap-2">
                        <Popover open={openIcd10Search} onOpenChange={setOpenIcd10Search}>
                            <PopoverTrigger asChild>
                                <Button
                                    variant="outline"
                                    role="combobox"
                                    aria-expanded={openIcd10Search}
                                    className="w-full justify-between"
                                >
                                    Pilih Diagnosa (ICD 10)...
                                    <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-[600px] p-0">
                                <Command shouldFilter={false}>
                                    <CommandInput placeholder="Cari kode atau nama penyakit..." onValueChange={setIcd10Search} />
                                    <CommandList>
                                        <CommandEmpty>Tidak ditemukan.</CommandEmpty>
                                        <CommandGroup>
                                            {icd10Master?.data?.map((item: any) => (
                                                <CommandItem
                                                    key={item.kd_penyakit}
                                                    value={item.kd_penyakit}
                                                    onSelect={() => handleAddIcd10(item)}
                                                >
                                                    <Check className={cn("mr-2 h-4 w-4", icd10List.find(i => i.kd_penyakit === item.kd_penyakit) ? "opacity-100" : "opacity-0")} />
                                                    {item.kd_penyakit} - {item.nm_penyakit}
                                                </CommandItem>
                                            ))}
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                    </div>

                    {/* Table */}
                    <div className="border rounded-md">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-[100px]">Kode</TableHead>
                                    <TableHead>Nama Penyakit</TableHead>
                                    <TableHead className="w-[100px]">Prioritas</TableHead>
                                    <TableHead className="w-[100px] text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {icd10List.map((item, index) => (
                                    <TableRow key={item.kd_penyakit}>
                                        <TableCell className="font-medium">{item.kd_penyakit}</TableCell>
                                        <TableCell>{item.nm_penyakit}</TableCell>
                                        <TableCell>
                                            <span className={`px-2 py-1 rounded-full text-xs font-medium ${index === 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700'}`}>
                                                {index === 0 ? 'Primer' : `Sekunder ${index}`}
                                            </span>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-1">
                                                <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => moveIcd10(index, 'up')} disabled={index === 0}>
                                                    <ArrowUp className="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => moveIcd10(index, 'down')} disabled={index === icd10List.length - 1}>
                                                    <ArrowDown className="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="icon" className="h-8 w-8 text-destructive" onClick={() => handleRemoveIcd10(index)}>
                                                    <X className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {icd10List.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={4} className="text-center text-muted-foreground py-8">
                                            Belum ada diagnosa dipilih
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                {/* ICD 9 Tab */}
                <TabsContent value="icd9" className="space-y-4 py-4">
                     {/* Search */}
                    <div className="flex gap-2">
                        <Popover open={openIcd9Search} onOpenChange={setOpenIcd9Search}>
                            <PopoverTrigger asChild>
                                <Button
                                    variant="outline"
                                    role="combobox"
                                    aria-expanded={openIcd9Search}
                                    className="w-full justify-between"
                                >
                                    Pilih Prosedur (ICD 9)...
                                    <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-[600px] p-0">
                                <Command shouldFilter={false}>
                                    <CommandInput placeholder="Cari kode atau deskripsi..." onValueChange={setIcd9Search} />
                                    <CommandList>
                                        <CommandEmpty>Tidak ditemukan.</CommandEmpty>
                                        <CommandGroup>
                                            {icd9Master?.data?.map((item: any) => (
                                                <CommandItem
                                                    key={item.kode}
                                                    value={item.kode}
                                                    onSelect={() => handleAddIcd9(item)}
                                                >
                                                    <Check className={cn("mr-2 h-4 w-4", icd9List.find(i => i.kode === item.kode) ? "opacity-100" : "opacity-0")} />
                                                    {item.kode} - {item.deskripsi_panjang}
                                                </CommandItem>
                                            ))}
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                    </div>

                    {/* Table */}
                    <div className="border rounded-md">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-[100px]">Kode</TableHead>
                                    <TableHead>Deskripsi</TableHead>
                                    <TableHead className="w-[100px]">Prioritas</TableHead>
                                    <TableHead className="w-[100px] text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {icd9List.map((item, index) => (
                                    <TableRow key={item.kode}>
                                        <TableCell className="font-medium">{item.kode}</TableCell>
                                        <TableCell>{item.deskripsi_panjang}</TableCell>
                                        <TableCell>
                                            <span className="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                {index + 1}
                                            </span>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-1">
                                                <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => moveIcd9(index, 'up')} disabled={index === 0}>
                                                    <ArrowUp className="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => moveIcd9(index, 'down')} disabled={index === icd9List.length - 1}>
                                                    <ArrowDown className="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="icon" className="h-8 w-8 text-destructive" onClick={() => handleRemoveIcd9(index)}>
                                                    <X className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {icd9List.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={4} className="text-center text-muted-foreground py-8">
                                            Belum ada prosedur dipilih
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>
            </Tabs>
            <DialogFooter>
                <Button variant="outline" onClick={() => setIsICDModalOpen(false)}>Batal</Button>
                <Button onClick={handleSaveICD} disabled={saveDiagnosaMutation.isPending || saveProsedurMutation.isPending} className="bg-emerald-600 hover:bg-emerald-700">
                    {(saveDiagnosaMutation.isPending || saveProsedurMutation.isPending) ? (
                        <>
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            Menyimpan...
                        </>
                    ) : (
                        <>
                            <Save className="mr-2 h-4 w-4" />
                            Simpan Semua
                        </>
                    )}
                </Button>
            </DialogFooter>
        </DialogContent>
      </Dialog>

      <Dialog open={isCatatanModalOpen} onOpenChange={setIsCatatanModalOpen}>
        <DialogContent className="sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>Catatan Pasien</DialogTitle>
            </DialogHeader>
            <div className="space-y-4 py-4">
                <div className="space-y-2">
                    <Label htmlFor="catatan">Catatan</Label>
                    <Textarea 
                        id="catatan" 
                        placeholder="Tulis catatan pasien..." 
                        value={catatanPasien}
                        onChange={(e) => setCatatanPasien(e.target.value)}
                        className="h-32"
                    />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" onClick={() => setIsCatatanModalOpen(false)}>Batal</Button>
                <Button onClick={handleSaveCatatan} disabled={saveCatatanMutation.isPending} className="bg-emerald-600 hover:bg-emerald-700">
                    {saveCatatanMutation.isPending ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                    Simpan
                </Button>
            </DialogFooter>
        </DialogContent>
      </Dialog>

      <Dialog open={isBerkasModalOpen} onOpenChange={setIsBerkasModalOpen}>
        <DialogContent className="sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>Berkas Digital</DialogTitle>
            </DialogHeader>
            <div className="space-y-4 py-4">
                <div className="space-y-2">
                    <Label htmlFor="judul">Judul Berkas</Label>
                    <Input 
                        id="judul" 
                        placeholder="Contoh: Hasil Lab Luar" 
                        value={berkasJudul}
                        onChange={(e) => setBerkasJudul(e.target.value)}
                    />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="deskripsi">Deskripsi / URL</Label>
                    <Textarea 
                        id="deskripsi" 
                        placeholder="Tulis deskripsi atau URL berkas..." 
                        value={berkasDeskripsi}
                        onChange={(e) => setBerkasDeskripsi(e.target.value)}
                    />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" onClick={() => setIsBerkasModalOpen(false)}>Batal</Button>
                <Button onClick={handleSaveBerkas} disabled={saveBerkasMutation.isPending} className="bg-emerald-600 hover:bg-emerald-700">
                    {saveBerkasMutation.isPending ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                    Simpan
                </Button>
            </DialogFooter>
        </DialogContent>
      </Dialog>

      <Dialog open={isResumeModalOpen} onOpenChange={setIsResumeModalOpen}>
        <DialogContent className="sm:max-w-[800px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Resume Medis</DialogTitle>
            </DialogHeader>
            <div className="space-y-4 py-4">
                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label>Diagnosa Utama</Label>
                        <Input value={resumeData.diagnosa_utama} onChange={(e) => setResumeData({...resumeData, diagnosa_utama: e.target.value})} />
                    </div>
                    <div className="space-y-2">
                        <Label>Diagnosa Sekunder</Label>
                        <Input value={resumeData.diagnosa_sekunder} onChange={(e) => setResumeData({...resumeData, diagnosa_sekunder: e.target.value})} />
                    </div>
                </div>
                <div className="space-y-2">
                    <Label>Jalannya Penyakit</Label>
                    <Textarea value={resumeData.jalannya_penyakit} onChange={(e) => setResumeData({...resumeData, jalannya_penyakit: e.target.value})} />
                </div>
                <div className="space-y-2">
                    <Label>Terapi</Label>
                    <Textarea value={resumeData.terapi} onChange={(e) => setResumeData({...resumeData, terapi: e.target.value})} />
                </div>
                <div className="space-y-2">
                    <Label>Kondisi Pulang</Label>
                    <Input value={resumeData.kondisi_pulang} onChange={(e) => setResumeData({...resumeData, kondisi_pulang: e.target.value})} />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" onClick={() => setIsResumeModalOpen(false)}>Batal</Button>
                <Button onClick={handleSaveResume} disabled={saveResumeMutation.isPending} className="bg-emerald-600 hover:bg-emerald-700">
                    {saveResumeMutation.isPending ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                    Simpan
                </Button>
            </DialogFooter>
        </DialogContent>
      </Dialog>

      <Dialog open={isRujukanModalOpen} onOpenChange={setIsRujukanModalOpen}>
        <DialogContent className="sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>Rujukan Internal</DialogTitle>
            </DialogHeader>
            <div className="space-y-4 py-4">
                <div className="space-y-2">
                    <Label>Poli Rujukan</Label>
                    <Select onValueChange={(v) => setRujukanData({...rujukanData, kd_poli: v})} value={rujukanData.kd_poli}>
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih Poli" />
                        </SelectTrigger>
                        <SelectContent>
                            {poliData?.data?.map((p: any) => (
                                <SelectItem key={p.kd_poli} value={p.kd_poli}>{p.nm_poli}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <div className="space-y-2">
                    <Label>Dokter Rujukan</Label>
                    <Select onValueChange={(v) => setRujukanData({...rujukanData, kd_dokter: v})} value={rujukanData.kd_dokter}>
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih Dokter" />
                        </SelectTrigger>
                        <SelectContent>
                            {petugasData?.data?.map((d: any) => (
                                <SelectItem key={d.nip} value={d.nip}>{d.nama}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <div className="space-y-2">
                    <Label>Catatan Rujukan</Label>
                    <Textarea value={rujukanData.catatan} onChange={(e) => setRujukanData({...rujukanData, catatan: e.target.value})} />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" onClick={() => setIsRujukanModalOpen(false)}>Batal</Button>
                <Button onClick={handleSaveRujukan} disabled={saveRujukanMutation.isPending} className="bg-emerald-600 hover:bg-emerald-700">
                    {saveRujukanMutation.isPending ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                    Simpan
                </Button>
            </DialogFooter>
        </DialogContent>
      </Dialog>

      <Dialog open={isOperasiModalOpen} onOpenChange={setIsOperasiModalOpen}>
        <DialogContent className="sm:max-w-[800px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Laporan Operasi</DialogTitle>
            </DialogHeader>
            <div className="space-y-4 py-4">
                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label>Tanggal Operasi</Label>
                        <Input type="date" value={operasiData.tanggal} onChange={(e) => setOperasiData({...operasiData, tanggal: e.target.value})} />
                    </div>
                    <div className="space-y-2">
                        <Label>Jam Operasi</Label>
                        <Input type="time" value={operasiData.jam} onChange={(e) => setOperasiData({...operasiData, jam: e.target.value})} />
                    </div>
                </div>
                <div className="space-y-2">
                    <Label>Operator</Label>
                    <Select onValueChange={(v) => setOperasiData({...operasiData, operator: v})} value={operasiData.operator}>
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih Operator" />
                        </SelectTrigger>
                        <SelectContent>
                            {petugasData?.data?.map((d: any) => (
                                <SelectItem key={d.nip} value={d.nip}>{d.nama}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <div className="space-y-2">
                    <Label>Laporan Operasi</Label>
                    <Textarea className="min-h-[200px]" value={operasiData.laporan} onChange={(e) => setOperasiData({...operasiData, laporan: e.target.value})} />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" onClick={() => setIsOperasiModalOpen(false)}>Batal</Button>
                <Button onClick={handleSaveOperasi} disabled={saveOperasiMutation.isPending} className="bg-emerald-600 hover:bg-emerald-700">
                    {saveOperasiMutation.isPending ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                    Simpan
                </Button>
            </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default Pemeriksaan;
