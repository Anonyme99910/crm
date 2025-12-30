import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/Home.vue'),
  },
  {
    path: '/portfolio/:id',
    name: 'PortfolioDetail',
    component: () => import('../views/PortfolioDetail.vue'),
  },
  {
    path: '/admin/login',
    name: 'AdminLogin',
    component: () => import('../views/admin/Login.vue'),
  },
  {
    path: '/admin',
    component: () => import('../layouts/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'AdminDashboard',
        component: () => import('../views/admin/Dashboard.vue'),
      },
      {
        path: 'sections',
        name: 'AdminSections',
        component: () => import('../views/admin/Sections.vue'),
      },
      {
        path: 'portfolio',
        name: 'AdminPortfolio',
        component: () => import('../views/admin/Portfolio.vue'),
      },
      {
        path: 'services',
        name: 'AdminServices',
        component: () => import('../views/admin/Services.vue'),
      },
      {
        path: 'testimonials',
        name: 'AdminTestimonials',
        component: () => import('../views/admin/Testimonials.vue'),
      },
      {
        path: 'settings',
        name: 'AdminSettings',
        component: () => import('../views/admin/Settings.vue'),
      },
      {
        path: 'contact-submissions',
        name: 'AdminContactSubmissions',
        component: () => import('../views/admin/ContactSubmissions.vue'),
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory('/hazem/'),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth',
      };
    } else {
      return { top: 0, behavior: 'smooth' };
    }
  },
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'AdminLogin' });
  } else if (to.name === 'AdminLogin' && authStore.isAuthenticated) {
    next({ name: 'AdminDashboard' });
  } else {
    next();
  }
});

export default router;
