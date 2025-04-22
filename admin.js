// Check if user is admin
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/auth/status.php`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (!data.authenticated || data.user.role !== 'admin') {
            window.location.href = 'index.html';
            return;
        }
        
        // Load admin dashboard data
        loadDashboardData();
        loadRecentUsers();
        loadRecentCampaigns();
        loadRecentMessages();
        loadUsers();
    } catch (error) {
        console.error('Error:', error);
        window.location.href = 'index.html';
    }
});

async function loadDashboardData() {
    try {
        const response = await fetch(`${API_BASE_URL}/admin/dashboard.php`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalUsers').textContent = data.stats.total_users;
            document.getElementById('activeCampaigns').textContent = data.stats.active_campaigns;
            document.getElementById('totalMessages').textContent = data.stats.total_messages;
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

async function loadRecentUsers() {
    try {
        const response = await fetch(`${API_BASE_URL}/admin/recent_users.php`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            const recentUsers = document.getElementById('recentUsers');
            recentUsers.innerHTML = data.users.map(user => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">${formatDate(user.created_at)}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${user.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${user.email}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${user.company_name}</td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading recent users:', error);
    }
}

async function loadRecentCampaigns() {
    try {
        const response = await fetch(`${API_BASE_URL}/admin/recent_campaigns.php`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            const recentCampaigns = document.getElementById('recentCampaigns');
            recentCampaigns.innerHTML = data.campaigns.map(campaign => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">${formatDate(campaign.created_at)}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${campaign.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${campaign.user_email}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${campaign.status}</td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading recent campaigns:', error);
    }
}

async function loadRecentMessages() {
    try {
        const response = await fetch(`${API_BASE_URL}/admin/recent_messages.php`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            const recentMessages = document.getElementById('recentMessages');
            recentMessages.innerHTML = data.messages.map(message => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">${formatDate(message.created_at)}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${message.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${message.email}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${message.subject}</td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading recent messages:', error);
    }
}

async function loadUsers() {
    try {
        const response = await fetch(`${API_BASE_URL}/admin/users.php`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            const usersList = document.getElementById('usersList');
            usersList.innerHTML = data.users.map(user => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">${user.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${user.email}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${user.role}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${user.company_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <button onclick="changeUserRole('${user.id}', '${user.role === 'admin' ? 'user' : 'admin'}')" 
                                class="text-blue-600 hover:text-blue-900 mr-3">
                            ${user.role === 'admin' ? 'Remove Admin' : 'Make Admin'}
                        </button>
                        <button onclick="deleteUser('${user.id}')" 
                                class="text-red-600 hover:text-red-900">
                            Delete
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

async function changeUserRole(userId, newRole) {
    try {
        const response = await fetch(`${API_BASE_URL}/admin/update_role.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId, role: newRole }),
            credentials: 'include'
        });
        
        const data = await response.json();
        if (data.success) {
            loadUsers();
            showMessage('User role updated successfully');
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('Error updating user role:', error);
        showError('Failed to update user role');
    }
}

async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/admin/delete_user.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId }),
            credentials: 'include'
        });
        
        const data = await response.json();
        if (data.success) {
            loadUsers();
            showMessage('User deleted successfully');
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('Error deleting user:', error);
        showError('Failed to delete user');
    }
}

function formatDate(timestamp) {
    return new Date(timestamp).toLocaleString();
}

function showMessage(message) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg';
    messageDiv.textContent = message;
    document.body.appendChild(messageDiv);
    setTimeout(() => messageDiv.remove(), 3000);
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg';
    errorDiv.textContent = message;
    document.body.appendChild(errorDiv);
    setTimeout(() => errorDiv.remove(), 3000);
} 