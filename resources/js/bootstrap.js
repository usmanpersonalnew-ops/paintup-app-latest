import axios from 'axios';
window.axios = axios;

axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF token setup - this fixes the 419 errors
if (document.querySelector('meta[name="csrf-token"]')) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] =
        document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}
