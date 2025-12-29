import { useState, useEffect } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { getUsersList, saveUser, deleteUser } from "@/lib/api";
import { useToast } from "@/hooks/use-toast";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
import {
  Search,
  Plus,
  User,
  Users,
  Shield,
  Stethoscope,
  Pencil,
  Key,
  Link as LinkIcon,
  Trash2,
  Loader2,
  Lock,
} from "lucide-react";
import { cn } from "@/lib/utils";

// Types
interface UserData {
  id: string;
  username: string; // Used as email in image
  nama: string;
  role: string;
  status: string; // 'Aktif' | 'Nonaktif'
  telepon: string;
  login_terakhir?: string;
  akses_poli?: string[]; // Assuming array of strings for poli
}

export default function ManajemenUser() {
  const [search, setSearch] = useState("");
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedUser, setSelectedUser] = useState<UserData | null>(null);
  const { toast } = useToast();
  const queryClient = useQueryClient();

  // Fetch Users
  const { data: usersData, isLoading } = useQuery({
    queryKey: ["users", search],
    queryFn: () => getUsersList(1, 100, search),
  });

  const users = usersData?.data || [];

  // Delete Mutation
  const deleteMutation = useMutation({
    mutationFn: (user: UserData) => deleteUser({ id: user.id }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["users"] });
      toast({
        title: "User dihapus",
        description: "Data user berhasil dihapus dari sistem",
      });
    },
    onError: () => {
      toast({
        title: "Gagal menghapus",
        description: "Terjadi kesalahan saat menghapus user",
        variant: "destructive",
      });
    },
  });

  // Stats Calculation
  const stats = {
    total: users.length,
    aktif: users.filter((u: any) => u.status === "Aktif").length,
    dokter: users.filter((u: any) => u.role?.toLowerCase() === "dokter").length,
    staff: users.filter((u: any) => ["staff", "admin", "apoteker"].includes(u.role?.toLowerCase())).length,
  };

  const handleEdit = (user: UserData) => {
    setSelectedUser(user);
    setIsModalOpen(true);
  };

  const handleAdd = () => {
    setSelectedUser(null);
    setIsModalOpen(true);
  };

  const handleDelete = (user: UserData) => {
    if (confirm("Apakah Anda yakin ingin menghapus user ini?")) {
      deleteMutation.mutate(user);
    }
  };

  return (
    <div className="container mx-auto p-6 space-y-6">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Manajemen User</h1>
          <p className="text-muted-foreground">
            Kelola akun pengguna dan hak akses sistem
          </p>
        </div>
        <Button onClick={handleAdd} className="bg-emerald-500 hover:bg-emerald-600">
          <Plus className="mr-2 h-4 w-4" /> Tambah User
        </Button>
      </div>

      {/* Stats Cards */}
      <div className="grid gap-4 md:grid-cols-4">
        <StatsCard
          title="Total User"
          value={stats.total}
          icon={Users}
          color="text-emerald-500"
        />
        <StatsCard
          title="User Aktif"
          value={stats.aktif}
          icon={User}
          color="text-emerald-500"
        />
        <StatsCard
          title="Dokter"
          value={stats.dokter}
          icon={Shield}
          color="text-blue-500"
        />
        <StatsCard
          title="Staff"
          value={stats.staff}
          icon={Users}
          color="text-orange-500"
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left Panel: User List */}
        <div className="lg:col-span-2 space-y-4">
          <Card className="h-full">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Users className="h-5 w-5" />
                Daftar Pengguna
              </CardTitle>
              <CardDescription>
                Kelola semua pengguna yang memiliki akses ke sistem
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="relative">
                <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  type="search"
                  placeholder="Cari berdasarkan nama, email, atau role..."
                  className="pl-8"
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
              </div>

              {isLoading ? (
                <div className="flex justify-center py-8">
                  <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
                </div>
              ) : (
                <div className="space-y-4">
                  {users.map((user: UserData) => (
                    <UserItem
                      key={user.id}
                      user={user}
                      onEdit={() => handleEdit(user)}
                      onDelete={() => handleDelete(user)}
                    />
                  ))}
                  {users.length === 0 && (
                    <div className="text-center py-8 text-muted-foreground">
                      Tidak ada data user ditemukan
                    </div>
                  )}
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Right Panel: Roles */}
        <div className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Shield className="h-5 w-5" />
                Hak Akses Role
              </CardTitle>
              <CardDescription>
                Daftar peran dan hak akses dalam sistem
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <RoleBox
                role="Administrator"
                badgeColor="bg-purple-100 text-purple-800 hover:bg-purple-200"
                permissions={[
                  "Kelola User",
                  "manage_inventory",
                  "manage_billing",
                  "manage_schedule",
                ]}
              />
              <RoleBox
                role="Dokter"
                badgeColor="bg-blue-100 text-blue-800 hover:bg-blue-200"
                permissions={[
                  "view_patients",
                  "write_examinations",
                  "write_prescriptions",
                  "view_schedule",
                ]}
              />
              <RoleBox
                role="Staf"
                badgeColor="bg-gray-100 text-gray-800 hover:bg-gray-200"
                permissions={[
                  "manage_billing",
                  "manage_schedule",
                  "manage_inventory",
                ]}
              />
            </CardContent>
          </Card>
        </div>
      </div>

      {/* User Modal */}
      <UserModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        user={selectedUser}
      />
    </div>
  );
}

