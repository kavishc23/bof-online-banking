import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  FlatList,
} from 'react-native';
import { fetchBillers, payBill } from '../services/billPaymentService';

export default function BillPaymentScreen() {
  const [billers, setBillers] = useState([]);
  const [selectedBiller, setSelectedBiller] = useState(null);
  const [amount, setAmount] = useState('');
  const [reference, setReference] = useState('');
  const [loading, setLoading] = useState(true);
  const [paying, setPaying] = useState(false);

  useEffect(() => {
    loadBillers();
  }, []);

  const loadBillers = async () => {
    try {
      setLoading(true);
      const data = await fetchBillers();
      setBillers(data);
    } catch (error) {
      Alert.alert('Error', 'Unable to load billers.');
    } finally {
      setLoading(false);
    }
  };

  const handlePayBill = async () => {
    if (!selectedBiller || !amount.trim()) {
      Alert.alert('Missing Information', 'Please select a biller and enter an amount.');
      return;
    }

    try {
      setPaying(true);
      await payBill({
        billerId: selectedBiller.id || selectedBiller.documentId,
        amount: Number(amount),
        reference,
      });
      Alert.alert('Success', 'Bill payment completed.');
      setAmount('');
      setReference('');
    } catch (error) {
      Alert.alert('Error', 'Unable to process bill payment.');
    } finally {
      setPaying(false);
    }
  };

  const renderBiller = ({ item }) => {
    const attrs = item.attributes || item;
    const name = attrs.name || attrs.billerName || 'Biller';
    const active = selectedBiller?.id === item.id;

    return (
      <TouchableOpacity
        style={[styles.billerCard, active && styles.selectedBiller]}
        onPress={() => setSelectedBiller(item)}
      >
        <Text style={styles.billerName}>{name}</Text>
      </TouchableOpacity>
    );
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Bill Payment</Text>

      {loading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" />
        </View>
      ) : (
        <FlatList
          data={billers}
          keyExtractor={(item, index) => String(item.id || index)}
          renderItem={renderBiller}
          ListEmptyComponent={<Text style={styles.empty}>No billers found.</Text>}
          style={{ maxHeight: 220 }}
        />
      )}

      <TextInput
        style={styles.input}
        placeholder="Amount"
        value={amount}
        onChangeText={setAmount}
        keyboardType="numeric"
      />

      <TextInput
        style={styles.input}
        placeholder="Reference / Note"
        value={reference}
        onChangeText={setReference}
      />

      <TouchableOpacity
        style={[styles.button, paying && styles.buttonDisabled]}
        onPress={handlePayBill}
        disabled={paying}
      >
        {paying ? <ActivityIndicator color="#fff" /> : <Text style={styles.buttonText}>Pay Bill</Text>}
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
    backgroundColor: '#f5f7fb',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#003366',
    marginBottom: 16,
  },
  loadingContainer: {
    marginTop: 20,
    marginBottom: 20,
  },
  billerCard: {
    backgroundColor: '#fff',
    padding: 14,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#ddd',
    marginBottom: 10,
  },
  selectedBiller: {
    borderColor: '#0055a5',
    borderWidth: 2,
  },
  billerName: {
    fontWeight: 'bold',
  },
  input: {
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 10,
    padding: 14,
    marginTop: 14,
  },
  button: {
    backgroundColor: '#0055a5',
    padding: 15,
    borderRadius: 10,
    alignItems: 'center',
    marginTop: 16,
  },
  buttonDisabled: {
    opacity: 0.7,
  },
  buttonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
  empty: {
    color: '#666',
  },
});