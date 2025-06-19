import Alpine from 'alpinejs'
import focus from '@alpinejs/focus'
import persist from '@alpinejs/persist'
import registration from './registration'
import automationRequest from './automationRequest'
import adminDashboard from './adminDashboard.js'

// Initialize Alpine.js plugins
Alpine.plugin(focus)
Alpine.plugin(persist)

// Global store for user state
Alpine.store('auth', {
    user: null,
    isAuthenticated: false,
    isAdmin: false,
    
    async init() {
        try {
            const response = await fetch('/php/check_auth.php')
            const data = await response.json()
            
            if (response.ok && data.authenticated) {
                this.user = data.user
                this.isAuthenticated = true
                this.isAdmin = data.user.is_admin === true
            }
        } catch (err) {
            console.error('Error checking authentication:', err)
        }
    },
    
    async login(email, password) {
        try {
            const response = await fetch('/php/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password })
            })

            const data = await response.json()
            
            if (!response.ok) {
                throw new Error(data.error || 'Login failed')
            }

            this.user = data.user
            this.isAuthenticated = true
            this.isAdmin = data.user.is_admin === true

            // Redirect based on user type
            if (this.isAdmin) {
                window.location.href = '/admin.html'
            } else {
                window.location.href = '/request.html'
            }

            return { success: true }

        } catch (err) {
            console.error('Login error:', err)
            return { 
                success: false, 
                error: err.message || 'Une erreur est survenue lors de la connexion'
            }
        }
    },
    
    async logout() {
        try {
            await fetch('/php/logout.php', { method: 'POST' })
            this.user = null
            this.isAuthenticated = false
            this.isAdmin = false
            window.location.href = '/'
        } catch (err) {
            console.error('Logout error:', err)
        }
    }
})

// Custom Alpine.js directives
Alpine.directive('scroll', (el, { expression }) => {
    el.addEventListener('click', () => {
        const target = document.querySelector(expression)
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' })
        }
    })
})

// Register Alpine.js components
Alpine.data('registration', registration)
Alpine.data('automationRequest', automationRequest)
Alpine.data('adminDashboard', adminDashboard)

// Initialize Alpine.js
window.Alpine = Alpine
Alpine.start() 