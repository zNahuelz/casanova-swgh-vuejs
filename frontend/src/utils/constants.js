export const SUCCESS_MESSAGES = {
    SUCCESS_TAG: 'Operación completada.',
    SUPPLIER_CREATED: 'Proveedor creado correctamente.',

}

export const ERROR_MESSAGES = {
    ERROR_TAG: 'Oops! Ha sucedido un error.',
    RUC_TAKEN: 'El RUC ingresado ya se encuentra en uso por otro proveedor.',
    SERVER_ERROR: 'Error interno del servidor. Intente nuevamente, si el error persiste comuniquese con administración.',
    SUPPLIER_NOT_FOUND: 'Proveedor no encontrado. Intente nuevamente, si el error persiste comuniquese con administración',
    SEARCH_ERROR: 'Oops! Error de busqueda.'

}

export const SUPPLIER_SEARCH_MODES = [
    {value: 'id', label: 'POR ID'},
    {value: 'name', label: 'POR NOMBRE'},
    {value: 'ruc', label: 'POR RUC'},
]