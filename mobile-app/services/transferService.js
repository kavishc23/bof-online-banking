import api from './api';

/**
 * Sends a transfer request.
 * Adjust endpoint if needed for your Strapi project.
 *
 * @param {object} payload
 * @returns {Promise<object>}
 */
export async function sendTransfer(payload) {
  const response = await api.post('/transfers', payload);
  return response.data;
}