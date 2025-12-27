import React, { useState } from 'react';
import { Clock, User, Calendar, Stethoscope, FileText, Pill, Smile } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

interface QueuePatient {
  time: string;
  name: string;
  age: string;
  poli: string;
  status: 'scheduled' | 'in-progress' | 'completed';
}

// Mock data
const queuePatients: QueuePatient[] = [
  { time: '08:00:00', name: 'Budi Santoso', age: '0 tahun', poli: 'Poli Gigi', status: 'scheduled' },
  { time: '09:00:00', name: 'Siti Aminah', age: 'tahun', poli: 'Poli Umum', status: 'scheduled' },
];

interface PatientHistory {
  date: string;
  icd10?: string;
  icd9?: string;
  vitals?: {
    tekananDarah?: string;
    nadi?: string;
    rr?: string;
    suhu?: string;
    berat?: string;
    tinggi?: string;
    saturasiO2?: string;
    kesadaran?: string;
    lingkarPerut?: string;
    gcs?: string;
  };
  keluhan?: string;
  pemeriksaan?: string;
  diagnosa?: string;
  tindakan?: string;
}

const mockHistory: PatientHistory[] = [
  { date: '25/12/2025, 17.55.00' },
  {
    date: '23/12/2025, 07.44.05',
    icd10: 'ICD-10',
    icd9: 'ICD-9',
    vitals: {
      tekananDarah: '- / -',
      nadi: '-',
      rr: '-',
      suhu: '-',
      berat: '-',
      tinggi: '-',
      saturasiO2: '-',
      kesadaran: '-',
      lingkarPerut: '-',
      gcs: '-',
    },
  },
  {
    date: '22/12/2025, 17.33.57',
    keluhan: 'asdasd',
    pemeriksaan: 'asd',
    diagnosa: 'asd',
    tindakan: 'asd',
  },
];

// Queue Item Component
interface QueueItemProps {
  patient: QueuePatient;
  isSelected: boolean;
  onClick: () => void;
}

const QueueItem: React.FC<QueueItemProps> = ({ patient, isSelected, onClick }) => (
  <div
    onClick={onClick}
    className={`p-4 rounded-xl border cursor-pointer transition-all ${
      isSelected
        ? 'border-primary bg-primary/5'
        : 'border-border hover:border-primary/50 hover:bg-accent/50'
    }`}
  >
    <div className="flex items-start justify-between mb-2">
      <span className="text-lg font-bold text-foreground">{patient.time}</span>
      <div className="flex items-center gap-2">
        <span className="text-xs text-muted-foreground">{patient.poli}</span>
        <span className="badge-status badge-scheduled">Terjadwal</span>
      </div>
    </div>
    <p className="font-medium text-foreground">{patient.name}</p>
    <p className="text-sm text-muted-foreground">Usia: {patient.age}</p>
  </div>
);

// History Item Component
const HistoryItem: React.FC<{ history: PatientHistory }> = ({ history }) => (
  <div className="p-4 border border-border rounded-xl">
    <div className="flex items-start justify-between">
      <span className="font-medium text-foreground">{history.date}</span>
      <Button variant="outline" size="sm">
        Hasil Pemeriksaan
      </Button>
    </div>
    {history.icd10 && (
      <div className="mt-3 space-y-2">
        <p className="text-sm text-muted-foreground">{history.icd10}</p>
        <p className="text-sm text-muted-foreground">{history.icd9}</p>
        {history.vitals && (
          <div className="grid grid-cols-4 gap-4 mt-4 text-sm">
            <div>
              <span className="text-muted-foreground">Tekanan Darah: </span>
              <span>{history.vitals.tekananDarah}</span>
            </div>
            <div>
              <span className="text-muted-foreground">Nadi: </span>
              <span>{history.vitals.nadi}</span>
            </div>
            <div>
              <span className="text-muted-foreground">RR: </span>
              <span>{history.vitals.rr}</span>
            </div>
            <div>
              <span className="text-muted-foreground">Suhu: </span>
              <span>{history.vitals.suhu}</span>
            </div>
            <div>
              <span className="text-muted-foreground">Berat: </span>
              <span>{history.vitals.berat}</span>
            </div>
            <div>
              <span className="text-muted-foreground">Tinggi: </span>
              <span>{history.vitals.tinggi}</span>
            </div>
            <div>
              <span className="text-muted-foreground">Saturasi O₂: </span>
              <span>{history.vitals.saturasiO2}</span>
            </div>
            <div>
              <span className="text-muted-foreground">Kesadaran: </span>
              <span>{history.vitals.kesadaran}</span>
            </div>
            <div>
              <span className="text-muted-foreground">Lingkar Perut: </span>
              <span>{history.vitals.lingkarPerut}</span>
            </div>
            <div>
              <span className="text-muted-foreground">GCS: </span>
              <span>{history.vitals.gcs}</span>
            </div>
          </div>
        )}
      </div>
    )}
    {history.keluhan && (
      <div className="mt-3 space-y-1 text-sm">
        <p>
          <span className="text-muted-foreground">Keluhan: </span>
          <span>{history.keluhan}</span>
        </p>
        <p>
          <span className="text-muted-foreground">Pemeriksaan Fisik: </span>
          <span>{history.pemeriksaan}</span>
        </p>
        <p>
          <span className="text-muted-foreground">Diagnosa: </span>
          <span>{history.diagnosa}</span>
        </p>
        <p>
          <span className="text-muted-foreground">Tindakan: </span>
          <span>{history.tindakan}</span>
        </p>
      </div>
    )}
  </div>
);

