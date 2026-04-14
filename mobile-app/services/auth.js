import AsyncStorage from '@react-native-async-storage/async-storage';
import api from './api';

/**
 * Logs a user into the system using Strapi authentication.
 *
 * @param {string} identifier - Email or username
 * @param {string} password - Password
 * @returns {Promise<{jwt: string, user: object}>}
 * @throws {Error} If login fails
 */
export async function login(identifier, password) {
  try {
    const response = await api.post('/auth/local', {
      identifier,
      password,
    });

    const { jwt, user } = response.data;

    await AsyncStorage.setItem('token', jwt);
    await AsyncStorage.setItem('user', JSON.stringify(user));

    return { jwt, user };
  } catch (error) {
    console.error('Login error:', error.response?.data || error.message);
    throw error;
  }
}

/**
 * Clears local session information.
 */
export async function logout() {
  try {
    await AsyncStorage.removeItem('token');
    await AsyncStorage.removeItem('user');
  } catch (error) {
    console.error('Logout error:', error);
  }
}

/**
 * Returns stored JWT.
 *
 * @returns {Promise<string|null>}
 */
export async function getToken() {
  try {
    return await AsyncStorage.getItem('token');
  } catch (error) {
    console.error('Get token error:', error);
    return null;
  }
}

/**
 * Returns stored user object.
 *
 * @returns {Promise<object|null>}
 */
export async function getUser() {
  try {
    const user = await AsyncStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  } catch (error) {
    console.error('Get user error:', error);
    return null;
  }
}