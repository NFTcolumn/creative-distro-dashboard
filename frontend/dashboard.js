/**
 * Creative Distro Dashboard JavaScript
 * Handles all dashboard functionality including invites, network stats, and user management
 */

// API base URL - adjust this to match your server setup
const API_BASE_URL = 'https://creative-distro-dashboard.onrender.com/dashboard_api.php';

// Global state
let currentUser = null;
let dashboardStats = null;

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

/**
 * Initialize the dashboard
 */
async function initializeDashboard() {
    try {
        // Check if user is authenticated
        const user = await getCurrentUser();
        if (!user) {
            // Redirect to login page
            window.location.href = 'login.html';
            return;
        }

        currentUser = user;
        
        // Load dashboard data
        await loadDashboardStats();
        await loadInvites();
        
        // Set up event listeners
        setupEventListeners();
        
    } catch (error) {
        console.error('Failed to initialize dashboard:', error);
        showAlert('Failed to load dashboard. Please refresh the page.', 'error');
    }
}

/**
 * Get current user information
 */
async function getCurrentUser() {
    try {
        const response = await fetch(`${API_BASE_URL}/user/profile`, {
            method: 'GET',
            credentials: 'include'
        });
        
        if (response.ok) {
            const data = await response.json();
            return data.user;
        }
        return null;
    } catch (error) {
        console.error('Error getting current user:', error);
        return null;
    }
}

/**
 * Load dashboard statistics
 */
async function loadDashboardStats() {
    try {
        const response = await fetch(`${API_BASE_URL}/stats/dashboard`, {
            method: 'GET',
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error('Failed to load dashboard stats');
        }
        
        const data = await response.json();
        dashboardStats = data;
        
        // Update UI with stats
        updateDashboardUI(data);
        
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
        showAlert('Failed to load dashboard statistics.', 'error');
    }
}

/**
 * Update dashboard UI with stats
 */
function updateDashboardUI(data) {
    // Update welcome message
    const welcomeElement = document.getElementById('user-welcome');
    if (welcomeElement && data.user) {
        welcomeElement.textContent = `Welcome back, ${data.user.first_name}!`;
    }
    
    // Update referral code
    const referralCodeElement = document.getElementById('referral-code');
    if (referralCodeElement && data.user) {
        referralCodeElement.textContent = data.user.referral_code;
    }
    
    // Update stats cards
    updateStatCard('invite-quota', data.user?.invite_quota || 0);
    updateStatCard('total-sent', data.user?.total_invites_sent || 0);
    updateStatCard('successful-invites', data.user?.total_successful_invites || 0);
    updateStatCard('network-size', data.network_stats?.total_network_size || 0);
    
    // Update network levels
    for (let i = 1; i <= 6; i++) {
        const levelElement = document.getElementById(`level-${i}`);
        if (levelElement) {
            levelElement.textContent = data.network_stats?.[`level_${i}_count`] || 0;
        }
    }
}

/**
 * Update a stat card
 */
function updateStatCard(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value;
    }
}

/**
 * Load user's invites
 */
async function loadInvites() {
    try {
        const response = await fetch(`${API_BASE_URL}/invites/list`, {
            method: 'GET',
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error('Failed to load invites');
        }
        
        const data = await response.json();
        displayInvites(data.invites);
        
    } catch (error) {
        console.error('Error loading invites:', error);
        showAlert('Failed to load invites.', 'error');
    }
}

/**
 * Display invites in the UI
 */
function displayInvites(invites) {
    const loadingElement = document.getElementById('invites-loading');
    const listElement = document.getElementById('invites-list');
    
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }
    
    if (!listElement) return;
    
    listElement.style.display = 'block';
    
    if (!invites || invites.length === 0) {
        listElement.innerHTML = '<p style="text-align: center; color: var(--secondary-text-color); padding: 20px;">No invites sent yet.</p>';
        return;
    }
    
    const invitesHTML = invites.map(invite => {
        const date = new Date(invite.created_at).toLocaleDateString();
        const statusClass = `status-${invite.status.toLowerCase()}`;
        
        return `
            <div class="invite-item">
                <div class="invite-info">
                    <div class="invite-email">${invite.invitee_email}</div>
                    <div class="invite-date">Sent on ${date}</div>
                </div>
                <div class="invite-status ${statusClass}">${invite.status}</div>
            </div>
        `;
    }).join('');
    
    listElement.innerHTML = invitesHTML;
}

