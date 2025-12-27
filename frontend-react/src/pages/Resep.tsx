import React from 'react';
import { Pill, Search, Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const Resep: React.FC = () => {
  return (
    <div className="space-y-6">
      <div className="flex items-start justify-between">
        <div>
          <h1 className="text-3xl font-bold text-foreground">Resep Obat</h1>
          <p className="text-muted-foreground mt-1">Kelola resep obat pasien</p>
        </div>
        <Button className="gap-2">
          <Plus className="w-4 h-4" />
          Buat Resep Baru
        </Button>
      </div>

      <div className="bg-card rounded-xl border border-border p-6">
        <div className="flex items-center gap-3 mb-6">
          <Input placeholder="Cari resep berdasarkan nama pasien atau nomor resep..." className="flex-1" />
          <Button className="gap-2">
            <Search className="w-4 h-4" />
            Cari
          </Button>
        </div>

        <div className="text-center py-12">
          <Pill className="w-12 h-12 mx-auto text-muted-foreground mb-4" />
          <p className="text-muted-foreground">Belum ada resep yang dibuat hari ini</p>
          <Button className="mt-4 gap-2">
            <Plus className="w-4 h-4" />
            Buat Resep Pertama
          </Button>
        </div>
      </div>
    </div>
  );
};

export default Resep;
