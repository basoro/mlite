import { useState, useMemo } from "react";
import { format, subMonths, startOfMonth, endOfMonth, parseISO, eachDayOfInterval, isSameDay } from "date-fns";
import { id } from "date-fns/locale";
import { useQuery } from "@tanstack/react-query";
import { getRawatJalanList, getStockMovementList, getGudangBarangList, GudangBarang, RiwayatBarang } from "@/lib/api";
import { cn } from "@/lib/utils";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  CardFooter,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";
import { Calendar } from "@/components/ui/calendar";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import {
  Calendar as CalendarIcon,
  BarChart3,
  Users,
  DollarSign,
  Pill,
  Activity,
  Download,
  Printer,
  TrendingUp,
  Package,
  AlertTriangle,
  AlertCircle,
  FileText
} from "lucide-react";

export default function Laporan() {
  const [date, setDate] = useState<{ from: Date; to: Date }>({
    from: subMonths(new Date(), 1),
    to: new Date(),
  });
  const [period, setPeriod] = useState("1bulan");

  // Fetch Data Kunjungan
  const { data: kunjunganData, isLoading: isLoadingKunjungan } = useQuery({
    queryKey: ['laporan-kunjungan', date.from, date.to],
    queryFn: () => getRawatJalanList(
      format(date.from, 'yyyy-MM-dd'),
      format(date.to, 'yyyy-MM-dd'),
      0,
      10000 // Fetch all for report (limit 10000)
    )
  });

  // Fetch Data Resep (Riwayat Barang Medis)
  const { data: resepData, isLoading: isLoadingResep } = useQuery({
    queryKey: ['laporan-resep', date.from, date.to],
    queryFn: () => getStockMovementList(
      1,
      10000,
      '',
      format(date.from, 'yyyy-MM-dd'),
      format(date.to, 'yyyy-MM-dd')
    )
  });

  // Fetch Data Inventory (Gudang Barang)
  const { data: inventoryData, isLoading: isLoadingInventory } = useQuery({
    queryKey: ['laporan-inventory'],
    queryFn: () => getGudangBarangList(1, 10000)
  });

  // Process Data
  const stats = useMemo(() => {
    // Kunjungan Stats
    const rawatJalanList = kunjunganData?.data || [];
    const totalKunjungan = rawatJalanList.length;
    
    // Group visits by date
    const visitsByDate = rawatJalanList.reduce((acc: any, curr: any) => {
      const date = curr.tgl_registrasi;
      acc[date] = (acc[date] || 0) + 1;
      return acc;
    }, {});

    const visitTrend = Object.keys(visitsByDate).map(date => ({
      date: format(parseISO(date), 'dd/MM/yyyy'),
      count: visitsByDate[date],
      revenue: 0 // Revenue data might need separate calculation or field
    })).sort((a, b) => new Date(a.date.split('/').reverse().join('-')).getTime() - new Date(b.date.split('/').reverse().join('-')).getTime());

    // Patient Stats (by Insurance/Penyamin)
    const patientStatsObj = rawatJalanList.reduce((acc: any, curr: any) => {
      const type = curr.kd_pj === 'UMU' || curr.kd_pj === '-' ? 'Pasien Umum' : 'Pasien BPJS/Asuransi';
      acc[type] = (acc[type] || 0) + 1;
      return acc;
    }, {});
    
    const patientStats = [
      { label: "Total Pasien", value: totalKunjungan, color: "bg-emerald-500" },
      { label: "Pasien BPJS/Asuransi", value: patientStatsObj['Pasien BPJS/Asuransi'] || 0, color: "bg-blue-500" },
      { label: "Pasien Umum", value: patientStatsObj['Pasien Umum'] || 0, color: "bg-emerald-500" },
    ];

    // Resep Stats
    const stockMovements: RiwayatBarang[] = resepData?.data || [];
    // Filter for outgoing items (sales/usage) - assuming 'Keluar' or similar status logic
    // Usually 'masuk' > 0 is in, 'keluar' > 0 is out.
    const salesItems = stockMovements.filter(item => parseFloat(item.keluar) > 0);
    const totalObatTerjual = salesItems.reduce((acc, item) => acc + parseFloat(item.keluar), 0);
    
    // Group by medicine name
    const salesByName = salesItems.reduce((acc: any, curr) => {
      const name = curr.nama_brng || curr.kode_brng;
      if (!acc[name]) {
        acc[name] = { name, sold: 0, revenue: 0 };
      }
      acc[name].sold += parseFloat(curr.keluar);
      // Revenue calculation needs price. Assuming we don't have it in riwayat, we skip or estimate.
      // If we had h_beli or h_jual joined, we could use it.
      return acc;
    }, {});
    
    const medicineSales = Object.values(salesByName)
      .sort((a: any, b: any) => b.sold - a.sold)
      .slice(0, 5); // Top 5

    // Inventory Stats
    const gudangItems: GudangBarang[] = inventoryData?.data || [];
    const totalInventoryValue = gudangItems.reduce((acc, item) => {
      return acc + (parseFloat(item.stok) * parseFloat(item.h_beli || '0'));
    }, 0);

    const stockStatus = {
      total: gudangItems.length,
      available: gudangItems.filter(i => parseFloat(i.stok) > 10).length, // Arbitrary threshold
      low: gudangItems.filter(i => parseFloat(i.stok) > 0 && parseFloat(i.stok) <= 10).length,
      empty: gudangItems.filter(i => parseFloat(i.stok) <= 0).length,
    };

    // KPI Data
    const kpiData = [
      {
        title: "Total Kunjungan",
        value: totalKunjungan.toString(),
        note: "Periode ini",
        icon: Users,
        color: "text-emerald-500",
        bg: "bg-emerald-50",
      },
      {
        title: "Total Pendapatan",
        value: "Rp -", // Placeholder as we don't have full financial data
        note: `Periode ${format(date.from, "dd/MM/yyyy")} s/d ${format(date.to, "dd/MM/yyyy")}`,
        icon: DollarSign,
        color: "text-emerald-500",
        bg: "bg-emerald-50",
      },
      {
        title: "Resep Obat",
        value: totalObatTerjual.toString(),
        note: "Item obat keluar",
        icon: Pill,
        color: "text-blue-500",
        bg: "bg-blue-50",
      },
      {
        title: "Rata-rata/Hari",
        value: Math.round(totalKunjungan / (eachDayOfInterval({ start: date.from, end: date.to }).length || 1)).toString(),
        note: "Pasien per hari",
        icon: Activity,
        color: "text-orange-500",
        bg: "bg-orange-50",
      },
    ];

    return {
      kpiData,
      visitTrend,
      patientStats,
      medicineSales,
      totalObatTerjual,
      totalInventoryValue,
      stockStatus,
      gudangItems
    };
  }, [kunjunganData, resepData, inventoryData, date]);

  // Mock data for things we can't fully calculate yet
  const topDiseases = [
    { name: "Data Belum Tersedia", cases: 0, percentage: 0 },
  ];
  
  const inventoryCategory = [
    { name: "Semua Kategori", items: stats.stockStatus.total, value: stats.totalInventoryValue, percentage: 100 },
  ];

  return (
    <div className="container mx-auto p-6 space-y-6">
      <div className="flex flex-col space-y-2">
        <h1 className="text-3xl font-bold tracking-tight">Laporan & Analisis</h1>
        <p className="text-muted-foreground">
          Laporan kunjungan, resep, keuangan dan analisis data klinik
        </p>
      </div>

      {/* Filter Section */}
      <Card>
        <CardHeader className="pb-3">
          <CardTitle className="flex items-center gap-2 text-base">
            <CalendarIcon className="h-4 w-4" />
            Filter Periode Laporan
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex flex-col md:flex-row gap-4 items-end">
            <div className="space-y-2 flex-1">
              <label className="text-sm font-medium text-muted-foreground">Dari Tanggal</label>
              <Popover>
                <PopoverTrigger asChild>
                  <Button
                    variant={"outline"}
                    className={cn(
                      "w-full justify-start text-left font-normal",
                      !date.from && "text-muted-foreground"
                    )}
                  >
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {date.from ? format(date.from, "dd/MM/yyyy") : <span>Pick a date</span>}
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
            </div>
            <div className="space-y-2 flex-1">
              <label className="text-sm font-medium text-muted-foreground">Sampai Tanggal</label>
              <Popover>
                <PopoverTrigger asChild>
                  <Button
                    variant={"outline"}
                    className={cn(
                      "w-full justify-start text-left font-normal",
                      !date.to && "text-muted-foreground"
                    )}
                  >
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {date.to ? format(date.to, "dd/MM/yyyy") : <span>Pick a date</span>}
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
            <div className="space-y-2 flex-1">
              <label className="text-sm font-medium text-muted-foreground">Periode Cepat</label>
              <Select value={period} onValueChange={setPeriod}>
                <SelectTrigger>
                  <SelectValue placeholder="Pilih periode" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="hari_ini">Hari Ini</SelectItem>
                  <SelectItem value="7hari">7 Hari Terakhir</SelectItem>
                  <SelectItem value="1bulan">1 Bulan Terakhir</SelectItem>
                  <SelectItem value="3bulan">3 Bulan Terakhir</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <Button className="bg-gradient-to-r from-emerald-500 to-cyan-500 text-white hover:from-emerald-600 hover:to-cyan-600 w-full md:w-auto">
              <BarChart3 className="mr-2 h-4 w-4" /> Update Laporan
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Tabs Section */}
      <Tabs defaultValue="overview" className="space-y-4">
        <TabsList className="w-full justify-start h-auto p-1 bg-transparent border-b rounded-none space-x-6">
          <TabsTrigger 
            value="overview" 
            className="rounded-none border-b-2 border-transparent data-[state=active]:border-emerald-500 data-[state=active]:bg-transparent px-0 pb-2"
          >
            Overview
          </TabsTrigger>
          <TabsTrigger 
            value="kunjungan" 
            className="rounded-none border-b-2 border-transparent data-[state=active]:border-emerald-500 data-[state=active]:bg-transparent px-0 pb-2"
          >
            Kunjungan
          </TabsTrigger>
          <TabsTrigger 
            value="resep" 
            className="rounded-none border-b-2 border-transparent data-[state=active]:border-emerald-500 data-[state=active]:bg-transparent px-0 pb-2"
          >
            Resep
          </TabsTrigger>
          <TabsTrigger 
            value="inventory" 
            className="rounded-none border-b-2 border-transparent data-[state=active]:border-emerald-500 data-[state=active]:bg-transparent px-0 pb-2"
          >
            Inventory
          </TabsTrigger>
          <TabsTrigger 
            value="keuangan" 
            className="rounded-none border-b-2 border-transparent data-[state=active]:border-emerald-500 data-[state=active]:bg-transparent px-0 pb-2"
          >
            Keuangan
          </TabsTrigger>
        </TabsList>

        {/* OVERVIEW TAB */}
        <TabsContent value="overview" className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            {stats.kpiData.map((kpi, index) => (
              <Card key={index}>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium text-muted-foreground">
                    {kpi.title}
                  </CardTitle>
                  <kpi.icon className={cn("h-4 w-4", kpi.color)} />
                </CardHeader>
                <CardContent>
                  <div className={cn("text-2xl font-bold", kpi.color)}>{kpi.value}</div>
                  <p className="text-xs text-muted-foreground mt-1">{kpi.note}</p>
                </CardContent>
              </Card>
            ))}
          </div>

          <Card>
            <CardHeader>
              <CardTitle>10 Penyakit Terbanyak</CardTitle>
              <CardDescription>Diagnosa yang paling sering ditemukan periode ini</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {topDiseases.map((item, index) => (
                <div key={index} className="space-y-2">
                  <div className="flex items-center justify-between text-sm">
                    <span className="font-medium">{item.name}</span>
                    <div className="flex items-center gap-2">
                      <span className="text-muted-foreground">{item.cases} kasus</span>
                      <Badge variant="secondary" className="text-xs">{item.percentage}%</Badge>
                    </div>
                  </div>
                  <Progress value={item.percentage} className="h-2 bg-slate-100" indicatorClassName="bg-emerald-500" />
                </div>
              ))}
            </CardContent>
          </Card>
        </TabsContent>

        {/* KUNJUNGAN TAB */}
        <TabsContent value="kunjungan" className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Trend Kunjungan Harian</CardTitle>
                <CardDescription>Jumlah kunjungan pasien per hari</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                {stats.visitTrend.length > 0 ? (
                  stats.visitTrend.map((item, index) => (
                    <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
                      <div>
                        <p className="font-semibold">{item.date}</p>
                        <p className="text-sm text-muted-foreground">{item.count} kunjungan</p>
                      </div>
                      <div className="text-right">
                        <p className="text-emerald-500 font-medium">Rp {item.revenue}</p>
                        <p className="text-xs text-muted-foreground">pendapatan</p>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center text-muted-foreground py-4">Tidak ada data kunjungan</div>
                )}
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Statistik Pasien</CardTitle>
                <CardDescription>Breakdown data pasien periode ini</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                {stats.patientStats.map((stat, i) => (
                  <div key={i} className="flex items-center justify-between py-2 border-b last:border-0">
                    <span className="font-medium">{stat.label}</span>
                    <Badge className={cn("hover:bg-opacity-80", stat.color)}>{stat.value}</Badge>
                  </div>
                ))}
              </CardContent>
            </Card>
          </div>
          
          <div className="flex gap-2">
            <Button className="bg-emerald-500 hover:bg-emerald-600">
              <Download className="mr-2 h-4 w-4" /> Download Laporan Kunjungan
            </Button>
            <Button variant="outline">
              <Printer className="mr-2 h-4 w-4" /> Cetak Laporan
            </Button>
          </div>
        </TabsContent>

        {/* RESEP TAB */}
        <TabsContent value="resep" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Laporan Penjualan Obat</CardTitle>
              <CardDescription>Obat yang paling banyak diresepkan dan terjual</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {stats.medicineSales.length > 0 ? (
                  stats.medicineSales.map((item: any, index: number) => (
                    <div key={index} className="flex items-center justify-between py-3 border-b">
                      <div>
                        <p className="font-semibold">{item.name}</p>
                        <p className="text-sm text-muted-foreground">{item.sold} unit terjual</p>
                      </div>
                      <div className="text-right">
                        <p className="text-emerald-500 font-medium">Rp {item.revenue.toLocaleString('id-ID')}</p>
                        <p className="text-xs text-muted-foreground">pendapatan</p>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center text-muted-foreground py-4">Tidak ada data penjualan obat</div>
                )}
              </div>
            </CardContent>
            <CardFooter className="bg-slate-50 p-4 rounded-b-lg flex justify-between items-center">
              <span className="font-bold">Total Penjualan Obat:</span>
              <span className="font-bold text-emerald-500 text-lg">Rp -</span>
            </CardFooter>
          </Card>

          <div className="flex gap-2">
            <Button className="bg-emerald-500 hover:bg-emerald-600">
              <Download className="mr-2 h-4 w-4" /> Download Laporan Resep
            </Button>
            <Button variant="outline">
              <Printer className="mr-2 h-4 w-4" /> Cetak Laporan
            </Button>
          </div>
        </TabsContent>

        {/* INVENTORY TAB */}
        <TabsContent value="inventory" className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Nilai Inventory per Kategori</CardTitle>
                <CardDescription>Breakdown nilai stok berdasarkan kategori obat</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                {inventoryCategory.map((cat, index) => (
                  <div key={index} className="flex items-center justify-between py-2 border-b last:border-0">
                    <div>
                      <p className="font-semibold">{cat.name}</p>
                      <p className="text-xs text-muted-foreground">{cat.items} item</p>
                    </div>
                    <div className="text-right">
                      <p className="text-emerald-500 font-medium">Rp {cat.value.toLocaleString('id-ID')}</p>
                      <p className="text-xs text-muted-foreground">{cat.percentage}% dari total</p>
                    </div>
                  </div>
                ))}
              </CardContent>
              <CardFooter className="bg-slate-50 p-4 rounded-b-lg flex justify-between items-center">
                <span className="font-bold">Total Nilai Inventory:</span>
                <span className="font-bold text-emerald-500">Rp {stats.totalInventoryValue.toLocaleString('id-ID')}</span>
              </CardFooter>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Statistik Inventory</CardTitle>
                <CardDescription>Status dan kondisi stok saat ini</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                {[
                  { label: "Total Jenis Obat", value: `${stats.stockStatus.total} item`, color: "bg-emerald-500" },
                  { label: "Stok Tersedia", value: `${stats.stockStatus.available} item`, color: "bg-emerald-500" },
                  { label: "Stok Menipis", value: `${stats.stockStatus.low} item`, color: "bg-orange-500" },
                  { label: "Habis Stok", value: `${stats.stockStatus.empty} item`, color: "bg-red-500" },
                  { label: "Kadaluarsa", value: "0 item", color: "bg-slate-500" },
                  { label: "Fast Moving Items", value: "0 item", color: "bg-slate-500" },
                  { label: "Slow Moving Items", value: "0 item", color: "bg-slate-500" },
                ].map((stat, i) => (
                  <div key={i} className="flex items-center justify-between py-2 border-b last:border-0">
                    <span className="font-medium">{stat.label}</span>
                    <Badge className={cn("hover:bg-opacity-80", stat.color)}>{stat.value}</Badge>
                  </div>
                ))}
              </CardContent>
            </Card>
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Top 10 Obat Terlaris</CardTitle>
              <CardDescription>Obat dengan pergerakan stok tertinggi periode ini</CardDescription>
            </CardHeader>
            <CardContent>
               <div className="h-20 flex items-center justify-center text-muted-foreground text-sm">
                 Belum ada data penjualan yang cukup
               </div>
            </CardContent>
          </Card>

          <div className="flex gap-2">
            <Button className="bg-emerald-500 hover:bg-emerald-600">
              <Download className="mr-2 h-4 w-4" /> Download Laporan Inventory
            </Button>
            <Button variant="outline">
              <Printer className="mr-2 h-4 w-4" /> Cetak Laporan
            </Button>
          </div>
        </TabsContent>

        {/* KEUANGAN TAB */}
        <TabsContent value="keuangan" className="space-y-4">
          <div className="grid gap-4 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Ringkasan Keuangan</CardTitle>
                <CardDescription>Overview pendapatan dan pengeluaran</CardDescription>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex justify-between">
                  <span>Pendapatan Konsultasi</span>
                  <span className="text-emerald-500 font-medium">Rp -</span>
                </div>
                <div className="flex justify-between">
                  <span>Pendapatan Obat</span>
                  <span className="text-emerald-500 font-medium">Rp -</span>
                </div>
                <div className="flex justify-between">
                  <span>Pendapatan Tindakan</span>
                  <span className="text-emerald-500 font-medium">Rp -</span>
                </div>
                <div className="my-2 border-t pt-2 flex justify-between font-bold">
                  <span>Total Pendapatan</span>
                  <span className="text-emerald-500">Rp -</span>
                </div>
                <div className="flex justify-between font-bold">
                  <span>Total Pengeluaran</span>
                  <span className="text-red-500">Rp 0</span>
                </div>
                <div className="my-2 border-t pt-2 flex justify-between font-bold text-lg">
                  <span>Keuntungan Bersih</span>
                  <span className="text-emerald-500">Rp -</span>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Trend Pendapatan</CardTitle>
                <CardDescription>Pendapatan harian dalam periode yang dipilih</CardDescription>
              </CardHeader>
              <CardContent className="h-[250px] flex items-center justify-center">
                <div className="text-center text-muted-foreground">
                  <BarChart3 className="h-10 w-10 mx-auto mb-2 opacity-20" />
                  <p>Grafik akan muncul setelah ada data transaksi</p>
                </div>
              </CardContent>
            </Card>
          </div>

          <div className="flex gap-2">
            <Button className="bg-emerald-500 hover:bg-emerald-600">
              <Download className="mr-2 h-4 w-4" /> Download Laporan Keuangan
            </Button>
            <Button variant="outline">
              <Printer className="mr-2 h-4 w-4" /> Cetak Laporan
            </Button>
          </div>
        </TabsContent>

      </Tabs>
    </div>
  );
}
