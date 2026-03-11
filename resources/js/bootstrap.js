import axios from 'axios';

window.axios = axios;

axios.defaults.baseURL = import.meta.env.VITE_APP_URL || window.location.origin;

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

axios.defaults.withCredentials = true;

// CSRF Token
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found.');
}