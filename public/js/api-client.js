// API Client dùng chung cho bản chuyển MVC -> Web API 100%.
window.API_BASE_URL = window.API_BASE_URL || '';

const ApiClient = {
    tokenKey: 'api_token',
    userKey: 'api_user',

    token() {
        return localStorage.getItem(this.tokenKey) || '';
    },

    user() {
        try { return JSON.parse(localStorage.getItem(this.userKey) || 'null'); }
        catch (e) { return null; }
    },

    setAuth(token, user) {
        localStorage.setItem(this.tokenKey, token);
        localStorage.setItem(this.userKey, JSON.stringify(user || {}));
    },

    logout() {
        localStorage.removeItem(this.tokenKey);
        localStorage.removeItem(this.userKey);
    },

    async request(path, options = {}) {
        const headers = options.headers || {};
        if (!(options.body instanceof FormData)) {
            headers['Content-Type'] = headers['Content-Type'] || 'application/json';
        }

        const token = this.token();
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }

        const response = await fetch(window.API_BASE_URL + path, {
            ...options,
            headers
        });

        let json;
        try { json = await response.json(); }
        catch (e) {
            json = {success:false, status: response.status, message: 'Response không phải JSON', data:null};
        }

        if (json.status === 401 && path !== '/api/auth/login') {
            // Token hết hạn/sai -> xóa token để người dùng đăng nhập lại.
            this.logout();
        }

        return json;
    },

    get(path) {
        return this.request(path, {method:'GET'});
    },

    post(path, data) {
        return this.request(path, {method:'POST', body: JSON.stringify(data || {})});
    },

    put(path, data) {
        return this.request(path, {method:'PUT', body: JSON.stringify(data || {})});
    },

    delete(path) {
        return this.request(path, {method:'DELETE'});
    },

    money(value) {
        return Number(value || 0).toLocaleString('vi-VN') + 'đ';
    },

    toast(message, type = 'info') {
        let box = document.getElementById('apiToastBox');
        if (!box) {
            box = document.createElement('div');
            box.id = 'apiToastBox';
            box.style.cssText = 'position:fixed;right:18px;top:18px;z-index:99999;display:grid;gap:10px';
            document.body.appendChild(box);
        }
        const item = document.createElement('div');
        const bg = type === 'success' ? '#16a34a' : (type === 'error' ? '#dc2626' : '#0f172a');
        item.style.cssText = `background:${bg};color:white;padding:12px 16px;border-radius:14px;box-shadow:0 12px 28px rgba(15,23,42,.2);font-weight:800;max-width:360px`;
        item.textContent = message;
        box.appendChild(item);
        setTimeout(() => item.remove(), 3200);
    }
};

window.ApiClient = ApiClient;
