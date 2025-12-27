import React from 'react';
import { Receipt, Search, Plus, FileText } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const Billing: React.FC = () => {
  return (
    <div className="space-y-6">
      <div className="flex items-start justify-between">
        <div>
          <h1 className="text-3xl font-bold text-foreground">Billing & Pembayaran</h1>
          <p className="text-muted-foreground mt-1">Kelola tagihan dan pembayaran pasien</p>
        </div>
        <Button className="gap-2">
          <Plus className="w-4 h-4" />
          Buat Tagihan Baru
        </Button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="stat-card">
          <p className="text-sm text-muted-foreground">Total Tagihan Hari Ini</p>
          <p className="text-2xl font-bold text-foreground mt-2">Rp 0</p>
        </div>
        <div className="stat-card">
          <p className="text-sm text-muted-foreground">Sudah Dibayar</p>
          <p className="text-2xl font-bold text-primary mt-2">Rp 0</p>
        </div>
        <div className="stat-card">
          <p className="text-sm text-muted-foreground">Belum Dibayar</p>
          <p className="text-2xl font-bold text-destructive mt-2">Rp 0</p>
        </div>
      </div>

      <div className="bg-card rounded-xl border border-border p-6">
        <div className="flex items-center gap-3 mb-6">
          <Input placeholder="Cari tagihan berdasarkan nama pasien atau nomor tagihan..." className="flex-1" />
          <Button className="gap-2">
            <Search className="w-4 h-4" />
            Cari
          </Button>
        </div>

        <div className="text-center py-12">
          <Receipt className="w-12 h-12 mx-auto text-muted-foreground mb-4" />
          <p className="text-muted-foreground">Belum ada tagihan hari ini</p>
        </div>
      </div>
    </div>
  );
};

export default Billing;
