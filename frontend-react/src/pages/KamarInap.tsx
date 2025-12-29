import React, { useState, useEffect } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Clock, User, Calendar, Stethoscope, FileText, Pill, Smile, Loader2, Save, Trash, Edit, AlertTriangle, Check, ChevronsUpDown, BedDouble } from 'lucide-react';
import { format } from 'date-fns';
import { cn } from '@/lib/utils';
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
import { 
    getKamarInapList, 
    getRiwayatPerawatan, 
    saveKamarInapSOAP, 
    deleteKamarInapSOAP, 
    getMasterList, 
    saveKamarInapTindakan, 
    getKamarInapTindakan, 
    deleteKamarInapTindakan 
} from '@/lib/api';

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
      <span className="text-lg font-bold text-foreground">{patient.kamar}</span>
      <div className="flex items-center gap-2">
        <span className="text-xs text-muted-foreground">{patient.nm_bangsal}</span>
        <span className={`px-2 py-0.5 rounded-full text-[10px] font-medium ${
            patient.stts_pulang === '-' ? 'bg-green-100 text-green-700' :
            'bg-gray-100 text-gray-700'
        }`}>
            {patient.stts_pulang === '-' ? 'Dirawat' : patient.stts_pulang}
        </span>
      </div>
    </div>
    <p className="font-medium text-foreground">{patient.nm_pasien}</p>
    <p className="text-sm text-muted-foreground">{patient.no_rkm_medis}</p>
    <div className="flex items-center gap-1 mt-1 text-xs text-muted-foreground">
        <Clock className="w-3 h-3" />
        <span>Masuk: {patient.tgl_masuk} {patient.jam_masuk}</span>
    </div>
  </div>
);

