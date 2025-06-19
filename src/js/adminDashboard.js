export default function adminDashboard() {
    return {
        requests: [],
        stats: {
            pending: 0,
            inProgress: 0,
            completed: 0,
            total: 0
        },
        filters: {
            status: '',
            priority: '',
            search: ''
        },
        pagination: {
            currentPage: 1,
            perPage: 10,
            total: 0
        },
        showRequestModal: false,
        selectedRequest: null,
        newStatus: '',
        statusComment: '',
        
        init() {
            this.loadRequests()
            this.loadStats()
        },

        async loadRequests() {
            try {
                const response = await fetch('/php/admin/get_requests.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        ...this.filters,
                        page: this.pagination.currentPage,
                        perPage: this.pagination.perPage
                    })
                })

                const data = await response.json()
                
                if (!response.ok) {
                    throw new Error(data.error || 'Une erreur est survenue')
                }

                this.requests = data.requests
                this.pagination.total = data.total
                
            } catch (err) {
                console.error('Error loading requests:', err)
            }
        },

        async loadStats() {
            try {
                const response = await fetch('/php/admin/get_stats.php')
                const data = await response.json()
                
                if (!response.ok) {
                    throw new Error(data.error || 'Une erreur est survenue')
                }

                this.stats = data.stats
                
            } catch (err) {
                console.error('Error loading stats:', err)
            }
        },

        async openRequestDetails(request) {
            try {
                const response = await fetch(`/php/admin/get_request_details.php?id=${request.id}`)
                const data = await response.json()
                
                if (!response.ok) {
                    throw new Error(data.error || 'Une erreur est survenue')
                }

                this.selectedRequest = data.request
                this.newStatus = data.request.status
                this.showRequestModal = true
                
            } catch (err) {
                console.error('Error loading request details:', err)
            }
        },

        async updateRequestStatus() {
            if (!this.selectedRequest || !this.newStatus) return

            try {
                const response = await fetch('/php/admin/update_request_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        request_id: this.selectedRequest.id,
                        new_status: this.newStatus,
                        comment: this.statusComment
                    })
                })

                const data = await response.json()
                
                if (!response.ok) {
                    throw new Error(data.error || 'Une erreur est survenue')
                }

                // Refresh request details and stats
                await this.openRequestDetails(this.selectedRequest)
                await this.loadStats()
                await this.loadRequests()

                this.statusComment = ''
                
            } catch (err) {
                console.error('Error updating status:', err)
            }
        },

        // Pagination methods
        get totalPages() {
            return Math.ceil(this.pagination.total / this.pagination.perPage)
        },

        get pageNumbers() {
            const pages = []
            for (let i = 1; i <= this.totalPages; i++) {
                pages.push(i)
            }
            return pages
        },

        get startIndex() {
            return (this.pagination.currentPage - 1) * this.pagination.perPage
        },

        get endIndex() {
            return this.startIndex + this.pagination.perPage
        },

        previousPage() {
            if (this.pagination.currentPage > 1) {
                this.pagination.currentPage--
                this.loadRequests()
            }
        },

        nextPage() {
            if (this.pagination.currentPage < this.totalPages) {
                this.pagination.currentPage++
                this.loadRequests()
            }
        },

        goToPage(page) {
            this.pagination.currentPage = page
            this.loadRequests()
        },

        // Utility methods
        truncate(text, length) {
            if (!text) return ''
            return text.length > length ? text.substring(0, length) + '...' : text
        },

        formatDate(date) {
            if (!date) return ''
            return new Date(date).toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })
        },

        formatBudget(budget) {
            if (!budget) return 'Non spécifié'
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(budget)
        },

        statusLabel(status) {
            const labels = {
                pending: 'En attente',
                approved: 'Approuvée',
                in_progress: 'En cours',
                completed: 'Complétée',
                rejected: 'Rejetée'
            }
            return labels[status] || status
        },

        priorityLabel(priority) {
            const labels = {
                high: 'Haute',
                medium: 'Moyenne',
                low: 'Basse'
            }
            return labels[priority] || priority
        },

        accountTypeLabel(type) {
            const labels = {
                individual: 'Particulier',
                freelance: 'Freelance',
                company: 'Entreprise'
            }
            return labels[type] || type
        }
    }
} 