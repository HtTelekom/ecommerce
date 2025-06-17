<template>
  <div class="login-container">
    <!-- Animated background -->
    <div class="background">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="particle particle-1"></div>
      <div class="particle particle-2"></div>
      <div class="particle particle-3"></div>
      <div class="particle particle-4"></div>
    </div>

    <!-- Login form -->
    <div class="login-card">
      <!-- Header -->
      <div class="header">
        <div class="logo">
          <div class="logo-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
        </div>
        <h1 class="title">Welcome Back</h1>
        <p class="subtitle">E-Commerce Admin Panel</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="form">
        <!-- Email Field -->
        <div class="input-group">
          <label for="email" class="label">Email Adresi</label>
          <div class="input-wrapper">
            <div class="input-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
              </svg>
            </div>
            <input id="email" v-model="form.email" type="email" :class="['input', { 'input-error': errors.email }]" placeholder="admin@example.com" :disabled="loading" @input="clearError('email')" />
            <div v-if="form.email && !errors.email && isValidEmail" class="success-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>
          <div v-if="errors.email" class="error-message">{{ errors.email }}</div>
        </div>

        <!-- Password Field -->
        <div class="input-group">
          <label for="password" class="label">Şifre</label>
          <div class="input-wrapper">
            <div class="input-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" :class="['input', { 'input-error': errors.password }]" placeholder="••••••••" :disabled="loading" @input="clearError('password')" />
            <button type="button" @click="togglePassword" class="password-toggle">
              <svg v-if="!showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
              </svg>
            </button>
          </div>
          <div v-if="form.password" class="password-strength">
            <div class="strength-bars">
              <div v-for="i in 4" :key="i" :class="['strength-bar', getPasswordStrengthClass(i)]"></div>
            </div>
            <span class="strength-text">{{ getPasswordStrengthText() }}</span>
          </div>
          <div v-if="errors.password" class="error-message">{{ errors.password }}</div>
        </div>

        <!-- Remember & Forgot -->
        <div class="form-options">
          <label class="checkbox-wrapper">
            <input v-model="form.remember" type="checkbox" :disabled="loading" />
            <span class="checkbox"></span>
            <span class="checkbox-text">Beni Hatırla</span>
          </label>
          <a href="#" class="forgot-link">Şifremi Unuttum?</a>
        </div>

        <!-- Message -->
        <div v-if="message" :class="['message', message.includes('✅') ? 'message-success' : 'message-error']">{{ message }}</div>

        <!-- Submit Button -->
        <button type="submit" :disabled="loading || !isFormValid" :class="['submit-btn', { 'submit-btn-disabled': loading || !isFormValid }]">
          <div v-if="loading" class="loading-spinner"></div>
          <span v-if="loading">Giriş yapılıyor...</span>
          <span v-else class="btn-content">
            <span>Giriş Yap</span>
            <svg class="btn-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </span>
        </button>
      </form>

      <!-- Divider -->
      <div class="divider"><span>Test Hesapları</span></div>

      <!-- Test Accounts -->
      <div class="test-accounts">
        <div v-for="(account, index) in testAccounts" :key="index" class="test-account" @click="fillCredentials(account.email, account.password)">
          <div :class="['account-avatar', account.role.toLowerCase().replace(' ', '')]">{{ account.role[0] }}</div>
          <div class="account-info">
            <div class="account-role">{{ account.role }}</div>
            <div class="account-email">{{ account.email }}</div>
          </div>
          <svg class="account-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </div>
      </div>

      <!-- Footer -->
      <div class="footer"><p>© 2024 E-Commerce Admin. Tüm hakları saklıdır.</p></div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'Login',
  setup() {
    const router = useRouter()
    const form = ref({ email: '', password: '', remember: false })
    const loading = ref(false)
    const showPassword = ref(false)
    const errors = ref({})
    const message = ref('')
    const testAccounts = ref([
      { role: 'Admin', email: 'admin@example.com', password: '123456' },
      { role: 'Super Admin', email: 'superadmin@example.com', password: '123456' },
      { role: 'User', email: 'user@example.com', password: '123456' }
    ])
    const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

    const isFormValid = computed(() => form.value.email && form.value.password && form.value.password.length >= 6 && validateEmail(form.value.email))
    const isValidEmail = computed(() => form.value.email && validateEmail(form.value.email))
    const passwordStrength = computed(() => {
      const password = form.value.password
      if (!password) return 0
      let strength = 0
      if (password.length >= 6) strength++
      if (password.length >= 8) strength++
      if (/[A-Z]/.test(password) && /[a-z]/.test(password)) strength++
      if (/[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) strength++
      return strength
    })

    const validateEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
    const togglePassword = () => showPassword.value = !showPassword.value
    const clearError = (field) => { if (errors.value[field]) { delete errors.value[field]; errors.value = { ...errors.value } } }
    const clearAllErrors = () => { errors.value = {}; message.value = '' }
    const fillCredentials = (email, password) => { form.value.email = email; form.value.password = password; clearAllErrors() }
    const getPasswordStrengthClass = (index) => {
      if (index <= passwordStrength.value) {
        if (passwordStrength.value <= 1) return 'strength-weak'
        if (passwordStrength.value <= 2) return 'strength-fair'
        if (passwordStrength.value <= 3) return 'strength-good'
        return 'strength-strong'
      }
      return 'strength-empty'
    }
    const getPasswordStrengthText = () => {
      if (passwordStrength.value <= 1) return 'Zayıf'
      if (passwordStrength.value <= 2) return 'Orta'
      if (passwordStrength.value <= 3) return 'Güçlü'
      return 'Çok Güçlü'
    }

    const validateForm = () => {
      clearAllErrors()
      const newErrors = {}
      if (!form.value.email) newErrors.email = 'Email adresi zorunludur'
      else if (!validateEmail(form.value.email)) newErrors.email = 'Geçerli bir email adresi giriniz'
      if (!form.value.password) newErrors.password = 'Şifre zorunludur'
      else if (form.value.password.length < 6) newErrors.password = 'Şifre en az 6 karakter olmalıdır'
      errors.value = newErrors
      return Object.keys(newErrors).length === 0
    }

    const handleLogin = async () => {
      if (!validateForm()) return
      loading.value = true
      message.value = ''
      try {
        const response = await fetch(`${API_BASE_URL}/auth/login`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ email: form.value.email, password: form.value.password })
        })
        const data = await response.json()
        if (data.success) {
          message.value = '✅ Giriş başarılı! Yönlendiriliyorsunuz...'
          localStorage.setItem('auth_token', data.data.token)
          localStorage.setItem('user', JSON.stringify(data.data.user))
          setTimeout(() => router.push('/dashboard'), 1500)
        } else {
          message.value = `❌ ${data.message}`
        }
      } catch (error) {
        console.error('Login error:', error)
        message.value = '❌ Bağlantı hatası. Sunucuya erişim sağlanamadı.'
      } finally {
        loading.value = false
      }
    }

    onMounted(() => { const emailInput = document.getElementById('email'); if (emailInput) emailInput.focus() })

    return { form, loading, showPassword, errors, message, testAccounts, isFormValid, isValidEmail, passwordStrength, togglePassword, clearError, fillCredentials, getPasswordStrengthClass, getPasswordStrengthText, handleLogin }
  }
}
</script>

