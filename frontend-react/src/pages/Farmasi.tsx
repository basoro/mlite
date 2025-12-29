import React, { useState } from 'react';
import { useQuery } from "@tanstack/react-query";
import { format } from "date-fns";
import { 
  Search, 
  Calendar as CalendarIcon, 
  Pill, 
  User, 
  Clock, 
  CheckCircle2, 
  AlertCircle,
  Stethoscope,
  ClipboardList
} from 'lucide-react';

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  CardFooter,
} from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import { Calendar } from "@/components/ui/calendar";
import { cn } from "@/lib/utils";
import { getResepList, getResepDetailItems } from '@/lib/api';
import { Skeleton } from "@/components/ui/skeleton";

const Farmasi = () => {
  const [date, setDate] = useState<{ from: Date; to: Date }>({
    from: new Date(),
    to: new Date(),
  });
  const [search, setSearch] = useState("");
  const [selectedResep, setSelectedResep] = useState<any>(null);

  // Fetch Resep List
  const { data: resepData, isLoading: isLoadingResep, refetch: refetchResep } = useQuery({
    queryKey: ['resep-obat', date.from, date.to, search],
    queryFn: () => getResepList(
      1, 
      100, 
      search, 
      format(date.from, 'yyyy-MM-dd'), 
      format(date.to, 'yyyy-MM-dd')
    ),
  });

  // Fetch Detail Resep (Items)
  const { data: detailData, isLoading: isLoadingDetail } = useQuery({
    queryKey: ['resep-detail', selectedResep?.no_resep],
    queryFn: () => getResepDetailItems(selectedResep?.no_resep),
    enabled: !!selectedResep?.no_resep,
  });

  const resepList = resepData?.data || [];
  const resepItems = detailData?.data || [];

  // Stats
  const stats = {
    total: resepList.length,
    selesai: resepList.filter((r: any) => r.tgl_perawatan !== '0000-00-00' && r.tgl_perawatan !== null).length,
    menunggu: resepList.filter((r: any) => r.tgl_perawatan === '0000-00-00' || r.tgl_perawatan === null).length,
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col space-y-2">
        <h1 className="text-3xl font-bold tracking-tight text-foreground">Farmasi & Apotek</h1>
        <p className="text-muted-foreground">
          Kelola resep masuk, validasi, dan penyerahan obat
        </p>
      </div>

      {/* Stats Cards */}
      <div className="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Resep</CardTitle>
            <ClipboardList className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats.total}</div>
            <p className="text-xs text-muted-foreground">Periode terpilih</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Menunggu Layanan</CardTitle>
            <Clock className="h-4 w-4 text-orange-500" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-orange-600">{stats.menunggu}</div>
            <p className="text-xs text-muted-foreground">Perlu segera diproses</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Selesai</CardTitle>
            <CheckCircle2 className="h-4 w-4 text-emerald-500" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-emerald-600">{stats.selesai}</div>
            <p className="text-xs text-muted-foreground">Sudah diserahkan</p>
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left Column: List Resep */}
        <div className="lg:col-span-2 space-y-4">
          <Card className="h-full flex flex-col">
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle>Daftar Resep Masuk</CardTitle>
                <div className="flex items-center gap-2">
                  <Popover>
                    <PopoverTrigger asChild>
                      <Button
                        variant={"outline"}
                        className={cn(
                          "w-[240px] justify-start text-left font-normal",
                          !date.from && "text-muted-foreground"
                        )}
                      >
                        <CalendarIcon className="mr-2 h-4 w-4" />
                        {date.from ? (
                          date.to ? (
                            <>
                              {format(date.from, "LLL dd, y")} -{" "}
                              {format(date.to, "LLL dd, y")}
                            </>
                          ) : (
                            format(date.from, "LLL dd, y")
                          )
                        ) : (
                          <span>Pilih Tanggal</span>
                        )}
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-auto p-0" align="end">
                      <Calendar
                        initialFocus
                        mode="range"
                        defaultMonth={date.from}
                        selected={{ from: date.from, to: date.to }}
                        onSelect={(range) => {
                          if (range?.from) {
                            setDate({ from: range.from, to: range.to || range.from });
                          }
                        }}
                        numberOfMonths={2}
                      />
                    </PopoverContent>
                  </Popover>
                  <Button variant="outline" size="icon" onClick={() => refetchResep()}>
                    <ClipboardList className="h-4 w-4" />
                  </Button>
                </div>
              </div>
              <div className="relative mt-2">
                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Cari No. Resep / Pasien..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="pl-8"
                />
              </div>
            </CardHeader>
            <CardContent className="flex-1 overflow-auto min-h-[400px]">
              {isLoadingResep ? (
                <div className="space-y-2">
                   {[1,2,3].map(i => <Skeleton key={i} className="h-12 w-full" />)}
                </div>
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>No. Resep</TableHead>
                      <TableHead>Waktu</TableHead>
                      <TableHead>Pasien</TableHead>
                      <TableHead>Dokter</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead className="text-right">Aksi</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {resepList.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={6} className="text-center py-8 text-muted-foreground">
                          Tidak ada data resep
                        </TableCell>
                      </TableRow>
                    ) : (
                      resepList.map((resep: any) => (
                        <TableRow 
                          key={resep.no_resep} 
                          className={cn("cursor-pointer hover:bg-muted/50", selectedResep?.no_resep === resep.no_resep && "bg-muted")}
                          onClick={() => setSelectedResep(resep)}
                        >
                          <TableCell className="font-medium">{resep.no_resep}</TableCell>
                          <TableCell>
                            <div className="flex flex-col">
                              <span className="text-sm">{resep.tgl_peresepan}</span>
                              <span className="text-xs text-muted-foreground">{resep.jam_peresepan}</span>
                            </div>
                          </TableCell>
                          <TableCell>
                            <div className="flex items-center gap-2">
                              <User className="h-3 w-3 text-muted-foreground" />
                              <span>{resep.nm_pasien || resep.no_rawat}</span>
                            </div>
                          </TableCell>
                          <TableCell>
                            <div className="flex items-center gap-2">
                              <Stethoscope className="h-3 w-3 text-muted-foreground" />
                              <span className="text-sm truncate max-w-[120px]">{resep.nm_dokter || resep.kd_dokter}</span>
                            </div>
                          </TableCell>
                          <TableCell>
                            {resep.tgl_perawatan && resep.tgl_perawatan !== '0000-00-00' ? (
                              <Badge variant="default" className="bg-emerald-500 hover:bg-emerald-600">Selesai</Badge>
                            ) : (
                              <Badge variant="secondary" className="bg-orange-100 text-orange-700 hover:bg-orange-200">Menunggu</Badge>
                            )}
                          </TableCell>
                          <TableCell className="text-right">
                            <Button size="sm" variant="ghost">Detail</Button>
                          </TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Right Column: Detail */}
        <div className="space-y-4">
          <Card className="h-full border-l-4 border-l-blue-500">
            <CardHeader className="bg-slate-50/50">
              <CardTitle className="text-lg flex items-center gap-2">
                <Pill className="h-5 w-5 text-blue-500" />
                Detail Resep
              </CardTitle>
              {selectedResep ? (
                <CardDescription>
                  {selectedResep.no_resep} • {selectedResep.tgl_peresepan}
                </CardDescription>
              ) : (
                <CardDescription>Pilih resep untuk melihat detail</CardDescription>
              )}
            </CardHeader>
            <CardContent className="pt-6">
              {!selectedResep ? (
                <div className="flex flex-col items-center justify-center h-[300px] text-muted-foreground text-center">
                  <Pill className="h-16 w-16 mb-4 opacity-10" />
                  <p>Silakan pilih resep dari tabel di sebelah kiri</p>
                </div>
              ) : (
                <div className="space-y-6">
                  {/* Patient Info Summary */}
                  <div className="p-3 bg-blue-50 rounded-lg space-y-1">
                    <div className="flex justify-between text-sm">
                      <span className="text-muted-foreground">Pasien:</span>
                      <span className="font-medium">{selectedResep.nm_pasien}</span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span className="text-muted-foreground">Dokter:</span>
                      <span className="font-medium">{selectedResep.nm_dokter}</span>
                    </div>
                  </div>

                  {/* Medicine List */}
                  <div className="space-y-3">
                    <h3 className="font-medium text-sm flex items-center gap-2 border-b pb-2">
                      <ClipboardList className="h-4 w-4" />
                      Daftar Obat
                    </h3>
                    
                    {isLoadingDetail ? (
                      <div className="space-y-2">
                        <Skeleton className="h-10 w-full" />
                        <Skeleton className="h-10 w-full" />
                      </div>
                    ) : resepItems.length === 0 ? (
                      <p className="text-sm text-muted-foreground py-4 text-center">Tidak ada item obat</p>
                    ) : (
                      <div className="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                        {resepItems.map((item: any, idx: number) => (
                          <div key={idx} className="flex justify-between items-start p-3 border rounded bg-white shadow-sm">
                            <div>
                              <p className="font-medium text-sm">{item.nama_brng || item.kode_brng}</p>
                              <p className="text-xs text-slate-500 mt-1">
                                {item.jml} {item.kode_sat || 'pcs'} • {item.aturan_pakai}
                              </p>
                            </div>
                            <Badge variant="outline" className="text-xs">
                              {item.jml}
                            </Badge>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                </div>
              )}
            </CardContent>
            {selectedResep && (
              <CardFooter className="border-t pt-4 bg-slate-50/50">
                <Button className="w-full bg-blue-600 hover:bg-blue-700">
                  <CheckCircle2 className="mr-2 h-4 w-4" />
                  Proses & Validasi
                </Button>
              </CardFooter>
            )}
          </Card>
        </div>
      </div>
    </div>
  );
};

export default Farmasi;
