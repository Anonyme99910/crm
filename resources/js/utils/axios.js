import axios from 'axios';

const instance = axios.create({
  baseURL: '/crm',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

const token = localStorage.getItem('token');
if (token) {
  instance.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

instance.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/crm/login';
    }
    return Promise.reject(error);
  }
);

export default instance;
