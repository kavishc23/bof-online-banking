import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  Alert,
  StyleSheet,
  ActivityIndicator,
  ScrollView,
} from 'react-native';
import { sendTransfer } from '../services/transferService';

export default function TransferScreen() {
  const [recipient, setRecipient] = useState('');
  const [amount, setAmount] = useState('');
  const [reference, setReference] = useState('');
  const [loading, setLoading] = useState(false);

  const handleTransfer = async () => {
    if (!recipient.trim() || !amount.trim()) {
      Alert.alert('Missing Information', 'Recipient and amount are required.');
      return;
    }

    try {
      setLoading(true);
      await sendTransfer({
        recipient,
        amount: Number(amount),
        reference,
      });
      Alert.alert('Success', 'Transfer completed successfully.');
      setRecipient('');
      setAmount('');
      setReference('');
    } catch (error) {
      Alert.alert('Transfer Failed', 'Unable to process transfer.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView contentContainerStyle={styles.container}>
      <Text style={styles.title}>Transfer Money</Text>

      <TextInput
        style={styles.input}
        placeholder="Recipient Account / Beneficiary"
        value={recipient}
        onChangeText={setRecipient}
      />

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
        style={[styles.button, loading && styles.buttonDisabled]}
        onPress={handleTransfer}
        disabled={loading}
      >
        {loading ? <ActivityIndicator color="#fff" /> : <Text style={styles.buttonText}>Submit Transfer</Text>}
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    padding: 24,
    backgroundColor: '#f5f7fb',
    flexGrow: 1,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#003366',
    marginBottom: 20,
  },
  input: {
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 10,
    padding: 14,
    marginBottom: 16,
  },
  button: {
    backgroundColor: '#0055a5',
    padding: 15,
    borderRadius: 10,
    alignItems: 'center',
  },
  buttonDisabled: {
    opacity: 0.7,
  },
  buttonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
});