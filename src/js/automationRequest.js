export default function automationRequest() {
    return {
        loading: false,
        error: null,
        dragover: false,
        showSuccessModal: false,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
        
        form: {
            title: '',
            description: '',
            budget: '',
            deadline: '',
        },

        files: [],
        
        // Computed property for minimum date (today)
        get minDate() {
            const today = new Date()
            return today.toISOString().split('T')[0]
        },

        // File handling methods
        handleFileSelect(event) {
            this.addFiles(event.target.files)
        },

        handleDrop(event) {
            this.dragover = false
            this.addFiles(event.dataTransfer.files)
        },

        addFiles(fileList) {
            const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf']
            const maxSize = 10 * 1024 * 1024 // 10MB

            Array.from(fileList).forEach(file => {
                if (!allowedTypes.includes(file.type)) {
                    this.error = `Le type de fichier ${file.type} n'est pas autorisé`
                    return
                }

                if (file.size > maxSize) {
                    this.error = `Le fichier ${file.name} dépasse la taille maximale de 10MB`
                    return
                }

                this.files.push(file)
            })
        },

        removeFile(index) {
            this.files.splice(index, 1)
        },

        async handleSubmit() {
            this.loading = true
            this.error = null

            try {
                const formData = new FormData()
                
                // Add form fields
                Object.entries(this.form).forEach(([key, value]) => {
                    if (value) formData.append(key, value)
                })
                
                // Add files
                this.files.forEach(file => {
                    formData.append('files[]', file)
                })

                // Add CSRF token
                formData.append('csrf_token', this.csrfToken)

                const response = await fetch('/php/submit_request.php', {
                    method: 'POST',
                    body: formData
                })

                const data = await response.json()

                if (!response.ok) {
                    throw new Error(data.error || 'Une erreur est survenue')
                }

                // Show success modal
                this.showSuccessModal = true
                
                // Reset form
                this.form = {
                    title: '',
                    description: '',
                    budget: '',
                    deadline: ''
                }
                this.files = []

            } catch (err) {
                this.error = err.message
                console.error('Submit error:', err)
            } finally {
                this.loading = false
            }
        }
    }
} 