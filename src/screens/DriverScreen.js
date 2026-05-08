import React, {useState, useEffect, useRef} from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Alert,
  FlatList,
  ActivityIndicator,
  StatusBar,
  ScrollView,
  Modal,
  TextInput,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import Geolocation from '@react-native-community/geolocation';
import {PermissionsAndroid} from 'react-native';
import api from '../api/axios';
import {useAuth} from '../context/AuthContext';

export default function DriverScreen() {
  const {user, logout} = useAuth();
  const [jeeps, setJeeps] = useState([]);
  const [selectedJeep, setSelectedJeep] = useState(null);
  const [tracking, setTracking] = useState(false);
  const [location, setLocation] = useState(null);
  const [loadingJeeps, setLoadingJeeps] = useState(true);
  const [updateCount, setUpdateCount] = useState(0);
  const [showAddModal, setShowAddModal] = useState(false);
  const [adding, setAdding] = useState(false);
  const [form, setForm] = useState({name: '', plate_number: '', route_name: '', capacity: ''});
  const watchRef = useRef(null);

  useEffect(() => {
    fetchJeeps();
    return () => stopTracking();
  }, []);

  async function fetchJeeps() {
    try {
      const {data} = await api.get('/jeeps');
      setJeeps(data.data ?? data);
    } catch {
      Alert.alert('Error', 'Failed to load jeeps.');
    } finally {
      setLoadingJeeps(false);
    }
  }

  async function requestLocationPermission() {
    if (Platform.OS === 'android') {
      const granted = await PermissionsAndroid.request(
        PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION,
      );
      return granted === PermissionsAndroid.RESULTS.GRANTED;
    }
    return true;
  }

  async function startTracking() {
    if (!selectedJeep) {
      Alert.alert('No Jeep Selected', 'Please select a jeep before tracking.');
      return;
    }
    const hasPermission = await requestLocationPermission();
    if (!hasPermission) {
      Alert.alert('Permission Denied', 'Location permission is required to track.');
      return;
    }
    setTracking(true);
    const jeepId = selectedJeep.id;
    watchRef.current = Geolocation.watchPosition(
      async pos => {
        const coords = {
          latitude: pos.coords.latitude,
          longitude: pos.coords.longitude,
          speed: pos.coords.speed ?? 0,
          heading: pos.coords.heading ?? 0,
        };
        setLocation(coords);
        setUpdateCount(c => c + 1);
        try {
          await api.post(`/jeeps/${jeepId}/location`, coords);
        } catch (e) {
          console.warn('Location send failed:', e.message);
        }
      },
      err => console.warn('GPS error:', err.message),
      {
        enableHighAccuracy: false,
        distanceFilter: 0,
        interval: 5000,
        fastestInterval: 3000,
      },
    );
  }

  async function stopTracking() {
    if (watchRef.current !== null) {
      Geolocation.clearWatch(watchRef.current);
      watchRef.current = null;
    }
    setTracking(false);
    setLocation(null);
    setUpdateCount(0);
    if (selectedJeep) {
      try {
        await api.patch(`/jeeps/${selectedJeep.id}`, {status: 'inactive'});
        setJeeps(prev => prev.map(j => j.id === selectedJeep.id ? {...j, status: 'inactive'} : j));
      } catch {
        // non-critical — status will self-correct on next login
      }
    }
  }

  async function addJeep() {
    if (!form.name.trim() || !form.plate_number.trim()) {
      Alert.alert('Required', 'Jeep name and plate number are required.');
      return;
    }
    setAdding(true);
    try {
      const {data} = await api.post('/jeeps', {
        name: form.name.trim(),
        plate_number: form.plate_number.trim().toUpperCase(),
        route_name: form.route_name.trim() || null,
        capacity: form.capacity ? parseInt(form.capacity, 10) : null,
      });
      const jeep = data.jeep;
      setJeeps(prev => [...prev, jeep]);
      setSelectedJeep(jeep);
      setForm({name: '', plate_number: '', route_name: '', capacity: ''});
      setShowAddModal(false);
    } catch (e) {
      const msg = e.response?.data?.message ?? e.response?.data?.errors?.plate_number?.[0] ?? 'Failed to add jeep.';
      Alert.alert('Error', msg);
    } finally {
      setAdding(false);
    }
  }

  const speedKmh = location?.speed ? (location.speed * 3.6).toFixed(1) : '0.0';

  return (
    <View style={styles.root}>
      <StatusBar barStyle="light-content" backgroundColor="#0F172A" />

      <View style={styles.header}>
        <View>
          <Text style={styles.headerRole}>Driver</Text>
          <Text style={styles.headerName}>{user?.name}</Text>
        </View>
        <TouchableOpacity style={styles.logoutBtn} onPress={logout}>
          <Text style={styles.logoutText}>Sign out</Text>
        </TouchableOpacity>
      </View>

      <Modal visible={showAddModal} animationType="slide" transparent onRequestClose={() => setShowAddModal(false)}>
        <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={styles.modalOverlay}>
          <View style={styles.modalSheet}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Add Your Jeep</Text>
              <TouchableOpacity onPress={() => setShowAddModal(false)}>
                <Text style={styles.modalClose}>✕</Text>
              </TouchableOpacity>
            </View>

            <Text style={styles.inputLabel}>Jeep Name *</Text>
            <TextInput
              style={styles.input}
              placeholder="e.g. Jeepney 01"
              placeholderTextColor="#94A3B8"
              value={form.name}
              onChangeText={v => setForm(f => ({...f, name: v}))}
            />

            <Text style={styles.inputLabel}>Plate Number *</Text>
            <TextInput
              style={styles.input}
              placeholder="e.g. ABC 1234"
              placeholderTextColor="#94A3B8"
              autoCapitalize="characters"
              value={form.plate_number}
              onChangeText={v => setForm(f => ({...f, plate_number: v}))}
            />

            <Text style={styles.inputLabel}>Route Name</Text>
            <TextInput
              style={styles.input}
              placeholder="e.g. Baguio City – SM"
              placeholderTextColor="#94A3B8"
              value={form.route_name}
              onChangeText={v => setForm(f => ({...f, route_name: v}))}
            />

            <Text style={styles.inputLabel}>Capacity (passengers)</Text>
            <TextInput
              style={styles.input}
              placeholder="e.g. 16"
              placeholderTextColor="#94A3B8"
              keyboardType="number-pad"
              value={form.capacity}
              onChangeText={v => setForm(f => ({...f, capacity: v}))}
            />

            <TouchableOpacity
              style={[styles.addBtn, adding && {opacity: 0.6}]}
              onPress={addJeep}
              disabled={adding}
              activeOpacity={0.85}>
              {adding ? (
                <ActivityIndicator color="#fff" />
              ) : (
                <Text style={styles.addBtnText}>Add Jeep</Text>
              )}
            </TouchableOpacity>
          </View>
        </KeyboardAvoidingView>
      </Modal>

      <ScrollView style={styles.body} showsVerticalScrollIndicator={false}>
        <View style={styles.sectionRow}>
          <Text style={styles.sectionTitle}>My Jeeps</Text>
          {!tracking && (
            <TouchableOpacity style={styles.addIconBtn} onPress={() => setShowAddModal(true)}>
              <Text style={styles.addIconText}>+ Add</Text>
            </TouchableOpacity>
          )}
        </View>

        {loadingJeeps ? (
          <ActivityIndicator color="#3B82F6" style={{marginVertical: 20}} />
        ) : jeeps.length === 0 ? (
          <TouchableOpacity style={styles.emptyCard} onPress={() => setShowAddModal(true)}>
            <Text style={styles.emptyIcon}>🚐</Text>
            <Text style={styles.emptyTitle}>No jeeps yet</Text>
            <Text style={styles.emptySubtitle}>Tap to add your jeep and start tracking</Text>
          </TouchableOpacity>
        ) : (
          <FlatList
            data={jeeps}
            keyExtractor={item => String(item.id)}
            scrollEnabled={false}
            renderItem={({item}) => {
              const selected = selectedJeep?.id === item.id;
              return (
                <TouchableOpacity
                  style={[styles.jeepCard, selected && styles.jeepCardSelected]}
                  onPress={() => !tracking && setSelectedJeep(selected ? null : item)}
                  activeOpacity={0.75}>
                  <View style={styles.jeepCardLeft}>
                    <View style={[styles.jeepDot, selected && styles.jeepDotSelected]} />
                    <View>
                      <Text style={[styles.jeepName, selected && styles.jeepNameSelected]}>
                        {item.name}
                      </Text>
                      <Text style={styles.jeepPlate}>
                        {item.plate_number}{item.route_name ? ` · ${item.route_name}` : ''}
                      </Text>
                    </View>
                  </View>
                  {selected && (
                    <View style={styles.selectedBadge}>
                      <Text style={styles.selectedBadgeText}>Selected</Text>
                    </View>
                  )}
                </TouchableOpacity>
              );
            }}
          />
        )}

        {tracking && location && (
          <View style={styles.liveCard}>
            <View style={styles.liveCardHeader}>
              <View style={styles.liveDot} />
              <Text style={styles.liveLabel}>
                LIVE · {updateCount} update{updateCount !== 1 ? 's' : ''}
              </Text>
            </View>
            <View style={styles.statsRow}>
              <View style={styles.statBox}>
                <Text style={styles.statValue}>{location.latitude.toFixed(5)}</Text>
                <Text style={styles.statLabel}>Latitude</Text>
              </View>
              <View style={styles.statDivider} />
              <View style={styles.statBox}>
                <Text style={styles.statValue}>{location.longitude.toFixed(5)}</Text>
                <Text style={styles.statLabel}>Longitude</Text>
              </View>
            </View>
            <View style={styles.statsRow}>
              <View style={styles.statBox}>
                <Text style={styles.statValue}>{speedKmh}</Text>
                <Text style={styles.statLabel}>km/h</Text>
              </View>
              <View style={styles.statDivider} />
              <View style={styles.statBox}>
                <Text style={styles.statValue}>
                  {location.heading ? `${location.heading.toFixed(0)}°` : '—'}
                </Text>
                <Text style={styles.statLabel}>Heading</Text>
              </View>
            </View>
          </View>
        )}

        {tracking && !location && (
          <View style={styles.acquiringCard}>
            <ActivityIndicator color="#3B82F6" />
            <Text style={styles.acquiringText}>Acquiring location…</Text>
          </View>
        )}

        <TouchableOpacity
          style={[styles.trackBtn, tracking ? styles.trackBtnStop : styles.trackBtnStart]}
          onPress={tracking ? stopTracking : startTracking}
          activeOpacity={0.85}>
          <Text style={styles.trackBtnText}>
            {tracking ? '⏹  Stop Tracking' : '▶  Start Tracking'}
          </Text>
        </TouchableOpacity>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  root: {flex: 1, backgroundColor: '#F1F5F9'},
  header: {
    backgroundColor: '#0F172A',
    paddingTop: 52,
    paddingBottom: 24,
    paddingHorizontal: 20,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
  },
  headerRole: {fontSize: 12, color: '#64748B', fontWeight: '600', letterSpacing: 1, textTransform: 'uppercase'},
  headerName: {fontSize: 22, fontWeight: '800', color: '#F8FAFC', marginTop: 2},
  logoutBtn: {
    borderWidth: 1,
    borderColor: '#334155',
    borderRadius: 20,
    paddingHorizontal: 14,
    paddingVertical: 6,
  },
  logoutText: {color: '#94A3B8', fontSize: 13},
  body: {flex: 1, paddingHorizontal: 16, paddingTop: 20},
  sectionTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#64748B',
    letterSpacing: 0.8,
    textTransform: 'uppercase',
    marginBottom: 12,
  },
  jeepCard: {
    backgroundColor: '#fff',
    borderRadius: 16,
    padding: 16,
    marginBottom: 10,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderWidth: 2,
    borderColor: 'transparent',
  },
  jeepCardSelected: {borderColor: '#3B82F6', backgroundColor: '#EFF6FF'},
  jeepCardLeft: {flexDirection: 'row', alignItems: 'center', gap: 12},
  jeepDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#CBD5E1',
  },
  jeepDotSelected: {backgroundColor: '#3B82F6'},
  jeepName: {fontSize: 16, fontWeight: '600', color: '#1E293B'},
  jeepNameSelected: {color: '#1D4ED8'},
  jeepPlate: {fontSize: 13, color: '#94A3B8', marginTop: 2},
  selectedBadge: {
    backgroundColor: '#DBEAFE',
    borderRadius: 20,
    paddingHorizontal: 10,
    paddingVertical: 4,
  },
  selectedBadgeText: {fontSize: 12, color: '#1D4ED8', fontWeight: '600'},
  liveCard: {
    backgroundColor: '#0F172A',
    borderRadius: 20,
    padding: 20,
    marginTop: 16,
    marginBottom: 8,
  },
  liveCardHeader: {flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 16},
  liveDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: '#22C55E',
  },
  liveLabel: {fontSize: 12, color: '#22C55E', fontWeight: '700', letterSpacing: 1},
  statsRow: {flexDirection: 'row', marginBottom: 12},
  statBox: {flex: 1, alignItems: 'center'},
  statDivider: {width: 1, backgroundColor: '#1E293B'},
  statValue: {fontSize: 18, fontWeight: '700', color: '#F8FAFC'},
  statLabel: {fontSize: 11, color: '#64748B', marginTop: 2, fontWeight: '600'},
  acquiringCard: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    backgroundColor: '#fff',
    borderRadius: 16,
    padding: 16,
    marginTop: 16,
    marginBottom: 8,
  },
  acquiringText: {fontSize: 14, color: '#64748B'},
  trackBtn: {
    borderRadius: 16,
    paddingVertical: 18,
    alignItems: 'center',
    marginTop: 16,
    marginBottom: 32,
  },
  trackBtnStart: {backgroundColor: '#1D4ED8'},
  trackBtnStop: {backgroundColor: '#DC2626'},
  trackBtnText: {color: '#fff', fontSize: 16, fontWeight: '700', letterSpacing: 0.3},
  sectionRow: {flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12},
  addIconBtn: {backgroundColor: '#1D4ED8', borderRadius: 20, paddingHorizontal: 14, paddingVertical: 6},
  addIconText: {color: '#fff', fontSize: 13, fontWeight: '700'},
  emptyCard: {
    backgroundColor: '#fff',
    borderRadius: 20,
    padding: 32,
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#E2E8F0',
    borderStyle: 'dashed',
    marginBottom: 16,
  },
  emptyIcon: {fontSize: 40, marginBottom: 12},
  emptyTitle: {fontSize: 17, fontWeight: '700', color: '#1E293B', marginBottom: 6},
  emptySubtitle: {fontSize: 13, color: '#94A3B8', textAlign: 'center'},
  modalOverlay: {flex: 1, justifyContent: 'flex-end', backgroundColor: 'rgba(0,0,0,0.5)'},
  modalSheet: {
    backgroundColor: '#fff',
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    padding: 24,
    paddingBottom: 40,
  },
  modalHeader: {flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20},
  modalTitle: {fontSize: 20, fontWeight: '800', color: '#0F172A'},
  modalClose: {fontSize: 18, color: '#94A3B8', padding: 4},
  inputLabel: {fontSize: 13, fontWeight: '600', color: '#475569', marginBottom: 6},
  input: {
    backgroundColor: '#F8FAFC',
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: '#E2E8F0',
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: 15,
    color: '#0F172A',
    marginBottom: 14,
  },
  addBtn: {
    backgroundColor: '#1D4ED8',
    borderRadius: 14,
    paddingVertical: 16,
    alignItems: 'center',
    marginTop: 4,
  },
  addBtnText: {color: '#fff', fontSize: 16, fontWeight: '700'},
});
