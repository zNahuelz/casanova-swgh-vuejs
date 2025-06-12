export const SUCCESS_MESSAGES = {
    SUCCESS_TAG: 'Operación completada.',
    SUPPLIER_CREATED: 'Proveedor creado correctamente.',
    PRESENTATION_CREATED: 'Presentación creada correctamente.',
    MEDICINE_CREATED: 'Medicamento creado correctamente.',
    CONFIRM_OPERATION: 'Confirmar operación'
}

export const ERROR_MESSAGES = {
    ERROR_TAG: 'Oops! Ha sucedido un error.',
    RUC_TAKEN: 'El RUC ingresado ya se encuentra en uso por otro proveedor.',
    SERVER_ERROR: 'Error interno del servidor. Intente nuevamente, si el problema persiste comuniquese con administración.',
    SUPPLIER_NOT_FOUND: 'Proveedor no encontrado. Intente nuevamente, si el problema persiste comuniquese con administración.',
    SEARCH_ERROR: 'Oops! Error de busqueda.',
    DUPLICATED_PRESENTATION: 'Ya existe una presentación con los datos ingresados.',
    BARCODE_GENERATION_ERROR: 'La generación de código de barras ha fallado. Intente nuevamente, si el problema persiste comuniquese con administración.',
    BARCODE_TAKEN: 'El código de barras ingresado ya se encuentra asignado a un medicamento.',
    IGV_VAL_NOT_LOADED: 'No se pudo recuperar el valor del IGV desde la configuración. Se utilizará el valor predeterminado de 18%. Intente nuevamente, si el problema persiste comuniquese con administración.',
    MEDICINE_NOT_FOUND: 'Medicamento no encontrado. Intente nuevamente, si el problema persiste comuniquese con administración',
    INVALID_RECOVERY_TOKEN: 'Oops! El enlace de recuperación de cuenta es invalido o ha expirado. Intente nuevamente, si el problema persiste comuniquese con administración',
    EXPIRED_RECOVERY_TOKEN: 'El enlace de recuperación de cuenta ha expirado. Intente nuevamente, si el problema persiste comuniquese con administración',
    EMAIL_TAKEN: 'El correo electrónico ingresado ya se encuentra vinculado a otra cuenta del sistema.',
    USERNAME_TAKEN: 'El nombre de usuario ingresado ya se encuentra vinculado a otra cuenta del sistema.',
    DNI_TAKEN: 'El DNI ingresado ya se encuentra en uso por otro trabajador.',
    WORKER_NOT_FOUND: 'Trabajador no encontrado. Intente nuevamente, si el problema persiste comuniquese con administración',
    TREATMENT_NAME_TAKEN: 'El nombre ingresado ya se encuentra en uso por otro tratamiento.',
    TREATMENT_NOT_FOUND: 'El tratamiento solicitado no se encuentra disponible o no existe. Vuelva a intentarlo.',
    INVALID_WORK_SCHEDULE: 'El horario de trabajo asignado no es válido. Vuelva a intentarlo.',
    WEEKENDS_CONFIG_NOT_FOUND: 'La llave "ESTADO_TRABAJO_FINDES" no se encuentra definida en la configuración del sistema. Un administrador puede restaurarla, hasta entonces el sistema utilizará la configuración predeterminada: "NO SE PERMITE ASIGNAR HORARIO DE TRABAJO LOS DÍAS SABADO-DOMINGO". Comuniquese con administración.',
    DOCTOR_NOT_FOUND: 'Doctor no encontrado. Intente nuevamente, si el problema persiste comuniquese con administración.',
    UNAVAILABILITY_OVERLAP: 'Ya existe una indisponibilidad que se solapa con el rango de fechas ingresado.',
    INCORRECT_SCHEDULE: 'El horario de trabajo asignado presenta errores. Verifique que los tiempos de trabajo sean adecuados y los espacios de descanso se encuentren dentro del horario laboral. Intente nuevamente, si el problema persiste, comuníquese con administración.',
    DOCTORS_NOT_LOADED: 'Error en la carga de doctores con horario disponible. Verifique que algún doctor tenga horario disponible. Intente nuevamente.',
    EMPTY_DOCTORS_TABLE: 'No se encontraron doctores registrados en el sistema. Realice el ingreso de algunos o comuniquese con administración.',
    DNI_PATIENT_NOT_FOUND: 'No se encontraron pacientes registrados con el DNI ingresado. Vuelva a intentarlo o registre al paciente.',
    APPOINTMENT_CREATION_ERROR: 'Error durante la reserva de citas. Vuelva a intentarlo, si el problema continua comuniquese con administración',
    DEFAULT_PATIENT_NOT_AVAILABLE: 'El DNI ingresado pertenece al paciente genérico; por tanto, no puede realizar la reserva de cita. Todo paciente debe reservar la cita con su número de DNI personal, intente nuevamente.',
    PATIENT_DNI_TAKEN: 'El DNI ingresado ya se encuentra registrado en el sistema.',
    PATIENT_EMAIL_TAKEN: 'El correo electrónico ingresado ya se encuentra en uso por otro paciente.',
    PATIENT_NOT_FOUND: 'Paciente no encontrado. Intente nuevamente, si el problema persiste comuniquese con administración.',
    APPOINTMENT_NOT_FOUND: 'Cita no encontrada. Intente nuevamente, si el problema persiste comuniquese con administración.',
    INVALID_APPOINTMENT_PREPARATION: 'Los datos del formulario son invalidos, la busqueda no es posible. Comuniquese con administración.',
    PAYMENT_TYPES_NOT_LOADED: 'Error en la busqueda de tipos de pagos. El módulo de ventas se encuentra deshabilitado. Comuniquese con el área de sistemas.',
    INVALID_CART_ITEMS_REMOVED: 'Algunos elementos del carrito de compras no se encuentran disponibles y fueron eliminados del mismo automáticamente. Verifique el listado y notifique al cliente. Puede proceder al pago con normalidad.',
    VOUCHER_DETAIL_NOT_FOUND: 'Detalle de voucher no encontrado. Vuelva a intentarlo o comuniquese con administración.',
    INVALID_NOTES_FORMAT: 'Debe llenar el campo notas con el formato correcto, intente nuevamente.',
}