// Sub-components

function StatsCard({ title, value, icon: Icon, color }: any) {
  return (
    <Card>
      <CardContent className="p-6 flex items-center justify-between">
        <div>
          <p className="text-sm font-medium text-muted-foreground">{title}</p>
          <h2 className={cn("text-3xl font-bold mt-2", color)}>{value}</h2>
        </div>
        <div className={cn("p-3 rounded-full bg-opacity-10", color.replace('text-', 'bg-'))}>
          <Icon className={cn("h-6 w-6", color)} />
        </div>
      </CardContent>
    </Card>
  );
}

function UserItem({ user, onEdit, onDelete }: { user: UserData; onEdit: () => void; onDelete: () => void }) {
  const roleColors: Record<string, string> = {
    admin: "bg-purple-100 text-purple-800 hover:bg-purple-200",
    dokter: "bg-blue-100 text-blue-800 hover:bg-blue-200",
    staff: "bg-gray-100 text-gray-800 hover:bg-gray-200",
  };

  const badgeColor = roleColors[user.role?.toLowerCase()] || "bg-gray-100 text-gray-800";

  return (
    <div className="flex flex-col sm:flex-row items-start justify-between p-4 border rounded-lg hover:bg-slate-50 transition-colors gap-4">
      <div className="space-y-1 flex-1">
        <div className="flex items-center gap-2 flex-wrap">
          <span className="font-semibold text-lg">{user.nama}</span>
          <Badge className={cn("font-normal", badgeColor)}>{user.role}</Badge>
          <Badge
            variant="outline"
            className={cn(
              "font-normal",
              user.status === "Aktif"
                ? "bg-emerald-50 text-emerald-700 border-emerald-200"
                : "bg-red-50 text-red-700 border-red-200"
            )}
          >
            {user.status}
          </Badge>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-1 text-sm text-muted-foreground mt-2">
          <p>Email: {user.username}</p>
          <p>Telepon: {user.telepon || "-"}</p>
          <p className="font-mono text-xs">ID: {user.id}</p>
          <p>Login Terakhir: {user.login_terakhir || "-"}</p>
        </div>
      </div>
      <div className="flex items-center gap-2">
        <Button variant="outline" size="icon" onClick={onEdit}>
          <Pencil className="h-4 w-4" />
        </Button>
        <Button variant="outline" size="icon">
          <Key className="h-4 w-4" />
        </Button>
        <Button variant="outline" size="icon">
          <LinkIcon className="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          size="icon"
          className="text-red-500 hover:text-red-600 hover:bg-red-50"
          onClick={onDelete}
        >
          <Trash2 className="h-4 w-4" />
        </Button>
      </div>
    </div>
  );
}

function RoleBox({ role, badgeColor, permissions }: any) {
  return (
    <div className="p-4 border rounded-lg space-y-3">
      <div className="flex items-center justify-between">
        <span className="font-semibold">{role}</span>
        <Badge className={cn("font-normal", badgeColor)}>{role}</Badge>
      </div>
      <div>
        <p className="text-sm font-medium mb-2">Hak Akses:</p>
        <ul className="text-sm text-muted-foreground space-y-1 list-disc list-inside">
          {permissions.map((perm: string, i: number) => (
            <li key={i}>{perm}</li>
          ))}
        </ul>
      </div>
    </div>
  );
}