/**
 * Set up event listeners
 */
function setupEventListeners() {
    // Invite form submission
    const inviteForm = document.getElementById('invite-form');
    if (inviteForm) {
        inviteForm.addEventListener('submit', handleInviteSubmission);
    }
}

/**
 * Handle invite form submission
 */
async function handleInviteSubmission(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const email = formData.get('email');
    const name = formData.get('name');
    
    const submitButton = document.getElementById('send-invite-btn');
    
    try {
        // Disable submit button
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Sending...';
        }
        
        // Clear previous alerts
        clearAlert();
        
        // Send invite
        const response = await fetch(`${API_BASE_URL}/invites/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ email, name })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showAlert('Invite sent successfully!', 'success');
            
            // Reset form
            event.target.reset();
            
            // Reload dashboard data
            await loadDashboardStats();
            await loadInvites();
            
        } else {
            showAlert(data.error || 'Failed to send invite', 'error');
        }
        
    } catch (error) {
        console.error('Error sending invite:', error);
        showAlert('Failed to send invite. Please try again.', 'error');
    } finally {
        // Re-enable submit button
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = 'Send Invite';
        }
    }
}

/**
 * Copy referral code to clipboard
 */
function copyReferralCode() {
    const referralCodeElement = document.getElementById('referral-code');
    if (!referralCodeElement) return;
    
    const code = referralCodeElement.textContent;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(code).then(() => {
            showAlert('Referral code copied to clipboard!', 'success');
        }).catch(() => {
            fallbackCopyToClipboard(code);
        });
    } else {
        fallbackCopyToClipboard(code);
    }
}

/**
 * Fallback copy to clipboard method
 */
function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showAlert('Referral code copied to clipboard!', 'success');
    } catch (err) {
        showAlert('Failed to copy referral code', 'error');
    }
    
    document.body.removeChild(textArea);
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('invite-alert');
    if (!alertContainer) return;
    
    const alertClass = type === 'error' ? 'alert-error' : 'alert-success';
    
    alertContainer.innerHTML = `
        <div class="alert ${alertClass}">
            ${message}
        </div>
    `;
    
    // Auto-hide success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            clearAlert();
        }, 5000);
    }
}

/**
 * Clear alert message
 */
function clearAlert() {
    const alertContainer = document.getElementById('invite-alert');
    if (alertContainer) {
        alertContainer.innerHTML = '';
    }
}

/**
 * Refresh dashboard data
 */
async function refreshDashboard() {
    try {
        await loadDashboardStats();
        await loadInvites();
        showAlert('Dashboard refreshed!', 'success');
    } catch (error) {
        console.error('Error refreshing dashboard:', error);
        showAlert('Failed to refresh dashboard', 'error');
    }
}

/**
 * Format number with commas
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Handle logout
 */
async function logout() {
    try {
        const response = await fetch(`${API_BASE_URL}/auth/logout`, {
            method: 'POST',
            credentials: 'include'
        });
        
        if (response.ok) {
            window.location.href = 'login.html';
        } else {
            showAlert('Failed to logout', 'error');
        }
    } catch (error) {
        console.error('Error during logout:', error);
        showAlert('Failed to logout', 'error');
    }
}

// Make functions available globally
window.copyReferralCode = copyReferralCode;
window.refreshDashboard = refreshDashboard;
window.logout = logout;
