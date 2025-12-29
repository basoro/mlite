import React, { useState } from 'react';
import { 
  Activity, 
  Search, 
  RefreshCw, 
  Calendar as CalendarIcon, 
  CheckCircle2, 
  XCircle, 
  Link,
  UploadCloud,
  FileJson
} from 'lucide-react';
import { format } from "date-fns";
import { useQuery } from "@tanstack/react-query";
import { getRawatJalanList } from '@/lib/api';
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
import { useToast } from "@/hooks/use-toast";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";

const Integrasi: React.FC = () => {
  const { toast } = useToast();
  const [date, setDate] = useState<{ from: Date; to: Date }>({
    from: new Date(),
    to: new Date(),
  });
  const [searchTerm, setSearchTerm] = useState("");
  
  // JSON Modal State
  const [isJsonModalOpen, setIsJsonModalOpen] = useState(false);
  const [jsonContent, setJsonContent] = useState<any>(null);

  // Fetch Data
  const { data: rawatJalanData, isLoading, refetch } = useQuery({
    queryKey: ['integrasi-list', date.from, date.to],
    queryFn: () => getRawatJalanList(
      format(date.from, 'yyyy-MM-dd'),
      format(date.to, 'yyyy-MM-dd'),
      0,
      100
    )
  });

  const patients = rawatJalanData?.data || [];

  // Filter based on search
  const filteredPatients = patients.filter((p: any) => 
    p.nm_pasien.toLowerCase().includes(searchTerm.toLowerCase()) || 
    p.no_rkm_medis.toLowerCase().includes(searchTerm.toLowerCase()) ||
    p.no_rawat.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // Mock statuses (randomize for demo)
  const getStatus = (id: string, type: 'bpjs' | 'satusehat') => {
    const sum = id.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    const states = ['success', 'pending', 'failed', 'not_linked'];
    const index = (sum + (type === 'bpjs' ? 0 : 1)) % 4;
    return states[index];
  };

  const renderStatusBadge = (status: string) => {
    switch(status) {
      case 'success':
        return <Badge className="bg-emerald-500 hover:bg-emerald-600"><CheckCircle2 className="w-3 h-3 mr-1" /> Terkirim</Badge>;
      case 'failed':
        return <Badge variant="destructive"><XCircle className="w-3 h-3 mr-1" /> Gagal</Badge>;
      case 'pending':
        return <Badge variant="outline" className="bg-yellow-50 text-yellow-600 border-yellow-200"><Activity className="w-3 h-3 mr-1" /> Pending</Badge>;
      default:
        return <Badge variant="outline" className="text-muted-foreground">Belum Bridging</Badge>;
    }
  };

  const handleSync = (type: string, noRawat: string) => {
    toast({
      title: "Memproses Bridging",
      description: `Mengirim data ${noRawat} ke ${type}...`,
    });
    
    setTimeout(() => {
      toast({
        title: "Berhasil",
        description: `Data berhasil dikirim ke ${type}`,
      });
    }, 1500);
  };

  const handleShowJson = (patient: any) => {
    // Mock JSON data
    const mockData = {
      request: {
        method: "POST",
        url: "https://api.bpjs-kesehatan.go.id/vclaim/SEP/2.0/insert",
        headers: {
          "X-cons-id": "12345",
          "X-timestamp": Math.floor(Date.now() / 1000).toString(),
          "X-signature": "a1b2c3d4e5"
        },
        body: {
          request: {
            t_sep: {
              noKartu: "000123456789",
              tglSep: format(new Date(), "yyyy-MM-dd"),
              ppkPelayanan: "12345678",
              jnsPelayanan: "2",
              noMR: patient.no_rkm_medis,
              catatan: "Integrasi bridging",
              diagAwal: "A00.1",
              poli: {
                tujuan: "INT",
                eksekutif: "0"
              }
            }
          }
        }
      },
      response: {
        metaData: {
          code: "200",
          message: "Sukses"
        },
        response: {
          sep: {
            noSep: "01234567890123456789",
            tglSep: format(new Date(), "yyyy-MM-dd"),
            peserta: {
              noKartu: "000123456789",
              nama: patient.nm_pasien,
              noMr: patient.no_rkm_medis
            }
          }
        }
      }
    };
    
    setJsonContent(mockData);
    setIsJsonModalOpen(true);
  };

  return (
    <div className="container mx-auto p-6 space-y-6">
      <div className="flex flex-col space-y-2">
        <h1 className="text-3xl font-bold tracking-tight text-foreground">Integrasi Sistem</h1>
        <p className="text-muted-foreground">
          Monitor dan kelola bridging data ke BPJS (VClaim/PCare) dan SatuSehat
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card className="bg-blue-50 border-blue-200">
          <CardHeader className="pb-2">
            <CardTitle className="flex items-center gap-2 text-blue-700 text-base">
              <Link className="h-5 w-5" /> BPJS Kesehatan
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-blue-800">Terhubung</div>
            <p className="text-xs text-blue-600 mt-1">Status: Online • PCare/VClaim Ready</p>
          </CardContent>
        </Card>
        
        <Card className="bg-emerald-50 border-emerald-200">
          <CardHeader className="pb-2">
            <CardTitle className="flex items-center gap-2 text-emerald-700 text-base">
              <Activity className="h-5 w-5" /> SatuSehat
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-emerald-800">Terhubung</div>
            <p className="text-xs text-emerald-600 mt-1">Status: Online • Token Valid</p>
          </CardContent>
        </Card>

        <Card>
           <CardHeader className="pb-2">
            <CardTitle className="flex items-center gap-2 text-base">
              <RefreshCw className="h-5 w-5" /> Antrean Bridging
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">0</div>
            <p className="text-xs text-muted-foreground mt-1">Data menunggu antrean</p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Data Kunjungan & Status Bridging</CardTitle>
          <CardDescription>
            Daftar pasien dan status pengiriman data ke sistem eksternal
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex flex-col md:flex-row gap-4 mb-6">
            <div className="flex items-center gap-2">
               <Popover>
                <PopoverTrigger asChild>
                  <Button
                    variant={"outline"}
                    className={cn(
                      "w-[140px] justify-start text-left font-normal",
                      !date.from && "text-muted-foreground"
                    )}
                  >
                    <CalendarIcon className="mr-2 h-4 w-4" />
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
              <span className="text-muted-foreground">-</span>
              <Popover>
                <PopoverTrigger asChild>
                  <Button
                    variant={"outline"}
                    className={cn(
                      "w-[140px] justify-start text-left font-normal",
                      !date.to && "text-muted-foreground"
                    )}
                  >
                    <CalendarIcon className="mr-2 h-4 w-4" />
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
            
            <div className="flex-1 relative">
              <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                type="search"
                placeholder="Cari No. Rawat / Pasien..."
                className="pl-8"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
            </div>
            
            <Button variant="outline" onClick={() => refetch()}>
              <RefreshCw className="mr-2 h-4 w-4" /> Refresh
            </Button>
          </div>

          <div className="border rounded-md">
            <div className="grid grid-cols-12 gap-4 p-4 bg-slate-50 border-b font-medium text-sm">
              <div className="col-span-4">Pasien</div>
              <div className="col-span-2">Info Medis</div>
              <div className="col-span-3 text-center">BPJS Kesehatan</div>
              <div className="col-span-3 text-center">SatuSehat</div>
            </div>
            
            <div className="max-h-[500px] overflow-y-auto">
              {isLoading ? (
                 <div className="p-8 text-center text-muted-foreground">Loading data...</div>
              ) : filteredPatients.length === 0 ? (
                 <div className="p-8 text-center text-muted-foreground">Tidak ada data kunjungan pada periode ini</div>
              ) : (
                filteredPatients.map((patient: any) => {
                  const bpjsStatus = getStatus(patient.no_rawat, 'bpjs');
                  const satuSehatStatus = getStatus(patient.no_rawat, 'satusehat');
                  
                  return (
                    <div key={patient.no_rawat} className="grid grid-cols-12 gap-4 p-4 border-b last:border-0 items-center hover:bg-slate-50">
                      <div className="col-span-4">
                        <p className="font-semibold text-sm">{patient.nm_pasien}</p>
                        <p className="text-xs text-muted-foreground">{patient.no_rkm_medis}</p>
                        <p className="text-xs text-muted-foreground">{patient.no_rawat}</p>
                      </div>
                      <div className="col-span-2">
                        <p className="text-xs font-medium">{patient.kd_dokter}</p>
                        <p className="text-xs text-muted-foreground">{patient.kd_poli}</p>
                        <Badge variant="outline" className="text-[10px] h-5 mt-1">{patient.kd_pj}</Badge>
                      </div>
                      
                      {/* BPJS Status */}
                      <div className="col-span-3 flex flex-col items-center gap-2">
                        {renderStatusBadge(bpjsStatus)}
                        {bpjsStatus !== 'success' && (
                          <Button 
                            size="sm" 
                            variant="ghost" 
                            className="h-6 text-xs text-blue-600 hover:text-blue-700 hover:bg-blue-50"
                            onClick={() => handleSync('BPJS', patient.no_rawat)}
                          >
                            <UploadCloud className="h-3 w-3 mr-1" /> Kirim BPJS
                          </Button>
                        )}
                        {bpjsStatus === 'success' && (
                          <span className="text-[10px] text-muted-foreground">ID: {Math.floor(Math.random() * 1000000)}</span>
                        )}
                        <Button 
                          variant="ghost" 
                          size="icon" 
                          title="Lihat JSON BPJS"
                          className="h-6 w-6"
                          onClick={() => handleShowJson(patient)}
                        >
                          <FileJson className="h-3 w-3 text-slate-500" />
                        </Button>
                      </div>

                      {/* SatuSehat Status */}
                      <div className="col-span-3 flex flex-col items-center gap-2">
                        {renderStatusBadge(satuSehatStatus)}
                        {satuSehatStatus !== 'success' && (
                          <Button 
                            size="sm" 
                            variant="ghost" 
                            className="h-6 text-xs text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                            onClick={() => handleSync('SatuSehat', patient.no_rawat)}
                          >
                            <Activity className="h-3 w-3 mr-1" /> Kirim SatuSehat
                          </Button>
                        )}
                         {satuSehatStatus === 'success' && (
                          <span className="text-[10px] text-muted-foreground">ID: {Math.floor(Math.random() * 1000000)}</span>
                        )}
                        <Button 
                          variant="ghost" 
                          size="icon" 
                          title="Lihat JSON SatuSehat"
                          className="h-6 w-6"
                          onClick={() => handleShowJson(patient)}
                        >
                          <FileJson className="h-3 w-3 text-slate-500" />
                        </Button>
                      </div>
                    </div>
                  );
                })
              )}
            </div>
          </div>
        </CardContent>
      </Card>

      {/* JSON Viewer Modal */}
      <Dialog open={isJsonModalOpen} onOpenChange={setIsJsonModalOpen}>
        <DialogContent className="max-w-3xl max-h-[80vh] overflow-hidden flex flex-col">
          <DialogHeader>
            <DialogTitle>Log Transaksi Bridging</DialogTitle>
            <DialogDescription>
              Detail request dan response JSON untuk transaksi ini.
            </DialogDescription>
          </DialogHeader>
          <Tabs defaultValue="request" className="flex-1 flex flex-col overflow-hidden">
            <TabsList className="grid w-full grid-cols-2">
              <TabsTrigger value="request">Request</TabsTrigger>
              <TabsTrigger value="response">Response</TabsTrigger>
            </TabsList>
            <TabsContent value="request" className="flex-1 overflow-auto p-4 bg-slate-950 rounded-md text-slate-50 font-mono text-xs mt-2">
              <pre>{JSON.stringify(jsonContent?.request, null, 2)}</pre>
            </TabsContent>
            <TabsContent value="response" className="flex-1 overflow-auto p-4 bg-slate-950 rounded-md text-slate-50 font-mono text-xs mt-2">
              <pre>{JSON.stringify(jsonContent?.response, null, 2)}</pre>
            </TabsContent>
          </Tabs>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default Integrasi;
