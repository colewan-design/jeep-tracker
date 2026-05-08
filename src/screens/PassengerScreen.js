import React, {useState, useEffect, useRef} from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  FlatList,
  ActivityIndicator,
  Alert,
  StatusBar,
  PermissionsAndroid,
  Platform,
} from 'react-native';
import MapView, {Marker, Polyline, PROVIDER_GOOGLE} from 'react-native-maps';
import Geolocation from '@react-native-community/geolocation';
import api from '../api/axios';
import echo from '../utils/echo';
import {useAuth} from '../context/AuthContext';

function haversineKm(lat1, lon1, lat2, lon2) {
  const R = 6371;
  const dLat = ((lat2 - lat1) * Math.PI) / 180;
  const dLon = ((lon2 - lon1) * Math.PI) / 180;
  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos((lat1 * Math.PI) / 180) *
      Math.cos((lat2 * Math.PI) / 180) *
      Math.sin(dLon / 2) ** 2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function formatDistance(km) {
  if (km < 1) return `${Math.round(km * 1000)} m`;
  return `${km.toFixed(1)} km`;
}

export default function PassengerScreen() {
  const {user, logout} = useAuth();
  const [jeeps, setJeeps] = useState([]);
  const [selectedJeep, setSelectedJeep] = useState(null);
  const [jeepLocation, setJeepLocation] = useState(null);
  const [passengerLocation, setPassengerLocation] = useState(null);
  const [loadingJeeps, setLoadingJeeps] = useState(true);
  const [lastUpdated, setLastUpdated] = useState(null);
  const [jeepTrail, setJeepTrail] = useState([]);
  const [jeepActive, setJeepActive] = useState(false);
  const channelRef = useRef(null);
  const subscribedJeepId = useRef(null);
  const watchRef = useRef(null);
  const mapRef = useRef(null);
  const jeepMarkerRef = useRef(null);

  useEffect(() => {
    fetchJeeps();
    requestAndWatchLocation();
    return () => {
      unsubscribeFromJeep();
      if (watchRef.current !== null) {
        Geolocation.clearWatch(watchRef.current);
      }
    };
  }, []);

  useEffect(() => {
    if (selectedJeep) {
      fetchHistory(selectedJeep.id);
      subscribeToJeep(selectedJeep.id);
    } else {
      unsubscribeFromJeep();
      setJeepLocation(null);
      setJeepTrail([]);
      setLastUpdated(null);
      setJeepActive(false);
    }
    return () => unsubscribeFromJeep();
  }, [selectedJeep]);

  useEffect(() => {
    if (jeepLocation && passengerLocation && mapRef.current) {
      mapRef.current.fitToCoordinates(
        [
          {latitude: jeepLocation.latitude, longitude: jeepLocation.longitude},
          {latitude: passengerLocation.latitude, longitude: passengerLocation.longitude},
        ],
        {edgePadding: {top: 80, right: 60, bottom: 80, left: 60}, animated: true},
      );
    } else if (jeepLocation && mapRef.current) {
      mapRef.current.animateToRegion({
        latitude: jeepLocation.latitude,
        longitude: jeepLocation.longitude,
        latitudeDelta: 0.01,
        longitudeDelta: 0.01,
      });
    }
  }, [jeepLocation, passengerLocation]);

  async function requestAndWatchLocation() {
    if (Platform.OS === 'android') {
      const granted = await PermissionsAndroid.request(
        PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION,
      );
      if (granted !== PermissionsAndroid.RESULTS.GRANTED) return;
    }
    watchRef.current = Geolocation.watchPosition(
      pos => {
        setPassengerLocation({
          latitude: pos.coords.latitude,
          longitude: pos.coords.longitude,
        });
      },
      err => console.warn('Passenger GPS error:', err.message),
      {enableHighAccuracy: false, distanceFilter: 10, interval: 5000},
    );
  }

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

  function normalizeLoc(loc) {
    return {...loc, latitude: parseFloat(loc.latitude), longitude: parseFloat(loc.longitude)};
  }

  async function fetchHistory(jeepId) {
    if (!jeepId) return;

    try {
      const {data} = await api.get(`/jeeps/${jeepId}/location`);
      if (data?.location?.latitude) {
        setJeepLocation(normalizeLoc(data.location));
        setJeepActive(data.jeep?.status === 'active');
        setLastUpdated(new Date());
      }
    } catch {
      // jeep not yet active
    }

    try {
      const {data} = await api.get(`/jeeps/${jeepId}/location/history`, {
        params: {limit: 60},
      });
      const points = (data.locations ?? [])
        .filter(l => l.latitude != null && l.longitude != null)
        .map(l => ({latitude: parseFloat(l.latitude), longitude: parseFloat(l.longitude)}))
        .reverse();
      if (points.length > 0) {
        setJeepTrail(points);
        setJeepLocation(normalizeLoc(data.locations[0]));
        setLastUpdated(new Date());
      }
    } catch {
      // trail unavailable — marker still shows from current location above
    }
  }

  function subscribeToJeep(jeepId) {
    unsubscribeFromJeep();
    subscribedJeepId.current = jeepId;
    channelRef.current = echo
      .channel(`jeep.${jeepId}`)
      .listen('.location.updated', payload => {
        if (payload?.location?.latitude) {
          const coord = {
            latitude: parseFloat(payload.location.latitude),
            longitude: parseFloat(payload.location.longitude),
          };
          setJeepLocation(normalizeLoc(payload.location));
          setJeepTrail(prev => [...prev.slice(-59), coord]);
          setJeepActive(true);
          setLastUpdated(new Date());
          if (jeepMarkerRef.current) {
            jeepMarkerRef.current.animateMarkerToCoordinate(coord, 1000);
          }
        }
      })
      .listen('.status.changed', payload => {
        if (payload?.status) {
          setJeepActive(payload.status === 'active');
        }
      });
  }

  function unsubscribeFromJeep() {
    if (subscribedJeepId.current !== null) {
      echo.leave(`jeep.${subscribedJeepId.current}`);
      subscribedJeepId.current = null;
      channelRef.current = null;
    }
  }

  const distance =
    jeepLocation && passengerLocation
      ? haversineKm(
          passengerLocation.latitude,
          passengerLocation.longitude,
          jeepLocation.latitude,
          jeepLocation.longitude,
        )
      : null;

  const speedKmh = jeepLocation?.speed
    ? (jeepLocation.speed * 3.6).toFixed(1)
    : '0.0';

  const initialRegion = passengerLocation
    ? {
        latitude: passengerLocation.latitude,
        longitude: passengerLocation.longitude,
        latitudeDelta: 0.05,
        longitudeDelta: 0.05,
      }
    : {
        latitude: 16.4023,
        longitude: 120.596,
        latitudeDelta: 0.05,
        longitudeDelta: 0.05,
      };

  return (
    <View style={styles.root}>
      <StatusBar barStyle="light-content" backgroundColor="#0F172A" />

      <View style={styles.header}>
        <View>
          <Text style={styles.headerRole}>Passenger</Text>
          <Text style={styles.headerName}>{user?.name}</Text>
        </View>
        <TouchableOpacity style={styles.logoutBtn} onPress={logout}>
          <Text style={styles.logoutText}>Sign out</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.chipRow}>
        {loadingJeeps ? (
          <ActivityIndicator color="#3B82F6" style={{marginLeft: 16}} />
        ) : (
          <FlatList
            data={jeeps}
            keyExtractor={item => String(item.id)}
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.chipList}
            renderItem={({item}) => {
              const selected = selectedJeep?.id === item.id;
              return (
                <TouchableOpacity
                  style={[styles.chip, selected && styles.chipSelected]}
                  onPress={() => setSelectedJeep(selected ? null : item)}
                  activeOpacity={0.75}>
                  <View style={[styles.chipDot, selected && styles.chipDotSelected]} />
                  <Text style={[styles.chipText, selected && styles.chipTextSelected]}>
                    {item.name}
                  </Text>
                </TouchableOpacity>
              );
            }}
          />
        )}
      </View>

      <View style={styles.mapWrap}>
        <MapView
          ref={mapRef}
          style={styles.map}
          provider={PROVIDER_GOOGLE}
          initialRegion={initialRegion}
          showsUserLocation={false}
          showsMyLocationButton={false}>
          {passengerLocation && (
            <Marker
              coordinate={passengerLocation}
              anchor={{x: 0.5, y: 0.5}}>
              <View style={styles.youMarker}>
                <View style={styles.youMarkerInner} />
              </View>
            </Marker>
          )}
          {jeepTrail.length > 1 && (
            <Polyline
              coordinates={jeepTrail}
              strokeColor="#3B82F6"
              strokeWidth={4}
              lineCap="round"
              lineJoin="round"
            />
          )}
          {jeepLocation && (
            <Marker
              ref={jeepMarkerRef}
              coordinate={{
                latitude: jeepLocation.latitude,
                longitude: jeepLocation.longitude,
              }}
              title={selectedJeep?.name}
              description={`Speed: ${speedKmh} km/h`}
              anchor={{x: 0.5, y: 0.5}}>
              <View style={styles.jeepMarker}>
                <Text style={styles.jeepMarkerText}>🚐</Text>
              </View>
            </Marker>
          )}
        </MapView>

        {!selectedJeep && (
          <View style={styles.mapOverlay}>
            <Text style={styles.mapOverlayIcon}>🚌</Text>
            <Text style={styles.mapOverlayText}>Select a jeep to track</Text>
          </View>
        )}

        {selectedJeep && !jeepLocation && (
          <View style={styles.mapOverlay}>
            <ActivityIndicator color="#3B82F6" />
            <Text style={styles.mapOverlayText}>
              Waiting for {selectedJeep.name}…
            </Text>
          </View>
        )}
      </View>

      {selectedJeep && jeepLocation && (
        <View style={styles.infoBar}>
          <View style={styles.infoItem}>
            <Text style={styles.infoLabel}>JEEP</Text>
            <Text style={styles.infoValue} numberOfLines={1}>
              {selectedJeep.name}
            </Text>
          </View>
          <View style={styles.infoDivider} />
          <View style={styles.infoItem}>
            <Text style={styles.infoLabel}>SPEED</Text>
            <Text style={styles.infoValue}>{speedKmh} km/h</Text>
          </View>
          <View style={styles.infoDivider} />
          <View style={styles.infoItem}>
            <Text style={styles.infoLabel}>DISTANCE</Text>
            <Text style={[styles.infoValue, styles.infoValueDistance]}>
              {distance !== null ? formatDistance(distance) : '—'}
            </Text>
          </View>
          <View style={styles.infoDivider} />
          <View style={styles.infoItem}>
            <View style={styles.liveRow}>
              <View style={[styles.liveDot, !jeepActive && styles.liveDotOffline]} />
              <Text style={[styles.infoLabel, !jeepActive && styles.infoLabelOffline]}>
                {jeepActive ? 'LIVE' : 'OFFLINE'}
              </Text>
            </View>
            <Text style={[styles.infoValue, !jeepActive && styles.infoValueOffline]}>
              {lastUpdated?.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit', second: '2-digit'}) ?? '—'}
            </Text>
          </View>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  root: {flex: 1, backgroundColor: '#0F172A'},
  header: {
    paddingTop: 52,
    paddingBottom: 16,
    paddingHorizontal: 20,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
    backgroundColor: '#0F172A',
  },
  headerRole: {
    fontSize: 12,
    color: '#64748B',
    fontWeight: '600',
    letterSpacing: 1,
    textTransform: 'uppercase',
  },
  headerName: {fontSize: 22, fontWeight: '800', color: '#F8FAFC', marginTop: 2},
  logoutBtn: {
    borderWidth: 1,
    borderColor: '#334155',
    borderRadius: 20,
    paddingHorizontal: 14,
    paddingVertical: 6,
  },
  logoutText: {color: '#94A3B8', fontSize: 13},
  chipRow: {
    paddingVertical: 12,
    backgroundColor: '#0F172A',
  },
  chipList: {paddingHorizontal: 16, gap: 8},
  chip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: '#1E293B',
    borderRadius: 24,
    paddingHorizontal: 14,
    paddingVertical: 9,
    borderWidth: 1.5,
    borderColor: 'transparent',
  },
  chipSelected: {borderColor: '#3B82F6', backgroundColor: '#172554'},
  chipDot: {width: 7, height: 7, borderRadius: 4, backgroundColor: '#475569'},
  chipDotSelected: {backgroundColor: '#3B82F6'},
  chipText: {fontSize: 14, fontWeight: '600', color: '#94A3B8'},
  chipTextSelected: {color: '#93C5FD'},
  mapWrap: {flex: 1, position: 'relative'},
  map: {flex: 1},
  mapOverlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(15,23,42,0.7)',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 12,
  },
  mapOverlayIcon: {fontSize: 48},
  mapOverlayText: {
    fontSize: 15,
    color: '#94A3B8',
    fontWeight: '600',
  },
  youMarker: {
    width: 22,
    height: 22,
    borderRadius: 11,
    backgroundColor: 'rgba(59,130,246,0.3)',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    borderColor: '#3B82F6',
  },
  youMarkerInner: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#3B82F6',
  },
  jeepMarker: {
    backgroundColor: '#fff',
    borderRadius: 20,
    padding: 4,
    elevation: 4,
    shadowColor: '#000',
    shadowOpacity: 0.2,
    shadowRadius: 4,
    shadowOffset: {width: 0, height: 2},
  },
  jeepMarkerText: {fontSize: 22},
  infoBar: {
    flexDirection: 'row',
    backgroundColor: '#0F172A',
    paddingVertical: 14,
    paddingHorizontal: 8,
    borderTopWidth: 1,
    borderTopColor: '#1E293B',
  },
  infoItem: {flex: 1, alignItems: 'center'},
  infoDivider: {width: 1, backgroundColor: '#1E293B'},
  infoLabel: {
    fontSize: 10,
    color: '#475569',
    fontWeight: '700',
    letterSpacing: 0.5,
    marginBottom: 3,
  },
  infoValue: {fontSize: 13, fontWeight: '700', color: '#F1F5F9'},
  infoValueDistance: {color: '#34D399'},
  liveRow: {flexDirection: 'row', alignItems: 'center', gap: 4, marginBottom: 3},
  liveDot: {width: 6, height: 6, borderRadius: 3, backgroundColor: '#22C55E'},
  liveDotOffline: {backgroundColor: '#EF4444'},
  infoLabelOffline: {color: '#EF4444'},
  infoValueOffline: {color: '#64748B'},
});