const Pemeriksaan: React.FC = () => {
  const [selectedPatient, setSelectedPatient] = useState<QueuePatient | null>(queuePatients[0]);
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div>
        <h1 className="text-3xl font-bold text-foreground">Pemeriksaan & Diagnosa</h1>
        <p className="text-muted-foreground mt-1">Lakukan pemeriksaan pasien dan input hasil diagnosa</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">
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
                    <Input
                      type="date"
                      value={dateFrom}
                      onChange={(e) => setDateFrom(e.target.value)}
                      className="text-sm"
                    />
                  </div>
                </div>
                <div>
                  <Label className="text-xs text-muted-foreground">Sampai Tanggal</Label>
                  <div className="relative mt-1">
                    <Input
                      type="date"
                      value={dateTo}
                      onChange={(e) => setDateTo(e.target.value)}
                      className="text-sm"
                    />
                  </div>
                </div>
              </div>
            </div>

            {/* Queue List */}
            <div className="space-y-3">
              {queuePatients.map((patient, index) => (
                <QueueItem
                  key={index}
                  patient={patient}
                  isSelected={selectedPatient?.name === patient.name}
                  onClick={() => setSelectedPatient(patient)}
                />
              ))}
            </div>
          </div>
        </div>

        {/* Right Column - Patient Info & History */}
        <div className="lg:col-span-3 space-y-4">
          {selectedPatient && (
            <>
              {/* Patient Info Card */}
              <div className="bg-card rounded-xl border border-border p-6">
                <div className="flex items-center gap-2 mb-4">
                  <User className="w-5 h-5 text-foreground" />
                  <h2 className="text-xl font-bold text-foreground">Informasi Pasien</h2>
                </div>

                <div className="grid grid-cols-4 gap-6">
                  <div>
                    <p className="text-sm text-muted-foreground">Nama</p>
                    <p className="font-semibold text-foreground">{selectedPatient.name}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">ID Pasien</p>
                    <p className="font-semibold text-foreground">000001</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">Usia</p>
                    <p className="font-semibold text-foreground">{selectedPatient.age}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">Waktu</p>
                    <p className="font-semibold text-foreground">{selectedPatient.time}</p>
                  </div>
                </div>

                <div className="mt-4">
                  <p className="text-sm text-muted-foreground">Keluhan Utama</p>
                  <p className="text-foreground">-</p>
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
                      Pemeriksaan
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
                        Pasien: {selectedPatient.name}
                      </p>
                    </div>
                    <div className="space-y-3">
                      {mockHistory.map((history, index) => (
                        <HistoryItem key={index} history={history} />
                      ))}
                    </div>
                  </TabsContent>

                  <TabsContent value="pemeriksaan" className="mt-6">
                    <div className="space-y-4">
                      <h3 className="text-lg font-bold text-foreground">Input Pemeriksaan</h3>
                      <div className="grid grid-cols-3 gap-4">
                        <div className="space-y-2">
                          <Label>Tekanan Darah</Label>
                          <Input placeholder="120/80" />
                        </div>
                        <div className="space-y-2">
                          <Label>Nadi</Label>
                          <Input placeholder="80 bpm" />
                        </div>
                        <div className="space-y-2">
                          <Label>Suhu</Label>
                          <Input placeholder="36.5°C" />
                        </div>
                        <div className="space-y-2">
                          <Label>Berat Badan</Label>
                          <Input placeholder="60 kg" />
                        </div>
                        <div className="space-y-2">
                          <Label>Tinggi Badan</Label>
                          <Input placeholder="170 cm" />
                        </div>
                        <div className="space-y-2">
                          <Label>Respirasi</Label>
                          <Input placeholder="20 /menit" />
                        </div>
                      </div>
                      <div className="space-y-2">
                        <Label>Keluhan</Label>
                        <Input placeholder="Masukkan keluhan pasien" />
                      </div>
                      <div className="space-y-2">
                        <Label>Pemeriksaan Fisik</Label>
                        <Input placeholder="Hasil pemeriksaan fisik" />
                      </div>
                      <div className="space-y-2">
                        <Label>Diagnosa</Label>
                        <Input placeholder="Diagnosa pasien" />
                      </div>
                      <Button className="mt-4">Simpan Pemeriksaan</Button>
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
          )}
        </div>
      </div>
    </div>
  );
};

export default Pemeriksaan;
