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