export function reloadOnDismiss(r: any){
  if(r.dismiss || r.isDismissed || r.isConfirmed){
    window.location.reload();
  }
}
