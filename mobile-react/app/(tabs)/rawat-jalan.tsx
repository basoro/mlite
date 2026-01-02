import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl, Modal, TextInput, Platform } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Stethoscope, Calendar, Clock, User, ChevronRight, Filter, X } from 'lucide-react-native';
import { useState, useEffect } from 'react';
import { api } from '@/lib/api';
import { router } from 'expo-router';
import DateTimePicker from '@react-native-community/datetimepicker';

type RawatJalan = {
  no_rawat: string;
  no_rkm_medis: string;
  nm_pasien: string;
  tgl_registrasi: string;
  jam_reg: string;
  nm_poli: string;
  nm_dokter: string;
  status_lanjut: string;
};

export default function RawatJalanScreen() {
  const [data, setData] = useState<RawatJalan[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  
  // Filter states
  const [showFilter, setShowFilter] = useState(false);
  const [startDate, setStartDate] = useState(new Date().toISOString().split('T')[0]);
  const [endDate, setEndDate] = useState(new Date().toISOString().split('T')[0]);
  const [status, setStatus] = useState(''); // 'Ralan' or 'Ranap' or empty

  // DatePicker state
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [datePickerMode, setDatePickerMode] = useState<'start' | 'end'>('start');

  const onDateChange = (event: any, selectedDate?: Date) => {
    if (Platform.OS === 'android') {
      setShowDatePicker(false);
    }
    
    if (selectedDate) {
      const currentDate = selectedDate.toISOString().split('T')[0];
      if (datePickerMode === 'start') {
        setStartDate(currentDate);
      } else {
        setEndDate(currentDate);
      }
    }
  };

  const showDatepicker = (mode: 'start' | 'end') => {
    setDatePickerMode(mode);
    setShowDatePicker(true);
  };

  const fetchData = async (isRefresh = false) => {
    try {
      if (isRefresh) setRefreshing(true);
      else setLoading(true);

      const response = await api.rawatJalan.list({ 
        page: 1, 
        per_page: 20,
        tgl_awal: startDate,
        tgl_akhir: endDate
      });
      
      console.log('API Response Rawat Jalan:', response.data);
      const items = Array.isArray(response.data) ? response.data : (response.data?.data || []);
      setData(items);
    } catch (error) {
      console.error('Error fetching rawat jalan:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const applyFilter = () => {
    setShowFilter(false);
    fetchData();
  };

  const renderItem = ({ item }: { item: RawatJalan }) => (
    <TouchableOpacity 
      style={styles.card}
      onPress={() => {
        // Normalize no_rawat by removing slashes
        const normalizedNoRawat = item.no_rawat.replace(/\//g, '');
        
        router.push(`/detail/rawat-jalan/${normalizedNoRawat}`);
      }}
    >
      <View style={styles.cardHeader}>
        <View style={styles.patientInfo}>
          <Text style={styles.patientName}>{item.nm_pasien}</Text>
          <Text style={styles.patientId}>{item.no_rkm_medis}</Text>
        </View>
        <View style={[styles.badge, { backgroundColor: item.status_lanjut === 'Ralan' ? '#E3F2FD' : '#FFF3E0' }]}>
          <Text style={[styles.badgeText, { color: item.status_lanjut === 'Ralan' ? '#1E88E5' : '#EF6C00' }]}>
            {item.status_lanjut}
          </Text>
        </View>
      </View>

      <View style={styles.divider} />

      <View style={styles.cardBody}>
        <View style={styles.infoRow}>
          <Calendar size={14} color="#666" />
          <Text style={styles.infoText}>{item.tgl_registrasi}</Text>
          <Clock size={14} color="#666" style={{ marginLeft: 8 }} />
          <Text style={styles.infoText}>{item.jam_reg}</Text>
        </View>

        <View style={styles.infoRow}>
          <Stethoscope size={14} color="#666" />
          <Text style={styles.infoText}>{item.nm_poli}</Text>
        </View>

        <View style={styles.infoRow}>
          <User size={14} color="#666" />
          <Text style={styles.infoText}>{item.nm_dokter}</Text>
        </View>
      </View>
      
      <View style={styles.cardFooter}>
        <Text style={styles.rawatId}>{item.no_rawat}</Text>
        <ChevronRight size={16} color="#999" />
      </View>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      <LinearGradient
        colors={['#4A90E2', '#5BA3F5', '#6FB6FF']}
        style={styles.header}
      >
        <View style={styles.headerTop}>
          <View>
            <Text style={styles.headerTitle}>Rawat Jalan</Text>
            <Text style={styles.headerSubtitle}>Kelola pasien rawat jalan</Text>
          </View>
          <TouchableOpacity onPress={() => setShowFilter(true)} style={styles.filterButton}>
            <Filter color="#FFF" size={24} />
          </TouchableOpacity>
        </View>
      </LinearGradient>

      {loading && !refreshing ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#4A90E2" />
        </View>
      ) : (
        <FlatList
          data={data}
          renderItem={renderItem}
          keyExtractor={(item) => item.no_rawat}
          contentContainerStyle={styles.listContainer}
          refreshControl={
            <RefreshControl refreshing={refreshing} onRefresh={() => fetchData(true)} tintColor="#4A90E2" />
          }
          ListEmptyComponent={
            <View style={styles.emptyState}>
              <Stethoscope color="#CCC" size={48} />
              <Text style={styles.emptyText}>Tidak ada data pasien</Text>
            </View>
          }
        />
      )}

      <Modal
        animationType="slide"
        transparent={true}
        visible={showFilter}
        onRequestClose={() => setShowFilter(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Filter Data</Text>
              <TouchableOpacity onPress={() => setShowFilter(false)}>
                <X color="#333" size={24} />
              </TouchableOpacity>
            </View>

            <View style={styles.formGroup}>
              <Text style={styles.label}>Tanggal Awal (YYYY-MM-DD)</Text>
              <TouchableOpacity onPress={() => showDatepicker('start')}>
                <View pointerEvents="none">
                  <TextInput
                    style={styles.input}
                    value={startDate}
                    editable={false}
                    placeholder="2024-01-01"
                  />
                </View>
                <Calendar style={styles.inputIcon} size={20} color="#666" />
              </TouchableOpacity>
            </View>

            <View style={styles.formGroup}>
              <Text style={styles.label}>Tanggal Akhir (YYYY-MM-DD)</Text>
              <TouchableOpacity onPress={() => showDatepicker('end')}>
                <View pointerEvents="none">
                  <TextInput
                    style={styles.input}
                    value={endDate}
                    editable={false}
                    placeholder="2024-01-01"
                  />
                </View>
                <Calendar style={styles.inputIcon} size={20} color="#666" />
              </TouchableOpacity>
            </View>

            {showDatePicker && (
              <DateTimePicker
                value={new Date(datePickerMode === 'start' ? startDate : endDate)}
                mode="date"
                display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                onChange={onDateChange}
                maximumDate={new Date()}
              />
            )}

            <View style={styles.formGroup}>
              <Text style={styles.label}>Status Lanjut</Text>
              <View style={styles.statusContainer}>
                <TouchableOpacity 
                  style={[styles.statusButton, status === '' && styles.statusButtonActive]}
                  onPress={() => setStatus('')}
                >
                  <Text style={[styles.statusText, status === '' && styles.statusTextActive]}>Semua</Text>
                </TouchableOpacity>
                <TouchableOpacity 
                  style={[styles.statusButton, status === 'Ralan' && styles.statusButtonActive]}
                  onPress={() => setStatus('Ralan')}
                >
                  <Text style={[styles.statusText, status === 'Ralan' && styles.statusTextActive]}>Ralan</Text>
                </TouchableOpacity>
                <TouchableOpacity 
                  style={[styles.statusButton, status === 'Ranap' && styles.statusButtonActive]}
                  onPress={() => setStatus('Ranap')}
                >
                  <Text style={[styles.statusText, status === 'Ranap' && styles.statusTextActive]}>Ranap</Text>
                </TouchableOpacity>
              </View>
            </View>

            <TouchableOpacity style={styles.applyButton} onPress={applyFilter}>
              <Text style={styles.applyButtonText}>Terapkan Filter</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F5F5',
  },
  header: {
    paddingTop: 60,
    paddingBottom: 24,
    paddingHorizontal: 24,
    borderBottomLeftRadius: 24,
    borderBottomRightRadius: 24,
  },
  headerTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: '#FFFFFF',
    marginBottom: 4,
  },
  headerSubtitle: {
    fontSize: 14,
    color: '#FFFFFF',
    opacity: 0.9,
  },
  filterButton: {
    padding: 8,
    backgroundColor: 'rgba(255,255,255,0.2)',
    borderRadius: 8,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  listContainer: {
    padding: 16,
    paddingBottom: 80,
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 3,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  patientInfo: {
    flex: 1,
  },
  patientName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 2,
  },
  patientId: {
    fontSize: 12,
    color: '#999',
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
  divider: {
    height: 1,
    backgroundColor: '#F0F0F0',
    marginBottom: 12,
  },
  cardBody: {
    gap: 8,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  infoText: {
    fontSize: 13,
    color: '#666',
    marginLeft: 8,
  },
  cardFooter: {
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#F0F0F0',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  rawatId: {
    fontSize: 12,
    color: '#999',
    fontFamily: 'monospace',
  },
  emptyState: {
    alignItems: 'center',
    justifyContent: 'center',
    padding: 40,
  },
  emptyText: {
    marginTop: 12,
    color: '#999',
    fontSize: 16,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: '#FFF',
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    padding: 24,
    minHeight: 400,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 24,
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#333',
  },
  formGroup: {
    marginBottom: 16,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#666',
    marginBottom: 8,
  },
  input: {
    borderWidth: 1,
    borderColor: '#DDD',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    color: '#333',
    paddingRight: 40,
  },
  inputIcon: {
    position: 'absolute',
    right: 12,
    top: 12,
  },
  statusContainer: {
    flexDirection: 'row',
    gap: 12,
  },
  statusButton: {
    flex: 1,
    padding: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#DDD',
    alignItems: 'center',
  },
  statusButtonActive: {
    backgroundColor: '#4A90E2',
    borderColor: '#4A90E2',
  },
  statusText: {
    fontSize: 14,
    color: '#666',
    fontWeight: '600',
  },
  statusTextActive: {
    color: '#FFF',
  },
  applyButton: {
    backgroundColor: '#4A90E2',
    padding: 16,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 24,
  },
  applyButtonText: {
    color: '#FFF',
    fontSize: 16,
    fontWeight: '700',
  },
});