export const SUPPLIER_SEARCH_MODES = [
    {value: 'id', label: 'POR ID'},
    {value: 'name', label: 'POR NOMBRE'},
    {value: 'ruc', label: 'POR RUC'},
]

export const PRESENTATION_SEARCH_MODES = [
    {value: 'id', label: 'POR ID'},
    {value: 'name', label: 'POR NOMBRE'},
    {value: 'aux', label: 'POR AUXILIAR'},
]

export const MEDICINE_SEARCH_MODES = [
    {value: 'id', label: 'POR ID'},
    {value: 'name', label: 'POR NOMBRE'},
    {value: 'composition', label: 'POR COMPOSICIÓN'},
    {value: 'description', label: 'POR DESCRIPCIÓN'},
    {value: 'barcode', label: 'POR CÓDIGO DE BARRAS'}
]

export const USER_SEARCH_MODES = [
    {value: 'id', label: 'POR ID'},
    {value: 'username', label: 'POR NOMBRE DE USUARIO'},
    {value: 'email', label: 'POR E-MAIL'}
]

export const WORKER_SEARCH_MODES = [
    {value: 'id', label: 'POR ID'},
    {value: 'name', label: 'POR NOMBRE'},
    {value: 'dni', label: 'POR DNI'},
    {value: 'position', label: 'POR CARGO'}
]

export const TREATMENT_SEARCH_MODES = [
    {value: 'id', label: 'POR ID'},
    {value: 'name', label: 'POR NOMBRE'},
    {value: 'description', label: 'POR DESCRIPCIÓN'},
    {value: 'procedure', label: 'POR PROCEDIMIENTO'}
]

export const PATIENT_SEARCH_MODES = [
    {value: 'id', label: 'POR ID'},
    {value: 'name', label: 'POR NOMBRE'},
    {value: 'dni', label: 'POR DNI'},
]

export const DOCTOR_SEARCH_MODES = [
    {value: 'id', label: 'POR ID'},
    {value: 'name', label: 'POR NOMBRE'},
    {value: 'dni', label: 'POR DNI'},
]

export const APPOINTMENT_SEARCH_MODES = [
    {value: 'id', label: 'POR ID'},
    {value: 'patient_dni', label: 'POR DNI - PACIENTE'},
    {value: 'doctor_dni', label: 'POR DNI - DOCTOR'},
    {value: 'status', label: 'POR ESTADO'},
    {value: 'date', label: 'POR FECHA'},
    {value: 'date_from', label: 'POR FECHA - EN ADELANTE'}
]

