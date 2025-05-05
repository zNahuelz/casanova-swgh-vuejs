import {createRouter, createWebHistory} from 'vue-router'
import LoginView from '@/views/auth/LoginView.vue'
import DashboardView from '@/views/shared/DashboardView.vue'
import ManagementLayout from '@/components/layout/ManagementLayout.vue'
import NewSupplierView from '@/views/supplier/NewSupplierView.vue'
import SupplierListView from '@/views/supplier/SupplierListView.vue'
import {useAuthStore} from '@/stores/auth.js'
import EditSupplierView from "@/views/supplier/EditSupplierView.vue";
import PresentationListView from "@/views/presentation/PresentationListView.vue";
import NewMedicineView from "@/views/medicine/NewMedicineView.vue";
import MedicineListView from "@/views/medicine/MedicineListView.vue";
import MedicineDetailView from "@/views/medicine/MedicineDetailView.vue";
import EditMedicineView from "@/views/medicine/EditMedicineView.vue";
import LostPasswordView from "@/views/auth/LostPasswordView.vue";
import MyAccountView from "@/views/auth/MyAccountView.vue";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/',
            name: 'login',
            component: LoginView,
        },
        {
            path: '/recover-account',
            name: 'recover-account',
            component: LostPasswordView,
        },
        {
            path: '/a',
            component: ManagementLayout,
            meta: {requiresAuth: true, roles: ['ADMINISTRADOR']},
            children: [
                {
                    path: '',
                    name: 'dashboard',
                    component: DashboardView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR']},
                },
                {
                    path: 'new-supplier',
                    name: 'new-supplier',
                    component: NewSupplierView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA', 'ENFERMERA']},
                },
                {
                    path: 'supplier-list',
                    name: 'supplier-list',
                    component: SupplierListView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA']},
                },
                {
                    path: 'edit-supplier/:id',
                    name: 'edit-supplier',
                    component: EditSupplierView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA']},
                },
                {
                    path: 'presentation-list',
                    name: 'presentation-list',
                    component: PresentationListView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA']},
                },
                {
                    path: 'new-medicine',
                    name: 'new-medicine',
                    component: NewMedicineView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA']},
                },
                {
                    path: 'medicine-list',
                    name: 'medicine-list',
                    component: MedicineListView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA', 'ENFERMERA', 'DOCTOR']}
                },
                {
                    path: 'medicine-detail/:id',
                    name: 'medicine-detail',
                    component: MedicineDetailView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA', 'ENFERMERA', 'DOCTOR']}
                },
                {
                    path: 'edit-medicine/:id',
                    name: 'edit-medicine',
                    component: EditMedicineView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA']},
                },
                {
                    path: 'my-account',
                    name: 'my-account',
                    component: MyAccountView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA','ENFERMERA','DOCTOR']}
                }
            ]
        },
        {
            path: '/d',
            component: ManagementLayout,
            meta: {requiresAuth: true, roles: ['DOCTOR']},
            children: [
                {
                    path: '',
                    name: 'doctor-dashboard',
                    component: DashboardView,
                    meta: {requiresAuth: true, roles: ['DOCTOR']},
                }
            ]
        },
        {
            path: '/e',
            component: ManagementLayout,
            meta: {requiresAuth: true, roles: ['ENFERMERA']},
            children: [
                {
                    path: '',
                    name: 'nurse-dashboard',
                    component: DashboardView,
                    meta: {requiresAuth: true, roles: ['ENFERMERA']},
                }
            ]
        },
        {
            path: '/s',
            component: ManagementLayout,
            meta: {requiresAuth: true, roles: ['SECRETARIA']},
            children: [
                {
                    path: '',
                    name: 'secretary-dashboard',
                    component: DashboardView,
                    meta: {requiresAuth: true, roles: ['SECRETARIA']},
                }
            ]
        },
    ],
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const redirectIfAuthenticated = ['login','recover-account'];
    // If trying to access login page but already authenticated
    if (redirectIfAuthenticated.includes(to.name) && authStore.isAuthenticated()) { //to.name === 'login'
        const userRole = authStore.getTokenDetails().role;
        // Redirect to appropriate dashboard based on role
        if (userRole === 'ADMINISTRADOR') next('/a');
        else if (userRole === 'DOCTOR') next('/d');
        else if (userRole === 'ENFERMERA') next('/e');
        else if (userRole === 'SECRETARIA') next('/s');
        else next('/');
        return;
    }

    // Protected route logic
    if (to.meta.requiresAuth && !authStore.isAuthenticated()) {
        next({name: 'login'});
    } else if (to.meta.roles) {
        const userRole = authStore.getTokenDetails().role;
        if (to.meta.roles.includes(userRole)) {
            next();
        } else {
            // Unauthorized access
            if (userRole === 'ADMINISTRADOR') next('/a');
            else if (userRole === 'DOCTOR') next('/d');
            else if (userRole === 'ENFERMERA') next('/e');
            else if (userRole === 'SECRETARIA') next('/s');
            else next({name: 'login'});
        }
    } else {
        next();
    }
});

export default router
