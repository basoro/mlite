import React, { useState } from 'react';
import { 
  FileText, 
  Search, 
  Plus, 
  Calendar as CalendarIcon, 
  User, 
  CreditCard,
  Eye,
  Trash2,
  CheckCircle2,
  AlertCircle
} from 'lucide-react';
import { format } from "date-fns";
import { useQuery } from "@tanstack/react-query";
import { getRawatJalanList } from '@/lib/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
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

// Mock Services Data
const services = [
  { id: '1', name: 'EKG', price: 100000 },
  { id: '2', name: 'Konsultasi Dokter Umum', price: 150000 },
  { id: '3', name: 'Scaling', price: 200000 },
  { id: '4', name: 'Tambal Gigi', price: 150000 },
];

// Mock Invoices Data
const invoices = [
  { id: 'INV478064', patient: 'Joko Santoso', date: '2025-12-23', amount: 350000, status: 'Lunas', method: 'cash' },
  { id: 'INV301837', patient: 'Siti Aminah', date: '2025-12-23', amount: 275000, status: 'Lunas', method: 'cash' },
  { id: 'INV380982', patient: 'Joko Santoso', date: '2025-12-20', amount: 150000, status: 'Lunas', method: 'cash' },
  { id: 'INV040919', patient: 'Budi Santoso', date: '2025-12-14', amount: 100000, status: 'Lunas', method: 'cash' },
];

interface BillItem {
  id: string;
  name: string;
  price: number;
  quantity: number;
  total: number;
}

