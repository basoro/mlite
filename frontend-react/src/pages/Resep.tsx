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
  Check
} from 'lucide-react';
import { format } from "date-fns";
import { useQuery } from "@tanstack/react-query";
import { getGudangBarangList, getRawatJalanList, GudangBarang } from '@/lib/api';
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

  const patients = rawatJalanData?.data || [];
  const medicines: GudangBarang[] = gudangData?.data || [];

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
    <div className="container mx-auto p-6 space-y-6">
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
                      {p.no_rkm_medis} - {p.nm_pasien}
                    </SelectItem>
                  ))}
                  {patients.length === 0 && (
                    <div className="p-2 text-xs text-center text-muted-foreground">
                      Tidak ada pasien pada periode ini
                    </div>
                  )}
                </SelectContent>
              </Select>
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
                Validasi dan kurangi stok saat siap
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="text-sm text-muted-foreground py-4 text-center">
                Tidak ada permintaan resep
              </div>
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
          {/* Tambah Obat Form */}
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
