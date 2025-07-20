import axios from 'axios'

const api = axios.create({
  baseURL: '/public/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
})

// Intercepteur pour ajouter les credentials de session
api.interceptors.request.use(
  config => {
    // Ajouter les cookies de session pour l'authentification PHP
    config.withCredentials = true
    return config
  },
  error => Promise.reject(error)
)

// Intercepteurs pour la gestion des erreurs
api.interceptors.response.use(
  response => response,
  error => {
    console.error('Erreur API:', error)
    
    // Gestion des erreurs d'authentification
    if (error.response?.status === 401) {
      // Rediriger vers la page de connexion PHP
      window.location.href = '/public/login.php'
    }
    
    return Promise.reject(error)
  }
)

// Services API
export const studentsAPI = {
  getAll: (params = {}) => api.get('/students.php', { params }),
  create: (data) => api.post('/students.php', data),
  update: (data) => api.put('/students.php', data),
  delete: (id) => api.delete('/students.php', { data: { id } }),
  search: (search, page = 1, limit = 50) => api.get('/students.php', { 
    params: { search, page, limit } 
  })
}

export const sallesAPI = {
  getAll: () => api.get('/salles.php'),
  create: (data) => api.post('/salles.php', data),
  update: (data) => api.put('/salles.php', data),
  delete: (id) => api.delete('/salles.php', { data: { id } })
}

export const autorisationsAPI = {
  getAll: () => api.get('/autorisations.php'),
  createIndividual: (data) => api.post('/autorisations.php', { ...data, type: 'individual' }),
  createGroup: (data) => api.post('/autorisations.php', { ...data, type: 'group' }),
  delete: (id) => api.delete('/autorisations.php', { data: { id } })
}

export const accessAPI = {
  verify: (matricule, salle_id) => api.get(`/verifier_acces.php?matricule=${matricule}&salle_id=${salle_id}`)
}

export const dashboardAPI = {
  getStats: () => api.get('/dashboard_stats.php'),
  getRecentAccess: (limit = 10) => api.get('/recent_access.php', { params: { limit } }),
  getRecentAuthorizations: (limit = 5) => api.get('/recent_authorizations.php', { params: { limit } })
}

export const ucbAPI = {
  getStudent: (matricule) => axios.get(`https://akhademie.ucbukavu.ac.cd/api/v1/school-students/read-by-matricule?matricule=${matricule}`),
  getFaculties: () => axios.get('https://akhademie.ucbukavu.ac.cd/api/v1/school/entity-main-list?entity_id=undefined&promotion_id=1&traditional=undefined')
}

// Service d'authentification
export const authAPI = {
  login: (credentials) => api.post('/auth/login.php', credentials),
  logout: () => api.post('/auth/logout.php'),
  checkAuth: () => api.get('/auth/check.php')
}

export default api