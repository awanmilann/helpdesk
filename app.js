// IT Helpdesk System - Frontend JavaScript
// Uses AJAX to communicate with PHP API

// Helper function to escape HTML
const escapeHtml = (text) => {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
};

document.addEventListener('DOMContentLoaded', () => {
    let statusChart = null;
    let categoryChart = null;
    
    // Language and Translation System
    let currentLanguage = localStorage.getItem('helpdesk_language') || 'en';
    
    const translations = {
        en: {
            menu: {
                main_dashboard: 'Main Dashboard',
                all_tickets: 'All Tickets',
                report: 'Report',
                user_management: 'User Management',
                create_ticket: 'Create Ticket',
                my_tickets: 'My Tickets',
                guideline: 'Panduan (Guideline)'
            },
            welcome: {
                title: 'Welcome to IT Helpdesk',
                subtitle: 'Manage your support tickets',
                my_tickets: 'My Tickets'
            }
        },
        id: {
            menu: {
                main_dashboard: 'Dashboard Utama',
                all_tickets: 'Semua Tiket',
                report: 'Laporan',
                user_management: 'Manajemen Pengguna',
                create_ticket: 'Buat Tiket',
                my_tickets: 'Tiket Saya',
                guideline: 'Panduan'
            },
            welcome: {
                title: 'Selamat Datang di IT Helpdesk',
                subtitle: 'Kelola tiket dukungan Anda',
                my_tickets: 'Tiket Saya'
            }
        }
    };
    
    // Translation function
    const t = (key) => {
        const keys = key.split('.');
        let value = translations[currentLanguage];
        for (const k of keys) {
            value = value?.[k];
        }
        return value || key;
    };
    
    // Update all translatable elements
    const updateTranslations = () => {
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            el.textContent = t(key);
        });
        
        // Update welcome message
        const welcomeTitle = document.querySelector('#user-view h1');
        const welcomeSubtitle = document.querySelector('#user-view .text-green-100');
        const myTicketsLabel = document.querySelector('#user-view .bg-white.bg-opacity-20 p.text-sm');
        
        if (welcomeTitle) welcomeTitle.textContent = t('welcome.title');
        if (welcomeSubtitle) welcomeSubtitle.textContent = t('welcome.subtitle');
        if (myTicketsLabel) myTicketsLabel.textContent = t('welcome.my_tickets');
    };
    
    // Sidebar functionality
    const initSidebar = () => {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const mainContentWrapper = document.getElementById('main-content-wrapper');
        const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
        
        if (isCollapsed) {
            sidebar.classList.remove('expanded');
            sidebar.classList.add('collapsed');
            mainContentWrapper.classList.add('sidebar-collapsed');
            const icon = sidebarToggle.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            }
        }
        
        sidebarToggle.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const isExpanded = sidebar.classList.contains('expanded');
            const icon = sidebarToggle.querySelector('i');
            
            if (isExpanded) {
                sidebar.classList.remove('expanded');
                sidebar.classList.add('collapsed');
                mainContentWrapper.classList.add('sidebar-collapsed');
                if (icon) {
                    icon.classList.remove('fa-chevron-left');
                    icon.classList.add('fa-chevron-right');
                }
                localStorage.setItem('sidebar_collapsed', 'true');
            } else {
                sidebar.classList.remove('collapsed');
                sidebar.classList.add('expanded');
                mainContentWrapper.classList.remove('sidebar-collapsed');
                if (icon) {
                    icon.classList.remove('fa-chevron-right');
                    icon.classList.add('fa-chevron-left');
                }
                localStorage.setItem('sidebar_collapsed', 'false');
            }
        });
        
        // Sidebar menu item click handlers
        const setupSidebarMenu = () => {
            // Admin menu items
            const adminMenuItems = {
                'admin-menu-dashboard': 'tab-dashboard',
                'admin-menu-all-tickets': 'tab-all-tickets',
                'admin-menu-report': 'tab-report',
                'admin-menu-user-management': 'tab-user-management'
            };
            
            Object.keys(adminMenuItems).forEach(menuId => {
                const menuItem = document.getElementById(menuId);
                if (menuItem) {
                    menuItem.addEventListener('click', (e) => {
                        e.preventDefault();
                        const tabId = adminMenuItems[menuId];
                        const tab = document.getElementById(tabId);
                        if (tab) {
                            // Remove active from all admin menu items
                            document.querySelectorAll('#admin-sidebar-menu .sidebar-menu-item').forEach(item => {
                                item.classList.remove('active');
                            });
                            // Add active to clicked item
                            menuItem.classList.add('active');
                            // Trigger tab click
                            tab.click();
                        }
                    });
                }
            });
            
            // User menu items
            const userMenuItems = {
                'user-menu-dashboard': 'user-tab-dashboard',
                'user-menu-create': 'user-tab-create',
                'user-menu-tickets': 'user-tab-tickets',
                'user-menu-panduan': 'user-tab-panduan'
            };
            
            Object.keys(userMenuItems).forEach(menuId => {
                const menuItem = document.getElementById(menuId);
                if (menuItem) {
                    menuItem.addEventListener('click', (e) => {
                        e.preventDefault();
                        const tabId = userMenuItems[menuId];
                        const tab = document.getElementById(tabId);
                        if (tab) {
                            // Remove active from all user menu items
                            document.querySelectorAll('#user-sidebar-menu .sidebar-menu-item').forEach(item => {
                                item.classList.remove('active');
                            });
                            // Add active to clicked item
                            menuItem.classList.add('active');
                            // Trigger tab click
                            tab.click();
                        }
                    });
                }
            });
        };
        
        setupSidebarMenu();
    };
    
    // Language switcher
    const initLanguageSwitcher = () => {
        const langButtons = document.querySelectorAll('.language-switcher-btn');
        
        // Set initial active state
        langButtons.forEach(btn => {
            if (btn.dataset.lang === currentLanguage) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        langButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const lang = btn.dataset.lang;
                currentLanguage = lang;
                localStorage.setItem('helpdesk_language', lang);
                
                // Update active state
                langButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                // Update translations
                updateTranslations();
            });
        });
    };
    
    // Show/hide sidebar menus based on user role
    const updateSidebarVisibility = (isAdmin) => {
        const adminMenu = document.getElementById('admin-sidebar-menu');
        const userMenu = document.getElementById('user-sidebar-menu');
        
        if (isAdmin) {
            adminMenu.classList.remove('hidden');
            userMenu.classList.add('hidden');
        } else {
            adminMenu.classList.add('hidden');
            userMenu.classList.remove('hidden');
        }
    };
    
    // Initialize sidebar and language
    initSidebar();
    initLanguageSwitcher();
    updateTranslations();
    
    // Store functions globally for later use
    window.updateSidebarVisibility = updateSidebarVisibility;
    window.updateTranslations = updateTranslations;
    let currentUser = null;
    
    const ADMIN_EMAILS = ['admin@helpdesk.local'];
    
    const views = {
        authContainer: document.getElementById('auth-container'),
        appContainer: document.getElementById('app-container'),
        loginView: document.getElementById('login-view'),
        signupView: document.getElementById('signup-view'),
        forgotPasswordView: document.getElementById('forgot-password-view'),
        resetPasswordView: document.getElementById('reset-password-view'),
        userView: document.getElementById('user-view'),
        adminView: document.getElementById('admin-view'),
    };

    // --- API Helper Functions ---
    async function apiCall(endpoint, method = 'GET', data = null, isFormData = false) {
        const options = {
            method: method,
            credentials: 'same-origin'
        };
        
        if (data && method !== 'GET') {
            if (isFormData) {
                // Don't set Content-Type for FormData, let browser set it with boundary
                options.body = data;
            } else {
                options.headers = {
                    'Content-Type': 'application/json',
                };
                options.body = JSON.stringify(data);
            }
        }
        
        try {
            let url;
            if (endpoint.includes('&')) {
                // Handle endpoints with parameters like 'get_attachments&ticket_id=123'
                url = `api.php?action=${endpoint}`;
            } else {
                url = `api.php?action=${endpoint}`;
            }
            console.log('API Call URL:', url);
            const response = await fetch(url, options);
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, message: 'Network error occurred' };
        }
    }

    // --- Helper Functions ---
    const showView = (viewToShow) => {
        console.log('showView called with:', viewToShow);
        console.log('Available views:', views);
        
        Object.values(views).forEach(v => v.classList.add('hidden'));
        if (viewToShow === views.appContainer || viewToShow === views.authContainer) {
            viewToShow.classList.remove('hidden');
            console.log('Showing container view');
        } else {
            views.authContainer.classList.remove('hidden');
            viewToShow.classList.remove('hidden');
            console.log('Showing auth view:', viewToShow);
        }
    };
    
    const displayError = (elementId, message) => {
        const el = document.getElementById(elementId);
        if (el) {
            el.textContent = message;
            el.classList.remove('hidden');
            // Ensure error class is set
            if (elementId.includes('error')) {
                el.classList.add('error');
                el.classList.remove('success');
            } else if (elementId.includes('success')) {
                el.classList.add('success');
                el.classList.remove('error');
            }
        }
    };
    
    const clearError = (elementId) => {
        const el = document.getElementById(elementId);
        if (el) {
            el.classList.add('hidden');
            el.textContent = '';
            // Reset color classes
            el.classList.remove('text-red-500', 'text-green-500', 'error', 'success');
        }
    };
    
    // Reset UI function
    const resetUI = () => {
        console.log('resetUI called');
        
        // Reset login form
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            console.log('Resetting login form');
            loginForm.reset();
        }
        
        // Clear all error messages
        console.log('Clearing error messages');
        clearError('login-error');
        clearError('signup-error');
        clearError('user-notification');
        
        // Reset login button state
        const loginBtn = document.querySelector('#login-form button[type="submit"]');
        if (loginBtn) {
            console.log('Resetting login button');
            loginBtn.textContent = 'Login';
            loginBtn.disabled = false;
        }
        
        // Reset user email display
        const userEmailDisplay = document.getElementById('user-email-display');
        if (userEmailDisplay) {
            userEmailDisplay.textContent = '';
        }
        
        // Hide change password button
        const changePasswordBtn = document.getElementById('change-password-btn');
        if (changePasswordBtn) {
            changePasswordBtn.classList.add('hidden');
        }
        
        // Clear any modal content
        const modalPlaceholder = document.getElementById('modal-placeholder');
        if (modalPlaceholder) {
            modalPlaceholder.innerHTML = '';
        }
        
        // Reset form states
        const signupForm = document.getElementById('signup-form');
        if (signupForm) {
            signupForm.reset();
        }
        
        // Clear ticket lists
        const userTicketList = document.getElementById('user-ticket-list');
        if (userTicketList) {
            userTicketList.innerHTML = '';
        }
        
        const allTicketsTable = document.getElementById('all-tickets-table');
        if (allTicketsTable) {
            allTicketsTable.innerHTML = '';
        }
        
        const userManagementTable = document.getElementById('user-management-table');
        if (userManagementTable) {
            userManagementTable.innerHTML = '';
        }
        
        // Reset admin dashboard stats
        const statsElements = ['stats-total', 'stats-progress', 'stats-delayed', 'stats-done'];
        statsElements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = '0';
            }
        });
        
        // Clear recent activity
        const recentActivityList = document.getElementById('recent-activity-list');
        if (recentActivityList) {
            recentActivityList.innerHTML = '';
        }
        
        console.log('UI reset completed');
    };

    const statusStyles = {
        'Open': 'bg-gray-200 text-gray-800 border-gray-400',
        'In Progress': 'bg-blue-100 text-blue-800 border-blue-400',
        'Done': 'bg-green-100 text-green-800 border-green-400',
        'Delayed': 'bg-yellow-100 text-yellow-800 border-yellow-400',
        'Revisi': 'bg-orange-100 text-orange-800 border-orange-400'
    };
    
    // Helper functions for styling
    const getStatusClass = (status) => {
        const classes = {
            'Open': 'bg-gray-100 text-gray-800',
            'In Progress': 'bg-blue-100 text-blue-800',
            'Done': 'bg-green-100 text-green-800',
            'Delayed': 'bg-yellow-100 text-yellow-800',
            'Revisi': 'bg-orange-100 text-orange-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    };
    
    const getStatusBorderClass = (status) => {
        const classes = {
            'Open': 'border-l-4 border-l-gray-400',
            'In Progress': 'border-l-4 border-l-blue-400',
            'Done': 'border-l-4 border-l-green-400',
            'Delayed': 'border-l-4 border-l-yellow-400',
            'Revisi': 'border-l-4 border-l-orange-400'
        };
        return classes[status] || 'border-l-4 border-l-gray-400';
    };
    
    const getPriorityClass = (priority) => {
        const classes = {
            'Low': 'bg-green-100 text-green-800',
            'Medium': 'bg-yellow-100 text-yellow-800',
            'High': 'bg-orange-100 text-orange-800',
            'Critical': 'bg-red-100 text-red-800'
        };
        return classes[priority] || 'bg-gray-100 text-gray-800';
    };

    // --- Modal Functions ---
    const showModal = (title, content, onSave, saveText = 'Save') => {
        const modalPlaceholder = document.getElementById('modal-placeholder');
        modalPlaceholder.innerHTML = `
            <div class="modal active">
                <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">${title}</h3>
                    <div id="modal-error" class="text-red-500 text-sm mb-4 hidden"></div>
                    <form id="modal-form">${content}</form>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" id="close-modal-btn" class="bg-gray-200 py-2 px-4 rounded-md text-sm font-medium hover:bg-gray-300">Cancel</button>
                        <button type="submit" form="modal-form" class="bg-indigo-600 text-white py-2 px-4 rounded-md text-sm font-medium hover:bg-indigo-700">${saveText}</button>
                    </div>
                </div>
            </div>`;
        
        const closeModal = () => modalPlaceholder.innerHTML = '';
        document.getElementById('close-modal-btn').onclick = closeModal;
        
        document.getElementById('modal-form').onsubmit = async (e) => {
            e.preventDefault();
            const result = await onSave(new FormData(e.target));
            if (result !== false) {
                closeModal();
            }
        };
    };
    
    const showConfirmModal = (title, message, onConfirm) => {
        const modalPlaceholder = document.getElementById('modal-placeholder');
        modalPlaceholder.innerHTML = `
            <div class="modal active">
                <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-sm">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">${title}</h3>
                    <p class="text-sm text-gray-600 mb-6">${message}</p>
                    <div class="flex justify-end gap-3">
                        <button id="confirm-cancel" class="bg-gray-200 py-2 px-4 rounded-md text-sm font-medium hover:bg-gray-300">Cancel</button>
                        <button id="confirm-ok" class="bg-red-600 text-white py-2 px-4 rounded-md text-sm font-medium hover:bg-red-700">Delete</button>
                    </div>
                </div>
            </div>`;
        
        const closeModal = () => modalPlaceholder.innerHTML = '';
        document.getElementById('confirm-cancel').onclick = closeModal;
        document.getElementById('confirm-ok').onclick = () => {
            onConfirm();
            closeModal();
        };
    };
    
    // Password Change Modal
    const showPasswordChangeModal = () => {
        const title = 'Change Password';
        const content = `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Current Password</label>
                    <input type="password" name="current_password" class="mt-1 w-full p-2 border rounded-md" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" name="new_password" class="mt-1 w-full p-2 border rounded-md" required minlength="6">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="mt-1 w-full p-2 border rounded-md" required minlength="6">
                </div>
            </div>
        `;

        const onSave = async (formData) => {
            const modalError = document.getElementById('modal-error');
            const data = {
                current_password: formData.get('current_password'),
                new_password: formData.get('new_password'),
                confirm_password: formData.get('confirm_password')
            };

            const result = await apiCall('change_password', 'POST', data);
            
            if (!result.success) {
                modalError.textContent = result.message;
                modalError.classList.remove('hidden');
                return false;
            }

            alert('Password changed successfully!');
            return true;
        };

        showModal(title, content, onSave, 'Change');
    };

    // Check URL for reset password token
    const urlParams = new URLSearchParams(window.location.search);
    const resetToken = urlParams.get('reset_token');
    if (resetToken) {
        // Verify token first
        apiCall('verify_reset_token', 'POST', { token: resetToken }).then(result => {
            if (result.success) {
                document.getElementById('reset-token').value = resetToken;
                showView(views.resetPasswordView);
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            } else {
                displayError('reset-password-error', result.message || 'Invalid or expired reset token');
                showView(views.resetPasswordView);
            }
        });
    }

    // --- Auth Views ---
    document.getElementById('show-signup-view').addEventListener('click', (e) => {
        e.preventDefault();
        showView(views.signupView);
    });
    
    document.getElementById('show-login-view-from-signup').addEventListener('click', (e) => {
        e.preventDefault();
        showView(views.loginView);
    });

    // Forgot Password View
    document.getElementById('show-forgot-password-view').addEventListener('click', (e) => {
        e.preventDefault();
        clearError('forgot-password-error');
        clearError('forgot-password-success');
        document.getElementById('forgot-password-form').reset();
        showView(views.forgotPasswordView);
    });

    document.getElementById('back-to-login-from-forgot').addEventListener('click', (e) => {
        e.preventDefault();
        clearError('forgot-password-error');
        clearError('forgot-password-success');
        showView(views.loginView);
    });

    document.getElementById('back-to-login-from-reset').addEventListener('click', (e) => {
        e.preventDefault();
        clearError('reset-password-error');
        clearError('reset-password-success');
        showView(views.loginView);
    });

    // Forgot Password Form Submit
    document.getElementById('forgot-password-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        clearError('forgot-password-error');
        clearError('forgot-password-success');
        
        const email = document.getElementById('forgot-password-email').value.trim().toLowerCase();
        
        if (!email) {
            displayError('forgot-password-error', 'Email is required');
            return;
        }

        const result = await apiCall('request_password_reset', 'POST', { email });
        
        if (result.success) {
            const successEl = document.getElementById('forgot-password-success');
            successEl.textContent = result.message || 'Password reset link has been sent to your email. Please check your inbox.';
            successEl.classList.remove('hidden');
            document.getElementById('forgot-password-form').reset();
        } else {
            displayError('forgot-password-error', result.message || 'Failed to send reset link');
        }
    });

    // Reset Password Form Submit
    document.getElementById('reset-password-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        clearError('reset-password-error');
        clearError('reset-password-success');
        
        const token = document.getElementById('reset-token').value;
        const newPassword = document.getElementById('reset-password-new').value;
        const confirmPassword = document.getElementById('reset-password-confirm').value;
        
        if (!token) {
            displayError('reset-password-error', 'Invalid reset token');
            return;
        }

        if (newPassword.length < 6) {
            displayError('reset-password-error', 'Password must be at least 6 characters');
            return;
        }

        if (newPassword !== confirmPassword) {
            displayError('reset-password-error', 'Passwords do not match');
            return;
        }

        const result = await apiCall('reset_password', 'POST', { 
            token, 
            new_password: newPassword 
        });
        
        if (result.success) {
            const successEl = document.getElementById('reset-password-success');
            successEl.textContent = result.message || 'Password has been reset successfully. You can now login with your new password.';
            successEl.classList.remove('hidden');
            document.getElementById('reset-password-form').reset();
            
            // Redirect to login after 2 seconds
            setTimeout(() => {
                showView(views.loginView);
            }, 2000);
        } else {
            displayError('reset-password-error', result.message || 'Failed to reset password');
        }
    });

    document.getElementById('signup-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        clearError('signup-error');
        
        const email = document.getElementById('signup-email').value.trim().toLowerCase();
        
        
        const data = {
            username: document.getElementById('signup-username').value,
            name: document.getElementById('signup-name').value,
            email: email,
            department: document.getElementById('signup-dept').value,
            password: document.getElementById('signup-password').value
        };
        
        const result = await apiCall('signup', 'POST', data);
        
        if (result.success) {
            alert(result.message);
            showView(views.loginView);
            document.getElementById('signup-form').reset();
        } else {
            displayError('signup-error', result.message);
        }
    });

    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Login form submitted');
        
        // Reset UI first to clear any previous state
        clearError('login-error');
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Logging in...';
        submitBtn.disabled = true;
        
        const data = {
            email: document.getElementById('login-email').value,
            password: document.getElementById('login-password').value
        };
        
        console.log('Sending login data:', data);
        
        try {
            const result = await apiCall('login', 'POST', data);
            console.log('Login response:', result);
            
            if (result.success) {
                currentUser = result.user;
                console.log('Login successful, user:', currentUser);
                
                // Show success message
                const loginErrorEl = document.getElementById('login-error');
                if (loginErrorEl) {
                    loginErrorEl.textContent = 'Login successful! Redirecting...';
                    loginErrorEl.classList.remove('hidden', 'error');
                    loginErrorEl.classList.add('success');
                }
                
            // Wait a moment then redirect to main app
            setTimeout(() => {
                console.log('Calling main() after login success');
                main();
            }, 1500);
                
            } else {
                console.log('Login failed:', result.message);
                displayError('login-error', result.message);
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Login error:', error);
            displayError('login-error', 'Network error. Please try again.');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });

    document.getElementById('logout-btn').addEventListener('click', async () => {
        console.log('Logout button clicked');
        
        try {
            console.log('Calling logout API...');
            const result = await apiCall('logout', 'POST');
            console.log('Logout API result:', result);
            
            // Clear charts
            if (statusChart) {
                console.log('Destroying status chart');
                statusChart.destroy();
            }
            if (categoryChart) {
                console.log('Destroying category chart');
                categoryChart.destroy();
            }
            
            // Clear user data
            console.log('Clearing current user');
            currentUser = null;
            
            // Reset all UI elements
            console.log('Resetting UI...');
            resetUI();
            
            // Show login view immediately
            console.log('Showing login view');
            showView(views.loginView);
            
            console.log('Logout completed successfully');
            
        } catch (error) {
            console.error('Logout error:', error);
            // Even if logout fails, clear local state
            currentUser = null;
            resetUI();
            showView(views.loginView);
        }
    });

    document.getElementById('change-password-btn').addEventListener('click', () => {
        showPasswordChangeModal();
    });

    // Notifications system
    const notifBtn = document.getElementById('notif-bell-btn');
    const notifBadge = document.getElementById('notif-badge');
    const notifDropdown = document.getElementById('notif-dropdown');
    const notifList = document.getElementById('notif-list');
    const notifEmpty = document.getElementById('notif-empty');
    const closeNotifDropdown = document.getElementById('close-notif-dropdown');
    
    // Load and display notifications
    const loadNotifications = async () => {
        try {
            const result = await apiCall('get_notifications');
            if (result.success) {
                // Update badge
                if (notifBadge) {
                    if (result.unread_count > 0) {
                        notifBadge.textContent = result.unread_count > 99 ? '99+' : result.unread_count;
                        notifBadge.classList.remove('hidden');
                    } else {
                        notifBadge.classList.add('hidden');
                    }
                }
                
                // Store notifications for dropdown
                window.currentNotifications = result.notifications || [];
                
                // Render notifications if dropdown is open
                if (notifDropdown && !notifDropdown.classList.contains('hidden')) {
                    renderNotifications(result.notifications);
                }
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    };
    
    // Render notifications in dropdown
    const renderNotifications = (notifications) => {
        if (!notifList || !notifEmpty) return;
        
        if (!notifications || notifications.length === 0) {
            notifList.innerHTML = '';
            notifEmpty.classList.remove('hidden');
            return;
        }
        
        notifEmpty.classList.add('hidden');
        notifList.innerHTML = '';
        
        notifications.forEach(notif => {
            const notifItem = document.createElement('div');
            notifItem.className = `p-4 hover:bg-gray-50 cursor-pointer transition-colors ${notif.is_read ? 'bg-gray-50' : 'bg-blue-50'}`;
            notifItem.dataset.notifId = notif.id;
            notifItem.dataset.isRead = notif.is_read;
            
            const date = new Date(notif.created_at).toLocaleString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            notifItem.innerHTML = `
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm ${notif.is_read ? 'text-gray-600' : 'text-gray-900 font-medium'}">${notif.message || 'No message'}</p>
                        <p class="text-xs text-gray-400 mt-1">${date}</p>
                    </div>
                    <div class="flex items-center gap-2 ml-2">
                        ${!notif.is_read ? '<div class="w-2 h-2 bg-blue-500 rounded-full"></div>' : ''}
                        ${!notif.is_read ? `<button class="mark-read-btn text-xs text-green-600 hover:text-green-700 px-2 py-1 hover:bg-green-50 rounded transition" data-notif-id="${notif.id}" title="Mark as Read">
                            <i class="fas fa-check"></i>
                        </button>` : ''}
                        <button class="delete-notif-btn text-xs text-red-600 hover:text-red-700 px-2 py-1 hover:bg-red-50 rounded transition" data-notif-id="${notif.id}" title="Delete">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            // Click handler for notification item (navigate to ticket)
            notifItem.addEventListener('click', async (e) => {
                // Don't trigger if clicking on buttons
                if (e.target.closest('.mark-read-btn') || e.target.closest('.delete-notif-btn')) {
                    return;
                }
                
                // Mark as read if unread
                if (!notif.is_read) {
                    await markNotificationAsRead(notif.id);
                    notifItem.dataset.isRead = '1';
                    notifItem.classList.remove('bg-blue-50');
                    notifItem.classList.add('bg-gray-50');
                    const messageEl = notifItem.querySelector('p');
                    if (messageEl) {
                        messageEl.classList.remove('text-gray-900', 'font-medium');
                        messageEl.classList.add('text-gray-600');
                    }
                    const dot = notifItem.querySelector('.bg-blue-500');
                    if (dot) dot.remove();
                    const markReadBtn = notifItem.querySelector('.mark-read-btn');
                    if (markReadBtn) markReadBtn.remove();
                    
                    // Update badge
                    await loadNotifications();
                }
                
                // Close dropdown
                if (notifDropdown) {
                    notifDropdown.classList.add('hidden');
                }
                
                // Navigate to ticket if ticket number found in message
                const ticketNumber = extractTicketNumber(notif.message);
                if (ticketNumber) {
                    navigateToTicket(ticketNumber);
                } else if (notif.url) {
                    // Fallback to URL if available
                    window.location.href = notif.url;
                }
            });
            
            // Mark as read button handler
            const markReadBtn = notifItem.querySelector('.mark-read-btn');
            if (markReadBtn) {
                markReadBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    if (!notif.is_read) {
                        await markNotificationAsRead(notif.id);
                        notifItem.dataset.isRead = '1';
                        notifItem.classList.remove('bg-blue-50');
                        notifItem.classList.add('bg-gray-50');
                        const messageEl = notifItem.querySelector('p');
                        if (messageEl) {
                            messageEl.classList.remove('text-gray-900', 'font-medium');
                            messageEl.classList.add('text-gray-600');
                        }
                        const dot = notifItem.querySelector('.bg-blue-500');
                        if (dot) dot.remove();
                        markReadBtn.remove();
                        
                        // Update badge
                        await loadNotifications();
                        showNotification('Notification marked as read', 'success');
                    }
                });
            }
            
            // Delete notification button handler
            const deleteBtn = notifItem.querySelector('.delete-notif-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    if (confirm('Are you sure you want to delete this notification?')) {
                        const deleted = await deleteNotification(notif.id);
                        if (deleted) {
                            notifItem.remove();
                            await loadNotifications();
                            showNotification('Notification deleted', 'success');
                            
                            // Check if no notifications left
                            const remainingNotifs = notifList.querySelectorAll('[data-notif-id]');
                            if (remainingNotifs.length === 0) {
                                notifEmpty.classList.remove('hidden');
                            }
                        }
                    }
                });
            }
            
            notifList.appendChild(notifItem);
        });
    };
    
    // Mark notification as read
    const markNotificationAsRead = async (notificationId) => {
        try {
            const response = await fetch('api.php?action=mark_notification_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ notification_id: notificationId })
            });
            
            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error marking notification as read:', error);
            return false;
        }
    };
    
    // Mark all notifications as read
    const markAllNotificationsAsRead = async () => {
        try {
            const response = await fetch('api.php?action=mark_all_notifications_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            });
            
            const result = await response.json();
            if (result.success) {
                // Reload notifications to update UI
                await loadNotifications();
                // Update all notification items in the list
                if (notifList) {
                    const items = notifList.querySelectorAll('[data-notif-id]');
                    items.forEach(item => {
                        item.dataset.isRead = '1';
                        item.classList.remove('bg-blue-50');
                        item.classList.add('bg-gray-50');
                        const messageEl = item.querySelector('p');
                        if (messageEl) {
                            messageEl.classList.remove('text-gray-900', 'font-medium');
                            messageEl.classList.add('text-gray-600');
                        }
                        const dot = item.querySelector('.bg-blue-500');
                        if (dot) dot.remove();
                        const markReadBtn = item.querySelector('.mark-read-btn');
                        if (markReadBtn) markReadBtn.remove();
                    });
                }
                showNotification('All notifications marked as read', 'success');
            }
            return result.success;
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            showNotification('Error marking all notifications as read', 'error');
            return false;
        }
    };
    
    // Delete notification
    const deleteNotification = async (notificationId) => {
        try {
            const response = await fetch('api.php?action=delete_notification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ notification_id: notificationId })
            });
            
            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error deleting notification:', error);
            showNotification('Error deleting notification', 'error');
            return false;
        }
    };
    
    // Clear all read notifications
    const clearReadNotifications = async () => {
        try {
            const response = await fetch('api.php?action=clear_read_notifications', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            });
            
            const result = await response.json();
            if (result.success) {
                // Reload notifications to update UI
                await loadNotifications();
                showNotification('Read notifications cleared', 'success');
            }
            return result.success;
        } catch (error) {
            console.error('Error clearing read notifications:', error);
            showNotification('Error clearing read notifications', 'error');
            return false;
        }
    };
    
    // Extract ticket number from notification message
    const extractTicketNumber = (message) => {
        if (!message) return null;
        // Match patterns like #T00001 or T00001 or ticket #T00001
        const match = message.match(/#?T\d{5}/i);
        return match ? match[0].replace('#', '') : null;
    };
    
    // Navigate to ticket detail based on ticket number
    const navigateToTicket = (ticketNumber) => {
        if (!ticketNumber) return;
        
        // Check if user is admin or regular user
        const isAdmin = currentUser && currentUser.role === 'admin';
        
        if (isAdmin) {
            // For admin: Switch to All Tickets tab and filter/highlight the ticket
            switchToAllTicketsTab();
            // Wait a bit for tab to switch, then highlight ticket
            setTimeout(() => {
                highlightTicket(ticketNumber);
            }, 300);
        } else {
            // For user: Switch to My Tickets tab
            const ticketsTab = document.getElementById('user-tab-tickets');
            if (ticketsTab) {
                ticketsTab.click();
                // Wait a bit for tab to switch, then highlight ticket
                setTimeout(() => {
                    highlightTicket(ticketNumber);
                }, 300);
            }
        }
    };
    
    // Toggle notification dropdown
    if (notifBtn) {
        notifBtn.addEventListener('click', async (e) => {
            e.stopPropagation();
            if (notifDropdown) {
                if (notifDropdown.classList.contains('hidden')) {
                    // Calculate position for fixed dropdown
                    const rect = notifBtn.getBoundingClientRect();
                    notifDropdown.style.top = (rect.bottom + window.scrollY + 8) + 'px';
                    notifDropdown.style.right = (window.innerWidth - rect.right) + 'px';
                    
                    // Open dropdown and load notifications
                    notifDropdown.classList.remove('hidden');
                    if (window.currentNotifications) {
                        renderNotifications(window.currentNotifications);
                    } else {
                        await loadNotifications();
                        if (window.currentNotifications) {
                            renderNotifications(window.currentNotifications);
                        }
                    }
                } else {
                    notifDropdown.classList.add('hidden');
                }
            }
        });
    }
    
    // Close dropdown
    if (closeNotifDropdown) {
        closeNotifDropdown.addEventListener('click', () => {
            if (notifDropdown) {
                notifDropdown.classList.add('hidden');
            }
        });
    }
    
    // Mark all as read button
    const markAllReadBtn = document.getElementById('mark-all-read-btn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', async (e) => {
            e.stopPropagation();
            await markAllNotificationsAsRead();
        });
    }
    
    // Clear read notifications button
    const clearReadBtn = document.getElementById('clear-read-btn');
    if (clearReadBtn) {
        clearReadBtn.addEventListener('click', async (e) => {
            e.stopPropagation();
            if (confirm('Are you sure you want to delete all read notifications?')) {
                await clearReadNotifications();
            }
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (notifDropdown && !notifDropdown.contains(e.target) && !notifBtn.contains(e.target)) {
            notifDropdown.classList.add('hidden');
        }
    });
    
    // Load notifications on page load
    loadNotifications();
    
    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
    
    // Listen for messages from Panduan.html iframe
    window.addEventListener('message', (event) => {
        // Accept messages from same origin (security)
        if (event.data && event.data.action === 'closePanduan') {
            // Switch to Dashboard tab
            const dashboardTab = document.getElementById('user-tab-dashboard');
            if (dashboardTab) {
                dashboardTab.click();
            }
        }
    });

    // --- User View ---
    const initUserView = (user) => {
        views.userView.classList.remove('hidden');
        
        // Update welcome message
        document.getElementById('user-welcome-message').textContent = `Welcome back, ${user.name}!`;
        
        // Setup user navigation tabs
        setupUserTabs();
        
        // Initialize user dashboard
        renderUserDashboard();
        
        // Setup ticket form
        setupTicketForm(user);
        
        // Load user tickets
        renderUserTickets();
    };
    
    // Setup User Navigation Tabs
    const setupUserTabs = () => {
        const tabs = document.querySelectorAll('#user-view .nav-tab');
        const tabContents = document.querySelectorAll('.user-tab-content');
        
        // Map tab IDs to sidebar menu IDs
        const tabToMenuMap = {
            'user-tab-dashboard': 'user-menu-dashboard',
            'user-tab-create': 'user-menu-create',
            'user-tab-tickets': 'user-menu-tickets',
            'user-tab-panduan': 'user-menu-panduan'
        };
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                tab.classList.add('active');
                
                // Update sidebar menu active state
                const menuId = tabToMenuMap[tab.id];
                if (menuId) {
                    document.querySelectorAll('#user-sidebar-menu .sidebar-menu-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    const menuItem = document.getElementById(menuId);
                    if (menuItem) {
                        menuItem.classList.add('active');
                    }
                }
                
                // Hide all tab contents
                tabContents.forEach(c => c.classList.add('hidden'));
                
                // Show corresponding content
                const tabId = tab.id.replace('user-tab-', '');
                const contentId = `user-${tabId}-view`;
                const content = document.getElementById(contentId);
                if (content) {
                    content.classList.remove('hidden');
                }
                
                // Load data based on active tab
                if (tabId === 'dashboard') {
                    renderUserDashboard();
                } else if (tabId === 'tickets') {
                    renderUserTickets();
                } else if (tabId === 'panduan') {
                    // Reset panduan iframe to start from slide 1
                    const panduanIframe = document.getElementById('panduan-iframe');
                    if (panduanIframe) {
                        // Wait a bit for iframe to be ready, then send reset message
                        setTimeout(() => {
                            try {
                                panduanIframe.contentWindow.postMessage({ action: 'resetSlide' }, '*');
                            } catch (e) {
                                // If iframe is not ready or cross-origin, reload it
                                panduanIframe.src = panduanIframe.src;
                            }
                        }, 100);
                    }
                }
            });
        });
        
        // Setup view all tickets button
        const viewAllBtn = document.getElementById('user-view-all-tickets');
        if (viewAllBtn) {
            viewAllBtn.addEventListener('click', () => {
                const ticketsTab = document.getElementById('user-tab-tickets');
                if (ticketsTab) {
                    ticketsTab.click();
                }
            });
        }
        
        // Setup refresh button
        const refreshBtn = document.getElementById('refresh-tickets-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                renderUserTickets();
            });
        }
        
        // Setup status filter
        const statusFilter = document.getElementById('ticket-status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                filterUserTickets(e.target.value);
            });
        }
    };
    
    // Render User Dashboard
    const renderUserDashboard = async () => {
        try {
            const result = await apiCall('list_tickets');
            if (result.success) {
                const tickets = result.tickets;
                
                // Calculate stats
                const stats = {
                    total: tickets.length,
                    open: tickets.filter(t => t.status === 'Open').length,
                    progress: tickets.filter(t => t.status === 'In Progress').length,
                    done: tickets.filter(t => t.status === 'Done').length
                };
                
                // Update stats
                document.getElementById('user-ticket-count').textContent = stats.total;
                document.getElementById('user-stats-total').textContent = stats.total;
                document.getElementById('user-stats-open').textContent = stats.open;
                document.getElementById('user-stats-progress').textContent = stats.progress;
                document.getElementById('user-stats-done').textContent = stats.done;
                
                // Show recent tickets (last 5)
                const recentTickets = tickets.slice(0, 5);
                renderRecentTickets(recentTickets);
                
                // Setup click handlers for stats cards
                setupUserStatsCardClickHandlers();
            }
        } catch (error) {
            console.error('Error loading user dashboard:', error);
        }
    };
    
    // Function to switch to user tickets tab
    const switchToUserTicketsTab = () => {
        const ticketsTab = document.getElementById('user-tab-tickets');
        if (ticketsTab) {
            ticketsTab.click();
        }
    };
    
    // Setup click handlers for user dashboard stats cards (using event delegation)
    let userStatsClickHandlerSet = false;
    const setupUserStatsCardClickHandlers = () => {
        // Only set up once using event delegation
        if (userStatsClickHandlerSet) return;
        
        const dashboardView = document.getElementById('user-dashboard-view');
        if (!dashboardView) return;
        
        dashboardView.addEventListener('click', async (e) => {
            // Find the clicked stats card (check if clicked element or its parent has data-user-stat)
            let card = e.target.closest('[data-user-stat]');
            if (!card) return;
            
            const status = card.getAttribute('data-status');
            if (!status) return;
            
            // Switch to My Tickets tab
            switchToUserTicketsTab();
            
            // Wait for tab to switch and tickets to load
            await new Promise(resolve => setTimeout(resolve, 300));
            
            // Filter tickets by status
            if (status === 'all') {
                // Show all tickets
                filterUserTickets('');
            } else {
                // Filter by specific status
                filterUserTickets(status);
            }
            
            // Also update the status filter dropdown if it exists
            const statusFilter = document.getElementById('ticket-status-filter');
            if (statusFilter) {
                statusFilter.value = status === 'all' ? '' : status;
            }
        });
        
        userStatsClickHandlerSet = true;
    };
    
    // Render Recent Tickets
    const renderRecentTickets = (tickets) => {
        const container = document.getElementById('user-recent-tickets');
        
        if (tickets.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center py-4">No tickets found. Create your first ticket!</p>';
            return;
        }
        
        container.innerHTML = '';
        tickets.forEach(ticket => {
            const ticketEl = document.createElement('div');
            ticketEl.className = `ticket-card p-4 border border-gray-200 rounded-lg cursor-pointer ${getStatusBorderClass(ticket.status)}`;
            
            const createdDate = new Date(ticket.created_at).toLocaleDateString('en-US');
            
            ticketEl.innerHTML = `
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="font-bold text-gray-800">${ticket.ticket_number}</span>
                            <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(ticket.status)}">${ticket.status}</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-1">${ticket.description.substring(0, 80)}${ticket.description.length > 80 ? '...' : ''}</p>
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-tag mr-1"></i>${ticket.category} | 
                            <i class="fas fa-calendar mr-1"></i>${createdDate}
                        </p>
                    </div>
                    <div class="text-right ml-4">
                        <p class="text-xs text-gray-500">Assigned to</p>
                        <p class="text-sm font-medium">${ticket.assign_to.split('@')[0]}</p>
                    </div>
                </div>
            `;
            
            // Click to view details
            ticketEl.addEventListener('click', () => {
                document.getElementById('user-tab-tickets').click();
                // Highlight the ticket
                setTimeout(() => {
                    const ticketInList = document.querySelector(`[data-ticket-id="${ticket.id}"]`);
                    if (ticketInList) {
                        ticketInList.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        ticketInList.classList.add('ticket-highlight');
                        setTimeout(() => ticketInList.classList.remove('ticket-highlight'), 3000);
                    }
                }, 300);
            });
            
            container.appendChild(ticketEl);
        });
    };
    
    // Setup Ticket Form
    const setupTicketForm = (user) => {
        const ticketForm = document.getElementById('ticket-form');
        
        const today = new Date();
        const formattedDate = today.toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' });

        // Mapping kategori ke PIC (Person In Charge)
        const categoryPIC = {
            'Jaringan & Keamanan': 'Awan',
            'Aplikasi & Sistem Internal': 'Awan',
            'Perangkat Keras': 'Awan',
            'Perangkat Lunak Umum & Akun': 'Awan',
            'Pengajuan pembuatan atau perubahan aplikasi sistem': 'Awan'
        };

        const categoryPICEmail = {
            'Jaringan & Keamanan': 'admin@helpdesk.local',
            'Aplikasi & Sistem Internal': 'admin@helpdesk.local',
            'Perangkat Keras': 'admin@helpdesk.local',
            'Perangkat Lunak Umum & Akun': 'admin@helpdesk.local',
            'Pengajuan pembuatan atau perubahan aplikasi sistem': 'admin@helpdesk.local'
        };

        const categoryDescriptions = {
            '': 'Pilih kategori untuk melihat deskripsi...',
            'Jaringan & Keamanan': 'Untuk semua masalah terkait koneksi internet, Wi-Fi, VPN, firewall, dan keamanan siber (laporan email phishing, virus, dll).',
            'Aplikasi & Sistem Internal': 'Khusus untuk error (bug), permintaan fitur baru, atau masalah pada sistem yang dikembangkan secara internal (contoh: sistem helpdesk itu sendiri).',
            'Perangkat Keras': 'Masalah terkait perangkat fisik seperti laptop/PC mati, monitor, mouse, keyboard, dan printer.',
            'Perangkat Lunak Umum & Akun': 'Masalah pada software umum (MS Office, Browser, dll.), permintaan instalasi software, reset password, atau masalah login.',
            'Pengajuan pembuatan atau perubahan aplikasi sistem': 'Untuk permintaan pembuatan aplikasi/sistem baru atau perubahan pada aplikasi/sistem yang sudah ada. Formulir SDLC akan ditampilkan untuk kategori ini.'
        };

        const priorityDescriptions = {
            'Low': 'Fungsi Non-Esensial',
            'Medium': 'Sistem Pelaporan Program & Donor',
            'High': 'Komunikasi Internal & Eksternal (Email, Teams)',
            'Critical': 'Akses Data Program & Keuangan (NAS, M365)'
        };

        ticketForm.innerHTML = `
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <p class="mt-1 text-gray-800 font-semibold">${user.name}</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Department</label>
                <p class="mt-1 text-gray-800 font-semibold">${user.department}</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Creation Date</label>
                <p class="mt-1 text-gray-800 font-semibold">${formattedDate}</p>
            </div>
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Issue Category</label>
                <select id="category" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Jaringan & Keamanan">Jaringan & Keamanan (Network & Security)</option>
                    <option value="Aplikasi & Sistem Internal">Aplikasi & Sistem Internal (In-house Apps & Systems)</option>
                    <option value="Perangkat Keras">Perangkat Keras (Hardware)</option>
                    <option value="Perangkat Lunak Umum & Akun">Perangkat Lunak Umum & Akun (General Software & Accounts)</option>
                    <option value="Pengajuan pembuatan atau perubahan aplikasi sistem">Pengajuan pembuatan atau perubahan aplikasi sistem (SDLC Form)</option>
                </select>
            </div>
            <div id="category-description" class="mt-2 text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-lg p-3">
                <p class="mb-2">Pilih kategori untuk melihat deskripsi...</p>
                <div id="category-pic" class="hidden mt-2 pt-2 border-t border-gray-300">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Person In Charge (PIC)</p>
                    <p class="text-sm font-bold text-indigo-600 bg-indigo-50 px-3 py-2 rounded-md inline-block">
                        <i class="fas fa-user-circle mr-2"></i><span id="pic-name"></span>
                    </p>
                </div>
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1 mt-4">Issue Details</label>
                <textarea id="description" name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select id="priority" name="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                    <div id="priority-description" class="mt-2 text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-lg p-2">Sistem Pelaporan Program & Donor</div>
                </div>
                <div>
                    <label for="assignTo" class="block text-sm font-medium text-gray-700 mb-1">Assign to</label>
                    <select id="assignTo" name="assign_to" class="w-full px-4 py-2 border-2 border-indigo-300 rounded-lg bg-indigo-50 font-semibold text-indigo-700 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                        <option value="admin@helpdesk.local">Awan - Admin</option>
                    </select>
                    <p class="text-xs text-indigo-600 mt-1 font-medium">
                        <i class="fas fa-info-circle mr-1"></i>PIC akan otomatis ter-assign sesuai kategori yang dipilih
                    </p>
                </div>
            </div>
            
            <!-- File Upload Section -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Attachments (Optional)</label>
                <div class="space-y-4">
                    <!-- File Upload -->
                    <div>
                        <label for="file-upload" class="block text-sm text-gray-600 mb-1">Upload Files</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="file-upload" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs text-gray-500">Images, PDF, DOC, XLS, etc. (MAX 10MB)</p>
                                </div>
                                <input id="file-upload" type="file" class="hidden" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar" />
                            </label>
                        </div>
                        <div id="file-list" class="mt-2 space-y-1"></div>
                    </div>
                    
                    <!-- URL Attachment -->
                    <div>
                        <label for="attachment-url" class="block text-sm text-gray-600 mb-1">Or Add URL/Link</label>
                        <div class="flex gap-2">
                            <input type="url" id="attachment-url" placeholder="https://example.com/screenshot.png" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="button" id="add-url-btn" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                                <i class="fas fa-plus mr-1"></i> Add URL
                            </button>
                        </div>
                        <div id="url-list" class="mt-2 space-y-1"></div>
                    </div>
                </div>
            </div>
            
            <!-- SDLC Form Container -->
            <div id="sdlc-form-container" class="hidden mt-6 p-6 bg-blue-50 border-2 border-blue-200 rounded-lg">
                <h3 class="text-xl font-bold text-blue-700 mb-4">Formulir SDLC (Pengajuan Pembuatan/Perubahan Aplikasi Sistem)</h3>
                <div id="sdlc-form-content" class="space-y-4">
                    <!-- Form will be loaded here -->
                </div>
            </div>
            
            <div class="text-center mt-6"><button type="submit" class="w-full md:w-auto bg-indigo-600 text-white font-bold py-2.5 px-8 rounded-lg hover:bg-indigo-700 transition">Submit Ticket</button></div>`;
        
        const categorySelect = document.getElementById('category');
        const categoryDesc = document.getElementById('category-description');
        const prioritySelect = document.getElementById('priority');
        const priorityDesc = document.getElementById('priority-description');
        const assignToSelect = document.getElementById('assignTo');
        const categoryPICDiv = document.getElementById('category-pic');
        const picNameSpan = document.getElementById('pic-name');
        
        // SDLC Form Container
        const sdlcFormContainer = document.getElementById('sdlc-form-container');
        const sdlcFormContent = document.getElementById('sdlc-form-content');
        
        // Function to load SDLC form
        async function loadSDLCForm() {
            if (!sdlcFormContent) return;
            
            try {
                const response = await fetch('form_sdlc.html');
                const html = await response.text();
                
                // Extract form content from the HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const formElement = doc.querySelector('form#requestForm');
                
                if (formElement) {
                    // Clone the form and adapt it for our container
                    const formClone = formElement.cloneNode(true);
                    
                    // Remove the header and submit button (we'll handle submission separately)
                    const header = formClone.querySelector('header');
                    if (header) header.remove();
                    
                    const submitBtn = formClone.querySelector('button[type="submit"]');
                    if (submitBtn) submitBtn.remove();
                    
                    // Update form ID to avoid conflicts
                    formClone.id = 'sdlc-form';
                    
                    sdlcFormContent.innerHTML = '';
                    sdlcFormContent.appendChild(formClone);
                    
                    // Set today's date
                    const tanggalInput = formClone.querySelector('#tanggal');
                    if (tanggalInput) {
                        tanggalInput.value = new Date().toISOString().split('T')[0];
                    }
                    
                    // Initialize dynamic fields functionality
                    initializeSDLCForm();
                }
            } catch (error) {
                console.error('Error loading SDLC form:', error);
                sdlcFormContent.innerHTML = '<p class="text-red-600">Error loading SDLC form. Please refresh the page.</p>';
            }
        }
        
        // Initialize SDLC form dynamic fields
        function initializeSDLCForm() {
            // Add role field button
            const addRoleBtn = sdlcFormContent.querySelector('button[onclick="addRoleField()"]');
            if (addRoleBtn) {
                addRoleBtn.onclick = addModalSDLCRole;
            }
            
            // Add feature field button
            const addFeatureBtn = sdlcFormContent.querySelector('button[onclick="addFeatureField()"]');
            if (addFeatureBtn) {
                addFeatureBtn.onclick = addModalSDLCFeature;
            }
        }
        
        function addModalSDLCRole() {
            const container = sdlcFormContent.querySelector('#userRolesContainer');
            if (!container) return;
            
            const newRoleDiv = document.createElement('div');
            newRoleDiv.className = 'flex space-x-3';
            newRoleDiv.innerHTML = `
                <input type="text" name="userRole" class="input-text flex-1" placeholder="Peran Pengguna" required>
                <input type="text" name="userFunction" class="input-text flex-1" placeholder="Fungsi Utama dalam Sistem" required>
            `;
            container.appendChild(newRoleDiv);
        }
        
        function addModalSDLCFeature() {
            const container = sdlcFormContent.querySelector('#featureContainer');
            if (!container) return;
            
            const newFeatureInput = document.createElement('input');
            newFeatureInput.type = 'text';
            newFeatureInput.name = 'fitur[]';
            newFeatureInput.className = 'input-text';
            newFeatureInput.placeholder = 'Fitur baru: [Isi di sini]';
            newFeatureInput.required = true;
            container.appendChild(newFeatureInput);
        }
        
        if (categorySelect && categoryDesc) {
            categorySelect.addEventListener('change', (e) => {
                const selectedCategory = e.target.value;
                const description = categoryDescriptions[selectedCategory] || categoryDescriptions[''];
                
                // Update description
                const descText = categoryDesc.querySelector('p:first-child');
                if (descText) {
                    descText.textContent = description;
                } else {
                    categoryDesc.innerHTML = `<p class="mb-2">${description}</p>`;
                }
                
                // Show/hide SDLC form
                if (selectedCategory === 'Pengajuan pembuatan atau perubahan aplikasi sistem') {
                    if (sdlcFormContainer) {
                        sdlcFormContainer.classList.remove('hidden');
                        loadSDLCForm();
                    }
                } else {
                    if (sdlcFormContainer) {
                        sdlcFormContainer.classList.add('hidden');
                    }
                }
                
                // Show/hide PIC and auto-assign
                if (selectedCategory && categoryPIC[selectedCategory]) {
                    const picName = categoryPIC[selectedCategory];
                    const picEmail = categoryPICEmail[selectedCategory];
                    
                    // Show PIC highlight
                    if (categoryPICDiv) {
                        categoryPICDiv.classList.remove('hidden');
                        if (picNameSpan) {
                            picNameSpan.textContent = picName;
                        }
                    }
                    
                    // Auto-assign to PIC
                    if (assignToSelect && picEmail) {
                        assignToSelect.value = picEmail;
                        // Trigger change event to update UI
                        assignToSelect.dispatchEvent(new Event('change'));
                    }
                } else {
                    // Hide PIC if no category selected
                    if (categoryPICDiv) {
                        categoryPICDiv.classList.add('hidden');
                    }
                }
                
            });
        }

        if (prioritySelect && priorityDesc) {
            const updatePriorityDesc = () => {
                const key = prioritySelect.value || 'Medium';
                priorityDesc.textContent = priorityDescriptions[key] || '';
            };
            updatePriorityDesc();
            prioritySelect.addEventListener('change', updatePriorityDesc);
        }

        // File upload handling
        let selectedFiles = [];
        let selectedUrls = [];

        const fileUpload = document.getElementById('file-upload');
        const fileList = document.getElementById('file-list');
        const urlInput = document.getElementById('attachment-url');
        const urlList = document.getElementById('url-list');
        const addUrlBtn = document.getElementById('add-url-btn');

        // File upload event
        fileUpload.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            files.forEach(file => {
                if (file.size > 10 * 1024 * 1024) { // 10MB limit
                    alert(`File ${file.name} is too large. Maximum size is 10MB.`);
                    return;
                }
                selectedFiles.push(file);
                displayFile(file);
            });
        });

        // URL add event
        addUrlBtn.addEventListener('click', () => {
            const url = urlInput.value.trim();
            if (url && isValidUrl(url)) {
                selectedUrls.push(url);
                displayUrl(url);
                urlInput.value = '';
            } else {
                alert('Please enter a valid URL');
            }
        });

        // Enter key for URL input
        urlInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                addUrlBtn.click();
            }
        });

        // Display file in list
        const displayFile = (file) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'flex items-center justify-between p-2 bg-gray-100 rounded-lg text-sm';
            fileItem.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-file mr-2 text-gray-500"></i>
                    <span class="text-gray-700">${file.name}</span>
                    <span class="text-gray-500 ml-2">(${formatFileSize(file.size)})</span>
                </div>
                <button type="button" class="text-red-500 hover:text-red-700" onclick="removeFile('${file.name}')">
                    <i class="fas fa-times"></i>
                </button>
            `;
            fileList.appendChild(fileItem);
        };

        // Display URL in list
        const displayUrl = (url) => {
            const urlItem = document.createElement('div');
            urlItem.className = 'flex items-center justify-between p-2 bg-blue-100 rounded-lg text-sm';
            urlItem.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-link mr-2 text-blue-500"></i>
                    <a href="${url}" target="_blank" class="text-blue-600 hover:text-blue-800 truncate">${url}</a>
                </div>
                <button type="button" class="text-red-500 hover:text-red-700" onclick="removeUrl('${url}')">
                    <i class="fas fa-times"></i>
                </button>
            `;
            urlList.appendChild(urlItem);
        };

        // Remove file function
        window.removeFile = (fileName) => {
            selectedFiles = selectedFiles.filter(file => file.name !== fileName);
            const fileItems = fileList.querySelectorAll('div');
            fileItems.forEach(item => {
                if (item.textContent.includes(fileName)) {
                    item.remove();
                }
            });
        };

        // Remove URL function
        window.removeUrl = (url) => {
            selectedUrls = selectedUrls.filter(u => u !== url);
            const urlItems = urlList.querySelectorAll('div');
            urlItems.forEach(item => {
                if (item.textContent.includes(url)) {
                    item.remove();
                }
            });
        };

        // Utility functions
        const isValidUrl = (string) => {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        };

        const formatFileSize = (bytes) => {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        };


        ticketForm.onsubmit = async (e) => {
            e.preventDefault();
            console.log('=== TICKET FORM SUBMIT TRIGGERED ===');
            const formData = new FormData(ticketForm);
            
            // Debug logging
            console.log('Form submission data:');
            console.log('Category:', formData.get('category'));
            console.log('Description:', formData.get('description'));
            console.log('Priority:', formData.get('priority'));
            console.log('Assign to:', formData.get('assign_to'));
            
            if (!formData.get('category')) {
                console.error('Validation failed: Category is empty');
                showNotification('Silakan pilih kategori isu.', 'error');
                return;
            }
            
            if (!formData.get('description')) {
                console.error('Validation failed: Description is empty');
                showNotification('Silakan isi deskripsi masalah.', 'error');
                return;
            }
            
            if (!formData.get('assign_to')) {
                console.error('Validation failed: Assign to is empty');
                showNotification('Silakan pilih assignee.', 'error');
                return;
            }
            
            console.log('Basic validation passed');
            
            // Check if SDLC form is required
            const category = formData.get('category');
            const isSDLC = category === 'Pengajuan pembuatan atau perubahan aplikasi sistem';
            let sdlcData = null;
            
            if (isSDLC) {
                // Collect SDLC form data
                const sdlcForm = document.getElementById('sdlc-form');
                if (!sdlcForm) {
                    showNotification('Form SDLC tidak ditemukan. Silakan refresh halaman.', 'error');
                    return;
                }
                
                const sdlcFormData = new FormData(sdlcForm);
                
                // Validate required SDLC fields
                const requiredFields = ['judul', 'unit', 'nama', 'jabatan', 'email', 'tanggal', 'prioritas',
                    'latarBelakang', 'masalah', 'dampakWaktu', 'dampakTransparansi', 
                    'dampakBiaya', 'dampakAkuntabilitas', 'tujuan', 'manfaat1', 'manfaat2',
                    'pemohonTtd', 'manajerTtd'];
                
                const missingFields = [];
                requiredFields.forEach(field => {
                    if (field === 'prioritas') {
                        // Check radio buttons
                        const radios = sdlcForm.querySelectorAll(`input[name="${field}"]`);
                        const checked = Array.from(radios).find(r => r.checked);
                        if (!checked) missingFields.push(field);
                    } else {
                        const value = sdlcFormData.get(field);
                        if (!value || value.trim() === '') {
                            missingFields.push(field);
                        }
                    }
                });
                
                if (missingFields.length > 0) {
                    showNotification(`Form SDLC belum lengkap. Field yang harus diisi: ${missingFields.join(', ')}`, 'error');
                    return;
                }
                
                // Collect user roles
                const userRoles = [];
                const roleInputs = Array.from(sdlcForm.querySelectorAll('input[name="userRole"]'));
                const functionInputs = Array.from(sdlcForm.querySelectorAll('input[name="userFunction"]'));
                
                roleInputs.forEach((roleInput, index) => {
                    const role = roleInput.value.trim();
                    const func = functionInputs[index]?.value.trim();
                    if (role && func) {
                        userRoles.push({ role, function: func });
                    }
                });
                
                if (userRoles.length === 0) {
                    showNotification('Pengguna utama sistem harus diisi minimal 1', 'error');
                    return;
                }
                
                // Collect features
                const features = [];
                const featureInputs = Array.from(sdlcForm.querySelectorAll('input[name^="fitur"]'));
                featureInputs.forEach(input => {
                    const value = input.value.trim();
                    if (value) {
                        features.push(value);
                    }
                });
                
                if (features.length < 2) {
                    showNotification('Fitur kunci harus diisi minimal 2', 'error');
                    return;
                }
                
                // Get prioritas (radio button)
                const prioritasRadios = sdlcForm.querySelectorAll('input[name="prioritas"]');
                const checkedPrioritas = Array.from(prioritasRadios).find(r => r.checked);
                const prioritas = checkedPrioritas ? checkedPrioritas.value : 'Sedang';
                
                // Prepare SDLC data
                sdlcData = {
                    judul: sdlcFormData.get('judul'),
                    unit: sdlcFormData.get('unit'),
                    nama: sdlcFormData.get('nama'),
                    jabatan: sdlcFormData.get('jabatan'),
                    email: sdlcFormData.get('email'),
                    tanggal: sdlcFormData.get('tanggal'),
                    prioritas: prioritas,
                    latarBelakang: sdlcFormData.get('latarBelakang'),
                    masalah: sdlcFormData.get('masalah'),
                    dampakWaktu: sdlcFormData.get('dampakWaktu'),
                    dampakTransparansi: sdlcFormData.get('dampakTransparansi'),
                    dampakBiaya: sdlcFormData.get('dampakBiaya'),
                    dampakAkuntabilitas: sdlcFormData.get('dampakAkuntabilitas'),
                    tujuan: sdlcFormData.get('tujuan'),
                    manfaat1: sdlcFormData.get('manfaat1'),
                    manfaat2: sdlcFormData.get('manfaat2'),
                    manfaat3: sdlcFormData.get('manfaat3') || '',
                    userRoles: userRoles,
                    features: features,
                    pemohonTtd: sdlcFormData.get('pemohonTtd'),
                    manajerTtd: sdlcFormData.get('manajerTtd')
                };
            }

            // Create FormData for file upload
            const submitData = new FormData();
            submitData.append('category', formData.get('category'));
            submitData.append('description', formData.get('description'));
            submitData.append('priority', formData.get('priority'));
            submitData.append('assign_to', formData.get('assign_to'));
            
            // Add files - use 'files[]' format for multiple files
            selectedFiles.forEach((file) => {
                submitData.append('files[]', file);
            });
            
            // Add URLs - use array format
            selectedUrls.forEach((url) => {
                submitData.append('urls[]', url);
            });

            console.log('=== PREPARING TO SUBMIT TICKET ===');
            console.log('Submitting ticket with data:', {
                category: submitData.get('category'),
                description: submitData.get('description'),
                priority: submitData.get('priority'),
                assign_to: submitData.get('assign_to'),
                filesCount: selectedFiles.length,
                urlsCount: selectedUrls.length
            });
            
            let result;
            try {
                console.log('Calling apiCall with create_ticket...');
                result = await apiCall('create_ticket', 'POST', submitData, true);
                console.log('=== CREATE TICKET RESULT ===');
                console.log('Create ticket result:', result);
            } catch (error) {
                console.error('=== ERROR IN CREATE TICKET ===');
                console.error('Error details:', error);
                showNotification('Terjadi error saat membuat ticket: ' + error.message, 'error');
                return;
            }
            
            if (result.success) {
                // Save SDLC data if applicable
                if (isSDLC && sdlcData && result.ticket) {
                    try {
                        const saveResult = await apiCall('save_sdlc_data', 'POST', {
                            ticket_id: result.ticket.id,
                            ticket_number: result.ticket.ticket_number,
                            ...sdlcData
                        });
                        
                        if (!saveResult.success) {
                            console.error('Failed to save SDLC data:', saveResult.message);
                            showNotification('Ticket berhasil dibuat, namun data SDLC gagal disimpan: ' + saveResult.message, 'warning');
                        }
                    } catch (error) {
                        console.error('Error saving SDLC data:', error);
                        showNotification('Ticket berhasil dibuat, namun data SDLC gagal disimpan.', 'warning');
                    }
                }
                
                ticketForm.reset();
                const categoryDesc = document.getElementById('category-description');
                if (categoryDesc) {
                    categoryDesc.textContent = categoryDescriptions[''];
                }
                
                // Hide SDLC form if visible
                if (sdlcFormContainer) {
                    sdlcFormContainer.classList.add('hidden');
                }
                
                // Clear attachments
                selectedFiles = [];
                selectedUrls = [];
                if (fileList) fileList.innerHTML = '';
                if (urlList) urlList.innerHTML = '';
                
                // Show success notification
                showNotification(`Ticket ${result.ticket.ticket_number} created successfully!`, 'success');
                // Refresh notifications
                if (typeof loadNotifications === 'function') {
                    loadNotifications();
                }
                
                // Update dashboard and tickets
                renderUserTickets();
                renderUserDashboard();
                
                // Switch to tickets tab to show the new ticket
                setTimeout(() => {
                    const ticketsTab = document.getElementById('user-tab-tickets');
                    if (ticketsTab) {
                        ticketsTab.click();
                    }
                }, 1000);
            } else {
                showNotification(result.message, 'error');
            }
        };
        
        renderUserTickets();
    };

    const renderUserTickets = async () => {
        const container = document.getElementById('user-ticket-list');
        container.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i><p class="text-gray-500 mt-2">Loading tickets...</p></div>';
        
        try {
            const result = await apiCall('list_tickets');
            
            if (!result.success) {
                container.innerHTML = '<div class="text-center py-8 text-red-500"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><p>Error loading tickets</p></div>';
                return;
            }
            
            if (result.tickets.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-ticket-alt text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-600 mb-2">No tickets found</h3>
                        <p class="text-gray-500 mb-4">You haven't created any support tickets yet.</p>
                        <button id="create-first-ticket" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-plus mr-2"></i>Create Your First Ticket
                        </button>
                    </div>
                `;
                
                // Add event listener for create first ticket button
                const createFirstBtn = document.getElementById('create-first-ticket');
                if (createFirstBtn) {
                    createFirstBtn.addEventListener('click', () => {
                        const createTab = document.getElementById('user-tab-create');
                        if (createTab) {
                            createTab.click();
                        }
                    });
                }
                return;
            }
            
            container.innerHTML = '';
            result.tickets.forEach(ticket => {
                const ticketEl = document.createElement('div');
                ticketEl.className = `ticket-card border border-gray-200 rounded-xl ${getStatusBorderClass(ticket.status)}`;
                ticketEl.setAttribute('data-ticket-id', ticket.id);
                ticketEl.setAttribute('data-status', ticket.status);
                
                const createdDate = new Date(ticket.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                
                const canEdit = ticket.status === 'Open';
                
                ticketEl.innerHTML = `
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="font-bold text-lg text-gray-800">${ticket.ticket_number}</h3>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full ${getStatusClass(ticket.status)}">${ticket.status}</span>
                                    <span class="px-2 py-1 text-xs rounded-full ${getPriorityClass(ticket.priority)}">${ticket.priority}</span>
                                </div>
                                <p class="text-gray-600 mb-3 leading-relaxed">${ticket.description}</p>
                                
                                <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <i class="fas fa-tag mr-2 text-gray-400"></i>
                                        <span>${ticket.category}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-user mr-2 text-gray-400"></i>
                                        <span>Assigned to ${ticket.assign_to.split('@')[0]}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                        <span>Created ${createdDate}</span>
                                    </div>
                                    ${ticket.status === 'Done' && ticket.end_date ? `
                                        <div class="flex items-center text-green-600">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            <span>Completed ${new Date(ticket.end_date).toLocaleDateString('en-US')}</span>
                                        </div>
                                    ` : ''}
                                </div>
                                
                                ${ticket.admin_comment ? `
                                    <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg">
                                        <div class="flex items-start">
                                            <i class="fas fa-comment-dots text-blue-600 mr-3 mt-1"></i>
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-blue-900 mb-2">Komentar dari Admin IT:</h4>
                                                <p class="text-gray-700 whitespace-pre-wrap">${escapeHtml(ticket.admin_comment)}</p>
                                            </div>
                                        </div>
                                    </div>
                                ` : ''}
                                
                                <div id="attachments-${ticket.id}" class="mt-3"></div>
                            </div>
                            
                            <div class="flex flex-col gap-2 ml-6">
                                ${canEdit ? `
                                    <button class="edit-ticket-btn flex items-center gap-2 bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-600 transition" data-ticket-id="${ticket.id}">
                                        <i class="fas fa-edit"></i>
                                        <span>Edit</span>
                                    </button>
                                    <button class="delete-ticket-btn flex items-center gap-2 bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-600 transition" data-ticket-id="${ticket.id}" data-ticket-number="${ticket.ticket_number}">
                                        <i class="fas fa-trash"></i>
                                        <span>Delete</span>
                                    </button>
                                ` : `
                                    <div class="text-center">
                                        <div class="bg-gray-100 text-gray-500 px-4 py-2 rounded-lg text-sm">
                                            <i class="fas fa-lock mr-2"></i>
                                            Cannot edit
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1">Ticket in progress</p>
                                    </div>
                                `}
                            </div>
                        </div>
                    </div>
                `;
                
                container.appendChild(ticketEl);
                
                // Load attachments for this ticket
                loadAttachments(ticket.id);
            });
            
            // Add event listeners for edit and delete buttons
            addTicketActionListeners();
            
        } catch (error) {
            console.error('Error loading tickets:', error);
            container.innerHTML = '<div class="text-center py-8 text-red-500"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><p>Error loading tickets</p></div>';
        }
    };
    
    // Add event listeners for ticket actions
    const addTicketActionListeners = () => {
        document.querySelectorAll('.edit-ticket-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const ticketId = e.currentTarget.dataset.ticketId;
                showEditTicketModal(ticketId);
            });
        });
        
        document.querySelectorAll('.delete-ticket-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const ticketId = e.currentTarget.dataset.ticketId;
                const ticketNumber = e.currentTarget.dataset.ticketNumber;
                showConfirmModal(
                    'Delete Ticket',
                    `Are you sure you want to delete ticket <strong>${ticketNumber}</strong>?<br><br>This action cannot be undone and will remove all associated attachments.`,
                    () => deleteUserTicket(ticketId)
                );
            });
        });
        
    };
    
    // Filter user tickets by status
    const filterUserTickets = (status) => {
        const tickets = document.querySelectorAll('[data-ticket-id]');
        let visibleCount = 0;
        
        tickets.forEach(ticket => {
            const ticketStatus = ticket.dataset.status;
            if (!status || ticketStatus === status) {
                ticket.style.display = '';
                visibleCount++;
            } else {
                ticket.style.display = 'none';
            }
        });
        
        // Show message if no tickets match filter
        const container = document.getElementById('user-ticket-list');
        const existingMessage = container.querySelector('.filter-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        if (visibleCount === 0 && status) {
            const messageEl = document.createElement('div');
            messageEl.className = 'filter-message text-center py-8 text-gray-500';
            messageEl.innerHTML = `
                <i class="fas fa-filter text-2xl mb-2"></i>
                <p>No tickets found with status: <strong>${status}</strong></p>
                <button onclick="clearTicketFilter();" class="text-indigo-600 hover:text-indigo-800 text-sm mt-2">
                    Clear filter
                </button>
            `;
            container.appendChild(messageEl);
        }
    };
    
    // Clear ticket filter function
    window.clearTicketFilter = () => {
        const statusFilter = document.getElementById('ticket-status-filter');
        if (statusFilter) {
            statusFilter.value = '';
            filterUserTickets('');
        }
    };

