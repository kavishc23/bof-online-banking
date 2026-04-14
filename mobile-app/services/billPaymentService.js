import api from './api';

/**
 * Fetches billers from Strapi.
 *
 * @returns {Promise<Array>}
 */
export async function fetchBillers() {
  const response = await api.get('/billers');
  return response.data?.data || response.data || [];
}

/**
 * Sends a bill payment request.
 *
 * @param {object} payload
 * @returns {Promise<object>}
 */
export async function payBill(payload) {
  const response = await api.post('/bill-payments', payload);
  return response.data;
}