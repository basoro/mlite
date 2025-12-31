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
  AlertCircle,
  Clock,
  Printer,
  X,
  Pencil,
  Check
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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogFooter,
  DialogClose
} from "@/components/ui/dialog";

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

const Kasir: React.FC = () => {
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
  
  // Modal State
  const [selectedInvoice, setSelectedInvoice] = useState<any>(null);
  const [isDetailOpen, setIsDetailOpen] = useState(false);
  const [isEditMode, setIsEditOpen] = useState(false);
  const [editItems, setEditItems] = useState<BillItem[]>([]);

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

  const handleViewDetail = (invoice: any) => {
    setSelectedInvoice(invoice);
    // Initialize mock edit items based on invoice amount or some logic
    // For demo purposes, we'll create a few mock items
    setEditItems([
      { id: '1', name: 'Scaling', price: 200000, quantity: 1, total: 200000 },
      { id: '2', name: 'Tambal Gigi', price: 150000, quantity: 1, total: 150000 },
    ]);
    setIsDetailOpen(true);
    setIsEditOpen(false);
  };

  const handleEditClick = () => {
    setIsEditOpen(true);
  };

  const handleAddEditItem = () => {
    const newItem: BillItem = {
      id: Math.random().toString(36).substr(2, 9),
      name: "",
      price: 0,
      quantity: 1,
      total: 0
    };
    setEditItems([...editItems, newItem]);
  };

  const handleUpdateEditItem = (index: number, field: keyof BillItem, value: any) => {
    const newItems = [...editItems];
    const item = newItems[index];
    
    if (field === 'quantity' || field === 'price') {
      const val = parseInt(value) || 0;
      (item as any)[field] = val;
      item.total = item.quantity * item.price;
    } else {
      (item as any)[field] = value;
    }
    
    setEditItems(newItems);
  };

  const handleRemoveEditItem = (index: number) => {
    const newItems = [...editItems];
    newItems.splice(index, 1);
    setEditItems(newItems);
  };

  const handleSaveEdit = () => {
    // Logic to save changes to backend would go here
    setIsEditOpen(false);
    toast({
      title: "Berhasil",
      description: "Perubahan tagihan disimpan",
    });
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    }).format(amount);
  };

  const handlePrintReceipt = () => {
    if (!selectedInvoice) return;

    const printWindow = window.open('', '', 'width=800,height=600');
    if (!printWindow) return;

    const itemsHtml = editItems.map(item => {
      let type = "Tindakan";
      if (item.name.toLowerCase().includes('konsultasi')) type = "Konsultasi";
      else if (item.name.toLowerCase().includes('administrasi')) type = "Administrasi";
      else if (item.name.toLowerCase().includes('obat')) type = "Obat";

      return `
      <tr>
        <td style="padding: 4px; border: 1px solid #000;">${type}</td>
        <td style="padding: 4px; border: 1px solid #000;">${item.name}</td>
        <td style="padding: 4px; border: 1px solid #000; text-align: center;">${item.quantity}</td>
        <td style="padding: 4px; border: 1px solid #000; text-align: right;">${formatCurrency(item.price)}</td>
        <td style="padding: 4px; border: 1px solid #000; text-align: right;">${formatCurrency(item.total)}</td>
      </tr>
    `}).join('');

    const total = editItems.reduce((acc, item) => acc + item.total, 0);
    const currentDate = format(new Date(), 'dd/MM/yyyy, HH.mm.ss');

    const htmlContent = `
      <!DOCTYPE html>
      <html>
      <head>
        <title>Struk Pembayaran - ${selectedInvoice.id}</title>
        <style>
          body { font-family: 'Courier New', Courier, monospace; color: #000; max-width: 800px; margin: 0 auto; padding: 20px; }
          .header { text-align: center; margin-bottom: 20px; }
          .header h1 { margin: 0; font-size: 24px; font-weight: bold; }
          .header p { margin: 5px 0; font-size: 14px; }
          .header h2 { margin: 10px 0; font-size: 18px; text-decoration: underline; font-weight: bold; }
          .divider { border-top: 1px solid #000; margin: 10px 0; }
          .info-table { width: 100%; margin-bottom: 15px; }
          .info-table td { padding: 2px 0; vertical-align: top; }
          .label { width: 150px; }
          .value { text-align: right; }
          .section-title { font-weight: bold; margin: 15px 0 5px 0; }
          .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 14px; }
          .items-table th { border: 1px solid #000; padding: 5px; text-align: left; font-weight: bold; }
          .items-table td { border: 1px solid #000; padding: 5px; }
          .total-section { font-weight: bold; font-size: 16px; text-align: right; margin: 15px 0; border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 10px 0; }
          .payment-box { border: 1px solid #000; padding: 10px; margin-bottom: 20px; }
          .footer { text-align: center; font-size: 12px; margin-top: 30px; }
          @media print {
            body { padding: 0; }
            .no-print { display: none; }
          }
        </style>
      </head>
      <body>
        <div class="header">
          <h1>Atila Medika</h1>
          <p>Sistem Manajemen Praktik Dokter</p>
          <h2>STRUK PEMBAYARAN</h2>
        </div>

        <div class="divider"></div>

        <table class="info-table">
          <tr>
            <td class="label">No. Transaksi:</td>
            <td class="value">${selectedInvoice.id}</td>
          </tr>
          <tr>
            <td class="label">Tanggal:</td>
            <td class="value">${selectedInvoice.date}</td>
          </tr>
          <tr>
            <td class="label">Kasir:</td>
            <td class="value">Kasir 1</td>
          </tr>
        </table>

        <div class="section-title">Data Pasien:</div>
        <table class="info-table">
          <tr>
            <td class="label">Nama:</td>
            <td class="value">${selectedInvoice.patient}</td>
          </tr>
          <tr>
            <td class="label">No. RM:</td>
            <td class="value">000004</td>
          </tr>
          <tr>
            <td class="label">Jenis Layanan:</td>
            <td class="value">Umum</td>
          </tr>
        </table>

        <div class="section-title">Detail Tagihan:</div>
        <table class="items-table">
          <thead>
            <tr>
              <th>Jenis</th>
              <th>Nama</th>
              <th style="text-align: center; width: 50px;">Qty</th>
              <th style="text-align: right;">Harga</th>
              <th style="text-align: right;">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            ${itemsHtml}
          </tbody>
        </table>

        <div class="total-section">
          <span style="float: left;">TOTAL TAGIHAN:</span>
          ${formatCurrency(total)}
        </div>

        <div class="payment-box">
          <table style="width: 100%;">
            <tr>
              <td>Cara Bayar:</td>
              <td style="text-align: right;">${selectedInvoice.method}</td>
            </tr>
            <tr>
              <td>Status:</td>
              <td style="text-align: right;">${selectedInvoice.status}</td>
            </tr>
            <tr>
              <td>Catatan:</td>
              <td style="text-align: right;">-</td>
            </tr>
          </table>
        </div>

        <div class="divider"></div>

        <div class="footer">
          <p>Terima kasih atas kunjungan Anda</p>
          <p>Semoga lekas sembuh</p>
          <p>Dicetak pada: ${currentDate}</p>
        </div>

        <script>
          window.onload = function() { window.print(); }
        </script>
      </body>
      </html>
    `;

    printWindow.document.write(htmlContent);
    printWindow.document.close();
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col space-y-2">
        <h1 className="text-3xl font-bold tracking-tight text-foreground">Kasir & Pembayaran</h1>
        <p className="text-muted-foreground">
          Kelola pembayaran pasien dan cetak struk
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* ... (Previous Left and Center Columns remain unchanged) ... */}
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
                  <Popover>
                    <PopoverTrigger asChild>
                      <Button
                        variant={"outline"}
                        className="w-full justify-start text-left font-normal h-9 text-xs"
                      >
                        <CalendarIcon className="mr-2 h-3 w-3" />
                        {format(date.from, "dd MMM yyyy")}
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
                        className="w-full justify-start text-left font-normal h-9 text-xs"
                      >
                        <CalendarIcon className="mr-2 h-3 w-3" />
                        {format(date.to, "dd MMM yyyy")}
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
                      <Button 
                        variant="outline" 
                        size="sm" 
                        className="h-8 text-xs"
                        onClick={() => handleViewDetail(inv)}
                      >
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

      {/* Invoice Detail Modal */}
      <Dialog open={isDetailOpen} onOpenChange={setIsDetailOpen}>
        <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
          <DialogHeader className="flex flex-row items-start justify-between space-y-0 pb-2 border-b mb-4">
            <div className="space-y-1">
              {!isEditMode ? (
                <>
                  <DialogTitle className="text-xl font-bold">Detail Tagihan {selectedInvoice?.id}</DialogTitle>
                  <DialogDescription>Informasi lengkap tagihan pasien</DialogDescription>
                </>
              ) : (
                <DialogTitle className="flex items-center gap-2 text-xl font-bold">
                  <FileText className="h-5 w-5" /> Edit Layanan
                </DialogTitle>
              )}
            </div>
          </DialogHeader>

          <div className="space-y-6">
            {/* Section 1: Informasi Pasien */}
            <Card className="border shadow-sm">
              <CardHeader className="pb-3 pt-4 px-4">
                <CardTitle className="flex items-center gap-2 text-sm font-bold">
                  <User className="h-4 w-4" /> Informasi Pasien
                </CardTitle>
              </CardHeader>
              <CardContent className="px-4 pb-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <p className="text-xs text-muted-foreground mb-1">Nama Pasien</p>
                    <p className="font-semibold">{selectedInvoice?.patient}</p>
                  </div>
                  <div>
                    <p className="text-xs text-muted-foreground mb-1">ID Pasien</p>
                    <p className="font-mono text-sm text-muted-foreground">a4be2260-8108-4d83-8536-b76e70746b6f</p>
                  </div>
                  <div>
                    <p className="text-xs text-muted-foreground mb-1">Jenis Asuransi</p>
                    <p className="font-semibold">Umum</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Section 2: Informasi Transaksi */}
            <Card className="border shadow-sm">
              <CardHeader className="pb-3 pt-4 px-4">
                <CardTitle className="flex items-center gap-2 text-sm font-bold">
                  <Clock className="h-4 w-4" /> Informasi Transaksi
                </CardTitle>
              </CardHeader>
              <CardContent className="px-4 pb-4">
                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-4">
                    <div>
                      <p className="text-xs text-muted-foreground mb-1">No. Invoice</p>
                      <p className="font-semibold">{selectedInvoice?.id}</p>
                    </div>
                    <div>
                      <p className="text-xs text-muted-foreground mb-1">Waktu</p>
                      <p className="font-semibold">17:03:00</p>
                    </div>
                    <div>
                      <p className="text-xs text-muted-foreground mb-1">Status</p>
                      <Badge className="bg-emerald-500 hover:bg-emerald-600">{selectedInvoice?.status}</Badge>
                    </div>
                  </div>
                  <div className="space-y-4">
                    <div>
                      <p className="text-xs text-muted-foreground mb-1">Tanggal</p>
                      <p className="font-semibold">{selectedInvoice?.date}</p>
                    </div>
                    <div>
                      <p className="text-xs text-muted-foreground mb-1">Kasir</p>
                      <p className="font-semibold">Kasir 1</p>
                    </div>
                    <div>
                      <p className="text-xs text-muted-foreground mb-1">Metode Pembayaran</p>
                      <p className="font-semibold">{selectedInvoice?.method}</p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            {!isEditMode ? (
              <>
                {/* Section 3: Detail Layanan (View Mode) */}
                <Card className="border shadow-sm">
                  <CardHeader className="pb-3 pt-4 px-4 flex flex-row items-center justify-between space-y-0">
                    <CardTitle className="flex items-center gap-2 text-sm font-bold">
                      <FileText className="h-4 w-4" /> Detail Layanan
                    </CardTitle>
                    <Button variant="outline" size="sm" className="h-7 text-xs" onClick={handleEditClick}>
                      <Pencil className="mr-1 h-3 w-3" /> Edit
                    </Button>
                  </CardHeader>
                  <CardContent className="px-4 pb-4 space-y-4">
                    {editItems.map((item, index) => (
                      <div key={index} className="flex justify-between items-start border-b pb-3">
                        <div>
                          <p className="font-semibold">{item.name}</p>
                          <p className="text-xs text-muted-foreground mt-1">Qty: {item.quantity}  Harga: {formatCurrency(item.price)}</p>
                        </div>
                        <p className="font-bold text-emerald-600">{formatCurrency(item.total)}</p>
                      </div>
                    ))}

                    <div className="flex justify-between items-center pt-2">
                      <span className="font-bold text-lg">Total Tagihan:</span>
                      <span className="font-bold text-xl text-emerald-600">
                        {formatCurrency(editItems.reduce((acc, item) => acc + item.total, 0))}
                      </span>
                    </div>
                  </CardContent>
                </Card>

                {/* Section 4: Catatan */}
                <Card className="border shadow-sm">
                  <CardHeader className="pb-3 pt-4 px-4">
                    <CardTitle className="text-sm font-bold">Catatan</CardTitle>
                  </CardHeader>
                  <CardContent className="px-4 pb-4">
                    <p className="text-sm text-muted-foreground">-</p>
                  </CardContent>
                </Card>

                <DialogFooter className="flex justify-between items-center mt-6 border-t pt-4">
                  <Button 
                    className="bg-emerald-500 hover:bg-emerald-600 text-white mr-auto"
                    onClick={handlePrintReceipt}
                  >
                    <Printer className="mr-2 h-4 w-4" /> Cetak Ulang Struk
                  </Button>
                  <Button variant="outline" onClick={() => setIsDetailOpen(false)}>
                    <X className="mr-2 h-4 w-4" /> Tutup
                  </Button>
                </DialogFooter>
              </>
            ) : (
              // EDIT MODE
              <>
                <div className="space-y-4">
                  {editItems.map((item, index) => (
                    <Card key={index} className="border shadow-sm p-4 relative">
                      <div className="grid grid-cols-12 gap-4">
                        <div className="col-span-12">
                          <Input 
                            value={item.name} 
                            onChange={(e) => handleUpdateEditItem(index, 'name', e.target.value)}
                            placeholder="Nama Layanan"
                            className="font-medium"
                          />
                        </div>
                        <div className="col-span-6">
                          <label className="text-xs font-bold mb-1 block">Quantity</label>
                          <Input 
                            type="number"
                            value={item.quantity}
                            onChange={(e) => handleUpdateEditItem(index, 'quantity', e.target.value)}
                          />
                        </div>
                        <div className="col-span-6">
                          <label className="text-xs font-bold mb-1 block">Harga</label>
                          <Input 
                            type="number"
                            value={item.price}
                            onChange={(e) => handleUpdateEditItem(index, 'price', e.target.value)}
                          />
                        </div>
                      </div>
                      <div className="absolute top-4 right-4 text-emerald-600 font-bold">
                        {formatCurrency(item.total)}
                      </div>
                      <Button
                        variant="ghost"
                        size="icon"
                        className="absolute bottom-4 right-4 text-red-500 hover:text-red-600 hover:bg-red-50 h-8 w-8"
                        onClick={() => handleRemoveEditItem(index)}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </Card>
                  ))}

                  <Button 
                    variant="outline" 
                    className="w-full border-dashed border-2 py-6 text-muted-foreground hover:text-foreground hover:border-solid"
                    onClick={handleAddEditItem}
                  >
                    <Plus className="mr-2 h-4 w-4" /> Tambah Item
                  </Button>

                  <div className="flex justify-between items-center pt-4 border-t">
                    <span className="font-bold text-lg">Total Tagihan:</span>
                    <span className="font-bold text-xl text-emerald-600">
                      {formatCurrency(editItems.reduce((acc, item) => acc + item.total, 0))}
                    </span>
                  </div>

                  <div className="space-y-2">
                    <label className="text-sm font-bold">Catatan</label>
                    <Textarea placeholder="Tambahkan catatan..." className="resize-none" />
                  </div>
                </div>

                <DialogFooter className="flex justify-between items-center mt-6 border-t pt-4">
                  <Button 
                    className="bg-emerald-500 hover:bg-emerald-600 text-white w-1/2 mr-2"
                    onClick={handleSaveEdit}
                  >
                    <Check className="mr-2 h-4 w-4" /> Simpan Perubahan
                  </Button>
                  <Button 
                    variant="outline" 
                    className="w-1/2 ml-2"
                    onClick={() => setIsEditOpen(false)}
                  >
                    <X className="mr-2 h-4 w-4" /> Batal
                  </Button>
                </DialogFooter>
              </>
            )}
          </div>
        </DialogContent>
      </Dialog>
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

export default Kasir;
