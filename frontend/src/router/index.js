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
import UserListView from "@/views/user/UserListView.vue";
import WorkerListView from "@/views/worker/WorkerListView.vue";
import NewWorkerView from "@/views/worker/NewWorkerView.vue";
import WorkerDetailView from "@/views/worker/WorkerDetailView.vue";
import EditWorkerView from "@/views/worker/EditWorkerView.vue";
import NewTreatmentView from "@/views/treatment/NewTreatmentView.vue";
import TreatmentListView from "@/views/treatment/TreatmentListView.vue";
import EditTreatmentView from "@/views/treatment/EditTreatmentView.vue";
import TreatmentDetailView from "@/views/treatment/TreatmentDetailView.vue";
import NewDoctorView from "@/views/doctor/NewDoctorView.vue";
import DoctorListView from "@/views/doctor/DoctorListView.vue";
import SettingManagementView from "@/views/setting/SettingManagementView.vue";
import DoctorDetailView from "@/views/doctor/DoctorDetailView.vue";
import EditDoctorView from "@/views/doctor/EditDoctorView.vue";
import EditDoctorAvailabilities from "@/views/doctor/EditDoctorAvailabilities.vue";
import PrepareAppointmentView from "@/views/appointment/PrepareAppointmentView.vue";
import NewPatientView from "@/views/patient/NewPatientView.vue";
import SalesModuleView from "@/views/sales/SalesModuleView.vue";
import PatientListView from "@/views/patient/PatientListView.vue";
import AppointmentListView from "@/views/appointment/AppointmentListView.vue";
import EditPatientView from "@/views/patient/EditPatientView.vue";
import PatientDetailView from "@/views/patient/PatientDetailView.vue";
import AppointmentDetailView from "@/views/appointment/AppointmentDetailView.vue";

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
                  path: 'user-list',
                  name: 'user-list',
                  component: UserListView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR']}
                },
                {
                  path: 'new-worker',
                  name: 'new-worker',
                  component: NewWorkerView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR',]},
                },
                {
                  path: 'worker-list',
                  name: 'worker-list',
                  component: WorkerListView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR','SECRETARIA']},
                },
                {
                  path: 'worker-detail/:id',
                  name: 'worker-detail',
                  component: WorkerDetailView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA']},
                },
                {
                  path: 'edit-worker/:id',
                  name: 'edit-worker',
                  component: EditWorkerView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR']}
                },
                {
                    path: 'new-treatment',
                    name: 'new-treatment',
                    component: NewTreatmentView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA']},
                },
                {
                  path: 'treatment-list',
                  name: 'treatment-list',
                  component: TreatmentListView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR','SECRETARIA','ENFERMERA','DOCTOR']},
                },
                {
                  path: 'edit-treatment/:id',
                  name: 'edit-treatment',
                  component: EditTreatmentView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA']},
                },
                {
                    path: 'treatment-detail/:id',
                    name: 'treatment-detail',
                    component: TreatmentDetailView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA','ENFERMERA','DOCTOR']}
                },
                {
                  path: 'new-doctor',
                  name: 'new-doctor',
                  component: NewDoctorView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR']}
                },
                {
                    path: 'doctor-list',
                    name: 'doctor-list',
                    component: DoctorListView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR','SECRETARIA','ENFERMERA']}
                },
                {
                  path: 'doctor-detail/:id',
                  name: 'doctor-detail',
                  component: DoctorDetailView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR','SECRETARIA','ENFERMERA']}
                },
                {
                  path: 'edit-doctor/:id',
                  name: 'edit-doctor',
                  component: EditDoctorView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA']},
                },
                {
                    path: 'edit-doctor-availabilities/:id',
                    name: 'edit-doctor-schedule',
                    component: EditDoctorAvailabilities,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA']},
                },
                {
                    path: 'new-appointment',
                    name: 'new-appointment',
                    component: PrepareAppointmentView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA','ENFERMERA']}
                },
                {
                  path: 'appointment-list',
                  name: 'appointment-list',
                  component: AppointmentListView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA','ENFERMERA','DOCTOR']}
                },
                {
                  path: 'appointment-detail/:id',
                  name: 'appointment-detail',
                  component: AppointmentDetailView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA','ENFERMERA','DOCTOR']}
                },
                {
                    path: 'new-patient',
                    name: 'new-patient',
                    component: NewPatientView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA','ENFERMERA']}
                },
                {
                    path: 'patient-list',
                    name: 'patient-list',
                    component: PatientListView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA','ENFERMERA','DOCTOR']}
                },
                {
                  path: 'edit-patient/:id',
                  name: 'edit-patient',
                  component: EditPatientView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA','ENFERMERA']}
                },
                {
                  path: 'patient-detail/:id',
                  name: 'patient-detail',
                  component: PatientDetailView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA','ENFERMERA','DOCTOR']}
                },
                {
                  path: 'sell-products',
                  name: 'sell-products',
                  component: SalesModuleView,
                  meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA','ENFERMERA']}
                },
                {
                    path: 'my-account',
                    name: 'my-account',
                    component: MyAccountView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR', 'SECRETARIA','ENFERMERA','DOCTOR']}
                },
                {
                    path: 'settings',
                    name: 'settings',
                    component: SettingManagementView,
                    meta: {requiresAuth: true, roles: ['ADMINISTRADOR']}
                },
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
