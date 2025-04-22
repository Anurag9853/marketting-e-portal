// API Configuration
const API_BASE_URL = 'http://localhost/project2/backend/api';

// DOM Elements
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const guestContent = document.getElementById('guestContent');
const userDashboard = document.getElementById('userDashboard');
const navButtons = document.querySelector('.nav-buttons');

// Current user data
let currentUser = null;

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    checkAuthStatus();
    
    // Add registration form event listener
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const userData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                role: document.getElementById('role').value,
                company_name: document.getElementById('company_name').value,
                phone: document.getElementById('phone').value
            };
            
            await register(userData);
        });
    }

    // Contact Form Handler
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Get form elements
            const nameInput = document.getElementById('contactName');
            const phoneInput = document.getElementById('contactPhone');
            const messageInput = document.getElementById('contactMessage');
            const submitButton = contactForm.querySelector('button[type="submit"]');

            // Disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';

            // Validate form data
            if (!nameInput.value || !phoneInput.value || !messageInput.value) {
                showError('Please fill in all required fields');
                submitButton.disabled = false;
                submitButton.innerHTML = 'Send Message';
                return;
            }

            // Validate phone number format
            const phoneRegex = /^\d{10}$/;
            if (!phoneRegex.test(phoneInput.value)) {
                showError('Please enter a valid 10-digit phone number');
                submitButton.disabled = false;
                submitButton.innerHTML = 'Send Message';
                return;
            }

            const formData = {
                name: nameInput.value,
                phone: phoneInput.value,
                message: messageInput.value
            };

            try {
                const response = await fetch('http://localhost/project2/backend/api/contact/submit.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();
                
                if (data.success) {
                    showMessage('Message sent successfully!');
                    contactForm.reset();
                } else {
                    showError(data.message || 'Failed to send message');
                }
            } catch (error) {
                console.error('Error submitting contact form:', error);
                showError('An error occurred while sending your message. Please try again later.');
            } finally {
                // Re-enable submit button and restore original text
                submitButton.disabled = false;
                submitButton.innerHTML = 'Send Message';
            }
        });
    }
});

// API Functions
async function checkAuthStatus() {
    try {
        const response = await fetch(`${API_BASE_URL}/auth/status.php`, {
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error('Failed to check authentication status');
        }
        
        const data = await response.json();
        
        if (data.authenticated) {
            currentUser = data.user;
            updateUIForLoggedInUser(data.user);
            if (data.user.role === 'admin') {
                loadAdminDashboard();
            }
        } else {
            showGuestContent();
        }
    } catch (error) {
        console.error('Error checking auth status:', error);
        showGuestContent();
    }
}

async function login(email, password) {
    try {
        const response = await fetch(`${API_BASE_URL}/auth/login.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password }),
            credentials: 'include'
        });
        
        const data = await response.json();
        if (data.success) {
            currentUser = data.user;
            updateUIForLoggedInUser(data.user);
            window.location.href = '/project2/frontend/';
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('Login error:', error);
        showError('An error occurred during login');
    }
}

async function register(userData) {
    try {
        // Validate form data
        if (!userData.name || !userData.email || !userData.password || !userData.role || !userData.company_name || !userData.phone) {
            showError('Please fill in all required fields');
            return;
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(userData.email)) {
            showError('Please enter a valid email address');
            return;
        }

        // Validate password strength
        if (userData.password.length < 6) {
            showError('Password must be at least 6 characters long');
            return;
        }

        // Ensure role is valid
        const validRoles = ['admin', 'marketer', 'client'];
        if (!validRoles.includes(userData.role)) {
            showError('Please select a valid role');
            return;
        }

        console.log('Submitting registration with data:', userData);

        const response = await fetch(`${API_BASE_URL}/auth/register.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData),
            credentials: 'include'
        });
        
        const data = await response.json();
        console.log('Registration response:', data);
        
        if (data.success) {
            // After successful registration, log the user in
            await login(userData.email, userData.password);
        } else {
            showError(data.message || 'An error occurred during registration');
        }
    } catch (error) {
        console.error('Registration error:', error);
        showError('An error occurred during registration. Please try again.');
    }
}

// UI Functions
function showGuestContent() {
    if (guestContent) guestContent.classList.remove('hidden');
    if (userDashboard) userDashboard.classList.add('hidden');
    
    if (navButtons) {
        navButtons.innerHTML = `
            <a href="login.html" class="py-2 px-2 font-medium text-gray-500 rounded hover:bg-green-500 hover:text-white transition duration-300">Log In</a>
            <a href="register.html" class="py-2 px-2 font-medium text-white bg-green-500 rounded hover:bg-green-400 transition duration-300">Sign Up</a>
        `;
    }
}

function updateUIForLoggedInUser(user) {
    if (guestContent) guestContent.classList.add('hidden');
    if (userDashboard) userDashboard.classList.remove('hidden');
    
    if (navButtons) {
        navButtons.innerHTML = `
            <span class="text-gray-700 mr-4">${user.name}</span>
            <button onclick="logout()" class="py-2 px-2 font-medium text-gray-500 rounded hover:bg-red-500 hover:text-white transition duration-300">
                Logout
            </button>
        `;
    }

    // Show admin section if user is admin
    const adminSection = document.getElementById('adminSection');
    if (user.role === 'admin' && adminSection) {
        adminSection.classList.remove('hidden');
        loadAdminDashboard();
    }

    // Load user's campaigns and analytics
    loadUserCampaigns();
    loadUserAnalytics();
}

