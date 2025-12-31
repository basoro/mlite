import React from 'react';
import { Construction, Settings, Save } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface PlaceholderPageProps {
  title: string;
  description: string;
}

const PlaceholderPage: React.FC<PlaceholderPageProps> = ({ title, description }) => {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-foreground">{title}</h1>
        <p className="text-muted-foreground mt-1">{description}</p>
      </div>

      <div className="bg-card rounded-xl border border-border p-12 text-center">
        <Construction className="w-16 h-16 mx-auto text-muted-foreground mb-4" />
        <h2 className="text-xl font-semibold text-foreground mb-2">Dalam Pengembangan</h2>
        <p className="text-muted-foreground max-w-md mx-auto">
          Fitur ini sedang dalam tahap pengembangan dan akan segera tersedia. 
          Terima kasih atas kesabaran Anda.
        </p>
      </div>
    </div>
  );
};

import Integrasi from "./Integrasi";
import ManajemenUser from "./ManajemenUser";

export const Inventory: React.FC = () => (
  <PlaceholderPage 
    title="Inventory" 
    description="Kelola stok obat dan alat kesehatan" 
  />
);

export const MasterData: React.FC = () => (
  <PlaceholderPage 
    title="Master Data" 
    description="Kelola data master seperti dokter, poliklinik, dan tindakan" 
  />
);

export const Laporan: React.FC = () => (
  <PlaceholderPage 
    title="Laporan" 
    description="Lihat dan unduh laporan klinik" 
  />
);

export { Integrasi, ManajemenUser };

export const Pengaturan = () => {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-foreground">Pengaturan Aplikasi</h1>
        <p className="text-muted-foreground mt-1">Kelola informasi identitas klinik</p>
      </div>

      <div className="bg-card rounded-xl border border-border p-6">
        <div className="flex items-center gap-2 mb-1">
          <Settings className="w-5 h-5 text-muted-foreground" />
          <h2 className="text-xl font-bold text-foreground">Informasi Klinik</h2>
        </div>
        <p className="text-sm text-muted-foreground mb-6">Data disimpan sebagai satu record</p>

        <div className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="nama_klinik">Nama Klinik *</Label>
            <Input id="nama_klinik" placeholder="Atila Medika" />
          </div>

          <div className="space-y-2">
            <Label htmlFor="alamat">Alamat</Label>
            <Textarea id="alamat" placeholder="Jl. A. Yani Mandingin" />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="kota">Kota</Label>
              <Input id="kota" placeholder="Kota" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="provinsi">Provinsi</Label>
              <Input id="provinsi" placeholder="Provinsi" />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="telepon">Telepon</Label>
              <Input id="telepon" placeholder="021-12345678" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="email">Email</Label>
              <Input id="email" type="email" placeholder="info@klinik.com" />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="website">Website</Label>
              <Input id="website" placeholder="www.klinik.com" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="sosial_media">Sosial Media</Label>
              <Input id="sosial_media" placeholder="@klinik" />
            </div>
          </div>

          <div className="flex justify-end mt-6">
            <Button className="bg-emerald-600 hover:bg-emerald-700 text-white">
              <Save className="w-4 h-4 mr-2" />
              Simpan Pengaturan
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
};
