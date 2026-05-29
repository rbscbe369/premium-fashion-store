// API Utility for communicating with the backend

const API_BASE = 'api'; // Adjusted for relative paths

async function fetchAPI(endpoint, method = 'GET', data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json'
        }
    };

    if (data) {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(`${API_BASE}${endpoint}`, options);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('API fetch error:', error);
        return { error: error.message };
    }
}

const api = {
    auth: {
        login: (data) => fetchAPI('/auth/login.php', 'POST', data),
        register: (data) => fetchAPI('/auth/register.php', 'POST', data),
        profile: () => fetchAPI('/auth/profile.php'),
        logout: () => fetchAPI('/auth/logout.php')
    },
    products: {
        getAll: (category = '') => fetchAPI(`/products/get.php${category ? '?category='+category : ''}`),
        getSingle: (id) => fetchAPI(`/products/single.php?id=${id}`)
    },
    cart: {
        add: (data) => fetchAPI('/cart/add.php', 'POST', data),
        remove: (cartId) => fetchAPI('/cart/remove.php', 'POST', { cart_id: cartId }),
        get: () => fetchAPI('/cart/get.php')
    },
    orders: {
        create: () => fetchAPI('/orders/create.php', 'POST'),
        get: () => fetchAPI('/orders/get.php')
    },
    admin: {
        products: () => fetchAPI('/admin/products.php'),
        orders: () => fetchAPI('/admin/orders.php')
    },
    tryon: {
        model: (productId) => fetchAPI('/tryon/model.php', 'POST', { product_id: productId })
    }
};

window.api = api;
