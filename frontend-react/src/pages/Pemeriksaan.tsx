import React, { useState, useEffect } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Clock, User, Calendar, Stethoscope, FileText, Pill, Smile, Loader2, Save } from 'lucide-react';
import { format } from 'date-fns';
import { cn } from '@/lib/utils';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
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
import { useToast } from '@/hooks/use-toast';
import { getRawatJalanList, getRiwayatPerawatan, saveSOAP } from '@/lib/api';

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
const HistoryItem: React.FC<{ history: any }> = ({ history }) => (
  <div className="p-4 border border-border rounded-xl bg-white hover:shadow-sm transition-shadow">
    <div className="flex items-start justify-between mb-3">
      <div className="flex items-center gap-2">
        <Calendar className="w-4 h-4 text-emerald-500" />
        <span className="font-medium text-foreground">{history.tgl_registrasi}</span>
      </div>
      <span className="text-sm text-muted-foreground">{history.no_rawat}</span>
    </div>
    
    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
            <p className="font-semibold text-gray-700 mb-1">Diagnosa</p>
            <p className="text-gray-600">{history.diagnosa || '-'}</p>
        </div>
        <div>
            <p className="font-semibold text-gray-700 mb-1">Keluhan</p>
            <p className="text-gray-600">{history.keluhan || '-'}</p>
        </div>
        <div>
            <p className="font-semibold text-gray-700 mb-1">Pemeriksaan</p>
            <p className="text-gray-600">{history.pemeriksaan || '-'}</p>
        </div>
        <div>
            <p className="font-semibold text-gray-700 mb-1">Terapi/Tindakan</p>
            <p className="text-gray-600">{history.tindakan || '-'}</p>
        </div>
    </div>
  </div>
);

const Pemeriksaan: React.FC = () => {
  const [selectedPatient, setSelectedPatient] = useState<any | null>(null);
  const [dateFrom, setDateFrom] = useState<Date | undefined>(new Date());
  const [dateTo, setDateTo] = useState<Date | undefined>(new Date());
  const { toast } = useToast();
  const queryClient = useQueryClient();

  const [isDateFromOpen, setIsDateFromOpen] = useState(false);
  const [isDateToOpen, setIsDateToOpen] = useState(false);

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
    keluhan: '',
    pemeriksaan: '',
    alergi: '',
    lingkar_perut: '',
    rtl: '',
    penilaian: '',
    instruksi: '',
    evaluasi: '',
    nip: '', // This should ideally come from logged in user
  });

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
        setSoapData({
            suhu_tubuh: '',
            tensi: '',
            nadi: '',
            respirasi: '',
            tinggi: '',
            berat: '',
            gcs: '',
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
    }
  }, [selectedPatient]);

  const saveSoapMutation = useMutation({
    mutationFn: (data: any) => saveSOAP(data),
    onSuccess: () => {
      toast({ title: 'Berhasil', description: 'Data pemeriksaan berhasil disimpan' });
      queryClient.invalidateQueries({ queryKey: ['riwayatPerawatan'] });
      // Optionally update status to 'Sudah'
    },
    onError: (error: any) => {
      toast({ title: 'Gagal', description: error.message || 'Gagal menyimpan data', variant: 'destructive' });
    },
  });

  const handleSoapSubmit = () => {
    if (!selectedPatient) return;

    const payload = {
        ...soapData,
        no_rawat: selectedPatient.no_rawat,
        tgl_perawatan: format(new Date(), 'yyyy-MM-dd'),
        jam_rawat: format(new Date(), 'HH:mm:ss'),
    };

    saveSoapMutation.mutate(payload);
  };

  const handleInputChange = (field: string, value: string) => {
    setSoapData(prev => ({ ...prev, [field]: value }));
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
                <Tabs defaultValue="riwayat" className="w-full">
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
                      ) : historyData?.data && historyData.data.length > 0 ? (
                          historyData.data.map((history: any, index: number) => (
                            <HistoryItem key={index} history={history} />
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
                        <h3 className="text-lg font-bold text-foreground">Input Pemeriksaan (SOAP)</h3>
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
                                    Simpan Pemeriksaan
                                </>
                            )}
                        </Button>
                      </div>
                      
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                          {/* Subjective & Objective */}
                          <div className="space-y-4">
                              <h4 className="font-semibold text-emerald-600 border-b pb-1">Tanda Vital & Fisik</h4>
                              <div className="grid grid-cols-2 gap-4">
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
                    </div>
                  </TabsContent>

                  <TabsContent value="odontogram" className="mt-6">
                    <div className="text-center py-12 text-muted-foreground">
                      Fitur Odontogram akan tersedia di pembaruan berikutnya
                    </div>
                  </TabsContent>

                  <TabsContent value="tindakan" className="mt-6">
                    <div className="space-y-4">
                      <h3 className="text-lg font-bold text-foreground">Input Tindakan</h3>
                      <div className="space-y-2">
                        <Label>Jenis Tindakan</Label>
                        <Select>
                          <SelectTrigger>
                            <SelectValue placeholder="Pilih jenis tindakan" />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="konsultasi">Konsultasi</SelectItem>
                            <SelectItem value="cabut-gigi">Cabut Gigi</SelectItem>
                            <SelectItem value="tambal-gigi">Tambal Gigi</SelectItem>
                            <SelectItem value="scaling">Scaling</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                      <Button>Tambah Tindakan</Button>
                    </div>
                  </TabsContent>

                  <TabsContent value="resep" className="mt-6">
                    <div className="space-y-4">
                      <h3 className="text-lg font-bold text-foreground">Resep Obat</h3>
                      <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                          <Label>Nama Obat</Label>
                          <Select>
                            <SelectTrigger>
                              <SelectValue placeholder="Pilih obat" />
                            </SelectTrigger>
                            <SelectContent>
                              <SelectItem value="paracetamol">Paracetamol 500mg</SelectItem>
                              <SelectItem value="amoxicillin">Amoxicillin 500mg</SelectItem>
                              <SelectItem value="ibuprofen">Ibuprofen 400mg</SelectItem>
                            </SelectContent>
                          </Select>
                        </div>
                        <div className="space-y-2">
                          <Label>Jumlah</Label>
                          <Input type="number" placeholder="0" />
                        </div>
                      </div>
                      <div className="space-y-2">
                        <Label>Aturan Pakai</Label>
                        <Input placeholder="3 x 1 sehari" />
                      </div>
                      <Button>Tambah ke Resep</Button>
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
    </div>
  );
};

export default Pemeriksaan;
