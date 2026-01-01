import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { AuthProvider } from "@/contexts/AuthContext";
import MainLayout from "@/components/layout/MainLayout";
import Login from "@/pages/Login";
import Dashboard from "@/pages/Dashboard";
import Pasien from "@/pages/Pasien";
import Pendaftaran from "@/pages/Pendaftaran";
import Poliklinik from "@/pages/Poliklinik";
import KamarInap from "@/pages/KamarInap";
import Farmasi from "@/pages/Farmasi";
import Inventory from "@/pages/Inventory";
import MasterData from "@/pages/MasterData";
import Laporan from "@/pages/Laporan";
import Kasir from "@/pages/Kasir";
import { 
  Integrasi, 
  ManajemenUser, 
  Pengaturan 
} from "@/pages/Placeholders";
import NotFound from "./pages/NotFound";

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <AuthProvider>
        <Toaster />
        <Sonner />
        <BrowserRouter>
          <Routes>
            <Route path="/login" element={<Login />} />
            <Route
              path="/"
              element={
                <MainLayout>
                  <Dashboard />
                </MainLayout>
              }
            />
            <Route
              path="/pasien"
              element={
                <MainLayout>
                  <Pasien />
                </MainLayout>
              }
            />
            <Route
              path="/pendaftaran"
              element={
                <MainLayout>
                  <Pendaftaran />
                </MainLayout>
              }
            />
            <Route
              path="/poliklinik"
              element={
                <MainLayout>
                  <Poliklinik />
                </MainLayout>
              }
            />
            <Route
              path="/kamar-inap"
              element={
                <MainLayout>
                  <KamarInap />
                </MainLayout>
              }
            />
            <Route
              path="/apotek"
              element={
                <MainLayout>
                  <Farmasi />
                </MainLayout>
              }
            />
            <Route
              path="/kasir"
              element={
                <MainLayout>
                  <Kasir />
                </MainLayout>
              }
            />
            <Route
              path="/integrasi"
              element={
                <MainLayout>
                  <Integrasi />
                </MainLayout>
              }
            />
            <Route
              path="/inventory"
              element={
                <MainLayout>
                  <Inventory />
                </MainLayout>
              }
            />
            <Route
              path="/master-data"
              element={
                <MainLayout>
                  <MasterData />
                </MainLayout>
              }
            />
            <Route
              path="/laporan"
              element={
                <MainLayout>
                  <Laporan />
                </MainLayout>
              }
            />
            <Route
              path="/manajemen-user"
              element={
                <MainLayout>
                  <ManajemenUser />
                </MainLayout>
              }
            />
            <Route
              path="/pengaturan"
              element={
                <MainLayout>
                  <Pengaturan />
                </MainLayout>
              }
            />
            <Route path="*" element={<NotFound />} />
          </Routes>
        </BrowserRouter>
      </AuthProvider>
    </TooltipProvider>
  </QueryClientProvider>
);

export default App;
