import { notify } from '@kyvg/vue3-notification';

export function getStatusVariant(status) {
  switch (status) {
    case 'confirm':
      return 'primary';
    case 'pending':
    case 'booked':
    case 'inactive':
      return 'warning';
    case 'Partialy Paid':
      return 'info';
    case 'success':
    case 'confirmed':
    case 'available':
    case 'paid':
    case 'completed':
    case 'approved':
    case 'active':
    case 1:
      return 'success';
    case 'cancel':
    case 0:
      return 'danger';
    default:
      return 'secondary';
  }
}

export function copyTextName(fileName) {
  // Copy the file name to clipboard
  navigator.clipboard.writeText(fileName).then(() => {
    notify({
      type: 'success',
      title: 'Success',
      text: fileName +' copy successfully',
    });
  }).catch((err) => {
    console.error("Failed to copy text: ", err);
  });
}