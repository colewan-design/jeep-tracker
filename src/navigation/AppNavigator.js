import React from 'react';
import {NavigationContainer} from '@react-navigation/native';
import {createStackNavigator} from '@react-navigation/stack';
import {ActivityIndicator, View} from 'react-native';
import {useAuth} from '../context/AuthContext';
import LoginScreen from '../screens/LoginScreen';
import DriverScreen from '../screens/DriverScreen';
import PassengerScreen from '../screens/PassengerScreen';

const Stack = createStackNavigator();

export default function AppNavigator() {
  const {user, loading} = useAuth();

  if (loading) {
    return (
      <View style={{flex: 1, justifyContent: 'center', alignItems: 'center'}}>
        <ActivityIndicator size="large" color="#2563eb" />
      </View>
    );
  }

  return (
    <NavigationContainer>
      <Stack.Navigator screenOptions={{headerShown: false}}>
        {!user ? (
          <Stack.Screen name="Login" component={LoginScreen} />
        ) : user.role === 'driver' ? (
          <Stack.Screen name="Driver" component={DriverScreen} />
        ) : (
          <Stack.Screen name="Passenger" component={PassengerScreen} />
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
}