<style scoped>
* { box-sizing: border-box; }
.login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; position: relative; overflow: hidden; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.background { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); z-index: 1; }
.shape { position: absolute; border-radius: 50%; filter: blur(40px); opacity: 0.7; }
.shape-1 { width: 300px; height: 300px; background: linear-gradient(45deg, #ff6b6b, #feca57); top: -150px; right: -150px; animation: float 6s ease-in-out infinite; }
.shape-2 { width: 400px; height: 400px; background: linear-gradient(45deg, #48cae4, #023e8a); bottom: -200px; left: -200px; animation: float 8s ease-in-out infinite reverse; }
.particle { position: absolute; background: rgba(255, 255, 255, 0.8); border-radius: 50%; pointer-events: none; }
.particle-1 { width: 4px; height: 4px; top: 20%; left: 20%; animation: float 6s ease-in-out infinite; }
.particle-2 { width: 6px; height: 6px; top: 60%; right: 30%; animation: float 8s ease-in-out infinite reverse; }
.particle-3 { width: 3px; height: 3px; bottom: 30%; left: 40%; animation: float 7s ease-in-out infinite; }
.particle-4 { width: 5px; height: 5px; top: 80%; right: 20%; animation: float 9s ease-in-out infinite reverse; }
@keyframes float { 0%, 100% { transform: translateY(0px) rotate(0deg); } 33% { transform: translateY(-30px) rotate(120deg); } 66% { transform: translateY(-60px) rotate(240deg); } }
.login-card { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 24px; padding: 2rem; width: 100%; max-width: 420px; position: relative; z-index: 2; box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1); }
.header { text-align: center; margin-bottom: 2rem; }
.logo { margin-bottom: 1.5rem; }
.logo-icon { width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 20px; display: flex; align-items: center; justify-content: center; position: relative; animation: pulse 2s infinite; }
.logo-icon::before { content: ''; position: absolute; inset: -2px; background: linear-gradient(135deg, #667eea, #764ba2, #667eea); border-radius: 22px; z-index: -1; filter: blur(6px); opacity: 0.5; }
.logo-icon svg { width: 40px; height: 40px; color: white; stroke-width: 2.5; }
.title { font-size: 2rem; font-weight: 700; color: white; margin: 0 0 0.5rem 0; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
.subtitle { color: rgba(255, 255, 255, 0.8); margin: 0; font-size: 1rem; }
.form { display: flex; flex-direction: column; gap: 1.5rem; }
.input-group { display: flex; flex-direction: column; gap: 0.5rem; }
.label { color: rgba(255, 255, 255, 0.9); font-weight: 500; font-size: 0.875rem; margin-bottom: 0.25rem; }
.input-wrapper { position: relative; display: flex; align-items: center; }
.input-icon { position: absolute; left: 1rem; width: 20px; height: 20px; color: rgba(255, 255, 255, 0.6); z-index: 2; }
.input-icon svg { width: 100%; height: 100%; }
.input { width: 100%; padding: 1rem 1rem 1rem 3rem; background: rgba(255, 255, 255, 0.1); border: 2px solid rgba(255, 255, 255, 0.2); border-radius: 16px; color: white; font-size: 1rem; transition: all 0.3s ease; backdrop-filter: blur(10px); }
.input::placeholder { color: rgba(255, 255, 255, 0.5); }
.input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2); background: rgba(255, 255, 255, 0.15); }
.input-error { border-color: #ef4444 !important; }
.input-error:focus { box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2) !important; }
.success-icon { position: absolute; right: 1rem; width: 20px; height: 20px; color: #10b981; z-index: 2; }
.success-icon svg { width: 100%; height: 100%; }
.password-toggle { position: absolute; right: 1rem; width: 20px; height: 20px; background: none; border: none; color: rgba(255, 255, 255, 0.6); cursor: pointer; transition: color 0.2s ease; z-index: 2; }
.password-toggle:hover { color: white; }
.password-toggle svg { width: 100%; height: 100%; }
.password-strength { display: flex; align-items: center; gap: 0.75rem; margin-top: 0.5rem; }
.strength-bars { display: flex; gap: 0.25rem; flex: 1; }
.strength-bar { height: 4px; flex: 1; border-radius: 2px; background: rgba(255, 255, 255, 0.2); transition: all 0.3s ease; }
.strength-weak { background: #ef4444; }
.strength-fair { background: #f59e0b; }
.strength-good { background: #3b82f6; }
.strength-strong { background: #10b981; }
.strength-text { font-size: 0.75rem; color: rgba(255, 255, 255, 0.8); min-width: 60px; }
.error-message { color: #fca5a5; font-size: 0.875rem; margin-top: 0.25rem; animation: slideIn 0.3s ease; }
.form-options { display: flex; justify-content: space-between; align-items: center; margin: 0.5rem 0; }
.checkbox-wrapper { display: flex; align-items: center; cursor: pointer; gap: 0.75rem; }
.checkbox-wrapper input { display: none; }
.checkbox { width: 20px; height: 20px; border: 2px solid rgba(255, 255, 255, 0.3); border-radius: 6px; position: relative; transition: all 0.3s ease; background: rgba(255, 255, 255, 0.1); }
.checkbox-wrapper input:checked + .checkbox { background: #667eea; border-color: #667eea; }
.checkbox-wrapper input:checked + .checkbox::after { content: '✓'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 12px; font-weight: bold; }
.checkbox-text { color: rgba(255, 255, 255, 0.9); font-size: 0.875rem; user-select: none; }
.forgot-link { color: #667eea; text-decoration: none; font-size: 0.875rem; transition: color 0.2s ease; }
.forgot-link:hover { color: #5a67d8; }
.message { padding: 1rem; border-radius: 12px; font-size: 0.875rem; text-align: center; animation: slideIn 0.3s ease; backdrop-filter: blur(10px); }
.message-success { background: rgba(16, 185, 129, 0.2); color: #a7f3d0; border: 1px solid rgba(16, 185, 129, 0.3); }
.message-error { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
.submit-btn { width: 100%; padding: 1rem; background: linear-gradient(135deg, #667eea, #764ba2); border: none; border-radius: 16px; color: white; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); }
.submit-btn:hover:not(.submit-btn-disabled) { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4); }
.submit-btn:active:not(.submit-btn-disabled) { transform: translateY(0); }
.submit-btn-disabled { opacity: 0.6; cursor: not-allowed; transform: none !important; }
.btn-content { display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
.btn-arrow { width: 20px; height: 20px; transition: transform 0.2s ease; }
.submit-btn:hover:not(.submit-btn-disabled) .btn-arrow { transform: translateX(4px); }
.loading-spinner { width: 20px; height: 20px; border: 2px solid rgba(255, 255, 255, 0.3); border-top: 2px solid white; border-radius: 50%; animation: spin 1s linear infinite; margin-right: 0.5rem; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.divider { margin: 2rem 0 1.5rem; text-align: center; position: relative; }
.divider::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: rgba(255, 255, 255, 0.2); }
.divider span { background: rgba(255, 255, 255, 0.1); padding: 0 1rem; color: rgba(255, 255, 255, 0.8); font-size: 0.875rem; backdrop-filter: blur(10px); }
.test-accounts { display: flex; flex-direction: column; gap: 0.75rem; }
.test-account { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; cursor: pointer; transition: all 0.3s ease; backdrop-filter: blur(10px); }
.test-account:hover { background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.2); transform: translateY(-2px); }
.account-avatar { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 1rem; }
.account-avatar.admin { background: linear-gradient(135deg, #ef4444, #dc2626); }
.account-avatar.superadmin { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.account-avatar.user { background: linear-gradient(135deg, #10b981, #059669); }
.account-info { flex: 1; }
.account-role { color: white; font-weight: 500; font-size: 0.875rem; }
.account-email { color: rgba(255, 255, 255, 0.7); font-size: 0.75rem; }
.account-arrow { width: 20px; height: 20px; color: rgba(255, 255, 255, 0.5); transition: all 0.2s ease; }
.test-account:hover .account-arrow { color: white; transform: translateX(4px); }
.footer { text-align: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.1); }
.footer p { color: rgba(255, 255, 255, 0.6); font-size: 0.75rem; margin: 0; }
@keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
@media (max-width: 480px) { .login-card { padding: 1.5rem; margin: 1rem; } .title { font-size: 1.75rem; } .logo-icon { width: 60px; height: 60px; } .logo-icon svg { width: 30px; height: 30px; } .test-account { padding: 0.75rem; } .account-avatar { width: 35px; height: 35px; font-size: 0.875rem; } }
@media (prefers-color-scheme: dark) { .login-card { background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.1); } }
@media (prefers-contrast: high) { .input { border-width: 3px; } .submit-btn { border: 2px solid white; } }
</style>