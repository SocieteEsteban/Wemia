export default function registration() {
    return {
        accountType: 'individual',
        loading: false,
        error: null,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
        
        form: {
            firstName: '',
            lastName: '',
            email: '',
            password: '',
            passwordConfirm: '',
            companyName: ''
        },

        async handleSubmit() {
            this.loading = true
            this.error = null

            // Validation basique
            if (this.form.password !== this.form.passwordConfirm) {
                this.error = 'Les mots de passe ne correspondent pas'
                this.loading = false
                return
            }

            if (this.form.password.length < 8) {
                this.error = 'Le mot de passe doit contenir au moins 8 caractères'
                this.loading = false
                return
            }

            if (this.accountType === 'company' && !this.form.companyName) {
                this.error = 'Le nom de l\'entreprise est obligatoire pour un compte entreprise'
                this.loading = false
                return
            }

            try {
                const response = await fetch('/php/register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        ...this.form,
                        accountType: this.accountType,
                        csrf_token: this.csrfToken
                    })
                })

                const data = await response.json()

                if (!response.ok) {
                    throw new Error(data.error || 'Une erreur est survenue')
                }

                // Stockage des informations utilisateur
                Alpine.store('auth').login(data.user)

                // Redirection vers la page d'accueil
                window.location.href = '/'

            } catch (err) {
                this.error = err.message
                console.error('Registration error:', err)
            } finally {
                this.loading = false
            }
        }
    }
} 