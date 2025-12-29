import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { format } from "date-fns";
import { id } from "date-fns/locale";
import { cn } from "@/lib/utils";
import { 
  getInventoryList, 
  getStockMovementList, 
  getGudangBarangList,
  DataBarang, 
  RiwayatBarang,
  GudangBarang
} from "@/lib/api";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Calendar } from "@/components/ui/calendar";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import { 
  Table, 
  TableBody, 
  TableCell, 
  TableHead, 
  TableHeader, 
  TableRow 
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { 
  Select, 
  SelectContent, 
  SelectItem, 
  SelectTrigger, 
  SelectValue 
} from "@/components/ui/select";
import { 
  Package, 
  AlertTriangle, 
  AlertCircle, 
  TrendingUp, 
  Search, 
  Download, 
  Plus,
  ShoppingCart,
  Pencil,
  Trash,
  Calendar as CalendarIcon
} from "lucide-react";

export default function Inventory() {
  const [activeTab, setActiveTab] = useState("inventory");
  const [searchQuery, setSearchQuery] = useState("");
  const [page, setPage] = useState(1);
  const [startDate, setStartDate] = useState<Date | undefined>(new Date(new Date().getFullYear(), new Date().getMonth(), 1));
  const [endDate, setEndDate] = useState<Date | undefined>(new Date());

  // Fetch Inventory Data (DataBarang)
  const { data: inventoryData, isLoading: isLoadingInventory } = useQuery({
    queryKey: ['inventory', page, searchQuery],
    queryFn: () => getInventoryList(page, 10, searchQuery),
  });

  // Fetch Stock Movement Data (RiwayatBarang)
  const { data: stockData, isLoading: isLoadingStock } = useQuery({
    queryKey: ['stockMovement', page, searchQuery, startDate, endDate],
    queryFn: () => getStockMovementList(
      page, 
      10, 
      searchQuery, 
      startDate ? format(startDate, "yyyy-MM-dd") : "", 
      endDate ? format(endDate, "yyyy-MM-dd") : ""
    ),
  });

  // Fetch Gudang Barang (for notifications and total value)
  const { data: gudangData, isLoading: isLoadingGudang } = useQuery({
    queryKey: ['gudangBarang', page], // Might want to fetch all or larger page for notifications
    queryFn: () => getGudangBarangList(1, 100), // Fetch more items for notifications
  });

  // Logic for Notifications
  const getLowStockItems = () => {
    if (!gudangData?.data || !inventoryData?.data) return [];
    
    // Create a map of DataBarang for easy lookup of stokminimal and nama_brng
    const dataBarangMap = new Map<string, DataBarang>();
    inventoryData.data.forEach((item: DataBarang) => {
      dataBarangMap.set(item.kode_brng, item);
    });

    return gudangData.data.filter((item: GudangBarang) => {
      const dataBarang = dataBarangMap.get(item.kode_brng);
      // Logic: If stok in gudang < stokminimal (if available) or < 10 (default)
      const stok = parseFloat(item.stok);
      const minStok = dataBarang ? parseFloat(dataBarang.stokminimal) : 10; 
      return stok > 0 && stok <= minStok;
    }).map((item: GudangBarang) => ({
      ...item,
      nama_brng: dataBarangMap.get(item.kode_brng)?.nama_brng || item.kode_brng,
      min_stok: dataBarangMap.get(item.kode_brng)?.stokminimal || 10
    }));
  };

  const getOutOfStockItems = () => {
    if (!gudangData?.data) return [];
    
    const dataBarangMap = new Map<string, DataBarang>();
    if (inventoryData?.data) {
        inventoryData.data.forEach((item: DataBarang) => {
            dataBarangMap.set(item.kode_brng, item);
        });
    }

    return gudangData.data.filter((item: GudangBarang) => parseFloat(item.stok) <= 0)
    .map((item: GudangBarang) => ({
        ...item,
        nama_brng: dataBarangMap.get(item.kode_brng)?.nama_brng || item.kode_brng
    }));
  };

  const getExpiredItems = () => {
    if (!inventoryData?.data) return [];
    const today = new Date();
    
    return inventoryData.data.filter((item: DataBarang) => {
      if (!item.expire) return false;
      const expireDate = new Date(item.expire);
      return expireDate < today;
    });
  };

  const lowStockList = getLowStockItems();
  const outOfStockList = getOutOfStockItems();
  const expiredList = getExpiredItems();

  // Calculate summaries
  const totalItems = inventoryData?.data?.length || 0;
  const lowStockItems = lowStockList.length;
  const expiredItems = expiredList.length;
  
  // Calculate total value based on gudangData (stok * h_beli)
  const totalValue = gudangData?.data?.reduce((acc: number, item: GudangBarang) => {
    // h_beli might come as string or number, default to 0
    const hBeli = parseFloat(item.h_beli || '0');
    const stok = parseFloat(item.stok || '0');
    return acc + (hBeli * stok);
  }, 0) || 0;

  return (
    <div className="container mx-auto p-6 space-y-6">
      <div className="flex justify-between items-start">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Inventory Obat</h1>
          <p className="text-muted-foreground">Kelola stok obat dan monitoring inventory</p>
        </div>
        <Button className="bg-emerald-500 hover:bg-emerald-600">
          <Plus className="mr-2 h-4 w-4" /> Tambah Obat
        </Button>
      </div>

      {/* Summary Cards */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Item</CardTitle>
            <Package className="h-4 w-4 text-emerald-500" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-emerald-500">{totalItems}</div>
            <p className="text-xs text-muted-foreground">Jenis obat terdaftar</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Stok Menipis</CardTitle>
            <AlertTriangle className="h-4 w-4 text-orange-500" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-orange-500">{lowStockItems}</div>
            <p className="text-xs text-muted-foreground">Perlu restock segera</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Kadaluwarsa</CardTitle>
            <AlertCircle className="h-4 w-4 text-red-500" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-red-500">{expiredItems}</div>
            <p className="text-xs text-muted-foreground">Item kadaluarsa</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Nilai Inventory</CardTitle>
            <TrendingUp className="h-4 w-4 text-emerald-500" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-emerald-500">
              Rp {totalValue.toLocaleString('id-ID')}
            </div>
            <p className="text-xs text-muted-foreground">Total nilai stok</p>
          </CardContent>
        </Card>
      </div>

      <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
        <TabsList>
          <TabsTrigger value="inventory">Inventory</TabsTrigger>
          <TabsTrigger value="pergerakan">Pergerakan Stok</TabsTrigger>
          <TabsTrigger value="notifikasi">Notifikasi</TabsTrigger>
        </TabsList>

        <TabsContent value="inventory" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Pencarian & Filter</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex flex-col md:flex-row gap-4">
                <div className="flex-1 space-y-2">
                  <label className="text-sm font-medium">Cari Obat</label>
                  <div className="relative">
                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input 
                      placeholder="Nama obat atau kategori..." 
                      className="pl-8" 
                      value={searchQuery}
                      onChange={(e) => setSearchQuery(e.target.value)}
                    />
                  </div>
                </div>
                <div className="flex-1 space-y-2">
                  <label className="text-sm font-medium">Kategori</label>
                  <Select>
                    <SelectTrigger>
                      <SelectValue placeholder="Semua Kategori" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">Semua Kategori</SelectItem>
                      <SelectItem value="antibiotik">Antibiotik</SelectItem>
                      <SelectItem value="analgesik">Analgesik</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="flex items-end">
                  <Button variant="outline" className="w-full md:w-auto">
                    <Download className="mr-2 h-4 w-4" /> Export Data
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Daftar Obat</CardTitle>
              <p className="text-sm text-muted-foreground">
                {inventoryData?.data?.length || 0} obat ditampilkan
              </p>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Nama Obat</TableHead>
                    <TableHead>Kategori</TableHead>
                    <TableHead>Stok</TableHead>
                    <TableHead>Satuan</TableHead>
                    <TableHead>Harga</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Kadaluwarsa</TableHead>
                    <TableHead>Aksi</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {isLoadingInventory ? (
                    <TableRow>
                      <TableCell colSpan={8} className="text-center">Loading...</TableCell>
                    </TableRow>
                  ) : inventoryData?.data?.map((item: DataBarang) => (
                    <TableRow key={item.kode_brng}>
                      <TableCell className="font-medium">{item.nama_brng}</TableCell>
                      <TableCell>{item.kdjns}</TableCell>
                      <TableCell>{item.stok || '0'}</TableCell>
                      <TableCell>{item.kode_sat}</TableCell>
                      <TableCell>Rp {parseInt(item.ralan).toLocaleString('id-ID')}</TableCell>
                      <TableCell>
                        <Badge variant={item.status === '1' ? 'default' : 'secondary'} className={item.status === '1' ? 'bg-emerald-500 hover:bg-emerald-600' : ''}>
                          {item.status === '1' ? 'Tersedia' : 'Tidak Aktif'}
                        </Badge>
                      </TableCell>
                      <TableCell>{item.expire}</TableCell>
                      <TableCell>
                        <div className="flex gap-2">
                          <Button variant="ghost" size="icon" className="h-8 w-8">
                            <ShoppingCart className="h-4 w-4" />
                          </Button>
                          <Button variant="ghost" size="icon" className="h-8 w-8">
                            <Pencil className="h-4 w-4" />
                          </Button>
                          <Button variant="ghost" size="icon" className="h-8 w-8 text-red-500">
                            <Trash className="h-4 w-4" />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                  {!isLoadingInventory && (!inventoryData?.data || inventoryData.data.length === 0) && (
                    <TableRow>
                      <TableCell colSpan={8} className="text-center text-muted-foreground">
                        Tidak ada data obat
                      </TableCell>
                    </TableRow>
                  )}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="pergerakan" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Filter Pergerakan Stok</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div className="space-y-2">
                  <label className="text-sm font-medium">Periode Mulai</label>
                  <Popover>
                    <PopoverTrigger asChild>
                      <Button
                        variant={"outline"}
                        className={cn(
                          "w-full justify-start text-left font-normal pl-3",
                          !startDate && "text-muted-foreground"
                        )}
                      >
                        <CalendarIcon className="mr-2 h-4 w-4" />
                        {startDate ? format(startDate, "dd MMMM yyyy", { locale: id }) : <span>Pilih Tanggal</span>}
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-auto p-0" align="start">
                      <Calendar
                        mode="single"
                        selected={startDate}
                        onSelect={setStartDate}
                        initialFocus
                      />
                    </PopoverContent>
                  </Popover>
                </div>
                <div className="space-y-2">
                  <label className="text-sm font-medium">Periode Selesai</label>
                  <Popover>
                    <PopoverTrigger asChild>
                      <Button
                        variant={"outline"}
                        className={cn(
                          "w-full justify-start text-left font-normal pl-3",
                          !endDate && "text-muted-foreground"
                        )}
                      >
                        <CalendarIcon className="mr-2 h-4 w-4" />
                        {endDate ? format(endDate, "dd MMMM yyyy", { locale: id }) : <span>Pilih Tanggal</span>}
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-auto p-0" align="start">
                      <Calendar
                        mode="single"
                        selected={endDate}
                        onSelect={setEndDate}
                        initialFocus
                      />
                    </PopoverContent>
                  </Popover>
                </div>
                <div className="space-y-2">
                  <label className="text-sm font-medium">Filter Keterangan</label>
                  <Input placeholder="Cari berdasarkan keterangan..." />
                </div>
                <div className="space-y-2">
                  <label className="text-sm font-medium">Filter Obat</label>
                  <Select>
                    <SelectTrigger>
                      <SelectValue placeholder="Semua Obat" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">Semua Obat</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Riwayat Pergerakan Stok</CardTitle>
              <p className="text-sm text-muted-foreground">
                {stockData?.data?.length || 0} pergerakan stok ditampilkan
              </p>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Tanggal</TableHead>
                    <TableHead>Obat</TableHead>
                    <TableHead>Tipe</TableHead>
                    <TableHead>Jumlah</TableHead>
                    <TableHead>Referensi</TableHead>
                    <TableHead>Keterangan</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {isLoadingStock ? (
                    <TableRow>
                      <TableCell colSpan={6} className="text-center">Loading...</TableCell>
                    </TableRow>
                  ) : stockData?.data?.map((item: RiwayatBarang, index: number) => (
                    <TableRow key={index}>
                      <TableCell>{item.tanggal}</TableCell>
                      <TableCell>{item.nama_brng || item.kode_brng}</TableCell>
                      <TableCell>
                        <Badge className={
                          Number(item.masuk) > 0 
                            ? "bg-emerald-500 hover:bg-emerald-600" 
                            : "bg-red-500 hover:bg-red-600"
                        }>
                          {Number(item.masuk) > 0 ? "Masuk" : "Keluar"}
                        </Badge>
                      </TableCell>
                      <TableCell>{Number(item.masuk) > 0 ? item.masuk : item.keluar}</TableCell>
                      <TableCell>{item.no_faktur !== '0' ? item.no_faktur : '-'}</TableCell>
                      <TableCell>{item.keterangan}</TableCell>
                    </TableRow>
                  ))}
                  {!isLoadingStock && (!stockData?.data || stockData.data.length === 0) && (
                    <TableRow>
                      <TableCell colSpan={6} className="text-center text-muted-foreground">
                        Tidak ada data pergerakan stok
                      </TableCell>
                    </TableRow>
                  )}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="notifikasi" className="space-y-4">
          <div className="grid gap-4 md:grid-cols-3">
            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-lg font-medium text-orange-500 flex items-center gap-2">
                  <TrendingUp className="h-5 w-5" /> Stok Menipis
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4 max-h-[400px] overflow-auto">
                {lowStockList.length > 0 ? (
                  lowStockList.map((item: any, index: number) => (
                    <div key={index} className="border rounded-lg p-3 space-y-2">
                      <div className="flex justify-between items-start">
                        <span className="font-medium">{item.nama_brng}</span>
                        <Badge variant="outline" className="text-orange-500 border-orange-200 bg-orange-50">
                          Min: {item.min_stok}
                        </Badge>
                      </div>
                      <p className="text-sm text-muted-foreground">Sisa: {item.stok} {item.kode_sat}</p>
                    </div>
                  ))
                ) : (
                  <p className="text-sm text-muted-foreground text-center py-4">Tidak ada stok menipis</p>
                )}
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-lg font-medium text-gray-500 flex items-center gap-2">
                  <Package className="h-5 w-5" /> Habis Stok
                </CardTitle>
              </CardHeader>
              <CardContent className="max-h-[400px] overflow-auto">
                {outOfStockList.length > 0 ? (
                    outOfStockList.map((item: any, index: number) => (
                        <div key={index} className="border rounded-lg p-3 space-y-2 mb-2">
                            <div className="flex justify-between items-start">
                                <span className="font-medium">{item.nama_brng}</span>
                                <Badge variant="outline" className="text-gray-500 border-gray-200 bg-gray-50">
                                    Stok: 0
                                </Badge>
                            </div>
                        </div>
                    ))
                ) : (
                    <p className="text-sm text-muted-foreground text-center py-4">Tidak ada obat habis</p>
                )}
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-lg font-medium text-red-500 flex items-center gap-2">
                  <AlertTriangle className="h-5 w-5" /> Kadaluwarsa
                </CardTitle>
              </CardHeader>
              <CardContent className="max-h-[400px] overflow-auto">
                {expiredList.length > 0 ? (
                    expiredList.map((item: DataBarang, index: number) => (
                        <div key={index} className="border rounded-lg p-3 space-y-2 mb-2">
                            <div className="flex justify-between items-start">
                                <span className="font-medium">{item.nama_brng}</span>
                                <Badge variant="outline" className="text-red-500 border-red-200 bg-red-50">
                                    {item.expire}
                                </Badge>
                            </div>
                        </div>
                    ))
                ) : (
                    <p className="text-sm text-muted-foreground text-center py-4">Tidak ada obat kadaluarsa</p>
                )}
              </CardContent>
            </Card>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  );
}