export const APPOINTMENT_STATUS_SEARCH_MODES = [
    {value: '', label: '- ESTADO DE CITA -'},
    {value: 'PENDIENTE', label: 'SOLO PENDIENTES'},
    {value: 'ATENDIDO', label: 'SOLO ATENDIDAS'},
    {value: 'REPROGRAMADO', label: 'SOLO REPROGRAMADAS'},
    {value: 'CANCELADO', label: 'SOLO CANCELADAS'},
    {value: 'NO_ASISTIO', label: 'SOLO NO ASISTIDAS'},
]

export const VOUCHER_SEARCH_MODES = [
    {value: 'id', label: 'POR ID'},
    {value: 'correlative', label: 'POR CORRELATIVO'},
    {value: 'set', label: 'POR SERIE'},
    {value: 'dni', label: 'POR DNI PACIENTE'},
];

export const WORKER_POSITIONS = [
    {value: 'SECRETARIA', label: 'SECRETARIA'},
    {value: 'ENFERMERA', label: 'ENFERMERA'},
]

export const DEFAULT_DOCTOR_AVAILABILITIES = [
    {weekday: 1, label: 'LUNES', start_time: '09:00', end_time: '18:00', break_start: '13:00', break_end: '13:40', is_active: true},
    {weekday: 2, label: 'MARTES', start_time: '09:00', end_time: '18:00', break_start: '13:00', break_end: '13:40', is_active: true},
    {weekday: 3, label: 'MIERCOLES', start_time: '09:00', end_time: '18:00', break_start: '13:00', break_end: '13:40', is_active: true},
    {weekday: 4, label: 'JUEVES', start_time: '09:00', end_time: '18:00', break_start: '13:00', break_end: '13:40', is_active: true},
    {weekday: 5, label: 'VIERNES', start_time: '09:00', end_time: '18:00', break_start: '13:00', break_end: '13:40', is_active: true},
    {weekday: 6, label: 'SABADO', start_time: '09:00', end_time: '18:00', break_start: '13:00', break_end: '13:40', is_active: true},
    {weekday: 7, label: 'DOMINGO', start_time: '09:00', end_time: '18:00', break_start: '13:00', break_end: '13:40', is_active: true},
]

export const WEEKDAY_NAMES = [
    {weekday: 1, name: 'LUNES'},
    {weekday: 2, name: 'MARTES'},
    {weekday: 3, name: 'MIERCOLES'},
    {weekday: 4, name: 'JUEVES'},
    {weekday: 5, name: 'VIERNES'},
    {weekday: 6, name: 'SABADO'},
    {weekday: 7, name: 'DOMINGO'},
]

export const UNAVAILABILITY_REASONS = [
    {reason: 'VACACIONES'},
    {reason: 'ENFERMEDAD'},
    {reason: 'DÍA LIBRE'},
    {reason: 'MATERNIDAD'},
    {reason: 'NO ESPECIFICADO'},
    {reason: 'RETIRADO'}
]

export const APPOINTMENT_TYPE = [
    {value: 30, label: 'PRIMERA CITA - 30Min.'},
    {value: 25, label: 'GENERAL - 25Min.'}
]

export const APPOINTMENT_SEARCH_LENGTH = [
    {value: 7, label: 'ESTÁ SEMANA'},
    {value: 15, label: '15 DÍAS'},
    {value: 30, label: '30 DÍAS'},
    {value: 60, label: 'DOS MESES'},
    {value: 90, label: 'TRES MESES'},
]

export const APPOINTMENT_STATUS = {
    PENDING: 'PENDIENTE',
    ATTENDED: 'ATENDIDO',
    RESCHEDULED: 'REPROGRAMADO',
    NOT_ATTENDED: 'NO_ASISTIO'
}

export const DOCTOR_APPOINTMENT_STATUS = [
    {value: 'ATENDIDO', label: 'ATENDIDO'},
    {value: 'NO_ASISTIO', label: 'NO ASISTIÓ'},
]

export const SALES_REPORT_TYPES = [
    {value: 'by_month', label: 'POR MES'},
    {value: 'by_year', label: 'POR AÑO'}
]

export const APPOINTMENTS_REPORT_TYPES = [
    {value: 'by_month', label: 'POR MES'},
    {value: 'by_year', label: 'POR AÑO'}
]