// Function to load attachments for a ticket
async function loadAttachments(ticketId) {
    try {
        const result = await apiCall(`get_attachments&ticket_id=${ticketId}`);
        if (result.success && result.attachments.length > 0) {
            const attachmentContainer = document.getElementById(`attachments-${ticketId}`);
            if (attachmentContainer) {
                attachmentContainer.innerHTML = `
                    <div class="flex flex-wrap gap-2 mt-2">
                        ${result.attachments.map(attachment => {
                            if (attachment.attachment_type === 'file') {
                                return `
                                    <a href="${attachment.file_path}" target="_blank" class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded hover:bg-gray-200">
                                        <i class="fas fa-file mr-1"></i>
                                        ${attachment.file_name}
                                    </a>
                                `;
                            } else if (attachment.attachment_type === 'url') {
                                return `
                                    <a href="${attachment.url}" target="_blank" class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded hover:bg-blue-200">
                                        <i class="fas fa-link mr-1"></i>
                                        Link
                                    </a>
                                `;
                            }
                        }).join('')}
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Error loading attachments:', error);
    }
};
    
    // Show Edit Ticket Modal
    const showEditTicketModal = async (ticketId) => {
        try {
            const result = await apiCall(`get_ticket_details&ticket_id=${ticketId}`);
            if (!result.success) {
                showNotification(result.message, 'error');
                return;
            }
            
            const ticket = result.ticket;
            const title = `<i class="fas fa-edit mr-2"></i>Edit Ticket ${ticket.ticket_number}`;
            
            const categoryDescriptions = {
                'Network': 'Issues related to internet connection, Wi-Fi, local network (LAN), or server access.',
                'Software': 'Errors in applications (e.g., MS Office, browser), operating system (Windows, MacOS), or non-functioning software.',
                'Hardware': 'Problems with hardware devices like laptop/PC not turning on, printer not printing, monitor, mouse, or keyboard.',
                'System & Aplikasi': 'Issues related to internal company systems (e.g., HR system, sales applications) or requests for new access.'
            };
            
            const content = `
                <div class="space-y-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            <span class="text-sm text-blue-800">You can only edit tickets with "Open" status</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Issue Category</label>
                            <select name="category" id="edit-category" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="Network" ${ticket.category === 'Network' ? 'selected' : ''}>Network</option>
                                <option value="Software" ${ticket.category === 'Software' ? 'selected' : ''}>Software</option>
                                <option value="Hardware" ${ticket.category === 'Hardware' ? 'selected' : ''}>Hardware</option>
                                <option value="System & Aplikasi" ${ticket.category === 'System & Aplikasi' ? 'selected' : ''}>System & Aplikasi</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Priority Level</label>
                            <select name="user_priority" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="Low" ${ticket.priority === 'Low' ? 'selected' : ''}>Low - Can wait</option>
                                <option value="Medium" ${ticket.priority === 'Medium' ? 'selected' : ''}>Medium - Normal</option>
                                <option value="High" ${ticket.priority === 'High' ? 'selected' : ''}>High - Urgent</option>
                                <option value="Critical" ${ticket.priority === 'Critical' ? 'selected' : ''}>Critical - Emergency</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category Description</label>
                        <div id="edit-category-description" class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-600 min-h-[60px]">
                            ${categoryDescriptions[ticket.category] || 'Select a category to see description...'}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Issue Description</label>
                        <textarea name="description" rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required placeholder="Describe your issue in detail...">${ticket.description}</textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assign to IT Support</label>
                        <select name="assign_to" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="admin@helpdesk.local" ${ticket.assign_to === 'admin@helpdesk.local' ? 'selected' : ''}>
                                <i class="fas fa-user mr-2"></i>Awan - Admin
                            </option>
                        </select>
                    </div>
                </div>
            `;

            const onSave = async (formData) => {
                const modalError = document.getElementById('modal-error');
                
                const updateData = {
                    ticket_id: parseInt(ticketId),
                    category: formData.get('category'),
                    description: formData.get('description'),
                    user_priority: formData.get('user_priority'),
                    assign_to: formData.get('assign_to')
                };

                const result = await apiCall('update_user_ticket', 'POST', updateData);
                
                if (result.success) {
                    showNotification('Ticket updated successfully!', 'success');
                    // Refresh notifications
                    if (typeof loadNotifications === 'function') {
                        loadNotifications();
                    }
                    renderUserTickets();
                    renderUserDashboard(); // Update dashboard stats
                    return true;
                } else {
                    modalError.textContent = result.message;
                    modalError.classList.remove('hidden');
                    return false;
                }
            };

            showModal(title, content, onSave, 'Update Ticket');
            
            // Add category change listener for description update
            setTimeout(() => {
                const categorySelect = document.getElementById('edit-category');
                const descriptionDiv = document.getElementById('edit-category-description');
                
                if (categorySelect && descriptionDiv) {
                    categorySelect.addEventListener('change', (e) => {
                        descriptionDiv.textContent = categoryDescriptions[e.target.value] || 'Select a category to see description...';
                    });
                }
            }, 100);
            
        } catch (error) {
            console.error('Error loading ticket details:', error);
            showNotification('Error loading ticket details', 'error');
        }
    };
    
    // SDLC Form Modal removed - feature rolled back
    
    // Delete User Ticket
    const deleteUserTicket = async (ticketId) => {
        try {
            const result = await apiCall('delete_user_ticket', 'POST', { ticket_id: parseInt(ticketId) });
            
            if (result.success) {
                showNotification('Ticket deleted successfully', 'success');
                renderUserTickets();
                renderUserDashboard(); // Update dashboard stats
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Error deleting ticket:', error);
            showNotification('Error deleting ticket', 'error');
        }
    };
    
    // Show notification
    const showNotification = (message, type = 'info') => {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(n => n.remove());
        
        const notification = document.createElement('div');
        notification.className = `notification fixed top-4 right-4 z-[9999] p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;
        
        const bgColor = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'warning': 'bg-yellow-500',
            'info': 'bg-blue-500'
        }[type] || 'bg-blue-500';
        
        const icon = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        }[type] || 'fas fa-info-circle';
        
        notification.className += ` ${bgColor} text-white`;
        
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="${icon} mr-3"></i>
                <span class="flex-1">${message}</span>
                <button class="ml-3 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    };

    // --- Admin View ---
    const initAdminView = () => {
        views.adminView.classList.remove('hidden');
        
        const tabs = document.querySelectorAll('#admin-view .nav-tab');
        const tabContents = document.querySelectorAll('.admin-tab-content');
        
        // Map tab IDs to sidebar menu IDs
        const tabToMenuMap = {
            'tab-dashboard': 'admin-menu-dashboard',
            'tab-all-tickets': 'admin-menu-all-tickets',
            'tab-report': 'admin-menu-report',
            'tab-user-management': 'admin-menu-user-management'
        };
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // Update sidebar menu active state
                const menuId = tabToMenuMap[tab.id];
                if (menuId) {
                    document.querySelectorAll('#admin-sidebar-menu .sidebar-menu-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    const menuItem = document.getElementById(menuId);
                    if (menuItem) {
                        menuItem.classList.add('active');
                    }
                }
                
                tabContents.forEach(c => c.classList.add('hidden'));
                const viewId = `${tab.id.replace('tab-','admin-')}-view`;
                document.getElementById(viewId).classList.remove('hidden');
                
                // Jika tab All Tickets diklik, pastikan tabel di-render
                if (tab.id === 'tab-all-tickets') {
                    const tableContainer = document.getElementById('all-tickets-table');
                    if (!tableContainer || tableContainer.innerHTML.trim() === '' || 
                        tableContainer.innerHTML.includes('Loading') || 
                        tableContainer.innerHTML.includes('No tickets found')) {
                        renderAllTicketsTable();
                    }
                }
                
                // Jika tab Report diklik, load admin list
                if (tab.id === 'tab-report') {
                    loadAdminListForReport();
                }
            });
        });

        renderAdminDashboard();
        renderAllTicketsTable();
        renderUserManagementTable();
        loadAdminListForReport();
        
        // Initialize stats navigation after a short delay to ensure DOM is ready
        setTimeout(() => {
            initStatsNavigation();
        }, 300);
        
        document.getElementById('add-user-btn').onclick = () => showUserFormModal(null);
        
        // Export functionality
        document.getElementById('export-pdf-btn').onclick = () => exportData('pdf');
        document.getElementById('export-excel-btn').onclick = () => exportData('excel');
        document.getElementById('export-dashboard-pdf-btn').onclick = () => exportData('pdf');
        document.getElementById('export-dashboard-excel-btn').onclick = () => exportData('excel');
        
        // Report functionality
        document.getElementById('view-report-btn').onclick = () => viewReport();
        document.getElementById('export-report-pdf-btn').onclick = () => exportReport('pdf');
        document.getElementById('export-report-excel-btn').onclick = () => exportReport('excel');
        document.getElementById('close-report-modal-btn').onclick = () => closeReportModal();
        document.getElementById('print-report-btn').onclick = () => printReport();
    };
    
    // Function to switch to All Tickets tab
    const switchToAllTicketsTab = () => {
        const allTicketsTab = document.getElementById('tab-all-tickets');
        if (allTicketsTab) {
            allTicketsTab.click();
        }
    };
    
    // Function to highlight a specific ticket
    const highlightTicket = (ticketNumber) => {
        // Remove any existing highlights
        document.querySelectorAll('.ticket-highlight').forEach(el => {
            el.classList.remove('ticket-highlight');
        });
        
        // Try admin table first
        const adminRows = document.querySelectorAll('#all-tickets-table tr');
        let found = false;
        
        adminRows.forEach(row => {
            const ticketNumberCell = row.querySelector('td:first-child');
            if (ticketNumberCell && ticketNumberCell.textContent.trim() === ticketNumber) {
                row.classList.add('ticket-highlight');
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                found = true;
            }
        });
        
        // If not found in admin table, try user tickets table
        if (!found) {
            const userRows = document.querySelectorAll('#user-tickets-table tbody tr, #user-tickets-list .ticket-item');
            userRows.forEach(row => {
                const ticketNumberCell = row.querySelector('[data-ticket-number], .ticket-number, td:first-child');
                if (ticketNumberCell) {
                    const cellText = ticketNumberCell.textContent.trim() || ticketNumberCell.getAttribute('data-ticket-number');
                    if (cellText === ticketNumber) {
                        row.classList.add('ticket-highlight');
                        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        found = true;
                    }
                }
            });
        }
    };
    
    // Function to filter tickets by status
    const filterTicketsByStatus = async (status) => {
        // Pastikan tab All Tickets sudah terbuka
        switchToAllTicketsTab();
        
        // Tunggu sebentar untuk memastikan tab sudah terbuka
        await new Promise(resolve => setTimeout(resolve, 300));
        
        // Render ulang tabel dengan semua ticket (tidak di-filter oleh assign_to)
        // karena filter dari chart harus menunjukkan semua ticket dengan status tersebut
        const container = document.getElementById('all-tickets-table');
        container.innerHTML = '<tr><td colspan="17" class="text-center p-4 text-gray-500">Loading...</td></tr>';
        
        const result = await apiCall('list_tickets');
        
        if (!result.success || result.tickets.length === 0) {
            container.innerHTML = '<tr><td colspan="17" class="text-center p-4 text-gray-500">No tickets found.</td></tr>';
            alert(`No tickets found with status: ${status}`);
            return;
        }
        
        // Filter tickets berdasarkan status yang dipilih (TANPA filter assign_to)
        const filteredTickets = (result.tickets || []).filter(t => t.status === status);
        
        if (filteredTickets.length === 0) {
            container.innerHTML = '<tr><td colspan="17" class="text-center p-4 text-gray-500">No tickets found with status: ' + status + '</td></tr>';
            alert(`No tickets found with status: ${status}`);
            return;
        }
        
        // Render tabel dengan ticket yang sudah difilter
        container.innerHTML = '';
        
        // Helper functions (sama seperti di renderAllTicketsTable)
        // Mapping kategori database ke tampilan user
        const categoryDisplayMapping = {
            'Network': 'Jaringan & Keamanan (Network & Security)',
            'Software': 'Perangkat Lunak Umum & Akun (General Software & Accounts)',
            'Hardware': 'Perangkat Keras (Hardware)',
            'System & Aplikasi': 'Aplikasi & Sistem Internal (In-house Apps & Systems)',
            'System & Applications': 'Aplikasi & Sistem Internal (In-house Apps & Systems)',
            'System & Application': 'Aplikasi & Sistem Internal (In-house Apps & Systems)',
            // Support kategori baru juga
            'Jaringan & Keamanan': 'Jaringan & Keamanan (Network & Security)',
            'Aplikasi & Sistem Internal': 'Aplikasi & Sistem Internal (In-house Apps & Systems)',
            'Perangkat Keras': 'Perangkat Keras (Hardware)',
            'Perangkat Lunak Umum & Akun': 'Perangkat Lunak Umum & Akun (General Software & Accounts)'
        };

        const formatCategory = (category) => {
            if (!category) return '-';
            return categoryDisplayMapping[category] || category;
        };

        const mapUserResponseTime = (priority) => {
            switch ((priority || '').toLowerCase()) {
                case 'low': return '4 Jam';
                case 'medium': return '2 Jam';
                case 'high': return '30 Menit';
                case 'critical': return '15 Menit';
                default: return '-';
            }
        };

        const mapItResolutionTime = (slaPriority) => {
            switch ((slaPriority || '').toLowerCase()) {
                case 'low': return '120 Jam';
                case 'medium': return '72 Jam';
                case 'high': return '8 Jam';
                case 'critical': return '4 Jam';
                default: return '-';
            }
        };

        const computeDueDate = (startDate, slaPriority) => {
            if (!startDate || !slaPriority) return '-';
            const hours = {
                'low': 120,
                'medium': 72,
                'high': 8,
                'critical': 4
            }[(slaPriority || '').toLowerCase()];
            if (!hours) return '-';
            const d = new Date(startDate);
            if (Number.isNaN(d.getTime())) return '-';
            d.setHours(d.getHours() + hours);
            return d.toISOString().slice(0,10);
        };

        const formatResponse = (minutes) => {
            if (!minutes && minutes !== 0) return '-';
            const h = Math.floor(minutes / 60);
            const m = minutes % 60;
            if (h > 0) return `${h} Jam ${m} Menit`;
            return `${m} Menit`;
        };

        // Render setiap ticket
        filteredTickets.forEach(ticket => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';

            const isDone = ticket.status === 'Done';
            // Pastikan perbandingan email case-insensitive dan trim whitespace
            const assignToNormalized = (ticket.assign_to || '').trim().toLowerCase();
            const currentUserEmailNormalized = (currentUser.email || '').trim().toLowerCase();
            const isAssignedToMe = assignToNormalized === currentUserEmailNormalized;
            const canEdit = isAssignedToMe;
            // Komentar admin selalu bisa diisi oleh admin yang di-assign, terlepas dari status ticket
            const canAddComment = isAssignedToMe && currentUser.email && ticket.assign_to;

            row.innerHTML = `
                <td class="px-4 py-3 font-medium">${ticket.ticket_number}</td>
                <td class="px-4 py-3">${ticket.reporter_name}</td>
                <td class="px-4 py-3">${formatCategory(ticket.category)}</td>
                <td class="px-4 py-3 align-top" style="min-width: 350px; max-width: 600px; word-wrap: break-word; white-space: normal; overflow: visible;">
                    <div style="word-wrap: break-word; overflow-wrap: break-word; white-space: pre-wrap;">${ticket.description || ''}</div>
                </td>
                <td class="px-4 py-3">${ticket.priority || '-'}</td>
                <td class="px-4 py-3">${mapUserResponseTime(ticket.priority)}</td>
                <td class="px-4 py-3">${ticket.assign_to.split('@')[0]}</td>
                <td class="px-4 py-3">
                    <select class="admin-assign-to border border-gray-300 rounded-md p-1 text-sm bg-blue-50 hover:bg-blue-100 cursor-pointer" 
                            data-id="${ticket.id}" data-current="${ticket.assign_to}" title="Reassign task to another admin">
                        <option value="admin@helpdesk.local" ${ticket.assign_to === 'admin@helpdesk.local' ? 'selected' : ''}>Awan</option>
                    </select>
                </td>
                <td class="px-4 py-3">
                    <div id="admin-attachments-${ticket.id}" class="flex flex-wrap gap-1"></div>
                </td>
                <td class="px-4 py-3">
                    <select class="admin-sla-priority border border-gray-300 rounded-md p-1 text-sm ${!canEdit || isDone ? 'bg-gray-100 cursor-not-allowed' : ''}" 
                            data-id="${ticket.id}" ${!canEdit || isDone ? 'disabled' : ''}>
                        <option value="Low" ${ticket.sla_priority === 'Low' ? 'selected' : ''}>Low</option>
                        <option value="Medium" ${ticket.sla_priority === 'Medium' ? 'selected' : ''}>Medium</option>
                        <option value="High" ${ticket.sla_priority === 'High' ? 'selected' : ''}>High</option>
                        <option value="Critical" ${ticket.sla_priority === 'Critical' ? 'selected' : ''}>Critical</option>
                    </select>
                </td>
                <td class="px-4 py-3">${mapItResolutionTime(ticket.sla_priority)}</td>
                <td class="px-4 py-3">${ticket.due_date || computeDueDate(ticket.start_date, ticket.sla_priority)}</td>
                <td class="px-4 py-3">${formatResponse(ticket.response_minutes)}</td>
                <td class="px-4 py-3">
                    <input type="date" class="admin-start-date border border-gray-300 rounded-md p-1 text-sm ${!canEdit || isDone ? 'bg-gray-100 cursor-not-allowed' : ''}" 
                           value="${ticket.start_date || ''}" data-id="${ticket.id}" ${!canEdit || isDone ? 'disabled' : ''}>
                </td>
                <td class="px-4 py-3">
                    <input type="date" class="admin-end-date border border-gray-300 rounded-md p-1 text-sm ${!canEdit || !isDone ? 'bg-gray-100 cursor-not-allowed' : ''}" 
                           value="${ticket.end_date || ''}" data-id="${ticket.id}" ${!canEdit || !isDone ? 'disabled' : ''}>
                </td>
                <td class="px-4 py-3">
                    <select class="admin-status-select border border-gray-300 rounded-md p-1 text-sm ${!canEdit ? 'bg-gray-100 cursor-not-allowed' : ''}" 
                            data-id="${ticket.id}" ${!canEdit ? 'disabled' : ''}>
                        <option value="Open" ${ticket.status === 'Open' ? 'selected' : ''}>Open</option>
                        <option value="In Progress" ${ticket.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                        <option value="Delayed" ${ticket.status === 'Delayed' ? 'selected' : ''}>Delayed</option>
                        <option value="Done" ${ticket.status === 'Done' ? 'selected' : ''}>Done</option>
                        <option value="Revisi" ${ticket.status === 'Revisi' ? 'selected' : ''}>Revisi</option>
                    </select>
                </td>
                <td class="px-4 py-3" style="min-width: 200px; max-width: 300px;">
                    <textarea class="admin-comment border border-gray-300 rounded-md p-2 text-sm w-full ${!canAddComment ? 'bg-gray-100 cursor-not-allowed' : ''}" 
                              rows="2" 
                              placeholder="${canAddComment ? 'Tambahkan komentar untuk user...' : 'Hanya admin yang di-assign yang dapat menambahkan komentar'}" 
                              data-id="${ticket.id}"
                              ${!canAddComment ? 'disabled' : ''}
                              style="resize: vertical; min-height: 50px;">${ticket.admin_comment || ''}</textarea>
                </td>
                <td class="px-4 py-3">
                    <div class="flex gap-2 flex-col">
                        <div class="flex gap-2">
                            <button class="admin-edit-ticket bg-blue-500 text-white px-2 py-1 rounded text-xs" data-id="${ticket.id}">Edit</button>
                            <button class="admin-delete-ticket bg-red-500 text-white px-2 py-1 rounded text-xs" data-id="${ticket.id}" data-number="${ticket.ticket_number}">Delete</button>
                        </div>
                        ${ticket.category === 'Pengajuan pembuatan atau perubahan aplikasi sistem' ? 
                            `<button class="admin-download-sdlc bg-green-500 text-white px-2 py-1 rounded text-xs mt-1 w-full" data-id="${ticket.id}" data-number="${ticket.ticket_number}" title="Download SDLC Form">
                                <i class="fas fa-download mr-1"></i>Download SDLC
                            </button>` : ''}
                    </div>
                </td>
            `;
            
            container.appendChild(row);
            
            // Load attachments for this ticket
            loadAdminAttachments(ticket.id);
        });
        
        // Scroll ke atas tabel
        if (filteredTickets.length > 0) {
            const firstRow = container.querySelector('tr');
            if (firstRow) {
                firstRow.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
        
        // Add a filter indicator
        const tableContainer = document.querySelector('#admin-all-tickets-view .table-container');
        const headerSection = tableContainer.querySelector('.sticky.top-0');
        let filterIndicator = document.getElementById('filter-indicator');
        if (!filterIndicator) {
            filterIndicator = document.createElement('div');
            filterIndicator.id = 'filter-indicator';
            filterIndicator.className = 'p-4 bg-blue-100 border-b border-blue-300';
            // Insert setelah header section (sticky header)
            if (headerSection && headerSection.nextSibling) {
                tableContainer.insertBefore(filterIndicator, headerSection.nextSibling);
            } else {
                // Fallback: insert di awal table container div
                const tableScrollContainer = tableContainer.querySelector('div[style*="max-height"]');
                if (tableScrollContainer && tableScrollContainer.parentNode) {
                    tableScrollContainer.parentNode.insertBefore(filterIndicator, tableScrollContainer);
                } else {
                    tableContainer.insertBefore(filterIndicator, tableContainer.firstChild);
                }
            }
        }
        filterIndicator.innerHTML = `
            <div class="flex justify-between items-center">
                <span class="text-blue-800">Showing all tickets with status: <strong>${status}</strong> (${filteredTickets.length} ticket${filteredTickets.length > 1 ? 's' : ''})</span>
                <button onclick="clearStatusFilter()" class="text-blue-600 hover:text-blue-800 underline">Clear Filter</button>
            </div>
        `;
    };
    
    // Function to clear status filter
    window.clearStatusFilter = async () => {
        // Hapus filter indicator
        const filterIndicator = document.getElementById('filter-indicator');
        if (filterIndicator) {
            filterIndicator.remove();
        }
        
        // Render ulang tabel dengan filter default (hanya ticket assigned ke user)
        await renderAllTicketsTable();
    };
    
    // Export functionality
    const exportData = async (format) => {
        try {
            console.log(`Exporting data as ${format}...`);
            
            const response = await fetch(`export.php?action=export_${format}`, {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            const result = await response.json();
            
            if (result.success) {
                if (format === 'pdf') {
                    // Open PDF in new window for printing
                    const newWindow = window.open('', '_blank');
                    newWindow.document.write(result.html);
                    newWindow.document.close();
                    newWindow.print();
                } else if (format === 'excel') {
                    // Download CSV file
                    const blob = new Blob([result.csv], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    const url = URL.createObjectURL(blob);
                    link.setAttribute('href', url);
                    link.setAttribute('download', result.filename);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
                
                console.log(`Export ${format} completed successfully`);
            } else {
                alert(`Export failed: ${result.message}`);
            }
        } catch (error) {
            console.error('Export error:', error);
            alert('Export failed. Please try again.');
        }
    };
    
    // Admin: Dashboard Tab
    // Stats cards horizontal navigation
    let statsNavigationInitialized = false;
    const initStatsNavigation = () => {
        const statsContainer = document.getElementById('stats-container');
        const statsSlider = document.getElementById('stats-slider');
        const prevBtn = document.getElementById('stats-prev');
        const nextBtn = document.getElementById('stats-next');
        
        if (!statsContainer || !statsSlider || !prevBtn || !nextBtn) return;
        
        // Prevent multiple initializations
        if (statsNavigationInitialized) {
            // Just update the navigation state
            const cardWidth = 200;
            const gap = 24;
            const cardWidthWithGap = cardWidth + gap;
            const visibleCards = Math.floor(statsContainer.offsetWidth / cardWidthWithGap);
            const totalCards = statsSlider.children.length;
            const maxIndex = Math.max(0, totalCards - visibleCards);
            return;
        }
        
        statsNavigationInitialized = true;
        
        let currentIndex = 0;
        const cardWidth = 200; // min-width dari card
        const gap = 24; // gap-6 = 1.5rem = 24px
        const cardWidthWithGap = cardWidth + gap;
        const visibleCards = Math.floor(statsContainer.offsetWidth / cardWidthWithGap);
        const totalCards = statsSlider.children.length;
        let maxIndex = Math.max(0, totalCards - visibleCards);
        
        const updateButtons = () => {
            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex >= maxIndex;
            
            if (prevBtn.disabled) {
                prevBtn.style.opacity = '0.5';
                prevBtn.style.cursor = 'not-allowed';
            } else {
                prevBtn.style.opacity = '1';
                prevBtn.style.cursor = 'pointer';
            }
            
            if (nextBtn.disabled) {
                nextBtn.style.opacity = '0.5';
                nextBtn.style.cursor = 'not-allowed';
            } else {
                nextBtn.style.opacity = '1';
                nextBtn.style.cursor = 'pointer';
            }
        };
        
        const scrollToIndex = (index) => {
            currentIndex = Math.max(0, Math.min(index, maxIndex));
            const translateX = -currentIndex * cardWidthWithGap;
            statsSlider.style.transform = `translateX(${translateX}px)`;
            updateButtons();
        };
        
        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                scrollToIndex(currentIndex - 1);
            }
        });
        
        nextBtn.addEventListener('click', () => {
            if (currentIndex < maxIndex) {
                scrollToIndex(currentIndex + 1);
            }
        });
        
        // Update on window resize
        let resizeTimeout;
        const handleResize = () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                const newVisibleCards = Math.floor(statsContainer.offsetWidth / cardWidthWithGap);
                const newMaxIndex = Math.max(0, totalCards - newVisibleCards);
                if (currentIndex > newMaxIndex) {
                    currentIndex = newMaxIndex;
                }
                maxIndex = newMaxIndex;
                scrollToIndex(currentIndex);
            }, 250);
        };
        
        // Remove existing resize listener if any
        window.removeEventListener('resize', handleResize);
        window.addEventListener('resize', handleResize);
        
        // Initial state
        updateButtons();
    };
    
    const renderAdminDashboard = async () => {
        const result = await apiCall('ticket_stats');
        
        if (!result.success) {
            console.error('Failed to load stats');
            return;
        }
        
        const stats = result.stats;
        
        document.getElementById('stats-total').textContent = stats.total || 0;
        document.getElementById('stats-open').textContent = stats.status['Open'] || 0;
        document.getElementById('stats-progress').textContent = stats.status['In Progress'] || 0;
        document.getElementById('stats-delayed').textContent = stats.status['Delayed'] || 0;
        document.getElementById('stats-done').textContent = stats.status['Done'] || 0;
        document.getElementById('stats-revisi').textContent = stats.status['Revisi'] || 0;
        
        // Initialize stats navigation after rendering
        setTimeout(() => {
            initStatsNavigation();
        }, 100);

        // Status Chart (Doughnut)
        const chartCtx = document.getElementById('status-chart').getContext('2d');
        if(statusChart) statusChart.destroy();
        statusChart = new Chart(chartCtx, {
            type: 'doughnut',
            data: {
                labels: ['Open', 'In Progress', 'Done', 'Delayed', 'Revisi'],
                datasets: [{
                    data: [
                        stats.status['Open'] || 0,
                        stats.status['In Progress'] || 0,
                        stats.status['Done'] || 0,
                        stats.status['Delayed'] || 0,
                        stats.status['Revisi'] || 0
                    ],
                    backgroundColor: ['#6b7280', '#6366f1', '#22c55e', '#f59e0b', '#f97316'],
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                onClick: async (event, elements) => {
                    if (elements.length > 0) {
                        const statusIndex = elements[0].index;
                        const statuses = ['Open', 'In Progress', 'Done', 'Delayed', 'Revisi'];
                        const selectedStatus = statuses[statusIndex];
                        console.log('Clicked on status:', selectedStatus);
                        
                        // Filter tickets by status (fungsi ini akan switch ke tab All Tickets dan render tabel)
                        await filterTicketsByStatus(selectedStatus);
                    }
                }
            }
        });

        // Category Chart (Bar)
        const categories = stats.categories || {};
        const categoryCtx = document.getElementById('category-chart').getContext('2d');
        if(categoryChart) categoryChart.destroy();
        categoryChart = new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: ['Jaringan & Keamanan', 'Perangkat Lunak Umum & Akun', 'Perangkat Keras', 'Aplikasi & Sistem Internal'],
                datasets: [{
                    label: 'Tickets by Category',
                    data: [
                        (categories['Network'] || 0),
                        (categories['Software'] || 0),
                        (categories['Hardware'] || 0),
                        (categories['System & Aplikasi'] || categories['System & Applications'] || categories['System & Application'] || 0)
                    ],
                    backgroundColor: ['#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
        
        // Render Recent Activity
        const activityList = document.getElementById('recent-activity-list');
        activityList.innerHTML = '';
        
        if (!stats.recent_activity || stats.recent_activity.length === 0) {
            activityList.innerHTML = '<p class="text-gray-500 text-sm">No recent activity.</p>';
        } else {
            stats.recent_activity.forEach(activity => {
                const date = new Date(activity.created_at).toLocaleString('en-US');
                activityList.innerHTML += `
                    <div class="text-sm p-2 rounded-md bg-gray-50 border cursor-pointer hover:bg-gray-100 transition-colors recent-activity-item" data-ticket-number="${activity.ticket_number}">
                        <span class="font-semibold">${activity.reporter_name}</span> created ticket
                        <span class="font-semibold text-indigo-600">${activity.ticket_number}</span>.
                        <span class="block text-xs text-gray-500">${date}</span>
                    </div>
                `;
            });
            
            // Add click event listeners to recent activity items
            document.querySelectorAll('.recent-activity-item').forEach(item => {
                item.addEventListener('click', () => {
                    const ticketNumber = item.dataset.ticketNumber;
                    console.log('Clicked on ticket:', ticketNumber);
                    // Switch to All Tickets tab and highlight the ticket
                    switchToAllTicketsTab();
                    highlightTicket(ticketNumber);
                });
            });
        }
        
        // Setup click handlers for admin dashboard stats cards
        setupAdminStatsCardClickHandlers();
    };
    
    // Setup click handlers for admin dashboard stats cards (using event delegation)
    let adminStatsClickHandlerSet = false;
    const setupAdminStatsCardClickHandlers = () => {
        // Only set up once using event delegation
        if (adminStatsClickHandlerSet) return;
        
        const statsContainer = document.getElementById('stats-container');
        if (!statsContainer) return;
        
        statsContainer.addEventListener('click', async (e) => {
            // Find the clicked stats card
            let card = e.target.closest('[data-status]');
            if (!card) return;
            
            const status = card.getAttribute('data-status');
            if (!status) return;
            
            // Filter tickets by status
            if (status === 'all') {
                // Switch to All Tickets tab and show all tickets
                switchToAllTicketsTab();
                await new Promise(resolve => setTimeout(resolve, 300));
                renderAllTicketsTable();
            } else {
                // Filter by specific status
                await filterTicketsByStatus(status);
            }
        });
        
        adminStatsClickHandlerSet = true;
    };

    // Admin: All Tickets Tab
    const renderAllTicketsTable = async () => {
        const container = document.getElementById('all-tickets-table');
        container.innerHTML = '<tr><td colspan="13" class="text-center p-4 text-gray-500">Loading...</td></tr>';
        
        const result = await apiCall('list_tickets');
        
        if (!result.success || result.tickets.length === 0) {
            container.innerHTML = '<tr><td colspan="13" class="text-center p-4 text-gray-500">No tickets found.</td></tr>';
            return;
        }
        
        container.innerHTML = '';

        // Mapping kategori database ke tampilan user
        const categoryDisplayMapping = {
            'Network': 'Jaringan & Keamanan (Network & Security)',
            'Software': 'Perangkat Lunak Umum & Akun (General Software & Accounts)',
            'Hardware': 'Perangkat Keras (Hardware)',
            'System & Aplikasi': 'Aplikasi & Sistem Internal (In-house Apps & Systems)',
            'System & Applications': 'Aplikasi & Sistem Internal (In-house Apps & Systems)',
            'System & Application': 'Aplikasi & Sistem Internal (In-house Apps & Systems)',
            // Support kategori baru juga
            'Jaringan & Keamanan': 'Jaringan & Keamanan (Network & Security)',
            'Aplikasi & Sistem Internal': 'Aplikasi & Sistem Internal (In-house Apps & Systems)',
            'Perangkat Keras': 'Perangkat Keras (Hardware)',
            'Perangkat Lunak Umum & Akun': 'Perangkat Lunak Umum & Akun (General Software & Accounts)'
        };

        const formatCategory = (category) => {
            if (!category) return '-';
            return categoryDisplayMapping[category] || category;
        };

        const mapUserResponseTime = (priority) => {
            switch ((priority || '').toLowerCase()) {
                case 'low': return '4 Jam';
                case 'medium': return '2 Jam';
                case 'high': return '30 Menit';
                case 'critical': return '15 Menit';
                default: return '-';
            }
        };

        const mapItResolutionTime = (slaPriority) => {
            switch ((slaPriority || '').toLowerCase()) {
                case 'low': return '120 Jam';
                case 'medium': return '72 Jam';
                case 'high': return '8 Jam';
                case 'critical': return '4 Jam';
                default: return '-';
            }
        };

        // Clean admin view: show only tickets assigned to current admin by default
        const tickets = (result.tickets || []).filter(t => t.assign_to === currentUser.email);

        const computeDueDate = (startDate, slaPriority) => {
            if (!startDate || !slaPriority) return '-';
            const hours = {
                'low': 120,
                'medium': 72,
                'high': 8,
                'critical': 4
            }[(slaPriority || '').toLowerCase()];
            if (!hours) return '-';
            const d = new Date(startDate);
            if (Number.isNaN(d.getTime())) return '-';
            d.setHours(d.getHours() + hours);
            return d.toISOString().slice(0,10);
        };

        const formatResponse = (minutes) => {
            if (!minutes && minutes !== 0) return '-';
            const h = Math.floor(minutes / 60);
            const m = minutes % 60;
            if (h > 0) return `${h} Jam ${m} Menit`;
            return `${m} Menit`;
        };

        tickets.forEach(ticket => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';

            const isDone = ticket.status === 'Done';
            // Pastikan perbandingan email case-insensitive dan trim whitespace
            const assignToNormalized = (ticket.assign_to || '').trim().toLowerCase();
            const currentUserEmailNormalized = (currentUser.email || '').trim().toLowerCase();
            const isAssignedToMe = assignToNormalized === currentUserEmailNormalized;
            const canEdit = isAssignedToMe;
            // Komentar admin selalu bisa diisi oleh admin yang di-assign, terlepas dari status ticket
            const canAddComment = isAssignedToMe && currentUser.email && ticket.assign_to;

            row.innerHTML = `
                <td class="px-4 py-3 font-medium">${ticket.ticket_number}</td>
                <td class="px-4 py-3">${ticket.reporter_name}</td>
                <td class="px-4 py-3">${formatCategory(ticket.category)}</td>
                <td class="px-4 py-3 align-top" style="min-width: 350px; max-width: 600px; word-wrap: break-word; white-space: normal; overflow: visible;">
                    <div style="word-wrap: break-word; overflow-wrap: break-word; white-space: pre-wrap;">${ticket.description || ''}</div>
                </td>
                <td class="px-4 py-3">${ticket.priority || '-'}</td>
                <td class="px-4 py-3">${mapUserResponseTime(ticket.priority)}</td>
                <td class="px-4 py-3">${ticket.assign_to.split('@')[0]}</td>
                <td class="px-4 py-3">
                    <select class="admin-assign-to border border-gray-300 rounded-md p-1 text-sm bg-blue-50 hover:bg-blue-100 cursor-pointer" 
                            data-id="${ticket.id}" data-current="${ticket.assign_to}" title="Reassign task to another admin">
                        <option value="admin@helpdesk.local" ${ticket.assign_to === 'admin@helpdesk.local' ? 'selected' : ''}>Awan</option>
                    </select>
                </td>
                <td class="px-4 py-3">
                    <div id="admin-attachments-${ticket.id}" class="flex flex-wrap gap-1"></div>
                </td>
                <td class="px-4 py-3">
                    <select class="admin-sla-priority border border-gray-300 rounded-md p-1 text-sm ${!canEdit || isDone ? 'bg-gray-100 cursor-not-allowed' : ''}" 
                            data-id="${ticket.id}" ${!canEdit || isDone ? 'disabled' : ''}>
                        <option value="Low" ${ticket.sla_priority === 'Low' ? 'selected' : ''}>Low</option>
                        <option value="Medium" ${ticket.sla_priority === 'Medium' ? 'selected' : ''}>Medium</option>
                        <option value="High" ${ticket.sla_priority === 'High' ? 'selected' : ''}>High</option>
                        <option value="Critical" ${ticket.sla_priority === 'Critical' ? 'selected' : ''}>Critical</option>
                    </select>
                </td>
                <td class="px-4 py-3">${mapItResolutionTime(ticket.sla_priority)}</td>
                <td class="px-4 py-3">${ticket.due_date || computeDueDate(ticket.start_date, ticket.sla_priority)}</td>
                <td class="px-4 py-3">${formatResponse(ticket.response_minutes)}</td>
                <td class="px-4 py-3">
                    <input type="date" class="admin-start-date border border-gray-300 rounded-md p-1 text-sm ${!canEdit || isDone ? 'bg-gray-100 cursor-not-allowed' : ''}" 
                           value="${ticket.start_date || ''}" data-id="${ticket.id}" ${!canEdit || isDone ? 'disabled' : ''}>
                </td>
                <td class="px-4 py-3">
                    <input type="date" class="admin-end-date border border-gray-300 rounded-md p-1 text-sm ${!canEdit || !isDone ? 'bg-gray-100 cursor-not-allowed' : ''}" 
                           value="${ticket.end_date || ''}" data-id="${ticket.id}" ${!canEdit || !isDone ? 'disabled' : ''}>
                </td>
                <td class="px-4 py-3">
                    <select class="admin-status-select border border-gray-300 rounded-md p-1 text-sm ${!canEdit ? 'bg-gray-100 cursor-not-allowed' : ''}" 
                            data-id="${ticket.id}" ${!canEdit ? 'disabled' : ''}>
                        <option value="Open" ${ticket.status === 'Open' ? 'selected' : ''}>Open</option>
                        <option value="In Progress" ${ticket.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                        <option value="Delayed" ${ticket.status === 'Delayed' ? 'selected' : ''}>Delayed</option>
                        <option value="Done" ${ticket.status === 'Done' ? 'selected' : ''}>Done</option>
                        <option value="Revisi" ${ticket.status === 'Revisi' ? 'selected' : ''}>Revisi</option>
                    </select>
                </td>
                <td class="px-4 py-3" style="min-width: 200px; max-width: 300px;">
                    <textarea class="admin-comment border border-gray-300 rounded-md p-2 text-sm w-full ${!canAddComment ? 'bg-gray-100 cursor-not-allowed' : ''}" 
                              rows="2" 
                              placeholder="${canAddComment ? 'Tambahkan komentar untuk user...' : 'Hanya admin yang di-assign yang dapat menambahkan komentar'}" 
                              data-id="${ticket.id}"
                              ${!canAddComment ? 'disabled' : ''}
                              style="resize: vertical; min-height: 50px;">${ticket.admin_comment || ''}</textarea>
                </td>
                <td class="px-4 py-3">
                    <div class="flex gap-2 flex-col">
                        <div class="flex gap-2">
                            <button class="admin-edit-ticket bg-blue-500 text-white px-2 py-1 rounded text-xs" data-id="${ticket.id}">Edit</button>
                            <button class="admin-delete-ticket bg-red-500 text-white px-2 py-1 rounded text-xs" data-id="${ticket.id}" data-number="${ticket.ticket_number}">Delete</button>
                        </div>
                        ${ticket.category === 'Pengajuan pembuatan atau perubahan aplikasi sistem' ? 
                            `<button class="admin-download-sdlc bg-green-500 text-white px-2 py-1 rounded text-xs mt-1 w-full" data-id="${ticket.id}" data-number="${ticket.ticket_number}" title="Download SDLC Form">
                                <i class="fas fa-download mr-1"></i>Download SDLC
                            </button>` : ''}
                    </div>
                </td>
            `;
            
            container.appendChild(row);
            
            // Load attachments for this ticket
            loadAdminAttachments(ticket.id);
        });
    };
    
    // Function to load attachments for admin table
    async function loadAdminAttachments(ticketId) {
        try {
            const result = await apiCall(`get_attachments&ticket_id=${ticketId}`);
            if (result.success && result.attachments.length > 0) {
                const attachmentContainer = document.getElementById(`admin-attachments-${ticketId}`);
                if (attachmentContainer) {
                    const getFileBadge = (name) => {
                        const ext = (name.split('.').pop() || '').toLowerCase();
                        if (['png','jpg','jpeg','gif','webp'].includes(ext)) return { cls: 'bg-pink-100 text-pink-600', icon: 'fa-file-image' };
                        if (ext === 'pdf') return { cls: 'bg-red-100 text-red-600', icon: 'fa-file-pdf' };
                        if (['doc','docx'].includes(ext)) return { cls: 'bg-blue-100 text-blue-600', icon: 'fa-file-word' };
                        if (['xls','xlsx','csv'].includes(ext)) return { cls: 'bg-green-100 text-green-600', icon: 'fa-file-excel' };
                        if (['zip','rar','7z'].includes(ext)) return { cls: 'bg-gray-100 text-gray-600', icon: 'fa-file-archive' };
                        return { cls: 'bg-gray-100 text-gray-600', icon: 'fa-file' };
                    };

                    attachmentContainer.innerHTML = result.attachments.map(attachment => {
                        if (attachment.attachment_type === 'file') {
                            const badge = getFileBadge(attachment.file_name || 'file');
                            return `
                                <span class="inline-flex items-center gap-1 ${badge.cls} rounded px-1.5 py-0.5" title="${attachment.file_name}">
                                    <i class="fas ${badge.icon} text-xs"></i>
                                    <a href="${attachment.file_path}" target="_blank" class="hover:underline" aria-label="View ${attachment.file_name}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="${attachment.file_path}" download class="hover:underline" aria-label="Download ${attachment.file_name}">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </span>
                            `;
                        } else if (attachment.attachment_type === 'url') {
                            return `
                                <a href="${attachment.url}" target="_blank" class="inline-flex items-center px-1.5 py-0.5 bg-blue-100 text-blue-600 text-xs rounded hover:bg-blue-200" title="Open link">
                                    <i class="fas fa-link text-xs"></i>
                                </a>
                            `;
                        }
                    }).join('');
                }
            }
        } catch (error) {
            console.error('Error loading admin attachments:', error);
        }
    }
    
    // Event listener untuk tabel tickets
    document.getElementById('all-tickets-table').addEventListener('change', async (e) => {
        const ticketId = e.target.dataset.id;
        if (!ticketId) return;

        let updateData = { ticket_id: parseInt(ticketId) };

        if (e.target.classList.contains('admin-status-select')) {
            updateData.status = e.target.value;
        } else if (e.target.classList.contains('admin-start-date')) {
            updateData.start_date = e.target.value;
        } else if (e.target.classList.contains('admin-end-date')) {
            updateData.end_date = e.target.value;
        } else if (e.target.classList.contains('admin-sla-priority')) {
            updateData.sla_priority = e.target.value;
        } else if (e.target.classList.contains('admin-assign-to')) {
            const newAssignTo = e.target.value;
            const currentAssignTo = e.target.dataset.current;
            
            if (!newAssignTo || newAssignTo === '') {
                // If empty, reset to current
                renderAllTicketsTable(); // Refresh to reset dropdown
                return;
            }
            
            if (newAssignTo === currentAssignTo) {
                // No change, don't update
                showNotification('Ticket is already assigned to this admin', 'info');
                return;
            }
            
            updateData.assign_to = newAssignTo;
            // Show notification when reassigning
            const newAdminName = newAssignTo.split('@')[0];
            showNotification(`Reassigning ticket to ${newAdminName}...`, 'info');
        }

        const result = await apiCall('update_ticket', 'POST', updateData);
        
        if (result.success) {
            renderAdminDashboard();
            renderAllTicketsTable();
        } else {
            alert(result.message);
            renderAllTicketsTable(); // Refresh to reset values
        }
    });
    
    // Event listener untuk admin comment (textarea - on blur dan Enter key)
    document.getElementById('all-tickets-table').addEventListener('blur', async (e) => {
        if (e.target.classList.contains('admin-comment')) {
            const ticketId = e.target.dataset.id;
            if (!ticketId) return;
            
            const commentValue = e.target.value.trim();
            const updateData = {
                ticket_id: parseInt(ticketId),
                admin_comment: commentValue
            };
            
            const result = await apiCall('update_ticket', 'POST', updateData);
            
            if (result.success) {
                if (commentValue) {
                    showNotification('Komentar berhasil disimpan', 'success');
                }
            } else {
                showNotification(result.message || 'Gagal menyimpan komentar', 'error');
            }
        }
    }, true);
    
    // Handle Enter key untuk save comment (Ctrl+Enter atau Shift+Enter)
    document.getElementById('all-tickets-table').addEventListener('keydown', async (e) => {
        if (e.target.classList.contains('admin-comment') && (e.ctrlKey || e.shiftKey) && e.key === 'Enter') {
            e.preventDefault();
            e.target.blur(); // Trigger blur event yang akan save comment
        }
    });


    // Admin edit/delete actions
    document.getElementById('all-tickets-table').addEventListener('click', async (e) => {
        const editBtn = e.target.closest('.admin-edit-ticket');
        const delBtn = e.target.closest('.admin-delete-ticket');
        const downloadSdlcBtn = e.target.closest('.admin-download-sdlc');
        
        if (editBtn) {
            const id = editBtn.dataset.id;
            showAdminEditTicketModal(id);
        } else if (downloadSdlcBtn) {
            const ticketId = downloadSdlcBtn.dataset.id;
            const ticketNumber = downloadSdlcBtn.dataset.number;
            
            // Show options: View, Download HTML, Download PDF
            const action = confirm(`Ticket ${ticketNumber}\n\nClick OK to VIEW in browser\nClick Cancel to see download options`);
            
            if (action) {
                // View in browser
                window.open(`api.php?action=view_sdlc&ticket_id=${ticketId}`, '_blank');
                showNotification(`Opening SDLC form for ticket ${ticketNumber}...`, 'success');
            } else {
                // Show download options
                const downloadType = confirm(`Download as:\n\nOK = PDF\nCancel = HTML`);
                if (downloadType) {
                    // Download PDF
                    window.open(`api.php?action=download_sdlc_pdf&ticket_id=${ticketId}`, '_blank');
                    showNotification(`Downloading SDLC PDF for ticket ${ticketNumber}...`, 'success');
                } else {
                    // Download HTML
                    window.open(`api.php?action=download_sdlc&ticket_id=${ticketId}`, '_blank');
                    showNotification(`Downloading SDLC HTML for ticket ${ticketNumber}...`, 'success');
                }
            }
        }
        if (delBtn) {
            const id = delBtn.dataset.id;
            const number = delBtn.dataset.number;
            showConfirmModal('Delete Ticket', `Are you sure you want to delete ticket <strong>${number}</strong>?`, async () => {
                const res = await apiCall('admin_delete_ticket', 'POST', { ticket_id: parseInt(id) });
                if (res.success) {
                    renderAdminDashboard();
                    renderAllTicketsTable();
                    showNotification('Ticket deleted successfully', 'success');
                } else {
                    showNotification(res.message || 'Delete failed', 'error');
                }
            });
        }
    });

    const showAdminEditTicketModal = async (ticketId) => {
        const details = await apiCall(`get_ticket_details&ticket_id=${ticketId}`);
        if (!details.success) {
            // Fallback: fetch from list if not reporter; admin may not access user endpoint
            const list = await apiCall('list_tickets');
            const t = (list.tickets || []).find(x => String(x.id) === String(ticketId));
            if (!t) { showNotification('Failed to load ticket details', 'error'); return; }
            details.ticket = t;
        }
        const t = details.ticket;
        const title = `Edit Ticket ${t.ticket_number}`;
        const content = `
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category" class="w-full p-2 border rounded-md">
                            <option value="Network" ${t.category==='Network'?'selected':''}>Network</option>
                            <option value="Software" ${t.category==='Software'?'selected':''}>Software</option>
                            <option value="Hardware" ${t.category==='Hardware'?'selected':''}>Hardware</option>
                            <option value="System & Aplikasi" ${t.category==='System & Aplikasi'?'selected':''}>System & Aplikasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" class="w-full p-2 border rounded-md">
                            <option value="Low" ${t.priority==='Low'?'selected':''}>Low</option>
                            <option value="Medium" ${t.priority==='Medium'?'selected':''}>Medium</option>
                            <option value="High" ${t.priority==='High'?'selected':''}>High</option>
                            <option value="Critical" ${t.priority==='Critical'?'selected':''}>Critical</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign to</label>
                    <select name="assign_to" class="w-full p-2 border rounded-md">
                        <option value="admin@helpdesk.local" ${t.assign_to==='admin@helpdesk.local'?'selected':''}>Awan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="4" class="w-full p-2 border rounded-md">${t.description||''}</textarea>
                </div>
            </div>
        `;
        const onSave = async (formData) => {
            const payload = {
                ticket_id: parseInt(ticketId),
                category: formData.get('category'),
                description: formData.get('description'),
                priority: formData.get('priority'),
                assign_to: formData.get('assign_to')
            };
            const res = await apiCall('update_ticket', 'POST', payload);
            if (res.success) {
                renderAdminDashboard();
                renderAllTicketsTable();
                showNotification('Ticket updated', 'success');
                return true;
            }
            alert(res.message || 'Update failed');
            return false;
        };
        showModal(title, content, onSave, 'Save Changes');
    };

    // Admin: User Management Tab
    const renderUserManagementTable = async () => {
        const container = document.getElementById('user-management-table');
        container.innerHTML = '<tr><td colspan="5" class="text-center p-4 text-gray-500">Loading...</td></tr>';
        
        const result = await apiCall('list_users');
        
        if (!result.success || result.users.length === 0) {
            container.innerHTML = '<tr><td colspan="5" class="text-center p-4 text-gray-500">No users found.</td></tr>';
            return;
        }
        
        container.innerHTML = '';
        result.users.forEach(user => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            row.innerHTML = `
                <td class="px-4 py-3 font-medium">${user.name}</td>
                <td class="px-4 py-3">${user.username}</td>
                <td class="px-4 py-3">${user.email}</td>
                <td class="px-4 py-3">${user.department}</td>
                <td class="px-4 py-3">${user.role}</td>
                <td class="px-4 py-3 space-x-2">
                    <button class="user-edit-btn text-sm font-medium text-indigo-600 hover:text-indigo-800" data-id="${user.id}">Edit</button>
                    <button class="user-reset-password-btn text-sm font-medium text-orange-600 hover:text-orange-800" data-id="${user.id}" data-name="${user.name}">Reset Password</button>
                    <button class="user-delete-btn text-sm font-medium text-red-600 hover:text-red-800" data-id="${user.id}" data-email="${user.email}">Delete</button>
                </td>
            `;
            container.appendChild(row);
        });
    };
    
    document.getElementById('user-management-table').addEventListener('click', async (e) => {
        const target = e.target;
        const userId = target.dataset.id;
        
        if (target.classList.contains('user-edit-btn')) {
            showUserFormModal(userId);
        }
        
        if (target.classList.contains('user-reset-password-btn')) {
            const userName = target.dataset.name;
            showResetPasswordModal(userId, userName);
        }
        
        if (target.classList.contains('user-delete-btn')) {
            if (ADMIN_EMAILS.includes(target.dataset.email)) {
                alert('Main admin accounts cannot be deleted.'); 
                return;
            }
            showConfirmModal('Delete User', 'Are you sure you want to delete this user? This action cannot be undone.', async () => {
                const result = await apiCall('delete_user', 'POST', { user_id: parseInt(userId) });
                if (result.success) {
                    renderUserManagementTable();
                } else {
                    alert(result.message);
                }
            });
        }
    });
    
    const showUserFormModal = async (userId) => {
        let user = null;
        
        if (userId) {
            const result = await apiCall('list_users');
            user = result.users.find(u => u.id == userId);
        }
        
        const title = user ? 'Edit User' : 'Create New User';
        const isEditingAdmin = user && ADMIN_EMAILS.includes(user.email);
        
        const content = `
            <div class="space-y-4">
                <input type="hidden" name="user_id" value="${user?.id || ''}">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" class="mt-1 w-full p-2 border rounded-md" value="${user?.username || ''}" ${user ? 'readonly' : ''} pattern="[a-zA-Z0-9_]+" placeholder="Letters, numbers, underscore only" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" class="mt-1 w-full p-2 border rounded-md" value="${user?.name || ''}" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" class="mt-1 w-full p-2 border rounded-md" value="${user?.email || ''}" ${user ? 'readonly' : ''} required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Department</label>
                    <input type="text" name="department" class="mt-1 w-full p-2 border rounded-md" value="${user?.department || ''}" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role</label>
                    <select name="role" class="mt-1 w-full p-2 border rounded-md" ${isEditingAdmin ? 'disabled' : ''}>
                        <option value="user" ${user?.role === 'user' ? 'selected' : ''}>User</option>
                        <option value="admin" ${user?.role === 'admin' ? 'selected' : ''}>Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" class="mt-1 w-full p-2 border rounded-md" placeholder="${user ? 'Leave blank to keep current' : 'Required'}" ${!user ? 'required' : ''}>
                </div>
            </div>
        `;
        
        const onSave = async (formData) => {
            const data = {
                username: formData.get('username'),
                name: formData.get('name'),
                email: formData.get('email'),
                department: formData.get('department'),
                role: formData.get('role'),
                password: formData.get('password')
            };
            
            let result;
            if (userId) {
                data.user_id = parseInt(userId);
                result = await apiCall('update_user', 'POST', data);
            } else {
                if (!data.password) {
                    alert('Password is required for new users');
                    return false;
                }
                result = await apiCall('create_user', 'POST', data);
            }
            
            if (result.success) {
                renderUserManagementTable();
                return true;
            } else {
                alert(result.message);
                return false;
            }
        };
        
        showModal(title, content, onSave);
    };
    
    // Show Reset Password Modal
    const showResetPasswordModal = (userId, userName) => {
        const title = `Reset Password for ${userName}`;
        const content = `
            <div class="space-y-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        This will reset the password for <strong>${userName}</strong>. The user will need to use the new password to login.
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" name="new_password" class="mt-1 w-full p-2 border rounded-md" required minlength="6" placeholder="Enter new password (min. 6 characters)">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="mt-1 w-full p-2 border rounded-md" required minlength="6" placeholder="Confirm new password">
                </div>
            </div>
        `;

        const onSave = async (formData) => {
            const newPassword = formData.get('new_password');
            const confirmPassword = formData.get('confirm_password');
            
            if (newPassword !== confirmPassword) {
                alert('Passwords do not match');
                return false;
            }
            
            const result = await apiCall('reset_password', 'POST', {
                user_id: parseInt(userId),
                new_password: newPassword
            });
            
            if (result.success) {
                alert(result.message);
                return true;
            } else {
                alert(result.message);
                return false;
            }
        };

        showModal(title, content, onSave, 'Reset Password');
    };

    // --- Report Functions ---
    
    const loadAdminListForReport = async () => {
        try {
            console.log('Loading admin list for report...');
            const result = await apiCall('get_admin_list');
            console.log('Admin list result:', result);
            console.log('Admins array:', result.admins);
            
            const select = document.getElementById('report-filter-admin');
            if (!select) {
                console.error('Report filter select not found!');
                return;
            }
            
            if (result.success && result.admins && Array.isArray(result.admins) && result.admins.length > 0) {
                // Clear existing options except "All Tickets"
                select.innerHTML = '<option value="all">All Tickets</option>';
                // Add admin options - display username but use email as value
                result.admins.forEach((admin, index) => {
                    console.log(`Processing admin ${index}:`, admin, typeof admin);
                    
                    let emailValue = '';
                    if (typeof admin === 'object' && admin !== null && !Array.isArray(admin)) {
                        emailValue = (admin.email && typeof admin.email === 'string') ? admin.email : '';
                    } else if (typeof admin === 'string') {
                        emailValue = admin;
                    }
                    
                    const option = document.createElement('option');
                    
                    // Handle both object and string formats
                    let displayText;
                    
                    if (typeof admin === 'object' && admin !== null && !Array.isArray(admin)) {
                        // Admin is an object with email and username properties
                        emailValue = (admin.email && typeof admin.email === 'string') ? admin.email : '';
                        if (admin.username && typeof admin.username === 'string') {
                            displayText = admin.username.trim();
                        } else if (admin.name && typeof admin.name === 'string') {
                            displayText = admin.name.trim();
                        } else if (emailValue) {
                            displayText = emailValue.split('@')[0];
                        } else {
                            displayText = 'Unknown';
                        }
                    } else if (typeof admin === 'string') {
                        // Admin is just an email string (backward compatibility)
                        emailValue = admin;
                        displayText = admin.split('@')[0];
                    } else {
                        console.warn('Unexpected admin format:', admin, typeof admin);
                        emailValue = String(admin);
                        displayText = String(admin);
                    }
                    
                    // Validate emailValue before using
                    if (!emailValue || emailValue === 'undefined' || emailValue === 'null' || emailValue.trim() === '') {
                        console.warn('Invalid email value for admin:', admin);
                        return; // Skip this admin if email is invalid
                    }
                    
                    // Capitalize first letter of display text (but preserve existing capitalization)
                    if (displayText && typeof displayText === 'string' && displayText.trim() !== '') {
                        // Only capitalize if it's all lowercase or starts with lowercase
                        if (displayText === displayText.toLowerCase() || displayText.charAt(0) === displayText.charAt(0).toLowerCase()) {
                            displayText = displayText.charAt(0).toUpperCase() + displayText.slice(1);
                        }
                    } else {
                        displayText = 'Unknown';
                    }
                    
                    option.value = emailValue.trim();
                    option.textContent = displayText;
                    select.appendChild(option);
                    
                    console.log(`Added option: value="${emailValue}", text="${displayText}"`);
                });
                console.log(`Loaded ${result.admins.length} admins successfully`);
            } else {
                console.warn('No admins found or API error:', result);
                // Keep "All Tickets" option
                select.innerHTML = '<option value="all">All Tickets</option>';
            }
        } catch (error) {
            console.error('Error loading admin list:', error);
            const select = document.getElementById('report-filter-admin');
            if (select) {
                select.innerHTML = '<option value="all">All Tickets</option>';
            }
        }
    };
    
    const viewReport = async () => {
        const select = document.getElementById('report-filter-admin');
        const filterAdmin = select ? select.value : 'all';
        const selectedOption = select ? select.options[select.selectedIndex] : null;
        const adminDisplayName = selectedOption ? selectedOption.textContent : '';
        
        const modal = document.getElementById('report-view-modal');
        const content = document.getElementById('report-content');
        const filterLabel = document.getElementById('report-modal-filter');
        
        console.log('View report called with filter:', filterAdmin, 'Display:', adminDisplayName);
        
        if (!modal || !content || !filterLabel) {
            alert('Error: Report modal elements not found!');
            return;
        }
        
        // Show modal
        modal.classList.remove('hidden');
        modal.classList.add('active');
        
        // Update filter label - show username if available
        filterLabel.textContent = filterAdmin === 'all' ? 'All Tickets' : `Filtered by: ${adminDisplayName}`;
        
        // Show loading
        content.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600">Loading report...</p>
            </div>
        `;
        
        try {
            const url = `api.php?action=get_report${filterAdmin && filterAdmin !== 'all' ? '&admin=' + encodeURIComponent(filterAdmin) : ''}`;
            console.log('Fetching report from:', url);
            
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Report result:', result);
            
            if (result.success && result.tickets) {
                console.log(`Loaded ${result.tickets.length} tickets`);
                // Generate HTML report - use display name from label
                const filterDisplayName = filterAdmin === 'all' ? 'All Tickets' : adminDisplayName;
                const html = generateReportHTML(result.tickets, filterDisplayName);
                content.innerHTML = html;
            } else {
                console.error('Report API error:', result);
                content.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-4xl text-yellow-400 mb-4"></i>
                        <p class="text-gray-600 mb-2">Error loading report: ${result.message || 'Unknown error'}</p>
                        <p class="text-sm text-gray-500">Please check console for details.</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading report:', error);
            content.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                    <p class="text-gray-600 mb-2">Error loading report. Please try again.</p>
                    <p class="text-sm text-gray-500">Error: ${error.message}</p>
                </div>
            `;
        }
    };
    
    const generateReportHTML = (tickets, filterLabel) => {
        let html = `
            <div style="display: flex; flex-direction: column; height: 100%; min-height: 0; width: 100%; max-width: 100%;">
                <div class="report-header mb-4 md:mb-6 pb-4 border-b border-gray-200" style="flex-shrink: 0;">
                    <h3 class="text-lg md:text-xl font-bold mb-1 md:mb-2 text-gray-800">IT Helpdesk System</h3>
                    <p class="text-sm md:text-base text-gray-600">Bamboo Village Trust</p>
                    <p class="text-xs md:text-sm text-gray-500 mt-2">Filter: ${escapeHtml(filterLabel)}</p>
                    <p class="text-xs md:text-sm text-gray-500">Generated on: ${new Date().toLocaleString('id-ID')}</p>
                </div>
                <div style="display: flex; flex-direction: column; flex: 1; min-height: 0; position: relative;">
                    <div class="overflow-x-auto sticky-scrollbar" style="flex: 1; overflow-y: auto; -webkit-overflow-scrolling: touch; position: relative;">
                    <table class="w-full text-sm text-left" style="table-layout: fixed; width: 100%;">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 sticky-header">
                        <tr>
                            <th style="width: 5%;">Ticket No.</th>
                            <th style="width: 7%;">Reporter</th>
                            <th style="width: 8%;">Department</th>
                            <th style="width: 8%;">Category</th>
                            <th style="width: 20%;">Description</th>
                            <th style="width: 5%;">Priority</th>
                            <th style="width: 6%;">Assigned To</th>
                            <th style="width: 6%;">Respon Time</th>
                            <th style="width: 5%;">Reassign</th>
                            <th style="width: 6%;">Admin Respon</th>
                            <th style="width: 6%;">Resolution Time</th>
                            <th style="width: 6%;">Status</th>
                            <th style="width: 8%;">Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        if (tickets.length === 0) {
            html += `
                <tr>
                    <td colspan="13" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <p>No tickets found</p>
                    </td>
                </tr>
            `;
        } else {
            tickets.forEach(ticket => {
                const statusClass = `status-${ticket.status.toLowerCase().replace(' ', '-')}`;
                const assignToDisplay = ticket.assign_to ? (ticket.assign_to.includes('@') ? ticket.assign_to.split('@')[0] : ticket.assign_to) : '-';
                html += `
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium" style="word-wrap: break-word; overflow-wrap: break-word;">${escapeHtml(ticket.ticket_number)}</td>
                        <td class="px-4 py-3" style="word-wrap: break-word; overflow-wrap: break-word;">${escapeHtml(ticket.reporter_name)}</td>
                        <td class="px-4 py-3" style="word-wrap: break-word; overflow-wrap: break-word;">${escapeHtml(ticket.reporter_dept)}</td>
                        <td class="px-4 py-3" style="word-wrap: break-word; overflow-wrap: break-word;">${escapeHtml(ticket.category)}</td>
                        <td class="px-4 py-3 description" style="white-space: normal; word-wrap: break-word; overflow-wrap: break-word; line-height: 1.4;">${escapeHtml(ticket.description)}</td>
                        <td class="px-4 py-3" style="word-wrap: break-word; overflow-wrap: break-word;">
                            <span class="px-2 py-1 rounded text-xs font-medium ${ticket.priority === 'High' ? 'bg-red-100 text-red-800' : ticket.priority === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'}">${escapeHtml(ticket.priority)}</span>
                        </td>
                        <td class="px-4 py-3" style="word-wrap: break-word; word-break: break-word; overflow-wrap: break-word;">${escapeHtml(assignToDisplay)}</td>
                        <td class="px-4 py-3" style="word-wrap: break-word; overflow-wrap: break-word;">${escapeHtml(ticket.response_time || '-')}</td>
                        <td class="px-4 py-3 text-center" style="word-wrap: break-word; overflow-wrap: break-word;">${escapeHtml(ticket.reassign_count || '0')}</td>
                        <td class="px-4 py-3" style="word-wrap: break-word; word-break: break-word; overflow-wrap: break-word;">${escapeHtml(ticket.admin_respon || '-')}</td>
                        <td class="px-4 py-3" style="word-wrap: break-word; overflow-wrap: break-word;">${escapeHtml(ticket.resolution_time || '-')}</td>
                        <td class="px-4 py-3 ${statusClass}" style="word-wrap: break-word; overflow-wrap: break-word;">
                            <span class="px-2 py-1 rounded text-xs font-medium ${ticket.status === 'Done' ? 'bg-green-100 text-green-800' : ticket.status === 'In Progress' ? 'bg-blue-100 text-blue-800' : ticket.status === 'Delayed' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'}">${escapeHtml(ticket.status)}</span>
                        </td>
                        <td class="px-4 py-3" style="word-wrap: break-word; overflow-wrap: break-word;">${new Date(ticket.created_at).toLocaleDateString('id-ID')}</td>
                    </tr>
                `;
            });
        }
        
        html += `
                    </tbody>
                    </table>
                    </div>
                </div>
                <div class="mt-4 md:mt-6 pt-4 border-t border-gray-200 text-center text-xs md:text-sm text-gray-600" style="flex-shrink: 0; padding-top: 1rem; margin-top: 1rem;">
                    <p style="margin: 0;"><strong>Total Tickets: ${tickets.length}</strong></p>
                </div>
            </div>
        `;
        
        return html;
    };
    
    const exportReport = async (format) => {
        const filterAdmin = document.getElementById('report-filter-admin').value;
        const url = `export.php?action=export_${format}${filterAdmin && filterAdmin !== 'all' ? '&admin=' + encodeURIComponent(filterAdmin) : ''}`;
        
        console.log('Exporting report:', format, 'with filter:', filterAdmin);
        console.log('Export URL:', url);
        
        try {
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Export result:', result);
            
            if (result.success) {
                if (format === 'pdf') {
                    // Open in new window for printing
                    const printWindow = window.open('', '_blank');
                    if (printWindow) {
                        printWindow.document.write(result.html);
                        printWindow.document.close();
                        printWindow.onload = () => {
                            printWindow.print();
                        };
                    } else {
                        alert('Please allow popups to print the report');
                    }
                } else if (format === 'excel') {
                    // Download CSV
                    const blob = new Blob([result.csv], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = result.filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    // Clean up
                    setTimeout(() => URL.revokeObjectURL(link.href), 100);
                }
            } else {
                console.error('Export API error:', result);
                alert('Error exporting report: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error exporting report:', error);
            alert('Error exporting report: ' + error.message + '\nPlease check console for details.');
        }
    };
    
    // Function to close print window
    const closePrintWindow = () => {
        if (printWindowRef && !printWindowRef.closed) {
            printWindowRef.close();
            printWindowRef = null;
        }
        // Hide close print button
        const closePrintBtn = document.getElementById('close-print-window-btn');
        if (closePrintBtn) {
            closePrintBtn.classList.add('hidden');
        }
    };
    
    const closeReportModal = () => {
        const modal = document.getElementById('report-view-modal');
        modal.classList.add('hidden');
        modal.classList.remove('active');
        // Also close print window if open
        closePrintWindow();
    };
    
    // Close modal when clicking outside
    document.addEventListener('click', (e) => {
        const modal = document.getElementById('report-view-modal');
        if (modal && !modal.classList.contains('hidden')) {
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent && !modalContent.contains(e.target) && e.target === modal) {
                closeReportModal();
            }
        }
    });
    
    // Store print window reference globally
    let printWindowRef = null;
    
    const printReport = () => {
        const content = document.getElementById('report-content').innerHTML;
        printWindowRef = window.open('', '_blank');
        
        if (!printWindowRef) {
            alert('Please allow popups to print the report');
            return;
        }
        
        printWindowRef.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Ticket Report</title>
                <meta charset="UTF-8">
                <style>
                    * {
                        box-sizing: border-box;
                    }
                    
                    body { 
                        font-family: Arial, sans-serif; 
                        margin: 10px; 
                        font-size: 10px;
                        line-height: 1.3;
                    }
                    
                    .report-header { 
                        margin-bottom: 15px; 
                        padding-bottom: 10px;
                        border-bottom: 2px solid #ddd;
                    }
                    
                    .report-header h3 {
                        margin: 0 0 5px 0;
                        font-size: 16px;
                        font-weight: bold;
                    }
                    
                    .report-header p {
                        margin: 3px 0;
                        font-size: 11px;
                        color: #666;
                    }
                    
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        font-size: 9px;
                        table-layout: fixed;
                    }
                    
                    thead {
                        background-color: #f3f4f6;
                    }
                    
                    th, td { 
                        border: 1px solid #ddd; 
                        padding: 4px 6px; 
                        text-align: left; 
                        vertical-align: top;
                        word-wrap: break-word;
                        overflow-wrap: break-word;
                    }
                    
                    th { 
                        background-color: #f3f4f6; 
                        font-weight: bold;
                        font-size: 9px;
                        position: sticky;
                        top: 0;
                        z-index: 10;
                    }
                    
                    /* Column widths untuk landscape A4 */
                    th:nth-child(1), td:nth-child(1) { width: 5%; } /* Ticket No. */
                    th:nth-child(2), td:nth-child(2) { width: 7%; } /* Reporter */
                    th:nth-child(3), td:nth-child(3) { width: 8%; } /* Department */
                    th:nth-child(4), td:nth-child(4) { width: 8%; } /* Category */
                    th:nth-child(5), td:nth-child(5) { width: 20%; } /* Description */
                    th:nth-child(6), td:nth-child(6) { width: 5%; } /* Priority */
                    th:nth-child(7), td:nth-child(7) { width: 6%; } /* Assigned To */
                    th:nth-child(8), td:nth-child(8) { width: 6%; } /* Respon Time */
                    th:nth-child(9), td:nth-child(9) { width: 5%; } /* Reassign */
                    th:nth-child(10), td:nth-child(10) { width: 6%; } /* Admin Respon */
                    th:nth-child(11), td:nth-child(11) { width: 6%; } /* Resolution Time */
                    th:nth-child(12), td:nth-child(12) { width: 6%; } /* Status */
                    th:nth-child(13), td:nth-child(13) { width: 8%; } /* Created Date */
                    
                    td.description {
                        word-wrap: break-word;
                        overflow-wrap: break-word;
                        white-space: normal;
                        line-height: 1.4;
                    }
                    
                    tbody tr {
                        page-break-inside: avoid;
                    }
                    
                    tbody tr:nth-child(even) {
                        background-color: #f9fafb;
                    }
                    
                    .sticky-scrollbar {
                        overflow-x: auto;
                        overflow-y: visible;
                    }
                    
                    @media print {
                        @page {
                            size: A4 landscape;
                            margin: 0.5cm;
                        }
                        
                        body { 
                            margin: 0;
                            font-size: 8px;
                        }
                        
                        .report-header {
                            margin-bottom: 10px;
                            padding-bottom: 8px;
                        }
                        
                        .report-header h3 {
                            font-size: 14px;
                        }
                        
                        .report-header p {
                            font-size: 9px;
                        }
                        
                        table {
                            font-size: 7px;
                        }
                        
                        th, td {
                            padding: 3px 4px;
                            font-size: 7px;
                        }
                        
                        th {
                            font-size: 7px;
                        }
                        
                        .sticky-scrollbar {
                            overflow: visible;
                        }
                        
                        /* Pastikan semua kolom terlihat */
                        table {
                            width: 100%;
                            table-layout: fixed;
                        }
                    }
                    
                    @media screen {
                        body {
                            background-color: #f5f5f5;
                        }
                        
                        .sticky-scrollbar {
                            max-height: 80vh;
                            overflow-y: auto;
                        }
                    }
                </style>
            </head>
            <body>
                ${content}
            </body>
            </html>
        `);
        printWindowRef.document.close();
        
        // Show close print window button
        const closePrintBtn = document.getElementById('close-print-window-btn');
        if (closePrintBtn) {
            closePrintBtn.classList.remove('hidden');
        }
        
        printWindowRef.onload = () => {
            printWindowRef.print();
        };
        
        // Monitor if print window is closed manually
        const checkPrintWindow = setInterval(() => {
            if (printWindowRef.closed) {
                clearInterval(checkPrintWindow);
                printWindowRef = null;
                // Hide close button
                if (closePrintBtn) {
                    closePrintBtn.classList.add('hidden');
                }
            }
        }, 500);
    };
    
    // Close print window button handler
    const closePrintWindowBtn = document.getElementById('close-print-window-btn');
    if (closePrintWindowBtn) {
        closePrintWindowBtn.addEventListener('click', () => {
            closePrintWindow();
        });
    }
    
    // Update user profile display function
    const updateUserProfileDisplay = (user) => {
        const profilePicture = document.getElementById('user-profile-picture');
        const profilePlaceholder = document.getElementById('user-profile-placeholder');
        const userNameDisplay = document.getElementById('user-name-display');
        const dropdownUserName = document.getElementById('dropdown-user-name');
        const dropdownUserEmail = document.getElementById('dropdown-user-email');
        
        // Display name in header
        if (userNameDisplay) {
            userNameDisplay.textContent = user.name || user.username || 'User';
        }
        
        // Display name and email in dropdown
        if (dropdownUserName) {
            dropdownUserName.textContent = user.name || user.username || 'User';
        }
        if (dropdownUserEmail) {
            dropdownUserEmail.textContent = user.email || '';
        }
        
        // Display profile picture
        if (user.profile_picture) {
            if (profilePicture) {
                profilePicture.src = user.profile_picture;
                profilePicture.style.display = 'block';
            }
            if (profilePlaceholder) {
                profilePlaceholder.style.display = 'none';
            }
        } else {
            if (profilePicture) {
                profilePicture.style.display = 'none';
            }
            if (profilePlaceholder) {
                profilePlaceholder.style.display = 'flex';
                // Show first letter of name
                const firstLetter = (user.name || user.username || 'U').charAt(0).toUpperCase();
                profilePlaceholder.innerHTML = `<span>${firstLetter}</span>`;
            }
        }
    };
    
    // Profile Dropdown Menu Handlers
    const userProfileBtn = document.getElementById('user-profile-btn');
    const userProfileDropdown = document.getElementById('user-profile-dropdown');
    const editProfileMenuBtn = document.getElementById('edit-profile-menu-btn');
    const changePasswordMenuBtn = document.getElementById('change-password-menu-btn');
    const logoutMenuBtn = document.getElementById('logout-menu-btn');
    const editProfileModal = document.getElementById('edit-profile-modal');
    const closeEditProfileModal = document.getElementById('close-edit-profile-modal');
    const cancelEditProfileBtn = document.getElementById('cancel-edit-profile-btn');
    const editProfileForm = document.getElementById('edit-profile-form');
    const profilePictureInput = document.getElementById('profile-picture-input');
    const profilePictureContainer = document.getElementById('profile-picture-container');
    const profilePicturePreview = document.getElementById('profile-picture-preview');
    const profilePicturePlaceholder = document.getElementById('profile-picture-placeholder');
    
    // Toggle profile dropdown
    if (userProfileBtn && userProfileDropdown) {
        userProfileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userProfileDropdown.classList.toggle('hidden');
            
            // Update dropdown content
            if (currentUser) {
                document.getElementById('dropdown-user-name').textContent = currentUser.name || currentUser.username || 'User';
                document.getElementById('dropdown-user-email').textContent = currentUser.email || '';
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userProfileBtn.contains(e.target) && !userProfileDropdown.contains(e.target)) {
                userProfileDropdown.classList.add('hidden');
            }
        });
    }
    
    // Open edit profile modal from dropdown menu
    const openEditProfileModal = (e) => {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        console.log('openEditProfileModal called', currentUser);
        
        if (!currentUser) {
            console.warn('No current user found');
            return;
        }
        
        // Close dropdown
        if (userProfileDropdown) {
            userProfileDropdown.classList.add('hidden');
        }
        
        // Get form elements
        const nameInput = document.getElementById('edit-profile-name');
        const emailInput = document.getElementById('edit-profile-email');
        const departmentInput = document.getElementById('edit-profile-department');
        
        if (!nameInput || !emailInput || !departmentInput) {
            console.error('Form elements not found');
            return;
        }
        
        // Populate form
        nameInput.value = currentUser.name || '';
        emailInput.value = currentUser.email || '';
        departmentInput.value = currentUser.department || '';
        
        // Display current profile picture
        if (profilePicturePreview && profilePicturePlaceholder) {
            if (currentUser.profile_picture) {
                profilePicturePreview.src = currentUser.profile_picture;
                profilePicturePreview.style.display = 'block';
                profilePicturePlaceholder.style.display = 'none';
            } else {
                profilePicturePreview.style.display = 'none';
                profilePicturePlaceholder.style.display = 'flex';
                const firstLetter = (currentUser.name || currentUser.username || 'U').charAt(0).toUpperCase();
                profilePicturePlaceholder.innerHTML = `<span>${firstLetter}</span>`;
            }
        }
        
        // Show modal
        if (editProfileModal) {
            editProfileModal.classList.remove('hidden');
            editProfileModal.classList.add('active');
            console.log('Modal should be visible now');
        } else {
            console.error('Edit profile modal not found');
        }
    };
    
    if (editProfileMenuBtn) {
        editProfileMenuBtn.addEventListener('click', (e) => {
            console.log('Edit profile menu button clicked');
            openEditProfileModal(e);
        });
        console.log('Edit profile menu button event listener attached');
    } else {
        console.error('Edit profile menu button not found');
    }
    
    // Handle change password from dropdown
    if (changePasswordMenuBtn) {
        changePasswordMenuBtn.addEventListener('click', () => {
            if (userProfileDropdown) {
                userProfileDropdown.classList.add('hidden');
            }
            // Trigger existing change password functionality
            const changePasswordBtn = document.getElementById('change-password-btn');
            if (changePasswordBtn) {
                changePasswordBtn.click();
            }
        });
    }
    
    // Handle logout from dropdown
    if (logoutMenuBtn) {
        logoutMenuBtn.addEventListener('click', () => {
            if (userProfileDropdown) {
                userProfileDropdown.classList.add('hidden');
            }
            // Trigger existing logout functionality
            const logoutBtn = document.getElementById('logout-btn');
            if (logoutBtn) {
                logoutBtn.click();
            }
        });
    }
    
    // Click on profile picture to upload
    if (profilePictureContainer && profilePictureInput) {
        profilePictureContainer.addEventListener('click', () => {
            profilePictureInput.click();
        });
    }
    
    // Close edit profile modal
    const closeEditProfileModalFunc = () => {
        if (editProfileModal) {
            editProfileModal.classList.add('hidden');
            editProfileModal.classList.remove('active');
        }
        if (editProfileForm) {
            editProfileForm.reset();
        }
    };
    
    if (closeEditProfileModal) {
        closeEditProfileModal.addEventListener('click', closeEditProfileModalFunc);
    }
    
    if (cancelEditProfileBtn) {
        cancelEditProfileBtn.addEventListener('click', closeEditProfileModalFunc);
    }
    
    // Close modal when clicking outside
    if (editProfileModal) {
        editProfileModal.addEventListener('click', (e) => {
            if (e.target === editProfileModal) {
                closeEditProfileModalFunc();
            }
        });
    }
    
    // Preview profile picture when selected
    if (profilePictureInput) {
        profilePictureInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('Please select an image file');
                    return;
                }
                
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = (event) => {
                    profilePicturePreview.src = event.target.result;
                    profilePicturePreview.style.display = 'block';
                    profilePicturePlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Handle edit profile form submission
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!currentUser) return;
            
            const formData = new FormData(editProfileForm);
            
            try {
                const response = await fetch('api.php?action=update_profile', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update currentUser
                    currentUser.name = result.user.name;
                    currentUser.department = result.user.department;
                    if (result.user.profile_picture) {
                        currentUser.profile_picture = result.user.profile_picture;
                    }
                    
                    // Update display
                    updateUserProfileDisplay(currentUser);
                    
                    // Close modal
                    if (editProfileModal) {
                        editProfileModal.classList.add('hidden');
                        editProfileModal.classList.remove('active');
                    }
                    if (editProfileForm) {
                        editProfileForm.reset();
                    }
                    
                    // Show success message
                    showNotification('Profile updated successfully!', 'success');
                } else {
                    showNotification(result.message || 'Failed to update profile', 'error');
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                showNotification('An error occurred while updating profile', 'error');
            }
        });
    }
    
    // Make updateUserProfileDisplay available globally
    window.updateUserProfileDisplay = updateUserProfileDisplay;

    // --- Main App Initialization ---
    const main = async () => {
        console.log('main() function called');
        try {
            console.log('Calling check_session API...');
            const result = await apiCall('check_session');
            console.log('Session check result:', result);
            
            if (result.success && result.user) {
                console.log('User is logged in:', result.user);
                currentUser = result.user;
                if (typeof updateUserProfileDisplay === 'function') {
                    updateUserProfileDisplay(currentUser);
                } else {
                    // Fallback if function not yet defined
                    const userNameDisplay = document.getElementById('user-name-display');
                    if (userNameDisplay) {
                        userNameDisplay.textContent = currentUser.name || currentUser.username || 'User';
                    }
                }
                
                // Update sidebar visibility based on user role
                const isAdmin = ADMIN_EMAILS.includes(result.user.email);
                if (typeof window.updateSidebarVisibility === 'function') {
                    window.updateSidebarVisibility(isAdmin);
                }
                
                // Keep change password button hidden (now accessed via dropdown)
                const changePasswordBtn = document.getElementById('change-password-btn');
                if (changePasswordBtn) {
                    changePasswordBtn.classList.remove('hidden');
                }

                showView(views.appContainer);
                
                if (currentUser.role === 'admin') {
                    views.adminView.classList.remove('hidden');
                    views.userView.classList.add('hidden');
                    initAdminView();
                } else {
                    views.userView.classList.remove('hidden');
                    views.adminView.classList.add('hidden');
                    initUserView(currentUser);
                }
                
                // Load notifications after login
                if (typeof loadNotifications === 'function') {
                    loadNotifications();
                }
            } else {
                console.log('User is not logged in, showing login view');
                // Clear any cached user data and reset UI
                currentUser = null;
                resetUI();
                showView(views.loginView);
            }
        } catch (error) {
            console.error('Session check error:', error);
            // On error, assume not logged in and reset UI
            currentUser = null;
            resetUI();
            showView(views.loginView);
        }
    };

    main();
});