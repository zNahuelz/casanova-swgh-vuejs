export const SUCCESS_MESSAGES = {
    SUCCESS_TAG: 'Operación completada.',
    SUPPLIER_CREATED: 'Proveedor creado correctamente.',
    PRESENTATION_CREATED: 'Presentación creada correctamente.',
    MEDICINE_CREATED: 'Medicamento creado correctamente.'
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

export const DOCTOR_SEARCH_MODES = [
    {value: 'id', label: 'POR ID'},
    {value: 'name', label: 'POR NOMBRE'},
    {value: 'dni', label: 'POR DNI'},
]

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
]