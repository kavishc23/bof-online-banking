import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ActivityIndicator,
  FlatList,
  Alert,
} from 'react-native';
import { fetchTransactions } from '../services/transactionService';

export default function TransactionsScreen() {
  const [transactions, setTransactions] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadTransactions();
  }, []);

  const loadTransactions = async () => {
    try {
      setLoading(true);
      const data = await fetchTransactions();
      setTransactions(data);
    } catch (error) {
      Alert.alert('Error', 'Unable to load transactions.');
    } finally {
      setLoading(false);
    }
  };

  const renderItem = ({ item }) => {
    const attrs = item.attributes || item;

    return (
      <View style={styles.card}>
        <Text style={styles.type}>{attrs.type || attrs.transactionType || 'Transaction'}</Text>
        <Text>Amount: {attrs.amount ?? 'N/A'}</Text>
        <Text>Date: {attrs.transactionDate || attrs.date || 'N/A'}</Text>
        <Text>Reference: {attrs.reference || attrs.description || 'N/A'}</Text>
      </View>
    );
  };

  if (loading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" />
        <Text style={styles.loadingText}>Loading transactions...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Transactions</Text>
      <FlatList
        data={transactions}
        keyExtractor={(item, index) => String(item.id || index)}
        renderItem={renderItem}
        ListEmptyComponent={<Text style={styles.empty}>No transactions found.</Text>}
      />
    </View>
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
    fontSize: 24,
    fontWeight: 'bold',
    color: '#003366',
    marginBottom: 16,
  },
  card: {
    backgroundColor: '#fff',
    padding: 16,
    borderRadius: 10,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#ddd',
  },
  type: {
    fontWeight: 'bold',
    marginBottom: 6,
    color: '#0055a5',
  },
  empty: {
    textAlign: 'center',
    marginTop: 40,
    color: '#666',
  },
});