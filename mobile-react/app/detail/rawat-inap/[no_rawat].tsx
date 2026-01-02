import { View, Text, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity, Modal, TextInput, Animated, TouchableWithoutFeedback } from 'react-native';
import { useLocalSearchParams, Stack, router } from 'expo-router';
import { useEffect, useState, useRef } from 'react';
import { api } from '@/lib/api';
import { LinearGradient } from 'expo-linear-gradient';
import { User, Calendar, Clock, Bed, FileText, AlertCircle, ChevronLeft, Activity, Pill, Thermometer, Plus, X, FlaskConical, Scan, Book } from 'lucide-react-native';

export default function RawatInapDetailScreen() {
  const { no_rawat } = useLocalSearchParams();
  const [data, setData] = useState<any>(null);
  const [tindakan, setTindakan] = useState<any[]>([]);
  const [soap, setSoap] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  
  // Menu State
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [activeModal, setActiveModal] = useState<string | null>(null);
  const fadeAnim = useRef(new Animated.Value(0)).current;

  const toggleMenu = () => {
    if (isMenuOpen) {
      Animated.timing(fadeAnim, {
        toValue: 0,
        duration: 200,
        useNativeDriver: true,
      }).start(() => setIsMenuOpen(false));
    } else {
      setIsMenuOpen(true);
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 200,
        useNativeDriver: true,
      }).start();
    }
  };

  const handleAction = (action: string) => {
    toggleMenu();
    setActiveModal(action);
  };

  const renderActionModal = () => {
    if (!activeModal) return null;

    let title = '';
    switch(activeModal) {
      case 'soap': title = 'Input SOAP'; break;
      case 'tindakan': title = 'Input Tindakan'; break;
      case 'lab': title = 'Permintaan Lab'; break;
      case 'rad': title = 'Permintaan Radiologi'; break;
      case 'resep': title = 'Input Resep'; break;
      case 'icd': title = 'Input Diagnosa (ICD)'; break;
    }

    const renderFormContent = () => {
      if (activeModal === 'soap') {
        return (
          <View>
            <Text style={styles.formSectionTitle}>Tanda Vital</Text>
            <View style={styles.formRow}>
              <View style={[styles.formGroup, { flex: 1, marginRight: 8 }]}>
                <Text style={styles.label}>Suhu (°C)</Text>
                <TextInput style={styles.input} placeholder="36.5" keyboardType="numeric" />
              </View>
              <View style={[styles.formGroup, { flex: 1, marginLeft: 8 }]}>
                <Text style={styles.label}>Tensi (mmHg)</Text>
                <TextInput style={styles.input} placeholder="120/80" />
              </View>
            </View>
            <View style={styles.formRow}>
              <View style={[styles.formGroup, { flex: 1, marginRight: 8 }]}>
                <Text style={styles.label}>Nadi (x/mnt)</Text>
                <TextInput style={styles.input} placeholder="80" keyboardType="numeric" />
              </View>
              <View style={[styles.formGroup, { flex: 1, marginLeft: 8 }]}>
                <Text style={styles.label}>Respirasi (x/mnt)</Text>
                <TextInput style={styles.input} placeholder="20" keyboardType="numeric" />
              </View>
            </View>
            <View style={styles.formRow}>
              <View style={[styles.formGroup, { flex: 1, marginRight: 8 }]}>
                <Text style={styles.label}>Tinggi (cm)</Text>
                <TextInput style={styles.input} placeholder="165" keyboardType="numeric" />
              </View>
              <View style={[styles.formGroup, { flex: 1, marginLeft: 8 }]}>
                <Text style={styles.label}>Berat (kg)</Text>
                <TextInput style={styles.input} placeholder="60" keyboardType="numeric" />
              </View>
            </View>
            <View style={styles.formGroup}>
              <Text style={styles.label}>GCS (E,V,M)</Text>
              <TextInput style={styles.input} placeholder="4,5,6" />
            </View>

            <Text style={[styles.formSectionTitle, { marginTop: 16 }]}>Pemeriksaan (SOAP)</Text>
            <View style={styles.formGroup}>
              <Text style={styles.label}>Keluhan (Subjective)</Text>
              <TextInput style={[styles.input, styles.textArea]} multiline placeholder="Keluhan pasien..." />
            </View>
            <View style={styles.formGroup}>
              <Text style={styles.label}>Pemeriksaan (Objective)</Text>
              <TextInput style={[styles.input, styles.textArea]} multiline placeholder="Hasil pemeriksaan fisik..." />
            </View>
            <View style={styles.formGroup}>
              <Text style={styles.label}>Penilaian (Assessment)</Text>
              <TextInput style={[styles.input, styles.textArea]} multiline placeholder="Diagnosa kerja..." />
            </View>
            <View style={styles.formGroup}>
              <Text style={styles.label}>Instruksi (Plan)</Text>
              <TextInput style={[styles.input, styles.textArea]} multiline placeholder="Rencana pengobatan/tindakan..." />
            </View>
          </View>
        );
      }

      // Default placeholder for other forms
      return (
        <View>
           <Text style={{ textAlign: 'center', marginTop: 20, color: '#666' }}>
             Form untuk {title} akan muncul di sini.
           </Text>
           <Text style={{ textAlign: 'center', marginTop: 10, color: '#999', fontSize: 12 }}>
             Endpoint API sedang disiapkan.
           </Text>
        </View>
      );
    };

    return (
      <Modal
        visible={!!activeModal}
        transparent={true}
        animationType="slide"
        onRequestClose={() => setActiveModal(null)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>{title}</Text>
              <TouchableOpacity onPress={() => setActiveModal(null)}>
                <X color="#333" size={24} />
              </TouchableOpacity>
            </View>
            <ScrollView style={styles.modalBody}>
               {renderFormContent()}
            </ScrollView>
            <View style={styles.modalFooter}>
              <TouchableOpacity 
                style={[styles.btn, styles.btnOutline]} 
                onPress={() => setActiveModal(null)}
              >
                <Text style={styles.btnTextOutline}>Batal</Text>
              </TouchableOpacity>
              <TouchableOpacity style={[styles.btn, styles.btnPrimary]}>
                <Text style={styles.btnTextPrimary}>Simpan</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    );
  };

  useEffect(() => {
    const fetchData = async () => {
      try {
        if (no_rawat) {
          // Use the ID directly as requested (no slashes)
          const apiNoRawat = Array.isArray(no_rawat) ? no_rawat[0] : no_rawat;

          const [detailRes, tindakanRes, soapRes] = await Promise.all([
            api.rawatInap.show(apiNoRawat),
            api.rawatInap.showTindakan(apiNoRawat).catch(() => ({ data: {} })),
            api.rawatInap.showSoap(apiNoRawat).catch(() => ({ data: [] }))
          ]);

          // Handle response structure: { status: 'success', data: { ... } }
          const patientData = detailRes.data?.data || detailRes.data || detailRes;
          setData(patientData);
          
          const tindakanData = tindakanRes.data || {};
          const mergedTindakan = [
            ...(tindakanData.rawat_jl_dr || []),
            ...(tindakanData.rawat_jl_pr || []),
            ...(tindakanData.rawat_jl_drpr || [])
          ];
          setTindakan(mergedTindakan);
          
          setSoap(soapRes.data || []);
        }
      } catch (error) {
        console.error('Error fetching detail:', error);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [no_rawat]);

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#4A90E2" />
      </View>
    );
  }

  if (!data) {
    return (
      <View style={styles.loadingContainer}>
        <Text>Data tidak ditemukan</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Stack.Screen options={{ headerShown: false }} />
      
      <LinearGradient
        colors={['#4A90E2', '#5BA3F5']}
        style={styles.header}
      >
        <View style={styles.headerContent}>
          <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
            <ChevronLeft color="#FFF" size={24} />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Detail Rawat Inap</Text>
          <View style={{ width: 24 }} />
        </View>
      </LinearGradient>

      <ScrollView contentContainerStyle={styles.content}>
        <LinearGradient
          colors={['#4A90E2', '#5BA3F5']}
          style={styles.headerCard}
        >
          <View style={styles.patientIcon}>
             <User color="#4A90E2" size={32} />
          </View>
          <Text style={styles.patientName}>{data.nm_pasien}</Text>
          <Text style={styles.patientId}>{data.no_rkm_medis}</Text>
          <Text style={styles.registrationInfo}>
             {data.no_rawat} • {data.tgl_masuk}
          </Text>
        </LinearGradient>

        <View style={styles.section}>
           <Text style={styles.sectionTitle}>Informasi Kamar</Text>
           <View style={styles.infoCard}>
             <View style={styles.infoRow}>
               <View style={styles.infoIcon}>
                 <Bed color="#4A90E2" size={20} />
               </View>
               <View style={styles.infoContent}>
                 <Text style={styles.infoLabel}>Kamar</Text>
                 <Text style={styles.infoValue}>{data.nm_kamar}</Text>
               </View>
             </View>
             
             <View style={styles.divider} />
             
             <View style={styles.infoRow}>
               <View style={styles.infoIcon}>
                 <User color="#4A90E2" size={20} />
               </View>
               <View style={styles.infoContent}>
                 <Text style={styles.infoLabel}>Dokter</Text>
                 <Text style={styles.infoValue}>{data.nm_dokter}</Text>
               </View>
             </View>

             <View style={styles.divider} />

             <View style={styles.infoRow}>
               <View style={styles.infoIcon}>
                 <AlertCircle color="#4A90E2" size={20} />
               </View>
               <View style={styles.infoContent}>
                 <Text style={styles.infoLabel}>Cara Bayar</Text>
                 <Text style={styles.infoValue}>{data.png_jawab}</Text>
               </View>
             </View>
           </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Pemeriksaan (SOAP)</Text>
          {soap.length > 0 ? (
            soap.map((item: any, index: number) => (
              <View key={index} style={styles.card}>
                <View style={styles.soapHeader}>
                   <Clock size={14} color="#666" />
                   <Text style={styles.soapDate}>{item.tgl_perawatan} {item.jam_rawat}</Text>
                </View>
                
                <View style={styles.soapRow}>
                   <View style={[styles.badge, { backgroundColor: '#E3F2FD' }]}>
                     <Text style={[styles.badgeText, { color: '#1E88E5' }]}>Suhu: {item.suhu_tubuh}°C</Text>
                   </View>
                   <View style={[styles.badge, { backgroundColor: '#FFF3E0' }]}>
                     <Text style={[styles.badgeText, { color: '#EF6C00' }]}>Tensi: {item.tensi}</Text>
                   </View>
                   <View style={[styles.badge, { backgroundColor: '#E8F5E9' }]}>
                     <Text style={[styles.badgeText, { color: '#2E7D32' }]}>Nadi: {item.nadi}</Text>
                   </View>
                </View>

                <View style={styles.soapSection}>
                  <Text style={styles.soapSectionTitle}>Keluhan (S):</Text>
                  <Text style={styles.soapSectionText}>{item.keluhan}</Text>
                </View>
                <View style={styles.soapSection}>
                  <Text style={styles.soapSectionTitle}>Pemeriksaan (O):</Text>
                  <Text style={styles.soapSectionText}>{item.pemeriksaan}</Text>
                </View>
                <View style={styles.soapSection}>
                  <Text style={styles.soapSectionTitle}>Penilaian (A):</Text>
                  <Text style={styles.soapSectionText}>{item.penilaian}</Text>
                </View>
                <View style={styles.soapSection}>
                  <Text style={styles.soapSectionTitle}>Instruksi (P):</Text>
                  <Text style={styles.soapSectionText}>{item.instruksi}</Text>
                </View>
              </View>
            ))
          ) : (
            <View style={styles.emptyState}>
              <Text style={styles.emptyText}>Belum ada data pemeriksaan</Text>
            </View>
          )}
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Tindakan</Text>
          {tindakan.length > 0 ? (
            tindakan.map((item: any, index: number) => (
              <View key={index} style={styles.card}>
                <Text style={styles.tindakanName}>{item.nm_perawatan}</Text>
                <View style={styles.tindakanMeta}>
                  <Text style={styles.tindakanInfo}>
                    {item.tgl_rawat} {item.jam_rawat}
                  </Text>
                  <Text style={styles.tindakanInfo}>
                     {item.nm_dokter || item.nm_petugas}
                  </Text>
                </View>
                <Text style={styles.tindakanCost}>
                   Rp {parseInt(item.biaya_rawat).toLocaleString('id-ID')}
                </Text>
              </View>
            ))
          ) : (
             <View style={styles.emptyState}>
               <Text style={styles.emptyText}>Belum ada data tindakan</Text>
             </View>
          )}
        </View>
      </ScrollView>

      {/* Floating Action Button & Menu */}
      {isMenuOpen && (
        <TouchableWithoutFeedback onPress={toggleMenu}>
          <View style={styles.menuOverlay} />
        </TouchableWithoutFeedback>
      )}

      {isMenuOpen && (
        <Animated.View style={[styles.menuContainer, { opacity: fadeAnim }]}>
          <TouchableOpacity style={styles.menuItem} onPress={() => handleAction('soap')}>
            <View style={[styles.menuIconContainer, { backgroundColor: '#E3F2FD' }]}>
              <FileText color="#1E88E5" size={20} />
            </View>
            <Text style={styles.menuLabel}>SOAP</Text>
          </TouchableOpacity>

          <TouchableOpacity style={styles.menuItem} onPress={() => handleAction('tindakan')}>
            <View style={[styles.menuIconContainer, { backgroundColor: '#F3E5F5' }]}>
              <Activity color="#8E24AA" size={20} />
            </View>
            <Text style={styles.menuLabel}>Tindakan</Text>
          </TouchableOpacity>

          <TouchableOpacity style={styles.menuItem} onPress={() => handleAction('lab')}>
            <View style={[styles.menuIconContainer, { backgroundColor: '#E8F5E9' }]}>
              <FlaskConical color="#43A047" size={20} />
            </View>
            <Text style={styles.menuLabel}>Lab</Text>
          </TouchableOpacity>

          <TouchableOpacity style={styles.menuItem} onPress={() => handleAction('rad')}>
            <View style={[styles.menuIconContainer, { backgroundColor: '#FFF3E0' }]}>
              <Scan color="#FB8C00" size={20} />
            </View>
            <Text style={styles.menuLabel}>Radiologi</Text>
          </TouchableOpacity>

          <TouchableOpacity style={styles.menuItem} onPress={() => handleAction('resep')}>
            <View style={[styles.menuIconContainer, { backgroundColor: '#FFEBEE' }]}>
              <Pill color="#E53935" size={20} />
            </View>
            <Text style={styles.menuLabel}>Resep</Text>
          </TouchableOpacity>
          
          <TouchableOpacity style={styles.menuItem} onPress={() => handleAction('icd')}>
            <View style={[styles.menuIconContainer, { backgroundColor: '#E0F7FA' }]}>
              <Book color="#00ACC1" size={20} />
            </View>
            <Text style={styles.menuLabel}>ICD</Text>
          </TouchableOpacity>
        </Animated.View>
      )}

      <TouchableOpacity 
        style={[styles.fab, isMenuOpen && styles.fabActive]} 
        onPress={toggleMenu}
        activeOpacity={0.8}
      >
        {isMenuOpen ? (
          <X color="#FFF" size={24} />
        ) : (
          <Plus color="#FFF" size={24} />
        )}
      </TouchableOpacity>

      {renderActionModal()}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F5F5',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    paddingTop: 60,
    paddingBottom: 20,
    paddingHorizontal: 24,
  },
  headerContent: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  backButton: {
    padding: 4,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  content: {
    padding: 24,
    paddingBottom: 100, // Add padding for FAB
  },
  headerCard: {
    borderRadius: 20,
    padding: 24,
    alignItems: 'center',
    marginBottom: 24,
    shadowColor: '#4A90E2',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 6,
  },
  patientIcon: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#FFF',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  patientName: {
    fontSize: 20,
    fontWeight: '700',
    color: '#FFF',
    marginBottom: 4,
    textAlign: 'center',
  },
  patientId: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.9)',
    marginBottom: 8,
  },
  registrationInfo: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.8)',
    backgroundColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 12,
    overflow: 'hidden',
  },
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#333',
    marginBottom: 16,
  },
  infoCard: {
    backgroundColor: '#FFF',
    borderRadius: 16,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  infoIcon: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#F5F9FF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 16,
  },
  infoContent: {
    flex: 1,
  },
  infoLabel: {
    fontSize: 12,
    color: '#999',
    marginBottom: 2,
  },
  infoValue: {
    fontSize: 14,
    fontWeight: '500',
    color: '#333',
  },
  divider: {
    height: 1,
    backgroundColor: '#F0F0F0',
    marginVertical: 12,
    marginLeft: 56,
  },
  card: {
    backgroundColor: '#FFF',
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  emptyState: {
    padding: 24,
    alignItems: 'center',
    backgroundColor: '#FFF',
    borderRadius: 12,
  },
  emptyText: {
    color: '#999',
  },
  soapHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  soapDate: {
    marginLeft: 8,
    fontSize: 14,
    fontWeight: '600',
    color: '#4A90E2',
  },
  soapRow: {
    flexDirection: 'row',
    marginBottom: 4,
    gap: 8,
    flexWrap: 'wrap',
  },
  badge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  badgeText: {
    fontSize: 10,
    fontWeight: '600',
  },
  soapSection: {
    marginTop: 12,
  },
  soapSectionTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: '#333',
    marginBottom: 4,
  },
  soapSectionText: {
    fontSize: 13,
    color: '#555',
    lineHeight: 20,
  },
  tindakanName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 4,
  },
  tindakanMeta: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  tindakanInfo: {
    fontSize: 12,
    color: '#999',
  },
  tindakanCost: {
    fontSize: 14,
    fontWeight: '700',
    color: '#4A90E2',
    textAlign: 'right',
  },
  // FAB & Menu Styles
  fab: {
    position: 'absolute',
    bottom: 24,
    right: 24,
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: '#4A90E2',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#4A90E2',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 6,
    zIndex: 100,
  },
  fabActive: {
    backgroundColor: '#333',
  },
  menuOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0,0,0,0.3)',
    zIndex: 90,
  },
  menuContainer: {
    position: 'absolute',
    bottom: 90,
    right: 24,
    backgroundColor: '#FFF',
    borderRadius: 12,
    padding: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 5,
    zIndex: 95,
    minWidth: 160,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    paddingHorizontal: 12,
  },
  menuIconContainer: {
    width: 32,
    height: 32,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  menuLabel: {
    fontSize: 14,
    fontWeight: '500',
    color: '#333',
  },
  // Modal Styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: '#FFF',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    minHeight: '50%',
    maxHeight: '80%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#EEE',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#333',
  },
  modalBody: {
    padding: 16,
  },
  modalFooter: {
    flexDirection: 'row',
    padding: 16,
    borderTopWidth: 1,
    borderTopColor: '#EEE',
    gap: 12,
  },
  btn: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
  },
  btnOutline: {
    borderWidth: 1,
    borderColor: '#DDD',
    backgroundColor: '#FFF',
  },
  btnPrimary: {
    backgroundColor: '#4A90E2',
  },
  btnTextOutline: {
    color: '#666',
    fontWeight: '600',
  },
  btnTextPrimary: {
    color: '#FFF',
    fontWeight: '600',
  },
  // Form Styles
  formGroup: {
    marginBottom: 16,
  },
  formRow: {
    flexDirection: 'row',
    marginBottom: 0,
  },
  label: {
    fontSize: 14,
    color: '#333',
    marginBottom: 6,
    fontWeight: '500',
  },
  input: {
    backgroundColor: '#F5F5F5',
    borderWidth: 1,
    borderColor: '#E0E0E0',
    borderRadius: 8,
    padding: 12,
    fontSize: 14,
    color: '#333',
  },
  textArea: {
    height: 80,
    textAlignVertical: 'top',
  },
  formSectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#4A90E2',
    marginBottom: 16,
    paddingBottom: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#EEE',
  },
});