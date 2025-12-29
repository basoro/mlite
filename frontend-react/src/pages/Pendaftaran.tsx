import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Calendar, Plus, Clock, Edit2, Trash2, ChevronDown, Activity, User, Loader2, Search, Calendar as CalendarIcon, AlertTriangle } from 'lucide-react';
import { format } from 'date-fns';
import { cn } from '@/lib/utils';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { Calendar as CalendarComponent } from '@/components/ui/calendar';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { useToast } from '@/hooks/use-toast';
import { getRawatJalanList, getMasterList, getPasienList, createRawatJalan, updateRawatJalan, deleteRawatJalan } from '@/lib/api';

interface ScheduleItem {
  no_rawat: string;
  jam_reg: string;
  no_reg: string;
  nm_poli: string;
  nm_pasien: string;
  no_rkm_medis: string;
  stts: string;
  kd_poli: string;
  kd_dokter: string;
  kd_pj: string;
  png_jawab: string;
  status_bayar: string;
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'Belum': return 'bg-gray-100 text-gray-600 hover:bg-gray-200';
    case 'Sudah': return 'bg-green-100 text-green-600 hover:bg-green-200';
    case 'Batal': return 'bg-red-100 text-red-600 hover:bg-red-200';
    case 'Berkas Diterima': return 'bg-blue-100 text-blue-600 hover:bg-blue-200';
    case 'Dirujuk': return 'bg-orange-100 text-orange-600 hover:bg-orange-200';
    case 'Meninggal': return 'bg-slate-800 text-white hover:bg-slate-700';
    case 'Dirawat': return 'bg-purple-100 text-purple-600 hover:bg-purple-200';
    case 'Pulang Paksa': return 'bg-rose-100 text-rose-600 hover:bg-rose-200';
    default: return 'bg-gray-100 text-gray-600 hover:bg-gray-200';
  }
};

