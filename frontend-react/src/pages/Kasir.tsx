import React, { useState } from 'react';
import { 
  FileText, 
  Search, 
  Calendar as CalendarIcon, 
  User, 
  CreditCard,
  Eye,
  CheckCircle2,
  Printer,
  Stethoscope,
  BedDouble
} from 'lucide-react';
import { format } from "date-fns";
import { useQuery } from "@tanstack/react-query";
import { getKasirRalanList, getKasirRanapList, getBillingDetail } from '@/lib/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import { Calendar } from "@/components/ui/calendar";
import { cn } from "@/lib/utils";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Separator } from "@/components/ui/separator";

const Kasir = () => {
  const [activeTab, setActiveTab] = useState<"ralan" | "ranap">("ralan");
  const [date, setDate] = useState<{ from: Date; to: Date }>({
    from: new Date(),
    to: new Date(),
  });
  const [search, setSearch] = useState("");
  const [selectedPasien, setSelectedPasien] = useState<any>(null);

  // Fetch List
  const { data: listData, isLoading: isLoadingList } = useQuery({
    queryKey: ['kasir-list', activeTab, date.from, date.to, search],
    queryFn: () => activeTab === 'ralan'
      ? getKasirRalanList(1, 100, search, format(date.from, 'yyyy-MM-dd'), format(date.to, 'yyyy-MM-dd'))
      : getKasirRanapList(1, 100, search, format(date.from, 'yyyy-MM-dd'), format(date.to, 'yyyy-MM-dd')),
  });

  // Fetch Detail
  const { data: detailData, isLoading: isLoadingDetail } = useQuery({
    queryKey: ['kasir-detail', selectedPasien?.no_rawat, activeTab],
    queryFn: () => getBillingDetail(selectedPasien?.no_rawat, activeTab),
    enabled: !!selectedPasien?.no_rawat,
  });

  const patients = listData?.data || [];
  const billingDetails = detailData?.data?.details || [];
  const billingTotal = detailData?.data?.total || 0;
  const billingInfo = detailData?.data?.registrasi || null;

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    }).format(amount);
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col space-y-2">
        <h1 className="text-3xl font-bold tracking-tight text-foreground">Kasir & Pembayaran</h1>
        <p className="text-muted-foreground">
          Kelola pembayaran pasien Rawat Jalan dan Rawat Inap
        </p>
      </div>

      <Tabs defaultValue="ralan" className="w-full" onValueChange={(v) => { setActiveTab(v as any); setSelectedPasien(null); }}>
        <TabsList className="grid w-full max-w-[400px] grid-cols-2">
          <TabsTrigger value="ralan">Rawat Jalan</TabsTrigger>
          <TabsTrigger value="ranap">Rawat Inap</TabsTrigger>
        </TabsList>

        <div className="mt-4 flex items-center justify-between gap-4">
          <div className="flex items-center gap-2 flex-1">
            <div className="relative flex-1 max-w-sm">
              <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Cari pasien..."
                className="pl-8"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <Popover>
              <PopoverTrigger asChild>
                <Button variant="outline" className={cn("justify-start text-left font-normal", !date.from && "text-muted-foreground")}>
                  <CalendarIcon className="mr-2 h-4 w-4" />
                  {date.from ? (
                    date.to ? (
                      <>
                        {format(date.from, "LLL dd, y")} - {format(date.to, "LLL dd, y")}
                      </>
                    ) : (
                      format(date.from, "LLL dd, y")
                    )
                  ) : (
                    <span>Pick a date</span>
                  )}
                </Button>
              </PopoverTrigger>
              <PopoverContent className="w-auto p-0" align="start">
                <Calendar
                  initialFocus
                  mode="range"
                  defaultMonth={date.from}
                  selected={date as any}
                  onSelect={(range: any) => setDate(range || { from: new Date(), to: new Date() })}
                  numberOfMonths={2}
                />
              </PopoverContent>
            </Popover>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
          {/* List Pasien */}
          <div className="md:col-span-2">
            <Card className="h-[calc(100vh-250px)] flex flex-col">
              <CardHeader className="pb-3">
                <CardTitle className="text-base">Daftar Tagihan</CardTitle>
                <CardDescription>
                  {isLoadingList ? "Memuat data..." : `Menampilkan ${patients.length} pasien`}
                </CardDescription>
              </CardHeader>
              <CardContent className="flex-1 overflow-auto p-0">
                <Table>
                  <TableHeader className="sticky top-0 bg-background z-10">
                    <TableRow>
                      <TableHead>No. Rawat / RM</TableHead>
                      <TableHead>Pasien</TableHead>
                      <TableHead>Dokter</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead className="text-right">Aksi</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {isLoadingList ? (
                      <TableRow>
                        <TableCell colSpan={5} className="text-center py-8 text-muted-foreground">
                          Memuat data...
                        </TableCell>
                      </TableRow>
                    ) : patients.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={5} className="text-center py-8 text-muted-foreground">
                          Tidak ada data ditemukan
                        </TableCell>
                      </TableRow>
                    ) : (
                      patients.map((pasien: any) => (
                        <TableRow 
                          key={pasien.no_rawat}
                          className={cn(
                            "cursor-pointer hover:bg-muted/50 transition-colors",
                            selectedPasien?.no_rawat === pasien.no_rawat && "bg-blue-50/50"
                          )}
                          onClick={() => setSelectedPasien(pasien)}
                        >
                          <TableCell className="font-medium">
                            <div className="flex flex-col">
                              <span>{pasien.no_rawat}</span>
                              <span className="text-xs text-muted-foreground">{pasien.no_rkm_medis}</span>
                            </div>
                          </TableCell>
                          <TableCell>{pasien.nm_pasien}</TableCell>
                          <TableCell>
                            <div className="flex items-center gap-2">
                              <Stethoscope className="h-3 w-3 text-muted-foreground" />
                              <span className="text-sm truncate max-w-[120px]">{pasien.nm_dokter}</span>
                            </div>
                          </TableCell>
                          <TableCell>
                            {pasien.status_bayar === 'Sudah Bayar' ? (
                              <Badge className="bg-emerald-500 hover:bg-emerald-600">Lunas</Badge>
                            ) : (
                              <Badge variant="secondary" className="bg-orange-100 text-orange-700 hover:bg-orange-200">Belum Bayar</Badge>
                            )}
                          </TableCell>
                          <TableCell className="text-right">
                            <Button 
                              size="sm" 
                              variant="ghost"
                              className="hover:text-blue-600"
                              onClick={(e) => {
                                e.stopPropagation();
                                setSelectedPasien(pasien);
                              }}
                            >
                              Detail
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              </CardContent>
            </Card>
          </div>

          {/* Detail Tagihan */}
          <div className="md:col-span-1">
            <Card className="h-[calc(100vh-250px)] flex flex-col">
              <CardHeader className="pb-3 border-b">
                <CardTitle className="text-base flex items-center gap-2">
                  <FileText className="h-4 w-4" />
                  Rincian Tagihan
                </CardTitle>
                {selectedPasien ? (
                  <div className="space-y-1 mt-2">
                    <p className="font-medium text-sm">{selectedPasien.nm_pasien}</p>
                    <p className="text-xs text-muted-foreground">{selectedPasien.no_rawat}</p>
                  </div>
                ) : (
                  <CardDescription>Pilih pasien untuk melihat rincian</CardDescription>
                )}
              </CardHeader>
              <CardContent className="flex-1 overflow-auto pt-4">
                {selectedPasien ? (
                  isLoadingDetail ? (
                    <div className="flex items-center justify-center py-8 text-muted-foreground">
                      Memuat rincian...
                    </div>
                  ) : (
                    <div className="space-y-4">
                      {billingDetails.map((item: any, idx: number) => (
                        <div key={idx} className="flex justify-between items-start text-sm border-b border-dashed pb-2 last:border-0">
                          <div className="flex-1 pr-4">
                            <p className="font-medium text-slate-700">{item.nama}</p>
                            <p className="text-xs text-muted-foreground">{item.kategori} {item.jumlah > 1 && `x ${item.jumlah}`}</p>
                          </div>
                          <div className="font-medium whitespace-nowrap">
                            {formatCurrency(item.subtotal)}
                          </div>
                        </div>
                      ))}
                      
                      <div className="pt-4 mt-4 border-t flex justify-between items-center bg-slate-50 p-3 rounded-md">
                        <span className="font-bold">Total Tagihan</span>
                        <span className="font-bold text-lg text-emerald-600">
                          {formatCurrency(billingTotal)}
                        </span>
                      </div>

                      <div className="pt-4 grid grid-cols-2 gap-2">
                        <Button variant="outline" className="w-full">
                          <Printer className="mr-2 h-4 w-4" /> Cetak
                        </Button>
                        <Button className="w-full bg-blue-600 hover:bg-blue-700">
                          <CreditCard className="mr-2 h-4 w-4" /> Bayar
                        </Button>
                      </div>
                    </div>
                  )
                ) : (
                  <div className="flex flex-col items-center justify-center h-full text-muted-foreground opacity-50">
                    <CreditCard className="h-12 w-12 mb-2" />
                    <p className="text-sm">Pilih pasien dari daftar</p>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>
        </div>
      </Tabs>
    </div>
  );
};

export default Kasir;
