import dayjs from "dayjs";

export function reloadOnDismiss(r) {
    if (r.dismiss || r.isDismissed || r.isConfirmed) {
        window.location.reload();
    }
}

export function reloadPage() {
    window.location.reload();
}

export function formatDate(date) {
    return date ? dayjs(date).format('DD/MM/YYYY hh:mm A') : '';
}