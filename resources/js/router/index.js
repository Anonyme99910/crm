import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/auth/Login.vue'),
        meta: { guest: true }
    },
    {
        path: '/',
        component: () => import('../layouts/MainLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            { path: '', redirect: '/dashboard' },
            { path: 'dashboard', name: 'dashboard', component: () => import('../views/Dashboard.vue') },
            
            // Leads
            { path: 'leads', name: 'leads', component: () => import('../views/leads/LeadsList.vue') },
            { path: 'leads/create', name: 'leads.create', component: () => import('../views/leads/LeadForm.vue') },
            { path: 'leads/:id', name: 'leads.show', component: () => import('../views/leads/LeadDetails.vue') },
            { path: 'leads/:id/edit', name: 'leads.edit', component: () => import('../views/leads/LeadForm.vue') },
            
            // Site Visits
            { path: 'site-visits', name: 'site-visits', component: () => import('../views/site-visits/SiteVisitsList.vue') },
            { path: 'site-visits/create', name: 'site-visits.create', component: () => import('../views/site-visits/SiteVisitForm.vue') },
            { path: 'site-visits/:id', name: 'site-visits.show', component: () => import('../views/site-visits/SiteVisitDetails.vue') },
            
            // Quotations
            { path: 'quotations', name: 'quotations', component: () => import('../views/quotations/QuotationsList.vue') },
            { path: 'quotations/create', name: 'quotations.create', component: () => import('../views/quotations/QuotationForm.vue') },
            { path: 'quotations/:id', name: 'quotations.show', component: () => import('../views/quotations/QuotationDetails.vue') },
            { path: 'quotations/:id/edit', name: 'quotations.edit', component: () => import('../views/quotations/QuotationForm.vue') },
            
            // Projects
            { path: 'projects', name: 'projects', component: () => import('../views/projects/ProjectsList.vue') },
            { path: 'projects/create', name: 'projects.create', component: () => import('../views/projects/ProjectForm.vue') },
            { path: 'projects/:id', name: 'projects.show', component: () => import('../views/projects/ProjectDetails.vue') },
            
            // Inventory
            { path: 'inventory', name: 'inventory', component: () => import('../views/inventory/InventoryList.vue') },
            { path: 'inventory/create', name: 'inventory.create', component: () => import('../views/inventory/MaterialForm.vue') },
            
            // Suppliers
            { path: 'suppliers', name: 'suppliers', component: () => import('../views/suppliers/SuppliersList.vue') },
            { path: 'suppliers/create', name: 'suppliers.create', component: () => import('../views/suppliers/SupplierForm.vue') },
            { path: 'suppliers/:id', name: 'suppliers.show', component: () => import('../views/suppliers/SupplierDetails.vue') },
            { path: 'purchase-orders', name: 'purchase-orders', component: () => import('../views/suppliers/PurchaseOrdersList.vue') },
            
            // Contracts
            { path: 'contracts', name: 'contracts', component: () => import('../views/contracts/ContractsList.vue') },
            { path: 'contracts/create', name: 'contracts.create', component: () => import('../views/contracts/ContractForm.vue') },
            { path: 'contracts/:id', name: 'contracts.show', component: () => import('../views/contracts/ContractDetails.vue') },
            
            // Finance
            { path: 'invoices', name: 'invoices', component: () => import('../views/finance/InvoicesList.vue') },
            { path: 'invoices/create', name: 'invoices.create', component: () => import('../views/finance/InvoiceForm.vue') },
            { path: 'payments', name: 'payments', component: () => import('../views/finance/PaymentsList.vue') },
            { path: 'expenses', name: 'expenses', component: () => import('../views/finance/ExpensesList.vue') },
            
            // Tasks
            { path: 'tasks', name: 'tasks', component: () => import('../views/tasks/TasksList.vue') },
            { path: 'tasks/create', name: 'tasks.create', component: () => import('../views/tasks/TaskForm.vue') },
            { path: 'tasks/:id', name: 'tasks.show', component: () => import('../views/tasks/TaskDetails.vue') },
            
            // Reports
            { path: 'reports', name: 'reports', component: () => import('../views/reports/ReportsIndex.vue') },
            
            // Users
            { path: 'users', name: 'users', component: () => import('../views/users/UsersList.vue') },
            
            // Settings
            { path: 'settings', name: 'settings', component: () => import('../views/Settings.vue') },
        ]
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('../views/NotFound.vue')
    }
];

const router = createRouter({
    history: createWebHistory('/crm/public/'),
    routes
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login');
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next('/dashboard');
    } else {
        next();
    }
});

export default router;