const Pendaftaran: React.FC = () => {
  const [selectedDate, setSelectedDate] = useState<Date>(new Date());
  const [isCalendarOpen, setIsCalendarOpen] = useState(false);
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [isEditMode, setIsEditMode] = useState(false);
  const [selectedNoRawat, setSelectedNoRawat] = useState<string | null>(null);
  const [formData, setFormData] = useState({
    no_rkm_medis: '',
    kd_poli: '',
    kd_dokter: '',
    kd_pj: '',
  });
  const [searchTerm, setSearchTerm] = useState('');
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [deleteNoRawat, setDeleteNoRawat] = useState<string | null>(null);

  const { toast } = useToast();
  const queryClient = useQueryClient();
  
  // Get current date formatted as YYYY-MM-DD
  const formattedDate = format(selectedDate, 'yyyy-MM-dd');
  
  const { data: scheduleData, isLoading } = useQuery({
    queryKey: ['rawatJalan', formattedDate],
    queryFn: () => getRawatJalanList(formattedDate, formattedDate, 0, 100),
  });

  // Fetch Master Data for Dropdowns
  const { data: poliList } = useQuery({
    queryKey: ['master', 'poliklinik'],
    queryFn: () => getMasterList('poliklinik', 1, 100),
  });

  const { data: dokterList } = useQuery({
    queryKey: ['master', 'dokter'],
    queryFn: () => getMasterList('dokter', 1, 100),
  });

  const { data: penjabList } = useQuery({
    queryKey: ['master', 'penjab'],
    queryFn: () => getMasterList('penjab', 1, 100),
  });

  // Patient Search
  const { data: patientList } = useQuery({
    queryKey: ['pasien', searchTerm],
    queryFn: () => getPasienList(1, 10, searchTerm),
    enabled: searchTerm.length > 2,
  });

  const createMutation = useMutation({
    mutationFn: createRawatJalan,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['rawatJalan'] });
      toast({ title: 'Berhasil', description: 'Jadwal berhasil ditambahkan' });
      handleCloseDialog();
    },
    onError: (error: any) => {
      toast({ title: 'Gagal', description: error.message || 'Gagal menambahkan jadwal', variant: 'destructive' });
    },
  });

  const updateMutation = useMutation({
    mutationFn: (data: any) => updateRawatJalan(selectedNoRawat!, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['rawatJalan'] });
      toast({ title: 'Berhasil', description: 'Pendaftaran berhasil diperbarui' });
      handleCloseDialog();
    },
    onError: (error: any) => {
      toast({ title: 'Gagal', description: error.message || 'Gagal memperbarui pendaftaran', variant: 'destructive' });
    },
  });

  const updateStatusMutation = useMutation({
    mutationFn: ({ noRawat, stts }: { noRawat: string; stts: string }) => 
      updateRawatJalan(noRawat, { stts }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['rawatJalan'] });
      toast({ title: 'Berhasil', description: 'Status berhasil diperbarui' });
    },
    onError: (error: any) => {
      toast({ title: 'Gagal', description: error.message || 'Gagal memperbarui status', variant: 'destructive' });
    },
  });

  const handleStatusChange = (noRawat: string, stts: string) => {
    updateStatusMutation.mutate({ noRawat, stts });
  };

  const deleteMutation = useMutation({
    mutationFn: deleteRawatJalan,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['rawatJalan'] });
      toast({ title: 'Berhasil', description: 'Jadwal berhasil dihapus' });
      setDeleteDialogOpen(false);
      setDeleteNoRawat(null);
    },
    onError: (error: any) => {
      toast({ title: 'Gagal', description: error.message || 'Gagal menghapus jadwal', variant: 'destructive' });
    },
  });

  const confirmDelete = () => {
    if (deleteNoRawat) {
      deleteMutation.mutate(deleteNoRawat);
    }
  };

  const handleCloseDialog = () => {
    setIsDialogOpen(false);
    setIsEditMode(false);
    setSelectedNoRawat(null);
    setFormData({ no_rkm_medis: '', kd_poli: '', kd_dokter: '', kd_pj: '' });
    setSearchTerm('');
  };

  const handleEdit = (item: ScheduleItem) => {
    setIsEditMode(true);
    setSelectedNoRawat(item.no_rawat);
    setFormData({
      no_rkm_medis: item.no_rkm_medis,
      kd_poli: item.kd_poli,
      kd_dokter: item.kd_dokter,
      kd_pj: item.kd_pj,
    });
    setSearchTerm(`${item.nm_pasien} (${item.no_rkm_medis})`);
    setIsDialogOpen(true);
  };

  const handleDelete = (noRawat: string) => {
    setDeleteNoRawat(noRawat);
    setDeleteDialogOpen(true);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (isEditMode) {
      updateMutation.mutate(formData);
    } else {
      createMutation.mutate({
        ...formData,
        tgl_registrasi: formattedDate,
        jam_reg: format(new Date(), 'HH:mm:ss')
      });
    }
  };

  const schedules: ScheduleItem[] = scheduleData?.data || [];

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex items-start justify-between">
        <div>
          <h1 className="text-3xl font-bold text-foreground">Manajemen Pendaftaran</h1>
          <p className="text-muted-foreground mt-1">Kelola pendaftaran dan appointment pasien</p>
        </div>
        
        <Dialog open={isDialogOpen} onOpenChange={(open) => {
          if (open) setIsDialogOpen(true);
          else handleCloseDialog();
        }}>
          <DialogTrigger asChild>
            <Button className="bg-emerald-500 hover:bg-emerald-600 text-white gap-2">
              <Plus className="w-4 h-4" />
              Tambah Pendaftaran
            </Button>
          </DialogTrigger>
          <DialogContent className="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle>{isEditMode ? 'Edit Pendaftaran' : 'Buat Pendaftaran Baru'}</DialogTitle>
            </DialogHeader>
            <form onSubmit={handleSubmit} className="space-y-4 pt-4">
              <div className="space-y-2">
                <Label>Cari Pasien (Nama/No. RM)</Label>
                <div className="relative">
                  <Input 
                    placeholder="Ketik minimal 3 karakter..." 
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="pl-9"
                    disabled={isEditMode}
                  />
                  <Search className="w-4 h-4 absolute left-3 top-3 text-muted-foreground" />
                </div>
                {!isEditMode && searchTerm.length > 2 && patientList?.data && (
                  <div className="border rounded-md max-h-40 overflow-y-auto bg-white shadow-sm mt-1">
                    {patientList.data.map((p: any) => (
                      <div
                        key={p.no_rkm_medis}
                        className="p-2 hover:bg-gray-100 cursor-pointer text-sm"
                        onClick={() => {
                          setFormData({ ...formData, no_rkm_medis: p.no_rkm_medis });
                          setSearchTerm(`${p.nm_pasien} (${p.no_rkm_medis})`);
                        }}
                      >
                        <div className="font-medium">{p.nm_pasien}</div>
                        <div className="text-xs text-muted-foreground">{p.no_rkm_medis}</div>
                      </div>
                    ))}
                  </div>
                )}
                {formData.no_rkm_medis && (
                   <p className="text-xs text-emerald-600 font-medium">Pasien terpilih: {formData.no_rkm_medis}</p>
                )}
              </div>

              <div className="space-y-2">
                <Label>Poliklinik</Label>
                <Select 
                  value={formData.kd_poli} 
                  onValueChange={(val) => setFormData({ ...formData, kd_poli: val })}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih Poliklinik" />
                  </SelectTrigger>
                  <SelectContent>
                    {poliList?.data?.map((poli: any) => (
                      <SelectItem key={poli.kd_poli} value={poli.kd_poli}>
                        {poli.nm_poli}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <Label>Dokter</Label>
                <Select 
                  value={formData.kd_dokter} 
                  onValueChange={(val) => setFormData({ ...formData, kd_dokter: val })}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih Dokter" />
                  </SelectTrigger>
                  <SelectContent>
                    {dokterList?.data?.map((dokter: any) => (
                      <SelectItem key={dokter.kd_dokter} value={dokter.kd_dokter}>
                        {dokter.nm_dokter}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <Label>Penjamin</Label>
                <Select 
                  value={formData.kd_pj} 
                  onValueChange={(val) => setFormData({ ...formData, kd_pj: val })}
                >
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

              <div className="flex justify-end gap-2 pt-4">
                <Button type="button" variant="outline" onClick={handleCloseDialog}>
                  Batal
                </Button>
                <Button type="submit" disabled={createMutation.isPending || updateMutation.isPending || !formData.no_rkm_medis}>
                  {createMutation.isPending || updateMutation.isPending ? 'Menyimpan...' : 'Simpan Pendaftaran'}
                </Button>
              </div>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        {/* Left Sidebar - Date Selection */}
        <Card className="h-fit">
          <CardContent className="p-6 space-y-6">
            <div>
              <h3 className="font-bold mb-4 flex items-center gap-2">
                <Calendar className="w-5 h-5" />
                Pilih Tanggal
              </h3>
              
              <div className="relative mb-4">
                <Popover open={isCalendarOpen} onOpenChange={setIsCalendarOpen}>
                  <PopoverTrigger asChild>
                    <Button
                      variant="outline"
                      className={cn(
                        "w-full justify-start text-left font-normal",
                        !selectedDate && "text-muted-foreground"
                      )}
                    >
                      <CalendarIcon className="mr-2 h-4 w-4" />
                      {selectedDate ? format(selectedDate, "dd MMMM yyyy") : "Pilih Tanggal"}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0" align="start">
                    <CalendarComponent
                      mode="single"
                      selected={selectedDate}
                      onSelect={(date) => {
                        if (date) {
                          setSelectedDate(date);
                          setIsCalendarOpen(false);
                        }
                      }}
                      initialFocus
                    />
                  </PopoverContent>
                </Popover>
              </div>

              <div className="space-y-2">
                <button
                  onClick={() => setSelectedDate(new Date())}
                  className={`w-full text-left px-4 py-2 rounded-md text-sm border transition-colors ${
                    format(selectedDate, 'yyyy-MM-dd') === format(new Date(), 'yyyy-MM-dd')
                      ? 'border-emerald-500 bg-white text-emerald-600 font-medium'
                      : 'border-gray-200 hover:bg-gray-50 text-gray-600'
                  }`}
                >
                  Hari Ini
                </button>
                <button
                  onClick={() => {
                    const yesterday = new Date();
                    yesterday.setDate(yesterday.getDate() - 1);
                    setSelectedDate(yesterday);
                  }}
                  className={`w-full text-left px-4 py-2 rounded-md text-sm border transition-colors ${
                    format(selectedDate, 'yyyy-MM-dd') === format(new Date(new Date().setDate(new Date().getDate() - 1)), 'yyyy-MM-dd')
                      ? 'border-emerald-500 bg-white text-emerald-600 font-medium'
                      : 'border-gray-200 hover:bg-gray-50 text-gray-600'
                  }`}
                >
                  Kemarin
                </button>
              </div>
            </div>

            <div>
              <h3 className="font-bold mb-4">Statistik Hari Ini</h3>
              <div className="space-y-3 text-sm">
                <div className="flex justify-between items-center">
                  <div className="flex items-center gap-2">
                    <span className="w-2 h-2 rounded-full bg-gray-800"></span>
                    <span>Total Pendaftaran:</span>
                  </div>
                  <span className="font-medium">{schedules.length}</span>
                </div>
                <div className="flex justify-between items-center">
                  <div className="flex items-center gap-2">
                    <span className="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Selesai:</span>
                  </div>
                  <span className="font-medium text-emerald-600">
                    {schedules.filter(s => s.stts === 'Sudah').length}
                  </span>
                </div>
                <div className="flex justify-between items-center">
                  <div className="flex items-center gap-2">
                    <span className="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span>Sedang Berlangsung:</span>
                  </div>
                  <span className="font-medium text-blue-600">
                    {schedules.filter(s => s.stts === 'Berkas Diterima').length}
                  </span>
                </div>
                <div className="flex justify-between items-center">
                  <div className="flex items-center gap-2">
                    <span className="w-2 h-2 rounded-full bg-red-500"></span>
                    <span>Menunggu:</span>
                  </div>
                  <span className="font-medium text-red-600">
                    {schedules.filter(s => s.stts === 'Belum').length}
                  </span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Main Content - Schedule List */}
        <Card className="md:col-span-3">
          <CardContent className="p-6">
            <div className="mb-6">
              <h2 className="text-xl font-bold flex items-center gap-2">
                <Clock className="w-5 h-5" />
                Pendaftaran {format(selectedDate, 'EEEE, dd MMMM yyyy')}
              </h2>
              <p className="text-sm text-gray-500 mt-1">
                Daftar appointment dan pendaftaran untuk tanggal yang dipilih
              </p>
            </div>

            {isLoading ? (
              <div className="flex justify-center py-8">
                <Loader2 className="w-8 h-8 animate-spin text-emerald-500" />
              </div>
            ) : (
              <div className="space-y-4">
                {schedules.map((item) => (
                  <div key={item.no_rawat} className="border rounded-lg p-4 bg-white hover:bg-gray-50 transition-colors">
                    <div className="flex flex-col md:flex-row justify-between gap-4">
                      <div className="flex gap-4 flex-1">
                        <div className="flex flex-col items-center gap-1 w-24 flex-shrink-0">
                          <div className="font-bold text-lg text-gray-700">
                            {item.jam_reg}
                          </div>
                          <div className="bg-gray-200 text-gray-600 text-xl font-bold w-14 h-14 flex items-center justify-center rounded-sm border-l-2 border-gray-400">
                            {item.no_reg}
                          </div>
                        </div>
                        
                        <div className="flex flex-col gap-1 flex-1">
                          <div className="flex items-center gap-3 h-[28px]">
                            <Badge variant="secondary" className={`
                                ${item.nm_poli.toLowerCase().includes('gigi') ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700'}
                                hover:bg-opacity-80 border-none px-3 py-1 rounded-full text-xs font-medium
                              `}>
                                {item.nm_poli}
                              </Badge>
                              <div className={`flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium cursor-pointer transition-colors ${getStatusColor(item.stts)}`}>
                                <DropdownMenu>
                                  <DropdownMenuTrigger asChild>
                                    <div className="flex items-center gap-1 cursor-pointer w-full justify-between">
                                      <span>{item.stts}</span>
                                      <ChevronDown className="w-3 h-3" />
                                    </div>
                                  </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                  {formattedDate > format(new Date(), 'yyyy-MM-dd') ? (
                                    ['Terdaftar', 'Belum', 'Batal', 'Dokter Berhalangan'].map((status) => (
                                      <DropdownMenuItem key={status} onClick={() => handleStatusChange(item.no_rawat, status)}>
                                        {status}
                                      </DropdownMenuItem>
                                    ))
                                  ) : (
                                    ['Belum', 'Sudah', 'Batal', 'Berkas Diterima', 'Dirujuk', 'Meninggal', 'Dirawat', 'Pulang Paksa'].map((status) => (
                                      <DropdownMenuItem key={status} onClick={() => handleStatusChange(item.no_rawat, status)}>
                                        {status}
                                      </DropdownMenuItem>
                                    ))
                                  )}
                                </DropdownMenuContent>
                              </DropdownMenu>
                            </div>
                          </div>
                          
                          <div className="flex items-center gap-2 h-[40px]">
                            <User className="w-4 h-4 text-gray-400 flex-shrink-0" />
                            <span className="font-bold text-gray-800">{item.nm_pasien}</span>
                            <span className="text-gray-400 text-sm">({item.no_rkm_medis})</span>
                            <span className="text-gray-400 text-sm">| {item.no_rawat}</span>
                          </div>
                          <div className="flex flex-wrap items-center gap-2">
                            <Badge variant="outline" className="text-xs font-normal border-purple-200 text-purple-700 bg-purple-50">
                                {item.png_jawab}
                            </Badge>
                            <Badge variant="outline" className={`text-xs font-normal ${
                                item.status_bayar === 'Sudah Bayar' ? 'border-emerald-200 text-emerald-700 bg-emerald-50' : 
                                'border-orange-200 text-orange-700 bg-orange-50'
                            }`}>
                                {item.status_bayar}
                            </Badge>
                          </div>
                        </div>
                      </div>

                      <div className="flex flex-col items-end gap-3 justify-between">
                        <div className="flex gap-2">
                          <Button 
                            variant="outline" 
                            size="icon" 
                            className="h-8 w-8 text-gray-500 hover:text-gray-700"
                            onClick={() => handleEdit(item)}
                          >
                            <Edit2 className="w-4 h-4" />
                          </Button>
                          <Button 
                            variant="outline" 
                            size="icon" 
                            className="h-8 w-8 text-red-500 hover:text-red-700 hover:bg-red-50 hover:border-red-200"
                            onClick={() => handleDelete(item.no_rawat)}
                          >
                            <Trash2 className="w-4 h-4" />
                          </Button>
                        </div>
                        
                        <div className="flex gap-2">
                          <Button variant="outline" size="sm" className="h-8 text-blue-600 border-blue-200 bg-blue-50 hover:bg-blue-100 gap-1.5 text-xs font-medium">
                            <Activity className="w-3.5 h-3.5" />
                            BPJS
                          </Button>
                          <Button variant="outline" size="sm" className="h-8 text-emerald-600 border-emerald-200 bg-emerald-50 hover:bg-emerald-100 gap-1.5 text-xs font-medium">
                            <User className="w-3.5 h-3.5" />
                            Satu Sehat
                          </Button>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
                {schedules.length === 0 && (
                  <div className="text-center py-8 text-muted-foreground">
                    Tidak ada pendaftaran untuk tanggal ini
                  </div>
                )}
              </div>
            )}
          </CardContent>
        </Card>
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
              Apakah Anda yakin ingin menghapus pendaftaran ini? Tindakan ini tidak dapat dibatalkan.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter className="sm:justify-center gap-2">
            <AlertDialogCancel onClick={() => setDeleteNoRawat(null)} className="mt-0">Batal</AlertDialogCancel>
            <AlertDialogAction onClick={confirmDelete} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">
              Ya, Hapus
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
};

export default Pendaftaran;
