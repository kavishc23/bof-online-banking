import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  ActivityIndicator,
  Alert,
  ScrollView,
} from 'react-native';
import {
  fetchScheduledTransfers,
  fetchScheduledBillPayments,
} from '../services/scheduledPaymentService';

export default function ScheduledPaymentsScreen() {
  const [transfers, setTransfers] = useState([]);
  const [billPayments, setBillPayments] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadScheduledPayments();
  }, []);

  const loadScheduledPayments = async () => {
    try {
      setLoading(true);
      const transferData = await fetchScheduledTransfers();
      const billData = await fetchScheduledBillPayments();
      setTransfers(transferData);
      setBillPayments(billData);
    } catch (error) {
      Alert.alert('Error', 'Unable to load scheduled payments.');
    } finally {
      setLoading(false);
    }
  };

  const renderCard = ({ item }) => {
    const attrs = item.attributes || item;

    return (
      <View style={styles.card}>
        <Text style={styles.cardTitle}>{attrs.name || attrs.reference || 'Scheduled Item'}</Text>
        <Text>Amount: {attrs.amount ?? 'N/A'}</Text>
        <Text>Date: {attrs.date || attrs.scheduledDate || 'N/A'}</Text>
      </View>
    );
  };

  if (loading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" />
        <Text style={styles.loadingText}>Loading scheduled payments...</Text>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>
      <Text style={styles.title}>Scheduled Transfers</Text>
      <FlatList
        data={transfers}
        keyExtractor={(item, index) => `transfer-${item.id || index}`}
        renderItem={renderCard}
        scrollEnabled={false}
        ListEmptyComponent={<Text style={styles.empty}>No scheduled transfers.</Text>}
      />

      <Text style={[styles.title, { marginTop: 24 }]}>Scheduled Bill Payments</Text>
      <FlatList
        data={billPayments}
        keyExtractor={(item, index) => `bill-${item.id || index}`}
        renderItem={renderCard}
        scrollEnabled={false}
        ListEmptyComponent={<Text style={styles.empty}>No scheduled bill payments.</Text>}
      />
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
    backgroundColor: '#f5f7fb',
  },
  center: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 10,
  },
  title: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#003366',
    marginBottom: 14,
  },
  card: {
    backgroundColor: '#fff',
    padding: 14,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#ddd',
    marginBottom: 10,
  },
  cardTitle: {
    fontWeight: 'bold',
    marginBottom: 4,
  },
  empty: {
    color: '#666',
    marginBottom: 10,
  },
});