// History Item Component
const HistoryItem: React.FC<{ history: any; onEdit?: (history: any, soap: any) => void; onDelete?: (history: any, soap: any) => void }> = ({ history, onEdit, onDelete }) => {
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
            {(history.rawat_inap_dr?.length > 0 || history.rawat_inap_pr?.length > 0 || history.rawat_inap_drpr?.length > 0) && (
                <div className="mt-4 pt-4 border-t">
                    <h4 className="font-semibold text-sm mb-2 text-emerald-700">Riwayat Tindakan</h4>
                    <div className="space-y-2 text-sm">
                        {history.rawat_inap_dr?.map((item: any, idx: number) => (
                            <div key={`dr-${idx}`} className="p-2 bg-gray-50 rounded border border-gray-100">
                                <p className="font-medium">{item.nm_perawatan}</p>
                                <p className="text-xs text-muted-foreground">
                                    Dokter: {item.nm_dokter} • {item.tgl_perawatan} {item.jam_rawat}
                                </p>
                            </div>
                        ))}
                        {history.rawat_inap_pr?.map((item: any, idx: number) => (
                            <div key={`pr-${idx}`} className="p-2 bg-gray-50 rounded border border-gray-100">
                                <p className="font-medium">{item.nm_perawatan}</p>
                                <p className="text-xs text-muted-foreground">
                                    Petugas: {item.nama} • {item.tgl_perawatan} {item.jam_rawat}
                                </p>
                            </div>
                        ))}
                        {history.rawat_inap_drpr?.map((item: any, idx: number) => (
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
        </div>
    );
};

const KamarInap: React.FC = () => {
  const [selectedPatient, setSelectedPatient] = useState<any | null>(null);
  const [dateFrom, setDateFrom] = useState<Date | undefined>(new Date());
  const [dateTo, setDateTo] = useState<Date | undefined>(new Date());
  const { toast } = useToast();
  const queryClient = useQueryClient();

  // Fetch Kamar Inap Tindakan
  const { data: kamarInapTindakan, refetch: refetchKamarInapTindakan } = useQuery({
    queryKey: ['kamarInapTindakan', selectedPatient?.no_rawat],
    queryFn: () => getKamarInapTindakan(selectedPatient.no_rawat),
    enabled: !!selectedPatient?.no_rawat
  });

  const deleteTindakanMutation = useMutation({
    mutationFn: (data: any) => deleteKamarInapTindakan(data),
    onSuccess: () => {
      toast({ title: 'Berhasil', description: 'Tindakan berhasil dihapus' });
      refetchKamarInapTindakan();
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
    provider: 'rawat_inap_dr',
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

  // Fetch Obat Data
  const { data: obatListData } = useQuery({
    queryKey: ['master', 'gudangbarang', obatData.search],
    queryFn: () => getMasterList('gudangbarang', 1, 50, obatData.search),
    enabled: true 
  });

  // Fetch Racikan Obat Data
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
        kd_jenis_prw: obatData.kode_brng, 
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
        kd_jenis_prw: racikanData.kd_jenis_racik,
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
    queryKey: ['master', 'jns_perawatan_inap'], // Assuming separate master or same
    queryFn: () => getMasterList('jns_perawatan_inap', 1, 1000), // Check if this exists or use jns_perawatan
  });

  const { data: dokterData } = useQuery({
    queryKey: ['master', 'dokter'],
    queryFn: () => getMasterList('dokter', 1, 1000),
  });

  const { data: petugasData } = useQuery({
    queryKey: ['master', 'petugas'],
    queryFn: () => getMasterList('petugas', 1, 1000),
  });

  const saveTindakanMutation = useMutation({
    mutationFn: (data: any) => saveKamarInapTindakan(data),
    onSuccess: () => {
      toast({ title: 'Berhasil', description: 'Tindakan berhasil disimpan' });
      queryClient.invalidateQueries({ queryKey: ['riwayatPerawatan'] });
      queryClient.invalidateQueries({ queryKey: ['kamarInapTindakan'] });
      setTindakanData(prev => ({
        ...prev,
        kd_jenis_prw: '',
      }));
    },
    onError: (error: any) => {
      toast({ title: 'Gagal', description: error.message || 'Gagal menyimpan tindakan', variant: 'destructive' });
    }
  });

  // Reset forms on success
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
    
    const currentUser = localStorage.getItem('auth_username') || '';

    const payload = {
        kat: 'tindakan',
        ...tindakanData,
        no_rawat: selectedPatient.no_rawat,
        kode_provider: tindakanData.kode_provider || currentUser,
        kode_provider2: tindakanData.provider === 'rawat_inap_drpr' ? tindakanData.kode_provider2 : '',
    };
    
    saveTindakanMutation.mutate(payload);
  };

  const handleTindakanChange = (field: string, value: string) => {
    setTindakanData(prev => {
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

  // Fetch Queue (Kamar Inap)
  const { data: queueData, isLoading: isQueueLoading } = useQuery({
    queryKey: ['kamarInap', formattedDateFrom, formattedDateTo],
    queryFn: () => getKamarInapList(formattedDateFrom, formattedDateTo, 0, 100),
    enabled: !!dateFrom && !!dateTo,
  });

  // Fetch Patient History
  const { data: historyData, isLoading: isHistoryLoading } = useQuery({
    queryKey: ['riwayatPerawatan', selectedPatient?.no_rkm_medis],
    queryFn: () => getRiwayatPerawatan(selectedPatient.no_rkm_medis),
    enabled: !!selectedPatient?.no_rkm_medis,
  });

  const patients = queueData?.data || [];
  
  useEffect(() => {
    if (selectedPatient) {
        resetSoapForm();
        setIsEditMode(false);
        setEditHistoryData(null);
    }
  }, [selectedPatient]);

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
    mutationFn: (data: any) => saveKamarInapSOAP(data),
    onSuccess: () => {
      toast({ title: 'Berhasil', description: `Data pemeriksaan berhasil ${isEditMode ? 'diperbarui' : 'disimpan'}` });
      queryClient.invalidateQueries({ queryKey: ['riwayatPerawatan'] });
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
    mutationFn: (data: any) => deleteKamarInapSOAP(data),
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

    const tglPerawatan = isEditMode && editHistoryData ? (editHistoryData.tgl_perawatan || editHistoryData.tgl_registrasi) : format(new Date(), 'yyyy-MM-dd');
    const jamRawat = isEditMode && editHistoryData ? (editHistoryData.jam_rawat || editHistoryData.jam_reg) : format(new Date(), 'HH:mm:ss');
    const noRawat = isEditMode && editHistoryData ? editHistoryData.no_rawat : selectedPatient.no_rawat;
    
    const currentUserNip = localStorage.getItem('auth_username') || import.meta.env.VITE_API_USERNAME || '-';

    const payload = {
        ...soapData,
        no_rawat: noRawat,
        tgl_perawatan: tglPerawatan,
        jam_rawat: jamRawat,
        nip: soapData.nip && soapData.nip !== '-' ? soapData.nip : currentUserNip, 
    };

    saveSoapMutation.mutate(payload);
  };

  const handleEditHistory = (history: any, soap: any) => {
      setActiveTab('pemeriksaan');
      setIsEditMode(true);
      setEditHistoryData({ ...history, ...soap }); 
      
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
          penilaian: soap.penilaian || soap.diagnosa || '', 
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

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div>
        <h1 className="text-3xl font-bold text-foreground">Kamar Inap & Visite</h1>
        <p className="text-muted-foreground mt-1">Lakukan pemeriksaan pasien kamar inap (visite) dan input hasil diagnosa</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-6 gap-6">
        {/* Left Column - Patient Queue */}
        <div className="lg:col-span-2 space-y-4">
          <div className="bg-card rounded-xl border border-border p-6">
            <div className="flex items-center gap-2 mb-2">
              <BedDouble className="w-5 h-5 text-foreground" />
              <h2 className="text-xl font-bold text-foreground">Pasien Rawat Inap</h2>
            </div>
            <p className="text-sm text-muted-foreground mb-4">
              Daftar pasien yang sedang dirawat
            </p>

            {/* Date Filter */}
            <div className="bg-muted/50 rounded-lg p-4 mb-4">
              <p className="text-sm font-medium text-foreground mb-3">Filter Periode Masuk</p>
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
                    Tidak ada pasien rawat inap
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
                    <p className="text-sm text-muted-foreground">Kamar / Bed</p>
                    <p className="font-semibold text-foreground text-xs">{selectedPatient.kamar}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">Bangsal</p>
                    <p className="font-semibold text-foreground">{selectedPatient.nm_bangsal}</p>
                  </div>
                </div>
              </div>

              {/* Tabs */}
              <div className="bg-card rounded-xl border border-border p-6">
                <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                  <TabsList className="grid grid-cols-5 w-full">
                    <TabsTrigger value="riwayat" className="gap-2">
                      <FileText className="w-4 h-4" />
                      Riwayat
                    </TabsTrigger>
                    <TabsTrigger value="pemeriksaan" className="gap-2">
                      <Stethoscope className="w-4 h-4" />
                      SOAP
                    </TabsTrigger>
                    <TabsTrigger value="odontogram" className="gap-2">
                      <Smile className="w-4 h-4" />
                      Odontogram
                    </TabsTrigger>
                    <TabsTrigger value="tindakan" className="gap-2">
                      <Calendar className="w-4 h-4" />
                      Tindakan
                    </TabsTrigger>
                    <TabsTrigger value="resep" className="gap-2">
                      <Pill className="w-4 h-4" />
                      Resep
                    </TabsTrigger>
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
                          {isEditMode ? 'Edit Visite (SOAP)' : 'Input Visite (SOAP)'}
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
                                      {isEditMode ? 'Update Visite' : 'Simpan Visite'}
                                  </>
                              )}
                          </Button>
                        </div>
                      </div>
                      
                      <div className="grid grid-cols-1 md:grid-cols-1 gap-6">
                          {/* Subjective & Objective */}
                          <div className="space-y-4">
                              <h4 className="font-semibold text-emerald-600 border-b pb-1">Tanda Vital & Fisik</h4>
                              <div className="grid grid-cols-3 gap-4">
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
                                  <Label>Kesadaran</Label>
                                  <Select value={soapData.kesadaran} onValueChange={(value) => handleInputChange('kesadaran', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih Kesadaran" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Compos Mentis">Compos Mentis</SelectItem>
                                        <SelectItem value="Somnolen">Somnolence</SelectItem>
                                        <SelectItem value="Sopor">Sopor</SelectItem>
                                        <SelectItem value="Coma">Coma</SelectItem>
                                    </SelectContent>
                                  </Select>
                                </div>
                                <div className="space-y-2">
                                  <Label>SpO2 (%)</Label>
                                  <Input placeholder="98" value={soapData.spo2} onChange={(e) => handleInputChange('spo2', e.target.value)} />
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
                             <div className="space-y-2">
                                <Label>Instruksi (Instruction)</Label>
                                <Textarea className="h-20" placeholder="Instruksi tindakan..." value={soapData.instruksi} onChange={(e) => handleInputChange('instruksi', e.target.value)} />
                             </div>
                             <div className="space-y-2">
                                <Label>Evaluasi (Evaluation)</Label>
                                <Textarea className="h-20" placeholder="Evaluasi hasil tindakan..." value={soapData.evaluasi} onChange={(e) => handleInputChange('evaluasi', e.target.value)} />
                             </div>
                          </div>
                      </div>
                    </div>
                  </TabsContent>

                  <TabsContent value="odontogram" className="mt-6">
                    <div className="text-center py-12 text-muted-foreground">
                      Fitur Odontogram akan tersedia di pembaruan berikutnya
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
                                            <SelectItem value="rawat_inap_dr">Dokter</SelectItem>
                                            <SelectItem value="rawat_inap_pr">Petugas</SelectItem>
                                            <SelectItem value="rawat_inap_drpr">Dokter & Petugas</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                {(tindakanData.provider === 'rawat_inap_dr' || tindakanData.provider === 'rawat_inap_drpr') && (
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

                                {(tindakanData.provider === 'rawat_inap_pr' || tindakanData.provider === 'rawat_inap_drpr') && (
                                    <div className="space-y-2">
                                        <Label>Petugas</Label>
                                        <Select 
                                            value={tindakanData.provider === 'rawat_inap_drpr' ? tindakanData.kode_provider2 : tindakanData.kode_provider} 
                                            onValueChange={(value) => {
                                                if (tindakanData.provider === 'rawat_inap_drpr') {
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
                                {kamarInapTindakan?.data ? (
                                    <>
                                        {/* Dokter */}
                                        {kamarInapTindakan.data.rawat_inap_dr?.length > 0 && (
                                            <div>
                                                <h5 className="text-sm font-medium text-emerald-600 mb-2">Tindakan Dokter</h5>
                                                <div className="space-y-2">
                                                    {kamarInapTindakan.data.rawat_inap_dr.map((item: any, idx: number) => (
                                                        <div key={`dr-${idx}`} className="flex justify-between items-center p-3 bg-muted/50 rounded-lg">
                                                            <div>
                                                                <p className="font-medium text-sm">{item.nm_perawatan}</p>
                                                                <p className="text-xs text-muted-foreground">{item.tgl_perawatan} {item.jam_rawat} • {item.nm_dokter}</p>
                                                            </div>
                                                            <Button 
                                                                variant="ghost" 
                                                                size="icon" 
                                                                className="h-8 w-8 text-destructive hover:text-destructive"
                                                                onClick={() => handleDeleteTindakan(item, 'rawat_inap_dr')}
                                                            >
                                                                <Trash className="h-4 w-4" />
                                                            </Button>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}

                                        {/* Petugas */}
                                        {kamarInapTindakan.data.rawat_inap_pr?.length > 0 && (
                                            <div>
                                                <h5 className="text-sm font-medium text-emerald-600 mb-2 mt-4">Tindakan Petugas</h5>
                                                <div className="space-y-2">
                                                    {kamarInapTindakan.data.rawat_inap_pr.map((item: any, idx: number) => (
                                                        <div key={`pr-${idx}`} className="flex justify-between items-center p-3 bg-muted/50 rounded-lg">
                                                            <div>
                                                                <p className="font-medium text-sm">{item.nm_perawatan}</p>
                                                                <p className="text-xs text-muted-foreground">{item.tgl_perawatan} {item.jam_rawat} • {item.nama}</p>
                                                            </div>
                                                            <Button 
                                                                variant="ghost" 
                                                                size="icon" 
                                                                className="h-8 w-8 text-destructive hover:text-destructive"
                                                                onClick={() => handleDeleteTindakan(item, 'rawat_inap_pr')}
                                                            >
                                                                <Trash className="h-4 w-4" />
                                                            </Button>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}

                                        {/* Dokter & Petugas */}
                                        {kamarInapTindakan.data.rawat_inap_drpr?.length > 0 && (
                                            <div>
                                                <h5 className="text-sm font-medium text-emerald-600 mb-2 mt-4">Tindakan Dokter & Petugas</h5>
                                                <div className="space-y-2">
                                                    {kamarInapTindakan.data.rawat_inap_drpr.map((item: any, idx: number) => (
                                                        <div key={`drpr-${idx}`} className="flex justify-between items-center p-3 bg-muted/50 rounded-lg">
                                                            <div>
                                                                <p className="font-medium text-sm">{item.nm_perawatan}</p>
                                                                <p className="text-xs text-muted-foreground">{item.tgl_perawatan} {item.jam_rawat} • {item.nm_dokter} & {item.nama}</p>
                                                            </div>
                                                            <Button 
                                                                variant="ghost" 
                                                                size="icon" 
                                                                className="h-8 w-8 text-destructive hover:text-destructive"
                                                                onClick={() => handleDeleteTindakan(item, 'rawat_inap_drpr')}
                                                            >
                                                                <Trash className="h-4 w-4" />
                                                            </Button>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                        
                                        {!kamarInapTindakan.data.rawat_inap_dr?.length && !kamarInapTindakan.data.rawat_inap_pr?.length && !kamarInapTindakan.data.rawat_inap_drpr?.length && (
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
                        </TabsContent>
                    </Tabs>
                  </TabsContent>
                </Tabs>
              </div>
            </>
          ) : (
             <div className="flex flex-col items-center justify-center h-full bg-card rounded-xl border border-border p-12 text-center">
                <div className="bg-muted p-4 rounded-full mb-4">
                    <BedDouble className="w-8 h-8 text-muted-foreground" />
                </div>
                <h3 className="text-xl font-semibold text-foreground">Pilih Pasien</h3>
                <p className="text-muted-foreground mt-2 max-w-sm">
                    Silakan pilih pasien dari daftar rawat inap di sebelah kiri untuk mulai melakukan pemeriksaan/visite.
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
    </div>
  );
};

export default KamarInap;
