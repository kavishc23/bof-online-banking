import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  Alert,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
} from 'react-native';
import { fetchBeneficiaries, addBeneficiary } from '../services/beneficiaryService';

export default function BeneficiariesScreen() {
  const [beneficiaries, setBeneficiaries] = useState([]);
  const [name, setName] = useState('');
  const [accountNumber, setAccountNumber] = useState('');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    loadBeneficiaries();
  }, []);

  const loadBeneficiaries = async () => {
    try {
      setLoading(true);
      const data = await fetchBeneficiaries();
      setBeneficiaries(data);
    } catch (error) {
      Alert.alert('Error', 'Unable to load beneficiaries.');
    } finally {
      setLoading(false);
    }
  };

  const handleAdd = async () => {
    if (!name.trim() || !accountNumber.trim()) {
      Alert.alert('Missing Information', 'Please enter beneficiary name and account number.');
      return;
    }

    try {
      setSaving(true);
      await addBeneficiary({
        name,
        accountNumber,
      });
      setName('');
      setAccountNumber('');
      Alert.alert('Success', 'Beneficiary added successfully.');
      await loadBeneficiaries();
    } catch (error) {
      Alert.alert('Error', 'Unable to add beneficiary.');
    } finally {
      setSaving(false);
    }
  };

  const renderItem = ({ item }) => {
    const attrs = item.attributes || item;

    return (
      <View style={styles.card}>
        <Text style={styles.cardTitle}>{attrs.name || attrs.beneficiaryName || 'Beneficiary'}</Text>
        <Text>Account: {attrs.accountNumber || 'N/A'}</Text>
      </View>
    );
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Beneficiaries</Text>

      <TextInput
        style={styles.input}
        placeholder="Beneficiary Name"
        value={name}
        onChangeText={setName}
      />

      <TextInput
        style={styles.input}
        placeholder="Account Number"
        value={accountNumber}
        onChangeText={setAccountNumber}
      />

      <TouchableOpacity
        style={[styles.button, saving && styles.buttonDisabled]}
        onPress={handleAdd}
        disabled={saving}
      >
        {saving ? <ActivityIndicator color="#fff" /> : <Text style={styles.buttonText}>Add Beneficiary</Text>}
      </TouchableOpacity>

      {loading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" />
        </View>
      ) : (
        <FlatList
          data={beneficiaries}
          keyExtractor={(item, index) => String(item.id || index)}
          renderItem={renderItem}
          ListEmptyComponent={<Text style={styles.empty}>No beneficiaries found.</Text>}
          contentContainerStyle={{ paddingTop: 20 }}
        />
      )}
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
  input: {
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 10,
    padding: 14,
    marginBottom: 12,
  },
  button: {
    backgroundColor: '#0055a5',
    padding: 14,
    borderRadius: 10,
    alignItems: 'center',
  },
  buttonDisabled: {
    opacity: 0.7,
  },
  buttonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  loadingContainer: {
    marginTop: 30,
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
    textAlign: 'center',
    marginTop: 30,
    color: '#666',
  },
});