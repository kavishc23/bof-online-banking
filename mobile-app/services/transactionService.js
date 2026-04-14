import api from './api';

/**
 * Fetches all transactions for the logged-in user.
 *
 * @returns {Promise<Array>} List of transaction objects
 * @throws {Error} If API request fails
 */
export async function fetchTransactions() {
  const response = await api.get('/transactions');
  return response.data;
}