async function loadUserCampaigns() {
    try {
        const response = await fetch(`${API_BASE_URL}/campaigns/user.php`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            const campaignsList = document.getElementById('campaignsList');
            if (campaignsList) {
                campaignsList.innerHTML = data.campaigns.map(campaign => `
                    <div class="border rounded p-4">
                        <h4 class="font-semibold">${campaign.title}</h4>
                        <p class="text-sm text-gray-600">Status: ${campaign.status}</p>
                        <p class="text-sm text-gray-600">Budget: $${campaign.budget}</p>
                    </div>
                `).join('');
            }
        }
    } catch (error) {
        console.error('Error loading campaigns:', error);
    }
}

async function loadUserAnalytics() {
    try {
        const response = await fetch(`${API_BASE_URL}/analytics/user.php`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            const analyticsData = document.getElementById('analyticsData');
            if (analyticsData) {
                analyticsData.innerHTML = `
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold">${data.total_impressions}</p>
                            <p class="text-sm text-gray-600">Impressions</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold">${data.total_clicks}</p>
                            <p class="text-sm text-gray-600">Clicks</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold">${data.total_conversions}</p>
                            <p class="text-sm text-gray-600">Conversions</p>
                        </div>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Error loading analytics:', error);
    }
}

async function loadAdminDashboard() {
    try {
        // Fetch dashboard statistics
        const dashboardResponse = await fetch('http://localhost/project2/backend/api/admin/dashboard.php', {
            credentials: 'include'
        });
        const dashboardData = await dashboardResponse.json();
        
        if (dashboardData.success) {
            document.getElementById('totalUsers').textContent = dashboardData.totalUsers;
            document.getElementById('activeCampaigns').textContent = dashboardData.activeCampaigns;
            document.getElementById('totalMessages').textContent = dashboardData.totalMessages;
        }

        // Fetch recent campaigns
        const campaignsResponse = await fetch('http://localhost/project2/backend/api/admin/recent_campaigns.php', {
            credentials: 'include'
        });
        const campaignsData = await campaignsResponse.json();
        
        if (campaignsData.success) {
            const campaignsTableBody = document.getElementById('recentCampaignsBody');
            campaignsTableBody.innerHTML = campaignsData.campaigns.map(campaign => `
                <tr>
                    <td>${campaign.name}</td>
                    <td>
                        <div>${campaign.user_name}</div>
                        <small class="text-muted">${campaign.user_email}</small>
                    </td>
                    <td>
                        <span class="badge ${getStatusBadgeClass(campaign.status)}">${campaign.status}</span>
                    </td>
                    <td>${campaign.budget}</td>
                    <td>${campaign.start_date}</td>
                    <td>${campaign.end_date}</td>
                </tr>
            `).join('');
        }

        // Update recent users table
        const recentUsersTable = document.getElementById('recentUsersTable');
        if (recentUsersTable) {
            recentUsersTable.innerHTML = dashboardData.recent_users.map(user => `
                <tr>
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>${user.role}</td>
                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                </tr>
            `).join('');
        }
        
        // Update recent messages table
        const recentMessagesTable = document.getElementById('recentMessagesTable');
        if (recentMessagesTable) {
            recentMessagesTable.innerHTML = dashboardData.recent_messages.map(message => `
                <tr>
                    <td>${message.name}</td>
                    <td>${message.email}</td>
                    <td>${message.subject}</td>
                    <td>${new Date(message.created_at).toLocaleDateString()}</td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading admin dashboard:', error);
        showError('Failed to load dashboard data');
    }
}

function getStatusBadgeClass(status) {
    switch (status.toLowerCase()) {
        case 'active':
            return 'bg-success';
        case 'pending':
            return 'bg-warning';
        case 'completed':
            return 'bg-info';
        case 'cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
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
            loadAdminDashboard();
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
            loadAdminDashboard();
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
    const notification = document.getElementById('successNotification');
    if (notification) {
        notification.style.display = 'block';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }
}

function showError(message) {
    const notification = document.getElementById('errorNotification');
    const errorMessageElement = document.getElementById('errorMessage');
    if (notification && errorMessageElement) {
        errorMessageElement.textContent = message;
        notification.style.display = 'block';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }
}

async function logout() {
    try {
        const response = await fetch(`${API_BASE_URL}/auth/logout.php`, {
            method: 'POST',
            credentials: 'include'
        });
        
        const data = await response.json();
        if (data.success) {
            currentUser = null;
            showGuestContent();
            window.location.href = '/project2/frontend/';
        } else {
            showError('Logout failed');
        }
    } catch (error) {
        console.error('Logout error:', error);
        showError('An error occurred during logout');
    }
}

// Form Event Listeners
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        await login(email, password);
    });
}