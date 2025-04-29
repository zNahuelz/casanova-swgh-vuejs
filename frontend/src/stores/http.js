import axios from '@/utils/axios';

export const Http = {
  async GET(url, params = {}) {
    const response = await axios.get(url, { params });
    return response.data;
  },

  async POST(url, body) {
    const response = await axios.post(url, body);
    return response.data;
  },

  async PUT(url, body) {
    const response = await axios.put(url, body);
    return response.data;
  },

  async DELETE(url) {
    const response = await axios.delete(url);
    return response.data;
  },

  async GET_BLOB(url) {
    const response = await axios.get(url, { responseType: 'blob' });
    return response.data;
  }
};