const Billing: React.FC = () => {
  const { toast } = useToast();
  const [date, setDate] = useState<{ from: Date; to: Date }>({
    from: new Date(),
    to: new Date(),
  });
  
  const [selectedPatient, setSelectedPatient] = useState<string>("");
  const [billItems, setBillItems] = useState<BillItem[]>([]);
  
  // Form State
  const [selectedService, setSelectedService] = useState<string>("");
  const [quantity, setQuantity] = useState("1");
  const [customPrice, setCustomPrice] = useState("");
  const [notes, setNotes] = useState("");

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

  const patients = rawatJalanData?.data || [];
  const selectedPatientData = patients.find((p: any) => p.no_rawat === selectedPatient);

  const handleAddItem = () => {
    if (!selectedService) {
      toast({
        title: "Error",
        description: "Mohon pilih layanan terlebih dahulu",
        variant: "destructive"
      });
      return;
    }

    const service = services.find(s => s.id === selectedService);
    if (!service) return;

    const qty = parseInt(quantity) || 1;
    const price = customPrice ? parseInt(customPrice) : service.price;
    
    const newItem: BillItem = {
      id: Math.random().toString(36).substr(2, 9),
      name: service.name,
      price: price,
      quantity: qty,
      total: price * qty
    };

    setBillItems([...billItems, newItem]);
    
    // Reset form
    setSelectedService("");
    setQuantity("1");
    setCustomPrice("");
    setNotes("");
    
    toast({
      title: "Berhasil",
      description: "Item ditambahkan ke tagihan"
    });
  };

  const handleRemoveItem = (index: number) => {
    const newItems = [...billItems];
    newItems.splice(index, 1);
    setBillItems(newItems);
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    }).format(amount);
  };

  return (
    <div className="container mx-auto p-6 space-y-6">
      <div className="flex flex-col space-y-2">
        <h1 className="text-3xl font-bold tracking-tight text-foreground">Billing & Faktur</h1>
        <p className="text-muted-foreground">
          Kelola tagihan pasien dan cetak faktur
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* Left Column (3 cols) */}
        <div className="lg:col-span-3 space-y-6">
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
                        {date.from ? format(date.from, "dd MMM yyyy") : <span>Dari</span>}
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
                        {date.to ? format(date.to, "dd MMM yyyy") : <span>Sampai</span>}
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
                      {p.nm_pasien} ({p.kd_pj === 'BPJ' ? 'BPJS' : 'Umum'})
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>

              {selectedPatientData && (
                <div className="bg-slate-50 p-3 rounded-md text-sm space-y-1 border">
                  <p className="font-bold">{selectedPatientData.nm_pasien}</p>
                  <p className="text-muted-foreground text-xs">ID: {selectedPatientData.no_rkm_medis}</p>
                  <p className="text-muted-foreground text-xs">Asuransi: {selectedPatientData.kd_pj === 'BPJ' ? 'BPJS' : 'Umum'}</p>
                  {selectedPatientData.no_peserta && (
                    <p className="text-muted-foreground text-xs">BPJS: {selectedPatientData.no_peserta}</p>
                  )}
                </div>
              )}

              {selectedPatientData && (
                <div className="space-y-2">
                  <div className="flex items-center gap-2 text-sm font-medium text-blue-600 bg-blue-50 p-2 rounded">
                    <FileText className="h-4 w-4" />
                    Riwayat Tagihan Pasien
                  </div>
                  <div className="border rounded-md p-3 space-y-2 bg-white">
                    <div className="flex justify-between items-start">
                      <span className="font-bold text-sm">INV040919</span>
                      <Badge className="bg-emerald-500 hover:bg-emerald-600 text-[10px] h-5">Lunas</Badge>
                    </div>
                    <p className="text-xs text-muted-foreground">2025-12-14 - 21:47:00</p>
                    <div className="flex justify-between items-center mt-1">
                      <span className="text-emerald-600 font-medium text-sm">Rp 100.000</span>
                      <span className="text-xs text-muted-foreground">via cash</span>
                    </div>
                    <Button variant="outline" size="sm" className="w-full mt-2 h-7 text-xs">
                      <Eye className="mr-2 h-3 w-3" /> Detail
                    </Button>
                  </div>
                </div>
              )}

              <div className="pt-2">
                <h4 className="text-sm font-bold mb-3">Daftar Layanan</h4>
                <div className="space-y-2">
                  {services.map((service) => (
                    <div 
                      key={service.id} 
                      className="flex justify-between items-center p-2 border rounded-md bg-white hover:bg-slate-50 cursor-pointer transition-colors"
                      onClick={() => setSelectedService(service.id)}
                    >
                      <span className="text-sm font-medium">{service.name}</span>
                      <span className="text-sm text-emerald-600 font-medium">{formatCurrency(service.price)}</span>
                    </div>
                  ))}
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Center Column (5 cols) */}
        <div className="lg:col-span-5 space-y-6">
          {/* Tambah Item Tagihan */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="flex items-center gap-2 text-base">
                <Plus className="h-4 w-4" />
                Tambah Item Tagihan
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <label className="text-sm font-medium text-muted-foreground">Pilih Layanan</label>
                <Select value={selectedService} onValueChange={setSelectedService}>
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih layanan..." />
                  </SelectTrigger>
                  <SelectContent>
                    {services.map((s) => (
                      <SelectItem key={s.id} value={s.id}>
                        {s.name} - {formatCurrency(s.price)}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <label className="text-sm font-medium text-muted-foreground">Jumlah</label>
                  <Input 
                    type="number"
                    value={quantity}
                    onChange={(e) => setQuantity(e.target.value)}
                  />
                </div>
                <div className="space-y-2">
                  <label className="text-sm font-medium text-muted-foreground">Harga Satuan</label>
                  <Input 
                    type="number"
                    placeholder={selectedService ? services.find(s => s.id === selectedService)?.price.toString() : ""}
                    value={customPrice}
                    onChange={(e) => setCustomPrice(e.target.value)}
                  />
                </div>
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium text-muted-foreground">Keterangan</label>
                <Textarea 
                  placeholder="Keterangan tambahan (opsional)" 
                  value={notes}
                  onChange={(e) => setNotes(e.target.value)}
                  className="resize-none"
                />
              </div>

              <Button 
                className="bg-emerald-500 hover:bg-emerald-600 text-white"
                onClick={handleAddItem}
              >
                <Plus className="mr-2 h-4 w-4" />
                Tambah ke Tagihan
              </Button>
            </CardContent>
          </Card>

          {/* Detail Tagihan */}
          <Card className="min-h-[300px]">
            <CardHeader className="pb-3 border-b">
              <CardTitle className="flex items-center gap-2 text-base">
                <FileText className="h-4 w-4" />
                Detail Tagihan
              </CardTitle>
              {selectedPatientData && (
                <CardDescription>
                  Tagihan untuk: {selectedPatientData.nm_pasien} - {format(new Date(), 'dd/MM/yyyy')}
                </CardDescription>
              )}
            </CardHeader>
            <CardContent className="pt-6">
              {billItems.length === 0 ? (
                <div className="flex flex-col items-center justify-center py-8 text-center text-muted-foreground">
                  <Receipt className="h-12 w-12 opacity-20 mb-3" />
                  <p className="font-medium">Belum ada item yang ditambahkan</p>
                  <p className="text-xs">Pilih layanan dari form di atas untuk menambahkan ke tagihan</p>
                </div>
              ) : (
                <div className="space-y-3">
                  {billItems.map((item, index) => (
                    <div key={item.id} className="flex items-center justify-between p-3 border rounded-lg bg-white">
                      <div>
                        <p className="font-medium">{item.name}</p>
                        <p className="text-xs text-muted-foreground">
                          {item.quantity} x {formatCurrency(item.price)}
                        </p>
                      </div>
                      <div className="flex items-center gap-3">
                        <span className="font-medium text-emerald-600">
                          {formatCurrency(item.total)}
                        </span>
                        <Button 
                          variant="ghost" 
                          size="icon" 
                          className="h-8 w-8 text-red-500 hover:text-red-600 hover:bg-red-50"
                          onClick={() => handleRemoveItem(index)}
                        >
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </div>
                    </div>
                  ))}
                  
                  <div className="mt-6 pt-4 border-t flex justify-between items-center">
                    <span className="font-bold text-lg">Total Tagihan</span>
                    <span className="font-bold text-xl text-emerald-600">
                      {formatCurrency(billItems.reduce((acc, item) => acc + item.total, 0))}
                    </span>
                  </div>
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Right Column (4 cols) */}
        <div className="lg:col-span-4 space-y-6">
          {/* Tagihan Terbaru */}
          <Card className="h-full">
            <CardHeader className="pb-3">
              <CardTitle className="flex items-center gap-2 text-base">
                <FileText className="h-4 w-4" />
                Tagihan Terbaru
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <label className="text-xs font-medium text-muted-foreground">Filter Periode</label>
                <div className="flex gap-2">
                  <Button
                    variant={"outline"}
                    className="w-full justify-start text-left font-normal h-9 text-xs"
                  >
                    <CalendarIcon className="mr-2 h-3 w-3" />
                    {format(date.from, "dd MMM yyyy")}
                  </Button>
                  <Button
                    variant={"outline"}
                    className="w-full justify-start text-left font-normal h-9 text-xs"
                  >
                    <CalendarIcon className="mr-2 h-3 w-3" />
                    {format(date.to, "dd MMM yyyy")}
                  </Button>
                </div>
              </div>

              <div className="space-y-3">
                {invoices.map((inv) => (
                  <div key={inv.id} className="p-3 border rounded-lg bg-white space-y-2">
                    <div className="flex justify-between items-start">
                      <span className="font-bold text-sm">{inv.id}</span>
                      <Badge className="bg-emerald-500 hover:bg-emerald-600 text-[10px] h-5">{inv.status}</Badge>
                    </div>
                    <div>
                      <p className="font-medium text-sm">{inv.patient}</p>
                      <p className="text-xs text-muted-foreground">{inv.date}</p>
                    </div>
                    <div className="flex justify-between items-center pt-1">
                      <span className="text-emerald-600 font-medium text-sm">{formatCurrency(inv.amount)}</span>
                      <span className="text-xs text-muted-foreground">via {inv.method}</span>
                    </div>
                    <div className="grid grid-cols-2 gap-2 pt-1">
                      <Button variant="outline" size="sm" className="h-8 text-xs">
                        <Eye className="mr-2 h-3 w-3" /> Lihat Detail
                      </Button>
                      <Button size="sm" className="h-8 text-xs bg-emerald-500 hover:bg-emerald-600">
                        <CheckCircle2 className="mr-2 h-3 w-3" /> Proses
                      </Button>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
};

// Re-use Receipt icon as fallback if not imported
function Receipt(props: any) {
  return (
    <svg
      {...props}
      xmlns="http://www.w3.org/2000/svg"
      width="24"
      height="24"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" />
      <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
      <path d="M12 17V7" />
    </svg>
  )
}

export default Billing;