function UserModal({ isOpen, onClose, user }: { isOpen: boolean; onClose: () => void; user: UserData | null }) {
  const queryClient = useQueryClient();
  const { toast } = useToast();
  const [formData, setFormData] = useState({
    nama: "",
    username: "",
    telepon: "",
    role: "Dokter",
    password: "",
    status: "Aktif",
    poli_gigi: false,
    poli_umum: false,
  });

  useEffect(() => {
    if (user) {
      setFormData({
        nama: user.nama || "",
        username: user.username || "",
        telepon: user.telepon || "",
        role: user.role || "Dokter",
        password: "",
        status: user.status || "Aktif",
        poli_gigi: false, // Need to parse user.akses_poli if available
        poli_umum: false,
      });
    } else {
      setFormData({
        nama: "",
        username: "",
        telepon: "",
        role: "Dokter",
        password: "",
        status: "Aktif",
        poli_gigi: false,
        poli_umum: false,
      });
    }
  }, [user, isOpen]);

  const saveMutation = useMutation({
    mutationFn: (data: any) => saveUser(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["users"] });
      toast({
        title: "Berhasil",
        description: `User berhasil ${user ? "diperbarui" : "ditambahkan"}`,
      });
      onClose();
    },
    onError: () => {
      toast({
        title: "Gagal",
        description: "Terjadi kesalahan saat menyimpan data",
        variant: "destructive",
      });
    },
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const payload: any = {
      ...formData,
      ...(user && { id: user.id }),
    };
    // Clean up empty password if editing
    if (user && !payload.password) {
      delete payload.password;
    }
    saveMutation.mutate(payload);
  };

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-2xl">
        <DialogHeader>
          <DialogTitle>{user ? "Edit Pengguna" : "Tambah Pengguna"}</DialogTitle>
          <DialogDescription>
            {user ? "Perbarui informasi pengguna" : "Tambahkan pengguna baru ke sistem"}
          </DialogDescription>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="nama">Nama Lengkap *</Label>
              <Input
                id="nama"
                value={formData.nama}
                onChange={(e) => setFormData({ ...formData, nama: e.target.value })}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="username">Email *</Label>
              <Input
                id="username"
                type="email"
                value={formData.username}
                onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                required
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="telepon">Nomor Telepon</Label>
              <Input
                id="telepon"
                value={formData.telepon}
                onChange={(e) => setFormData({ ...formData, telepon: e.target.value })}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="role">Role *</Label>
              <Select
                value={formData.role}
                onValueChange={(val) => setFormData({ ...formData, role: val })}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Pilih Role" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="Dokter">Dokter</SelectItem>
                  <SelectItem value="Admin">Admin</SelectItem>
                  <SelectItem value="Staff">Staff</SelectItem>
                  <SelectItem value="Apoteker">Apoteker</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label htmlFor="password">
                {user ? "Password Baru (kosongkan jika tidak diubah)" : "Password *"}
              </Label>
              <Input
                id="password"
                type="password"
                placeholder="Masukkan password"
                value={formData.password}
                onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                required={!user}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="status">Status</Label>
              <Select
                value={formData.status}
                onValueChange={(val) => setFormData({ ...formData, status: val })}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Pilih Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="Aktif">Aktif</SelectItem>
                  <SelectItem value="Nonaktif">Nonaktif</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="space-y-3">
            <Label>Poliklinik Akses</Label>
            <div className="flex gap-6 p-4 border rounded-md">
              <div className="flex items-center space-x-2">
                <Checkbox
                  id="poli_gigi"
                  checked={formData.poli_gigi}
                  onCheckedChange={(checked) =>
                    setFormData({ ...formData, poli_gigi: checked as boolean })
                  }
                />
                <Label htmlFor="poli_gigi" className="font-normal cursor-pointer">
                  Poli Gigi
                </Label>
              </div>
              <div className="flex items-center space-x-2">
                <Checkbox
                  id="poli_umum"
                  checked={formData.poli_umum}
                  onCheckedChange={(checked) =>
                    setFormData({ ...formData, poli_umum: checked as boolean })
                  }
                />
                <Label htmlFor="poli_umum" className="font-normal cursor-pointer">
                  Poli Umum
                </Label>
              </div>
            </div>
            <p className="text-xs text-muted-foreground">
              Pilih satu atau lebih poliklinik untuk akses dokter/staf.
            </p>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" onClick={onClose}>
              Batal
            </Button>
            <Button type="submit" className="bg-emerald-500 hover:bg-emerald-600" disabled={saveMutation.isPending}>
              {saveMutation.isPending ? (
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
              ) : (
                <Lock className="mr-2 h-4 w-4" />
              )}
              {user ? "Update User" : "Simpan User"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
