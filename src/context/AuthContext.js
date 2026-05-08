import React, {createContext, useContext, useState, useEffect} from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import api from '../api/axios';

const AuthContext = createContext();

export function AuthProvider({children}) {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    (async () => {
      const storedToken = await AsyncStorage.getItem('token');
      if (storedToken) {
        setToken(storedToken);
        try {
          const {data} = await api.get('/me');
          setUser(data);
        } catch {
          await AsyncStorage.removeItem('token');
        }
      }
      setLoading(false);
    })();
  }, []);

  async function login(email, password) {
    const {data} = await api.post('/login', {email, password});
    await AsyncStorage.setItem('token', data.token);
    setToken(data.token);
    setUser(data.user);
  }

  async function logout() {
    await api.post('/logout').catch(() => {});
    await AsyncStorage.removeItem('token');
    setToken(null);
    setUser(null);
  }

  return (
    <AuthContext.Provider value={{user, token, loading, login, logout}}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);
