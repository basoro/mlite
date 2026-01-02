import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Image, RefreshControl } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Stethoscope, ClipboardList, Users, Edit3 } from 'lucide-react-native';
import { useAuth } from '@/contexts/AuthContext';
import { Ionicons, MaterialCommunityIcons, FontAwesome5 } from '@expo/vector-icons';
import { router } from 'expo-router';
import { BlurView } from 'expo-blur';
import { useEffect, useState } from 'react';
import { api } from '@/lib/api';

export default function HomeScreen() {
  const { session } = useAuth();
  const [refreshing, setRefreshing] = useState(false);
  const [ralanCount, setRalanCount] = useState(0);
  const [ranapCount, setRanapCount] = useState(0);
  
  const displayName = session?.fullname || session?.username || 'User';

  const fetchDashboardData = async () => {
    try {
      const today = new Date().toISOString().split('T')[0];
      
      // Fetch Ralan Count (Today)
      // Use per_page=1 to just get metadata 'total'
      const ralanRes = await api.rawatJalan.list({
        tgl_awal: today,
        tgl_akhir: today,
        per_page: 1
      });
      // Handle response structure
      const ralanTotal = (ralanRes.data as any)?.meta?.total || 0;
      setRalanCount(ralanTotal);

      // Fetch Ranap Count (Active: '-' and 'Pindah Kamar')
      const ranapRes = await api.rawatInap.list({
        stts_pulang: '-',
        per_page: 100 // Fetch more to allow grouping locally
      });
      const ranapData = (ranapRes.data as any)?.data || [];
      
      const ranapPindahRes = await api.rawatInap.list({
        stts_pulang: 'Pindah Kamar',
        per_page: 100 // Fetch more to allow grouping locally
      });
      const ranapPindahData = (ranapPindahRes.data as any)?.data || [];

      // Combine and Group by no_rawat
      const allRanap = [...ranapData, ...ranapPindahData];
      const uniqueRanap = new Set(allRanap.map((item: any) => item.no_rawat));

      setRanapCount(uniqueRanap.size);

    } catch (error) {
      console.error('Error fetching dashboard data:', error);
    }
  };

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchDashboardData();
    setRefreshing(false);
  };

  return (
    <ScrollView 
      style={styles.container} 
      showsVerticalScrollIndicator={false}
      bounces={true}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#FFF" />
      }
    >
      {/* Decorative Background Elements for Glass Effect */}
      <View style={[styles.bgBlob, styles.blob1]} />
      <View style={[styles.bgBlob, styles.blob2]} />

      <View style={styles.headerWrapper}>
        <LinearGradient
          colors={['#4A90E2', '#5BA3F5', '#6FB6FF']}
          style={styles.header}
        >
          {/* Header decorative circles */}
          <View style={styles.headerCircle1} />
          <View style={styles.headerCircle2} />

          <View style={styles.headerContent}>
            <TouchableOpacity style={styles.profileButton} onPress={() => router.push('/(tabs)/profile')}>
              <View style={styles.avatar}>
                {/* Use a static image for now to match the design or fallback to text */}
                <Image 
                   source={{ uri: 'https://randomuser.me/api/portraits/women/44.jpg' }} 
                   style={styles.avatarImage} 
                />
              </View>
            </TouchableOpacity>
          </View>

          <View style={styles.greetingSection}>
             <Text style={styles.greeting}>Halo,</Text>
             <Text style={styles.name} numberOfLines={1} ellipsizeMode="tail">
               {displayName}
             </Text>
          </View>
        </LinearGradient>
          
        <View style={styles.bannerContainer}>
          <LinearGradient
            colors={['#1E3A8A', '#2563EB']} // Darker blue gradient
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.bannerGradient}
          >
            <View style={styles.bannerContent}>
              <View style={{ flex: 1, paddingRight: 60 }}>
                <Text style={styles.bannerSubtitleSmall}>Selamat Hari Jadi</Text>
                <Text style={styles.bannerSubtitleSmall}>yang ke 66 tahun</Text>
                <Text style={styles.bannerTitleLarge}>Kabupaten Hulu Sungai Tengah</Text>
              </View>
              <View style={styles.bannerIconContainer}>
                  <View style={styles.bannerIconCircle}>
                    <FontAwesome5 name="user-md" size={60} color="#FFFFFF" />
                  </View>
              </View>
            </View>
          </LinearGradient>
        </View>
      </View>

      <View style={styles.menuContainer}>
        <View style={styles.menuGrid}>
          <TouchableOpacity 
            style={styles.menuCard}
            onPress={() => router.push('/(tabs)/rawat-jalan')}
          >
            <View style={[styles.menuCardContent, { backgroundColor: '#3B82F6' }]}>
              <View style={styles.menuIconCircle}>
                <Stethoscope color="#3B82F6" size={24} strokeWidth={2.5} />
              </View>
              <View style={styles.menuTextContent}>
                <Text style={styles.menuTitle}>Rawat Jalan</Text>
                <Text style={styles.menuSubtitle}>Kelola pasien rawat jalan</Text>
              </View>
            </View>
          </TouchableOpacity>

          <TouchableOpacity 
            style={styles.menuCard}
            onPress={() => router.push('/(tabs)/rawat-inap')}
          >
             <View style={[styles.menuCardContent, { backgroundColor: '#3B82F6' }]}>
              <View style={styles.menuIconCircle}>
                <ClipboardList color="#3B82F6" size={24} strokeWidth={2.5} />
              </View>
              <View style={styles.menuTextContent}>
                <Text style={styles.menuTitle}>Rawat Inap</Text>
                <Text style={styles.menuSubtitle}>Kelola pasien rawat inap</Text>
              </View>
            </View>
          </TouchableOpacity>

          <TouchableOpacity 
            style={styles.menuCard}
            onPress={() => router.push('/(tabs)/kepegawaian')}
          >
             <View style={[styles.menuCardContent, { backgroundColor: '#3B82F6' }]}>
              <View style={styles.menuIconCircle}>
                <Users color="#3B82F6" size={24} strokeWidth={2.5} />
              </View>
              <View style={styles.menuTextContent}>
                <Text style={styles.menuTitle}>Pegawai</Text>
                <Text style={styles.menuSubtitle}>Data kepegawaian pengguna</Text>
              </View>
            </View>
          </TouchableOpacity>

          <TouchableOpacity 
            style={styles.menuCard}
            onPress={() => router.push('/(tabs)/presensi')}
          >
             <View style={[styles.menuCardContent, { backgroundColor: '#3B82F6' }]}>
              <View style={styles.menuIconCircle}>
                <Edit3 color="#3B82F6" size={24} strokeWidth={2.5} />
              </View>
              <View style={styles.menuTextContent}>
                <Text style={styles.menuTitle}>Presensi</Text>
                <Text style={styles.menuSubtitle}>Presensi kehadiran karyawan</Text>
              </View>
            </View>
          </TouchableOpacity>
        </View>
      </View>
      
      <View style={{ height: 100 }} /> 
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F5F5',
  },
  bgBlob: {
    position: 'absolute',
    borderRadius: 999,
    backgroundColor: '#4A90E2',
    opacity: 0.1,
  },
  blob1: {
    width: 300,
    height: 300,
    top: 200,
    left: -100,
  },
  blob2: {
    width: 250,
    height: 250,
    top: 400,
    right: -80,
  },
  headerWrapper: {
    position: 'relative',
    marginBottom: 24,
  },
  header: {
    paddingTop: 60,
    paddingBottom: 40,
    paddingHorizontal: 24,
    borderBottomLeftRadius: 30,
    borderBottomRightRadius: 30,
  },
  headerContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  greetingSection: {
    marginBottom: 40,
  },
  headerCircle1: {
    position: 'absolute',
    top: -50,
    right: -50,
    width: 200,
    height: 200,
    borderRadius: 100,
    backgroundColor: 'rgba(255,255,255,0.1)',
  },
  headerCircle2: {
    position: 'absolute',
    top: 50,
    right: -20,
    width: 150,
    height: 150,
    borderRadius: 75,
    backgroundColor: 'rgba(255,255,255,0.05)',
  },
  greetingContainer: {
    flex: 1,
    marginLeft: 16,
  },
  avatarImage: {
    width: 40,
    height: 40,
    borderRadius: 20,
  },
  bannerSubtitleSmall: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.9)',
    marginBottom: 2,
  },
  bannerTitleLarge: {
    fontSize: 18,
    fontWeight: '800', // Extra bold
    color: '#FFFFFF',
    fontStyle: 'italic', // Italic as seen in image
    marginTop: 4,
  },
  bannerIconContainer: {
    position: 'absolute',
    right: -30,
    bottom: -40,
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 1,
  },
  bannerIconCircle: {
    width: 120, // Increased size
    height: 120, // Increased size
    borderRadius: 60,
    backgroundColor: 'rgba(255,255,255,0.15)', // Lighter background
    justifyContent: 'center',
    alignItems: 'center',
  },
  menuCardContent: {
    flex: 1,
    padding: 16,
    borderRadius: 20,
    justifyContent: 'space-between',
    alignItems: 'center', // Center icon
    minHeight: 140,
  },
  menuIconCircle: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#FFFFFF',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
  },
  menuTextContent: {
    alignItems: 'center',
  },
  menuSubtitle: {
    fontSize: 10, // Reduced from 11
    color: 'rgba(255,255,255,0.8)',
    textAlign: 'center',
    marginTop: 2,
  },
  // Re-adding missing styles
  profileButton: {
    marginLeft: 0,
    marginRight: 16,
  },
  avatar: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: 'rgba(255,255,255,0.2)',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#FFFFFF',
    overflow: 'hidden',
  },
  greeting: {
    fontSize: 16,
    color: '#FFFFFF',
    fontWeight: '500',
    marginBottom: 2,
  },
  name: {
    fontSize: 24,
    fontWeight: '800',
    color: '#FFFFFF',
  },
  bannerContainer: {
    marginHorizontal: 24,
    borderRadius: 25,
    elevation: 8,
    shadowColor: '#1E3A8A',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    marginTop: -50,
    backgroundColor: 'transparent',
    overflow: 'hidden',
    zIndex: 10, // Ensure it sits above other elements if needed
  },
  bannerGradient: {
    padding: 24,
    borderRadius: 25,
    overflow: 'hidden', // Ensure icon is clipped by gradient border
  },
  bannerContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  menuContainer: {
    paddingHorizontal: 24,
  },
  menuGrid: {
    gap: 12, // Reduced gap from 16
    flexDirection: 'row',
    flexWrap: 'wrap',
  },
  menuCard: {
    width: '48%', // Slightly wider to fill space with smaller gap
    borderRadius: 20,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 4,
    backgroundColor: 'white', 
  },
  menuTitle: {
    fontSize: 14, // Reduced from 16 to fit better
    fontWeight: '700',
    color: '#FFFFFF',
    marginBottom: 2,
    textAlign: 'center', // Ensure text centers if it wraps
  },
  badge: {
    position: 'absolute',
    top: 12,
    right: 12,
    backgroundColor: '#EF4444',
    borderRadius: 12,
    paddingHorizontal: 8,
    paddingVertical: 2,
    minWidth: 24,
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 10,
    borderWidth: 2,
    borderColor: '#FFFFFF',
  },
  badgeText: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '700',
  },
});