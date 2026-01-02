import { Tabs } from 'expo-router';
import { View, StyleSheet, Platform } from 'react-native';
import { Home, Stethoscope, ScanLine, ClipboardList, User } from 'lucide-react-native';
import { useState, useEffect } from 'react';
import { api } from '@/lib/api';
import { BlurView } from 'expo-blur';

function TabBarIcon({ name, color, focused }: { name: string; color: string; focused: boolean }) {
  switch (name) {
    case 'index':
      return <Home size={24} color={color} strokeWidth={focused ? 2.5 : 2} />;
    case 'rawat-jalan':
      return <Stethoscope size={24} color={color} strokeWidth={focused ? 2.5 : 2} />;
    case 'scan':
      return (
        <View style={[styles.scanButton, focused && styles.scanButtonFocused]}>
          <ScanLine size={32} color="#FFFFFF" strokeWidth={2.5} />
        </View>
      );
    case 'rawat-inap':
      return <ClipboardList size={24} color={color} strokeWidth={focused ? 2.5 : 2} />;
    case 'profile':
      return <User size={24} color={color} strokeWidth={focused ? 2.5 : 2} />;
    default:
      return <Home size={24} color={color} />;
  }
}

export default function TabLayout() {
  const [showScan, setShowScan] = useState(false);
  const [ralanCount, setRalanCount] = useState(0);
  const [ranapCount, setRanapCount] = useState(0);

  const fetchCounts = async () => {
    try {
      const today = new Date().toISOString().split('T')[0];
      
      // Fetch Ralan Count (Today)
      const ralanRes = await api.rawatJalan.list({
        tgl_awal: today,
        tgl_akhir: today,
        per_page: 1
      });
      const ralanTotal = (ralanRes.data as any)?.meta?.total || 0;
      setRalanCount(ralanTotal);

      // Fetch Ranap Count
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
      console.error('Error fetching counts:', error);
    }
  };

  useEffect(() => {
    // Check permission or setting to show scan menu
    setShowScan(true);
    
    fetchCounts();
    // Set up interval to refresh counts every minute
    const interval = setInterval(fetchCounts, 60000);
    return () => clearInterval(interval);
  }, []);

  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: '#4A90E2',
        tabBarInactiveTintColor: '#999',
        tabBarStyle: {
          position: 'absolute',
          bottom: 20, // Lift it up from the bottom edge
          left: 20,   // Add margin from left
          right: 20,  // Add margin from right
          elevation: 0,
          height: 70, // Fixed height
          borderRadius: 35, // Fully rounded ends (half of height)
          paddingBottom: 0, // Reset padding
          paddingTop: 0,    // Reset padding
          backgroundColor: Platform.OS === 'ios' ? 'transparent' : '#FFFFFF', // Transparent for glass effect
          borderTopWidth: 0,
          shadowColor: '#000',
          shadowOffset: {
            width: 0,
            height: 10,
          },
          shadowOpacity: 0.1,
          shadowRadius: 10,
        },
        tabBarBackground: () => (
          Platform.OS === 'ios' ? (
            <BlurView 
              intensity={90} 
              style={{
                ...StyleSheet.absoluteFillObject,
                borderRadius: 35, // Match tabBarStyle borderRadius
                overflow: 'hidden',
                backgroundColor: 'rgba(255,255,255,0.7)', // Slightly more opaque for the "liquid" feel
              }} 
              tint="light" 
            />
          ) : undefined
        ),
        tabBarShowLabel: true, // Keep labels
        tabBarItemStyle: {
          justifyContent: 'center',
          alignItems: 'center',
          paddingVertical: 10,
          height: 70,
        },
        tabBarLabelStyle: {
          fontSize: 10,
          fontWeight: '500',
          paddingBottom: 5,
        },
      }}
    >
      <Tabs.Screen
        name="index"
        options={{
          title: 'Home',
          tabBarIcon: ({ color, focused }) => <TabBarIcon name="index" color={color} focused={focused} />,
        }}
      />
      <Tabs.Screen
        name="rawat-jalan"
        options={{
          title: 'Ralan',
          tabBarIcon: ({ color, focused }) => <TabBarIcon name="rawat-jalan" color={color} focused={focused} />,
          tabBarBadge: ralanCount > 0 ? ralanCount : undefined,
          tabBarBadgeStyle: { backgroundColor: '#EF4444', fontSize: 10, minWidth: 16, height: 16, lineHeight: 14 },
        }}
      />
      <Tabs.Screen
        name="scan"
        options={{
          title: '',
          tabBarIcon: ({ color, focused }) => <TabBarIcon name="scan" color={color} focused={focused} />,
          tabBarLabelStyle: { display: 'none' }, // Hide label for scan button
          href: showScan ? '/scan' : null,
        }}
        listeners={() => ({
          tabPress: (e) => {
            // Prevent default action if needed, or handle camera permission
          },
        })}
      />
      <Tabs.Screen
        name="rawat-inap"
        options={{
          title: 'Ranap',
          tabBarIcon: ({ color, focused }) => <TabBarIcon name="rawat-inap" color={color} focused={focused} />,
          tabBarBadge: ranapCount > 0 ? ranapCount : undefined,
          tabBarBadgeStyle: { backgroundColor: '#EF4444', fontSize: 10, minWidth: 16, height: 16, lineHeight: 14 },
        }}
      />
      <Tabs.Screen
        name="profile"
        options={{
          title: 'Profil',
          tabBarIcon: ({ color, focused }) => <TabBarIcon name="profile" color={color} focused={focused} />,
        }}
      />
    </Tabs>
  );
}

const styles = StyleSheet.create({
  scanButton: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#4A90E2',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20, // Lift the button up
    shadowColor: '#4A90E2',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
  },
  scanButtonFocused: {
    backgroundColor: '#357ABD',
    transform: [{ scale: 1.05 }],
  },
});