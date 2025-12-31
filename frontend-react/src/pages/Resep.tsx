import React, { useState, useEffect } from 'react';
import { 
  Pill, 
  Search, 
  Plus, 
  Calendar as CalendarIcon, 
  ShoppingCart, 
  User, 
  Paperclip,
  Trash2,
  Check,
  Clock
} from 'lucide-react';
import { format } from "date-fns";
import { useQuery } from "@tanstack/react-query";
import { 
  getGudangBarangList, 
  getRawatJalanList, 
  GudangBarang, 
  getRawatJalanResep,
  getResepList,
  getResepDetailItems,
  validasiResep,
  hapusResep
} from '@/lib/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  CardFooter,
} from "@/components/ui/card";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import { Calendar } from "@/components/ui/calendar";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { cn } from "@/lib/utils";
import { useToast } from "@/hooks/use-toast";

interface PrescriptionItem {
  kode_brng: string;
  nama_brng: string;
  dosis: string;
  frekuensi: string;
  durasi: string;
  jumlah: string;
  instruksi: string;
}

const Resep: React.FC = () => {
  const { toast } = useToast();
  const [date, setDate] = useState<{ from: Date; to: Date }>({
    from: new Date(),
    to: new Date(),
  });
  
  const [selectedPatient, setSelectedPatient] = useState<string>("");
  const [prescriptionItems, setPrescriptionItems] = useState<PrescriptionItem[]>([]);
  const [savedPrescriptions, setSavedPrescriptions] = useState<{ obat: any[], racikan: any[] }>({ obat: [], racikan: [] });
  const [selectedRequest, setSelectedRequest] = useState<any | null>(null);
  const [requestDetails, setRequestDetails] = useState<any[]>([]);
  const [isDetailLoading, setIsDetailLoading] = useState(false);
  
  // Form State
  const [selectedMedicine, setSelectedMedicine] = useState<string>("");
  const [dosage, setDosage] = useState("");
  const [frequency, setFrequency] = useState("");
  const [duration, setDuration] = useState("");
  const [amount, setAmount] = useState("");
  const [instructions, setInstructions] = useState("");

  // Queries
  const { data: rawatJalanData } = useQuery({
    queryKey: ['rawat-jalan', date.from, date.to],
    queryFn: () => getRawatJalanList(
      format(date.from, 'yyyy-MM-dd'),
      format(date.to, 'yyyy-MM-dd'),
      0,
      100
    )
  });

  const { data: gudangData } = useQuery({
    queryKey: ['gudang-barang'],
    queryFn: () => getGudangBarangList(1, 100)
  });

  const { data: resepData, refetch: refetchResep } = useQuery({
    queryKey: ['resep-list', date.from, date.to],
    queryFn: () => getResepList(
      1, 
      100, 
      '', 
      format(date.from, 'yyyy-MM-dd'),
      format(date.to, 'yyyy-MM-dd')
    )
  });

  const patients = rawatJalanData?.data || [];
  const medicines: GudangBarang[] = gudangData?.data || [];
  const resepList = resepData?.data || [];

  // Filter resep list for "Permintaan Resep" (belum divalidasi/diserahkan)
  const pendingPrescriptions = resepList.filter((r: any) => 
    (!r.tgl_penyerahan || r.tgl_penyerahan === '0000-00-00') && 
    (!r.tgl_perawatan || r.tgl_perawatan === '0000-00-00')
  );

  // Fetch saved prescriptions when patient is selected
  useEffect(() => {
    if (selectedPatient) {
      const fetchPrescriptions = async () => {
        try {
          const result = await getRawatJalanResep(selectedPatient);
          if (result.status === 'success') {
            setSavedPrescriptions(result.data);
          }
        } catch (error) {
          console.error("Failed to fetch prescriptions", error);
        }
      };
      fetchPrescriptions();
    } else {
      setSavedPrescriptions({ obat: [], racikan: [] });
    }
  }, [selectedPatient]);

  // Fetch detail items when a request is selected
  useEffect(() => {
    if (selectedRequest) {
      const fetchDetails = async () => {
        setIsDetailLoading(true);
        try {
          // Pass no_rawat and status (default to 'ralan' if missing)
          const result = await getResepDetailItems(
            selectedRequest.no_resep, 
            selectedRequest.no_rawat, 
            selectedRequest.status || 'ralan'
          );
          if (result.status === 'success') {
            setRequestDetails(result.data);
          }
        } catch (error) {
          console.error("Failed to fetch details", error);
        } finally {
          setIsDetailLoading(false);
        }
      };
      fetchDetails();
    } else {
      setRequestDetails([]);
    }
  }, [selectedRequest]);

  // Group saved prescriptions by no_resep
  const groupedSavedPrescriptions = React.useMemo(() => {
    const groups: Record<string, { tgl: string, jam: string, items: any[] }> = {};
    
    // Helper to add item
    const addItem = (item: any, type: 'obat' | 'racikan') => {
      const key = item.no_resep || 'Tanpa Nomor Resep';
      if (!groups[key]) {
        groups[key] = {
          tgl: item.tgl_perawatan || '-',
          jam: item.jam || '-',
          items: []
        };
      }
      groups[key].items.push({ ...item, type });
    };

    savedPrescriptions.obat.forEach(item => addItem(item, 'obat'));
    savedPrescriptions.racikan.forEach(item => addItem(item, 'racikan'));

    return Object.entries(groups).map(([no_resep, data]) => ({
      no_resep,
      ...data
    }));
  }, [savedPrescriptions]);

  const renderDetailItem = (item: any, idx: number) => {
    const isRacikanHeader = item.jenis === 'Racikan';
    const isRacikanDetail = item.jenis === 'Racikan Detail';
    
    return (
      <div 
        key={idx} 
        className={cn(
          "border-b pb-4 last:border-0 border-blue-100",
          isRacikanDetail && "pl-8 bg-slate-50/50 py-2 border-dashed",
          isRacikanHeader && "bg-blue-50/80 pt-4 px-2 rounded-t-md border-b-2 border-blue-200"
        )}
      >
         <div className="flex justify-between items-center mb-3">
            <h4 className={cn(
              "font-semibold text-sm",
              isRacikanHeader ? "text-blue-900" : "text-slate-800"
            )}>
              {isRacikanDetail ? `- ${item.nama_brng}` : (isRacikanHeader ? `Racikan: ${item.nama_brng}` : `${idx + 1}. ${item.nama_brng}`)}
            </h4>
            {!isRacikanHeader && (
              <span className="text-xs text-muted-foreground bg-white px-2 py-1 rounded border border-blue-100">
                {item.kode_brng}
              </span>
            )}
         </div>
         
         {!isRacikanHeader ? (
           <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div className="space-y-1">
                <label className="text-xs font-medium text-slate-500">Jumlah</label>
                <Input className="h-8 text-xs bg-white" defaultValue={item.jml} readOnly={isRacikanDetail} />
              </div>
              {!isRacikanDetail && (
                <div className="space-y-1">
                  <label className="text-xs font-medium text-slate-500">Aturan Pakai</label>
                  <Input className="h-8 text-xs bg-white md:col-span-3" defaultValue={item.aturan_pakai || '-'} />
                </div>
              )}
              {isRacikanDetail && (
                <div className="space-y-1">
                   <label className="text-xs font-medium text-slate-500">Kandungan</label>
                   <Input className="h-8 text-xs bg-white" defaultValue={item.kandungan || '-'} readOnly />
                </div>
              )}
           </div>
         ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
               <div className="space-y-1">
                  <label className="text-xs font-medium text-blue-700">Jumlah Bungkus</label>
                  <div className="text-sm font-bold">{item.jml}</div>
               </div>
               <div className="space-y-1">
                  <label className="text-xs font-medium text-blue-700">Aturan Pakai</label>
                  <div className="text-sm font-bold">{item.aturan_pakai}</div>
               </div>
               {item.keterangan && (
                 <div className="col-span-2 text-xs italic text-slate-500">
                   "{item.keterangan}"
                 </div>
               )}
            </div>
         )}
      </div>
    );
  };

  const handleDetailClick = (resep: any) => {
    setSelectedRequest(resep);
  };

  const handleCloseDetail = () => {
    setSelectedRequest(null);
    setRequestDetails([]);
  };

  const handleValidateRequest = async () => {
    if (!selectedRequest) return;

    try {
        // Construct payload for validation
        // Based on Apotek_Ralan/Admin.php postValidasiResep
        // It expects no_resep, no_rawat, etc.
        const payload = {
            no_resep: selectedRequest.no_resep,
            no_rawat: selectedRequest.no_rawat,
            penyerahan: 'validasi', // Or empty/null for validation step (deduct stock)
            // If we need to update quantities, we should pass 'jumlah' array, 'aturan_pakai' array, etc.
            // For now, we assume simple validation of existing items
        };
        
        const result = await validasiResep(payload);
        
        if (result.status === 'success' || !result.status) { // Handle void response
            toast({
                title: "Berhasil",
                description: "Resep berhasil divalidasi",
            });
            handleCloseDetail();
            refetchResep();
        } else {
            toast({
                title: "Gagal",
                description: result.message || "Gagal memvalidasi resep",
                variant: "destructive"
            });
        }
    } catch (error) {
        toast({
            title: "Error",
            description: "Terjadi kesalahan sistem",
            variant: "destructive"
        });
    }
  };

  const handleCancelRequest = async () => {
      if (!selectedRequest) return;
      
      try {
          const payload = {
              no_resep: selectedRequest.no_resep,
              no_rawat: selectedRequest.no_rawat,
              tgl_peresepan: selectedRequest.tgl_peresepan,
              jam_peresepan: selectedRequest.jam_peresepan
          };

          const result = await hapusResep(payload);
           if (result.status === 'success' || !result.status) {
              toast({
                  title: "Berhasil",
                  description: "Permintaan resep dibatalkan",
              });
              handleCloseDetail();
              refetchResep();
          } else {
              toast({
                  title: "Gagal",
                  description: result.message || "Gagal membatalkan resep",
                  variant: "destructive"
              });
          }
      } catch (error) {
          toast({
              title: "Error",
              description: "Terjadi kesalahan sistem",
              variant: "destructive"
          });
      }
  };

  const handleAddMedicine = () => {
    if (!selectedMedicine || !amount) {
      toast({
        title: "Error",
        description: "Mohon pilih obat dan isi jumlah",
        variant: "destructive"
      });
      return;
    }

    const medicine = medicines.find(m => m.kode_brng === selectedMedicine);
    if (!medicine) return;

    const newItem: PrescriptionItem = {
      kode_brng: medicine.kode_brng,
      nama_brng: medicine.nama_brng || medicine.kode_brng,
      dosis: dosage,
      frekuensi: frequency,
      durasi: duration,
      jumlah: amount,
      instruksi: instructions
    };

    setPrescriptionItems([...prescriptionItems, newItem]);
    
    // Reset form
    setSelectedMedicine("");
    setDosage("");
    setFrequency("");
    setDuration("");
    setAmount("");
    setInstructions("");
    
    toast({
      title: "Berhasil",
      description: "Obat ditambahkan ke resep"
    });
  };

  const handleRemoveItem = (index: number) => {
    const newItems = [...prescriptionItems];
    newItems.splice(index, 1);
    setPrescriptionItems(newItems);
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col space-y-2">
        <h1 className="text-3xl font-bold tracking-tight text-foreground">Resep Obat & Tindakan Medis</h1>
        <p className="text-muted-foreground">
          Buat resep obat dan catat tindakan medis untuk pasien
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left Column */}
        <div className="space-y-6">
          {/* Pilih Pasien */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="flex items-center gap-2 text-base">
                <User className="h-4 w-4" />
                Pilih Pasien
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <label className="text-xs font-medium text-muted-foreground">Filter Periode</label>
                <div className="flex gap-2">
                  <Popover>
                    <PopoverTrigger asChild>
                      <Button
                        variant={"outline"}
                        className={cn(
                          "w-full justify-start text-left font-normal h-9 text-xs",
                          !date.from && "text-muted-foreground"
                        )}
                      >
                        <CalendarIcon className="mr-2 h-3 w-3" />
                        {date.from ? format(date.from, "dd/MM/yyyy") : <span>Dari</span>}
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-auto p-0">
                      <Calendar
                        mode="single"
                        selected={date.from}
                        onSelect={(d) => d && setDate({ ...date, from: d })}
                        initialFocus
                      />
                    </PopoverContent>
                  </Popover>
                  <Popover>
                    <PopoverTrigger asChild>
                      <Button
                        variant={"outline"}
                        className={cn(
                          "w-full justify-start text-left font-normal h-9 text-xs",
                          !date.to && "text-muted-foreground"
                        )}
                      >
                        <CalendarIcon className="mr-2 h-3 w-3" />
                        {date.to ? format(date.to, "dd/MM/yyyy") : <span>Sampai</span>}
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-auto p-0">
                      <Calendar
                        mode="single"
                        selected={date.to}
                        onSelect={(d) => d && setDate({ ...date, to: d })}
                        initialFocus
                      />
                    </PopoverContent>
                  </Popover>
                </div>
              </div>

              <Select value={selectedPatient} onValueChange={setSelectedPatient}>
                <SelectTrigger>
                  <SelectValue placeholder="Pilih pasien..." />
                </SelectTrigger>
                <SelectContent>
                  {patients.map((p: any) => (
                    <SelectItem key={p.no_rawat} value={p.no_rawat}>
                      <span className="font-medium">{p.no_rawat}</span> - {p.nm_pasien} ({p.nm_poli})
                    </SelectItem>
                  ))}
                  {patients.length === 0 && (
                    <div className="p-2 text-xs text-center text-muted-foreground">
                      Tidak ada pasien pada periode ini
                    </div>
                  )}
                </SelectContent>
              </Select>

              {/* Data Resep Pasien (Tersimpan) - Moved here as per Image 1 */}
              {(savedPrescriptions.obat.length > 0 || savedPrescriptions.racikan.length > 0) && (
                <div className="mt-4 pt-4 border-t">
                  <h4 className="text-xs font-bold text-muted-foreground mb-3 uppercase tracking-wider">Data Resep Pasien</h4>
                  <div className="space-y-4">
                     {groupedSavedPrescriptions.map((group, idx) => (
                       <div key={idx} className="border rounded-lg bg-slate-50 overflow-hidden shadow-sm">
                          <div className="bg-slate-100 px-3 py-2 border-b flex justify-between items-center">
                             <div className="flex flex-col">
                               <span className="font-mono text-xs font-bold text-slate-700">#{group.no_resep}</span>
                               <span className="text-[10px] text-muted-foreground">{group.tgl} {group.jam}</span>
                             </div>
                             <Badge className="bg-emerald-500 text-[10px] h-5 px-2">dispensed</Badge>
                          </div>
                          <div className="p-2 space-y-2">
                             {group.items.map((item, i) => (
                               <div key={i} className="text-xs border-b border-dashed last:border-0 pb-1 last:pb-0">
                                  <div className="font-medium text-slate-800">
                                    {item.type === 'racikan' ? `${item.nama_racik} (Racikan)` : item.nama_brng}
                                  </div>
                                  <div className="text-slate-500 flex justify-between">
                                     <span>{item.jml} {item.type === 'racikan' ? 'Bungkus' : item.kode_sat}</span>
                                     <span className="italic">{item.aturan_pakai || '-'}</span>
                                  </div>
                               </div>
                             ))}
                          </div>
                       </div>
                     ))}
                  </div>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Permintaan Resep */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="flex items-center gap-2 text-base">
                <ShoppingCart className="h-4 w-4" />
                Permintaan Resep
              </CardTitle>
              <CardDescription className="text-xs">
                Resep belum divalidasi dan diserahkan
              </CardDescription>
            </CardHeader>
            <CardContent>
               {pendingPrescriptions.length > 0 ? (
                <div className="space-y-3 max-h-[300px] overflow-y-auto pr-2">
                  {pendingPrescriptions.map((resep: any, idx: number) => (
                    <div key={idx} className="p-3 border rounded-lg bg-slate-50 text-sm">
                      <div className="flex justify-between items-start mb-1">
                         <span className="font-semibold text-emerald-700">{resep.no_resep}</span>
                         <Badge variant="outline" className="text-[10px] bg-white text-slate-500 border-slate-200">requested</Badge>
                      </div>
                      <div className="mt-1 mb-3">
                        <p className="text-sm font-bold text-slate-800 truncate">{resep.nm_pasien}</p>
                        <p className="text-xs text-slate-500">RM: {resep.no_rkm_medis}</p>
                      </div>
                      <div className="flex items-center gap-1 text-[10px] text-muted-foreground mb-3">
                        <Clock className="w-3 h-3" />
                        <span>{resep.tgl_peresepan}, {resep.jam_peresepan}</span>
                      </div>
                      <div className="flex justify-end">
                          <Button 
                            variant="outline" 
                            size="sm" 
                            className="h-7 text-xs"
                            onClick={() => handleDetailClick(resep)}
                          >
                            Detail
                          </Button>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="text-sm text-muted-foreground py-4 text-center">
                  Tidak ada permintaan resep
                </div>
              )}
            </CardContent>
          </Card>

          {/* Stok Obat */}
          <Card className="max-h-[500px] overflow-hidden flex flex-col">
            <CardHeader className="pb-3">
              <CardTitle className="flex items-center gap-2 text-base">
                <ShoppingCart className="h-4 w-4" />
                Stok Obat
              </CardTitle>
              <CardDescription className="text-xs">
                Cek ketersediaan obat di klinik
              </CardDescription>
            </CardHeader>
            <CardContent className="overflow-y-auto flex-1 pr-2">
              <div className="space-y-3">
                {medicines.map((item, index) => (
                  <div key={index} className="flex items-center justify-between p-2 border rounded-lg">
                    <div>
                      <p className="font-medium text-sm truncate max-w-[150px]">{item.nama_brng || item.kode_brng}</p>
                      <p className="text-xs text-muted-foreground">{item.kode_sat || 'Satuan'}</p>
                    </div>
                    <Badge className={cn(
                      "text-xs",
                      parseFloat(item.stok) > 10 ? "bg-emerald-500 hover:bg-emerald-600" : 
                      parseFloat(item.stok) > 0 ? "bg-orange-500 hover:bg-orange-600" : "bg-red-500 hover:bg-red-600"
                    )}>
                      {item.stok}
                    </Badge>
                  </div>
                ))}
                {medicines.length === 0 && (
                   <div className="text-center text-muted-foreground text-sm py-4">Loading stok...</div>
                )}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Right Column */}
        <div className="lg:col-span-2 space-y-6">
          
          {selectedRequest && (
            /* Detail Permintaan Resep View */
            <Card className="mb-6 border-blue-200 bg-blue-50/30">
              <CardHeader className="pb-3 border-b">
                <CardTitle className="flex items-center gap-2 text-base text-blue-800">
                   <ShoppingCart className="h-4 w-4" />
                   Detail Permintaan Resep #{selectedRequest.no_resep}
                </CardTitle>
                <CardDescription className="text-xs">
                  Pasien: {selectedRequest.nm_pasien} ({selectedRequest.no_rkm_medis})
                </CardDescription>
              </CardHeader>
              <CardContent className="pt-6">
                 {isDetailLoading ? (
                   <div className="text-center py-8 text-muted-foreground">Memuat detail resep...</div>
                 ) : (
                   <div className="space-y-6">
                      {requestDetails.map((item: any, idx: number) => renderDetailItem(item, idx))}
                      {requestDetails.length === 0 && (
                        <div className="text-center py-4 text-muted-foreground">Tidak ada item detail</div>
                      )}
                   </div>
                 )}
              </CardContent>
              <CardFooter className="flex justify-end gap-2 pt-2 pb-4 border-t bg-blue-50/50">
                 <Button variant="outline" onClick={handleCloseDetail} className="bg-white hover:bg-slate-50 text-xs h-8">
                    Tutup
                 </Button>
                 <Button variant="destructive" onClick={handleCancelRequest} className="text-xs h-8">
                    Batalkan Permintaan
                 </Button>
                 <Button className="bg-blue-600 hover:bg-blue-700 text-white text-xs h-8" onClick={handleValidateRequest}>
                    Validasi Resep
                 </Button>
              </CardFooter>
            </Card>
          )}

          {/* Tambah Obat Form - Always visible */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="flex items-center gap-2 text-base">
                <Plus className="h-4 w-4" />
                Tambah Obat
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
                  <div className="space-y-2">
                    <label className="text-sm font-medium">Pilih Obat</label>
                    <Select value={selectedMedicine} onValueChange={setSelectedMedicine}>
                      <SelectTrigger>
                        <SelectValue placeholder="Pilih obat..." />
                      </SelectTrigger>
                      <SelectContent>
                        {medicines.map((m) => (
                          <SelectItem key={m.kode_brng} value={m.kode_brng}>
                            {m.nama_brng} (Stok: {m.stok})
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <label className="text-sm font-medium">Dosis</label>
                      <Input 
                        placeholder="contoh: 1 tablet" 
                        value={dosage}
                        onChange={(e) => setDosage(e.target.value)}
                      />
                    </div>
                    <div className="space-y-2">
                      <label className="text-sm font-medium">Frekuensi</label>
                      <Select value={frequency} onValueChange={setFrequency}>
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih frekuensi..." />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="1x1">1 x 1 Sehari</SelectItem>
                          <SelectItem value="2x1">2 x 1 Sehari</SelectItem>
                          <SelectItem value="3x1">3 x 1 Sehari</SelectItem>
                          <SelectItem value="4x1">4 x 1 Sehari</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <label className="text-sm font-medium">Durasi</label>
                      <Input 
                        placeholder="contoh: 7 hari" 
                        value={duration}
                        onChange={(e) => setDuration(e.target.value)}
                      />
                    </div>
                    <div className="space-y-2">
                      <label className="text-sm font-medium">Jumlah</label>
                      <Input 
                        placeholder="Jumlah obat" 
                        type="number"
                        value={amount}
                        onChange={(e) => setAmount(e.target.value)}
                      />
                    </div>
                  </div>

                  <div className="space-y-2">
                    <label className="text-sm font-medium">Instruksi Khusus</label>
                    <Input 
                      placeholder="contoh: sesudah makan, sebelum tidur" 
                      value={instructions}
                      onChange={(e) => setInstructions(e.target.value)}
                    />
                  </div>

                  <Button 
                    className="w-full md:w-auto bg-emerald-500 hover:bg-emerald-600 text-white"
                    onClick={handleAddMedicine}
                  >
                    <Plus className="mr-2 h-4 w-4" />
                    Tambah ke Resep
                  </Button>
                </CardContent>
              </Card>

              {/* Daftar Resep List */}
              <Card>
                <CardHeader className="pb-3 border-b">
                  <CardTitle className="flex items-center gap-2 text-base">
                    <Paperclip className="h-4 w-4" />
                    Daftar Resep ({prescriptionItems.length} item)
                  </CardTitle>
                </CardHeader>
                <CardContent className="pt-6 min-h-[200px] flex flex-col justify-center items-center">
                  {prescriptionItems.length === 0 ? (
                    <div className="text-center text-muted-foreground">
                      <Pill className="w-12 h-12 mx-auto mb-3 opacity-20" />
                      <p className="font-medium">Belum ada resep</p>
                      <p className="text-xs">Validasi permintaan atau tambah manual</p>
                    </div>
                  ) : (
                    <div className="w-full space-y-4">
                      {prescriptionItems.map((item, index) => (
                        <div key={index} className="flex items-start justify-between p-4 border rounded-lg bg-slate-50">
                          <div>
                            <h4 className="font-semibold text-emerald-700">{item.nama_brng}</h4>
                            <div className="text-sm text-slate-600 mt-1 space-y-1">
                              <p>{item.jumlah} item • {item.dosis} • {item.frekuensi}</p>
                              <p className="italic">"{item.instruksi}"</p>
                            </div>
                          </div>
                          <Button 
                            variant="ghost" 
                            size="icon" 
                            className="text-red-500 hover:text-red-600 hover:bg-red-50"
                            onClick={() => handleRemoveItem(index)}
                          >
                            <Trash2 className="h-4 w-4" />
                          </Button>
                        </div>
                      ))}
                      
                      <div className="flex justify-end pt-4 border-t mt-4">
                        <Button className="bg-emerald-500 hover:bg-emerald-600">
                          <Check className="mr-2 h-4 w-4" /> Simpan Resep
                        </Button>
                      </div>
                    </div>
                  )}
                </CardContent>
              </Card>
        </div>
      </div>
    </div>
  );
};

export default Resep;
