import dayjs from "dayjs";

export function reloadOnDismiss(r) {
    if (r.dismiss || r.isDismissed || r.isConfirmed) {
        window.location.reload();
    }
}

export function reloadPage() {
    window.location.reload();
}

export function formatAsDatetime(date) {
    return date ? dayjs(date).format('DD/MM/YYYY hh:mm A') : '';
}

export function formatAsDate(date){
    return date ? dayjs(date).format('DD/MM/YYYY') : '';
}