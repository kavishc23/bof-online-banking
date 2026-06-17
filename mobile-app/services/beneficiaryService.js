import api from './api';

/**
 * Fetches beneficiaries from Strapi.
 *
 * @returns {Promise<Array>}
 */
export async function fetchBeneficiaries() {
  const response = await api.get('/beneficiaries');
  return response.data?.data || response.data || [];
}

/**
 * Adds a new beneficiary.
 *
 * @param {object} payload
 * @returns {Promise<object>}
 */
export async function addBeneficiary(payload) {
  const response = await api.post('/beneficiaries', payload);
  return response.data;
}