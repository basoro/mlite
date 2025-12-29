import React from 'react';
import { Construction } from 'lucide-react';

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

export const Pengaturan = () => (
  <div className="p-6">
    <h1 className="text-2xl font-bold mb-4">Pengaturan Aplikasi</h1>
    <p>Halaman pengaturan umum aplikasi akan ditampilkan di sini.</p>
  </div>
);
