import api from './api';

/**
 * Fetches scheduled transfers.
 *
 * @returns {Promise<Array>}
 */
export async function fetchScheduledTransfers() {
  const response = await api.get('/scheduled-transfers');
  return response.data?.data || response.data || [];
}

/**
 * Fetches scheduled bill payments.
 *
 * @returns {Promise<Array>}
 */
export async function fetchScheduledBillPayments() {
  const response = await api.get('/scheduled-bill-payments');
  return response.data?.data || response.data || [];
}