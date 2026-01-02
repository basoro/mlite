import { View, Text, StyleSheet, ActivityIndicator } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Activity } from 'lucide-react-native';

export default function SplashScreen() {
  return (
    <LinearGradient
      colors={['#4A90E2', '#5BA3F5', '#6FB6FF']}
      style={styles.container}
    >
      <View style={styles.content}>
        <View style={styles.iconContainer}>
          <Activity color="#FFFFFF" size={80} strokeWidth={2} />
        </View>
        <Text style={styles.title}>mLITE Mobile</Text>
        <Text style={styles.subtitle}>Aplikasi mobile Medic LITE Indonesia</Text>
        <ActivityIndicator
          color="#FFFFFF"
          size="large"
          style={styles.loader}
        />
      </View>
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  content: {
    alignItems: 'center',
  },
  iconContainer: {
    marginBottom: 24,
  },
  title: {
    fontSize: 36,
    fontWeight: '700',
    color: '#FFFFFF',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 16,
    color: '#FFFFFF',
    opacity: 0.9,
  },
  loader: {
    marginTop: 32,
  },
});
