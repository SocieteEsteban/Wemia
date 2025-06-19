import Alpine from 'alpinejs'
import focus from '@alpinejs/focus'
import persist from '@alpinejs/persist'

// Initialize Alpine.js plugins
Alpine.plugin(focus)
Alpine.plugin(persist)

// Global store for user state
Alpine.store('auth', {
    user: null,
    isLoggedIn: false,
    
    login(userData) {
        this.user = userData
        this.isLoggedIn = true
    },
    
    logout() {
        this.user = null
        this.isLoggedIn = false
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

// Initialize Alpine.js
window.Alpine = Alpine
Alpine.start() 