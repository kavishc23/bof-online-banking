import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

import LoginScreen from '../screens/LoginScreen';
import DashboardScreen from '../screens/DashboardScreen';
import TransactionsScreen from '../screens/transactionsScreen';
import TransferScreen from '../screens/transferScreen';
import BeneficiariesScreen from '../screens/beneficiariesScreen';
import ScheduledPaymentsScreen from '../screens/scheduledPaymentsScreen';
import BillPaymentScreen from '../screens/billPaymentScreen';

const Stack = createNativeStackNavigator();

export default function AppNavigator() {
  return (
    <NavigationContainer>
      <Stack.Navigator initialRouteName="Login">
        <Stack.Screen
          name="Login"
          component={LoginScreen}
          options={{ headerShown: false }}
        />
        <Stack.Screen name="Dashboard" component={DashboardScreen} />
        <Stack.Screen name="Transactions" component={TransactionsScreen} />
        <Stack.Screen name="Transfer" component={TransferScreen} />
        <Stack.Screen name="Beneficiaries" component={BeneficiariesScreen} />
        <Stack.Screen name="ScheduledPayments" component={ScheduledPaymentsScreen} />
        <Stack.Screen name="BillPayment" component={BillPaymentScreen} />
      </Stack.Navigator>
    </NavigationContainer>
  );
}