import axios from 'axios';
window.axios = axios;

// Make X-Requested-With header available for Laravel to detect AJAX
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Ensure cookies (session) are sent with requests (required for Laravel auth/session)
window.axios.defaults.withCredentials = true;

// Set CSRF token header if present in the page's meta tag
const token = document.head?.querySelector('meta[name="csrf-token"]');
if (token) {
	window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
